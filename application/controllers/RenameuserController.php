<?php
class RenameuserController extends Zend_Controller_Action{


	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Users";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('25',$user_id);
		if((!empty($data1->edit) && ($data1->edit==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$this->_helper->layout()->setLayout("admindashbord");
		$userObj=new Gbc_Model_DbTable_Userinfo();
		$userlogsObj=new Gbc_Model_DbTable_Userlogs();
		$useradmin=$authUserNamespace->user;
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
			
		/*$result=$userObj->fetchRow($userObj->select()
		 ->setIntegrityCheck(false)
		 ->from(array('user_info'),array("user_info.username"))
		 );*/
		//	$user = $this->_request->getParam("username");
		$user_id='1';

		//	$data1=GetAccessRightByUserId('25',$user_id,$conn);


		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$data1=$common_obj->GetAccessRightByUserId('25',$user_id);


			

		if(!empty($data1['view']) && $data1['view']==1 || (($authUserNamespace->user=="admin"))){

			try{
				$update_by=$authUserNamespace->user;
			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
		}
		else {

			$msg="You do not have sufficient privileges to access this area.";
			$this->view->msg=$msg;exit;
		}

		if(!empty($_POST['username']) && isset($_POST['username']))
		{

			try{

				$user=$_POST['username'];
					
					
				//$usersdetail = getUserData($conn,$user);
					


				$usersdetail=$userObj->fetchRow($userObj->select()
				->setIntegrityCheck(false)
				->from(array('user_info'),array('user_info.username'))
				->where("user_info.username= ?",$user));

				$this->view->result=$usersdetail;
					
			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}

		}

		//$this->view->result=$usersdetail;


			


	}

	public function renameAction()
	{
			
		if($this->_request->isPost())
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$userObj=new Gbc_Model_DbTable_Userinfo();
			$userlogsObj=new Gbc_Model_DbTable_Userlogs();
			$useradmin=$authUserNamespace->user;
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
			if(empty($_POST['new_username']))
			{
				//$msg="Please enter New Username";
				//$this->view->msg=$msg;
				$data=array('success'=>'','failure'=>'Please enter New Username');
				echo json_encode($data);exit;
			}
			if(!empty($_POST['new_username']))
			{
				$token=$_POST['token'];
					
		//		if($authUserNamespace->token==$token){

					$NewUsername = $_POST['new_username'];
					$user=$_POST['username'];
						
					$newuser=$userObj->fetchRow($userObj->select()
					->setIntegrityCheck(false)
					->from(array('user_info'),array('username'))
					->where("username = ?",$NewUsername));

					/*
					 for($i=0;$i<sizeof($newuser);$i++)
					 {
					 */
					if($NewUsername!=$newuser->username){
							
						/*		}*/

						//$renameUser = renameUser($user,$NewUsername);
						$common_obj = new Gbc_Model_Custom_CommonFunc();

						$renameuser=$common_obj->renameuser($user,$NewUsername);
							
						//$resultrenameuser=sizeof($renameuser);
						$description = "";
							
						if(!empty($renameuser)){

							/*if($usersdetail->email_address != $email){
							 $description .= "Useranme has been changed from  ".$user." to $NewUsername";

							 }*/
							$description .= "Useranme has been changed from  ".$user." to $NewUsername";

							if(!empty($description)){

								//saveUserLog($update_by,"renameUser",$description);


								$table_name="renameUser";
								//mysql_query("insert into user_logs(username,description,table_name) values('$username','$description','$table_name')");
								$saveLog=array("username"=>$useradmin,"description"=>$description,"table_name"=>$table_name,"created_on"=>new Zend_Db_Expr('NOW()'));
								$insertlog=$userlogsObj->insert($saveLog);

								//$msg='Username Updated Successfully';

								//$this->view->msg=$msg;


								/*$insertsaveLog=sizeof($insertlog);
								 if($insertsaveLog  && $insertsaveLog >0)
								 {
								 $msg = "success";
								 }
								 else{
								 $msg="insert into user_logs(username,description,table_name) values('$username','$description','$table_name')";
								 }*/




									
								if(!empty($insertlog))
								{
									//$msg="Username Updated Successfully";
									//$authUserNamespace->msg=$msg;
									//$this->_redirect("/Binaryuser");
									$data=array('success'=>'Username Updated Successfully','failure'=>'');
									echo json_encode($data);exit;

								}

							}




						} else {
							//$msg = 'Username failed to update';
							//$this->view->msg=$msg;
							$data=array('success'=>'','failure'=>'Username failed to update');
							echo json_encode($data);exit;
						}

					}
					else {
						//$msg = 'Username already exist';
						//$this->view->msg=$msg;
						$data=array('success'=>'','failure'=>'Username already exist');
						echo json_encode($data);exit;
							
					}

		/*		}else{
					$data=array('success'=>'','failure'=>'Invalid Request Found.');
					echo json_encode($data);exit;

				}
		*/			
					
			}

		}
			
	}
}
