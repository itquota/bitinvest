<?php
class AddnewController extends Zend_Controller_Action{

	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Find Us";
		$this->_helper->layout()->setLayout("admindashbord");
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('20',$user_id);
		
		if(!empty($data1->add) && ($data1->add==1) || (($authUserNamespace->user)=='admin'))
		{

		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$findusObj= new Gbc_Model_DbTable_Findus();
		$cityObj= new Gbc_Model_DbTable_City();
		
		$findusresult=$findusObj->fetchAll($findusObj->select()
		->setIntegrityCheck(false)
		->from(array('find_us'))
		);
	
		$this->view->findusresult=$findusresult;
		
		/*$result=$findusObj->fetchRow($findusObj->select()
		->setIntegrityCheck(false)
		->from(array('find_us'),array('id'))
		);

			
		//$this->view->result=$result;
		$result1=sizeof($result);
		if($result1 && $result1>0)
		{
			$id=$result->id+1;
		}
		else{
			$id='1';
		}*/

		if($this->_request->isPost())
		{
			$antixss = new Gbc_Model_Custom_StringLimit();
			
			foreach($_POST as $key => $value)
			{
				//if($key!='contact_name' && $key!='address')	{
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
	
							$data=array('success'=>'','failure'=>'Invalid Input.');
							echo json_encode($data);exit;
	
						}
	
					}
			//}
			}	
			
			try {
				
				$adddata=array();
				if($_POST['contact_name']!="" && $_POST['address']!="" && $_POST['mobile']!="" && $_POST['city']!="" && ($_POST['status']==1 || $_POST['status']==0))
				{
				$token=$_POST['token'];
		//	if($authUserNamespace->token==$token){
				
				$adddata=array("contact_name"=>$_POST['contact_name'],"contact_address"=>$_POST['address'],"mobile"=>$_POST['mobile'],"city"=>$_POST['city'],"status"=>$_POST['status'],"updated_on"=>new Zend_Db_Expr('NOW()'));
				$insertdata=$findusObj->insert($adddata);
				if(!empty($insertdata))
				{
					$msg="Added successfully";
					$authUserNamespace->msg=$msg;
					$this->_redirect("/Findus");
				}
				else{
					$errormsg="Failed to add! Please try again";
					$authUserNamespace->errormsg=$errormsg;
					
					
				}
				//$this->view->msg=$msg;
				
		/*	}
			else {
				//$data=array('success'=>'','failure'=>'Invalid Request Found.');
				// echo json_encode($data);exit;
				$errormsg="Invalid Request Found";
				$authUserNamespace->errormsg=$errormsg;
					
			}
			*/
			}else{
			
				$errormsg="All fields are required";
				$authUserNamespace->errormsg=$errormsg;
			}
			}
			catch(Exception $e)
			{
			echo $e->getMessage();exit;
				$this->view->msg=$e->getMessage();
			}
		}
		
		$cityresult=$cityObj->fetchAll($cityObj->select()
		->setIntegrityCheck(false)
		->from(array('city'))
		);

			
			
		$this->view->cityresult=$cityresult;

	}

}
