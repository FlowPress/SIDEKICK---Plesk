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

		// $license   = new pm_License();
		// $keyNumber = 'APS.02960584.0008';
		// $license   = new pm_License($keyNumber);
		// $props     = $license->getProperties();
		// var_dump($props);



		$licenses = pm_License::getAdditionalKeysList('ext-sidekick');

		if (count($licenses) == 0) {
			$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
		} else {
			$license = reset($licenses);
			if (isset($license['key-body'])) {
				// var_dump($license);
				$activation_id = base64_decode($license['key-body']);

				foreach ($_POST as $key => $value) {
					var_dump($key);
					if (strpos($key, 'sidekick_activated_') !== false && $value == '1') {
						// activate this domain
						list($blah,$domain_id) = explode('sidekick_activated_',$key);
						$instanceId = $domain_id;
						var_dump('Enable ' . $domain_id);
						$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $instanceId, "--"];
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "install", "sidekick","--activate","--force"]));
						} catch (Exception $e){
							var_dump($e->getMessage());
						}
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "update", "sk_activation_id", $activation_id]));
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "sk_activation_id"]));
					} else if (strpos($key, 'sidekick_activated_') !== false && $value == '0') {
						list($blah,$domain_id) = explode('sidekick_activated_',$key);
						$instanceId = $domain_id;
						var_dump('Disable' . $domain_id);
						$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $instanceId, "--"];
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "deactivate", "sidekick","--uninstall"]));
						} catch (Exception $e){
							var_dump($e->getMessage());
						}
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["option", "delete", "sk_activation_id"]));
						} catch (Exception $e){
							var_dump($e->getMessage());
						}

					}
					var_dump($result);
				}

			} else {
				$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
				return;
			}

		}

		$this->_status->addMessage('info', 'Successfully activated!');
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

		// $inst = $this->_dbAdapter->query("SELECT * FROM WordpressInstances");
		// var_dump($inst);

		$this->view->wp_installs = array();
		foreach ($installs as $key => $wp) {

			$domain = pm_Domain::getByDomainId($wp['domainId'])->getName();

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['domainId'], "--"];
			$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
			// $key = $result['stdout'];

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['domainId'], "--"];
			// $result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "is-installed", "sidekick"]));
			try{
				// $result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "sk_activation_id"]));
				$result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "is-installed", "sidekick"]));
				$sk_activation_id = $result['stdout'];
			} catch(Exception $e){

			}

			$this->view->wp_installs[] = array(
				'id' => $wp['id'],
				'domainId' => $wp['domainId'],
				'domain' => $domain,
				'path' => $wp['path'],
				'apsInstanceId' => $wp['apsInstanceId'],
			);
			// var_dump($sk_activation_id);
			$form->addElement('checkbox', 'sidekick_activated_' . $wp['domainId'], array(
				'label' => $domain,
				'value' =>  pm_Settings::get('sidekick_activated_' . $wp['domainId']),
				'checked' =>  (intval($sk_activation_id)) ? true: false,
			));
		}

		$this->view->licenses = pm_License::getAdditionalKeysList('ext-sidekick');

		$form->addControlButtons(array(
			'cancelLink' => pm_Context::getModulesListUrl(),
			'sendTitle' => 'Update',
			'cancelTitle' => 'Cancel'
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
