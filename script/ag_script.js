var afterFormClickCreateJson = true;
var editingButtonNow = false;

function booleanToChecked(bool){
	if(bool == 'true'){	
		return 'checked="checked"';
	}else if(bool == 'checked'){	
		return 'checked="checked"';
	}
}

function hideShowSubmenus(index){
	
	var finish = false;
	var	found = false;
	jQuery('#ag_edit_adminmenu td').each(function(){	
		if(jQuery('#ag_edit_adminmenu td').index(jQuery(this)) >= index && (finish == false)){			
			if(jQuery(this).hasClass('ag_admin_menu_child')){
				jQuery(this).parent().toggleClass('noclass');
				found = true;
			}
			if((jQuery('#ag_edit_adminmenu td').index(jQuery(this)) > index) && jQuery(this).hasClass('ag_admin_menu_parent')){			
				finish = true;
			}
		}
	});
	/*FOCUS*/
	if(!jQuery('#ag_edit_adminmenu td').eq((index+2)).parent().hasClass('noclass') && (found == true)){
		jQuery('#ag_edit_adminmenu td').eq((index+2)).find('a').trigger('focus');		
	};	
}

/*
	Makes admin edit page pretty grouping items and submenus, and adding fancy interactions
*/
function prettyEditMenuPage(){
	jQuery('#ag_edit_adminmenu td').each(function(){
		if(jQuery(this).hasClass('ag_admin_menu_child')){
			jQuery(this).parent().addClass('noclass');
		};
	});
	jQuery('#ag_edit_adminmenu td').each(function(){
		if(jQuery(this).hasClass('ag_admin_menu_parent')){
		jQuery(this).parent().css('background-color','#d8eAd8');
		jQuery(this).bind('click',function(evt){	
			if(evt.target.className == 'ag_admin_menu_parent'){
				hideShowSubmenus(jQuery('#ag_edit_adminmenu td').index(this));
			}			
		});
		};
	});
	jQuery('#ag_edit_adminmenu td > a').bind('click',function(){	
		jQuery(this).parent().click();		
		//jQuery(this).parent().focus();
	});
};

function createEditMenuPage(checkboxes,textboxes){		
	 /*Create menu page in AGCA settings*/	
	
	 //console.log(textboxes);
	var counter = 0;
	var TBlength = textboxes.length;
	if(textboxes==""){
		TBlength = 9999999;
	}
	
	var topElement="";
	jQuery('ul#adminmenu li').each(function(){  
		if(!jQuery(this).hasClass("wp-menu-separator") && !jQuery(this).hasClass("wp-menu-separator-last") && !jQuery(this).hasClass("ag-custom-button") && (counter < TBlength )){	
			
			//if subelement
			if(jQuery(this).parent().parent().hasClass('wp-submenu')){	
				subElement = jQuery(this).find('a').text();
				//console.log(jQuery(this));
				//console.log(subElement);
				var isHidden = "";
				var sub_item_text_value;
				if(textboxes ==""){	
					sub_item_text_value = subElement;
				}else{
					sub_item_text_value = textboxes[counter][1];
					isHidden = checkboxes[counter][1];
				}	
				jQuery('#ag_edit_adminmenu').append("<tr><td class='ag_admin_menu_child'><div style=\"float:left\"><a tabindex=\"-1\" href=\"javascript:void(0)\" style=\"font-weight:bold;\"title=\""+topElement+" submenu: "+subElement+"\"><span style=\"font-weight:normal\">submenu: </span>"+subElement+"</a></div><div style=\"float:right\"><input type=\"checkbox\" title=\"Remove "+topElement+" submenu: "+subElement+" from menu\" class=\""+subElement+"\" "+booleanToChecked(isHidden)+"  name=\"ag_edit_adminmenu_item_sub_"+counter+"\" /></div></td><td class='ag_admin_menu_child2' ><input type=\"text\" title=\"Rename submenu item "+subElement+" with this value\" class=\""+subElement+"\" size=\"47\" value=\""+sub_item_text_value+"\" name=\"ag_edit_adminmenu_item_sub_"+counter+"\" /></td></tr>");
			}
			//if top element
			else{
				topElement = jQuery(this).children('a').clone().children().remove().end().text();		
				topElement = jQuery.trim(topElement);
				var top_item_text_value;
				var isHidden = "";
				if(textboxes ==""){	
					top_item_text_value = topElement;
				}else{
					top_item_text_value = textboxes[counter][1];
					isHidden = checkboxes[counter][1];					
				}	
				jQuery('#ag_edit_adminmenu').append("<tr><td class='ag_admin_menu_parent'><br /><a tabindex=\"0\" href=\"javascript:void(0)\" >" + topElement +"</a><div style=\"float:right\"><input title=\"Remove "+topElement+" from menu\" class=\""+jQuery(this).attr("id")+"\" type=\"checkbox\" "+booleanToChecked(isHidden)+" name=\"ag_edit_adminmenu_item_top_"+counter+"\" /></div></td><td class='ag_admin_menu_parent2' ><input title=\"Rename "+topElement+" with this value\" type=\"text\" class=\""+jQuery(this).attr("id")+"\" size=\"47\" value=\""+top_item_text_value+"\" name=\"ag_edit_adminmenu_item_top_"+counter+"\" /></td></tr>");
			}			
		counter++;
		}		
	});
	 
	//console.log(checkboxes.replace('<-TOP->','')+"|"+textboxes.replace('<-TOP->',''));	  
	  prettyEditMenuPage();
}

