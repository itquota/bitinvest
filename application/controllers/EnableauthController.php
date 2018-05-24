<?php
class EnableauthController extends Zend_Controller_Action{
	
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="")$this->_redirect("/Login");
		//if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	
	public function indexAction()
	{
	
		//$this->view->title="Gainbitcoin - Admin Payout";
		require("library/PHPGangsta/GoogleAuthenticator.php");
		$ga = new PHPGangsta_GoogleAuthenticator();
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$admin_set_obj = new Gbc_Model_DbTable_Adminsetting();
		$username=$authUserNamespace->user;
		
		$secret=$ga->createSecret();
		$authUserNamespace ->secret=$secret;
		
		$website = BASE; //Your Website
		$title= 'bitcoin';
		$qrCodeUrl = $ga->getQRCodeGoogleUrl($title, $secret,$website);
			
		if($authUserNamespace->user_type=="admin")
		{
		$admin_set=$admin_set_obj->fetchRow($admin_set_obj->select()
		->setIntegrityCheck(false)
		->from(array('a'=>"admin_setting"))
		->where("admin_user_name=?",$username)
		);
	
		
		$this->view->qrCodeUrl=$qrCodeUrl;
			
		
		//$this->view->admin_set=$admin_set;
		}
		else {
			
		$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
		$username=$authUserNamespace->user;
	
		$admin_set = $sub_admin_obj->fetchRow($sub_admin_obj->select()
		->setIntegrityCheck(false)
		->from(array("u"=>"sub_admin_users"))
		->where("u.email=?",$username));
		
		$this->view->qrCodeUrl=$qrCodeUrl;
		}
		
		$this->view->admin_set=$admin_set;
	
		
	//	$this->view->sub_admin_set=$sub_admin_set;
		
	
	}
	public function enableauthAction()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
		{
			echo "You do not have access to this area";exit;
		}
		require("library/PHPGangsta/GoogleAuthenticator.php");
	//	$user_obj = new Gbc_Model_DbTable_Userinfo();
		$admin_set_obj = new Gbc_Model_DbTable_Adminsetting();
		$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$code=trim($_POST['code']);
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$ga = new PHPGangsta_GoogleAuthenticator();
		$secret= $authUserNamespace ->secret;
		
		$username=$authUserNamespace->user;
		
		if($authUserNamespace->user_type=="subadmin")
		{
			
			
			$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
			if ($checkResult) {
				
					$common_obj = new Gbc_Model_Custom_CommonFunc();
					$update_arr=array('google_authenticator'=>2,'google_auth_code'=>$secret,'updated_on'=>new Zend_Db_Expr('NOW()'));
					$upd=$sub_admin_obj->update($update_arr,$DB->quoteInto("email=?",$username));
					$authUserNamespace->gacode="success";
					$data=array('success'=>'success','failure'=>'');
					echo json_encode($data);exit;
					
		
				} else {
					$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication');
					echo json_encode($data);exit;
					//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
					//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
				}
			}
			else{
			
					$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
					if ($checkResult) {
					
						$common_obj = new Gbc_Model_Custom_CommonFunc();
						$update_arr=array('google_authenticator'=>2,'google_auth_code'=>$secret,'updated_on'=>new Zend_Db_Expr('NOW()'));
						$upd=$admin_set_obj->update($update_arr,$DB->quoteInto("admin_user_name=?",$username));
						$authUserNamespace->gacode="success";
						$data=array('success'=>'success','failure'=>'');
						echo json_encode($data);exit;
						
			
					} else {
						$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication');
						echo json_encode($data);exit;
						//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
						//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
					}
		}
		
	}
	public function disableauthAction()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		if(empty($authUserNamespace->user) || $authUserNamespace->user=='')
		{
			echo "You do not have access to this area";exit;
		}
		require("library/PHPGangsta/GoogleAuthenticator.php");
		
		$code=trim($_POST['code']);
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$admin_set_obj = new Gbc_Model_DbTable_Adminsetting();
		$sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
		
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$ga = new PHPGangsta_GoogleAuthenticator();
		//$secret= $authUserNamespace ->secret;
		$username=$authUserNamespace->user;

//	$userInfo = $common_obj->getUserInfo($username);
		if($authUserNamespace->user_type=="subadmin")
		{
			
				$subadminuser = $sub_admin_obj->fetchRow($sub_admin_obj->select()
				->setIntegrityCheck(false)
				->from(array("u"=>"sub_admin_users"),array("google_auth_code"))
				->where("email=?",$username));
			
				$secret=$subadminuser->google_auth_code;
			
			
				$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
				if ($checkResult) {
					$common_obj = new Gbc_Model_Custom_CommonFunc();
					$update_arr=array('google_authenticator'=>1,'updated_on'=>new Zend_Db_Expr('NOW()'));
					$upd=$sub_admin_obj->update($update_arr,$DB->quoteInto("email=?",$username));
					$authUserNamespace->gacode="success";
					$data=array('success'=>'success','failure'=>'');
					echo json_encode($data);exit;
					//echo "success";exit;
				} else {
					$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
					echo json_encode($data);exit;
					//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
					//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
				}
			
		
			
		
		}
		else{
				$admin_set=$admin_set_obj->fetchRow($admin_set_obj->select()
				->setIntegrityCheck(false)
				->from(array("u"=>"admin_setting"),array("google_auth_code")));
						
				$secret= $admin_set->google_auth_code;
				
				//echo $secret;
				$checkResult = $ga->verifyCode($secret,$code, 2);    // 2 = 2*30sec clock tolerance
				if ($checkResult) {
					$common_obj = new Gbc_Model_Custom_CommonFunc();
					$update_arr=array('google_authenticator'=>1,'updated_on'=>new Zend_Db_Expr('NOW()'));
					$upd=$admin_set_obj->update($update_arr,$DB->quoteInto("admin_user_name= ?",$username));
					$authUserNamespace->gacode="success";
					$data=array('success'=>'success','failure'=>'');
					echo json_encode($data);exit;
					//echo "success";exit;
				} else {
					$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP');
					echo json_encode($data);exit;
					//$data=array('success'=>'','failure'=>'Incorrect One Time Password for 2FA Authentication. Please generate a new OTP.');
					//echo "Incorrect One Time Password for 2FA Authentication. Please generate a new OTP"; exit;
				}
			}
	}

	
	

	
	
}
