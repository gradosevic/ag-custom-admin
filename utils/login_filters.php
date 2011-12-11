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
					<?php if(get_option('agca_login_notifications')==true){ ?>
							jQuery("#login p.message").remove();
					<?php } ?>
					<?php if(get_option('agca_login_forgetmenot')==true){ ?>
							jQuery("form#loginform p.forgetmenot").css("display","none");
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
		