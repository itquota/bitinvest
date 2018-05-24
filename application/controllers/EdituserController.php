<?php
class EdituserController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		try{
			$this->view->title="Gainbitcoin - Users";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('22',$user_id);
			if((!empty($data1->edit) && ($data1->edit==1)) || $authUserNamespace->user=='admin')
			{

			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
			$this->_helper->layout()->setLayout("admindashbord");

			$userinfoObj=new Gbc_Model_DbTable_Userinfo();
			//$profilecontactObj=new Gbc_Model_DbTable_Profilecontact();
			$useradmin=$authUserNamespace->user;

			$user_id='1';


			//$data1=GetAccessRightByUserId('22',$user_id,$conn);
			$misc_obj=new Gbc_Model_Custom_Miscellaneous;
			$common_obj=new Gbc_Model_Custom_CommonFunc();
			$data1=$common_obj->GetAccessRightByUserId('22',$user_id);
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$this->_redirect("/Profileerror/errormsg");

					}

				}

			}


			if(!empty($data1['edit']) && $data1['edit']==1 || (($authUserNamespace->user=="admin")||($authUserNamespace->user=="1"))){
				$update_by = $authUserNamespace->user;
			}
			else{
				$msg="You do not have sufficient privileges to access this area.";

				$this->view->msg=$msg;exit;
			}

			//$user=$this->_request->getParam("username");

			if(!empty($_POST['username']) && isset($_POST['username']))
			{

				//$user=$this->_request->getParam("username");
				$user = $_POST['username'];
				$this->view->username = $user;
				 
				$resultprofile=$userinfoObj->fetchRow($userinfoObj->select()
						->setIntegrityCheck(false)
						->from(array('user_info'))
						//->joinLeft(array('profile'=>'profile_contact'),'Profile.username = user_info.username', array('Profile.full_name','Profile.contact_email','Profile.contact_phone'))
						->where("username= ?",$user));

				//echo "<pre>";
				//print_r($resultprofile)    ;exit;

				$this->view->result=$resultprofile;
			}

			$country_obj=new Gbc_Model_DbTable_Countries();
			$country_data=$country_obj->fetchAll($country_obj->select()
					->order('ccode ASC')
					);

			//$country_data = $country_data->toArray();
			$this->view->country_data = $country_data;

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

	public function changeauthAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$status = $common_obj->cleanQueryParameter($_POST['status']);
		$username = $common_obj->cleanQueryParameter($_POST['username']);
		$token = $common_obj->cleanQueryParameter($_POST['token']);
		$userinfo_Obj=new Gbc_Model_DbTable_Userinfo();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		/*Change 1 Starts*/
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$useradmin=$authUserNamespace->user;
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $ip_address=$misc_obj->get_client_ip();
        $comment = $common_obj->cleanQueryParameter($_POST['comment']);
		/*Change 1 Ends*/
		//if(!empty($authUserNamespace->token) && $authUserNamespace->token==$token){
		if(!empty($status) && $status!='')
		{
			$updateuserinfo = array("authentication_type"=>$status);
			$upd_qry = $userinfo_Obj->update($updateuserinfo,$DB->quoteInto("username=?",$username));
			if(!empty($upd_qry))
			{
				if($status==1)
				{
					
					/*Change 2 Starts*/
					
						$description = "2fa disabled by $useradmin Comment Mentioned : ".$comment;
						
						if(!empty($description))
						{
							$table_name="user_info";
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$saveUserLog=$common_obj->saveUserLog2fa($username,$table_name,$description,$useradmin,$ip_address);
						}
					/*Change 2 Ends*/					
					
					$arr=array('success'=>"success",'failure'=>'',data=>'2FA has been disabled');
					echo  json_encode($arr);exit;
				}
				else
				{
					$arr=array('success'=>"success",'failure'=>'',data=>'2FA has been enabled');
					echo  json_encode($arr);exit;
				}
			}

		}
		else
		{
			$arr=array('success'=>"",'failure'=>'failure',data=>'Failed to update.');
			echo  json_encode($arr);exit;
		}
		//    }
		//$arr=array('success'=>"",'failure'=>'failure',data=>'Invalid Token');
		//    echo  json_encode($arr);exit;

	}

	public function editchangeAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$userinfoObj=new Gbc_Model_DbTable_Userinfo();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		$binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
		$useradmin=$authUserNamespace->user;
		if($this->_request->isPost("submit"))
		{
			$antixss = new Gbc_Model_Custom_StringLimit();
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

		//	if($authUserNamespace->token==$token && !empty($_POST['token'])){
				if(!empty($_POST['username']))
				{

					$resultuser=$userinfoObj->fetchRow($userinfoObj->select()
							->from(array('user_info'),array('email_address','password','salt'))
							->where("username= ?",$_POST['username']));
					if(trim($_POST['email'])==$resultuser->email_address)
					{
						$email=$resultuser->email_address;
					}
					else
					{
						$chkquery=$userinfoObj->fetchRow($userinfoObj->select()
								->from(array('user_info'))
								->where("email_address= ?",$_POST['email']));

						if(!empty($chkquery))
						{
							$msg = '<span style="color:green;font-wieght-bold;">Email Id already Exist</span>';
							$this->view->msg=$msg;
						}
						else{
							$email=$_POST['email'];
						}

					}

					/*if($_POST['password']==$resultuser->password)
					 {
					 $Password=$resultuser->password;
					 $salt =$resultuser->salt;
					 }
					 else{
					 $salt = $misc_obj->generateSalt();
					 $Password = $misc_obj->encryptPassword($_POST['password'], $salt);
					 }*/

					$name = $this->_request->getParam("full_name");
					$phone = $this->_request->getParam("phone");
					$comment = $this->_request->getParam("comment");
					$profile_email = $this->_request->getParam("profile_email");
					$ccode = $this->_request->getParam("ccode");
					$update=date('Y-m-d h:i:s');
					$payment_status= !empty($_POST['payment_status'])?$_POST['payment_status']:'0';
					$b2_status=!empty($_POST['b2_status'])?$_POST['b2_status']:'0';

				
					
						$resultprofile=$userinfoObj->fetchRow($userinfoObj->select()
									->setIntegrityCheck(false)
									->from(array('user_info'))
									->where("username= ?",$_POST['username']));

					//$updateuserinfo=array("user_info.email_address"=>$email,"user_info.password"=>$Password,"user_info.salt"=>$salt,"user_info.isActiveId"=>$_POST['status'],"user_info.payment_status"=>$payment_status,"user_info.b2_status"=>$b2_status,"user_info.comment"=>$_POST['comment'],"user_info.updated_on"=>$update,"name"=>$name,"phone"=>$phone,"comment"=>$comment);
					//$updateuserinfo=array("user_info.email_address"=>$email,"user_info.isActiveId"=>$_POST['status'],"user_info.payment_status_hold"=>$payment_status,"user_info.b2_status"=>$b2_status,"user_info.comment"=>$_POST['comment'],"user_info.updated_on"=>$update,"name"=>$name,"phone"=>$phone,"comment"=>$comment,'comm_email'=>$profile_email,'country'=>$ccode);
				
					$description="";

						//if($updateuserinfodata == 1 || $updateuserinfodata == 0 )
						
							
						//	if($resultprofile->email_address != $email)
						//	{
						//		$description .= "Email address has been changed from  ".$resultprofile->email_address." to $email";
					//		}
							if($resultprofile->name != $name){
								$description .= "Name has been changed from  ".$resultprofile->name." to $name";
							}
							if($resultprofile->isActiveId != $_POST['status']){
								$description .= "isActiveId has been changed from  ".$resultprofile->isActiveId." to ".$_POST['status'];

							}
							if($resultprofile->comm_email != $profile_email){
								$description .= "Profile email has been changed from  ".$resultprofile->comm_email." to ".$profile_email;

							}
							if($resultprofile->country != $ccode){
								$description .= "Country has been changed from  ".$resultprofile->country." to ".$ccode;

							}
							if($resultprofile->payment_status_hold != $payment_status){
								$description .= "Payment Status has been changed from  ".$resultprofile->payment_status_hold." to $payment_status";
							}
							if($resultprofile->b2_status != $b2_status){
								$description .= "B2 Status has been changed from  ".$resultprofile->b2_status." to $b2_status";
							}

							if($resultprofile->comment != $_POST['comment']){
								$description .= "Comment has been changed from  ".$resultprofile->comment." to ".$_POST['comment'];
							}

							if($resultprofile->phone != $_POST['phone']){
								$description .= "Contact Phone has been changed from  ".$resultprofile->phone." to ".$_POST['phone'];
							}
							
						
							$user = $_POST['username'];
							
							if(!empty($description))
							{
								$updateuserinfo=array("user_info.isActiveId"=>$_POST['status'], "user_info.payment_status_hold"=>$payment_status, "user_info.b2_status"=>$b2_status, "user_info.comment"=>$_POST['comment'],  "user_info.updated_on"=>$update, "name"=>$name, "phone"=>$phone, "comment"=>$comment, 'comm_email'=>$profile_email, 'country'=>$ccode);
								//$updateBinary_network=$Binary_network_detailsObj->update($updateBinary_network_details,$DB->quoteInto("id=?",$id));
								$updateuserinfodata=$userinfoObj->update($updateuserinfo,$DB->quoteInto("user_info.username=?",$_POST['username']));



								$redis = $comm_obj->getRedisInstance();
								$redis_tree_root_user = "";
								$res = $binary_usr_ref->fetchAll($binary_usr_ref->select()
										->setIntegrityCheck(false)
										->from(array("binary_user_refences"), array('redis_tree_root_user'))
										->where("username = ?", $_POST['username'])
										);

								if (isset($res[0]['$redis_tree_root_user']))
									$redis_tree_root_user = $res[0]['$redis_tree_root_user'];
									if (strlen($redis_tree_root_user)){
										$tree = json_decode($redis->get($redis_tree_root_user));
										for($i = 0; $i < count($tree); $i++){
											if ($tree[$i]->username == $_POST['username']){
												$tree[$i]->isActiveId = $_POST['status'];
												break;
											}
										}
										$redis->set($redis_tree_root_user, json_encode($tree));
									}
							}else{
								 echo json_encode("nochange");exit;
							}
								
					if(!empty($updateuserinfodata))
						{
							//$saveUserLog = saveUserLog($update_by,"user_info,profile_contact",$description);
							$table_name="user_info";
							$common_obj = new Gbc_Model_Custom_CommonFunc();
							$saveUserLog=$common_obj->saveUserLog($user,$table_name,$description);
							if(!empty($saveUserLog)){
								echo json_encode("success");exit;
							}

						//	}
							//$msg = '<span style="color:green;font-weight-bold;">User Updated Successfully</span>';
							//$this->view->msg=$msg;
							echo json_encode("success");exit;

						} else {
						 echo json_encode("notupdated");exit;
						 //$msg = 'User Not Updated';
						 //$this->view->msg=$msg;
						 }

				}
				else{
					echo json_encode("failure");exit;
				}
				//            echo "<script>window.location.reload();</script>";
		/*	}else{
				//$data=array('success'=>'','failure'=>'failure');
				//echo json_encode($data);exit;
				echo json_encode("failure");exit;
			}*/
		}


	}


}

