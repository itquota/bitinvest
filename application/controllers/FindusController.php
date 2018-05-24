<?php
class  FindusController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Find Us";
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('20',$user_id);
		if(!empty($data1->view)&&($data1->view==1) || $authUserNamespace->user=='admin')
		{
		
		}
		else 
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}
		
		$findusObj  = new Gbc_Model_DbTable_Findus();
		$cityObj= new Gbc_Model_DbTable_City();
		
		try {


			$this->_helper->layout()->setLayout("admindashbord");//dashboard

			$result=array();
				
			$result=$findusObj->fetchAll($findusObj->select()
			->setIntegrityCheck(false)
			->from(array('f'=>'find_us'),array('f.id','f.contact_name','f.contact_address','f.mobile','f.created_on','f.status'))
			->joinInner(array("c"=>'city'),'(f.city=c.city_id)',array('c.city_name'))
			->order("id desc"));
		/*echo "<pre>";
		print_r($result);exit;*/

	 	$this->view->result=$result;
	 /*	echo "<pre>";
	 	print_r($result);exit;*/
	 		
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function deleteusAction()
	{
		$findusObj  = new Gbc_Model_DbTable_Findus();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();

		
		if(!empty($_POST['id']))
		{
			$id=$_POST['id'];
			$del=$findusObj->delete($DB->quoteInto("id =?",$id));
			
			
			if(!empty($del))
			{
				echo "Deleted successfully";exit;
			}
			else
			{
				echo "Failed to delete! Please try again";exit;
			}
		}
		else
		{
			echo "Failed to delete! Please try again";exit;
		}
	}

	public function updateusAction()
	{
		$findusObj  = new Gbc_Model_DbTable_Findus();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		if(!empty($_POST['id']))
		{
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
			
			
			
			$id=$_POST['id'];
			$status=$_POST['status'];
			$tokn=$_POST['tokn'];
	//		if($authUserNamespace->token==$tokn){	
			if($status==0)
			{
				$desired=1;
			}
			else
			{
				$desired=0;
			}
			$arr=array('status'=>$desired,'updated_on'=> new Zend_Db_Expr('NOW()'));
			$upd=$findusObj->update($arr,$DB->quoteInto("id=?",$id));
			
			if(!empty($upd))
			{
				echo "Updated successfully";exit;
			}
			else
			{
				echo "Failed to update! Please try again";exit;
			}
			
	/*	}
		else{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
				 echo json_encode($data);exit;
		}
	*/	
		}
		else
		{
			echo "Failed to delete! Please try again";exit;
		}
	}
}
