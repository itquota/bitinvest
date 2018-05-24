<?php
class GalleryController extends Zend_Controller_Action{

	public function init()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try
		{
			
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");//dashboard

			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$this->view->title="Gainbitcoin - Gallery";
			$manage_gallery_obj= new Gbc_Model_DbTable_Gallery();	
	
			$result=$manage_gallery_obj->fetchAll($manage_gallery_obj->select()
				->setIntegrityCheck(false)
				->from(array('gallery'),array('id','title_image','title','status'))
				->where('status =?',1)									  
				);
			
			if(empty($result) || sizeof($result)<=0)
			{
				$result=array();
			}
			
			$this->view->resultmanagebanner=$result;				

		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
	
	public function detailAction(){
		$this->_helper->layout()->setLayout("dashbord");
		$db = Zend_Db_Table::getDefaultAdapter();

		$gallery_details_obj = new Gbc_Model_DbTable_GalleryDetails();
		$manage_gallery_obj= new Gbc_Model_DbTable_Gallery();	

		$url = $this->_request->getRequestUri();
		//echo $url; exit;
		$param = strstr($url,'detail');
		$param = explode('/',str_replace('detail/','',$param));
		$id = $param[0];
		$title = $param[1];
		$result=$manage_gallery_obj->fetchAll($manage_gallery_obj->select()
			->setIntegrityCheck(false)
			->from(array('m'=>'gallery'),array('m.video_links','m.title'))
			->where('m.id =?',$id)
			
			);
		$result1=$gallery_details_obj->fetchAll($gallery_details_obj->select()
			->setIntegrityCheck(false)
			->from(array('n'=>'gallery_details'),array('n.id','n.subimage','n.title'))
			->where('n.gallery_id =?',$id)
			
			);		
		if(!empty($result) || !empty($result1)){
			$result =  $result->toArray();
			$result1 =  $result1->toArray();
		}
		
		$this->view->result=$result;
		$this->view->result1=$result1;
	}

}