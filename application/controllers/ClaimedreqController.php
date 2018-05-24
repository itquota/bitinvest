<?php
class ClaimedreqController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('37',$user_id);
			if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
			{
			
			}
			else
			{
				
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}


				


		 $this->_helper->layout()->setLayout("admindashbord");

		 	
			if($this->_request->isXmlHttpRequest()){

			}
			else {

			}

		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
}