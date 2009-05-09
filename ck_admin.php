<?php

/////////////////////// ADMIN FILE //////////////////////////////////////////////////	

/*

This file handles all back office tasks 


*/

function ckStat_setAdmin() {
	global $ck_domain;

    // Add a new top-level menu
    add_menu_page('Clik Stats', 'Clik Stats', 8, __FILE__,'cs_summary');

    // Add a submenu to the custom top-level menu:
    add_submenu_page(__FILE__, __('Summary',$ck_domain), __('Summary',$ck_domain), 8, __FILE__, 'cs_summary');

	    // Add a second submenu to the custom top-level menu:
    add_submenu_page(__FILE__, __('Counter',$ck_domain), __('Counter',$ck_domain), 8, 'counter', 'cs_counter');
	
    // Add a second submenu to the custom top-level menu:
    add_submenu_page(__FILE__, __('Timeline',$ck_domain), __('Timeline',$ck_domain), 8, 'show', 'cs_show');
    
	// Add a third submenu to the custom top-level menu:
	add_submenu_page(__FILE__, __('Options',$ck_domain), __('Options',$ck_domain), 8, 'options', 'cs_options');
	
	}
		
function cs_summary() {
	global $wpdb;
	global $table_name;
	global $Ck_domain;
	include('ck_functions.php');
	include('ckStyle.php');
	
	
	$location = '?page='.getCkDir().'/ck_admin.php';

	?>
	<div class="wrap">
		<h2><?php _e('Summary',$Ck_domain); ?></h2>
		<ul class="subsubsub">
			<li>
				<a href="<?php echo $_SERVER['PHP_SELF'].$location;?>" <?php if (empty($_GET['day'])) echo 'class="current"'; ?>><?php _e('Todays Summary',$Ck_domain); ?></a> |
			</li>
			<li>
				<a href="<?php echo $_SERVER['PHP_SELF'].$location;?>&day=yesterday" <?php if ($_GET['day']=="yesterday") echo 'class="current"'; ?>><?php _e('Yesterdays Summary',$ck_domain);?></a>
			</li>
			<li>
				<a href="<?php echo $_SERVER['PHP_SELF'].$location;?>&day=all" <?php if ($_GET['day']=="all") echo 'class="current"'; ?>><?php _e('Total Summary',$ck_domain);?></a>
			</li>
		</ul>
		
		<form id="posts-filter" action="admin.php" method="get">
			<p class="search-box">
				<input class="search-input" id="post-search-input" name="val" value="" type="text">
				<input value="Search Links" class="button" type="submit">
				<input type="hidden" name="page" value="show">
				<input type="hidden" name="request" value="name">
			</p>
		</form>

		
		<?php
		
		switch ($_GET['day']){
			
			// details for yesterday only
			case "yesterday": 
				$arg ="DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= date AND date < DATE_SUB(CURDATE(),INTERVAL 0 DAY)";
				$day = 'yesterday';
				break;

			// all details
			case "all":
				$arg = "1=1";
				$day = 'total';
				break;
				
			// details for today only (default)	
			default:
				$arg ="DATE_SUB(CURDATE(),INTERVAL 0 DAY) <= date";
				$day = 'today';
			}	
		
		
		
		
		/*
		
		The Twin populating querys that set up the page content
		They only process if switched on, which speeds up loading time by a tiny, weeny amount
		
		*/
		
				
		// latest cliks summary 		
		$SQL= "SELECT id, url as Link, date, ip ";		
		$SQL.="FROM ".$table_name." ";
		$SQL.="WHERE ".$arg." ";
		$SQL.="ORDER BY id DESC LIMIT 0,".intval(get_option('clikStats_last'));	
		if (get_option('clikStats_last')) $latest = $wpdb->get_results($SQL, ARRAY_A);	
		
		// most popular sumary
		$SQL= "SELECT id, url as Link, date, ip ";		
		$SQL.="FROM ".$table_name." ";
		if (get_option('clikStats_top')) $topList = $wpdb->get_results($SQL, ARRAY_A);
		

		// get the count
		$SQL= "SELECT COUNT(id) AS Count FROM ".$table_name." WHERE ".$arg;
		list($total) = $wpdb->get_results($SQL, ARRAY_A);
		
		//print_r($topList);
		?>
		<div style="margin-top:60px; padding:20px; border:1px solid #333333; background:#ffffff;">
			<h3 style="margin:0; padding:0;">
				<?php echo $total['Count']; ?> Cliks <?php _e($day,$ck_domain);?>
			</h3>
			
			<table class="widefat" style="border:none;">
			
				<?php if (!empty($latest)){?>
					<tr height="40px">
						<td class="noBorder"></td>
						<td class="noBorder"></td>
						<td class="noBorder"></td>
					</tr>
					<tr>
						<th>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=show">
							<?php 
								_e('Last',$ck_domain);
								echo '&nbsp;';
								$cKlast = get_option('clikStats_last');
								echo $cKlast!=="1" ? $cKlast." clik`s " : "clik"; 
							?>
							</a>
						</th>
						<th></th>
						<th></th>
					</tr>
					
					<?php 
					
						/*

						get the count of cliks for each link 
						
						in version 0.6, mySql brought the server to its knees so has been
						changed to PHP processing
						
						It is calculated here because we know that if we within this conditional statement
						we have some content to count
						
						*/					
						$linkz_freq = array_count_values(array_map(create_function('$val','return $val[\'Link\'];'),$latest));
						
						
					?>
							
					<?php foreach ($latest as $last) : ?>
					<tr>
						<td class="noBorder"></td>
						<td class="noBorder">
							<?php addIpAnchor($last['ip']); echo ' @ '.end(explode(' ',$last['date'])); ?>
							&nbsp;-&nbsp;<?php addUrlAnchor($last['Link']); ?>
						</td>
						<td style="color:#aa0000;" class="noBorder">
							<?php echo '('.$linkz_freq[$last['Link']].') Cliks'; ?>
						</td>
					</tr>				
					<?php endforeach;?>
				<?php } ?>
				
				
				<?php if (!empty($topList)){
				
					/*

					get the count of cliks for each link 
					
					in version 0.6, mySql brought the server to its knees so has been
					changed to PHP processing
					
					It is calculated here because we know that if we are within this conditional statement
					we have some content to count
					
					*/	
					
					function orderByValues($array){

						$temp = array();
						$mainReturn = array();
						
						// place the values within array as keys, handles duplicates
						foreach ($array AS $key=>$val)	$temp[intval($val)][]=$key;
						ksort($temp); // sort by keys

						// re-order the array, and add the values as associative
						foreach ($temp AS $key=>$counter) foreach ($counter AS $value) $mainReturn[] = array('Name'=>$value,'Count'=>$key);
							
						return $mainReturn;
						}

					
					
					// get the count
					$linkz_freq = array_count_values(array_map(create_function('$val','return $val[\'Link\'];'),$topList));
					$linkz = array_reverse(orderByValues($linkz_freq), true);
					?>
					<tr height="40px">
						<td class="noBorder"></td>
						<td class="noBorder"></td>
						<td class="noBorder"></td>
					</tr>
					<tr>
						<th>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=counter">
							<?php 
								_e('Top',$ck_domain);
								echo '&nbsp;';
								$cKtop = get_option('clikStats_top');
								echo $cKtop!=="1" ? $cKtop." clik`s " : "clik"; 
							?>
							</a>
						</th>
						<th></th>
						<th></th>
					</tr>

					<?php 
					$x=0; foreach ($linkz AS $link){ $x++; if ($x>$cKtop) break; ?>
						<tr>
							<td class="noBorder"></td>
							<td class="noBorder">
								<?php addUrlAnchor($link['Name']);?>&nbsp;&nbsp;
							</td>
							<td style="color:#aa0000;" class="noBorder">
								<?php echo '('.$link['Count'].') Cliks'; ?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
				
				
				
				<tr height="40px">
					<td class="noBorder"></td>
					<td class="noBorder"></td>
					<td class="noBorder"></td>
				</tr>
				
				
				
				
			</table>
		</div>
	</div>
	<?php
	}
	
	
		
function cs_counter() {
	global $wpdb;
	global $table_name;
	global $ck_domain;
	include('ck_functions.php');
	include('ckStyle.php');
	
	
	// gets the current date or the selected one
	$selDate = pagenateMonthYearPreProccessor();
	$month 		= $selDate['month']; 	// gets the requested month
	$year  		= $selDate['year'];  	// gets the requested year
	$pagArg		= $selDate['argument']; // this argument is placed into the pagenation date argument

	?>
	<div class="wrap">
		<h2><?php _e('Counter',$ck_domain);?></h2>
		
		<ul class="subsubsub">
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=counter" <?php if (empty($_GET['request'])) echo 'class="current"'; ?>><?php _e('Links',$ck_domain);?></a> |</li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=counter&request=participants" <?php if ($_GET['request']=='participants') echo 'class="current"'; ?>><?php _e('Participants',$ck_domain);?></a></li>
		</ul>
		<br style="clear:both;" />
		<?php
		
		switch ($_GET['request']){
		
			// request for users
			case 'participants':
				$SQL = "SELECT ip as Participant, COUNT(id) as Clicks, ";
				$SQL.= "MIN(date) as 'First Visit', MAX(date) as 'Last Visit' ";
				$SQL.= "FROM ".$table_name." ";
				$SQL.= $month=="99" && $year=="9999" ? "":"WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";	
				$SQL.= "GROUP BY ip ORDER BY Clicks DESC";
				$title="Total Participants";
			break;
			
			default:
				$SQL= "SELECT url as Url, ";
				$SQL.="COUNT(".$table_name.".id) as Count ";
				$SQL.="FROM ".$table_name." ";
				$SQL.= $month=="99" && $year=="9999" ? "" : "WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";
				$SQL.="GROUP BY url ORDER BY Count DESC";
				$title="Total links";
			}
			

		
		$array = $wpdb->get_results($SQL, ARRAY_A); 
		if (!empty($array)) echo '<h3 style="margin:0; padding:0;">'.__($title, $ck_domain).' '.intval(count($array)).'</h3>';
		else echo '<h3>'.__('No stats yet',$ck_domain).'!</h3>';
		
		
		// arguments for pagination
		$args = array('Participant'=>'addIpAnchor','Url'=>'addUrlAnchor','Last Visit'=>'addDateAnchor','First Visit'=>'addDateAnchor');
		$maxColumns = intval(get_option("clikStats_pagenation"));
		$maxScope 	= intval(get_option("ClikStats_pScope"));
		
		pagenate($array, $maxColumns, 'counter',$args, $pagArg, $maxScope);
		
		?>
	</div>	
<?php }		
	
	
function cs_show() {
	global $wpdb;
	global $table_name;
	global $ck_domain;
	include('ck_functions.php');
	include('ckStyle.php');
	
		
	// gets the current date or the selected one
	$selDate = pagenateMonthYearPreProccessor();
	$month 		= $selDate['month']; 	// gets the requested month
	$year  		= $selDate['year'];  	// gets the requested year
	$pagArg		= $selDate['argument']; // this argument is placed into the pagenation date argument
	
	
	?>
	<div class="wrap">
		<?php
		// handles deletion
		if (!empty($_POST['delete'])){
			$deleted=0;
			
			foreach ($_POST['delete'] as $del) {
				$SQL = "DELETE FROM ".$table_name." WHERE id=".intval($del);
				if ($wpdb->query($SQL)) $deleted++;
				}
			echo '<br class="clear"><span style="color:#cc0000;">';
			echo $deleted>1 ? $deleted." ".__('entrys deleted',$ck_domain) : $deleted." ".__('entry deleted',$ck_domain);
			echo '</span><br class="clear">';
			}
		
		?>
		<?php 
		
		// process the request by setting the correct SQL and any arguments
		switch($_GET['request']){
			
			// source display	
			case 'source': 	
				$SQL= "SELECT id as _del, date as Date, url as Url, post_id as Source, ip ";
				$SQL.="FROM ".$table_name." ";
				$SQL.="WHERE post_id='".mysql_real_escape_string($_GET['val'])."' ";
				
				// check for date clause 
				if (isset($_GET['date_sel'])) {
					$date = explode('-',$_GET['date_sel']);
					$SQL.="AND MONTH(date)=".intval($date[0])." AND YEAR(date)=".intval($date[1])." ";
					}
				
				$SQL.="ORDER BY id DESC";


				// page name
				list($post) = $wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.'posts` WHERE id='.intval($_GET['val']), ARRAY_A);
				$title = '('.$post['post_type'].' - '.$post['post_name'].') ';
				
				break;
			
			
			// ip display	
			case 'ip': 	
				$SQL= "SELECT id as _del, date as Date, url as Url, post_id as Source, ip ";
				$SQL.="FROM ".$table_name." ";
				$SQL.="WHERE ip='".mysql_real_escape_string($_GET['val'])."' ";
				
				// check for date clause 
				if (isset($_GET['date_sel'])) {
					$date = explode('-',$_GET['date_sel']);
					$SQL.="AND MONTH(date)=".intval($date[0])." AND YEAR(date)=".intval($date[1])." ";
					}
				
				$SQL.="ORDER BY id DESC";			
				$title = '('.$_GET['val'].') ';
				break;
			
			// url display
			case 'url': 
				$reserved = array('page','request','val','stat','date_sel');
				$url=$_GET['val'];
				foreach ($_GET as $key=>$val) $url.= !in_array($key, $reserved, true)  ? '&'.$key.'='.$val : '';	
				$SQL= "SELECT id as _del, date as Date, url as Url, post_id as Source, ip ";
				$SQL.="FROM ".$table_name." ";
				$SQL.="WHERE url='".mysql_real_escape_string($url)."' ";
				
				// check for date clause 
				if (isset($_GET['date_sel'])) {
					$date = explode('-',$_GET['date_sel']);
					$SQL.="AND MONTH(date)=".intval($date[0])." AND YEAR(date)=".intval($date[1])." ";
					}
				
				// wrap the sql up
				$SQL.="ORDER BY id DESC";			
				$title = '('.$_GET['val'].') ';
				break;

			// date display	
			case 'date': 
				list($date) = explode(' ',$_GET['val']);
				$SQL= "SELECT id as _del, date as Date, url as Url, post_id as Source, ip ";
				$SQL.="FROM ".$table_name." ";
				$SQL.="WHERE Date BETWEEN '".mysql_real_escape_string($date)." 00:00:01' AND ";
				$SQL.="'".mysql_real_escape_string($date)." 23:59:59' ";
				$SQL.="ORDER BY id DESC";
				$title = '('.$date.') ';
				break;	
			
			case 'name':

				// perform a search
				$SQL= "SELECT id, url as Link, date, post_id, ip ";		
				$SQL.="FROM ".$table_name." WHERE url LIKE '%".addslashes($_GET['val'])."%'";
				
				// check for date clause 
				if (isset($_GET['date_sel'])) {
					$date = explode('-',$_GET['date_sel']);
					$SQL.="AND MONTH(date)=".intval($date[0])." AND YEAR(date)=".intval($date[1])." ";
					}
				
				$results = $wpdb->get_results($SQL, ARRAY_A);				
				$title=stripslashes($_GET['val']);
				break;
				
			// standard layout, with selected date
			default : 
				$SQL= "SELECT id as _del, date as Date, url as Url, post_id as Source, ip ";
				$SQL.="FROM ".$table_name." ";
				$SQL.= $month=="99" && $year=="9999" ? "" : "WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";
				$SQL.="ORDER BY id DESC";
			}
			
		// get the page details
		$array = $wpdb->get_results($SQL, ARRAY_A);	
		
		?>
		<h2>
			<?php _e('Timeline',$ck_domain);?><?php echo empty($title) ? '': ' '.__('for',$ck_domain).' ';?>
			<span style="font-size:12px;"><?php echo $title;?></span>
		</h2>
		<?php
		if (!empty($array)) echo '<h3>'.__('Total clicks').' '.intval(count($array)).'</h3>';
		else echo '<h3>'.__('No stats yet',$ck_domain).'!</h3>';
		
		
		// arguments for pagination
		$args = array(
		'ip'=>'addIpAnchor',
		'Url'=>'addUrlAnchor',
		'Date'=>'addDateAnchor',
		'Source'=>'addSourceAnchor'
		);
		
		$maxColumns = intval(get_option("clikStats_pagenation"));
		$maxScope 	= intval(get_option("ClikStats_pScope"));
		
		pagenate($array, $maxColumns, 'stat', $args, $pagArg, $maxScope);
		?>	
		
	</div>
<?php }	
	
	
function cs_options() { ?>
	<div class="wrap">
		<h2>Clik Stats <?php _e('Options');?></h2>

		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<table class="form-table">

			<tr valign="top">
			<th scope="row"><?php _e('Number of entrys per page',$ck_domain);?></th>
			<td><input type="text" name="clikStats_pagenation" value="<?php echo get_option('clikStats_pagenation'); ?>" /></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Scope of pagination',$ck_domain);?></th>
			<td><input type="text" name="ClikStats_pScope" value="<?php echo get_option('ClikStats_pScope'); ?>" /></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Number of last cliks on summary',$ck_domain);?></th>
			<td><input type="text" name="clikStats_last" value="<?php echo get_option('clikStats_last'); ?>" /></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Number of top cliks on summary',$ck_domain);?></th>
			<td><input type="text" name="clikStats_top" value="<?php echo get_option('clikStats_top'); ?>" /></td>
			</tr>
							
		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="clikStats_pagenation, ClikStats_pScope, clikStats_top, clikStats_last" />

		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save Changes',$ck_domain) ?>" />
		</p>

		</form>
	</div>
<?php }