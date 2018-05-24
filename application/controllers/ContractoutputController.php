<?php
class ContractoutputController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$username=$authUserNamespace->user;
		$invoiceId='1438846858_759866298';
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$url= BASE."/Contractoutputapi?username=".$username."&invoiceId=".$invoiceId;
		$result=$common_obj->call_curl($url);
		$result=(array)json_decode($result,true);
		//print_r($result);exit;
		//print_r($result['data']);exit;
		$this->view->result=$result;
	}
}