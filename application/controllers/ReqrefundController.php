<?php
class ReqrefundController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$username=$authUserNamespace->user;
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		$ref_req_obj = new Gbc_Model_DbTable_Refrequest();
		$ip_address=$misc_obj->get_client_ip();
		$kitEligibleforrefund = $comm_obj->kitEligibleforrefund($username,'Active');
		$this->_helper->layout()->setLayout("dashbord");
		$this->view->title="Gainbitcoin - Refund";
		
		/*$allRequestsResult=$ref_req_obj->fetchAll($ref_req_obj->select()
		->setIntegrityCheck(false)
		->from(array('a'=>"refund_requests"))
		->where("username = '$username'")
		);*/
		
		$allRequestsResult=$ref_req_obj->fetchAll($ref_req_obj->select()
		->setIntegrityCheck(false)
		->from(array('a'=>"refund_requests"))
		->where("username =?",$username)
		);
		
			$arr=array();
			if(isset($allRequestsResult) && sizeof($allRequestsResult)>0)
			{
				$subarr=array();
				for($i=0;$i<(sizeof($allRequestsResult));$i++) {
					$subarr = array('username'=>$allRequestsResult[$i]['username'],'full_name'=>$allRequestsResult[$i]['full_name'],'email'=>$allRequestsResult[$i]['email'],'phone'=>$allRequestsResult[$i]['phone'],'invoice_id'=>$allRequestsResult[$i]['invoice_id'],'kit_number'=>$allRequestsResult[$i]['kit_number'],'txid'=>$allRequestsResult[$i]['txid'],'reason_to_refund'=>$allRequestsResult[$i]['reason_to_refund'], 'comment'=>$allRequestsResult[$i]['comment'], 'wallet_addr'=>$allRequestsResult[$i]['wallet_addr'],'ip_address'=>$allRequestsResult[$i]['ip_address'],'status'=>$allRequestsResult[$i]['status'],'updated_by'=>$allRequestsResult[$i]['updated_by'],'ip_address2'=>$allRequestsResult[$i]['ip_address2'],'created_on'=>$allRequestsResult[$i]['created_on'],'updated_on'=>$allRequestsResult[$i]['updated_on']);
					array_push($arr,$subarr);
				}

			}
			
		$this->view->allRequests=$arr;
		$this->view->title="Gainbitcoin - Refund List";
	}
	public function refundAction()
	{
		$comm_obj = new Gbc_Model_Custom_CommonFunc();
		//$comm_obj->cleanQueryParameter(($_POST['username']));
		
		//$wadd=trim($_POST['wadd']);
		$wadd=$comm_obj->cleanQueryParameter(($_POST['wadd']));
		//$comment=trim($_POST['comment']);
		$comment=$comm_obj->cleanQueryParameter(($_POST['comment']));
		//$invoiceId=trim($_POST['invoiceId']);
		$invoiceId=$comm_obj->cleanQueryParameter(($_POST['invoiceId']));
	}
}
