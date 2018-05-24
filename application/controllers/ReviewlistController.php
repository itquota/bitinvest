<?php
class ReviewlistController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user_id=$authUserNamespace->user_id;
		$antixss = new Gbc_Model_Custom_StringLimit();
		$data1=$misc_obj->GetAccessRightByUserId('37',$user_id);
		if((!empty($data1->view) && ($data1->view==1)) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$reviewObj = new Gbc_Model_DbTable_Reviews();
			
		try{

		 $this->_helper->layout()->setLayout("admindashbord");
		 $PaginateLimit=100;
		 	
	 		
		 	$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		 		
		 	foreach($_POST as $key => $value)
		 	{
		 			
					if(isset($value) && $value != ""){
						$antixss->setEncoding($value, "UTF-8");
						if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
							$this->_redirect("/Profileerror/errormsg");

						}

					}
		 	}
		 		
			$result=$reviewObj->fetchAll($reviewObj->select()
				->setIntegrityCheck(false)
				->from(array('reviews'))
				->order("status ASC")						 

				);

			if(empty($result) || sizeof($result)<=0)
			{
				$result=array();
			}
			$this->view->reviews=$result;
		}

		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}


	}
	
	public function changestatusAction()
	{
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$reviewObj = new Gbc_Model_DbTable_Reviews();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();			

			$id=$_POST['id'];

			$status=$_POST['status'];

			 $result=$reviewObj->fetchRow($reviewObj->select()
			->setIntegrityCheck(false)
			->from(array('reviews'))
			->where("id=?",$id));	

			if(!empty($result) && sizeof($result)>0)
			{

				$upd_arr=array('status'=>$status);

				$upd_member=$reviewObj->update($upd_arr,$DB->quoteInto("id=?",$id));

				if(!empty($upd_member))
				{

					$data=array('success'=>$status,'failure'=>'');
					echo json_encode($data);exit;
				}
				else
				{
					$data=array('success'=>'','failure'=>'');
					echo  json_encode($data);exit;
				}


			}

		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}
	}	
}
