<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-script-single-row.php
*/

if ( ! isset($data) ) {
	exit; // no direct access
}

if (isset($data['row']['obj']->position) && $data['row']['obj']->position !== '') {
	$extraInfo[] = __('Position:', 'wp-asset-clean-up') . ' ' . (( $data['row']['obj']->position === 'head') ? 'HEAD' : 'BODY') . '<a class="go-pro-link-no-style" href="' . apply_filters('wpacu_go_pro_affiliate_link', WPACU_PLUGIN_GO_PRO_URL . '?utm_source=manage_asset&utm_medium=change_js_position') .'"><span class="wpacu-tooltip" style="width: 322px; margin-left: -146px;">Upgrade to Pro and change the location of the JS file <br /> (e.g. to BODY to reduce render-blocking <br /> or to HEAD for very early triggering)</span><img width="20" height="20" src="' . WPACU_PLUGIN_URL . '/assets/icons/icon-lock.svg" valign="top" alt="" /> Change it?</a>';
}