<?php

class EditrainingController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){
		try {
			$this->view->title="Gainbitcoin - Training";
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$user_id=$authUserNamespace->user_id;
			$data1=$misc_obj->GetAccessRightByUserId('18',$user_id);
			if(!empty($data1->edit)&&($data1->edit==1) || $authUserNamespace->user=='admin')
			{

			}
			else
			{
				$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
				$this->_redirect("/Admindashboard");
			}
			$this->_helper->layout()->setLayout("admindashbord");

			//$id=$this->_request->getParam("id");

			$id=$_POST['uid'];
			
			$cms_obj=new Gbc_Model_DbTable_Cms();
			$cms_data=$cms_obj->fetchRow($cms_obj->select()
			->where("id= ?",$id)
			);

			if(!empty($cms_data) && sizeof($cms_data)>0)
			{
				$this->view->result=$cms_data;
			}
			else
			{
				$cms_data=array();
				$this->view->result=$cms_data;

			}


		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function editdataAction()
	{
		
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		
		  $antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
				//if($key!='informational_desc'){
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
		
		$cms_obj=new Gbc_Model_DbTable_Cms();	
					if(!empty($_POST['informational_title']) && $_POST['informational_title']!="")
					{
					$informational_title=$_POST['informational_title'];
					}
					else{
						$msg="Please enter Title";
						$data=array('success'=>'','failure'=>$msg);
 							echo json_encode($data);exit;
						
					}
					
					$informational_desc=trim($_POST['informational_desc']);
					
					if(!empty($informational_desc) && $informational_desc!="")
					{
						$informational_desc= $_POST['informational_desc'];
					}
					else{
						$msg="Please enter Description";
						$data=array('success'=>'','failure'=>$msg);
 							echo json_encode($data);exit;
					}
					
					/*if(!empty($_POST["status"]) && $_POST["status"]!="")
					{
					$status=$_POST["status"];
					}
					else{
						$msg="Please select status";
						$data=array('success'=>'','failure'=>$msg);
 							echo json_encode($data);exit;
					}*/
					
			$status=$_POST["status"];
					
			$token = $_POST["token_id"];
			
		//	if($authUserNamespace->token==$token){
			$cms_arr=array('title'=>$informational_title,'description'=>$informational_desc,'status'=>$status,'updated_on'=>$date = new Zend_Db_Expr('NOW()'));
			
			$upd_qry=$cms_obj->update($cms_arr,$DB->quoteInto("id=?",$_POST['user_id']));
			
			
			if(!empty($upd_qry))
			{
				$arr=array('success'=>'Data updated successfully','failure'=>'');
				echo  json_encode($arr);exit;
			}
			else
			{
				$arr=array('success'=>"",'failure'=>'failure');
				echo  json_encode($arr);exit;
			}
	/*	}
		else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
 			echo json_encode($data);exit;
		} */
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}

}
