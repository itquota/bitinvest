<?php

class GauthenticatorController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

	}

	public function indexAction(){

		$this->_helper->layout()->setLayout("dashbord");


		//$authUserNamespace->user_id=
	}
}