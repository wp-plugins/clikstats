<?php
/*
Plugin Name: Clik stats
Plugin URI: http://adriancallaghan.co.uk/clikStats/
Description: This plugin records the amount of anchor point clicks within the blog, and provides who, when, what information.
Version: 0.7
Author: Adrian Callaghan
Author URI: http://adriancallaghan.co.uk
*/

/*  Copyright 2009  Adrian Callaghan  (email : admin@adriancallaghan.co.uk)

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



$table_name = $wpdb->prefix."clik_stats"; // database name
$ck_domain	= 'ClikStats';				  // language domain


// register the install function
include('ck_inst.php');
register_activation_hook(__FILE__,'ckStat_install');


// register the pages to the hook
include('ck_admin.php');
add_action('admin_menu','ckStat_setAdmin');


// register the view
include('ck_view.php');
add_filter('the_content','ckStat_setFilter');

?>