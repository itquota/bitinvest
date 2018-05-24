<?php
class AddkitapiController extends Zend_Controller_Action{

	public function init(){

		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){
		//if(trim($_REQUEST["total_amount"])==0 && trim($_REQUEST["real_total_amount"])==0)
		if(trim($this->_request->getParam("total_amount"))==0 && trim($this->_request->getParam("real_total_amount"))==0)
		{
			$data=array('success'=>'','failure'=>'There are something errors!. Go back and try it again.');
			echo json_encode($data);exit;
		}
		$db = Zend_Db_Table::getDefaultAdapter();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$db->beginTransaction();
		$kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
		$req_resp_obj=new Gbc_Model_DbTable_Requestresponse();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//if($_REQUEST['kit_type']==1){
		if($this->_request->getParam("id")==1){
			$kit_type='Referral';
			$paid_amount = 1;
		}
		//else if($_REQUEST['kit_type']==6){
		else if($this->_request->getParam("kit_type")==6){
			$kit_type='Referral';
			$paid_amount = 0.5;
		}
		//else if($_REQUEST['kit_type']==4){
		else if($this->_request->getParam("kit_type")==4){
			$kit_type='Silver';
			// $paid_amount = 12.5;
			$paid_amount = 12.99;

			$AvailableKits = $common_obj->AvailableSilverKits($MaxSilverKits);

			if($AvailableKits < 1){
				$db->rollBack();
				$msg = "Kit not available";
				$data=array('success'=>'','failure'=>$msg);
				echo json_encode($data);exit;
			}
		}
		//else if($_REQUEST['kit_type']==5){
		else if($this->_request->getParam("kit_type")==5){
			$kit_type='Gold';
			$paid_amount = 25;
		}
		//else if($_REQUEST['kit_type']==2){
		else if($this->_request->getParam("kit_type")==2){
			$kit_type='ROI';
			$paid_amount = 0.1;
		}
		//else if($_REQUEST['kit_type']==7){
		else if($this->_request->getParam("kit_type")==7){
			$kit_type='ROI';
			$paid_amount = 0.2;
		}

		//$noOfKits = $_REQUEST["no_of_kits"];
		$noOfKits = $this->_request->getParam("no_of_kits");
		$KitsPrice = round($paid_amount * $noOfKits,2);

		//if(($KitsPrice == $_REQUEST["total_amount"]) && ($KitsPrice == $_REQUEST["real_total_amount"] )){
		if(($KitsPrice == $this->_request->getParam("total_amount")) && ($KitsPrice == $this->_request->getParam("real_total_amount") )){
			//$contract_rate = $price_in_btc = $_REQUEST["total_amount"];
			$contract_rate = $price_in_btc = $this->_request->getParam("total_amount");
			//$realPrice = $_REQUEST["real_total_amount"]/$noOfKits;
			$realPrice = $this->_request->getParam("real_total_amount")/$noOfKits;
				
		}else{
			$db->rollBack();
			$msg = "There is something error!. Go back and try it again.";
			$data=array('success'=>'','failure'=>$msg);
			echo json_encode($data);exit;
		}
		$shipAddress ='' ;
		$email = '';



		$invoiceArray=$common_obj->createInvoiceForKit($contract_rate, $username, 0, $shipAddress, $email, $noOfKits);
		$userNameArra =$common_obj-> createKits($conn, $invoiceId, $noOfKits, $username,$realPrice,$kit_type);

		$add=$common_obj->getBitAddr();
		if($add->static_flag==1)
		{
			$my_bitcoin_address=$add->bit_coin_static;
		}
		else{
			$my_bitcoin_address=$common_obj->sslDec($add->bit_coin_address);
		}
		$payment_mode = $add->payment_mode;
		if(($payment_mode =="static") || ($price_in_btc >5)){
			$kits_obj=new Gbc_Model_DbTable_Kits();
			$kits_pay_obj=new Gbc_Model_DbTable_Kitspayment();
			$inputAddress = $my_bitcoin_address;
			$update=date('Y-m-d h:i:s');
			$payment_mode = "static";

			$upd_arr=array('payment_mode'=>$payment_mode,'updated_on'=>'now()');
			$upd_data=$kits_pay_obj->update($upd_arr,$DB->quoteInto("invoice_id = ?",$invoiceId));



			$updat_arr=array('middleAddr'=>$inputAddress,'updated_on'=>'now()');
			$updat_data=$kit_invoices_obj->update($updat_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

			$update_arr=array('status'=>'Pending','updated_on'=>'now()');
			$update_data=$kits_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));
		/*
			$email = "<div>Buy Now has been clicked for the following invoice id: ".$invoiceId.". Coins will be sent to: ".$inputAddress." by the customer.</div>";
			//sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "Buy Now Clicked", $email);
			$to = "thegainbitcoin@gmail.com";
			$from = 'admin@gainbitco.in';
			$replyTo = 'thegainbitcoinhelp@gmail.com';
			$subject = 'Buy Now Clicked';
			$message = $email;
			$htmlMessage = $email;
			$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
		*/
		}
		else
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);

			curl_setopt($ch, CURLOPT_POST, TRUE);

			curl_setopt($ch, CURLOPT_POSTFIELDS, "{
		  \"settled_currency\": \"BTC\",
		  \"return_url\": \"http://gainbitcoin.com/cpnew/view/bitcoinpay/invoice_status.php?invoice-id=$invoiceId\",
		  \"notify_url\": \"http://gainbitcoin.com/cpnew/view/bitcoinpay/invoice_status.php?invoice-id=$invoiceId\",
		  \"notify_email\": \"thegainbitcoinhelp@gmail.com\",
		  \"price\": $price_in_btc,
		  \"currency\": \"BTC\",
		  \"reference\": {
			\"invoice-id\": \"$invoiceId\",
			\"username\": \"$username\"
		   
		  },
		  \"item\": \"Invoice #$invoiceId\",
		  \"description\": \"Please send \"
		}");

			//curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "Content-Type: application/json",
		  "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
		  ));

		  $Response = curl_exec($ch);
		  curl_close($ch);

		  // $check_payment_status = json_decode($response);
		  // $payment_url = $Response->data->payment_url; // echo $payment_url; //die;
		  // $payment_id = $check_payment_status->data->payment_id;
		  // $paid_amount_in_btc = $check_payment_status->data->paid_amount;
		  // $address_id = $check_payment_status->data->address;

		  // print_r($Response);
		  $check_payment_status = json_decode($Response);
		  // var_dump($check_payment_status);
		  $payment_url = $check_payment_status->data->payment_url;
		  $payment_id = $check_payment_status->data->payment_id;

		  $inputAddress = $check_payment_status->data->address;
		  if(is_null($inputAddress)){
		  	$inputAddress = $my_bitcoin_address;
		  }

		  /*$ExistingPaymentId =$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
		  ->setIntegrityCheck(false)
		  ->from(array('kv' =>'kit_invoices'),array('kv.transactionid'))
		  ->where("invoice_id='".$invoiceId."' and transactionid !=''"));*/
		  
		  $ExistingPaymentId =$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
		  ->setIntegrityCheck(false)
		  ->from(array('kv' =>'kit_invoices'),array('kv.transactionid'))
		  ->where("invoice_id=?",$invoiceId)
		  ->where("transactionid != ?",""));
		  

		  //$update=date('Y-m-d h:i:s');
		  // var_dump($ExistingPaymentId);
		  if(!isset($ExistingPaymentId) || empty($ExistingPaymentId) || sizeof($ExistingPaymentId)<=0){
		  	// echo "here";
		  	$update_arr=array('middleAddr'=>$inputAddress ,'transactionid'=>$payment_id,'updated_on'=>'now()');
		  	$update_data=$kit_invoices_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

		  }else{


		  	$insrt_arr=array('invoice_id'=>$invoiceId ,'response'=>$Response);
		  	$insrt_data=$req_resp_obj->insert($insrt_arr);


		  }
		  $updat_arr=array('status'=>'Pending','updated_on'=>'now()');
		  $updat_data=$kit_invoices_obj->update($updat_arr,$DB->quoteInto("invoice_id= ?",$invoiceId));
		   
		  $db->commit();
	/*
		  // echo $query1;
		  // var_dump($result);
		  $email = "<div>Buy Now has been clicked for the following invoice id: ".$invoiceId.". Coins will be sent to: ".$inputAddress." by the customer.</div>";
		  //sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "Buy Now Clicked", $email);
		  $to = "thegainbitcoin@gmail.com";
		  $from = 'admin@gainbitco.in';
		  $replyTo = 'thegainbitcoinhelp@gmail.com';
		  $subject = 'Buy Now Clicked';
		  $message = $email;
		  $htmlMessage = $email;
		  $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
	*/
		}


	}

}
