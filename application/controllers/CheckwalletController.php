<?php
class CheckwalletController extends Zend_Controller_Action{
	
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

    public function checkcoinbankwalletAction()
    {
        
        $common_obj = new Gbc_Model_Custom_CommonFunc();
		
		$walletAddr=$common_obj->cleanQueryParameter(($_POST['wallet_addr']));
		
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

		if(!empty($response->error)){
			$data=array('success'=>'','failure'=>'Invalid Coinbank Address.');
			echo json_encode($data);exit;
		}else{
			$data=array('success'=>'Valid Coinbank Address','failure'=>'');
			echo json_encode($data);exit;			
		}
    }	
	

	
	
}
