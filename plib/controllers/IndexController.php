<?php
// Copyright 1999-2015. Parallels IP Holdings GmbH.

class IndexController extends pm_Controller_Action
{

	public function init()
	{
		parent::init();

		if (!pm_Session::getClient()->isAdmin()) {
			throw new pm_Exception('Permission denied');
		}

		// Init title for all actions
		$this->view->pageTitle = 'Sidekick';
	}

	public function indexAction()
	{
		// Default action will be formAction
		$this->_forward('wordpress');
	}


	public function wordpressAction(){

		$form = new pm_Form_Simple();

		$this->setupFormActivation($form);

		if ($this->getRequest()->isPost()){
			$this->processActivations($form);
		}
		$this->view->form = $form;
	}

	private function processActivations($form){

		$licenses = pm_License::getAdditionalKeysList('ext-sidekick');

		if (count($licenses) == 0) {
			$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
		} else {
			$license = reset($licenses);
			if (isset($license['key-body'])) {

				$activation_id = base64_decode($license['key-body']);
				$activation_id = 'ab9bfce9-60fa-4bf3-9a22-0711a336bd3e';

				foreach ($_POST as $key => $value) {
					// var_dump($key);
					if (strpos($key, 'sidekick_activated_') !== false && $value == '1') {
						// activate this domain
						list($blah,$domain_id) = explode('sidekick_activated_',$key);
						$instanceId = $domain_id;
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
				}

			} else {
				$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
				return;
			}

		}

		$this->_status->addMessage('info', 'Successfully updated!');
	}

	private function setupFormActivation($form){

		$installed_extensions = pm_ApiCli::call('extension', array('--list'));
		// var_dump($installed_extensions);
		if (strpos( $installed_extensions['stdout'], 'WordPress Toolkit' ) === false) {
			$this->_status->addWarning('Toolkit not installed');
			$this->view->toolkit_not_there = true;
			return;
		}

		$fileManager = new pm_ServerFileManager();
		$dbName = $fileManager->joinPath(PRODUCT_VAR, 'modules', 'wp-toolkit', 'wp-toolkit' . '.sqlite3');
		$db =  new Zend_Db_Adapter_Pdo_Sqlite(['dbname' => $dbName]);
		$installs = $db->fetchAll("SELECT * FROM Instances");

		$sorted_installs = array();
		foreach ($installs as $key => $install) {
			$sorted_installs[$install['domainId']][] = $install;
		}
		// var_dump($sorted_installs);


		$this->view->wp_installs = array();
		foreach ($installs as $key => $wp) {

			$domain = pm_Domain::getByDomainId($wp['domainId'])->getName();
			$sk_activation_id = null;

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['id'], "--"];
			$result = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "siteurl"]));

			$args = ["--call", 'wp-toolkit', "--wp-cli", "-instance-id", $wp['id'], "--"];
			try{
				$result           = pm_ApiCli::call('extension', array_merge($args, ["option", "get", "sk_activation_id"]));
				$sk_activation_id = $result['stdout'];
			} catch(Exception $e){
					// $this->_status->addWarning('Toolkit not installed');
			}

			$this->view->wp_installs[$wp['domainId']][] = array(
				'id' => $wp['id'],
				'domainId' => $wp['domainId'],
				'domain' => $domain,
				'path' => $wp['path'],
				'apsInstanceId' => $wp['apsInstanceId'],
			);

			$form->addElement('checkbox', 'sidekick_activated_' . $wp['id'], array(
				'label' => $domain . $wp['path'],
				'value' =>  pm_Settings::get('sidekick_activated_' . $wp['id']),
				'checked' =>  ($sk_activation_id) ? true: false,
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
