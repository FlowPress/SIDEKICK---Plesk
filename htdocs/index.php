<?php 

if (!pm_Session::getClient()->isAdmin()) {
	throw new pm_Exception('Permission denied');
}

pm_Context::init('sidekick');
$application = new pm_Application();
$application->run();