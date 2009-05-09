<?php 

// gets the name of the folder that the install is in
function getCkDir(){
	$env = explode('/',get_option('clikStats_enviroment'));
	return $env[(count($env))-2];
	}


// send to a 404
function notFoundClik(){
	global $wp_query;
	$wp_query->set_404(); status_header(404);
	exit;
	}
	
	
////////// formatting functions ///////////////////////////////////////////////////	

// click to timeline thet handles date
function addDateAnchor($date){
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&request=date&val='.date($date).'" class="info">'.$date.'</a>';
	}

// click to timeline that handles ip		
function addIpAnchor($ip){
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&request=ip&val='.$ip.'" class="info">'.$ip.'</a>';
	}

// click to timeline that handles url		
function addUrlAnchor($url){
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&request=url&val='.$url.'" class="info">'.$url.'</a>';
	}

// click to timeline that handles source (also resolves source by post_id)
function addSourceAnchor($postId){
	global $wpdb;
	
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&request=source&val='.$postId.'" class="info">';
	
	$SQL = 'SELECT * FROM `'.$wpdb->prefix.'posts` WHERE id='.intval($postId);
	list($post) = $wpdb->get_results($SQL, ARRAY_A);

	echo $post['post_name'];
	echo '</a><br />'.$post['post_type'];
	
	}
	
function pagenateMonthYearPreProccessor(){
	// returns an assoc array containing the date selection in the correct format for pagenate
	// also handles the forwarding
	$_GET['date_sel']=!empty($_POST['date_sel'])?$_POST['date_sel']:mysql_real_escape_string($_GET['date_sel']);
	if (!empty($_GET['date_sel'])) list($month, $year) = explode('-', $_GET['date_sel']);
	else {$month = date('n'); $year = date('Y'); $_GET['date_sel']=$month.'-'.$year;}
	return array('month'=>$month,'year'=>$year,'argument'=>mysql_real_escape_string($_GET['date_sel']));
	}
	
