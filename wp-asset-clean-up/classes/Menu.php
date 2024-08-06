<?php
/** @noinspection MultipleReturnStatementsInspection */

namespace WpAssetCleanUp;

/**
 * Class Menu
 * @package WpAssetCleanUp
 */
class Menu
{
	/**
	 * @var array|string[]
	 */
	public static $allMenuPages = array();

	/**
	 * @var string
	 */
	private static $_capability = 'administrator';

    /**
     * Menu constructor.
     */
    public function __construct()
    {
    	self::$allMenuPages = array(
		    WPACU_PLUGIN_ID . '_getting_started',
		    WPACU_PLUGIN_ID . '_settings',
		    WPACU_PLUGIN_ID . '_assets_manager',
		    WPACU_PLUGIN_ID . '_plugins_manager',
		    WPACU_PLUGIN_ID . '_bulk_unloads',
		    WPACU_PLUGIN_ID . '_overview',
		    WPACU_PLUGIN_ID . '_tools',
		    WPACU_PLUGIN_ID . '_license',
		    WPACU_PLUGIN_ID . '_get_help',
		    WPACU_PLUGIN_ID . '_go_pro'
	    );

        add_action('admin_menu', array($this, 'activeMenu'));

        // Whenever the following option is on: "Settings" - "Plugin Usage Preferences" - "Visibility" - "Hide it from the left sidebar within the Dashboard"
        // Make sure that on any plugin page that is visited the following sidebar Dashboard menu item will be visible: "Settings" - "Asset CleanUp Pro"
        if (self::isPluginPage() && Main::instance()->settings['hide_from_side_bar']) {
            self::makeSidebarSettingsPluginLinkVisible();
            add_filter('admin_body_class', array($this, 'filterAdminBodyClass'), PHP_INT_MAX);
        }

        // [wpacu_lite]
        if (isset($_GET['page']) && $_GET['page'] === WPACU_PLUGIN_ID . '_go_pro') {
        	header('Location: '.apply_filters('wpacu_go_pro_affiliate_link', WPACU_PLUGIN_GO_PRO_URL.'?utm_source=plugin_go_pro'));
        	exit();
        }
        // [/wpacu_lite]

	    add_filter( 'post_row_actions', array($this, 'editPostRowActions'), 10, 2 );
	    add_filter( 'page_row_actions', array($this, 'editPostRowActions'), 10, 2 );

	    add_action('admin_page_access_denied', array($this, 'pluginPagesAccessDenied'));
    }

    /**
     * @param $classes
     *
     * @return mixed
     */
    public function filterAdminBodyClass($classes)
    {
        $sanitizedData = 'asset-cleanup';

        $classes .= ' '.$sanitizedData.'_page_'.sanitize_title($_GET['page']).' ';

        return $classes;
    }

