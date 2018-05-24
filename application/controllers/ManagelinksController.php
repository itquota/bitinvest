<?php
class ManagelinksController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");




	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Navigation";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				//$loggedIn==true;
			}
			else 
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
		$cntry_obj = new Gbc_Model_DbTable_Countries();
			
		try{
				
		 $this->_helper->layout()->setLayout("admindashbord");
		 	
	 	$result=$cntry_obj->fetchAll($cntry_obj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('c'=>'countries'))
	 	);


	 	 
	 	$this->view->result=$result;

		}


		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}