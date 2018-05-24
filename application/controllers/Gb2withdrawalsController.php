<?php
class Gb2withdrawalsController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$special_permission=new Gbc_Model_DbTable_SpecialPermission();
	 	$gb2_request_obj = new Gbc_Model_DbTable_Gb2withdrawalrequests();
	 	$gb2_final_balance_obj = new Gbc_Model_DbTable_Gb2FinalBalance();
        $username=$authUserNamespace->user;
		$this->_helper->layout()->setLayout("dashbord");

		$userInfo = $common_obj ->getUserInfo($username);
		$userInfo=$userInfo->toArray();
		$this->view->userInfo=$userInfo;

		$result =$gb2_final_balance_obj->fetchRow($gb2_final_balance_obj->select()
		->setIntegrityCheck(false)
		->from(array('fb' =>'gb2_final_balance'),array('fb.total_amt','fb.total_withdrawal','fb.bal_amt'))
		->where("username= ?",$username));		
		
		$this->view->result=$result;

		$max_eth_limit =$special_permission->fetchRow($special_permission->select()
		->setIntegrityCheck(false)
		->from(array('sp' =>'special_permissions'),array('sp.max_eth_withdraw_limit')));	
		
		$this->view->max_eth_limit  = $max_eth_limit;			

		$allWithdrawals= $gb2_request_obj->fetchAll($gb2_request_obj->select()
		->where("username= ?",$username)
		->where("amount != ?",'0')
		);

		$allWithdrawals=$allWithdrawals->toArray();			
		$this->view->allWithdrawals=$allWithdrawals;		
		

	}
	
	
    public function generaterequestAction()
    {			

		$db = Zend_Db_Table::getDefaultAdapter();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
	 	$gb2_request_obj = new Gbc_Model_DbTable_Gb2withdrawalrequests();
	 	$gb2_final_balance_obj = new Gbc_Model_DbTable_Gb2FinalBalance();
		
		$special_permission=new Gbc_Model_DbTable_SpecialPermission();
		$walletAddr=$common_obj->cleanQueryParameter(($_POST['withdrawal_address']));
		$amount=$common_obj->cleanQueryParameter(($_POST['amount']));
		$username=$common_obj->cleanQueryParameter(($_POST['username']));
		
		if(!empty($walletAddr) && $amount > 0){
		
			$query="select * from gb2_withdrawal_requests where username='$username' and status='1'";
			$result=$db->query($query);
			$withdrawalDetails = $result->fetchAll();

			if(!empty($withdrawalDetails)){
				$data=array('success'=>'','failure'=>'Request already exits.');
				echo json_encode($data);exit;			
			}else{
				$max_withdrawal_limit =$special_permission->fetchRow($special_permission->select()
					->setIntegrityCheck(false)
					->from(array('sp' =>'special_permissions'),array('sp.max_eth_withdraw_limit')));

				if($amount <= $max_withdrawal_limit['max_eth_withdraw_limit']){

					$result =$gb2_final_balance_obj->fetchRow($gb2_final_balance_obj->select()
					->setIntegrityCheck(false)
					->from(array('fb' =>'gb2_final_balance'),array('fb.total_amt','fb.total_withdrawal','fb.bal_amt'))
					->where("username= ?",$username));		


					if($amount <= $result['bal_amt'] ){  
						$insert_arr=array('username'=>$username,'mcap_address'=>$walletAddr,'amount'=>$amount,'status'=>'1','request_date'=>new Zend_Db_Expr('NOW()'),'comment'=>'pending');
						$insert_data=$gb2_request_obj->insert($insert_arr);
						if(!empty($insert_data)){
							$data=array('success'=>'Request Initiated Successfully !','failure'=>'');
							echo json_encode($data);exit;			
						}					
					}else{
						$data=array('success'=>'','failure'=>'Requested amount is greater than balance amount');
						echo json_encode($data);exit;				
					} 
				}else{
					$data=array('success'=>'','failure'=>'Limit exceed');
					echo json_encode($data);exit;			
				}
			}
		}else{
					$data=array('success'=>'','failure'=>'Invalid Input');
					echo json_encode($data);exit;	
		}
    }			

}