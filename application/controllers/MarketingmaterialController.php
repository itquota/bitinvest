<?php
class MarketingmaterialController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		
		
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$banner_obj=new Gbc_Model_DbTable_Managebanner();
		$binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
		$username=$authUserNamespace->user;
		$result=array();
		$url= BASE."/Userinfoapi?username=".$username;

		$result=$common_obj->call_curl($url);
		$userInfo=(array)json_decode($result,true);
		$this->view->userInfo=$userInfo;

		$banner_list=$banner_obj->fetchAll($banner_obj->select()
		->setIntegrityCheck(false)
		->from(array('manage_banner'))
		->where("banner_image IS NOT NULL")
		->where("status=?",'1'));
		//->limit("4"));

		if(empty($banner_list) || sizeof($banner_list)<=0)
		{
			$banner_list=array();
		}
		$this->view->banner_list=$banner_list;
		
		$checkleg = $binary_usr_ref->fetchAll($binary_usr_ref->select()
		->setIntegrityCheck(false)
		->from(array('binary_user_refences'))		
		->where("parent_username=?",$username));
		
		$totalchild = sizeof($checkleg);
		$this->view->totalchild=$totalchild;

	}
	public function appshare()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj=new Gbc_Model_Custom_CommonFunc();	
		$result=array();
		$url= BASE."/Userinfoapi?username=".$username;
		
		$username=$authUserNamespace->user;
		$result=$common_obj->call_curl($url);
		$userInfo=(array)json_decode($result,true);
		$this->view->userInfo=$userInfo;

	}
	
	public function saveimageAction()
	{
		
		$banner_obj=new Gbc_Model_DbTable_Managebanner();
		$id=$_POST['id'];
		//echo $id;exit;
		
		$imageContent=$banner_obj->fetchRow($banner_obj->select()
		->setIntegrityCheck(false)
		->from(array('manage_banner'),array('banner_image'))
		->where("id=?",$id));
		
			
	$image_content=$imageContent->banner_image;
		//int_r($imageContent);exit;
		                           	
		$datatemp = file_get_contents($temp);
		$tdate = strtotime(date("Y-m-d H:i:s"));
	 	$new =BASEPATH.'images/banner image/'.$tdate.'.jpg';
		$domain_path = BASE.'/images/banner image/'.$tdate.'.jpg';
	     // $domain_path   =$imagename.'_'.$tdate.'.jpg';
		file_put_contents($new, $image_content);
		echo $domain_path;exit;
		
		
	}

}
