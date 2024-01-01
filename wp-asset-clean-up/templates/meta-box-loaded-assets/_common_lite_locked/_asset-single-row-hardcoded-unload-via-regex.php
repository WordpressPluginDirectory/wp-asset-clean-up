<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-(style|script)-single-row-hardcoded.php
*/
if (! isset($data)) {
	exit; // no direct access
}

$assetType  = $data['row']['asset_type'];
$assetTypeS = substr($data['row']['asset_type'], 0, -1); // "styles" to "style" & "scripts" to "script"
?>
<div class="wpacu_asset_options_wrap wpacu_unload_regex_area_wrap">
    <ul class="wpacu_asset_options">
        <li>
            <label class="wpacu-manage-hardcoded-assets-requires-pro-popup"
                   for="wpacu_unload_it_regex_option_<?php echo $assetTypeS; ?>_<?php echo esc_attr($data['row']['obj']->handle); ?>">
                <span style="color: #ccc;" class="wpacu-manage-hardcoded-assets-requires-pro-popup dashicons dashicons-lock"></span>
                <?php
                $assetTypeText = ($assetType === 'styles') ? 'CSS' : 'JS';
                echo sprintf(__('Unload %s for URLs with request URI matching the following RegEx(es)', 'wp-asset-clean-up'), $assetTypeText);
                ?>:
            </label>
            <a style="text-decoration: none; color: inherit; vertical-align: middle;" target="_blank"
               href="https://assetcleanup.com/docs/?p=313#wpacu-unload-by-regex"><span
                        class="dashicons dashicons-editor-help"></span></a>
        </li>
    </ul>
</div>