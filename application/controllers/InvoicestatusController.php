<?php
class InvoicestatusController extends Zend_Controller_Action{

    public function init(){

        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

    }

    public function indexAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $this->_helper->layout()->setLayout("dashbord");//dashboard
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            $db->beginTransaction();
            $BasePaymentUrl = "https://api.paybitz.com";
            //$username=$_POST['username'];
            //$bitcoinpay = $_POST['bitcoinpay-status'];
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

            $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
            $token=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['token']);
//			if($authUserNamespace->token==$token){
            //$invoiceId = $_POST['invoice_id'];
            $invoiceId=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['invoice_id']);
            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
            $ip=$misc_obj->get_client_ip();
            $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
            $trans_details_obj=new Gbc_Model_DbTable_Transactiondetails();
            $payment_resp_obj=new Gbc_Model_DbTable_Paymentresponose();
			$username = $authUserNamespace->user;

            $row = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                ->where("invoice_id = ?",$invoiceId));

            $kits_obj=new Gbc_Model_DbTable_Kits();
            $row1 = $kits_obj->fetchRow($kits_obj->select()
                ->where("invoice_id = ?",$invoiceId));
		//	var_dump($_POST);
			if(!empty($row->middleAddr) && !empty($row->amtPaidInMcap) && $row1->payment_gateway == 3){
				$data = array('address' => $row->middleAddr, 'amount' => $row->amtPaidInMcap );
				//	var_dump($data);
				//	echo "<br><br>";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/getdepstatus");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					$Response = curl_exec($ch);
					curl_close($ch);
					
					$Res = json_decode($Response);
					$result = $Res->message;

				//	var_dump($result);
				//	echo "<br><br>";
					if($result == true){
						
						 $rowInvoice = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                		->where("middleAddr = ?",$data['address'])
                		->where("contract_rate = ?",$data['amount'])
                		->where("invoice_status = ?",'0'));
					
						$invoiceId = $rowInvoice->invoice_id;
						
						$user  = $row->username;
						if(!empty($invoiceId)){
						$query2=array('status'=> 'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
						$query2_data=$kits_obj->update($query2," invoice_id = '" . $invoiceId . "' AND (status='Pending' or status='Inactive' or status='Partial Payment')");
						
						$query["invoice_status"] = "1";
						
						 $que_data=$kit_invoices_obj->update($query,$db->quoteInto("invoice_id= ?",$invoiceId));
						
						$db->commit();

						
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$description = "kits activated with invoice_id $invoiceId by $username";
							$saveUserLog=$common_obj->saveUserLog($user,'kit_invoices,kits',$description);
							
					//	echo "success";
						//$arr=array('success'=>'success','failure'=>'');
						 //echo json_encode($arr);

					$arr=array('success'=>'success','failure'=>'','invoiceId'=>$invoiceId,'status'=>'PAID','mcap_price'=>$data['amount'],'mcap-status'=>true);
						}
					//	$this->view->result=$arr;

					}
				//	exit;
			}else{
			
			
			
			

            if(isset($row) && sizeof($row)>0)
            {
                $payment_id = $row->transactionid; //"bQSvCnofMLkCfJgs";//
                $value = $row->contract_rate;
                $username = $row->username;
                $middleAddr = $row->middleAddr;
                if($row1->payment_gateway == '1'){
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/transaction-history/". $payment_id);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
                    ));

                }else{
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "$BasePaymentUrl/merchant/order_id/status/?order_id=$invoiceId");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Token IdJtt10Q7efe3Q4O1vcFhZeR"
                    ));
                }


                $response = curl_exec($ch);
                curl_close($ch);
                $transaction_status = json_decode($response);

                $address = $transaction_status->data->address;
                $confirmation = $transaction_status->data->confirmations;
                $create_time = $transaction_status->data->create_time;
                $currency = $transaction_status->data->currency;
                $denominated_amount = $transaction_status->data->denominated_amount;
                $denominated_currency = $transaction_status->data->denominated_currency;
                $item = $transaction_status->data->item;
                $description = $transaction_status->data->description;
                $paid_amount = $transaction_status->data->paid_amount;
                $paid_currency = $transaction_status->data->paid_currency;
                $payment_id = $transaction_status->data->payment_id;
                $payment_url = $transaction_status->data->payment_url;
                $price = $transaction_status->data->price;

                $server_time = $transaction_status->data->server_time;
                $settled_amount = $transaction_status->data->settled_amount;
                $settled_currency = $transaction_status->data->settled_currency;
                $settled_split_amount = $transaction_status->data->settled_split_amount;
                $settled_split_currency = $transaction_status->data->settled_split_currency;
                $status = $transaction_status->data->status;
                $timeout_time = $transaction_status->data->timeout_time;
                $transaction_fee = $transaction_status->data->transaction_fee;
                $transaction_fee_currency = $transaction_status->data->transaction_fee_currency;
                $transaction_split_fee = $transaction_status->data->transaction_split_fee;
                $transaction_split_fee_currency = $transaction_status->data->transaction_split_fee_currency;
                $txid = $transaction_status->data->txid;

                $reference = $transaction_status->data->reference;


                if($row1->payment_gateway == '2'){
                    $reference = json_encode($reference);
                }
                //print_r($reference);

                $transaction_details_txid_result = $trans_details_obj->fetchRow($trans_details_obj->select()
                    ->where("txid = ?",$txid));
                if(isset($transaction_details_txid_result) && sizeof($transaction_details_txid_result)>0)
                {
                    $transaction_details_id = $transaction_details_txid_result->id;
                    $update_arr=array('confirmations'=>$confirmation ,'status'=>$status,'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $update_data=$trans_details_obj->update($update_arr,$DB->quoteInto("id = ?",$transaction_details_id));

                }
                else
                {

                    //	$insert_arr=array('address'=> $address,'confirmations'=>$confirmation,'create_time'=>$create_time,'currency'=>$currency,'denominated_amount'=>$denominated_amount,'denominated_currency'=>$denominated_currency,'item'=>$item,'description'=>$description,'paid_amount'=>$paid_amount,'paid_currency'=>$paid_currency,'payment_id'=>$payment_id,'payment_url'=>$payment_url,'price'=>$price,'reference'=>$reference,'server_time'=>$server_time,'settled_amount'=>$settled_amount,'settled_currency'=>$settled_currency,'settled_split_amount'=>$settled_split_amount,'settled_split_currency'=>$settled_split_currency,'status'=>$status,'timeout_time'=>$timeout_time,'transaction_fee'=>$transaction_fee,'transaction_fee_currency'=>$transaction_fee_currency,'transaction_split_fee'=>$transaction_split_fee,'transaction_split_fee_currency'=>$transaction_split_fee_currency,'txid'=>$txid);
                    $insert_arr=array('address'=> $address,'confirmations'=>$confirmation,'create_time'=>$create_time,'currency'=>$currency,'denominated_amount'=>$denominated_amount,'denominated_currency'=>$denominated_currency,'item'=>$item,'description'=>$description,'paid_amount'=>$paid_amount,'paid_currency'=>$paid_currency,'payment_id'=>$payment_id,'payment_url'=>$payment_url,'price'=>$price,'reference'=>$reference,'server_time'=>$server_time,'settled_amount'=>$settled_amount,'settled_currency'=>$settled_currency,'settled_split_amount'=>$settled_split_amount,'settled_split_currency'=>$settled_split_currency,'status'=>$status,'timeout_time'=>$timeout_time,'transaction_fee'=>$transaction_fee,'transaction_fee_currency'=>$transaction_fee_currency,'transaction_split_fee'=>$transaction_split_fee,'transaction_split_fee_currency'=>$transaction_split_fee_currency,'txid'=>$txid);

                    $insert_data=$trans_details_obj->insert($insert_arr);
                    //	 print_r($insert_data);
                    //die("here");
                }

                if($row1->payment_gateway == '1') {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc/" . $payment_id);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
                    ));

                    $response = curl_exec($ch);
                    curl_close($ch);
                    //print_r($response);

                    $data = json_decode($response);
                    $address = $data->data->address;
                    $confirmations = $data->data->confirmations;
                    $create_time = $data->data->create_time;
                    $currency = $data->data->currency;
                    $item = $data->data->item;
                    $description = $data->data->description;
                    $paid_amount = $data->data->paid_amount;
                    $paid_currency = $data->data->paid_currency;
                    $payment_id = $data->data->payment_id;
                    $payment_url = $data->data->payment_url;
                    $price = $data->data->price;

                    $server_time = $data->data->server_time;
                    $settled_amount = $data->data->settled_amount;
                    $settled_currency = $data->data->settled_currency;
                    $status = $data->data->status;
                    $timeout_time = $data->data->timeout_time;
                    $txid = $data->data->txid;

                    $reference = json_decode($data->data->reference);
                    $invoice_id = $reference->{'invoice-id'};
                    $username = $reference->username;

                    $payment_response_txid_result = $payment_resp_obj->fetchRow($payment_resp_obj->select()
                        ->where("txid = ?", $txid));

                    if (isset($payment_response_txid_result) && sizeof($payment_response_txid_result) > 0) {
                        $id = $payment_response_txid_result->id;
                        $updateResponse_arr = array('confirmations' => $confirmations, 'status' => $status, 'updated_on' => new Zend_Db_Expr('NOW()'));
                        $updateResponse = $payment_resp_obj->update($updateResponse_arr, $DB->quoteInto("id = ?", $id));

                    } else {
                        $insert_arr = array('address' => $address, 'confirmations' => $confirmation, 'create_time' => $create_time, 'currency' => $currency, 'item' => $item, 'description' => $description, 'paid_amount' => $paid_amount, 'paid_currency' => $paid_currency, 'payment_id' => $payment_id, 'payment_url' => $payment_url, 'price' => $price, 'invoice_id' => $invoice_id, 'username' => $username, 'server_time' => $server_time, 'settled_amount' => $settled_amount, 'settled_currency' => $settled_currency, 'status' => $status, 'timeout_time' => $timeout_time, 'txid' => $txid);
                        $insert_data = $payment_resp_obj->insert($insert_arr);

                    }
                }

                    if (($paid_amount >= $value) && ($status == "confirmed")){

                      
                        //$query = "update kit_invoices set confirmations=" . $confirmations . ",origtxid =  '".$txid."',middleAddr='" . $middleAddr . "', amtPaid='" . $paid_amount . "', updated_on= now() ";
                        if ($confirmations >= 1 || $confirmation >= 1 ) {
                            $kits_obj=new Gbc_Model_DbTable_Kits();
                            $KitDetail=array();
                            $KitDetail=$kits_obj->fetchRow($kits_obj->select()
                                ->setIntegrityCheck(false)
                                ->from(array('kits'=>"kits"),array('kits.kit_number'))
                                ->joinLeft(array('user_info'=>'user_info'),"user_info.username = kits.username",array('user_info.phone'))
                                ->where("invoice_id = ?",$invoiceId));
                            $kit_number = '';
                            $contact_phone = '';

                            if(isset($KitDetail) && sizeof($KitDetail)>0)
                            {
                                $kit_number = $KitDetail->kit_number;
                                $contact_phone = $KitDetail->phone;
                            }



                            // Message details
                            // $numbers = array(918123456789, 918987654321);


                            $email = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".BASE."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p><p>Amount: " . $value . " BTC</p><p>Confirmations: " . $confirmations . "</p><p>Transaction ID: " . $txid . "</p></div>";
                            $userEmail = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".BASE."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p><p>Amount: " . $value . " BTC</p><p>Confirmations: " . $confirmations . "</p><p>Transaction ID: " . $txid . "</p></div>";

                            $invoiceDetails = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                                ->setIntegrityCheck(false)
                                ->from(array('kit_invoices'=>"kit_invoices"),array('sum(contract_rate) as total_paid', 'username'))
                                ->where("invoice_id = ?",$invoiceId)
						  		->where("invoice_status = ?",'0'));

                            if(isset($invoiceDetails) && sizeof($invoiceDetails)>0) {
								
								  //echo "here";
								$queryemail="update kit_invoices set confirmations=" . $confirmations . ",origtxid =  '".$txid."',middleAddr='" . $middleAddr . "', amtPaid='" . $paid_amount . "', updated_on= now() ";
								$query=array('confirmations'=>$confirmations,'origtxid'=>$txid,'middleAddr'=>$middleAddr,'amtPaid'=>$paid_amount ,'updated_on'=>new Zend_Db_Expr('NOW()'));
								
                                $amtDue = $invoiceDetails->total_paid;
                                //$amtDue=$value;
                                //printArr($value."-".$amtDue);
                                //$amtDue = $value;
                                if ($paid_amount < $amtDue) {
                                    $email .= "<p style='color: red'>The amount paid (" . $value . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
                                    $userEmail .= "<p style='color: red'>The amount paid (" . $value . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";

                                    $queryemail .= ", comment = '<div style = \"color:red; font-weight:bold;\">We have received only partial payment. Payment due:'".$amtDue - $paid_amount."'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>' ";
                                    $query['comment'] ='<div>We have received only partial payment. Payment due:'.$amtDue - $paid_amount.'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>';

                                    $query2=array('status'=> 'Partial Payment','updated_on'=>new Zend_Db_Expr('NOW()'));
                                    //$query2_data=$kits_obj->update($query2,"invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive'|| status='Partial Payment'");
                                    $query2_data=$kits_obj->update($query2,$DB->quoteInto("invoice_id=?",$invoiceId) . ' AND ' . $DB->quoteInto("status='Pending' || status='Inactive'|| status='Partial Payment'"));


                                } else {
                                    $queryemail .= ", invoice_status=1";
                                    $query['invoice_status']='1';

                                    $email .= "<p style='color: red'>This purchase has been activated.</p>";
                                    $userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
                                    $query2=array('status'=> 'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
                                    $query2_data=$kits_obj->update($query2,"  invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive' || status='Partial Payment')");

                                    if(!empty($query2_data))
                                    {
                                        $calculateKits = $Gbc_Model_Custom_func_obj-> CalculateAvailableKits($paid_amount);
                                    }
                                }
                                $buyerUsername='';
                                $buyerEmail='';
                                $invoiceDetails = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                                    ->where("invoice_id = ?",$invoiceId));
                                //	print_r($invoiceDetails);


                                $buyerUsername = $invoiceDetails->username;

                                $buyerUserInfo =$misc_obj-> getUserInfo($buyerUsername);
                                $buyerEmail = $buyerUserInfo->comm_email;
                                if(empty($buyerEmail) || ($buyerEmail==''))
                                {
                                    $buyerEmail = $buyerUserInfo->email_address;
                                }

                                //$Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);

                                //$Gbc_Model_Custom_func_obj->sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $userEmail);
                                /*		$to = "thegainbitcoin@gmail.com";
                                        $from = 'admin@gainbitco.in';
                                        $replyTo = 'thegainbitcoinhelp@gmail.com';
                                        $subject = 'Buy Now Clicked';
                                        $message = $email;
                                        $htmlMessage = $email;
                                        $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                                */
                                $to = $buyerEmail;
                                $from = 'admin@gainbitco.in';
                                $replyTo = 'thegainbitcoinhelp@gmail.com';
                                $subject = 'Buy Now Clicked';
                                $message = $userEmail;
                                $htmlMessage = $userEmail;
                                $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                            
								  $queryemail .= " where invoice_id='" . $invoiceId . "'";
						 $que_data=$kit_invoices_obj->update($query,$DB->quoteInto("invoice_id= ?",$invoiceId));
							
							}
                        }
                      
                        //echo $query;
                        //printArr($query);
                        //printArr($email);
                        //printArr($userEmail);
                        //$Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $queryemail);
                        /*	$to = "thegainbitcoin@gmail.com";
                            $from = 'admin@gainbitco.in';
                            $replyTo = 'thegainbitcoinhelp@gmail.com';
                            $subject = 'A payment has been received';
                            $message = $queryemail;
                            $htmlMessage = $queryemail;
                            $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                       */
                       

                        $status_row = $kits_obj->fetchRow($kits_obj->select()
                            ->where("invoice_id = ?",$invoiceId));
                        $status='';
                        if(isset($status_row) && sizeof($status_row)>0)
                        {
                            $status=$status_row->status;
                        }

                    }




                }

                $db->commit();
                $bitcoinpay = $_POST['bitcoinpay-status'];
                if(empty($bitcoinpay) || $bitcoinpay=='')
                {
                    $bitcoinpay='';
                }
                $arr=array('success'=>'success','failure'=>'','invoiceId'=>$invoiceId,'status'=>$status,'price'=>$value,'bitcoinpay-status'=>$bitcoinpay);

                /*	}else{
                        //$data=array('success'=>'','failure'=>'Invalid Request Found.');
                        //echo json_encode($data);exit;
                        $msg = 'Invalid Request Found.';
                        $authUserNamespace->errmsg=$msg;
                    }
                */
			}

            }
        catch(Exception $e)
		{
            $db->rollBack();
            $arr=array('success'=>'','failure'=>'Something went wrong!! Please try again.');

        }
		$this->view->result=$arr;

	}
	
	 public function kitstatusAction(){
		 
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $this->_helper->layout()->setLayout("dashbord");//dashboard
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            $db->beginTransaction();
            $BasePaymentUrl = "https://api.paybitz.com";
            //$username=$_POST['username'];
            //$bitcoinpay = $_POST['bitcoinpay-status'];
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

            $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
            $token=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['token']);
