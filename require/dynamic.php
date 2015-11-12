<?php  
 require '../../../../wp-load.php';  
 
 $themes = get_option('agca_templates');	
 $selectedTheme = get_option('agca_selected_template');
 $type = "";
 $optionName = "";
 $agcaContext = "";
 
 if(isset($_GET["type"])){
	$type = $_GET["type"];
 }
 if(isset($_GET["context"])){
	$agcaContext = $_GET["context"];
 }
 
if ( $agcaContext != "login" && !is_user_logged_in()) {
	die();
} 
 
 if($type == "css"){
	header('Content-type: text/css');
	$optionName = ($agcaContext == "login")? "logincss":"admincss";
	
 }else if($type == "js"){
	header('Content-type: application/javascript');	
	$optionName = ($agcaContext == "login")? "loginjs":"adminjs";
 }
 if($type == "css" || $type == "js"){ 	  
	  if(isset($themes[$selectedTheme])){
		$theme = $themes[$selectedTheme];
		$admin_capability = get_option('agca_admin_capability');		
		if($admin_capability == ""){
			$admin_capability = "edit_dashboard";
		}
		if(!((get_option('agca_role_allbutadmin')==true) and (current_user_can($admin_capability )))){	
			$adminscript = $theme[$optionName];
			$settings = $theme['settings'];
			if($settings == "" || $settings == " ") $settings = "{}";		
			$adminscript = agcaAppendSettingsToAGCATemplateCustomizations($adminscript, $settings);				
			$admindata = agcaEnableSpecificWPVersionCustomizations($admindata);
			$admindata = agcaRemoveCSSComments($admindata);	
			
			echo $adminscript;
		}	
	 } 
 }

 //print_r($themes); print_r($selectedTheme); die;
 die;

	
	function agcaEnableSpecificWPVersionCustomizations($customizations){	
		/*enable special CSS for this WP version*/	
		$ver = agcat_get_wp_version();		
		$customizations = str_replace("/*".$ver," ", $customizations);
		$customizations = str_replace($ver."*/"," ", $customizations);
		return $customizations;
	}
	function agcat_get_wp_version(){
		global $wp_version;
		$array = explode('-', $wp_version);		
		$version = $array[0];		
		return $version;
	}
	function agcaAppendSettingsToAGCATemplateCustomizations($customizations, $settings){
		$template_settings = json_decode($settings);
	    //print_r($template_settings);
		foreach($template_settings as $sett){
			$key = $sett->code;
							
			//use default value if user's value is not set
			$value="";
			if($sett->value != ""){
				$value = $sett->value;						
			}else{
				$value = $sett->default_value;						
			}
			
			//Prepare settings					
			if($sett->type == 6){
				if($value !== null && (strtolower($value) == "on" || $value == "1")){
					$value = "true";
				}else{
					$value = "false";
				}						
			}								
			$customizations = str_replace("%".$key."%",$value, $customizations);						
		}	
		return $customizations;
	}
	function agcaRemoveCSSComments($customizations){				
		$customizations = preg_replace('#/\*.*?\*/#si','',$customizations);
		return $customizations;
	}