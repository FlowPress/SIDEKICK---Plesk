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
		$this->_forward('form');
	}

	private function processDeactivation($sidekick,$form){

		$sidekick = new sidekick;
		$sidekick->email = $form->getValue('sk_email');
		$sidekick->password = $form->getValue('sk_password');

		if ($msg = $sidekick->delete_key()){
			$this->_status->addMessage('info', 'Deactivated!');
		} else {
			$this->_status->addMessage('error', 'Error deactivating... ' . $msg);
		}
	}

	private function processActivation($sidekick,$form){

		$sidekick = new sidekick;
		$sidekick->email = $form->getValue('sk_email');
		$sidekick->password = $form->getValue('sk_password');

		pm_Settings::set('sk_email', $form->getValue('sk_email'));
		if ($form->getValue('sk_password')) {
			pm_Settings::set('sk_password', $form->getValue('sk_password'));
		}		

		if ($msg = $sidekick->login()) {
			if ($msg2 = $sidekick->generate_key()){
				$this->_status->addMessage('info', 'Successfully activated!');
			} else {
				$this->_status->addMessage('error', 'Error generating a key. ' . $msg2);
			}
		} else {
			$this->_status->addMessage('error', 'Error logging in. ' . $msg);
		}
	}

	private function setupFormDeactivation($form){
		$form->addControlButtons(array(
			'sendTitle' => 'Deactivate',
			'cancelHidden' => true
			));
	}

	private function setupFormActivation($form){
		$form->addElement('text', 'sk_email', array(
			'label' => 'Sidekick API account e-mail',
			'value' => pm_Settings::get('sk_email'),
			'required' => true,
			'validators' => array(
				array('NotEmpty', true),
				),
			));
		$form->addElement('password', 'sk_password', array(
			'label' => 'Password',
			'description' => '',
			'required' => true,
			'validators' => array(
				array('StringLength', true, array(5, 255)),
				array('NotEmpty', true),
				),
			));

		$form->addControlButtons(array(
			'cancelLink' => pm_Context::getModulesListUrl(),
			'sendTitle' => 'Activate',
			'cancelHidden' => true
			));
	}

	public function formAction(){

		require_once __DIR__ . '/../scripts/sidekick_api.php';

		$this->view->key = pm_Settings::get('sidekick_activation_id');

		$form = new pm_Form_Simple();
		$sidekick = new sidekick;

		if ($this->view->key) {
			$this->setupFormDeactivation($form);
		} else {
			$this->setupFormActivation($form);
		}

		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) && $this->view->key) {

			$this->processDeactivation($sidekick,$form);
			$this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));

		} else if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) && !$this->view->key) {

			$this->processActivation($sidekick,$form);
			$this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));

		}

		$this->view->form = $form;
	}
}










