<?php
class AddkitController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{

		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		
		
		$username=$authUserNamespace->user;
		$url= BASE."/Contractmaster?type=gb2";
		$result=$common_obj->call_curl($url);
		$contract=(array)json_decode($result,true);
	//	echo "<pre>";
	//	echo $url;
	//	print_r($result);exit;
		$this->view->contract=$contract;
		$hardware_data=array();
		$sha_data=array();

		for($list=0;$list<sizeof($contract['data']);$list++)
		{
			$cont_data = array('contract_id'=>$contract['data'][$list]['contract_id'],'contract_ts'=>$contract['data'][$list]['contract_ts'],'contract_name'=>$contract['data'][$list]['contract_name'],'contract_type'=>$contract['data'][$list]['contract_type'],'contract_qty'=>$contract['data'][$list]['contract_qty'],'contract_descr'=>$contract['data'][$list]['contract_descr'],'contract_rate'=>$contract['data'][$list]['contract_rate'],'discount'=>$contract['data'][$list]['discount'],'price_paid'=>$contract['data'][$list]['price_paid'],'total_price'=>$contract['data'][$list]['total_price'],'description'=>$contract['data'][$list]['description'],'ordering'=>$contract['data'][$list]['ordering'],'admin_limit'=>$contract['data'][$list]['admin_limit'],'available_limit'=>$contract['data'][$list]['available_limit'],'max_limit'=>$contract['data'][$list]['max_limit'],'direct_earning'=>$contract['data'][$list]['direct_earning']);
			if($contract['data'][$list]['contract_type']=='hardware')
			{
				array_push($hardware_data,$cont_data);
			}
			else
			{
				array_push($sha_data,$cont_data);
			}
		}
	//	print_r($sha_data);exit;
		$this->view->sha_contract=$sha_data;
		$this->view->hardware_contract=$hardware_data;


		$countries_obj=new Gbc_Model_DbTable_Countries();
		$countries_data=$countries_obj->fetchAll($countries_obj->select()
		->setIntegrityCheck(false)
		->from(array('countries'))
		->order('country ASC')
		);

		$cities_obj=new Gbc_Model_DbTable_Cities();
		$cities_data=$cities_obj->fetchAll($cities_obj->select()
		->setIntegrityCheck(false)
		->from(array('cities'))
		->order('name ASC')
		);

		$states_obj=new Gbc_Model_DbTable_States();
		$states_data=$states_obj->fetchAll($states_obj->select()
		->setIntegrityCheck(false)
		->from(array('states'))
		->order('name ASC')
		);

			
		$users_obj=new Gbc_Model_DbTable_Userinfo();
		
		/*$users_data=$users_obj->fetchAll($users_obj->select()
		->setIntegrityCheck(false)
		->from(array('user_info'))
		->where("username='$username'"));*/
		
		$users_data=$users_obj->fetchAll($users_obj->select()
		->setIntegrityCheck(false)
		->from(array('user_info'))
		->where("username=?",$username));
		
		
		/*
		$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		
		$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
		->setIntegrityCheck(false)
		->from(array('featured_permissions'),array('value','end'))
		->where("name=?",'pay_via_wallet_disable'));
		
		$this->view->permissions_data=$permissions_data;
		
		*/
		
		$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		$permissions_data=$permissions_obj->fetchAll($permissions_obj->select()
		->setIntegrityCheck(false)
		->from(array('featured_permissions'),array('name','value','start','end'))
		->where("name in(?)",array('pay_via_wallet_disable','btc_conversion_value','btc_usd_price')))->toArray();
		
		$this->view->permissions_data=$permissions_data;
		
							

							
		
	//	print_r($permissions_data);
	//	exit;
		
		$final_balance_obj=new Gbc_Model_DbTable_FinalBalance();
			
		$BalanceDetail=$final_balance_obj->fetchRow($final_balance_obj->select()
				->setIntegrityCheck(false)
				->from(array('f'=>'final_balance'),array('bal_amt'))
				->joinLeft(array('m'=>'manual_withdrawal_request'),"m.username = f.username and m.status = 'Requested'",array('amount as requested_amount'))
				->where("f.username =? ",$username)
				);
		

