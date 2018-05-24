<?php

class LoginController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!empty($authUserNamespace->user) && isset($authUserNamespace->user) && $authUserNamespace->user!="" && $authUserNamespace->user_type=="binary")$this->_redirect("/Dashboard");

		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){

        $this->_helper->layout()->setLayout("login");


		//$authUserNamespace->user_id=
	}
	public function registerAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("login");


	}
	public function forgotAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("login");


	}

	public function checkloginAction()
	{

	try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$authUserNamespace->setExpirationHops(10000);
			$authUserNamespace->setExpirationSeconds(10000);
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$admin_obj=new Gbc_Model_DbTable_Adminsetting();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$user_obj = new Gbc_Model_DbTable_Userinfo();
			$subadmin_obj = new Gbc_Model_DbTable_Subadminusers();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$user_session = new Gbc_Model_DbTable_Usersessions();
			$admin_session = new Gbc_Model_DbTable_Adminsession();
			$aesctr=new Gbc_Model_AesCtr();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			if($this->_request->isXmlHttpRequest())
			{

				$username=$common_obj->cleanQueryParameter($_POST['username']);
				$password=($_POST['password']);
				$password2=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
				$token=$common_obj->cleanQueryParameter($_POST['token']);
				$remember=trim($_POST['remember']);
              //  $username = str_replace ( ' ', '%20', $username);
				//if($authUserNamespace->token == $token){
					$url= BASE."/Loginapi?username=".$username."&password=".$password2;


					$result=$common_obj->call_curl($url);
				//	print_r($result);
					if(isset($result) && sizeof($result)>0)
					{

						$data=(array)json_decode($result,true);

						$sucess= $data['success'];
						if($username=="admin"){
							$userInfo_chk = $admin_obj->fetchRow($admin_obj->select()
							->where("status=1"));
						}else if(!empty($data['user_type']) && $data['user_type']=='subadmin'){
							$userInfo_chk = $subadmin_obj->fetchRow($subadmin_obj->select()
							->where("email=?",trim($username)));
						}else{
							/*$userInfo_chk = $user_obj->fetchRow($user_obj->select()
							->where("username='".trim($username)."'"));*/
							
							$userInfo_chk = $user_obj->fetchRow($user_obj->select()
							->where("username=?",trim($username)));
						}
						$successful=$userInfo_chk->last_successful_login;
						$failed=$userInfo_chk->last_failed_login;
						//echo $failed;exit;
						if(!empty($successful) && $successful!='' && $successful!='0000-00-00 00:00:00')
						{
							//echo "in";exit;
							$successful = date("jS M, Y  H:i:s", strtotime($successful));
							//$failed = date("jS M, Y H:i:s", strtotime($failed));
						}
						else
						{
							//echo "else";exit;
							$successful="---";
						}
						if(!empty($failed) && $failed!='' && $failed!='0000-00-00 00:00:00')
						{
							//echo "in if";exit;
							//date conversion
							//$successful = date("jS M, Y  H:i:s", strtotime($successful));
							$failed = date("jS M, Y H:i:s", strtotime($failed));
						}
						else
						{
							//echo "else";exit;
							$failed="---";
						}

						$authUserNamespace->last_successful=$successful;
						$authUserNamespace->last_failed=$failed;

						if(isset($sucess) && !empty($sucess) && $sucess!='' && $sucess!=null)
						{

							if(!empty($data['user_type']) && isset($data['user_type']) && $data['user_type']!=''){
								$user_type=$data['user_type'];
							}else{
								$user_type='';
							}

							//$result=array();
							//$url= BASE."/UserinfoApi?username=".$username;
							//$result=$common_obj->call_curl($url);
							//$userInfo=(array)json_decode($result,true);

							//$session_id='6enibpitioh9gf576j2pvca795';
							if(!empty($data['user_type']) && $data['user_type']=='admin')
							{
									
									
								if((!empty($data['authentication_type']) && $data['authentication_type']!='2') || $data['authentication_type']=='0'){

									if(!empty($data['session_id']) && $data['session_id']!='' && $data['session_id']!=null && $data['session_id']!=" ")
									{
											
										/*	Zend_Session::destroy(true,true);
										 $upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'),'session_id'=>'');
										 $upd_qry=$admin_obj->update($upd_arr,"admin_user_name='".$username."'");
										 $data=array('success'=>'','failure'=>'Already Login');
										 echo json_encode($data);exit;  */

										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										//$authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}
										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										//$upd_qry=$admin_obj->update($upd_arr,"admin_user_name='".$username."'");
										$upd_qry=$admin_obj->update($upd_arr,$DB->quoteInto("admin_user_name=?",$username));
										

										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$admin_session->insert($ins_arr);
									}
									else
									{

										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										//$authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}
										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										$upd_qry=$admin_obj->update($upd_arr,$DB->quoteInto("admin_user_name=?",$username));


										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$admin_session->insert($ins_arr);
									}
								}
								else
								{

									$authUserNamespace->uname=$username;
									$authUserNamespace->utype='admin';
								}
							}
							if(!empty($data['user_type']) && $data['user_type']=='special')
							{
								$authUserNamespace->user = $username;
								$authUserNamespace->user_type =$user_type;
								if($remember==1)
								{
									if (isset($_COOKIE['remember'])){
										$lxid = $_COOKIE['remember'];
									} else {

										$expire = time()+60*60*24*30;
										setcookie('remember', $username, $expire, '/');
									}
								}
							}

							if(!empty($data['user_type']) && $data['user_type']=='subadmin')
							{
								$authUserNamespace->subadmin = 1;
								$authUserNamespace->user = $username;
								$authUserNamespace->user_type =$user_type;
								
								$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
									
								if((!empty($data['authentication_type']) && $data['authentication_type']!='2') || $data['authentication_type']=='0'){

									if(!empty($data['session_id']) && $data['session_id']!='' && $data['session_id']!=null && $data['session_id']!=" ")
									{
										/*	Zend_Session::destroy(true,true);
										 $upd_arr=array('session_id'=>'');
										 $upd_qry=$sub_admin_obj->update($upd_arr,"email='".$username."'");
										 $data=array('success'=>'','failure'=>'Already Login');
										 echo json_encode($data);exit;  */

										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										//$authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}

										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										$upd_qry=$sub_admin_obj->update($upd_arr,$DB->quoteInto("email=?",$username));
										
										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$user_session->insert($ins_arr);
									}
									else
									{

										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										//$authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}

										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										$upd_qry=$sub_admin_obj->update($upd_arr,$DB->quoteInto("email=?",$username));
										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$user_session->insert($ins_arr);
									}
								}
								else
								{

									$authUserNamespace->uname=$username;
									$authUserNamespace->utype='subadmin';
								}
								
								
							}
							if(!empty($data['user_type']) && $data['user_type']=='binary')
							{
								if(!empty($data['authentication_type']) && $data['authentication_type']!='' && $data['authentication_type']!='2')
								{
									if(!empty($data['session_id']) && $data['session_id']!='' && $data['session_id']!=null && $data['session_id']!=" ")
									{
										/*	Zend_Session::destroy(true,true);
										 $upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'),'session_id'=>'');
										 $upd_qry=$user_obj->update($upd_arr,"username='".$username."'");
										 $data=array(''=>'success','failure'=>'Already Login');
										 echo json_encode($data);exit; */

										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										// $authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}
										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										$upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));


										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$user_session->insert($ins_arr);
									}
									else
									{
										$authUserNamespace->user = $username;
										$authUserNamespace->user_type =$user_type;
										$authUserNamespace->user_id =$data['user_id'];
										//$authUserNamespace->last_successful =$data['last_successful_login'];
										// $authUserNamespace->last_failed =$data['last_failed_login'];
										setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
										if($remember==1)
										{
											if (isset($_COOKIE['remember'])){
												$lxid = $_COOKIE['remember'];
											} else {

												$expire = time()+60*60*24*30;
												setcookie('remember', $username, $expire, '/');
											}
										}
										$ip_address=$misc_obj->get_client_ip();
										$sess_id=zend_session::getId();
										$authUserNamespace->sess_id = $sess_id;
										$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
										$upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));


										$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
										$ins_qry=$user_session->insert($ins_arr);
									}

								}
								else
								{
									$authUserNamespace->uname=$username;
									$authUserNamespace->utype='binary';
								}
							}


							//$authUserNamespace->userInfo = $userInfo;
							//$updateSession =$common_obj->updateSession($username,"", session_id());
						}
						else
						{
							//$get_user_data=$common_obj->getUserInfo($username);
							//if(!empty($get_user_data) && sizeof($get_user_data)>0)
						//	{
							//echo "here";
								$ip_address=$misc_obj->get_client_ip();
								$upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'),'session_id'=>'');
								if($username=="admin"){
									$upd_qry=$admin_obj->update($upd_arr,$DB->quoteInto("admin_user_name=?",$username));
								}else{
									$upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));
								}
							//}
						}
						print_r ($result);exit;
					}

				/*}
				else
				{
					$data=array('success'=>'','failure'=> TOKENMSG);
					echo json_encode($data);exit;
				}*/
			}
		}
		catch(Exception $e)
		{
			
			print_r($e->getMessage());
			//$data=array('success'=>'','failure'=> 'Incorrect Username/Password. Please try to login again.');
			$data=array('success'=>'','failure'=> 'There seems to be some system issue right now, please try again in some time.');
			//die($e);
			echo json_encode($data);exit;
		}


	}
	public function checkregisterAction()
	{
		try {
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$user_obj = new Gbc_Model_DbTable_Userinfo();
			$antixss = new Gbc_Model_Custom_StringLimit();
			$aesctr=new Gbc_Model_AesCtr();
			foreach($_REQUEST as $key => $value)
			{
				if($key!='password' && $key!='password_rpt'){
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;

						}

						//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
					}
				}

			}

			$username=$common_obj->cleanQueryParameter($_POST['username']);
			$ref_sponser_id=$common_obj->cleanQueryParameter($_POST['ref_sponser_id']);
			$email_addr=$common_obj->cleanQueryParameter($_POST['email_addr']);
			$password=($_POST['password']);
			$password=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
			//$password_rpt=$common_obj->cleanQueryParameter($_REQUEST['password_rpt']);
			$leg=$common_obj->cleanQueryParameter($_POST['leg']);
			$token=$common_obj->cleanQueryParameter($_POST['token']);
			$captcha=$common_obj->cleanQueryParameter($_POST['captcha']);
			//$og_captcha=$common_obj->cleanQueryParameter($_REQUEST['og_captcha']);
			//if($authUserNamespace->token == $token){
			if(empty($_POST['username']) || empty($_POST['email_addr']) ||  empty($_POST['password']) || empty($_POST['ref_sponser_id']) || empty($_POST['captcha'])){
				$data=array('success'=>'','failure'=>'All fields are required!!');
				echo json_encode($data);exit;
			}
			else if($captcha!=$authUserNamespace->code){
				$data=array('success'=>'','failure'=>'The reCAPTCHA is not entered correctly.');
				echo json_encode($data);exit;
			}
			if (!filter_var($email_addr, FILTER_VALIDATE_EMAIL) == false) {
		
					/*$check_user = $user_obj->fetchRow($user_obj->select()
											->where("binaryUser is NOT NULL AND email_address='".trim($email_addr)."'"));*/
				
				$check_user = $user_obj->fetchRow($user_obj->select()
											->where("binaryUser is NOT NULL")
											->where("email_address=?",trim($email_addr)));
					
					if(isset($check_user) && sizeof($check_user)>0)
					{
						$data=array('success'=>'','failure'=>'Email id already present');
						echo json_encode($data);exit;
					}
				
			}
			else
			{
				$data=array('success'=>'','failure'=>'Please enter valid email address');
				echo json_encode($data);exit;
			}
			/*$chk_data = $user_obj->fetchRow($user_obj->select()
										->where("binaryUser is NOT NULL AND (sponsor_id='".$ref_sponser_id."' or username='".$ref_sponser_id."')"));*/
			
			$chk_data = $user_obj->fetchRow($user_obj->select()
										->where("binaryUser =1")
										->where($DB->quoteInto("sponsor_id=?",$ref_sponser_id) . ' OR ' . $DB->quoteInto("username=?",$ref_sponser_id)));

			
			if(empty($chk_data) || !isset($chk_data) || sizeof($chk_data)<=0)
			{
				$data=array('success'=>'','failure'=>'Reference id wrong');
				echo json_encode($data);exit;
			}
			
			
			/*$userInfo_chk = $user_obj->fetchRow($user_obj->select()
								->where("username='".$username."'"));*/
			
			$userInfo_chk = $user_obj->fetchRow($user_obj->select()
								->where("username=?",$username));
								
			if(!empty($userInfo_chk) || sizeof($userInfo_chk)>0)
			{	
				$data=array('success'=>'','failure'=>'Username already present');
				echo json_encode($data);exit;
			}
		/*	else if($_REQUEST['password'] != $_REQUEST['password_rpt']){
				$data=array('success'=>'','failure'=>'Passwords do not match');
				echo json_encode($data);exit;
			}*/

			$result=array();
			$url= BASE."/Registerapi?username=".$username."&ref_sponser_id=".$ref_sponser_id."&email_addr=".$email_addr."&password=".$password."&child_position=".$leg."&leg=".$leg."&captcha=".$captcha;
			$result=$common_obj->call_curl($url);

			if(isset($result) && sizeof($result)>0)
			{

				$userInfo=(array)json_decode($result,true);
				$sucess= $userInfo['success'];
				if(isset($sucess) && !empty($sucess) && $sucess!='' && $sucess!=null)
				{

					$result=array();
					$url= BASE."/Userinfoapi?username=".$username;
					$result=$common_obj->call_curl($url);
					$userInfo=(array)json_decode($result,true);
					$session_id='6enibpitioh9gf576j2pvca795';
					$authUserNamespace->user = $username;
					$authUserNamespace->user_type ='binary';
					$ip_address=$misc_obj->get_client_ip();
					$sess_id=zend_session::getId();
					$authUserNamespace->sess_id = $sess_id;
					$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
					$upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));

					$authUserNamespace->sess_id = $sess_id;
					//$authUserNamespace->user_id = $userInfo['id'];
					$authUserNamespace->userInfo = $userInfo;
					setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
					//$updateSession =$common_obj->updateSession($username,"", session_id());
				}
				$db->commit();
				print_r ($result);exit;
			}
			/*}
			else
			{
				$db->rollBack();
				$data=array('success'=>'','failure'=>TOKENMSG);
				echo json_encode($data);exit;
			}*/
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=> $e->getMessage());
			echo json_encode($data);exit;
		}
	}


	public function logoutAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$user_obj = new Gbc_Model_DbTable_Userinfo();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$username=$authUserNamespace->user;
		$upd_arr=array('session_id'=>'');
		$upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));
		Zend_Session::destroy(true,true);
		$this->_redirect("/Login");
		//echo "success";exit;
	}

	
