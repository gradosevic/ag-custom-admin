<?php
/*
Plugin Name: AG Custom Admin
Plugin URI: http://wordpressadminpanel.com/ag-custom-admin/
Description: All-in-one tool for admin panel customization. Change almost everything: admin menu, dashboard, login page, admin bar etc. Apply admin panel themes.
Author: WAP
Version: 5.7.3
Text Domain: ag-custom-admin
Domain Path: /languages
Author URI: http://www.wordpressadminpanel.com/

    Copyright 2017. WAP (email : info@wordpressadminpanel.com)
 
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
    private $colorizer="";
    private $agca_version;
    private $agca_debug = false;
    private $admin_capabilities;
    private $context = "";
    private $saveAfterImport = false;
    private $templateCustomizations = "";
    private $templates_ep = "http://wordpressadminpanel.com/configuration.php";

    public function __construct()
    {
        add_action('init', array(&$this,'init'));
    }

    function init(){
        $this->reloadScript();
        $this->checkPOST();
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
        add_action( 'wp_before_admin_bar_render', array(&$this,'admin_bar_changes') );
        add_action( 'plugins_loaded', array(&$this,'load_plugin_textdomain') );
        register_deactivation_hook(__FILE__, array(&$this,'agca_deactivate'));

        add_action( 'customize_controls_enqueue_scripts',  array(&$this,'agca_customizer_php') );

        /* wp_localize_script(
             'agca-script',//use agca enqueued script
             'agca_string',
             $this->get_language_strings()
         );*/

        /*Initialize properties*/
        $this->colorizer = $this->jsonMenuArray(get_option('ag_colorizer_json'),'colorizer');

        $this->agca_version = "5.7.3";

        //TODO:upload images programmatically
    }

    function load_plugin_textdomain() {
        load_plugin_textdomain( 'ag-custom-admin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    // Add donate and support information
    function jk_filter_plugin_links($links, $file)
    {
        if ( $file == plugin_basename(__FILE__) )
        {
            if(!is_network_admin()){
                $links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php#general-settings">' . __('Settings', 'ag-custom-admin') . '</a>';
                $links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php#ag-templates">' . __('Admin Themes', 'ag-custom-admin') . '</a>';
            }
            $links[] = '<a href="http://wordpressadminpanel.com/agca-support/">' . __('Support', 'ag-custom-admin') . '</a>';
            $links[] = '<a href="https://cusmin.com/upgrade-to-cusmin?ref=plugins">' . __('Upgrade', 'ag-custom-admin') . '</a>';
            $links[] = '<a href="http://wordpressadminpanel.com/agca-support/support-for-future-development">' . __('Donate', 'ag-custom-admin') . '</a>';
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

    function agca_customizer_php(){
        $this->agca_get_includes();
    }

    function admin_init(){
        $this->agca_register_settings();
        $this->agca_init_session();
        $isAdminUser = current_user_can($this->admin_capability());
        if(!$isAdminUser || ($isAdminUser && !get_option('agca_role_allbutadmin'))){
            if(get_option('agca_profile_color_scheme')){
                remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
            }
        }
    }

    function login_init(){
        $this->agca_enqueue_scripts();
    }

    function agca_init_session(){
        if (!session_id())
            session_start();
    }

    function checkGET(){
        if(isset($_GET['agca_action'])){
            if($_GET['agca_action'] =="remove_templates"){
                $this->delete_template_images_all();
                update_option('agca_templates', "");
                update_option('agca_selected_template', "");
            }
        }
        if(isset($_GET['agca_debug'])){
            if($_GET['agca_debug'] =="true"){
                $this->agca_debug = true;
            }else{
                $this->agca_debug = false;
            }
        }
    }

    function checkPOST(){
        if(isset($_POST['_agca_save_template'])){
            $this->verifyPostRequest();
            //print_r($_POST);
            $data = $_POST['templates_data'];
            $parts = explode("|||",$data);

            $common_data = $parts [0];
            $admin_js = $parts [1];
            $admin_css = $parts [2];
            $login_js = $parts [3];
            $login_css = $parts [4];
            $settings = $parts [5];
            $images = $parts [6];

            $template_name = $_POST['templates_name'];

            update_option('agca_selected_template', $template_name);

            $templates = get_option( 'agca_templates' );
            if($templates == ""){
                $templates = array();
            }

            $templates[$template_name] = array(
                'common'=>$common_data,
                'admin'=>"",
                'adminjs'=>$admin_js,
                'admincss'=>$admin_css,
                'login'=>"",
                'loginjs'=>$login_js,
                'logincss'=>$login_css,
                'images'=>$images,
                'settings'=>$settings
            );
            update_option('agca_templates', $templates);

            $_POST = array();

        }else if(isset($_POST['_agca_templates_session'])){
            $this->verifyPostRequest();
            $this->agcaAdminSession();
            if($_POST['template'] !="")
                $_SESSION["AGCA"]["Templates"][$_POST['template']] = array("license"=>$_POST['license']);

            //print_r($_SESSION);
            echo "_agca_templates_session:OK";
            exit;
        }else if(isset($_POST['_agca_templates_session_remove_license'])){
            $this->verifyPostRequest();
            $this->agcaAdminSession();
            if($_POST['template'] !="")
                $_SESSION["AGCA"]["Templates"][$_POST['template']] = null;
            print_r($_SESSION);
            echo "_agca_templates_session_remove_license:OK";
            exit;
        }else if(isset($_POST['_agca_get_templates'])){
            $this->verifyPostRequest();
            $templates = get_option( 'agca_templates' );
            if($templates == "") $templates = array();
            $results = array();
            foreach($templates as $key=>$val){
                $results[]=$key;
            }
            echo json_encode($results);
            exit;
        }else if(isset($_POST['_agca_activate_template'])){
            $this->verifyPostRequest();
            update_option('agca_selected_template', $_POST['_agca_activate_template']);
            $_POST = array();
            //unset($_POST);
            exit;
        }else if(isset($_POST['_agca_template_settings'])){
            $this->verifyPostRequest();
            $settings = $_POST['_agca_template_settings'];

            $templates = get_option( 'agca_templates' );
            if($templates == ""){
                $templates = array();
            }
            $template_name = $_POST["_agca_current_template"];

            $templates[$template_name]["settings"] = $settings;
            update_option('agca_templates', $templates);

            $_POST = array();
            //print_r($templates);
            exit;
        }else if(isset($_POST['_agca_upload_image'])){
            $this->verifyPostRequest();
            function my_sideload_image() {
                $remoteurl = $_POST['_agca_upload_image'];
                $file = media_sideload_image( $remoteurl, 0 ,__("AG Custom Admin theme image (do not delete)", 'ag-custom-admin'));
                try{
                    $fileparts = explode("src='", $file);
                    $url=explode("'",$fileparts[1]);
                    echo $url[0];
                }catch (\Exception $e){}
                exit;
            }
            add_action( 'admin_init', 'my_sideload_image' );

        }else if(isset($_POST['_agca_remove_template_images'])){
            $this->verifyPostRequest();
            $this->delete_template_images($_POST['_agca_remove_template_images']);
            exit;
        }
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
                $msError = __('Please try temporary disabling POST verification. Go to AG Custom Admin -> Advanced -> Temporary disable POST verification. Do not forget to un-check this option once you are done with customizations.', 'ag-custom-admin');
                if (is_user_member_of_blog($user_id, $blog_id)) {
                    if (!current_user_can('manage_options')) {
                        _e('Multi-site: Current user is not recognized as administrator.', 'ag-custom-admin');
                        echo ' '.$msError;
                        exit;
                    }
                } else {
                    printf(
                    /*translators: 1: User Id 2: Blog Id*/
                        __('Multi-site: User (%1$s) does not have access to this blog (%2$s).', 'ag-custom-admin'),
                        $user_id,
                        $blog_id
                    );
                    echo ' '. $msError;
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

    function get_language_strings() {
        $strings = array(

        );
        return $strings;
    }

    function admin_bar_changes(){
        if( current_user_can( 'manage_options' )){
            global $wp_admin_bar;
            if(!is_network_admin()){
                $wp_admin_bar->add_menu( array(
                    'id'    => 'agca-admin-themes',
                    'title' => '<span class="ab-icon"></span>'.__( 'Admin Themes', 'ag-custom-admin' ),
                    'href'  => 'tools.php?page=ag-custom-admin/plugin.php#ag-templates'
                ));
            }
        }
    }

    function delete_template_images_all(){
        $templates = get_option('agca_templates');
        if($templates != null && $templates != ""){
            foreach($templates as $template){
                if($template != null && $template['images'] != null && $template['images'] != ""){
                    //print_r($template['images']);
                    $imgs = explode(',',$template['images']);
                    foreach($imgs as $imageSrc){
                        $this->delete_attachment_by_src($imageSrc);
                    }
                    //print_r($imgs);
                }
            }
        }
        //print_r($templates);
    }

    function delete_template_images($template_name){
        $templates = get_option('agca_templates');
        if($templates != null && $templates != ""){
            $template = $templates[$template_name];
            if($template != null && $template['images'] != null && $template['images'] != ""){
                //print_r($template['images']); exit;
                $imgs = explode(',',$template['images']);
                foreach($imgs as $imageSrc){
                    $this->delete_attachment_by_src($imageSrc);
                }
                //print_r($imgs);
            }
        }
        //print_r($templates);
    }

    function delete_attachment_by_src ($image_src) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);
        wp_delete_attachment( $id, $true );
    }

    function get_installed_agca_templates(){
        $templates = get_option( 'agca_templates' );
        if($templates == "")return '[]';
        $results = array();
        foreach($templates as $key=>$val){
            $results[]=$key;
        }
        return json_encode($results);
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
            $customTitle = str_replace('%BLOG%',$blog,$customTitle);
            $customTitle = str_replace('%PAGE%',$page,$customTitle);
            return $customTitle;
        }else{
            return $admin_title;
        }
    }
    function agca_get_includes() {
        ?>
        <script type="text/javascript">
            <?php
                //AGCA GLOBALS
                echo "var agca_global_plugin_url = '".$this->pluginUrl()."';";
            ?>
        </script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl(); ?>style/ag_style.css?ver=<?php echo $this->agca_version; ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl(); ?>require/dynamic.php?type=css&context=<?php echo $this->context; ?>&ver=<?php echo "changed_theme"; ?>" />
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/ag_script.js?ver=<?php echo $this->agca_version; ?>"></script>
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>require/dynamic.php?type=js&context=<?php echo $this->context; ?>&ver=<?php echo "changed_theme"; ?>"></script>

        <?php
        if($this->context == "login"){
            ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl(); ?>style/login.min.css" />
            <?php
        }else{
            ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl(); ?>style/admin.min.css" />
            <?php
        }
        ?>
        <?php
        echo $this->templateCustomizations;

        if(!((get_option('agca_role_allbutadmin')==true) and  (current_user_can($this->admin_capability())))){
            ?>
            <style type="text/css">
                <?php
                   echo get_option('agca_custom_css');
                ?>
            </style>
            <script type="text/javascript">
                try{
                    eval("<?php echo str_replace(array("\r\n", "\n", "\r"), ' ', get_option('agca_custom_js')); ?>");
                }catch(e){
                    alert('AG CUSTOM ADMIN: <?php _e('There is an error in your custom JS script. Please fix it:', 'ag-custom-admin'); ?> \n\n' + e + '\n\n (<?php _e('AG CUSTOM ADMIN -> Advanced -> Custom JavaScript', 'ag-custom-admin'); ?>)');
                    console.log(e);
                }
            </script>
            <?php
        }
    }

    function agca_enqueue_scripts() {
        wp_enqueue_script('jquery');
        //TODO: Use this instead of manually adding scripts
        //wp_enqueue_script('agca-script', __FILE__.'/script/ag_script.js', array('jquery'),$this->agca_version );
    }

    function WPSPluginIsLoginPage(){

        $WPSPluginName = 'wps-hide-login/wps-hide-login.php';
        if(is_multisite()){
            if ( ! function_exists( 'is_plugin_active_for_network' ) )
                require_once($this->filePath('wp-admin/includes/plugin.php#general-settings'));

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

        register_setting( 'agca-options-group', 'agca_login_banner' );
        register_setting( 'agca-options-group', 'agca_login_banner_text' );
        register_setting( 'agca-options-group', 'agca_login_photo_remove' );
        register_setting( 'agca-options-group', 'agca_login_photo_url' );
        register_setting( 'agca-options-group', 'agca_login_photo_href' );
        register_setting( 'agca-options-group', 'agca_login_round_box' );
        register_setting( 'agca-options-group', 'agca_login_round_box_size' );

        register_setting( 'agca-options-group', 'agca_dashboard_icon' );
        register_setting( 'agca-options-group', 'agca_dashboard_text' );
        register_setting( 'agca-options-group', 'agca_dashboard_text_paragraph' );
        register_setting( 'agca-options-group', 'agca_dashboard_widget_welcome' );
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
        register_setting( 'agca-options-group', 'agca_admin_bar_admin_themes' );
        register_setting( 'agca-options-group', 'agca_remove_top_bar_dropdowns' );
        register_setting( 'agca-options-group', 'agca_admin_bar_frontend' );
        register_setting( 'agca-options-group', 'agca_admin_bar_frontend_hide' );
        register_setting( 'agca-options-group', 'agca_login_register_remove' );
        register_setting( 'agca-options-group', 'agca_login_register_href' );
        register_setting( 'agca-options-group', 'agca_login_lostpassword_remove' );
        register_setting( 'agca-options-group', 'agca_admin_capability' );
        register_setting( 'agca-options-group', 'agca_disablewarning' );
        register_setting( 'agca-template-group', 'agca_selected_template' );
        register_setting( 'agca-template-group', 'agca_templates' );
        //delete_option( 'agca_templates' );


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
            'agca_login_banner',
            'agca_login_banner_text',
            'agca_login_photo_remove',
            'agca_login_photo_url',
            'agca_login_photo_href',
            'agca_login_round_box',
            'agca_login_round_box_size',
            'agca_dashboard_icon',
            'agca_dashboard_text',
            'agca_dashboard_text_paragraph',
            'agca_dashboard_widget_welcome',
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
            'agca_admin_bar_admin_themes',
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
            'agca_selected_template',
            'agca_templates',
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
            $optionValue = "";
            $optionValue = $savedOptions[$optionName];

            if($optionName == "ag_edit_adminmenu_json" || "ag_edit_adminmenu_json_new"|| $optionName == "ag_add_adminmenu_json" ||$optionName == "ag_colorizer_json"){
                $optionValue = str_replace("\\\"", "\"", $optionValue);
                $optionValue = str_replace("\\\'", "\'", $optionValue);
            }else if($optionName == "agca_custom_js" || $optionName == "agca_custom_css"){
                //fb($optionValue);
                $optionValue = htmlspecialchars_decode($optionValue);
                $optionValue = str_replace("\'", '"', $optionValue);
                $optionValue = str_replace('\"', "'", $optionValue);
                //fb($optionValue);
            }else{

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
        echo $str;
        die();
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }



    function agca_create_menu() {
        add_management_page('AG Custom Admin', 'AG Custom Admin', 'administrator', __FILE__, array(&$this,'agca_admin_page') );
    }

    function agca_create_admin_button($name,$arr) {

        $href = $arr["value"];
        $target =$arr["target"];
        $button ="<li class=\"wp-not-current-submenu menu-top menu-top-last\" id=\"menu-$name\"><a href=\"$href\" target=\"$target\" class=\"wp-not-current-submenu menu-top\"><div class=\"wp-menu-arrow\"><div></div></div><div class=\"wp-menu-image dashicons-before dashicons-admin-$name\" style=\"width:15px\"><br></div><div class=\"wp-menu-name\">$name</div></a></li>";

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
                    $array.='<tr><td colspan="2"><button target="'.$v['target'].'" title="'.$v['value'].'" type="button">'.$k.'</button>&nbsp;<a style="cursor:pointer;" title="Edit" class="button_edit"><span class="dashicons dashicons-edit"></span></a>&nbsp;<a style="cursor:pointer" title="Delete" class="button_remove"><span class="dashicons dashicons-no"></span></a></td><td></td></tr>';
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
        global $wp_meta_boxes;
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
            echo get_option('agca_custom_css');
            if(get_option('agca_menu_remove_customize_button')){
                echo '#wp-admin-bar-customize{display:none;}';
            }
            ?>
            </style><?php
        }

        if(get_option('agca_admin_bar_frontend_hide')==true){
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
        if(get_option('agca_admin_bar_frontend')!=true && is_user_logged_in()){

            $this->context = "page";
            //$wpversion = $this->get_wp_version();
            ?>
            <script type="text/javascript">
                var agca_version = "<?php echo $this->agca_version; ?>";
                var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
                var jQueryScriptOutputted = false;
                var agca_context = "page";
                var agca_orig_admin_menu = [];
                function initJQuery() {
                    //if the jQuery object isn't available
                    if (typeof(jQuery) == 'undefined') {
                        if (! jQueryScriptOutputted) {
                            //only output the script once..
                            jQueryScriptOutputted = true;
                            //output the script (load it from google api)
                            document.write("<scr" + "ipt type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js\"></scr" + "ipt>");
                        }
                        setTimeout("initJQuery()", 50);
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
                <?php echo "var agca_global_plugin_url = '".$this->pluginUrl()."';"; ?>
            </script>
            <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/ag_script.js?ver=<?php echo $this->agca_version; ?>"></script>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    <?php if(get_option('agca_colorizer_turnonoff') == 'on' && (get_option('agca_admin_bar_frontend_hide')!=true)){
                            foreach($this->colorizer as $k => $v){
                                if(($k !="") and ($v !="")){
                                    if(
                                        $k == "color_header" ||
                                        $k == "color_font_header"
                                    ){
                                        ?> updateTargetColor("<?php echo $k;?>","<?php echo $v;?>"); <?php
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
        <?php if(get_option('agca_admin_bar_admin_themes')!=""){  ?>
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-agca-admin-themes").css("display","none");
        <?php } ?>



        <?php if(get_option('agca_header_logo')==true){ ?>
            jQuery("#wphead #header-logo").css("display","none");
            jQuery("ul#wp-admin-bar-root-default li#wp-admin-bar-wp-logo").css("display","none");

        <?php } ?>
        <?php if(get_option('agca_header_logo_custom')!=""){ ?>

            var img_url = '<?php echo addslashes(get_option('agca_header_logo_custom')); ?>';

            advanced_url = img_url;
            image = jQuery("<img style='max-width:98%;position:relative;'/>").attr("src",advanced_url);
            jQuery(image).load(function() {
            jQuery("#wpbody-content").prepend(image);
            });

        <?php } ?>
        <?php if(get_option('agca_wp_logo_custom')!=""){ ?>
            jQuery("li#wp-admin-bar-wp-logo a.ab-item span.ab-icon")
            .html("<img style=\"height:32px;margin-top:0\" src=\"<?php echo get_option('agca_wp_logo_custom'); ?>\" />")
            .css('background-image','none')
            .css('width','auto');
            jQuery("li#wp-admin-bar-wp-logo > a.ab-item")
            .attr('href',"<?php echo get_bloginfo('wpurl'); ?>")
            .css('padding', 0);
            jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-item:before").attr('title','');
            jQuery('body #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon').attr('class','ab-icon2');
            jQuery("#wp-admin-bar-wp-logo").show();
        <?php }?>
        <?php if(get_option('agca_remove_site_link')==true){ ?>
            jQuery("#wp-admin-bar-site-name").css("display","none");

        <?php } ?>
        <?php if(get_option('agca_wp_logo_custom_link')!=""){ ?>
            var href = "<?php echo get_option('agca_wp_logo_custom_link'); ?>";
            href = href.replace("%BLOG%", "<?php echo get_bloginfo('wpurl'); ?>");
            if(href == "%SWITCH%"){
            href = "<?php echo get_bloginfo('wpurl'); ?>";
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
            jQuery("#wphead #site-heading").after('<h1><?php echo addslashes(get_option('agca_custom_site_heading')); ?></h1>');
            jQuery("#wp-admin-bar-site-name a:first").html('<?php echo addslashes(get_option('agca_custom_site_heading')); ?>');

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
            jQuery("#wpbody-content").prepend('<a href="../wp-login.php?action=logout" tabindex="10" style="float:right;margin-right:20px" class="ab-item agca_logout_button"><?php echo $agca_logout_text; ?></a>');


        <?php } ?>
        <?php if(get_option('agca_howdy')!=""){ ?>
            var alltext="";
            alltext="";
            jQuery('li#wp-admin-bar-my-account').css('cursor','default');
            alltext = jQuery('li#wp-admin-bar-my-account').html();
            if(alltext!=null){
            var parts = alltext.split(',');
            alltext = "<?php echo get_option('agca_howdy'); ?>" + ", " + parts[1];
            }
            jQuery("li#wp-admin-bar-my-account").html("<a href=\"#\" class=\"ab-item\">"+alltext+"</a>");

        <?php } ?>
        <?php
        if(get_option('agca_custom_title')!=""){
            //add_filter('admin_title', '$this->change_title', 10, 2);

        }
        ?>
        <?php if(get_option('agca_logout')!=""){ ?>
            jQuery("ul#wp-admin-bar-user-actions li#wp-admin-bar-logout a").text("<?php echo get_option('agca_logout'); ?>");
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

    function updateAllColors(){

        ?>
        function updateAllColors(){
        <?php
        foreach($this->colorizer as $k => $v){
            if(($k !="") and ($v !="")){
                ?> updateTargetColor("<?php echo $k;?>","<?php echo $v;?>"); <?php
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
        /*echo $selectedValue;
        die;*/
        ksort($capabs);
        foreach($capabs as $k=>$v){
            $selected = "";
            if($this->startsWith($k, 'level_')) continue;
            if($selectedValue == $k){
                $selected = " selected=\"selected\" ";
            }
            //TODO:Find out why this does not work
            //$capabilitySelector .="<option val=\"$k\" $selected >".str_replace(' ', ' ', ucwords(str_replace('_', ' ', $k))) ."</option>\n";
            $capabilitySelector .="<option value=\"$k\" $selected >".$k."</option>\n";
        }

        $this->admin_capabilities  = "<select class=\"agca-selectbox\" id=\"agca_admin_capability\"  name=\"agca_admin_capability\" val=\"upload_files\">".$capabilitySelector."</select>";
    }

    function admin_capability(){
        $selectedValue = get_option('agca_admin_capability');
        if($selectedValue == ""){
            $selectedValue = "edit_dashboard";
        }
        return $selectedValue;
    }

    function JSPrintAGCATemplateSettingsVar($settings){
        echo "\n<script type=\"text/javascript\">\n";
        echo "var agca_template_settings = ".preg_replace('#<script(.*?)>(.*?)</script>#is', '', $settings).";\n";  //TODO: think about this
        echo "</script>";
    }

    function appendSettingsToAGCATemplateCustomizations($customizations, $settings){
        $template_settings = json_decode($settings);
        //print_r($template_settings);
        foreach($template_settings as $sett){
            $key = $sett->code;

            //use default value if user's value is not set
            $value="";
            if($sett->value != ""){
                $value = $sett->value;
            }else{
                $value = $sett->default_value;
            }

            //Prepare settings
            if($sett->type == 6){
                if($value !== null && (strtolower($value) == "on" || $value == "1")){
                    $value = "true";
                }else{
                    $value = "false";
                }
            }
            $customizations = str_replace("%".$key."%",$value, $customizations);
        }
        return $customizations;
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

    function prepareAGCAAdminTemplates(){
        if(get_option( 'agca_templates' ) != ""){
            //print_r(get_option( 'agca_templates' ));
            $themes = get_option( 'agca_templates' );
            $selectedTheme = get_option('agca_selected_template');
            if(isset($themes[$selectedTheme])){
                $theme = $themes[$selectedTheme];
                add_filter('get_user_option_admin_color', array(&$this,'change_admin_color'));

                echo (stripslashes($theme['common']));
                echo "<!--AGCAIMAGES: ".$theme['images']."-->";

                //KEEP THIS FOR MIGRATION PURPOSE FOR SOME TIME
                if(!((get_option('agca_role_allbutadmin')==true) and  (current_user_can($this->admin_capability())))){
                    if($theme['settings'] == "" || $theme['settings'] == " ") $theme['settings'] = "{}";
                    //print_r($templdata);

                    $this->JSPrintAGCATemplateSettingsVar($theme['settings']);

                    $admindata = $this->appendSettingsToAGCATemplateCustomizations(stripslashes($theme['admin']), $theme['settings']);
                    $admindata = $this->enableSpecificWPVersionCustomizations($admindata);
                    $admindata = $this->removeCSSComments($admindata);

                    //echo $admindata;
                    //REPLACE TAGS WITH CUSTOM TEMPLATE SETTINGS
                    $this->templateCustomizations = $admindata;
                }
                //KEEP THIS FOR MIGRATION PURPOSE FOR SOME TIME
            }
        }
    }

    function agcaAdminSession(){
        $agcaTemplatesSession = array();

        //session_destroy();
        //session_unset();

        /*if(!session_id()){
            session_start();
        }*/

        if(!isset($_SESSION["AGCA"])){
            $_SESSION["AGCA"] = array();
            $_SESSION["AGCA"]["Templates"] = array();
        }
        //print_r($_SESSION);

        if(isset($_SESSION["AGCA"])){
            if(isset($_SESSION["AGCA"]["Templates"])){
                //print_r($_SESSION["AGCA"]["Templates"]);
                $agcaTemplatesSession = json_encode($_SESSION["AGCA"]["Templates"]);
            }
        }


        if($agcaTemplatesSession == '""' || $agcaTemplatesSession == '"[]"'){
            $agcaTemplatesSession = array();
        }


        return $agcaTemplatesSession;

    }

    function getAGCAColor($name){
        if(isset($this->colorizer[$name])){
            echo htmlspecialchars($this->colorizer[$name]);
        }
    }

    function prepareAGCALoginTemplates(){
        if(get_option( 'agca_templates' ) != ""){
            //print_r(get_option( 'agca_templates' ));
            $templates = get_option( 'agca_templates' );
            foreach($templates as $templname=>$templdata){
                if($templname == get_option('agca_selected_template')){
                    echo (stripslashes($templdata['common']));

                    if($templdata['settings'] == "" || $templdata['settings'] == " ") $templdata['settings'] = "{}";
                    $this->JSPrintAGCATemplateSettingsVar($templdata['settings']);

                    $logindata = $this->appendSettingsToAGCATemplateCustomizations(stripslashes($templdata['login']), $templdata['settings']);
                    $logindata = $this->enableSpecificWPVersionCustomizations($logindata);
                    $logindata = $this->removeCSSComments($logindata);

                    echo($logindata);
                    break;
                }
            }
        }
    }

    function agca_error_check(){
        ?>
        <script type="text/javascript">
            function AGCAErrorPage(msg, url, line){
                var agca_error_details = "___________________________________________________<br/>";
                agca_error_details += '<br/>' + msg +'<br/>source:' + url + '<br/>line:' + line + '<br/>';
                agca_error_details += "___________________________________________________<br/>";
                window.agca_error_details_text = agca_error_details + '<br/><?php _e('This JavaScript error can stop AG Custom Admin plugin to work properly. If everything still works, you can ignore this notification.', 'ag-custom-admin'); ?> <br/><br/><?php _e('Possible solutions', 'ag-custom-admin'); ?>:<br/><br/>1) <?php _e('Make sure to have everything up to date: WordPress site, plugins and themes.', 'ag-custom-admin'); ?><br/><br/>2) <?php _e('Try disabling plugins one by one to see if problem can be resolved this way. If so, one of disabled plugins caused this error.', 'ag-custom-admin'); ?><br/><br/>3) <?php _e('Check "source" path of this error. This could be indicator of the plugin/theme that caused the error.', 'ag-custom-admin'); ?><br/><br/>4) <?php _e('If it\\\'s obvious that error is thrown from a particular plugin/theme, please report this error to their support.', 'ag-custom-admin'); ?> <br/><br/>5) <?php _e('Try activating default WordPress theme instead of your current theme.', 'ag-custom-admin'); ?><br/><br/>6) <?php _e('Advanced: Try fixing this issue manually: Navigate to the link above in your browser and open the source of the page (right click -> view page source) and find the line in code where it fails. You should access this file via FTP and try to fix this error on that line.', 'ag-custom-admin') ?><br/><br/>7) <?php _e('Contact us if nothing above helps. Please do not post errors that are caused by other plugins/themes to our support page. Contact their support instead. If you think that error is somehow related to AG Custom Admin plugin, or something unexpected happens, please report that on our', 'ag-custom-admin'); ?> <a href="http://wordpressadminpanel.com/agca-support/ag_custom_admin/error-ocurred-javascript-error-caught/" target="_blank"><?php _e('SUPPORT PAGE', 'ag-custom-admin'); ?></a>';
                document.getElementsByTagName('html')[0].style.visibility = "visible";
                var errorDivHtml = '<div style="background: #f08080;border-radius: 3px;color: #ffffff;height: auto; margin-right: 13px;padding: 6px 14px;width: 450px;z-index: 99999; position:absolute;">\
                        <?php _e('AG Custom Admin caught an error on your site!', 'ag-custom-admin'); ?>&nbsp;<a target="_blank" href="#" onclick="var aedt = document.getElementById(\'agca_error_details_text\'); if(aedt.style.display !== \'block\') {aedt.style.display = \'block\';} else{aedt.style.display = \'none\';} return false;"  style="color: #ffffff !important;float:right;font-weight: bold;text-decoration: none;">(<?php _e('show/hide more...', 'ag-custom-admin'); ?>)</a><div id="agca_error_details_text" style="display:none;margin: 10px 0;background:#ffffff;border-radius: 5px;padding:8px;color: #777;">'+agca_error_details_text+'</div></div>';

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
    function error_check(){
        ?>
        <script type="text/javascript">
            function AGCAErrorOtherPages(msg, url, line){
                var agca_error_details = "___________________________________________________\n";
                agca_error_details += '\n' + msg +'\nsource:' + url + '\nline:' + line + '\n';

                document.getElementsByTagName('html')[0].style.visibility = "visible";

                if(typeof window.console === "object"){
                    console.log("___________________________________________________");
                    console.log("<?php _e('AG Custom Admin caught a JavaScript on your site', 'ag-custom-admin'); ?>:");
                    console.log(agca_error_details);
                }
            }
            window.onerror = function(msg, url, line) {
                window.onload = function() {
                    AGCAErrorOtherPages(msg, url, line);
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

        //var_dump($menu); die;
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
            $name = $this->menu_item_cleartext($name);

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
                    $urlSub = $sub[2];
                    $removeSub = false;
                    $nameSub = $this->menu_item_cleartext($nameSub);
                    $s[$key]=array(
                        'name'=>$nameSub,
                        'new'=>'',
                        'remove'=>$removeSub,
                        'url'=>$urlSub
                    );

                    if(isset($pc['submenus'][$key])){
                        $s[$key]['new'] = $pc['submenus'][$key]['new'];
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
                'url'=>$url,
                'cls'=>$cls,
                'submenus'=>$s
            );

            //apply previous top menu customizations
            if($customizationsSet && isset($previousCustomizations[$url])){
                $pc = $previousCustomizations[$url];
                if(isset($pc)){
                    $m[$url]['remove'] = $pc['remove'];
                    $m[$url]['new'] = $pc['new'];
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

        //print_r($submenu);die;
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
        /*print_r($menu);
        print_r($customizations);
        print_r($textboxes);*/

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
    function isCusminActive(){
        return $this->isPluginActive('cusmin/cusmin.php');
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
    function print_admin_css()
    {
        $agcaTemplateSession = $this->agcaAdminSession();
        $wpversion = $this->get_wp_version();
        $this->context = "admin";
        $this->error_check();
        $currentScreen = get_current_screen();
        ?>
        <script type="text/javascript">
            var wpversion = "<?php echo $wpversion; ?>";
            var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
            var agca_version = "<?php echo $this->agca_version; ?>";
            var agcaTemplatesSession = <?php echo ($agcaTemplateSession==null)?"[]":$agcaTemplateSession; ?>;
            var errors = false;
            var isSettingsImport = false;
            var isCusminActive = <?php echo $this->isCusminActive()?'true':'false'; ?>;
            var agca_context = "admin";
            var roundedSidberSize = 0;
            var agca_installed_templates = <?php echo $this->get_installed_agca_templates(); ?>;
            var agca_admin_menu = <?php echo json_encode($this->get_menu_customizations()) ?>;
            var agca_string = {
                file_imp_not_sel: '<?php _e('File for import is not selected!', 'ag-custom-admin'); ?>',
                menu_general: '<?php _e('General', 'ag-custom-admin'); ?>',
                menu_admin_bar: '<?php _e('Admin Bar', 'ag-custom-admin'); ?>',
                menu_footer: '<?php _e('Footer', 'ag-custom-admin'); ?>',
                menu_dashb: '<?php _e('Dashboard', 'ag-custom-admin'); ?>',
                menu_login: '<?php _e('Login Page', 'ag-custom-admin'); ?>',
                menu_admin_menu: '<?php _e('Admin Menu', 'ag-custom-admin'); ?>',
                menu_colorizer: '<?php _e('Colorizer', 'ag-custom-admin'); ?>',
                menu_themes: '<?php _e('Themes', 'ag-custom-admin'); ?>',
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
        $this->prepareAGCAAdminTemplates();
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


                    //get saved onfigurations

                    <?php   $buttons = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttons'); ?>
                    var buttons = '<?php echo $buttons; ?>';

                    <?php   $buttonsJq = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttonsJq'); ?>
                    var buttonsJq = '<?php echo $buttonsJq; ?>';

                    createEditMenuPageNew(agca_admin_menu);
                    //createEditMenuPageV32(checkboxes, textboxes);

                    <?php
                    //if admin, and option to hide settings for admin is set
                    if((get_option('agca_role_allbutadmin')==true) and current_user_can($this->admin_capability()) or $this->isCusminActive()){
                    ?>
                    <?php } else{ ?>
                    <?php if(get_option('agca_admin_menu_brand')!=""){ ?>
                    additionalStyles = ' style="margin-bottom:-4px" ';
                    jQuery("#adminmenu").before('<div '+additionalStyles+' id="sidebar_adminmenu_logo"><img width="160" src="<?php echo get_option('agca_admin_menu_brand'); ?>" /></div>');
                    <?php } ?>
                    <?php if(get_option('agca_admin_menu_brand_link')!=""){ ?>

                    var href = "<?php echo get_option('agca_admin_menu_brand_link'); ?>";
                    href = href.replace("%BLOG%", "<?php echo get_bloginfo('wpurl'); ?>");

                    jQuery("#sidebar_adminmenu_logo").attr('onclick','window.open(\"'+ href+ '\");');
                    jQuery("#sidebar_adminmenu_logo").attr('title',href);

                    <?php }else{ ?>
                    href = "<?php echo get_bloginfo('wpurl'); ?>";
                    jQuery("#sidebar_adminmenu_logo").attr('onclick','window.open(\"'+ href+ '\");');
                    jQuery("#sidebar_adminmenu_logo").attr('title',href);
                    <?php } ?>

                    <?php if(get_option('agca_admin_menu_submenu_round')==true){ ?>
                    jQuery("#adminmenu .wp-submenu").css("border-radius","<?php echo get_option('agca_admin_menu_submenu_round_size'); ?>px");
                    jQuery("#adminmenu .wp-menu-open .wp-submenu").css('border-radius','');
                    <?php $roundedSidebarSize = get_option('agca_admin_menu_submenu_round_size'); ?>
                    roundedSidberSize = <?php echo ($roundedSidebarSize == "")?"0":$roundedSidebarSize; ?>;


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
                    jQuery("#footer-left").html('<?php echo addslashes(get_option('agca_footer_left')); ?>');
                    <?php } ?>
                    <?php if(get_option('agca_footer_left_hide')==true){ ?>
                    jQuery("#footer-left").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_footer_right')!=""){ ?>
                    jQuery("#footer-upgrade").html('<?php echo addslashes(get_option('agca_footer_right')); ?>');
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
                    jQuery("#dashboard-widgets-wrap").parent().find("h1").html("<?php echo addslashes(get_option('agca_dashboard_text')); ?>");
                    <?php } ?>
                    <?php if(get_option('agca_dashboard_text_paragraph')!=""){
                                                        require_once($this->filePath('wp-includes/formatting.php'));
                                        ?>
                    jQuery("#wpbody-content #dashboard-widgets-wrap").before('<div id="agca_custom_dashboard_content"></div>');

                    jQuery("#agca_custom_dashboard_content").html('<br /><?php echo preg_replace('/(\r\n|\r|\n)/', '\n', addslashes(wpautop(get_option('agca_dashboard_text_paragraph')))); ?>');
                    <?php } ?>

                    <?php /*Remove Dashboard widgets*/ ?>
                    <?php

                        if(get_option('agca_dashboard_widget_welcome')==true){
                            ?>jQuery("#welcome-panel").css("display","none");<?php
                        }else{
                            ?>jQuery("#welcome-panel").css("display","block");<?php
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
                    jQuery('#adminmenu').append('<?php echo $this->agca_create_admin_button('AG Custom Admin',array('value'=>'tools.php?page=ag-custom-admin/plugin.php#general-settings','target'=>'_self')); ?>');
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
        $this->error_check();
        //$wpversion = $this->get_wp_version();

        ?>
        <script type="text/javascript">
            document.write('<style type="text/css">html{visibility:hidden;}</style>');
            var agca_version = "<?php echo $this->agca_version; ?>";
            <?php //var wpversion = "echo $wpversion; ?>
            var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
            var isCusminActive = <?php echo $this->isCusminActive()?'true':'false'; ?>;
            var isSettingsImport = false;
            var agca_context = "login";
        </script>
        <?php
        $this->prepareAGCALoginTemplates();
        $this->agca_get_includes();

        ?>

        <script type="text/javascript">


            /* <![CDATA[ */
            jQuery(document).ready(function() {
                try{
                    <?php if(get_option('agca_login_round_box')==true){ ?>
                    jQuery("form#loginform").css("border-radius","<?php echo get_option('agca_login_round_box_size'); ?>px");
                    jQuery("#login h1 a").css("border-radius","<?php echo get_option('agca_login_round_box_size'); ?>px");
                    jQuery("#login h1 a").css("margin-bottom",'10px');
                    jQuery("#login h1 a").css("padding-bottom",'0');
                    jQuery("form#lostpasswordform").css("border-radius","<?php echo get_option('agca_login_round_box_size'); ?>px");
                    <?php } ?>
                    <?php if(get_option('agca_login_banner')==true){ ?>
                    jQuery("#backtoblog").css("display","none");
                    <?php } ?>
                    <?php if(get_option('agca_login_banner_text')==true){ ?>
                    jQuery("#backtoblog a").html('<?php echo "← " . addslashes(get_option('agca_login_banner_text')); ?>');
                    <?php } ?>
                    <?php if(get_option('agca_login_photo_url')==true && get_option('agca_login_photo_remove')!=true){ ?>
                    advanced_url = "<?php echo get_option('agca_login_photo_url'); ?>";
                    var $url = "url(" + advanced_url + ")";
                    jQuery("#login h1 a").css("background",$url+' no-repeat');
                    jQuery("#login h1 a").hide();
                    image = jQuery("<img />").attr("src",advanced_url);
                    jQuery(image).load(function() {
                        var originalWidth = 326;
                        var widthDiff = this.width - originalWidth;
                        jQuery("#login h1 a").height(this.height);
                        jQuery("#login h1 a").width(this.width);
                        jQuery("#login h1 a").css("background-size",this.width+"px "+this.height+"px");

                        var loginWidth = jQuery('#login').width();
                        var originalLoginWidth = 320;
                        var photoWidth = this.width;

                        if(loginWidth > photoWidth){
                            jQuery("#login h1 a").css('margin','auto');
                        }else{
                            jQuery("#login h1 a").css('margin-left',-(widthDiff/2)+((loginWidth-originalLoginWidth)/2)+"px");
                        }

                        jQuery("#login h1 a").show();
                    });
                    <?php } ?>
                    <?php if(get_option('agca_login_photo_href')==true){ ?>
                    var $href = "<?php echo get_option('agca_login_photo_href'); ?>";
                    $href = $href.replace("%BLOG%", "<?php echo get_bloginfo('wpurl'); ?>");

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
                            jQuery(this).attr('href','<?php echo get_option('agca_login_register_href'); ?>');
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
                    jQuery("body.login, html").css("background","<?php echo $this->colorizer['login_color_background'];?>");


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
        return trailingslashit(plugins_url(basename(dirname(__FILE__))));
    }

    function agca_admin_page() {

        $wpversion = $this->get_wp_version();
        $this->agca_error_check();
        ?>
        <?php //includes ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl();  ?>style/farbtastic.css?ver=<?php echo $wpversion; ?>" />
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/farbtastic.js?ver=<?php echo $wpversion; ?>"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo $this->pluginUrl(); ?>style/agca_farbtastic.css?ver=<?php echo $wpversion; ?>" />
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/agca_farbtastic.js?ver=<?php echo $wpversion; ?>"></script>
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/xd.js?ver=<?php echo $wpversion; ?>"></script>
        <script type="text/javascript">
            var templates_ep = "<?php echo $this->templates_ep; ?>";
            var template_selected = '<?php echo get_option('agca_selected_template'); ?>';
        </script>
        <script type="text/javascript" src="<?php echo $this->pluginUrl(); ?>script/agca_tmpl.js?ver=<?php echo $wpversion; ?>"></script>
        <?php //includes ?>
        <div class="wrap">
            <h1 id="agca-title"><img src="<?php echo plugins_url( 'images/agca.png', __FILE__ ) ?>" /><span class="title">AG Custom Admin <?php _e('Settings', 'ag-custom-admin'); ?></span> <span class="version">(v<?php echo $this->agca_version; ?>)</span></h1>
            <div id="agca-social" style="float:right; margin-top: -23px;">
                <div class="fb-like" data-href="https://www.facebook.com/AG-Custom-Admin-892218404232342/timeline" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>
            </div>
            <div id="agca_error_placeholder"></div>
            <div id="agca_news">&nbsp;</div><br />
            <form method="post" id="agca_form" action="options.php">
                <?php wp_nonce_field('agca_form','_agca_token'); ?>
                <?php settings_fields( 'agca-options-group' ); ?>
                <div id="agca-your-feedback">
                    <strong>
                        <span style="color:#005B69">Your feedback:</span>
                    </strong>
                    <a class="feedback positive" target="_blank" title="<?php _e('POSITIVE FEEDBACK: I like this plugin!', 'ag-custom-admin'); ?>" href="http://wordpressadminpanel.com/agca-support/feedback/ag-custom-admin-positive-feedback/">
                        <span class="dashicons dashicons-thumbs-up"></span>
                    </a>
                    <a class="feedback negative" target="_blank" title="<?php _e('NEGATIVE FEEDBACK: I don\'t like this plugin.', 'ag-custom-admin'); ?>" href="http://wordpressadminpanel.com/agca-support/feedback/ag-custom-admin-negative-feedback/">
                        <span class="dashicons dashicons-thumbs-down"></span>
                    </a>
                </div>
                <br />
                <ul id="ag_main_menu" style="<?php echo $this->isCusminActive()?'display:none':''; ?>">
                    <li class="selected" style="border-top-left-radius: 10px; "><a href="#general-settings" title="<?php _e('General Settings', 'ag-custom-admin')?>" ><?php _e('General', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#admin-bar-settings" title="<?php _e('Settings for admin bar', 'ag-custom-admin')?>" ><?php _e('Admin Bar', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#admin-footer-settings" title="<?php _e('Settings for admin footer', 'ag-custom-admin')?>" ><?php _e('Footer', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#dashboad-page-settings" title="<?php _e('Settings for Dashboard page', 'ag-custom-admin')?>"><?php _e('Dashboard', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#login-page-settings" title="<?php _e('Settings for Login page', 'ag-custom-admin')?>"><?php _e('Login Page', 'ag-custom-admin')?></a></li>
                    <li class="normal" ><a href="#admin-menu-settings" title="<?php _e('Settings for main admin menu', 'ag-custom-admin')?>"><?php _e('Admin Menu', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#ag-colorizer-settings" title="<?php _e('Colorizer settings', 'ag-custom-admin')?>"><?php _e('Colorizer', 'ag-custom-admin')?></a></li>
                    <li class="normal"><a href="#ag-advanced" title="<?php _e('My custom scripts', 'ag-custom-admin')?>"><?php _e('Advanced', 'ag-custom-admin')?></a></li>
                    <li class="normal" style=""><a style="color:#DB6014;font-weight:bolder;" href="#ag-templates" title="<?php _e('AG Custom Admin Themes', 'ag-custom-admin')?>"><?php _e('Themes', 'ag-custom-admin')?></a></li>
                    <li class="normal upgrade"><a href="https://cusmin.com/upgrade-to-cusmin?ref=menu" target="_blank" title="<?php _e('Upgrade to Cusmin', 'ag-custom-admin')?>"><img src="<?php echo plugins_url( 'images/cusminlogo.png', __FILE__ ) ?>" /><?php _e('Upgrade', 'ag-custom-admin')?></a></li>

                    <li style="background:none;border:none;padding:0;"><a id="agca_donate_button" target="_blank" style="margin-left:8px" title="<?php _e('Do you like this plugin? You can support its future development by giving a donation by your choice', 'ag-custom-admin')?> " href="http://wordpressadminpanel.com/agca-support/support-for-future-development/"><img alt="<?php _e('Donate', 'ag-custom-admin')?>" src="<?php echo $this->pluginUrl(); ?>images/btn_donate_LG.gif" /></a>
                    </li>
                    <li style="background:none;border:none;padding:0;padding-left:10px;margin-top:-7px"></li>
                </ul>
                <div id="agca_advertising">
                    <ul>
                        <li style="min-height:105px;display: block"></li>
                    </ul>
                </div>
                <div class="agca-clear"></div>
                <div id="section-cusmin" style="display:none;"><?php _e('All AG Custom Admin plugin\'s settings, except admin themes, are disabled. Please use', 'ag-custom-admin')?> <a href="options-general.php?page=cusmin">Cusmin</a> <?php _e('plugin to manage these settings.', 'ag-custom-admin')?></div>
                <div id="section_general" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('General Settings', 'ag-custom-admin')?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <p tabindex="0" class="agca-clear agca-tip"><i><?php _e('<strong>Tip: </strong>Move mouse cursor over the option label to see more information about an option', 'ag-custom-admin')?></i></p>
                    <table class="agca-clear form-table" width="500px">
                        <?php

                        $this->print_checkbox(array(
                            'name'=>'agca_role_allbutadmin',
                            'label'=>__('Exclude AGCA admin from customizations', 'ag-custom-admin'),
                            'title'=>__('<h3>Applying customizations</h3><br><strong>Checked</strong> - apply to all users, except admin<br><strong>Not checked</strong> - apply to everyone</br></br><strong>Q</strong>: Who is AGCA administrator?</br><strong>A</strong>: Go to <i>General -> Security -> AGCA admin capability</i> and change capability option to define administrators. Only the users with selected capability will be AGCA administrators.</br>', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Pages', 'ag-custom-admin'));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_screen_options_menu',
                            'label'=>__('"Screen Options" menu', 'ag-custom-admin'),
                            'title'=>__('Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_help_menu',
                            'label'=>__('"Help" menu', 'ag-custom-admin'),
                            'title'=>__('Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Hides colors scheme on profile page', 'ag-custom-admin'),
                            'name'=>'agca_profile_color_scheme',
                            'hide'=>true,
                            'label'=>__('Profile Color Scheme', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Security', 'ag-custom-admin'));

                        ?>

                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Choose which WordPress capability will be used to distinguish AGCA admin users from other users.</br>AGCA admin users have access to AGCA settings. AGCA administrators can be excluded from customizations if that option is checked', 'ag-custom-admin'); ?>" for="agca_admin_capability"><?php _e('AGCA admin capability', 'ag-custom-admin'); ?>:</label>
                            </th>
                            <td><?php echo $this->admin_capabilities; ?>&nbsp;&nbsp;<i>(<?php _e('<strong>Edit Dashboard</strong> - selected by default', 'ag-custom-admin'); ?>)</i>
                                <p style="margin-left:5px;"><i><?php _e('Find more information about', 'ag-custom-admin'); ?> <a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank"><?php _e('WordPress capabilities', 'ag-custom-admin'); ?></a></i></p>
                            </td>
                            <td>
                            </td>
                        </tr>
                        <?php
                        $this->print_options_h3(__('Feedback and Support', 'ag-custom-admin'));

                        ?>
                        <tr valign="center">
                            <td colspan="2">
                                <div class="agca-feedback-and-support">
                                    <ul>
                                        <li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=feature" target="_blank"><span class="dashicons dashicons-lightbulb"></span>&nbsp;&nbsp;<?php _e('Idea for improvement', 'ag-custom-admin'); ?></a> - <?php _e('submit your idea for improvement', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=bug" target="_blank"><span class="dashicons dashicons-megaphone"></span>&nbsp;&nbsp;<?php _e('Report an issue', 'ag-custom-admin'); ?></a> - <?php _e('If plugin does not work as expected', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=theme" target="_blank"><span class="dashicons dashicons-art"></span>&nbsp;&nbsp;<?php _e('Idea for admin theme', 'ag-custom-admin'); ?></a> - <?php _e('submit your idea for admin theme', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="https://wordpress.org/support/view/plugin-reviews/ag-custom-admin" target="_blank"><span class="dashicons dashicons-awards"></span>&nbsp;&nbsp;<?php _e('Add a review on WordPress.org', 'ag-custom-admin'); ?></a> - <?php _e('add your review and rate us on WordPress.org', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="http://wordpressadminpanel.com/agca-support/" target="_blank"><span class="dashicons dashicons-shield-alt"></span>&nbsp;&nbsp;<?php _e('Visit our support site', 'ag-custom-admin'); ?></a> - <?php _e('for any other questions, feel free to contact us', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul>
                                        <li><a href="http://wordpressadminpanel.com/agca-support/support-for-future-development/" target="_blank"><span class="dashicons dashicons-palmtree"></span>&nbsp;&nbsp;<?php _e('Donate', 'ag-custom-admin'); ?></a> - <?php _e('only if you find this plugin helpful for your needs', 'ag-custom-admin'); ?> </li>
                                    </ul>
                                    <ul class="upgrade">
                                        <li><a href="https://cusmin.com/upgrade-to-cusmin?ref=page" target="_blank">
                                                <img src="<?php echo plugins_url( 'images/cusminlogo.png', __FILE__ ) ?>" />
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
                            'title'=>__('Hides admin bar completely from the admin panel', 'ag-custom-admin'),
                            'name'=>'agca_header',
                            'label'=>__('<strong>Admin bar</strong>', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant="#agca_header_show_logout_content"',
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
                            'label'=>__('Site pages: Admin bar customizations', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Hides admin bar completely for authenticated users on site pages.', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_frontend_hide',
                            'hide'=>true,
                            'label'=>__('Site pages: Admin bar', 'ag-custom-admin')
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
                            'hint' =>__('Link', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Customize WordPress title using custom title template.</br></br>Examples', 'ag-custom-admin').':</br><strong>%BLOG% -- %PAGE%</strong>  '.'('.__('will be', 'ag-custom-admin').')'.' <i>My Blog -- Add New Post</i></br><strong>%BLOG%</strong> ('.__('will be', 'ag-custom-admin').') <i>My Blog</i></br><strong>My Company > %BLOG% > %PAGE%</strong> ('.__('will be', 'ag-custom-admin').') <i>My Company > My Blog > Tools</i>',
                            'name'=>'agca_custom_title',
                            'label'=>__('Page title template', 'ag-custom-admin'),
                            'hint' =>__('Please use', 'ag-custom-admin').' <strong>%BLOG%</strong> '.__('and', 'ag-custom-admin'). ' <strong>%PAGE%</strong> '.__('in your title template.nd', 'ag-custom-admin')
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
                            'label'=>__('Admin bar logo', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides WordPress context menu on admin bar logo from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_remove_top_bar_dropdowns',
                            'label'=>__('Admin bar logo context menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides site name link from the admin bar', 'ag-custom-admin'),
                            'name'=>'agca_remove_site_link',
                            'label'=>__('Site name', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides update notifications from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_update_notifications',
                            'label'=>__('Update notifications', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides comments block from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_comments',
                            'label'=>__('"Comments" block', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'style'=>'margin-top:20px;'
                            ),
                            'title'=>__('Hides "+ New" block and its context menu from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content',
                            'label'=>__('"+ New" block', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant=".new_content_header_submenu"',
                            'input-class'=>'has-dependant dependant-opposite'
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides "Post" sub-menu from "+ New" block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_post',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('"+ New" -> Post sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides "Link" sub-menu from "+ New" block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_link',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('"+ New" -> Link sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides "Page" sub-menu from "+ New" block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_page',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('"+ New" -> Page sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides "User" sub-menu from "+ New" block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_user',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('"+ New" -> User sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'attributes'=>array(
                                'class'=>'new_content_header_submenu'
                            ),
                            'title'=>__('Hides "Media" sub-menu from "+ New" block on admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_new_content_media',
                            'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('"+ New" -> Media sub-menu', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides "Admin themes" from admin bar', 'ag-custom-admin'),
                            'name'=>'agca_admin_bar_admin_themes',
                            'label'=>__('"Admin themes"', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Adds custom text in admin top bar.', 'ag-custom-admin'),
                            'name'=>'agca_custom_site_heading',
                            'label'=>__('Custom blog heading', 'ag-custom-admin'),
                            'hint'=>__('<strong>Tip: </strong>You can use HTML tags like', 'ag-custom-admin')." &lt;h1&gt; ".__('or', 'ag-custom-admin')." &lt;a&gt;"
                        ));


                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides yellow bar with information about new WordPress release', 'ag-custom-admin'),
                            'name'=>'agca_update_bar',
                            'label'=>__('Update WordPress notification', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Right Side', 'ag-custom-admin'));

                        $this->print_input(array(
                            'name'=>'agca_howdy',
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
                            'label'=>__('"Edit My Profile" option from dropdown menu', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('If selected, hides all elements in top right corner, except Log Out button', 'ag-custom-admin'),
                            'name'=>'agca_logout_only',
                            'label'=>__('Log out only', 'ag-custom-admin')
                        ));

                        ?>

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
                            'label'=>__('<strong>Footer</strong>', 'ag-custom-admin')
                        ));

                        $this->print_options_h3(__('Footer Options', 'ag-custom-admin'));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides default text in footer', 'ag-custom-admin'),
                            'name'=>'agca_footer_left_hide',
                            'label'=>__('Footer text', 'ag-custom-admin')
                        ));

                        $this->print_textarea(array(
                            'title'=>__('Replaces text \'Thank you for creating with WordPress\' with custom text', 'ag-custom-admin'),
                            'name'=>'agca_footer_left',
                            'label'=>__('Change footer text', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides text \'Get Version ...\' on right', 'ag-custom-admin'),
                            'name'=>'agca_footer_right_hide',
                            'label'=>__('Version text', 'ag-custom-admin')
                        ));

                        $this->print_textarea(array(
                            'title'=>__('Replaces text \'Get Version ...\' with custom text', 'ag-custom-admin'),
                            'name'=>'agca_footer_right',
                            'label'=>__('Change version text', 'ag-custom-admin')
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
                            'label'=>__('Change Dashboard heading text', 'ag-custom-admin'),
                        ));

                        ?>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Adds custom text (or HTML) between heading and widgets area on Dashboard page', 'ag-custom-admin'); ?>" for="agca_dashboard_text_paragraph"><?php _e('Add custom Dashboard content<br> <em>(text or HTML content)', 'ag-custom-admin'); ?></em></label>
                            </th>
                            <td class="agca_editor">
                                <?php $this->getTextEditor('agca_dashboard_text_paragraph'); ?>
                            </td>
                        </tr>
                        <?php

                        $this->print_options_h3(__('Dashboard Widgets Options', 'ag-custom-admin'));

                        ?>
                        <tr>
                            <td colspan="2">
                                <p tabindex="0" class="agca-tip"><i><strong><?php _e('Note', 'ag-custom-admin'); ?>:</strong> <?php _e('These settings override settings in Screen options on Dashboard page.', 'ag-custom-admin'); ?></i></p>
                            </td>
                        </tr>
                        <?php
                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides Welcome WordPress widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_welcome',
                            'label'=>__('"Welcome" widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides Activity dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_activity',
                            'label'=>__('"Activity" widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides Quick Draft dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_qp',
                            'label'=>__('"Quick Draft" widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides At a Glance dashboard widget', 'ag-custom-admin'),
                            'name'=>'agca_dashboard_widget_rn',
                            'label'=>__('"At a Glance" widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_dashboard_widget_primary',
                            'title'=>__('This is \'WordPress News\' or \'WordPress Development Blog\' widget in older WordPress versions', 'ag-custom-admin'),
                            'label'=>__('"WordPress News" widget', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'name'=>'agca_dashboard_widget_secondary',
                            'title'=>__('This is \'Other WordPress News\' widget by default', 'ag-custom-admin'),
                            'label'=>__('Secondary widget area', 'ag-custom-admin')
                        ));

                        ?>
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
                            'title'=>__('Hide back to blog block', 'ag-custom-admin'),
                            'label'=>__('Back to blog text', 'ag-custom-admin')
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
                            'title'=>__('Put here custom link to a web location, that will be triggered on image click', 'ag-custom-admin'),
                            'name'=>'agca_login_photo_href',
                            'label'=>__('Change link on login image', 'ag-custom-admin'),
                            'hint'=>__('For blog URL use', 'ag-custom-admin').' %BLOG%'
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides login image completely', 'ag-custom-admin'),
                            'name'=>'agca_login_photo_remove',
                            'label'=>__('Login header image', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Rounds box on login page', 'ag-custom-admin'),
                            'name'=>'agca_login_round_box',
                            'label'=>'Round box corners',
                            'input-class'=>'has-dependant',
                            'input-attributes'=>'data-dependant="#agca_login_round_box_size_block"'
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_login_round_box')=='true')?'display:none':''),
                                'id'=>'agca_login_round_box_size_block'
                            ),
                            'title'=>__('Size of rounded box curve', 'ag-custom-admin'),
                            'name'=>'agca_login_round_box_size',
                            'label'=>__('Round box corners - size', 'ag-custom-admin'),
                            'input-class'=>'validateNumber',
                            'hint'=>__('(Size in px)', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides register link on login page', 'ag-custom-admin'),
                            'name'=>'agca_login_register_remove',
                            'label'=>__('Register link', 'ag-custom-admin'),
                            'input-class'=>'has-dependant dependant-opposite',
                            'input-attributes'=>'data-dependant="#agca_login_register_href_block"'
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_login_register_remove')=='true')?'display:none':''),
                                'id'=>'agca_login_register_href_block'
                            ),
                            'title'=>__('Change register link on login page to point to your custom registration page.', 'ag-custom-admin'),
                            'name'=>'agca_login_register_href',
                            'label'=>__('Change register link', 'ag-custom-admin'),
                            'hint'=>__('Link to new registration page', 'ag-custom-admin')
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Hides lost password link on login page', 'ag-custom-admin'),
                            'name'=>'agca_login_lostpassword_remove',
                            'label'=>__('Lost password link', 'ag-custom-admin'),
                        ));
                        ?>
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
                            <td>
                                <strong>

                                    <input class="agca-radio" type="radio" id="agca_admin_menu_turnonoff_on" name="agca_admin_menu_turnonoff" title="<?php _e('Apply admin menu customizations', 'ag-custom-admin'); ?>" value="on" <?php if(get_option('agca_admin_menu_turnonoff') == 'on') echo 'checked="checked" '; ?> />
                                    <span class="agca-radio-text on"><?php _e('YES', 'ag-custom-admin'); ?></span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <input class="agca-radio" type="radio" name="agca_admin_menu_turnonoff" title="<?php _e('Do not apply admin menu customizations', 'ag-custom-admin'); ?>" value="off" <?php if(get_option('agca_admin_menu_turnonoff') != 'on') echo 'checked="checked"'; ?> />
                                    <span class="agca-radio-text off"><?php _e('NO', 'ag-custom-admin'); ?></span>
                                </strong>
                            </td>
                        </tr>
                        <tr valign="center" class="ag_table_major_options">
                            <td><label for="agca_admin_menu_agca_button_only"><strong><?php _e('Admin menu', 'ag-custom-admin'); ?></strong></label></td>
                            <td><input class="agca-checkbox visibility" type="checkbox" name="agca_admin_menu_agca_button_only" title="<?php _e('Hides admin menu completely (administrator can see \'AG custom admin\' button)', 'ag-custom-admin'); ?>" value="true" <?php if (get_option('agca_admin_menu_agca_button_only')==true) echo 'checked="checked" '; ?> /></td>
                        </tr>
                        <?php
                        $this->print_options_h3(__('Edit / Remove Menu Items', 'ag-custom-admin'));
                        ?>
                        <tr>
                            <td colspan="2">
                                <input type="button" class="agca_button"  id="ag_edit_adminmenu_reset_button" title="<?php _e('Reset menu settings to default values', 'ag-custom-admin'); ?>" name="ag_edit_adminmenu_reset_button" value="<?php _e('Reset to default settings', 'ag-custom-admin'); ?>" /><br />
                                <p tabindex="0"><em>(<?php _e('click on the top menu item to show its sub-menus', 'ag-custom-admin'); ?>)</em></p>
                                <table id="ag_edit_adminmenu">
                                    <tr style="background-color:#999;">
                                        <td width="300px"><div style="float:left;color:#fff;"><h3><?php _e('Item', 'ag-custom-admin'); ?></h3></div><div style="float:right;color:#fff;"><h3><?php _e('Visibility', 'ag-custom-admin'); ?></h3></div></td><td width="300px" style="color:#fff;" ><h3><?php _e('Change Text', 'ag-custom-admin'); ?></h3>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" size="47" id="ag_edit_adminmenu_json" name="ag_edit_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_edit_adminmenu_json')); ?>" />
                                <input type="hidden" size="47" id="ag_edit_adminmenu_json_new" name="ag_edit_adminmenu_json_new" value="" />
                            </td>
                            <td></td>
                        </tr>
                        <?php
                        $this->print_checkbox(array(
                            'title'=>__('Removes Profile menu item for non-admin users.', 'ag-custom-admin'),
                            'hide'=>true,
                            'name'=>'agca_menu_remove_client_profile',
                            'label'=>__('Remove Profile button from user menu', 'ag-custom-admin'),
                        ));
                        $this->print_checkbox(array(
                            'title'=>__('Removes Customize button on front end for authenticated users.', 'ag-custom-admin'),
                            'hide'=>true,
                            'name'=>'agca_menu_remove_customize_button',
                            'label'=>__('Front end: Remove Customize button', 'ag-custom-admin'),
                        ));
                        ?>

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
                                            <?php _e('open in', 'ag-custom-admin'); ?>:<select id="ag_add_adminmenu_target" class="agca-selectbox" style="width:95px">
                                                <option value="_self" selected><?php _e('same tab', 'ag-custom-admin'); ?></option>
                                                <option value="_blank" ><?php _e('new tab', 'ag-custom-admin'); ?></option>
                                            </select>
                                            <input type="button" id="ag_add_adminmenu_button" class="agca_button" title="<?php _e('Add new item button" name="ag_add_adminmenu_button', 'ag-custom-admin'); ?>" value="<?php _e('Add new item', 'ag-custom-admin'); ?>" />
                                        </td><td></td>
                                    </tr>
                                </table>
                                <input type="hidden" size="47" id="ag_add_adminmenu_json" name="ag_add_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_add_adminmenu_json')); ?>" />
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
                            'label'=>__('Menu items separators', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes icons from dmin menu buttons', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_icons',
                            'label'=>__('Menu icons', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes small arrow that appears on the top button hover', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_arrow',
                            'label'=>__('Sub-menu arrow', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'hide'=>true,
                            'title'=>__('Removes collapse button at the end of admin menu', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_collapse_button',
                            'label'=>__('"Collapse menu" button', 'ag-custom-admin'),
                        ));

                        $this->print_checkbox(array(
                            'title'=>__('Rounds submenu pop-up box', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_submenu_round',
                            'label'=>__('Round sub-menu pop-up box', 'ag-custom-admin'),
                            'input-attributes'=>'data-dependant="#agca_admin_menu_submenu_round_size"',
                            'input-class'=>'has-dependant',
                        ));

                        $this->print_input(array(
                            'attributes'=>array(
                                'style'=> ((get_option('agca_admin_menu_submenu_round')!='true')?'display:none':''),
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
                            'label'=>__('Admin menu branding with logo', 'ag-custom-admin'),
                            'hint'=>__('Image URL', 'ag-custom-admin')
                        ));

                        $this->print_input(array(
                            'title'=>__('Change branding logo link</br></br>Use:', 'ag-custom-admin').'</br><strong>%BLOG%</strong> - '. __('for blog URL', 'ag-custom-admin'),
                            'name'=>'agca_admin_menu_brand_link',
                            'label'=>__('Branding logo link', 'ag-custom-admin'),
                            'hint'=>__('Branding image URL', 'ag-custom-admin')
                        ));
                        ?>
                    </table>
                </div>
                <div id="section_ag_colorizer_settings" style="display:none" class="ag_section">
                    <h2 class="section_title"><?php _e('Colorizer Page', 'ag-custom-admin'); ?></h2>
                    <?php $this->show_save_button_upper(); ?>
                    <table class="form-table" width="500px">
                        <tr valign="center" class="ag_table_major_options">
                            <td><label for="agca_colorizer_turnonoff"><strong><?php _e('Apply Colorizer settings', 'ag-custom-admin'); ?></strong></label></td>
                            <td><strong><input class="agca-radio" type="radio" name="agca_colorizer_turnonoff" title="<?php _e('Apply Colorizer customizations', 'ag-custom-admin'); ?>" value="on" <?php if(get_option('agca_colorizer_turnonoff') == 'on') echo 'checked="checked" '; ?> /><span class="agca-radio-text on"><?php _e('YES', 'ag-custom-admin'); ?></span>&nbsp;&nbsp;&nbsp;&nbsp;<input class="agca-radio" type="radio" name="agca_colorizer_turnonoff" title="<?php _e('Do not apply Colorizer customizations', 'ag-custom-admin'); ?>" value="off" <?php if(get_option('agca_colorizer_turnonoff') != 'on') echo 'checked="checked"'; ?> /><span class="agca-radio-text off"><?php _e('NO', 'ag-custom-admin'); ?></span></strong></td>
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
                    <input type="hidden" size="47" id="ag_colorizer_json" name="ag_colorizer_json" value="<?php echo htmlspecialchars(get_option('ag_colorizer_json')); ?>" />
                    <div id="picker"></div>
                </div>
                <div id="section_templates" style="display:none" class="ag_section">
                    <h2 class="section_title"><span style="float:left"><?php _e('Admin Themes', 'ag-custom-admin'); ?></span></h2>
                    <table class="form-table" width="500px">
                        <tr valign="center">
                            <td>
                                <div id="agca_templates"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="advanced_template_options" style="display:none">
                                    <div class="agca-feedback-and-support">
                                        <ul>
                                            <li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=theme" title="<?php _e('If you have any ideas for theme improvements, or you have new themes requests, please feel free to send us a message', 'ag-custom-admin'); ?>" target="_blank"><span class="dashicons dashicons-art"></span>&nbsp;&nbsp;<?php _e('Submit your admin themes ideas', 'ag-custom-admin'); ?></a></li>
                                            <li><a style="background: #f08080;color:#fff;" href="javascript:agca_removeAllTemplates();" title="<?php _e('WARNING: All installed themes will be removed. To activate them again, you would need to install theme and activate using valid license keys. Free themes can be installed again.', 'ag-custom-admin'); ?>"><span style="color:#fff" class="dashicons dashicons-trash"></span>&nbsp;&nbsp;<?php _e('Uninstall all installed themes', 'ag-custom-admin'); ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
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
                            </th>
                            <td>
                                <textarea style="width:100%;height:200px" title="<?php _e('Add custom CSS script to override existing styles', 'ag-custom-admin'); ?>" rows="5" id="agca_custom_css"  name="agca_custom_css" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_css')); ?></textarea>
                            </td>
                        </tr>
                        <tr valign="center">
                            <th scope="row">
                                <label title="<?php _e('Add additional custom JavaScript', 'ag-custom-admin'); ?>" for="agca_custom_js"><?php _e('Custom JavaScript', 'ag-custom-admin'); ?></label>
                            </th>
                            <td>
                                <textarea style="width:100%;height:200px" title="<?php _e('Add additional custom JavaScript', 'ag-custom-admin'); ?>" rows="5" name="agca_custom_js"  id="agca_custom_js" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_js')); ?></textarea>
                            </td>
                        </tr>
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
                                <input class="agca_button"  type="button" name="agca_export_settings" value="<?php _e('Export Settings', 'ag-custom-admin'); ?>" onclick="exportSettings();"/></br>
                                <input type="file" id="settings_import_file" name="settings_import_file" style="display: none"/>
                                <input type="hidden" id="_agca_import_settings" name="_agca_import_settings" value="false" />
                                <input type="hidden" id="_agca_export_settings" name="_agca_export_settings" value="false" />
                                <input class="agca_button" type="button" name="agca_import_settings" value="<?php _e('Import Settings', 'ag-custom-admin'); ?>" onclick="importSettings();"/>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php $this->show_save_button(); ?>
            </form>
            <form id="agca_templates_form" name="agca_templates_form" action="<?php echo get_site_url().$_SERVER['PHP_SELF'];?>?page=ag-custom-admin/plugin.php" method="post">
                <?php wp_nonce_field('agca_form','_agca_token'); ?>
                <input type="hidden" name="_agca_save_template" value="true" />
                <input type="hidden" id="templates_data" name="templates_data" value="" />
                <input type="hidden" id="templates_name" name="templates_name" value="" />
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
            <input type="button" id="save_plugin_settings" style="padding:0px" title="<?php _e('Save AG Custom Admin configuration', 'ag-custom-admin'); ?>" class="button-primary" value="<?php _e('Save Changes') ?>" onClick="savePluginSettings()" />
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
                $strAttributes.=' '.$key.'="'.$val.'"';
            }
        }
        if(isset($data['input-class'])){
            $strInputClass = $data['input-class'];
        }
        if(isset($data['hide'])){
            $strInputClass .= " visibility";
        }
        if(isset($data['input-attributes'])){
            $strInputAttributes = $data['input-attributes'];
        }
        if(isset($data['onchange'])){
            $strOnchange = $data['onchange'];
        }
        if(!isset($data['title'])){
            $data['title'] = $data['label'];
        }
        if(isset($data['checked'])){
            $isChecked = $data['checked'];
        }else{
            //use default check with the option
            $isChecked = get_option($data['name'])==true;
        }
        ?>
        <tr valign="center" <?php echo $strAttributes ?> >
            <th>
                <label tabindex="0" title='<?php echo $data['title'] ?>' for="<?php echo $data['name'] ?>" ><?php echo $data['label'] ?></label>
            </th>
            <td>
                <input type="checkbox" class="agca-checkbox <?php echo $strInputClass ?> "  <?php echo $strOnchange ?>  <?php echo $strInputAttributes ?> title='<?php _e('Toggle on/off', 'ag-custom-admin'); ?>' name="<?php echo $data['name'] ?>" value="true" <?php echo ($isChecked)?' checked="checked"':'' ?> />
            </td>
        </tr>
        <?php
    }
    function print_input($data){
        $strHint = '';
        $suffix ='';
        $strAttributes = '';
        $parentAttr = '';
        if(isset($data['hint'])){
            $strHint = '&nbsp;<p><i>'.$data['hint'].'</i></p>';
        }
        if(!isset($data['title'])){
            $data['title'] = $data['label'];
        }
        if(isset($data['suffix'])){
            $suffix = $data['suffix'];
        }
        if(isset($data['attributes'])){
            foreach($data['attributes'] as $key=>$val){
                $strAttributes.=' '.$key.'="'.$val.'"';
            }
        }
        ?>
        <tr valign="center" <?php echo $strAttributes ?> >
            <th >
                <label title="<?php echo $data['title'] ?>" for="<?php echo $data['name'] ?>"><?php echo $data['label'] ?></label>
            </th>
            <td>
                <input id="<?php echo $data['name'] ?>" title="<?php echo $data['title'] ?>" type="text" size="47" name="<?php echo $data['name'] ?>" value="<?php echo get_option($data['name']); ?>" />
                <a title="<?php _e('Clear', 'ag-custom-admin'); ?>" class="agca_button clear" onClick="jQuery('#<?php echo $data['name'] ?>').val('');"><span class="dashicons clear dashicons-no-alt"></span></a><?php echo $suffix ?>
                <?php echo $strHint ?>
            </td>
        </tr>
        <?php
    }
    function print_textarea($data){
        $strHint = '';
        if(isset($data['hint'])){
            $strHint = '&nbsp;<p><i>'.$data['hint'].'</i>.</p>';
        }
        if(!isset($data['title'])){
            $data['title'] = $data['label'];
        }
        ?>
        <tr valign="center">
            <th scope="row">
                <label title="<?php echo $data['title'] ?>" for="<?php echo $data['name'] ?>"><?php echo $data['label'] ?></label>
            </th>
            <td>
                <textarea title="<?php echo $data['title'] ?>" rows="5" name="<?php echo $data['name'] ?>" cols="40"><?php echo htmlspecialchars(get_option($data['name'])); ?></textarea>
                <?php echo $strHint ?>
            </td>
        </tr>
        <?php
    }
    function print_color($name, $label, $title){
        ?>
        <tr valign="center" class="color">
            <th><label title="<?php echo $title ?>" for="<?php echo $name ?>"><?php echo $label ?></label></th>
            <td><input type="text" id="<?php echo $name ?>" name="<?php echo $name ?>" class="color_picker" value="<?php echo $this->getAGCAColor($name); ?>" />
                <a title="<?php _e('Pick Color', 'ag-custom-admin'); ?>" alt="<?php echo $name ?>" class="pick_color_button agca_button"><span class="dashicons dashicons-art"></span></a>
                <a title="<?php _e('Clear', 'ag-custom-admin'); ?>" alt="<?php echo $name ?>" class="pick_color_button_clear agca_button" ><span class="dashicons clear dashicons-no-alt"></span></a>
            </td>
        </tr>
        <?php
    }
    function print_options_h3($title){
        ?>
        <tr valign="center">
            <td colspan="2">
                <div class="ag_table_heading"><h3 tabindex="0"><?php echo $title ?></h3></div>
            </td>
            <td></td>
        </tr>
        <?php
    }
    function print_option_tr(){
        ?>

        <tr valign="center">
            <th><label title="<?php _e('Change submenu item background color', 'ag-custom-admin'); ?>" for="color_admin_menu_submenu_background"><?php _e('Submenu button background color:', 'ag-custom-admin'); ?></label></th>
            <td><input type="text" id="color_admin_menu_submenu_background" name="color_admin_menu_submenu_background" class="color_picker" value="<?php echo $this->getAGCAColor('color_admin_menu_submenu_background'); ?>" />
                <input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button agca_button" value="<?php _e('Pick color', 'ag-custom-admin'); ?>" />
                <input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button_clear agca_button" value="<?php _e('Clear', 'ag-custom-admin'); ?>" />
            </td>
        </tr>
        <?php
    }
    #endregion

}
?>