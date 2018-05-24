SKA PAV
<?php
class BusinesscalapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$username=$_REQUEST['username'];
			$username = $this->_request->getParam("username");
			//$startdate =$_REQUEST["startdate"];
			$startdate = $this->_request->getParam("startdate");
			//$enddate = $_REQUEST["enddate"];
			$enddate = $this->_request->getParam("enddate");
			$userDetails=$common_obj->userBusinessDetails($username,$startdate,$enddate);
			if(empty($userDetails)|| !isset($userDetails))
			{
				$db->rollBack();
				$data=array('success'=>'','failure'=>"Their is some issue. Please try again later.");
				echo json_encode($data);exit;
			}
			else
			{
				$db->commit();
				$data=array('success'=>'success','failure'=>'','data'=>$userDetails);
				echo json_encode($data);exit;
			}
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}


	}

}