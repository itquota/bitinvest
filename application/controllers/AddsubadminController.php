<?php
class AddsubadminController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{

			$this->_helper->layout()->setLayout("admindashbord");//dashboard
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->view->title="Gainbitcoin - Subadmin";
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
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			
			
			
			
		
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
				
				
						if(!empty($_POST['firstname']) && $_POST['firstname']!="")
						{
						$Firstname=$_POST['firstname'];
						}
						else {
							$data=array('success'=>'','failure'=>'Please enter First Name.');
				     		echo json_encode($data);exit;
						}
						
						if(!empty($_POST['lastname']) && $_POST['lastname']!="" )
						{
						$lastname=$_POST['lastname'];
						}
						else{
							$data=array('success'=>'','failure'=>'Please enter Last Name..');
				     		echo json_encode($data);exit;
						}
						
					//$password=$_POST['password'];
						if(!empty($_POST['password']) && $_POST['password']!="")
						{
						$psd=trim($_POST['password']);
						}
						else{
							$data=array('success'=>'','failure'=>'Please enter Password...');
				     		echo json_encode($data);exit;
						}
						
						if(!empty($_POST['email']) && $_POST['email']!="")
						{
						$email=$_POST['email'];
						}
						else {
							$data=array('success'=>'','failure'=>'Please enter Email.');
				     		echo json_encode($data);exit;
						}
						
						
						if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
						
            			 $msg="Please enter valid Email Id.";
							$data=array('success'=>'','failure'=>$msg);
							echo json_encode($data);exit;
           				 } 
						
           				 if(!empty($_POST['contact_no']) && $_POST['contact_no']!="")
           				 {
						$contact_no=$_POST['contact_no'];
           				 }
           				 else{
           				 	 $msg="Please enter Contact No..";
							$data=array('success'=>'','failure'=>$msg);
							echo json_encode($data);exit;
           				 }
           				 
						if (preg_match('/^[0-9]{10}+$/',$_POST['contact_no'])) {
						
	 				 //echo 'valid';
	 				 
						} else {
						
   						$data=array('success'=>'','failure'=>'Please enter 10 digit Mobile No');
						echo json_encode($data);exit;
						}
						
           				 if($_POST['status']!="select" || $_POST['status']==1 || $_POST['status']==0)
           				 {
							$status=$_POST['status'];
           				 }
           				 else {
           				 $data=array('success'=>'','failure'=>'Please select status');
							echo json_encode($data);exit;
           				 }
					
					$salt =$misc_obj->generateSalt();
            		$password=$misc_obj->encryptPassword($psd,$salt);
					$date=new Zend_Db_Expr('NOW()');
            		
            		  $token=$_POST['token'];
            	
		 	   // if($authUserNamespace->token==$token){
					$checkmail=$subadmin_Obj->fetchRow($subadmin_Obj->select()
				 ->setIntegrityCheck(false)
				 ->from(array('sub_admin_users'))
				 ->where("email= ?",$email)
				 );
				 
				 if(empty($checkmail) && sizeof($checkmail)<=0)
				 {
            		 $data=array("first_name"=>$Firstname,"last_name"=>$lastname,"password"=>$password,"salt"=>$salt,"email"=>$email,"mob"=>$contact_no,"created_on"=>$date,"status"=>$status);
					$insertdata=$subadmin_Obj->insert($data);
					//echo"in";exit;
					if(!empty($insertdata))
					{
					$arr=array('success'=>'Data added successfully','failure'=>'');
					echo  json_encode($arr);exit;
					}
					else {
						$arr=array('success'=>'','failure'=>'failure');
						echo  json_encode($arr);exit;
					}
				 }
				 else{
				 	$arr=array('success'=>'','failure'=>'This Email already exist');
						echo  json_encode($arr);exit;
				 	///echo "email already exist";exit;
				 }
 					//$this->view->msg=$msg;
			/*	}else{
		 			$data=array('success'=>'','failure'=>'Invalid Request Found.');
				     echo json_encode($data);exit;
		 		}
		 	  */  	
			}
			}

}
