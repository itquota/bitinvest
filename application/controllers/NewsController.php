<?php

class NewsController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - News";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('19',$user_id);
		if(!empty($data1->view)&&($data1->view==1) || $authUserNamespace->user=='admin')
		{
		
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$NewsObj = new Gbc_Model_DbTable_News();

		try {
				
			$this->_helper->layout()->setLayout("admindashbord");//dashboard



			$result=$NewsObj->fetchAll($NewsObj->select()
			->setIntegrityCheck(false)
			->from(array('news'))
			->order('id DESC')
			);
				

			$this->view->result = $result;
			//echo "<pre>";
			//print_r($result);exit;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

}




