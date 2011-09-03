<?php
/*
Plugin Name: AG Custom Admin
Plugin URI: http://wordpress.org/extend/plugins/ag-custom-admin
Description: Hide or change items in admin panel. Customize buttons from admin menu. Colorize admin and login page with custom colors.
Author: Argonius
Version: 1.2.5
Author URI: http://wordpress.argonius.com/ag-custom-admin

	Copyright 2011. Argonius (email : info@argonius.com)
 
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
	public function __construct()
	{			
		
				
		add_filter('plugin_row_meta', array(&$this,'jk_filter_plugin_links'), 10, 2);
		add_action('admin_init', array(&$this,'agca_register_settings'));
		add_action('admin_head', array(&$this,'print_admin_css'));		
		add_action('login_head', array(&$this,'print_login_head'));	
		add_action('admin_menu', array(&$this,'agca_create_menu'));		
		register_deactivation_hook(__FILE__, array(&$this,'agca_deactivate'));		
		
		/*Styles*/
	//	add_action('admin_menu', array(&$this,'agca_get_styles'));
	//	add_action('login_head', array(&$this,'agca_get_styles'));
	
		/*Initialize properties*/		
		$this->colorizer = $this->jsonMenuArray(get_option('ag_colorizer_json'),'colorizer');		
	}
	// Add donate and support information
	function jk_filter_plugin_links($links, $file)
	{
		if ( $file == plugin_basename(__FILE__) )
		{
		$links[] = '<a href="tools.php?page=ag-custom-admin/plugin.php">' . __('Settings') . '</a>';
		$links[] = '<a href="http://wordpress.argonius.com/ag-custom-admin">' . __('Support') . '</a>';
		$links[] = '<a href="http://wordpress.argonius.com/donate">' . __('Donate') . '</a>';
		}
		return $links;
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
	function agca_get_includes() {
		?>			
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/ag_style.css" />
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/ag_script.js"></script>	
			
		<?php
	}
	
	function reloadScript(){
			?>
			<script type="text/javascript" src="<?php echo get_bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery.js"></script>	
			<?php
			
	}
	
	function agca_register_settings() {	
		register_setting( 'agca-options-group', 'agca_role_allbutadmin' );
		register_setting( 'agca-options-group', 'agca_screen_options_menu' );
		register_setting( 'agca-options-group', 'agca_help_menu' );
		register_setting( 'agca-options-group', 'agca_logout' );
		register_setting( 'agca-options-group', 'agca_remove_your_profile' );
		register_setting( 'agca-options-group', 'agca_logout_only' );
		register_setting( 'agca-options-group', 'agca_options_menu' );
		register_setting( 'agca-options-group', 'agca_howdy' );
		register_setting( 'agca-options-group', 'agca_header' );
		register_setting( 'agca-options-group', 'agca_header_show_logout' );		
		register_setting( 'agca-options-group', 'agca_footer' );
		register_setting( 'agca-options-group', 'agca_privacy_options' );
		register_setting( 'agca-options-group', 'agca_header_logo' );
		register_setting( 'agca-options-group', 'agca_header_logo_custom' );
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
		
		//register_setting( 'agca-options-group', 'agca_menu_dashboard' ); DEPRECATED 1.2
		register_setting( 'agca-options-group', 'agca_dashboard_icon' );
		register_setting( 'agca-options-group', 'agca_dashboard_text' );
		register_setting( 'agca-options-group', 'agca_dashboard_text_paragraph' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_rc' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_il' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_plugins' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_qp' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_rn' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_rd' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_primary' );	
		register_setting( 'agca-options-group', 'agca_dashboard_widget_secondary' );			

		/*Admin menu*/
		register_setting( 'agca-options-group', 'agca_admin_menu_turnonoff' );	
		register_setting( 'agca-options-group', 'agca_admin_menu_agca_button_only' );	
		register_setting( 'agca-options-group', 'agca_admin_menu_separator_first' );	
		register_setting( 'agca-options-group', 'agca_admin_menu_separator_second' );	
		register_setting( 'agca-options-group', 'agca_admin_menu_icons' );		
		register_setting( 'agca-options-group', 'ag_edit_adminmenu_json' );
		register_setting( 'agca-options-group', 'ag_add_adminmenu_json' );	
		register_setting( 'agca-options-group', 'ag_colorizer_json' );	
		register_setting( 'agca-options-group', 'agca_colorizer_turnonoff' );	
		
	}

	function agca_deactivate() {	
		delete_option( 'agca_role_allbutadmin' );
		delete_option( 'agca_screen_options_menu' );
		delete_option(  'agca_help_menu' );
		delete_option(  'agca_logout' );
		delete_option(  'agca_remove_your_profile' );
		delete_option(  'agca_logout_only' );
		delete_option(  'agca_options_menu' );
		delete_option(  'agca_howdy' );
		delete_option(  'agca_header' );
		delete_option(  'agca_header_show_logout' );
		delete_option(  'agca_footer' );
		delete_option(  'agca_privacy_options' );
		delete_option(  'agca_header_logo' );
		delete_option(  'agca_header_logo_custom' );
		delete_option(  'agca_site_heading' );
		delete_option(  'agca_custom_site_heading' );
		delete_option(  'agca_update_bar' );
		
		delete_option(  'agca_footer_left' );
		delete_option(  'agca_footer_left_hide' );
		delete_option(  'agca_footer_right' );
		delete_option(  'agca_footer_right_hide' );
		
		delete_option( 'agca_login_banner' );
		delete_option( 'agca_login_banner_text' );
		delete_option( 'agca_login_photo_remove' );
		delete_option( 'agca_login_photo_url' );
		delete_option( 'agca_login_photo_href' );		
		
		//delete_option(  'agca_menu_dashboard' ); DEPRECATED 1.2
		delete_option(  'agca_dashboard_icon' );
		delete_option(  'agca_dashboard_text' );
		delete_option(  'agca_dashboard_text_paragraph' );	
		delete_option(  'agca_dashboard_widget_rc' );	
		delete_option(  'agca_dashboard_widget_il' );	
		delete_option(  'agca_dashboard_widget_plugins' );	
		delete_option(  'agca_dashboard_widget_qp' );	
		delete_option(  'agca_dashboard_widget_rn' );	
		delete_option(  'agca_dashboard_widget_rd' );	
		delete_option(  'agca_dashboard_widget_primary' );	
		delete_option(  'agca_dashboard_widget_secondary' );

		/*Admin menu*/
		delete_option(  'agca_admin_menu_turnonoff' );
		delete_option(  'agca_admin_menu_agca_button_only' );
		delete_option(  'agca_admin_menu_separator_first' );
		delete_option(  'agca_admin_menu_separator_second' );
		delete_option(  'agca_admin_menu_icons' );
		delete_option(  'ag_edit_adminmenu_json' );
		delete_option(  'ag_add_adminmenu_json' );
		delete_option(  'ag_colorizer_json' );	
		delete_option(  'agca_colorizer_turnonoff' );
	}   
	function agca_create_menu() {
	//create new top-level menu		
		add_management_page( 'AG Custom Admin', 'AG Custom Admin', 'administrator', __FILE__, array(&$this,'agca_admin_page') );
	}
	
	function agca_create_admin_button($name,$href) {
		$class="";
		if($name == 'AG Custom Admin'){
			$class="agca_button_only";
		}
		$button ="";
		$button .= '<li id="menu-'.$name.'" class="ag-custom-button menu-top menu-top-first '.$class.' menu-top-last">';
			/*<div class="wp-menu-image">
				<a href="edit-comments.php"><br></a>
			</div>*/
			$button .= '<div class="wp-menu-toggle" style="display: none;"><br></div>';
			$button .=  '<a tabindex="1" class="menu-top menu-top-last" href="'.$href.'">'.$name.'<a>';
		$button .=  '</li>';
		
		return $button;		
	}	
	function agca_decode($code){
		$code = str_replace("{","",$code);
		$code = str_replace("}","",$code);
		$elements = explode(", ",$code);
		
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
					$array.='<tr><td colspan="2"><button title="'.$v.'" type="button">'.$k.'</button>&nbsp;(<a style="cursor:pointer" class="button_edit">edit</a>)&nbsp;(<a style="cursor:pointer" class="button_remove">remove</a>)</td><td></td></tr>';							
				}	
			}
		}else{
			//$elements = json_decode($arr[$type],true);			
			$elements = $this->agca_decode($arr[$type]);
			if($elements !=""){
				foreach($elements as $element){
					if(!$first){
						$array .=",";
					}
					$parts = explode(" : ",$element);
					$array.="[".$parts[0].", ".$parts[1]."]";					
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

	function print_admin_css()
	{
		$this->agca_get_includes();
		
		get_currentuserinfo() ;
		global $user_level;
		$wpversion = $this->get_wp_version();

	?>	
<?php
	//in case that javaScript is disabled only admin can access admin menu
	if($user_level <= 9){
	?>
		<style type="text/css">
			#adminmenu{display:none;}
		</style>
	<?php
	}
?>	
<script type="text/javascript">
document.write('<style type="text/css">html{visibility:hidden;}</style>');
var wpversion = "<?php echo $wpversion; ?>";
var agca_version = "1.2.5";
var errors = false;

  /* <![CDATA[ */
jQuery(document).ready(function() {	

try
  {  
				
				<?php /*CHECK OTHER PLUGNS*/	
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
					<?php	$checkboxes = $this->jsonMenuArray(get_option('ag_edit_adminmenu_json'),'0');	?>

					var checkboxes = <?php echo "[".$checkboxes."]"; ?>;

					<?php	$textboxes = $this->jsonMenuArray(get_option('ag_edit_adminmenu_json'),'1');	?>
					var textboxes = <?php echo "[".$textboxes."]"; ?>;
					
					<?php	$buttons = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttons');	?>
					var buttons = '<?php echo $buttons; ?>';	
					
					<?php	$buttonsJq = $this->jsonMenuArray(get_option('ag_add_adminmenu_json'),'buttonsJq');	?>
					var buttonsJq = '<?php echo $buttonsJq; ?>';					
					
					<?php if($wpversion >=3.2 ){ ?>
						createEditMenuPageV32(checkboxes,textboxes);
					<?php }else{ ?>
						createEditMenuPage(checkboxes,textboxes);
					<?php } ?>
			
		<?php
		//if admin, and option to hide settings for admin is set
		if((get_option('agca_role_allbutadmin')==true) and  ($user_level > 9)){	
		?>				
		<?php } else{ ?>
			
					<?php if(get_option('agca_screen_options_menu')==true){ ?>
							jQuery("#screen-options-link-wrap").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_help_menu')==true){ ?>
							jQuery("#contextual-help-link-wrap").css("display","none");
							jQuery("#contextual-help-link").css("display","none");							
					<?php } ?>	
					<?php if(get_option('agca_options_menu')==true){ ?>
							jQuery("#favorite-actions").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_privacy_options')==true){ ?>
							jQuery("#privacy-on-link").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_header_logo')==true){ ?>
							jQuery("#wphead #header-logo").css("display","none");
					<?php } ?>
					<?php if(get_option('agca_header_logo_custom')!=""){ ?>								  
							jQuery("#wphead img#header-logo").attr('src','');
							jQuery("#wphead img#header-logo").hide(); 							
							var img_url = '<?php echo get_option('agca_header_logo_custom'); ?>';							
							advanced_url = img_url+ "?" + new Date().getTime();
							image = jQuery("<img />").attr("src",advanced_url);								
							jQuery(image).load(function() {	
								jQuery("#wphead img#header-logo").attr('src', advanced_url);
								jQuery("#wphead img#header-logo").attr('width',this.width);			
								jQuery("#wphead img#header-logo").attr('height',this.height);	
								jQuery("#wphead").css('height', (14 + this.height)+'px');
								jQuery("#wphead img#header-logo").show();										
							});	

					<?php } ?>					
					<?php if(get_option('agca_site_heading')==true){ ?>
							jQuery("#wphead #site-heading").css("display","none");
					<?php } ?>
					<?php if(get_option('agca_custom_site_heading')!=""){ ?>	
							jQuery("#wphead #site-heading").after('<h1><?php echo get_option('agca_custom_site_heading'); ?></h1>');
					<?php } ?>	
					<?php if(get_option('agca_update_bar')==true){ ?>
							jQuery("#update-nag").css("display","none");
							jQuery(".update-nag").css("display","none");							
					<?php } ?>
					<?php if(get_option('agca_header')==true){ ?>
							jQuery("#wphead").css("display","none");
					<?php } ?>	
					<?php if((get_option('agca_header')==true)&&(get_option('agca_header_show_logout')==true)){ ?>
							var clon ="";
							jQuery("div#user_info a").each(function(){
								if(jQuery(this).text() =="Log Out"){
									clon = jQuery(this).clone();
								}								
							});
							if(clon !=""){
								jQuery(clon).attr('style','float:right;padding:15px');	
								jQuery(clon).html('<?php echo ((get_option('agca_logout')=="")?"Log Out":get_option('agca_logout')); ?>');	
							}													
							jQuery("#wphead").after(clon);
							
					<?php } ?>	
					<?php if(get_option('agca_footer')==true){ ?>
							jQuery("#footer").css("display","none");
					<?php } ?>											
					<?php if(get_option('agca_howdy')!=""){ ?>					
							<?php //code for wp version >= 3.2 ?>
							<?php if($wpversion >= 3.2 ){ ?>
									var alltext="";								
									alltext="";
									alltext = jQuery('#user_info div.hide-if-no-js').html();
									alltext = alltext.replace('Howdy',"<?php echo get_option('agca_howdy'); ?>");									
									jQuery("#user_info div.hide-if-no-js").html(alltext);
									
							<?php }else{ ?>
							<?php //code for wp version < 3.2 ?>
								var howdyText = jQuery("#user_info").html();
								if(howdyText !=null){
									jQuery("#user_info").html("<p>"+"<?php echo get_option('agca_howdy'); ?>"+howdyText.substr(9));
								}			
							<?php } ?>							
					<?php } ?>
					<?php if(get_option('agca_logout')!=""){ ?>
							<?php //code for wp version >= 3.2 ?>
							<?php if($wpversion >= 3.2 ){ ?>
								jQuery("#user_info #user_info_links a:eq(1)").text("<?php echo get_option('agca_logout'); ?>");
							<?php }else{ ?>
							<?php //code for wp version < 3.2 ?>
								jQuery("#user_info a:eq(1)").text("<?php echo get_option('agca_logout'); ?>");
							<?php } ?>
					<?php } ?>
					<?php if(get_option('agca_remove_your_profile')==true){ ?>						
							<?php if($wpversion >= 3.2 ){ ?>
								jQuery("#user_info #user_info_links li:eq(0)").remove();
							<?php }?>
					<?php } ?>						
					<?php if(get_option('agca_logout_only')==true){ ?>	
							<?php //code for wp version >= 3.2 ?>
							<?php if($wpversion >= 3.2 ){ ?>							
								var logoutText = jQuery("#user_info a:nth-child(2)").text();
								<?php if(get_option('agca_logout')!=""){ ?>
									logoutText = "<?php echo get_option('agca_logout'); ?>";
								<?php } ?>
								var logoutLink = jQuery("#user_info a:nth-child(2)").attr("href");						
								jQuery("#user_info").html("<a href=\""+logoutLink+"\" title=\"Log Out\">"+logoutText+"</a>");
							<?php }else{ ?>
							<?php //code for wp version < 3.2 ?>
								var logoutText = jQuery("#user_info a:nth-child(2)").text();
								var logoutLink = jQuery("#user_info a:nth-child(2)").attr("href");						
								jQuery("#user_info").html("<a href=\""+logoutLink+"\" title=\"Log Out\">"+logoutText+"</a>");
							<?php } ?>
					<?php } ?>	

					
					<?php if(get_option('agca_footer_left')!=""){ ?>												
								jQuery("#footer-left").html('<?php echo get_option('agca_footer_left'); ?>');
					<?php } ?>	
					<?php if(get_option('agca_footer_left_hide')==true){ ?>											
								jQuery("#footer-left").css("display","none");
					<?php } ?>
					<?php if(get_option('agca_footer_right')!=""){ ?>												
								jQuery("#footer-upgrade").html('<?php echo get_option('agca_footer_right'); ?>');
					<?php } ?>
					<?php if(get_option('agca_footer_right_hide')==true){ ?>											
								jQuery("#footer-upgrade").css("display","none");
					<?php } ?>
					
					<?php if(get_option('agca_language_bar')==true){ ?>
							jQuery("#user_info p").append('<?php include("language_bar/language_bar.php"); ?>');
					<?php } ?>
					<?php //DEPRECATED 1.2
					/*if(get_option('agca_menu_dashboard')==true){ 
							jQuery("#adminmenu #menu-dashboard").css("display","none");
					 } */?>
					<?php if(get_option('agca_dashboard_icon')==true){ ?>
							var className = jQuery("#icon-index").attr("class");
							if(className=='icon32'){
								jQuery("#icon-index").attr("id","icon-index-removed");
							}
					<?php } ?>
					<?php if(get_option('agca_dashboard_text')!=""){ ?>							
							jQuery("#dashboard-widgets-wrap").parent().find("h2").text("<?php echo get_option('agca_dashboard_text'); ?>");
					<?php } ?>
					<?php if(get_option('agca_dashboard_text_paragraph')!=""){ ?>	
							jQuery("#wpbody-content #dashboard-widgets-wrap").before('<br /><p style=\"text-indent:45px;\"><?php echo get_option('agca_dashboard_text_paragraph'); ?></p>');
					<?php } ?>
					
					<?php /*Remove Dashboard widgets*/ ?>
					<?php			

 
						if(get_option('agca_dashboard_widget_rc')==true){
							$this->remove_dashboard_widget('dashboard_recent_comments','normal');
						}else{
							?>jQuery("#dashboard_recent_comments").css("display","block");<?php
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
					?>				
			
					
					<?php /*ADMIN MENU*/ ?>							
								
					
							<?php if(get_option('agca_admin_menu_separator_first')==true){ ?>											
								jQuery("li.wp-menu-separator").eq(0).css("display","none");
							<?php } ?>
							<?php if(get_option('agca_admin_menu_separator_second')==true){ ?>											
								jQuery("li.wp-menu-separator").eq(1).css("display","none");
							<?php } ?>	
							<?php if(get_option('agca_admin_menu_icons') == true){ ?>											
										jQuery(".wp-menu-image").each(function(){
											jQuery(this).css("display","none");
										});
							<?php } ?>	
					<?php if(get_option('agca_admin_menu_turnonoff') == 'on'){ ?>
					
					<?php /*If Turned on*/ ?>
					
					<?php /*Only admin see button*/
							if ($user_level > 9){ ?>								
								jQuery('#adminmenu').append('<?php echo $this->agca_create_admin_button('AG Custom Admin','tools.php?page=ag-custom-admin/plugin.php'); ?>');
							<?php } ?>
							
							<?php if(get_option('agca_admin_menu_agca_button_only') == true){ ?>											
								jQuery('#adminmenu > li').each(function(){
									if(!jQuery(this).hasClass('agca_button_only')){
										jQuery(this).addClass('noclass');
									}
								});
							 <?php } ?>				
													
							<?php /*EDIT MENU ITEMS*/?>
							<?php if(get_option('ag_edit_adminmenu_json')!=""){ 											
									
									?>			
										var checkboxes_counter = 0;
										var createAGCAbutton = false;
									//console.log(checkboxes);							
									//console.log(textboxes);
									<?php //loop through original menu and hide and change elements according to user setttngs ?>																		

										var topmenuitem;
										jQuery('ul#adminmenu > li').each(function(){											
											
											if(!jQuery(this).hasClass("wp-menu-separator") && !jQuery(this).hasClass("wp-menu-separator-last")){
												//alert(checkboxes[checkboxes_counter]);
												
												topmenuitem = jQuery(this).attr('id');
												//console.log(jQuery(this));										
												
												var matchFound = false;
												var subMenus = "";
												
												for(i=0; i< checkboxes.length ; i++){
												
													if(checkboxes[i][0].indexOf("<-TOP->") >=0){ //if it is top item													
														if(checkboxes[i][0].indexOf(topmenuitem) >0){//if found match in menu, with top item in array															
															matchFound = true;		
															//console.log(checkboxes[i][0]);															
															jQuery(this).find('a').eq(1).html(textboxes[i][1]);
															if((checkboxes[i][1] == "true") || (checkboxes[i][1] == "checked")){
																jQuery(this).addClass('noclass');
															}
															
															i++;
															var selector = '#' + topmenuitem + ' ul li';
															//console.log(i+" "+checkboxes);													
																while((i<checkboxes.length) && (checkboxes[i][0].indexOf("<-TOP->") < 0)){															
																	jQuery(selector).each(function(){ //loop through all submenus																	
																		if(checkboxes[i][0] == jQuery(this).text()){
																			if((checkboxes[i][1] == "true") || (checkboxes[i][1] == "checked")){
																				jQuery(this).addClass('noclass');
																			}
																			jQuery(this).find('a').text(textboxes[i][1]);																			
																		}
																	});
																	i++;
																}						
														};
													}else{
														//i++;
													}												
												}
												//console.log(subMenus);					
												//checkboxes_counter++;
											}
										});								
									<?php
							 } ?>
							
							
							/*Add user buttons*/					
							jQuery('#adminmenu').append(buttons);							
							
							
					<?php /*END If Turned on*/ ?>
					<?php } else{ ?>
							jQuery("#adminmenu").removeClass("noclass");
					<?php } ?>				
					
					reloadRemoveButtonEvents();					
				
					
					<?php //COLORIZER ?>
					<?php if(get_option('agca_colorizer_turnonoff') == 'on'){ ?>
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
					
					<?php } ?>
					<?php //COLORIZER END ?>				
<?php } //end of apply for any user except admin ?>		
/*Add user buttons*/	
jQuery('#ag_add_adminmenu').append(buttonsJq); 
 }catch(err){	
	errors = "AGCA - ADMIN ERROR: " + err.name + " / " + err.message;
	alert(errors);		
 }finally{
	jQuery('html').css('visibility','visible');	
	if(errors){
		jQuery("#agca_form").html('<div style="height:500px"><p style="color:red"><strong>WARNING:</strong> AG Custom Admin stops its execution because of an error. Please resolve this error before continue: <br /><br /><strong>' + errors + '</strong></p></div>');
	}	
 }  
 });
 /* ]]> */ 

</script>
		<style type="text/css">
			.underline_text{
				text-decoration:underline;
			}
			.form-table th{
				width:300px;
			}
		</style>
	<?php 	
	}
	
	function print_login_head(){
		
		$this->reloadScript();
		$this->agca_get_includes();		
		$wpversion = $this->get_wp_version();
	?>	
		
	     <script type="text/javascript">
		 document.write('<style type="text/css">html{display:none;}</style>');
		 var wpversion = "<?php echo $wpversion; ?>";		
		 var agca_version = "1.2.5";		 
        /* <![CDATA[ */
            jQuery(document).ready(function() {			
				try{ 
					<?php if(get_option('agca_login_banner')==true){ ?>
							jQuery("#backtoblog").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_login_banner_text')==true){ ?>
							jQuery("#backtoblog").html('<?php echo get_option('agca_login_banner_text'); ?>');
					<?php } ?>
					<?php if(get_option('agca_login_photo_url')==true){ ?>								
							advanced_url = "<?php echo get_option('agca_login_photo_url'); ?>" + "?" + new Date().getTime();
							var $url = "url(" + advanced_url + ")";
							jQuery("#login h1 a").css("background-image",$url);	
							jQuery("#login h1 a").hide();
							image = jQuery("<img />").attr("src",advanced_url);	
							jQuery(image).load(function() {
								var originalWidth = 326;
								var widthDiff = this.width - originalWidth; 
								jQuery("#login h1 a").height(this.height);
								jQuery("#login h1 a").width(this.width);
								jQuery("#login h1 a").css('margin-left',-(widthDiff/2)+"px");
								jQuery("#login h1 a").show();
							});												
					<?php } ?>
					<?php if(get_option('agca_login_photo_href')==true){ ?>						
							var $href = "<?php echo get_option('agca_login_photo_href'); ?>";
							jQuery("#login h1 a").attr("href",$href);							
					<?php } ?>
					<?php if(get_option('agca_login_photo_remove')==true){ ?>
							jQuery("#login h1 a").css("display","none");
					<?php } ?>	
									
						jQuery("#login h1 a").attr("title","");	

						
					<?php //COLORIZER ?>
					<?php if(get_option('agca_colorizer_turnonoff') == 'on'){ ?>
						jQuery('label,h1,h2,h3,h4,h5,h6,a,p,.form-table th,.form-wrap label').css('text-shadow','none');
					<?php					
					
							if($this->colorizer['color_background']!=""){							
								?> 							
								updateTargetColor("color_background","<?php echo $this->colorizer['color_background'];?>"); 								
								<?php
							}	
							if($this->colorizer['color_header']!=""){							
								?> 	
								<?php if($wpversion < 3.2){ ?>
									jQuery("#backtoblog").css("background","<?php echo $this->colorizer['color_header'];?>");
								<?php } ?>
								<?php
							}
							if($this->colorizer['color_font_header']!=""){							
								?> 										
									jQuery("#backtoblog a,#backtoblog p").css("color","<?php echo $this->colorizer['color_font_header'];?>");									
								<?php
							}							
					 } ?>
					<?php //COLORIZER END ?>			
			 }catch(err){				
				alert("AGCA - LOGIN ERROR: " + err.name + " / " + err.message);							
			 }finally{						
						jQuery(document).ready(function() {
							jQuery('html').show();
							jQuery('html').css("display","block");
						});										
			 }
            });
        /* ]]> */
        </script>
	<?php 	
	}
	
	function agca_admin_page() {
	
		$wpversion = $this->get_wp_version();
		?>		
		<?php //includes ?>
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/farbtastic.css" />
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/farbtastic.js"></script>	
			
			<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>style/agca_farbtastic.css" />
			<script type="text/javascript" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>script/agca_farbtastic.js"></script>	
		<?php //includes ?>		
		<div class="wrap">
			<h1 style="color:green">AG Custom Admin Settings <span style="font-size:15px;">(v1.2.5)</span></h1>						
										<div id="agca_news">&nbsp;</div><br />								
			<form method="post" id="agca_form" action="options.php">
				<?php settings_fields( 'agca-options-group' ); ?>
			<table>
				<tr valign="center" >
								<th scope="row">
									<label title="If checked, all users will be affected with these changes, except admin. Not checked = appy for all" for="agca_role_allbutadmin">Do not apply these settings for Admin&nbsp;&nbsp;</label>
								</th>
								<td><input title="If checked, all users will be affected with these changes, except admin. Not checked = appy for all" type="checkbox" name="agca_role_allbutadmin" value="true" <?php if (get_option('agca_role_allbutadmin')==true) echo 'checked="checked" '; echo get_option('agca_role_allbutadmin'); ?> />								
								</td>
				</tr> 
			</table>
			<br />
			<ul id="ag_main_menu">
				<li class="selected"><a href="#admin-bar-settings" title="Settings for admin bar" >Admin Bar</a></li>
				<li class="normal"><a href="#admin-footer-settings" title="Settings for admin footer" >Admin Footer</a></li>
				<li class="normal"><a href="#dashboad-page-settings" title="Settings for Dashboard page">Dashboard Page</a></li>
				<li class="normal"><a href="#login-page-settings" title="Settings for Login page">Login Page</a></li>
				<li class="normal" ><a href="#admin-menu-settings" title="Settings for main admin menu">Admin Menu</a></li>
				<li class="normal"><a href="#ag-colorizer-setttings" title="AG colorizer settings">Colorizer</a></li>
				<li style="background:none;border:none;padding:0;"><a id="agca_donate_button" style="margin-left:8px" title="Do You like this plugin? You can support its future development by providing small donation" href="http://wordpress.argonius.com/donate"><img alt="Donate" src="<?php echo trailingslashit(plugins_url(basename(dirname(__FILE__)))); ?>images/btn_donate_LG.gif" /></a>
				</li>
				<li style="background:none;border:none;padding:0;padding-left:10px;margin-top:-7px"></li>		
			</ul>					
				<div id="section_admin_bar" class="ag_section">
				<h2 class="section_title" tabindex="-1">Admin Bar Settings Page</h2>
				<br />
					<p tabindex="0"><i><strong>Info: </strong>Roll over option labels for more information about option.</i></p>							
				<br />
				<table class="form-table" width="500px">							
							<tr valign="center" class="ag_table_major_options" >
								<td>
									<label tabindex="0" title="Hide admin bar with all elements in top of admin page" for="agca_header"><strong>Hide admin bar completely</strong></label>
								</td>
								<td>					
									<input type="checkbox" title="Hide admin bar with all elements in top of admin page" name="agca_header" value="true" <?php if (get_option('agca_header')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center" class="ag_table_major_options" >
								<td>
									<label tabindex="0" title='Check this if You want to show Log Out button in top right corner of admin page' for="agca_header_show_logout"><strong>(but show Log Out button)</strong></label>
								</td>
								<td>					
									<input type="checkbox" title='Check this if You want to show Log Out button in top right corner of admin page' name="agca_header_show_logout" value="true" <?php if ((get_option('agca_header')==true) && (get_option('agca_header_show_logout')==true)) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">								
								<td colspan="2">
									<div class="ag_table_heading"><h3 tabindex="0">Elements on Left</h3></div>
								</td>
								<td></td>
							</tr>
							<tr valign="center">
								<th >
									<label title="This is link next to heading in admin bar" for="agca_privacy_options">Hide Privacy link</label>
								</th>
								<td>					
									<input type="checkbox" title="This is link next to heading in admin bar" name="agca_privacy_options" value="true" <?php if (get_option('agca_privacy_options')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th >
									<label title="Change default WordPress logo with custom image." for="agca_header_logo_custom">Change WordPress logo</label>
								</th>
								<td>
									<input title="If this field is not empty, image from provided url will be visible in top bar" type="text" size="47" name="agca_header_logo_custom" value="<?php echo get_option('agca_header_logo_custom'); ?>" />																
									&nbsp;<p><i>Put here link of new top bar photo</i>.</p>
								</td>
							</tr> 
							<tr valign="center">
								<th >
									<label title="Small Wordpress logo in admin top bar" for="agca_header_logo">Hide WordPress logo</label>
								</th>
								<td>					
									<input title="Small Wordpress logo in admin top bar" type="checkbox" name="agca_header_logo" value="true" <?php if (get_option('agca_header_logo')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Adds custom text in admin top bar. Default Wordpress heading stays intact." for="agca_custom_site_heading">Custom blog heading</label>
								</th>
								<td>
								<textarea title="Adds custom text in admin top bar. Default Wordpress heading stays intact." rows="5" name="agca_custom_site_heading" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_site_heading')); ?></textarea><p><em><strong>Info: </strong>You can use HTML tags like 'h1' and/or 'a' tag</em></p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Hides yellow bar with notifications of new Wordpress release" for="agca_update_bar">Hide WordPress update notification bar</label>
								</th>
								<td>					
									<input title="Hides yellow bar with notifications of new Wordpress release" type="checkbox" name="agca_update_bar" value="true" <?php if (get_option('agca_update_bar')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_site_heading">Hide default blog heading</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_site_heading" value="true" <?php if (get_option('agca_site_heading')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Elements on Right</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_screen_options_menu-options">Hide Screen Options menu</label>
								</th>
								<td>						
									<input type="checkbox" name="agca_screen_options_menu" value="true" <?php if (get_option('agca_screen_options_menu')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_help_menu">Hide Help menu</label>
								</th>
								<td>						
									<input type="checkbox" name="agca_help_menu" value="true" <?php if (get_option('agca_help_menu')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_options_menu">Hide Favorite Actions</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_options_menu" value="true" <?php if (get_option('agca_options_menu')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 	
							<tr valign="center">
								<th scope="row">
									<label for="agca_howdy">Change Howdy text</label>
								</th>
								<td><input type="text" size="47" name="agca_howdy" value="<?php echo get_option('agca_howdy'); ?>" /></td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Put 'Exit', for example" for="agca_logout">Change Log out text</label>
								</th>
								<td><input title="Put 'Exit', for example" type="text" size="47" name="agca_logout" value="<?php echo get_option('agca_logout'); ?>" /></td>
							</tr> 	
							<?php if($wpversion >= 3.2){ ?>
							<tr valign="center">
								<th scope="row">
									<label for="agca_remove_your_profile">Remove "Your profile" option from dropdown menu</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_remove_your_profile" value="true" <?php if (get_option('agca_remove_your_profile')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<?php } ?>
							<tr valign="center">
								<th scope="row">
									<label title="If selected, hides all elements in top right corner, except Log Out button" for="agca_logout_only">Log out only</label>
								</th>
								<td>
									<input title="If selected, hides all elements in top right corner, except Log Out button" type="checkbox" name="agca_logout_only" value="true" <?php if (get_option('agca_logout_only')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>							
							</table>
						</div>
						
						<div id="section_admin_footer" style="display:none" class="ag_section">	
							<h2 class="section_title" tabindex="-1">Admin Footer Settings Page</h2>
							<br /><br />						
							<table class="form-table" width="500px">		
							<tr valign="center" class="ag_table_major_options">
								<td>
									<label title="Hides footer with all elements" for="agca_footer"><strong>Hide footer completely</strong></label>
								</td>
								<td>					
									<input title="Hides footer with all elements" type="checkbox" id="agca_footer" name="agca_footer" value="true" <?php if (get_option('agca_footer')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Footer Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label title="Hides default text in footer" for="agca_footer_left_hide">Hide footer text</label>
								</th>
								<td><input title="Hides default text in footer" type="checkbox" name="agca_footer_left_hide" value="true" <?php if (get_option('agca_footer_left_hide')==true) echo 'checked="checked" '; ?> />								
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Replaces text 'Thank you for creating with WordPress. | Documentation | Feedback' with custom text" for="agca_footer_left">Change footer text</label>
								</th>
								<td>
									<textarea title="Replaces text 'Thank you for creating with WordPress. | Documentation | Feedback' with custom text" rows="5" name="agca_footer_left" cols="40"><?php echo htmlspecialchars(get_option('agca_footer_left')); ?></textarea>
								</td>						
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Hides text 'Get Version ...' on right" for="agca_footer_right_hide">Hide version text</label>
								</th>
								<td><input title="Hides text 'Get Version ...' on right" type="checkbox" name="agca_footer_right_hide" value="true" <?php if (get_option('agca_footer_right_hide')==true) echo 'checked="checked" '; ?> />								
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Replaces text 'Get Version ...' with custom text" for="agca_footer_right">Change version text</label>
								</th>
								<td>
									<textarea title="Replaces text 'Get Version ...' with custom text" rows="5" name="agca_footer_right" cols="40"><?php echo htmlspecialchars(get_option('agca_footer_right')); ?></textarea>
								</td>
							</tr> 	
							</table>
						</div>
						
						<div id="section_dashboard_page" style="display:none" class="ag_section">	
							<h2 class="section_title"  tabindex="-1">Dashboard Page Settings</h2>
							<table class="form-table" width="500px">	
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Dashboard Page Options</h3></div>
								</td>
								<td></td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label title="This is small 'house' icon next to main heading (Dashboard text by default) on Dashboard page" for="agca_dashboard_icon">Hide Dashboard heading icon</label>
								</th>
								<td>					
									<input title="This is small house icon next to main heading on Dashboard page. Dashboard text is shown by default" type="checkbox" name="agca_dashboard_icon" value="true" <?php if (get_option('agca_dashboard_icon')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							
							<tr valign="center">
								<th scope="row">
									<label title="Main heading ('Dashboard') on Dashboard page" for="agca_dashboard_text">Change Dashboard heading text</label>
								</th>
								<td><input title="Main heading with text 'Dashboard' on Dashboard page" type="text" size="47" name="agca_dashboard_text" value="<?php echo get_option('agca_dashboard_text'); ?>" /></td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label title="Adds custom text (or HTML) between heading and widgets area on Dashboard page" for="agca_dashboard_text_paragraph">Add custom Dashboard content<br> <em>(text or HTML content)</em></label>
								</th>
								<td>
								<textarea title="Adds custom text or HTML between heading and widgets area on Dashboard page" rows="5" name="agca_dashboard_text_paragraph" cols="40"><?php echo htmlspecialchars(get_option('agca_dashboard_text_paragraph')); ?></textarea>
								</td>
							</tr>
							<?php /* DEPRECATED 1.2
							<tr valign="center">
								<th scope="row">
									<label for="agca_menu_dashboard">Hide Dashboard button from main menu</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_menu_dashboard" value="true" <php if (get_option('agca_menu_dashboard')==true) echo 'checked="checked" '; > />
								</td>
							</tr> */ ?>
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Dashboard widgets Options</h3></div>
								</td>
								<td></td>
							</tr>
							<tr><td>
							<p tabindex="0"><i><strong>Info:</strong> These settings override settings in Screen options on Dashboard page.</i></p>							
							</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_rc">Hide "Recent Comments"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_rc" value="true" <?php if (get_option('agca_dashboard_widget_rc')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_il">Hide "Incoming Links"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_il" value="true" <?php if (get_option('agca_dashboard_widget_il')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
								<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_plugins">Hide "Plugins"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_plugins" value="true" <?php if (get_option('agca_dashboard_widget_plugins')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>											
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_qp">Hide "Quick Press"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_qp" value="true" <?php if (get_option('agca_dashboard_widget_qp')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_rn">Hide "Right Now"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_rn" value="true" <?php if (get_option('agca_dashboard_widget_rn')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_widget_rd">Hide "Recent Drafts"</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_widget_rd" value="true" <?php if (get_option('agca_dashboard_widget_rd')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr valign="center">
								<th scope="row">
									<label title="This is 'WordPress Development Blog' widget by default" for="agca_dashboard_widget_primary">Hide primary widget area</label>
								</th>
								<td>					
									<input title="This is 'WordPress Development Blog' widget by default" type="checkbox" name="agca_dashboard_widget_primary" value="true" <?php if (get_option('agca_dashboard_widget_primary')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr valign="center">
								<th scope="row">
									<label title="This is 'Other WordPress News' widget by default"  for="agca_dashboard_widget_secondary">Hide secondary widget area</label>
								</th>
								<td>					
									<input title="This is 'Other WordPress News' widget by default" type="checkbox" name="agca_dashboard_widget_secondary" value="true" <?php if (get_option('agca_dashboard_widget_secondary')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							</table>
						</div>
						<div id="section_login_page" style="display:none" class="ag_section">
						<h2 class="section_title" tabindex="-1">Login Page Settings</h2>
							<br /><br />					
							<table class="form-table" width="500px">							
							<tr valign="center" class="ag_table_major_options">
									<td>
										<label for="agca_login_banner"><strong><?php if($wpversion < 3.2){ ?>Hide Login top bar completely<?php }else{ ?>Hide back to blog block completely<?php } ?></strong></label>
									</td>
									<td>					
										<input type="checkbox" name="agca_login_banner" title="<?php if($wpversion < 3.2){ ?>Hide Login top bar completely<?php }else{ ?>Hide back to blog block completely<?php } ?>" value="true" <?php if (get_option('agca_login_banner')==true) echo 'checked="checked" '; ?> />
									</td>
							</tr>						
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Login Page Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label title="Changes '<- Back to ...' text in top bar on Login page" for="agca_login_banner_text"><?php if($wpversion < 3.2){ ?>Change Login top bar text<?php }else{ ?>Change back to blog text<?php } ?></label>
								</th>
								<td>
									<textarea title="Changes 'Back to ...' text in top bar on Login page" rows="5" name="agca_login_banner_text" cols="40"><?php echo htmlspecialchars(get_option('agca_login_banner_text')); ?></textarea>&nbsp;<p><i>You should surround it with anchor tag &lt;a&gt;&lt;/a&gt;.</i></p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="If this field is not empty, image from provided url will be visible on Login page" for="agca_login_photo_url">Change Login header image</label>
								</th>
								<td>
									<input title="If this field is not empty, image from provided url will be visible on Login page" type="text" size="47" name="agca_login_photo_url" value="<?php echo get_option('agca_login_photo_url'); ?>" />																
									&nbsp;<p><i>Put here link of new login photo. Photo could be of any size and type</i>.</p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Put here custom link to a web location, that will be triggered on image click" for="agca_login_photo_href">Change hyperlink on Login image</label>
								</th>
								<td>
									<input title="Put here custom link to a web location, that will be triggered on image click" type="text" size="47" name="agca_login_photo_href" value="<?php echo get_option('agca_login_photo_href'); ?>" />								
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Remove login image completely" for="agca_login_photo_remove">Hide Login header image</label>
								</th>
								<td>
									<input title="Remove login image completely" type="checkbox" name="agca_login_photo_remove" value="true" <?php if (get_option('agca_login_photo_remove')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
						</table>
						</div>
						<?php
							/*ADMIN MENU*/
						?>
						<div id="section_admin_menu" style="display:none" class="ag_section">
						<h2 class="section_title" tabindex="-1">Admin Menu Settings Page</h2>
						<br />
						<p style="font-style:italic" tabindex="0"><strong>Important: </strong>Please Turn off menu configuration before activating or disabling other plugins (or making any other changes to main menu). Use "Reset Settings" button to restore default values if anything went wrong.</p>					
						<br />
							<table class="form-table" width="500px">	
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_admin_menu_turnonoff"><strong>Turn on/off admin menu configuration</strong></label></td>
								<td><strong><input type="radio" name="agca_admin_menu_turnonoff" title="Turn ON admin menu configuration" value="on" <?php if(get_option('agca_admin_menu_turnonoff') == 'on') echo 'checked="checked" '; ?> /><span style="color:green">ON</span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="agca_admin_menu_turnonoff" title="Turn OFF admin menu configuration" value="off" <?php if(get_option('agca_admin_menu_turnonoff') != 'on') echo 'checked="checked"'; ?> /><span style="color:red">OFF</span></strong></td>
							</tr>
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_admin_menu_agca_button_only"><strong>Hide admin menu completly (administrator can see AG custom admin button)</strong></label></td>
								<td><input type="checkbox" name="agca_admin_menu_agca_button_only" title="Hide admin menu completly (administrator can see 'AG custom admin' button)" value="true" <?php if (get_option('agca_admin_menu_agca_button_only')==true) echo 'checked="checked" '; ?> /></td>
							</tr>
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Edit/Remove Menu Items</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr>
								<td colspan="2">
								Reset to default values
											<button type="button" id="ag_edit_adminmenu_reset_button" title="Reset menu settings to default values" name="ag_edit_adminmenu_reset_button">Reset Settings</button><br />
											<p tabindex="0"><em>(click on menu link to show/hide its submenus below it)</em></p>
									<table id="ag_edit_adminmenu">									
										<tr style="background-color:#999;">
											<td width="300px"><div style="float:left;color:#fff;"><h3>Item</h3></div><div style="float:right;color:#fff;"><h3>Remove?</h3></div></td><td width="300px" style="color:#fff;" ><h3>Change Text</h3>													
											</td>
										</tr>
									</table>
									<input type="hidden" size="47" id="ag_edit_adminmenu_json" name="ag_edit_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_edit_adminmenu_json')); ?>" />
								</td>
								<td></td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label title="This is arrow like separator between Dashboard and Posts button (by default)" for="agca_admin_menu_separator_first">Remove first items separator</label>
								</th>
								<td>
									<input title="This is arrow like separator between Dashboard and Posts button (by default)" type="checkbox" name="agca_admin_menu_separator_first" value="true" <?php if (get_option('agca_admin_menu_separator_first')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="This is arrow like separator between Comments and Appearance button (by default)" for="agca_admin_menu_separator_second">Remove second items separator</label>
								</th>
								<td>
									<input title="This is arrow like separator between Comments and Appearance button (by default)" type="checkbox" name="agca_admin_menu_separator_second" value="true" <?php if (get_option('agca_admin_menu_separator_second')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label title="Removes small icons on admin menu buttons" for="agca_admin_menu_icons">Remove menu icons</label>
								</th>
								<td>
									<input title="Removes small icons on admin menu buttons" type="checkbox" name="agca_admin_menu_icons" value="true" <?php if (get_option('agca_admin_menu_icons')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Add New Menu Items</h3></div>
								</td>
								<td>									
								</td>
							</tr> 
							<tr>
								<td colspan="2">
									
									<table id="ag_add_adminmenu">									
										<tr>
											<td colspan="2">
												name:<input type="text" size="47" title="New button visible name" id="ag_add_adminmenu_name" name="ag_add_adminmenu_name" />
												url:<input type="text" size="47" title="New button link" id="ag_add_adminmenu_url" name="ag_add_adminmenu_url" />
												<button type="button" id="ag_add_adminmenu_button" title="Add new item button" name="ag_add_adminmenu_button">Add new item</button>	
											</td><td></td>	
										</tr>
									</table>
								<input type="hidden" size="47" id="ag_add_adminmenu_json" name="ag_add_adminmenu_json" value="<?php echo htmlspecialchars(get_option('ag_add_adminmenu_json')); ?>" />									
								</td>						
								<td>									
								</td>								
							</tr>
							</table>
						</div>
						<div id="section_ag_colorizer_settings" style="display:none" class="ag_section">
						<h2 class="section_title">Colorizer Page</h2>
						<br />
						<p><i><strong>Info: </strong>Change Admin panel colors (This is Colorizer demo with only few color options. More color options will be available in future realeases).</i></p>					
						<br />
						<table class="form-table" width="500px">	
							<tr valign="center" class="ag_table_major_options">
								<td><label for="agca_colorizer_turnonoff"><strong>Turn on/off Colorizer configuration</strong></label></td>
								<td><strong><input type="radio" name="agca_colorizer_turnonoff" title="Turn ON Colorizer configuration" value="on" <?php if(get_option('agca_colorizer_turnonoff') == 'on') echo 'checked="checked" '; ?> /><span style="color:green">ON</span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="agca_colorizer_turnonoff" title="Turn OFF Colorizer configuration" value="off" <?php if(get_option('agca_colorizer_turnonoff') != 'on') echo 'checked="checked"'; ?> /><span style="color:red">OFF</span></strong></td>
							</tr>	
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Global Color Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change admin background color" for="color_background">Background color:</label></th>
								<td><input type="text" id="color_background" name="color_background" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_background']); ?>" />
									<input type="button" alt="color_background" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_background" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change footer color in admin panel" for="color_footer">Footer color:</label></th>
								<td><input type="text" id="color_footer" name="color_footer" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_footer']); ?>" />
									<input type="button" alt="color_footer" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_footer" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change header color in admin panel" for="color_header">Header color:</label></th>
								<td><input type="text" id="color_header" name="color_header" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_header']); ?>" />
									<input type="button" alt="color_header" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_header" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Admin Menu Color Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change background color in admin menu" for="color_admin_menu_top_button_background">Top button background color:</label></th>
								<td><input type="text" id="color_admin_menu_top_button_background" name="color_admin_menu_top_button_background" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_admin_menu_top_button_background']); ?>" />
									<input type="button" alt="color_admin_menu_top_button_background" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_admin_menu_top_button_background" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change background submenu color in admin menu" for="color_admin_menu_submenu_background">Submenu button background color:</label></th>
								<td><input type="text" id="color_admin_menu_submenu_background" name="color_admin_menu_submenu_background" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_admin_menu_submenu_background']); ?>" />
									<input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_admin_menu_submenu_background" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change text color in admin menu" for="color_admin_menu_font">Text color:</label></th>
								<td><input type="text" id="color_admin_menu_font" name="color_admin_menu_font" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_admin_menu_font']); ?>" />
									<input type="button" alt="color_admin_menu_font" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_admin_menu_font" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<?php if($wpversion >= 3.2) { ?>
							<tr valign="center">
								<th><label title="Change background color of element behind admin menu" for="color_admin_menu_behind_background">Wrapper background color:</label></th>
								<td><input type="text" id="color_admin_menu_behind_background" name="color_admin_menu_behind_background" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_admin_menu_behind_background']); ?>" />
									<input type="button" alt="color_admin_menu_behind_background" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_admin_menu_behind_background" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change border color of element behind admin menu" for="color_admin_menu_behind_border">Wrapper border color:</label></th>
								<td><input type="text" id="color_admin_menu_behind_border" name="color_admin_menu_behind_border" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_admin_menu_behind_border']); ?>" />
									<input type="button" alt="color_admin_menu_behind_border" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_admin_menu_behind_border" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<?php } ?>
							<!--<tr valign="center">
								<th><label title="Change background submenu color on mouse over in admin menu" for="color_admin_menu_submenu_background_over">Submenu button background (Mouse over):</label></th>
								<td><input type="text" id="color_admin_menu_submenu_background_over" name="color_admin_menu_submenu_background_over" class="color_picker" value="#123456" />
									<input type="button" alt="color_admin_menu_submenu_background_over" class="pick_color_button" value="Pick color" />
								</td>
							</tr>-->
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Font Color Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change color in content text" for="color_font_content">Content text color:</label></th>
								<td><input type="text" id="color_font_content" name="color_font_content" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_font_content']); ?>" />
									<input type="button" alt="color_font_content" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_font_content" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change color in header text" for="color_font_header">Header text color:</label></th>
								<td><input type="text" id="color_font_header" name="color_font_header" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_font_header']); ?>" />
									<input type="button" alt="color_font_header" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_font_header" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change color in fotter text" for="color_font_footer">Footer text color:</label></th>
								<td><input type="text" id="color_font_footer" name="color_font_footer" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_font_footer']); ?>" />
									<input type="button" alt="color_font_footer" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_font_footer" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>	
							<tr valign="center">
								<td colspan="2">
										<div class="ag_table_heading"><h3 tabindex="0">Widgets Color Options</h3></div>
								</td>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change color in header text" for="color_widget_bar">Title bar background color:</label></th>
								<td><input type="text" id="color_widget_bar" name="color_widget_bar" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_widget_bar']); ?>" />
									<input type="button" alt="color_widget_bar" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_widget_bar" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>
							<tr valign="center">
								<th><label title="Change widget background color" for="color_widget_background">Background color:</label></th>
								<td><input type="text" id="color_widget_background" name="color_widget_background" class="color_picker" value="<?php echo htmlspecialchars($this->colorizer['color_widget_background']); ?>" />
									<input type="button" alt="color_widget_background" class="pick_color_button" value="Pick color" />
									<input type="button" alt="color_widget_background" class="pick_color_button_clear" value="Clear" />
								</td>
							</tr>	
							</table>
							<input type="hidden" size="47" id="ag_colorizer_json" name="ag_colorizer_json" value="<?php echo htmlspecialchars(get_option('ag_colorizer_json')); ?>" />	
							 <div id="picker"></div>			
						</div>					
				<br /><br /><br />
				<p class="submit">
				<input type="submit" title="Save changes button" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

			</form>
			</div>
							<p tabindex="0"><i><strong>Info:</strong> You can use HTML tags in text areas, e.g. &lt;a href=&quot;http://www.mywebsite.com&quot;&gt;Visit Us&lt;/a&gt;</i></p>
										<br />
			<br /><br /><br /><p id="agca_footer_support_info">WordPress 'AG Custom Admin' plugin by Argonius. If you have any questions, ideas for future development or if you found a bug or having any issues regarding this plugin, please visit plugin's <a href="http://wordpress.argonius.com/ag-custom-admin">SUPPORT</a> page. <br />You can also participate in development of this plugin if you <a href="http://wordpress.argonius.com/donate">BUY ME A DRINK</a> to refresh my energy for programming. Thanks!<br /><br />Have a nice blogging!</p><br />
		<?php
	}
}
?>