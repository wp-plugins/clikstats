<?php
/*
Plugin Name: Clik stats
Plugin URI: http://adriancallaghan.co.uk/clikStats/
Description: This plugin records the amount of anchor point clicks within the blog, and provides who, when, what information.
Version: 0.2
Author: Adrian Callaghan
Author URI: http://adriancallaghan.co.uk
*/







/*  Copyright 2008  Adrian Callaghan  (email : admin@adriancallaghan.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



$table_name = $wpdb->prefix."clik_stats"; // this needs to be changed in a number of places!



//////////////// FRONT END STARTS ///////////////////////////////////////////////////////////

add_filter('the_content','counter');

function counter ($text){
	
	$processor_file = get_option('siteurl')."/wp-content/plugins/clik_stats/record.php";
	$text = str_replace('<a href="', '<a href="'.$processor_file.'?link=', $text);
	
	return $text;
	}

///////// FRONT END ENDS ///////////////////////////////////////////////////////////////




























///////////////////// BACK OFFICE /////////////////////////////////////////////////


// register the pages to the hook
add_action('admin_menu','cs_setup_pages');


function cs_setup_pages() {

	// adds option to manage
	//add_management_page('Clik Stats', 'Clik Stats', 8, __FILE__, 'cs_show');

    // Add a new top-level menu
    add_menu_page('Clik Stats', 'Clik Stats', 8, __FILE__,'cs_summary');

    // Add a submenu to the custom top-level menu:
    add_submenu_page(__FILE__, 'Summary', 'Summary', 8, __FILE__, 'cs_summary');

    // Add a second submenu to the custom top-level menu:
    add_submenu_page(__FILE__, 'Timeline', 'Timeline', 8, 'sub-page2', 'cs_show');
	}
		
	
function cs_summary() {
	global $wpdb;
	global $table_name;
	
	?>
	<div class="wrap">
		<h2>Summary</h2>
		
		<?php 
		$SQL= "SELECT DISTINCT(url) FROM ".$table_name;
		$results = $wpdb->get_results($SQL, ARRAY_A); 
		?>	
		
		<h3>Total links followed <?php echo intval(count($results)); ?></h3>
		<br class="clear" />
		
		<?php if (!empty($results)) { ?>					
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col">Link</th>
						<th scope="col">Clicks</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results as $val){ 
						$SQL= "SELECT count(id) as count FROM ".$table_name." WHERE url='".$val['url']."'";
						$count = $wpdb->get_results($SQL, ARRAY_A);
					?>
					<tr>
						<td><?php echo $val['url'];?></td>
						<td><?php echo $count[0]['count'];?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>			
			
		<?php }	else echo '<h3>No stats yet!</h3>'; ?>
	</div>
<?php }		
	
	
function cs_show() {
	global $wpdb;
	global $table_name;
	?>
	<div class="wrap">
		<h2>Timeline</h2>
		
		<?php 
		$SQL= "SELECT date, url, ip FROM ".$table_name." ORDER BY id DESC";
		$results = $wpdb->get_results($SQL, ARRAY_A);	
		?>	

		<h3>Total clicks <?php echo intval(count($results)); ?></h3>

	
	
		<?php
		/*
		THIS WILL BE USED FOR PAGENATION WHEN I ADD IT
		<form id="posts-filter" action="" method="get">



			<div class="tablenav">


				<br class="clear" />
			</div>


		</form>
		*/?>

		<br class="clear" />
		
		<?php if (!empty($results)) { ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col">Date</th>
						<th scope="col">Url</th>
						<th scope="col">Ip</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($results as $val){ ?>	
					<tr id='page-2' class='alternate'>
						<td><?php echo $val['date'];?></td>
						<td><?php echo $val['url'];?></td>
						<td><?php echo $val['ip'];?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>	
		<?php }	else echo '<h3>No stats yet!</h3>'; ?>
		
	</div>
<?php }	
	
///////////////////////// BACK OFFICE ENDS //////////////////////////////////////



	
	
/////////////////////// INSTALL ////////////////////////////////////////////////	

// run install function
register_activation_hook(__FILE__,'ckStat_install');

function ckStat_install () {


	// this installs the plugin
	
	
	
	global $wpdb;
	
	// was for a config file but cant be arsed anymore
	global $clikStats_version;
	global $table_name;
	
	
		
	// values
	$clikStats_version = "0.1"; // version number
	$table_name = $wpdb->prefix . "clik_stats";
    
	
	
	// setup SQL
	$sql = "CREATE TABLE " . $table_name . " (
	id bigint(255) NOT NULL AUTO_INCREMENT,
	url varchar(255) NOT NULL,
	ip varchar(255) NOT NULL,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	UNIQUE KEY id (id)
	);";
   
   
	// get which version is installed
	$installed_ver = get_option( "clikStats_version" );

   
	// fresh install using sql above
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("clikStats_version", $clikStats_version);
		//add_option("dload_folder", 'downloads/');
		}
     
	// upgrade database if table exists but version number is not current with the sql above
	if( $installed_ver != $ckSpy_version ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		update_option( "clikStats_version", $clikStats_version);
		}
	}
 
////////////////////////// INSTALL ENDS ///////////////////////////////////////////////// 
?>