<?php
class LoginnewController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("login");


	}

}