function pagenate($array, $limit_per_page, $queryString, $rules=0, $date_sel=0, $range=0) {
	
	// wordpress resources
	global $ck_domain;
	global $wpdb;
	global $table_name; // used to get the list of months and years etc
	
	
	// rules is an array of arguments each containing 2 arguments
	
	/*
	
	$rules['key']=> call	= name of key that executes the function for the layout
					call	= name of the function to be called automatically, the current value is passed
	
	// I.E many arguments can be passed within one function call providing the key is unique
	
	
	
	$date_sel = if date_sel contains a value in the format MM-YYYY the dropdown becomes available
				it selects the date based on the value, (populated from information from $tablename)
	
	
	$range = 	the scope of the pagenation displayed
	
	*/
	
	// Special wordpress reserved operands ///////////////////////////////////////
	$del_key ="_del"; // this key is reserved and activates the deletion tick boxes used in wp (can be altered)

	
	
	
	
	// output
	
	// catch bad thangs
	if (intval($limit_per_page)<1 || empty($array)) {
		
		// have allowed it to continue on to a cut off point
		// the idea is that it will still display the heading and options, and then quit
		
		$exit=true;
		
		$current_loc=0; $previous_loc=0; $all=0; 
		}
	else {
		$exit=false;
			
		// pagination internal settings
		$result = $array;
		$limit = intval($limit_per_page);
		$pagelink = $_SERVER['PHP_SELF']; 

		

		$listing = intval($_GET[$queryString]);
		$start = $listing * $limit;

		$all = count($result);
		$all_pages = ceil($all/$limit);
		
		// this forwards any criteria associated with the search by adding it onto the end of the hyperlink
		$args="";
		foreach ($_GET as $key=>$val) $args.= $key!=$queryString ? "&".$key."=".$val : "" ;

		

		if(($listing+1) > $all_pages){$listing=0;}
		$of = 0;
	
	
		// below shows where in the pagination the results are
		$current_loc 	= ($listing+1)*$limit_per_page;
		if ($current_loc>$all) $current_loc=$all;
		
		$previous_loc = $listing*$limit_per_page;
		}
		
		
	////////////////////////////////////////// navigation starts ?>
	<h2 style="float:right; margin:0; padding:0; font-size:12px;">
		<?php echo 'Displaying '.$previous_loc.'-'.$current_loc.' of '.$all; ?>
	</h2>
	<form method="post" action="">
		<div class="tablenav">
			<div class="alignleft">
				<?php if (array_key_exists($del_key, $array[0])) { ?>
					<input value="Delete" class="button-secondary delete" type="submit">
				<?php } ?>
				<?php if ($date_sel!==0){ 

					// get all the dates from the database
					$SQL ='SELECT DISTINCT YEAR(date) as Year, MONTH(date) as Month, ';
					$SQL.='MONTHNAME(date) as MonthName FROM '.$table_name.' ORDER BY date';
					$dates = $wpdb->get_results($SQL, ARRAY_A); 
										
					if (!empty($dates)) { ?>
						<select name="date_sel" id="date_sel" class="postform">
							<?php foreach($dates as $date){
								
								$date_value = $date['Month'].'-'.$date['Year']; // return value
								$selected = $date_value==$date_sel ? 'selected="selected"' : '' ;?>
								
								<option <?php echo $selected; ?> value="<?php echo $date_value; ?>">
									<?php echo __($date['MonthName'],$ck_domain).' '.$date['Year']; ?>
								</option> 
							<?php }	
							$selected = $date_sel=="99-9999" ? 'selected="selected"' : '';
							?>
							<option <?php echo $selected; ?>value="99-9999"><?php _e('Show all dates',$ck_domain);?></option>
						</select>
						<input id="post-query-submit" value="Filter" class="button-secondary" type="submit">
					<?php } ?>
				<?php } ?>	
			</div>
			<?php 
			
			/* 
			if there where no values to display, the "$exit" value would have been set to true earlier 
			so it continues on to display the header layouts and there options etc.
			
			because it also skipped the processing, (would have crashed royally) we MUST exit before output
			
			*/
			if ($exit) {
				// leave but do it pretty like
				?>
				<div class='tablenav-pages'>
				
				</div>
				
				<br class="clear">
			
				</div>
		
				<br class="clear">
		
		
				<table class="widefat">
					<thead>
						<tr>
							<th scope="row" class="check-column">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td valign="top" style="border:0; padding:100px 0; text-align:center;">
							<?php _e('There are no results',$ck_domain); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
				return; 
				}
			?>
			<div class='tablenav-pages'>		
				<?php
								
				if(($listing+1) > 1 && ($listing+1) <= $all_pages){
					// up
					echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo;</a>';
					}
	
				
				if ($all_pages>1){
									
					$startpoint	= $listing-$range>0 ? $listing-$range : 0;
					$endpoint 	= $listing+$range<$all_pages ? $listing+$range+1: $all_pages;
					
					// if range is set to zero, it does not set a range, I.E it prints out as normal
					if ($range==0) {$endpoint=$all_pages;$startpoint=0;}
					
					// holds the min page tab number that will display the first page tab 
					elseif ($startpoint>0) echo '<a href="'.$pagelink.'?'.$queryString.'=0'.$args.'" class="page-numbers" title="page '.($all_pages).'" alt="page '.($all_pages).'">'.__('First',$ck_domain).'</a>  ... ';

					// goto page
					for($i=$startpoint;$i<$endpoint;$i++){
						// echo`s the number also checks to see if this is the current page
						echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
						}
					// holds the min page tab number that will display the last page tab 
					if ($endpoint<$all_pages) echo ' ... <a href="'.$pagelink.'?'.$queryString.'='.($all_pages-1).$args.'" class="page-numbers" title="page '.($all_pages).'" alt="page '.($all_pages).'">'.__('Last',$ck_domain).'</a>';
					}

				if(($listing+1) < $all_pages){
					// down
					echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">&raquo;</a>';
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
					<?php foreach(array_keys($array[0]) as $title) if ($title!==$del_key) echo '<th scope="col">'.$title.'</th>'; else echo '<th scope="col">&nbsp;</th>'; ?>		
				</tr>
			</thead>
			<tbody>
			
			<?php for($i=$start;$i<($start+$limit);$i++){ ?>
				<?php if (empty($result[$i])) break;	?>
				<tr>
					<?php 
			
					foreach ($result[$i] as $key=>$data) {
							
						if ($i%2) $class='alternate';
						else $class='';
							
						// check to see if the rule exists, which is defined by its key name
						if (is_array($rules) && array_key_exists($key, $rules)){
							// if it is maintain layout and call function
							
							echo '<td class="tableContents '.$class.'" valign="top">';
							call_user_func($rules[$key], stripslashes($data));
							echo '</td>';
							}
							
						elseif ($key==$del_key){ 
							
							?>
							<th scope="row" class="check-column <?php echo $class; ?>">
								<input name="delete[]" value="<?php echo $result[$i][$del_key];?>" type="checkbox">
							</th>
						<?php }

						else echo '<td class="tableContents '.$class.'" valign="top">'.stripslashes($data).'</td>'; 
						}
					?>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		
		<?php ////////////////////////////////////////// navigation starts ?>
					
			<div class="tablenav">
				<?php
				if (array_key_exists($del_key, $array[0])) {?>
					<div class="alignleft">
						<input value="Delete" class="button-secondary delete" type="submit">
					</div> 
				<?php } ?>
			
				<div class='tablenav-pages'>
					<?php
					
					
					if(($listing+1) > 1 && ($listing+1) <= $all_pages){
						// up
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo;</a>';
						}
		
					
					if ($all_pages>1){

						
						$startpoint	= $listing-$range>0 ? $listing-$range : 0;
						$endpoint 	= $listing+$range<$all_pages ? $listing+$range+1: $all_pages;
						
						// if range is set to zero, it does not set a range, I.E it prints out as normal
						if ($range==0) {$endpoint=$all_pages;$startpoint=0;}
						
						// holds the min page tab number that will display the first page tab 
						elseif ($startpoint>0) echo '<a href="'.$pagelink.'?'.$queryString.'=0'.$args.'" class="page-numbers" title="page '.($all_pages).'" alt="page '.($all_pages).'">'.__('First',$ck_domain).'</a>  ... ';
						
						// goto page
						for($i=$startpoint;$i<$endpoint;$i++){
							// echo`s the number also checks to see if this is the current page
							echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
							}
						// holds the min page tab number that will display the last page tab 
						if ($endpoint<$all_pages) echo ' ... <a href="'.$pagelink.'?'.$queryString.'='.($all_pages-1).$args.'" class="page-numbers" title="page '.($all_pages).'" alt="page '.($all_pages).'">'.__('Last',$ck_domain).'</a>';
						}

					if(($listing+1) < $all_pages){
						// down
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">&raquo;</a>';
						}

					?>	
				</div>

				<br class="clear">
				
			</div>
			
		<br class="clear">	
			
		<?php ///////////////////////////////////////// navigation ends	?>
	</form>
	<?php } ?>