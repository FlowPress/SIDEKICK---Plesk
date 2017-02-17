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

		$this->setupFormActivation($form);

		if ($this->getRequest()->isPost()){
			$this->processActivations($sidekick,$form);
		}
		$this->view->form = $form;
	}

	private function processActivations($sidekick,$form){

		$licenses = pm_License::getAdditionalKeysList('ext-sidekick');

		if (count($licenses) == 0) {
			$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
		} else {
			$license = reset($licenses);
			if (isset($license['key-body'])) {
				// var_dump($license);
				$activation_id = base64_decode($license['key-body']);

				foreach ($_POST as $key => $value) {
					// var_dump($key);
					if (strpos($key, 'sidekick_activated_') !== false && $value == '1') {
						// activate this domain
						list($blah,$domain_id) = explode('sidekick_activated_',$key);
						$instanceId = $domain_id;
						// var_dump('Enable ' . $domain_id);
						$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $instanceId, "--"];
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "install", "sidekick","--activate","--force"]));
						} catch (Exception $e){
							// var_dump($e->getMessage());
						}
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "update", "sk_activation_id", $activation_id]));
						$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "sk_activation_id"]));
					} else if (strpos($key, 'sidekick_activated_') !== false && $value == '0') {
						list($blah,$domain_id) = explode('sidekick_activated_',$key);
						$instanceId = $domain_id;
						// var_dump('Disable' . $domain_id);
						$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $instanceId, "--"];
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "deactivate", "sidekick","--uninstall"]));
						} catch (Exception $e){
							// var_dump($e->getMessage());
						}
						try{
							$result = pm_ApiCli::call('extension', array_merge($args, ["option", "delete", "sk_activation_id"]));
						} catch (Exception $e){
							// var_dump($e->getMessage());
						}

					}
					// var_dump($result);
				}

			} else {
				$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
				return;
			}

		}

		$this->_status->addMessage('info', 'Successfully updated!');
	}

	private function setupFormActivation($form){

		$fileManager = new pm_ServerFileManager();
		$dbName = $fileManager->joinPath(PRODUCT_VAR, 'modules', 'wp-toolkit', 'wp-toolkit' . '.sqlite3');
		$db =  new Zend_Db_Adapter_Pdo_Sqlite(['dbname' => $dbName]);
		$installs = $db->fetchAll("SELECT * FROM Instances");


		$this->view->wp_installs = array();
		foreach ($installs as $key => $wp) {

			$domain = pm_Domain::getByDomainId($wp['domainId'])->getName();

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['domainId'], "--"];
			$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));
			// $key = $result['stdout'];

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['domainId'], "--"];
			// $result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "is-installed", "sidekick"]));
			try{
				$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "sk_activation_id"]));
				// $result = pm_ApiCli::call('extension', array_merge($args, ["plugin", "is-installed", "sidekick"]));
				// var_dump($result);
				$sk_activation_id = $result['stdout'];
			} catch(Exception $e){
				// var_dump($e->getMessage());
			}
			// var_dump($sk_activation_id);

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
}
