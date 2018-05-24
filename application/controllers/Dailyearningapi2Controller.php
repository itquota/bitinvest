<?php

class Dailyearningapi2Controller extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction()
	{
		//$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		try {
			
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_REQUEST['username']));
			
			$this->getResponse()->setHeader('Content-Type', 'application/json');
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			/*if(empty($_REQUEST['username']) || $_REQUEST['username']=='')
			{
				$arr=array('success'=>'','failure'=>'Please provide username');exit;
				echo json_encode($arr);exit;
			}
			$username=$_REQUEST['username'];*/
		//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
		$username = $this->_request->getParam("username");;
		
		 if($username != ''){

		 	$username=$username;
		 	
		 }else{
		 	$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
		 	echo json_encode($arr);
		 	exit;
		 }
			$dailyearningobj = new Gbc_Model_DbTable_Gb2Invoices();
			$result=$dailyearningobj->fetchAll($dailyearningobj->select()
			->setIntegrityCheck(false)
			->from(array('i'=>'gb2_invoices'), array('i.invoice_id','i.created_on','i.contract_qty', 'i.contract_rate','i.amtPaid', 'i.invoice_status'))
			//->where("invoice_status!=2 AND username = '" . trim($username) . "' AND contract_type = 'SHA'"));
			->where("invoice_status!= ?",'2')
			->where("username= ?",$username)
			->where("contract_type in('gb2')")
			->order("kit_created DESC")
			);
			
	//	print_r($result);
			$address=array();
			$result1=sizeOf($result);
			if(isset($result1) && $result1>0)
			{
				foreach($result as $result)
				{
					$address[]=array('Date/Time'=>$result->created_on,'InvoiceId'=>$result->invoice_id,'Qty'=>$result->contract_qty,'Rate'=>$result->contract_rate,'PaidAmount'=>$result->amtPaid,'Status'=>$result->invoice_status,);
				}
				/// $arr=array('Success'=>'','Failure'=>'','data'=>$address);
				$arr=array('success'=>'success','failure'=>'','data'=>$address);
				$db->commit();
				echo  json_encode($arr);exit;
			}
			else
			{
				$db->commit();
				$arr=array('success'=>'','failure'=>'');
				echo json_encode($arr);exit;
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
