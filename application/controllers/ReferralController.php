<?php

class ReferralController extends Zend_Controller_Action{

	public function init(){

		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		/*	if(empty($_POST['user']) || empty($_POST['user'])=='' || empty($_POST['place']) || empty($_POST['place'])=='')
		 {
			echo "Something went wrong!! Please try again";exit;
			}*/
		$ref_user=trim($_GET['user']);
		$userflag=trim($_GET['userflag']);
	 	$placement=trim($_GET['plc']);
		$refsponserid=trim($_GET['ref_sponsor_id']);
		

		$comm_obj = new Gbc_Model_Custom_CommonFunc();

		$user_data=$comm_obj->getUserInfo($ref_user);
		if(empty($user_data) || sizeof($user_data)<=0)
		{
			echo "Incorrect Link. Please try with corrent link.";
		}
		$this->_helper->layout()->setLayout("login");




		$this->view->ref_user=$ref_user;
		$this->view->userflag=$userflag;
		$this->view->place=$placement;


	}
	public function clickAction(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		/*	if(empty($_POST['user']) || empty($_POST['user'])=='' || empty($_POST['place']) || empty($_POST['place'])=='')
		 {
			echo "Something went wrong!! Please try again";exit;
			}*/
		$ref_user=trim($_POST['user']);
		$userflag=trim($_POST['userflag']);
	 	$placement=trim($_POST['plc']);
		$refsponserid=trim($_POST['ref_sponsor_id']);
		

		$comm_obj = new Gbc_Model_Custom_CommonFunc();

		$user_data=$comm_obj->getUserInfo($ref_user);
		if(empty($user_data) || sizeof($user_data)<=0)
		{
			echo "Incorrect Link. Please try with corrent link.";
		}
		$this->_helper->layout()->setLayout("login");




		$this->view->ref_user=$ref_user;
		$this->view->userflag=$userflag;
		$this->view->place=$placement;
        
      
	}

	public function checkregisterAction()
	{
		try {
				
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
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			$placement=	$common_obj->cleanQueryParameter($_POST['plc']);
			$username=$common_obj->cleanQueryParameter($_POST['username']);
			$ref_sponser_id=$common_obj->cleanQueryParameter($_POST['ref_sponser_id']);
			$email_addr=$common_obj->cleanQueryParameter($_POST['email_addr']);
			$password=$_POST['password'];
			$password=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
			//$password_rpt=$_POST['password_rpt'];
			$leg=$common_obj->cleanQueryParameter($_POST['leg']);
			$captcha=$common_obj->cleanQueryParameter($_POST['captcha']);
			$token=$_POST['token'];
			//$og_captcha=$_POST['og_captcha'];
	//		if($authUserNamespace->token == $token){	
			$userflag=$_POST['userflag'];
			//echo $userflag;exit;
			
			if(empty($_POST['username']) || empty($_POST['email_addr']) ||  empty($_POST['password']) || empty($_POST['ref_sponser_id']) || empty($_POST['captcha'])){
				$data=array('success'=>'','failure'=>'All fields are required!!');
				echo json_encode($data);exit;
			}
			else if($captcha!=$authUserNamespace->code){
				$data=array('success'=>'','failure'=>'The reCAPTCHA is not entered correctly.');
				echo json_encode($data);exit;
			}
			if (!filter_var($email_addr, FILTER_VALIDATE_EMAIL) == false) {
		
					$check_user = $user_obj->fetchRow($user_obj->select()
											->where("binaryUser = 1 AND email_address=?",trim($email_addr)));
				
				
											

											
											
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
										->where($db->quoteInto("sponsor_id=?",$ref_sponser_id) . ' OR ' . $db->quoteInto("username=?",$ref_sponser_id)));
										
		
			if(empty($chk_data) || !isset($chk_data) || sizeof($chk_data)<=0)
			{
				$data=array('success'=>'','failure'=>'Reference id wrong');
				echo json_encode($data);exit;
			}
			
			
			$userInfo_chk = $user_obj->fetchRow($user_obj->select()
								->where("username=?",$username));
								
			if(!empty($userInfo_chk) || sizeof($userInfo_chk)>0)
			{	
				$data=array('success'=>'','failure'=>'Username already present');
				echo json_encode($data);exit;
			}
			
			
				
			$result=array();
			$url= BASE."/Registerapi?username=".$username."&ref_sponser_id=".$ref_sponser_id."&email_addr=".$email_addr."&password=".$password."&child_position=".$leg."&leg=".$leg."&captcha=".$captcha."&plc=".$placement;
			$result=$common_obj->call_curl($url);

			if(isset($result) && sizeof($result)>0)
			{
					
				$userInfo=(array)json_decode($result,true);
				$sucess= $userInfo['success'];
				if(isset($sucess) && !empty($sucess) && $sucess!='' && $sucess!=null)
				{
					if(empty($userflag) || $userflag=='')
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
						//$upd_qry=$user_obj->update($upd_arr,"username='".$username."'");
						$upd_qry=$user_obj->update($upd_arr,$db->quoteInto("username=?",$username));
						
						$authUserNamespace->sess_id = $sess_id;
						//$authUserNamespace->user_id = $userInfo['id'];
						$authUserNamespace->userInfo = $userInfo;
						setcookie('user_id', $username, null, BASEPATH.'/', null, null , TRUE);
						//$updateSession =$common_obj->updateSession($username,"", session_id());
					}
				}
				$db->commit();
					
				print_r ($result);exit;
			}
	/*		}
			else
			{
				$db->rollBack();
				$data=array('success'=>'','failure'=>TOKENMSG);
				echo json_encode($data);exit;
			} */
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=> $e->getMessage());
			echo json_encode($data);exit;
		}
	}
	
	public function regeneratecaptchaAction()
	{
		require("library/recaptcha/captcha.php");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$username=$authUserNamespace->user;
		$ga = new PHPGangsta_GoogleAuthenticator();
	}



}
