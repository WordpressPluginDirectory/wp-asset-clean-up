<?php
namespace WpAssetCleanUp\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\MetaBoxes;
use WpAssetCleanUp\Misc;

/**
 * Class MinifyJs
 * @package WpAssetCleanUp\OptimiseAssets
 */
class MinifyJs
{
	/**
	 * @param $jsContent
	 *
	 * @return string|string[]|null
	 */
	public static function applyMinification($jsContent)
	{
		if (class_exists('\MatthiasMullie\Minify\JS')) {
				$sha1OriginalContent = sha1($jsContent);
				$checkForAlreadyMinifiedShaOne = mb_strlen($jsContent) > 40000;

				// Let's check if the content is already minified
				// Save resources as the minify process can take time if the content is very large
				// Limit the total number of entries to 100: if it's more than that, it's likely because there's dynamic JS altering on every page load
				if ($checkForAlreadyMinifiedShaOne && OptimizeCommon::originalContentIsAlreadyMarkedAsMinified($sha1OriginalContent, 'scripts')) {
					return $jsContent;
				}

				// Minify it
				$alreadyMinified = false; // default

				$minifier = new \MatthiasMullie\Minify\JS($jsContent);
				$minifiedContent = trim($minifier->minify());

				if (trim($minifiedContent) === trim(trim($jsContent, ';'))) {
					$minifiedContent = $jsContent; // consider them the same if only the ';' at the end was stripped (it doesn't worth the resources that would be used)
					$alreadyMinified = true;
				}

				// If the resulting content is the same, mark it as minified to avoid the minify process next time
				if ($checkForAlreadyMinifiedShaOne && $alreadyMinified) {
					// If the resulting content is the same, mark it as minified to avoid the minify process next time
					OptimizeCommon::originalContentMarkAsAlreadyMinified( $sha1OriginalContent, 'scripts' );
				}

				return $minifiedContent;
			}

			return $jsContent;
		}

