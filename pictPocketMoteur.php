<?php
// PickPocketMoteur


$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root.'/wp-load.php'))
{
	require_once($root.'/wp-load.php');
}
else
{
	require_once($root.'/wp-config.php');
}
$pic= $_GET['pic'];
$last_ref = (isset($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER']) : '');
global $wpdb;
$table_name = $wpdb->prefix . "pictPocket";

//gestion du visites
$qry = $wpdb->get_results("SELECT visited FROM $table_name WHERE url='$ref' ");

if ($qry == null)
{
	//test			
	$visited = 1;
	$blogage = 0;
	$ip = $_SERVER['REMOTE_ADDR'];
	$timestamp  = current_time('timestamp');
	
	$insert = "INSERT INTO " . $table_name .
            " ( url, visited, ip, blocage,last_ref,last_pic, timestamp) " .
            "VALUES ('$ref','$visited','$ip','$blocage','$last_ref','$pic','$timestamp')";
	$results = $wpdb->query( $insert );
}
else
{

$visited=$qry[0]->visited+1;

		

		$insert = "UPDATE " . $table_name .
}


$qry = $wpdb->get_results("SELECT blocage FROM $table_name WHERE url='$ref'");

 if ($qry[0]->blocage == 'bloc') 


?>