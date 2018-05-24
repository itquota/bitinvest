<?php
class MiningoutputapiController extends Zend_Controller_Action{

	public function init()
	{
		//echo "fsdfsd";exit;
	}
	public function indexAction()
	{
		try{
			$common_obj=new Gbc_Model_Custom_CommonFunc();
			$username=trim($_REQUEST['username']);
			$invoiceId=trim($_REQUEST['invoiceId']);
			$type=trim($_REQUEST['type']);
			if($type == "gb2" && (!empty($invoiceId))){
				$pastRecords =$common_obj-> getEarningsFor_gb2($username, "", $invoiceId);
			}else{
				$pastRecords =$common_obj-> getEarningsFor($username, "", $invoiceId);
			}
			$master=array();
			if(isset($pastRecords) && sizeof($pastRecords)>0)
			{
				for($i=0;$i<sizeof($pastRecords);$i++)
				{
					if($type == "gb2" && (!empty($invoiceId))){
						$subarr=array('username'=>$pastRecords[$i]['username'],'electricity_cost'=>$pastRecords[$i]['electricity_cost'],'maintenance_cost'=>$pastRecords[$i]['maintenance_cost'],'pool_fees'=>$pastRecords[$i]['pool_fees'],'total_amt'=>$pastRecords[$i]['amount'],'timestamp'=>$pastRecords[$i]['timestamp'],'invoice_id'=>$pastRecords[$i]['invoice_id']);
					}else{
					$subarr=array('username'=>$pastRecords[$i]['username'],'total_amt'=>$pastRecords[$i]['total_amt'],'timestamp'=>$pastRecords[$i]['timestamp'],'invoice_id'=>$pastRecords[$i]['invoice_id']);
					}
					array_push($master,$subarr);
				}
			}
			$arr=array('success'=>'success','failure'=>'','data'=>$master);
			echo json_encode($arr);exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
}