    /**
     * @noinspection NestedAssignmentsUsageInspection
     */
    public function activeMenu()
    {
	    // User should be of 'administrator' role and allowed to activate plugins
	    if (! self::userCanManageAssets()) {
		    return;
	    }

        $slug = $parentSlug = WPACU_PLUGIN_ID . '_getting_started'; // default

        if (Main::instance()->settings['hide_from_side_bar']) {
            $parentSlug = null;
        }

        add_menu_page(
            WPACU_PLUGIN_TITLE,
	        WPACU_PLUGIN_TITLE,
	        self::getAccessCapability(),
            $slug,
            array(new Info, 'gettingStarted'),
	        WPACU_PLUGIN_URL.'/assets/icons/icon-asset-cleanup.png'
        );

        add_submenu_page(
            $parentSlug,
            __('Getting Started', 'wp-asset-clean-up'),
            __('Getting Started', 'wp-asset-clean-up'),
            self::getAccessCapability(),
            $parentSlug
        );

	    add_submenu_page(
		    $parentSlug,
		    __('Settings', 'wp-asset-clean-up'),
		    __('Settings', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_settings',
		    array(new SettingsAdmin, 'settingsPage')
	    );

	    add_submenu_page(
		    $parentSlug,
		    __('CSS/JS Manager', 'wp-asset-clean-up'),
		    __('CSS/JS Manager', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_assets_manager',
		    array(new AssetsManagerAdmin, 'renderPage')
	    );

	    add_submenu_page(
		    $parentSlug,
		    __('Plugins Manager', 'wp-asset-clean-up'),
		    __('Plugins Manager', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_plugins_manager',
		    array(new PluginsManager, 'page')
	    );

	    add_submenu_page(
	        $parentSlug,
            __('Bulk Changes', 'wp-asset-clean-up'),
            __('Bulk Changes', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_bulk_unloads',
            array(new BulkChanges, 'pageBulkUnloads')
        );

	    add_submenu_page(
		    $parentSlug,
		    __('Overview', 'wp-asset-clean-up'),
		    __('Overview', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_overview',
		    array(new Overview, 'pageOverview')
	    );

	    add_submenu_page(
		    $parentSlug,
		    __('Tools', 'wp-asset-clean-up'),
		    __('Tools', 'wp-asset-clean-up'),
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_tools',
		    array(new Tools, 'toolsPage')
	    );

        // [wpacu_lite]
        // License Page
        add_submenu_page(
            $parentSlug,
            __('License', 'wp-asset-clean-up'),
            __('License', 'wp-asset-clean-up'),
            self::getAccessCapability(),
            WPACU_PLUGIN_ID . '_license',
            array(new Info, 'license')
        );
        // [/wpacu_lite]

        // Get Help | Support Page
        add_submenu_page(
	        $parentSlug,
            __('Help', 'wp-asset-clean-up'),
            __('Help', 'wp-asset-clean-up'),
	        self::getAccessCapability(),
	        WPACU_PLUGIN_ID . '_get_help',
            array(new Info, 'help')
        );

		// [wpacu_lite]
	    // Upgrade to "Go Pro" | Redirects to sale page
	    add_submenu_page(
            $parentSlug,
		    __('Go Pro', 'wp-asset-clean-up'),
		    __('Go Pro', 'wp-asset-clean-up') . ' <span style="font-size: 16px; color: inherit;" class="dashicons dashicons-star-filled"></span>',
		    self::getAccessCapability(),
		    WPACU_PLUGIN_ID . '_go_pro',
		    function() {}
	    );
		// [/wpacu_lite]

        // Add plugin settings link to the main "Settings" menu within the Dashboard, for easier navigation
        add_options_page(
            WPACU_PLUGIN_TITLE,
            WPACU_PLUGIN_TITLE,
            self::getAccessCapability(),
            admin_url('admin.php?page=' . WPACU_PLUGIN_ID . '_settings')
        );

        }

    /**
     * @return void
     */
    public static function makeSidebarSettingsPluginLinkVisible()
    {
        add_action('wp_loaded', static function() {
            ob_start(static function($htmlSource) {
                $reps = array(
                    '<li class="wp-has-submenu wp-not-current-submenu menu-top menu-icon-settings menu-top-last" id="menu-settings">' =>
                        '<li class="wp-has-submenu wp-has-current-submenu wp-menu-open menu-top menu-icon-settings menu-top-last" id="menu-settings">',

                    '<a href=\'options-general.php\' class="wp-has-submenu wp-not-current-submenu' =>
                        '<a href=\'options-general.php\' class="wp-has-submenu wp-has-current-submenu wp-menu-open',

                    '<li><a href=\''.admin_url('admin.php?page=wpassetcleanup_settings').'\'>' . WPACU_PLUGIN_TITLE . '</a></li></ul>' =>
                        '<li class="current"><a class="current" aria-current="page" href=\''.admin_url('admin.php?page=wpassetcleanup_settings').'\'>' . WPACU_PLUGIN_TITLE . '</a></li></ul>'
                );

                return str_replace(array_keys($reps), array_values($reps), $htmlSource);
            });
        }, 0);
    }

    /**
     * @param array|string $params
     *
     * @return bool
     */
    public static function userCanManageAssets($params = array())
	{
        if ( function_exists('is_user_logged_in') && ! is_user_logged_in() ) {
            return false; // it's a must that the user has to be logged-in
        }

        if ( is_string($params) ) {
            $params = array($params);
        }

        // Note: Sometimes, the function "is_super_admin" is called before the "userCanManageAssets" method
        // To avoid loading the "Menu" class in the first place (for optimization)
		if ( in_array('skip_is_super_admin', $params) || is_super_admin() ) {
			return true; // For security reasons, super admins will always be able to access the plugin's settings
		}

		// Has self::$_capability been changed? Just user current_user_can()
		if (self::getAccessCapability() !== self::$_capability) {
			return current_user_can(self::getAccessCapability());
		}

		// self::$_capability default value: "administrator"
		return current_user_can(self::getAccessCapability());
	}

	/**
	 * @return bool
	 */
	public static function isPluginPage()
	{
		return isset($_GET['page']) && is_string($_GET['page']) && in_array($_GET['page'], self::$allMenuPages);
	}

	/**
	 * Here self::$_capability can be overridden
	 *
	 * @return mixed|void
	 */
	public static function getAccessCapability()
	{
		return apply_filters('wpacu_access_role', self::$_capability);
	}

	/**
	 * @param $actions
	 * @param $post
	 *
	 * @return mixed
	 */
	public function editPostRowActions($actions, $post)
	{
		// Check for your post type.
		if ( $post->post_type === 'post' ) {
			$wpacuFor = 'posts';
		} elseif ( $post->post_type === 'page' ) {
			$wpacuFor = 'pages';
		} elseif ( $post->post_type === 'attachment' ) {
			$wpacuFor = 'media_attachment';
		} else {
			$wpacuFor = 'custom_post_types';
		}

		$postTypeObject = get_post_type_object($post->post_type);

		if ( ! (isset($postTypeObject->public) && $postTypeObject->public == 1) ) {
			return $actions;
		}

		if ( ! in_array(get_post_status($post), array('publish', 'private')) ) {
			return $actions;
		}

		// Do not show the management link to specific post types that are marked as "public", but not relevant such as "ct_template" from Oxygen Builder
		if (in_array($post->post_type, MetaBoxes::$noMetaBoxesForPostTypes)) {
			return $actions;
		}

		// Build your links URL.
		$url = esc_url(admin_url( 'admin.php?page=wpassetcleanup_assets_manager' ));

		// Maybe put in some extra arguments based on the post status.
		$edit_link = add_query_arg(
			array(
				'wpacu_for'     => $wpacuFor,
				'wpacu_post_id' => $post->ID
			), $url
		);

		// Only show it to the user that has "administrator" access, and it's in the following list (if a certain list of admins is provided)
		// "Settings" -> "Plugin Usage Preferences" -> "Allow managing assets to:"
		if (self::userCanManageAssets() && AssetsManager::currentUserCanViewAssetsList()) {
			/*
			 * You can reset the default $actions with your own array, or simply merge them
			 * here I want to rewrite my Edit link, remove the Quick-link, and introduce a
			 * new link 'Copy'
			 */
			$actions['wpacu_manage_assets'] = sprintf( '<a href="%1$s">%2$s</a>',
				esc_url( $edit_link ),
				esc_html( __( 'Manage CSS &amp; JS', 'wp-asset-clean-up' ) )
			);
		}

		return $actions;
	}

	/**
	 * Message to show if the user does not have self::$_capability role and tries to access a plugin's page
	 */
	public function pluginPagesAccessDenied()
	{
		if ( ! self::isPluginPage() ) {
			// Not an Asset CleanUp page
			return;
		}

		$userMeta = get_userdata(get_current_user_id());
		$userRoles = $userMeta->roles;

		wp_die(
			__('Sorry, you are not allowed to access this page.').'<br /><br />'.
			sprintf(__('Asset CleanUp requires "%s" role and the ability to activate plugins in order to access its pages.', 'wp-asset-clean-up'), '<span style="color: green; font-weight: bold;">'.self::getAccessCapability().'</span>').'<br />'.
			sprintf(__('Your current role(s): <strong>%s</strong>', 'wp-asset-clean-up'), implode(', ', $userRoles)).'<br /><br />'.
			__('The value (in green color) can be changed if you use the following snippet in functions.php (within your theme/child theme or a custom plugin):').'<br />'.
			'<p><code style="background: #f2f3ea; padding: 5px;">add_filter(\'wpacu_access_role\', function($role) { return \'your_role_here\'; });</code></p>'.
			'<p>If the snippet is not used, it will default to "administrator".</p>'.
			'<p>Possible values: <strong>manage_options</strong>, <strong>activate_plugins</strong>, <strong>manager</strong> etc.</p>'.
			'<p>Read more: <a target="_blank" href="https://wordpress.org/support/article/roles-and-capabilities/#summary-of-roles">https://wordpress.org/support/article/roles-and-capabilities/#summary-of-roles</a></p>',
			403
		);
	}
}
