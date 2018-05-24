<?php
class ContractmasterController extends Zend_Controller_Action{

	public function init(){
			
	}

	public function indexAction(){
		
		try {
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$common_obj->setLimitFull();
			$getCotracts=$common_obj->getUserContracts("", "", '1471235757_1657303824');
			if($_REQUEST['type'] == "gb2"){
				$getCotracts=$common_obj->getCotractNew('gb2');
			}else{
				$getCotracts=$common_obj->getCotractNew('SHA');
			}
		//	echo "here";
		//	print_r($getCotracts);
		//	exit;
			//$getCotracts=$common_obj->checkKitStatus('fsdfsdfsdfsdf',$kit_type='sha',$kit_price='52');
			$common_obj->CalculateAllKits();	
			$arr=array();
			if(isset($getCotracts) && sizeof($getCotracts)>0)
			{
				$subarr=array();
				for($i=0;$i<(sizeof($getCotracts));$i++) {
					$subarr = array('contract_id'=>$getCotracts[$i]['contract_id'],'contract_ts'=>$getCotracts[$i]['contract_ts'],'contract_name'=>$getCotracts[$i]['contract_name'],'contract_type'=>$getCotracts[$i]['contract_type'],'contract_qty'=>$getCotracts[$i]['contract_qty'],'discount'=>$getCotracts[$i]['discount'],'price_paid'=>$getCotracts[$i]['price_paid'],'total_price'=>$getCotracts[$i]['total_price'],'available_limit'=>$getCotracts[$i]['available_limit'],'max_limit'=>$getCotracts[$i]['max_limit'],'ordering'=>$getCotracts[$i]['ordering']);
					array_push($arr,$subarr);
				}

			}
			$data=array('success'=>'success','failure'=>'','data'=>$arr);
			echo json_encode($data);exit;
		}
		catch(Exception $e)
		{
			$data=array('success'=>'','failure'=>'Something went wrong! Please try again.');
			echo $e->getMessage();exit;
		}

	}

}