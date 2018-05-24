<?php
class BinrynetworkController extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
	
	}

	public function indexAction(){
		try
		{
			$this->view->title="Gainbitcoin - Users";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
				$this->_helper->layout()->setLayout("admindashbord");//dashboard
				
			
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
}