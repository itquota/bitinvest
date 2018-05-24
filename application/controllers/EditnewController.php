<?php
class EditnewController extends Zend_Controller_Action{

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
		if(!empty($data1->edit) && ($data1->edit==1) || $authUserNamespace->user=='admin')
		{

		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$NewsObj = new Gbc_Model_DbTable_News();
		
			$this->_helper->layout()->setLayout("admindashbord");

			$id=$_POST['newsid'];
			
			//$id=$this->_request->getParam("id");
			$result=$NewsObj->fetchRow($NewsObj->select()
			->where("id= ?",$id));
			
			if(!empty($result) && sizeof($result)>0)
				{
				$this->view->result=$result;
				}
			else
				{
				$result=array();
				$this->view->result=$result;
				}
				
			/*if($this->_request->isPost())
			{
			 	$update_on=new Zend_Db_Expr('NOW()');
			 	$headline=$_POST['headline'];
			 	$newsdetails=$_POST['informational_desc'];
			 	$link=$_POST['link'];
			 	 
			 	 
			 	$updata=array("headline"=>$headline,"news_details"=>$newsdetails,"link"=>$link,"updated_on"=>$update_on);
			 	$updated=$NewsObj->update($updata,"id='".$_POST['user_id']."'");
			
			
	if(!empty($updated))
			{
				$arr=array('success'=>'success','failure'=>'');
					echo  json_encode($arr);exit;
			}
			else
			{
				$arr=array('success'=>"",'failure'=>'failure');
					echo  json_encode($arr);exit;
			}
			}*/
	}
	
	public function newseditAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$NewsObj = new Gbc_Model_DbTable_News();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
			
		 $antixss = new Gbc_Model_Custom_StringLimit();
		 
			foreach($_POST as $key => $value)
			{
				//if($key!='headline' && $key!='informational_desc'){
				if(isset($value) && $value != ""){
								
								if($antixss->setFilter($value, "black", "string") == "invalidInput"){
									
									$data=array('success'=>'','failure'=>'Invalid Input');
									echo json_encode($data);exit;
			
								}
			
							}
				//}			
			}	
		
			$update_on=date('Y-m-d h:i:s');
				$link=$_POST['link'];
				if(!empty($_POST['headline']) && $_POST['headline']!="")
				{
					$headline=$_POST['headline'];
				}
				else{
					$msg="Please enter Heade Line";
						$data=array('success'=>'','failure'=>$msg);
 							echo json_encode($data);exit;
				}
				
				$newsdetails=trim($_POST['informational_desc']);
				if(!empty($newsdetails) && $newsdetails!="")
				{
					$newsdetails=$_POST['informational_desc'];
				}else{
					$msg="Please enter Description.";
					$data=array('success'=>'','failure'=>$msg);
 							echo json_encode($data);exit;
					
				}
				//if(!empty($_POST['status']) && $_POST['status']!=""){
				//}
				///else{
			//	$msg="Please select status.";
				//	$data=array('success'=>'','failure'=>$msg);
 					//		echo json_encode($data);exit;
				//}
				$status=$_POST['status'];
				
				
			 	$tokn=$_POST['tokn'];
		//	 	if($authUserNamespace->token==$tokn){
			$updata=array("headline"=>$headline,"news_details"=>$newsdetails,"link"=>$link,"updated_on"=>$update_on,'status'=>$status);
			$updated=$NewsObj->update($updata,$DB->quoteInto("id=?",$_POST['user_id']));
			
			if(!empty($updated))
			{
				$arr=array('success'=>'Data updated successfully','failure'=>'');
					echo  json_encode($arr);exit;
			}
			else
			{
				$arr=array('success'=>"",'failure'=>'failure');
					echo  json_encode($arr);exit;
			}
			
			
/*			
			 	}else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
 			echo json_encode($data);exit;
		} */
	}
}
