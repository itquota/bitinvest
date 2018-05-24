<?php
class WalletbalanceController extends Zend_Controller_Action{

	public function init(){
		header('Access-Control-Allow-Origin: *');
		header('Content-type: application/json');

		header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$requests_obj=new Gbc_Model_DbTable_Requests();
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $ip_address=$misc_obj->get_client_ip();
		
		$query = $_SERVER['REQUEST_URI'];
		//var_dump($_SERVER);
		$source = basename($_SERVER['PHP_SELF']);
		$params = !empty($_REQUEST)?json_encode($_REQUEST):"";
		$method = $_SERVER['REQUEST_METHOD'];
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if($authUserNamespace->user){
			$username=$authUserNamespace->user;
		}else{
			$username="";
		}
		
		$content = trim(file_get_contents("php://input"));
		$params .= $content;
		$insertRequest = array(
			"username" => $username,
			"method" => $method,
			"data" => $query,
			"params" => $params,
			"source" => $source,
			"ip_address" => $ip_address
		);
		$insertsdds1 = $requests_obj->insert($insertRequest);
		//$insertRequest = "insert into requests(username, method, data, params, source,error,ip_address) values('$LoggedinUser', '$method', '".$query."', '$params', '$source','$error','$ip_address')";
		// echo "insert into requests(username, data, error,ip_address) values('$LoggedinUser','".$query."','$error','$ip_address')";
		//echo $insertRequest;
		//echo $insert1;
		//exit;

		
	}
	public function indexAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);

		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}
	}
	
	public function completebalanceAction(){
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();			
			$key = $_REQUEST['key'];
						
			if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
				throw new Exception('Request method must be POST!');
			}

			//Make sure that the content type of the POST request has been set to application/json
			$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
			if(strcasecmp($contentType, 'application/json') != 0){
				throw new Exception('Content type must be: application/json');
			}

			//Receive the RAW post data.
			$content = trim(file_get_contents("php://input"));

			//Attempt to decode the incoming RAW post data from JSON.
			$decoded = json_decode($content, true);

			//If json_decode failed, the JSON is invalid.
			if(!is_array($decoded)){
				throw new Exception('Received content contained invalid JSON!');
			}

			$query="select coinbank_token from special_permissions";
			$result=$db->query($query);
			$token = $result->fetchAll();
			$token = $token[0]['coinbank_token'];
			
			$query="select daily_payout_tx_fees from global_variables order by id desc limit 1";
			$result=$db->query($query);
			$results = $result->fetchAll();
			$fees = $results[0]['daily_payout_tx_fees'];
			
			if($key == $token){

				//	echo $token; exit;
				//$walletarray['wallet_address']=array('0'=>'1FkCQHk5SxsYjrX96SBsfB8e2HCUvi4nAt','1'=>'1GTQ9L1r1iLPnNRnz1rTvp9fnFFwEReMnu ','2'=>'fghij','3'=>'klmno','4'=>'pqrst');
				//$walletarray['wallet_address']=array('1FkCQHk5SxsYjrX96SBsfB8e2HCUvi4nAt','1GTQ9L1r1iLPnNRnz1rTvp9fnFFwEReMnu','12ENn1ZTKztRaPxo5HtW4LiCREkMbkymVX','klmno','pqrst');
				//echo json_encode($walletarray,  JSON_FORCE_OBJECT);
			//	$walletStrings =  '{"wallet_address":{"0":"1FkCQHk5SxsYjrX96SBsfB8e2HCUvi4nAt","1":"1GTQ9L1r1iLPnNRnz1rTvp9fnFFwEReMnu","2":"12ENn1ZTKztRaPxo5HtW4LiCREkMbkymVX","3":"klmno","4":"pqrst"}}';
				//$walletStrings =  $_POST;
				//$walletStrings =  json_encode($walletarray);
				//echo $walletStrings;
			//	$walletarray = json_decode($walletStrings,true);
				$walletarray = $decoded;
				//print_r($walletarray);
				//exit;
				$subarr=array();
				//for($i=0;$i<sizeof($walletarray);$i++)
				foreach($walletarray['wallet_address'] as $wallet )
				{	
					$arr = array();
					$query="select wallet_address,amount,status from daily_payout_withdrawal_requests where wallet_address='".$wallet."' order by id desc limit 1";
					$result=$db->query($query);
					$walletDetails = $result->fetchAll();
					
					
				//	$query1="select balance_btc from gbc_wallet_data where wallet_addr='$wallet' and wallet_addr_type='btc'";
					$query1="select balance_btc from gbc_wallet_data where wallet_addr='$wallet' ";
					$result1=$db->query($query1);
					$balAmount = $result1->fetchAll();
					
					if(!empty($balAmount)){
						$arr['address']=$wallet;
						$arr['balance']=$balAmount[0]['balance_btc'];
						$arr['fees']=$fees;
						$arr['status']="";
						$arr['last_request']="";
						$arr['token']=$token;
					
						if(!empty($walletDetails)){
							//$arr=array('address'=>$walletDetails[0]['wallet_address'],'balance'=>$walletDetails[0]['amount'],'status'=>$walletDetails[0]['status'],'last_request'=>$walletDetails[0]['amount'],'token'=>$token);
							$arr['status']=$walletDetails[0]['status'];
							$arr['last_request']=$walletDetails[0]['amount'];
							$arr['token']=$token;
						}
						array_push($subarr,$arr);
					}
					
				}

				$db->commit();
				$data=array('success'=>'success','data'=>$subarr);
				//echo "<pre>";
				echo json_encode($data,JSON_FORCE_OBJECT);exit;	

			}
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}
	} 
	
	public function requestwithdrawalAction(){
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
		//	$db->beginTransaction();		
	        $wallet_balance_obj=new Gbc_Model_DbTable_Walletbalance();
	        $cb_wallet_obj=new Gbc_Model_DbTable_Cbwalletstatus();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
       		$antixss = new Gbc_Model_Custom_StringLimit();
			
			 foreach ($_REQUEST as $keys => $value) {
				if (isset($value) && $value != "") {
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilters($value, "black", "string") == "invalidInput") {
						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}
					//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
				}
			}
			
			
            $username=$common_obj ->cleanQueryParameter($_REQUEST['username']);
        
			
			$query="select coinbank_token from special_permissions";
			$result=$db->query($query);
			$token = $result->fetchAll();
			$token = $token[0]['coinbank_token'];
			
			
			$walletAddr = $common_obj ->cleanQueryParameter($_REQUEST['wallet']);
			$amount = $common_obj ->cleanQueryParameter($_REQUEST['amount']);
			$fees = $common_obj ->cleanQueryParameter($_REQUEST['fees']);
			$key = $common_obj ->cleanQueryParameter($_REQUEST['key']);
			$mcapAddr = $common_obj ->cleanQueryParameter($_REQUEST['mcap_addr']);
			$success = "";
			
			//$query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr' and wallet_addr_type='btc'";
			$query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr'";
			$result=$db->query($query);
			$balAmount = $result->fetchAll();
			if($balAmount[0]['balance_btc']){
				$balanceAmount = $balAmount[0]['balance_btc'];
			}else{
				$balanceAmount = 0;
			}
			
			if($key == $token){
				if(!empty($walletAddr) && !empty($amount) && !empty($mcapAddr)){
					$subarr=array();
					/*Check for blacklisted wallet starts*/
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
							$walletId=$response->walletId;
					
						//$query="select * from cb_wallet_status where wallet_address='$walletAddr' and status='2'";
							$query="select * from cb_wallet_status where wallet_id='$walletId' and status='2'";
							$result=$db->query($query);
							$walletBlkDetails = $result->fetchAll();					
					
						if(!empty($walletBlkDetails)){
							$subarr=array('failure'=>'Wallet address is blacklisted');
							$success = "failure";			
						}else{
							/*Check for valid coinbank wallet starts*/
						//	echo $response->walletId; exit;
							if(!empty($response->error)){
								$subarr=array('failure'=>'Not a valid coinbank wallet');
								$success = "failure";			
							}else{
								$walletId=$response->walletId;
								
								$query="select * from cb_wallet_status where wallet_address='$walletAddr' and wallet_id='$walletId'";
								$result=$db->query($query);
								$walletDetails = $result->fetchAll();								
								if(empty($walletDetails)){
									$insert_arr1=array('wallet_address'=>$walletAddr,'wallet_id'=>$walletId,'status'=>'1','created_on'=>new Zend_Db_Expr('NOW()'));
									$insert_data1=$cb_wallet_obj->insert($insert_arr1);
								}

									if($fees <= 0 || !($_REQUEST['fees'])){
											$arr=array('address'=>$walletAddr,'balance'=>$balanceAmount,'status'=>'5','message'=>'New Update will be live in few hours & withdrawal requests will be accepted after it.');
											array_push($subarr,$arr);
											$success = "failure";
										}else{

									$query="select * from daily_payout_withdrawal_requests where wallet_address='$walletAddr' and status='1'";
									$result=$db->query($query);
									$walletDetails = $result->fetchAll();
									if(!empty($walletDetails)){
											$arr=array('address'=>$walletAddr,'balance'=>$balanceAmount,'status'=>'4','message'=>'Request Already Exist');
											array_push($subarr,$arr);						
											$success = "failure";
									}else{
										 if($balanceAmount >= $amount  && $amount>0 && $fees>=0){
											$insert_arr=array('wallet_address'=>$walletAddr,'mcap_address'=>$mcapAddr,'withdrawal_type'=>'2','amount'=>round($amount,8),'tx_fees' => $fees,  'comment' => 'processing', 'status'=>'1','request_date'=>new Zend_Db_Expr('NOW()'));
											$insert_data=$wallet_balance_obj->insert($insert_arr);

											// $query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr' and wallet_addr_type='btc'";
											$query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr'";
											$result=$db->query($query);
											$balAmount = $result->fetchAll();
											if($balAmount[0]['balance_btc']){
												$balanceAmount = $balAmount[0]['balance_btc'];
											}else{
												$balanceAmount = 0;
											}

											if(!empty($insert_data)){



												$query="select comm_email from user_info where wallet_addr='$walletAddr' group by comm_email";
												$result=$db->query($query);
												$emails = $result->fetchAll();
											//	print_r($emails);
												$i=1;
												foreach($emails as $email){
													if($i==1){
														$to = $email['comm_email'];
													}else{
														$cc = array($email['comm_email']=>"");	
													}
													$i++;
												}
												//print_r($cc);
												$from = 'support@gainbitcoin.com';
												$replyTo = 'thegainbitcoinhelp@gmail.com';
												$subject = 'Daily Payout Withdrawal Request';
												 $htmlMessage = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".BASEPATH."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>Dear User,</p><p>A withdrawal request has been generated from wallet address '$walletAddr' for '$amount' BTC. If you haven't initiated this request, please login into your GainBitcoin account & visit \"Withdrawals -> Daily Withdrawals\" section to reject the request.</p><p>Thanks & Regards,<br>Team GainBitcoin</p></div>";

											//	$htmlMessage = "test mail";
												$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage,$cc);



												$arr=array('address'=>$walletAddr,'balance'=>$balanceAmount,'status'=>'1','message'=>'Success withdrawal entry');
												array_push($subarr,$arr);
												$success = "success";	
											}else{
												$arr=array('address'=>$walletAddr,'balance'=>$balanceAmount,'status'=>'3','message'=>'Some error occured');
												array_push($subarr,$arr);						
												$success = "failure";		
											}	
										}else{
												$arr=array('address'=>$walletAddr,'balance'=>$balanceAmount,'status'=>'2','message'=>'Insufficient balance');
												array_push($subarr,$arr);	
												$success = "failure";
											}

										}				 

									}
								//	$db->commit();
							}		/*Check for valid coinbank wallet Ends*/
						}/*Check for blacklisted wallet starts*/
				}else{
					//$db->commit();
					$subarr=array('message'=>'Field cannot be blank');
					$success  = "failure";
					//	echo json_encode($data);exit;			
				}			
				
				//echo "<pre>";
				//echo json_encode($data);exit;				
			}else
			{
				$subarr=array('failure'=>'Invalid token');
				$success = "failure";
				//echo "<pre>";
			//	echo json_encode($data);exit;	
			}
			$data=array('success'=>$success,'data'=>$subarr);
			echo json_encode($data,JSON_FORCE_OBJECT);exit;	

		}
		
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}
	}

	
	public function uploadcsvAction(){
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
	        $wallet_balance_obj=new Gbc_Model_DbTable_Walletbalance();
	        $gbc_wallet_obj=new Gbc_Model_DbTable_Gbcwalletdata();
			if(!empty($_POST['submit'])){
				$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
				if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
					if(is_uploaded_file($_FILES['file']['tmp_name'])){
						$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
						fgetcsv($csvFile);
						$subarr=array();
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
							}
						}
						$db->commit();
						$data=array('success'=>'File uploaded successfully');
						echo "<pre>";
						echo json_encode($data);exit;
						fclose($csvFile);						
					}else{
						$data=array('failure'=>'Error in uploading');
						echo "<pre>";
						echo json_encode($data);exit;					}
				}else{
					$data=array('failure'=>'Invalid file type');
					echo "<pre>";
					echo json_encode($data);exit;						
				}
					$authUserNamespace->msg=$qstring;

			}			
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}	
	}
	
	
	public function updatewithdrawalAction(){
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
	        $wallet_balance_obj=new Gbc_Model_DbTable_Walletbalance();
	        $gbc_wallet_obj=new Gbc_Model_DbTable_Gbcwalletdata();
			
			$walletAddr = $_REQUEST['wallet'];
			$amount = $_REQUEST['amount'];
			$txid = $_REQUEST['txid'];
			$key = $_REQUEST['key'];
			
			$query="select coinbank_token from special_permissions";
			$result=$db->query($query);
			$token = $result->fetchAll();
			$token = $token[0]['coinbank_token'];
			$success = "";
			
			if($key == $token){
				if(!empty($walletAddr) && !empty($amount) && !empty($txid)){
					$update_arr=array('txid'=>$txid,'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'));
					$where[] = $db->quoteInto('wallet_address = ?', $walletAddr);
					$where[] = $db->quoteInto('status = ?',1);
					$where[] = $db->quoteInto('amount = ?',$amount);

					$update_data=$wallet_balance_obj->update($update_arr,$where);
					
					if(!empty($update_data)){			
						$query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr'";
						$result=$db->query($query);
						$balanceAmount = $result->fetchAll();
						$bal_btc=$balanceAmount[0]['balance_btc']-$amount;
						$update_arr=array('balance_btc'=>$bal_btc,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$update_data=$gbc_wallet_obj->update($update_arr,$db->quoteInto("wallet_addr=?",$walletAddr));	
						//$db->commit();
						$data=array('message'=>'Record updated successfully');
						$success  = "success";
					}else{
						$data=array('message'=>'Some parameters does not match, so data is not updated in DB.');
						$success  = "failure";
					}
						
						//echo json_encode($data);exit;			
				}else{
						//$db->commit();
						$data=array('message'=>'Field cannot be blank');
					$success  = "failure";
					//	echo json_encode($data);exit;			

				}			
			}else
			{
				$data=array('message'=>'Invalid token');
				$success = "failure";
				//echo "<pre>";
			//	echo json_encode($data);exit;	
			}
				$db->commit();
				$data=array('success'=>$success,'data'=>$data);
				echo json_encode($data,JSON_FORCE_OBJECT);exit;
			
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}	
	}	
	
	public function approveentryAction(){
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
	        $wallet_balance_obj=new Gbc_Model_DbTable_Walletbalance();
	        $gbc_wallet_obj=new Gbc_Model_DbTable_Gbcwalletdata();
			
			$walletAddr = $_REQUEST['wallet'];
			$amount = $_REQUEST['amount'];
			$txid = $_REQUEST['txid'];
			$key = $_REQUEST['key'];
			
			$query="select coinbank_token from special_permissions";
			$result=$db->query($query);
			$token = $result->fetchAll();
			$token = $token[0]['coinbank_token'];
			$success = "";
			
			if($key == $token){
				if(!empty($walletAddr) && !empty($amount) && !empty($txid)){
					$update_arr=array('txid'=>$txid,'status'=>'2','updated_date'=>new Zend_Db_Expr('NOW()'));
					$where[] = $db->quoteInto('wallet_address = ?', $walletAddr);
					$where[] = $db->quoteInto('status = ?',1);
					$where[] = $db->quoteInto('amount = ?',$amount);

					$update_data=$wallet_balance_obj->update($update_arr,$where);
					
					if(!empty($update_data)){			
						$query="select balance_btc from gbc_wallet_data where wallet_addr='$walletAddr'";
						$result=$db->query($query);
						$balanceAmount = $result->fetchAll();
						$bal_btc=$balanceAmount[0]['balance_btc']-$amount;
						$update_arr=array('balance_btc'=>$bal_btc,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$update_data=$gbc_wallet_obj->update($update_arr,$db->quoteInto("wallet_addr=?",$walletAddr));								
						$data=array('message'=>'Record updated successfully');
						$success  = "success";
					}else{
						$data=array('message'=>'Some parameters does not match, so data is not updated in DB.');
						$success  = "failure";
					}
						//echo json_encode($data);exit;			
				}else{
					//	$db->commit();
						$data=array('failure'=>'Field cannot be blank');
						$success  = "failure";
						//echo "<pre>";
					//	echo json_encode($data);exit;			

				}			
			}else
			{
				$data=array('message'=>'Invalid token');
				$success = "failure";
				//echo "<pre>";
			//	echo json_encode($data);exit;	
			}
			$db->commit();
			$data=array('success'=>$success,'data'=>$data);
			echo json_encode($data,JSON_FORCE_OBJECT);exit;
			
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}	
	}	
	
	
