<?php
class AuditwalletController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$antixss = new Gbc_Model_Custom_StringLimit();
		$data1=$misc_obj->GetAccessRightByUserId('48',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

	    $cb_wallet_obj=new Gbc_Model_DbTable_Cbwalletstatus();
			
		try{

		 $this->_helper->layout()->setLayout("admindashbord");
		 $PaginateLimit=100;
		 	
	 		
		 	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		 		
			if(!empty($_POST['search'])){
				$searchQuery = $common_obj->cleanQueryParameter($_POST['search']);

				foreach($_POST as $key => $value)
				{

					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							$this->_redirect("/Profileerror/errormsg");



						}

					}

				}
				$result=$cb_wallet_obj->fetchAll($cb_wallet_obj->select()
				->setIntegrityCheck(false)
				->from(array('cb_wallet_status'=>'cb_wallet_status'))				
				->where("cb_wallet_status.wallet_address = ?",$searchQuery)
				->ORwhere("cb_wallet_status.wallet_id = ?",$searchQuery)
				);

			}else{
				$result=$cb_wallet_obj->fetchAll($cb_wallet_obj->select()
				->setIntegrityCheck(false)
				->from(array('cb_wallet_status'))
			//	->order("status ASC")						 

				);			
			
			}

			if(empty($result) || sizeof($result)<=0)
			{
				$result=array();
			}
			$this->view->reviews=$result;
		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	
	public function changestatusAction()
	{
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	  		$cb_wallet_obj=new Gbc_Model_DbTable_Cbwalletstatus();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();			
            $username=$authUserNamespace->user;
            $common_obj = new Gbc_Model_Custom_CommonFunc();

			$walletAddr=$_POST['wallet'];

			$status=$_POST['status'];

			 $result=$cb_wallet_obj->fetchRow($cb_wallet_obj->select()
			->setIntegrityCheck(false)
			->from(array('cb_wallet_status'))
			->where("wallet_address=?",$walletAddr)	
			->where("status!=?",$status));	

			if(!empty($result) && sizeof($result)>0)
			{

				$upd_arr=array('status'=>$status,'updated_on'=>new Zend_Db_Expr('NOW()'));
				$upd_member=$cb_wallet_obj->update($upd_arr,$DB->quoteInto("wallet_address=?",$walletAddr));

				if(!empty($upd_member))
				{
					$description = "Status of '".$walletAddr."' set to '".$status."' by '".$username."'";
					if(!empty($description)){
						$saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);
						$data=array('success'=>'Wallet status updated successfully.','failure'=>'');
						echo json_encode($data);exit;
					}
				}
				else
				{
					$data=array('success'=>'','failure'=>'');
					echo  json_encode($data);exit;
				}


			}

		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}
	}	
}
