<?php
class makekitpaymentController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		if(!empty($_POST['invoice_id'])){
			$invoiceId = $_POST['invoice_id'];
			$invoiceDetails =$common_obj-> getKitInvoiceDetails($invoiceId);
				
			if(!empty($invoiceDetails) && isset($invoiceDetails) && sizeof($invoiceDetails)>0){

				$price_in_btc = $invoiceDetails->contract_rate;
				$noOfKits = $invoiceDetails->kits_qty;
				$payment_url = "https://bitcoinpay.com/en/sci/invoice/btc/".$invoiceDetails->transactionid;

				$arr=array("success"=>"success","failure"=>"","price"=>$price_in_btc,"no_of_kits"=>$noOfKits,"payment_url"=>$payment_url);
				$this->view->result=$arr;

			} else {
				print("Error fetching invoice details");
				exit;
			}
		}
	}


}