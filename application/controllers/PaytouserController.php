
<?php
class PaytouserController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$this->view->title="Gainbitcoin - Users";

		if(!empty($_POST['username']) && isset($_POST['username']))
		{
			$user_obj = new Gbc_Model_DbTable_Userinfo();
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
			$username=$_POST['username'];
				
			$query=$user_obj->fetchAll($user_obj->select()
			->setIntegrityCheck(false)
			->from(array('user_info'),array('username','wallet_addr'))
			->where("username= ?",$username));
			$WalletAddress=array();
			for($i=0;$i<sizeof($query);$i++)
			{
				$WalletAddress[] = $query[$i]['wallet_addr'];
				//echo $query[$i]['wallet_addr'];exit;
				$this->view->query=$query['wallet_addr'];
			}
				


			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$totalEarnings=$common_obj->getTotalEarnings($username);
			if(!empty($totalEarnings) && sizeof($totalEarnings)>0)
			{
				$total_admin_fees = $totalEarnings->total_admin_fees;
					
				$totalAmount = $totalEarnings->total_amt - $total_admin_fees;

				$total_withdrawal = $totalEarnings->total_withdrawal;
					
				$bal_amt = $totalEarnings->bal_amt;
			}
			$totalEarnings=array('username'=>$username,'total_admin_fees'=>$total_admin_fees,'totalAmount'=>$totalAmount,'total_withdrawal'=>$total_withdrawal,'bal_amt'=>$bal_amt,'wallet_addr'=>$query);

			$this->view->totalEarnings=$totalEarnings;
		}
	}
	public function editpaytouserAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		
		if($this->_request->isPost()){
			//echo "in";exit;
			$token=$_POST['token'];
	//  if($authUserNamespace->token==$token){
	  	if(!empty($_POST["withdrawal_amt"]) && $_POST["withdrawal_amt"]!=''){
	  		//$withdrawalDetails["addr"] = $_POST["withdrawal_amt"];
	  	}
	  	else{
	  		$errmsg = 'Please enter Withdrawal Amount.';
	  		$authUserNamespace->errmsg=$errmsg;
	  		$this->_redirect("/Paytouser");
	  	}	
	  	if(!empty($_POST["withdrawal_type"]) && $_POST["withdrawal_type"]=='select'){
	  		$errmsg = 'Please enter Withdrawal Type.';
	  		$authUserNamespace->errmsg=$errmsg;
	  		$this->_redirect("/Paytouser");
	  	}
	  			
	  	$username = $_POST["usr"];
	  	$withdrawalDetails["usr"] = $username;
	  	$withdrawalDetails["chosen_coin"] = $_POST["coin_choice"];
	  	$withdrawalDetails["addr"] = $_POST["withdrawal_address"];
	  	$withdrawalDetails["status"] = 0;
	  	$withdrawalDetails['withdrawal_type'] = $_POST["withdrawal_type"];
	  	$withdrawalDetails['date']=$_POST["date"];
	  	$withdrawalDetails["btc_amt"] = round($_POST["withdrawal_amt"]*2,8);
	  	$withdrawalDetails["new_token_amt"] = $_POST["withdrawal_amt"];
	  	//print_r($withdrawalDetails);exit;
	  	//$updater = addNewWithdrawalByAdmin($withdrawalDetails);
	  	if(!empty($withdrawalDetails))
	  	{
			$db = Zend_Db_Table::getDefaultAdapter();
	  		$common_obj = new Gbc_Model_Custom_CommonFunc();
	  		$updater=$common_obj->addNewWithdrawalByAdmin($withdrawalDetails);
			
			$upd_qry=$db->query("UPDATE `final_balance` SET `new_token_amt` = new_token_amt+".$withdrawalDetails["new_token_amt"].", `updated_on` = NOW() WHERE (username='".$username."')");
						
			
			//echo $updater;exit;
	  		$this->_redirect('/Binaryuser');
	  	}
	  	else{
	  		echo "else error";exit;
	  	}
/*
	  }else{
	  	//$data=array('success'=>'','failure'=>'Invalid Request Found.');
	  	//echo json_encode($data);exit;
	  	$errmsg = 'Invalid Request Found.';
	  	$authUserNamespace->errmsg=$errmsg;
	  	$this->_redirect('/Binaryuser');
	  } */

		}
	}

}
