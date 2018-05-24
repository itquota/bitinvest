<?php
class AddnewsController extends Zend_Controller_Action{


	public function init(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - News";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('19',$user_id);
		if(!empty($data1->add)==1 && ($data1->add==1) || $authUserNamespace->user=='admin')
		{

		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		$this->_helper->layout()->setLayout("admindashbord");

		$NewsObj = new Gbc_Model_DbTable_News();

/*
		$result=array();
		$result=$NewsObj->fetchRow($NewsObj->select()
		->setIntegrityCheck(false)
		->from(array('news'),array('id'))
		->order("id DESC"));


		$result1=sizeof($result);
		if(isset($result1) && $result1>0)
		{
			$id=$result->id+1;
		}
		else
		{
			$id=1;
		}
*/
		

		if($this->_request->isPost())
		{
			$antixss = new Gbc_Model_Custom_StringLimit();
			
			foreach($_POST as $key => $value)
			{ 	
				//if($key!="headline" && $key!="informational_desc")
				//{
					if(isset($value) && $value != ""){
									
									if($antixss->setFilter($value, "black", "string") == "invalidInput"){
										
										$data=array('success'=>'','failure'=>'Invalid Input');
										echo json_encode($data);exit;
				
									}
				
								}
			//}
			}	
			
			try{
				
				$link=$_POST['link'];
				if(!empty($_POST['headline']) && $_POST['headline']!="")
				{
					$headline=$_POST['headline'];
				}
				else{
					$msg="Please enter Heade Line";
					$data=array('success'=>'','failure'=>$msg);
								echo  json_encode($data);exit;
				}
				
				$newsdetails=trim($_POST['informational_desc']);
				if(!empty($newsdetails) && $newsdetails!="")
				{
					$newsdetails=$_POST['informational_desc'];
				}else{
					$msg="Please enter Description.";
					$data=array('success'=>'','failure'=>$msg);
								echo  json_encode($data);exit;
					
				}
				if($_POST['status']!="select" || $_POST['status']==1 || $_POST['status']==0 )
				{
				$status=$_POST['status'];
				}
				else{
					$msg="Please select Status";
					$data=array('success'=>'','failure'=>$msg);
								echo  json_encode($data);exit;
				}
				$token =$_POST['token']; 
	//		if($authUserNamespace->token==$token){
				
				$data=array("headline"=>$headline,"news_details"=>$newsdetails,"link"=>$link,"status"=>$status,"updated_on"=>new Zend_Db_Expr('NOW()'));
				$insetnews=$NewsObj->insert($data);
						if(!empty($insetnews))
						{
						$arr=array('success'=>'Data Added successfully','failure'=>'');
								echo  json_encode($arr);exit;
						}
						else
						{
							$arr=array('success'=>"",'failure'=>'failure');
								echo  json_encode($arr);exit;
						}
	/*			}
			else {
						$data=array('success'=>'','failure'=>'Invalid Request Found.');
		 			echo json_encode($data);exit;
			
				}
*/
			}
			catch(Exception $e)
			{
				$this->view->msg=$e->getMessage();
			}
		}
	}
}
