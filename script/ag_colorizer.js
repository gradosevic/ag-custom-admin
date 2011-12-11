/*C O L O R I Z E R*/
function updateTargetColor(id, color){ 
	switch(id)
		{ 
		case 'color_background':		
			jQuery('html, .wp-dialog').css({'background-color':color});			
		  break;
		case 'color_footer':
			jQuery('#footer').css({'background':color});
			if(wpversion >= 3.2){
				jQuery('#footer').css({'margin':'0','margin-left':'146px','padding':'15px'});
			}
		  break;
		 case 'color_header':		 
			jQuery('#wphead').css({'background':color});
			if(wpversion >= 3.2){
				jQuery('#wphead').css({'margin':'0','margin-left':'-14px','padding-left':'15px'});
				jQuery("#backtoblog").attr("style","");
			}
		  break;
		  case 'color_admin_menu_top_button_background':
			jQuery('#adminmenu a.menu-top').css({'background':color});
		  break;
		   case 'color_admin_menu_submenu_background':
		   jQuery("#adminmenu li.wp-has-current-submenu").removeClass("wp-has-current-submenu");			
			jQuery('#adminmenu .wp-submenu a, #adminmenu li.wp-has-current-submenu .wp-submenu a').each(function(){
				jQuery(this).css({'background':color});			
			});
		  break;
		    case 'color_admin_menu_font':
			jQuery('#adminmenu, #adminmenu a, #adminmenu p').css({'color':color});
		  break;
		     case 'color_admin_menu_behind_background':
			jQuery('#adminmenuback, #adminmenuwrap').css({'background-color':color});
		  break;
		   case 'color_admin_menu_behind_border':
			jQuery('#adminmenuback, #adminmenuwrap').css({'border-color':color});
		  break;
		   case 'color_admin_menu_submenu_background_over':
			//jQuery('#adminmenu .wp-submenu a:hover').css({'background':color});
		  break;
		   case 'color_font_content':
			jQuery('#wpbody-content, #wpbody-content label, #wpbody-content p,#wpbody-content .form-table th, #wpbody-content .form-wrap label').css({'color':color});
		  break;
		   case 'color_font_header':
			jQuery('#wphead, #wphead a').css({'color':color});
		  break;
		   case 'color_font_footer':
			jQuery('#footer, #footer a').css({'color':color});
		   break;
			case 'color_widget_bar':
			jQuery(".widget .widget-top, .postbox h3, .stuffbox h3").css({'background' : color, 'text-shadow' :'none'});
		   break;
			case 'color_widget_background':
			jQuery(".widget, .postbox").css('background',color);			
			//jQuery(".widget, #widget-list .widget-top, .postbox, .menu-item-settings").css('background',color); remove if <3.2 work
		  break;		 
		default:	
		}	
}
function updateColor(id,color){
		jQuery("#"+id).css({'background-color':color});
		jQuery("#"+id).val(color);
		if(isDarker(color) == true){
			jQuery("#"+id).css('color','#ffffff');
		}else{
			jQuery("#"+id).css('color','#000000');
		}		
		updateTargetColor(id,color);
}
/*First load apply colours from fields*/

/*C O L O R I Z E R  E N D*/