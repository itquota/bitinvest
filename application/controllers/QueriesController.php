<?php
class QueriesController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
			
	}

	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('30',$user_id);
		if(!empty($data1['view']) && $data1['view']==1 || (($authUserNamespace->user=="admin")))
		{
		
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$queriesObj = new Gbc_Model_DbTable_Helpquery();

		try {
				
			$this->_helper->layout()->setLayout("admindashbord");

			$result=array();

			$result=$queriesObj->fetchAll($queriesObj->select()
			->setIntegrityCheck(false)
			->from(array('help_query'))
			->order('created_on DESC'));


	 	$this->view->result=$result;
	 	//echo "<pre>";
	 	//	print_r($result);exit;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}


}

