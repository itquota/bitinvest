<?php
class ProfileerrorController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		
			$this->_helper->layout()->setLayout("login");
			
		
		
	}
	
	
	public function errormsgAction()
	{
		
		$this->_helper->layout()->setLayout("login");
			
		
		
	}
	
}