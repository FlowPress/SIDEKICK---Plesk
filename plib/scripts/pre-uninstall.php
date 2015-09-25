<?php

// Copyright 1999-2015. FlowPress Inc.
pm_Context::init('sidekick');

// Params needed email, password, and optionally subscription id

echo "UNINSTALL SIDEKICK\n";

// Disabled for the time being, relying on pings anyway

// require_once('sidekick_api.php');

// $sidekick = new sidekick;
// if ($sidekick->login()) {
//     $sidekick->delete_key();
// }

$taskId = pm_Settings::get('sidekick_scheduled_task_id');
$task = pm_Scheduler::getInstance()->getTaskById($taskId);
pm_Scheduler::getInstance()->removeTask($task);

exit(1);
