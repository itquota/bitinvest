<?php
class UserinfoapiController extends Zend_Controller_Action{

	public function init(){

		//$this->_helper->layout()->disableLayout();
	}

	public function indexAction(){
		
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));
		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username=$this->_request->getParam("username");
		
		if($username != ''){

		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username=$this->_request->getParam("username");
		
		}else{
			$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
			echo json_encode($arr);
			exit;
		}

		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$userInfo = $common_obj->getUserInfo($username);
		$userInfo=$userInfo->toArray();
	
		if(isset($userInfo) && $userInfo!='' && sizeof($userInfo)>0)
		{
			$data=array('success'=>'success','failure'=>'','data'=>$userInfo);
			
			//echo "<pre>";
			//print_r($data);
			echo json_encode($data);exit;
		}
		else
		{
			$data=array('success'=>'','failure'=>'Invalid username');
			echo json_encode($data);exit;
		}

	}
}