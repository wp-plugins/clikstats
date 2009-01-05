<?php
if (empty($_GET['link'])) {
	echo 'You have reached this page in error';
	exit(0);
	}
	
// get the database	
include ('../../../wp-config.php');	



$table_name = $wpdb->prefix."clik_stats";



// generate the link info
$url = $_GET['link'];

// add any additional arguments
foreach ($_GET as $key=>$val) $url.= $key!=='link' ? '&'.$key.'='.$val : '' ;



	
$SQL = "INSERT INTO ".$table_name." (";
$SQL.= "url, ip";
$SQL.= ") VALUES (";
$SQL.= "'".addslashes($url)."',";
$SQL.= "'".$_SERVER['REMOTE_ADDR']."'";
$SQL.= ")";

//echo $SQL;


// Perform update
$wpdb->query($SQL);	
	
	

header ('Location: '.$url);	
	
	
?>	