<?php

class Error404Controller extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}


	public function indexAction()
	{
		//echo "in";exit;
		$this->_helper->layout()->setLayout("admindashbord");
	}
}