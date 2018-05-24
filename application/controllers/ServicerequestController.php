<?php
class ServicerequestController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");

			$username=$authUserNamespace->user;
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$userinfo = $common_obj->getUserInfo($username);
			$this->view->userinfo=$userinfo;
			$this->view->title="Gainbitcoin - Ticket";

			$lov_obj=new Gbc_Model_DbTable_Lov();
			$lov_data=$lov_obj->fetchAll($lov_obj->select()
			->setIntegrityCheck(false)
			->from(array('lov'))
			//->where("name='sr_type'"));
			->where("name=?",'sr_type'));

			$lovcategory_data=$lov_obj->fetchAll($lov_obj->select()
			->setIntegrityCheck(false)
			->from(array('lov'))
			//->where("name='sr_category'"));
			->where("name=?",'sr_category')
			->where("status=?",'1'));

			//echo "<pre>";
			//print_r($lov_data);exit;
			$user_cat=array('lov_data'=>$lov_data,'lovcategory_data'=>$lovcategory_data);
			$this->view->user_cat=$user_cat;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function generateticketAction()
	{
		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;
			$antixss = new Gbc_Model_Custom_StringLimit();
			$assigned_qry = new Gbc_Model_DbTable_Assignedquery();
			$special_perm = new Gbc_Model_DbTable_SpecialPermission();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			
			foreach($_POST as $key => $value)
			{
				
			
				
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.','data'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

					//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
				}
				
			}
			if(empty($username) || $username=='')
			{
				
				$arr=array('success'=>'','failure'=>'failure','data'=>'Session timeout. Please login again and try.');
				echo json_encode($arr);exit;
			}
			$token=$common_obj->cleanQueryParameter($_POST['token']);
	//		if(!empty($authUserNamespace->user) && $authUserNamespace->user!='' && $authUserNamespace->token==$token)	{
				$comment=$common_obj->cleanQueryParameter($_POST['comment']);
				$srtype=$common_obj->cleanQueryParameter($_POST['srtype']);
				$category=$common_obj->cleanQueryParameter($_POST['category']);
				
			
				$txnid=$common_obj->cleanQueryParameter($_POST['txn']);							

			/*	if($category == 'Kit activation'){
					if(empty($txnid))
					{
						$msg="Please add transaction ID";
					}
					$comment = $comment .' For reference Transaction ID is: '.$txnid;

				}			
			*/
			
				if(empty($comment))
				{
					$msg="Please add Description";
				}
			/*	else if(empty($srtype))
				{
					$msg="Please select service request type";
				} */
				else if(empty($category))
				{
					$msg="Please select service request category";
				}
				if(!empty($msg) && $msg!='')
				{
					//$this->view->msg=$msg;
					$arr=array('success'=>'','failure'=>'failure','data'=>$msg);
					echo json_encode($arr);exit;
				}
				

				$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
				$help_obj = new Gbc_Model_DbTable_Helpquery();
				$userinfo = $common_obj->getUserInfo($username);

			//	$ticket_id=$common_obj->generateRandomString(10);
				
			
			$unique_id =$help_obj->fetchRow($help_obj->select()
								->from(array('a' =>'help_query'),array('id'))
								->order("id desc")
								->limit(1)
								);
			
				//$ticket_id = $common_obj->get_rand_alphanumeric(5).$common_obj->get_rand_numbers(4);
				$ticket_id = $common_obj->get_rand_alphanumeric(5).$unique_id['id'];
				$mob = $common_obj->cleanQueryParameter($_POST['mob']);
				$email = $common_obj->cleanQueryParameter($_POST['email']);
			
			
				
			
				if($category == 'Kit Activation')
				{
					$category_code = '1';
				}else if($category == 'Payout Calculations')
				{
					$category_code = '2';
				}else if($category == 'Profile Changes')
				{
					$category_code = '3';
				}else if($category == 'Others')
				{
					$category_code = '4';
				}				
			
				if(!empty($userinfo) && sizeof($userinfo)>0)
				{
					if(isset($userinfo->comm_email) && $userinfo->comm_email!='')
					{
						$ext=$userinfo->comm_email;
					}
					else
					{
						$ext=$userinfo->email_address;
					}
					$ip_address=$misc_obj->get_client_ip();
		
					$assignedUsersData =$assigned_qry->fetchAll($assigned_qry->select()
								->from(array('a' =>'assigned_queries_users'),array('username','total_queries'))
								->where("status = ?",'1')
								->where("query_type = ?",$category_code)								
								->order("total_queries desc"));
								
					
					for($i=0;$i<sizeof($assignedUsersData);$i++)
					{
							$assignedUser = $assignedUsersData[$i]['username'];
							$total_queries = $assignedUsersData[$i]['total_queries'];
						   
						
							$subadminuser =$sub_admin_obj->fetchRow($sub_admin_obj->select()
								->from(array('a' =>'sub_admin_users'),array('first_name'))
								->where("email =?",$assignedUser)
								);
								
								
						if(!empty($subadminuser) && sizeof($subadminuser)>0)
						{
							$assignedUser1=$subadminuser->first_name;
						}		
					}
					
					$assignedUsersData =$special_perm->fetchRow($special_perm->select()
								->from(array('s' =>'special_permissions'),array('auto_assign'))
									);
					$auto_assign = $assignedUsersData->auto_assign;
					
						if(!empty($auto_assign) && $auto_assign!='' && $auto_assign!='0'){
								$insert_data=array('name'=>$userinfo->name,'email'=>$email,'mob'=>$mob,'req_type'=>$srtype,'req_category'=>$category,'query'=>$comment,'created_on'=>new Zend_Db_Expr('NOW()'),'ticket_id'=>$ticket_id,'username'=>$username,'status'=>'1','subject'=>$category,'assigned_to'=>$assignedUser1,'loggedin_user'=>$username,'extension'=>$ext,'ip_address'=>$ip_address);
							
						}else{
							 	$insert_data=array('name'=>$userinfo->name,'email'=>$email,'mob'=>$mob,'req_type'=>$srtype,'req_category'=>$category,'query'=>$comment,'created_on'=>new Zend_Db_Expr('NOW()'),'ticket_id'=>$ticket_id,'username'=>$username,'status'=>'1','subject'=>$category,'assigned_to'=>$assignedUser1,'loggedin_user'=>$username,'extension'=>$ext,'ip_address'=>$ip_address);
						}
					
					//$insert_data=array('name'=>$userinfo->username,'email'=>$_POST['email'],'mob'=>$_POST['mob'],'req_type'=>$srtype,'req_category'=>$category,'query'=>$comment,'created_on'=>new Zend_Db_Expr('NOW()'),'ticket_id'=>$ticket_id,'username'=>$username,'status'=>'1');
					$insert_qry=$help_obj->insert($insert_data);
					
					   if(!empty($auto_assign) && $auto_assign!='' && $auto_assign!='0'){
						   $total_assigned_queries = $total_queries + 1;
						   $upd_arr = array('total_queries'=>$total_assigned_queries);
						   //$upd_qry = $assigned_qry->update($upd_arr,"username = '$assignedUser'");
						  
						  // $upd_qry = $assigned_qry->update($upd_arr, $DB->quoteInto("username = ?",$assignedUser));
						   $upd_qry = $assigned_qry->update($upd_arr, $DB->quoteInto("username = ? AND (query_type ='$category_code')",$assignedUser));
							
					   }
					    $user = "<p>You have request contact form on www.gainbitcoin.com:</p><p>Ticket_id: ".$ticket_id."</p><p>Name: ".$username." (".$ext.")</p><p>Query: ".$comment."</p>";

						$email = "<p>A message has been sent from the contact form on www.gainbitcoin.com:</p><p>From: ".$username." (".$ext.")</p><p>Mobile: ".$mob."</p><p>Subject: ".$category."</p><p>Query: ".$comment."</p><p>Ticket_id: ".$ticket_id."</p>";
					
				/*		$to ="thegainbitcoin@gmail.com";
						$cc = "thegainbitcoinhelp@gmail.com";
						$from = 'support@gainbitcoin.com';
						$replyTo = 'thegainbitcoinhelp@gmail.com';
						$subject = "Contact From gainbitcoin.com";
						$message = $email;
						$htmlMessage = $email;
						$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage,$cc);
				*/		
					//	if(!empty($sendMail)) {
							// Your browser message to them
						  if(filter_var($ext, FILTER_VALIDATE_EMAIL)) {

							$headerRep  = "From: gainbitcoin <admin@gainbitco.in>";
							$subjectRep =   "No reply : Gainbitcoin Support";
							
							$messageRep =   $user;
							$messageRep .=   "Thank you for contacting Gainbitcoin support. Your message has been sent to the support team and a representative will contact you shortly.";
							$messageRep .=   '<br/><br/><p>Best Regards,</p>';
							$messageRep .=   '<p>Team Gainbitcoin Support</p>';
							
				/*             $messageRep   =  '<html><body>';
							$messageRep .=   '<p>Thank you for contacting Gainbitcoin support.</p>';
							$messageRep .=   '<p>Your message has been sent to the support team and a representative will contact you shortly.</p>';
							$messageRep .=   '<br/><br/><p>Best Regards,</p>';
							$messageRep .=   '<p>Team Gainbitcoin Support</p></body></html>';  */

							// mail($replyFrom, $subjectRep, $messageRep, $headerRep);
							
							
							$to =$ext;
							
							$from = 'support@gainbitcoin.com';
							$replyTo = 'thegainbitcoinhelp@gmail.com';
							$subject = "No reply : Gainbitcoin Support";
							$message = $messageRep;
							$htmlMessage = $messageRep;
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
							
							}
							$arr=array('success'=>'success','failure'=>'','data'=>'Service request genereated successfully.');
							echo json_encode($arr);exit;
							
				/*		}else {

						//	$msg = ("Error sending email. Please contact us directly at amit@nexgenfmpl.com");
							$arr=array('success'=>'','failure'=>'failure','data'=>'Error sending email. Please contact us directly at thegainbitcoinhelp@gmail.com');
							echo json_encode($arr);exit;

						}
				*/
					
				}
				else
				{
					$arr=array('success'=>'','failure'=>'failure','data'=>'Session timeout. Please login again and try.');
					echo json_encode($arr);exit;
				}
					
	/*			}
			else 
			{
	
				$arr=array('success'=>'','failure'=>'failure','data'=>'Invalide Token.');
				echo json_encode($arr);exit;
			}
	*/
		}catch(Exception $e)
		{
			echo $e->getMessage();exit;
			$arr=array('success'=>'','failure'=>'failure','data'=>'Error while creating service request. Please try again.');
			echo json_encode($arr);exit;
		}
	}

	public function generateticketviaapiAction()
	{
		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;
			$antixss = new Gbc_Model_Custom_StringLimit();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$help_obj = new Gbc_Model_DbTable_Helpquery();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$payment_obj = new Gbc_Model_DbTable_Querypayments();
            $db = Zend_Db_Table::getDefaultAdapter();

			if(empty($username) || $username=='')
			{
				
				$arr=array('success'=>'','failure'=>'failure','data'=>'Session timeout. Please login again and try.');
				echo json_encode($arr);exit;
			}


				$comment=$common_obj->cleanQueryParameter($_POST['comment']);
				$category=$common_obj->cleanQueryParameter($_POST['category']);
				$txnid=$common_obj->cleanQueryParameter($_POST['txn']);							
				$invoiceid=$common_obj->cleanQueryParameter($_POST['invoice']);							
				$mob = $common_obj->cleanQueryParameter($_POST['mob']);
				$email = $common_obj->cleanQueryParameter($_POST['email']);
				$userinfo = $common_obj->getUserInfo($username);
				$ticket_id = $common_obj->get_rand_alphanumeric(5).$unique_id['id'];
				$language = $common_obj->cleanQueryParameter($_POST['language']);
				$type = $common_obj->cleanQueryParameter($_POST['type']);
				$payment_id = $common_obj->cleanQueryParameter($_POST['payment_id']);
			
				if($category == 'Kit Activation'){
					
					$commentText = $comment.'<br/><div style="border:1px solid #ccc;border-radius:5px;margin-top:10px;padding:10px;"><p>For Reference:</p><b>Invoice ID:</b>'.$invoiceid.'<br/><b>Transaction ID:</b>'.$txnid.'</div>';
				}else{
				
					$commentText = $comment;
				}
				if(!empty($userinfo) && sizeof($userinfo)>0)
				{
					
					if(isset($userinfo->comm_email) && $userinfo->comm_email!='')
					{
						$ext=$userinfo->comm_email;
					}
					else
					{
						$ext=$userinfo->email_address;
					}
					$ip_address=$misc_obj->get_client_ip();	
					
					if($type == 1){
						$support_type = 1; 
					}else{
					
						$support_type = 2;
					}
			
					$insert_data=array('name'=>$userinfo->name,'email'=>$email,'mob'=>$mob,'req_type'=>$srtype,'req_category'=>$category,'query'=>$comment,'created_on'=>new Zend_Db_Expr('NOW()'),'ticket_id'=>$ticket_id,'username'=>$username,'status'=>'1','subject'=>$category,'loggedin_user'=>$username,'extension'=>$ext,'ip_address'=>$ip_address,'support_type'=>$support_type);
					$insert_qry=$help_obj->insert($insert_data);					
					if(!empty($insert_qry)){
						if($type == 2){
					  	  $description = "<p>Hi Support,</p><p>Order_id: ".$payment_id."</p><p>Username: ".$username." </p><p>Email: ".$ext."</p><p>Subject: ".$category."</p><p>Description: ".$commentText."</p>";
							if(filter_var($ext, FILTER_VALIDATE_EMAIL)) {

							$messageRep =   $description;
							$messageRep .=   '<br/><br/><p>Best Regards,</p>';
							$messageRep .=   $username;
											
							
							$to ='paidsupport@gainbitcoin.com';						
						//	$to ='vikas@nexgenfmpl.com';
							$cc = array($ext=>"");
							$from = 'support@gainbitcoin.com';
							$replyTo = $ext;
							$subject = "A ticket raised on paid support : $category(OrderId#$payment_id)";
							$message = $messageRep;
							$htmlMessage = $messageRep;
							$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage,$cc);
							  
							if(!empty($sendMail)){
							
								$user = "<p>You have request contact form on www.gainbitcoin.com:</p><p>Order_id: ".$payment_id."</p><p>Name: ".$username." </p><p>Email: ".$ext."</p><p>Subject: ".$category."</p><p>Query: ".$commentText."</p>";

								//$email = "<p>A message has been sent from the contact form on www.gainbitcoin.com:</p><p>From: ".$username." (".$ext.")</p><p>Mobile: ".$mob."</p><p>Subject: ".$category."</p><p>Query: ".$comment."</p><p>Ticket_id: ".$ticket_id."</p>";

								  if(filter_var($ext, FILTER_VALIDATE_EMAIL)) {

									$messageRep =   $user;
									$messageRep .=   "Thank you for contacting Gainbitcoin support. Your message has been sent to the support team and a representative will contact you shortly.";
									$messageRep .=   '<br/><br/><p>Best Regards,</p>';
									$messageRep .=   '<p>Team Gainbitcoin Support</p>';

									$to =$ext;
									$from = 'support@gainbitcoin.com';
									$replyTo = 'thegainbitcoinhelp@gmail.com';
									$subject = "No reply : Gainbitcoin Support";
									$message = $messageRep;
									$htmlMessage = $messageRep;
									$sendMail1 = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
									if(!empty($sendMail1)){
									    $update_arr=array('status'=>'Used','updated_on'=>new Zend_Db_Expr('NOW()'));
										$where = array();
										$where[] = $db->quoteInto('username = ?', $username);
										$where[] = $db->quoteInto('status = ?', 'Active');
										$update_data=$payment_obj->update($update_arr,$where);
										if(!empty($update_data)){
										}
									}
										$arr=array('success'=>'success','failure'=>'','data'=>'Service request generated successfully.');
										echo json_encode($arr);exit;
										  
								}
							
								}  
							
							}

							
						
						
						}else{

							$query="select fdesk_api_key,fdesk_pwd,fdesk_domain from special_permissions";
							$result=$db->query($query);
							$fdeskDetails = $result->fetchAll();
							$api_key = $fdeskDetails[0]['fdesk_api_key'];							
							$password = $fdeskDetails[0]['fdesk_pwd'];							
							$yourdomain = $fdeskDetails[0]['fdesk_domain'];							
							
						/*	$api_key = "FCDVy4pvl1k5ve4Ut9qD";
							$password = "Incorrect2387";
							$yourdomain = "devphp"; 
						*/
						/*	$api_key = "TaNWZlZIRtiKwKpGFxE";
							$password = "Ajay@123#@!";
							$yourdomain = "gbhelp";
						*/
							$custom_fields = array("username" => $username,"subject" => $category ,"language" => $language);
						//	$custom_fields = array("username" => $username,"subject" => $category);
							$ticket_data = json_encode(array(
							  "description" => $commentText,
							  "subject" => $category,
							  "email" => $email,
							  "priority" => 1,
							  "status" => 2,
							  "custom_fields" => $custom_fields, 		

							//  "cc_emails" => array("vikas@nexgenfmpl.com")
							));

							$url = "https://$yourdomain.freshdesk.com/api/v2/tickets";
							$ch = curl_init($url);
							$header[] = "Content-type: application/json";
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
							curl_setopt($ch, CURLOPT_HEADER, true);
							curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$password");
							curl_setopt($ch, CURLOPT_POSTFIELDS, $ticket_data);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$server_output = curl_exec($ch);
							$info = curl_getinfo($ch);
							$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
							$headers = substr($server_output, 0, $header_size);
							$response = substr($server_output, $header_size);

							if($info['http_code'] == 201) {
								$arr=array('success'=>'success','failure'=>'','data'=>$response);
								curl_close($ch);
								echo json_encode($arr);exit;							

							} else {
							  if($info['http_code'] == 404) {
								$arr=array('success'=>'','failure'=>'','data'=>'Error, Please check the end point');
								curl_close($ch);
								echo json_encode($arr);exit;							  
								 } else {
								$arr=array('success'=>'','failure'=>'','data'=>'Something went wrong. Try Again.');
								curl_close($ch);
								echo json_encode($arr);exit;							  
							  }
							}

						}
					}
				}
				else
				{
					$arr=array('success'=>'','failure'=>'failure','data'=>'Session timeout. Please login again and try.');
					echo json_encode($arr);exit;
				}
					
		}catch(Exception $e)
		{
			echo $e->getMessage();exit;
			$arr=array('success'=>'','failure'=>'failure','data'=>'Error while creating service request. Please try again.');
			echo json_encode($arr);exit;
		}
	}

	public function showqrAction(){
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;
			$antixss = new Gbc_Model_Custom_StringLimit();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$help_obj = new Gbc_Model_DbTable_Helpquery();
			$payment_obj = new Gbc_Model_DbTable_Querypayments();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();		
        	$base=BASE;
            $db = Zend_Db_Table::getDefaultAdapter();
			
			$invoiceId = $common_obj->get_rand_alphanumeric(10).$unique_id['id'];
	
			$query="select ticket_cost from special_permissions"; 
			$result=$db->query($query);
			$ticketCost = $result->fetchAll();
	
			$price_in_btc = $ticketCost[0]['ticket_cost'];			
					
			$BasePaymentUrl = "https://api.paybitz.com";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$BasePaymentUrl/merchant/invoice/");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			$data = array("merchant_id" => "58c11d4ad6b43120b5997c00",
				"amount" => $price_in_btc,// in BTC
				"redirect_url" => BASE."/thankyou/index.php",
				"notify_url" => "$base/Invoicestatusapi/paymentupdate",
				"required_confirmations" => "1",
				"order_id"=> $invoiceId,
				"username"=> $username);
			$data_string = json_encode($data);

			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json",
				"Authorization: Token IdJtt10Q7efe3Q4O1vcFhZeR"
			));

			$response = json_decode(curl_exec($ch));
			curl_close($ch);
			$url = $response->data->payment_url;
			$inputAddress = $response->data->address;			
			$payment_gateway = '2';		

			$insert_arr=array('username'=>$username ,'invoice_id'=>$invoiceId,'wallet_addr'=>$inputAddress,'payment_url'=>$url,'amount'=>$price_in_btc,'created_on'=>new Zend_Db_Expr('NOW()'));
			$insert_data=$payment_obj->insert($insert_arr);	
			if(!empty($insert_data)){
				$data=array('input_address'=>$inputAddress,'payment_gateway'=>$payment_gateway,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'payment_url'=>$url);
				$arr=array('success'=>'success','failure'=>'','data'=>$data);
				echo json_encode($data);exit;			
			}
	
		}catch(Exception $e){
			echo $e->getMessage();exit;
			$arr=array('success'=>'','failure'=>'failure','data'=>'Error while creating invoice. Please try again.');
			echo json_encode($arr);exit;
		}
	}
	
	public function checkpaymentstatusAction(){
	
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();

			$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();		
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$payment_obj = new Gbc_Model_DbTable_Querypayments();	
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;	
			$common_obj = new Gbc_Model_Custom_CommonFunc();

			$mode=$common_obj->cleanQueryParameter($_POST['mode']);

			$date=date("Y-m-d h:i:s");
			$currentDay = date('w');
			$currentTime = date('H');		

			$query="select * from ticket_settings where type='$mode' and status='1'";
			$result=$db->query($query);
			$ticketDetails = $result->fetchAll();


			$days = $ticketDetails[0]['days'];
			$start_time = $ticketDetails[0]['start_time'];
			$end_time = $ticketDetails[0]['end_time'];

			if ($mode == 1){

				if ($days == 5){
					if($currentDay != 6 && $currentDay != 0){
						if($currentTime >= $start_time && $currentTime < $end_time){
							$data = '3';
						}					
					}
				}else if ($days == 6){
					if($currentDay != 0){
						if($currentTime >= $start_time && $currentTime < $end_time){
							$data = '3';
						}					
					}				
				}else {
					if($currentTime >= $start_time && $currentTime < $end_time){
						$data = '3';
					}									
				}

				$arr=array('success'=>'success','failure'=>'','data' =>$data);
				echo json_encode($arr);exit;

			}else{

			$paymentDetails =$payment_obj->fetchRow($payment_obj->select()
				->from(array('a' =>'query_payments'))
				->where("status =?",'Active')
				->where("username =?",$username)
				);


			if(!empty($paymentDetails) && sizeof($paymentDetails)>0)
			{
				if ($days == 5){
					if($currentDay != 6 && $currentDay != 0){
						if($currentTime >= $start_time && $currentTime < $end_time){
							$data = '1';
						}else{
							$data = '2';
						}
					}
				}else if ($days == 6){
					if($currentDay != 0){
						if($currentTime >= $start_time && $currentTime < $end_time){
							$data = '1';
						}else{
							$data = '2';
						}
					}
				}else{
					if($currentTime >= $start_time && $currentTime < $end_time){
						$data = '1';
					}else{
						$data = '2';
					}			
				}

				$arr=array('success'=>'success','failure'=>'','data' =>$data);
				echo json_encode($arr);exit;			
			}else{
				$arr=array('success'=>'','failure'=>'No active payment found');
				echo json_encode($arr);exit;	

			}		
		}

		}catch(Exception $e){
			echo $e->getMessage();exit;
			$arr=array('success'=>'','failure'=>'failure','data'=>'Something went wrong. Please try again.');
			echo json_encode($arr);exit;
		}

	
	}

	
	
}
