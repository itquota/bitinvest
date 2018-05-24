<?php
class ChangepassController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//	if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();

			$this->_helper->layout->disableLayout();
			$userObj = new Gbc_Model_DbTable_Userinfo();
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		   $antixss = new Gbc_Model_Custom_StringLimit();
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
			$username=$authUserNamespace->user;
			
			$user = $this->_request->getParam("username");
			 $token=$_POST['token'];
//		 	    if($authUserNamespace->token==$token){
			if($user!=""){

				$userInfo['data']["username"] = $user;
				$userInfo['data']["password"] = "GBCPASS@1234";
					
				/*$userInfo = $userObj->fetchRow($userObj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('t'=>"user_info"))
				 ->where("username='".$user."'"));

				 $email=$userInfo->email_address;
				 $email=$userInfo->email_address;*/
					
					
				//$updateAccount = updateUserAccount($userInfo, $conn, true);

				$common_obj = new Gbc_Model_Custom_CommonFunc();
				$updateAccount=$common_obj->updateUserPassword($userInfo,$changePwd = false);
					
				if($updateAccount!="")
				{
					$description = "Password resetted for '".$user."' by '".$username."'." ;


					if(!empty($description)){
						$saveUserLog = $common_obj->saveUserLog($user,"user_info",$description);						
					}					
					
					$msg = "Password Successfully Changed!!";


					//$description = "Password has been reset for ".$user." and password is ".$userInfo['data']["password"];
					//echo $description;exit;

					$db->commit();	
					$arr=array('Success'=>"Password has been reset for ".$user." and password is ".$userInfo['data']["password"],'Failure'=>'');
					echo  json_encode($arr);exit;
				}
					
					

			}else{
				$db->rollBack();
				//echo "no";exit;
				$arr=array('Success'=>'','Failure'=>'error while updating password please try again... ');
				echo  json_encode($arr);exit;
			}
		 	 /*   	}else{
		 			$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     echo json_encode($data);exit;
		 		} */
			//	if($user!="" && ((isset($authUserNamespace->user)  && ($authUserNamespace->user == 'admin')))){
			//		echo "in";exit;
			//			//$user = $_POST["username"];
			//
			//			 echo $user;
			//			$userInfo["password"] = "binary@12345";
			//		//	$updateAccount = updateUserAccount($userInfo, $conn, true);
			//
			//			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//			$updateAccount=$common_obj->updateUserAccount($userInfo, $changePwd = false);
			//
			//			if(!empty($updateAccount)){
			//		//Show message and navigate user to home page
			//				$msg = "Password Successfully Changed!!";
			//
			//
			//				$description = "Password has been reset for ".$user." and password is ".$userInfo["password"]." by ".$_SESSION["user"];
			//
			//			if(!empty($description)){
			//				$saveUserLog = saveUserLog($user,"user_info",$description);
			//				$table_name="user_info";
			//				$common_obj = new Gbc_Model_Custom_CommonFunc();
			//				$saveUserLog=$common_obj->saveUserLog($username,$table_name,$description);
			//
			//
			//
			//				 }
			//			} else {
			//		//Show message and navigate user to home page
			//			$msg = "Password Change Failed. Please try again later.";
			//			}
			//
			//
			//
			//
			//
			//
			//
			//		}
		
		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}

}
