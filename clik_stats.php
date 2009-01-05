<?php
/*
Plugin Name: Clik stats
Plugin URI: http://adriancallaghan.co.uk/clikStats/
Description: This plugin records the amount of anchor point clicks within the blog, and provides who, when, what information.
Version: 0.4
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
	
	$processor_file = get_option('siteurl')."/wp-content/plugins/clikstats/record.php";
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
    add_submenu_page(__FILE__, 'Timeline', 'Timeline', 8, 'show', 'cs_show');
    
	// Add a third submenu to the custom top-level menu:
	add_submenu_page(__FILE__, 'Options', 'Options', 8, 'options', 'cs_options');
	
	}
		
	
function cs_summary() {
	global $wpdb;
	global $table_name;
	
	?>
	<div class="wrap">
		<h2>Summary</h2>
		
		<?php
		/*
		if (!empty($_POST['delete'])){
			$deleted=0;
			
			foreach ($_POST['delete'] as $del) {
				$SQL = "DELETE FROM ".$table_name." WHERE id=".intval($del);
				if ($wpdb->query($SQL)) $deleted++;
				}
			echo '<br class="clear"><span style="color:#cc0000;">';
			echo $deleted>1 ? $deleted." entrys deleted" : $deleted." entry deleted";
			echo '</span><br class="clear">';
			}
		*/
		?>
		
		
		
		<?php 
		$SQL= "SELECT DISTINCT(url) FROM ".$table_name;
		$array = $wpdb->get_results($SQL, ARRAY_A); 
		?>	
		
		<h3>Total links followed <?php echo intval(count($array)); ?></h3>	
		
		<form method="post" action="">
		
		<?php
			// pagination loop begins
			$result = $array;
			$limit = intval(get_option("clikStats_pagenation"));
			if ($limit==0) $limit=15;
			$pagelink = get_option('siteurl').'/wp-admin/admin.php';
			$queryString = 'stat';

			// pagenation begins, KUDOS to andrew for his considerable help here
			$listing = intval($_GET[$queryString]);
			$start = $listing * $limit;

			$all = count($result);
			$all_pages = ceil($all / $limit);

			// this forwards any criteria associated with the search by adding it onto the end of the hyperlink
			$args=""; foreach ($_GET as $key=>$val){
				$args.= $key!=$queryString ? "&".$key."=".$val : "" ;
				}

			if($all>0){
				if(($listing+1) > $all_pages){$listing=0;}
				$of = 0;

				////////////////////////////////////////// navigation starts

				?>
				
			<div class="tablenav">
				
				<?php /*
				<div class="alignleft">
					<input value="Delete" class="button-secondary delete" type="submit">
				</div> */ ?>
			
				<div class='tablenav-pages'>
							
					<?php
					
					
					if(($listing+1) > 1 && ($listing+1) <= $all_pages){
						// up
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; Previous</a>';
						}

					if ($all_pages>1){
						// goto page
						for($i=0;$i<$all_pages;$i++){

							// echo`s the number also checks to see if this is the current page
							echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
							//if($i < ($all_pages-1)) echo ' | ';
							}
						}

					if(($listing+1) < $all_pages){
						// down
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">Next &raquo;</a>';
						}

					?>
					
				</div>

				<br class="clear">
				
			</div>
		
		<br class="clear">
		
		<?php ///////////////////////////////////////// navigation ends	?>
		
		
		
					
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">Link</th>
					<th scope="col">Clicks</th>
				</tr>
			</thead>
			<tbody>
				<?php
				// pagenation resumes

				for($i=$start;$i<($start+$limit);$i++){
					if (empty($result[$i])) break; // break if complete
					// format results
					
					$SQL= "SELECT count(id) as count FROM ".$table_name." WHERE url='".$result[$i]['url']."'";
					$count = $wpdb->get_results($SQL, ARRAY_A);
				?>
				<tr>
					<td><?php echo $result[$i]['url'];?></td>
					<td><?php echo $count[0]['count'];?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>			
			
		<?php ////////////////////////////////////////// navigation starts ?>
				
			<div class="tablenav">
				
				<?php /*
				<div class="alignleft">
					<input value="Delete" class="button-secondary delete" type="submit">
				</div> */?>
			
				<div class='tablenav-pages'>
							
					<?php
					
					
					if(($listing+1) > 1 && ($listing+1) <= $all_pages){
						// up
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; Previous</a>';
						}

					if ($all_pages>1){
						// goto page
						for($i=0;$i<$all_pages;$i++){

							// echo`s the number also checks to see if this is the current page
							echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
							//if($i < ($all_pages-1)) echo ' | ';
							}
						}

					if(($listing+1) < $all_pages){
						// down
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">Next &raquo;</a>';
						}

					?>
					
				</div>

				<br class="clear">
				
			</div>
			
		<br class="clear">	
		
		<?php ///////////////////////////////////////// navigation ends	?>	
			
			
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
		
		if (!empty($_POST['delete'])){
			$deleted=0;
			
			foreach ($_POST['delete'] as $del) {
				$SQL = "DELETE FROM ".$table_name." WHERE id=".intval($del);
				if ($wpdb->query($SQL)) $deleted++;
				}
			echo '<br class="clear"><span style="color:#cc0000;">';
			echo $deleted>1 ? $deleted." entrys deleted" : $deleted." entry deleted";
			echo '</span><br class="clear">';
			}
		
		?>
		
		
		
		<?php 
		$SQL= "SELECT * FROM ".$table_name." ORDER BY id DESC";
		$array = $wpdb->get_results($SQL, ARRAY_A);	
		?>	
		
		<h3>Total clicks <?php echo intval(count($array)); ?></h3>
		
		<form method="post" action="">
		
		<?php
			// pagination loop begins
			$result = $array;
			$limit = intval(get_option("clikStats_pagenation"));
			if ($limit==0) $limit=15;
			$pagelink = get_option('siteurl').'/wp-admin/admin.php';
			$queryString = 'stat';

			// pagenation begins, KUDOS to andrew for his considerable help here
			$listing = intval($_GET[$queryString]);
			$start = $listing * $limit;

			$all = count($result);
			$all_pages = ceil($all / $limit);

			// this forwards any criteria associated with the search by adding it onto the end of the hyperlink
			$args=""; foreach ($_GET as $key=>$val){
				$args.= $key!=$queryString ? "&".$key."=".$val : "" ;
				}

			if($all>0){
				if(($listing+1) > $all_pages){$listing=0;}
				$of = 0;

				////////////////////////////////////////// navigation starts

				?>
				
			<div class="tablenav">
				
				<div class="alignleft">
					<input value="Delete" class="button-secondary delete" type="submit">
				</div>
			
				<div class='tablenav-pages'>
							
					<?php
					
					
					if(($listing+1) > 1 && ($listing+1) <= $all_pages){
						// up
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; Previous</a>';
						}

					if ($all_pages>1){
						// goto page
						for($i=0;$i<$all_pages;$i++){

							// echo`s the number also checks to see if this is the current page
							echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
							//if($i < ($all_pages-1)) echo ' | ';
							}
						}

					if(($listing+1) < $all_pages){
						// down
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">Next &raquo;</a>';
						}

					?>
					
				</div>

				<br class="clear">
				
			</div>
				
		<?php ///////////////////////////////////////// navigation ends	?>


		<br class="clear" />
		
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"></th>
						<th scope="col">Date</th>
						<th scope="col">Url</th>
						<th scope="col">Ip</th>
					</tr>
				</thead>
				<tbody>
				
					<?php
					// pagenation resumes

					for($i=$start;$i<($start+$limit);$i++){
						if (empty($result[$i])) break; // break if complete
						// format results
						?>	
						<tr id='page-2' class='alternate'>
							<th scope="row" class="check-column">
								<input name="delete[]" value="<?php echo $result[$i]['id'];?>" type="checkbox">
							</th>
							<td><?php echo $result[$i]['date'];?></td>
							<td><?php echo $result[$i]['url'];?></td>
							<td><?php echo $result[$i]['ip'];?></td>
						</tr>
					<?php } ?>

				</tbody>
			</table>

		<?php ////////////////////////////////////////// navigation starts ?>
				
			<div class="tablenav">
				
				<div class="alignleft">
					<input value="Delete" class="button-secondary delete" type="submit">
				</div>
			
				<div class='tablenav-pages'>
							
					<?php
					
					
					if(($listing+1) > 1 && ($listing+1) <= $all_pages){
						// up
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; Previous</a>';
						}

					if ($all_pages>1){
						// goto page
						for($i=0;$i<$all_pages;$i++){

							// echo`s the number also checks to see if this is the current page
							echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
							//if($i < ($all_pages-1)) echo ' | ';
							}
						}

					if(($listing+1) < $all_pages){
						// down
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">Next &raquo;</a>';
						}

					?>
					
				</div>

				<br class="clear">
				
			</div>
				
		<?php ///////////////////////////////////////// navigation ends	?>



			
		<?php } else echo '<h3>No stats yet!</h3>'; ?>
		
		</form>
		
	</div>
<?php }	
	
	
function cs_options() { ?>
	<div class="wrap">
		<h2>Clik Stats Options</h2>

		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">

		<tr valign="top">
		<th scope="row">Number of entrys per page</th>
		<td><input type="text" name="clikStats_pagenation" value="<?php echo get_option('clikStats_pagenation'); ?>" /></td>
		</tr>
		

		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="clikStats_pagenation" />

		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
		</p>

		</form>
	</div>

	<?php }
///////////////////////// BACK OFFICE ENDS //////////////////////////////////////



	
	
/////////////////////// INSTALL ////////////////////////////////////////////////	

// run install function
register_activation_hook(__FILE__,'ckStat_install');

function ckStat_install () {


	// this installs the plugin
	
	
	
	global $wpdb;
	
	
	
		
	// values
	$clikStats_version = "0.1"; // table version number
	$table_name = $wpdb->prefix . "clik_stats";
    
	add_option("clikStats_pagenation", 15);
	

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
		}
     
	// upgrade database if table exists but version number is not current with the sql above
	if( $installed_ver != $clikStats_version ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		update_option( "clikStats_version", $clikStats_version);
		}
	}
 
////////////////////////// INSTALL ENDS ///////////////////////////////////////////////// 
?>