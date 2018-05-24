<?php
class BinaryuserController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Users";
		$arr=array();

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('22',$user_id);
		$antixss = new Gbc_Model_Custom_StringLimit();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}


		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$binaryuserObj = new Gbc_Model_DbTable_Userinfo();
		$accessrightObj= new Gbc_Model_DbTable_Accessright();
		$loginattemptObj = new Gbc_Model_DbTable_Loginattempts();

		/*$result=array();
		 $result=$binaryuserObj->fetchAll($binaryuserObj->select()
		 ->setIntegrityCheck(false)
		 ->from(array('user_info'))
		 ->where("binaryUser= ?",1)
		 ->where("username!= ?",admin));
		 //->where("'id='$id"));*/


		$PaginateLimit=100;
		$UserCountRes=$binaryuserObj->fetchRow($binaryuserObj->select()
		->setIntegrityCheck(false)
		->from(array('user_info'),array('count(username) as count'))
		->where("binaryUser= ?",1)
		->where("username!= ?",'admin'));



		$UserCount=$UserCountRes->count;
		// echo $UserCount;
		$pages = ceil($UserCount/$PaginateLimit);

		$this->view->pages=$pages;
		// echo $pages;
		if(!empty($_GET['page']))
		{
			$value = $_GET['page'];
			$antixss->setEncoding($_GET['page'], "UTF-8");
			if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
				$this->_redirect("/Profileerror/errormsg");

			}
			$startLimit=($_GET['page']*$PaginateLimit);
		}
		else
		{
			$startLimit=0;
		}

		if(!empty($_POST)){
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$this->_redirect("/Profileerror/errormsg");


					}

				}

			}
			$token=$_POST['token'];

		//	if($authUserNamespace->token==$token){
				$searchQuery = $_POST['search'];
					
				$SearchResult=$binaryuserObj->fetchAll($binaryuserObj->select()
				->from(array('user_info'),array('username' ,'email_address','comm_email' ,'name' ,'country','phone' ,'salt' ,'password' ,'session_start_time' ,'session_id' ,'referrer_id' ,'otp','wallet_addr' ,'sponsor_id' ,'ref_sponsor_id' ,'lock_status' ,'user_type','isActiveId','isLevelFull' ,'login_date','assign_date','ip_address' ,'created_on' ,'updated_on' ,'isVerified' ,'payment_status_hold' ,'b2_status','comment' ,'binaryUser' ,'authentication_type','placement','secret','login_pin','com_code','wallet_ver_code','profile_ver_code','daily_payout_hold','payout_option','daily_payout_value_for_110_days'))
				->where("username= ?",$searchQuery));
					
				if(!empty($SearchResult) && sizeof($SearchResult)>0)
				{
					$SearchResult = $SearchResult->toArray();
				}
					

				//$SearchResult = ("SELECT * FROM user_info where username='$searchQuery' ");
				if(isset($SearchResult) && sizeof($SearchResult)>0)
				{
					$result = $SearchResult;

				}
					
				if(!empty($result)){
					$allUsers = $SearchResult;

				}else{
					$allUsers = $common_obj->getBinaryUsersforAdmin($PaginateLimit,$startLimit);
				}

				// var_dump($allUsers);
		/*	}else{
				//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				//echo json_encode($data);exit;
				$msg = 'Invalid Request Found.';
				$authUserNamespace->errmsg=$msg;
			} */
		}else{

			$allUsers = $common_obj->getBinaryUsersforAdmin($PaginateLimit,$startLimit);
		}


		$this->view->result =$allUsers;










	}
	
	/*
	public function unlockAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		$loginattemptObj = new Gbc_Model_DbTable_Loginattempts();
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$binaryuserObj = new Gbc_Model_DbTable_Userinfo();

		

		if(!empty($_POST['username']) || $authUserNamespace->user =="admin")
		{
			$arr=array();


			$user=$_POST['username'];

			$result=$binaryuserObj->fetchRow($binaryuserObj->select()
			->setIntegrityCheck(false)
			->from(array('user_info'),array('lock_status'))
			->where("username= ?",$user));


			if(!empty($result) && sizeof($result)>0)
			{
				$lockstatus=$result->lock_status;

			}
			if($lockstatus=='lock')
			{
				$lockstatus='unlock';
			}


			$ip_address= $misc_obj->get_client_ip();

			$updateAttempts=array("login_attempts"=>'',"comment"=>'Unlocked by admin',"ip_address"=>$ip_address,"updated_on"=>new Zend_Db_Expr('NOW()'));

			$updatelogin=$loginattemptObj->update($updateAttempts,"username='$user'");


			$updatelockstatus=array("comment"=>'Unlocked by admin',"lock_status"=>$lockstatus,"ip_address"=>$ip_address,"updated_on"=>new Zend_Db_Expr('NOW()'));

			$updatelogin=$binaryuserObj->update($updatelockstatus,"username='$user'");

			if(!empty($updatelogin))
			{
				$arr=array('Success'=>"Unlocked successfully",'Failure'=>'');
				echo  json_encode($arr);exit;
			}
			else
			{
				$arr=array('Success'=>'','Failure'=>"Failed to unlock");
				echo  json_encode($arr);exit;
			}
		}
			
		else
		{
			echo "Please provide proper data";exit;

		}

	}
	*/
	
	public function unlockAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$user_id=$authUserNamespace->user_id;
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$binaryuserObj = new Gbc_Model_DbTable_Userinfo();
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$data1=$misc_obj->GetAccessRightByUserId('49',$user_id);
		
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		/*$user=$_POST['username'];
			echo "inuserlock".$user;exit;*/



		if(!empty($_POST['username']) )
		{
			$arr=array();
			$user=$_POST['username'];

			$result=$binaryuserObj->fetchRow($binaryuserObj->select()
			->setIntegrityCheck(false)
			->from(array('user_info'),array('lock_status','payment_status_hold','daily_payout_hold'))
			->where("username= ?",$user));


			if(!empty($result) && sizeof($result)>0)
			{
				$lockstatus=$result->lock_status;
				$daily_payout_hold=$result->daily_payout_hold;
				$payment_status_hold=$result->payment_status_hold;

			}
			
			if($lockstatus=='lock' && ($payment_status_hold == '1') && ($daily_payout_hold == '1'))
			{
				$lockstatus='unlock';
				$payment_status_hold='0';
				$daily_payout_hold='0';
				$ip_address= $misc_obj->get_client_ip();
				
				$updatelockstatus=array("comment"=>'Unlocked by admin',"lock_status"=>$lockstatus, "daily_payout_hold"=>$daily_payout_hold, "payment_status_hold"=>$payment_status_hold, "ip_address"=>$ip_address,"updated_on"=>new Zend_Db_Expr('NOW()'));
				$updatelogin=$binaryuserObj->update($updatelockstatus,"username='$user'");

				if(!empty($updatelogin)){
					$description = " User $user lock_status is unlocked, payment_status_hold and daily_payout_hold are changed " ;
                    $saveUserLog = $common_obj->saveUserLog($user,"user_info",$description);
					
					$arr=array('Success'=>"Unlocked successfully",'Failure'=>'');
				//	echo  json_encode($arr);exit;
				}else{
					$arr=array('Success'=>'','Failure'=>"Failed to unlock");
					
				}
			}


			
		}
			
		else
		{
			//echo "Please provide proper data";exit;
			$arr=array('Success'=>'','Failure'=>"Please provide proper data");

		}
		
		echo  json_encode($arr);exit;
	}
	
	
	public function payoutholdAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		
		$binaryuserObj = new Gbc_Model_DbTable_Userinfo();
		$useradmin=$authUserNamespace->user;
        $ip_address=$misc_obj->get_client_ip();


		if(!empty($_POST['username']) || $authUserNamespace->user =="admin")
		{
			$arr=array();


			$user=$_POST['username'];

			$result=$binaryuserObj->fetchRow($binaryuserObj->select()
			->setIntegrityCheck(false)
			->from(array('user_info'),array('daily_payout_hold'))
			->where("username= ?",$user));


			if(!empty($result) && sizeof($result)>0)
			{
				$holdstatus=$result->daily_payout_hold;

			}
			if($holdstatus == 0)
			{
				$lockstatus = 1;
				$description = "Payout status changed to hold by ". $useradmin;
				
			}else{
				$lockstatus = 0;
				$description = "Payout status changed to unhold by ". $useradmin;
				
			}


			$updatepayouthold=array("daily_payout_hold"=>$lockstatus,"updated_on"=>new Zend_Db_Expr('NOW()'));

			$updatepayouthold=$binaryuserObj->update($updatepayouthold,"username='$user'");
			

			if(!empty($updatepayouthold))
			{
				if(!empty($description))
				{
					$table_name="user_info";
					$common_obj = new Gbc_Model_Custom_CommonFunc();
					$saveUserLog=$common_obj->saveUserLog2fa($user,$table_name,$description,$useradmin,$ip_address);
				}					
				
				$arr=array('Success'=>"Payout hold status changed successfully",'Failure'=>'');
				echo  json_encode($arr);exit;
			}
			else
			{
				$arr=array('Success'=>'','Failure'=>"Failed to hold payout");
				echo  json_encode($arr);exit;
			}
		}
			
		else
		{
			echo "Please provide proper data";exit;

		}

	}

}
