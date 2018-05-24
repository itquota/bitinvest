<?php
class RefundcontractsController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$invoices_obj = new Gbc_Model_DbTable_Invoices();
		$this->_helper->layout()->setLayout("admindashbord");//dashboard
		$username=$authUserNamespace->user;
		$enddate=date('Y-m-d')." 23:59:59";
		$startdate= date('Y-m-d', strtotime('-30 days'))." 00:00:00";


		$inv_detail = $invoices_obj->fetchAll($invoices_obj->select()
		//->where("1=1 AND invoice_status=1  AND username = '" . ($username) . "' AND created_on IS NOT NULL and created_on <> '0000-00-00 00:00:00' AND created_on BETWEEN '".$startdate."' AND '".$enddate."'")
	->where("1=1")
	->where("invoice_status=?",1)
	->where("username = ?",$username)
	->where("created_on IS NOT NULL")
	->where("created_on <> ?",'0000-00-00 00:00:00')
	->where("created_on BETWEEN '".$startdate."' AND '".$enddate."'")
	->order("invoice_id DESC")
		);

		if(empty($inv_detail) && sizeof($inv_detail)<=0)
		{
			$inv_detail=array();
		}
		$this->view->result=$inv_detail;

		//print_r($inv_detail);exit;

	}
	public function refundAction()
	{
		$wadd=trim($_POST['wadd']);
		$comment=trim($_POST['comment']);
		$invoiceId=trim($_POST['invoiceId']);
	}
}
