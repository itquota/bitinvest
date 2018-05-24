<?php
class EditsubadminController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{

		try{
			$this->view->title="Gainbitcoin - Subadmin";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
			{
				//$loggedIn==true;
			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Login");
			}
			$subadmin_Obj = new Gbc_Model_DbTable_Subadminuser();
			$misc_obj=new Gbc_Model_Custom_Miscellaneous;
			
			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			/*if(!empty($_POST['userid']) && $_POST['userid']!='')
			{
				$id=$_POST['userid'];
				
			}
			else
			{
				$id=$_GET['id'];
			}*/

			//$id=$this->_request->getParam("id");
			
			$id=$_POST['edit_id'];
			
			$result=$subadmin_Obj->fetchRow($subadmin_Obj->select()
			->setIntegrityCheck(false)
			->from(array('sub_admin_users'))
			->where("id= ?",$id)
			);
			
			$this->view->result=$result;

						/*else
			{

				$this->_helper->layout()->setLayout("admindashbord");//dashboard

				$result=array();

				$id=$_GET['id'];

				$result=$subadmin_Obj->fetchRow($subadmin_Obj->select()
				->setIntegrityCheck(false)
				->from(array('sub_admin_users'))
				->where("id= ?",$id)
				);

				$this->view->msg='';
				$this->view->result=$result;

				//echo "<pre>";
				//print_r($result);exit;

			}*/

		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}







	}
	
public function updatedataAction()
{
	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	$subadmin_Obj = new Gbc_Model_DbTable_Subadminuser();
	$misc_obj=new Gbc_Model_Custom_Miscellaneous;
	$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	
				
	$result=$subadmin_Obj->fetchRow($subadmin_Obj->select()
			->setIntegrityCheck(false)
			->from(array('sub_admin_users'))
			->where("id= ?",$_POST['userid'])
			);
	
	
	if($this->_request->isPost())
			{
				$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				//if($key!="firstname" && $key!="lastname")
				//{
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

					}
				
				//}
			}
			
				
				$userid=$_POST['userid'];
					

				try{
					if(!empty($_POST['firstname']) && $_POST['firstname']!="")
					{
					$name=$_POST['firstname'];
					}
					else{
						$data=array('success'=>'','failure'=>'Please enter First Name');
						echo json_encode($data);exit;
					}
					
					if(!empty($_POST['lastname']) && $_POST['lastname']!="")
					{
					$lastname=$_POST['lastname'];
						
					}
					else{
						$data=array('success'=>'','failure'=>'Please enter Last Name');
						echo json_encode($data);exit;
					}
					if(!empty($_POST['password']) && $_POST['password']!="")
					{
					$password=$_POST['password'];
					}
					else{
						$data=array('success'=>'','failure'=>'Please enter Password.');
						echo json_encode($data);exit;
					}
					
					if(!empty($_POST['email']) && $_POST['email']!="")
					{
					$email=$_POST['email'];
					}
					else {
						$data=array('success'=>'','failure'=>'Please enter Email');
						echo json_encode($data);exit;
					}
					
					if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
						
            			 $msg="Please provide valid email.";
							$data=array('success'=>'','failure'=>$msg);
							echo json_encode($data);exit;
           			 } 
				
					
					if(!empty($_POST['contact_no']) && $_POST['contact_no']!="")
					{
					$contact_no=$_POST['contact_no'];
					}
					else{
						$data=array('success'=>'','failure'=>'Please enter Contact No');
						echo json_encode($data);exit;
					}
					
					if (preg_match('/^[0-9]{10}+$/',$_POST['contact_no'])) {
						
	 				 //echo 'valid';
	 				 
					} else {
						
   					$data=array('success'=>'','failure'=>'Please enter 10 digit Mobile No');
						echo json_encode($data);exit;
					}
					
					/*if(!empty($_POST['status']) &&  $_POST['status']!="")
					{
				
					
					}
					else{
					$data=array('success'=>'','failure'=>'Please select status');
						echo json_encode($data);exit;
					}*/
					 $token=$_POST['token'];
				 
					$status=$_POST['status'];
		//		 if($authUserNamespace->token==$token){
				 		
				 	if($_POST['password']==$result->password)
				 	{

				 		$password=$result->password;
				 		$salt = $result->salt;
				 	}
				 	else{

				 		$salt = $result->salt;
				 		$password = $misc_obj->encryptPassword(($_POST['password']), $salt);
				 	}
				 	$checkmail=$subadmin_Obj->fetchRow($subadmin_Obj->select()
				 	->setIntegrityCheck(false)
				 	->from(array('sub_admin_users'))
				 	->where("email=?",$email)
				 	->where("id!=?",$userid)
				 	);
				
				 	if(empty($checkmail) && sizeof($checkmail)<=0)
				 	{
							//echo "if";exit;
				 		$update=date('Y-m-d h:i:s');

				 		$data=array("first_name"=>$name,"last_name"=>$lastname,"email"=>$email,"mob"=>$contact_no,"status"=>$status,"updated_on"=>$update,"password"=>$password,"salt"=>$salt);
				 		$upd=$subadmin_Obj->update($data,$DB->quoteInto("id=?",$_POST['userid']));

				 		if(!empty($upd)|| $upd==0) {
				 			//die('Zero rows affected');
				 			//$message='Subadmin Updated Successfully.';
				 			$arr=array('success'=>'Data updated successfully','failure'=>'');
				 			echo  json_encode($arr);exit;
				 			 

				 		}
				 		else
				 		{
				 			//$message='Subadmin not Updated. Please try again.';
				 			$arr=array('success'=>'','failure'=>'failure');
				 			echo  json_encode($arr);exit;
				 			 
				 		}
				 	}
				 	else{
				 		$arr=array('success'=>'','failure'=>'This Email already exist');
				 		echo  json_encode($arr);exit;
				 		///echo "email already exist";exit;
				 	}
	/*			}else{
					$data=array('success'=>'','failure'=>'Invalid Request Found.');
				echo json_encode($data);exit;
				 } */
				 //$this->view->msg=$message;
				}
				catch(Exception $e)
				{
					echo $e->getMessage();exit;
				}

			}
	
}	
	
	
	
	

	
public function editAction()
	{

		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			if($this->_request->isPost())
			{

				$uid=$_POST["id"];
				$Firstname=$_POST["first_name"];
				$Email=$_POST["email"];

				$Mobile=$_POST["mob"];
				$Createdon=$_POST["created_on"];


				if(isset ($uid) && ($uid!=null))
				{
					$data=array("first_name"=>$Firstname,"email"=>$Email,"mob"=>$Mobile,"created_on"=>$Createdon);
					$userObj->update($data,"id='$uid'");
					//$authUserNamespace->messageset="Updated successfully";
					///$this->_redirect('/admin/persondetail');

					if (empty($userObj)) {
						//die('Zero rows affected');
						$message='Zero rows affected';
						$this->view->message=$message;
					}
					else{
						$message="Updated successfully";
						$this->view->message=$message;exit;
					}

				}
			}




		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function setpermissionAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("admindashbord");//
			if($this->_request->isPost())
			{
				$uid=$_POST["id"];
				if(isset ($uid) && ($uid!=null))
				{
					$data=array("first_name"=>$Firstname,"email"=>$Email,"mob"=>$Mobile,"created_on"=>$Createdon);
					$userObj->update($data,"id='$uid'");
				}


				if (empty($userObj)) {
					//die('Zero rows affected');
					$message='Zero rows affected';
					$this->view->message=$message;
				}
				else{
					$message="Updated successfully";
					$this->view->message=$message;exit;
				}
			}
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

}
