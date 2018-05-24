<?php

class AllcontractController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Contracts";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('2',$user_id);
		if(!empty($data1->view) && ($data1->view==1) || $authUserNamespace->user=='admin')
		{
		 
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$editcontractObj  = new Gbc_Model_DbTable_Contracts ();

		try {
				
			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$result=array();
				
			$result=$editcontractObj->fetchAll($editcontractObj->select()
			->setIntegrityCheck(false)
			->from(array('contracts'))
			->where("contract_type in('SHA','hardware')")
			->orwhere("contract_type= ?",hardware)
			->order(array('contracts.contract_type desc','contracts.total_price desc'))
			
			);
			
			
	 	$this->view->result=$result;
	 	//echo"<pre>";
	 	//	print_r($result);exit;
	 	 
	 	 
	 		

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}



	}
	/*public function SubmitAction()
	 {


		try{
			
		//dashboard
		if($this->_request->isPost('submit'))
		{
		echo "in";exit;
		$contractid=$_POST["contract_id"];
		$contractname=$_POST["contract_name"];
		$contractqty=$_POST["contract_qty"];
		$description=$_POST["description"];
		$totalprice=$_POST["total_price"];
		$contracttype=$_POST["contract_type"];
			
			
			
			
			
			
		$editcontractObj  = new Gbc_Model_DbTable_Contract();

		$data=array("contract_name"=>$contractname ,"contract_qty"=>$contract_qty,"description"=>$description,"total_price"=>$totalprice,"contract_type"=>$contracttype);
		$editcontractObj->insert($data);
		//$authUserNamespace->messageset="Updated successfully";
		///$this->_redirect('/admin/persondetail');

		if (empty($editcontractObj)) {
		//die('Zero rows affected');
		$message='Zero rows affected';
		$this->view->message =$message;
		}
		else{
		$message="updated  successfully";
		$this->view->message =$message;exit;
		}

			
		}
		 
			
			
			
		}
		catch(Exception $e)
		{
		$e->getMessage();exit;
		}
		}*/



}
