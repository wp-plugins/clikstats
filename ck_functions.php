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
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&date='.date($date).'" class="info">'.$date.'</a>';
	}

// click to timeline that handles ip		
function addIpAnchor($ip){
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&ip='.$ip.'" class="info">'.$ip.'</a>';
	}

// click to timeline that handles url		
function addUrlAnchor($url){
	echo '<a href="'.$_SERVER['PHP_SELF'].'?page=show&url='.$url.'" class="info">'.$url.'</a>';
	}

	
function pagenateMonthYearPreProccessor(){
	// returns an assoc array containing the date selection in the correct format for pagenate
	// also handles the forwarding
	$_GET['date_sel']=!empty($_POST['date_sel'])?$_POST['date_sel']:mysql_real_escape_string($_GET['date_sel']);
	if (!empty($_GET['date_sel'])) list($month, $year) = explode('-', $_GET['date_sel']);
	else {$month = date('n'); $year = date('Y'); $_GET['date_sel']=$month.'-'.$year;}
	return array('month'=>$month,'year'=>$year,'argument'=>mysql_real_escape_string($_GET['date_sel']));
	}
	
function pagenate($array, $limit_per_page, $queryString, $rules=0, $date_sel=0) {
	
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
	
	*/
	
	// Special wordpress reserved operands ///////////////////////////////////////
	$del_key ="_del"; // this key is reserved and activates the deletion tick boxes used in wp (can be altered)

	
	
	
	
	// output
	
	// catch bad thangs
	if (intval($limit_per_page)<1 || empty($array)) return;
		
	// pagination internal settings
	$result = $array;
	$limit = intval($limit_per_page);
	$pagelink = $_SERVER['PHP_SELF']; 

	

	$listing = intval($_GET[$queryString]);
	$start = $listing * $limit;

	$all = count($result);
	$all_pages = ceil($all / $limit);
	
	// this forwards any criteria associated with the search by adding it onto the end of the hyperlink
	$args="";
	foreach ($_GET as $key=>$val) $args.= $key!=$queryString ? "&".$key."=".$val : "" ;

	
	if($all>0){
		if(($listing+1) > $all_pages){$listing=0;}
		$of = 0;
		
		////////////////////////////////////////// navigation starts ?>
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

			<div class='tablenav-pages'>		
				<?php
				
				
				if(($listing+1) > 1 && ($listing+1) <= $all_pages){
					// up
					echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; '.__('Previous',$ck_domain).'</a>';
					}

				if ($all_pages>1){
					// goto page
					for($i=0;$i<$all_pages;$i++){

						// echo`s the number also checks to see if this is the current page
						echo $listing==$i ? '<span class="page-numbers current">'.($i+1).'</span>' : '<a href="'.$pagelink.'?'.$queryString.'='.$i.$args.'" class="page-numbers" title="page '.($i+1).'" alt="page '.($i+1).'">'.($i+1).'</a>';
						}
					}

				if(($listing+1) < $all_pages){
					// down
					echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">'.__('Next',$ck_domain).' &raquo;</a>';
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
													
						// check to see if the rule exists, which is defined by its key name
						if (is_array($rules) && array_key_exists($key, $rules)){
							// if it is maintain layout and call function
							
							echo '<td class="tableContents" valign="top">';
							call_user_func($rules[$key], stripslashes($data));
							echo '</td>';
							}
							
						elseif ($key==$del_key){ ?>
							<th scope="row" class="check-column">
								<input name="delete[]" value="<?php echo $result[$i][$del_key];?>" type="checkbox">
							</th>
						<?php }

						else echo '<td class="tableContents" valign="top">'.stripslashes($data).'</td>'; 
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
						echo '<a class="prev page-numbers" href="'.$pagelink.'?'.$queryString.'='.($listing-1).$args.'" title="previous" alt="previous">&laquo; '.__('Previous',$ck_domain).'</a>';
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
						echo ' <a href="'.$pagelink.'?'.$queryString.'='.($listing+1).$args.'" class="next page-numbers" title="next" alt="next">'.__('Next',$ck_domain).' &raquo;</a>';
						}

					?>
					
				</div>

				<br class="clear">
				
			</div>
			
		<br class="clear">	
			
		<?php ///////////////////////////////////////// navigation ends	?>
	</form>
	<?php
		}
	} ?>