public function getwalletdetailsAction()
	{
	 try
	 {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
		 
			if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
				throw new Exception('Request method must be POST!');
			}

			//Make sure that the content type of the POST request has been set to application/json
			$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
			if(strcasecmp($contentType, 'application/json') != 0){
				throw new Exception('Content type must be: application/json');
			}

			//Receive the RAW post data.
			$content = trim(file_get_contents("php://input"));

			//Attempt to decode the incoming RAW post data from JSON.
			$decoded = json_decode($content, true);

		    //If json_decode failed, the JSON is invalid.
			if(!is_array($decoded)){
				throw new Exception('Received content contained invalid JSON!');
			}		 
			
			$key = $_REQUEST['key'];
		 	$walletarray = $decoded;
			$walletAddr = $walletarray['wallet'];

		 	$query="select coinbank_token from special_permissions";
			$result=$db->query($query);
			$token = $result->fetchAll();
			$token = $token[0]['coinbank_token'];
			$success = "";
		 	if($key == $token){
				if(!empty($walletAddr)){
					$query="select * from daily_payout_withdrawal_requests where wallet_address='$walletAddr'";
					$result=$db->query($query);
					$walletDetails = $result->fetchAll();					
					$subarr = array();
					if(!empty($walletDetails)){	
						foreach($walletDetails as $walletDetail){
							$arr=array('address'=>$walletDetail['wallet_address'],'amount_transferred'=>$walletDetail['amount'],'withdrawal_fees'=>$walletDetail['tx_fees'],'status'=>$walletDetail['status'],'txn_id'=>$walletDetail['txid'],'requested_on'=>$walletDetail['request_date']);
							array_push($subarr,$arr);						
						}
						$success  = "success";
					}else{
						$subarr=array('failure'=>'No details found');
						$success  = "failure";					}

				}else {
				
					$subarr=array('failure'=>'Address cannot be blank');
					$success  = "failure";

				}					
								
			}else{
				$subarr=array('failure'=>'Invalid token');
				$success = "failure";			
			}
			$data=array('success'=>$success,'data'=>$subarr);
			echo json_encode($data,JSON_FORCE_OBJECT);exit;		 }
	catch(Exception $e)
	{
		$db->rollBack();
		$data=array('success'=>'','failure'=>$e->getMessage());
		echo json_encode($data);exit;
	}	
	
	}
}