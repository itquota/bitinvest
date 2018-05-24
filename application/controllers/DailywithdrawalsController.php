<?php
class DailywithdrawalsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		
		$this->view->title="Gainbitcoin - Daily Withdrawal Requests";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$antixss = new Gbc_Model_Custom_StringLimit();
		$data1=$misc_obj->GetAccessRightByUserId('50',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{
			$user = $authUserNamespace->user;
		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$DailypayoutrequestsObj = new Gbc_Model_DbTable_Dailypayoutrequest();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	
		try{

		 	$this->_helper->layout()->setLayout("admindashbord");//dashboard
		 	$MaxRowsCount=100;
	 	
			//$table = "kit_invoices";
			$data = $DailypayoutrequestsObj->select()
			->from(array('d'=>'daily_payout_withdrawal_requests'),array('count(id) as total_count'));
			//->where("kit_invoices.contract_rate <>?", '0');
			//$whereQuery = "where kit_invoices.contract_rate <> 0";
			//$UserCountQuery = "select invoice_id from $table ";
				
				
			if(!empty($_POST['search'])){

				$searchQuery = $_POST['search'];
				//$whereQuery .= " and kit_invoices.username = '$searchQuery'";
				$data->where('d.wallet_address = ?',$searchQuery);
			}
		
			$UserCountResult = $DailypayoutrequestsObj->fetchRow($data);
			$UserCount = ($UserCountResult->total_count);
				
			
			
			if(!empty($PaginateLimit)){
				$PaginateLimit=$PaginateLimit;
			}else{
				$PaginateLimit=100;
			}
			$pages = ceil($UserCount/$PaginateLimit);

			$this->view->pages=$pages;
			// echo $pages;

			if(!empty($_GET['page'])){
				$value = $_GET['page'];
				$antixss->setEncoding($_GET['page'], "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$this->_redirect("/Profileerror/errormsg");

				}
				$startLimit=($_GET['page']-1)*$PaginateLimit;
			}else{
				$startLimit=0;
			}
			// echo $startLimit;
			// exit;
			if(!empty($PaginateLimit) && !empty($startLimit)){
				$Limit = $startLimit.", ".$PaginateLimit;
			}else if(!empty($PaginateLimit)){
				$Limit = $PaginateLimit;
			}else{
				$Limit ="";
			}

			if(!empty($_POST['search'])){
				$searchQuery = $common_obj->cleanQueryParameter($_POST['search']);

				foreach($_POST as $key => $value){

					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
							$this->_redirect("/Profileerror/errormsg");
						}
					}
				}
			
				$result = $DailypayoutrequestsObj->select()
				->setIntegrityCheck(false)
				->from(array('d'=>'daily_payout_withdrawal_requests'))
				->where("d.wallet_address = ?",$searchQuery)
			//	->ORwhere("kit_invoices.invoice_id = ?",$searchQuery)
			//	->ORwhere("kit_invoices.middleAddr LIKE ?",'%'.$searchQuery.'%')
			//	->ORwhere("kit_invoices.comment LIKE ?",'%'.$searchQuery.'%')
			//	->where("kit_invoices.contract_rate <> ?",'0')
			//	->group("kit_invoices.invoice_id")
				->order("d.request_date DESC");
							
				if(!empty($PaginateLimit) && !empty($startLimit)){
					$result->limit($PaginateLimit,$startLimit);
				}else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}
			}else{
				$result = $DailypayoutrequestsObj->select()
				->setIntegrityCheck(false)
				->from(array('d'=>'daily_payout_withdrawal_requests'))
			//	->where("kit_invoices.contract_rate <> ?",'0')
			//	->group("kit_invoices.invoice_id")
				->order("d.request_date DESC");
								
				if(!empty($PaginateLimit) && !empty($startLimit)){
					$result->limit($PaginateLimit,$startLimit);
				}else if(!empty($PaginateLimit)){
					$result->limit($PaginateLimit);
				}
			}

			$Dailypayoutrequests = $DailypayoutrequestsObj->fetchAll($result);
			if(isset($Dailypayoutrequests) && sizeof($Dailypayoutrequests)>0){
				$DailypayoutrequestsResult = $Dailypayoutrequests->toArray();
			}else{
				$DailypayoutrequestsResult = array();
			}
	 		$this->view->result=$DailypayoutrequestsResult;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	
	
		public function changestatusAction(){
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        	$daily_payout_request_obj = new Gbc_Model_DbTable_Dailypayoutrequest();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$username=$authUserNamespace->user;
			
			$walletAddr=$_POST['wallet_addr'];

			$amount=$_POST['amount'];
			$comment=$_POST['comment'];
			
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('50',$user_id);
			if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
			{
				$user = $authUserNamespace->user;
			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
		//	var_dump($_POST);
		//	exit;
			
			 $result=$daily_payout_request_obj->fetchRow($daily_payout_request_obj->select()
			->setIntegrityCheck(false)
			->from(array('daily_payout_withdrawal_requests'))
			->where("wallet_address=?",$walletAddr)
			->where("amount=?",$amount)
			->where("status=?",1));				
						
			if(!empty($result) && sizeof($result)>0)
			{
					$time = date('Y-m-d H:i:s');
				
							//$update_arr=array('status'=>'3','comment'=>"request cancel by $username on $time");
							$update_arr=array('status'=>'3','comment'=> $comment);
			//	var_dump($update_arr);
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
						$data = "Withdrawal request rejected successfully !";
					//	$data=array('success'=>"Withdrawal request rejected successfully !",'failure'=>'');
					//	echo json_encode($data);exit;
					}
					else
					{
						$data = "Some error occured";
					//	$data=array('success'=>'','failure'=>'Some error occured');
					//	echo  json_encode($data);exit;
					}
				$authUserNamespace->msg = $data;
			}
			$this->_redirect('/Dailywithdrawals');
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
