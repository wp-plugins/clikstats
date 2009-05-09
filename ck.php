<?php
// get the database	
include ('../../../wp-config.php');	
include ('ck_functions.php');


	


//////////////// variables ////////////////////////////////////////////

$reserved = array('Ck_id','Ck_lnk'); // querys that are used
$table_name = $wpdb->prefix."clik_stats"; // tablename
$post_id = intval($_GET['Ck_id']); // supposed source of the link


///////////////////////////////////////////////////////////////////////







/////////////// Some info about whats happening ///////////////////////

// get the supposed origin content, that SHOULD contain the link
$SQL = 'SELECT post_content FROM `'.$wpdb->prefix.'posts` WHERE id='.$post_id;
$post = $wpdb->get_results($SQL, ARRAY_A); 

// reassemble the url (reserved querys must be removed from the url assembly)
$urlA = $_GET['Ck_lnk']; 
foreach ($_GET as $key=>$val) $urlA.= !in_array($key, $reserved, true)  ? '&'.$key.'='.$val : '';

// same as above but reassemble it with wordpress escaped ampersands, just stops clikstats messing anything up
$urlB = $_GET['Ck_lnk']; 
foreach ($_GET as $key=>$val) $urlB.= !in_array($key, $reserved, true)  ? '&amp;'.$key.'='.$val : '';

//////////////////////////////////////////////////////////////////////




/////// security checks begin ////////////////////////////////////////


foreach ($reserved as $required) if (empty($_GET[$required])) notFoundClik(); // bad args
if (empty($post)) notFoundClik(); // bad post source
if (strpos($post[0]['post_content'], 'href="'.$urlA.'"')==false && strpos($post[0]['post_content'], 'href="'.$urlB.'"')==false && !get_permalink($post_id)=='href="'.$urlA.'"' && !get_permalink($post_id)=='href="'.$urlB.'"') notFoundClik(); // bad url


/////////////////////////////////////////////////////////////////////

	


///////// record the click //////////////////////////////////////////
	
$SQL = "INSERT INTO ".$table_name." (";
$SQL.= "url, ip, post_id";
$SQL.= ") VALUES (";
$SQL.= "'".addslashes($urlA)."',";
$SQL.= "'".$_SERVER['REMOTE_ADDR']."',";
$SQL.= "'".$post_id."'";
$SQL.= ")";

$wpdb->query($SQL);	

////////////////////////////////////////////////////////////////////

	
	
	
	
// send them to where they originally requested
header ('Location: '.$urlA);	
	
	
?>	