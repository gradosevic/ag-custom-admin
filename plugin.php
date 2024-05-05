<?php
/*
Plugin Name: AGCA - Custom Dashboard & Login Page
Plugin URI: https://cusmin.com/agca
Description: CHANGE: admin menu, login page, admin bar, dashboard widgets, custom colors, custom CSS & JS, logo & images
Author: Cusmin
Version: 7.2.5
Text Domain: ag-custom-admin
Domain Path: /languages
Author URI: https://cusmin.com/

    Copyright 2024. Cusmin (email : info@cusmin.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

$agca = new AGCA();

class AGCA{
    private $agca_version = "7.2.5";
    private $colorizer = "";
    private $agca_debug = false;
    private $admin_capabilities;
    private $context = "";
    private $saveAfterImport = false;

    const PLACEHOLDER_BLOG = '%BLOG%';
    const PLACEHOLDER_PAGE = '%PAGE%';

    public function __construct()
    {
        add_action( 'plugins_loaded', array(&$this,'my_load_plugin_textdomain') );
        add_action('init', array(&$this,'init'));
    }

    function init(){
        $this->reloadScript();
        $this->checkGET();

        if(function_exists("add_filter")){
            add_filter('admin_title', array(&$this,'change_title'), 10, 2);
            add_filter('plugin_row_meta', array(&$this,'jk_filter_plugin_links'), 10, 2);
        }

        add_action('admin_init', array(&$this,'admin_init'));
        add_action('login_init', array(&$this,'login_init'));
        add_action('admin_head', array(&$this,'print_admin_css'));
        add_action('login_head', array(&$this,'print_login_head'));
        add_action('admin_menu', array(&$this,'agca_create_menu'));
        add_action('wp_head', array(&$this,'print_page'));


        register_deactivation_hook(__FILE__, array(&$this,'agca_deactivate'));

        add_action( 'customize_controls_enqueue_scripts',  array(&$this,'agca_customizer_php') );

        add_action( 'admin_bar_menu', array(&$this, 'wp_admin_bar_my_custom_account_menu'), 11 );
        add_action( 'updated_option', array(&$this, 'after_update_option'), 10, 3);

        /*Initialize properties*/
        $this->colorizer = $this->jsonMenuArray(get_option('ag_colorizer_json'),'colorizer');

        if(!get_option('agca_check_js_notice') && get_option('agca_custom_js')) {
            add_action( 'admin_notices', array(&$this, 'agca_check_js_notice') );
        }

        //admin bar on front end
        if(!is_admin() && is_admin_bar_showing()){
            add_action('wp_head', array(&$this, 'hide_admin_bar_css'));
            $this->agca_enqueue_js();
        }
    }

    function hide_admin_bar_css(){
        if(is_user_logged_in() &&
            current_user_can($this->admin_capability()) &&
            get_option('agca_role_allbutadmin')){
            return;
        }
        ?>
        <style type="text/css">
            #wpadminbar{
                display: none;
            }
        </style>
        <?php
    }

    function agca_check_js_notice(){
            echo '<div class="notice error">
             <p><h2>IMPORTANT MESSAGE FROM AGCA PLUGIN! (aka Custom Dashboard & Login Page plugin)</h2></p>
             <p>We are enforcing security in AGCA plugin and custom JavaScript code is currently disabled and requires your review. Please go to AGCA plugin settings (Tools > AGCA > Advanced)  page and make sure that your JavaScript does not contain any unwanted code. If you are sure that the script looks good to you, please save the settings in AGCA plugin again, to dismiss this notice and re-enable the script again.</p>
             <p><a href="'. get_site_url() .'/wp-admin/tools.php?page=ag-custom-admin%2Fplugin.php#ag-advanced">Click here to revisit and save AGCA settings again</a></p>
         </div>';
    }

    function isAGCASettingsPage() {
        if(!is_admin()) {
            return false;
        }
        if(isset($_GET['page']) && $_GET['page'] == 'ag-custom-admin/plugin.php'){
            return true;
        }

        return false;
    }

    function my_load_plugin_textdomain() {
        load_plugin_textdomain( 'ag-custom-admin');
    }

    // Add donate and support information
    function jk_filter_plugin_links($links, $file)
    {
        if ( $file == plugin_basename(__FILE__) )
        {
            if(!is_network_admin()){
                $links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php#general-settings">' . __('Settings', 'ag-custom-admin') . '</a>';
            }
            $links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/ag-custom-admin">' . __('Support', 'ag-custom-admin') . '</a>';
            $links[] = '<a target="_blank" href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-plugins-page">' . __('Upgrade', 'ag-custom-admin') . '</a>';
            $links[] = '<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=agca@cusmin.com&amount=10&item_name=Support+for+AGCA+Development">' . __('Donate', 'ag-custom-admin') . '</a>';
        }
        return $links;
    }


    function filePath($url){
        $absPath = ABSPATH;
        $absPath = rtrim($absPath, '/');
        $url = ltrim($url, '/');
        return $absPath.'/'.$url;
    }

    function change_admin_color(){
        return 'default';
    }

    //Prevent non-admin users to update sensitive options
    //Revert option value to previous
    function after_update_option( $option, $old_value, $new_value ){
        if((
                !current_user_can('administrator') ||
                !current_user_can('unfiltered_html')
            ) &&
            in_array($option, [
                'agca_dashboard_text_paragraph',
                'agca_dashboard_text',
                'agca_custom_css',
                'agca_footer_left',
                'agca_footer_right',
                'agca_custom_js',
                'agca_check_js_notice',
            ])) {
            remove_action( 'updated_option', array(&$this,'after_update_option'));
            update_option($option, $old_value);
            add_action( 'updated_option', array(&$this,'after_update_option'), 10, 3);
        }
    }

    function is_wp_admin(){
        return current_user_can('administrator');
    }

    function can_save_unfiltered_html(){
        return current_user_can('unfiltered_html');
    }

    function agca_customizer_php(){
        $this->agca_get_includes();
    }

    function admin_init(){

        $this->agca_register_settings();
        $isAdminUser = current_user_can($this->admin_capability());
        if(!$isAdminUser || ($isAdminUser && !get_option('agca_role_allbutadmin'))){
            if(get_option('agca_profile_color_scheme')){
                remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
            }
        }

        $this->enqueue_scripts();
    }

    function login_init(){
        $this->agca_enqueue_scripts();
    }

    function enqueue_scripts() {

        $wpversion = $this->get_wp_version();

        if($this->isAGCASettingsPage()) {
            wp_register_script ( 'agca-farbtastic-script', $this->pluginUrl() . 'script/agca_farbtastic.js', array('farbtastic'), $wpversion );
            wp_register_style('agca-farbtastic-style', $this->pluginUrl() . 'style/agca_farbtastic.css', array('farbtastic'), $wpversion);

            wp_enqueue_script ( 'farbtastic' );
            wp_enqueue_style( 'farbtastic' );
            wp_enqueue_script ( 'agca-farbtastic-script', array('farbtastic') );
            wp_enqueue_style ( 'agca-farbtastic-style', array('farbtastic') );

        }

        if(is_admin()) {
            $agcaStyleFileName = get_option('agca_no_style') == true ? 'ag_style_simple' : 'ag_style';
            wp_register_style('agca-style', $this->pluginUrl() . 'style/' . $agcaStyleFileName . '.css', array('farbtastic'), $this->agca_version);
            wp_enqueue_style( 'agca-style' );
            $this->agca_enqueue_js();
        }
    }

    function agca_enqueue_js(){
        wp_register_script ( 'agca-script', $this->pluginUrl() . 'script/ag_script.js', array('jquery'), $this->agca_version );
        add_action('wp_print_scripts', function () {
            wp_enqueue_script ( 'agca-script' );
        }, 2);
    }

    function checkGET(){
        if(isset($_GET['agca_debug'])){
            if($_GET['agca_debug'] =="true"){
                $this->agca_debug = true;
            }else{
                $this->agca_debug = false;
            }
        }
    }

    function getFieldSecurityProtected(){
        if($this->is_wp_admin() && $this->can_save_unfiltered_html()){
            return '';
        }
        return "<p class=\"agca-field-secured-notice\">(&nbsp;For security reasons, this field is available for editing only to WordPress <b>Administrators</b> with the allowed capability to save unfiltered HTML&nbsp;)</p>";
    }

    function verifyPostRequest(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_admin()) {
                _e('Not allowed. This action is allowed exclusively in admin panel', 'ag-custom-admin');
                exit;
            }
            //In case of problems with saving AGCA settings on MS disable verification temporary
            if(get_option('agca_disable_postver')){
                return;
            }
            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                $user_id = get_current_user_id();
                $msError = __('Please try temporary disabling POST verification. Go to Absolutely Glamorous Custom Admin -> Advanced -> Temporary disable POST verification. Do not forget to un-check this option once you are done with customizations.', 'ag-custom-admin');
                if (is_user_member_of_blog($user_id, $blog_id)) {
                    if (!current_user_can('manage_options')) {
                        _e('Multi-site: Current user is not recognized as administrator.', 'ag-custom-admin');
                        echo ' '. $this->sanitize_html($msError);
                        exit;
                    }
                } else {
                    printf(
                    /*translators: 1: User Id 2: Blog Id*/
                        __('Multi-site: User (%1$s) does not have access to this blog (%2$s).', 'ag-custom-admin'),
                        $user_id,
                        $blog_id
                    );
                    echo ' '. $this->sanitize_html($msError);
                    exit;
                }
            } else {
                include_once($this->filePath('wp-includes/pluggable.php'));
                if (!is_user_logged_in() || !current_user_can('manage_options')) {
                    echo !is_user_logged_in() ? __('User is not logged in.', 'ag-custom-admin').' ' : '';
                    echo !current_user_can('manage_options') ? __('User can not manage options.', 'ag-custom-admin').' ' : '';
                    exit;
                }
            }
            if (!wp_verify_nonce($_POST['_agca_token'], 'agca_form')) {
                echo __('Nonce verification failed.', 'ag-custom-admin');
                exit;
            }
        }
    }

    function isGuest(){
        global $user_login;
        if($user_login) {
            return false;
        }else{
            return true;
        }
    }
    function change_title($admin_title, $title){
        //return get_bloginfo('name').' - '.$title;
        if(get_option('agca_custom_title')!=""){
            $blog = get_bloginfo('name');
            $page = $title;
            $customTitle = get_option('agca_custom_title');
            $customTitle = str_replace(self::PLACEHOLDER_BLOG,$blog,$customTitle);
            $customTitle = str_replace(self::PLACEHOLDER_PAGE,$page,$customTitle);
            return htmlentities(strip_tags($customTitle));
        }
        return strip_tags($admin_title);
    }
    function agca_get_includes() {
        if(!((get_option('agca_role_allbutadmin')==true) and (current_user_can($this->admin_capability())))){
            ?>
            <style type="text/css">
                <?php echo wp_kses_post(get_option('agca_custom_css')); ?>
            </style>
            <?php if(get_option('agca_check_js_notice')) { ?>
                <script type="text/javascript">
                    try{
                        <?php echo wp_kses_post(get_option('agca_custom_js')); ?>
                    }catch(e){
                        alert('AGCA: <?php _e('There is an error in your custom JS script. Please fix it:', 'ag-custom-admin'); ?> \n\n' + e + '\n\n (<?php _e('AGCA -> Advanced -> Custom JavaScript', 'ag-custom-admin'); ?>)');
                        console.log(e);
                    }
                </script>
            <?php } ?>
            <?php
        }
    }

    function agca_enqueue_scripts() {
        wp_enqueue_script('jquery');
    }

    function WPSPluginIsLoginPage(){
        $WPSPluginName = 'wps-hide-login/wps-hide-login.php';
        if(is_multisite()){
            if ( ! function_exists( 'is_plugin_active_for_network' ) ){
                $pluginPhpFilePath = $this->filePath('wp-admin/includes/plugins.php');
                if(!file_exists($pluginPhpFilePath)){
                    return '';
                }
                require_once($pluginPhpFilePath);
            }

            if(!$this->isPluginActiveForNetwork($WPSPluginName)){
                return '';
            }
        }else{
            if(!$this->isPluginActive($WPSPluginName)){
                return '';
            }
        }

        if ( $slug = get_option( 'whl_page' ) ) {
            return $slug;
        } else if ( ( is_multisite() && $this->isPluginActiveForNetwork($WPSPluginName) && ( $slug = get_site_option( 'whl_page', 'login' ) ) ) ) {
            return $slug;
        } else if ( $slug = 'login' ) {
            return $slug;
        }
        $requestURI = $_SERVER['REQUEST_URI'];
        return $this->startsWith('/'.$slug.'/', $requestURI);
    }

    function reloadScript(){
        $isAdmin = false;
        if(defined('WP_ADMIN') && WP_ADMIN == 1){
            $isAdmin = true;
        }
        if($isAdmin || $this->WPSPluginIsLoginPage()){
            $this->agca_enqueue_scripts();
        }
    }

    function agca_register_settings() {
        register_setting( 'agca-options-group', 'agca_role_allbutadmin' );
        register_setting( 'agca-options-group', 'agca_no_style' );
        register_setting( 'agca-options-group', 'agca_screen_options_menu' );
        register_setting( 'agca-options-group', 'agca_help_menu' );
        register_setting( 'agca-options-group', 'agca_logout' );
        register_setting( 'agca-options-group', 'agca_remove_your_profile' );
        register_setting( 'agca-options-group', 'agca_logout_only' );
        register_setting( 'agca-options-group', 'agca_custom_title' );
        register_setting( 'agca-options-group', 'agca_howdy' );
        register_setting( 'agca-options-group', 'agca_header' );
        register_setting( 'agca-options-group', 'agca_header_show_logout' );
        register_setting( 'agca-options-group', 'agca_footer' );
        register_setting( 'agca-options-group', 'agca_privacy_options' );
        register_setting( 'agca-options-group', 'agca_header_logo' );
        register_setting( 'agca-options-group', 'agca_header_logo_custom' );
        register_setting( 'agca-options-group', 'agca_wp_logo_custom' );
        register_setting( 'agca-options-group', 'agca_remove_site_link' );
        register_setting( 'agca-options-group', 'agca_wp_logo_custom_link' );
        register_setting( 'agca-options-group', 'agca_profile_color_scheme' );

        register_setting( 'agca-options-group', 'agca_site_heading' );
        register_setting( 'agca-options-group', 'agca_custom_site_heading' );
        register_setting( 'agca-options-group', 'agca_update_bar' );

        register_setting( 'agca-options-group', 'agca_footer_left' );
        register_setting( 'agca-options-group', 'agca_footer_left_hide' );
        register_setting( 'agca-options-group', 'agca_footer_right' );
        register_setting( 'agca-options-group', 'agca_footer_right_hide' );
        register_setting( 'agca-options-group', 'agca_check_js_notice' );

        register_setting( 'agca-options-group', 'agca_login_banner' );
        register_setting( 'agca-options-group', 'agca_login_banner_text' );
        register_setting( 'agca-options-group', 'agca_login_photo_remove' );
        register_setting( 'agca-options-group', 'agca_login_photo_url' );
        register_setting( 'agca-options-group', 'agca_login_photo_href' );
        register_setting( 'agca-options-group', 'agca_login_round_box' );
        register_setting( 'agca-options-group', 'agca_login_round_box_size' );
        register_setting( 'agca-options-group', 'agca_login_round_box_skip_logo' );


        register_setting( 'agca-options-group', 'agca_dashboard_icon' );
        register_setting( 'agca-options-group', 'agca_dashboard_text' );
        register_setting( 'agca-options-group', 'agca_dashboard_text_paragraph' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_welcome' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_health_status' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_activity' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_il' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_plugins' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_qp' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_rn' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_rd' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_primary' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_secondary' );

        //WP3.3
        register_setting( 'agca-options-group', 'agca_admin_bar_comments' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content_post' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content_link' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content_page' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content_user' );
        register_setting( 'agca-options-group', 'agca_admin_bar_new_content_media' );
        register_setting( 'agca-options-group', 'agca_admin_bar_update_notifications' );
        register_setting( 'agca-options-group', 'agca_remove_top_bar_dropdowns' );
        register_setting( 'agca-options-group', 'agca_admin_bar_frontend' );
        register_setting( 'agca-options-group', 'agca_admin_bar_frontend_hide' );
        register_setting( 'agca-options-group', 'agca_login_register_remove' );
        register_setting( 'agca-options-group', 'agca_login_register_href' );
        register_setting( 'agca-options-group', 'agca_login_lostpassword_remove' );
        register_setting( 'agca-options-group', 'agca_admin_capability' );
        register_setting( 'agca-options-group', 'agca_disablewarning' );

        /*Admin menu*/
        register_setting( 'agca-options-group', 'agca_admin_menu_turnonoff' );
        register_setting( 'agca-options-group', 'agca_admin_menu_agca_button_only' );
        register_setting( 'agca-options-group', 'agca_admin_menu_separators' );
        register_setting( 'agca-options-group', 'agca_admin_menu_icons' );
        register_setting( 'agca-options-group', 'agca_admin_menu_collapse_button' );
        register_setting( 'agca-options-group', 'agca_admin_menu_arrow' );
        register_setting( 'agca-options-group', 'agca_admin_menu_submenu_round' );
        register_setting( 'agca-options-group', 'agca_admin_menu_submenu_round_size' );
        register_setting( 'agca-options-group', 'agca_admin_menu_brand' );
        register_setting( 'agca-options-group', 'agca_admin_menu_brand_link' );
        register_setting( 'agca-options-group', 'agca_admin_menu_autofold' );
        register_setting( 'agca-options-group', 'ag_edit_adminmenu_json' );
        register_setting( 'agca-options-group', 'ag_edit_adminmenu_json_new' );
        register_setting( 'agca-options-group', 'ag_add_adminmenu_json' );
        register_setting( 'agca-options-group', 'ag_colorizer_json' );
        register_setting( 'agca-options-group', 'agca_colorizer_turnonoff' );

        register_setting( 'agca-options-group', 'agca_custom_js' );
        register_setting( 'agca-options-group', 'agca_custom_css' );
        register_setting( 'agca-options-group', 'agca_disable_postver' );
        register_setting( 'agca-options-group', 'agca_menu_remove_client_profile' );
        register_setting( 'agca-options-group', 'agca_menu_remove_customize_button' );


        if(!empty($_POST)){
            if(isset($_POST['_agca_import_settings']) && $_POST['_agca_import_settings']=="true"){
                $this->verifyPostRequest();
                if(isset($_FILES) && isset($_FILES['settings_import_file']) ){
                    if($_FILES["settings_import_file"]["error"] > 0){
                    }else{
                        $file = $_FILES['settings_import_file'];
                        if($this->startsWith($file['name'],'AGCA_Settings')){
                            if (file_exists($file['tmp_name'])) {
                                $fh = fopen($file['tmp_name'], 'r');
                                $theData = "";
                                if(filesize($file['tmp_name']) > 0){
                                    $theData = fread($fh,filesize($file['tmp_name']));
                                }
                                fclose($fh);
                                $this->importSettings($theData);
                            }
                        }
                    }
                }
            }else if(isset($_POST['_agca_export_settings']) && $_POST['_agca_export_settings']=="true"){
                $this->verifyPostRequest();
                $this->exportSettings();
            }
        }

        if(isset($_GET['agca_action'])){
            if($_GET['agca_action'] == "disablewarning"){
                update_option('agca_disablewarning', true);
            }
        }
    }

    function agca_deactivate() {

    }

    function getOptions(){
        return Array(
            'agca_role_allbutadmin',
            'agca_no_style',
            'agca_admin_bar_frontend',
            'agca_admin_bar_frontend_hide',
            'agca_login_register_remove',
            'agca_login_register_href',
            'agca_login_lostpassword_remove',
            'agca_admin_capability',
            'agca_screen_options_menu',
            'agca_help_menu',
            'agca_logout',
            'agca_remove_your_profile',
            'agca_logout_only',
            'agca_custom_title',
            'agca_howdy',
            'agca_header',
            'agca_header_show_logout',
            'agca_footer',
            'agca_privacy_options',
            'agca_header_logo',
            'agca_header_logo_custom',
            'agca_remove_site_link',
            'agca_wp_logo_custom',
            'agca_wp_logo_custom_link',
            'agca_profile_color_scheme',
            'agca_site_heading',
            'agca_custom_site_heading',
            'agca_update_bar',
            'agca_footer_left',
            'agca_footer_left_hide',
            'agca_footer_right',
            'agca_footer_right_hide',
            'agca_check_js_notice',
            'agca_login_banner',
            'agca_login_banner_text',
            'agca_login_photo_remove',
            'agca_login_photo_url',
            'agca_login_photo_href',
            'agca_login_round_box',
            'agca_login_round_box_size',
            'agca_login_round_box_skip_logo',
            'agca_dashboard_icon',
            'agca_dashboard_text',
            'agca_dashboard_text_paragraph',
            'agca_dashboard_widget_welcome',
            'agca_dashboard_widget_health_status',
            'agca_dashboard_widget_activity',
            'agca_dashboard_widget_il',
            'agca_dashboard_widget_plugins',
            'agca_dashboard_widget_qp',
            'agca_dashboard_widget_rn',
            'agca_dashboard_widget_rd',
            'agca_dashboard_widget_primary',
            'agca_dashboard_widget_secondary',
            'agca_admin_bar_comments',
            'agca_admin_bar_new_content',
            'agca_admin_bar_new_content_post',
            'agca_admin_bar_new_content_link',
            'agca_admin_bar_new_content_page',
            'agca_admin_bar_new_content_user',
            'agca_admin_bar_new_content_media',
            'agca_admin_bar_update_notifications',
            'agca_remove_top_bar_dropdowns',
            'agca_admin_menu_turnonoff',
            'agca_admin_menu_agca_button_only',
            'agca_admin_menu_separators',
            'agca_admin_menu_icons',
            'agca_admin_menu_arrow',
            'agca_admin_menu_submenu_round',
            'agca_admin_menu_submenu_round_size',
            'agca_admin_menu_brand',
            'agca_admin_menu_brand_link',
            'agca_admin_menu_autofold',
            'agca_admin_menu_collapse_button',
            'ag_edit_adminmenu_json',
            'ag_edit_adminmenu_json_new',
            'ag_add_adminmenu_json',
            'ag_colorizer_json',
            'agca_colorizer_turnonof',
            'agca_custom_js',
            'agca_custom_css',
            'agca_colorizer_turnonoff',
            'agca_disablewarning',
            'agca_disable_postver',
            'agca_menu_remove_client_profile',
            'agca_menu_remove_customize_button',
        );
    }

    function getTextEditor($name){
        $settings = array(
            'textarea_name' => $name,
            'media_buttons' => true,
            'tinymce' => array(
                'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
                    'bullist,blockquote,|,justifyleft,justifycenter' .
                    ',justifyright,justifyfull,|,link,unlink,|' .
                    ',spellchecker,wp_fullscreen,wp_adv'
            )
        );
        wp_editor( get_option($name), $name, $settings );
    }

    function importSettings($settings){
        $exploaded = explode("|^|^|", $settings);
        // $str = "EEE: ";
        $str = '';

        $savedOptions = array();

        foreach ($exploaded as $setting){

            $key = current(explode(':', $setting));
            $value = substr($setting, strlen($key)+1);
            $cleanedValue = str_replace('|^|^|','',$value);
            $savedOptions[$key] = $cleanedValue;
        }

        // print_r($savedOptions);

        $optionNames = $this->getOptions();

        foreach ($optionNames as $optionName){
            $optionValue = $savedOptions[$optionName];

            if($optionName == "ag_edit_adminmenu_json" || "ag_edit_adminmenu_json_new"|| $optionName == "ag_add_adminmenu_json" ||$optionName == "ag_colorizer_json"){
                $optionValue = str_replace("\\\"", "\"", $optionValue);
                $optionValue = str_replace("\\\'", "\'", $optionValue);
            }else if($optionName == "agca_custom_js" || $optionName == "agca_custom_css"){

                $optionValue = htmlspecialchars_decode($optionValue);
                $optionValue = str_replace("\'", '"', $optionValue);
                $optionValue = str_replace('\"', "'", $optionValue);

            }
            update_option($optionName, $optionValue);
            $str.="/".$optionName."/".$optionValue."\n";
        }

        //Migration from 1.2.6. to 1.2.5.1 - remove in later versions
        //agca_script_css
        //
        // fb($savedOptions);
        if($savedOptions['agca_script_css'] != null){
            $optionValue = "";
            $optionValue = str_replace("\'", '"', $savedOptions['agca_script_css']);
            $optionValue = str_replace('\"', "'", $optionValue);
            update_option('agca_custom_css', $optionValue);
        }
        if($savedOptions['agca_script_js'] != null){
            $optionValue = "";
            $optionValue = str_replace("\'", '"', $savedOptions['agca_script_js']);
            $optionValue = str_replace('\"', "'", $optionValue);
            update_option('agca_custom_js', $optionValue);
        }

        //echo $str;

        //save imported settings
        $this->saveAfterImport = true;
    }

    function exportSettings(){
        $str = "";

        $include_menu_settings = false;
        if(isset($_POST['export_settings_include_admin_menu'])){
            if($_POST['export_settings_include_admin_menu'] == 'on'){
                $include_menu_settings = true;
            }
        }

        foreach ($_POST as $key => $value) {
            if ($this->startsWith($key,'ag')||$this->startsWith($key,'color')) {
                if($this->startsWith($key,'ag_edit_adminmenu')){
                    if($include_menu_settings) $str .=$key. ":".$value."|^|^|";
                }else{
                    $str .=$key. ":".$value."|^|^|";
                }
            }
        }

        $filename = __('AGCA_Settings', 'ag-custom-admin').'_'.date("Y-M-d_H-i-s").'.agca';
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: text/plain; ");
        header("Content-Transfer-Encoding: binary");
        echo wp_kses_post($str);
        die();
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    function agca_create_menu() {
        add_management_page('AGCA', 'AGCA', 'administrator', __FILE__, array(&$this,'agca_admin_page') );
    }

    function filter_url($url = ''){
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return $url;
    }

    function filter_target($target = '_self'){
        return $target === '_blank' ? '_blank' : '_self';
    }

    function agca_create_admin_button($name,$arr) {

        $href = $this->filter_url($arr["value"]);
        $target = $this->filter_target($arr["target"]);

        $button ="<li class=\"wp-not-current-submenu menu-top aaa menu-top-last\" id=\"menu-" . $this->sanitize_html($name) . "\"><a href=\"".$this->sanitize_html($href)."\" target=\"".$this->sanitize_html($target)."\" class=\"wp-not-current-submenu menu-top\"><div class=\"wp-menu-arrow\"><div></div></div><div class=\"wp-menu-image dashicons-before dashicons-admin-".$this->sanitize_html($name)."\" style=\"width:15px\"><br></div><div class=\"wp-menu-name\">".$this->sanitize_html($name)."</div></a></li>";

        return $button;
    }
    function agca_decode($code){
        $code = str_replace("{","",$code);
        $code = str_replace("}","",$code);
        $code = str_replace("\", \"","\"|||\"",$code);
        $elements = explode("|||",$code);

        return $elements;
    }

    function jsonMenuArray($json,$type){
        $arr = explode("|",$json);
        $elements = "";
        $array ="";
        $first = true;
        //print_r($json);
        if($type == "colorizer"){
            $elements = json_decode($arr[0],true);
            if($elements !=""){
                return $elements;
            }
        }else if($type == "buttons"){
            $elements = json_decode($arr[0],true);
            if($elements !=""){
                foreach($elements as $k => $v){
                    $array.=$this->agca_create_admin_button($k,$v);
                }
            }
        }else if($type == "buttonsJq"){
            $elements = json_decode($arr[0],true);
            if($elements !=""){
                foreach($elements as $k => $v){
                    $array.='<tr><td colspan="2"><button target="'.$this->sanitize_html($v['target']).'" title="'.$this->sanitize_html($v['value']).'" class="button-secondary" type="button">'.$this->sanitize_html($k).'</button>&nbsp;<a style="cursor:pointer;" title="Edit" class="button_edit"><span class="dashicons dashicons-edit"></span></a>&nbsp;<a style="cursor:pointer" title="Delete" class="button_remove"><span class="dashicons dashicons-no"></span></a></td><td></td></tr>';
                }
            }
        }else{
            if(isset($arr[$type])){
                $elements = $this->agca_decode($arr[$type]);
            }

            if($elements !=""){
                foreach($elements as $element){
                    if(!$first){
                        $array .=",";
                    }
                    $parts = explode(" : ",$element);
                    if(isset($parts[0]) && isset($parts[1])){
                        $array.="[".$parts[0].", ".$parts[1]."]";
                    }
                    $first=false;
                }
            }
        }

        return $array;
    }

    function remove_dashboard_widget($widget,$side)
    {
        //side can be 'normal' or 'side'
        remove_meta_box($widget, 'dashboard', $side);
    }

    function get_wp_version(){
        global $wp_version;
        $array = explode('-', $wp_version);
        $version = $array[0];
        return $version;
    }

    function print_page()
    {
        if($this->isGuest() && get_option('agca_admin_bar_frontend_hide') or $this->isCusminActive()){
            return false;
        }
        if(!$this->isGuest()){
            ?><style type="text/css">
            <?php
            echo $this->sanitize_html(get_option('agca_custom_css'));
            if(get_option('agca_menu_remove_customize_button')){
                echo '#wp-admin-bar-customize{display:none;}';
            }
            ?>
            </style><?php
        }

        if(get_option('agca_admin_bar_frontend_hide')==true){
            if(!((get_option('agca_role_allbutadmin')==true) and (current_user_can($this->admin_capability())))) {
                add_filter( 'show_admin_bar', '__return_false' );
                ?>
                <style type="text/css">
                    #wpadminbar{
                        display: none;
                    }
                </style>
                <script type="text/javascript">
                    window.setTimeout(function(){document.getElementsByTagName('html')[0].setAttribute('style',"margin-top:0px !important");},50);
                </script>
                <?php
            }
        }
        if(get_option('agca_admin_bar_frontend')!=true && is_user_logged_in()){

            $this->context = "page";
            //$wpversion = $this->get_wp_version();
            ?>
            <script type="text/javascript">
                var agca_version = "<?php echo $this->sanitize_html($this->agca_version); ?>";
                var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
                var jQueryScriptOutputted = false;
                var agca_context = "page";
                var agca_orig_admin_menu = [];
                function initJQuery() {
                    //if the jQuery object isn't available
                    if (typeof(jQuery) == 'undefined') {
                        if (! jQueryScriptOutputted) {
                            console.log('AGCA: jQuery not found!!!');
                        }
                    } else {
                        jQuery(function() {
                            try
                            {
                                <?php if(get_option('agca_header')!=true){ ?>
                                jQuery('#wpadminbar').show();
                                <?php } ?>

                                <?php  $this->print_admin_bar_scripts(); ?>
                            }catch(ex){}
                        });
                    }
                }
                initJQuery();
            </script>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    <?php if(get_option('agca_colorizer_turnonoff') == 'on' && (get_option('agca_admin_bar_frontend_hide')!=true)){
                            foreach($this->colorizer as $k => $v){
                                if(($k !="") and ($v !="")){
                                    if(
                                        $k == "color_header" ||
                                        $k == "color_font_header"
                                    ){
                                        ?> updateTargetColor("<?php echo $this->sanitize_html($k);?>","<?php echo $this->sanitize_html($v);?>"); <?php
                                }

                            }
                        }
                    ?>

                    <?php
                    }
                     ?>
                });
            </script>
            <?php
        }

    }

    function is_safe_remote_image($url){
        $imgCheck = wp_safe_remote_get($this->sanitize_html($url));
        if(!is_wp_error($imgCheck)) {
            $cid = $imgCheck['headers'];
            if (strpos($cid->offsetGet('content-type'), 'image/') === 0) {
                return true;
            }
        }
        return false;
    }

    function print_admin_bar_scripts(){
        ?>
        <?php if(get_option('agca_remove_top_bar_dropdowns')==true){ ?>
            jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-sub-wrapper").hide();
            jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-site-name .ab-sub-wrapper").hide();
            jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-item").attr('title','');

            <?php if(get_option('agca_admin_bar_new_content')!=""){  ?>
                jQuery(".new_content_header_submenu").hide();
            <?php } ?>

        <?php } ?>

        <?php if(get_option('agca_admin_bar_comments')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-comments").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content_post')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content li#wp-admin-bar-new-post").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content_link')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content li#wp-admin-bar-new-link").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content_page')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content li#wp-admin-bar-new-page").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content_user')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content li#wp-admin-bar-new-user").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_new_content_media')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-new-content li#wp-admin-bar-new-media").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_admin_bar_update_notifications')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-updates").css("display","none");
        <?php } ?>

        <?php if(get_option('agca_header_logo')==true){ ?>
            jQuery("#wphead #header-logo").css("display","none");
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-wp-logo").css("display","none");

        <?php } ?>
        <?php if(get_option('agca_header_logo_custom')!="" && $this->is_safe_remote_image(get_option('agca_header_logo_custom'))){ ?>

            var img_url = '<?php echo $this->sanitize_html($this->sanitize_html(get_option('agca_header_logo_custom'))); ?>';

            advanced_url = img_url;
            image = jQuery("<img id=\"admin-top-branding-logo\" style='max-width:98%;position:relative;'/>").attr("src",advanced_url);
            jQuery(image).on('load', function() {
            jQuery("#wpbody-content").prepend(image);
            });

        <?php } ?>
        <?php if(get_option('agca_wp_logo_custom')!="" && $this->is_safe_remote_image(get_option('agca_wp_logo_custom'))){ ?>
            jQuery("li#wp-admin-bar-wp-logo a.ab-item span.ab-icon")
            .html("<img alt=\"Logo\" style=\"height:32px;margin-top:0\" src=\"<?php echo $this->sanitize_html(get_option('agca_wp_logo_custom')); ?>\" />")
            .css('background-image','none')
            .css('width','auto');
            jQuery("li#wp-admin-bar-wp-logo > a.ab-item")
            .attr('href',"<?php echo $this->sanitize_html(get_bloginfo('wpurl')); ?>")
            .css('padding', 0);
            jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-item:before").attr('title','');
            jQuery('body #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon').attr('class','ab-icon2');
            jQuery("#wp-admin-bar-wp-logo").show();
        <?php }?>
        <?php if(get_option('agca_remove_site_link')==true){ ?>
            jQuery("#wp-admin-bar-site-name").css("display","none");

        <?php } ?>
        <?php if(get_option('agca_wp_logo_custom_link')!=""){ ?>

            <?php
                $href = get_option('agca_wp_logo_custom_link');
                $href = str_replace(self::PLACEHOLDER_BLOG, get_bloginfo('wpurl'), $href);
            ?>
            var href = "<?php echo esc_url($href); ?>";
            if(href == "%SWITCH%"){
            href = "<?php echo $this->sanitize_html(get_bloginfo('wpurl')); ?>";
            <?php if($this->context == "page"){
                ?>href+="/wp-admin";<?php
            }
            ?>
            }
            jQuery("li#wp-admin-bar-wp-logo a.ab-item").attr('href',href);

        <?php }?>
        <?php if(get_option('agca_site_heading')==true){ ?>
            jQuery("#wphead #site-heading").css("display","none");
        <?php } ?>
        <?php if(get_option('agca_custom_site_heading')!=""){ ?>
            jQuery("#wp-admin-bar-site-name a:first").html("<?php echo (htmlentities(get_option('agca_custom_site_heading'))); ?>");

        <?php } ?>
        <?php if(get_option('agca_header')==true && $this->context =='admin'){
            ?>
            jQuery("#wpadminbar").css("display","none");
            jQuery("body.admin-bar").css("padding-top","0");
            jQuery("#wphead").css("display","none");
            jQuery('html.wp-toolbar').css("padding-top","0");

        <?php } ?>
        <?php if((get_option('agca_header')==true)&&(get_option('agca_header_show_logout')==true)){ ?>
            <?php
            $agca_logout_text = ((get_option('agca_logout')=="")?__("Log Out", 'ag-custom-admin'):get_option('agca_logout'));
            ?>
            jQuery("#wpbody-content").prepend('<a href="<?php echo wp_logout_url(); ?>" tabindex="10" style="float:right;margin-right:20px" class="ab-item agca_logout_button"><?php echo $this->sanitize_html(strip_tags($agca_logout_text)); ?></a>');

        <?php } ?>
        <?php if(get_option('agca_logout')!=""){ ?>
            jQuery("ul#wp-admin-bar-user-actions li#wp-admin-bar-logout a").html("<?php echo $this->sanitize_html(strip_tags(get_option('agca_logout'))); ?>");
        <?php } ?>
        <?php if(get_option('agca_remove_your_profile')==true){ ?>
            jQuery("ul#wp-admin-bar-user-actions li#wp-admin-bar-edit-profile").css("visibility","hidden");
            jQuery("ul#wp-admin-bar-user-actions li#wp-admin-bar-edit-profile").css("height","10px");
            jQuery('#wpadminbar #wp-admin-bar-top-secondary > #wp-admin-bar-my-account > a').attr('href','#');
            jQuery('#wpadminbar #wp-admin-bar-top-secondary #wp-admin-bar-user-info > a').attr('href','#');
            jQuery('#wpadminbar #wp-admin-bar-top-secondary #wp-admin-bar-edit-profile > a').attr('href','#');
        <?php } ?>
        <?php if(get_option('agca_logout_only')==true){ ?>
            var logout_content = jQuery("li#wp-admin-bar-logout").html();
            jQuery("ul#wp-admin-bar-top-secondary").html('<li id="wp-admin-bar-logout" style="display:block;">'+ logout_content +'</li>');
            jQuery("#wp-admin-bar-logout a").css('padding','0 8px');


        <?php } ?>

        <?php


    }

    function wp_admin_bar_my_custom_account_menu( $wp_admin_bar ) {
        if(get_option('agca_howdy')!="" && !$this->isCusminActive()){
            $user_id = get_current_user_id();
            $current_user = wp_get_current_user();
            $profile_url = get_edit_profile_url( $user_id );

            if ( 0 != $user_id ) {
                /* Add the "My Account" menu */
                $avatar = get_avatar( $user_id, 28 );
                $howdy = sprintf( __(get_option('agca_howdy').', %1$s'), $current_user->display_name );
                $class = empty( $avatar ) ? '' : 'with-avatar';

                $wp_admin_bar->add_menu( array(
                    'id' => 'my-account',
                    'parent' => 'top-secondary',
                    'title' => htmlentities(strip_tags($howdy)) . $avatar,
                    'href' => $profile_url,
                    'meta' => array(
                        'class' => $class,
                    ),
                ) );

            }
        }
    }

    function updateAllColors(){

        ?>
        function updateAllColors(){
        <?php
        foreach((array) $this->colorizer as $k => $v){
            if(($k !="") and ($v !="")){
                ?> updateTargetColor("<?php echo $this->sanitize_html($k);?>","<?php echo $this->sanitize_html($v);?>"); <?php
            }
        }
        ?>
        jQuery('.color_picker').each(function(){
        updateColor(jQuery(this).attr('id'),jQuery(this).val())
        });
        jQuery('label,h1,h2,h3,h4,h5,h6,a,p,.form-table th,.form-wrap label').css('text-shadow','none');
        jQuery('#adminmenu li.wp-menu-open').css('border','none');
        jQuery('#adminmenu li.wp-menu-open .wp-submenu').css({'border':'none','margin':'0px','border-radius':'0px'});
        }<?php

    }
    function admin_capabilities(){
        global $wp_roles;
        $capabs = $wp_roles->roles['administrator']['capabilities'];
        $capabilitySelector = "";

        $selectedValue = get_option('agca_admin_capability');
        if($selectedValue == ""){
            $selectedValue = "edit_dashboard";
        }

        ksort($capabs);
        foreach($capabs as $k=>$v){
            $selected = "";
            if($this->startsWith($k, 'level_')) continue;
            if($selectedValue == $k){
                $selected = " selected=\"selected\" ";
            }
            $capabilitySelector .="<option value=\"". $this->sanitize_html($k) . "\" $selected >".$this->sanitize_html((ucwords(str_replace('_', ' ', $k))))."</option>\n";
        }

        $this->admin_capabilities  = "<select class=\"agca-selectbox\" id=\"agca_admin_capability\"  name=\"agca_admin_capability\" val=\"upload_files\" onchange='agca_show_affected_groups()'>".($capabilitySelector)."</select>";
    }

    function admin_capability(){
        $selectedValue = get_option('agca_admin_capability');
        if($selectedValue == ""){
            $selectedValue = "edit_dashboard";
        }
        return $selectedValue;
    }

    function enableSpecificWPVersionCustomizations($customizations){
        /*enable special CSS for this WP version*/
        $ver = $this->get_wp_version();
        $customizations = str_replace("/*".$ver," ", $customizations);
        $customizations = str_replace($ver."*/"," ", $customizations);
        return $customizations;
    }

    function removeCSSComments($customizations){
        $customizations = preg_replace('#/\*.*?\*/#si','',$customizations);
        return $customizations;
    }

    function getAGCAColor($name){
        if(isset($this->colorizer[$name])){
            echo $this->sanitize_html($this->colorizer[$name]);
        }
    }

    function agca_error_check(){
        ?>
        <script type="text/javascript">
            function AGCAErrorPage(msg, url, line){
                var agca_error_details = "___________________________________________________<br/>";
                agca_error_details += '<br/>' + msg +'<br/>source:' + url + '<br/>line:' + line + '<br/>';
                agca_error_details += "___________________________________________________<br/>";
                window.agca_error_details_text = agca_error_details + '<br/><?php _e('This JavaScript error can stop AGCA plugin to work properly. If everything still works, you can ignore this notification.', 'ag-custom-admin'); ?> <br/><br/><?php _e('Possible solutions', 'ag-custom-admin'); ?>:<br/><br/>1) <?php _e('Make sure to have everything up to date: WordPress site, plugins and themes.', 'ag-custom-admin'); ?><br/><br/>2) <?php _e('Try disabling plugins one by one to see if problem can be resolved this way. If so, one of disabled plugins caused this error.', 'ag-custom-admin'); ?><br/><br/>3) <?php _e('Check "source" path of this error. This could be indicator of the plugin/theme that caused the error.', 'ag-custom-admin'); ?><br/><br/>4) <?php _e('If it\\\'s obvious that error is thrown from a particular plugin/theme, please report this error to their support.', 'ag-custom-admin'); ?> <br/><br/>5) <?php _e('Try activating default WordPress theme instead of your current theme.', 'ag-custom-admin'); ?><br/><br/>6) <?php _e('Advanced: Try fixing this issue manually: Navigate to the link above in your browser and open the source of the page (right click -> view page source) and find the line in code where it fails. You should access this file via FTP and try to fix this error on that line.', 'ag-custom-admin') ?><br/><br/>7) <?php _e('Contact us if nothing above helps. Please do not post errors that are caused by other plugins/themes to our support page. Contact their support instead. If you think that error is somehow related to AGCA plugin, or something unexpected happens, please report that on our', 'ag-custom-admin'); ?> <a href="https://wordpress.org/support/plugin/ag-custom-admin/" target="_blank"><?php _e('SUPPORT PAGE', 'ag-custom-admin'); ?></a>';
                document.getElementsByTagName('html')[0].style.visibility = "visible";
                var errorDivHtml = '<div style="background: #f08080;border-radius: 3px;color: #ffffff;height: auto; margin-right: 13px;padding: 6px 14px;width: 450px;z-index: 99999; position:absolute;">\
                        <?php _e('AGCA plugin caught an error on your site!', 'ag-custom-admin'); ?>&nbsp;<a target="_blank" href="#" onclick="var aedt = document.getElementById(\'agca_error_details_text\'); if(aedt.style.display !== \'block\') {aedt.style.display = \'block\';} else{aedt.style.display = \'none\';} return false;"  style="color: #ffffff !important;float:right;font-weight: bold;text-decoration: none;">(<?php _e('show/hide more...', 'ag-custom-admin'); ?>)</a><div id="agca_error_details_text" style="display:none;margin: 10px 0;background:#ffffff;border-radius: 5px;padding:8px;color: #777;">'+agca_error_details_text+'</div></div>';

                var ph = document.getElementById('agca_error_placeholder');
                ph.innerHTML = errorDivHtml;
                document.getElementById('agca_news').style.visibility = "hidden";
            }
            window.onerror = function(msg, url, line) {
                window.onload = function() {
                    AGCAErrorPage(msg, url, line);
                }
                return true;
            };
        </script>
        <?php
    }

    function menu_item_cleartext($name){
        if(strpos($name,' <span') !== false){
            $parts = explode(' <span', $name);
            $name = $parts[0];
        }
        $name = trim($name);
        $name = strip_tags($name);
        $name = trim($name);
        return $name;
    }

    /**
     * Loops through all original menu items, and creates customizations array
     * applies previous customizations if set
     * @return array|mixed|object
     */
    function get_menu_customizations(){
        global $menu;
        global $submenu;

        $previousCustomizations = json_decode(get_option('ag_edit_adminmenu_json_new'), true);

        $customizationsSet = true;
        if($previousCustomizations == null){
            $customizationsSet = false;
        }

        //set default menu configuration
        //and apply previously saved customizations
        $m = array();
        foreach($menu as $top){
            $name = $top[0];
            $url = $top[2];
            $cls = isset($top[5])?$top[5]:"";
            $remove = false;
            if($name == '') continue;
            $pc = null;
            $name = htmlentities($this->menu_item_cleartext($name));

            //apply previous submenu customizations
            if($customizationsSet && isset($previousCustomizations[$url])){
                $pc = $previousCustomizations[$url];
            }

            //get submenu
            $s = array();
            if(isset($submenu[$url])){
                $sitems = $submenu[$url];
                foreach($sitems as $key=>$sub){
                    $nameSub = $sub[0];
                    $urlSub = htmlentities($sub[2]);
                    $removeSub = false;
                    $nameSub = htmlentities($this->menu_item_cleartext($nameSub));
                    $s[$key]=array(
                        'name'=>$nameSub,
                        'new'=>'',
                        'remove'=>$removeSub,
                        'url'=>htmlentities($urlSub)
                    );

                    if(isset($pc['submenus'][$key])){
                        $s[$key]['new'] = htmlentities($pc['submenus'][$key]['new']);
                        $s[$key]['remove'] = $pc['submenus'][$key]['remove'];

                        if($s[$key]['new'] == null){
                            $s[$key]['new'] = '';
                        }
                        if($s[$key]['remove'] == null){
                            $s[$key]['remove'] = false;
                        }
                    }
                }
            }

            $m[$url]=array(
                'name'=>$name,
                'remove'=>$remove,
                'new'=>'',
                'url'=>htmlentities($url),
                'cls'=>$cls,
                'submenus'=>$s
            );

            //apply previous top menu customizations
            if($customizationsSet && isset($previousCustomizations[$url])){
                $pc = $previousCustomizations[$url];
                if(isset($pc)){
                    $m[$url]['remove'] = $pc['remove'];
                    $m[$url]['new'] = htmlentities($pc['new']);
                }
            }
        }
        return $m;
    }

    /**
     * Applies customizations to admin menu
     */
    function customized_menu(){
        $customizations = $this->get_menu_customizations();

        global $menu;
        global $submenu;

        //apply customizations to original admin menu
        foreach($menu as $key=>$top){
            $url = $top[2];

            if(isset($customizations[$url])){
                $topCustomized = $customizations[$url];
                if($topCustomized['new']) {
                    $menu[$key][0] = $topCustomized['new'];
                }
                if($topCustomized['remove']){
                    unset($menu[$key]);
                }
            }
        }

        foreach($submenu as $topkey=>$subs){
            foreach($subs as $subkey=>$sub){
                if(isset($customizations[$topkey]['submenus'][$subkey])){
                    $cs = $customizations[$topkey]['submenus'][$subkey];
                    if($cs['new']) {
                        $submenu[$topkey][$subkey][0] = preg_replace("/".$cs['name']."/",$cs['new'], $submenu[$topkey][$subkey][0],1);
                    }
                    if($cs['remove']){
                        unset($submenu[$topkey][$subkey]);
                    }
                }
            }
        }
    }

    /**
     * Used only for removing admin menu customizations to AGCA 1.5 version or later
     * @param $checkboxes
     * @param $textboxes
     */
    function migrate_menu_customizations($checkboxes, $textboxes){
        $customizations = $this->get_menu_customizations();
        global $menu;

        $oldTopValue = "";

        //Migrate checkboxes
        foreach($checkboxes as $key=>$value){
            $isTop = false;
            $oldSubValue = "";
            if (strpos($key,'<-TOP->') !== false) {
                $oldTopValue = str_replace('<-TOP->','',$key);
                $isTop = true;
            }else{
                $oldSubValue = $key;
            }
            if($value == 'checked'){
                $topIndex = "";
                foreach($customizations as $k=>$c){
                    if($c['cls'] == $oldTopValue){
                        $topIndex = $k;
                        break;
                    }
                }
                if($topIndex == "") continue;
                if($isTop){
                    $customizations[$topIndex]['remove'] = true;
                }else{
                    if(is_array($customizations[$topIndex]['submenus'])){
                        foreach($customizations[$topIndex]['submenus'] as $skey=>$sval){
                            if($sval['name'] == $oldSubValue){
                                $customizations[$topIndex]['submenus'][$skey]['remove'] = true;
                            }
                        }
                    }
                }
            }
        }

        //Migrate textboxes
        foreach($textboxes as $key=>$value){
            $isTop = false;
            $oldSubValue = "";
            if (strpos($key,'<-TOP->') !== false) {
                $oldTopValue = str_replace('<-TOP->','',$key);
                $isTop = true;
            }else{
                $oldSubValue = $key;
            }
            if($value != ''){
                $topIndex = "";
                foreach($customizations as $k=>$c){
                    if($c['cls'] == $oldTopValue){
                        $topIndex = $k;
                        break;
                    }
                }
                if($topIndex == "") continue;
                if($isTop){
                    $customizations[$topIndex]['new'] = $value;
                }else{
                    if(is_array($customizations[$topIndex]['submenus'])){
                        foreach($customizations[$topIndex]['submenus'] as $skey=>$sval){
                            if($sval['name'] == $oldSubValue){
                                if($customizations[$topIndex]['submenus'][$skey]['name'] != $value){
                                    $customizations[$topIndex]['submenus'][$skey]['new'] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        update_option('ag_edit_adminmenu_json','');//remove previous admin menu configuration
        update_option('ag_edit_adminmenu_json_new',json_encode($customizations));
    }

    //Checks for Cusmin active status on back-end and front-end
    function isCusminActive(){
        //return $this->isPluginActive('cusmin/cusmin.php');
        try {
            if(!is_admin()) {
                //if cusmin option don't exist in DB, it's not active
                if(empty(get_option('cusmin'))) {
                    return false;
                }else { //if cusmin options exist, then include script to check if cusmin is active or not
                    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
            }
            if(!function_exists('is_plugin_active')) {
                return false;
            }
            return is_plugin_active('cusmin/cusmin.php');
        }catch (\Exception $e){
            return false;
        }
    }
    function isPluginActive($plugin){
        if(!is_admin()){
            return false;
        }
        return is_plugin_active($plugin);
    }
    function isPluginActiveForNetwork($plugin){
        return is_plugin_active_for_network($plugin);
    }

    function sanitize($content){
        return addslashes(wp_kses_post($content));
    }

    function sanitize_html($content){
        return htmlentities(wp_kses_post($content));
    }

    function print_admin_css()
    {
        global $wp_roles;
        $wpversion = $this->get_wp_version();
        $this->context = "admin";
        $currentScreen = get_current_screen();
        ?>
        <script type="text/javascript">
            var wpversion = "<?php echo $this->sanitize_html($wpversion); ?>";
            var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
            var agca_version = "<?php echo $this->sanitize_html($this->agca_version); ?>";
            var agca_wp_groups = <?php echo json_encode($wp_roles->roles); ?>;
            var agca_selected_capability = "<?php echo $this->sanitize_html($this->admin_capability()); ?>";
            var errors = false;
            var isSettingsImport = false;
            var isCusminActive = <?php echo $this->isCusminActive()?'true':'false'; ?>;
            var agca_context = "admin";
            var roundedSidberSize = 0;
            var agca_admin_menu = <?php echo json_encode($this->get_menu_customizations()); ?>;
            var agca_string = {
                file_imp_not_sel: '<?php _e('File for import is not selected!', 'ag-custom-admin'); ?>',
                menu_general: '<?php _e('General', 'ag-custom-admin'); ?>',
                menu_admin_bar: '<?php _e('Admin Bar', 'ag-custom-admin'); ?>',
                menu_footer: '<?php _e('Footer', 'ag-custom-admin'); ?>',
                menu_dashb: '<?php _e('Dashboard', 'ag-custom-admin'); ?>',
                menu_login: '<?php _e('Login Page', 'ag-custom-admin'); ?>',
                menu_admin_menu: '<?php _e('Admin Menu', 'ag-custom-admin'); ?>',
                menu_colorizer: '<?php _e('Colorizer', 'ag-custom-admin'); ?>',
                menu_upgrade: '<?php _e('Upgrade', 'ag-custom-admin'); ?>',
                menu_advanced: '<?php _e('Advanced', 'ag-custom-admin'); ?>',
                remove: '<?php _e('Remove', 'ag-custom-admin'); ?>',
                frommenu:'<?php _e('from menu', 'ag-custom-admin'); ?>',
                rename:'<?php _e('Rename', 'ag-custom-admin'); ?>',
                withthisvalue:'<?php _e('with this value', 'ag-custom-admin'); ?>',
                submenuitem:'<?php _e('sub-menu item', 'ag-custom-admin'); ?>',
                open:'<?php _e('open', 'ag-custom-admin'); ?>',
                'delete':'<?php _e('Delete', 'ag-custom-admin'); ?>',
                'in':'<?php _e('in', 'ag-custom-admin'); ?>',
                save_changes: '<?php _e('Save changes', 'ag-custom-admin'); ?>'
            };
        </script>
        <?php
        $this->agca_get_includes();
        $this->admin_capabilities();
        wp_get_current_user() ;
        ?>
        <?php
        //in case that javaScript is disabled only admin can access admin menu
        if(!current_user_can($this->admin_capability())){
            ?>
            <style type="text/css">
                #adminmenu{display:none;}
            </style>
            <?php
        }
        ?>
        <script type="text/javascript">
            document.write('<style type="text/css">html{visibility:hidden;}</style>');
            <?php
            if(isset($_POST['_agca_import_settings']) && $_POST['_agca_import_settings']=='true'){
                echo 'isSettingsImport = true;';
            }
            ?>
        </script>
        <?php if(get_option('agca_admin_menu_arrow') == true){ ?>
        <style type="text/css">
            .wp-has-current-submenu:after{border:none !important;}
            #adminmenu li.wp-has-submenu.wp-not-current-submenu.opensub:hover:after{border:none !important;}
        </style>
    <?php }

        if($currentScreen->id == 'tools_page_ag-custom-admin/plugin'){
            ?>
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=765552763482314";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
            <?php
        }

        if(get_option('agca_menu_remove_client_profile')){
            remove_menu_page('profile.php');
        }
        ?>
        <script type="text/javascript">
            /* <![CDATA[ */
            jQuery(document).ready(function() {
                try
                {

                    <?php /*CHECK OTHER PLUGINS*/
                        if($this->isPluginActive('ozh-admin-drop-down-menu/wp_ozh_adminmenu.php')){
                            ?>
                    jQuery('ul#adminmenu').css('display','none');
                    jQuery('#footer-ozh-oam').css('display','none');
                    jQuery('#ag_main_menu li').each(function(){
                        if(jQuery(this).text() == "Admin Menu"){
                            jQuery(this).hide();
                        }
                    });
                    <?php
                }
            ?>


                    //get saved configurations

                    <?php   $buttons = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttons'); ?>
                    var buttons = '<?php echo $this->sanitize($buttons); ?>';

                    <?php   $buttonsJq = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttonsJq'); ?>
                    var buttonsJq = '<?php echo $this->sanitize($buttonsJq); ?>';

                    createEditMenuPageNew(agca_admin_menu);
                    //createEditMenuPageV32(checkboxes, textboxes);

                    <?php
                    //if admin, and option to hide settings for admin is set
                    if((get_option('agca_role_allbutadmin')==true) and current_user_can($this->admin_capability()) or $this->isCusminActive()){
                    ?>
                    <?php } else{ ?>
                    <?php if(get_option('agca_admin_menu_brand')!="" && $this->is_safe_remote_image(get_option('agca_admin_menu_brand'))){ ?>
                    additionalStyles = ' style="margin-bottom:-4px" ';
                    jQuery("#adminmenu").before('<div '+additionalStyles+' id="sidebar_adminmenu_logo"><img width="160" src="<?php echo $this->sanitize_html(get_option('agca_admin_menu_brand')); ?>" /></div>');
                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_brand_link')!=""){ ?>
                    <?php
                    $href = get_option('agca_admin_menu_brand_link');
                    $href = str_replace(self::PLACEHOLDER_BLOG, get_bloginfo('wpurl'), $href);
                    ?>
                    var href = "<?php echo esc_url($href); ?>";

                    jQuery("#sidebar_adminmenu_logo").attr('onclick','window.open(\"'+ href+ '\");');
                    jQuery("#sidebar_adminmenu_logo").attr('title',href);

                    <?php }else{ ?>
                    href = "<?php echo $this->sanitize_html(get_bloginfo('wpurl')); ?>";
                    jQuery("#sidebar_adminmenu_logo").attr('onclick','window.open(\"'+ href+ '\");');
                    jQuery("#sidebar_adminmenu_logo").attr('title',href);
                    <?php } ?>

                    <?php if(get_option('agca_admin_menu_submenu_round')==true){ ?>
                    jQuery("#adminmenu .wp-submenu").css("border-radius","<?php echo intval($this->sanitize_html(get_option('agca_admin_menu_submenu_round_size'))); ?>px");
                    jQuery("#adminmenu .wp-menu-open .wp-submenu").css('border-radius','');
                    <?php $roundedSidebarSize = $this->sanitize_html(get_option('agca_admin_menu_submenu_round_size')); ?>
                    roundedSidberSize = <?php echo intval(($roundedSidebarSize == "") ? "0" : $roundedSidebarSize); ?>;


                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_autofold')=="force"){ ?>
                    jQuery("body").addClass("auto-fold");
                    <?php } else if(get_option('agca_admin_menu_autofold')=="disable"){ ?>
                    jQuery("body").removeClass("auto-fold");
                    <?php } ?>

                    <?php $this->print_admin_bar_scripts(); ?>

                    <?php if(get_option('agca_screen_options_menu')==true){ ?>
                    jQuery("#screen-options-link-wrap").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_help_menu')==true){ ?>
                    jQuery("#contextual-help-link-wrap").css("display","none");
                    jQuery("#contextual-help-link").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_privacy_options')==true){ ?>
                    jQuery("#privacy-on-link").css("display","none");
                    <?php } ?>

                    <?php if(get_option('agca_update_bar')==true){ ?>
                    <?php
                    if ( ! function_exists( 'c2c_no_update_nag' ) ) :
                    function c2c_no_update_nag() {
                        remove_action( 'admin_notices', 'update_nag', 3 );
                    }
                    endif;
                    add_action( 'admin_init', 'c2c_no_update_nag' );
                    ?>
                    jQuery("#update-nag").css("display","none");
                    jQuery(".update-nag").css("display","none");
                    <?php } ?>

                    <?php if(get_option('agca_footer')==true){ ?>
                    jQuery("#footer,#wpfooter").css("display","none");
                    <?php } ?>

                    <?php if(get_option('agca_footer_left')!=""){ ?>
                    jQuery("#footer-left").html('<?php echo $this->sanitize(get_option('agca_footer_left')); ?>');
                    <?php } ?>
                    <?php if(get_option('agca_footer_left_hide')==true){ ?>
                    jQuery("#footer-left").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_footer_right')!=""){ ?>
                    jQuery("#footer-upgrade").html('<?php echo $this->sanitize(get_option('agca_footer_right')); ?>');
                    <?php } ?>
                    <?php if(get_option('agca_footer_right_hide')==true){ ?>
                    jQuery("#footer-upgrade").css("display","none");
                    <?php } ?>

                    <?php if(get_option('agca_language_bar')==true){ ?>
                    jQuery("#user_info p").append('<?php include("language_bar/language_bar.php"); ?>');
                    <?php } ?>
                    <?php if(get_option('agca_dashboard_icon')==true){ ?>
                    var className = jQuery("#icon-index").attr("class");
                    if(className=='icon32'){
                        jQuery("#icon-index").attr("id","icon-index-removed");
                    }
                    <?php } ?>
                    <?php if(get_option('agca_dashboard_text')!=""){ ?>
                    jQuery("#dashboard-widgets-wrap").parent().find("h1").html("<?php echo $this->sanitize(get_option('agca_dashboard_text')); ?>");
                    <?php } ?>
                    <?php if(get_option('agca_dashboard_text_paragraph')!=""){
                                                        require_once($this->filePath('wp-includes/formatting.php'));
                                        ?>
                    jQuery("#wpbody-content #dashboard-widgets-wrap").before('<div id="agca_custom_dashboard_content"></div>');

                    jQuery("#agca_custom_dashboard_content").html('<br /><?php echo preg_replace('/(\r\n|\r|\n)/', '\n', $this->sanitize(wpautop(do_shortcode(get_option('agca_dashboard_text_paragraph'))))); ?>');
                    <?php } ?>

                    <?php /*Remove Dashboard widgets*/ ?>
                    <?php
                        ?>
                        jQuery('.welcome-panel-close').click(function(){
                          setTimeout(function(){
                            jQuery("#welcome-panel").removeAttr('style')
                          }, 0)
                        });
                        <?php
                        if(get_option('agca_dashboard_widget_welcome')==true){
                            ?>jQuery("#welcome-panel").css("display","none");<?php
                        }else{
                            ?> jQuery("#welcome-panel:not(.hidden)").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_health_status')==true){
                            $this->remove_dashboard_widget('dashboard_site_health','normal');
                        }
                        if(get_option('agca_dashboard_widget_il')==true){
                            $this->remove_dashboard_widget('dashboard_incoming_links','normal');
                        }else{
                            ?>jQuery("#dashboard_incoming_links").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_plugins')==true){
                            $this->remove_dashboard_widget('dashboard_plugins','normal');
                        }else{
                            ?>jQuery("#dashboard_plugins").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_qp')==true){
                            $this->remove_dashboard_widget('dashboard_quick_press','side');
                        }else{
                            ?>jQuery("#dashboard_quick_press").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_rn')==true){
                            $this->remove_dashboard_widget('dashboard_right_now','normal');
                        }else{
                            ?>jQuery("#dashboard_right_now").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_rd')==true){
                            $this->remove_dashboard_widget('dashboard_recent_drafts','side');
                        }else{
                            ?>jQuery("#dashboard_recent_drafts").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_primary')==true){
                            $this->remove_dashboard_widget('dashboard_primary','side');
                        }else{
                            ?>jQuery("#dashboard_primary").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_secondary')==true){
                            $this->remove_dashboard_widget('dashboard_secondary','side');
                        }else{
                            ?>jQuery("#dashboard_secondary").css("display","block");<?php
                        }
                        if(get_option('agca_dashboard_widget_activity')==true){
                            remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
                        }else{
                            ?>jQuery("#dashboard_activity").css("display","block");<?php
                        }

                    ?>

                    <?php /*ADMIN MENU*/ ?>

                    <?php if(get_option('agca_admin_menu_separators')==true){ ?>
                    jQuery("#adminmenu li.wp-menu-separator").css({height: 0, margin: 0});
                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_icons') == true){ ?>
                    jQuery(".wp-menu-image").each(function(){
                        jQuery(this).css("display","none");
                    });
                    jQuery('#adminmenu div.wp-menu-name').css('padding','8px');
                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_arrow') == true){ ?>
                    jQuery("#adminmenu .wp-menu-arrow").css("visibility","hidden");

                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_turnonoff') == 'on'){ ?>

                    <?php /*If Turned on*/ ?>

                    <?php if(get_option('agca_admin_menu_agca_button_only') == true){ ?>
                    jQuery('#adminmenu > li').each(function(){
                        if(!jQuery(this).hasClass('agca_button_only')){
                            jQuery(this).addClass('noclass');
                        }
                    });
                    <?php /*Only admin see button*/
                       if (current_user_can($this->admin_capability())){ ?>
                    jQuery('#adminmenu').append('<?php echo $this->agca_create_admin_button('AGCA',array('value'=>'tools.php?page=ag-custom-admin/plugin.php#general-settings','target'=>'_self')); ?>');
                    <?php } ?>
                    <?php } ?>


                    <?php /*EDIT MENU ITEMS*/?>
                    <?php if(get_option('ag_edit_adminmenu_json')!=""){

                      $arr = explode("|",get_option('ag_edit_adminmenu_json'));

                      $checkboxes = json_decode($arr[0]);
                      $textboxes = json_decode($arr[1]);

                      $this->migrate_menu_customizations($checkboxes, $textboxes);

                     } ?>

                    <?php if(get_option('ag_edit_adminmenu_json_new')!=""){
                          $this->customized_menu();
                     } ?>




                    /*Add user buttons*/
                    jQuery('#adminmenu').append(buttons);

                    <?php /*END If Turned on*/ ?>
                    <?php } else{ ?>
                    jQuery("#adminmenu").removeClass("noclass");
                    <?php } ?>

                    reloadRemoveButtonEvents();

                    <?php if(get_option('agca_admin_menu_collapse_button') == true){ ?>
                    //remove collapse menu button
                    jQuery('#collapse-menu').remove();
                    <?php } ?>

                    <?php //COLORIZER ?>
                    updateAllColors();
                    <?php //COLORIZER END ?>
                    <?php } //end of apply for any user except admin ?>
                    /*Add user buttons*/
                    jQuery('#ag_add_adminmenu').append(buttonsJq);


                }catch(err){
                    errors = "AGCA - ADMIN ERROR: " + err.name + " / " + err.message;
                    console.log(errors);
                }finally{
                    jQuery('html').css('visibility','visible');
                    jQuery('head').append('<style>html{visibility: visible !important;}</style>');
                }
                <?php
                if($this->saveAfterImport == true){
                    ?>savePluginSettings();<?php
 }
 ?>

            });

            <?php if(get_option('agca_colorizer_turnonoff') == 'on'){
               $this->updateAllColors();
             }else{
               ?>function updateAllColors(){}; <?php
    }  ?>


            /* ]]> */
        </script>
        <style type="text/css">
            .underline_text{
                text-decoration:underline;
            }
            .form-table th{
                width:300px;
            }

            #dashboard-widgets div.empty-container{
                border:none;
            }
        </style>
        <?php
    }

    function print_login_head(){
        if($this->isCusminActive()){
            return false;
        }

        $this->context = "login";

        ?>
        <script type="text/javascript">
            document.write('<style type="text/css">html{visibility:hidden;}</style>');
            var agca_version = "<?php echo $this->sanitize_html($this->agca_version); ?>";
            <?php //var wpversion = "echo $wpversion; ?>
            var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
            var isCusminActive = <?php echo $this->isCusminActive()?'true':'false'; ?>;
            var isSettingsImport = false;
            var agca_context = "login";
        </script>
        <?php
        $this->agca_get_includes();

        ?>

        <script type="text/javascript">

            /* <![CDATA[ */
            window.onerror = function(msg, url, line) {
                document.getElementsByTagName('html')[0].style.visibility = "visible";
            };
            jQuery(document).ready(function() {
                try{
                    <?php if(get_option('agca_login_round_box')==true){ ?>
                    jQuery("form#loginform").css("border-radius","<?php echo intval($this->sanitize_html(get_option('agca_login_round_box_size'))); ?>px");
                        <?php if(!get_option('agca_login_round_box_skip_logo')){ ?>
                        jQuery("#login h1 a").css("border-radius","<?php echo intval($this->sanitize_html(get_option('agca_login_round_box_size'))); ?>px");
                        jQuery("#login h1 a").css("margin-bottom",'10px');
                        jQuery("#login h1 a").css("padding-bottom",'0');
                        <?php } ?>
                    jQuery("form#lostpasswordform").css("border-radius","<?php echo intval($this->sanitize_html(get_option('agca_login_round_box_size'))); ?>px");
                    <?php } ?>
                    <?php if(get_option('agca_login_banner')==true){ ?>
                    jQuery("#backtoblog").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_login_banner_text')==true){ ?>
                    jQuery("#backtoblog a").html('<?php echo "← " . $this->sanitize_html(strip_tags(get_option('agca_login_banner_text'))); ?>');
                    <?php } ?>
                    <?php if(
                            get_option('agca_login_photo_url')==true &&
                            get_option('agca_login_photo_remove')!=true &&
                            $this->is_safe_remote_image(get_option('agca_login_photo_url'))
                    ){ ?>
                    advanced_url = "<?php echo $this->sanitize_html(get_option('agca_login_photo_url')); ?>";
                    var $url = "url(" + advanced_url + ")";
                    var $a = jQuery("#login h1 a");
                    $a.css("background",$url+' no-repeat');
                    $a.hide();
                    image = jQuery("<img />").attr("src",advanced_url);
                    jQuery(image).on('load', function() {
                        var originalWidth = 326;
                        var widthDiff = this.width - originalWidth;
                        $a.height(this.height)
                          .width(this.width)
                          .css("background-size",this.width+"px "+this.height+"px")
                          .css("text-indent", "-9999px")
                          .css("font-size", 0);


                        var loginWidth = jQuery('#login').width();
                        var originalLoginWidth = 320;
                        var photoWidth = this.width;

                        if(loginWidth > photoWidth){
                            $a.css('margin','auto');
                        }else{
                            $a.css('margin-left',-(widthDiff/2)+((loginWidth-originalLoginWidth)/2)+"px");
                        }

                        $a.show();
                    });
                    <?php } ?>
                    <?php if(get_option('agca_login_photo_href')==true){ ?>
                    var $href = "<?php echo $this->sanitize_html(get_option('agca_login_photo_href')); ?>";
                    $href = $href.replace("<?php echo self::PLACEHOLDER_BLOG ?>", "<?php echo $this->sanitize_html(get_bloginfo('wpurl')); ?>");

                    jQuery("#login h1 a").attr("href",$href);
                    <?php } ?>
                    <?php if(get_option('agca_login_photo_remove')==true){ ?>
                    jQuery("#login h1 a").css("display","none");
                    <?php } ?>

                    jQuery("#login h1 a").attr("title","");

                    <?php if(get_option('agca_login_register_remove')==true){ ?>
                    if(jQuery('p#nav').size() > 0){
                        jQuery('p#nav').html(jQuery('p#nav').html().replace('|',''));
                    }
                    jQuery('p#nav a').each(function(){
                        if(jQuery(this).attr('href').indexOf('register') != -1){
                            jQuery(this).remove();
                        }
                    });

                    <?php } ?>
                    <?php if(get_option('agca_login_register_href')!=""){ ?>
                    jQuery('p#nav a').each(function(){
                        if(jQuery(this).attr('href').indexOf('register') != -1){
                            jQuery(this).attr('href','<?php echo $this->sanitize_html(get_option('agca_login_register_href')); ?>');
                        }
                    });

                    <?php } ?>

                    <?php if(get_option('agca_login_lostpassword_remove')==true){ ?>
                    if(jQuery('p#nav').size() > 0){
                        jQuery('p#nav').html(jQuery('p#nav').html().replace('|',''));
                    }
                    jQuery('p#nav a').each(function(){
                        if(jQuery(this).attr('href').indexOf('lostpassword') != -1){
                            jQuery(this).remove();
                        }
                    });

                    <?php } ?>


                    <?php //COLORIZER ?>
                    <?php if(get_option('agca_colorizer_turnonoff') == 'on'){ ?>
                    jQuery('label,h1,h2,h3,h4,h5,h6,a,p,.form-table th,.form-wrap label').css('text-shadow','none');
                    jQuery("body.login, html").css("background","<?php echo $this->sanitize_html($this->colorizer['login_color_background']);?>");


                    <?php


                     } ?>
                    <?php //COLORIZER END ?>
                }catch(err){
                    console.log("AGCA - LOGIN ERROR: " + err.name + " / " + err.message);
                }finally{
                    jQuery('html').show();
                    jQuery('html').css('visibility','visible');
                }
            });
            /* ]]> */

        </script>
        <?php
    }

    function pluginUrl(){
        return trailingslashit(plugin_dir_url( __FILE__ ));
    }

    function agca_admin_page() {

        $wpversion = $this->get_wp_version();
        $this->agca_error_check();
        ?>
        <div class="wrap">
            <div id="agca-header">
                <h1 id="agca-title"><img src="<?php echo plugins_url( 'images/agca-logo.svg', __FILE__ ) ?>" /><span class="title">absolutely glamorous custom admin</span> <span class="version">(v<?php echo $this->sanitize_html($this->agca_version); ?>)</span></h1>
                <div id="agca-social">
                    <div class="fb-like" data-href="https://www.facebook.com/AG-Custom-Admin-892218404232342/timeline" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=agca@cusmin.com&amount=10&item_name=Support+for+AGCA+Development" target="_blank" class="agca-donate-btn" title="Please help us ensure future updates for AGCA. <br/><br/>We can't make them without your support.">Donate <span class="heart">❤</span></a>
                </div>
            </div>
            <div id="agca_error_placeholder"></div>
            <div id="agca_news">&nbsp;</div><br />
            <form method="post" id="agca_form" action="options.php">
                <?php wp_nonce_field('agca_form','_agca_token'); ?>
                <?php settings_fields( 'agca-options-group' ); ?>
                <?php if(get_option('agca_check_js_notice') || (!get_option('agca_check_js_notice') && current_user_can('unfiltered_html'))) { ?>
                    <input type="hidden" name="agca_check_js_notice" value="true" />
                <?php } ?>
                <div id="agca-your-feedback">
                    <strong>
                        <span style="color:#005B69">Your feedback:</span>
                    </strong>
                    <a class="feedback positive" target="_blank" title="<?php _e('POSITIVE FEEDBACK: I like this plugin!', 'ag-custom-admin'); ?>" href="https://wordpress.org/support/plugin/ag-custom-admin/reviews/">
                        <span class="dashicons dashicons-thumbs-up"></span>
                    </a>
                    <a class="feedback negative" target="_blank" title="<?php _e('NEGATIVE FEEDBACK: I don\'t like this plugin.', 'ag-custom-admin'); ?>" href="https://wordpress.org/support/plugin/ag-custom-admin/reviews/">
                        <span class="dashicons dashicons-thumbs-down"></span>
                    </a>
                </div>
                <br />
                <ul id="ag_main_menu" style="<?php echo $this->isCusminActive()?'display:none':''; ?>">
                    <li class="selected" style="border-top-left-radius: 10px; "><a href="#general-settings" class="dashicons-before dashicons-admin-tools" title="<?php _e('General Settings', 'ag-custom-admin')?>" ><?php echo _e('General', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#admin-bar-settings" class="dashicons-before dashicons-welcome-widgets-menus" title="<?php _e('Settings for admin bar', 'ag-custom-admin')?>" ><?php _e('Admin Bar', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#admin-footer-settings" class="dashicons-before dashicons-welcome-widgets-menus agca-invert-icon" title="<?php _e('Settings for admin footer', 'ag-custom-admin')?>" ><?php _e('Footer', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#dashboad-page-settings" class="dashicons-before dashicons-dashboard" title="<?php _e('Settings for Dashboard page', 'ag-custom-admin')?>"><?php _e('Dashboard', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#login-page-settings" class="dashicons-before dashicons-admin-network" title="<?php _e('Settings for Login page', 'ag-custom-admin')?>"><?php _e('Login Page', 'ag-custom-admin')?></a></li>
                    <li class="normal" ><a href="#admin-menu-settings" class="dashicons-before dashicons-editor-justify" title="<?php _e('Settings for main admin menu', 'ag-custom-admin')?>"><?php _e('Admin Menu', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#ag-colorizer-settings" class="dashicons-before dashicons-admin-appearance agca-invert-icon" title="<?php _e('Colorizer settings', 'ag-custom-admin')?>"><?php _e('Colorizer', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#ag-advanced" class="dashicons-before dashicons-welcome-learn-more" title="<?php _e('My custom scripts', 'ag-custom-admin')?>"><?php _e('Advanced', 'ag-custom-admin')?></a></li>
                    <li class="normal upgrade"><a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-main-menu" target="_blank" title="<?php _e('Upgrade to Cusmin </br>to unlock all premium features', 'ag-custom-admin')?>"><img src="<?php echo plugins_url( 'images/cusmin-logo.svg', __FILE__ ) ?>" /><?php _e('Upgrade', 'ag-custom-admin')?></a></li>

                    <li style="background:none;border:none;padding:0;"><a id="agca_donate_button" target="_blank" style="margin-left:8px" title="<?php _e('Enjoying AGCA? Help us further develop it and support it!', 'ag-custom-admin')?> " href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=agca@cusmin.com&amount=10&item_name=Support+for+AGCA+Development"><img alt="<?php _e('Donate', 'ag-custom-admin')?>" src="<?php echo $this->pluginUrl(); ?>images/donate-btn.png" /></a>
                    </li>
                    <li style="background:none;border:none;padding:0;padding-left:10px;margin-top:-7px"></li>
                </ul>
                <a class="agca_advertising" href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-ad" target="_blank">
                    <img class="cusmin-logo" src="<?php echo plugins_url( 'images/cusmin-logo.svg', __FILE__ ) ?>" alt="Cusmin" />
                    <ul>
                        <li><?php _e('Multiple sets of settings', 'ag-custom-admin') ?></li>
                        <li><?php _e('Reuse settings on other sites', 'ag-custom-admin') ?></li>
                        <li><?php _e('Drag & drop to reorder', 'ag-custom-admin') ?></li>
                        <li><?php _e('Advanced admin menu manager', 'ag-custom-admin') ?></li>
                        <li><?php _e('Advanced admin bar manager', 'ag-custom-admin') ?></li>
                        <li><?php _e('Custom admin menu icons', 'ag-custom-admin') ?></li>
                        <li><?php _e('Custom dashboard widgets', 'ag-custom-admin') ?></li>
                        <li><?php _e('Create custom admin pages', 'ag-custom-admin') ?></li>
                    </ul>
                    <span class="unlock-features">Unlock all features</span>
                </a>
                <div class="agca-clear"></div>
                <div id="section-cusmin" style="display:none;"><?php _e('All AGCA plugin\'s settings are disabled. Please use', 'ag-custom-admin')?> <a href="options-general.php?page=cusmin">Cusmin</a> <?php _e('plugin to manage these settings.', 'ag-custom-admin')?></div>
                <div id="section_general" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('General Settings', 'ag-custom-admin')?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <p tabindex="0" class="agca-clear agca-tip"><i><?php _e('<strong>Tip: </strong>Move the cursor over an option label to see the more information about the option', 'ag-custom-admin')?></i></p>
                    <table class="agca-clear form-table" width="500px">
                        <?php

                        $this->print_checkbox(array(
                            'name'=>'agca_role_allbutadmin',
                            'label'=>__('Exclude the AGCA safe users from the customizations', 'ag-custom-admin'),
                            'title'=>__('<h3>Applying customizations</h3><br><strong>Checked</strong> - apply to all users, except AGCA safe users<br><strong>Not checked</strong> - apply to everyone</br></br><strong>Q</strong>: Who are AGCA safe users?</br><strong>A</strong>: Go to <i>General -> AGCA Safe Users -> AGCA safe users capability</i> and change capability option to define AGCA safe users. Only the users with selected capability will be AGCA safe users.</br>', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('AGCA Safe Users', 'ag-custom-admin'));

                        ?>

                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Choose which WordPress capability will be used to distinguish AGCA safe users from other users.</br>AGCA safe users can be excluded from customizations if that option is checked', 'ag-custom-admin'); ?>" for="agca_admin_capability"><?php _e('AGCA safe users capability', 'ag-custom-admin'); ?>:</label>
                            </th>
                            <td><?php echo ($this->admin_capabilities); ?>&nbsp;&nbsp;<i>(<?php _e('default:&nbsp;<strong>Edit Dashboard</strong>', 'ag-custom-admin'); ?>)</i>
                                <p style="margin-left:5px;"><i><?php _e('Find more information about', 'ag-custom-admin'); ?> <a href="https://wordpress.org/support/article/roles-and-capabilities/" target="_blank"><?php _e('WordPress capabilities', 'ag-custom-admin'); ?></a></i></p>
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr valign="center">
                            <td>
                                <p>These groups are AGCA safe users:</p>
                            </td>
                            <td>
                                <div id="agca-affected-roles">Loading...</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Need to apply multiple configurations to multiple user groups/roles? <a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-configs#group-customizations" target="_blank">Upgrade to Cusmin</a></p>
                            </td>
                        </tr>
                        <?php

                        $this->print_options_h3(__('Pages', 'ag-custom-admin'));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_screen_options_menu',
                            'label'=>__('Hide <b>Screen Options</b> menu', 'ag-custom-admin'),
                            'title'=>__('Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_help_menu',
                            'label'=>__('Hide <b>Help</b> menu', 'ag-custom-admin'),
                            'title'=>__('Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Hides colors scheme on profile page', 'ag-custom-admin'),
                            'name'=>'agca_profile_color_scheme',
                            'hide'=>true,
                            'label'=>__('Hide User\'s Profile Color Scheme', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Feedback and Support', 'ag-custom-admin'));

                        ?>
                        <tr valign="center">
                            <td colspan="2">
                                <div class="agca-feedback-and-support">
                                    <?php /*<ul>
                                        <li><a href="" target="_blank"><span class="dashicons dashicons-lightbulb"></span>&nbsp;&nbsp;<?php _e('Idea for improvement', 'ag-custom-admin'); ?></a> - <?php _e('submit your idea for improvement', 'ag-custom-admin'); ?> </li>
                                    </ul>*/
                                    ?>
                                    <ul>
                                        <li><a href="https://wordpress.org/support/plugin/ag-custom-admin" target="_blank"><span class="dashicons dashicons-megaphone"></span>&nbsp;&nbsp;<?php _e('Report an issue', 'ag-custom-admin'); ?></a> - <?php _e('If plugin does not work as expected', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="https://wordpress.org/support/view/plugin-reviews/ag-custom-admin" target="_blank"><span class="dashicons dashicons-star-filled"></span>&nbsp;&nbsp;<?php _e('Add your review on WordPress.org', 'ag-custom-admin'); ?></a> - <?php _e('add your review and rate us on WordPress.org', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=agca@cusmin.com&amount=10&item_name=Support+for+AGCA+Development" target="_blank"><span class="dashicons dashicons-heart"></span>&nbsp;&nbsp;<?php _e('Donate $10', 'ag-custom-admin'); ?></a> - <?php _e('to give your thanks for our hard work', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul class="upgrade">
                                        <li><a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-general" target="_blank">
                                                <img class="cusmin-spinner" src="<?php echo plugins_url( 'images/cusmin-logo.svg', __FILE__ ) ?>" />
                                                &nbsp;&nbsp;<span><?php _e('Upgrade to Cusmin', 'ag-custom-admin'); ?></span></a><span><?php _e('&nbsp;- unlock the ultimate branding experience', 'ag-custom-admin'); ?></span></li>
                                    </ul>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div id="section_admin_bar" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Admin Bar Settings', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">

                        <?php
                        $this->print_checkbox(array(
                            'attributes'=>array(
                                'class'=>'ag_table_major_options',
                            ),
                            'hide'=>true,
                            'title'=>__('Hides the admin bar completely from the admin panel', 'ag-custom-admin'),
                            'name'=>'agca_header',
                            'label'=>__('<strong>Hide Admin bar</strong>', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant=\'#agca_header_show_logout_content\'',
                            'input-class'=>'has-dependant',
                        ));

                        $this->print_checkbox(array(
                            'attributes'=>array(
                                'class'=>'ag_table_major_options',
                                'style'=> ((get_option('agca_header')!='true')?'display:none':''),
                                'id'=>'agca_header_show_logout_content',
                            ),
                            'title'=>__('Check this if you want to show Log Out button in top right corner of the admin page', 'ag-custom-admin'),
                            'name'=>'agca_header_show_logout',
                            'checked'=> ((get_option('agca_header')==true) && (get_option('agca_header_show_logout')==true)),
                            'label'=>__('<strong>(but show Log Out button)</strong>', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Removes admin bar customizations for authenticated users on site pages.</br>This option can be useful if you want to remove AGCA scripts (styles, JavaScript) on your website for any reason.', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_frontend',
                            'hide'=>true,
                            'label'=>__('Site pages: Do not apply Admin bar customizations', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Hides admin bar completely for authenticated users on site pages.', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_frontend_hide',
                            'hide'=>true,
                            'label'=>__('Site pages: Hide Admin bar', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Left Side', 'ag-custom-admin'));

                        $this->print_input(array(
                            'title'=>__('Change default WordPress logo with custom image.', 'ag-custom-admin'),
                            'name'=>'agca_wp_logo_custom',
                            'label'=>__('Admin bar logo', 'ag-custom-admin'),
                            'hint' =>__('Image URL', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Custom link on admin bar logo.', 'ag-custom-admin').'</br></br>Use:</br><strong>%BLOG%</strong> - '.__('for blog URL.', 'ag-custom-admin').'</br><strong>%SWITCH%</strong> - '.__('to switch betweent admin and site area', 'ag-custom-admin'),
                            'name'=>'agca_wp_logo_custom_link',
                            'label'=>__('Admin bar logo link', 'ag-custom-admin'),
                            'hint' =>__('Link', 'ag-custom-admin'),
                            'link' => true
                        ));

                        $this->print_input(array(
                            'title'=>__('Customize WordPress title using custom title template.</br></br>Examples', 'ag-custom-admin').':</br><strong>%BLOG% -- %PAGE%</strong>  '.'('.__('will be', 'ag-custom-admin').')'.' <i>My Blog -- Add New Post</i></br><strong>%BLOG%</strong> ('.__('will be', 'ag-custom-admin').') <i>My Blog</i></br><strong>My Company > %BLOG% > %PAGE%</strong> ('.__('will be', 'ag-custom-admin').') <i>My Company > My Blog > Tools</i>',
                            'name'=>'agca_custom_title',
                            'label'=>__('Page title template', 'ag-custom-admin'),
                            'hint' =>__('Please use', 'ag-custom-admin').' <strong>%BLOG%</strong> '.__('and', 'ag-custom-admin'). ' <strong>%PAGE%</strong> '.__('in your title template.', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Add custom image on the top of the admin content.', 'ag-custom-admin'),
                            'name'=>'agca_header_logo_custom',
                            'label'=>__('Header image', 'ag-custom-admin'),
                            'hint' =>__('Image URL', 'ag-custom-admin')
                        ));


                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides small Wordpress logo or custom logo from the admin bar', 'ag-custom-admin'),
                            'name'=>'agca_header_logo',
                            'label'=>__('Hide Admin bar logo', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides WordPress context menu on admin bar logo from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_remove_top_bar_dropdowns',
                            'label'=>__('Hide Admin bar logo context menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides site name link from the admin bar', 'ag-custom-admin'),
                            'name'=>'agca_remove_site_link',
                            'label'=>__('Hide Site name', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides update notifications from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_update_notifications',
                            'label'=>__('Remove update notifications', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides comments block from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_comments',
                            'label'=>__('Hide <b>Comments</b> block', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'style'=>'margin-top:20px;'
                            ),
                            'title'=>__('Hides <b>+ New</b> block and its context menu from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content',
                            'label'=>__('Hide <b>+ New</b> block', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant=\'.new_content_header_submenu\'',
                            'input-class'=>'has-dependant dependant-opposite'
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides <b>Post</b> sub-menu from <b>+ New</b> block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_post',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Hide <b>+ New > Post</b> sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides <b>Link</b> sub-menu from <b>+ New</b> block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_link',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Hide <b>+ New > Link</b> sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides <b>Page</b> sub-menu from <b>+ New</b> block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_page',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Hide <b>+ New > Page</b> sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides <b>User</b> sub-menu from <b>+ New</b> block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_user',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Hide <b>+ New > User</b> sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides <b>Media</b> sub-menu from <b>+ New</b> block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_media',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Hide <b>+ New > Media</b> sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Adds custom text in admin top bar.', 'ag-custom-admin'),
                            'name'=>'agca_custom_site_heading',
                            'label'=>__('Custom blog heading', 'ag-custom-admin'),
                        ));


                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides the yellow bar with the information about the new WordPress release', 'ag-custom-admin'),
                            'name'=>'agca_update_bar',
                            'label'=>__('Hide WordPress update notification', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Right Side', 'ag-custom-admin'));

                        $this->print_input(array(
                            'name'=>'agca_howdy',
                            'title' => '',
                            'label'=>__('Change Howdy text', 'ag-custom-admin'),
                        ));

                        $this->print_input(array(
                            'title'=>__('Put \'Exit\', for example', 'ag-custom-admin'),
                            'name'=>'agca_logout',
                            'label'=>__('Change Log out text', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_remove_your_profile',
                            'label'=>__('Hide "Edit My Profile" link', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('If selected, hides all elements in top right corner, except Log Out button', 'ag-custom-admin'),
                            'name'=>'agca_logout_only',
                            'label'=>__('Log out only', 'ag-custom-admin')
                        ));

                        ?>

                        <tr>
                            <td>
                                <p>Try the Cusmin admin bar editor instead: <a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-admin-bar#group-admin-bar" target="_blank"><br>Upgrade to Cusmin</a></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="section_admin_footer" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Admin Footer Settings', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <?php
                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'ag_table_major_options'
                            ),
                            'title'=>__('Hides footer with all elements', 'ag-custom-admin'),
                            'name'=>'agca_footer',
                            'label'=>__('<strong>Hide footer</strong>', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Footer Options', 'ag-custom-admin'));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides default text in footer', 'ag-custom-admin'),
                            'name'=>'agca_footer_left_hide',
                            'label'=>__('Hide footer text', 'ag-custom-admin')
                        ));

                        $this->print_textarea(array(
                            'title'=>__('Replaces text \'Thank you for creating with WordPress\' with custom text', 'ag-custom-admin'),
                            'name'=>'agca_footer_left',
                            'disabled' => !$this->is_wp_admin(),
                            'class' => 'one-line',
                            'label'=>__('Change footer text', 'ag-custom-admin') . $this->getFieldSecurityProtected()
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides text \'Get Version ...\' on right', 'ag-custom-admin'),
                            'name'=>'agca_footer_right_hide',
                            'label'=>__('Hide version text', 'ag-custom-admin')
                        ));

                        $this->print_textarea(array(
                            'title'=>__('Replaces text \'Get Version ...\' with custom text', 'ag-custom-admin'),
                            'name'=>'agca_footer_right',
                            'disabled' => !$this->is_wp_admin(),
                            'class' => 'one-line',
                            'label'=>__('Change version text', 'ag-custom-admin') . $this->getFieldSecurityProtected()
                        ));

                        ?>

                    </table>
                </div>
                <div id="section_dashboard_page" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Dashboard Settings', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <?php

                        $this->print_options_h3(__('Dashboard Options', 'ag-custom-admin'));

                        $this->print_input(array(
                            'title'=>__('Main heading (\'Dashboard\') on Dashboard page', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_text',
                            'disabled' => !$this->is_wp_admin(),
                            'label'=>__('Change Dashboard heading text', 'ag-custom-admin') . $this->getFieldSecurityProtected(),
                        ));

                        ?>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Adds custom text (or HTML) between heading and widgets area on Dashboard page', 'ag-custom-admin'); ?>" for="agca_dashboard_text_paragraph">
                                    <?php _e('Add custom Dashboard content<br> <em>(&nbsp;text or HTML content&nbsp;)', 'ag-custom-admin'); ?></em>
                                    <?php echo $this->getFieldSecurityProtected(); ?>
                                </label>
                            </th>
                            <td class="agca_editor<?php echo !$this->is_wp_admin() ? ' disabled' : ''; ?>">
                                <?php $this->getTextEditor('agca_dashboard_text_paragraph'); ?>
                            </td>
                        </tr>
                        <?php

                        $this->print_options_h3(__('Dashboard Widgets Options', 'ag-custom-admin'));

                        ?>
                        <tr>
                            <td colspan="2">
                                <p tabindex="0" class="agca-tip"><i><strong><?php _e('Note', 'ag-custom-admin'); ?>:</strong> <?php _e('These settings will override settings configured in the Screen options on Dashboard page.', 'ag-custom-admin'); ?></i></p>
                            </td>
                        </tr>
                        <?php
                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides <b>Welcome WordPress</b> widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_welcome',
                            'label'=>__('Hide <b>Welcome</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides <b>Health Status</b> dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_health_status',
                            'label'=>__('Hide <b>Health Status</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides <b>Activity</b> dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_activity',
                            'label'=>__('Hide <b>Activity</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides <b>Quick Draft</b> dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_qp',
                            'label'=>__('Hide <b>Quick Draft</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides <b>At a Glance</b> dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_rn',
                            'label'=>__('Hide <b>At a Glance</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_dashboard_widget_primary',
                            'title'=>__('This is <b>WordPress News</b> or <b>WordPress Development Blog</b> widget in older WordPress versions', 'ag-custom-admin'),
                            'label'=>__('Hide <b>WordPress News</b> widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_dashboard_widget_secondary',
                            'title'=>__('This is <b>Other WordPress News</b> widget by default', 'ag-custom-admin'),
                            'label'=>__('Hide secondary widget area', 'ag-custom-admin')
                        ));

                        ?>
                        <tr>
                            <td>
                                <p>Create custom Dashboard Widgets<br><a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-widgets#group-dashboard-page" target="_blank">Upgrade to Cusmin</a></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="section_login_page" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Login Page Settings', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <?php

                        $this->print_options_h3(__('Login Page Options', 'ag-custom-admin'));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_login_banner',
                            'title'=>__('Hide "Back to blog" block', 'ag-custom-admin'),
                            'label'=>__('Hide "Back to blog" text', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'name'=>'agca_login_banner_text',
                            'title'=>__('Changes \'<- Back to ...\' text in top bar on Login page', 'ag-custom-admin'),
                            'label'=>__('Change back to blog text', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('If this field is not empty, image from provided url will be visible on Login page', 'ag-custom-admin'),
                            'name'=>'agca_login_photo_url',
                            'label'=>__('Change Login header image', 'ag-custom-admin'),
                            'hint'=>__('Image URL', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Add here a custom link to a web location that will be triggered on image click', 'ag-custom-admin'),
                            'name'=>'agca_login_photo_href',
                            'label'=>__('Change link on the login image', 'ag-custom-admin'),
                            'hint'=>__('For blog URL use', 'ag-custom-admin').' %BLOG%'
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides login image completely', 'ag-custom-admin'),
                            'name'=>'agca_login_photo_remove',
                            'label'=>__('Hide login header image', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Rounds box on login page', 'ag-custom-admin'),
                            'name'=>'agca_login_round_box',
                            'label'=>'Round box corners',
                            'input-class'=>'has-dependant',
                            'input-attributes'=>'data-dependant=\'#agca_login_round_box_size_block, #agca_login_round_box_skip_logo\''
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_login_round_box') !== 'true' )?'display:none':''),
                                'id'=>'agca_login_round_box_size_block'
                            ),
                            'title'=>__('Size of rounded box curve', 'ag-custom-admin'),
                            'name'=>'agca_login_round_box_size',
                            'label'=>__('Round box corners - size', 'ag-custom-admin'),
                            'input-class'=>'validateNumber',
                            'hint'=>__('(Size in px)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_login_round_box') !== 'true' )?'display:none':''),
                                'id'=>'agca_login_round_box_skip_logo'
                            ),
                            //'hide'=>true,
                            'title'=>__('Skip rounding logo on the login page', 'ag-custom-admin'),
                            'name'=>'agca_login_round_box_skip_logo',
                            'label'=>__('Don\'t round logo', 'ag-custom-admin'),
                            'input-class'=>'',
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides register link on login page', 'ag-custom-admin'),
                            'name'=>'agca_login_register_remove',
                            'label'=>__('Hide "Register" link', 'ag-custom-admin'),
                            'input-class'=>'has-dependant dependant-opposite',
                            'input-attributes'=>'data-dependant=\'#agca_login_register_href_block\''
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_login_register_remove')=='true')?'display:none':''),
                                'id'=>'agca_login_register_href_block'
                            ),
                            'title'=>__('Change register link on login page to point to your custom registration page.', 'ag-custom-admin'),
                            'name'=>'agca_login_register_href',
                            'label'=>__('Change register link', 'ag-custom-admin'),
                            'hint'=>__('Link to a new registration page', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides lost password link on the login page', 'ag-custom-admin'),
                            'name'=>'agca_login_lostpassword_remove',
                            'label'=>__('Hide "Lost password" link', 'ag-custom-admin'),
                        ));
                        ?>
                        <tr>
                            <td>
                                <p>Add a custom login background image and apply more login page options: <a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-login#group-login-page" target="_blank">Upgrade to Cusmin</a></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php
                /*ADMIN MENU*/
                ?>
                <div id="section_admin_menu" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Admin Menu Settings', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <tr valign="center" class="ag_table_major_options">
                            <td><label for="agca_admin_menu_turnonoff"><strong><?php _e('Apply admin menu customizations', 'ag-custom-admin'); ?></strong></label></td>
                            <td><input class="agca-checkbox visibility" type="checkbox" name="agca_admin_menu_turnonoff" title="<?php _e('Hides admin menu completely (administrator can see \'AGCA\' button)', 'ag-custom-admin'); ?>" value="on" <?php if (get_option('agca_admin_menu_turnonoff')==true) echo 'checked="checked" '; ?> /></td>
                        </tr>
                        <tr valign="center" class="ag_table_major_options">
                            <td><label for="agca_admin_menu_agca_button_only" title="<?php _e('Hides admin menu completely (administrator can see <b>AGCA</b> button)', 'ag-custom-admin'); ?>"><strong><?php _e('Hide admin menu', 'ag-custom-admin'); ?></strong></label></td>
                            <td><input class="agca-checkbox visibility" type="checkbox" name="agca_admin_menu_agca_button_only" title="<?php _e('Hides admin menu completely (administrator can see <b>AGCA</b> button)', 'ag-custom-admin'); ?>" value="true" <?php if (get_option('agca_admin_menu_agca_button_only')==true) echo 'checked="checked" '; ?> /></td>
                        </tr>
                        <?php
                        $this->print_options_h3(__('Edit / Remove Menu Items', 'ag-custom-admin'));
                        ?>
                        <tr>
                            <td colspan="2">
                                <input type="button" class="agca_button button-secondary"  id="ag_edit_adminmenu_reset_button" title="<?php _e('Reset menu settings to default values', 'ag-custom-admin'); ?>" name="ag_edit_adminmenu_reset_button" value="<?php _e('Reset to default settings', 'ag-custom-admin'); ?>" /><br />
                                <p tabindex="0"><em>(<?php _e('click on the top menu item to show its sub-menus', 'ag-custom-admin'); ?>)</em></p>
                                <table id="ag_edit_adminmenu">
                                    <tr style="background-color:#816c64;">
                                        <td width="300px"><div style="float:left;color:#fff;"><h3><?php _e('Item', 'ag-custom-admin'); ?></h3></div><div style="float:right;color:#fff;"><h3><?php _e('Hide?', 'ag-custom-admin'); ?></h3></div></td><td width="300px" style="color:#fff;" ><h3><?php _e('Change Text', 'ag-custom-admin'); ?></h3>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" size="47" id="ag_edit_adminmenu_json" name="ag_edit_adminmenu_json" value="<?php echo $this->sanitize_html(get_option('ag_edit_adminmenu_json')); ?>" />
                                <input type="hidden" size="47" id="ag_edit_adminmenu_json_new" name="ag_edit_adminmenu_json_new" value="" />
                            </td>
                            <td></td>
                        </tr>
                        <?php
                        $this->print_checkbox(array(
                            'title'=>__('Removes Profile menu item for non-admin users.', 'ag-custom-admin'),
                            'hide'=>true,
                            'name'=>'agca_menu_remove_client_profile',
                            'label'=>__('Remove Profile button from the user menu', 'ag-custom-admin'),
                        ));
                        $this->print_checkbox(array(
                            'title'=>__('Removes Customize button on front end for authenticated users.', 'ag-custom-admin'),
                            'hide'=>true,
                            'name'=>'agca_menu_remove_customize_button',
                            'label'=>__('Front end: Remove Customize button', 'ag-custom-admin'),
                        ));
                        ?>
                        <tr>
                            <td>
                                <p>Try Cusmin admin menu editor instead: <a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-menu#group-admin-menu" target="_blank"><br>Upgrade to Cusmin</a></p>
                            </td>
                        </tr>

                        <?php
                        $this->print_options_h3(__('Add New Menu Items', 'ag-custom-admin'));
                        ?>
                        <tr>
                            <td colspan="2">
                                <table id="ag_add_adminmenu">
                                    <tr>
                                        <td colspan="2">
                                            <?php _e('name', 'ag-custom-admin'); ?>:<input type="text" size="47" title="<?php _e('New button visible name', 'ag-custom-admin'); ?>" id="ag_add_adminmenu_name" name="ag_add_adminmenu_name" />
                                            <?php _e('url', 'ag-custom-admin'); ?>:<input type="text" size="47" title="<?php _e('New button link', 'ag-custom-admin'); ?>" id="ag_add_adminmenu_url" name="ag_add_adminmenu_url" />
                                            <?php _e('open in', 'ag-custom-admin'); ?>:&nbsp;<select id="ag_add_adminmenu_target" class="agca-selectbox" style="width:auto;">
                                                <option value="_self" selected><?php _e('the same tab', 'ag-custom-admin'); ?></option>
                                                <option value="_blank" ><?php _e('a new tab', 'ag-custom-admin'); ?></option>
                                            </select>
                                            <input type="button" id="ag_add_adminmenu_button" class="agca_button button-secondary" title="<?php _e('Add new item button" name="ag_add_adminmenu_button', 'ag-custom-admin'); ?>" value="<?php _e('Add new item', 'ag-custom-admin'); ?>" />
                                        </td><td></td>
                                    </tr>
                                </table>
                                <input type="hidden" size="47" id="ag_add_adminmenu_json" name="ag_add_adminmenu_json" value="<?php echo $this->sanitize_html(get_option('ag_add_adminmenu_json')); ?>" />
                            </td>
                            <td>
                            </td>
                        </tr>
                        <?php
                        $this->print_options_h3(__('Admin Menu Settings', 'ag-custom-admin'));
                        ?>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Choose how admin menu should behave on mobile devices / small screens', 'ag-custom-admin'); ?>" for="agca_admin_menu_autofold"><?php _e('Admin menu auto folding', 'ag-custom-admin'); ?></label>
                            </th>
                            <td>
                                <select title="<?php _e('General', 'ag-custom-admin'); ?>Choose how admin menu should behave on mobile devices / small screens" class="agca-selectbox" name="agca_admin_menu_autofold" >
                                    <option value="" <?php echo (get_option('agca_admin_menu_autofold') == "")?" selected ":""; ?> ><?php _e('Default', 'ag-custom-admin'); ?></option>
                                    <option value="force" <?php echo (get_option('agca_admin_menu_autofold') == "force")?" selected ":""; ?> ><?php _e('Force admin menu auto-folding', 'ag-custom-admin'); ?></option>
                                    <option value="disable" <?php echo (get_option('agca_admin_menu_autofold') == "disable")?" selected ":""; ?> ><?php _e('Disable admin menu auto-folding', 'ag-custom-admin'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <?php

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes empty space between some top menu items', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_separators',
                            'label'=>__('Hide menu items separators', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes icons from dmin menu buttons', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_icons',
                            'label'=>__('Hide menu icons', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes small arrow that appears on the top button hover', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_arrow',
                            'label'=>__('Hide sub-menu arrow', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes collapse button at the end of admin menu', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_collapse_button',
                            'label'=>__('Hide "Collapse menu" button', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Rounds submenu pop-up box', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_submenu_round',
                            'label'=>__('Round sub-menu pop-up box', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant=\'#agca_admin_menu_submenu_round_size\'',
                            'input-class'=>'has-dependant',
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_admin_menu_submenu_round')!=='true')?'display:none':''),
                                'id'=>'agca_admin_menu_submenu_round_size'
                            ),
                            'title'=>__('Size of rounded box curve', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_submenu_round_size',
                            'label'=>__('Round sub-menu pop-up box - size', 'ag-custom-admin'),
                            'input-class'=>'validateNumber',
                            'hint'=>__('(Size in px)', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Adds custom logo above the admin menu', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_brand',
                            'label'=>__('Admin menu branding logo', 'ag-custom-admin'),
                            'hint'=>__('Image URL', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Change branding logo link</br></br>Use:', 'ag-custom-admin').'</br><strong>%BLOG%</strong> - '. __('for blog URL', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_brand_link',
                            'label'=>__('Branding logo link', 'ag-custom-admin'),
                            'hint'=>__('Branding image URL', 'ag-custom-admin'),
                            'link' => true
                        ));
                        ?>
                    </table>
                </div>
                <div id="section_ag_colorizer_settings" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Colorizer Page', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <tr valign="center" class="ag_table_major_options">
                            <td><label for="agca_colorizer_turnonoff"><strong><?php _e('Apply Colorizer customizations', 'ag-custom-admin'); ?></strong></label></td>
                            <td><input class="agca-checkbox visibility" type="checkbox" name="agca_colorizer_turnonoff" value="on" <?php if (get_option('agca_colorizer_turnonoff')==true) { echo 'checked="checked" ';} ?> /></td>
                        </tr>
                        <?php
                        $this->print_options_h3(__('Global Color Options', 'ag-custom-admin'));

                        $this->print_color('color_background',__('Background:', 'ag-custom-admin'),__('Change admin page background color', 'ag-custom-admin'));
                        $this->print_color('login_color_background',__('Login page background:', 'ag-custom-admin'),__('Change login page background color', 'ag-custom-admin'));
                        $this->print_color('color_header',__('Admin bar:', 'ag-custom-admin'),__('Change admin bar (on top) color in admin panel', 'ag-custom-admin'));

                        $this->print_options_h3(__('Admin Menu Color Options', 'ag-custom-admin'));

                        $this->print_color('color_admin_menu_top_button_background',__('Button background:', 'ag-custom-admin'),__('Change button background color', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_font',__('Button text:', 'ag-custom-admin'),__('Change button text color', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_top_button_current_background',__('Selected button background:', 'ag-custom-admin'),__('Change button background color for current button', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_top_button_hover_background',__('Hover button background:', 'ag-custom-admin'),__('Change button background color on mouseover', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_submenu_background',__('Sub-menu button background:', 'ag-custom-admin'),__('Change submenu item background color', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_submenu_background_hover',__('Sub-menu hover button background:', 'ag-custom-admin'),__('Change submenu item background color on mouseover', 'ag-custom-admin'));
                        $this->print_color('color_admin_submenu_font',__('Sub-menu text:', 'ag-custom-admin'),__('Sub-menu text color', 'ag-custom-admin'));
                        $this->print_color('color_admin_menu_behind_background',__('Wrapper background:', 'ag-custom-admin'),__('Change background color of element behind admin menu', 'ag-custom-admin'));

                        $this->print_options_h3(__('Font Color Options', 'ag-custom-admin'));

                        $this->print_color('color_font_content',__('Content text:', 'ag-custom-admin'),__('Change color in content text', 'ag-custom-admin'));
                        $this->print_color('color_font_header',__('Admin bar text:', 'ag-custom-admin'),__('Change color of admin bar text', 'ag-custom-admin'));
                        $this->print_color('color_font_footer',__('Footer text:', 'ag-custom-admin'),__('Change color in footer text', 'ag-custom-admin'));

                        $this->print_options_h3(__('Widgets Color Options', 'ag-custom-admin'));

                        $this->print_color('color_widget_bar',__('Title bar background:', 'ag-custom-admin'),__('Change color in header text', 'ag-custom-admin'));
                        $this->print_color('color_widget_background',__('Background:', 'ag-custom-admin'),__('Change widget background color', 'ag-custom-admin'));
                        ?>

                    </table>
                    <p>Need more Colorizer options? <a href="https://cusmin.com/upgrade-to-cusmin/?ref=agca-colorizer#group-custom-colors" target="_blank">Upgrade to Cusmin</a></p>
                    <input type="hidden" size="47" id="ag_colorizer_json" name="ag_colorizer_json" value="<?php echo $this->sanitize_html(get_option('ag_colorizer_json')); ?>" />
                    <div id="picker"></div>
                </div>
                <div id="section_advanced" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Advanced', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <tr valign="center">
                            <td colspan="2">
                                <p class="agca-tip"><i><?php _e('<strong>Note: </strong>These options will override existing customizations', 'ag-custom-admin'); ?></i></p>
                            </td><td></td>
                        </tr>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Add custom CSS script to override existing styles', 'ag-custom-admin'); ?>" for="agca_script_css"><?php _e('Custom CSS script', 'ag-custom-admin'); ?></em></label>
                                <?php echo $this->getFieldSecurityProtected(); ?>
                            </th>
                            <td>
                                <textarea
                                        class="<?php echo !$this->is_wp_admin() ? 'disabled' : ''; ?>"
                                        style="width:100%;height:200px"
                                        title="<?php _e('Add custom CSS script to override existing styles', 'ag-custom-admin'); ?>"
                                        rows="5"
                                        <?php echo !$this->is_wp_admin() ? 'disabled="disabled"' : ''; ?>
                                        id="agca_custom_css"
                                        name="agca_custom_css"
                                        cols="40"><?php echo htmlentities(get_option('agca_custom_css')); ?></textarea>
                            </td>
                        </tr>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Add additional custom JavaScript', 'ag-custom-admin'); ?>" for="agca_custom_js"><?php _e('Custom JavaScript', 'ag-custom-admin'); ?></label>
                                <?php echo $this->getFieldSecurityProtected(); ?>
                            </th>
                            <td>
                                <textarea
                                        class="<?php echo !$this->is_wp_admin() ? 'disabled' : ''; ?>"
                                        style="width:100%;height:200px"
                                        title="<?php _e('Add additional custom JavaScript', 'ag-custom-admin'); ?>"
                                        rows="5"
                                        <?php echo !$this->is_wp_admin() ? 'disabled="disabled"' : ''; ?>
                                        name="agca_custom_js"
                                        id="agca_custom_js"
                                        cols="40"><?php echo htmlentities(get_option('agca_custom_js')); ?></textarea>
                            </td>
                        </tr>

                        <?php
                        $this->print_checkbox(array(
                            'title'=>__('For users who prefer to see the default WordPress styling on the AGCA page', 'ag-custom-admin'),
                            'name'=>'agca_no_style',
                            'label'=>__('Remove styles on the AGCA settings page', 'ag-custom-admin'),
                        ));
                        ?>

                        <?php
                        $this->print_checkbox(array(
                            'title'=>__('Temporary enable this option if you are experiencing any problems with saving AGCA options. Please turn it off after you are done with your customizations.', 'ag-custom-admin'),
                            'name'=>'agca_disable_postver',
                            'label'=>__('Temporary disable POST verification', 'ag-custom-admin'),
                        ));
                        ?>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Export / import settings', 'ag-custom-admin'); ?>" for="agca_export_import"><?php _e('Export / import settings', 'ag-custom-admin'); ?></label>
                            </th>
                            <td id="import_file_area">
                                <input class="agca_button button-secondary"  type="button" name="agca_export_settings" value="<?php _e('Export Settings', 'ag-custom-admin'); ?>" onclick="exportSettings();"/></br>
                                <input type="file" class="button-secondary" id="settings_import_file" name="settings_import_file" style="display: none"/>
                                <input type="hidden" id="_agca_import_settings" name="_agca_import_settings" value="false" />
                                <input type="hidden" id="_agca_export_settings" name="_agca_export_settings" value="false" />
                                <input class="agca_button button-secondary" type="button" name="agca_import_settings" value="<?php _e('Import Settings', 'ag-custom-admin'); ?>" onclick="importSettings();"/>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php $this->show_save_button(); ?>
            </form>
        </div>
        <?php
    }

    #region PRIVATE METHODS
    function show_save_button_upper(){
        ?>
        <div class="save-button-upper">
            <?php $this->show_save_button() ?>
        </div>
        <?php
    }
    function show_save_button(){
        ?>
        <p class="submit agca-clear">
            <input type="button" id="save_plugin_settings" style="padding:0px" title="<?php _e('Save AGCA configuration', 'ag-custom-admin'); ?>" class="button-primary" value="<?php _e('Save Changes') ?>" onClick="savePluginSettings()" />
        </p>
        <?php
    }

    function print_checkbox($data){
        $strAttributes = '';
        $strOnchange = '';
        $strInputClass='';
        $strInputAttributes='';
        $isChecked = false;

        if(isset($data['attributes'])){
            foreach($data['attributes'] as $key=>$val){
                $strAttributes.=' '.$this->sanitize_html($key).'="'.$this->sanitize_html($val).'"';
            }
        }
        if(isset($data['input-class'])){
            $strInputClass = $this->sanitize_html($data['input-class']);
        }
        if(isset($data['hide'])){
            $strInputClass .= " visibility";
        }
        if(isset($data['input-attributes'])){
            $strInputAttributes = $data['input-attributes'];
        }
        if(isset($data['onchange'])){
            $strOnchange = $this->sanitize_html($data['onchange']);
        }
        if(!isset($data['title'])){
            $data['title'] = $this->sanitize_html($data['label']);
        }
        if(isset($data['checked'])){
            $isChecked = (bool) $data['checked'];
        }else{
            //use default check with the option
            $isChecked = get_option($data['name'])==true;
        }
        ?>
        <tr valign="center" <?php echo $strAttributes ?> >
            <th>
                <label tabindex="0" title='<?php echo $this->sanitize_html($data['title']) ?>' for="<?php echo $this->sanitize_html($data['name']) ?>" ><?php echo wp_kses_post($data['label']) ?></label>
            </th>
            <td>
                <input type="checkbox" class="agca-checkbox <?php echo $this->sanitize_html($strInputClass) ?> "  <?php echo $this->sanitize_html($strOnchange) ?>  <?php echo $this->sanitize_html($strInputAttributes) ?> title='<?php _e('Toggle on/off', 'ag-custom-admin'); ?>' name="<?php echo $this->sanitize_html($data['name']) ?>" value="true" <?php echo ($isChecked)?' checked="checked"':'' ?> />
            </td>
        </tr>
        <?php
    }
    function print_input($data){
        $strHint = '';
        $suffix ='';
        $strAttributes = '';
        $parentAttr = '';
        $isLink = false;
        if(isset($data['hint'])){
            $strHint = '&nbsp;<p><i>'.$this->sanitize($data['hint']).'</i></p>';
        }
        if(!isset($data['title'])){
            $data['title'] = $data['label'];
        }
        if(!isset($data['disabled'])){
            $data['disabled'] = false;
        }
        if(isset($data['suffix'])){
            $suffix = $data['suffix'];
        }
        if(isset($data['link']) && $data['link']){
            $isLink = true;
        }
        if(isset($data['attributes'])){
            foreach($data['attributes'] as $key=>$val){
                $strAttributes.=' '.$this->sanitize_html($key).'="'.$this->sanitize_html($val).'"';
            }
        }
        ?>
        <tr valign="center" <?php echo $strAttributes ?> >
            <th >
                <label title="<?php echo $this->sanitize_html($data['title']) ?>" for="<?php echo $this->sanitize_html($data['name']) ?>"><?php echo wp_kses_post($data['label']) ?></label>
            </th>
            <td>
                <input id="<?php
                $value = get_option($data['name']);
                $value = $isLink ?
                    (
                            (
                                $value === self::PLACEHOLDER_BLOG ||
                                $value === self::PLACEHOLDER_PAGE
                            ) ? $value : esc_url($value)
                    ) :
                    htmlentities($value);
                echo $this->sanitize_html($data['name']) ?>"
                       title="<?php echo $this->sanitize_html($data['title']) ?>"
                       type="text"
                       size="47"
                       class="<?php echo $data['disabled'] ? 'disabled' : ''; ?>"
                       name="<?php echo $this->sanitize_html($data['name']) ?>"
                       value="<?php echo $value; ?>"
                    <?php echo $data['disabled'] ? 'disabled="disabled"':''; ?> />
                <?php if(!$data['disabled']) { ?>
                <a title="<?php _e('Clear', 'ag-custom-admin'); ?>" class="agca_button clear" onClick="jQuery('#<?php echo $this->sanitize_html($data['name']) ?>').val('');"><span class="dashicons clear dashicons-no-alt"></span></a>
                <?php } ?>
                <?php echo $this->sanitize($suffix) ?>
                <?php echo $this->sanitize($strHint) ?>
            </td>
        </tr>
        <?php
    }
    function print_textarea($data){
        $strHint = '';
        $strClass = '';
        if(isset($data['hint'])){
            $strHint = '&nbsp;<p><i>'.$this->sanitize_html($data['hint']).'</i>.</p>';
        }
        if(!isset($data['title'])){
            $data['title'] = $data['label'];
        }
        if(isset($data['class'])){
            $strClass = $data['class'];
        }
        if(!isset($data['disabled'])){
            $data['disabled'] = false;
        }
        if($data['disabled']){
            $strClass .= ' disabled';
        }
        ?>
        <tr valign="center">
            <th scope="row">
                <label title="<?php echo $this->sanitize_html($data['title']) ?>" for="<?php echo $this->sanitize_html($data['name']) ?>"><?php echo wp_kses_post($data['label']) ?></label>
            </th>
            <td>
                <textarea <?php echo !empty($strClass)?'class="'.$this->sanitize_html($strClass).'"':''; ?> title="<?php echo $this->sanitize_html($data['title']) ?>" rows="5" name="<?php echo $this->sanitize_html($data['name']) ?>" cols="40" <?php echo $data['disabled'] ? 'disabled="disabled"':''; ?> ><?php echo $this->sanitize_html(get_option($data['name'])); ?></textarea>
                <?php echo $this->sanitize($strHint) ?>
            </td>
        </tr>
        <?php
    }
    function print_color($name, $label, $title){
        ?>
        <tr valign="center" class="color">
            <th><label title="<?php echo $this->sanitize_html($title); ?>" for="<?php echo $this->sanitize_html($name); ?>"><?php echo $this->sanitize_html($label); ?></label></th>
            <td><input type="text" placeholder="#" id="<?php echo $this->sanitize_html($name); ?>" name="<?php echo $this->sanitize_html($name); ?>" class="color_picker" value="<?php echo $this->sanitize_html($this->getAGCAColor($name)); ?>" />
                <a title="<?php _e('Pick Color', 'ag-custom-admin'); ?>" alt="<?php echo $this->sanitize_html($name); ?>" class="pick_color_button agca_button"><span class="dashicons dashicons-art"></span></a>
                <a title="<?php _e('Clear', 'ag-custom-admin'); ?>" alt="<?php echo $this->sanitize_html($name); ?>" class="pick_color_button_clear agca_button" ><span class="dashicons clear dashicons-no-alt"></span></a>
            </td>
        </tr>
        <?php
    }
    function print_options_h3($title){
        ?>
        <tr valign="center">
            <td colspan="2">
                <div class="ag_table_heading"><h3 tabindex="0"><?php echo $this->sanitize_html($title) ?></h3></div>
            </td>
            <td></td>
        </tr>
        <?php
    }
    function print_option_tr(){
        ?>

        <tr valign="center">
            <th><label title="<?php _e('Change submenu item background color', 'ag-custom-admin'); ?>" for="color_admin_menu_submenu_background"><?php _e('Submenu button background color:', 'ag-custom-admin'); ?></label></th>
            <td><input type="text" id="color_admin_menu_submenu_background" name="color_admin_menu_submenu_background" class="color_picker" value="<?php echo $this->sanitize_html($this->getAGCAColor('color_admin_menu_submenu_background')); ?>" />
                <input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button agca_button" value="<?php _e('Pick color', 'ag-custom-admin'); ?>" />
                <input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button_clear agca_button" value="<?php _e('Clear', 'ag-custom-admin'); ?>" />
            </td>
        </tr>
        <?php
    }
    #endregion

}
//TODO: Add log out button to the admin menu