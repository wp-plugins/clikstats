<?php

//////////////// FRONT END STARTS ///////////////////////////////////////////////////////////

/*

This file handles all display tasks 


*/

function ckStat_setFilter($text){
	global $post;
	
	$processor_file = get_option('clikStats_enviroment')."ck.php";
	$text = str_replace(' href="', ' href="'.$processor_file.'?Ck_id='.$post->ID.'&Ck_lnk=', $text);
	
	return $text;
	}

?>