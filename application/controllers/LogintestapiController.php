<?php
class LogintestapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		$aesctr=new Gbc_Model_AesCtr();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$user_info_table = new Gbc_Model_DbTable_Subadminusers();
		$user = new Gbc_Model_DbTable_Userinfo();
		$adminuser = new Gbc_Model_DbTable_Adminsetting();
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		$login_attempt = new Gbc_Model_DbTable_Loginattempts();


		$username=$comm_obj->cleanQueryParameter(($_REQUEST['username']));
		$password=$comm_obj->cleanQueryParameter($_REQUEST['password']);
	
		$password=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
		$_REQUEST['password']=$password;
		$antixss = new Gbc_Model_Custom_StringLimit();

		foreach($_REQUEST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'Invalid Input.');
					echo json_encode($data);exit;

				}

				//echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
			}

		}
	$password='amit@amit.com';
		if(empty($username) || !isset($username) || $username=='')
		{

			$data=array('success'=>'','failure'=>'Username should not be blank');
			echo json_encode($data);exit;
		}
		else if (empty($password) || !isset($password) || $password=='')
		{
			$data=array('success'=>'','failure'=>'Password should not be blank');
			echo json_encode($data);exit;
		}
		else
		{
		
			$admin = false;
			$ip_address=$misc_obj->get_client_ip();
			$admin_user_row = $adminuser->fetchRow($adminuser->select()
			->where("status=1"));



			$admin_pwd='';
			$admin_user_name='';
			$pwd1='';
			$pwd2='';
			if($admin_user_row!="" && sizeof($admin_user_row)>0)
			{
			
				$Admpassword=$misc_obj->encryptPassword($password,$admin_user_row->admin_salt);
				
				$admin_pwd=$admin_user_row->admin_pwd;
				echo $Admpassword." and ".$admin_pwd;exit;
				$admin_user_name=$admin_user_row->admin_user_name;
				$pwd1=$admin_user_row->pwd1;
				$pwd2=$admin_user_row->pwd2;
				$stcPss=$misc_obj->encryptPassword($password, $admin_user_row->static_salt);

			}

			$user_row = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('u.username' ))
			->where("username = '$username' && lock_status = 'lock'"));

			if($user_row!="" && sizeof($user_row)>0)
			{
				$data=array('success'=>'','failure'=>'You are locked out');
				echo json_encode($data);exit;
			}
			if(($Admpassword == $admin_pwd && $username == $admin_user_name))
			{
			echo "fsdfdsf";exit;
				$auth = Array(
			    "errCode" => Array (
			            "-1" => -1
				),
					
			    "errMsg" => "Login Successful."
			    );
			    $admin=true;
			}
			else if(($stcPss == $pwd1 && $username != $admin_user_name))
			{

				$userInfo = $misc_obj->getUserInfo($username);
				if(isset($userInfo ) && sizeof($userInfo)>0)
				{
					$auth = Array(
						"errCode" => Array (
								"-1" => -1
					),

						"errMsg" => "Login Successful."
						);
						$adminUser=true;
				}
				$adminUser=true;
			}
			else if(($stcPss == $pwd2 && $username != $admin_user_name))
			{
				$auth = Array(
						"errCode" => Array (
								"-1" => -1
				),
					
						"errMsg" => "Login Successful."
						);
						$unlockUser=true;
			}
			else
			{
				
			
				$SubadminAuth = $user_info_table->fetchRow($user_info_table->select()
				->setIntegrityCheck(false)
				->from(array('u' =>'sub_admin_users'),array('u.salt','u.password','id','session_id' ))
				->where('u.email ="'.trim($username.'"')));

				if(!empty($SubadminAuth)  && sizeof($SubadminAuth)>0)
				{
					$user_id=$SubadminAuth->id;
					$sess_id=$SubadminAuth->session_id;
				
					if($username != 'admin'){
						$userInfo =$misc_obj-> getSubadminUserInfo($username);

						//	$userInfo = $userInfo["errMsg"];
						$auth = Array(
						"errCode" => Array (
								"-1" => -1
						),
							
						"errMsg" => "Login Successful."
						);

						$SubAdminUser=true;
						$redirectURL = "../view/admin/index.php";

					}
				}
				else
				{
						
					//$auth = $misc_obj->checkPassword($username, $password);
					$auth_result = $user->fetchRow($user->select()
					->setIntegrityCheck(false)
					->from(array('u' =>'user_info'),array('u.username','u.password','u.salt' ))
					->where("u.username = '$username' && u.binaryUser IS NOT NULL"));
					if(isset($auth_result) && sizeof($auth_result)>0)
					{
						$salt = $auth_result->salt;
						$pwd =$misc_obj->encryptPassword($password, $salt);
						if ($pwd == $auth_result->password) {

							$auth["msg"] = "Login Successful.";
						} else {
							$data=array('success'=>'','failure'=>'Incorrect Username/Password. Please try to login again.');
							echo json_encode($data);exit;
						}
					}
					else
					{
					echo "fsdf";exit;
						$data=array('success'=>'','failure'=>'Incorrect Username/Password. Please try to login again.');
						echo json_encode($data);exit;
					}
				}

			}

			if(!empty($unlockUser)){
				$updateAttempts=array('login_attempts'=>'','comment'=>'Unlocked by'.$username,'updated_on'=>new Zend_Db_Expr('NOW()'));
				$upd_qry=$login_attempt->update($updateAttempts,"(username = '$username') and (date(created_on) ='".new Zend_Db_Expr('NOW()')."'");
				// echo "update login_attempts set login_attempts = '', comment = 'Unlocked by $username' ,updated_on = now() where ip_address = '$ip_address' and (date(created_on) = curdate())";
				//$updateAttempts = mysql_query("update login_attempts set login_attempts = '', comment = 'Unlocked by $username' ,updated_on = now() where (username = '$username' or ip_address = '$ip_address') and (date(created_on) = curdate())");

				$msg = "User unlocked successfully.";
				$data=array('success'=>'','failure'=>$msg,'redirect'=>'login');
				echo json_encode($data);exit;

			}else{
			
				$LoginAttempts= $login_attempt->fetchRow($login_attempt->select()
				->setIntegrityCheck(false)
				->from(array('u' =>'login_attempts'),array('u.login_attempts'))
				->where("u.username ='".trim($username."' AND (date(created_on) like '".new Zend_Db_Expr('NOW()')."')")));

				if(isset($LoginAttempts) && sizeof($LoginAttempts)>0)
				{

					$login_attempt1 = $LoginAttempts->login_attempts;
					if($LoginAttempts->login_attempts >= 5){
						$msg = "Sorry, You are locked out of login attempts after 5 unsuccessful attempts for today. Please try again tomorrow";
					}else{
						if(($auth) == false){
							$login_attempts = ($LoginAttempts->login_attempts) + 1;
							$updatedata=array('login_attempts'=>$login_attempts,'updated_on'=>new Zend_Db_Expr('NOW()'));
							$where="(username = '$username') and (date(created_on) = '".new Zend_Db_Expr('NOW()')."')";
							$userUpdate= $login_attempt->update($updatedata,$where);
						}
							
					}
				}
				else
				{
					
					$LoginAttempts = $user->fetchRow($user->select()
					->setIntegrityCheck(false)
					->from(array('u' =>'login_attempts'),array('sum(u.login_attempts) as login_attempts' ))
					->where("u.ip_address = '$ip_address' and (date(u.created_on) like '".new Zend_Db_Expr('NOW()')."')"));

	

					if(!empty($LoginAttempts) && isset($LoginAttempts) && sizeof($LoginAttempts)>0 && ($LoginAttempts->login_attempts)!= null)
					{
							echo "fdsffhghdf";exit;

						$ip_login_attempt = $LoginAttempts->login_attempts;
						if(($LoginAttempts->login_attempts)>20)
						{
							$msg = "Sorry, Your IP address has been blocked due to spam unsuccessful attempts for today. Please try again tomorrow";
							//$data=array('success'=>'','failure'=>$msg);
							//echo json_encode($data);exit;
						}
						else
						{
							if($auth==false){
								$login_attempts = 1;

								$insert_data=array('username'=>$username,'login_attempts'=>$login_attempts,'ip_address'=>$ip_address);
									
								$login_attempt->insert($insert_data);
							}
						}
					}
					else
					{
						$login_attempts = 1;
							
						$insert_data=array('username'=>$username,'login_attempts'=>$login_attempts,'ip_address'=>$ip_address);

						$login_attempt->insert($insert_data);
						
					}
				}
				if(!empty($LoginAttempts) && (($LoginAttempts->login_attempts >= 5) || (($LoginAttempts->login_attempts) >= 20))){
					$data=array('success'=>'','failure'=>$msg);
					echo json_encode($data);exit;
				}

					
			}








			$authentication_type='';
			if(isset($auth) && sizeof($auth)>0)
			{
				
				if((($username != $admin_user_name) && (!isset($SubAdminUser))) || (!empty($adminUser))){
					$userInfo1 =$misc_obj-> getUserInfo($username);
					if(isset($userInfo1) && sizeof($userInfo1)>0)
					{
						
						$login_date=date('Y-m-d');
						$assign_date=date("Y-m-d", strtotime( "$login_date +49 days" ) );
							
						$redirectURL = "../view/dashboard.php";
					}
					else if($adminUser)
					{
						$redirectURL = $rootURL."/view/dashboard.php";
					}
					else
					{
						$data=array('success'=>'','failure'=>'Error fetching User Info. Please try again.');
						echo json_encode($data);exit;
					}
				}
				else
				{
					$redirectURL = "../view/admin/index.php";
				}
				//$session_id='6enibpitioh9gf576j2pvca795';
				//$updateSession =$comm_obj-> updateSession($username, "", $session_id);
				if(!empty($admin)){
					$user_type="admin";
					$user_id='1';
					$sess_id=$admin_user_row->session_id;
				}else if(!empty($SubAdminUser)){
					$user_type="subadmin";

					//$updateSession = updateSubadminSession($username, $conn, "", session_id());

				}
				else
				{
					$userIf =$misc_obj-> getUserInfo($username);
					$authentication_type=$userIf->authentication_type;
					$user_type=$userIf->user_type;
					$comm_email=$userIf->comm_email;
					$email_address=$userIf->email_address;
					$mobile=$userIf->phone;
					$sess_id=$userIf->session_id;
				

				}
				$data=array('success'=>'success','failure'=>'','redirect'=>$redirectURL,'username'=>$username,'user_type'=>$user_type,'authentication_type'=>$authentication_type,'user_id'=>$user_id,'comm_email'=>$comm_email,'email_address'=>$email_address,'mobile'=>$mobile,'session_id'=>$sess_id);
				echo json_encode($data);exit;
			}
			else
			{
				$data=array('success'=>'','failure'=>'Your account will be locked after 5 unsuccessful attempts for today.');
				echo json_encode($data);exit;
			}


		}




	}


}