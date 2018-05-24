<?php
class OutputController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$username=$authUserNamespace->user;
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		//if($this->_request->isPost())
		//{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$url= BASE."/Dailyearningapi2?username=".$username;
	//	echo $url;
		
		$result=$common_obj->call_curl($url);
	//	echo "yes";
	//	print_r($result);
	//	exit;
		$result=(array)json_decode($result,true);

		//$records_per_page = $this->_request->getParam('getPageValue',10);
		//$this->view->records_per_page = $records_per_page;

		$this->view->result=$result['data'];
		$this->view->title="Gainbitcoin Phase 2 - Daily Output";
		

	}

}