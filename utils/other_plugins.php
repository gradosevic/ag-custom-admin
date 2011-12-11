<?php
/*Check compatibility with other plugins*/
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