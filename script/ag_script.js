var afterFormClickCreateJson = true;
var editingButtonNow = false;
var agca_ajax_url = agca_global_plugin_url + "ajax.php";
function booleanToChecked(bool){
    if(bool == 'true'){
        return 'checked="checked"';
    }else if(bool == 'checked'){
        return 'checked="checked"';
    }
}

function agcaLog(text){
    console.log(text);
}

function agcaDebug(text){
    if(agca_debug){
        console.log('- '+text);
    }
}

function agcaDebugObj(obj){
    if(agca_debug){
        console.log(obj);
    }
}

/*use only on agca page*/
if(window.location.href.indexOf(encodeURIComponent('ag-custom-admin/plugin.php')) !== -1 || window.location.href.indexOf('ag-custom-admin/plugin.php') !== -1){
    jQuery(function(){
        if(!isLocalStorageNameSupported()){
            return false;
        }
        var agcapage = localStorage.getItem('agca-page');
        if(!agcapage){
            var hash = window.location.hash || 'general-settings';
            localStorage.setItem('agca-page', hash);
            agcapage = localStorage.getItem('agca-page');
        }
        window.location.hash = window.location.hash || agcapage;
        localStorage.setItem('agca-page', window.location.hash);
    });
    window.onhashchange = function(){
        if(!isLocalStorageNameSupported()){
            return false;
        }
        localStorage.setItem('agca-page', window.location.hash);
        if(jQuery('#ag_main_menu a.selected').attr('href') !== window.location.hash){
            if(window.location.hash !== ""){
              //  jQuery("#ag_main_menu a[href='"+window.location.hash+"']").trigger('click');
            }
        }
    };
}

function isLocalStorageNameSupported()
{
    var testKey = 'test-storage', storage = window.localStorage;
    try
    {
        storage.setItem(testKey, '1');
        storage.removeItem(testKey);
        return true;
    }
    catch (error)
    {
        return false;
    }
}

