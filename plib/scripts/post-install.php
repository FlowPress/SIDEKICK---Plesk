<?php

$today = date("F j, Y, g:i a");                 // March 10, 2001, 5:16 pm


file_put_contents('/tmp/log.txt',"$today - Installed\n");

pm_Context::init('sidekick');


$task = new pm_Scheduler_Task();

$existing_task = $task->getSchedule();
var_dump($existing_task);

if (!$existing_task) {
	$task->setSchedule(pm_Scheduler::$EVERY_MIN);
	$task->setCmd('sidekick-ping.php');
}





// $task->setArguments(array('john', 'robert', 'ivan'));
pm_Scheduler::getInstance()->putTask($task);


// pm_Scheduler::getInstance()->putTask($task);
$taskId = $task->getId();
echo "task - ";
var_dump($taskId);
pm_Settings::set('sidekick_scheduled_task_id', $taskId);
