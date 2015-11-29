<?php
/*
Plugin Name: AG Custom Admin
Plugin URI: http://wordpressadminpanel.com/ag-custom-admin/
Description: All-in-one tool for admin panel customization. Change almost everything: admin menu, dashboard, login page, admin bar etc. Apply admin panel themes.
Author: WAP
Version: 1.5.2
Author URI: http://www.wordpressadminpanel.com/

	Copyright 2015. WAP (email : info@wordpressadminpanel.com)
 
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
	private $active_plugin;
	private $agca_version;    
	private $agca_debug = false;    
	private $admin_capabilities;    	
    private $context = "";
    private $saveAfterImport = false;	
	private $templateCustomizations = "";
	private $templates_ep = "http://wordpressadminpanel.com/configuration.php";	
	public function __construct()
	{   	        			
        $this->reloadScript();		
		$this->checkPOST();
		$this->checkGET();		
            
		if(function_exists("add_filter")){
			add_filter('admin_title', array(&$this,'change_title'), 10, 2);
			add_filter('plugin_row_meta', array(&$this,'jk_filter_plugin_links'), 10, 2);
		}

		add_action('admin_init', array(&$this,'agca_register_settings'));
		add_action('admin_init', array(&$this,'agca_init_session'));
		add_action('admin_head', array(&$this,'print_admin_css'));		
		add_action('login_head', array(&$this,'print_login_head'));	
		add_action('admin_menu', array(&$this,'agca_create_menu'));		
		add_action('wp_head', array(&$this,'print_page'));			
		add_action( 'wp_before_admin_bar_render', array(&$this,'admin_bar_changes') ); 
		register_deactivation_hook(__FILE__, array(&$this,'agca_deactivate'));	
		
		add_action( 'customize_controls_enqueue_scripts',  array(&$this,'agca_customizer_php') );
		
		/*Initialize properties*/		
		$this->colorizer = $this->jsonMenuArray(get_option('ag_colorizer_json'),'colorizer');
              
		$this->agca_version = "1.5.2";
		
		//TODO:upload images programmatically

	}
	// Add donate and support information
	function jk_filter_plugin_links($links, $file)
	{
		if ( $file == plugin_basename(__FILE__) )
		{
			$links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php">' . __('Settings') . '</a>';
			$links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php#ag-templates">' . __('Admin Themes') . '</a>';
			$links[] = '<a href="http://wordpressadminpanel.com/agca-support/">' . __('Support') . '</a>';
			$links[] = '<a href="http://wordpressadminpanel.com/agca-support/support-for-future-development">' . __('Donate') . '</a>';
		}
		return $links;
	}
	
	function change_admin_color(){
		return 'default';
	}
	
	function agca_customizer_php(){
		$this->agca_get_includes();
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
			$this->agcaAdminSession();
			if($_POST['template'] !="")
				$_SESSION["AGCA"]["Templates"][$_POST['template']] = array("license"=>$_POST['license']);			
			
			print_r($_SESSION);
			echo "_agca_templates_session:OK";
			exit;
		}else if(isset($_POST['_agca_templates_session_remove_license'])){			
			$this->agcaAdminSession();
			if($_POST['template'] !="")
				$_SESSION["AGCA"]["Templates"][$_POST['template']] = null;						
			print_r($_SESSION);
			echo "_agca_templates_session_remove_license:OK";
			exit;
		}else if(isset($_POST['_agca_get_templates'])){
			$templates = get_option( 'agca_templates' );
			if($templates == "") $templates = array();	
			$results = array();
			foreach($templates as $key=>$val){
				$results[]=$key;
			}
			echo json_encode($results);
			exit;
		}else if(isset($_POST['_agca_activate_template'])){
			update_option('agca_selected_template', $_POST['_agca_activate_template']);
			$_POST = array();
			//unset($_POST);
			exit;
		}else if(isset($_POST['_agca_template_settings'])){
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
			function my_sideload_image() {
				$remoteurl = $_POST['_agca_upload_image'];			
				$file = media_sideload_image( $remoteurl, 0 ,"AG Custom Admin Template Image (do not delete)");	
				$fileparts = explode("src='",$file);
				$url=explode("'",$fileparts[1]);						
				echo $url[0];				
				exit;				
			}
			add_action( 'admin_init', 'my_sideload_image' );
		
		}else if(isset($_POST['_agca_remove_template_images'])){		
			$this->delete_template_images($_POST['_agca_remove_template_images']);			
			exit;
		}
	}
	
	function admin_bar_changes(){
		if( current_user_can( 'manage_options' )){
			global $wp_admin_bar;
			$wp_admin_bar->add_menu( array(
				'id'    => 'agca-admin-themes',
				'title' => '<span class="ab-icon"></span>'.__( 'Admin Themes', 'agca-admin-themes' ),
				'href'  => 'tools.php?page=ag-custom-admin/plugin.php#ag-templates'				
			) );
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
	function check_active_plugin(){
		
		$ozh = false;			
			
		if (is_plugin_active('ozh-admin-drop-down-menu/wp_ozh_adminmenu.php')) {		
			$ozh = true;
		}		
		
		$this->active_plugin = array(
			"ozh" => $ozh
		);
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
                                echo "var agca_global_plugin_url = '".trailingslashit(plugins_url(basename(dirname(__FILE__))))."';"; 
                            ?>
                        </script>
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/ag_style.css?ver=<?php echo $this->agca_version; ?>" />
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>require/dynamic.php?type=css&context=<?php echo $this->context; ?>&ver=<?php echo "changed_theme"; ?>" /> 
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/ag_script.js?ver=<?php echo $this->agca_version; ?>"></script>	                        	
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>require/dynamic.php?type=js&context=<?php echo $this->context; ?>&ver=<?php echo "changed_theme"; ?>"></script>	                        	
			
			<?php
				if($this->context == "login"){				
				?>
				<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/login.min.css" /> 
				<?php
				}else{
				?>
				<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/admin.min.css" /> 
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
									alert('AG CUSTOM ADMIN : There is an error in your custom JS script. Please fix it: \n\n' + e + '\n\n (AG CUSTOM ADMIN -> Advanced -> Custom JavaScript)');
									console.log(e);
								 }
                             </script>
                            <?php
                        }			
	}
	
	function agca_enqueue_scripts() {			
		wp_enqueue_script('jquery');	
	}
	
	function reloadScript(){
		$isAdmin = false;
		if(defined('WP_ADMIN') && WP_ADMIN == 1){
			$isAdmin = true;
		}
        if(in_array((isset($GLOBALS['pagenow'])?$GLOBALS['pagenow']:""), array('wp-login.php', 'wp-register.php')) || $isAdmin){             			
			add_action('init', array(&$this,'agca_enqueue_scripts'));				
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
             
                
                if(!empty($_POST)){
                 // fb($_POST);
                    if(isset($_POST['_agca_import_settings']) && $_POST['_agca_import_settings']=="true"){                            
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
          
             $filename = 'AGCA_Settings_'.date("Y-M-d_H-i-s").'.agca';             
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
		add_management_page( 'AG Custom Admin', 'AG Custom Admin', 'administrator', __FILE__, array(&$this,'agca_admin_page') );	
	}
	
	function agca_create_admin_button($name,$arr) {

		$href = $arr["value"];
		$target =$arr["target"];
		$button ="<li class=\"wp-not-current-submenu menu-top menu-top-last\" id=\"menu-$name\"><a href=\"$href\" target=\"$target\" class=\"wp-not-current-submenu menu-top\"><div class=\"wp-menu-arrow\"><div></div></div><div class=\"wp-menu-image dashicons-before dashicons-admin-$name\"><br></div><div class=\"wp-menu-name\">$name</div></a></li>";

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
	if($this->isGuest() && get_option('agca_admin_bar_frontend_hide')){
		return false;
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
		if(get_option('agca_admin_bar_frontend')!=true){ 				 		
		
            $this->context = "page";
            $wpversion = $this->get_wp_version();

		?>
                       
                 
                 <script type="text/javascript">      
                    var wpversion = "<?php echo $wpversion; ?>";
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
                                document.write("<scr" + "ipt type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></scr" + "ipt>");
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
                     <?php echo "var agca_global_plugin_url = '".trailingslashit(plugins_url(basename(dirname(__FILE__))))."';"; ?>
                 </script>
                <script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/ag_script.js?ver=<?php echo $this->agca_version; ?>"></script>
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
								image = jQuery("<img />").attr("src",advanced_url);								
								jQuery(image).load(function() {										
										jQuery("#wpbody-content").prepend(image);
								});                                				

                <?php } ?>	
                <?php if(get_option('agca_wp_logo_custom')!=""){ ?>		                                                                     
                                         jQuery("li#wp-admin-bar-wp-logo a.ab-item span.ab-icon").html("<img style=\"height:28px;margin-top:-4px\" src=\"<?php echo get_option('agca_wp_logo_custom'); ?>\" />");
                                         jQuery("li#wp-admin-bar-wp-logo a.ab-item span.ab-icon").css('background-image','none');
                                         jQuery("li#wp-admin-bar-wp-logo a.ab-item span.ab-icon").css('width','auto');										 									 
                                         jQuery("li#wp-admin-bar-wp-logo a.ab-item").attr('href',"<?php echo get_bloginfo('wpurl'); ?>");                                       
                                         jQuery("#wpadminbar #wp-admin-bar-root-default > #wp-admin-bar-wp-logo .ab-item:before").attr('title','');    
										 jQuery('body #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon').attr('class','ab-icon2');	
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
									$agca_logout_text = ((get_option('agca_logout')=="")?"Log Out":get_option('agca_logout'));
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
								jQuery("ul#wp-admin-bar-top-secondary").html('<li id="wp-admin-bar-logout">'+ logout_content +'</li>');
                                    						
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
				$capabilitySelector .="<option val=\"$k\" $selected >".$k."</option>\n";
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
		echo "var agca_template_settings = ".preg_replace('#<script(.*?)>(.*?)</script>#is', '', $settings).";\n";	//TODO: think about this				
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
				 window.agca_error_details_text = agca_error_details + '<br/>This JavaScript error could stop AG Custom Admin plugin to work properly. If everything still works, you can ignore this notification. <br/><br/>Possible solutions:<br/><br/>1) Make sure to have everything up to date: WordPress site, plugins and themes.<br/><br/>2) Try disabling plugins one by one to see if problem can be resolved this way. If so, one of disabled plugins caused this error.<br/><br/>3) Check "source" path of this error. This could be indicator of the plugin/theme that caused the error.<br/><br/>4) If it\'s obvious that error is thrown from a particular plugin/theme, please report this error to their support. <br/><br/>5) Try activating default WordPress theme instead of your current theme.<br/><br/>6) Advanced: Try fixing this issue manually: Navigate to the link above in your browser and open the source of the page (right click -> view page source) and find the line in code where it fails. You should access this file via FTP and try to fix this error on that line.<br/><br/>7) Contact us if nothing above helps. Please do not post errors that are caused by other plugins/themes to our support page. Contact their support instead. If you think that error is somehow related to AG Custom Admin plugin, or something unexpected happens, please report that on our <a href="http://wordpressadminpanel.com/agca-support/ag_custom_admin/error-ocurred-javascript-error-caught/" target="_blank">SUPPORT PAGE</a>';
                        document.getElementsByTagName('html')[0].style.visibility = "visible";
                        var errorDivHtml = '<div style="background: #f08080;border-radius: 3px;color: #ffffff;height: auto; margin-right: 13px;padding: 6px 14px;width: 450px;z-index: 99999; position:absolute;">\
						AG Custom Admin caught an error on your site!&nbsp;<a target="_blank" href="#" onclick="var aedt = document.getElementById(\'agca_error_details_text\'); if(aedt.style.display !== \'block\') {aedt.style.display = \'block\';} else{aedt.style.display = \'none\';} return false;"  style="color: #ffffff !important;float:right;font-weight: bold;text-decoration: none;">(show/hide more...)</a><div id="agca_error_details_text" style="display:none;margin: 10px 0;background:#ffffff;border-radius: 5px;padding:8px;color: #777;">'+agca_error_details_text+'</div></div>';			
								
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
						console.log("AG Custom Admin caught a JavaScript on your site:");							
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
			if($customizationsSet){
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

					if(isset($pc) && isset($pc['submenus'])){
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
			if($customizationsSet){
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
	function print_admin_css()
	{	
		$agcaTemplateSession = $this->agcaAdminSession();
		$wpversion = $this->get_wp_version();	
		$this->context = "admin";
		$this->error_check();
		?>
		<script type="text/javascript">
			var wpversion = "<?php echo $wpversion; ?>";
			var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
			var agca_version = "<?php echo $this->agca_version; ?>";
			var agcaTemplatesSession = <?php echo ($agcaTemplateSession==null)?"[]":$agcaTemplateSession; ?>;
			var errors = false;
			var isSettingsImport = false;
			var agca_context = "admin";
			var roundedSidberSize = 0;		
			var agca_installed_templates = <?php echo $this->get_installed_agca_templates(); ?>;
			var agca_admin_menu = <?= json_encode($this->get_menu_customizations()) ?>;
		</script>
		<?php
		$this->prepareAGCAAdminTemplates();
		$this->agca_get_includes();
		$this->admin_capabilities();		
		get_currentuserinfo() ;		
				
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
<?php } ?>
<script type="text/javascript">
  /* <![CDATA[ */
jQuery(document).ready(function() {
try
  {  				
				
				<?php /*CHECK OTHER PLUGINS*/
					$this->check_active_plugin(); 
					
					if($this->active_plugin["ozh"]){
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

					<?php	$buttons = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttons');	?>
					var buttons = '<?php echo $buttons; ?>';	
					
					<?php	$buttonsJq = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttonsJq');	?>
					var buttonsJq = '<?php echo $buttonsJq; ?>';

	  				createEditMenuPageNew(agca_admin_menu);
	  				//createEditMenuPageV32(checkboxes, textboxes);

		<?php
		//if admin, and option to hide settings for admin is set	
		
		if((get_option('agca_role_allbutadmin')==true) and current_user_can($this->admin_capability())){
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
                                                        require_once(ABSPATH . 'wp-includes/formatting.php');
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
											jQuery('#adminmenu').append('<?php echo $this->agca_create_admin_button('AG Custom Admin',array('value'=>'tools.php?page=ag-custom-admin/plugin.php','target'=>'_self')); ?>');
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
		$this->context = "login";	
		$this->error_check();
		$wpversion = $this->get_wp_version();
                
		?>
		<script type="text/javascript">		
		 document.write('<style type="text/css">html{visibility:hidden;}</style>');		 
		 var agca_version = "<?php echo $this->agca_version; ?>";
		 <?php //var wpversion = "echo $wpversion; ?>
		 var agca_debug = <?php echo ($this->agca_debug)?"true":"false"; ?>;
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
							jQuery("#backtoblog").html('<?php echo addslashes(get_option('agca_login_banner_text')); ?>');
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

	function agca_admin_page() {

		$wpversion = $this->get_wp_version();
		$this->agca_error_check();
		?>		
		<?php //includes ?>
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/farbtastic.css?ver=<?php echo $wpversion; ?>" />
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/farbtastic.js?ver=<?php echo $wpversion; ?>"></script>	
			
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/agca_farbtastic.css?ver=<?php echo $wpversion; ?>" />
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/agca_farbtastic.js?ver=<?php echo $wpversion; ?>"></script>
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/xd.js?ver=<?php echo $wpversion; ?>"></script>			
			<script type="text/javascript">
             var templates_ep = "<?php echo $this->templates_ep; ?>"; 
			 var template_selected = '<?php echo get_option('agca_selected_template'); ?>';			 
            </script>
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/agca_tmpl.js?ver=<?php echo $wpversion; ?>"></script>                  						
		<?php //includes ?>
		<div class="wrap">
			<h1 id="agca-title">AG Custom Admin Settings <span style="font-size:15px;">(v<?php echo $this->agca_version; ?>)</span></h1>
			<div id="agca_error_placeholder"></div>
										<div id="agca_news">&nbsp;</div><br />								
			<form method="post" id="agca_form" action="options.php">
				<?php settings_fields( 'agca-options-group' ); ?>
                        <div id="agca-your-feedback">
							<strong>
								<span style="color:#005B69">Your feedback:</span>
							</strong>
							<a class="feedback positive" target="_blank" title="POSITIVE FEEDBACK: I like this plugin!" href="http://wordpressadminpanel.com/agca-support/feedback/ag-custom-admin-positive-feedback/">
								<span class="dashicons dashicons-thumbs-up"></span>
							</a>
							<a class="feedback negative" target="_blank" title="NEGATIVE FEEDBACK: I don't like this plugin." href="http://wordpressadminpanel.com/agca-support/feedback/ag-custom-admin-negative-feedback/">
								<span class="dashicons dashicons-thumbs-down"></span>
							</a>
						</div>
			<br />
			<ul id="ag_main_menu">
				<li class="selected"><a href="#general-settings" title="General Settings" >General</a></li>
				<li class="normal"><a href="#admin-bar-settings" title="Settings for admin bar" >Admin Bar</a></li>
				<li class="normal"><a href="#admin-footer-settings" title="Settings for admin footer" >Admin Footer</a></li>
				<li class="normal"><a href="#dashboad-page-settings" title="Settings for Dashboard page">Dashboard Page</a></li>
				<li class="normal"><a href="#login-page-settings" title="Settings for Login page">Login Page</a></li>
				<li class="normal" ><a href="#admin-menu-settings" title="Settings for main admin menu">Admin Menu</a></li>
				<li class="normal"><a href="#ag-colorizer-setttings" title="AG colorizer settings">Colorizer</a></li>				
                <li class="normal"><a href="#ag-advanced" title="My custom scripts">Advanced</a></li>
				<li class="normal" style=""><a style="color:#DB6014;font-weight:bolder;" href="#ag-templates" title="AG Custom Admin Themes">Admin Themes</a></li>
								
				<li style="background:none;border:none;padding:0;"><a id="agca_donate_button" target="_blank" style="margin-left:8px" title="Like this plugin? You can support its future development by giving a donation by your wish " href="http://wordpressadminpanel.com/agca-support/support-for-future-development/"><img alt="Donate" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>images/btn_donate_LG.gif" /></a>
				</li>                                
				<li style="background:none;border:none;padding:0;padding-left:10px;margin-top:-7px"></li>		
			</ul>
                        <div id="agca_advertising">
                            <ul>    
                                <li style="min-height:105px;display: block"></li>
                            </ul>
                        </div>
                        <div class="agca-clear"></div>
				<div id="section_general" style="display:none" class="ag_section">
					<h2 class="section_title">General Settings</h2>
					<p tabindex="0" class="agca-clear agca-tip"><i><strong>Tip: </strong>Move mouse cursor over the option label to see more information about an option</i></p>
					<table class="agca-clear form-table" width="500px">
						<?php

						$this->print_checkbox(array(
							'name'=>'agca_role_allbutadmin',
							'label'=>'Exclude AGCA admin from customizations',
							'title'=>'<h3>Applying customizations</h3><br><strong>Checked</strong> - apply to all users, except admin<br><strong>Not checked</strong> - apply to everyone</br></br><strong>Q</strong>: Who is AGCA administrator?</br><strong>A</strong>: Go to <i>Advanced</i> tab and change capability option to define administrators. Only the users with selected capability will be AGCA administrators.</br>'
						));

						$this->print_options_h3('Pages');

						$this->print_checkbox(array(
							'hide'=>true,
							'name'=>'agca_screen_options_menu',
							'label'=>'"Screen Options" menu',
							'title'=>'Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)'
						));

						$this->print_checkbox(array(
							'hide'=>true,
							'name'=>'agca_help_menu',
							'label'=>'"Help" menu',
							'title'=>'Hides the menu from the admin pages (located on the top right corner of the page, below the admin bar)'
						));

						$this->print_options_h3('Security');

						?>

						<tr valign="center">
							<th scope="row">
								<label title="Choose which WordPress capability will be used to distinguish AGCA admin users from other users.</br>AGCA admin users have access to AGCA settings. AGCA administrators can be excluded from customizations if that option is checked" for="agca_admin_capability">AGCA admin capability:</label>
							</th>
							<td><?php echo $this->admin_capabilities; ?>&nbsp;&nbsp;<i>(<strong>Edit Dashboard</strong> - selected by default)</i>
								<p style="margin-left:5px;"><i>Find more information about <a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank">WordPress capabilities</a></i></p>
							</td>
							<td>
							</td>
						</tr>
						<?php
						$this->print_options_h3('Feedback and Support');

						?>
						<tr valign="center">
							<td colspan="2">
								<div class="agca-feedback-and-support">
									<ul>
										<li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=feature" target="_blank"><span class="dashicons dashicons-lightbulb"></span>&nbsp;&nbsp;Idea for improvement</a> - submit your idea for improvement </li>
									</ul>
									<ul>
										<li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=bug" target="_blank"><span class="dashicons dashicons-megaphone"></span>&nbsp;&nbsp;Report an issue</a> - if plugin does not work as expected </li>
									</ul>
									<ul>
										<li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=theme" target="_blank"><span class="dashicons dashicons-art"></span>&nbsp;&nbsp;Idea for admin theme</a> - submit your idea for admin theme </li>
									</ul>
									<ul>
										<li><a href="https://wordpress.org/support/view/plugin-reviews/ag-custom-admin" target="_blank"><span class="dashicons dashicons-awards"></span>&nbsp;&nbsp;Add a review on WordPress.org</a> - add your review and rate us on WordPress.org </li>
									</ul>
									<ul>
										<li><a href="http://wordpressadminpanel.com/agca-support/" target="_blank"><span class="dashicons dashicons-shield-alt"></span>&nbsp;&nbsp;Visit our support site</a> - for any other questions, feel free to contact us </li>
									</ul>
									<ul>
										<li><a href="http://wordpressadminpanel.com/agca-support/support-for-future-development/" target="_blank"><span class="dashicons dashicons-palmtree"></span>&nbsp;&nbsp;Donate</a> - only if you find this plugin helpful for your needs </li>
									</ul>
								</div>
							</td>
							<td></td>
						</tr>
					</table>
				</div>
				<div id="section_admin_bar" class="ag_section">
				<h2 class="section_title">Admin Bar Settings</h2>
				<table class="form-table" width="500px">

					<?php
					$this->print_checkbox(array(
						'attributes'=>array(
							'class'=>'ag_table_major_options',
					    ),
						'hide'=>true,
						'title'=>'Hides admin bar completely from the admin panel',
						'name'=>'agca_header',
						'label'=>'<strong>Admin bar</strong>',
						'input-attributes'=>'data-dependant="#agca_header_show_logout_content"',
						'input-class'=>'has-dependant',
					));

					$this->print_checkbox(array(
						'attributes'=>array(
							'class'=>'ag_table_major_options',
							'style'=> ((get_option('agca_header')!='true')?'display:none':''),
							'id'=>'agca_header_show_logout_content',
						),
						'title'=>'Check this if you want to show Log Out button in top right corner of the admin page',
						'name'=>'agca_header_show_logout',
						'checked'=> ((get_option('agca_header')==true) && (get_option('agca_header_show_logout')==true)),
						'label'=>'<strong>(but show Log Out button)</strong>'
					));

					$this->print_checkbox(array(
						'title'=>'Removes admin bar customizations for authenticated users on site pages.</br>This option can be useful if you want to remove AGCA scripts (styles, JavaScript) on your website for any reason.',
						'name'=>'agca_admin_bar_frontend',
						'hide'=>true,
						'label'=>'Site pages: Admin bar customizations'
					));

					$this->print_checkbox(array(
						'title'=>'Hides admin bar completely for authenticated users on site pages.',
						'name'=>'agca_admin_bar_frontend_hide',
						'hide'=>true,
						'label'=>'Site pages: Admin bar'
					));

					$this->print_options_h3('Left Side');

					$this->print_input(array(
						'title'=>'Change default WordPress logo with custom image.',
						'name'=>'agca_wp_logo_custom',
						'label'=>'Admin bar logo',
						'hint' =>'Image URL (maximum height is 28px)'
					));

					$this->print_input(array(
						'title'=>'Custom link on admin bar logo.</br></br>Use:</br><strong>%BLOG%</strong> - for blog URL</br><strong>%SWITCH%</strong> - to switch betweent admin and site area',
						'name'=>'agca_wp_logo_custom_link',
						'label'=>'Admin bar logo link',
						'hint' =>'Link'
					));

					$this->print_input(array(
						'title'=>'Customize WordPress title using custom title template.</br></br>Examples:</br><strong>%BLOG% -- %PAGE%</strong>  (will be) <i>My Blog -- Add New Post</i></br><strong>%BLOG%</strong> (will be) <i>My Blog</i></br><strong>My Company > %BLOG% > %PAGE%</strong> (will be) <i>My Company > My Blog > Tools</i>',
						'name'=>'agca_custom_title',
						'label'=>'Page title template',
						'hint' =>'Please use <strong>%BLOG%</strong> and <strong>%PAGE%</strong> in your title template.'
					));

					$this->print_input(array(
						'title'=>'Add custom image on the top of the admin content.',
						'name'=>'agca_header_logo_custom',
						'label'=>'Header image',
						'hint' =>'Image URL'
					));


					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides small Wordpress logo from the admin bar',
						'name'=>'agca_header_logo',
						'label'=>'WordPress logo'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides WordPress context menu on WordPress logo icon from admin bar',
						'name'=>'agca_remove_top_bar_dropdowns',
						'label'=>'WordPress logo context menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides site name link from the admin bar',
						'name'=>'agca_remove_site_link',
						'label'=>'Site name'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides update notifications from admin bar',
						'name'=>'agca_admin_bar_update_notifications',
						'label'=>'Update notifications'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides comments block from admin bar',
						'name'=>'agca_admin_bar_comments',
						'label'=>'"Comments" block'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'style'=>'margin-top:20px;'
					     ),
						'title'=>'Hides "+ New" block and its context menu from admin bar',
						'name'=>'agca_admin_bar_new_content',
						'label'=>'"+ New" block',
						'input-attributes'=>'data-dependant=".new_content_header_submenu"',
						'input-class'=>'has-dependant dependant-opposite'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'class'=>'new_content_header_submenu'
						),
						'title'=>'Hides "Post" sub-menu from "+ New" block on admin bar',
						'name'=>'agca_admin_bar_new_content_post',
						'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ New" -> Post sub-menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'class'=>'new_content_header_submenu'
						),
						'title'=>'Hides "Link" sub-menu from "+ New" block on admin bar',
						'name'=>'agca_admin_bar_new_content_link',
						'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ New" -> Link sub-menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'class'=>'new_content_header_submenu'
						),
						'title'=>'Hides "Page" sub-menu from "+ New" block on admin bar',
						'name'=>'agca_admin_bar_new_content_page',
						'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ New" -> Page sub-menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'class'=>'new_content_header_submenu'
						),
						'title'=>'Hides "User" sub-menu from "+ New" block on admin bar',
						'name'=>'agca_admin_bar_new_content_user',
						'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ New" -> User sub-menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'attributes'=>array(
							'class'=>'new_content_header_submenu'
						),
						'title'=>'Hides "Media" sub-menu from "+ New" block on admin bar',
						'name'=>'agca_admin_bar_new_content_media',
						'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+ New" -> Media sub-menu'
					));

					$this->print_checkbox(array(
						'hide'=>true,
						'title'=>'Hides "Admin themes" from admin bar',
						'name'=>'agca_admin_bar_admin_themes',
						'label'=>'"Admin themes"'
					));

					$this->print_textarea(array(
						'title'=>'Adds custom text in admin top bar.',
						'name'=>'agca_custom_site_heading',
						'label'=>'Custom blog heading',
						'hint'=>"<strong>Tip: </strong>You can use HTML tags like &lt;h1&gt; or &lt;a&gt;"
					));


							$this->print_checkbox(array(
								'hide'=>true,
								'title'=>'Hides yellow bar with information about new WordPress release',
								'name'=>'agca_update_bar',
								'label'=>'Update WordPress notification'
							));

							$this->print_options_h3('Right Side');

							$this->print_input(array(
								'name'=>'agca_howdy',
								'label'=>'Change Howdy text',
							));

							$this->print_input(array(
								'title'=>'Put \'Exit\', for example',
								'name'=>'agca_logout',
								'label'=>'Change Log out text',
							));

							$this->print_checkbox(array(
								'hide'=>true,
								'name'=>'agca_remove_your_profile',
								'label'=>'"Edit My Profile" option from dropdown menu'
							));

							$this->print_checkbox(array(
								'title'=>'If selected, hides all elements in top right corner, except Log Out button',
								'name'=>'agca_logout_only',
								'label'=>'Log out only'
							));

					?>

							</table>
						</div>
						
						<div id="section_admin_footer" style="display:none" class="ag_section">	
							<h2 class="section_title">Admin Footer Settings</h2>
							<table class="form-table" width="500px">
								<?php
								$this->print_checkbox(array(
									'hide'=>true,
									'attributes'=>array(
										'class'=>'ag_table_major_options'
									),
									'title'=>'Hides footer with all elements',
									'name'=>'agca_footer',
									'label'=>'<strong>Footer</strong>'
								));

								$this->print_options_h3('Footer Options');

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Hides default text in footer',
									'name'=>'agca_footer_left_hide',
									'label'=>'Footer text'
								));

								$this->print_textarea(array(
									'title'=>'Replaces text \'Thank you for creating with WordPress\' with custom text',
									'name'=>'agca_footer_left',
									'label'=>'Change footer text'
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Hides text \'Get Version ...\' on right',
									'name'=>'agca_footer_right_hide',
									'label'=>'Version text'
								));

								$this->print_textarea(array(
									'title'=>'Replaces text \'Get Version ...\' with custom text',
									'name'=>'agca_footer_right',
									'label'=>'Change version text'
								));

								?>

							</table>
						</div>
						<div id="section_dashboard_page" style="display:none" class="ag_section">
							<h2 class="section_title">Dashboard Page Settings</h2>
							<table class="form-table" width="500px">
								<?php

								$this->print_options_h3('Dashboard Page Options');

								$this->print_input(array(
									'title'=>"Main heading ('Dashboard') on Dashboard page",
									'name'=>'agca_dashboard_text',
									'label'=>'Change Dashboard heading text',
								));

								?>
							<tr valign="center">
								<th scope="row">
									<label title="Adds custom text (or HTML) between heading and widgets area on Dashboard page" for="agca_dashboard_text_paragraph">Add custom Dashboard content<br> <em>(text or HTML content)</em></label>
								</th>
								<td class="agca_editor">								
								<?php $this->getTextEditor('agca_dashboard_text_paragraph'); ?>			
								</td>
							</tr>
								<?php

								$this->print_options_h3('Dashboard Widgets Options');

								?>
							<tr>
								<td colspan="2">
							<p tabindex="0" class="agca-tip"><i><strong>Note:</strong> These settings override settings in Screen options on Dashboard page.</i></p>
							</td>
							</tr>
								<?php
									$this->print_checkbox(array(
										'hide'=>true,
										'title'=>'Hides Welcome WordPress widget',
										'name'=>'agca_dashboard_widget_welcome',
										'label'=>'"Welcome" widget'
									));

									$this->print_checkbox(array(
										'hide'=>true,
										'title'=>'Hides Activity dashboard widget',
										'name'=>'agca_dashboard_widget_activity',
										'label'=>'"Activity" widget'
									));

									$this->print_checkbox(array(
										'hide'=>true,
										'title'=>'Hides Quick Draft dashboard widget',
										'name'=>'agca_dashboard_widget_qp',
										'label'=>'"Quick Draft" widget'
									));

									$this->print_checkbox(array(
										'hide'=>true,
										'title'=>'Hides At a Glance dashboard widget',
										'name'=>'agca_dashboard_widget_rn',
										'label'=>'"At a Glance" widget'
									));

									$this->print_checkbox(array(
										'hide'=>true,
										'name'=>'agca_dashboard_widget_primary',
										'title'=>"This is 'WordPress News' or 'WordPress Development Blog' widget in older WordPress versions",
										'label'=>'"WordPress News" widget'
									));

									$this->print_checkbox(array(
										'hide'=>true,
										'name'=>'agca_dashboard_widget_secondary',
										'title'=>"This is 'Other WordPress News' widget by default",
										'label'=>'Secondary widget area'
									));

								?>
							</table>
						</div>
						<div id="section_login_page" style="display:none" class="ag_section">
						<h2 class="section_title">Login Page Settings</h2>												
							<table class="form-table" width="500px">
								<?php

								$this->print_options_h3('Login Page Options');

								$this->print_checkbox(array(
									'hide'=>true,
									'name'=>'agca_login_banner',
									'title'=>"Hide back to blog block",
									'label'=>'Back to blog text'
								));

								$this->print_textarea(array(
									'name'=>'agca_login_banner_text',
									'title'=>"Changes '<- Back to ...' text in top bar on Login page",
									'label'=>'Change back to blog text',
									'hint'=>'Should be wrapped with an anchor tag &lt;a&gt;&lt;/a&gt;'
								));

								$this->print_input(array(
									'title'=>'If this field is not empty, image from provided url will be visible on Login page',
									'name'=>'agca_login_photo_url',
									'label'=>'Change Login header image',
									'hint'=>'Image URL'
								));

								$this->print_input(array(
									'title'=>'Put here custom link to a web location, that will be triggered on image click',
									'name'=>'agca_login_photo_href',
									'label'=>'Change link on login image',
									'hint'=>'For blog URL use %BLOG%'
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Hides login image completely',
									'name'=>'agca_login_photo_remove',
									'label'=>'Login header image',
								));

								$this->print_checkbox(array(
									'title'=>'Rounds box on login page',
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
									'title'=>'Size of rounded box curve',
									'name'=>'agca_login_round_box_size',
									'label'=>'Round box corners - size',
									'input-class'=>'validateNumber',
									'hint'=>'(Size in px)'
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Hides register link on login page',
									'name'=>'agca_login_register_remove',
									'label'=>'Register link',
									'input-class'=>'has-dependant dependant-opposite',
									'input-attributes'=>'data-dependant="#agca_login_register_href_block"'
								));

								$this->print_input(array(
									'attributes'=>array(
										'style'=> ((get_option('agca_login_register_remove')=='true')?'display:none':''),
										'id'=>'agca_login_register_href_block'
									),
									'title'=>'Change register link on login page to point to your custom registration page.',
									'name'=>'agca_login_register_href',
									'label'=>'Change register link',
									'hint'=>'Link to new registration page'
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Hides lost password link on login page',
									'name'=>'agca_login_lostpassword_remove',
									'label'=>'Lost password link',
								));
								?>
						</table>
						</div>
						<?php
							/*ADMIN MENU*/
						?>
						<div id="section_admin_menu" style="display:none" class="ag_section">
						<h2 class="section_title">Admin Menu Settings</h2>
							<table class="form-table" width="500px">
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_admin_menu_turnonoff"><strong>Apply admin menu customizations</strong></label></td>
								<td>
									<strong>

											<input class="agca-radio" type="radio" id="agca_admin_menu_turnonoff_on" name="agca_admin_menu_turnonoff" title="Apply admin menu customizations" value="on" <?php if(get_option('agca_admin_menu_turnonoff') == 'on') echo 'checked="checked" '; ?> />
											<span class="agca-radio-text on">YES</span>
										&nbsp;&nbsp;&nbsp;&nbsp;
											<input class="agca-radio" type="radio" name="agca_admin_menu_turnonoff" title="Do not apply admin menu customizations" value="off" <?php if(get_option('agca_admin_menu_turnonoff') != 'on') echo 'checked="checked"'; ?> />
											<span class="agca-radio-text off">NO</span>
									</strong>
								</td>
							</tr>
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_admin_menu_agca_button_only"><strong>Admin menu</strong></label></td>
								<td><input class="agca-checkbox visibility" type="checkbox" name="agca_admin_menu_agca_button_only" title="Hides admin menu completly (administrator can see 'AG custom admin' button)" value="true" <?php if (get_option('agca_admin_menu_agca_button_only')==true) echo 'checked="checked" '; ?> /></td>
							</tr>
								<?php
								$this->print_options_h3('Edit / Remove Menu Items');
								?>
							<tr>
								<td colspan="2">
											<input type="button" class="agca_button"  id="ag_edit_adminmenu_reset_button" title="Reset menu settings to default values" name="ag_edit_adminmenu_reset_button" value="Reset to default settings" /><br />
											<p tabindex="0"><em>(click on the top menu item to show its sub-menus)</em></p>
									<table id="ag_edit_adminmenu">									
										<tr style="background-color:#999;">
											<td width="300px"><div style="float:left;color:#fff;"><h3>Item</h3></div><div style="float:right;color:#fff;"><h3>Visibility</h3></div></td><td width="300px" style="color:#fff;" ><h3>Change Text</h3>
											</td>
										</tr>
									</table>
									<input type="hidden" size="47" id="ag_edit_adminmenu_json" name="ag_edit_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_edit_adminmenu_json')); ?>" />
									<input type="hidden" size="47" id="ag_edit_adminmenu_json_new" name="ag_edit_adminmenu_json_new" value="" />
								</td>
								<td></td>
							</tr>
								<?php
								$this->print_options_h3('Add New Menu Items');
								?>
							<tr>
								<td colspan="2">
									<table id="ag_add_adminmenu">
										<tr>
											<td colspan="2">
												name:<input type="text" size="47" title="New button visible name" id="ag_add_adminmenu_name" name="ag_add_adminmenu_name" />
												url:<input type="text" size="47" title="New button link" id="ag_add_adminmenu_url" name="ag_add_adminmenu_url" />
												open in:<select id="ag_add_adminmenu_target" class="agca-selectbox" style="width:95px">
													<option value="_self" selected>same tab</option>
													<option value="_blank" >new tab</option>
												</select>
												<input type="button" id="ag_add_adminmenu_button" class="agca_button" title="Add new item button" name="ag_add_adminmenu_button" value="Add new item" />	
											</td><td></td>	
										</tr>
									</table>
								<input type="hidden" size="47" id="ag_add_adminmenu_json" name="ag_add_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_add_adminmenu_json')); ?>" />									
								</td>						
								<td>									
								</td>								
							</tr>
								<?php
								$this->print_options_h3('Admin Menu Settings');
								?>
								<tr valign="center">
									<th scope="row">
										<label title="Choose how admin menu should behave on mobile devices / small screens" for="agca_admin_menu_autofold">Admin menu auto folding</label>
									</th>
									<td>
										<select title="Choose how admin menu should behave on mobile devices / small screens" class="agca-selectbox" name="agca_admin_menu_autofold" >
											<option value="" <?php echo (get_option('agca_admin_menu_autofold') == "")?" selected ":""; ?> >Default</option>
											<option value="force" <?php echo (get_option('agca_admin_menu_autofold') == "force")?" selected ":""; ?> >Force admin menu auto-folding</option>
											<option value="disable" <?php echo (get_option('agca_admin_menu_autofold') == "disable")?" selected ":""; ?> >Disable admin menu auto-folding</option>
										</select>
									</td>
								</tr>
								<?php

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Removes empty space between some top menu items',
									'name'=>'agca_admin_menu_separators',
									'label'=>'Menu items separators',
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Removes icons from dmin menu buttons',
									'name'=>'agca_admin_menu_icons',
									'label'=>'Menu icons',
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Removes small arrow that appears on the top button hover',
									'name'=>'agca_admin_menu_arrow',
									'label'=>'Sub-menu arrow',
								));

								$this->print_checkbox(array(
									'hide'=>true,
									'title'=>'Removes collapse button at the end of admin menu',
									'name'=>'agca_admin_menu_collapse_button',
									'label'=>'"Collapse menu" button',
								));

								$this->print_checkbox(array(
									'title'=>'Rounds submenu pop-up box',
									'name'=>'agca_admin_menu_submenu_round',
									'label'=>'Round sub-menu pop-up box',
									'input-attributes'=>'data-dependant="#agca_admin_menu_submenu_round_size"',
									'input-class'=>'has-dependant',
								));

								$this->print_input(array(
									'attributes'=>array(
										'style'=> ((get_option('agca_admin_menu_submenu_round')!='true')?'display:none':''),
										'id'=>'agca_admin_menu_submenu_round_size'
									),
									'title'=>'Size of rounded box curve',
									'name'=>'agca_admin_menu_submenu_round_size',
									'label'=>'Round sub-menu pop-up box - size',
									'input-class'=>'validateNumber',
									'hint'=>'(Size in px)'
								));

								$this->print_input(array(
									'title'=>'Adds custom logo above the admin menu',
									'name'=>'agca_admin_menu_brand',
									'label'=>'Admin menu branding with logo',
									'hint'=>'Image URL'
								));

								$this->print_input(array(
									'title'=>'Change branding logo link</br></br>Use:</br><strong>%BLOG%</strong> - for blog URL',
									'name'=>'agca_admin_menu_brand_link',
									'label'=>'Branding logo link',
									'hint'=>'Branding image URL'
								));
								?>
							</table>
						</div>
						<div id="section_ag_colorizer_settings" style="display:none" class="ag_section">
						<h2 class="section_title">Colorizer Page</h2>
						<table class="form-table" width="500px">
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_colorizer_turnonoff"><strong>Apply Colorizer settings</strong></label></td>
								<td><strong><input class="agca-radio" type="radio" name="agca_colorizer_turnonoff" title="Apply Colorizer customizations" value="on" <?php if(get_option('agca_colorizer_turnonoff') == 'on') echo 'checked="checked" '; ?> /><span class="agca-radio-text on">YES</span>&nbsp;&nbsp;&nbsp;&nbsp;<input class="agca-radio" type="radio" name="agca_colorizer_turnonoff" title="Do not apply Colorizer customizations" value="off" <?php if(get_option('agca_colorizer_turnonoff') != 'on') echo 'checked="checked"'; ?> /><span class="agca-radio-text off">NO</span></strong></td>
							</tr>
							<?php
							$this->print_options_h3('Global Color Options');

							$this->print_color('color_background','Background:','Change admin page background color');
							$this->print_color('login_color_background','Login page background:','Change login page background color');
							$this->print_color('color_header','Admin bar:','Change admin bar (on top) color in admin panel');

							$this->print_options_h3('Admin Menu Color Options');

							$this->print_color('color_admin_menu_top_button_background','Button background:','Change button background color');
							$this->print_color('color_admin_menu_font','Button text:','Change button text color');
							$this->print_color('color_admin_menu_top_button_current_background','Selected button background:','Change button background color for current button');
							$this->print_color('color_admin_menu_top_button_hover_background','Hover button background:','Change button background color on mouseover');
							$this->print_color('color_admin_menu_submenu_background','Sub-menu button background:','Change submenu item background color');
							$this->print_color('color_admin_menu_submenu_background_hover','Sub-menu hover button background:','Change submenu item background color on mouseover');
							$this->print_color('color_admin_submenu_font','Sub-menu text:','Sub-menu text color');
							$this->print_color('color_admin_menu_behind_background','Wrapper background:','Change background color of element behind admin menu');

							$this->print_options_h3('Font Color Options');

							$this->print_color('color_font_content','Content text:','Change color in content text');
							$this->print_color('color_font_header','Admin bar text:','Change color of admin bar text');
							$this->print_color('color_font_footer','Footer text:','Change color in fotter text');

							$this->print_options_h3('Widgets Color Options');

							$this->print_color('color_widget_bar','Title bar background:','Change color in header text');
							$this->print_color('color_widget_background','Background:','Change widget background color');

							?>
							</table>
							<input type="hidden" size="47" id="ag_colorizer_json" name="ag_colorizer_json" value="<?php echo htmlspecialchars(get_option('ag_colorizer_json')); ?>" />	
							 <div id="picker"></div>			
						</div>
						<div id="section_templates" style="display:none" class="ag_section">	
							<h2 class="section_title"><span style="float:left">Admin Themes</span></h2>											
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
												<li><a href="http://wordpressadminpanel.com/agca-support/contact/?type=theme" title="If you have any ideas for theme improvements, or you have new themes requests, please feel free to send us a message" target="_blank"><span class="dashicons dashicons-art"></span>&nbsp;&nbsp;Submit your admin themes ideas</a></li>
												<li><a style="background: #f08080;color:#fff;" href="javascript:agca_removeAllTemplates();" title="WARNING: All installed themes will be removed. To activate them again, you would need to install theme and activate using valid license keys. Free themes can be installed again."><span style="color:#fff" class="dashicons dashicons-trash"></span>&nbsp;&nbsp;Uninstall all installed themes</a></li>
											</ul>
										</div>
									</div>
								</td>
							</tr>
							</table>
						</div>
                                                <div id="section_advanced" style="display:none" class="ag_section">
                                                                        <h2 class="section_title">Advanced</h2>
                                                                                <table class="form-table" width="500px">
																					<tr valign="center">
																					<td colspan="2">
																						<p class="agca-tip"><i><strong>Note: </strong>These options will override existing customizations</i></p>
																					</td><td></td>
																					</tr>
                                                                                    <tr valign="center">
                                                                                            <th scope="row">
                                                                                                    <label title="Add custom CSS script to override existing styles" for="agca_script_css">Custom CSS script</em></label>
                                                                                            </th>
                                                                                            <td>
                                                                                            <textarea style="width:100%;height:200px" title="Add custom CSS script to override existing styles" rows="5" id="agca_custom_css"  name="agca_custom_css" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_css')); ?></textarea>
                                                                                            </td>
                                                                                    </tr>	
                                                                                    <tr valign="center">
                                                                                            <th scope="row">
                                                                                                    <label title="Add additional custom JavaScript" for="agca_custom_js">Custom JavaScript</label>
                                                                                            </th>
                                                                                            <td>
                                                                                            <textarea style="width:100%;height:200px" title="Add additional custom JavaScript" rows="5" name="agca_custom_js"  id="agca_custom_js" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_js')); ?></textarea>
                                                                                            </td>
                                                                                    </tr>
                                                                                     <tr valign="center">
                                                                                            <th scope="row">
                                                                                                    <label title="Export / import settings" for="agca_export_import">Export / import settings</label>
                                                                                            </th>
                                                                                            <td id="import_file_area">
                                                                                                <input class="agca_button"  type="button" name="agca_export_settings" value="Export Settings" onclick="exportSettings();"/></br>
                                                                                                <input type="file" id="settings_import_file" name="settings_import_file" style="display: none"/>       
                                                                                                    <input type="hidden" id="_agca_import_settings" name="_agca_import_settings" value="false" /> 
                                                                                                    <input type="hidden" id="_agca_export_settings" name="_agca_export_settings" value="false" /> 
                                                                                               <input class="agca_button" type="button" name="agca_import_settings" value="Import Settings" onclick="importSettings();"/>
                                                                                            </td>                                                                                        
                                                                                    </tr>
                                                                                </table>
                                                </div>
				<p class="submit">
				<input type="button" id="save_plugin_settings" style="padding:0px" title="Save AG Custom Admin configuration" class="button-primary" value="<?php _e('Save Changes') ?>" onClick="savePluginSettings()" />
				</p>
                                
			</form>
			<form id="agca_templates_form" name="agca_templates_form" action="<?php echo $_SERVER['PHP_SELF'];?>?page=ag-custom-admin/plugin.php" method="post">
					<input type="hidden" name="_agca_save_template" value="true" />
					<input type="hidden" id="templates_data" name="templates_data" value="" />								
					<input type="hidden" id="templates_name" name="templates_name" value="" />			
			</form>		
			</div>
		<?php
	}

	#region PRIVATE METHODS
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
		<tr valign="center" <?= $strAttributes ?> >
			<th>
				<label tabindex="0" title='<?= $data['title'] ?>' for="<?= $data['name'] ?>" ><?= $data['label'] ?></label>
			</th>
			<td>
				<input type="checkbox" class="agca-checkbox <?= $strInputClass ?> "  <?= $strOnchange ?>  <?= $strInputAttributes ?> title='Toggle on/off' name="<?= $data['name'] ?>" value="true" <?= ($isChecked)?' checked="checked"':'' ?> />
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
		<tr valign="center" <?= $strAttributes ?> >
			<th >
				<label title="<?= $data['title'] ?>" for="<?= $data['name'] ?>"><?= $data['label'] ?></label>
			</th>
			<td>
				<input id="<?= $data['name'] ?>" title="<?= $data['title'] ?>" type="text" size="47" name="<?= $data['name'] ?>" value="<?php echo get_option($data['name']); ?>" />
				<a title="Clear" class="agca_button clear" onClick="jQuery('#<?= $data['name'] ?>').val('');"><span class="dashicons clear dashicons-no-alt"></span></a><?= $suffix ?>
				<?= $strHint ?>
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
				<label title="<?= $data['title'] ?>" for="<?= $data['name'] ?>"><?= $data['label'] ?></label>
			</th>
			<td>
				<textarea title="<?= $data['title'] ?>" rows="5" name="<?= $data['name'] ?>" cols="40"><?php echo htmlspecialchars(get_option($data['name'])); ?></textarea>
				<?= $strHint ?>
			</td>
		</tr>
		<?php
	}
	function print_color($name, $label, $title){
		?>
		<tr valign="center" class="color">
			<th><label title="<?= $title ?>" for="<?= $name ?>"><?= $label ?></label></th>
			<td><input type="text" id="<?= $name ?>" name="<?= $name ?>" class="color_picker" value="<?php echo $this->getAGCAColor($name); ?>" />
				<a title="Pick Color" alt="<?= $name ?>" class="pick_color_button agca_button"><span class="dashicons dashicons-art"></span></a>
				<a title="Clear" alt="<?= $name ?>" class="pick_color_button_clear agca_button" ><span class="dashicons clear dashicons-no-alt"></span></a>
			</td>
		</tr>
		<?php
	}
	function print_options_h3($title){
		?>
		<tr valign="center">
			<td colspan="2">
				<div class="ag_table_heading"><h3 tabindex="0"><?= $title ?></h3></div>
			</td>
			<td></td>
		</tr>
		<?php
	}
	function print_option_tr(){
		?>

		<tr valign="center">
			<th><label title="Change submenu item background color" for="color_admin_menu_submenu_background">Submenu button background color:</label></th>
			<td><input type="text" id="color_admin_menu_submenu_background" name="color_admin_menu_submenu_background" class="color_picker" value="<?php echo $this->getAGCAColor('color_admin_menu_submenu_background'); ?>" />
				<input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button agca_button" value="Pick color" />
				<input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button_clear agca_button" value="Clear" />
			</td>
		</tr>
		<?php
	}
	#endregion

}
?>