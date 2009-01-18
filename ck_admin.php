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
				<a href="<?php echo $_SERVER['PHP_SELF'].$location;?>&day=all" <?php if ($_GET['day']=="all") echo 'class="current"'; ?>><?php _e('Overall Summary',$ck_domain);?></a>
			</li>
		</ul>
		<br style="clear:both;" />
		<?php
		

		if (empty($_GET['day'])){
			$arg1.="DATE_SUB(CURDATE(),INTERVAL 0 DAY) <= date";
			}
		
		if ($_GET['day']=="yesterday"){
			$arg1.="DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= date AND date < DATE_SUB(CURDATE(),INTERVAL 0 DAY)";
			$arg2.="WHERE ".$arg1;
			}

		if ($_GET['day']=="all"){
			$arg1.="1=1";
			}	
			
		
		$SQL= "SELECT id, url as Link, date, ip, ";		
		$SQL.="(SELECT COUNT(id) FROM ".$table_name." WHERE LEFT(url,255)=Link AND ";
		$SQL.=$arg1.") ";
		$SQL.="AS Clicks ";
		$SQL.="FROM ".$table_name." ";
		$SQL.=$arg2;
		$SQL.="ORDER BY id DESC LIMIT 0,".intval(get_option('clikStats_last'));				
		$latest = $wpdb->get_results($SQL, ARRAY_A);
		
		
		
		$SQL= "SELECT DISTINCT left(url,255) as Link, ";
		$SQL.="(SELECT COUNT(id) FROM ".$table_name." WHERE LEFT(url,255)=Link AND ";
		$SQL.=$arg1.") as Clicks ";
		$SQL.="FROM ".$table_name." ";
		$SQL.="ORDER BY Clicks DESC LIMIT 0,".intval(get_option('clikStats_top'));
		$topList = $wpdb->get_results($SQL, ARRAY_A);
		
		
		
		$SQL= "SELECT COUNT(id) AS Count FROM ".$table_name." WHERE ".$arg1;
		$total = $wpdb->get_results($SQL, ARRAY_A);
		
		
		?>
		<h3 style="margin:0; padding:0;"><?php _e('Total',$ck_domain);?> cliks <?php echo $total[0]['Count']; ?></h3>
		<table style="margin-top:10px; border:none;" class="widefat">
			<?php if (!empty($latest)){?>
				<tr>
					<th>
						<?php _e('Last',$ck_domain);?> <?php echo get_option('clikStats_last')!=="1" ? get_option('clikStats_last')." clik`s " : "clik"; ?>
					</th>
					<th></th>
					<th></th>
				</tr>
				<?php foreach ($latest as $last) : ?>
				<tr>
					<td class="noBorder"></td>
					<td class="noBorder">
						<?php addIpAnchor($last['ip']); echo ' @ '.end(explode(' ',$last['date'])); ?>
						&nbsp;-&nbsp;<?php addUrlAnchor($last['Link']); ?>
					</td>
					<td style="color:#aa0000;" class="noBorder">
						<?php echo '('.$last['Clicks'].') Cliks'; ?>
					</td>
				</tr>				
				<?php endforeach;?>
				<tr height="40px">
					<td class="noBorder"></td>
					<td class="noBorder"></td>
					<td class="noBorder"></td>
				</tr>
			<?php } ?>
			<?php if (!empty($topList)){?>
				<tr>
					<th>
						<?php _e('Top',$ck_domain);?> <?php echo get_option('clikStats_top')!=="1" ? get_option('clikStats_top')." clik`s " : "clik"; ?>
					</th>
					<th></th>
					<th></th>
				</tr>

				<?php foreach ($topList as $top) : ?>
					<tr>
						<td class="noBorder"></td>
						<td class="noBorder">
							<?php addUrlAnchor($top['Link']);?>&nbsp;&nbsp;
						</td>
						<td style="color:#aa0000;" class="noBorder">
							<?php echo '('.$top['Clicks'].') Cliks'; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php } ?>
		</table>
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
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=counter" <?php if (empty($_GET['users'])) echo 'class="current"'; ?>><?php _e('Links',$ck_domain);?></a> |</li>
			<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=counter&users=total" <?php if (!empty($_GET['users'])) echo 'class="current"'; ?>><?php _e('Participants',$ck_domain);?></a></li>
		</ul>
		<br style="clear:both;" />
		<?php
		
		if (empty($_GET['users'])){
			$SQL= "SELECT url as Url, ";
			$SQL.="COUNT(".$table_name.".id) as Count ";
			$SQL.="FROM ".$table_name." ";
			$SQL.= $month=="99" && $year=="9999" ? "" : "WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";
			$SQL.="GROUP BY url ORDER BY Count DESC";
			$title="Total links";
			}
		else {
			$SQL = "SELECT ip as Participant, COUNT(id) as Clicks, ";
			$SQL.= "MIN(date) as 'First Visit', MAX(date) as 'Last Visit' ";
			$SQL.= "FROM ".$table_name." ";
			$SQL.= $month=="99" && $year=="9999" ? "" : "WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";	
			$SQL.= "GROUP BY ip ORDER BY Clicks DESC";
			$title="Total Participants";
			}
		
		$array = $wpdb->get_results($SQL, ARRAY_A); 
		if (!empty($array)) echo '<h3 style="margin:0; padding:0;">'.__($title, $ck_domain).' '.intval(count($array)).'</h3>';
		else echo '<h3>'.__('No stats yet',$ck_domain).'!</h3>';
		
		$args = array('Participant'=>'addIpAnchor','Url'=>'addUrlAnchor','Last Visit'=>'addDateAnchor','First Visit'=>'addDateAnchor');

		pagenate($array, intval(get_option("clikStats_pagenation")), 'counter',$args, $pagArg);
		
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
		
		// default display	
		if (empty($_GET['ip']) && empty($_GET['url']) && empty($_GET['date'])){
			$SQL= "SELECT id as _del, date as Date, url as Url, ip ";
			$SQL.="FROM ".$table_name." ";
			$SQL.= $month=="99" && $year=="9999" ? "" : "WHERE MONTH(date)=".$month." AND YEAR(date)=".$year." ";
			$SQL.="ORDER BY id DESC";
			$array = $wpdb->get_results($SQL, ARRAY_A);	
			}
		
		// ip display	
		if (!empty($_GET['ip'])){
			$SQL= "SELECT id as _del, date as Date, url as Url, ip ";
			$SQL.="FROM ".$table_name." ";
			$SQL.="WHERE ip='".mysql_real_escape_string($_GET['ip'])."' ";
			$SQL.="ORDER BY id DESC";			
			$array = $wpdb->get_results($SQL, ARRAY_A);	
			$title = '('.$_GET['ip'].') ';
			$pagArg=0;
			}
		
		// url display	
		if (!empty($_GET['url'])){
			
			$reserved = array('page','url','stat','date_sel');
			$url=$_GET['url'];
			foreach ($_GET as $key=>$val) $url.= !in_array($key, $reserved, true)  ? '&'.$key.'='.$val : '';
					
			$SQL= "SELECT id as _del, date as Date, url as Url, ip ";
			$SQL.="FROM ".$table_name." ";
			$SQL.="WHERE url='".mysql_real_escape_string($url)."' ";
			$SQL.="ORDER BY id DESC";			
			$array = $wpdb->get_results($SQL, ARRAY_A);	
			$title = '('.$_GET['url'].') ';
			$pagArg=0;
			}
		
		// date display	
		if (!empty($_GET['date'])){
		
			list($date) = explode(' ',$_GET['date']);
			$SQL= "SELECT id as _del, date as Date, url as Url, ip ";
			$SQL.="FROM ".$table_name." ";
			$SQL.="WHERE Date BETWEEN '".mysql_real_escape_string($date)." 00:00:01' AND ";
			$SQL.="'".mysql_real_escape_string($date)." 23:59:59' ";
			$SQL.="ORDER BY id DESC";
			$array = $wpdb->get_results($SQL, ARRAY_A);	
			$title = '('.$date.') ';
			$pagArg=0;
			}
		
		?>
		
		<h2><?php _e('Timeline',$ck_domain);?><?php echo empty($title) ? '': ' '.__('for',$ck_domain).' ';?><span style="font-size:12px;"><?php echo $title;?></span></h2>
		
		<?php
		if (!empty($array)) echo '<h3>'.__('Total clicks').' '.intval(count($array)).'</h3>';
		else echo '<h3>'.__('No stats yet',$ck_domain).'!</h3>';
		
		$args = array('ip'=>'addIpAnchor','Url'=>'addUrlAnchor','Date'=>'addDateAnchor');
		pagenate($array, intval(get_option("clikStats_pagenation")), 'stat', $args, $pagArg);
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
			<th scope="row"><?php _e('Number of last cliks on summary',$ck_domain);?></th>
			<td><input type="text" name="clikStats_last" value="<?php echo get_option('clikStats_last'); ?>" /></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Number of top cliks on summary',$ck_domain);?></th>
			<td><input type="text" name="clikStats_top" value="<?php echo get_option('clikStats_top'); ?>" /></td>
			</tr>
				
		</table>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="clikStats_pagenation, clikStats_top, clikStats_last" />

		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Save Changes',$ck_domain) ?>" />
		</p>

		</form>
	</div>
<?php }