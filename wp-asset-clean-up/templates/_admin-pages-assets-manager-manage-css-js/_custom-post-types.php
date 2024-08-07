<?php
/*
 * No direct access to this file
 */
if (! isset($data)) {
    exit;
}
?>
<div style="margin: 25px 0 0;">
	<?php
	$data['post_id'] = (isset($_GET['wpacu_post_id']) && $_GET['wpacu_post_id']) ? (int)$_GET['wpacu_post_id'] : false;
	?>
    <p>Popular examples: 'product' created by WooCommerce, 'download' created by Easy Digital Downloads etc. &#10230; <a target="_blank" href="https://wordpress.org/support/article/post-types/#custom-post-types"><?php _e('read more', 'wp-asset-clean-up'); ?></a></p>
    <?php
    $data['dashboard_edit_not_allowed'] = false;

    require_once __DIR__ . '/_common/_is-dashboard-edit-allowed.php';

    if ($data['dashboard_edit_not_allowed']) {
	    return; // stop here as the message about the restricted access has been printed
    }

    if ($data['post_id']) {
	    // There's a POST ID requested in the URL / Show the assets
	    $data['post_type'] = get_post_type($data['post_id']);
	    do_action('wpacu_admin_notices');
	    require_once __DIR__ . '/_singular-page.php';
    } else {
        // There's no POST ID requested
        $data['post_type'] = '';
        require_once __DIR__ . '/_singular-page-search-form.php';
    }
    ?>
</div>