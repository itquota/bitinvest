<?php
class PaymentconfirmationscronController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$payment_resp_obj=new Gbc_Model_DbTable_Paymentresponose();
		$tran_details = new Gbc_Model_DbTable_Transactiondetails();
		$kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$cronName = "payment_confirmations_cron";


		$start = date('Y-m-d H:i:s');

		$saveUserLog=$common_obj->updateCronStatus($cronName,$start,'');
		$date = date('Y-m-d');
			
		$payment_responses_qry = ("select invoice_id,payment_id from payment_response where confirmations < 1 and status in ('confirmed','received') and created_on BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW() and invoice_id in (select invoice_id from kits where invoice_id = payment_response.invoice_id and status not in('active','used'))");

		$payment_responses=$payment_resp_obj->fetchAll($payment_resp_obj->select()
		->setIntegrityCheck(false)
		->from(array('payment_response'),array('invoice_id','payment_id'))
		->where("confirmations < 1 and status in ('confirmed','received') and created_on BETWEEN DATE_SUB(NOW(), INTERVAL 6 DAY) AND NOW() and invoice_id in (select invoice_id from kits where invoice_id = payment_response.invoice_id and status not in('active','used'))")
		);


		$kit_invoices=$kit_invoices_obj->fetchAll($kit_invoices_obj->select()
		->where("origtxid ='' AND  transactionid !='' AND creared_on BETWEEN DATE_SUB('".new Zend_Db_Expr('NOW()')."', INTERVAL 6 DAY) AND '".new Zend_Db_Expr('NOW()')."'  and contract_rate<=15 and comment is null")
		);

		if(!empty($kit_invoices) && sizeof($kit_invoices)>0)
		{
			for($i=0;$i<sizeof($kit_invoices);$i++)
			{
				$unconfirmedRequests[$i]['invoice_id'] = $kit_invoices[$i]['invoice_id'];
				$unconfirmedRequests[$i]['kits_qty'] = $kit_invoices[$i]['kits_qty'];
				$unconfirmedRequests[$i]['confirmations'] = $kit_invoices[$i]['confirmations'];
				$unconfirmedRequests[$i]['transactionid'] = $kit_invoices[$i]['transactionid'];
				$unconfirmedRequests[$i]['invoice_status'] = $kit_invoices[$i]['invoice_status'];
				$unconfirmedRequests[$i]['username'] = $kit_invoices[$i]['username'];
				$unconfirmedRequests[$i]['contract_rate'] = $kit_invoices[$i]['contract_rate'];
				$unconfirmedRequests[$i]['origtxid'] = $kit_invoices[$i]['origtxid'];

			}
		}
		if(!empty($kit_invoices) && sizeof($kit_invoices)>0)
		{
			$i++;
		}

		if(!empty($payment_responses)){
			for($j=0;$j<sizeof($payment_responses);$j++){
				$unconfirmedRequests[$i]['invoice_id'] = $payment_responses[$j]['invoice_id'];
				$unconfirmedRequests[$i]['payment_id'] = $payment_responses[$j]['payment_id'];
				$i++;
			}
		}

		if(!empty($unconfirmedRequests) && sizeof($unconfirmedRequests)>0){
			foreach($unconfirmedRequests as $unconfirmedRequest){
				$payment_id = !empty($unconfirmedRequest['transactionid'])?$unconfirmedRequest['transactionid']:$unconfirmedRequest['payment_id'];
				$invoice_id = $unconfirmedRequest['invoice_id'];
				$buyerUsername = $unconfirmedRequest["username"];
				if(!empty($kit_invoices_rows)){
					$row=$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
					->where("invoice_id = '".$invoice_id."'")
					);
				}
				else
				{
					$buyerUsername = $unconfirmedRequest["username"];
				}
				if(!empty($buyerUsername)){
					$buyerUserInfo = $common_obj->getUserInfo($buyerUsername);
					$buyerEmail = $buyerUserInfo->email_address;
				}
				$value = $row->contract_rate;
				$username = $row->username;
				$middleAddr = $row->middleAddr;
					

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc/". $payment_id);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				 "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
				 ));

				 $response = curl_exec($ch);
				 curl_close($ch);
				 //print_r($response);

				 $data =json_decode($response);
				 $confirmations = $data->data->confirmations;
				 $paid_amount = $data->data->paid_amount;
				 $txid = $data->data->txid;
				 $status = $data->data->status;

				 if($confirmations >= 1){

				 	$checkPaymentResponse=$payment_resp_obj->fetchRow($payment_resp_obj->select()
				 	->where("payment_id = '$payment_id'")
				 	);

				 	if(!empty($checkPaymentResponse) && sizeof($checkPaymentResponse)>0){
				 		$upd_arr=array('confirmations'=>$confirmations,'status'=>$status,'updated_on'=>new Zend_Db_Expr('NOW()'));
				 		$updatePaymentResponse = $payment_resp_obj->update($upd_arr,"payment_id = '$payment_id' and invoice_id = '$invoice_id'");

				 	}else{
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

				 		$insert_arr=array('address'=>$address,'confirmations'=>$confirmations,'create_time'=>$create_time,'currency'=>$currency,'item'=>$item,'description'=>$description,'paid_amount'=>$paid_amount,'paid_currency'=>$paid_currency,'payment_id'=>$payment_id,'payment_url'=>$payment_url,'price'=>$price,'invoice_id'=>$invoice_id,'username'=>$username,'server_time'=>$server_time,'settled_amount'=>$settled_amount,'settled_currency'=>$settled_currency,'status'=>$status,'timeout_time'=>$timeout_time,'txid'=>$txid);
				 		$insertResponse = $payment_resp_obj->insert($insert_arr);

				 		// echo "insert into payment_response(address,confirmations,create_time,currency,item,description,paid_amount,paid_currency,payment_id,payment_url,price,invoice_id,username,server_time,settled_amount,settled_currency,status,timeout_time,txid) values('$address',$confirmations,$create_time,'$currency','$item','$description',$paid_amount,'$paid_currency','$payment_id','$payment_url',$price,'$invoice_id','$username','$server_time',$settled_amount,'$settled_currency','$status','$timeout_time','$txid')";

				 	}
				 }
				 $ch = curl_init();
				 curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/transaction-history/". $payment_id);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				 curl_setopt($ch, CURLOPT_HEADER, FALSE);
				 curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				 "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
				 ));

				 $response = curl_exec($ch);
				 curl_close($ch);

				 $transaction_status = json_decode($response);
				 $transaction_confirmations = $transaction_status->data->confirmations;
				 $tran_status = $transaction_status->data->status;

				 if($transaction_confirmations >= 1){
				 	$checkTransactionResponse=$tran_details->fetchRow($tran_details->select()
				 	->where("payment_id = '$payment_id'")
				 	);


				 	if(!empty($checkTransactionResponse) && sizeof($checkTransactionResponse)>0){
				 		$upd_data=array('confirmations'=>$transaction_confirmations,'updated_on'=>new Zend_Db_Expr('NOW()'));
				 		$updateTransactionResponse=$tran_details->update($upd_data,"payment_id = '$payment_id'");

				 	}else{
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
				 		$tran_status = $transaction_status->data->status;
				 		$timeout_time = $transaction_status->data->timeout_time;
				 		$transaction_fee = $transaction_status->data->transaction_fee;
				 		$transaction_fee_currency = $transaction_status->data->transaction_fee_currency;
				 		$transaction_split_fee = $transaction_status->data->transaction_split_fee;
				 		$transaction_split_fee_currency = $transaction_status->data->transaction_split_fee_currency;
				 		$txid = $transaction_status->data->txid;

				 		$reference = $transaction_status->data->reference;

				 		$insert_data=array('address'=>$address,'confirmations'=>$confirmation,'create_time'=>$create_time,'currency'=>$currency,'denominated_amount'=>$denominated_amount,'denominated_currency'=>$denominated_currency,'item'=>$item,'description'=>$description,'paid_amount'=>$paid_amount,'paid_currency'=>$paid_currency,'payment_id'=>$payment_id,'payment_url'=>$payment_url,'price'=>$price,'reference'=>$reference,'server_time'=>$server_time,'settled_amount'=>$settled_amount,'settled_currency'=>$settled_currency,'settled_split_amount'=>$settled_split_amount,'settled_split_currency'=>$settled_split_currency,'status'=>$tran_status,'timeout_time'=>$timeout_time,'transaction_fee'=>$transaction_fee,'transaction_fee_currency'=>$transaction_fee_currency,'transaction_split_fee'=>$transaction_split_fee,'transaction_split_fee_currency'=>$transaction_split_fee_currency,'txid'=>$txid);
				 		$insertResponse=$tran_details->insert($insert_data);

				 	}
				 	$confirmations = $transaction_confirmations;
				 	$status =  $tran_status;
				 }

				 if (($paid_amount >= $value) && ($status == "confirmed")){
				 	//$query = "update kit_invoices set confirmations=" . $confirmations . ",origtxid =  '".$txid."',middleAddr='" . $middleAddr . "', amtPaid='" . $paid_amount . "', updated_on= now() ";

				 	$updat_arr=array('confirmations'=>$confirmations,'origtxid'=>$txid,'middleAddr'=>$middleAddr,'amtPaid'=>$paid_amount,'updated_on'=>new Zend_Db_Expr('NOW()'));
				 	if ($confirmations >= 1) {
				 		$ExistingPaymentId =$kits_obj->fetchRow($kits_obj->select()
				 		->setIntegrityCheck(false)
				 		->from(array('kits' =>'kits'),array('kits.kit_number'))
				 		->joinLeft(array('user_info'=>'user_info'),"kits.username = user_info.username",array('user_info.phone'))
				 		->where("invoice_id = '$invoice_id'"));

				 		$kit_number = $KitDetails['kit_number'];
				 		$contact_phone = $KitDetails['phone'];

				 		$numbers = array($contact_phone);

				 		if(!empty($contact_phone)){
				 			$message = rawurlencode('Greetings from Gainbitcoin. Your payment is successful and your kit is now activated. Proceed to step 2 in My Purchases. Visit gainbitcoin.com');
				 			$numbers = implode(',', $numbers);

				 			// Prepare data for POST request
				 			// $data = array('username' => $MSGusername, 'hash' => $MSGhash, 'numbers' => $numbers, "sender" => $MSGsender, "message" => $message);
				 			$data = 'login='.MSGusername.'&pword='.MSGhash.'&mobnum='.$numbers."&senderid=".MSGsender."&msg=".$message;
				 			$MsgResponse = $common_obj->sendMSG($data);

				 			if(!empty($MsgResponse)){
				 				//$saveMessage = mysql_query("insert into sms_log(username,mobile,msg,response_code) values('$username','$numbers','$message','$MsgResponse')");
				 			}
				 		}
				 		$email = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".$rootURL."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p><p>Amount: " . $value . " BTC</p><p>Confirmations: " . $confirmations . "</p><p>Transaction ID: " . $txid . "</p></div>";
				 		$userEmail = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".$rootURL."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p><p>Amount: " . $value . " BTC</p><p>Confirmations: " . $confirmations . "</p><p>Transaction ID: " . $txid . "</p></div>";

				 		$result=$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
				 		->setIntegrityCheck(false)
				 		->from(array('kit_invoices' =>'kit_invoices'),array('sum(contract_rate) as total_paid', 'username'))
				 		->where("invoice_id = '$invoice_id'"));

				 		if(!empty($result) && sizeof($result)>0)
				 		{
				 			$invoiceDetails['total_paid']=$result->total_paid;
				 			$invoiceDetails['username']=$result->username;
				 		}
				 		if(!empty($result) && sizeof($result)>0)
				 		{
				 			$amtDue = $invoiceDetails["total_paid"];
				 			if ($paid_amount < $amtDue) {
				 				$email .= "<p style='color: red'>The amount paid (" . $value . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
				 				$userEmail .= "<p style='color: red'>The amount paid (" . $value . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";

				 				$query .= ", comment = '<div style = \"color:red; font-weight:bold;\">We have received only partial payment. Payment due:'".$amtDue - $paid_amount."'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>' ";
				 				$updat_arr["comment"] = "<div style = \"color:red; font-weight:bold;\">We have received only partial payment. Payment due:'".$amtDue - $paid_amount."'<div>Please contact support@gainbitcoin.com to activate kit.</div></div> ";

				 				$query2_arr=array('status'=>'Partial Payment','updated_on'=>new Zend_Db_Expr('NOW()'));
				 				$query2=$kits_obj->update($query2_arr,"invoice_id = '" . $invoice_id . "' AND (status='Pending' || status='Inactive'|| status='Partial Payment')");
				 			}
				 			else
				 			{
				 				$updat_arr["invoice_status"]="1";
				 				$email .= "<p style='color: red'>This purchase has been activated.</p>";
				 				$userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
				 				$query2_arr=array('status'=>'Active','updated_on'=>new Zend_Db_Expr('NOW()'));
				 				$query2=$kits_obj->update($query2_arr,"invoice_id = '" . $invoice_id . "' AND status in ('Pending','Inactive','Partial Payment')");
				 				$calculateKits = $common_obj->CalculateAvailableKits($paid_amount);
				 			}
				 			//$common_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);
				 			//$common_obj->sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $userEmail);
				 /*			$to = 'thegainbitcoin@gmail.com'; 
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
							$message = $userEmail;
							$htmlMessage = $userEmail;
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
				 		}
				 	}
				 	$upd_qry=$kit_invoices_obj->update($updat_arr,"invoice_id='" . $invoice_id . "'");
				 	//$common_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $query);
				 /*			$to = 'thegainbitcoin@gmail.com'; 
							$from = 'admin@gainbitco.in';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = 'A payment has been received';
							$message = $query;
							$htmlMessage = $query;
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
				*/
				 }
			}

		}
		$cronName = "payment_confirmations_cron";
		// updateCronStatus($cronName);
		$end = date('Y-m-d H:i:s');
		// echo $end;
		$common_obj->updateCronStatus($cronName,'',$end);
		echo "success";exit;
	}

}
