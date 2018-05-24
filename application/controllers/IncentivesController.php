<?php
class IncentivesController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{

		try
		{
			$db = Zend_Db_Table::getDefaultAdapter();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			$username=$authUserNamespace->user;
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$special_permission=new Gbc_Model_DbTable_SpecialPermission();
	 		$requestincentive_obj = new Gbc_Model_DbTable_Gb2incentivesrequests();
			
			$url= BASE."/Directearningapi?username=".$username."&type=gb2";
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			$result1=$result['data'];
			$this->view->result1=$result1;

			$query="select sum(amount) as incentive from gb2_binaryuserincome where ben_username='$username'";
			$result=$db->query($query);
			$earningDetails = $result->fetchAll();				

			$this->view->earning=$earningDetails;
	
			
			$query1="select sum(amount) as withdrawals from gb2_incentives_requests where username='$username' and status='2'";
			$result1=$db->query($query1);
			$withdrawalDetails = $result1->fetchAll();	
	
			$this->view->withdrawals=$withdrawalDetails;
			
			
			$userInfo = $common_obj ->getUserInfo($username);
			$userInfo=$userInfo->toArray();
			$this->view->userInfo=$userInfo;

			$max_incentive_limit =$special_permission->fetchRow($special_permission->select()
			->setIntegrityCheck(false)
			->from(array('sp' =>'special_permissions'),array('sp.max_incentive_withdraw_limit')));
		
			$this->view->max_incentive_limit  = $max_incentive_limit;			

			$allWithdrawals= $requestincentive_obj->fetchAll($requestincentive_obj->select()
			->where("username= ?",$username)
			->where("amount != ?",'0')
			);

			$allWithdrawals=$allWithdrawals->toArray();			
			$this->view->allWithdrawals=$allWithdrawals;
			
			
			$this->view->title="Gainbitcoin - GB2 Incentives";


		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}

	}
	
    public function generaterequestAction()
    {			

		$db = Zend_Db_Table::getDefaultAdapter();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
	    $requestincentive_obj = new Gbc_Model_DbTable_Gb2incentivesrequests();
		
		$special_permission=new Gbc_Model_DbTable_SpecialPermission();
		$walletAddr=$common_obj->cleanQueryParameter(($_POST['withdrawal_address']));
		$amount=$common_obj->cleanQueryParameter(($_POST['amount']));
		$username=$common_obj->cleanQueryParameter(($_POST['username']));

		if(!empty($walletAddr) && $amount > 0){

			$query="select * from gb2_incentives_requests where username='$username' and status='1'";
			$result=$db->query($query);
			$withdrawalDetails = $result->fetchAll();

			if(!empty($withdrawalDetails)){
				$data=array('success'=>'','failure'=>'Request already exits.');
				echo json_encode($data);exit;			
			}else{
				$max_withdrawal_limit =$special_permission->fetchRow($special_permission->select()
					->setIntegrityCheck(false)
					->from(array('sp' =>'special_permissions'),array('sp.max_incentive_withdraw_limit')));

				if($amount <= $max_withdrawal_limit['max_incentive_withdraw_limit']){

					$query="select sum(amount) as incentive from gb2_binaryuserincome where ben_username='$username'";
					$result=$db->query($query);
					$earningDetails = $result->fetchAll();	


					$query1="select sum(amount) as withdrawals from gb2_incentives_requests where username='$username' and status='2'";
					$result1=$db->query($query1);
					$withdrawalDetails = $result1->fetchAll();	


					$balance = number_format($earningDetails[0]['incentive'],8) - number_format($withdrawalDetails[0]['withdrawals'],8);


					if($amount <= $balance){  
						$insert_arr=array('username'=>$username,'mcap_address'=>$walletAddr,'amount'=>$amount,'status'=>'1','request_date'=>new Zend_Db_Expr('NOW()'),'comment'=>'pending');
						$insert_data=$requestincentive_obj->insert($insert_arr);
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