<?php
class TicketlistController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
	}
	public function indexAction()
	{	

		$this->view->title="Gainbitcoin - Service Request";
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		$antixss = new Gbc_Model_Custom_StringLimit();
		
		$help_obj = new Gbc_Model_DbTable_Helpquery();

		$username=$authUserNamespace->user;

		if($username=="admin"){
		
			$result=$help_obj->fetchRow($help_obj->select()
			->setIntegrityCheck(false)
			->from(array('help_query'),array('count(id) as total_count'))
			->where("created_on >= '2017-02-08'")
			->order("created_on desc"));
		}else{
				
			$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();

			$user_row = $sub_admin_obj->fetchRow($sub_admin_obj->select()
			->setIntegrityCheck(false)
			->from(array("u"=>"sub_admin_users"),array("session_id","first_name"))
			->where("u.email=?",$username)
			);
	
			if(!empty($user_row) && sizeof($user_row)>0)
			{
				$result=$help_obj->fetchRow($help_obj->select()
				->setIntegrityCheck(false)
				->from(array('help_query'),array('count(id) as total_count'))
				->where("assigned_to =?",$user_row->first_name)
				->where("created_on >= '2017-02-08'")
				//->where("status = ?",2)
				//->orwhere("status = ?",3)
				->order("created_on desc"));
				
			}
		}

	$UserCount = $result->total_count;
		if(empty($result) || sizeof($result)<=0)
		{
			$result=array();
		}
		$MaxRowsCount=100;
		if(!empty($PaginateLimit))
			{
				$PaginateLimit=$PaginateLimit;
			}
			else
			{
				$PaginateLimit=100;
			}
			$pages = ceil($UserCount/$PaginateLimit);

			$this->view->pages=$pages;
			
			if(!empty($_GET['page']))
			{
				$value = $_GET['page'];
				$antixss->setEncoding($_GET['page'], "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$this->_redirect("/Profileerror/errormsg");

				}
				$startLimit=$_GET['page']*$PaginateLimit;
			}
			else 
			{
				$startLimit=0;
			}
			
		if(!empty($PaginateLimit) && !empty($startLimit)){
			
				$Limit = $PaginateLimit.", ".$startLimit;
			}else if(!empty($PaginateLimit)){
				$Limit = $PaginateLimit;
			}else{
				$Limit ="";
			}
			
		if($username=="admin"){
		
			$result=$help_obj->fetchAll($help_obj->select()
			->setIntegrityCheck(false)
			->from(array('h'=>'help_query')	)										
			->joinLeft(array('s'=>'query_status'),"h.status = s.status_id",array("s.status_id"))

			->where("h.created_on >= '2017-02-08'")
			->order(array("h.created_on desc","s.order_id asc"))
			->limit($PaginateLimit,$startLimit)
			);


		}else{
				
			$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();

			$user_row = $sub_admin_obj->fetchRow($sub_admin_obj->select()
			->setIntegrityCheck(false)
			->from(array("u"=>"sub_admin_users"),array("session_id","first_name"))
			->where("u.email=?",$username)
			);
	
			if(!empty($user_row) && sizeof($user_row)>0)
			{
				
				
				$result=$help_obj->fetchAll($help_obj->select()
				->setIntegrityCheck(false)
				->from(array('h'=>'help_query'))											
				->joinLeft(array('s'=>'query_status'),"h.status = s.status_id",array("s.status_id"))
				->where("h.assigned_to =?", $user_row->first_name)
				->where("h.created_on >= '2017-02-08'")
				->order(array("h.created_on desc","s.order_id asc"))
				->limit($PaginateLimit,$startLimit)
				);
			}
		}
		
	//	echo "<pre>";
	//	var_dump($result);
	//	echo "</pre>";
	//	exit;
			//if(!empty($_POST['search'])){
			if($this->_request->isPost()){
				
			
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
				
			
			$startdate = (date('Y-m-d',strtotime($_POST["startdate"])));
			$enddate = (date('Y-m-d',strtotime($_POST["enddate"])))." 23:59:59";
			$tick_id = $_POST['tick_id'];
			$User = $_POST['username'];
			$tokn = $comm_obj->cleanQueryParameter($_POST['tokn']);
		//	if($authUserNamespace->token==$tokn)	{
				$data=$help_obj->select()
				->setIntegrityCheck(false)
				->from(array('help_query'));
				
				
				//$where = ' 1 = 1 ';
				if(!empty($tick_id))
				{
					//$where = $where.("AND ticket_id='".$tick_id." '");
					$data->where('ticket_id = ?',$tick_id);
				}
				
				if(!empty($User))
				{
					$data->where('username = ?',$User);
				}
				

				if(!empty($_POST["startdate"]) && empty($_POST["enddate"])) {
					//$where = $where.("AND created_on between '$startdate' AND '$enddate'");
					$data->where("created_on >= '$startdate'");
				}
				if(!empty($_POST["enddate"]) && empty($_POST["startdate"])) {
					$data->where("created_on <= '$enddate'");
				}
				if(!empty($_POST["startdate"]) && !empty($_POST["enddate"])  ) {
					$data->where("created_on between '$startdate' AND '$enddate'");
				}

				$data->where("created_on >= '2017-02-08'");
				//echo $where;exit;
				$result=$help_obj->fetchAll($data);

	/*		}
			else{
				$msg="Invalid Request found";
				$authUserNamespace->msg=$msg;
			}  */
		}
/*		
		$data1=$help_obj->select()
				->setIntegrityCheck(false)
				->from(array('help_query'));
		
		$data1->where('ticket_id = ?','ydNyFeIVUT');
		$result1=$help_obj->fetchAll($data1);
		$this->view->data1=$result1;
*/
		
		//print_r(sizeof($result));exit;
		$this->view->result=$result;


	}
	public function requestAction()
	{

			
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$help_obj = new Gbc_Model_DbTable_Helpquery();
			$lov_obj = new Gbc_Model_DbTable_Lov();
			$assigned_qry = new Gbc_Model_DbTable_Assignedquery();
			$sub_admin_obj = new Gbc_Model_DbTable_Subadminuser();
			$reply_obj = new Gbc_Model_DbTable_Reply();
			$username=$authUserNamespace->user;
			
			$ticket_id=$_POST['request'];
			$request_category=$_POST['request_cat'];
			$id=$_POST['id'];
			
			$user_type=$authUserNamespace->user_type;
			$helpquerycommentobj = new Gbc_Model_DbTable_Helpquerycomment();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			
	
			//$request_type=$_REQUEST['type'];




			/*$help_data=$help_obj->fetchAll($help_obj->select()
			->setIntegrityCheck(false)
			->from(array('help_query'))
			->where("ticket_id='$id'"));*/
			
			$help_data=$help_obj->fetchAll($help_obj->select()
			->setIntegrityCheck(false)
			->from(array('help_query'))
			->where("ticket_id=?",$ticket_id)
			->where("id=?",$id));
			
	//		echo $ticket_id;
		//	echo $id;
			
		//	echo "<pre>";
//print_r($help_data->toArray());
//			echo "</pre>";
			$arr=array();

			if(isset($help_data) && sizeof($help_data)>0)
			{

				for($j=0;$j<sizeof($help_data);$j++)
				{
					$newstatus=$help_data[$j]['status'];

					if($newstatus=='1')
					{
						$req='Open';
					}
					else if($newstatus=='2')
					{
						$req='Assigned';
					}
					else if($newstatus=='3')
					{
						$req='Resolved';
					}else if($newstatus=='4')
					{
						$req='Reopen';
					}
					else if($newstatus=='5')
					{
						$req='Pending';
					}
					else if($newstatus=='6')
					{
						$req='Response Sent';
					}
					else if($newstatus=='7')
					{
						$req='Sent to Technical Team';
					}
					else if($newstatus=='8')
					{
						$req='Input Required from User';
					}
					else if($newstatus=='9')
					{
						$req='Sent to Senior Support';
					}
					else if($newstatus=='10')
					{
						$req='Sent to Admin';
					}
					else if($newstatus=='11')
					{
						$req='Pending_Kit_Activation';
					}
					else if($newstatus=='12')
					{
						$req='Pending_Refund_Requests';
					}
					else if($newstatus=='13')
					{
						$req='Pending_Others';
					}					
					else
					{
						$req='Closed';
					}

                 $assignedUsersub=$help_data[$j]['assigned_to'];
					$subarr=array('ticket_id'=>$help_data[$j]['ticket_id'],
								  'username'=>$help_data[$j]['username'],
								  'created_on'=>$help_data[$j]['created_on'],
								  'query'=>utf8_encode($help_data[$j]['query']),
								  //'query'=>$help_data[$j]['query'],
								  'req_category'=>$help_data[$j]['req_category'],
								  'req_type'=>$help_data[$j]['req_type'],
								  'status'=>$req,
								  'reply_comment'=>$help_data[$j]['reply_comment'],
								  'assigned_to'=>$help_data[$j]['assigned_to']);
					array_push($arr,$subarr);
				//	$arr = $subarr;

				}


			}
		//	print_r($arr);
		//	exit;
			
			$add=array();

			/*$subadmin=$assigned_qry->fetchAll($assigned_qry->select()
			->setIntegrityCheck(false)
			->from(array('assigned_queries_users'))
			->where("status='1'"));*/
			$subadmin=$assigned_qry->fetchAll($assigned_qry->select()
			->setIntegrityCheck(false)
			->from(array('assigned_queries_users'))
			->where("status=?",'1'));

			if(!empty($subadmin) && sizeof($subadmin)>0)
			{
				if($authUserNamespace->user_type=="subadmin"){
								$subarr=array('name'=>$assignedUsersub);
								array_push($add,$subarr);
						}else{
				for($i=0;$i<sizeof($subadmin);$i++)
				{
					$email=$subadmin[$i]['username'];
					/*$subadminuser =$sub_admin_obj->fetchRow($sub_admin_obj->select()
								->from(array('a' =>'sub_admin_users'),array('first_name'))
								->where("email = '".$email."'")
								);*/
					$subadminuser =$sub_admin_obj->fetchRow($sub_admin_obj->select()
								->from(array('a' =>'sub_admin_users'),array('first_name'))
								->where("email = ?",$email)
								);
							
								
						if(!empty($subadminuser) && sizeof($subadminuser)>0)
						{
							$assignedUser1=$subadminuser->first_name;
						}
						
							$subarr=array('name'=>$assignedUser1);
						
					
						array_push($add,$subarr);
						}

						}
			}

			$address=array();
			
			$helpcmt=array();
			/*$helpdata = $helpquerycommentobj->fetchAll($helpquerycommentobj->select()
								->setIntegrityCheck(false)
								//->where("ID !='".$ins_data."'")
								->where("ticket_id='".$id."'"));*/
			
			$helpdata = $helpquerycommentobj->fetchAll($helpquerycommentobj->select()
								->setIntegrityCheck(false)
								//->where("ID !='".$ins_data."'")
								->where("ticket_id=?",$ticket_id));
								
				
			if(!empty($helpdata)&& sizeof($helpdata)>0)
					{
						for($j=0;$j<sizeof($helpdata);$j++)
						{
							$subarr=array('Date'=>$helpdata[$j]['comment_date'],'Comments'=>$helpdata[$j]['comments'],'CommentsBy'=>$helpdata[$j]['comments_by']);
							array_push($helpcmt,$subarr);
						}
					
					
						
					}
			
			if($authUserNamespace->user_type=='admin')
			{
					
				$resultlov=$lov_obj->fetchAll($lov_obj->select()
				->setIntegrityCheck(false)
				->from(array('lov'))
				->where("name='sr_category' OR name='sr_type' OR (name='ticket_status')and value IN('Open','Assigned','Resolved','Closed','Pending_Kit_Activation','Pending_Refund_Requests','Pending_Others')")
				->order("value ASC"));
			
			}
			elseif($authUserNamespace->user_type=="subadmin"){
			
				$resultlov=$lov_obj->fetchAll($lov_obj->select()
				->setIntegrityCheck(false)
				->from(array('lov'))
				->where("name='sr_category' OR name='sr_type' OR (name='ticket_status')and  value IN('Open','Resolved','Closed','Pending_Kit_Activation','Pending_Refund_Requests','Pending_Others')"));
			}
			

			if(!empty($resultlov) && sizeof($resultlov)>0)
			{
			 for($j=0;$j<sizeof($resultlov);$j++)
			 {
			 	$subarr=array('name'=>$resultlov[$j]['name'],'value'=>$resultlov[$j]['value'],'status'=>$resultlov[$j]['status']);
			 	array_push($address,$subarr);

			 }
			 $reply_data = $reply_obj->fetchAll($reply_obj->select()
				 );
				
			$auto_reply=array();	
			if(!empty($reply_data) && sizeof($reply_data)>0)
			{
				$auto_reply = $reply_data->toArray();
			}
			 
			 $data1=array('success'=>'success','failure'=>'','data'=>$arr,'data1'=>$address,'data2'=>$add,'data3'=>$helpcmt,'data4'=>$auto_reply);
				echo  json_encode($data1);exit;

			}
		
			
			$updaterequestdata=array("req_category"=>$_POST['category'],"updated_on"=>new Zend_Db_Expr('NOW()'));
			//$updateresult=$help_obj->update($updaterequestdata,"ticket_id='$id'");
			$updateresult=$help_obj->update($updaterequestdata,$DB->quoteInto("ticket_id=?",$ticket_id));
		
			
		/*	$data1=array('success'=>'success','failure'=>'','data3'=>$helpcmt);
				echo  json_encode($data1);*/
				//$this->$_redirect(/Ticketlist)

			if(!empty($updateresult))
			{
				$data=array('success'=>'success','failure'=>'');
				echo  json_encode($data);exit;
			}
	

			}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	
	}
	public function updateticketstatusAction()
	{
	
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username=$authUserNamespace->user;
			$help_obj = new Gbc_Model_DbTable_Helpquery();
			$lov_obj = new Gbc_Model_DbTable_Lov();
			$helpquerycommentobj = new Gbc_Model_DbTable_Helpquerycomment();
			$comm_obj = new Gbc_Model_Custom_CommonFunc();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			foreach($_POST as $key => $value)
			{
				
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//$data=array('success'=>'','failure'=>'Invalid Input.');
						//echo json_encode($data);exit;

					}

				}
			
		  }
			
			$arr=array();
			$tickedid = $comm_obj->cleanQueryParameter($_POST['tid']);
				
			
			$date = $comm_obj->cleanQueryParameter($_POST['dte']);
		
		
			$query = $comm_obj->cleanQueryParameter($_POST['qry']);
		
		
			$category = $comm_obj->cleanQueryParameter($_POST['cVal']);
		
		
			$type = $comm_obj->cleanQueryParameter($_POST['tVal']);
			
		
			$status = $comm_obj->cleanQueryParameter($_POST['sts']);
		
			
		if(!empty($_POST['comnt']) && $_POST['comnt']!=""){
			$comment = $comm_obj->cleanQueryParameter($_POST['comnt']);
		}
		else{
			$data=array('success'=>'','failure'=>'Please enter comment');
				echo json_encode($data);exit;
	
		}
					
			$assign = $comm_obj->cleanQueryParameter($_POST['tassign']);
			
			$tokn = $comm_obj->cleanQueryParameter($_POST['tkn']);


			if($status=='Open')
			{
				$status=1;
			}
			else if($status=='Assigned')
			{
				$status=2;
			}
			else if($status=='Resolved')
			{
				$status=3;
			}
			else if($status=='Reopen')
			{
				$status=4;
			}
			else if($status=='Pending')
			{
				$status=5;
			}
			else if($status=='Response Sent')
			{
				$status=6;
			}
			else if($status=='Sent to Technical Team')
			{
				$status=7;
			}
			else if($status=='Input Required from User')
			{
				$status=8;
			}
			else if($status=='Sent to Senior Support')
			{
				$status=9;
			}
			else if($status=='Sent to Admin')
			{
				$status=10;
			}
			else if($status=='Pending_Kit_Activation')
			{
				$status=11;
			}
			else if($status=='Pending_Refund_Requests')
			{
				$status=12;
			}
			else if($status=='Pending_Others')
			{
				$status=13;
			}		
			else if($status=='Closed')
			{
				$status=0;
			}
			
	//		if($authUserNamespace->token==$tokn){
				/*$ticket_row = $help_obj->fetchRow($help_obj->select()
				->setIntegrityCheck(false)
				->where("ticket_id='".$tickedid."'"));*/
				$ticket_row = $help_obj->fetchRow($help_obj->select()
				->setIntegrityCheck(false)
				->where("ticket_id=?",$tickedid));
				
				if(!empty($ticket_row) && sizeof($ticket_row)>0)
				{
					//	$query=$ticket_row->query."<br>".$query;
					//	$comment=$ticket_row->reply_comment."<br>".$comment;
					//$old_comment=$ticket_row->query;
					//$query=$old_comment."<b>".$username."</b>". ":"." ".$query."<br/>";
					
					if($status == 2){
						$upticklist=array("query"=>$query,"req_category"=>$category,"req_type"=>$type,"status"=>$status,"updated_on"=>new Zend_Db_Expr('NOW()'),"reply_date"=>new Zend_Db_Expr('NOW()'),'assigned_to'=>$assign);
					}else{
						$upticklist=array("query"=>$query,"req_category"=>$category,"req_type"=>$type,"status"=>$status,"updated_on"=>new Zend_Db_Expr('NOW()'),"reply_date"=>new Zend_Db_Expr('NOW()'));
					}
					//$updatetickdata=$help_obj->update($upticklist,"ticket_id='$tickedid'");
					$updatetickdata=$help_obj->update($upticklist,$DB->quoteInto("ticket_id=?",$tickedid));
					
					if($comment!="")
					{
					$helpqueryinsdata=array('comments'=>$comment,'ticket_id'=>$tickedid,'comments_by'=>$username,'comment_date'=>new Zend_Db_Expr('NOW()'),'created_date'=>new Zend_Db_Expr('NOW()'));
					$ins_data=$helpquerycommentobj->insert($helpqueryinsdata);	
					}
				/*$helpdata = $helpquerycommentobj->fetchAll($helpquerycommentobj->select()
				 ->setIntegrityCheck(false)
				 //->where("ID !='".$ins_data."'")
				 ->where("ticket_id='".$tickedid."'"));*/
				$name = $ticket_row->name;
				$replyFrom = $ticket_row->email;
				$mob = $ticket_row->mob;
				$subject = $ticket_row->subject;
				
				$user = "<p>You have request contact form on www.gainbitcoin.com:</p><p>Ticket_id: ".$tickedid."</p><p>Name: ".$name." (".$replyFrom.")</p><p>Query: ".$query."</p>";

				$email = "<p>A message has been sent from the contact form on www.gainbitcoin.com:</p><p>From: ".$name." (".$replyFrom.")</p><p>Mobile: ".$mob."</p><p>Subject: ".$subject."</p><p>Query: ".$query."</p><p>Ticket_id: ".$tickedid."</p>";



				// Mail it
				/* $to ="thegainbitcoin@gmail.com";
				 // $to ="thegainbitcoinhelp@gmail.com";
				 $from =$_POST['email'];

				 $headers  = 'MIME-Version: 1.0' . "\r\n";
				 $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

				 // Additional headers
				 //$headers .= 'To: ' .$to. "\r\n";


				 $headers .= "From: gainbitcoin <admin@gainbitco.in> \r\n";
				 $headers .= "CC: thegainbitcoinhelp@gmail.com \r\n";
				 // $headers .= "CC: virender321@gmail.com \r\n"; */


			/*	$to ="thegainbitcoin@gmail.com";
				$cc = "thegainbitcoinhelp@gmail.com";
				$from = 'support@gainbitcoin.com';
				$replyTo = 'thegainbitcoinhelp@gmail.com';
				$subject = "Contact From gainbitcoin.com";
				$message = $email;
				$htmlMessage = $email;
				$sendMail = $comm_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage,$cc);
			*/
				// You are saying if the mail to you succeeds, continue on.
		//		if(!empty($sendMail)) {
					// Your browser message to them
					if(filter_var($replyFrom, FILTER_VALIDATE_EMAIL)) {

						$headerRep  = "From: gainbitcoin <admin@gainbitco.in>";
						$subjectRep =   "No reply : Gainbitcoin Support";
							
						$messageRep =   $user;
						if($comment!="")
						{
							$messageRep .=   "<p>Comment: ".$comment."</p>";
						}						
						//$messageRep .=   "Thank you for contacting Gainbitcoin support. Your message has been sent to the support team and a representative will contact you shortly.";
						$messageRep .=   '<br/><br/><p>Best Regards,</p>';
						$messageRep .=   '<p>Team Gainbitcoin Support</p>';
							
						/* $messageRep   =  '<html><body>';
						 $messageRep .=   '<p>Thank you for contacting Gainbitcoin support.</p>';
						 $messageRep .=   '<p>Your message has been sent to the support team and a representative will contact you shortly.</p>';
						 $messageRep .=   '<br/><br/><p>Best Regards,</p>';
						 $messageRep .=   '<p>Team Gainbitcoin Support</p></body></html>';  */

						// mail($replyFrom, $subjectRep, $messageRep, $headerRep);
							
							
						$to =$replyFrom;
							
						$from = 'support@gainbitcoin.com';
						$replyTo = 'thegainbitcoinhelp@gmail.com';
						$subject = "No reply : Gainbitcoin Support";
						$message = $messageRep;
						$htmlMessage = $messageRep;
						$sendMail = $comm_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

					}

		/*		}else {

					$msg = ("Error sending email. Please contact us directly at thegainbitcoinhelp@gmail.com");

				}
		*/						
					
				
				}
		else {
				$data=array('success'=>'','failure'=>'failure');
				echo json_encode($data);exit;

			}
					

			
			
			if(!empty($updatetickdata))
			{
				$arr=array('success'=>'success','failure'=>'');
				echo  json_encode($arr);exit;
					
			}

/*		}
		else{
			$data=array('success'=>'','failure'=>'Invalid Request found');
				echo json_encode($data);exit;
		}  */

	}
	
	public function getanswerAction()
	{
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		$id = $comm_obj->cleanQueryParameter($_POST['id']);
		$reply_obj = new Gbc_Model_DbTable_Reply();
		/*$reply_data = $reply_obj->fetchRow($reply_obj->select()
				->where("id = '".$id."'"));*/
		
		$reply_data = $reply_obj->fetchRow($reply_obj->select()
				->where("id = ?",$id));
				
		$auto_reply=array();	
		if(!empty($reply_data) && sizeof($reply_data)>0)
		{
			$auto_reply = $reply_data->toArray();
			$auto_reply['answer'] = strip_tags($auto_reply['answer']);
		}
		echo json_encode($auto_reply);exit;
	}

}
