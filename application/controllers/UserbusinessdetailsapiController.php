<?php
class UserbusinessdetailsapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));
		
		$username=$this->_request->getParam("username");
		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$startdate=$this->_request->getParam("startdate");
		//$startdate =$common_obj->cleanQueryParameter(($_REQUEST["startdate"]));
		$enddate=$this->_request->getParam("enddate");
		//$enddate = $common_obj->cleanQueryParameter(($_REQUEST["enddate"]));
		
		$userDetails=$common_obj->userBusinessDetails($username,$startdate,$enddate);
		if(empty($userDetails)|| !isset($userDetails))
		{
			$data=array('success'=>'','failure'=>"There is some issue. Please try again later.");
			echo json_encode($data);exit;
		}
		else
		{
			$data=array('success'=>'success','failure'=>'','data'=>$userDetails);
			echo json_encode($data);exit;
		}


	}

}