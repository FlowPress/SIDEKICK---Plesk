<?php 

$activation_id = pm_Settings::get('sidekick_activation_id');			

if ($activation_id) {
	$today = date("F j, Y, g:i a");                 // March 10, 2001, 5:16 pm
	file_put_contents('/tmp/log.txt',"$today - test\n");
	require_once('sidekick_api.php');
	$sidekick = new sidekick;
	$sidekick->ping();
}
