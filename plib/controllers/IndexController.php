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

		$license = $this->getWordPressLicense();

		if (is_null($license)) {
			$this->_status->addMessage('error', 'SIDEKICK license seems to be missing. ');
		} else {
			$activation_id = base64_decode($license);

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
			$this->_status->addMessage('info', 'Successfully updated!');

		}

		$this->_helper->json(['redirect' => pm_Context::getBaseUrl()]);

	}

	private function _getBuyUrl(){
		if (method_exists('pm_Context', 'getBuyUrl')) {
			return pm_Context::getBuyUrl();
		}
		return (new SimpleXMLElement(file_get_contents(pm_Context::getPlibDir() . '/meta.xml')))->buy_url;
	}

	private function setupFormActivation($form){

		$installed_extensions = pm_ApiCli::call('extension', array('--list'));
		$wp_tool_kit_license = (bool)(new pm_License())->getProperty('wordpress-toolkit');

		if (strpos( $installed_extensions['stdout'], 'WordPress Toolkit' ) === false || !$wp_tool_kit_license) {
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

		$this->view->wp_installs = array();
		foreach ($installs as $key => $wp) {

			$domain = pm_Domain::getByDomainId($wp['domainId'])->getName();
			$sk_activation_id = null;

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

		$this->view->license = $this->getWordPressLicense();

		$form->addControlButtons(array(
			'cancelLink' => pm_Context::getModulesListUrl(),
			'sendTitle' => 'Update',
			'cancelTitle' => 'Cancel'
			)
		);

		$this->view->buy_link = $this->_getBuyUrl();
	}

	private function getWordPressLicense()
	{
		$license = $this->getLicense();
		if (is_null($license) || !isset($license['wordpress'])) {
			return null;
		}
		return $license['wordpress'];
	}

	private function getLicense()
	{
		$licenses = pm_License::getAdditionalKeysList('ext-sidekick');
		if (0 == count($licenses)) {
			return null;
		}
		$license = reset($licenses);
		return json_decode($license['key-body'], true);
	}
}