function hideShowSubmenus(index){
    var finish = false;
    var	found = false;
    jQuery('#ag_edit_adminmenu td').each(function(){
        if(jQuery('#ag_edit_adminmenu td').index(jQuery(this)) >= index && (finish == false)){
            //  jQuery(this).find('.agcaMenuEditorPlusMinus').show();
            var caller = jQuery('#ag_edit_adminmenu td:eq(' + index + ')');
            jQuery(caller).find('.agcaMenuEditorPlusMinus .minus').toggle();
            jQuery(caller).find('.agcaMenuEditorPlusMinus .plus').toggle();
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
function editMenuRow(url, originalName, newName, removed){
    return '<tr class="top" data-url="'+url+'" style="background-color: rgb(216, 234, 216);"><td class="ag_admin_menu_parent"><br><span class=\"agcaMenuEditorPlusMinus\"><span class="dashicons plus dashicons-arrow-down-alt2"></span><span class="dashicons minus dashicons-arrow-up-alt2"></span></span><a tabindex="0" href="javascript:void(0)">'+originalName+'</a><div style="float:right"><input title="Remove Home from menu" class=" agca-checkbox visibility" type="checkbox" '+(removed?' checked=\"checked\" ':'')+' name="ag_edit_adminmenu_item_top_2" style="display: none;"><div onclick="agcaCheckBoxOnClick(jQuery(this));" class="agca-checkbox-box" title="'+agca_string.remove+' '+originalName+' '+agca_string.frommenu+'"><div></div></div></div></td><td class="ag_admin_menu_parent2"><input title="'+agca_string.rename+' '+originalName+' '+agca_string.withthisvalue+'" type="text" class="undefined" size="47" value="'+newName+'" name="ag_edit_adminmenu_item_top_2"></td></tr>';
}
function editMenuSubrow(index, parentName, parentIndex, url, originalName, newName, removed){
    return '<tr class="sub" data-index="'+index+'" data-parent="'+parentIndex+'" data-name="'+originalName+'" data-url="'+url+'"><td class="ag_admin_menu_child"><div style="float:left"><a tabindex="-1" href="javascript:void(0)" style="font-weight:bold;" title="Dashboard submenu: '+originalName+'"><span style="font-weight:normal"> </span>'+originalName+'</a></div><div style="float:right"><input type="checkbox" title="'+agca_string.remove+' '+parentName+' submenu: '+originalName+' from menu" class="Home agca-checkbox visibility" '+(removed?' checked=\"checked\" ':'')+' name="ag_edit_adminmenu_item_sub_1" style="display: none;"><div onclick="agcaCheckBoxOnClick(jQuery(this));" class="agca-checkbox-box" title="'+agca_string.remove+' '+parentName+' sub-menu: '+originalName+' from menu"><div></div></div></div></td><td class="ag_admin_menu_child2"><input type="text" title="'+agca_string.rename+' '+agca_string.submenuitem+' '+originalName+' '+agca_string.withthisvalue+'" class="'+originalName+'" size="47" value="'+newName+'" name="ag_edit_adminmenu_item_sub_1"></td></tr>';
}
function createEditMenuPageNew(agca_menu){
    for(var url in agca_menu){
        jQuery('#ag_edit_adminmenu').append(editMenuRow(url, agca_menu[url]['name'], agca_menu[url]['new'], agca_menu[url]['remove']));
        //console.log(agca_menu[url]['name'],agca_menu[url]['new']);

        //submenus
        var subs = agca_menu[url]['submenus'];
        if(subs){
            for(var ind in subs){
                //console.log('--- '+ subs[ind]['name'], subs[ind]['new']);
                jQuery('#ag_edit_adminmenu').append(editMenuSubrow(ind, agca_menu[url]['name'],url, subs[ind]['url'], subs[ind]['name'], subs[ind]['new'], subs[ind]['remove']));
            }
        }
    }

    prettyEditMenuPage();
    hideShowSubmenus();

    var parent = null;
    var subs = 0;
    jQuery('#ag_edit_adminmenu tr').each(function(){
        if(jQuery(this).find('td').hasClass('ag_admin_menu_parent')){
            //jQuery(this).css('color','#ffffff');
            if(parent != null){
                if(subs == 0){
                    jQuery(parent).find('.agcaMenuEditorPlusMinus').html('<span>&nbsp;&nbsp;&nbsp;</span>');
                }
            }
            subs = 0;
            parent = jQuery(this);
        }else{
            subs++;
        }

    });
    agcaChangeCheckBoxStyles();
}

function applyAdminMenuCustomizations(){
    jQuery('ul#adminmenu > li').each(function() {
        var $o = jQuery(this);
        if (!$o.hasClass("wp-menu-separator") && !$o.hasClass("wp-menu-separator-last") && $o.attr('id') !== 'collapse-menu') {
            var topUrl = $o.find('a:first').attr('href');
            var t = agca_admin_menu[topUrl];
            if(t){
                if(t.remove){
                    $o.remove();
                } else if(c.new){
                    $o.find('a:first .wp-menu-name').text(c.new);
                }
                var s = t.submenu;
                if(s){
                    for(var ind in s){
                        var cs = s[ind];
                        var subUrl = cs.url;
                        var $s = $o.find('.wp-submenu a[href="'+subUrl+'"]:first');
                        if(cs.remove){
                            $s.remove();
                        } else if(cs.new){
                            $s.text(cs.new);
                        }
                    }
                }

            }
            console.log(topUrl);
        }
    });
}

function showHideSection(text) {
    switch(text)
    {
        case agca_string.menu_general:
            jQuery('#section_general').show();
            jQuery('#section_general .section_title').trigger('focus');
            break;
        case agca_string.menu_admin_bar:
            jQuery('#section_admin_bar').show();
            jQuery('#section_admin_bar .section_title').trigger('focus');
            break;
        case agca_string.menu_footer:
            jQuery('#section_admin_footer').show();
            jQuery('#section_admin_footer .section_title').trigger('focus');
            break;
        case agca_string.menu_dashb:
            jQuery('#section_dashboard_page').show();
            jQuery('#section_dashboard_page .section_title').trigger('focus');
            break;
        case agca_string.menu_login:
            jQuery('#section_login_page').show();
            jQuery('#section_login_page .section_title').trigger('focus');
            break;
        case agca_string.menu_admin_menu:
            jQuery('#section_admin_menu').show();
            jQuery('#section_admin_menu .section_title').trigger('focus');
            break;
        case agca_string.menu_colorizer:
            jQuery('#section_ag_colorizer_settings').show();
            jQuery('#section_ag_colorizer_settings .section_title').trigger('focus');
            break;
        case agca_string.menu_themes:
            if(!jQuery('#section_templates').hasClass("loaded")){
                jQuery('#section_templates').addClass('loaded');
                agca_client_init();
            }
            jQuery('#section_templates').show();
            jQuery('#section_templates .section_title').trigger('focus');
            break;
        case agca_string.menu_advanced:
            jQuery('#section_advanced').show();
            jQuery('#section_advanced .section_title').trigger('focus');
            break;
        default:
            jQuery('#section_admin_bar').show();
            jQuery('#section_admin_bar .section_title').trigger('focus');
    }
}

function hideAllSections(){
    jQuery('#ag_main_menu li').each(function(){
        jQuery(this).addClass("normal").removeClass("selected");
    });
    jQuery('.ag_section').each(function(){
        jQuery(this).hide();
    });
}
function reloadRemoveButtonEvents(){
}
function createTargetCombo(target,clas){

    var combo = ""

    combo+= "&nbsp;"+agca_string.open+"&nbsp;"+agca_string.in+":<select";
    if( clas != null){
        combo+=" class=\"editTarget\" ";
    }
    combo+= ">";
    combo+= "<option value=\"_self\"";
    if(target === "_self"){
        combo+= " selected ";
    }
    combo+= ">same tab</option>";
    combo+= "<option value=\"_blank\"";
    if(target === "_blank"){
        combo+= " selected ";
    }
    combo+= ">new tab</option>";
    combo+= "</select>";
    return combo;
}

function exportSettings(){
    jQuery('#agca_form').attr('action','tools.php?page=ag-custom-admin/plugin.php');
    jQuery('#agca_form #_agca_import_settings').val("false");
    jQuery('#agca_form #_agca_export_settings').val("true");


    jQuery('#ag_add_adminmenu_json').val('');
    jQuery('#ag_edit_adminmenu_json').val('');
    jQuery('#ag_edit_adminmenu_json_new').val('');


    jQuery('#agca_form').submit();
}

function importSettings(){
    if(jQuery('#settings_import_file').css("display") == "none"){
        jQuery('#settings_import_file').show("slide");
    }else{
        jQuery('#agca_form').attr('action','tools.php?page=ag-custom-admin/plugin.php');
        if(jQuery('#settings_import_file').val() !=""){
            jQuery('#agca_form #_agca_import_settings').val("true");
            jQuery('#agca_form #_agca_export_settings').val("false");
            jQuery('#agca_form').attr('enctype','multipart/form-data');
            jQuery('#agca_form').submit();
        }else{
            alert(agca_string.file_imp_not_sel);
        }
    }

}

function savePluginSettings(){
    jQuery('#import_file_area').remove();
    jQuery('#agca_form').submit();
}

jQuery(document).ready(function(){
    jQuery('#ag_add_adminmenu').on("click", 'a.button_remove', function(){
        jQuery(this).parent().parent().remove();
    });
    jQuery('#ag_add_adminmenu').on('click', 'a.button_edit', function(){
        if(editingButtonNow == false){
            var name = jQuery(this).parent().find('button').text();
            var url = jQuery(this).parent().find('button').attr('title');
            var target = jQuery(this).parent().find('button').attr('target');
            //console.log(target);
            editingButtonNow = name;
            jQuery(this).parent().append('<div id="temporary_button_edit">name:<input type="text" size="47" value="'+name+'" id="ag_add_adminmenu_name_edit" name="ag_add_adminmenu_name_edit" />url:<input type="text" size="47" value="'+url+'" id="ag_add_adminmenu_url_edit" name="ag_add_adminmenu_url_edit" />' + createTargetCombo(target,"edit")+ '<input type="button" id="ag_add_adminmenu_button_edit" name="ag_add_adminmenu_button_edit" value="'+agca_string.save_changes+'" /></div>');
            reloadRemoveButtonEvents();
        }
    });/*Save editing changes*/
    jQuery('#ag_add_adminmenu').on("click", '#ag_add_adminmenu_button_edit', function(){
        //alert(jQuery(this).parent().html());			
        var name = jQuery('#ag_add_adminmenu_name_edit').val();
        var url = jQuery('#ag_add_adminmenu_url_edit').val();
        var target = jQuery('select.editTarget').val();
        //var target = jQuery(this).parent().find('button').attr('target');
        name = name.replace(/["']{1}/gi,"");
        url = url.replace(/["']{1}/gi,"");
        jQuery('#temporary_button_edit').remove();

        var element = 0;
        jQuery('#ag_add_adminmenu :button').each(function(){
            //dont use first button for adding new buttons				
            if(element > 0){
                if(jQuery(this).html() == editingButtonNow){
                    jQuery(this).attr('title',url);
                    jQuery(this).attr('target',target);
                    jQuery(this).html(name);
                }
            }
            element++;
        });
        editingButtonNow = false;
    });

    setTimeout(function(){
        jQuery('#agca_advertising').show(),700
    });
});
/*ToolTip*/
function agcaApplyTooltip(){
    if(jQuery(this).attr('title') != ""){
        jQuery(this).hover(function(e) {
            if(jQuery(this).hasClass('feedback')){
                jQuery(this).mousemove(function(e) {
                    var tipY = e.pageY + 16;
                    var tipX = e.pageX - 236;
                    var type = '#f08080';
                    if(jQuery(this).hasClass('positive')) {
                        type = '#ACC491';
                    }
                    jQuery("#AGToolTipDiv").css({
                        'top': tipY,
                        'left': tipX,
                        'background': type,
                        'border': 'none',
                        'padding': '10px',
                        'color':'#fff',
                        'font-weight':'bold',
                        'border-radius': '5px'
                    });
                });
            }else{
                jQuery(this).mousemove(function(e) {
                    var tipY = e.pageY + 16;
                    var tipX = e.pageX + 16;
                    jQuery("#AGToolTipDiv").css({
                        'top': tipY,
                        'left': tipX,
                        'background': '#FFFFD4',
                        'border': '1px solid #FFFF00'
                    });
                });
            }
            jQuery("#AGToolTipDiv")
                .html(jQuery(this).attr('title'))
                .stop(true,true)
                .fadeIn("fast");
            jQuery(this).removeAttr('title');
        }, function() {
            jQuery("#AGToolTipDiv")
                .stop(true,true)
                .fadeOut("fast", function(){
                    jQuery(this).css({
                        'color': '',
                        'border': '',
                        'font-weight':''
                    });
                })

            jQuery(this).attr('title', jQuery("#AGToolTipDiv").html());
        });
    }
}

jQuery(document).ready(function(){
    /*Add click handler on main buttons*/
    jQuery('#ag_main_menu a, #ag_main_menu li').bind('click',function(e){
        if(jQuery(e.target).is('li')){
            var hash = jQuery(this).find('a:first').attr('href');
            document.location.hash = hash;
        };
        hideAllSections();
        var text = jQuery(this).text();
        jQuery(this).removeClass('normal').addClass("selected");
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
        var target = jQuery('#ag_add_adminmenu_target').val();
        name = name.replace(/["']{1}/gi,"");
        url = url.replace(/["']{1}/gi,"");
        jQuery('#ag_add_adminmenu_name').val("");
        jQuery('#ag_add_adminmenu_url').val("");
        jQuery('#ag_add_adminmenu_target').val("_self");
        jQuery('#ag_add_adminmenu').append('<tr><td colspan="2"><button target="'+target+'" title="'+url+'" type="button">'+name+'</button>&nbsp;<a style="cursor:pointer;" title="Edit" class="button_edit"><span class="dashicons dashicons-edit"></span></a>&nbsp;<a style="cursor:pointer" title="'+agca_string.delete+'" class="button_remove"><span class="dashicons dashicons-no"></span></a></td><td></td></tr>');
        reloadRemoveButtonEvents();
    });

    /*Add tooltip box*/
    jQuery("body").append("<div id='AGToolTipDiv'></div>");

    /*ToolTip*/
    jQuery("label[title],#agca_donate_button, a.feedback").each(agcaApplyTooltip);

    /*SECTION FOCUS*/
    jQuery('.section_title').focus(function(){
    });


    //check hashtag
    if(document.location.hash !== ""){
        jQuery("#ag_main_menu a[href='"+window.location.hash+"']").trigger('click')
    };

    if(typeof isCusminActive !== 'undefined' && isCusminActive){
        hideAllSections();
        jQuery('#section_templates, #section-cusmin').show();
        if(typeof agca_client_init === 'function'){
            agca_client_init();
        }
    }

});

jQuery(function($){
    /*Admin menu*/
    jQuery('#adminmenu').css('display','block');

    /*FORM SUBMITTED*/
    jQuery('#agca_form').submit(function(){
        processData();
        return true;
    });

    //on radio button text click
    jQuery('.agca-radio-text').on('click', function(){
        var $o = jQuery(this);
        var type = 'off';
        if($o.hasClass('on')){
            type='on';
        }
        $o.parent().find('.agca-radiobox[value='+type+']').trigger('click');
    });

    //Colorizer update input text colors
    colorizerColorizeTextboxes();
});


function processEditAdminMenu(){
    var array = {};

    //top menus
    jQuery('#ag_edit_adminmenu tr.top').each(function(){
        var $tr = jQuery(this);
        var originalText = $tr.find('.ag_admin_menu_parent a').text();
        var newText = $tr.find('.ag_admin_menu_parent2 input').val();
        var remove = $tr.find('.agca-checkbox').is(':checked');
        var url = $tr.data('url');
        array[url]={
            'name': originalText,
            'new': newText || "",
            'remove':remove,
            'url':url,
            'submenus':{}
        };
    });

    //submenus
    jQuery('#ag_edit_adminmenu tr.sub').each(function(){
        var $tr = jQuery(this);
        var newText = $tr.find('.ag_admin_menu_child2 input').val();
        var remove = $tr.find('.agca-checkbox').is(':checked');
        var url = $tr.data('url');
        var index = $tr.data('index');
        var parentIndex = $tr.data('parent');
        var originalName = $tr.data('name');
        array[parentIndex]['submenus'][index]={
            'name': originalName,
            'new': newText || "",
            'remove':remove,
            'url':url
        };
    });
    return array;
}

function processData(){

    //serialize admin menu edit configuration
    var array = processEditAdminMenu();
    if(afterFormClickCreateJson == true){
        jQuery('#ag_edit_adminmenu_json_new').val(JSON.stringify(array));
    }else{
        jQuery('#ag_edit_adminmenu_json_new').val('');
    }

    //serialize buttons
    array = "{";
    var element = 0;
    jQuery('#ag_add_adminmenu :button').each(function(){
        //console.log(jQuery(this).html()+jQuery(this).attr('title'));
        if(element > 0){
            if(element > 1){
                array += ", ";
            }
            array += "\"" + jQuery(this).html() + "\" : {";
            array += " \"value\" : ";
            array += "\"" + jQuery(this).attr('title') + "\"";
            array += ", \"target\" : ";
            array += "\"" + jQuery(this).attr('target') + "\"}";
        }
        element++;
    });
    array += "}";
    if(element == 1){
        array="";
    }
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

    if(!isSettingsImport){
        jQuery('#ag_colorizer_json').val(array);
    }
}

function agca_escapeHTMLChars(str){
    return str
        .replace(/&/g, '&amp;')
        .replace(/'/g, '&#39;')
        .replace(/"/g, '&quot;');
}

function agca_escapeChars(str){
    return str.replace(/[\/\\\"\']/g, "\\$&");
    //return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

/*C O L O R I Z E R*/
function updateTargetColor(id, color){
    switch(id)
    {
        case 'color_background':
            jQuery('html, .wp-dialog, #wpwrap').css({
                'background-color':color
            });
            break;
        case 'login_color_background':
            jQuery('body.login').css({
                'background-color':color
            });
            break;

        case 'color_header':
            jQuery('#wphead').css({
                'background':color
            });

            //wp > 3.3
            jQuery('#wpadminbar').css({'background':color});
            jQuery('#wpadminbar .ab-top-menu').css({'background':color});
            jQuery('#wphead').css({
                'margin':'0',
                'margin-left':'-14px',
                'padding-left':'15px'
            });
            jQuery("#backtoblog").attr("style","");
            break;
        case 'color_admin_menu_top_button_background':
            jQuery('#adminmenu a.menu-top').css({
                'background':color
            });
            jQuery('#adminmenu li.menu-top').css({
                'background':color
            });
            break;
        case 'color_admin_menu_top_button_current_background':
            jQuery('#adminmenu li.menu-top.wp-menu-open a.menu-top').css({
                'background':color

            });
            jQuery('#adminmenu li.menu-top.current a.menu-top').css({
                'background':color
            });
            jQuery('body.folded #adminmenu li.menu-top.wp-menu-open').css({
                'background':color
            });
            break;
        case 'color_admin_menu_top_button_hover_background':
            if(color == "")break;
            var selector = '#adminmenu a.menu-top';
            if(jQuery('body').hasClass('folded')){
                selector = '#adminmenu li.menu-top';
            }
            var originalBackground = jQuery(selector).css('background-color');

            //if first is selected, use second
            if((jQuery(selector+":eq(0)").hasClass('current') || jQuery(selector+":eq(0)").hasClass('wp-has-current-submenu'))){
                originalBackground = jQuery(selector+':eq(1)').css('background-color');
            }
            jQuery(selector).mouseover(function(){
                if(!(jQuery(this).hasClass('current') || jQuery(this).hasClass('wp-has-current-submenu'))){
                    jQuery(this).css({'background':color});
                }
            }).mouseout(function(){
                if(!(jQuery(this).hasClass('current') || jQuery(this).hasClass('wp-has-current-submenu'))){
                    jQuery(this).css('background',originalBackground);
                }
            });

            //for folded menu


            break;
        case 'color_admin_menu_submenu_background':
            // jQuery("#adminmenu li.wp-has-current-submenu").removeClass("wp-has-current-submenu");
            jQuery('#adminmenu .wp-submenu.sub-open').remove();
            jQuery('#adminmenu .wp-submenu a, #adminmenu li.wp-has-current-submenu .wp-submenu a').each(function(){
                jQuery(this).css({
                    'background':color
                });
            });
            jQuery('#adminmenu .wp-submenu').css({'background':color,'margin-left':'0',"padding-top":roundedSidberSize+"px","padding-bottom":roundedSidberSize+"px"});
            jQuery('#adminmenu .wp-has-current-submenu .wp-submenu').css("padding","0");
            //jQuery('#adminmenu .wp-submenu').css('border','3px solid red');
            jQuery('#adminmenu .wp-submenu ul').css({'background':'none'});
            jQuery('#adminmenu .wp-submenu ul').css('border','none');
            jQuery('#adminmenu .wp-submenu .wp-submenu-wrap').css('border','none');

            break;
        case 'color_admin_submenu_font':
            jQuery('#adminmenu .wp-submenu li a').css('color',color);
            break;
        case 'color_admin_menu_font':
            jQuery('#adminmenu, #adminmenu a, #adminmenu p').css({
                'color':color
            });
            break;
        case 'color_admin_menu_behind_background':
            jQuery('#adminmenuback, #adminmenuwrap').css({
                'background-color':color
            });
            break;
        case 'color_admin_menu_submenu_background_hover':
            var submenuSelector = '#adminmenu .wp-submenu a';
            var originalSubBackground = jQuery(submenuSelector).css('background-color');
            jQuery(submenuSelector).mouseover(function(){
                jQuery(this).css({
                    'background':color
                });
            }).mouseout(function(){
                jQuery(this).css('background',originalSubBackground);
            });
            //jQuery('#adminmenu .wp-submenu a:hover').css({'background':color});
            break;
        case 'color_font_content':
            jQuery('#wpbody-content, #wpbody-content label, #wpbody-content p,#wpbody-content .form-table th, #wpbody-content .form-wrap label').css({
                'color':color
            });
            break;
        case 'color_font_header':
            jQuery('#wphead, #wphead a, #wpadminbar a, #wpadminbar span').css({
                'color':color
            });
            break;
        case 'color_font_footer':
            jQuery('#wpfooter, #wpfooter a').css({
                'color':color
            });
            break;
        case 'color_widget_bar':
            jQuery(".widget .widget-top, .postbox h3, .stuffbox h3").css({
                'background' : color,
                'text-shadow' :'none'
            });
            break;
        case 'color_widget_background':
            jQuery(".widget, .postbox").css('background',color);
            //jQuery(".widget, #widget-list .widget-top, .postbox, .menu-item-settings").css('background',color); remove if <3.2 work
            break;
        default:
    }
}
function updateColor(id,color){
    jQuery("#"+id).css({
        'background-color':color
    });
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

/*A J A X*/
jQuery(document).ready(function(){

    //Ams
    if(typeof isAGCAPage !== 'undefined'){
        if(isAGCAPage == true){
            //alert('admin page');
            var url="http://wordpressadminpanel.com/ads/ep/ads/ads?jsoncallback=?";
            jQuery.getJSON(
                url,{
                    wp_ver: wpversion,
                    agca_ver: agca_version,
                    format: "json"
                },
                function(json){
                    jQuery.each(json,function(i,post){
                        jQuery('#agca_advertising ul').append('<li><a target="_blank" href="http://wordpressadminpanel.com/ads/ep/ads/ad?id=' + post.id + '" ><img height=\"100px\" src=\"'+post.src+'\"  title=\"'+post.title+'\" /></a></li>');
                    });
                    jQuery('#agca_advertising').show();

                });
        }
    }


    //News
    jQuery(document).ready(function(){
        if(typeof isAGCAPage !== 'undefined'){
            if(isAGCAPage == true){
                var url="http://wordpressadminpanel.com/info/info?jsoncallback=?";
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
            }
        }

    });


});
/*A J A X*/

/*AGCA CHECKBOX RADIOBOX*/
function agcaCheckBoxOnClick(obj){
    var isNowChecked = false;
    var $input = jQuery(obj).prev();
    if($input.is(':checked')){
        $input.removeAttr('checked');
    }else{
        isNowChecked = true;
        $input.attr('checked','checked');
    }

    if($input.hasClass('has-dependant')){
        var dependantSel = $input.data('dependant');
        var isOppositeToggle = $input.hasClass('dependant-opposite');

        if(isNowChecked && !isOppositeToggle || !isNowChecked && isOppositeToggle){
            jQuery(dependantSel).show("slide");
        }else{
            jQuery(dependantSel).hide("slideDown");
        }
    }

    //console.log(obj.prev());
    if($input.is(':checked')){
        jQuery(obj).addClass('agca-checkbox-box-checked');
        jQuery(obj).removeClass('agca-checkbox-box');
    }else{
        jQuery(obj).addClass('agca-checkbox-box');
        jQuery(obj).removeClass('agca-checkbox-box-checked');
    }
}
function agcaRadioBoxOnClick(obj){
    obj.prev().trigger('click');
    //console.log(obj.prev());
    obj.parent().find('.agca-radiobox').removeClass('checked');
    if(jQuery(obj).prev().is(':checked')){
        jQuery(obj).addClass('checked');
    }else{
        jQuery(obj).removeClass('checked');
    }
}
function agcaChangeCheckBoxStyles(){
    /*checkbox*/
    jQuery('.agca-checkbox-box-checked').remove();
    jQuery('.agca-checkbox-box').remove();
    jQuery('.agca-checkbox').each(function(){
        jQuery(this).hide();
        var cls = "agca-checkbox-box";
        if(jQuery(this).is(':checked')){
            cls = "agca-checkbox-box-checked";
        }
        if(jQuery(this).hasClass('visibility')){
            cls +=' visibility';
        }
        jQuery(this).after('<div onClick="agcaCheckBoxOnClick(jQuery(this));" class="' + cls + '" title="'+jQuery(this).attr('title')+'"><div/>');
    });

    /*radio*/
    jQuery('.agca-radiobox').remove();
    jQuery('.agca-radio').each(function(){
        jQuery(this).hide();
        var cls = "agca-radiobox";
        if(jQuery(this).is(':checked')){
            cls = "agca-radiobox checked";
        }

        jQuery(this).after('<div onClick="agcaRadioBoxOnClick(jQuery(this));" value="'+jQuery(this).attr('value')+'" class="' + cls + '" title="'+jQuery(this).attr('title')+'"><div/>');
    });
}

function isWPHigherOrEqualThan(targetVersion){
    //remove sufixes, beta RC etc
    if (wpversion.indexOf("-")!=-1){
        var parts = wpversion.split("-");
        wpversion = parts[0];
    }
    var currentVersion = parseFloat(wpversion);
    targetVersion = parseFloat(targetVersion);

    return (wpversion >= targetVersion);
}

function colorizerColorizeTextboxes(){
    jQuery('#section_ag_colorizer_settings .color_picker').each(function(){
        var $o = jQuery(this);
        $o.css('background-color', $o.val());
        $o.lightOrDark();
        if($o.hasClass('dark')){
            $o.css('color','#fff');
        }else{
            $o.css('color','#000');
        }
    });
}


//VALIDATION
jQuery(document).ready(function() {
    jQuery(".validateNumber").on('keydown',function(event) {
        // Allow only backspace and delete
        var limit = jQuery(this).attr('limit');
        var value = jQuery(this).val();

        if ( event.keyCode == 46 || event.keyCode == 8 ) {
        } else {
            if((typeof(limit) != undefined) && value.length >= limit){
                return false;
            }
            if (event.keyCode < 95) {
                if (event.keyCode < 48 || event.keyCode > 57 ) {
                    event.preventDefault();
                }
            } else {
                if (event.keyCode < 96 || event.keyCode > 105 ) {
                    event.preventDefault();
                }
            }
        }
    });
});

(function($){

    $.fn.lightOrDark = function(){
        var r,b,g,hsp
            , a = this.css('background-color');

        if (a.match(/^rgb/)) {
            a = a.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
            r = a[1];
            g = a[2];
            b = a[3];
        } else {
            a = +("0x" + a.slice(1).replace( // thanks to jed : http://gist.github.com/983661
                    a.length < 5 && /./g, '$&$&'
                )
            );
            r = a >> 16;
            b = a >> 8 & 255;
            g = a & 255;
        }
        hsp = Math.sqrt( // HSP equation from http://alienryderflex.com/hsp.html
            0.299 * (r * r) +
            0.587 * (g * g) +
            0.114 * (b * b)
        );
        if (hsp>127.5) {
            this.addClass('light');
        } else {
            this.addClass('dark');
        }
    }

})(jQuery);