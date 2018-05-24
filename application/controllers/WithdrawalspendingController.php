<?php

class WithdrawalspendingController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
	}

	public function indexAction(){
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$username=$authUserNamespace->user;
		$this->view->title="Pending Withdrawals";
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();			

        $daily_payout_obj = new Gbc_Model_DbTable_Dailypayout();
        $daily_payout_request_obj = new Gbc_Model_DbTable_Dailypayoutrequest();

		
		$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
		->setIntegrityCheck(false)
		->from(array('featured_permissions'),array('name','value','start','end'))
		->where("name =?",'withdrawal_reject_disable'));

		$this->view->withdrawal_reject_disable=$permissions_data;

		
		 $result=$daily_payout_obj->fetchAll($daily_payout_obj->select()
		->setIntegrityCheck(false)
		->from(array('daily_payout_data'))
		->where("username=?",$username)
		->group("wallet_addr")
		);		

		$res = array();
		$res1 = array();
		$res2 = array();
		
		if(!empty($result)){

			for($i=0;$i<sizeof($result);$i++){
				
				$address = $result[$i]['wallet_addr'];	
				 $result1=$daily_payout_request_obj->fetchAll($daily_payout_request_obj->select()
				->setIntegrityCheck(false)
				->from(array('daily_payout_withdrawal_requests'))
				->where("status=?",1)
			    ->where($db->quoteInto("wallet_address =?",$address)));
				
				 $result2=$daily_payout_request_obj->fetchAll($daily_payout_request_obj->select()
				->setIntegrityCheck(false)
				->from(array('daily_payout_withdrawal_requests'))
				->where("status=?",2)
			    ->where($db->quoteInto("wallet_address =?",$address)));		
				
				 $result3=$daily_payout_request_obj->fetchAll($daily_payout_request_obj->select()
				->setIntegrityCheck(false)
				->from(array('daily_payout_withdrawal_requests'))
				->where("status=?",3)
			    ->where($db->quoteInto("wallet_address =?",$address)));	
				
				for($k=0;$k<sizeof($result1);$k++){
					array_push($res,$result1[$k]);
				}				
				
				
				for($j=0;$j<sizeof($result2);$j++){
					array_push($res1,$result2[$j]);
				}

				for($m=0;$m<sizeof($result3);$m++){
					array_push($res2,$result3[$m]);
				}				
			
			}

		}
	 	if(empty($res) || sizeof($res)<=0)
	 	{
	 		$res=array();
	 	}
	 	if(empty($res1) || sizeof($res1)<=0)
	 	{
	 		$res1=array();
	 	}	
	 	if(empty($res2) || sizeof($res2)<=0)
	 	{
	 		$res2=array();
	 	}		
		$this->view->requests = $res;
		$this->view->processed = $res1;
		$this->view->rejected = $res2;
	}

	public function changestatusAction(){
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        	$daily_payout_request_obj = new Gbc_Model_DbTable_Dailypayoutrequest();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
			
			$username=$authUserNamespace->user;
			
			$walletAddr=$_POST['wallet_addr'];

			$amount=$_POST['amount'];
			
			
			 $result=$daily_payout_request_obj->fetchRow($daily_payout_request_obj->select()
			->setIntegrityCheck(false)
			->from(array('daily_payout_withdrawal_requests'))
			->where("wallet_address=?",$walletAddr)
			->where("amount=?",$amount)
			->where("status=?",1));				
			
			
				$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
				$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
				->setIntegrityCheck(false)
				->from(array('featured_permissions'),array('name','value','start','end'))
				->where("name =?",'withdrawal_reject_disable'));

			//	$this->view->withdrawal_reject_disable=$permissions_data;
			//print_r($permissions_data['value']);
			
			if($permissions_data['value'] ==1){
				$data=array('success'=>'','failure'=>'You are not allowed');
						echo  json_encode($data);exit;
			}
			
			if(!empty($result) && sizeof($result)>0)
			{
					$time = date('Y-m-d H:i:s');
				
							$update_arr=array('status'=>'3','comment'=>"request cancel by $username on $time");
							$where = array();
							$where[] = $db->quoteInto('wallet_address = ?', $walletAddr);
							$where[] = $db->quoteInto('status = ?',1);
							$where[] = $db->quoteInto('amount = ?',$amount);
							$update_data=$daily_payout_request_obj->update($update_arr,$where);
					if(!empty($update_data))
					{

						  $description = "Request has been cancelled for '".$walletAddr."' of amount '".$amount."' by '".$username."'." ;
			
          
						if(!empty($description)){
							$saveUserLog = $common_obj->saveUserLog($username,"daily_payout_withdrawal_requests",$description);						
						}
						$data=array('success'=>"Withdrawal request rejected successfully !",'failure'=>'');
						echo json_encode($data);exit;
					}
					else
					{
						$data=array('success'=>'','failure'=>'Some error occured');
						echo  json_encode($data);exit;
					}	
			}
			//echo $walletAddr; exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}		
	}
	

}