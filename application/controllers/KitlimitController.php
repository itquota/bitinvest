<?php
class KitlimitController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$contracts_obj = new Gbc_Model_DbTable_Contracts();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('33',$user_id);
			$db = Zend_Db_Table::getDefaultAdapter();
			$this->_helper->layout()->setLayout("admindashbord");
			if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
			{

			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}



			$allContracts = $contracts_obj->fetchAll($contracts_obj->select()
			->where("1=1")
			->where("status = ?",1)
			->order("ordering")
			);

			$allContracts=$allContracts->toArray();

			$this->view->allContracts = $allContracts;

		

			if ($this->getRequest()->isPost())
			{
				$bflag = 0;
				$antixss = new Gbc_Model_Custom_StringLimit();
				foreach($_POST as $key => $value)
				{
					//if($key!="text"){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							//$data=array('success'=>'','failure'=>'Invalid Input.');
							//echo json_encode($data);exit;
							//$errormessage="Invalid Input";
							//$authUserNamespace->errormessage=$errormessage;
							//$authUserNamespace->msg = $errormessage;
							$bflag = 1;

						}

					}
					//}
				}


				$contractId = $common_obj->cleanQueryParameter($_POST['kit_type']);
				$max_limit = $common_obj->cleanQueryParameter($_POST['max_limit']);
				$admin_limit = $common_obj->cleanQueryParameter($_POST['admin_limit']);
				$available_limit = $_POST['available_limit'];
				$token=$_POST['token'];
				if($bflag != 1)
				{
			//		if($authUserNamespace->token==$token){

						if(!empty($available_limit) && $available_limit!='')
						{
							$available_limit = $common_obj->cleanQueryParameter($_POST['available_limit']);
						}
						else
						{
							$available_limit = $max_limit;
						}
						$upd_arr = array('max_limit'=>$max_limit,'admin_limit'=>$admin_limit,'available_limit'=>$available_limit);

						$upd_data = $contracts_obj->update($upd_arr,$db->quoteInto("contract_id = ?",$contractId));

						if(!empty($upd_data)){
							$msg="<p style='color:green;'>Kit limit Updated</p>";
							$authUserNamespace->msg = $msg;
						}
						else
						{
							$msg="<p style='color:red;'>error while updating. Please try again</p>";
							$authUserNamespace->msg = $msg;
						}
/*
					}
					else{
						$msg="<p style='color:red;'>Invaild token</p>";
						$authUserNamespace->msg = $msg;

					} */
				}
				else 
				{
					$msg="<p style='color:red;'>Invaild request found</p>";
					$authUserNamespace->msg = $msg;
				}
			}
		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

	public function kitdataAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$contracts_obj = new Gbc_Model_DbTable_Contracts();
		$contractId = $common_obj->cleanQueryParameter($_POST['kit']);
		$token = $common_obj->cleanQueryParameter($_POST['token']);
	/*	if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
		{
			$data=array('success'=>'','failure'=>'failure','data'=>'Invalid token');
			echo json_encode($data);exit;
		}  */
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'failure','data'=>'Invalid Input');
					echo json_encode($data);exit;


				}

			}

		}



		$allContracts = $contracts_obj->fetchAll($contracts_obj->select()
		->where("contract_id = ?",$contractId)
		);



		if(!empty($allContracts) && sizeof($allContracts)>0)
		{
			$allContracts = $allContracts->toArray();
			$arr=array('success'=>'success','failure'=>'','data'=>$allContracts);
			echo json_encode($arr);exit;
		}
	}



}
