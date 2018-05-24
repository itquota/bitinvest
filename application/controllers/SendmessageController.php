<?php
class SendmessageController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$checkAdminAuthentication=$misc_obj->checkAdminAuthentication();
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");



	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Send Message";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		/*$this->getResponse()->setHeader('Content-Type', 'application/json');
		 $this->_helper->layout->disableLayout();
		 $this->_helper->viewRenderer->setNoRender(true);*/

		$this->_helper->layout()->setLayout("admindashbord");

		$sendmessageObj = new Gbc_Model_DbTable_Smslog();
		
		$userinfoObj= new Gbc_Model_DbTable_Userinfo();
		

		$resultmessage=$sendmessageObj->fetchRow($sendmessageObj->select()
		->setIntegrityCheck(false)
		->from(array('sms_log'),array('id'))
		->order("id DESC"));

		$result1=sizeof($resultmessage);
		if(isset($result1) && $result1>0)
		{
			$id=$resultmessage->id+1;
		}
		else
		{
			$id=1;
		}
		
		try{


			

			if($this->_request->isPost()){
				
				
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				//if($key!="text"){
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//$data=array('success'=>'','failure'=>'Invalid Input.');
						//echo json_encode($data);exit;
						$errormessage="Invalid Input";
						$authUserNamespace->errormessage=$errormessage;
						$this->_redirect("/Sendmessage");

					}

				}
			//}
		}
				
				
				

				$token=$common_obj->cleanQueryParameter($_POST['token']);
				//echo $token;exit;
	//			if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){

				if(empty($_POST['text']))
				{

					$message2 = "<p style='color:red;'>Please enter Message Text</p>";
					


				}
				else
				{
				
					$userinfolist=$userinfoObj->fetchAll($userinfoObj->select()
					->setIntegrityCheck(false)
					->from(array('user_info'),array('username','phone')));
						
					
					for($i=0;$i<sizeof($userinfolist);$i++) {
						//while($row =  $userinfolist->fetch($userinfolist)){
						if($userinfolist[$i]['username']!=""){
							$username[] = $userinfolist[$i]['username'];
						}
						if($userinfolist[$i]['phone']!=""){
							$phoneno[] = $userinfolist[$i]['phone'];
						}
							
					}

					if(!empty($phoneno))
					{
						
						$message = rawurlencode($_POST['text']);
						
						$username = implode(',', $username);
						
						$phoneno = implode(',', $phoneno);
						

						$usernm = $authUserNamespace->user;
						$data = 'login='.MSGusername.'&pword='.MSGhash.'&mobnum='.$phoneno."&senderid=".MSGsender."&msg=".$message;
					
						//$data=array('login'=>MSGusername,'pword='=>MSGhash,'username'=>$username,'senderid'=>$MSGsender,'msg'=>$message);
						
						//$MsgResponse = sendMSG($data);
						$common_obj = new Gbc_Model_Custom_CommonFunc();
						$MsgResponse=$common_obj->sendMSG($data);
						
						if(!empty($MsgResponse)){
								
							//$saveMessage = mysql_query("insert into sms_log(username,mobile,msg,response_code) values('$users','$numbers','$message','$MsgResponse')");
							try{
								$savemessage=array('username'=>$username,'mobile'=>$phoneno,'msg'=>$message,'response_code'=>$MsgResponse,"id"=>$id);
								$send=$sendmessageObj->insert($savemessage);
							}
							catch(Exception $e){
								echo $e->getMessage();exit;
							}
								
								 		
							if(empty($send))
							 {
									
								//die('Zero rows affected');
								$message2="<p style='color:red;'>Zero rows affected</p>";
								//$this->view->msg=$message;
								}
								else{
									
								$message2="<p style='color:green;'>Sent Message successfully</p>";
								//$this->view->msg=$message;
								}
								
						}

					}

				}
			$this->view->msg=$message2;
/*			}
		else{
			//$data=array('success'=>'','failure'=>'Invalid Request Found.');
			// echo json_encode($data);exit;
			$errormessage="Invalid Request Found";
			$authUserNamespace->errormessage=$errormessage;
				} */
		}

		}
		catch(Exception $e)
		{
			$this->view->msg=$e->getMessage();exit;
		}

	}
}
