<?php
class SubadminController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Subadmin";

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
		$subadmin_Obj = new Gbc_Model_DbTable_Subadminuser();

		try{

			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$result=array();

				

			$result=$subadmin_Obj->fetchAll($subadmin_Obj->select()
			->setIntegrityCheck(false)
			->from(array('sub_admin_users')));
				

	 	$this->view->result=$result;
	 	 
	 	//echo "<pre>";
	 	//print_r($result);exit;
	 		
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
			
			
			
			



	}
	public function editAction()
	{
		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$db = Zend_Db_Table::getDefaultAdapter();
			
				
			$this->_helper->layout()->setLayout("admindashbord");//dashboard
		 if($this->_request->isPost())
		 {

		 	$uid=$_POST["id"];
		 	$Firstname=$_POST["first_name"];
		 	$Email=$_POST["email"];
		 		
		 	$Mobile=$_POST["mob"];
		 	$Createdon=$_POST["created_on"];
		 		
		 		
		 	if(isset ($uid) && ($uid!=null))
		 	{
		 		$data=array("first_name"=>$Firstname,"email"=>$Email,"mob"=>$Mobile,"created_on"=>$Createdon);
		 		$userObj->update($data,$db->quoteInto("id=?",$uid));
		 		//$authUserNamespace->messageset="Updated successfully";
		 		///$this->_redirect('/admin/persondetail');

		 		if(empty($userObj)) {
		 			//die('Zero rows affected');
		 			$message='Zero rows affected';
		 			$this->view->message=$message;
		 		}
		 		else{
		 			$message="Updated successfully";
		 			$this->view->message=$message;exit;
		 		}

		 	}
		 }
		 	
		 	
		 	
		 	
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function setpermissionAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$db = Zend_Db_Table::getDefaultAdapter();
			
			$this->_helper->layout()->setLayout("admindashbord");//
			if($this->_request->isPost())
			{
				$uid=$_POST["id"];
				if(isset ($uid) && ($uid!=null))
				{
					$data=array("first_name"=>$Firstname,"email"=>$Email,"mob"=>$Mobile,"created_on"=>$Createdon);
					$userObj->update($data,$db->quoteInto("id=?",$uid));
				}


				if (empty($userObj)) {
					//die('Zero rows affected');
					$message='Zero rows affected';
					$this->view->message=$message;
				}
				else{
					$message="Updated successfully";
					$this->view->message=$message;exit;
				}
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}


}