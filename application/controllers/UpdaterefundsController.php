<?php
class UpdaterefundsController extends Zend_Controller_Action{
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin'))$this->_redirect("/Login");	
		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Update Refunds";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
	//	$data1=$misc_obj->GetAccessRightByUserId('6',$user_id);
	//	$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
	
		
		if($this->_request->isPost())
		{
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

			if(!empty($_POST['submit'])){
				
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
					//	fgetcsv($csvFile);
					//	$subarr=array();
						$i=0;
						
						while(($line = fgetcsv($csvFile)) !== FALSE){
							if($i>0){
								//$insertArray =  array("kit_number"=>$line[1],'txid'=>$line[2],'status'=>'1');
								$insertUpdatedRefunds = "insert into updated_refunds(kit_number,txid) values('$line[0]','$line[1]')";
							//	echo $insertUpdatedRefunds;
								$insertUpdatedRefund = $db->query($insertUpdatedRefunds);

								$RefundRequestStatusquery = "update refund_requests left join invoices on (invoices.use_kit_number = refund_requests.kit_number or invoices.invoice_id = refund_requests.invoice_id) left join kits on (kits.kit_number = refund_requests.kit_number or kits.kit_number = invoices.use_kit_number) set refund_requests.status = 1, kits.status = 'Refunded' where refund_requests.kit_number = '$line[0]' and refund_requests.status = 3 ";
								//echo $RefundRequestStatusquery;
								$RefundRequest= $db->query($RefundRequestStatusquery);

								//print_r($line);
								
							}
							$i++;
						}
						$db->commit();
						fclose($csvFile);	
					$i -=1;
						$qstring1 = 'File uploaded successfully and  '.$i.'   records updated';

					}else{
						$qstring2 = 'Error while uploading';
					}
				}else{
					$qstring2 = 'Unsupported file type';
					
				}
					$authUserNamespace->msg1=$qstring1;
					$authUserNamespace->msg2=$qstring2;
				

			}				
			
			
		}


	}

}
