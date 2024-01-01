<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-script-single-row.php
*/

if (! isset($data)) {
	exit; // no direct access
}

$isGroupUnloaded  = $data['row']['is_group_unloaded'];
$anyUnloadRuleSet = ($isGroupUnloaded || $data['row']['checked']);

if ($anyUnloadRuleSet) {
    $data['row']['at_least_one_rule_set'] = true;
}
?>
<div class="wpacu_exception_options_area_load_exception <?php if (! $anyUnloadRuleSet) { echo 'wpacu_hide'; } ?>">
	<div data-style-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
	     class="wpacu_exception_options_area_wrap">
        <fieldset>
            <legend>Make an exception from any unload rule &amp; <strong>always load it</strong>:</legend>

		<ul class="wpacu_area_two wpacu_asset_options wpacu_exception_options_area">
            <li id="wpacu_load_it_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                              id="wpacu_style_load_it_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                              class="wpacu_load_it_option_one wpacu_style wpacu_load_exception"
                              type="checkbox"
						<?php if ($data['row']['is_load_exception_per_page']) { ?> checked="checked" <?php } ?>
                              name="wpacu_styles_load_it[]"
                              value="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"/>
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
						$loadBulkText = sprintf(__('On All Pages of "<strong>%s</strong>" post type', 'wp-asset-clean-up'), $data['post_type']);
				}
				?>
                <li id="wpacu_load_it_post_type_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  id="wpacu_style_load_it_post_type_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  class="wpacu_load_it_option_post_type wpacu_style wpacu_load_exception"
                                  type="checkbox"
							<?php if ($data['row']['is_load_exception_post_type']) { ?> checked="checked" <?php } ?>
                                  name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[styles][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][load_it_post_type]"
                                  value="1"/>
                        <span><?php echo wp_kses($loadBulkText, array('strong' => array())); ?></span></label>
                </li>
				<?php
				if (isset($data['post_type']) && $data['post_type'] !== 'attachment' && ! empty($data['post_type_has_tax_assoc'])) {
					include dirname(__DIR__).'/_common/_asset-single-row-load-exceptions-taxonomy.php';
				}
			}
			?>
            <li>
                    <label for="wpacu_load_it_regex_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                               id="wpacu_load_it_regex_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           class="wpacu_load_it_option_two wpacu_style wpacu_load_exception wpacu_lite_locked"
                           type="checkbox"
                           disabled="disabled"
                           value="1"/>
                    Load it for URLs with request URI matching this RegEx(es): <a class="go-pro-link-no-style"
                                                                              href="<?php echo apply_filters('wpacu_go_pro_affiliate_link', WPACU_PLUGIN_GO_PRO_URL.'?utm_source=manage_asset&utm_medium=load_via_regex_make_exception'); ?>"><span
                                class="wpacu-tooltip wpacu-larger"><?php echo str_replace('the premium', 'the<br />premium', wp_kses(__('This feature is available in the premium version of the plugin.',
				                    'wp-asset-clean-up' ), array('br' => array()))); ?><br/> <?php _e( 'Click here to upgrade to Pro',
								'wp-asset-clean-up' ); ?>!</span><img width="20" height="20"
                                                                      src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/icons/icon-lock.svg"
                                                                      valign="top" alt=""/></a> <a
                            style="text-decoration: none; color: inherit;" target="_blank"
                            href="https://assetcleanup.com/docs/?p=21#wpacu-method-2"><span
                                class="dashicons dashicons-editor-help"></span></a></label>
            </li>
			<?php
			$isLoadItLoggedIn = in_array($data['row']['obj']->handle, $data['handle_load_logged_in']['styles']);

			if ($isLoadItLoggedIn) { $data['row']['at_least_one_rule_set'] = true; }
			?>
            <li id="wpacu_load_it_user_logged_in_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                <label>
                    <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           id="wpacu_load_it_user_logged_in_option_style_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           class="wpacu_load_it_option_three wpacu_style wpacu_load_exception"
                           type="checkbox"
						<?php if ($isLoadItLoggedIn) { ?> checked="checked" <?php } ?>
                           name="wpacu_load_it_logged_in[styles][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                           value="1"/>
                    <span>If the user is logged-in</span></label>
            </li>
		</ul>
		<div class="wpacu_clearfix"></div>
        </fieldset>
	</div>
</div>
