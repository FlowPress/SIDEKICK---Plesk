<?php
// Copyright 1999-2015. Parallels IP Holdings GmbH.

class IndexController extends pm_Controller_Action
{

	public function init()
	{
		parent::init();

		// Init title for all actions
		$this->view->pageTitle = 'Sidekick';
	}

	public function indexAction()
	{
		// Default action will be formAction
		// $this->_forward('form');
		$this->_forward('wordpress');
	}


	public function wordpressAction(){

		require_once __DIR__ . '/../scripts/sidekick_api.php';

		// $this->view->key = pm_Settings::get('sidekick_activation_id');

		$form = new pm_Form_Simple();

		$sidekick = new sidekick;

		// if ($this->view->key) {
		// $this->setupFormDeactivation($form);
		// } else {
		$this->setupFormActivation($form);
		// }

		if ($this->getRequest()->isPost()){
			$this->processActivations($sidekick,$form);
		}



		// var_dump($db);


		// var_dump($test);

		// $res = pm_ApiCli::callSbin('wpmng', array_merge([
		// '--user=' . $subscription->getSysUser(),
		// '--php=' . $subscription->getPhpCli(),
		// '--',
		// '--path=' . $fileManager->getFilePath($wordpress['path']),
		// ], $args));
		// if (0 !== $res['code']) {
		// throw new pm_Exception($res['stdout'] . $res['stderr']);
		// }

		// $this->_getDbAdapter();
		// var_dump($this->_dbAdapter);

		// $test = $this->_dbAdapter->query("SELECT * FROM WordpressInstances");
		// var_dump($test);

		// $this->view->wp_installs = 'a';
		// $list = new Modules_SecurityAdvisor_View_List_Domains($this->view, $this->_request);
		// $list = new Modules_SecurityAdvisor_View_List_Wordpress($this->view, $this->_request);


		$this->view->form = $form;
	}

	private function processActivations($sidekick,$form){
		// var_dump('processActivations');
		// var_dump($sidekick);
		// var_dump($form);
		// var_dump($sidekick);
		// var_dump($form);

		// var_dump($_POST);

		$license   = new pm_License();
		$keyNumber = 'APS.02960584.0008';
		$license   = new pm_License($keyNumber);
		$props     = $license->getProperties();
		var_dump($props);


		foreach ($_POST as $key => $value) {
			if (strpos($key, 'sidekick_activated_') !== false && $value == '1') {
				// activate this domain
				list($blah,$domain_id) = explode('sidekick_activated_',$key);
				// var_dump($domain_id);

				//  plesk bin wp_instance --get-list
				//  Modules_SecurityAdvisor_WordPress::call('wp-cli', $wordpress['id'], $args);

				$instanceId = 1;
				$instanceId = $domain_id;
				$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $instanceId, "--"];
				$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
				$result = pm_ApiCli::call('extension', array_merge($args, ["option", "update", "test_key", "bart"]));
				$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "test_key"]));

				// 	 var_dump($result);


			}
		}

		// var_dump($form->getValues());

		// var_dump($form->getValue('sidekick_activated_1'));
		// var_dump($form->getValue('sidekick_activated_2'));

		// $sidekick = new sidekick;
		// $sidekick->email = $form->getValue('sk_email');
		// $sidekick->password = $form->getValue('sk_password');

		// pm_Settings::set('sk_email', $form->getValue('sk_email'));
		// if ($form->getValue('sk_password')) {
		// 	pm_Settings::set('sk_password', $form->getValue('sk_password'));
		// }
		//

		// 		$this->_status->addMessage('info', 'Successfully activated!');p

		// if ($msg = $sidekick->login()) {
		// 	if ($msg2 = $sidekick->generate_key()){
		// 		$this->_status->addMessage('info', 'Successfully activated!');
		// 	} else {
		// 		$this->_status->addMessage('error', 'Error generating a key. ' . $msg2);
		// 	}
		// } else {
		// 	$this->_status->addMessage('error', 'Error logging in. ' . $msg);
		// }
	}

	// private function setupFormDeactivation($form){
	// 	$form->addControlButtons(array(
	// 		'sendTitle' => 'Deactivate',
	// 		'cancelHidden' => true
	// 		));
	// }
	//
	private function setupFormActivation($form){
		// var_dump('setupFormActivation');

		$fileManager = new pm_ServerFileManager();
		$dbName = $fileManager->joinPath(PRODUCT_VAR, 'modules', 'wp-toolkit', 'wp-toolkit' . '.sqlite3');
		$db =  new Zend_Db_Adapter_Pdo_Sqlite(['dbname' => $dbName]);
		$installs = $db->fetchAll("SELECT * FROM Instances");
		// var_dump($installs);

		$this->view->wp_installs = array();
		foreach ($installs as $key => $wp) {

			$domain = pm_Domain::getByDomainId($wp['domainId'])->getName();

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['domainId'], "--"];
			$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
			$key = $result['stdout'];

			$this->view->wp_installs[] = array(
				'id' => $wp['id'],
				'domainId' => $wp['domainId'],
				'domain' => $domain,
				'path' => $wp['path'],
				'apsInstanceId' => $wp['apsInstanceId'],
			);

			$form->addElement('checkbox', 'sidekick_activated_' . $wp['domainId'], array(
				'label' => $domain,
				'value' =>  pm_Settings::get('sidekick_activated_' . $wp['domainId']),
				'checked' =>  ($key) ? true: false,
			));
		}

		$form->addControlButtons(array(
			'cancelLink' => pm_Context::getModulesListUrl(),
			'sendTitle' => 'Activate',
			'cancelTitle' => 'Deactivate'
			)
		);
	}

	function get_wordpress_installs(){
		// require_once('wp.php');
		// new Modules_SecurityAdvisor_Helper_WordPress_Extension();

		// require_once('')
		// var_dump(Modules_SecurityAdvisor_Helper_WordPress::get()->getNotSecureCount());
		// var_dump(Modules_SecurityAdvisor_Helper_WordPress);
		// $Modules_SecurityAdvisor_Helper_WordPress_Plesk = new Modules_SecurityAdvisor_Helper_WordPress_Plesk;

		// $sidekickWP = new sidekickWP;
		// var_dump($sidekickWP);
		// Modules_SecurityAdvisor_WordPress::call('wp-cli', $wordpress['id'], $args);

		// return array('asd');
	}

	function get_license_key(){
		// https://docs.plesk.com/en-US/12.5/extensions-guide/retrieving-plesk-license-information.75339/
	}




}
