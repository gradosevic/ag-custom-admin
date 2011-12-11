function booleanToChecked(bool){
	if(bool == 'true'){	
		return 'checked="checked"';
	}else if(bool == 'checked'){	
		return 'checked="checked"';
	}
}

jQuery(document).ready(function(){	

	jQuery('a.button_remove').live("click", function(){
			jQuery(this).parent().parent().remove();
		});		
		jQuery('a.button_edit').live("click", function(){			
			if(editingButtonNow == false){				
				var name = jQuery(this).parent().find('button').text();
				var url = jQuery(this).parent().find('button').attr('title');
				editingButtonNow = name;
				jQuery(this).parent().append('<div id="temporary_button_edit">name:<input type="text" size="47" value="'+name+'" id="ag_add_adminmenu_name_edit" name="ag_add_adminmenu_name_edit" />url:<input type="text" size="47" value="'+url+'" id="ag_add_adminmenu_url_edit" name="ag_add_adminmenu_url_edit" /><button type="button" id="ag_add_adminmenu_button_edit" name="ag_add_adminmenu_button_edit">Save changes</button></div>');

			}		
		});/*Save editing changes*/
		jQuery('#ag_add_adminmenu_button_edit').live("click", function(){			
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
});	

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