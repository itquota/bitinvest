<?php
class BinaryearningController extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$result=array();
		$username=$authUserNamespace->user;
		$url= BASE."/Binaryearningapi?username=".$username;

		$result=$common_obj->call_curl($url);
		$result=(array)json_decode($result,true);
			
			


		$this->view->result=$result;
		$this->view->title="Gainbitcoin - Binary outputs";

	}

}

