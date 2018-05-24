<?php
class ContractsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$username=$authUserNamespace->user;
		$url= BASE."/Usercontract?username=".$username;
		$result=$common_obj->call_curl($url);
		$result=(array)json_decode($result,true);
		$this->view->result=$result;

	}

}