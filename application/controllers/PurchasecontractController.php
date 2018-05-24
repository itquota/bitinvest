<?php
class PurchasecontractController extends Zend_Controller_Action{

    public function init(){

    }

    public function indexAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $this->_helper->layout()->setLayout("dashbord");//dashboard
        if($this->_request->isPost())
        {
            $token = $common_obj->cleanQueryParameter(($_POST['token']));
         /*   if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
                $data=array('success'=>'','failure'=>'Invalid request found.');
                echo json_encode($data);exit;

            }
		*/	
			
            $antixss = new Gbc_Model_Custom_StringLimit();
            foreach($_POST as $key => $value)
            {
                if($key!="description"){
                    if(isset($value) && $value != ""){
                        $antixss->setEncoding($value, "UTF-8");
                        if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                            $data=array('success'=>'','failure'=>'Invalid Input.');
                            echo json_encode($data);exit;

                        }

                    }
                }
            }
            $contract_type=$common_obj->cleanQueryParameter($_POST['contract_type']);
            $hid_btc_value=$common_obj->cleanQueryParameter($_POST['hid_btc_value']);
            $sha_hashrate_qty=$common_obj->cleanQueryParameter($_POST['sha_hashrate_qty']);
            if($_POST['contract_type']){
                $contract_type = $common_obj->cleanQueryParameter($_POST['contract_type']);
            }
            $amount = (isset($_POST['hid_btc_value']) ? $_POST['hid_btc_value']: '');
            $contract_id = (isset($_POST['sha_hashrate_qty']) ? $_POST['sha_hashrate_qty'] : '');
            if($amount == 15){
                // $value = 12.5;
                $value = 12.99;
            }else if($amount == 31){
                $value = 25;
            }else {
                $value = $amount;
            }
            $arr=array('success'=>'success','failure'=>'','contract_type'=>$contract_type,'contract_id'=>$contract_id,'value'=>$value,'amount'=>$hid_btc_value,'sha_hashrate_qty'=>$sha_hashrate_qty);
            $this->view->result=$arr;
        }
    }

    public function purchasekitAction()
    {
        try {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $username=$authUserNamespace->user;
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $user_member_obj = new Gbc_Model_DbTable_Usermember();
            $token = $common_obj->cleanQueryParameter($_POST['token']);
			$kits_obj=new Gbc_Model_DbTable_Kits();
        /*    if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
                $data=array('success'=>'','failure'=>'Invalid request found.');
                echo json_encode($data);exit;
            }
		*/
            $antixss = new Gbc_Model_Custom_StringLimit();
            foreach($_POST as $key => $value)
            {
                if(isset($value) && $value != ""){
                    $antixss->setEncoding($value, "UTF-8");
                    if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
                        $db->rollBack();
                        $data=array('success'=>'','failure'=>'Invalid Input.');
                        echo json_encode($data);exit;

                    }

                }

            }
			if(empty($username)){
				 $data=array('success'=>'','failure'=>'You are logged out. Login again to use kit.');
                echo json_encode($data);exit;
			}
			
            $kitdetails=$common_obj->checkKitPrice($username, $_POST['kit_number']);

            if(!empty($kitdetails) && sizeof($kitdetails)>0) {
                $kitprice=$kitdetails->kit_price;
                $ContractRateBTC=$kitdetails->contract_price;
                $kitNumber=$kitdetails->kit_number;
                $KitLock=$kitdetails->locked;
                $KitContractId=$kitdetails->contract_id;
            } else {
                $db->rollBack();
                $data=array('success'=>'','failure'=>'Invalid Kit Number. Please try another one.');
                echo json_encode($data);exit;
            }
            $kt_type=$common_obj->cleanQueryParameter($_POST['kt_type']);
            if($kt_type=='hardware' && (!isset($_POST['full_name']) || !isset($_POST['mobile']) || !isset($_POST['email']) || !isset($_POST['address1']) || !isset($_POST['address2']) || !isset($_POST['pincode']) || !isset($_POST['country']) || !isset($_POST['state']) || !isset($_POST['state'])))
            {
                $data=array('success'=>'','failure'=>'All fields required');
                echo json_encode($data);exit;

            }
            $contractId = $common_obj->cleanQueryParameter($_POST["hiddencontractId"]);

            if(!isset($contractId) || $contractId==''){
                $db->rollBack();
                $data=array('success'=>'','failure'=>'Invalid Details');
                echo json_encode($data);exit;

            }
			
			if($contractId == 13){
							
          		$result_kit=$kits_obj->fetchRow($kits_obj->select()
					->setIntegrityCheck(false)
					->from(array('ki'=>"kits"),array('ki.comment'))
					
					->where("ki.kit_number = ?",$kitNumber)
					->where("ki.comment like 'kit_generation by%'")
				);
				
				if($result_kit){
					$data=array('success'=>'','failure'=>'Purchase of "Power of One" kit via GB wallet balance is not allowed');
					echo json_encode($data);exit;
				}
			}
			
			
            $contractDetails =$common_obj-> getContracts($contractId);
            if(!empty($contractDetails) && isset($contractDetails) && sizeof($contractDetails)>0)
            {
                //$contractDetails = $contractDetails->$contractId;
                $contractDetails=$contractDetails->toArray();
                //$ContractRate = $contractDetails['total_price'];
                $ContractRateInUSD = $contractDetails['total_price'];
            }
			$ContractRate = $ContractRateBTC;
			 $contractDetails['total_price'] = $ContractRateBTC;
			

			//var_dump($contractDetails);
			//exit;
            if($_POST["contract_type"]=='ROI')
            {
                $contractDetails['isReferred']='No';
            }
            else{
                $contractDetails['isReferred']='Yes';
            }
         //   $value=$common_obj->cleanQueryParameter($_POST['total_amount']);
			 $value = $kitprice;
			
			
         //   if(number_format($value,6)<=number_format($kitprice,6)) {
			if($KitContractId == $contractId){
                $txid=$username.'_'.time() . "_" . rand();
                $invoice = $common_obj-> createNewConHwInvoice($contractDetails, $username,$kitNumber, 0);
                if($invoice=='failure'){
                    $db->rollBack();
                    $data=array('success'=>'','failure'=>'Error creating invoice:');
                    echo json_encode($data);exit;
                }
                else
                {
                    $invoiceId=$invoice;
                }
                $confirmations=3;
            }// Need to discuss if kit amount is greater than value amount what about remaining kit amount....
            else{
                $db->rollBack();
              // $message = "The amount paid (".$value." BTC) is not the same as the Kit price (".$kitprice." BTC)";
               $message = "You have chosen wrong contract";

                $data=array('success'=>'','failure'=>$message);
                echo json_encode($data);exit;

            }

            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $invoiceDetails = $common_obj-> getUserContracts("", "", $invoiceId);
            $buyerUsername = $invoiceDetails->username;
            $buyerUserInfo = $misc_obj->getUserInfo($buyerUsername);
            //$buyerEmail = $buyerUserInfo->comm_email;
            if(!empty($buyerUserInfo->comm_email) && $buyerUserInfo->comm_email!='')
            {
                $buyerEmail = $buyerUserInfo->comm_email;
            }
            else
            {
                $buyerEmail = $buyerUserInfo->email_address;
            }

            $kit_inv_obj=new Gbc_Model_DbTable_Kitinvoices();
            $origtxid='';

            $result_kit=$kit_inv_obj->fetchRow($kit_inv_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('ki'=>"kit_invoices"),array('ki.origtxid'))
                ->joinLeft(array('k'=>'kits'),"k.invoice_id  = ki.invoice_id",array('k.invoice_id'))
                ->where("k.kit_number = ?",$kitNumber)
            );

            $origtxid = ($result_kit->origtxid);
            $benfusers=$common_obj->getUserByRefId($buyerUserInfo->ref_sponsor_id);
            $invoices_obj = new Gbc_Model_DbTable_Invoices();

            $result_invoices=$invoices_obj->fetchRow($invoices_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('i'=>"invoices"),array('count(invoice_id) as check_benuser'))
                ->where(" contract_rate>=?",'0.5')
                ->where("username=?",$benfusers->username)
                ->where("invoice_status=?",1)

            );

            $contractDetails['isBenfit']='';
            if(isset($result_invoices) && ($result_invoices))
            {
                $contractDetails['isBenfit']=$result_invoices->check_benuser;
            }

            $depthArray = $common_obj->getDepthOfBinaryUser($buyerUsername);
            $depth = 1;
            if(!empty($depthArray) && sizeof($depthArray)>0){
                $depth1 = trim(str_replace(",amitsabnetwork,", "",$depthArray->depth));
                $depth = substr($depth1,0, -1);
                $depthExpArray = explode(",", $depth);

                if((count($depthExpArray)) > 0){
                    array_pop($depthExpArray);
                    $depthLevel = count($depthExpArray)-1;

                    if($depthLevel<=0){
                        $depth = 1;
                    }else{
                        // $depth = count($depthExpArray)-1;
                        $depth = count($depthExpArray);
                    }
                }
            }
  /*
            if ($ContractRate == 5){
                $tol_amount = 0.2;
                $tol_amount = 0.2;
            }else if ($ContractRate == 15){
                $tol_amount = 0.75;
            }else if ($ContractRate == 25){
                $tol_amount = 1.25;
            }else if ($ContractRate == 50){
                $tol_amount = 2.5;
            }
          
            else if ($ContractRate == 75){
                $tol_amount = 3.5;
            }
           
            else if ($ContractRate == 100){
                $tol_amount = 5;
            }else {
                $tol_amount=number_format(($ContractRate*5)/100,6);
            }
             */
             $tol_amount=number_format(($ContractRate*5)/100,6);
            if($kitprice ==  4.85){
                $tol_amount = 0.2;
            } 
            if ($kitprice == 64){
                $tol_amount = 3.5;
            }
            
            
            
            
            $percentage=5;
            if($ContractRate==0.149 || $ContractRate==0.079)
            {
                $tol_amount=number_format(($ContractRate*(0.8))/100,6);
                $percentage=0.8;
            }



            /*if($confirmations==3)
             {
                $update_membership=$common_obj->updateMembership($username,$ContractRate);
                }
                else
                {
                $membership_type_qry=$common_obj->updateMembership($username);
                }

                $current_membership=$user_member_obj->fetchRow($user_member_obj->select()
                ->where("username='".$benfusers->username."'")
                ->order('date desc')
                ->limit(1)
                );
                $member_obj=new Gbc_Model_DbTable_Membershiplist();
                if(!empty($current_membership) && sizeof($current_membership)>0)
                {
                $membership_type_qry = $member_obj->fetchRow($member_obj->select()
                ->where("membership_type='".$current_membership->membership."'"));
                }
                else
                {
                $membership_type_qry = $member_obj->fetchRow($member_obj->select()
                ->where("membership_type='Customer'"));

                $user_memb_arr=array('username'=>$benfusers->username,'membership'=>'Customer','date'=>new Zend_Db_Expr('NOW()'));
                $insert_memb=$user_member_obj->insert($user_memb_arr);
                }


                $tol_amount=number_format(($ContractRate * ($membership_type_qry->direct_sales_comission))/100,6);
                $percentage=$membership_type_qry->direct_sales_comission;*/
			
			
			/*  DSI calculation according to USD price*/
			
						
				 $tol_amount_in_usd=number_format(($ContractRateInUSD*5)/100,6,'.','');
			
				$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
				$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
				if($Price_in_usd){
					$Price_in_usd = json_decode($Price_in_usd);
					$Price_in_usd = $Price_in_usd->bpi->USD->rate;

					$update_Price_in_usd = array('value' => $Price_in_usd);
					$udpatePrice = $permissions_obj->update($update_Price_in_usd,"name = 'btc_usd_price'");

				}else{
					$permissions_data1=$permissions_obj->fetchRow($permissions_obj->select()
						->setIntegrityCheck(false)
						->from(array('featured_permissions'),array('name','value'))
						->where("name =?",'btc_usd_price'));

					if(!empty($permissions_data1)){
						$Price_in_usd = $permissions_data1['value'];
					}else{
						$Price_in_usd = 0;
					}
				}

			//		print_r($Price_in_usd);
			//	$Price_in_usd = number_format($Price_in_usd,'4','.','');
				$Price_in_usd = str_replace(',','',$Price_in_usd);

				 $tol_amount = round(($tol_amount_in_usd/$Price_in_usd),4);

				/*  DSI calculation according to USD price*/


			
			
			

            if($contractDetails['isBenfit']>=1){

                if(($benfusers->isActiveId=='1')  ){
                    $bin_user_income=new  Gbc_Model_DbTable_Binaryuserincome();
                    $insert_arr=array('ben_username'=>$benfusers->username,'from_username'=>$username,'invoice_id'=>$invoiceId,'amount'=>$tol_amount,'level'=>$depth,'percentage'=>$percentage,'partial'=>'1','created_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $insert_data=$bin_user_income->insert($insert_arr);

                }
            }else{

                if(($benfusers->isActiveId=='1')  ){
                    $bin_user_income=new  Gbc_Model_DbTable_Binaryuserincome();
                    $insert_arr=array('ben_username'=>$benfusers->username,'from_username'=>$username,'invoice_id'=>$invoiceId,'amount'=>$tol_amount,'level'=>$depth,'percentage'=>$percentage,'created_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $insert_data=$bin_user_income->insert($insert_arr);

                }
            }
            $update_arr=array('confirmations'=>$confirmations,'transactionid'=>$txid,'origtxid'=>$origtxid,'amtPaid'=>$value);


            if(!empty($KitLock) && $KitLock == 1){

                $update_arr['locked']="1";
                $update_arr1=array('status'=>'0','updated_on'=>new Zend_Db_Expr('NOW()'));
                $update_data=$bin_user_income->update($update_arr1,"invoice_id = '$invoiceId'");

            }
            $user = new Gbc_Model_DbTable_Userinfo();
            if($confirmations==3){

                $update_arr['invoice_status']="1";
                //array_push($update_arr,"invoice_status","1");
                $email = "<div style='border: solid thin black; padding: 10px'>A payment has been received with the following details:<p>Invoice ID: ".$invoiceId."</p><p>Amount: ".$value." BTC</p><p>Confirmations: ".$confirmations."</p><p>Transaction ID: ".$txid."</p></div>";
                $userEmail = "<div style='border: solid thin black; padding: 10px'>A payment has been received with the following details:<p>Invoice ID: ".$invoiceId."</p><p>Amount: ".$value." BTC</p><p>Confirmations: ".$confirmations."</p><p>Transaction ID: ".$txid."</p></div>";
                $email .= "<p style='color: red'>This purchase has been activated.</p>";
                $userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
                // update kit as used
                //$kit_status= "update kits set status='Used' WHERE username='" . $username . "' and kit_number='" . $kitNumber . "' ";
                // $usedDate=date('Y-m-d h:i:s'); /* for used kit date and usedby user*/
             //   $kits_obj=new Gbc_Model_DbTable_Kits();
                $updat_arr1=array('status'=>'Used','kit_used_by'=>$username,'kit_used_date'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kits_obj->update($updat_arr1,"kit_number='" . $kitNumber . "'");

                $upd_arr2=array('isActiveId'=>'1','updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data2=$user->update($upd_arr2,"username='".$username."'");

                $comm_obj = new Gbc_Model_Custom_CommonFunc();
                $binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
                $redis = $comm_obj->getRedisInstance();
                $redis_tree_root_user = "";

                $res_for_redis = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                    ->setIntegrityCheck(false)
                    ->from(array("binary_user_refences"), array('parent_username', 'redis_tree_root_user'))
                    ->where("username = ?", $username)
                );

                $parent_username = false;

                if (isset($res_for_redis[0]['parent_username']))
                    $parent_username = $res_for_redis[0]['parent_username'];

                if (isset($res_for_redis[0]['redis_tree_root_user']))
                    $redis_tree_root_user = $res_for_redis[0]['redis_tree_root_user'];

                if (strlen($redis_tree_root_user)){
                    $tree = json_decode($redis->get($redis_tree_root_user));
                    if(count($tree) == 1) {
                        if($tree->username == $username)
                            $tree->isactive = 1;
                    } else {
                        for ($i = 0; $i < count($tree); $i++) {
                            if ($tree[$i]->username == $username) {
                                $tree[$i]->isactive = 1;
                                break;
                            }
                        }
                    }
                    $redis->set($redis_tree_root_user, json_encode($tree));
                }

                if ($parent_username) {

                    $res_for_redis = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                        ->setIntegrityCheck(false)
                        ->from(array("binary_user_refences"), array('redis_tree_root_user'))
                        ->where("username = ?", $parent_username)
                    );

                    if (isset($res_for_redis[0]['redis_tree_root_user']))
                        $redis_tree_root_user = $res_for_redis[0]['redis_tree_root_user'];

                    if (strlen($redis_tree_root_user)) {
                        $tree = json_decode($redis->get($redis_tree_root_user));
                        if (count($tree) == 1) {
                            if ($tree->username == $username)
                                $tree->isactive = 1;
                        } else {
                            for ($i = 0; $i < count($tree); $i++) {
                                if ($tree[$i]->username == $username) {
                                    $tree[$i]->isactive = 1;
                                    break;
                                }
                            }
                        }
                        $redis->set($redis_tree_root_user, json_encode($tree));
                    }

                }


                //$common_obj-> sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);
                //$common_obj-> sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $email);
        /*        $to = "thegainbitcoin@gmail.com";
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'A payment has been received';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
		*/
                $to = $buyerEmail;
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Your contract purchase has been activated';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            }else{
                //$kit_status= "update kits set status='Used' WHERE username='" . $username . "' and kit_number='" . $kitNumber . "' ";
                //$usedDate=date('Y-m-d h:i:s'); /* for used kit date and usedby user*/
                //$updat_arr1=array('status'=>'Used','kit_used_by'=>$username,'kit_used_date'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_arr1=array('status'=>'Used','kit_used_by'=>$username,'kit_used_date'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kits_obj->update($updat_arr1,"kit_number='" . $kitNumber . "'");

                $upd_arr2=array('comment'=>'not fully paid','updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data2=$user->update($upd_arr2,"username='".$username."'");

                $update_arr['invoice_status']="0";
                //$query .= ", invoice_status= 0";
                $email .= "<p style='color: red'>The amount paid (".$value." BTC) is not the same as the amount due (".$amtDue." BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
                $userEmail .= "<p style='color: red'>The amount paid (".$value." BTC) is not the same as the amount due (".$amtDue." BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";
                //$common_obj-> sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);
                //$common_obj-> sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $email);
          /*      $to = "thegainbitcoin@gmail.com";
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'A payment has been received';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
*/
                $to = $buyerEmail;
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Your contract purchase has been activated';
                $message = $email;
                $htmlMessage = $email;
             //   $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            }

            $upat_data=$invoices_obj->update($update_arr,"invoice_id='".$invoiceId."'");

            $a=1;

            if(isset($kt_type) && $kt_type!='' && $kt_type=='hardware')
            {
                $full_name=$_POST['full_name'];
                $mobile=$_POST['mobile'];
                $email=$_POST['email'];
                $address1=$_POST['address1'];
                $address2=$_POST['address2'];
                $pincode=$_POST['pincode'];
                $country=$_POST['country'];
                $state=$_POST['state'];
                $city=$_POST['city'];

                $ship_addr_obj=new Gbc_Model_DbTable_Shippingaddress();
                $ship_data=array('username'=>$username,'invoice_id'=>$invoiceId,'full_name'=>$full_name,'mobile'=>$mobile,'email'=>$email,'address1'=>$address1,'address2'=>$address2,'pincode'=>$pincode,'country'=>$country,'state'=>$state,'city'=>$city,'date'=>new Zend_Db_Expr('NOW()'),'status'=>'1');
                $ship_qry=$ship_addr_obj->insert($ship_data);

                //$common_obj->sendMail($email, "admin@gainbitco.in", "Buy Now Clicked", "Your order is placed successfully for invoice id ".$invoiceId. " and kit number ".$kitNumber  );
            }


            $contact_phone = $buyerUserInfo->phone;
            $numbers = array($contact_phone);
            if(!empty($contact_phone) && $contact_phone!=''){
                $message = rawurlencode("Congrats your account has been activated with ".$value." BTC. Visit gainbitcoin.com");
                $numbers = implode(',', $numbers);
                // Prepare data for POST request
                // $data = array('username' => $MSGusername, 'hash' => $MSGhash, 'numbers' => $numbers, "sender" => $MSGsender, "message" => $message);


                $data = 'login='.$MSGusername.'&pword='.$MSGhash.'&mobnum='.$numbers."&senderid=".$MSGsender."&msg=".$message;
                $MsgResponse = $common_obj-> sendMSG($data);

                if(!empty($MsgResponse)){
                    $smslog_obj=new Gbc_Model_DbTable_Smslog();
                    //$saveMessage_arr=array('username'=>$username,'mobile'=>$numbers,'msg'=>$message,'response_code'=>$MsgResponse);
                    //$saveMessage_data=$smslog_obj->insert($saveMessage_arr);
                }
            }
            if(number_format($value,6) <= number_format($kitprice,6)) {
                $ref['username'] = $username;
                $updateBinaryNetwork =$common_obj->insertUpdateBinaryNetwork($ref);
                $purch_complete=1;
            }
            else
            {
                $purch_complete=0;
            }
            $db->commit();
            $data=array('success'=>'success','failure'=>'','tran_flag'=>$purch_complete,'kitprice'=>$kitprice,'amount'=>$amtDue);
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg=$e->getMessage();
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }

	
    public function purchasekit2Action()
    {
        try {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $username=$authUserNamespace->user;
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $user_member_obj = new Gbc_Model_DbTable_Usermember();
            $token = $common_obj->cleanQueryParameter($_POST['token']);
			$kits_obj=new Gbc_Model_DbTable_Gb2Kits();
       		$invoices_obj = new Gbc_Model_DbTable_Gb2Invoices();
			$user = new Gbc_Model_DbTable_Userinfo();
			
            $antixss = new Gbc_Model_Custom_StringLimit();
            foreach($_POST as $key => $value)
            {
                if(isset($value) && $value != ""){
                    $antixss->setEncoding($value, "UTF-8");
                    if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
                        $db->rollBack();
                        $data=array('success'=>'','failure'=>'Invalid Input.');
                        echo json_encode($data);exit;

                    }

                }

            }
			if(empty($username)){
				 $data=array('success'=>'','failure'=>'You are logged out. Login again to use kit.');
                echo json_encode($data);exit;
			}
			
            $kitdetails=$common_obj->checkKitPrice($username, $_POST['kit_number'],'GB2');

            if(!empty($kitdetails) && sizeof($kitdetails)>0) {
                $kitprice=$kitdetails->kit_price;
                $ContractRateBTC=$kitdetails->contract_price;
                $kitNumber=$kitdetails->kit_number;
                $KitLock=$kitdetails->locked;
                $KitContractId=$kitdetails->contract_id;
            } else {
                $db->rollBack();
                $data=array('success'=>'','failure'=>'Invalid Kit Number. Please try another one.');
                echo json_encode($data);exit;
            }
            $kt_type=$common_obj->cleanQueryParameter($_POST['kt_type']);
            $contractId = $common_obj->cleanQueryParameter($_POST["hiddencontractId"]);

            if(!isset($contractId) || $contractId==''){
                $db->rollBack();
                $data=array('success'=>'','failure'=>'Invalid Details');
                echo json_encode($data);exit;

            }
			
            $contractDetails =$common_obj-> getContracts($contractId,'GB2');
            if(!empty($contractDetails) && isset($contractDetails) && sizeof($contractDetails)>0)
            {
                $contractDetails=$contractDetails->toArray();
                $ContractRateInUSD = $contractDetails['total_price'];
            }
			$ContractRate = $ContractRateBTC;
			 $contractDetails['total_price'] = $ContractRateBTC;
			

			//var_dump($contractDetails);
			//exit;
            if($_POST["contract_type"]=='ROI')
            {
                $contractDetails['isReferred']='No';
            }
            else{
                $contractDetails['isReferred']='Yes';
            }
         //   $value=$common_obj->cleanQueryParameter($_POST['total_amount']);
			 $value = $kitprice;
			
		//	die("here");
         //   if(number_format($value,6)<=number_format($kitprice,6)) {
			if($KitContractId == $contractId){
                $txid=$username.'_'.time() . "_" . rand();
                $invoice = $common_obj-> createNewConHwInvoice($contractDetails, $username,$kitNumber, 0,'GB2');
                if($invoice=='failure'){
                    $db->rollBack();
                    $data=array('success'=>'','failure'=>'Error creating invoice:');
                    echo json_encode($data);exit;
                }
                else
                {
                    $invoiceId=$invoice;
                }
                $confirmations=3;
            }// Need to discuss if kit amount is greater than value amount what about remaining kit amount....
            else{
                $db->rollBack();
              // $message = "The amount paid (".$value." BTC) is not the same as the Kit price (".$kitprice." BTC)";
               $message = "You have chosen wrong contract";

                $data=array('success'=>'','failure'=>$message);
                echo json_encode($data);exit;

            }

            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $invoiceDetails = $common_obj-> getUserContracts_gb2("", "", $invoiceId);
            $buyerUsername = $invoiceDetails->username;
            $buyerUserInfo = $misc_obj->getUserInfo($buyerUsername);
            //$buyerEmail = $buyerUserInfo->comm_email;
            if(!empty($buyerUserInfo->comm_email) && $buyerUserInfo->comm_email!='')
            {
                $buyerEmail = $buyerUserInfo->comm_email;
            }
            else
            {
                $buyerEmail = $buyerUserInfo->email_address;
            }

            $kit_inv_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
            $origtxid='';

            $result_kit=$kit_inv_obj->fetchRow($kit_inv_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('ki'=>"kit_invoices"),array('ki.origtxid'))
                ->joinLeft(array('k'=>'kits'),"k.invoice_id  = ki.invoice_id",array('k.invoice_id'))
                ->where("k.kit_number = ?",$kitNumber)
            );

            $origtxid = ($result_kit->origtxid);
            $update_arr=array('confirmations'=>$confirmations,'transactionid'=>$txid,'origtxid'=>$origtxid,'amtPaid'=>$value);
			
            if($confirmations==3){
                $update_arr['invoice_status']="1";
                //array_push($update_arr,"invoice_status","1");
                $email = "<div style='border: solid thin black; padding: 10px'>A payment has been received with the following details:<p>Invoice ID: ".$invoiceId."</p><p>Amount: ".$value." BTC</p><p>Confirmations: ".$confirmations."</p><p>Transaction ID: ".$txid."</p></div>";
                $userEmail = "<div style='border: solid thin black; padding: 10px'>A payment has been received with the following details:<p>Invoice ID: ".$invoiceId."</p><p>Amount: ".$value." BTC</p><p>Confirmations: ".$confirmations."</p><p>Transaction ID: ".$txid."</p></div>";
                $email .= "<p style='color: red'>This purchase has been activated.</p>";
                $userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
                
                $updat_arr1=array('status'=>'Used','kit_used_by'=>$username,'kit_used_date'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kits_obj->update($updat_arr1,"kit_number='" . $kitNumber . "'");

                $upd_arr2=array('isActiveId'=>'1','updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data2=$user->update($upd_arr2,"username='".$username."'");

                $comm_obj = new Gbc_Model_Custom_CommonFunc();
                $binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
            
                $to = $buyerEmail;
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Your contract purchase has been activated';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            }else{
                
                $updat_arr1=array('status'=>'Used','kit_used_by'=>$username,'kit_used_date'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kits_obj->update($updat_arr1,"kit_number='" . $kitNumber . "'");

                $upd_arr2=array('comment'=>'not fully paid','updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data2=$user->update($upd_arr2,"username='".$username."'");

                $update_arr['invoice_status']="0";
                //$query .= ", invoice_status= 0";
                $email .= "<p style='color: red'>The amount paid (".$value." BTC) is not the same as the amount due (".$amtDue." BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
                $userEmail .= "<p style='color: red'>The amount paid (".$value." BTC) is not the same as the amount due (".$amtDue." BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";
                
                $to = $buyerEmail;
                $from = 'admin@gainbitco.in';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Your contract purchase has been activated';
                $message = $email;
                $htmlMessage = $email;
           //     $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            }
            $upat_data=$invoices_obj->update($update_arr,"invoice_id='".$invoiceId."'");
            
            $db->commit();
            $data=array('success'=>'success','failure'=>'','tran_flag'=>$purch_complete,'kitprice'=>$kitprice,'amount'=>$amtDue);
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg=$e->getMessage();
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }
	
	
}