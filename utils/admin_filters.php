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