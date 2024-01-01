<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-script-single-row.php
*/

if ( ! isset($data, $ver) ) {
	exit; // no direct access
}

if (isset($data['row']['obj']->src, $data['row']['obj']->srcHref) && trim($data['row']['obj']->src) !== '' && $data['row']['obj']->srcHref) {
	$assetHandleHasSrc = $isExternalSrc = true;

	if (\WpAssetCleanUp\Misc::getLocalSrc($data['row']['obj']->src)
	    || strpos($data['row']['obj']->src, '/?') !== false // Dynamic Local URL
	    || strpos(str_replace(site_url(), '', $data['row']['obj']->src), '?') === 0 // Starts with ? right after the site url (it's a local URL)
	) {
		$isExternalSrc = false;
	}

	$srcHref = $data['row']['obj']->srcHref;

	// If the source starts with ../ mark it as external to be checked via the AJAX call (special case)
	if (strpos($srcHref, '../') === 0) {
		$currentPageUrl = \WpAssetCleanUp\Misc::getCurrentPageUrl();
		$srcHref = trim($currentPageUrl, '/') . '/'. $data['row']['obj']->srcHref;
		$isExternalSrc = true; // simulation
	}

	$relSrc = str_replace(site_url(), '', $data['row']['obj']->src);

	if (isset($data['row']['obj']->baseUrl)) {
		$relSrc = str_replace($data['row']['obj']->baseUrl, '/', $relSrc);
	}

	if ($isExternalSrc) {
		$verToAppend = ''; // no need for any "ver"
	} else {
		$appendAfterSrcHref = ( strpos( $srcHref, '?' ) === false ) ? '?' : '&';

		if ( isset( $data['row']['obj']->ver ) && $data['row']['obj']->ver ) {
			$verToAppend = $appendAfterSrcHref .
			               (is_array( $data['row']['obj']->ver )
				               ? http_build_query( array( 'ver' => $data['row']['obj']->ver ) )
				               : 'ver=' . $ver);
		} else {
			global $wp_version;
			$verToAppend = $appendAfterSrcHref . 'ver=' . $wp_version;
		}
	}

	$isJsPreload = (isset($data['preloads']['scripts'][$data['row']['obj']->handle]) && $data['preloads']['scripts'][$data['row']['obj']->handle])
		? $data['preloads']['scripts'][$data['row']['obj']->handle]
		: false;

	if ($isJsPreload) {
		$data['row']['obj']->preload_status = 'preloaded';
		$data['row']['at_least_one_rule_set'] = true;
	}
	?>
	<div class="wpacu-source-row">
		<?php
		if (isset($data['row']['obj']->src_origin, $data['row']['obj']->ver_origin) && $data['row']['obj']->src_origin) {
			$sourceText = esc_html__('Source (updated):', 'wp-asset-clean-up');
			$messageToAlert = sprintf(
				esc_html__('On this page, the `%s` JavaScript handle had its source updated via `%s` filter tag.' ."\n\n". 'Original Source: %s (version: %s)'),
				$data['row']['obj']->handle,
				'wpacu_'.$data['row']['obj']->handle.'_js_handle_data',
				$data['row']['obj']->src_origin,
				($data['row']['obj']->ver_origin ?: esc_html__('null', 'wp-asset-clean-up'))
			);
			?>
            <a style="text-decoration: none; display: inline-block;"
               href="#"
               class="wpacu-filter-handle"
               data-wpacu-filter-handle-message="<?php echo esc_attr($messageToAlert); ?>"
            ><span class="dashicons dashicons-filter"></span></a>
		<?php } else {
			$sourceText = esc_html__('Source:', 'wp-asset-clean-up'); // as it is, no replacement
		}
		echo esc_html($sourceText); ?>
        <a target="_blank"  style="color: green;" <?php if ($isExternalSrc) { ?> data-wpacu-external-source="<?php echo esc_attr($srcHref . $verToAppend); ?>" <?php } ?> href="<?php echo esc_attr($srcHref . $verToAppend); ?>"><?php echo wp_kses($relSrc, array('u' => array('style' => array()))); ?></a> <?php if ($isExternalSrc) { ?><span data-wpacu-external-source-status></span><?php } ?>
		<div class="wpacu_hide_if_handle_row_contracted">
            &nbsp;&#10230;&nbsp;
             Preload (if kept loaded)?
            &nbsp;<select style="display: inline-block; width: auto; <?php if ($isJsPreload) {
                echo 'background: #f2faf2 url(\'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E\') no-repeat right 5px top 55%; padding-right: 30px; color: black;';
            } ?>"
                     data-wpacu-input="preload"
                     name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[scripts][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][preload]">
                <option value="">No (default)</option>
                <option <?php if ($isJsPreload) { ?>selected="selected"<?php } ?> value="basic">Yes, basic</option>
            </select>
            <small>* applies site-wide</small> <small><a style="text-decoration: none; color: inherit;" target="_blank" href="https://assetcleanup.com/docs/?p=202"><span class="dashicons dashicons-editor-help"></span></a></small>
        </div>
	</div>
	<?php
} else {
    $hasNoSrc = true;
}
