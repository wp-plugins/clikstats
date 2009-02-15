<?php

/////////////////////// INSTALL FILE ////////////////////////////////////////////////	

/*

This file handles all upgrades and installation 


*/


function ckStat_install () {
	// this installs the plugin

	global $wpdb;
	

	
	// values
	$clikStats_version = "0.2"; // table version number
	$table_name = $wpdb->prefix."clik_stats";
	
	// enviromental variables
	$env_dir =  array_pop(explode('/',dirname(__FILE__)));
	
	// check the enviroment looks valid (less than 11 characters more than zero) if not try a different method
	if (empty($env_dir) || strlen($env_dir)>11) $env_dir = array_pop(explode(chr(92),dirname(__FILE__)));
	
	// if the enviroment still looks invalid try naming it manually (fail safe)
	if (empty($env_dir) || strlen($env_dir)>11) $env_dir = 'clikstats';
	
	
	$enviroment = get_option('siteurl')."/wp-content/plugins/".$env_dir."/";// get enviroment
    
	
	// create the option if it does`nt already exist
	add_option('clikStats_pagenation', 15);
	add_option('clikStats_top', 3);
	add_option('clikStats_last', 1);
	add_option('clikStats_enviroment', '');
	add_option('ClikStats_pScope', 3);
	
	
	// update the option if it is inaccurate, future proofs too!!
	if (get_option("clikStats_enviroment")!==$enviroment) update_option("clikStats_enviroment", $enviroment);

	
	// setup SQL
	$sql = "CREATE TABLE " . $table_name . " (
	id bigint(255) NOT NULL AUTO_INCREMENT,
	url varchar(255) NOT NULL,
	ip varchar(255) NOT NULL,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	post_id bigint(255) NOT NULL,
	UNIQUE KEY id (id)
	);";
   
   
	// get which version is installed
	$installed_ver = get_option( "clikStats_version" );

   
	// fresh install using sql above
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("clikStats_version", $clikStats_version);
		}
     
	// upgrade database if table exists but version number is not current with the sql above
	if( $installed_ver != $clikStats_version ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		update_option( "clikStats_version", $clikStats_version);
		}
	}

	
?>