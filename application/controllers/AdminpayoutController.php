<?php

class AdminpayoutController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="")$this->_redirect("/Login");
		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Admin Payout";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('6',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
		//$KitFundMode = getKitFundMode();
		$KitFundMode=$common_obj->getKitFundMode();
		$this->view->KitFundMode=$KitFundMode;
		}
		
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
       
		if($this->_request->isPost())
		{
			 $antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}

			}	
			
			
			
			
				$token=$_POST['token'];
		 	//if($authUserNamespace->token==$token){
			if(!empty($_POST['submit_payout']))
			{
				
				$startdate = date('Y-m-d');
				$file = '';
				$file = $startdate.'_'.time().'.csv';
				
				$result=$common_obj->adminPayout($_FILES,$file);
				$msg=$result;
				$authUserNamespace->msg=$msg;
			}
			else if(!empty($_POST['dis_submit']))
			{
				
				$data = $_POST;
				
				//$ChangeKitFundMode = KitFundUpdate($data);
				$ChangeKitFundMode=$common_obj->KitFundUpdate($data);
				if($ChangeKitFundMode){
					$msg = $ChangeKitFundMode;
					$authUserNamespace->msg=$msg;
				}
				$this->_redirect("/Adminpayout");
				
				
			}
/*
		 	    }else{
		 			//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     //echo json_encode($data);exit;
				     $msg = 'Invalid Request Found.';
				     $authUserNamespace->message=$msg;
		 		}
*/
		}


	}



}
