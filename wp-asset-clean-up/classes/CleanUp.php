<?php
/** @noinspection MultipleReturnStatementsInspection */

namespace WpAssetCleanUp;

use WpAssetCleanUp\OptimiseAssets\OptimizeCommon;

/**
 * Class CleanUp
 * @package WpAssetCleanUp
 */
class CleanUp
{
	/**
	 *
	 */
	public function init()
	{
		// "Test Mode" is enabled and the page is viewed by a regular visitor (not administrator with plugin activation privileges)
		// Stop here as the script will NOT PREVENT any of the elements below to load
		// They will load as they used to for the regular visitor while the admin debugs the website
		add_action('init', array($this, 'doClean'), 12);
	}

	/**
	 *
	 */
	public function doClean()
	{
		if ( ! method_exists( '\WpAssetCleanUp\OptimiseAssets\OptimizeCommon', 'preventAnyFrontendOptimization' ) ) {
			return; // something's funny; for some reason, on very rare occasions, the class is not found, so don't continue
		}

		if (wpacuIsDefinedConstant('WPACU_ALLOW_ONLY_UNLOAD_RULES') || OptimizeCommon::preventAnyFrontendOptimization() || Main::instance()->preventAssetsSettings() ) {
			return;
		}

		$settings = Main::instance()->settings;

		// Remove "Really Simple Discovery (RSD)" link?
		if ($settings['remove_rsd_link'] == 1 || $settings['disable_xmlrpc'] === 'disable_all') {
			// <link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://yourwebsite.com/xmlrpc.php?rsd" />
			remove_action('wp_head', 'rsd_link');
		}

		// Remove "REST API" link?
		if ($settings['remove_rest_api_link'] == 1) {
			// <link rel='https://api.w.org/' href='https://yourwebsite.com/wp-json/' />
			remove_action('wp_head', 'rest_output_link_wp_head');

			// Removes the following printed within "Response headers":
			// <https://yourwebsite.com/wp-json/>; rel="https://api.w.org/"
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
		}

		// Remove "Shortlink"?
		if ($settings['remove_shortlink'] == 1) {
			// <link rel='shortlink' href="https://yourdomain.com/?p=1">
			remove_action('wp_head', 'wp_shortlink_wp_head');

			// link: <https://yourdomainname.com/wp-json/>; rel="https://api.w.org/", <https://yourdomainname.com/?p=[post_id_here]>; rel=shortlink
			remove_action('template_redirect', 'wp_shortlink_header', 11);
		}

		// Remove "WordPress version" tag?
		if ($settings['remove_wp_version'] || $settings['remove_generator_tag']) {
			// <meta name="generator" content="WordPress 4.9.8" />
			remove_action('wp_head', 'wp_generator');

			// also hide it from RSS
			add_filter('the_generator', '__return_false');
		}

        // Remove all generator tags?
        // Another function will trigger later that will scan the HTML source, but it's slower
        // and if, for instance, the meta "generator" will be removed here, then this will process faster
        // and save some resources (the HTML source is not scanned anymore)
        if ($settings['remove_generator_tag']) {
            // WPML Multilingual CMS
            if (defined('ICL_SITEPRESS_VERSION')) {
                global $sitepress;

                if ( ! empty($sitepress) && method_exists($sitepress, 'meta_generator_tag')) {
                    remove_action('wp_head', array($sitepress, 'meta_generator_tag'));
                }
            }

            // Give - Donation Plugin
            remove_action( 'wp_head', 'give_version_in_header' );
        }

		if ($settings['disable_rss_feed']) {
			$this->doDisableRssFeed();
		}

		// Remove Main RSS Feed Link?
		if ($settings['remove_main_feed_link']) {
			add_filter('feed_links_show_posts_feed', '__return_false');
			remove_action('wp_head', 'feed_links_extra', 3);
		}

		// Remove Comment RSS Feed Link?
		if ($settings['remove_comment_feed_link']) {
			add_filter('feed_links_show_comments_feed', '__return_false');
		}

		// Disable XML-RPC protocol support (partially or completely)
		if (in_array($settings['disable_xmlrpc'], array('disable_all', 'disable_pingback'))) {
			// Partially or Completely Options / Pingback will be disabled
			$this->disableXmlRpcPingback();

			// Complete disable the service
			if ($settings['disable_xmlrpc'] === 'disable_all') {
				add_filter('xmlrpc_enabled', '__return_false');
			}
		}

        /*
         * [START] DEPRECATED
         */
            // Remove "Post's Relational Links"?
            if ($settings['remove_posts_rel_links'] == 1) {
                // <link rel='prev' title='Title of adjacent post' href='https://yourdomain.com/adjacent-post-slug-here/' />
                remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
            }

            // Remove "Windows Live Writer" link?
            if ($settings['remove_wlw_link'] == 1) {
                // <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://yourwebsite.com/wp-includes/wlwmanifest.xml">
                remove_action('wp_head', 'wlwmanifest_link');
            }
        /*
         * [END] DEPRECATED
         */
	}

