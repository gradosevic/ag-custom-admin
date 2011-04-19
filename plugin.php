<?php
/*
Plugin Name: AG Custom Admin
Plugin URI: http://wordpress.org/extend/plugins/ag-custom-admin
Description: Hide or change items in admin panel.
Author: Argonius
Version: 1.0
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
	
	public function __construct()
	{		
		add_filter('plugin_row_meta', array(&$this,'jk_filter_plugin_links'), 10, 2);
		add_action('admin_init', array(&$this,'agca_register_settings'));
		add_action('admin_head', array(&$this,'print_admin_css'));		
		add_action('login_head', array(&$this,'print_login_head'));	
		add_action('admin_menu', array(&$this,'agca_create_menu'));
		register_deactivation_hook(__FILE__, array(&$this,'agca_deactivate'));				
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
	
	function reloadScript(){
			?>
			<script type="text/javascript" src="<?php echo get_settings('home'); ?>/wp-includes/js/jquery/jquery.js"></script>				
			<?php
	}
	
	function agca_register_settings() {
		register_setting( 'agca-options-group', 'agca_screen_options_menu' );
		register_setting( 'agca-options-group', 'agca_help_menu' );
		register_setting( 'agca-options-group', 'agca_logout' );
		register_setting( 'agca-options-group', 'agca_logout_only' );
		register_setting( 'agca-options-group', 'agca_options_menu' );
		register_setting( 'agca-options-group', 'agca_howdy' );
		register_setting( 'agca-options-group', 'agca_header' );
		register_setting( 'agca-options-group', 'agca_footer' );
		register_setting( 'agca-options-group', 'agca_privacy_options' );
		register_setting( 'agca-options-group', 'agca_header_logo' );
		register_setting( 'agca-options-group', 'agca_site_heading' );
		register_setting( 'agca-options-group', 'agca_custom_site_heading' );
		register_setting( 'agca-options-group', 'agca_update_bar' );
		
		register_setting( 'agca-options-group', 'agca_footer_left' );
		register_setting( 'agca-options-group', 'agca_footer_right' );
		register_setting( 'agca-options-group', 'agca_footer_right_hide' );
		
		register_setting( 'agca-options-group', 'agca_login_banner' );
		register_setting( 'agca-options-group', 'agca_login_banner_text' );
		register_setting( 'agca-options-group', 'agca_login_photo_remove' );
		register_setting( 'agca-options-group', 'agca_login_photo_url' );
		register_setting( 'agca-options-group', 'agca_login_photo_href' );
		
		register_setting( 'agca-options-group', 'agca_menu_dashboard' );
		register_setting( 'agca-options-group', 'agca_dashboard_icon' );
		register_setting( 'agca-options-group', 'agca_dashboard_text' );
		register_setting( 'agca-options-group', 'agca_dashboard_text_paragraph' );		

	}

	function agca_deactivate() {
		delete_option( 'agca_screen_options_menu' );
		delete_option(  'agca_help_menu' );
		delete_option(  'agca_logout' );
		delete_option(  'agca_logout_only' );
		delete_option(  'agca_options_menu' );
		delete_option(  'agca_howdy' );
		delete_option(  'agca_header' );
		delete_option(  'agca_footer' );
		delete_option(  'agca_privacy_options' );
		delete_option(  'agca_header_logo' );
		delete_option(  'agca_site_heading' );
		delete_option(  'agca_custom_site_heading' );
		delete_option(  'agca_update_bar' );
		
		delete_option(  'agca_footer_left' );
		delete_option(  'agca_footer_right' );
		delete_option(  'agca_footer_right_hide' );
		
		delete_option( 'agca_login_banner' );
		delete_option( 'agca_login_banner_text' );
		delete_option( 'agca_login_photo_remove' );
		delete_option( 'agca_login_photo_url' );
		delete_option( 'agca_login_photo_href' );
		
		
		delete_option(  'agca_menu_dashboard' );
		delete_option(  'agca_dashboard_icon' );
		delete_option(  'agca_dashboard_text' );
		delete_option(  'agca_dashboard_text_paragraph' );		

		
	
	}   
	function agca_create_menu() {
	//create new top-level menu		
		add_management_page( 'AG Custom Admin', 'AG Custom Admin', 'administrator', __FILE__, array(&$this,'agca_admin_page') );
	}

	function print_admin_css()
	{?>		
	      <script type="text/javascript">
        /* <![CDATA[ */
            jQuery(document).ready(function() {

					<?php if(get_option('agca_screen_options_menu')==true){ ?>
							jQuery("#screen-options-link-wrap").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_help_menu')==true){ ?>
							jQuery("#contextual-help-link-wrap").css("display","none");
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
					<?php if(get_option('agca_site_heading')==true){ ?>
							jQuery("#wphead #site-heading").css("display","none");
					<?php } ?>
					<?php if(get_option('agca_custom_site_heading')!=""){ ?>	
							jQuery("#wphead #site-heading").after('<h1><?php echo get_option('agca_custom_site_heading'); ?></h1>');
					<?php } ?>	
					<?php if(get_option('agca_update_bar')==true){ ?>
							jQuery(".update-nag").css("display","none");
					<?php } ?>
					<?php if(get_option('agca_header')==true){ ?>
							jQuery("#wphead").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_footer')==true){ ?>
							jQuery("#footer").css("display","none");
					<?php } ?>											
					<?php if(get_option('agca_howdy')!=""){ ?>
							var howdyText = jQuery("#user_info").html();
							jQuery("#user_info").html("<p>"+"<?php echo get_option('agca_howdy'); ?>"+howdyText.substr(9));	
					<?php } ?>
					<?php if(get_option('agca_logout')!=""){ ?>							
							jQuery("#user_info a:eq(1)").text("<?php echo get_option('agca_logout'); ?>");
					<?php } ?>
					<?php if(get_option('agca_logout_only')==true){ ?>						
							var logoutText = jQuery("#user_info a:nth-child(2)").text();
							var logoutLink = jQuery("#user_info a:nth-child(2)").attr("href");						
							jQuery("#user_info").html("<a href=\""+logoutLink+"\" title=\"Log Out\">"+logoutText+"</a>");
					<?php } ?>	

					
					<?php if(get_option('agca_footer_left')!=""){ ?>												
								jQuery("#footer-left").html('<?php echo get_option('agca_footer_left'); ?>');
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
					<?php if(get_option('agca_menu_dashboard')==true){ ?>
							jQuery("#adminmenu #menu-dashboard").css("display","none");
					<?php } ?>
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
					  
										 
										
            });
        /* ]]> */
        </script>
		<style type="text/css">
			.underline_text{
				text-decoration:underline;
			}
		</style>
	<?php 	
	}
	
		function print_login_head(){
		
		$this->reloadScript();
	?>	
	     <script type="text/javascript">
        /* <![CDATA[ */
            jQuery(document).ready(function() {

					<?php if(get_option('agca_login_banner')==true){ ?>
							jQuery("#backtoblog").css("display","none");
					<?php } ?>	
					<?php if(get_option('agca_login_banner_text')==true){ ?>
							jQuery("#backtoblog").html('<?php echo get_option('agca_login_banner_text'); ?>');
					<?php } ?>
					<?php if(get_option('agca_login_photo_url')==true){ ?>						
							var $url = "url(" + "<?php echo get_option('agca_login_photo_url'); ?>" + ")";
							jQuery("#login h1 a").css("background-image",$url);							
					<?php } ?>
					<?php if(get_option('agca_login_photo_href')==true){ ?>						
							var $href = "<?php echo get_option('agca_login_photo_href'); ?>";
							jQuery("#login h1 a").attr("href",$href);							
					<?php } ?>
					<?php if(get_option('agca_login_photo_remove')==true){ ?>
							jQuery("#login h1 a").css("display","none");
					<?php } ?>	
									
						jQuery("#login h1 a").attr("title","");		
            });
        /* ]]> */
        </script>
	<?php 	
	}
	
	function agca_admin_page() {
		?>
					<div class="wrap">
			<h1>AG Custom Admin Options</h1>						
										<br />						
			<form method="post" action="options.php">
				<?php settings_fields( 'agca-options-group' ); ?>				
				<table class="form-table">
							<tr valign="center">
								<th scope="row">
									<h2>Header Options</h2>
								</th>						
							</tr>
							<br />
							<tr valign="center">
								<td scope="row">
									<label for="agca_header"><p><strong>Hide header completely</strong></p></label>
								</td>
								<td scope="row">					
									<input type="checkbox" name="agca_header" value="true" <?php if (get_option('agca_header')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<br /><p class="underline_text">On left:</p>
								</th>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_privacy_options">Hide Privacy link</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_privacy_options" value="true" <?php if (get_option('agca_privacy_options')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_header_logo">Hide header logo</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_header_logo" value="true" <?php if (get_option('agca_header_logo')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_custom_site_heading">Custom site heading</label>
								</th>
								<td>
								<textarea rows="5" name="agca_custom_site_heading" cols="40"><?php echo htmlspecialchars(get_option('agca_custom_site_heading')); ?>									
									</textarea><p><em><strong>Info: </strong>Use 'h1' and/or 'a' tag</em></p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_update_bar">Hide WP update to latest version notification</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_update_bar" value="true" <?php if (get_option('agca_update_bar')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_site_heading">Hide original site heading</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_site_heading" value="true" <?php if (get_option('agca_site_heading')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<br /><p class="underline_text">On right</p>
								</th>
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
									<label for="agca_logout">Change Log out text</label>
								</th>
								<td><input type="text" size="47" name="agca_logout" value="<?php echo get_option('agca_logout'); ?>" /></td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_logout_only">Log out only</label>
								</th>
								<td>
									<input type="checkbox" name="agca_logout_only" value="true" <?php if (get_option('agca_logout_only')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 

							 <tr valign="center">
								<th scope="row">
									<br /><br /><br /><h2>Footer Options</h2>
								</th>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_footer"><p><strong>Hide footer completely</strong></p></label>
								</th>
								<td>					
									<input type="checkbox" name="agca_footer" value="true" <?php if (get_option('agca_footer')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_footer_left">Change footer text</label>
								</th>
								<td>
									<textarea rows="5" name="agca_footer_left" cols="40"><?php echo htmlspecialchars(get_option('agca_footer_left')); ?>									
									</textarea>
								</td>						
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_footer_right_hide">Hide version text</label>
								</th>
								<td><input type="checkbox" name="agca_footer_right_hide" value="true" <?php if (get_option('agca_footer_right_hide')==true) echo 'checked="checked" '; ?> />								
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_footer_right">Change version text</label>
								</th>
								<td>
									<textarea rows="5" name="agca_footer_right" cols="40"><?php echo htmlspecialchars(get_option('agca_footer_right')); ?>									
									</textarea>
								</td>
							</tr> 								
							<tr valign="center">
								<th scope="row" colspan="2">
									<br /><br /><br /><h2>Dashboard Options</h2>
								</th>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_icon">Hide Dashboard icon</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_dashboard_icon" value="true" <?php if (get_option('agca_dashboard_icon')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_text">Change Dashboard text</label>
								</th>
								<td><input type="text" size="47" name="agca_dashboard_text" value="<?php echo get_option('agca_dashboard_text'); ?>" /></td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_dashboard_text_paragraph">Add Dashboard paragraph text</label>
								</th>
								<td>
								<textarea rows="5" name="agca_dashboard_text_paragraph" cols="40"><?php echo htmlspecialchars(get_option('agca_dashboard_text_paragraph')); ?>									
									</textarea>
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_menu_dashboard">Hide Dashboard from menu</label>
								</th>
								<td>					
									<input type="checkbox" name="agca_menu_dashboard" value="true" <?php if (get_option('agca_menu_dashboard')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>	
							<tr><td>
							<p><i><strong>Info:</strong> To remove Dashboard widgets go to Screen Options on Dashboard page.</i></p>							
							</td>
							</tr>
							 <tr valign="top">
								<th scope="row" colspan="2">
									<br /><br /><br /><h2>Login Page Options</h2>
								</th>
								<td>									
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_login_banner"><strong>Hide Login top bar completely</strong></label>
								</th>
								<td>					
									<input type="checkbox" name="agca_login_banner" value="true" <?php if (get_option('agca_login_banner')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr>
							<tr valign="center">
								<th scope="row">
									<label for="agca_login_banner_text">Change Login top bar text</label>
								</th>
								<td>
									<textarea rows="5" name="agca_login_banner_text" cols="40"><?php echo htmlspecialchars(get_option('agca_login_banner_text')); ?>									
									</textarea>&nbsp;<p><i>You should surround it with anchor tag &lt;a&gt;&lt;/a&gt;.</i></p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_login_photo_url">Change Login header image</label>
								</th>
								<td>
									<input type="text" size="47" name="agca_login_photo_url" value="<?php echo get_option('agca_login_photo_url'); ?>" />																
									&nbsp;<p><i>Put here link of new login photo (e.g http://www.photo.com/myphoto.jpg). Original photo dimensions are: 310px x 70px</i>.</p>
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_login_photo_href">Change hyperlink on Login image</label>
								</th>
								<td>
									<input type="text" size="47" name="agca_login_photo_href" value="<?php echo get_option('agca_login_photo_href'); ?>" />								
								</td>
							</tr> 
							<tr valign="center">
								<th scope="row">
									<label for="agca_login_photo_remove">Hide Login header image</label>
								</th>
								<td>
									<input type="checkbox" name="agca_login_photo_remove" value="true" <?php if (get_option('agca_login_photo_remove')==true) echo 'checked="checked" '; ?> />
								</td>
							</tr> 
				</table>
				<br /><br /><br />
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

			</form>
			</div>
							<p><i><strong>Info:</strong> You can use HTML tags in text areas, e.g. &lt;a href=&quot;http://www.mywebsite.com&quot;&gt;Visit Us&lt;/a&gt;</i></p>
										<br />
			<br /><br /><br /><br />
		<?php
	}
}
?>