	/**
	 * @param $src
	 * @param string $handle
	 *
	 * @return bool
	 */
	public static function skipMinify($src, $handle = '')
	{
		// Things like WP Fastest Cache Toolbar JS shouldn't be minified and take up space on the server
		if ($handle !== '' && in_array($handle, Main::instance()->skipAssets['scripts'])) {
			return true;
		}

		$regExps = array(
			'#/wp-content/plugins/wp-asset-clean-up(.*?).js#',

			// Other libraries from the core that end in .min.js
			'#/wp-includes/(.*?).min.js#',

			// jQuery & jQuery Migrate
			'#/wp-includes/js/jquery/jquery.js#',
			'#/wp-includes/js/jquery/jquery-migrate.js#',

			// Files within /wp-content/uploads/
			// Files within /wp-content/uploads/ or /wp-content/cache/
			// Could belong to plugins such as "Elementor, "Oxygen" etc.
			//'#/wp-content/uploads/(.*?).js#',
			'#/wp-content/cache/(.*?).js#',

			// Already minified, and it also has a random name making the cache folder make bigger
			'#/wp-content/bs-booster-cache/#',

			// Elementor .min.js
			'#/wp-content/plugins/elementor/assets/(.*?).min.js#',

			// WooCommerce Assets
			'#/wp-content/plugins/woocommerce/assets/js/(.*?).min.js#',

			// Google Site Kit
			// The files are already optimized (they just have comments once in a while)
			// Minifying them causes some errors, so better to leave them load as they are
			'#/wp-content/plugins/google-site-kit/#',

            // TranslatePress Multilingual
            '#/translatepress-multilingual/assets/js/trp-editor.js#',

			);

		$regExps = Misc::replaceRelPluginPath($regExps);

		if (Main::instance()->settings['minify_loaded_js_exceptions'] !== '') {
			$loadedJsExceptionsPatterns = trim(Main::instance()->settings['minify_loaded_js_exceptions']);

			if (strpos($loadedJsExceptionsPatterns, "\n")) {
				// Multiple values (one per line)
				foreach (explode("\n", $loadedJsExceptionsPatterns) as $loadedJsExceptionPattern) {
					$regExps[] = '#'.trim($loadedJsExceptionPattern).'#';
				}
			} else {
				// Only one value?
				$regExps[] = '#'.trim($loadedJsExceptionsPatterns).'#';
			}
		}

		foreach ($regExps as $regExp) {
			if ( preg_match( $regExp, $src ) || ( strpos($src, $regExp) !== false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed|string
	 */
	public static function minifyInlineScriptTags($htmlSource)
	{
		if (stripos($htmlSource, '<script') === false) {
			return $htmlSource; // no SCRIPT tags, hmm
		}

		$skipTagsContaining = array_map( static function ( $toMatch ) {
			return preg_quote($toMatch, '/');
		}, array(
			'data-wpacu-skip',
			'/* <![CDATA[ */', // added via wp_localize_script()
			'wpacu-google-fonts-async-load',
			'wpacu-preload-async-css-fallback',
			/* [wpacu_pro] */'data-wpacu-inline-js-file',/* [/wpacu_pro] */
			'document.body.prepend(wpacuLinkTag',
			'var wc_product_block_data = JSON.parse( decodeURIComponent(',
			'/(^|\s)(no-)?customize-support(?=\s|$)/', // WP Core
			'b[c] += ( window.postMessage && request ? \' \' : \' no-\' ) + cs;', // WP Core
			'data-wpacu-own-inline-script', // Only shown to the admin, irrelevant for any optimization (save resources)
			// [wpacu_pro]
			'data-wpacu-inline-js-file', // already minified/optimized since the INLINE was generated from the cached file
			// [/wpacu_pro]
		));

		// Do not perform another \DOMDocument call if it was done already somewhere else (e.g. CombineJs)
		$fetchType = 'regex'; // 'regex' or 'dom'

		if ($fetchType === 'regex') {
			preg_match_all( '@(<script[^>]*?>).*?</script>@si', $htmlSource, $matchesScriptTags, PREG_SET_ORDER );

			if ( $matchesScriptTags === null ) {
				return $htmlSource;
			}

			foreach ($matchesScriptTags as $matchedScript) {
				if (isset($matchedScript[0]) && $matchedScript[0]) {
					$originalTag = $matchedScript[0];

					if (strpos($originalTag, 'src=') && strtolower(substr($originalTag, -strlen('></script>'))) === strtolower('></script>')) {
						// Only inline SCRIPT tags allowed
						continue;
					}

					// No need to use extra resources as the tag is already minified
					if ( preg_match( '/(' . implode( '|', $skipTagsContaining ) . ')/', $originalTag ) ) {
						continue;
					}

					// Only 'text/javascript' type is allowed for minification
					$scriptType = Misc::getValueFromTag($originalTag, 'type') ?: 'text/javascript'; // default

					if ($scriptType !== 'text/javascript') {
						continue;
					}

					$tagOpen     = $matchedScript[1];
					$withTagOpenStripped = substr($originalTag, strlen($tagOpen));
					$originalTagContents = substr($withTagOpenStripped, 0, -strlen('</script>'));

					$newTagContents = OptimizeJs::maybeAlterContentForInlineScriptTag( $originalTagContents, true );

					if ( $newTagContents !== $originalTagContents ) {
						$htmlSource = str_ireplace( '>' . $originalTagContents . '</script', '>' . $newTagContents . '</script', $htmlSource );
					}
				}
			}
		}

		return $htmlSource;
	}

	/**
	 * @return bool
	 */
	public static function isMinifyJsEnabled()
	{
		if (defined('WPACU_IS_MINIFY_JS_ENABLED')) {
			return WPACU_IS_MINIFY_JS_ENABLED;
		}

		// Request Minify On The Fly
		// It will preview the page with JS minified
		// Only if the admin is logged-in as it uses more resources (CPU / Memory)
		if ( isset($_GET['wpacu_js_minify']) && Menu::userCanManageAssets()) {
			self::isMinifyJsEnabledChecked('true');
			return true;
		}

		if ( isset($_REQUEST['wpacu_no_js_minify']) || // not on query string request (debugging purposes)
		     is_admin() || // not for Dashboard view
		     (! Main::instance()->settings['minify_loaded_js']) || // Minify JS has to be Enabled
		     (Main::instance()->settings['test_mode'] && ! Menu::userCanManageAssets()) ) { // Does not trigger if "Test Mode" is Enabled
			self::isMinifyJsEnabledChecked('false');
			return false;
		}

		$isSingularPage = defined('WPACU_CURRENT_PAGE_ID') && WPACU_CURRENT_PAGE_ID > 0 && is_singular();

		if ($isSingularPage || Misc::isHomePage()) {
			// If "Do not minify JS on this page" is checked in "Asset CleanUp: Options" side meta box
			if ($isSingularPage) {
				$pageOptions = MetaBoxes::getPageOptions( WPACU_CURRENT_PAGE_ID ); // Singular page
			} else {
				$pageOptions = MetaBoxes::getPageOptions(0, 'front_page'); // Home page
			}

			if ( isset( $pageOptions['no_js_minify'] ) && $pageOptions['no_js_minify'] ) {
				self::isMinifyJsEnabledChecked('false');
				return false;
			}
		}

		if (OptimizeJs::isOptimizeJsEnabledByOtherParty('if_enabled')) {
			self::isMinifyJsEnabledChecked('false');
			return false;
		}

		self::isMinifyJsEnabledChecked('true');
		return true;
	}

	/**
	 * @param $value
	 */
	public static function isMinifyJsEnabledChecked($value)
	{
		if (! defined('WPACU_IS_MINIFY_JS_ENABLED')) {
			if ($value === 'true') {
				define( 'WPACU_IS_MINIFY_JS_ENABLED', true );
			} elseif ($value === 'false') {
				define( 'WPACU_IS_MINIFY_JS_ENABLED', false );
			}
		}
	}
}
