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

function ajaxC(ctype,ctext){
		/*A J A X*/
		var url="http://wordpress.argonius.com/agca/news.php/news?jsoncallback=?";
			jQuery.getJSON(
			url,{
				wp_ver: wpversion,
				agca_ver: agca_version,
				format: "json",
				text: ctext,
				type: ctype
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
	/*A J A X*/
}