<?php

require_once('sdk.php');


// Copyright 1999-2015. FlowPress Inc.
pm_Context::init('sidekick');

// Params needed email, password, and optionally subscription id

echo "INSTALL SIDEKICK\n";
var_dump($argv);

if (!isset($argv[1]) || !isset($argv[2])) {
	echo "Missing SIDEKICK credentials.";
	exit(1);
}


require_once('sidekick_api.php');

$sidekick = new sidekick;
$sidekick->email = $argv[1];
$sidekick->password = $argv[2];

if ($sidekick->login()) {
	$sidekick->generate_key();
}


