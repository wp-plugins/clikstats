<?php
if (empty($_GET['link'])) {
	echo 'You have reached this page in error';
	exit(0);
	}
	
// query the database	
include ('../../../wp-config.php');	




$table_name = $wpdb->prefix."clik_stats";



	
$SQL = "INSERT INTO ".$table_name." (";
$SQL.= "url, ip";
$SQL.= ") VALUES (";
$SQL.= "'".addslashes($_GET['link'])."',";
$SQL.= "'".$_SERVER['REMOTE_ADDR']."'";
$SQL.= ")";

//echo $SQL;

// Perform update
$wpdb->query($SQL);	
	
// do the link redirect
$location = $_GET['link'];
header ('Location: '.$location);	
	
	
?>	