function createEditMenuPageV32(checkboxes,textboxes){		
	 /*Create menu page in AGCA settings*/	
	
	var counter = 0;
	var TBlength = textboxes.length;
	if(textboxes==""){
		TBlength = 9999999;
	}
	
	var topElement="";
	jQuery('ul#adminmenu li').each(function(){  
		if(!jQuery(this).hasClass("wp-menu-separator") && !jQuery(this).hasClass("wp-menu-separator-last") && !jQuery(this).hasClass("ag-custom-button") && (jQuery(this).attr('id') !="collapse-menu") && (counter < TBlength )){	
			
			//if subelement
			if(jQuery(this).parent().parent().parent().hasClass('wp-submenu')){	
				subElement = jQuery(this).find('a').text();
				//console.log(jQuery(this));
				//console.log(subElement);
				var isHidden = "";
				var sub_item_text_value;
				if(textboxes ==""){	
					sub_item_text_value = subElement;
				}else{
					sub_item_text_value = textboxes[counter][1];
					isHidden = checkboxes[counter][1];					
				}	
				jQuery('#ag_edit_adminmenu').append("<tr><td class='ag_admin_menu_child'><div style=\"float:left\"><a tabindex=\"-1\" href=\"javascript:void(0)\" style=\"font-weight:bold;\"title=\""+topElement+" submenu: "+subElement+"\"><span style=\"font-weight:normal\">submenu: </span>"+subElement+"</a></div><div style=\"float:right\"><input type=\"checkbox\" title=\"Remove "+topElement+" submenu: "+subElement+" from menu\" class=\""+subElement+"\" "+booleanToChecked(isHidden)+"  name=\"ag_edit_adminmenu_item_sub_"+counter+"\" /></div></td><td class='ag_admin_menu_child2' ><input type=\"text\" title=\"Rename submenu item "+subElement+" with this value\" class=\""+subElement+"\" size=\"47\" value=\""+sub_item_text_value+"\" name=\"ag_edit_adminmenu_item_sub_"+counter+"\" /></td></tr>");
			}
			//if top element
			else{
				topElement = jQuery(this).children('a').clone().children().remove().end().text();		
				topElement = jQuery.trim(topElement);
				var top_item_text_value;
				var isHidden = "";
				if(textboxes ==""){	
					top_item_text_value = topElement;
				}else{
					top_item_text_value = textboxes[counter][1];
					isHidden = checkboxes[counter][1];
				}	
				jQuery('#ag_edit_adminmenu').append("<tr><td class='ag_admin_menu_parent'><br /><a tabindex=\"0\" href=\"javascript:void(0)\" >" + topElement +"</a><div style=\"float:right\"><input title=\"Remove "+topElement+" from menu\" class=\""+jQuery(this).attr("id")+"\" type=\"checkbox\" "+booleanToChecked(isHidden)+" name=\"ag_edit_adminmenu_item_top_"+counter+"\" /></div></td><td class='ag_admin_menu_parent2' ><input title=\"Rename "+topElement+" with this value\" type=\"text\" class=\""+jQuery(this).attr("id")+"\" size=\"47\" value=\""+top_item_text_value+"\" name=\"ag_edit_adminmenu_item_top_"+counter+"\" /></td></tr>");
			}			
		counter++;
		}else if(jQuery(this).attr('id') =="collapse-menu"){
			jQuery(this).remove();
		}
	});
	 
	//console.log(checkboxes.replace('<-TOP->','')+"|"+textboxes.replace('<-TOP->',''));	  
	  prettyEditMenuPage();
}

function showHideSection(text) {	
	switch(text)
	{
		case 'Admin Bar': jQuery('#section_admin_bar').show(); jQuery('#section_admin_bar .section_title').trigger('focus');							
			break;
		case 'Admin Footer': jQuery('#section_admin_footer').show(); jQuery('#section_admin_footer .section_title').trigger('focus');
			break;
		case 'Dashboard Page': jQuery('#section_dashboard_page').show(); jQuery('#section_dashboard_page .section_title').trigger('focus');
			break;
		case 'Login Page': jQuery('#section_login_page').show(); jQuery('#section_login_page .section_title').trigger('focus');
			break;
		case 'Admin Menu': jQuery('#section_admin_menu').show(); jQuery('#section_admin_menu .section_title').trigger('focus');
			break; 
		case 'Colorizer': jQuery('#section_ag_colorizer_settings').show(); jQuery('#section_ag_colorizer_settings .section_title').trigger('focus');
			break;
		default: jQuery('#section_admin_bar').show(); jQuery('#section_admin_bar .section_title').trigger('focus');
		
		
	}
}

