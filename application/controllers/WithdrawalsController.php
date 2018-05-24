<?php

class WithdrawalsController extends Zend_Controller_Action{

    public function init(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
        //$this->_helper->layout()->disableLayout();
    }

    public function indexAction(){
        require("library/PHPGangsta/GoogleAuthenticator.php");
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $ga = new PHPGangsta_GoogleAuthenticator();
        $this->_helper->layout()->setLayout("dashbord");//dashboard
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $manualwithdrawal_obj = new Gbc_Model_DbTable_Manualwithdrawal();
		$special_permission=new Gbc_Model_DbTable_SpecialPermission();
        $username=$authUserNamespace->user;
        $url= BASE."/Withdrawalapi?username=".$username;
        $result=$common_obj->call_curl($url);
        $result=(array)json_decode($result,true);
        //$result=$result->toArray();
        //echo "<pre>";
        //print_r($result);exit;
        //echo $result['Success'];exit;
        $this->view->result=$result;
        $this->view->title="Withdrawals";

        $userInfo = $common_obj ->getUserInfo($username);
        $userInfo=$userInfo->toArray();
        $this->view->userInfo=$userInfo;


        // print_r($result);exit;
        $secret = 'DZPVUXRVSX33NZFF';
        $authUserNamespace ->secret=$secret;


        $website = BASE; //Your Website
        $title= 'bitcoin';
        //$qrCodeUrl = $ga->getQRCodeGoogleUrl($title, $secret,$website);

        $authUserNamespace ->secret=$secret;
        $this->view->title="Gainbitcoin - Network Outputs";

        $withdrawal_status = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('m'=>"manual_withdrawal_request"),array('status','amount'))
        ->where("username= ?",$username)
        ->order("request_date desc")
        ->Limit(1)
        );
        
        if(isset($withdrawal_status) && sizeof($withdrawal_status)>0){
        $this->view->withdrawal_status  =  $withdrawal_status->status;
        $withdrawal_status = $withdrawal_status->toArray();
        $this->view->manual_withdrawal  =  $withdrawal_status;
        }
		
		
		$withdrawal_count_status = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
		->setIntegrityCheck(false)
		->from(array('m'=>"manual_withdrawal_request"),array('round(sum(amount),8) as count'))
		->where("status= ?",'Requested')
		
		);
		
		//print_r($withdrawal_count_status->count);
	//	exit;
		$this->view->manual_withdrawal_count  = $withdrawal_count_status->count;
		
		
			
		$withdrawal_requests = $manualwithdrawal_obj->fetchAll($manualwithdrawal_obj->select()
		->setIntegrityCheck(false)
		->from(array('m'=>"manual_withdrawal_request"),array('username','amount','wallet_address','request_date'))
		->where("status= ?",'Requested')
		->order(new Zend_Db_Expr('rand()'))
		->Limit(200)
		);
		
		if(isset($withdrawal_requests) && sizeof($withdrawal_requests)>0){
			$withdrawal_requests = $withdrawal_requests->toArray();
			$this->view->withdrawal_requests  = $withdrawal_requests;
		}
		
		
		
		$max_payout_load =$special_permission->fetchRow($special_permission->select()
			->setIntegrityCheck(false)
			->from(array('sp' =>'special_permissions'),array('sp.max_payout_load','sp.user_specific_load','sp.country_specific_load','sp.new_user_payout_limit','sp.payout_limit_for_manual_withdrawal')));
		
		
		$this->view->max_payout_load  = $max_payout_load;
	
		$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		$permissions_data=$permissions_obj->fetchAll($permissions_obj->select()
		->setIntegrityCheck(false)
		->from(array('featured_permissions'),array('name','value','start','end'))
		->where("name in(?)",array('wallet_disable','manual_withdrawal_disable')))->toArray();
		
		$this->view->permissions_data=$permissions_data;
	//	var_dump($permissions_data);
		
			
    }
    public function savewalletAction()
    {
		try{
            require("library/PHPGangsta/GoogleAuthenticator.php");
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            //$common_obj->cleanQueryParameter(($_POST['username']));
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
            //$token=$_POST['token'];
            $token=$common_obj->cleanQueryParameter(($_POST['token']));
    //        if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token){
                $withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
                $username=$authUserNamespace->user;
                $userInfo = $misc_obj->getUserInfo($username);
                $userInfo=$userInfo->toArray();
                $otp = $userInfo["otp"];
	            $isenable=$common_obj->cleanQueryParameter(($_POST['isenable']));
                if(!empty($isenable) && $isenable!='' && $isenable=='2')
                {

                    $ga = new PHPGangsta_GoogleAuthenticator();
                    $secret=$userInfo['secret'];
                    //$authcode=$_POST['authcode'];
                    $authcode=$common_obj->cleanQueryParameter(($_POST['authcode']));
                    //echo $secret;exit;
                //    $checkResult = $ga->verifyCode($secret, trim($_POST['authcode']), 2);    // 2 = 2*30sec clock tolerance
                    $checkResult = $ga->verifyCode($secret, trim($_POST['authcode']), 2);

                    if ($checkResult) {

                    } else {
                        $data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
                        echo json_encode($data);exit;
                    }

                }

				$com_code= md5(rand(100000, 999999));
				$encodedWalletAddress = base64_encode ( $_POST['withdrawal_address']);
				$encodedMode = base64_encode ( $_POST['mode']);
				$encodedName = base64_encode($username);
				$update_arrverify=array('requestVerify' => '2','com_code'=>$com_code);
				// $update_data=$user_obj->update($update_arrverify,"username='".$username."'");
				 $update_data=$user_obj->update($update_arrverify,$DB->quoteInto("username=?",$username));

                 $UserWallet=$user_obj->fetchRow($user_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('B'=>"user_info"),array('B.wallet_addr'))
                ->where("username= ?",$username)
                ->where("wallet_addr is not null"));
                $withdrawal_address=$common_obj->cleanQueryParameter(($_POST['withdrawal_address']));
				if(isset($UserWallet) && sizeof($UserWallet)>0){
                    $ExistUserWallet=$UserWallet->wallet_addr;
                }else{
                    $ExistUserWallet='';
                }
			 $confirmation_url = "https://gainbitcoin.com/gbc/Confirmwalletchanges?n=$encodedName&e=$encodedWalletAddress&m=$encodedMode&cd=$com_code";
				$description = "";
                $description .= "  A Wallet address updation confirmation email has been sent for wallet from '".$ExistUserWallet."' to '".$withdrawal_address."' for auto withdrawal and url is $confirmation_url." ;
                if(!empty($description)){
                    $saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);
                }
			
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
                    <img src='https://gainbitcoin.com/gbc/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
                Wallet Address  Updation</h2><br/>Dear ".$username.", <br/><p>A request for wallet address update has been initiated for the account related to this email address. If it wasn't you who initiated this request, please report us to gbspamreport@gmail.com or send us query through contact form on website. If it was you, please follow the link below to confirm your wallet address updations:</p><p><a href = '".$confirmation_url."'>".$confirmation_url."</a></p></div>";

                $tomail = $userInfo["comm_email"];
                if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
                {
                    $tomail = $userInfo["email_address"];
                }

                $to = $tomail;
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Wallet Address Updation Request';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

				$data=array('success'=>'A Wallet address updation confirmation email has been sent to the registered email address. Please follow instructions within that email to update your wallet address.','failure'=>'');
				echo json_encode($data);exit;

            /* }
            else
            {
                $data=array('success'=>'','failure'=>'Invalid request found.');
                echo json_encode($data);exit;
            } */
			  
        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }

    public function manualwithdrawalAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $username = $authUserNamespace->user;
        $amount = $_POST['withdraw_amt'];
        $wallet_addrs = $_POST['withdrawal_address'];
        $manualwithdrawal_obj = new Gbc_Model_DbTable_Manualwithdrawal();
        $finalBalance_obj = new Gbc_Model_DbTable_FinalBalance();
		 $special_permission=new Gbc_Model_DbTable_SpecialPermission();
		
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $date = date('Y-m-d', time());

        $userInfo = $misc_obj->getUserInfo($username);
        $userInfo = $userInfo->toArray();
        if($amount!="" && $wallet_addrs!=""){
			
			$withdrawal_count_status = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
			->setIntegrityCheck(false)
			->from(array('m'=>"manual_withdrawal_request"),array('round(sum(amount),8) as count'))
			->where("status= ?",'Requested')

			);
			
			
			$max_payout_load =$special_permission->fetchRow($special_permission->select()
			->setIntegrityCheck(false)
			->from(array('sp' =>'special_permissions'),array('sp.max_payout_load')));
						
			$MaxPayoutLimit  = $max_payout_load->max_payout_load;
					
			$PayoutLoad = round($withdrawal_count_status->count,0);
			
			if($PayoutLoad >= $MaxPayoutLimit){
				$data=array('success'=>'','failure'=>'load_exceed');
				echo json_encode($data);exit;
			}
		
			$CountPercentage = round(($PayoutLoad/$MaxPayoutLimit)*100,0);
			
			$withdrawal_status = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
		->setIntegrityCheck(false)
		->from(array('m'=>"manual_withdrawal_request"),array('status','amount'))
		->where("username= ?",$username)
		->where("status= 'Requested'")
		->order("request_date desc")
		->Limit(1)
		);
		//Nilesh: Prevent subsquent request, if another request is not yet processed/rejected/cancelled.
		 if(isset($withdrawal_status) && sizeof($withdrawal_status)>0)
		 {
			$data=array('success'=>'','failure'=>'Invalid Request');
				echo json_encode($data);exit;
		 }
		 // Minimum withdrawal limit check.
		 if($amount<0.025){
			 $data=array('success'=>'','failure'=>'Withdrawal amount should be more than or equal to 0.05 BTC');
				echo json_encode($data);exit;
		 }
			
            // Total withdrawal for the day
            $day_total = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('m'=>"manual_withdrawal_request"),array('round(SUM(amount),8) as todays_withdrawal'))
            ->where("username= ?",$username)
            ->where("status!= 'Rejected' && status!= 'Canceled'")
            ->where("request_date like '%$date%'")
            );
            $todays_total = $amount + $day_total->todays_withdrawal;
            if($todays_total>25){
                $data=array('success'=>'','failure'=>'exceed');
                echo json_encode($data);exit;
            }

            //Check bal BTC
            $balance = $finalBalance_obj->fetchRow($finalBalance_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('f'=>"final_balance"),array('bal_amt'))
                ->where("username= ?",$username)
                );
			$balance_amt = round($balance->bal_amt/2,8);
            if(sizeof($balance)>0 && $amount<$balance_amt){
                $insert_arr = array('payout_load' =>$CountPercentage,'username'=>$username,'amount'=>$amount,'wallet_address'=>$wallet_addrs,'status'=>'Requested','request_date'=>new Zend_Db_Expr('NOW()'),'updated_date'=>new Zend_Db_Expr('NOW()'));
                $insert_data = $manualwithdrawal_obj->insert($insert_arr);
                $email = "Dear ".$username.", <br/><p>Your request for withdrawal of ".$amount." BTC has been received. We will process the same within 24 hours.<br> If the request was not initiated by you then please login to your account and cancel the request.
                        </p> <p>Best Regards, <br>Team Gainbitcoin Support</p></div>";

                $tomail = $userInfo["comm_email"];
                if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
                {
                    $tomail = $userInfo["email_address"];
                }
				
                $to = $tomail;
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Withdrawal Request';
                $message = $email;
                $htmlMessage = $email;
				
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

                $data=array('success'=>'success','failure'=>'');
                echo json_encode($data);exit;

            }else{
                $data=array('success'=>'','failure'=>'insufficient_balance');
                echo json_encode($data);exit;
            }
        }else{
            $data=array('success'=>'','failure'=>'Unable to process request');
            echo json_encode($data);exit;
        }

    }
    public function cancelwithdrawalAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $manualwithdrawal_obj = new Gbc_Model_DbTable_Manualwithdrawal();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $username=$authUserNamespace->user;

        $withdrawal_status = $manualwithdrawal_obj->fetchRow($manualwithdrawal_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('m'=>"manual_withdrawal_request"),array('id','status'))
        ->where("username= ?",$username)
        ->order("request_date desc")
        ->Limit(1)
        );

        if(isset($withdrawal_status->status) && $withdrawal_status->status=="Requested"){
            $arr=array('status'=>"Canceled","comment"=>"Rejected By User",'updated_date'=>new Zend_Db_Expr('NOW()'));
            $upd=$manualwithdrawal_obj->update($arr,$DB->quoteInto("id = ?",$withdrawal_status->id));

            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;
        }else{
            $data=array('success'=>'','failure'=>'An error occured. Please contact your admin');
            echo json_encode($data);exit;
        }
    }
	
    public function checkcoinbankwalletAction()
    {
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();            
        $common_obj = new Gbc_Model_Custom_CommonFunc();
	    $cb_wallet_obj=new Gbc_Model_DbTable_Cbwalletstatus();
		
		$walletAddr=$common_obj->cleanQueryParameter(($_POST['wallet_addr']));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://api.coinbank.info/bws/api/v2/user/details/$walletAddr");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"x-access-token: 12u3hur@3hhchjg3gg3%^c2823832"
		));
		$response = json_decode(curl_exec($ch));
		curl_close($ch);

		if(!empty($response->error)){
			$data=array('success'=>'','failure'=>'Enter valid coinbank address only.');
			echo json_encode($data);exit;
		}else{
			
			$walletId=$response->walletId;
			$blkWallet = $this->checkblacklistedwallet($walletId);
		//	print_r($blkWallet); 
		//	exit;
			
			if($blkWallet['success'] == '' || $blkWallet['success'] == 'NULL'){
				$data=array('success'=>'','failure'=>'Wallet address is blacklisted.');
				echo json_encode($data);exit;				
			}else{
				$query="select * from cb_wallet_status where wallet_address='$walletAddr' and wallet_id='$walletId'";
				$result=$db->query($query);
				$walletDetails = $result->fetchAll();	
				if(empty($walletDetails)){
					$insert_arr1=array('wallet_address'=>$walletAddr,'wallet_id'=>$walletId,'status'=>'1','created_on'=>new Zend_Db_Expr('NOW()'));
					$insert_data1=$cb_wallet_obj->insert($insert_arr1);
					$db->commit();				
				}	
				$data=array('success'=>'success','failure'=>'');
				echo json_encode($data);exit;			
			
			}
		}
    }	
	
    public function checkblacklistedwallet($wallet_id)
    {	
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$query="select * from cb_wallet_status where wallet_id='$wallet_id' and status='2'";
		$result=$db->query($query);
		$walletDetails = $result->fetchAll();
		if(!empty($walletDetails)){
			$data=array('success'=>'','failure'=>'Wallet address is blacklisted.');
			//echo json_encode($data);exit;
			return $data;
		}else{
			$data=array('success'=>'success','failure'=>'');
			//echo json_encode($data);exit;
			return $data;
		}
    }		
}
