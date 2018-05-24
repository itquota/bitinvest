<?php
class DeviceconfirmationController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((empty($authUserNamespace->uname)) || (!isset($authUserNamespace->uname)) || $authUserNamespace->uname=="")$this->_redirect("/Login");
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		try
		{
			require("library/PHPGangsta/GoogleAuthenticator.php");
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("login");//dashboard
			//$username=$authUserNamespace->user;
			//$this->view->username=$username;


			$website = BASE; //Your Website
			$title= 'bitcoin';

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

	public function verifyauthAction()
	{
		/*if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
		 {
			//echo "You do not have access to this area";exit;
			$data=array('success'=>'','failure'=>'You do not have access to this area');
			echo json_encode($data);exit;
			}*/
		$code=trim($_POST['code']);
		$username=trim($_POST['username']);
		$user = new Gbc_Model_DbTable_Userinfo();
		require("library/PHPGangsta/GoogleAuthenticator.php");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		

		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$user_session = new Gbc_Model_DbTable_Usersessions();

		if(!empty($authUserNamespace->utype) && $authUserNamespace->utype=='binary')
		{
			$userInfo = $common_obj->getUserInfo($username);
			$ga = new PHPGangsta_GoogleAuthenticator();
			$secret= $userInfo ->secret;


			//echo $secret;
			$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
			if ($checkResult) {
				$ip_address=$misc_obj->get_client_ip();
				$sess_id=zend_session::getId();
				$authUserNamespace->sess_id = $sess_id;
				$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
				$upd_qry=$user->update($upd_arr,$DB->quoteInto("username= ?",$username));
					
				$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
				$ins_qry=$user_session->insert($ins_arr);
					

				$authUserNamespace->user=$username;
				$authUserNamespace->user_type =$userInfo->user_type;
				setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
				$data=array('success'=>'success','failure'=>'','data'=>'binary');
				echo json_encode($data);exit;
				//echo "success";exit;
			} else {
				$get_user_data=$common_obj->getUserInfo($username);
				if(!empty($get_user_data) && sizeof($get_user_data)>0)
				{
					$ip_address=$misc_obj->get_client_ip();
					$upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'));
					$upd_qry=$user->update($upd_arr,$DB->quoteInto("username = ?",$username));
				}
				$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
				echo json_encode($data);exit;
				//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
				//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
			}
		}



		if(!empty($authUserNamespace->utype) && $authUserNamespace->utype=='admin')
		{
			$adminuser = new Gbc_Model_DbTable_Adminsetting();
			$admin_session = new Gbc_Model_DbTable_Adminsession();
			$userInfo =	 $adminuser->fetchRow($adminuser->select()
			->where("status = ?",1));

			$ga = new PHPGangsta_GoogleAuthenticator();
			$secret= $userInfo ->google_auth_code;



			$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
			if ($checkResult) {

				$ip_address=$misc_obj->get_client_ip();
				$sess_id=zend_session::getId();
				$authUserNamespace->sess_id = $sess_id;
				$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
				$upd_qry=$adminuser->update($upd_arr,$DB->quoteInto("admin_user_name= ?",$username));


				$ins_arr=array('username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
				$ins_qry=$admin_session->insert($ins_arr);
					

				$authUserNamespace->user=$username;
				$authUserNamespace->user_type = "admin";
				$authUserNamespace->user_id =$userInfo->id;
				setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
				$data=array('success'=>'success','failure'=>'','data'=>'admin');
				echo json_encode($data);exit;
				//echo "success";exit;
			} else {
				 
				//$get_user_data=$common_obj->getUserInfo($username);
				if(!empty($userInfo) && sizeof($userInfo)>0)
				{
					$ip_address=$misc_obj->get_client_ip();
					$sess_id=zend_session::getId();
					$upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
						
					$upd_qry=$adminuser->update($upd_arr,$DB->quoteInto("admin_user_name=?",$username));
						
				}
				$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
				echo json_encode($data);exit;
				//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
				//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
			}
		}



		if(!empty($authUserNamespace->utype) && $authUserNamespace->utype=='subadmin')
		{
			try{

				$sub_admin_obj = new Gbc_Model_DbTable_Subadminusers();
				/*$userInfo = $sub_admin_obj->fetchRow($sub_admin_obj->select()
				->where('email ="'.trim($username.'"')));*/
				$userInfo = $sub_admin_obj->fetchRow($sub_admin_obj->select()
				->where("email =?",trim($username)));

				$ga = new PHPGangsta_GoogleAuthenticator();
				$secret= $userInfo ->google_auth_code;


				//echo $secret;
				$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
				if ($checkResult) {
					$ip_address=$misc_obj->get_client_ip();
					$sess_id=zend_session::getId();
					$authUserNamespace->sess_id = $sess_id;
					$upd_arr=array('last_successful_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);
					$upd_qry=$sub_admin_obj->update($upd_arr,$DB->quoteInto("email=?",$username));
						
						

					$authUserNamespace->user=$username;
					$authUserNamespace->user_type = "subadmin";
					$authUserNamespace->user_id =$userInfo->id;
					setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
					$data=array('success'=>'success','failure'=>'','data'=>'subadmin');
					echo json_encode($data);exit;
					//echo "success";exit;
				} else {

					//$get_user_data=$common_obj->getUserInfo($username);
					if(!empty($userInfo) && sizeof($userInfo)>0)
					{

						$ip_address=$misc_obj->get_client_ip();
						$sess_id=zend_session::getId();
						$upd_arr=array('last_failed_login'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip_address,'session_id'=>$sess_id);

						$upd_qry=$sub_admin_obj->update($upd_arr,$DB->quoteInto("email=?",$username));
					}
					$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
					echo json_encode($data);exit;
					//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
					//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
				}
			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
		}



	}
}