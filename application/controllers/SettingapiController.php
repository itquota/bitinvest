<?php
class SettingapiController extends Zend_Controller_Action{

	public function init(){

	}
	public function indexAction()
	{
		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);



		$settingobj = new Gbc_Model_DbTable_Globalvariables();
		 
		try{
				

	 	$result=$settingobj->fetchRow($settingobj->select()
	 	->setIntegrityCheck(false)
	 	->from(array('global_variables')));
	 		


	 	$address=array();
	 	$result1=sizeof($result);
	 	if($result1 && $result1>0)
	 	{
	 		//foreach($result as $result)
	 		//{
	 			
	 		$address[]=array('hashrate'=>$result->hash_rate,'hashratehardware'=>$result->hash_rate_hardware,'target'=>$result->target,'Progress'=>$result->progress,'Payoutghz'=>$result->payout_ghz,'Payouthardware'=>$result->payout_hardware,'Payoutincnic'=>$result->payout_inc_nic,'Allowedkits'=>$result->allowed_kits,'Perkitcost'=>$result->per_kit_cost,'Shippingcost'=>$result->shipping_cost);

	 		//}
	 		$arr=array('Success'=>'','Failure'=>'','data'=>$address);

			 echo  json_encode($arr);
	 	}

		}
		catch(Exception $e)
		{
			$e->getMessage();exit;
		}

	}



}