public function forgotpasswordAction()
	{
		try {
		

			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$misc_obj = new Gbc_Model_Custom_Miscellaneous();
			$user_reset = new Gbc_Model_DbTable_Resetpassword();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

			
			
			
			$recoveryUsername = $common_obj->cleanQueryParameter($_POST["forgot_pass_username"]);
			$token=$common_obj->cleanQueryParameter($_POST['token']);
			//if($authUserNamespace->token == $token){
				$result=array();
				$url= BASE."/Userinfoapi?username=".$recoveryUsername;
				$result=$common_obj->call_curl($url);
				$userInfo=(array)json_decode($result,true);

				if(!empty($userInfo) && sizeof($userInfo)>0)
				{
					$time = time();
					//$userid = $recoveryUsername;
					//$oldpassword = hash('sha256', $userInfo['data']['password']);
				//	$recoveryEmail = $userInfo['data']["email_address"];
				//	$token = "t={$time}&i={$userid}&o={$oldpassword}";
				//	$verification = hash_hmac('sha256', $token, $userInfo['data']['salt']);


					$time = time();
					$userid = $recoveryUsername;
					$oldpassword = hash('sha256', $userInfo['data']['password']);
					$token = "t={$time}&i={$userid}&o={$oldpassword}";
					$verification = hash_hmac('sha256', $token, $userInfo['data']['salt']);

					// Commented below code to fix forgot password issue - Removed special characters from temp password - Pronto Infotech - 13.02.2017
	//				$NewPassword = $misc_obj->generateStrongPassword($length = 10, $add_dashes = false, $available_sets = 'ludse');
					$NewPassword = $misc_obj->generateStrongPassword($length = 10, $add_dashes = false, $available_sets = 'lud');

				$salt = $misc_obj->generateSalt();
				$password = $misc_obj->encryptPassword($NewPassword, $salt);

				$existUserReset = $common_obj->getUserReset($userid);
		//	 var_dump($existUserReset);
				// exit;
				//	print_r($misc_obj->noError($existUserReset));
				if($misc_obj->noError($existUserReset)){
					//$resetPasswordQuery = "update reset_password set salt = '$salt',password = '$password',reset_code = '$verification',updated_on = now() where username = '$userid'";
				//	$resetPassword = runQuery($resetPasswordQuery,$conn);

					$upd_arr=array('reset_code'=>$verification,'salt'=>$salt,'password'=>$password,'updated_on'=>new Zend_Db_Expr('NOW()'));
					$upd_qry=$user_reset->update($upd_arr, $DB->quoteInto("username=?",$userid));
//print_r($upd_qry);	
			}else{
					//$resetPasswordQuery = "insert into reset_password(username,salt,password,reset_code) values('$userid','$salt','$password','$verification')";
					//$resetPassword = runTransactionedQuery($resetPasswordQuery,$conn);

					$insertPwdReset=array("username"=>$userid,"salt"=>$salt,"password"=>$password,"reset_code"=>$verification);
					$insertPwd=$user_reset->insert($insertPwdReset);
			
//print_r($insertPwd);
//echo "here";
	}



				//	$newpasswordurl = BASE."/Login/Resetpwd?t=$time&i=$userid&o=$oldpassword&v=$verification";
					$newpasswordurl = BASE."/Login/changepwd?key=$verification";
					//echo $newpasswordurl;
					//exit;
					$email = "<div style='padding: 10px; margin: 10px;'><div style='padding: 0px;'>
					<img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
					<h2>Password Recovery</h2>
					<p>A request for password recovery has been initiated for the account related to this email address. Please click on the below link to access your account and use the temporary password given below to login </p>
					
					<p><a href = '".$newpasswordurl."'>".$newpasswordurl."</a></p>
							
					
					<p>Your temporary password is: ".$NewPassword."</p>


					<p>Do not forget to visit the my profile section and change your password after first login.</p>
					
					</div>";

					//	$query = sprintf("SELECT contact_email,contact_phone FROM profile_contact WHERE username='%s'", $username);
					//$prof_cont_obj=new Gbc_Model_DbTable_Profilecontact();

					//$user_row = $prof_cont_obj->fetchRow($prof_cont_obj->select()
					//->where("username='".$username."'"));
					/*		if(isset($userInfo) && sizeof($userInfo)>0)
					 {
					 $contactMail=$userInfo->comm_email;
					 }
					 else
					 {
					 $contactMail=$recoveryEmail;
					 }*/

					if(!empty($userInfo['data']["comm_email"]) && $userInfo['data']["comm_email"]!='')
					{
						$tomail=$userInfo['data']["comm_email"];
					}
					else
					{
						$tomail=$userInfo['data']["email_address"];
					}
					//$sendMail = $common_obj->sendMail($tomail, "admin@gainbitco.in", "Password recovery request", $email);

					$to = $tomail;
					$from = 'support@gainbitcoin.com';
					$replyTo = 'thegainbitcoinhelp@gmail.com';
					$subject = 'Password recovery request';
					$message = $email;
					$htmlMessage = $email;
					$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

					if(!empty($sendMail)){
						//Show success and navigate user to profile creation process starting with secure account
						$msg = "A password recovery email has been sent to the registered email address. Please follow instructions within that email to reset your password.";
						$arr=array('success'=>'success','failure'=>'','data'=>$msg);
						echo json_encode($arr);exit;
					} else {
						$msg = "Recovery email could not be sent at this moment. Please try again later or contact us if the problem persists.";
						$arr=array('success'=>'success','failure'=>'','data'=>$msg);
						echo json_encode($arr);exit;
					}
					//$this->_redirect("/Login");
				}else
				{
					$arr=array("success"=>'','failure'=>'Could not find username.');
					echo json_encode($arr);exit;
				}
			/*}
			else
			{
				$arr=array("success"=>'','failure'=>TOKENMSG);
				echo json_encode($arr);exit;
			}*/
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=> $e->getMessage());
			echo json_encode($data);exit;
		}
		
	}


	public function resetpwdAction()
	{
		/*$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		 $common_obj = new Gbc_Model_Custom_CommonFunc();
		 //$username=$authUserNamespace->user;
		 $reset_req_obj= new Gbc_Model_DbTable_Resetreq();
		 $misc_obj = new Gbc_Model_Custom_Miscellaneous();
		 if($_POST){
			// var_dump($_SERVER['REQUEST_METHOD']);
			$ip = $misc_obj->get_client_ip();
			$user = $_SESSION['user'];
			$method = $_SERVER['REQUEST_METHOD'];
			$data = json_encode($_POST);
			$source = basename($_SERVER['PHP_SELF']);

			// $last_query = mysql_info();
			// var_dump(mysql_info($this->db));
			// echo $user;
			$insrt_arr=array('username'=>$user,'method'=>$method,'data'=>$data,'source'=>$source,'ip_address'=>$ip);
			$insrt_qry=$reset_req_obj->insert($insrt_arr);

			}*/
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$input = $common_obj->cleanQueryParameter($_GET);
		// echo $tokenExpiryTime;
		$tokenExpiryTime = 60*60;
		$userid = $input['i'];
		$time = $input['t'];
		$expire_time = strtotime($time)+$tokenExpiryTime;
		$oldpassword = $input['o'];
		$hash = $input['v'];

		$p_info = $common_obj->getUserInfo($userid);
		if(empty($p_info ) || sizeof($p_info)<=0)
		{
			echo "This email address is not recognized as a registered email address. Please try again.";exit;
		}
		$token = "t=$time&i=$userid&o=$oldpassword";
		$correct_hash = hash_hmac('sha256', $token, $p_info->salt);

		if ( ($hash != $correct_hash) || ($oldpassword != hash('sha256', $p_info->password)) ) {
			echo ("That password link is invalid, has expired or has been used."."<br/>"."<br/>");
			$redirectURL =	BASEPATH."/Login";

			print("<script data-cfasync='false'>");
			print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
			print("</script>");

			print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
			exit;
			exit;
		}
		$this->view->username=$userid;
		$this->_helper->layout()->setLayout("login");
	}




	
	public function changepwdAction(){
		
		$this->_helper->layout()->setLayout("login");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		
		if(!empty($_REQUEST['key'])){

			$input = $common_obj->cleanQueryParameter($_REQUEST);
			$key = $input['key'];
			// echo $tokenExpiryTime;
			$existUserReset = $common_obj->getUserReset('',$key);
				// var_dump($existUserReset);
			if($misc_obj->noError($existUserReset)) {
				$existUserResetData = $existUserReset["errMsg"];
				 $userInfo["username"] = $existUserResetData->username;
				 $userInfo["salt"] = $existUserResetData->salt;
				 $userInfo["password"] = $existUserResetData->password;
				$updateAccount = $common_obj->updateUserAccountPassword($userInfo,true,$key);
				 //var_dump($updateAccount);
	//exit;
		
				if($updateAccount == "success"){
						//if($misc_obj->noError($updateAccount)) {
					//Show message and navigate user to home page
					$msg = "Password Successfully Changed!!";

					$description = "Password has been changed for ".$userInfo["username"]." and password is ".$userInfo["password"];
					if($_SESSION["user"]){
						$description .= " by ".$_SESSION["user"];
					}
					if(!empty($description)){
						$saveUserLog = $common_obj->saveUserLog($userInfo['username'],"user_info",$description);
					 }		
				 } else {
					//Show message and navigate user to home page
					$msg = "Password Change Failed. Please try again later.";
				} 		
				// var_dump($updateAccount);
				// exit;
				//$alertMessage = createAlertMsgBox($msg);
				//printArr($alertMessage);
				echo $msg."<br/>"."<br/>";
				$redirectURL =	BASEPATH."/Login";
				
				print("<script data-cfasync='false'>");
					print("var t = setTimeout(\"window.location='".$redirectURL."';\", 30000);");
				print("</script>");	
			}else{
				$msg = "You link has been used or expired, Please try again";
				//$alertMessage = createAlertMsgBox($msg);
				//printArr($alertMessage);
				echo $msg."<br/>"."<br/>";
				$redirectURL =	BASEPATH."/Login";
				
				print("<script data-cfasync='false'>");
					print("var t = setTimeout(\"window.location='".$redirectURL."';\", 30000);");
				print("</script>");	

			}
		}

	}
	


	public function changepwdAction_old()
	{

		$this->_helper->layout()->setLayout("login");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$userID = !empty($_GET["usr"])?$_GET["usr"]:$_POST["username"];
		if(!empty($_GET["usr"]) && $_GET["usr"]!='')
		{
			$userID = $common_obj->cleanQueryParameter($_GET["usr"]);
		}
		else
		{
			$userID = $common_obj->cleanQueryParameter($_GET["username"]);
		}

		//$userInfo = $common_obj->getUserInfo($userID);

		//$user_id=$authUserNamespace->id;
		//$data1=$common_obj->GetAccessRightByUserId('22',$user_id);



		$result=array();
		$url= BASE."/Userinfoapi?username=".$userID;
		$result=$common_obj->call_curl($url);
		$userInfo=(array)json_decode($result,true);

		if(empty($userInfo['data']) || sizeof($userInfo['data'])<=0)
		{
			$msg = "We're sorry. This email address is not registered with us. You will now be navigated to the registration page.";
			$authUserNamespace->msg=$msg;
			echo $msg."<br/>"."<br/>";
			$redirectURL =	BASEPATH."/Login";

			print("<script data-cfasync='false'>");
			print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
			print("</script>");

			print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
			exit;
			exit;
			//$this->_redirect("/Login");exit;
		}

		/* if(!empty($_GET["usr"]) && ((isset($authUserNamespace->user)  && ($authUserNamespace->user == 'admin')) || (!empty($data1->edit) && $data1->edit==1))){
			$user = $_GET["usr"];
			$userInfo['data']["password"] = $default;
			$updateAccount = $common_obj->updateUserAccountPassword($userInfo['data'], true);

			if(!empty($updateAccount))
			{
			$msg="Password Successfully Changed.";
			$authUserNamespace->msg=$msg;

			$description = "Password has been reset for ".$user." and password is ".$userInfo["password"];
			if(!empty($description)){
			$saveUserLog = $common_obj->saveUserLog($user,"user_info",$description);
			}
			}
			else
			{
			$msg="Password Change Failed. Please try again later.";
			$authUserNamespace->msg=$msg;
			}

			}*/

		if(!empty($_GET)){

			$newPasswd = $common_obj->cleanQueryParameter($_GET["password"]);
			$newPasswdRpt = $common_obj->cleanQueryParameter($_GET["password_rpt"]);
			if($newPasswd != $newPasswdRpt){
				$msg = "The passwords you entered are not identical";
				$authUserNamespace->msg=$msg;
				//$this->_redirect("/Login");exit;
				echo $msg."<br/>"."<br/>";
				$redirectURL =	BASEPATH."/Login";

				print("<script data-cfasync='false'>");
				print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
				print("</script>");
					
				print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
				exit;
				exit;
			}
			else
			{
				$userInfo["data"]["password"] = $newPasswd;
				$updateAccount =$common_obj-> updateUserAccountPassword($userInfo["data"], true);
				if(!empty($updateAccount))
				{

					$msg="Password Successfully Changed.";
					$authUserNamespace->msg=$msg;

					$description = "Password has been reset for ".$userID." and password is ".$userInfo["data"]["password"];
					if(!empty($description)){
						$saveUserLog = $common_obj->saveUserLog($userID,"user_info",$description);
					}
					echo $msg."<br/>"."<br/>";
					$redirectURL =	BASEPATH."/Login";

					print("<script data-cfasync='false'>");
					print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
					print("</script>");

					print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
					exit;
					exit;
					//$this->_redirect("/Login");exit;
				}
				else
				{
					$msg="Password Change Failed. Please try again later.";
					$authUserNamespace->msg=$msg;
					echo $msg."<br/>"."<br/>";
					$redirectURL =	BASEPATH."/Login";

					print("<script data-cfasync='false'>");
					print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
					print("</script>");

					print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
					exit;
					exit;
				}
			}
		}
		else
		{
			$msg="You are not authorised";
			echo $msg."<br/>"."<br/>";
			$redirectURL =	BASEPATH."/Login";

			print("<script data-cfasync='false'>");
			print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
			print("</script>");

			print("<a href=".$redirectURL." class='center block big_bold'>Click here if you are not redirected automatically by your browser within 3 seconds</a>");
			exit;
			exit;
		}
	}


}

