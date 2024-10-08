<?php
// no direct access
use WpAssetCleanUp\Admin\Info;
use WpAssetCleanUp\Admin\MiscAdmin;
use WpAssetCleanUp\Misc;

if (! isset($data)) {
	exit;
}

// Show areas by:
// "Plugins", "Themes" (parent theme and child theme), "WordPress Core"
// External locations (outside plugins and themes)
// 3rd party external locations (e.g. Google API Fonts, CND urls such as the ones for Bootstrap etc.)
$listAreaStatus    = $data['plugin_settings']['assets_list_layout_areas_status'];
$pluginsAreaStatus = $data['plugin_settings']['assets_list_layout_plugin_area_status'] ?: 'expanded';

/*
* -------------------------
* [START] BY EACH LOCATION
* -------------------------
*/
if (! empty($data['all']['styles']) || ! empty($data['all']['scripts'])) {
    require_once __DIR__.'/_assets-top-area.php';

    if (! function_exists('get_plugins') && ! is_admin()) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

	$allPlugins            = get_plugins();
	$allThemes             = wp_get_themes();
	$allActivePluginsIcons = MiscAdmin::getAllActivePluginsIcons();

    $data['rows_build_array'] =
    $data['rows_by_location'] = true;

    $data['rows_assets'] = array();

    require_once __DIR__.'/_asset-rows.php';

    $locationsText = array(
        'plugins'   => '<span class="dashicons dashicons-admin-plugins"></span> '.esc_html__('From Plugins', 'wp-asset-clean-up').' (.css &amp; .js)',
        'themes'    => '<span class="dashicons dashicons-admin-appearance"></span> '.esc_html__('From Themes', 'wp-asset-clean-up').' (.css &amp; .js)',
        'uploads'   => '<span class="dashicons dashicons-wordpress"></span> '.esc_html__('WordPress Uploads Directory', 'wp-asset-clean-up').' (.css &amp; .js)',
        'wp_core'   => '<span class="dashicons dashicons-wordpress"></span> '.esc_html__('WordPress Core', 'wp-asset-clean-up').' (.css &amp; .js)',
        'external'  => '<span class="dashicons dashicons-cloud"></span> '.esc_html__('External 3rd Party', 'wp-asset-clean-up').' (.css &amp; .js)'
    );

    if (! empty($data['rows_assets'])) {
        // Sorting: Plugins, Themes, Uploads Directory and External Assets
        $rowsAssets = array('plugins' => array(), 'themes' => array(), 'uploads' => array(), 'wp_core' => array(), 'external' => array());

        foreach ($data['rows_assets'] as $locationMain => $values) {
            $rowsAssets[$locationMain] = $values;
        }

        foreach ($rowsAssets as $locationMain => $values) {
            ksort($values);
            $totalLocationAssets  = count($values);
            $hideLocationMainArea = ($locationMain === 'uploads' && $totalLocationAssets === 0);
            $hideListOfAssetsOnly = ($locationMain === 'wp_core' && $data['plugin_settings']['hide_core_files']);

	        $contractExpandAllAssetsHtml = <<<HTML
<div class="wpacu-area-toggle-all-assets wpacu-right">
    <a class="wpacu-area-contract-all-assets wpacu_area_handles_row_expand_contract"
       data-wpacu-area="{$locationMain}" href="#">Contract</a>
    |
    <a class="wpacu-area-expand-all-assets wpacu_area_handles_row_expand_contract"
       data-wpacu-area="{$locationMain}" href="#">Expand</a>
    All Assets
</div>
HTML;
            ob_start();
            ?>
            <div <?php if ($hideLocationMainArea) {
                echo 'style="display: none;"';
            } ?> class="wpacu-assets-collapsible-wrap wpacu-by-location wpacu-<?php echo esc_attr($locationMain); ?>">
            <a class="wpacu-assets-collapsible <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-assets-collapsible-active<?php } ?>"
               href="#wpacu-assets-collapsible-content-<?php echo esc_attr($locationMain); ?>">
                <?php echo wp_kses($locationsText[$locationMain], array('span' => array('class' => array()))); ?> &#10141; Total files: {total_files_<?php echo esc_html($locationMain); ?>}
            </a>

            <div class="wpacu-assets-collapsible-content <?php if ($listAreaStatus !== 'contracted') { ?>wpacu-open<?php } ?>">
            <?php if ($locationMain === 'external') { ?>
                <div class="wpacu-assets-note wpacu-with-toggle-all-assets"><strong>Note:</strong> External .css and .js assets are considered
                    those who are hosted on a different domain (e.g. Google Font API, assets loaded from external
                    CDNs) and the ones outside the WordPress "plugins" (usually /wp-content/plugins/), "themes"
                    (usually /wp-content/themes/) and "uploads" (usually /wp-content/uploads/) directories.</div>
	            <?php if (count($values) > 0) { echo $contractExpandAllAssetsHtml; } ?>
            <?php
                // WP Core CSS/JS list is visible
            } elseif ($locationMain === 'wp_core' && ! $data['plugin_settings']['hide_core_files']) { ?>
                <div class="wpacu-assets-note wpacu-with-toggle-all-assets"><span style="color: red;" class="dashicons dashicons-warning"></span> <strong>Warning:</strong> Please be careful when doing any changes to the
                    following core assets as they can break the functionality of the front-end website. If you're
                    not sure about unloading any asset, just leave it loaded.</div>
                <?php if (count($values) > 0) { echo $contractExpandAllAssetsHtml; } ?>
            <?php
                // WP Core CSS/JS list is hidden
            } elseif ($locationMain === 'wp_core' && $data['plugin_settings']['hide_core_files']) {
                ?>
                <div class="wpacu-assets-note"><strong>Note:</strong> By default, <?php echo WPACU_PLUGIN_TITLE; ?> does not show the list of CSS/JS loaded from the WordPress core. Usually, WordPress core files are loaded for a reason and this setting was applied to prevent accidental unload of files that could be needed (e.g. jQuery library, Underscore library etc.).</div>
                <div class="wpacu-assets-note"><span class="dashicons dashicons-info"></span> If you believe that you do not need some loaded core files (e.g. WordPress Gutenberg styling - Handle: 'wp-block-library') and you want to manage the files loaded from <em>/wp-includes/</em>, you can go to the plugin's <strong>"Settings"</strong>, click on the <strong>"Plugin Usage Preferences"</strong> tab, scroll to <strong>"Hide WordPress Core Files From The Assets List?"</strong> and make sure the option <strong>is turned off</strong>.</div>
                <?php
            } elseif ($locationMain === 'uploads') { ?>
                <div class="wpacu-assets-note" style="padding: 15px 15px 0 0;"><strong>Note:</strong> These are the
                    CSS/JS files load from the /wp-content/uploads/ WordPress directory. They were copied there by
                    other plugins or developers working on the website. In case the file was detected to be
                    generated by a specific plugin through various verification patterns (e.g. for plugins such as
                    Elementor, Oxygen Builder etc.), then it will be not listed here, but in the "From Plugins (.css
                    &amp; .js)" area for the detected plugin. This is to have all the files related to a plugin
                    organised in one place.</div>
	            <?php if (count($values) > 0) { echo $contractExpandAllAssetsHtml; } ?>
                <?php
            }
            ?>

                <?php
                $locationRowCount = 0;
                $totalLocationAssets = count($values);

                // Total files from all the plugins
                $totalFilesArray[$locationMain] = 0;

                // Default value (not contracted)
                $pluginListContracted = false;

                if ($totalLocationAssets > 0) {
                    $locI = 1;

                    // Going through each plugin / theme, etc.
                    foreach ( $values as $locationChild => $values2 ) {
                        ksort($values2);

                        if ($locationMain === 'plugins') {
                            $totalPluginAssets = $totalBulkUnloadedAssetsPerPlugin = 0;
                        }

                        $assetRowsOutput = '';

                        // Going through each asset from the plugin/theme
                        foreach ( $values2 as $assetRows ) {
                            foreach ( $assetRows as $assetRow ) {
                                $assetRowsOutput .= $assetRow . "\n";

                                if ($locationMain === 'plugins' && strpos($assetRow, 'wpacu_this_asset_row_area_is_hidden') === false) {
                                    if (strpos($assetRow, 'wpacu_is_bulk_unloaded') !== false) {
                                        $totalBulkUnloadedAssetsPerPlugin++;
                                    }

                                    $totalPluginAssets++;
                                }

                                $totalFilesArray[$locationMain]++;
                            }
                        }

                        if ( $locationChild !== 'none' ) {
                            if ( $locationMain === 'plugins' ) {
                                $locationChildText = Info::getPluginInfo( $locationChild, $allPlugins, $allActivePluginsIcons );

                                $isLastPluginAsset    = ( count( $values ) - 1 ) === $locationRowCount;
                                $pluginListContracted = ( $locationMain === 'plugins' && $pluginsAreaStatus === 'contracted' );

                                // Show it if there is at least one available "Unload on this page"
                                $showUnloadOnThisPageCheckUncheckAll = $totalPluginAssets !== $totalBulkUnloadedAssetsPerPlugin;

                                // Show it if all the assets from the plugin are bulk unloaded
                                $showLoadItOnThisPageCheckUncheckAll = $totalBulkUnloadedAssetsPerPlugin === $totalPluginAssets;
                            } elseif ( $locationMain === 'themes' ) {
                                $locationChildThemeArray = Info::getThemeInfo( $locationChild, $allThemes );
                                $locationChildText = $locationChildThemeArray['output'];
                            } else {
                                $locationChildText = $locationChild;
                            }

                            $extraClassesToAppend = '';

                            if ( $locationMain === 'plugins' && $isLastPluginAsset ) {
                                $extraClassesToAppend .= ' wpacu-area-last ';
                            }

                            if ($locI === 1) {
                                $extraClassesToAppend .= ' wpacu-location-child-area-first ';
                            }

                            // PLUGIN LIST: VIEW THEIR ASSETS
                            // EXPANDED (DEFAULT)
                            if ( $locationMain === 'plugins' ) {
                                if ( $pluginListContracted ) {
                                    // CONTRACTED (+ -)
                                    ?>
                                    <a href="#"
                                       class="wpacu-plugin-contracted-wrap-link wpacu-pro wpacu-link-closed <?php if ( ( count( $values ) - 1 ) === $locationRowCount ) { echo 'wpacu-last-wrap-link'; } ?>">
                                        <div class="wpacu-plugin-title-contracted wpacu-area-contracted">
                                            <?php echo wp_kses($locationChildText, array('div' => array('class' => array(), 'style' => array()), 'span' => array('class' => array()))); ?> <span style="font-weight: 200;">/</span> <span style="font-weight: 400;"><?php echo (int)$totalPluginAssets; ?></span> file<?php echo ($totalPluginAssets > 1) ? 's' : ''; ?>
                                        </div>
                                    </a>
                                    <?php
                                } else { ?>
                                    <div data-wpacu-plugin="<?php echo esc_attr($locationChild); ?>"
                                         data-wpacu-area="<?php echo esc_attr($locationChild); ?>_plugin"
                                         class="wpacu-location-child-area wpacu-area-expanded <?php echo esc_attr($extraClassesToAppend); ?>">
                                        <div class="wpacu-area-title">
	                                        <?php echo wp_kses($locationChildText, array('div' => array('class' => array(), 'style' => array()), 'span' => array('class' => array()))); ?> <span style="font-weight: 200;">/</span> <span style="font-weight: 400;"><?php echo (int)$totalPluginAssets; ?></span> file<?php echo ($totalPluginAssets > 1) ? 's' : ''; ?>
                                            <?php
                                            include __DIR__ . '/_view-by-location/_plugin-list-expanded-actions.php';
                                            ?>
                                        </div>
                                        <div class="wpacu-area-toggle-all-assets">
                                            <a class="wpacu-area-contract-all-assets wpacu_area_handles_row_expand_contract"
                                               data-wpacu-area="<?php echo esc_html($locationChild); ?>_plugin" href="#">Contract</a>
                                            |
                                            <a class="wpacu-area-expand-all-assets wpacu_area_handles_row_expand_contract"
                                               data-wpacu-area="<?php echo esc_html($locationChild); ?>_plugin" href="#">Expand</a>
                                            All Assets
                                        </div>
                                    </div>
                                <?php }
                            } elseif ( $locationMain === 'themes' ) {
                                ?>
                                <div data-wpacu-area="<?php echo esc_attr($locationChild); ?>_theme"
                                     class="wpacu-location-child-area wpacu-area-expanded <?php echo esc_attr($extraClassesToAppend); ?>">
                                    <div class="wpacu-area-title <?php if ($locationChildThemeArray['has_icon'] === true) { echo 'wpacu-theme-has-icon'; } ?>"><?php echo MiscAdmin::stripIrrelevantHtmlTags($locationChildText); ?></div>
                                    <div class="wpacu-area-toggle-all-assets">
                                        <a class="wpacu-area-contract-all-assets wpacu_area_handles_row_expand_contract"
                                           data-wpacu-area="<?php echo esc_html($locationChild); ?>_theme" href="#">Contract</a>
                                        |
                                        <a class="wpacu-area-expand-all-assets wpacu_area_handles_row_expand_contract"
                                           data-wpacu-area="<?php echo esc_html($locationChild); ?>_theme" href="#">Expand</a>
                                        All Assets
                                    </div>
                                </div>
                                <?php
                            } else { // WordPress Core, Uploads, 3rd Party, etc.
                                ?>
                                <div data-wpacu-area="<?php echo esc_attr($locationChild); ?>"
                                     class="wpacu-location-child-area wpacu-area-expanded <?php echo esc_attr($extraClassesToAppend); ?>">
                                    <div class="wpacu-area-title"><?php echo wp_kses($locationChildText, array('div' => array('class' => array(), 'style' => array()), 'span' => array('class' => array()))); ?></div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <div class="wpacu-assets-table-list-wrap <?php if ( $locationMain === 'plugins' ) { echo ' wpacu-area-assets-wrap '; }
                            if ( $pluginListContracted ) {
                                echo ' wpacu-area-closed ';

                                if (isset($isLastPluginAsset) && $isLastPluginAsset) {
                                    echo ' wpacu-area-assets-last ';
                                }
                            } ?>">
                            <?php
                            // CONTRACTED (+ -)
                            if ( $locationMain === 'plugins' && $pluginListContracted ) {
                                include __DIR__.'/_view-by-location/_plugin-list-contracted-actions.php';
                            }
                            ?>
                            <table <?php
                                   if ( $locationMain === 'plugins' ) { echo ' data-wpacu-plugin="' . esc_attr($locationChild) . '" data-wpacu-area="' . esc_attr($locationChild) . '_plugin" '; }
                                   if ( $locationMain === 'themes' ) { echo ' data-wpacu-area="' . esc_attr($locationChild) . '_theme" '; }
                                   if ( in_array($locationMain, array('uploads', 'wp_core', 'external') ) ) { echo ' data-wpacu-area="' . esc_attr($locationMain) . '" '; }
                                   ?>
                                   class="wpacu_list_table wpacu_list_by_location wpacu_widefat wpacu_striped">
                                <tbody>
                                    <?php
                                    if ( $locationMain === 'plugins' ) {
                                        do_action('wpacu_assets_plugin_notice_table_row', $locationChild);
                                    }

                                    echo MiscAdmin::stripIrrelevantHtmlTags($assetRowsOutput);
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        $locationRowCount ++;
                    }
                } else {
                    // There are no loaded CSS/JS
                    $showOxygenMsg = $locationMain === 'themes' && in_array('oxygen/functions.php', Misc::getActivePlugins());

                    if ($showOxygenMsg) {
                    ?>
                        <div style="padding: 12px 0;">
                            <img style="height: 30px; vertical-align: bottom;" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSIzODFweCIgaGVpZ2h0PSIzODVweCIgdmlld0JveD0iMCAwIDM4MSAzODUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+VW50aXRsZWQgMzwvdGl0bGU+ICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPiAgICA8ZGVmcz4gICAgICAgIDxwb2x5Z29uIGlkPSJwYXRoLTEiIHBvaW50cz0iMC4wNiAzODQuOTQgMzgwLjgwNSAzODQuOTQgMzgwLjgwNSAwLjYyOCAwLjA2IDAuNjI4Ij48L3BvbHlnb24+ICAgIDwvZGVmcz4gICAgPGcgaWQ9IlBhZ2UtMSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9IiNhMGE1YWEiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgICAgICA8ZyBpZD0iT3h5Z2VuLUljb24tQ01ZSyI+ICAgICAgICAgICAgPG1hc2sgaWQ9Im1hc2stMiIgZmlsbD0iI2EwYTVhYSI+ICAgICAgICAgICAgICAgIDx1c2UgeGxpbms6aHJlZj0iI3BhdGgtMSI+PC91c2U+ICAgICAgICAgICAgPC9tYXNrPiAgICAgICAgICAgIDxnIGlkPSJDbGlwLTIiPjwvZz4gICAgICAgICAgICA8cGF0aCBkPSJNMjk3LjUwOCwzNDkuNzQ4IEMyNzUuNDQzLDM0OS43NDggMjU3LjU1NiwzMzEuODYgMjU3LjU1NiwzMDkuNzk2IEMyNTcuNTU2LDI4Ny43MzEgMjc1LjQ0MywyNjkuODQ0IDI5Ny41MDgsMjY5Ljg0NCBDMzE5LjU3MywyNjkuODQ0IDMzNy40NiwyODcuNzMxIDMzNy40NiwzMDkuNzk2IEMzMzcuNDYsMzMxLjg2IDMxOS41NzMsMzQ5Ljc0OCAyOTcuNTA4LDM0OS43NDggTDI5Ny41MDgsMzQ5Ljc0OCBaIE0yMjIuMzA0LDMwOS43OTYgQzIyMi4zMDQsMzEyLjAzOSAyMjIuNDQ3LDMxNC4yNDcgMjIyLjYzOSwzMTYuNDQxIEMyMTIuMzMsMzE5LjA5MiAyMDEuNTI4LDMyMC41MDUgMTkwLjQwMywzMjAuNTA1IEMxMTkuMDEsMzIwLjUwNSA2MC45MjksMjYyLjQyMyA2MC45MjksMTkxLjAzMSBDNjAuOTI5LDExOS42MzggMTE5LjAxLDYxLjU1NyAxOTAuNDAzLDYxLjU1NyBDMjYxLjc5NCw2MS41NTcgMzE5Ljg3NywxMTkuNjM4IDMxOS44NzcsMTkxLjAzMSBDMzE5Ljg3NywyMDYuODMzIDMxNy4wMiwyMjEuOTc4IDMxMS44MTUsMjM1Ljk5IEMzMDcuMTc5LDIzNS4wOTcgMzAyLjQwNCwyMzQuNTkyIDI5Ny41MDgsMjM0LjU5MiBDMjU1Ljk3NCwyMzQuNTkyIDIyMi4zMDQsMjY4LjI2MiAyMjIuMzA0LDMwOS43OTYgTDIyMi4zMDQsMzA5Ljc5NiBaIE0zODAuODA1LDE5MS4wMzEgQzM4MC44MDUsODYuMDQyIDI5NS4zOTIsMC42MjggMTkwLjQwMywwLjYyOCBDODUuNDE0LDAuNjI4IDAsODYuMDQyIDAsMTkxLjAzMSBDMCwyOTYuMDIgODUuNDE0LDM4MS40MzMgMTkwLjQwMywzODEuNDMzIEMyMTIuNDk4LDM4MS40MzMgMjMzLjcwOCwzNzcuNjA5IDI1My40NTYsMzcwLjY1NyBDMjY1Ljg0NSwzNzkuNjQxIDI4MS4wMzQsMzg1IDI5Ny41MDgsMzg1IEMzMzkuMDQyLDM4NSAzNzIuNzEyLDM1MS4zMyAzNzIuNzEyLDMwOS43OTYgQzM3Mi43MTIsMjk2LjA5MiAzNjguOTg4LDI4My4yODMgMzYyLjU4NCwyNzIuMjE5IEMzNzQuMjUxLDI0Ny41NzUgMzgwLjgwNSwyMjAuMDU4IDM4MC44MDUsMTkxLjAzMSBMMzgwLjgwNSwxOTEuMDMxIFoiIGlkPSJGaWxsLTEiIGZpbGw9IiNhMGE1YWEiIG1hc2s9InVybCgjbWFzay0yKSI+PC9wYXRoPiAgICAgICAgPC9nPiAgICA8L2c+PC9zdmc+" alt="" />
                            &nbsp;You're using <a href="<?php echo esc_url(admin_url('admin.php?page=ct_dashboard_page')); ?>" target="_blank"><span style="font-weight: 600; color: #6036ca;">Oxygen</span></a> to design your site, which disables the WordPress theme system. Thus, no assets related to the theme are loaded.
                        </div>
                    <?php } else { ?>
                        <div style="padding: 0 0 16px 16px;"><?php _e('There are no CSS/JS loaded from this location.', 'wp-asset-clean-up'); ?></div>
                    <?php } ?>
                    <?php
                }
                ?>
                </div>
            </div>
            <?php
            $locationMainOutput = ob_get_clean();
            $locationMainOutput = str_replace(
                '{total_files_'.$locationMain.'}',
                $totalFilesArray[$locationMain],
                $locationMainOutput
            );

            echo MiscAdmin::stripIrrelevantHtmlTags($locationMainOutput);
        }
    }
}
/*
* -------------------------
* [END] BY EACH LOCATION
* -------------------------
*/

include_once __DIR__ . '/_view-common-footer.php';
