<?php
class ManagegalleryController extends Zend_Controller_Action{
	
	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{
		$this->view->title="Gainbitcoin - Manage Gallery";
		$this->_helper->layout()->setLayout("admindashbord");
		$manage_gallery_obj= new Gbc_Model_DbTable_Gallery();
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
		$result=$manage_gallery_obj->fetchAll($manage_gallery_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery'),array('id','title_image','title','status'))
			
			);
			
		if(empty($result) || sizeof($result)<=0)
		{
			$result=array();
		}
		$this->view->resultmanagebanner=$result;

	}
	
	public function changestatusAction(){
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$managegallery_obj=new Gbc_Model_DbTable_Gallery();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();			

			$id=$_POST['memberid'];

			$status=$_POST['memberstatus'];

			if($status=='1')
			{
				$status=0;
			}
			else{

				$status=1;
			}
	
			 $result=$managegallery_obj->fetchRow($managegallery_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery'))
			->where("id=?",$id));	

			if(!empty($result) && sizeof($result)>0)
			{

				$upd_arr=array('status'=>$status);

				$upd_member=$managegallery_obj->update($upd_arr,$DB->quoteInto("id=?",$id));

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
	
	public function deletegalleryAction(){
		try
		{
		
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$gallery_details_obj=new Gbc_Model_DbTable_GalleryDetails();
			$managegallery_obj=new Gbc_Model_DbTable_Gallery();
			
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();			

			$id=$_POST['id'];

			 $result=$managegallery_obj->fetchRow($managegallery_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery'))
			->where("id=?",$id));	
			
			 $result1=$gallery_details_obj->fetchRow($gallery_details_obj->select()
			->setIntegrityCheck(false)
			->from(array('gallery_details'))
			->where("gallery_id=?",$id));	

			if((!empty($result) && sizeof($result)>0) || (!empty($result1) && sizeof($result1)>0) )
			{
				$delete=$db->delete('gallery', array('id = ?' => $id));
				$delete1=$db->delete('gallery_details', array('gallery_id = ?' => $id));


				if(!empty($delete) || !empty($delete1))
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
