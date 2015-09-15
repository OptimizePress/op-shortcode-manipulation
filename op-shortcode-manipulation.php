<?php
/**
 * Plugin Name: OptimizePress Shortcode Manipulation
 * Plugin URI:  www.optimizepress.com
 * Description: Plugin enables you to remove or force OptimizePress shorcode from LiveEditor pages
 * Version:     1.0.0
 * Author:      OptimizePress <info@optimizepress.com>
 * Author URI:  optimizepress.com
 */


class OptimizePress_Shortcode_Manipulation
{
    /**
     * @var OptimizePress_Shortcode_Manipulation
     */
    protected static $instance;

    private $assets_array   = array();
    private $settingsPage   = 'op_shortcode_edit';
    private $adminNonce     = 'op_shortcode_edit_nonce';
    private $userSettings   = 'op_shortcode_user_settings';

    /**
     * Registering actions and filters
     */
    protected function __construct()
    {
        /*
         * Filters
         */
        add_filter('op_assets_after_addons', array($this, 'removeFormAssetList'));
        add_filter('op_assets_before_shortcode_init', array($this, 'removeFromShortcodes'));

        /*
         * Actions
         */
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('init' , array($this, 'op_force_shortcodes'), 99);
    }

    /**
     * Singleton
     * @return OptimizePress_Shortcode_Manipulation
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Adds option page in WordPress
     * @return mixed
     */
    public function admin_menu(){

        $settings_page = add_options_page(
            __('OptimizePress Shortcodes','optimizepress'),
            __('OptimizePress Shortcodes','optimizepress'),
            'administrator',
            $this->settingsPage,
            array($this,'op_shorcode_settings_page')
        );
        add_action( "load-{$settings_page}", array($this, 'streemfire_load_settings_page') );
    }

    /**
     * Saves users settings
     * @return mixed
     */
    public function streemfire_load_settings_page(){
        if (isset($_POST["op-tag-settings-submit"])) {
            if ($_POST["op-tag-settings-submit"] == 'Y') {
                check_admin_referer($this->adminNonce, $this->adminNonce);

                if (isset($_POST["op_tag"])) {
                    update_option( $this->userSettings, $_POST["op_tag"] );
                }

                wp_redirect(admin_url('options-general.php?page=' . $this->settingsPage . '&updated=true'));
                exit;
            }
        }
    }

    /**
     * Renders option page
     * @return mixed
     */
    public function op_shorcode_settings_page() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        include_once 'partials/show/settings-page.php';
    }

    /**
     * Removes element from asset list
     * @param  array $assets
     * @return array
     */
    public function removeFormAssetList($assets)
    {
        $user_settings = get_option( $this->userSettings );
        $user_settings = (!is_array($user_settings)) ? array() : $user_settings;
        foreach ($user_settings as $user_settings_tag => $user_settings_value){
            if ($user_settings_value == 'disable'){
                unset($assets['core'][$user_settings_tag]);
            }
        }

        return $assets;
    }

    /**
     * Removes element from init shortcode list
     * @param $assets
     * @return mixed
     */
    public function removeFromShortcodes($assets)
    {
        $this->assets_array = $assets;

        $user_settings = get_option( $this->userSettings );
        $user_settings = (!is_array($user_settings)) ? array() : $user_settings;

        foreach ($user_settings as $user_settings_tag => $user_settings_value){
            if ($user_settings_value == 'disable'){
                unset($assets[$user_settings_tag]);
            }
        }

        return $assets;
    }

    /**
     * Forces OptimizePress shortcode on LiveEditor page
     * @return mixed
     */

    public function op_force_shortcodes(){
        $checkIfLEPage = get_post_meta( url_to_postid( "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ), '_optimizepress_pagebuilder', true );

        $pageBuilder = false;
        if ( isset($_GET) ) {
            if (array_key_exists('page', $_GET)) {
                $pageBuilder = ($_GET['page'] == 'optimizepress-page-builder' ) ? true : false;
            }
        }

        if ( ($checkIfLEPage == 'Y') || $pageBuilder){
            global $shortcode_tags;
            $user_settings = get_option( $this->userSettings );
            $user_settings = (!is_array($user_settings)) ? array() : $user_settings;

            foreach ($user_settings as $user_settings_tag => $user_settings_value){
                if ($user_settings_value == 'force'){
                    $shortcode_tags[$user_settings_tag] = array();
                    $shortcode_tags[$user_settings_tag][0] = 'OptimizePress_Default_Assets';
                    $shortcode_tags[$user_settings_tag][1] = $user_settings_tag;
                }
            }
        }
    }
}

OptimizePress_Shortcode_Manipulation::getInstance();