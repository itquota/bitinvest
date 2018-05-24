<?php

class UploadwithdrawalsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Upload WithdrawalsCSV";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('6',$user_id);
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		
		
	 	$gb2_request_obj = new Gbc_Model_DbTable_Gb2withdrawalrequests();
	 	$requestincentive_obj = new Gbc_Model_DbTable_Gb2incentivesrequests();
	 	$gb2_final_balance_obj = new Gbc_Model_DbTable_Gb2FinalBalance();
		
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
			
			if(!empty($_POST['submit_eth'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$subarr=array();
						$i=0;
						while(($line = fgetcsv($csvFile)) !== FALSE){
							$update_arr=array('txid'=>$line[3],'tx_fees'=>$line[1],'received_amount'=>$line[2],'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'),'comment' => 'paid');
							$where = array();
						//	$where[] = $db->quoteInto('mcap_address = ?', $line[1]);
							$where[] = $db->quoteInto('status = ?',1);
							$where[] = $db->quoteInto('id = ?',$line[0]);		
							
							$update_data=$gb2_request_obj->update($update_arr,$where);
							
							if(!empty($update_data)){	
								$query1="select * from gb2_withdrawal_requests where id='$line[0]'";
								$result1=$db->query($query1);
								$details = $result1->fetchAll();
								$amount=$details[0]['amount'];
								$username=$details[0]['username'];
								
								$query="select bal_amt,total_withdrawal from gb2_final_balance where username='$username'";
								$result=$db->query($query);
								$balanceAmount = $result->fetchAll();
								$bal_eth=$balanceAmount[0]['bal_amt']-$amount;
								$withdrawn_eth=$balanceAmount[0]['total_withdrawal']+$amount;

						 		$update_arr=array('bal_amt'=>$bal_eth,'total_withdrawal'=>$withdrawn_eth,'updated_on'=>new Zend_Db_Expr('NOW()'));
          						$update_data=$gb2_final_balance_obj->update($update_arr,$db->quoteInto("username=?",$username));	
								$i++;
							}
							
						}
						$db->commit();
						fclose($csvFile);	
					
						$qstring3 = 'File uploaded successfully and  '.$i.'   records updated';

					}else{
						$qstring4 = 'Error while uploading';
					}
				}else{
					$qstring4 = 'Unsupported file type';
					
				}
					$authUserNamespace->msg1=$qstring3;
					$authUserNamespace->msg2=$qstring4;
				

			}	
			
			
			if(!empty($_POST['submit_mcap'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$subarr=array();
						$i=0;
						while(($line = fgetcsv($csvFile)) !== FALSE){
							$update_arr=array('txid'=>$line[3],'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'),'comment' => 'paid');
							$where = array();
							$where[] = $db->quoteInto('mcap_address = ?', $line[1]);
							$where[] = $db->quoteInto('status = ?', 1);
							$where[] = $db->quoteInto('id = ?',$line[0]);		
							
							$update_data=$requestincentive_obj->update($update_arr,$where);
							
							if(!empty($update_data)){	
								$i++;
							}
							
						}
						$db->commit();
						fclose($csvFile);	
					
						$qstring3 = 'File uploaded successfully and  '.$i.'   records updated';

					}else{
						$qstring4 = 'Error while uploading';
					}
				}else{
					$qstring4 = 'Unsupported file type';
					
				}
					$authUserNamespace->msg3=$qstring3;
					$authUserNamespace->msg4=$qstring4;
				

			}				
			
			
		}


	}

}
