<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}

include_once __DIR__ . '/_top-area.php';
?>
<div class="wpacu-wrap wpacu-get-help-wrap">
    <!-- [wpacu_lite] -->
    <p>Go to: &nbsp; <span class="dashicons dashicons-welcome-learn-more"></span> <a href="<?php echo esc_url(admin_url('admin.php?page=wpassetcleanup_getting_started')); ?>">Getting Started</a> &nbsp;&nbsp; <span class="dashicons dashicons-text-page"></span> <a target="_blank" href="https://assetcleanup.com/docs/?utm_source=plugin_help_page_pro">Documentation</a></p>
    <!-- [/wpacu_lite] -->

    <p>If you believe <?php echo WPACU_PLUGIN_TITLE; ?> has a bug (e.g. you're getting JavaScript or PHP errors generated by <?php echo WPACU_PLUGIN_TITLE; ?> or the selected scripts are not unloading etc.) that needs to be fixed, then <a target="_blank" href="https://wordpress.org/support/plugin/wp-asset-clean-up/?utm_source=wpacu_lite_plugin_help_page">please report it by opening a support ticket</a> on the forum which is dedicated for assistance related to the Lite version. Note that the support is only for reporting bugs &amp; any incompatibility with themes/plugins and not for custom work request.</p>

    <hr />

    <div class="help-content-wrap">
        <div class="help-content upgrade-help">
            <h2 class="content-title"><?php _e('I have purchased the Pro version. How to do the upgrade?', 'wp-asset-clean-up'); ?></h2>
            <ul>
                <li><strong>1)</strong> <?php
                    echo sprintf(
                        __('Go to %s"Plugins" -&gt; "Add New" -&gt; "Upload Plugin"%s / You will notice an upload form and an "Install Now" submit button. Download the ZIP file you received in your purchase email receipt (<em>example: wp-asset-clean-up-pro-v1.0.8.5.zip</em>), attach it to the form and install the new upgraded plugin.', 'wp-asset-clean-up'),
                        '<a style="text-decoration: none;" target="_blank" href="'.admin_url('plugin-install.php?tab=upload').'">', '</a>'
                    ); ?>
                    <div class="wpacu-image-area">
                        <img src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/images/wpacu-upgrade-steps/wpacu-upgrade-step-1-choose-file.jpg" alt="" />
                    </div>
                    <hr />
                </li>
                <li><strong>2)</strong> <?php _e('Finally, <strong>activate</strong> Asset CleanUp Pro! That\'s it', 'wp-asset-clean-up'); ?> <span class="dashicons dashicons-smiley"></span>
                    <div class="wpacu-image-area activate-plugin">
                        <img src="<?php echo esc_url(WPACU_PLUGIN_URL); ?>/assets/images/wpacu-upgrade-steps/wpacu-upgrade-step-2-activate-plugin.jpg" alt="" />
                    </div>
                </li>
            </ul>

            <p><strong><?php _e('Reminder', 'wp-asset-clean-up'); ?>:</strong> <?php _e('Once the Pro version is active, make sure to get the license key from the purchase email receipt and activate it in "License" in order to be eligible for plugin updates from the Dashboard.', 'wp-asset-clean-up'); ?></p>
        </div>

        <div class="help-content tech-help second">
            <h2 class="content-title"><?php _e('In case you are stuck, need assistance or just want to save time you would spend for your website optimization, we can help you!', 'wp-asset-clean-up'); ?></h2>

            <p>If the following scenarios apply to you &amp; don't have a developer available to provide what you need, then me or any of my colleagues from <a href="https://www.gabelivan.com/visit/codeable/apply/from-lite/">Codeable</a>, would be able to assist you:</p>

            <ul class="hire-reasons">
                <li><span class="dashicons dashicons-yes"></span> You have many CSS and JavaScript files loaded in a page and you're not sure which ones you could prevent from loading, worrying that something could be messed up. A Codeable expert could analyse your pages and give the advices needed.</li>
                <li><span class="dashicons dashicons-yes"></span> You want to improve the speed of your website and you need help getting a faster loading page and a better Google PageSpeed score.</li>
                <li><span class="dashicons dashicons-yes"></span> You need help with a WordPress task and you're looking for a professional to help you with whatever you need.</li>
                <li><span class="dashicons dashicons-yes"></span> You are looking to fully optimize your website to get a page speed score as high as possible.</li>
            </ul>

            <p><strong>Getting started is easy:</strong></p>
            <ol class="getting-started">
                <li>Explain your website needs or problems</li>
                <li>The experts reach out to you with questions &amp; estimates.</li>
                <li>You choose the one you want to work with.</li>
            </ol>

            <div class="wpacu_clearfix"></div>
            <hr /><br />

            <div class="wpacu-btns">
                <a class="btn btn-success" href="https://www.gabelivan.com/visit/codeable/apply/from-lite/"><?php _e('Hire a Speed Optimization Expert', 'wp-asset-clean-up'); ?></a>
                &nbsp;&nbsp;
                <a class="btn btn-secondary" href="https://www.gabelivan.com/visit/codeable/apply/from-lite/"><?php _e('Find out more', 'wp-asset-clean-up'); ?></a>
            </div>
        </div>
    </div>
</div>