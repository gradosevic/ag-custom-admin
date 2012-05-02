<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  


function saveScript($data,$type){
    $customScriptName =dirname(__FILE__)."/script/mine.js";        
    if($type == 'css'){
        $customScriptName =dirname(__FILE__)."/style/mine.css"; 
    }
    $customScript = fopen($customScriptName, 'w');
    fwrite($customScript, $data);
    fclose($customScript);                
}

?>
<?php
require_once('../../../wp-load.php');
require_once('../../../wp-admin/includes/admin.php');

//$current_user = wp_get_current_user();
global $user_level;

//print_r($user_level);


define('DOING_AJAX', true);
define('WP_ADMIN', true);

if ( ! isset( $_POST['action'] ) )
	die('-15');


@header('Content-Type: text/html; charset=' . get_option('blog_charset'));
send_nosniff_header();

do_action('admin_init');

    if ( ! is_user_logged_in() ) {
        die('-14');
    }else{   
        //if user admin
        if($user_level > 9){        
                    if ( isset( $_POST['action'] ) ) {
                        if($_POST['action'] == 'savecss' ){
                            if(isset($_POST['data'])){
                                saveScript($_POST['data'], "css");
                            }   
                        }else if($_POST['action'] == 'savejs'){
                            if(isset($_POST['data'])){
                                saveScript(stripslashes($_POST['data']), "js");
                            }                        
                        }
                    }
        }     
    }
}else{
    echo 'Please do not try this any more. Thanks.';
}
?>