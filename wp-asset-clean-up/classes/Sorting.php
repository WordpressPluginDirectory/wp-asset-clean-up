<?php
namespace WpAssetCleanUp;

/**
 * Class Sorting
 * @package WpAssetCleanUp
 */
class Sorting
{
	/**
	 * Sorts styles or scripts list in alphabetical ascending order (from A to Z) by the handle name
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public static function sortListByAlpha($list)
	{
		if (isset($list['styles']) && ! empty($list['styles'])) {
			$newStyles = array();

			foreach ($list['styles'] as $indexNo => $styleObj) {
				if (! isset($styleObj->handle)) {
					continue;
				}

				if ($assetAlt = self::matchesWpCoreCriteria($styleObj, 'styles')) {
					if (isset($assetAlt->wp)) {
						$styleObj->wp = true;
					}

					if (isset($assetAlt->ver)) {
						$styleObj->ver = $assetAlt->ver;
					}
				}

				$newStyles[$styleObj->handle] = $styleObj;
			}

			$list['styles'] = $newStyles;

			sort($list['styles']);
		}

		if (isset($list['scripts']) && ! empty($list['scripts'])) {
			$newScripts = array();

			foreach ($list['scripts'] as $indexNo => $scriptObj) {
				if (! isset($scriptObj->handle)) {
					continue;
				}

				if ($assetAlt = self::matchesWpCoreCriteria($scriptObj, 'scripts')) {
					if (isset($assetAlt->wp)) {
						$scriptObj->wp = true;
					}

					if (isset($assetAlt->ver)) {
						$scriptObj->ver = $assetAlt->ver;
					}
				}

				$newScripts[$scriptObj->handle] = $scriptObj;
			}

			$list['scripts'] = $newScripts;

			sort($list['scripts']);
		}

		return $list;
	}

	/**
	 * The appended location values will be used to sort the list of assets
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public static function appendLocation($list)
	{
		if (empty($list) || (! isset($list['styles']) && ! isset($list['scripts']))) {
			return $list;
		}

		$pluginsUrl = plugins_url();

		$urlsToThemeDirs = array();

		foreach (search_theme_directories() as $themeDir => $themeDirArray) {
			$themeUrl = '/'.
	            str_replace(
	                '//',
		            '/',
		            str_replace(Misc::getWpRootDirPath(), '', $themeDirArray['theme_root']) . '/'. $themeDir . '/'
	            );

			$urlsToThemeDirs[] = $themeUrl;
		}

		$urlsToThemeDirs = array_unique($urlsToThemeDirs);

		foreach (array('styles', 'scripts') as $assetType) {
			if ( ! (isset($list[$assetType]) && ! empty($list[$assetType])) ) {
				continue;
			}

			foreach ( $list[$assetType] as $indexNo => $asset ) {
				$src = isset($asset->src) ? $asset->src : '';
				$miscLocalSrc = Misc::getLocalSrc($src);

				if ($assetAlt = self::matchesWpCoreCriteria($asset, $assetType)) {
					// Core Files
					$asset->locationMain = 'wp_core';
					$asset->locationChild = 'none';

					if (isset($assetAlt->wp)) {
						$asset->wp = true;
					}

					if (isset($assetAlt->ver)) {
						$asset->ver = true;
					}
				} elseif ($pluginDir = self::matchesPluginCriteria($asset)) {
					// From plugins directory (usually /wp-content/plugins/)
					if ($pluginDir === 'n/a' && $src) {
						if (strpos($src, '/'.Misc::getPluginsDir().'/') !== false) {
							$srcParts = explode('/'.Misc::getPluginsDir().'/', $src);
							list ($pluginDir) = explode('/', $srcParts[1]);
						} else {
							$relSrc = str_replace($pluginsUrl, '', $src);

							if ($relSrc[0] === '/') {
								$relSrc = substr($relSrc, 1);
							}

							list ($pluginDir) = explode('/', $relSrc);
						}
					}

					$asset->locationMain  = 'plugins';
					$asset->locationChild = $pluginDir;

					} elseif ( (! empty($miscLocalSrc) && strpos($src, '/wp-content/uploads/') !== false) || strpos($src, '/wp-content/uploads/') === 0 ) {
					$asset->locationMain  = 'uploads';
					$asset->locationChild = 'none';
				} else {
					$isWithinThemes = false;

					foreach ( $urlsToThemeDirs as $urlToThemeDir ) {
						$srcRel = str_replace(site_url(),'', $src);

						if ( strpos( $srcRel, $urlToThemeDir ) !== false ) {
							$isWithinThemes = true;

							$themeDir = substr(strrchr(trim($urlToThemeDir, '/'), '/'), 1);

							$asset->locationMain  = 'themes';
							$asset->locationChild = $themeDir;
							break;
							}
					}

					// Default: "External"
					if ( ! $isWithinThemes ) {
						// Outside "themes", "plugins" and "wp-includes"
						$asset->locationMain  = 'external';
						$asset->locationChild = 'none';
						}
				}

				$list[$assetType][$indexNo] = $asset;
			}
		}

		return $list;
	}

	/**
	 * @param $asset
	 * @param $assetType
	 *
	 * @return bool
	 */
	public static function matchesWpCoreCriteria($asset, $assetType)
	{
		global $wp_version;

		$src = isset($asset->src) ? $asset->src : '';

		$localSrc = Misc::getLocalSrc($asset->src);

		$srcToUse = $src;

		if (! empty($localSrc) && isset($localSrc['rel_src']) && $localSrc['rel_src']) {
			$srcToUse = $localSrc['rel_src']; // the relative path
		}

		$isJQueryHandle       = ($assetType === 'scripts') && in_array($asset->handle, array('jquery', 'jquery-core', 'jquery-migrate'));
		$isJQueryUpdater      = ($assetType === 'scripts') && strpos($asset->src, '/' . Misc::getPluginsDir( 'dir_name' ) . '/jquery-updater/js/jquery-') !== false;

		$startsWithWpIncludes = strpos($srcToUse,'wp-includes/') === 0;
		$startsWithWpAdmin    = strpos($srcToUse,'wp-admin/') === 0;
		$wpCoreOnJetpackCdn   = strpos($src, '.wp.com/c/'.$wp_version.'/wp-includes/') !== false;

		$coreCssHandlesList = <<<LIST
global-styles
global-styles-css-custom-properties
wp-block-directory
wp-block-library
wp-block-styles
wp-block-library-theme
wp-block-pattern
wp-webfonts
wp-block-post-date
LIST;
		$cssCoreHandles = array_merge(
			explode("\n", $coreCssHandlesList),
			Misc::getWpCoreCssHandlesFromWpIncludesBlocks() // Source: /wp-includes/blocks/
		);

		$coreJsHandlesList = <<<LIST
admin-bar
code-editor
jquery-ui-datepicker
LIST;
		$jsCoreHandles = explode("\n", $coreJsHandlesList);

		$isCssCoreHandleFromWpIncludesBlocks = ($assetType === 'styles')  && in_array($asset->handle, $cssCoreHandles);
		$isJsCoreHandleFromWpIncludesBlocks  = ($assetType === 'scripts') && in_array($asset->handle, $jsCoreHandles);

		if ( ! ($isJQueryHandle || $isJQueryUpdater || $startsWithWpIncludes || $startsWithWpAdmin || $isCssCoreHandleFromWpIncludesBlocks || $isJsCoreHandleFromWpIncludesBlocks || $wpCoreOnJetpackCdn) ) {
			return false; // none of the above conditions matched, thus, this is not a WP core file
		}

		$assetAlt = $asset;

		if ($wpCoreOnJetpackCdn) {
			$assetAlt->wp  = true;
			$assetAlt->ver = $wp_version;
		}

		return $assetAlt;
	}

