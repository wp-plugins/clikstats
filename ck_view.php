<?php

//////////////// FRONT END STARTS ///////////////////////////////////////////////////////////

/*

This file handles all display tasks 


*/

function ckStat_setFilter($html){

	global $post;
	
	$processor_file = get_option('clikStats_enviroment')."ck.php";

	preg_match_all("#<[aA] href=[\"'](.*?)[\"']#i", $html, $matches);

	#return "<textarea rows='300'>".print_r($matches,true)."</textarea>"; 

	if ($matches[0]){

		foreach ($matches[0] AS $key=>$entry){
			
			if (!isset($matches[1][$key])){
				continue; // hmmmm
			}
			
			$originalLink = $matches[1][$key];

			// replace anchor points - why we did all this!
			$newLink = $processor_file.'?Ck_id='.$post->ID.'&Ck_lnk='.$matches[1][$key];
			$newLink = str_replace('#', '&Ck_hsh=', $newLink);

			$altered = str_replace($originalLink, $newLink, $entry);
			$html = str_replace($entry, $altered, $html);
		}

	}
	#return "<textarea rows='300'>$html</textarea>"; 
	return $html;
	}

?>
