<?php
/*
 * No direct access to this file
 */

use WpAssetCleanUp\Admin\MainAdmin;
use WpAssetCleanUp\MetaBoxes;

if (! isset($data)) {
    exit;
}

if ($data['is_list_fetchable']) {
    ?>
    <input type="hidden" id="wpacu_ajax_fetch_assets_list_dashboard_view" value="1" />
<?php
}
?>
<div id="wpacu_meta_box_content">
    <?php
    if ($data['is_list_fetchable']) {
        if ($data['fetch_assets_on_click']) {
            ?>
            <a style="margin: 10px 0; padding: 0 26px;" href="#" class="button button-secondary button-hero" id="wpacu_ajax_fetch_on_click_btn"><span style="font-size: 24px; vertical-align: middle;" class="dashicons dashicons-download"></span>&nbsp; Fetch CSS &amp; JavaScript Management List</a>
            <?php
        }
        ?>
        <div id="wpacu_fetching_assets_list_wrap" <?php if ($data['fetch_assets_on_click']) { echo 'style="display: none;"'; } ?>>
            <?php
            if ($data['dom_get_type'] === 'direct') {
	            ?>
                <div id="wpacu-list-step-default-status" style="display: none;"><img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" align="top" width="20" height="20" alt="" />&nbsp; Please wait...</div>
                <div id="wpacu-list-step-completed-status" style="display: none;"><span style="color: green;" class="dashicons dashicons-yes-alt"></span> Completed</div>
                <div>
                    <ul class="wpacu_meta_box_content_fetch_steps">
                        <li id="wpacu-fetch-list-step-1-wrap"><strong>Step 1</strong>: <?php echo sprintf(__('Fetch the assets from <strong>%s</strong>', 'wp-asset-clean-up'), $data['fetch_url']); ?>... <span id="wpacu-fetch-list-step-1-status"><img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" align="top" width="20" height="20" alt="" />&nbsp; Please wait...</span></li>
                        <li id="wpacu-fetch-list-step-2-wrap"><strong>Step 2</strong>: Build the list of the fetched assets and print it... <span id="wpacu-fetch-list-step-2-status"></span></li>
                    </ul>
                </div>
            <?php } else { ?>
                    <div style="margin: 18px 0;">
                        <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" align="top" width="20" height="20" alt="" />&nbsp;
                        <?php echo sprintf(__('Fetching the loaded scripts and styles for <strong>%s</strong>... Please wait...', 'wp-asset-clean-up'), $data['fetch_url']); ?>
                    </div>
            <?php } ?>

            <hr>
            <div style="margin-top: 20px;">
                    <strong>Is the fetching taking too long? Please do the following:</strong>
                    <ul style="margin-top: 8px; margin-left: 20px; padding: 0; list-style: disc;">
                        <li>Check your internet connection and the actual page that is being fetched to see if it loads completely.</li>
                        <li>If the targeted page loads fine and your internet connection is working fine, please try managing the assets in the front-end view by going to <em>"Settings" -&gt; "Plugin Usage Preferences" -&gt; "Manage in the Front-end"</em></li>
                    </ul>
            </div>
        </div>
        <?php
    } elseif ($data['status'] === 2) {
	    echo '<p>'.esc_html__('In order to manage the CSS/JS files here, you need to have "Manage in the Dashboard?" enabled within the plugin\'s settings ("Plugin Usage Preferences" tab).', 'wp-asset-clean-up').'</p>';
	    echo '<p style="margin-bottom: 0;">'.esc_html__('If you prefer to manage the assets within the front-end view and wish to hide this meta box, you can click on "Screen Options" at the top of this page and deselect "Asset CleanUp Pro: CSS &amp; JavaScript Manager".').'</p>';
    } elseif ($data['status'] === 3) {
        _e('The styles and scripts will be available for unload once this post/page is <strong>public</strong> and <strong>publish</strong>ed as the whole page needs to be scanned for all the loaded assets.', 'wp-asset-clean-up');
        ?>
        <p class="wpacu-warning" style="margin: 15px 0 0; padding: 10px; font-size: inherit;"><span class="dashicons dashicons-image-rotate" style="-webkit-transform: rotateY(180deg); transform: rotateY(180deg);"></span> &nbsp;<?php _e('If this post/page was meanwhile published (after you saw the above notice), just reload this edit page and you should see the list of CSS/JS files loaded in the page.', 'wp-asset-clean-up'); ?></p>
    <?php
    } elseif ($data['status'] === 4) {
        ?>
            <p style="margin-bottom: 0;">
                <span class="dashicons dashicons-info"></span>
                <?php
                _e('There are no CSS/JS to manage as the permalink for this attachment redirects to the attachment itself because <em>"Redirect attachment URLs to the attachment itself?"</em> is set to <em>"Yes"</em> in <em>"Search Appearance - Yoast SEO" - "Media"</em> tab).', 'wp-asset-clean-up');

                echo ' '.sprintf(
		                esc_html__('As a result, the "%s" side meta box is not shown as it is irrelevant in this situation.', 'wp-asset-clean-up'),
                    WPACU_PLUGIN_TITLE.': '.esc_html__('Options', 'wp-asset-clean-up')
                );
                ?>
            </p>
        <?php
    }

    if (in_array($data['status'], array(5, 6))) {
        $data['show_page_options'] = true;

	    $post = get_post($data['post_id']);

	    // Current Post Type
	    $data['post_type'] = $post->post_type;

	    $data['bulk_unloaded_type'] = 'post_type';
	    $data['is_bulk_unloadable'] = true;

	    $data = MainAdmin::instance()->setPageTemplate($data);

        $data['page_options'] = MetaBoxes::getPageOptions($data['post_id']);
        $data['page_options_with_assets_manager_no_load'] = true;

        include __DIR__.'/meta-box-restricted-page-load.php';
    }
    ?>
</div>
