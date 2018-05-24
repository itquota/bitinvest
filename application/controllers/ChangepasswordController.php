<?php

class ChangepasswordController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
	
		$checkAdminAuthentication=$misc_obj->checkAdminAuthentication();
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");


	}
	public function indexAction()
	{
		
		$this->view->title="Gainbitcoin - Change Password";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$adminsettingObj=  new Gbc_Model_DbTable_Adminsetting();
		$SubadminsettingObj = new Gbc_Model_DbTable_Subadminusers();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$misc_obj=new Gbc_Model_Custom_Miscellaneous;
		$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						//$data=array('success'=>'','failure'=>'Invalid Input.');
						//echo json_encode($data);exit;
							$messag =  'Invalid Input';
				 				$this->view->messag=$messag;

					}

				}

			}
		try{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout()->setLayout("admindashbord");

			if($this->_request->isPost()){
				$token=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['token']);
//				if($authUserNamespace->token==$token){
				$oldpassword=$Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['old_password']);
				if( (empty($_POST['old_password'])))
				{
					// printArr("<span style='text-align:right;margin-left: 20%;color:red'>All Fields Required</span>");
					$msg = "<p style='color:red;'>Old Password required</p>";
				}
				else if( (empty($_POST['new_password'])) && (empty($_POST['confirm'])) &&  (empty($_POST['static'])) && (empty($_POST['c1'])) &&  (empty($_POST['u1']))&&  (empty($_POST['k2'])))
				{
					$msg = "<p style='color:red;'>New Password should not blank</p>";
				}
				
				else if(!empty($_POST['new_password']) && ($_POST['new_password']!=$_POST['confirm']))
				{
					$msg = "<p style='color:red;'>New Password and Repeat Password not Match</p>";
				}
				

				else
				{
				
				if(!empty($authUserNamespace->user) && ($authUserNamespace->user=="admin") && $authUserNamespace->user_type=='admin'){			  
					 $adminsetting=$adminsettingObj->fetchRow($adminsettingObj->select()
					 ->setIntegrityCheck(false)
					 ->from(array('admin_setting')));

					 //$common_obj = new Gbc_Model_Custom_CommonFunc();
					 //$check=$common_obj->getAdmDetails($adminsetting);

					 $salt =$misc_obj-> generateSalt();
					 $pwd= $misc_obj->encryptPassword(trim($_POST['old_password']), $adminsetting->admin_salt);
					 $newPass=$misc_obj->encryptPassword(trim($_POST['new_password']), $salt);
					 $StaticPass=$misc_obj->encryptPassword(trim($_POST['static']), $adminsetting->static_salt);
					 $c1=$misc_obj->encryptPassword(trim($_POST['c1']), $adminsetting->static_salt);
					 $u1=$misc_obj->encryptPassword(trim($_POST['u1']), $adminsetting->static_salt);
					 $k2=$misc_obj->encryptPassword(trim($_POST['k2']), $adminsetting->static_salt);

					 if($pwd===($adminsetting->admin_pwd))
					 {
						$updated_on=date('Y-m-d h:i:s');
						$query=array();
						if(!empty($_POST['new_password']))
						{
							$query['admin_pwd'] = $newPass;
							$query['admin_salt'] = $salt;

							}
							if(!empty($_POST['static'])){

								$query['static_pwd'] = $StaticPass;
							}
							if(!empty($_POST['c1'])){

								$query['pwd1'] = $c1;
							}
							if(!empty($_POST['u1'])){
								$query['pwd2'] = $u1;

							}

							if(!empty($_POST['k2'])){
								$query['kit_g_pwd'] = $k2;
							}

							$where = array(
								 'id' => '1',
							);

							$query['updated_on'] = $updated_on;

							$upd=$adminsettingObj->update($query,$where);
							$description = " Password has been changed from  $pwd to $newPass" ;
                    $saveUserLog = $common_obj->saveUserLog("admin","user_info",$description);
							$msg =  "<p style='color:green;'>Password Updated Successfully</p>";

					 }else{

						$messag =  'Old Password is incorrect';
						$this->view->messag=$messag;
					 }
					}else if(!empty($authUserNamespace->user) && $authUserNamespace->user_type=='subadmin'){
						$username = $authUserNamespace->user;
						 $Subadminsetting = $SubadminsettingObj->fetchRow($SubadminsettingObj->select()
						 ->setIntegrityCheck(false)
						 ->from(array('sub_admin_users'))
						->where("email =?",$username));

						 //$common_obj = new Gbc_Model_Custom_CommonFunc();
						 //$check=$common_obj->getAdmDetails($adminsetting);

						 $salt =$misc_obj-> generateSalt();
						 $pwd= $misc_obj->encryptPassword(trim($_POST['old_password']), $Subadminsetting->salt);
						 $newPass=$misc_obj->encryptPassword(trim($_POST['new_password']), $salt);
					
						 if($pwd===($Subadminsetting->password))
						 {
							$updated_on=date('Y-m-d h:i:s');
							$query=array();
							if(!empty($_POST['new_password']))
							{
								$query['password'] = $newPass;
								$query['salt'] = $salt;

							}
								//$where = "email = '$username'";
								$where = array(
									 'email =?' => $username
								);

								$query['updated_on'] = $updated_on;
 
								$upd=$SubadminsettingObj->update($query,$where);
							 if($upd){
								 $description = " Password has been changed from  $pwd to $newPass" ;
                    $saveUserLog = $common_obj->saveUserLog($username,"user_info",$description);
								 
								$msg =  "<p style='color:green;'>Password Updated Successfully</p>";
							 }else{
								 $msg =  "<p style='color:green;'>Some Error in update</p>";
							 }

						 }else{

							$messag =  'Old Password is incorrect';
							$this->view->messag=$messag;
						 }
					
					}
				}
				
				$this->view->msg=$msg;

/*			}
			
		else{
			//$data=array('success'=>'','failure'=>'Invalid Request Found.');
			// echo json_encode($data);exit;
			$messag =  'Invalid Request Found';
				 	$this->view->messag=$messag;
			
				} */
			}
			$db->commit();	

		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}

}
