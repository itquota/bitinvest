<?php
class QuerytypeassignController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");
			
	}

	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Assign Query Type";

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous();
		$user_id=$authUserNamespace->user_id;
		$data1=$misc_obj->GetAccessRightByUserId('29',$user_id);
		if((!empty($data1->view) && ($data1->view)==1) || $authUserNamespace->user=='admin')
		{

		}
		else
		{
			$authUserNamespace->msg="You do not have sufficient privileges to access this area.";
			$this->_redirect("/Admindashboard");
		}

		$changepermissionObj = new  Gbc_Model_DbTable_SpecialPermission();

		try {

				$db = Zend_Db_Table::getDefaultAdapter();
				$db->beginTransaction();

				$this->_helper->layout()->setLayout("admindashbord");//dashboard

				$subadmin_obj=new Gbc_Model_DbTable_Subadminuser();
				$assigned_obj = new Gbc_Model_DbTable_Assignedquery();

				$subadmin_data=$subadmin_obj->fetchAll($subadmin_obj->select()
				->setIntegrityCheck(false)
				->from(array('sub_admin_users'))
				->where("status=?",'1'));

				$subadmin_type=array('subadmin_data'=>$subadmin_data);
				$this->view->subadmin_type=$subadmin_type;

			
				$assigned_qry=$assigned_obj->fetchAll($assigned_obj->select()
				->setIntegrityCheck(false)
				->from(array('assigned_queries_users'))
				->where("query_type>?",'0')
				->where("status=?",'1'));

				$assigned_data=array('assigned_data'=>$assigned_qry);
				$this->view->assigned_type=$assigned_data;
				//echo "<pre>";
				//print_r($assigned_data);
			
				$lov_obj=new Gbc_Model_DbTable_Lov();
				$lovcategory_data=$lov_obj->fetchAll($lov_obj->select()
				->setIntegrityCheck(false)
				->from(array('lov'))
				->where("name=?",'sr_category')
				->where("status=?",'1'));

				$user_cat=array('lovcategory_data'=>$lovcategory_data);
				$this->view->user_cat=$user_cat;			

			//$result1=sizeof($result);

			}
			catch(Exception $e)
			{
				//$db->rollBack();
				echo $e->getMessage();exit;
			}


		}
		public function updatepermissionAction()
		{
		 try
		 {

			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$assigned_qry = new Gbc_Model_DbTable_Assignedquery();

			if($this->_request->isPost())
			{
			$subadmin = $_POST['subadmin'];	
			$category = explode(',',$_POST['category']);
			//echo $category;
			foreach ($category as $selectedOption){

					if($selectedOption == 'Kit Activation')
					{
						$category_code = '1';
					}else if($selectedOption == 'Payout Calculations')
					{
						$category_code = '2';
					}else if($selectedOption == 'Profile Changes')
					{
						$category_code = '3';
					}else if($selectedOption == 'Others')
					{
						$category_code = '4';
					}	
					//	echo $category_code."\n";


					$assignedUsersData =$assigned_qry->fetchAll($assigned_qry->select()
					->from(array('a' =>'assigned_queries_users'),array('total_queries','status'))
					->where("status = ?",'1')
					->where("username = ?",$subadmin)
					->where("query_type = ?",$category_code));		
					if(!empty($assignedUsersData) && sizeof($assignedUsersData)>0)
					{

					}else{
							$insert_data=array('username'=>$subadmin,'total_queries'=>'0','status'=>'1','query_type'=>$category_code,'created_on'=>new Zend_Db_Expr('NOW()'));
							$insert_qry=$assigned_qry->insert($insert_data);
					}	

					if($insert_qry){
						$data=array('success'=>'Query type assigned successfully','failure'=>'');
						echo json_encode($data);exit;
					}

			}


			}
				
	  }
			catch(Exception $e)
		{
			//$db->rollBack();
			echo $e->getMessage();exit;
		}	
	}

	public function changestatusAction()
	{

		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$assigned_qry = new Gbc_Model_DbTable_Assignedquery();
			$db = Zend_Db_Table::getDefaultAdapter();
			$username=$_POST['username'];
			$query_type=$_POST['query_type'];
	//		$db->getProfiler()->setEnabled(true);

			$result=$assigned_qry->fetchAll($assigned_qry->select()
			->setIntegrityCheck(false)
			->from(array('assigned_queries_users'))
			->where("username=?",$username)
			->where($db->quoteInto("query_type =?",$query_type))
			->where($db->quoteInto("status =?",1))
			);
			
	//		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	//		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			
			if(!empty($result) && sizeof($result)>0)
			{
				$update_arr=array('status'=>'0','updated_on'=>new Zend_Db_Expr('NOW()'));
				$where = array();
				$where[] = $db->quoteInto('username = ?', $username);
				$where[] = $db->quoteInto('status = ?',1);
				$where[] = $db->quoteInto('query_type = ?',$query_type);
				$update_data=$assigned_qry->update($update_arr,$where);

				if(!empty($update_data))
				{

					$data=array('success'=>'Query Type De-assigned successfully !','failure'=>'');
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
	
	
		public function resetcounterAction()
		{
		 try
		 {

			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$misc_obj=new Gbc_Model_Custom_Miscellaneous();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$assigned_qry = new Gbc_Model_DbTable_Assignedquery();

			if($this->_request->isPost())
			{
			$category = $_POST['category'];	
		//	echo $category; exit; 
	//		foreach ($category as $selectedOption){

					if($category == 'Kit Activation')
					{
						$category_code = '1';
					}else if($category == 'Payout Calculations')
					{
						$category_code = '2';
					}else if($category == 'Profile Changes')
					{
						$category_code = '3';
					}else if($category == 'Others')
					{
						$category_code = '4';
					}	
					$assignedUsersData =$assigned_qry->fetchAll($assigned_qry->select()
					->from(array('a' =>'assigned_queries_users'),array('total_queries','status'))
					->where("status = ?",'1')
					->where("query_type = ?",$category_code));		

					if(!empty($assignedUsersData) && sizeof($assignedUsersData)>0)
					{
						$update_arr=array('total_queries'=>'0','updated_on'=>new Zend_Db_Expr('NOW()'));
						$where = array();
						$where[] = $db->quoteInto('status = ?',1);
						$where[] = $db->quoteInto('query_type = ?',$category_code);
						$update_data=$assigned_qry->update($update_arr,$where);
						

						if(!empty($update_data))
						{

							$data=array('success'=>'Query Counter resetted successfully !','failure'=>'');
							echo json_encode($data);exit;
						}
						else
						{
							$data=array('success'=>'','failure'=>'');
							echo  json_encode($data);exit;
						}
						

					}

		//	}


			}
				
	  }
			catch(Exception $e)
		{
			//$db->rollBack();
			echo $e->getMessage();exit;
		}	
	}
	
}

