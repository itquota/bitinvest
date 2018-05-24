<?php
class ContractoutputapiController extends Zend_Controller_Action{

	public function init(){

	}
	public function indexAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			//$username=trim($_REQUEST['username']);
			$username = $this->_request->getParam("username");
			//$invoiceId=trim($_REQUEST['invoiceId']);
			$invoiceId = $this->_request->getParam("invoiceId");
			$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
			$pastRecords=$Gbc_Model_Custom_func_obj->getEarningsFor($username, "", $invoiceId);


			$subarr=array();
			if(isset($pastRecords) && sizeof($pastRecords)>0)
			{
				for($i=0;$i<sizeof($pastRecords);$i++)
				{
					$arr=array('username'=>$pastRecords[$i]['username'],'pool_fees'=>$pastRecords[$i]['pool_fees'],'total_amt'=>$pastRecords[$i]['total_amt'],'net_amt'=>$pastRecords[$i]['net_amt'],'coin'=>$pastRecords[$i]['coin'],'contract_id'=>$pastRecords[$i]['contract_id'],'invoice_id'=>$pastRecords[$i]['invoice_id']);
					array_push($subarr,$arr);
				}
			}
			$db->commit();
			$data=array('success'=>'success','failure'=>'','data'=>$subarr);
			echo json_encode($data);exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$data=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($data);exit;
		}
	}

}