		//$currentBalance['bal_amt'] = round(($BalanceDetail['bal_amt'] - $BalanceDetail['amount']),8);
		$this->view->current_bal=$BalanceDetail;
		//print_r($currentBalance);
		//exit;
		//echo "<pre>";
		//print_r($users_data);exit;
		
		
		$user_cat=array('countries_data'=>$countries_data,'cities_data'=>$cities_data,'states_data'=>$states_data,'users_data'=>$users_data);
		$this->view->user_cat=$user_cat;
		$this->view->title="Gainbitcoin - Buy Contract";
	}

	public function buycontractAction()
	{
		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_POST['username']));
				
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$token = $common_obj->cleanQueryParameter(($_POST['token']));
		/*	if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
				$data=array('success'=>'','failure'=>'Invalid request found.');
				echo json_encode($data);exit;

			}*/
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{
					
				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}

			}
			
			
			$invoiceid =$common_obj->cleanQueryParameter(($_POST['invoice-id']));

			if(!empty($invoiceid))
			{

				$InvoiceId = $invoiceid;
					
				$kits_obj=new Gbc_Model_DbTable_Gb2Kits();

				/*$InvoiceDetail=$kits_obj->fetchRow($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('kits'=>"kits"),array('kits.kit_price', 'kits.kit_number'))
				->joinLeft(array('contracts'=>'contracts'),"contracts.price_paid = kits.kit_price",array('contracts.contract_id','contracts.total_price'))
				->where("invoice_id = '$InvoiceId' and status = 'Active'")
				);*/

				
				$InvoiceDetail=$kits_obj->fetchRow($kits_obj->select()
				->setIntegrityCheck(false)
				->from(array('kits'=>"kits"),array('kits.kit_price', 'kits.kit_number'))
				->joinLeft(array('contracts'=>'contracts'),"contracts.price_paid = kits.kit_price",array('contracts.contract_id','contracts.total_price'))
				->where("invoice_id = ? and status = 'Active'",$InvoiceId)
				);
				
				
			}
			$kit_price = !empty($InvoiceDetail->kit_price)?$InvoiceDetail->kit_price:0;
			$kit_number = !empty($InvoiceDetail->kit_number)?$InvoiceDetail->kit_number:'';
			$contract_id = !empty($InvoiceDetail->contract_id)?$InvoiceDetail->contract_id:'';
		//	$contract_price = !empty($InvoiceDetail->total_price)?$InvoiceDetail->total_price:'';
			$contract_price = !empty($InvoiceDetail->kit_price)?$InvoiceDetail->kit_price:'';
			$contract_type=$common_obj->cleanQueryParameter(($_POST['contract_type']));
			
			$contract_type = "ROI";
			
			
			$hid_btc_value=$common_obj->cleanQueryParameter(($_POST['hid_btc_value']));
				
			$hid_btc_value = (isset($hid_btc_value) ? $hid_btc_value: '');
			$value = !empty($InvoiceDetail->kit_price)?$InvoiceDetail->kit_price:0;

			$sha_hashrate_qty=$common_obj->cleanQueryParameter(($_POST['sha_hashrate_qty']));
	
			if(empty($contract_id) || $contract_id==''){
				$contract_id = (isset($sha_hashrate_qty) ? $sha_hashrate_qty : '');
			}
	
			if(!empty($kit_price) && $kit_price!='' && $kit_price!=0){
				/* if(($kit_price == 12.5) || ($kit_price == 12.99)){
				 $amount = 15;
				 }else if($kit_price == 25){
				 $amount = 31;
				 }else {
				 $amount = $value;
				 } */
				$amount = $contract_price;

			}else{

				$contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();

				$amount_data = $contracts_obj->fetchRow($contracts_obj->select()
				->where("contract_id =?",$contract_id)
				->where("status=1"));

				
				//$amount = mysql_query("select price_paid from contracts where total_price = '$hid_btc_value'");
				$amount = $amount_data->contract_qty;
				// var_dump($amount);
				
			/*	$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
				$Price_in_usd = json_decode($Price_in_usd);
				$Price_in_usd = $Price_in_usd->bpi->USD->rate;
			//		print_r($Price_in_usd);
			//	$Price_in_usd = number_format($Price_in_usd,'4','.','');
				$Price_in_usd = str_replace(',','',$Price_in_usd);

				$value = round(($amount/$Price_in_usd),2);
				*/
				$value = $amount;
			}
			$type = (isset($_GET['t']) ? $_GET['t'] : '');
			if(!empty($_GET['t']) && isset($_GET['t']))
			{
				$type=$_GET['t'];
			}
			else
			{
				$type='';
			}
			
			//$k_type=$_POST['k_type'];
			$k_type=$common_obj->cleanQueryParameter(($_POST['k_type']));
			
			if(!empty($k_type) && $k_type=='hardware')
			{
				$k_type='hardware';
			}
			else
			{
				$k_type=$k_type;
			}


			/*
			 $contract_type=$_POST['contract_type'];
			 $hid_btc_value=$_POST['hid_btc_value'];
			 $sha_hashrate_qty=$_POST['sha_hashrate_qty'];
			 if($_POST['contract_type']){
			 $contract_type = $_POST['contract_type'];
			 }
			 $amount = (isset($_POST['hid_btc_value']) ? $_POST['hid_btc_value']: '');
			 $contract_id = (isset($_POST['sha_hashrate_qty']) ? $_POST['sha_hashrate_qty'] : '');
			 if($amount == 15){
			 // $value = 12.5;
			 $value = 12.99;
			 }else if($amount == 31){
			 $value = 25;
			 }else {
			 $value = $amount;
			 }*/
			//$sha_hashrate_qty=$_POST['sha_hashrate_qty'];
			$sha_hashrate_qty=$common_obj->cleanQueryParameter(($_POST['sha_hashrate_qty']));
			$db->commit();
			$arr=array('success'=>'success','failure'=>'','contract_type'=>$contract_type,'contract_id'=>$contract_id,'value'=>$value,'amount'=>$amount,'sha_hashrate_qty'=>$sha_hashrate_qty,'type'=>$type,'k_type'=>$k_type);

			echo json_encode($arr);exit;
		}
		catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}
	}

}
