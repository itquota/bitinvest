<?php
class MiningoutputController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		try
		{
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			$username=$authUserNamespace->user;
			//$invoiceId=$_GET['invoiceId'];
			$invoiceId=$_POST['invoiceId'];
			
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$url= BASE."/Miningoutputapi?username=".$username."&invoiceId=".$invoiceId;
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);

			$this->view->result=$result;


		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
}