	/**
	 * @param $asset
	 *
	 * @return bool|string
	 */
	public static function matchesPluginCriteria($asset)
	{
		$src = isset($asset->src) ? $asset->src : '';

		$isOxygenBuilderPlugin = strpos( $src, '/wp-content/uploads/oxygen/css/' ) !== false;
		$isElementorPlugin     = strpos( $src, '/wp-content/uploads/elementor/css/' ) !== false;
		$isWooCommerceInline   = $asset->handle === 'woocommerce-inline';
		$miscLocalSrc          = Misc::getLocalSrc($src);

		$isPlugin = $isOxygenBuilderPlugin ||
		            $isElementorPlugin     ||
		            $isWooCommerceInline   ||
		            strpos( $src, plugins_url() ) !== false ||
		            ((! empty($miscLocalSrc) && strpos($src, '/'.Misc::getPluginsDir().'/') !== false) || strpos($src, '/'.Misc::getPluginsDir().'/') === 0);

		if (! $isPlugin) {
			return false;
		}

		$pluginDir = 'n/a'; // default

		if ($isOxygenBuilderPlugin) {
			$pluginDir = 'oxygen';
		} elseif ($isElementorPlugin) {
			$pluginDir = 'elementor';
		} elseif ($isWooCommerceInline) {
			$pluginDir = 'woocommerce';
		}

		return $pluginDir;
	}
}
