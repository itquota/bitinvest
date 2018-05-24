<?php

class UploadmvpusersController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Upload CSV";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('6',$user_id);
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		$wallet_balance_obj=new Gbc_Model_DbTable_Walletbalance();
		$gbc_wallet_obj=new Gbc_Model_DbTable_Gbcwalletdata();
		
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
			
			
			if(!empty($_POST['submit_payout'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						while(($line = fgetcsv($csvFile)) !== FALSE){
							if($line[2] == ''){
								
								$line[2] = 'In process';
							
							}
								if($line[1] == ''){
								
								$line[1] = 'NULL';
							
							}
								$DB->query("INSERT INTO mvpusers (username,name,qualified_status) VALUES ('".$line[0]."','".$line[1]."','".$line[2]."')");							
						}

						fclose($csvFile);

						$qstring = 'Uploaded Successfully';
						
					}else{
						$qstring = 'Error while uploading';
					}
				}else{
					$qstring = 'Unsupported file type';
					
				}
					$authUserNamespace->msg=$qstring;

			}	
			
			
			
			if(!empty($_POST['submit'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$subarr=array();
						$i=0;
						while(($line = fgetcsv($csvFile)) !== FALSE){

					 		$update_arr=array('txid'=>$line[2],'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'));
							$where = array();
							$where[] = $db->quoteInto('wallet_address = ?', $line[0]);
							$where[] = $db->quoteInto('status = ?',1);
							$where[] = $db->quoteInto('amount = ?',$line[1]);
							$update_data=$wallet_balance_obj->update($update_arr,$where);
							
							if(!empty($update_data)){			
								$query="select balance_btc from gbc_wallet_data where wallet_addr='$line[0]'";
								$result=$db->query($query);
								$balanceAmount = $result->fetchAll();
								$bal_btc=$balanceAmount[0]['balance_btc']-$line[1];
						 		$update_arr=array('balance_btc'=>$bal_btc,'updated_on'=>new Zend_Db_Expr('NOW()'));
          						$update_data=$gbc_wallet_obj->update($update_arr,$db->quoteInto("wallet_addr=?",$line[0]));
								$i++;
							}
							
						}
						$db->commit();
						fclose($csvFile);	
					
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
			if(!empty($_POST['submit_mvp'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$subarr=array();
						$i=0;
						while(($line = fgetcsv($csvFile)) !== FALSE){
					 		/*$update_arr=array('txid'=>$line[5],'mcap_value'=>$line[4],'mcap_rate'=>$line[3],'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'));
							$where = array();
							$where[] = $db->quoteInto('wallet_address = ?', $line[0]);
							$where[] = $db->quoteInto('mcap_address = ?', $line[1]);
							$where[] = $db->quoteInto('status = ?',1);
							$where[] = $db->quoteInto('amount = ?',$line[2]);*/
					 		$update_arr=array('txid'=>$line[4],'mcap_value'=>$line[3],'mcap_rate'=>$line[2],'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'),'comment' => 'PAID');
							$where = array();
							$where[] = $db->quoteInto('wallet_address = ?', $line[1]);
							//$where[] = $db->quoteInto('mcap_address = ?', $line[2]);
							//$where[] = $db->quoteInto('status = ?',1);
							//$where[] = $db->quoteInto('amount = ?',$line[3]);							
							$where[] = $db->quoteInto('id = ?',$line[0]);							
							$update_data=$wallet_balance_obj->update($update_arr,$where);
						
							if(!empty($update_data)){	

								$query1="select amount from daily_payout_withdrawal_requests where id='$line[0]'";
								$result1=$db->query($query1);
								$details = $result1->fetchAll();
						//		print_r($details);
								$amount=$details[0]['amount'];
								
								/*$query="select balance_btc,total_btc_withdrawal from gbc_wallet_data where wallet_addr='$line[0]'";
								$result=$db->query($query);
								$balanceAmount = $result->fetchAll();
								$bal_btc=$balanceAmount[0]['balance_btc']-$line[2];
								$withdrawn_btc=$balanceAmount[0]['total_btc_withdrawal']+$line[2];
						 		$update_arr=array('balance_btc'=>$bal_btc,'total_btc_withdrawal'=>$withdrawn_btc,'updated_on'=>new Zend_Db_Expr('NOW()'));
          						$update_data=$gbc_wallet_obj->update($update_arr,$db->quoteInto("wallet_addr=?",$line[0])); */
								
								$query="select balance_btc,total_btc_withdrawal from gbc_wallet_data where wallet_addr='$line[1]'";
								$result=$db->query($query);
								$balanceAmount = $result->fetchAll();
							//	print_r($balanceAmount); exit;
								$bal_btc=$balanceAmount[0]['balance_btc']-$amount;
								$withdrawn_btc=$balanceAmount[0]['total_btc_withdrawal']+$amount;
						 		$update_arr=array('balance_btc'=>$bal_btc,'total_btc_withdrawal'=>$withdrawn_btc,'updated_on'=>new Zend_Db_Expr('NOW()'));
          						$update_data=$gbc_wallet_obj->update($update_arr,$db->quoteInto("wallet_addr=?",$line[1]));								
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
