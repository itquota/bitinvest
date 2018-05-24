<?php
class KitgenerationController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin'))$this->_redirect("/Login");

	}
	public function indexAction()
	{

		$this->_helper->layout()->setLayout("Admindashbord");//dashboard
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$username=$authUserNamespace->user;
		$url= BASE."/Contractmaster";
		$result=$common_obj->call_curl($url);
		$contract=(array)json_decode($result,true);
		//echo "<pre>";
		//print_r($contract);exit;
		$this->view->contract=$contract;
		$hardware_data=array();
		$sha_data=array();

		for($list=0;$list<sizeof($contract['data']);$list++)
		{
			$cont_data = array('contract_id'=>$contract['data'][$list]['contract_id'],'contract_ts'=>$contract['data'][$list]['contract_ts'],'contract_name'=>$contract['data'][$list]['contract_name'],'contract_type'=>$contract['data'][$list]['contract_type'],'contract_qty'=>$contract['data'][$list]['contract_qty'],'contract_descr'=>$contract['data'][$list]['contract_descr'],'contract_rate'=>$contract['data'][$list]['contract_rate'],'price_paid'=>$contract['data'][$list]['price_paid'],'total_price'=>$contract['data'][$list]['total_price'],'description'=>$contract['data'][$list]['description'],'ordering'=>$contract['data'][$list]['ordering'],'admin_limit'=>$contract['data'][$list]['admin_limit'],'available_limit'=>$contract['data'][$list]['available_limit'],'max_limit'=>$contract['data'][$list]['max_limit'],'direct_earning'=>$contract['data'][$list]['direct_earning']);
			if($contract['data'][$list]['contract_type']=='hardware')
			{
				array_push($hardware_data,$cont_data);
			}
			else
			{
				array_push($sha_data,$cont_data);
			}
		}
		//print_r($sha_data);exit;
		$this->view->sha_contract=$sha_data;
		$this->view->hardware_contract=$hardware_data;


		$this->view->title="Gainbitcoin - Buy Contract";
	}

}
