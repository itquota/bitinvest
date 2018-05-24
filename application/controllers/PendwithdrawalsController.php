<?php
class PendwithdrawalsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		try
		{
			$this->view->title="Gainbitcoin - Withdrawals";	
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			
			$data1=$misc_obj->GetAccessRightByUserId('8',$user_id);
			if(!empty($data1->view) && ($data1->view==1) || $authUserNamespace->user=='admin')
			{
			
			}
			else 
			{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
			}
		
			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			//if($this->_request->isPost())
			//{
			$pendwithdrawalsobj = new Gbc_Model_DbTable_Withdrawals();
			$result=$pendwithdrawalsobj->fetchAll($pendwithdrawalsobj->select()
			->setIntegrityCheck(false)
			->from(array('withdrawals'))
			->order("timestamp desc ")
			->limit(1000)
			);

			$this->view->result=$result;
			//	$records_per_page = $this->_request->getParam('getPageValue',10);
			//	$this->view->records_per_page = $records_per_page;
				

		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function changewithdrawalstatusAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$withdrawals=new Gbc_Model_DbTable_Withdrawals();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('8',$user_id);
			
			if(!empty($data1->edit)&&($data1->edit==1) || $authUserNamespace->user=='admin')
			{		}
			else 
			{	
				$data=array('success'=>'','failure'=>'You do not have sufficient privileges to access this area.');
		 		echo json_encode($data);exit;
			}
			
			$withdrawalDetails["withdrawalId"] = $_POST["withdrawalId"];
			$withdrawalDetails["status"]= $_POST["status_value"];

			$desiredStatusValue = $withdrawalDetails["status"];
			if($withdrawalDetails["status"]==0){
				$desiredStatusValue =1;
			}elseif($withdrawalDetails["status"]==1){
				$desiredStatusValue  = 0;
			}

			$arr=array('status'=>$desiredStatusValue);
			//$upd=$withdrawals->update($arr,"id = '" . $withdrawalDetails["withdrawalId"]. "'");
			$upd=$withdrawals->update($arr,$DB->quoteInto("id = ?",$withdrawalDetails["withdrawalId"]));
			
			echo $upd;exit;
			if(!empty($upd))
			{

				if($desiredStatusValue==1){

					$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
					$withdrawalInfo =$withdrawals->fetchRow($withdrawals->select()
					->setIntegrityCheck(false)
					->from(array('w' =>'withdrawals'),array('w.username','w.chosen_coin','w.addr','w.btc_amt'))
					->where("btc_amt != ?",0)
					->where("id= ?",$withdrawalDetails["withdrawalId"])
					);	
					if(!empty($userInfo->comm_email) && $userInfo->comm_email!='')
					{
						$usermail = $userInfo->comm_email;
					}
					else
					{
						$usermail = $userInfo->comm_email;
					}			
					if(!empty($withdrawalInfo) && sizeof($withdrawalInfo)>0)
					{
						$coinChoice = $withdrawalInfo->chosen_coin;
						$userInfo =$Gbc_Model_Custom_func_obj-> getUserInfo($withdrawalInfo->username);
						$withdrawalAmt = ($coinChoice=="BTC")?$withdrawalInfo->btc_amt:'';
						$email = "<div style='padding: 10px; border: solid thin black'><p>Dear ".$userInfo->username.",</p><p>Your withdrawal request for ".$withdrawalAmt." ".$coinChoice." to ".$withdrawalInfo->addr." has been completed.</p></div>";
						//$Gbc_Model_Custom_func_obj->sendMail($usermail, "admin@gainbitco.in", "Your withdrawal request has been completed successfully", $email);
							$to = $usermail; 
							$from = 'admin@gainbitco.in';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = 'Your withdrawal request has been completed successfully';
							$message = $email;
							$htmlMessage = $email;
							$sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
						//echo "true";exit;
						$data=array('success'=>'true','failure'=>'');
		 				echo json_encode($data);exit;
					}
					//echo "fsfdsfdf";exit;

				}
				//echo "true";exit;
				$data=array('success'=>'true','failure'=>'');
		 				echo json_encode($data);exit;
			}
			else
			{
			
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function deletestatusAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$withdrawals=new Gbc_Model_DbTable_Withdrawals();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('8',$user_id);
			
			if(!empty($data1->delete)&&($data1->delete==1) || $authUserNamespace->user=='admin')
			{
	
			}
			else 
			{		
				$data=array('success'=>'','failure'=>'You do not have sufficient privileges to access this area.');
		 		echo json_encode($data);exit;
			}
			$withId=trim($_POST['withId']);
			if($withId=='')
			{
			 echo 'Please provide id';
			 exit();
			}
			else{
				$data = $withdrawals->fetchRow($withdrawals->select()
				->where("id= ?",$withId));

				if(!empty($data) && sizeof($data)>0)
				{
					$withdrawals->delete($DB->quoteInto("id=?",$withId));
					// $insert="INSERT INTO delete_withdrawal_log(del_id, username, btc_amt, withdrawal_type, chosen_coin, addr, with_status, with_timestamp,withdrawalHash,paymentTxId,apiMsg,ip_address) VALUES ('" .cleanQueryParameter($data["id"]). "', '".cleanQueryParameter($data["username"])."', '" .$data["btc_amt"]. "', '" .$data["withdrawal_type"]. "', '" .$data["chosen_coin"]. "', '" .$data["addr"]. "', '" .$data["status"]. "', '".$data["timestamp"]."','" .$data["withdrawalHash"]. "','" .$data["paymentTxId"]. "','" .$data["apiMsg"]. "','" .$data["ip_address"]. "')";
					//echo 'true';exit();
					$data=array('success'=>'true','failure'=>'');
					echo json_encode($data);exit;
				}
				else
				{
					//echo 'Failed to delete! Please try again';exit();
					$data=array('success'=>'true','failure'=>'Failed to delete! Please try again');
					echo json_encode($data);exit;
				}
					
					
			}
		}catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
}