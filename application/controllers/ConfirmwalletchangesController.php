<?php

class ConfirmwalletchangesController extends Zend_Controller_Action{

    public function init(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        //if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
        //$this->_helper->layout()->disableLayout();
    }

    public function indexAction(){
		$com_code = $_GET['cd'];
        $username=base64_decode($_GET['n']);
        $walletAddress=base64_decode($_GET['e']);
        $payoutOption=base64_decode($_GET['m']);
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $claimed_offers_obj = new Gbc_Model_DbTable_Claimedoffers2();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $sms_log_obj=new Gbc_Model_DbTable_Smslog();
        $withdrawal_obj=new Gbc_Model_DbTable_Withdrawals();

        /*$userInfo = $user_obj->fetchRow($user_obj->select()
    ->where("username='".trim($username)."'"));*/
        $userInfo = $common_obj->getUserInfo($username);
		$com_codeinfo=$userInfo->com_code;

		$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
		->setIntegrityCheck(false)
		->from(array('featured_permissions'),array('value','start','end'))
		->where("name=?",'wallet_disable'));
						 
        $CurrentDate = date('Y-m-d H:i:s');
        $startDate = $permissions_data['start'];
        $endDate = $permissions_data['end'];
		
 if(!empty($_GET['cd']) && !empty($_GET['n']) && !empty($_GET['e']) && !empty($_GET['m']) && ($com_codeinfo == $com_code)  && (($CurrentDate <= $startDate) || ($CurrentDate > $endDate))){
//if($com_codeinfo==$com_code){
            $UserWallet=$user_obj->fetchRow($user_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('B'=>"user_info"),array('B.wallet_addr','B.mcap_wallet_addr'))
                ->where("username= ?",$username)
                ->where("wallet_addr is not null"));
            $withdrawal_address=base64_decode($_GET['e']);
			if($payoutOption == 1){
				$update_arr1=array('wallet_addr'=>$withdrawal_address,'payout_option' => $payoutOption,'requestVerify' => '1', 'com_code' => '', 'updated_on' => new Zend_Db_Expr('NOW()'));
			}else{
				$update_arr1=array('mcap_wallet_addr'=>$withdrawal_address,'payout_option' => $payoutOption, 'requestVerify' => '1', 'com_code' => '','updated_on'=>new Zend_Db_Expr('NOW()'));
			}
            $update_data=$user_obj->update($update_arr1,"username='".$username."'");
	 
	 
	 
	 		 $UserClaimed=$claimed_offers_obj->fetchRow($claimed_offers_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('B'=>"claimed_offers2"),array('B.hash'))
                ->where("user= ?",$username)
                ->where("hash=?",$withdrawal_address));
	 
				 $a=array("mobile","car","trip","TV");
				$random_keys=array_rand($a,2);
				$reward = $a[$random_keys[0]];
				 $offer_id = rand();
	 
	  		if(isset($UserClaimed) && sizeof($UserClaimed)>0){
		//		echo "here"; 
				$update_offers_arr1=array('user' => $username, 'hash'=>$withdrawal_address, 'updated_on'=>new Zend_Db_Expr('NOW()'));
				 $updateClaimedOffers = $claimed_offers_obj->update($update_offers_arr1,"user='".$username."'");
	 		}else{
	//			echo "else";
				 $update_offers_arr1=array('user' => $username, 'hash'=>$withdrawal_address,'offer_id' => $offer_id,'reward' => $reward);
				 $updateClaimedOffers = $claimed_offers_obj->insert($update_offers_arr1);
	 		}
	 
	 
	// var_dump($updateClaimedOffers);
	//exit;
            if(isset($UserWallet) && sizeof($UserWallet)>0)
            {
				if($payoutOption == 1){
                	$ExistUserWallet=$UserWallet->wallet_addr;
				}else{
					$ExistUserWallet=$UserWallet->mcap_wallet_addr;
				}
            }else{
                $ExistUserWallet='';
            }
            //$_POST['withdrawal_address']
			if($payoutOption == 1){
			  $description = " wallet_addr has been changed from '".$ExistUserWallet."' to '".$withdrawal_address."' for auto withdrawal." ;
			}else{
			  $description = " mcap_wallet_addr has been changed from '".$ExistUserWallet."' to '".$withdrawal_address."' for auto withdrawal." ;
			}
          
            if(!empty($description)){
                $saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'>
					<img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
					<div><br/>Dear ".$username.", <br/>Your Wallet address has been changed from ".$ExistUserWallet." to ".$withdrawal_address." </div>";
            }
	 
            if(!empty($email)){

                //	$common_obj->sendMail($userInfo['comm_email'], "admin@gainbitco.in", "Wallet address Updated", $email);

                $to = $userInfo->comm_email;
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Wallet Address Updation Request';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            }
	 
            $withdrawalDetails["username"] = $username;
            $withdrawalDetails["wallet_addr"] = $withdrawal_address;
            $withdrawalDetails["chosen_coin"] = "BTC";
            $withdrawalDetails["btc_amt"] = '';
            $withdrawalDetails["status"] = 0;
            $withdrawalDetails["cron_status"] = 0;
            $withdrawalDetails['withdrawal_type']='auto_withdrawal';

            $ip=$misc_obj->get_client_ip();
	 
            if(!empty($update_data)){
				$insert_arr11 = array('username' => $withdrawalDetails["username"],'user_to' => $withdrawalDetails["user_to"],'btc_amt' => $withdrawalDetails["btc_amt"], 'withdrawal_type' => $withdrawalDetails["withdrawal_type"], 'wallet_addr' => $withdrawalDetails["wallet_addr"], 'alt_amt' => $withdrawalDetails["btc_amt"], 'chosen_coin' =>  $withdrawalDetails["chosen_coin"], 'status' => $withdrawalDetails["status"], 'cron_status' => $withdrawalDetails["cron_status"], 'timestamp' => new Zend_Db_Expr('NOW()'), 'created_on' => new Zend_Db_Expr('NOW()'),  'ip_address' => $ip);
                $insert_data = $withdrawal_obj->insert($insert_arr11);
            }
	 
            if($authUserNamespace->user == $username){
                $this->_redirect("Withdrawals");
            }else{
                Zend_Session::destroy(true,true);
                $this->_redirect("/Login");
            }

            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;

        }else{
            $this->_redirect("/Profileerror");
            $msg = "That verification link is invalid, has expired or has been used.";
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
            exit;

        }
	}
	


}
