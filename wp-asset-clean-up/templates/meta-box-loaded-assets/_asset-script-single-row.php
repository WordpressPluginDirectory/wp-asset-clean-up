<?php
/*
 * The file is included from _asset-script-rows.php
*/
if (! isset($data)) {
	exit; // no direct access
}

$inlineCodeStatus = $data['plugin_settings']['assets_list_inline_code_status'];
$isCoreFile       = isset($data['row']['obj']->wp) && $data['row']['obj']->wp;
$hideCoreFiles    = $data['plugin_settings']['hide_core_files'];
$isGroupUnloaded  = $data['row']['is_group_unloaded'];

// Does it have "children"? - other JS file(s) depending on it
$childHandles     = isset($data['all_deps']['parent_to_child']['scripts'][$data['row']['obj']->handle]) ? $data['all_deps']['parent_to_child']['scripts'][$data['row']['obj']->handle] : array();
sort($childHandles);

$jqueryIconHtmlHandle  = '<img src="'.WPACU_PLUGIN_URL.'/assets/icons/handles/icon-jquery.png" style="max-width: 22px; max-height: 22px;" width="18" height="18" title="" alt="" />';
$jqueryIconHtmlDepends = '<img src="'.WPACU_PLUGIN_URL.'/assets/icons/handles/icon-jquery.png" style="max-width: 22px; max-height: 22px; vertical-align: text-top;" width="16" height="16" alt="" />';

// Unloaded site-wide
if ($data['row']['global_unloaded']) {
	$data['row']['class'] .= ' wpacu_is_global_unloaded';
}

// Unloaded site-wide OR on all posts, pages etc.
if ($isGroupUnloaded) {
	$data['row']['class'] .= ' wpacu_is_bulk_unloaded';
}

$rowIsContracted   = '';
$dashSign          = 'minus';
$dataRowStatusAttr = 'expanded';

if (isset($data['handle_rows_contracted']['scripts'][$data['row']['obj']->handle]) && $data['handle_rows_contracted']['scripts'][$data['row']['obj']->handle]) {
	$rowIsContracted   = 1;
	$dashSign          = 'plus';
	$dataRowStatusAttr = 'contracted';
}
?>
<tr data-script-handle-row="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
    id="wpacu_script_row_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
    class="wpacu_asset_row <?php echo esc_attr($data['row']['class']); ?>"
    style="<?php if ($isCoreFile && $hideCoreFiles) { echo 'display: none;'; } ?>">
	<td valign="top" style="position: relative;" data-wpacu-row-status="<?php echo esc_attr($dataRowStatusAttr); ?>">
        <!-- [reference field] -->
        <input type="hidden" name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[scripts][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]" value="" />
        <!-- [/reference field] -->
        <div class="wpacu_handle_row_expand_contract_area">
            <a data-wpacu-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
               data-wpacu-handle-for="script"
               class="wpacu_handle_row_expand_contract"
               href="#"><span class="dashicons dashicons-<?php echo esc_attr($dashSign); ?>"></span></a>
            <!-- -->
        </div>
	    <?php
        include '_asset-script-single-row/_handle.php';

	    $ver = $data['wp_version']; // default
	    if (isset($data['row']['obj']->ver) && $data['row']['obj']->ver) {
		    $ver = is_array($data['row']['obj']->ver) ? implode(', ', $data['row']['obj']->ver) : $data['row']['obj']->ver;
	    }

	    $data['row']['obj']->preload_status = 'not_preloaded'; // default

	    $assetHandleHasSrc = false;

        // Source, Preload area
        include '_asset-script-single-row/_source.php';

	    // Any tips?
	    if (isset($data['tips']['js'][$data['row']['obj']->handle]) && ($assetTip = $data['tips']['js'][$data['row']['obj']->handle])) {
            ?>
            <div class="tip"><strong>Tip:</strong> <?php echo esc_html($assetTip); ?></div>
		    <?php
	    }
	    ?>
		<div class="wpacu_handle_row_expanded_area <?php if ($rowIsContracted) { echo 'wpacu_hide'; } ?>">
	        <?php
	        $extraInfo = array();

		    include '_asset-script-single-row/_handle_deps.php';

	        $extraInfo[] = esc_html__('Version:', 'wp-asset-clean-up').' '.$ver;

	        include '_common/_asset-single-row-position.php';

	        if (isset($data['row']['obj']->src) && trim($data['row']['obj']->src)) {
		        $extraInfo[] = esc_html__('File Size:', 'wp-asset-clean-up') . ' <em>' . $data['row']['obj']->size . '</em>';
	        }

	        if (! empty($extraInfo)) {
	            $stylingDiv = 'margin: 10px 0';

	            if (isset($hasNoSrc) && $hasNoSrc) {
	                $stylingDiv = 'margin: 15px 0 10px;';
	            }

		        echo '<div style="'.$stylingDiv.'">'.implode(' &nbsp;/&nbsp; ', $extraInfo).'</div>';
	        }
	        ?>

	        <div class="wrap_bulk_unload_options">
	            <?php
	            // Unload on this page
	            include '_common/_asset-single-row-unload-per-page.php';

	            // Unload site-wide (everywhere)
	            include '_common/_asset-single-row-unload-site-wide.php';

	            // Unload on all pages of [post] post type (if applicable)
	            include '_common/_asset-single-row-unload-post-type.php';

	            // Unload on all pages where this [post] post type has a certain taxonomy set for it (e.g. a Tag or a Category) (if applicable)
                // There has to be at least a taxonomy created for this [post] post type in order to show this option
	            if (isset($data['post_type']) && $data['post_type'] !== 'attachment' && ! $data['row']['is_post_type_unloaded'] && ! empty($data['post_type_has_tax_assoc'])) {
	                include '_common/_asset-single-row-unload-post-type-taxonomy.php';
                }

	            // Unload via RegEx (if site-wide is not already chosen)
	            include '_common/_asset-single-row-unload-via-regex.php';

	            // If any bulk unload rule is set, show the load exceptions
	            include '_common/_asset-single-row-load-exceptions.php';
			    ?>
	            <div class="wpacu_clearfix"></div>
	        </div>

	        <?php
	        // Extra inline associated with the SCRIPT tag
	        include '_common/_asset-single-row-extra-inline.php';

	        // Async, Defer, Media Query Load (Pro features)
	        include '_asset-script-single-row/_loaded-rules.php';

	        // Handle Note
	        include '_common/_asset-single-row-notes.php';
	        ?>
		</div>
        <img style="display: none;" class="wpacu_ajax_loader" src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/icons/icon-ajax-loading-spinner.svg" alt="<?php echo esc_html__('Loading'); ?>..." />
	</td>
</tr>