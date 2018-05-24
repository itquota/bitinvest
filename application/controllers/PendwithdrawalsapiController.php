<?php

class PendwithdrawalsapiController extends Zend_Controller_Action{

	public function init(){

	}
	public function indexAction()
	{
		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);


		 
		$pendwithdrawalsobj = new Gbc_Model_DbTable_Withdrawals();

		try{

			$result=$pendwithdrawalsobj->fetchAll($pendwithdrawalsobj->select()
			->setIntegrityCheck(false)
			->from(array('withdrawals'))
			->order("timestamp desc ")
			->limit(1000)
			);


		}
		catch(Exception $e){

			echo $e->getMessage();exit;
		}



		$address=array();

		//echo $result1;exit;
		if($result && sizeof($result)>0)
		{
			$result = $result -> toArray();
			
		}
		else 
		{
			$result = array();
		}
		echo  json_encode($result);exit;
	}

}