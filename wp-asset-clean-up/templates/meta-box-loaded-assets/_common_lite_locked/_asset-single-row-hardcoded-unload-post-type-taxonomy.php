<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-(script|style)-single-row.php
*/

if (! isset($data)) {
	exit; // no direct access
}

$assetType  = $data['row']['asset_type'];
$assetTypeS = substr($data['row']['asset_type'], 0, -1); // "styles" to "style" & "scripts" to "script"

// Unload it if the post has a certain "Category", "Tag" or other taxonomy associated with it.

// Only show it if "Unload site-wide" is NOT enabled
// Otherwise, there's no point to use this unload rule based on the chosen taxonomy's value if the asset is unloaded site-wide
?>
<div class="wpacu_asset_options_wrap wpacu_manage_via_tax_area_wrap">
    <ul class="wpacu_asset_options">
        <?php
        if ($assetType === 'scripts') {
            switch ( $data['post_type'] ) {
                case 'product':
                    $unloadViaTaxText = __( 'Unload JS on all WooCommerce "Product" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                    break;
                case 'download':
                    $unloadViaTaxText = __( 'Unload JS on all Easy Digital Downloads "Download" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                    break;
                default:
                    $unloadViaTaxText = sprintf( __( 'Unload JS on all pages of "<strong>%s</strong>" post type if these taxonomies (category, tag, etc.) are set', 'wp-asset-clean-up' ), $data['post_type'] );
            }
        } else {
            switch ( $data['post_type'] ) {
                case 'product':
                    $unloadViaTaxText = __( 'Unload CSS on all WooCommerce "Product" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                    break;
                case 'download':
                    $unloadViaTaxText = __( 'Unload CSS on all Easy Digital Downloads "Download" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                    break;
                default:
                    $unloadViaTaxText = sprintf( __( 'Unload CSS on all pages of "<strong>%s</strong>" post type if these taxonomies (category, tag, etc.) are set', 'wp-asset-clean-up' ), $data['post_type'] );
            }
        }
        ?>
        <li>
            <label class="wpacu-manage-hardcoded-assets-requires-pro-popup">
                <span style="color: #ccc;" class="wpacu-manage-hardcoded-assets-requires-pro-popup dashicons dashicons-lock"></span>
                <?php echo wp_kses($unloadViaTaxText, array('strong' => array())); ?></label>
            <a style="text-decoration: none; color: inherit; vertical-align: middle;" target="_blank"
               href="https://www.assetcleanup.com/docs/?p=1415#unload"><span
                        class="dashicons dashicons-editor-help"></span></a>
        </li>
    </ul>
</div>
