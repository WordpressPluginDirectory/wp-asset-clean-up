<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-(script|style)-single-row.php
*/

if (! isset($data)) {
	exit; // no direct access
}

$assetType  = $data['row']['asset_type'];
$assetTypeS = substr($data['row']['asset_type'], 0, -1); // "styles" to "style" & "scripts" to "script"

$isGroupUnloaded = $data['row']['is_group_unloaded'];
// [wpacu_pro]
$isMarkedForPostTypeViaTaxUnload = isset($data['handle_unload_via_tax'][$assetType][$data['row']['obj']->handle ]['enable'], $data['handle_unload_via_tax'][$assetType][$data['row']['obj']->handle]['values'])
    && $data['handle_unload_via_tax'][$assetType][$data['row']['obj']->handle ]['enable'] && ! empty($data['handle_unload_via_tax'][$assetType][$data['row']['obj']->handle]['values']);
$isMarkedForRegExUnload = isset($data['handle_unload_regex'][$assetType][ $data['row']['obj']->handle ]['enable']) ? $data['handle_unload_regex'][$assetType][ $data['row']['obj']->handle ]['enable'] : false;
// [/wpacu_pro]
$anyUnloadRuleSet = ($isGroupUnloaded || $isMarkedForRegExUnload || $isMarkedForPostTypeViaTaxUnload || $data['row']['checked']);

if ($anyUnloadRuleSet || $data['row']['is_load_exception_per_page']) {
    $data['row']['at_least_one_rule_set'] = true;
}

$loadExceptionOptionsAreaCss = '';

if ($data['row']['global_unloaded']) {
    // Move it to the right side or extend it to avoid so much empty space and a higher DIV
	$loadExceptionOptionsAreaCss = 'display: contents;';
}
?>
<div class="wpacu_exception_options_area_load_exception <?php if (! $anyUnloadRuleSet) { echo 'wpacu_hide'; } ?>" style="<?php echo $loadExceptionOptionsAreaCss; ?>">
    <div data-<?php echo $assetTypeS; ?>-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
         class="wpacu_exception_options_area_wrap">
        <fieldset>
            <legend>Make an exception from any unload rule &amp; <strong>always load it</strong>:</legend>
		    <ul class="wpacu_area_two wpacu_asset_options wpacu_exception_options_area">
                <li id="wpacu_load_it_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  id="wpacu_load_it_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  class="wpacu_load_it_option_one wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                  type="checkbox"
                            <?php if ($data['row']['is_load_exception_per_page']) { ?> checked="checked" <?php } ?>
                                  name="wpacu_<?php echo $assetType; ?>_load_it[]"
                                  value="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>" />
                        <span>On this page</span></label>
                </li>
                <?php
                if ($data['bulk_unloaded_type'] === 'post_type') {
                    // Only show it on edit post/page/custom post type
                    switch ($data['post_type']) {
                        case 'product':
                            $loadBulkText = __('On all WooCommerce "Product" pages', 'wp-asset-clean-up');
                            break;
                        case 'download':
                            $loadBulkText = __('On all Easy Digital Downloads "Download" pages', 'wp-asset-clean-up');
                            break;
                        default:
                            $loadBulkText = sprintf(__('On all pages of "<strong>%s</strong>" post type', 'wp-asset-clean-up'), $data['post_type']);
                    }
                    ?>
                    <li id="wpacu_load_it_post_type_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      id="wpacu_<?php echo $assetTypeS; ?>_load_it_post_type_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      class="wpacu_load_it_option_post_type wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                      type="checkbox"
                                <?php if ($data['row']['is_load_exception_post_type']) { ?> checked="checked" <?php } ?>
                                      name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][load_it_post_type]"
                                      value="1"/>
                            <span><?php echo wp_kses($loadBulkText, array('strong' => array())); ?></span></label>
                    </li>
                    <?php
                    if (isset($data['post_type']) && $data['post_type'] !== 'attachment' && ! empty($data['post_type_has_tax_assoc'])) {
                        include '_asset-single-row-load-exceptions-taxonomy.php';
                    }
                }
                ?>
                <li>
                    <label for="wpacu_load_it_regex_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                id="wpacu_load_it_regex_option_<?php echo $assetTypeS; ?>_<?php echo $data['row']['obj']->handle; ?>"
                                class="wpacu_load_it_option_two wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception wpacu_lite_locked"
                                type="checkbox"
                                disabled="disabled"
                                value="1"/>
                        Load it for URLs with request URI matching this RegEx(es): <a class="go-pro-link-no-style"
                                                                                      href="<?php echo apply_filters('wpacu_go_pro_affiliate_link', WPACU_PLUGIN_GO_PRO_URL.'?utm_source=manage_asset&utm_medium=load_exception_'.$assetTypeS.'_post_type_via_tax'); ?>"><span
                                    class="wpacu-tooltip wpacu-larger" style="left: -26px;"><?php echo str_replace('the premium', 'the<br />premium', wp_kses(__('This feature is available in the premium version of the plugin.',
				                    'wp-asset-clean-up' ), array('br' => array()))); ?><br/> <?php _e( 'Click here to upgrade to Pro',
				                    'wp-asset-clean-up' ); ?>!</span><img width="20" height="20"
                                                                          src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/icons/icon-lock.svg"
                                                                          valign="top" alt=""/></a> <a
                                style="text-decoration: none; color: inherit;" target="_blank"
                                href="https://assetcleanup.com/docs/?p=21#wpacu-method-2"><span
                                    class="dashicons dashicons-editor-help"></span></a></label>
                </li>
                <?php
                $isLoadItLoggedIn = in_array($data['row']['obj']->handle, $data['handle_load_logged_in'][$assetType]);
                if ($isLoadItLoggedIn) { $data['row']['at_least_one_rule_set'] = true; }
                ?>
                <li id="wpacu_load_it_user_logged_in_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  id="wpacu_load_it_user_logged_in_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  class="wpacu_load_it_option_three wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                  type="checkbox"
                            <?php if ($isLoadItLoggedIn) { ?> checked="checked" <?php } ?>
                                  name="wpacu_load_it_logged_in[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                  value="1"/>
                        <span><?php esc_html_e('If the user is logged-in', 'wp-asset-clean-up'); ?></span></label>
                </li>
		    </ul>
            <div class="wpacu_clearfix"></div>
        </fieldset>
	</div>
</div>