	/**
	 * Called in OptimiseAssets/OptimizeCommon.php
	 *
	 * @param $htmlSource
	 *
	 * @return string|string[]|null
	 */
	public static function cleanPingbackLinkRel($htmlSource)
	{
		$pingBackUrl = get_bloginfo('pingback_url');

		$matchRegExps = array(
			'#<link rel=("|\')pingback("|\') href=("|\')'.$pingBackUrl.'("|\')( /|)>#',
			'#<link href=("|\')'.$pingBackUrl.'("|\') rel=("|\')pingback("|\')( /|)>#'
		);

		foreach ($matchRegExps as $matchRegExp) {
			$htmlSource = preg_replace($matchRegExp, '', $htmlSource);
		}

		return $htmlSource;
	}

	/**
	 *
	 */
	public function disableXmlRpcPingback()
	{
		// Disable Pingback method
		add_filter('xmlrpc_methods', static function ($methods) {
			unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
			return $methods;
		} );

		// Remove X-Pingback HTTP header
		add_filter('wp_headers', static function ($headers) {
			unset($headers['X-Pingback']);
			return $headers;
		});
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed
     * @noinspection NestedAssignmentsUsageInspection
     */
	public static function removeMetaGenerators($htmlSource)
	{
        if ( ! preg_match('#name(\s+|)=(\s+|)("|\'|)generator("|\'|)#Umi', $htmlSource) ) {
            return $htmlSource;
        }

        // Are there any meta-generator tags already cached? use these as it's faster than traversing the DOM
        $allCachedMetaGeneratorTags = $metaTagsToStrip = array();
        $metaGeneratorCacheKey = WPACU_PLUGIN_ID.'_site_meta_generator_tags';

        if ( $metaGeneratorTagsJsonEncoded = get_transient($metaGeneratorCacheKey) ) {
            $metaGeneratorTags = @json_decode( $metaGeneratorTagsJsonEncoded, true );

            if ( ! empty($metaGeneratorTags) && wpacuJsonLastError() === JSON_ERROR_NONE ) {
                $allCachedMetaGeneratorTags = $metaGeneratorTags;

                foreach ( $metaGeneratorTags as $metaGeneratorTag ) {
                    $metaTagsToStrip[$metaGeneratorTag] = '';
                }

                $htmlSource = strtr($htmlSource, $metaTagsToStrip);

                if ( ! preg_match('#name(\s+|)=(\s+|)("|\'|)generator("|\'|)#Umi', $htmlSource) ) {
                    // All good! The tags were stripped
                    // Otherwise, fetch the DOM, and make sure they are all stripped
                    return $htmlSource;
                }
            }
        }

		$fetchMethod = 'dom'; // 'regex' or 'dom'

		if ( $fetchMethod === 'dom' && $htmlSource && Misc::isDOMDocumentOn() ) {
			$domTag = OptimizeCommon::getDomLoadedTag($htmlSource, 'removeMetaGenerators');

			$metaTagsToStrip = array();

			foreach ( $domTag->getElementsByTagName( 'meta' ) as $tagObject ) {
				$nameAttrValue = $tagObject->getAttribute( 'name' );

				if ( $nameAttrValue === 'generator' ) {
					$outerTag = $outerTagRegExp = trim( Misc::getOuterHTML( $tagObject ) );

					// As DOMDocument doesn't retrieve the exact string, some alterations to the RegEx have to be made
					// Leave no room for errors as all sorts of characters can be within the "content" attribute
					$last2Chars = substr( $outerTag, - 2 );

					if ( $last2Chars === '">' || $last2Chars === "'>" ) {
						$tagWithoutLastChar = substr( $outerTag, 0, - 1 );
						$outerTagRegExp     = preg_quote( $tagWithoutLastChar, '/' ) . '(.*?)>';
					}

					$outerTagRegExp = str_replace(
						array( '"', '&lt;', '&gt;' ),
						array( '("|\'|)', '(<|&lt;)', '(>|&gt;)' ),
						$outerTagRegExp
					);

					if ( strpos( $outerTagRegExp, '<meta' ) !== false ) {
						$outerTagRegExp = str_replace('#', '\#', $outerTagRegExp);
                        $outerTagRegExp = str_replace('\\\\#', '\#', $outerTagRegExp);

                        preg_match_all( '#' . $outerTagRegExp . '#si', $htmlSource, $matches );

						if ( ! empty( $matches[0][0] ) && strip_tags( $matches[0][0] ) === '' ) {
							$metaTagsToStrip[$matches[0][0]] = '';
						}
					}
				}
			}

			libxml_clear_errors();
		}

        if ( ! empty( $metaTagsToStrip ) ) {
            $htmlSource = strtr( $htmlSource, $metaTagsToStrip );

            foreach ( array_keys($metaTagsToStrip) as $metaGeneratorTagExtracted ) {
                if ( ! in_array( $metaGeneratorTagExtracted, $allCachedMetaGeneratorTags ) ) {
                    $allCachedMetaGeneratorTags[] = $metaGeneratorTagExtracted;
                }
            }

            // Cache the meta-generator tags; In some tests, this method performs even 5x faster when caching is used
            // Set it to refresh every 30 days; This is reasonable when the website changes (e.g. new plugins are activated, a new website structure is used)
            set_transient( $metaGeneratorCacheKey, wp_json_encode($allCachedMetaGeneratorTags), 3600 * 24 * 30 );
        }

		return $htmlSource;
	}

	/**
	 * @param string $htmlSource
	 * @param bool $ignoreExceptions - this is related to "Do not remove comments containing the following (case-insensitive) text" from "Strip HTML comments?" area
	 *
	 * @return string|string[]
     * @noinspection ParameterDefaultValueIsNotNullInspection
     */
	public static function removeHtmlComments($htmlSource, $ignoreExceptions = false)
	{
		if ( strpos($htmlSource, '<!--') === false || ! Misc::isDOMDocumentOn() ) {
			return $htmlSource;
		}

		$domTag = OptimizeCommon::getDomLoadedTag($htmlSource, 'removeHtmlComments');

        if ( ! $domTag ) {
            return $htmlSource;
        }

        $xpathComments = new \DOMXPath($domTag);
        $comments      = $xpathComments->query('//comment()');

        libxml_clear_errors();

        if ( $comments === null ) {
			return $htmlSource;
		}

		preg_match_all('#<!--(.*?)-->#s', $htmlSource, $matchesRegExpComments);

		// "comments" within tag attributes or script tags?
		// e.g. <script>var type='<!-- A comment here -->';</script>
		// e.g. <div data-info="This is just a <!-- comment --> text">Content here</div>
		$commentsWithinQuotes = array();

		if (isset($matchesRegExpComments[1]) && count($matchesRegExpComments[1]) !== count($comments)) {
			preg_match_all('#=(|\s+)([\'"])(|\s+)<!--(.*?)-->(|\s+)([\'"])#s', $htmlSource, $matchesCommentsWithinQuotes);

			if ( ! empty($matchesCommentsWithinQuotes[0]) ) {
				foreach ($matchesCommentsWithinQuotes[0] as $matchedDataOriginal) {
					$matchedDataUpdated = str_replace(
						array('', '<!--', '-->'),
						array('--wpacu-space-del--', '--wpacu-start-comm--', '--wpacu-end-comm--'),
						$matchedDataOriginal
					);

					$htmlSource = str_replace($matchedDataOriginal, $matchedDataUpdated, $htmlSource);

					$commentsWithinQuotes[] = array(
						'original' => $matchedDataOriginal,
						'updated'  => $matchedDataUpdated
					);
				}
			}
		}

		$stripCommentsList = array();

		foreach ($comments as $comment) {
			$entireComment = '<!--' . $comment->nodeValue . '-->';

            // Do not strip MSIE conditional comments
            if ( strpos( $entireComment, '<!--<![endif]-->' ) !== false ||
                 preg_match( '#<!--\[if(.*?)]>(.*?)<!-->#si', $entireComment ) ||
                 preg_match( '#<!--\[if(.*?)\[endif]-->#si', $entireComment ) ) {
                continue;
            }

            // Any exceptions set in "Strip HTML comments?" textarea?
            // $ignoreExceptions has to be set to false (as it is by default)
            if (! $ignoreExceptions && Main::instance()->settings['remove_html_comments_exceptions']) {
                $removeHtmlCommentsExceptions = trim(Main::instance()->settings['remove_html_comments_exceptions']);

                if (strpos($removeHtmlCommentsExceptions, "\n") !== false) {
                    foreach (explode("\n", $removeHtmlCommentsExceptions) as $removeCommExceptionPattern) {
                        $removeCommExceptionPattern = trim($removeCommExceptionPattern);

                        if (stripos($entireComment, $removeCommExceptionPattern) !== false) {
                            continue 2;
                        }
                    }
                } elseif (stripos($entireComment, $removeHtmlCommentsExceptions) !== false) {
                    continue;
                }
            }

            if (strlen($entireComment) < 200) {
                $stripCommentsList[ $entireComment ] = '';
            } else {
                $htmlSource = str_replace($entireComment, '', $htmlSource);
            }
        }

        if (! empty($stripCommentsList)) {
            $htmlSource = strtr( $htmlSource, $stripCommentsList );
        }

        if (! empty($commentsWithinQuotes)) {
            foreach ($commentsWithinQuotes as $commentQuote) {
                $htmlSource = str_replace($commentQuote['updated'], $commentQuote['original'], $htmlSource);
            }
        }

        return $htmlSource;
    }

    /**
     * @param $strContains
     * @param $htmlSource
     *
     * @return mixed
     */
	public static function cleanLinkTagFromHtmlSource($strContains, $htmlSource)
	{
		if (! is_array($strContains)) {
			$strContains = array($strContains);
		}

		$strContains = array_map(function($value) {
			if (strpos($value, 'data-wpacu-style-handle') !== false) {
				return $value; // no need to use preg-quote
			}

			return preg_quote($value, '/');
		}, $strContains);

		preg_match_all(
			'#<link[^>]*('.implode('|', $strContains).')[^>].*(>)#Usmi',
			$htmlSource,
			$matchesSourcesFromTags
		);

		if (empty($matchesSourcesFromTags)) {
			return $htmlSource;
		}

		foreach ($matchesSourcesFromTags as $matchesFromTag) {
			if (! (isset($matchesFromTag[0]) && $matchesFromTag[0])) {
				continue;
			}

			$linkTag = $matchesFromTag[0];

			if (strncasecmp($linkTag, '<link', 5) === 0 && substr($linkTag, -1) === '>' && strip_tags($linkTag) === '') {
				$htmlSource = str_replace($linkTag, '', $htmlSource);
			}
		}

		return $htmlSource;
	}

	/**
	 * @param $strContains
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public static function cleanScriptTagFromHtmlSource($strContains, $htmlSource)
	{
		if (! is_array($strContains)) {
			$strContains = array($strContains);
		}

		$strContains = array_map(function($value) {
			if (strpos($value, 'data-wpacu-script-handle') !== false) {
				return $value; // no need to use preg-quote
			}

			return preg_quote($value, '/');
		}, $strContains);

		preg_match_all(
			'#<script[^>]*('.implode('|', $strContains).')[^>].*(>)#Usmi',
			$htmlSource,
			$matchesSourcesFromTags
		);

		if (empty($matchesSourcesFromTags)) {
			return $htmlSource;
		}

		foreach ($matchesSourcesFromTags as $matchesFromTag) {
			if (isset($matchesFromTag[0]) && $matchesFromTag[0] && strip_tags($matchesFromTag[0]) === '') {
				$htmlSource = str_replace($matchesFromTag[0] . '</script>', '', $htmlSource);
			}
		}

		return $htmlSource;
	}

	/**
	 *
	 */
	public function doDisableEmojis()
	{
		// Emojis Actions and Filters
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');

		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');

		// TinyMCE Emojis
		add_filter('tiny_mce_plugins', array($this, 'removeEmojisTinymce'));

		add_filter('emoji_svg_url', '__return_false');
	}

	/**
	 * @param $plugins
	 *
	 * @return array
	 */
	public function removeEmojisTinymce($plugins)
	{
		if (is_array($plugins)) {
			return array_diff($plugins, array('wpemoji'));
		}

		return array();
	}

	/**
	 *
	 */
	public function doDisableOembed()
	{
		add_action('init', static function() {
			// Remove the REST API endpoint.
			remove_action('rest_api_init', 'wp_oembed_register_route');

			// Turn off oEmbed auto discovery.
			add_filter('embed_oembed_discover', '__return_false');

			// Don't filter oEmbed results.
			remove_filter('oembed_dataparse', 'wp_filter_oembed_result');

			// Remove oEmbed discovery links.
			remove_action('wp_head', 'wp_oembed_add_discovery_links');

			// Remove oEmbed-specific JavaScript from the front-end and back-end.
			remove_action('wp_head', 'wp_oembed_add_host_js');

			add_filter('tiny_mce_plugins', static function ($plugins) {
				return array_diff($plugins, array('wpembed'));
			});

			// Remove all embeds rewrite rules.
			add_filter('rewrite_rules_array', static function ($rules) {
				if ( ! empty($rules) ) {
					foreach ( $rules as $rule => $rewrite ) {
						if ( is_string($rewrite) && false !== strpos( $rewrite, 'embed=true' ) ) {
							unset( $rules[ $rule ] );
						}
					}
				}
				return $rules;
			});

			// Remove filter of the oEmbed result before any HTTP requests are made.
			remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result');
		}, 9999 );
	}

	/**
	 *
	 */
	public function doDisableRssFeed()
	{
		add_action('do_feed',               array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_rdf',           array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_rss',           array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_rss2',          array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_atom',          array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_rss2_comments', array($this, 'disabledRssFeedMsg'), 1);
		add_action('do_feed_atom_comments', array($this, 'disabledRssFeedMsg'), 1);
	}

	/**
	 *
	 */
	public function disabledRssFeedMsg()
	{
		$wpacuSettings = new Settings();
		$settingsArray = $wpacuSettings->getAll();

		$feedDisableMsg = isset($settingsArray['disable_rss_feed_message']) ? $settingsArray['disable_rss_feed_message'] : '';

		wp_die( __($feedDisableMsg, 'wp-asset-clean-up') );
	}

	/**
	 *
     * @noinspection PhpUnusedParameterInspection
     */
	public function cleanUpHtmlOutputForAssetsCall()
	{
		if (isset($_GET['wpacu_clean_load'])) {
			// No Admin Bar
			add_filter('show_admin_bar', '__return_false', PHP_INT_MAX);
		}

		// No Autoptimize
        wpacuDefineConstant( 'AUTOPTIMIZE_NOBUFFER_OPTIMIZE' );

		add_filter('autoptimize_filter_noptimize', '__return_false');

		// Use fewer resources during CSS/JS fetching by preventing other plugins to interfere with the HTML output as it's completely unnecessary in this instance
		if (wpacuIsPluginActive('autoptimize/autoptimize.php')) {
			foreach (array('autoptimize_html', 'autoptimize_css', 'autoptimize_js', 'autoptimize_cdn_url', 'autoptimize_optimize_logged') as $aoOption) {
				add_filter('pre_option_'.$aoOption, static function($value) { return ''; });
			}
		}

		// No Fast Velocity Minify
		add_action('plugins_loaded', static function() {
			remove_action( 'setup_theme', 'fastvelocity_process_frontend' );
		}, PHP_INT_MAX);

		// No WP Rocket (Minify / Concatenate)
		add_filter( 'get_rocket_option_minify_css',             '__return_false' );
		add_filter( 'get_rocket_option_minify_concatenate_css', '__return_false' );

		add_filter( 'get_rocket_option_minify_js',              '__return_false' );
		add_filter( 'get_rocket_option_minify_concatenate_js',  '__return_false' );

		// No W3 Total Cache: Minify
		add_filter('w3tc_minify_enable', '__return_false');

		// [NO SG Optimiser]
		self::filterSGOptions();

		// Emulate page builder param to view page with no SG Optimiser on request
		// Extra params to be used in case 'SG Optimiser' is called before Asset CleanUp:
        // 'fl_builder', 'vcv-action', 'et_fb', 'ct_builder', 'tve'
		add_filter('sgo_pb_params', static function($pbParams) {
			$pbParams[] = WPACU_LOAD_ASSETS_REQ_KEY; // fetching assets
			$pbParams[] = 'wpacu_clean_load'; // loading the page unoptimized for debugging purposes
			return $pbParams;
		});

		// Fallback in case SG Optimizer is triggered BEFORE Asset CleanUp and the filter above will not work
		add_filter('sgo_css_combine_exclude', array($this, 'allCssHandles'));
		add_filter('sgo_css_minify_exclude',  array($this, 'allCssHandles'));
		add_filter('sgo_js_minify_exclude',   array($this, 'allJsHandles'));
		add_filter('sgo_js_async_exclude',    array($this, 'allJsHandles'));

		add_filter('sgo_html_minify_exclude_params', static function ($excludeParams) {
			$excludeParams[] = WPACU_LOAD_ASSETS_REQ_KEY; // fetching assets
			$excludeParams[] = 'wpacu_clean_load'; // loading the page unoptimized for debugging purposes
			return $excludeParams;
		});
		// [/NO SG Optimiser]
	}

	/**
	 *
	 */
	public static function filterSGOptions()
	{
		// SG Optimizer Plugin
		$sgOptimizerMapping = array(
			'autoflush'            => 'siteground_optimizer_autoflush_cache',
			'dynamic-cache'        => 'siteground_optimizer_enable_cache',
			'memcache'             => 'siteground_optimizer_enable_memcached',
			'ssl-fix'              => 'siteground_optimizer_fix_insecure_content',
			'html'                 => 'siteground_optimizer_optimize_html',
			'js'                   => 'siteground_optimizer_optimize_javascript',
			'js-async'             => 'siteground_optimizer_optimize_javascript_async',
			'css'                  => 'siteground_optimizer_optimize_css',
			'combine-css'          => 'siteground_optimizer_combine_css',
			'querystring'          => 'siteground_optimizer_remove_query_strings',
			'emojis'               => 'siteground_optimizer_disable_emojis',
			'images'               => 'siteground_optimizer_optimize_images',
			'lazyload_images'      => 'siteground_optimizer_lazyload_images',
			'lazyload_gravatars'   => 'siteground_optimizer_lazyload_gravatars',
			'lazyload_thumbnails'  => 'siteground_optimizer_lazyload_thumbnails',
			'lazyload_responsive'  => 'siteground_optimizer_lazyload_responsive',
			'lazyload_textwidgets' => 'siteground_optimizer_lazyload_textwidgets',
			'ssl'                  => 'siteground_optimizer_ssl_enabled',
			'gzip'                 => 'siteground_optimizer_enable_gzip_compression',
			'browser-caching'      => 'siteground_optimizer_enable_browser_caching',
		);

		foreach ($sgOptimizerMapping as $optionName) {
			add_filter('pre_option_'.$optionName, '__return_false');
		}
	}

	/**
	 * @return array
	 */
	public function allCssHandles()
	{
		global $wp_styles;

		$allCssHandles = array();

		if ( ! empty($wp_styles->registered) ) {
			$allCssHandles = array_keys($wp_styles->registered);
		}

		return $allCssHandles;
	}

	/**
	 * @return array
	 */
	public function allJsHandles()
	{
		global $wp_scripts;

		$allJsHandles = array();

		if ( ! empty($wp_scripts->registered) ) {
			$allJsHandles = array_keys($wp_scripts->registered);
		}

		return $allJsHandles;
	}
}
