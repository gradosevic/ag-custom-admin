var afterFormClickCreateJson = true;
var editingButtonNow = false;

function ajaxC(ctype,ctext){
		/*A J A X*/
		var url="http://wordpress.argonius.com/agca/news.php/news?jsoncallback=?";
			jQuery.getJSON(
			url,{
				wp_ver: wpversion,
				agca_ver: agca_version,				
				type: ctype,
				text: ctext				
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