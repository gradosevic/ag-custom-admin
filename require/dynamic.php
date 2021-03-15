<?php  
 
// If using the default WordPress file structure
if (file_exists('../../../../wp-load.php')) {
    // Make WordPress functions and methods available
    require '../../../../wp-load.php';

// If using alternative WordPress file structure like Roots' Bedrock
} else {
    // WP's constant ABSPATH is not available, so use $_SERVER['DOCUMENT_ROOT'] to get the web root
    $directory = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT']);

    // Recursively search web root for wp-load.php
    foreach (new RecursiveIteratorIterator($directory) as $file) {
        // wp-load.php found, require and break loop
        if ($file->getFilename() === 'wp-load.php') {
            // Make WordPress functions and methods available
            require $file->getPathname();
            break;
        }
    }
}
 
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
 die;

function agcat_get_wp_version(){
    global $wp_version;
    $array = explode('-', $wp_version);
    $version = $array[0];
    return $version;
}