//			if($authUserNamespace->token==$token){
            //$invoiceId = $_POST['invoice_id'];
            $invoiceId=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['invoice_id']);
            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
            $ip=$misc_obj->get_client_ip();
            $kit_invoices_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
			$kits_obj=new Gbc_Model_DbTable_Gb2Kits();
			$Gb2Binaryuserincome_obj=new Gbc_Model_DbTable_Gb2Binaryuserincome();
			$Gb2Contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();
            $trans_details_obj=new Gbc_Model_DbTable_Transactiondetails();
            $payment_resp_obj=new Gbc_Model_DbTable_Paymentresponose();
			$username = $authUserNamespace->user;

            $row = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                ->where("invoice_id = ?",$invoiceId));

            
            $row1 = $kits_obj->fetchRow($kits_obj->select()
                ->where("invoice_id = ?",$invoiceId));
		//	var_dump($_POST);
			
			if(!empty($row->middleAddr) && !empty($row->contract_rate) && $row1->payment_gateway == 3){
				$data = array('address' => $row->middleAddr, 'amount' => $row->contract_rate );
				//	var_dump($data);
				//	echo "<br><br>";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/getdepstatus");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					$Response = curl_exec($ch);
					curl_close($ch);
				//	$Response = '{"message":true}';
					$Res = json_decode($Response);
					$result = $Res->message;

				//	var_dump($result);
			//		echo "<br><br>";
					if($result == true){
						 $rowInvoice = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                		->where("middleAddr = ?",$data['address'])
                		->where("contract_rate = ?",$data['amount'])
                		->where("invoice_status = ?",'0'));
					
					$invoiceId = $rowInvoice->invoice_id;
						
						$user  = $row->username;
					
					$referer_user = isset($row->referer_user)?$row->referer_user:'';
					if(!empty($invoiceId)){
						$query2=array('status'=> 'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
						$query2_data=$kits_obj->update($query2," invoice_id = '" . $invoiceId . "' AND (status='Pending' or status='Inactive' or status='Partial Payment')");
						
						$query["invoice_status"] = "1";
						
						 $que_data=$kit_invoices_obj->update($query,$db->quoteInto("invoice_id= ?",$invoiceId));
						
						$contract_id = $row1->contract_id;
						$kits_qty = $row->kits_qty;
						$contract_query["available_limit"] = new Zend_Db_Expr("available_limit - $kits_qty");
						$Gb2Contracts_obj=$Gb2Contracts_obj->update($contract_query,$db->quoteInto("contract_id= ?",$contract_id));
						
						
							if(!empty($referer_user)){
								$amount = round(($data['amount'] * 2)/100,4);
								$insert = array('ben_username' => $referer_user, 'from_username'=> $user, 'amount'=> $amount, 'invoice_id' => $invoiceId, 'status' => '1', 'percentage'=> '2');
								$insert1 = $Gb2Binaryuserincome_obj->insert($insert);

							}
						}
						
						$db->commit();

						
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$description = "kits activated with invoice_id $invoiceId by $username";
							$saveUserLog=$common_obj->saveUserLog($user,'kit_invoices,kits',$description);
							
					//	echo "success";
						//$arr=array('success'=>'success','failure'=>'');
						 //echo json_encode($arr);

						 $arr=array('success'=>'success','failure'=>'','invoiceId'=>$invoiceId,'status'=>'PAID','mcap_price'=>$data['amount'],'mcap-status'=>true);
					//	$this->view->result=$arr;

					}
				//	exit;
			}

            }
        catch(Exception $e)
		{
            $db->rollBack();
            $arr=array('success'=>'','failure'=>'Something went wrong!! Please try again.');

        }
		$this->view->result=$arr;

	}
	
}