function hideAllSections(){
	jQuery('#ag_main_menu li').each(function(){
		jQuery(this).attr("class","normal");
	});
	jQuery('.ag_section').each(function(){
		jQuery(this).hide();
	});
}

function reloadRemoveButtonEvents(){
		jQuery('a.button_remove').click(function(){
			jQuery(this).parent().parent().remove();
		});		
		jQuery('a.button_edit').click(function(){			
			if(editingButtonNow == false){				
				var name = jQuery(this).parent().find('button').text();
				var url = jQuery(this).parent().find('button').attr('title');
				editingButtonNow = name;
				jQuery(this).parent().append('<div id="temporary_button_edit">name:<input type="text" size="47" value="'+name+'" id="ag_add_adminmenu_name_edit" name="ag_add_adminmenu_name_edit" />url:<input type="text" size="47" value="'+url+'" id="ag_add_adminmenu_url_edit" name="ag_add_adminmenu_url_edit" /><button type="button" id="ag_add_adminmenu_button_edit" name="ag_add_adminmenu_button_edit">Save changes</button></div>');
				reloadRemoveButtonEvents();
			}		
		});/*Save editing changes*/
		jQuery('#ag_add_adminmenu_button_edit').click(function(){			
			//alert(jQuery(this).parent().html());			
			var name = jQuery('#ag_add_adminmenu_name_edit').val();
			var url = jQuery('#ag_add_adminmenu_url_edit').val();
			name = name.replace(/["']{1}/gi,"");
			url = url.replace(/["']{1}/gi,"");	
			jQuery('#temporary_button_edit').remove();
			
			var element = 0;
			jQuery('#ag_add_adminmenu :button').each(function(){
				//dont use first button for adding new buttons				
				if(element > 0){						
					if(jQuery(this).html() == editingButtonNow){
						jQuery(this).attr('title',url);
						jQuery(this).html(name);						
					}
				}
				element++;
			});
			editingButtonNow = false;
		});
};	

jQuery(document).ready(function(){	
	/*Add click handler on main buttons*/
	jQuery('#ag_main_menu a, #ag_main_menu li').bind('click',function(){
		hideAllSections();		
		var text = jQuery(this).text();
		jQuery(this).attr("class","selected");		
		showHideSection(text);
	});
	
	/*Admin Menu Reset all setings button*/	
	jQuery('#ag_edit_adminmenu_reset_button').click(function(){	
		afterFormClickCreateJson = false;
		jQuery('#agca_form').submit();
	});	

	/*Add new menu item button - creates new HTMl button elements*/
	jQuery('#ag_add_adminmenu_button').click(function(){	
		var name = jQuery('#ag_add_adminmenu_name').val();
		var url = jQuery('#ag_add_adminmenu_url').val();	
		name = name.replace(/["']{1}/gi,"");
		url = url.replace(/["']{1}/gi,"");		
		jQuery('#ag_add_adminmenu_name').val("");
		jQuery('#ag_add_adminmenu_url').val("");
		jQuery('#ag_add_adminmenu').append('<tr><td colspan="2"><button title="'+url+'" type="button">'+name+'</button>&nbsp;(<a style="cursor:pointer" class="button_edit">edit</a>)&nbsp;(<a style="cursor:pointer" class="button_remove">remove</a>)</td><td></td></tr>');
		reloadRemoveButtonEvents();
	});	
	
	/*Add tooltip box*/
	jQuery("body").append("<div id='AGToolTipDiv'></div>");	
	
	/*ToolTip*/
	  jQuery("label[title],#agca_donate_button").each(function() {  
			jQuery(this).hover(function(e) { 	
			  jQuery(this).mousemove(function(e) {			
				var tipY = e.pageY + 16; 
				var tipX = e.pageX + 16;	
				jQuery("#AGToolTipDiv").css({'top': tipY, 'left': tipX});
			  });
			  jQuery("#AGToolTipDiv")
				.html(jQuery(this).attr('title'))
				.stop(true,true)
				.fadeIn("fast");
			  jQuery(this).removeAttr('title');
			}, function() {
			  jQuery("#AGToolTipDiv")
				.stop(true,true)
				.fadeOut("fast");
			  jQuery(this).attr('title', jQuery("#AGToolTipDiv").html());
			});
	  });
	  
	  /*SECTION FOCUS*/
	  jQuery('.section_title').focus(function(){		
	  });	 
});

/*CLICKING ON ITEMS HANDLING*/
jQuery(document).ready(function(){	
	jQuery('#agca_footer').change(function(){
	});
});

/*Admin menu*/
jQuery(document).ready(function(){	
	jQuery('#adminmenu').css('display','block');
});

/*FORM SUBMITTED*/
jQuery(document).ready(function(){	
	jQuery('#agca_form').submit(function(){
		
		/*Serialize checkboxes*/
		var array = "{";
		var firstElement = true;
		var topMarker = "";
		jQuery('#ag_edit_adminmenu :checkbox').each(function(){		
				if(firstElement != true){
					array += ", ";				
				}
				topMarker = "";
				if(jQuery(this).parent().parent().hasClass('ag_admin_menu_parent')){
					topMarker="<-TOP->"; 
				}
				array += "\"" + topMarker + jQuery(this).attr('class') + "\" : ";
				array += "\"" + jQuery(this).attr('checked') + "\"";
				firstElement = false;			
		});
		array += "}|";
		
		/*Serialize textboxes*/
		array += "{";
		firstElement = true;
		jQuery('#ag_edit_adminmenu :text').each(function(){		
				if(firstElement != true){
					array += ", ";				
				}
				topMarker = "";
				if(jQuery(this).parent().hasClass('ag_admin_menu_parent2')){
					topMarker="<-TOP->";
				}
				array += "\"" + topMarker  + jQuery(this).attr('class') + "\" : ";
				array += "\"" + jQuery(this).val() + "\"";
				firstElement = false;			
		});
		array += "}";
		
		if(afterFormClickCreateJson == true){
			jQuery('#ag_edit_adminmenu_json').val(array);		
		}else{
			jQuery('#ag_edit_adminmenu_json').val('');						
		}
		//console.log(array);
		//serialize buttons
		array = "{";
		var element = 0;
		jQuery('#ag_add_adminmenu :button').each(function(){
			//console.log(jQuery(this).html()+jQuery(this).attr('title'));
				if(element > 0){
					if(element > 1){
						array += ", ";				
					}
					array += "\"" + jQuery(this).html() + "\" : ";
					array += "\"" + jQuery(this).attr('title') + "\"";					
				}
				element++;
		});
		array += "}";	
		if(element == 1){array="";}
		jQuery('#ag_add_adminmenu_json').val(array);
		
		
		/*Serialize colors*/
		var array = "{";
		var firstElement = true;
		var topMarker = "";
		jQuery('input.color_picker').each(function(){	
				if(firstElement != true){
					array += ", ";				
				}			
				array += "\"" + jQuery(this).attr('id') + "\" : ";
				array += "\"" + jQuery(this).val() + "\"";
				firstElement = false;			
		});
		array += "}";
		jQuery('#ag_colorizer_json').val(array);
		
		return true;
	});
});

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

/*A J A X*//*
jQuery(document).ready(function(){	
	jQuery.ajax({          
		url: "http://wordpress.argonius.com/ag-custom-admin/news.php",           
		type: "POST",       
			cache: false,      
			success: function (html) {             
				//if data turned       
				if (html==1) { 
				  // jQuery('#agca_news').html(html);
				alert(html+'3');
				} else{   
					alert('no');
				}
			}      
	});
});*/
/*A J A X*/
jQuery(document).ready(function(){		
		jQuery(document).ready(function(){
			var url="http://wordpress.argonius.com/agca/news.php/news?jsoncallback=?";
			jQuery.getJSON(
			url,{
				wp_ver: wpversion,
				agca_ver: agca_version,
				format: "json"
			  },
			function(json){                                    
				jQuery.each(json.posts,function(i,post){						
						jQuery('#agca_news').append('<p><strong>Info: </strong>'+post.news+'</p>');
				});
				jQuery('#agca_news p').each(function(){						
						jQuery(this).hide();
				});

			});
		});
		setInterval(function() {
				if(jQuery('#agca_news p.news_online').size() == 0){
					jQuery('#agca_news p:first').addClass('news_online');
					jQuery('#agca_news p:first').show();
				}else{
					var changed = false;
					var finish = false;
					jQuery('#agca_news p').each(function(){
						if(finish != true){
							if(changed == true){						
								jQuery(this).addClass('news_online');
								jQuery(this).show();
								finish = true;
							}
							else if(jQuery(this).hasClass('news_online')){
								jQuery(this).hide();
								jQuery(this).removeClass('news_online');
								changed = true;								
							};
						}						
					});
					if(jQuery('#agca_news p.news_online').size() == 0){
						jQuery('#agca_news p:first').addClass('news_online');
						jQuery('#agca_news p:first').show();
					}
				}
        }, 5000);

});
/*A J A X*/