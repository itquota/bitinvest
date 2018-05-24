<?php

class ChecklegController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->disableLayout();
		
	}

	public function indexAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		try {
			$leg = $common_obj->cleanQueryParameter($_POST['leg']);
			$ref_sponser_id = $common_obj->cleanQueryParameter($_POST['ref_sponser_id']);
			$token = $common_obj->cleanQueryParameter($_POST['token']);
/*			if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
			{
				$data=array('success'=>'successs','failure'=>'','data'=>"Invalid token");
				echo json_encode($data);exit;
			} */
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'successs','failure'=>'','data'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}

			}

			$user = new Gbc_Model_DbTable_Userinfo();
			$bin_user_ref= new Gbc_Model_DbTable_Binaryuserreferences();


			$check_user = $user->fetchRow($user->select()
			->where("binaryUser = 1 ")
			->where($db->quoteInto("sponsor_id=?",trim($_POST["ref_sponser_id"])) . ' OR ' . $db->quoteInto("username=?",trim($_POST["ref_sponser_id"])))
			);


			if(isset($check_user) && sizeof($check_user)>0)
			{
				$current_users = $bin_user_ref->fetchAll($bin_user_ref->select()
				->setIntegrityCheck(false)
				->from(array('b' =>'binary_user_refences'),array('b.child_position'))
				->where("parent_username=?" , trim($check_user->username)));

				if(sizeof($current_users)<=0)
				{
					$current_users = $bin_user_ref->fetchAll($bin_user_ref->select()
					->setIntegrityCheck(false)
					->from(array('b' =>'binary_user_refences'),array('b.child_position'))
					->where("username=?" , trim($check_user->username)));

					if($current_users[0]->child_position==$_POST['leg']){
						$ref['choice_leg']=$_POST['leg'];
						$msg = "success";
					}else{
						if($current_users[0]->child_position == "R"){
							$position = "Right";
						}else{
							$position = "Left";
						}
						$msg = "You can add your first child in ".$position." side.";

					}
				}

				else if(($check_user->isActiveId)==0 && ($current_users[0]->child_position==$_POST['leg'])){
					$ref['choice_leg']=$_POST['leg'];
					$msg = "success";
				}else if($check_user->isActiveId==1){
					$ref['choice_leg']=$_POST['leg'];
					$msg = "success";
					// $msg = "false";
				}else{
					$msg = "The referrer user is not active. Go back and try it again.";
					// $msg = "false";
				}
			}
			else
			{
				$msg = "Given referred id not valid! ";
			}
			$data=array('success'=>'successs','failure'=>'','data'=>$msg);
			echo json_encode($data);exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

	public function checkemailAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$token = $common_obj->cleanQueryParameter($_POST["token"]);
		/*if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
		{
			$data=array('success'=>'','failure'=>'Invalid token');
			echo json_encode($data);exit;
		}*/
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'Invalid Input');
					echo json_encode($data);exit;


				}

			}

		}

		$email = $common_obj->cleanQueryParameter($_POST['email']);

		$user = new Gbc_Model_DbTable_Userinfo();
		$check_user = $user->fetchRow($user->select()
		->where("binaryUser is NOT NULL")
		->where("email_address=?",trim($email))
		);



		if(isset($check_user) && $check_user!='' && sizeof($check_user)>0)
		{
			$data=array('success'=>'success','failure'=>'','data'=>$check_user);
			echo json_encode($data);exit;
		}
		else
		{
			$data=array('success'=>'','failure'=>'Invalid username');
			echo json_encode($data);exit;
		}

	}
	public function checkpasswordAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj=new Gbc_Model_Custom_Miscellaneous;
		$user_obj = new Gbc_Model_DbTable_Userinfo();
		$common_obj = new Gbc_Model_Custom_CommonFunc();
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

		if(!empty($authUserNamespace->user) && $authUserNamespace->user!='')
		{

			$result=array();
			$url= BASE."/Userinfoapi?username=".$authUserNamespace->user;
			$result=$common_obj->call_curl($url);
			$userInfo=(array)json_decode($result,true);

			$salt = $userInfo['data']["salt"];
			$username=$authUserNamespace->user;
			$pwd =$misc_obj->encryptPassword($_POST['old_pass'], $salt);
			$newPass=$misc_obj->encryptPassword($_POST['new_pass'], $salt);
			if($_POST['new_pass']!=$_POST['repeat_pass'])
			{
				$data=array('success'=>'','failure'=>'New Password and Repeat password not Match');
				echo json_encode($data);exit;
			}


			else if($pwd===$userInfo['data']["password"])
			{
				$data=array('success'=>'success','failure'=>'');
				echo json_encode($data);exit;
			}
			else
			{
				$data=array('success'=>'','failure'=>'Error, old password not match.');
				echo json_encode($data);exit;
			}
		}
		else
		{
			$data=array('success'=>'','failure'=>'Invalid Request Found.');
			echo json_encode($data);exit;
		}

	}
	public function checkusernameAction(){
		//$email=$_POST['email'];
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$user = new Gbc_Model_DbTable_Userinfo();
		$token = $common_obj->cleanQueryParameter($_POST["token"]);
		/*if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
		{
			$data=array('success'=>'','failure'=>'Invalid token');
			echo json_encode($data);exit;
		}*/
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'Invalid Input');
					echo json_encode($data);exit;

				}

			}

		}

		$userInfo_chk = $user->fetchRow($user->select()
		->where("username=?",trim($_POST["username"])));



		if(isset($userInfo_chk) && $userInfo_chk!='' && sizeof($userInfo_chk)>0)
		{
			$data=array('success'=>'','failure'=>'Duplicate');
			echo json_encode($data);exit;
		}
		else
		{
			$data=array('success'=>'success','failure'=>'');
			echo json_encode($data);exit;
		}

	}

	public function checkregidAction(){

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$token = $common_obj->cleanQueryParameter($_POST["token"]);
		/*if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
		{
			$data=array('success'=>'','failure'=>'Invalid token');
			echo json_encode($data);exit;
		}*/
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					$data=array('success'=>'','failure'=>'Invalid Input');
					echo json_encode($data);exit;


				}

			}

		}
		//$email=$_POST['email'];
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$user = new Gbc_Model_DbTable_Userinfo();

		$userInfo = $user->fetchRow($user->select()
		->where("binaryUser =1")
		->where($db->quoteInto("sponsor_id= ?",trim($_POST["ref_id"])) . ' OR ' . $db->quoteInto("username=?", trim($_POST["ref_id"])))
		);



		if(isset($userInfo) && $userInfo!='' && sizeof($userInfo)>0)
		{
			$data=array('success'=>'success','failure'=>'','data'=>$userInfo);
			echo json_encode($data);exit;
		}
		else
		{
			$data=array('success'=>'','failure'=>'Invalid username');
			echo json_encode($data);exit;
		}

	}
	public function checkcountAction(){
		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$token = $common_obj->cleanQueryParameter($_POST["token"]);
			/*if(!isset($authUserNamespace->token)|| $authUserNamespace->token!=$token)
			{
				$data=array('success'=>'','failure'=>'Invalid token');
				echo json_encode($data);exit;
			}*/
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

						$data=array('success'=>'','failure'=>'Invalid Input');
						echo json_encode($data);exit;


					}

				}

			}
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$ref_id = $common_obj->cleanQueryParameter($_POST['ref_id']);

			$user = new Gbc_Model_DbTable_Userinfo();

			$userInfo = $user->fetchRow($user->select()
			->where("binaryUser=1")
			->where($db->quoteInto("sponsor_id= ?",trim($_POST["ref_id"])) . ' OR ' . $db->quoteInto("username=?", trim($_POST["ref_id"])))
			);

			$isLevelFull=$userInfo->isLevelFull;

		/*	if(isset($userInfo) && $userInfo!='' && sizeof($userInfo)>0 && ($userInfo->isLevelFull)==1)
			{
				$data=array('success'=>'success','failure'=>'','data'=>$userInfo);
				echo json_encode($data);exit;
			}
			else
			{
				$data=array('success'=>'','failure'=>'');
				echo json_encode($data);exit;
			}

*/
			//print_r($userInfo);
			if(isset($userInfo) && $userInfo!='' && sizeof($userInfo)>0 && ($userInfo->isLevelFull)==1)
			{
				$data=array('success'=>'success','failure'=>'','data'=>$userInfo);
				echo json_encode($data);exit;
				
				
			}
			else
			{				
				$data=array('success'=>'','failure'=>'');
				echo json_encode($data);exit;
			}
		}
	catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
	public function checkkitstatusAction()
	{
		
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

					echo "false";exit;

				}

			}

		}
		$returnArr = array();
		// if (isset($_SESSION["user"])) {
		if (isset($_POST['kit_number'])) {

			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$kitNumber = $_POST['kit_number'];
			$kit_type = $_POST['kit_type'];
			$kit_price = $_POST['total_amount'];
			if($kit_price ==  "86"){
				$total_price = 100;
			}else if($kit_price ==  "64"){
				$total_price = 75;
			}else if($kit_price ==  "44.5"){
				$total_price = 50;
			}else if($kit_price ==  "22.75"){
				$total_price = 25;
			}else if($kit_price ==  "13.75"){
				$total_price = 15;
			}else if($kit_price ==  "4.85"){
				$total_price = 5;
			}else{
				$total_price = $kit_price;
			}
			
			$kits_obj=new Gbc_Model_DbTable_Kits();
			
			$kits_data = $kits_obj->select();
			$kits_data->setIntegrityCheck(false)
			->from(array('A'=>"kits"),array('count(*) as countkit', 'kit_price', 'comment as KitsComment','created_on'));
			$kits_data->joinLeft(array('kitInvoice'=>'kit_invoices'),"kitInvoice.invoice_id =  A.invoice_id",array('kitInvoice.comment as KitinvoiceComment'));
			
			
			
			if(isset($kit_type) && $kit_type!=''){
				$kits_data->where('kit_type = ?',$kit_type);
			}
			
			if(isset($kit_price) && $kit_price!=''){
			//	$kits_data->where("kit_price = '$kit_price' or kit_price = '$total_price'");
					
			}
			$kits_data->where("kit_number LIKE ?",$kitNumber);
			$kits_data->where("status = ?",'Active');
			$kits_data->where($db->quoteInto("kit_used_by =?", "") . ' OR ' . $db->quoteInto("kit_used_by is NULL"));
			$kits_data = $kits_obj->fetchRow($kits_data);
			$kits_data1 = $kits_data->toArray();
			//print_r($kits_data1);
			
			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value','start','end'))
			->where("name =?",'kits_valid_date'));
			

			$CurrentDate = date('Y-m-d h:i:s');
			$StartDate = "2017-04-30 23:59:59";
			$EndDate = "2017-05-31 23:59:59";
			
			if(!empty($permissions_data)){
				$StartDate = $permissions_data['start'];
				$EndDate = $permissions_data['end'];
			}
			if(!empty($kits_data1['countkit'])){
				$total_price = $kits_data1['kit_price'];
				if(($kits_data1['KitsComment'] || $kits_data1['KitinvoiceComment'] ) && ($kits_data1['created_on'] <= $StartDate && $CurrentDate <= $EndDate)){
					$result = "The old kits before ".date('jS F, Y', strtotime($StartDate)).", acquired from GainBitcoin without paying BTC will not be available for use till ".date('jS F, Y', strtotime($EndDate)).".";
					echo $result;
					exit;
				}
			}
			
			$kitValArray =$common_obj-> checkKitStatus($_POST['kit_number'],$_POST['kit_type'],$_POST['total_amount'],$total_price);
			//var_dump($kitValArray);
			if($kitValArray > 0){
				echo "true";exit;
			}else if($kitValArray < 0){
				echo "2";exit;
			}else{
				echo "false";exit;
			}
		}else{
			echo "false";exit;
		}
		// }else {
		//     echo "false";
		// }

	}
	
	public function checkkitAction()
	{
		
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{
			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					echo "false";exit;
				}
			}
		}
		$returnArr = array();
		// if (isset($_SESSION["user"])) {
		if (isset($_POST['kit_number'])) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$kitNumber = $_POST['kit_number'];
			$kit_type = $_POST['kit_type'];
			$contract_power = $_POST['total_amount'];
			$total_price = $kit_price;
			$Gb2Contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();
			
			$Gb2Contracts_data = $Gb2Contracts_obj->select();
			$Gb2Contracts_data->setIntegrityCheck(false)
			->from(array('A'=>"gb2_contracts"),array('count(*) as countkit', 'contract_id'));
			
						
			if(isset($contract_power) && $contract_power!=''){
				$Gb2Contracts_data->where('contract_qty = ?',$contract_power);
			}
			
			
			$Gb2Contracts_data = $Gb2Contracts_obj->fetchRow($Gb2Contracts_data);
			$Gb2Contracts_data1 = $Gb2Contracts_data->toArray();
		//	print_r($kits_data1);
			
			
			if(!empty($Gb2Contracts_data1['countkit'])){
				$contract_id = $Gb2Contracts_data1['contract_id'];
			}
		//	echo $contract_id;
			$kitValArray =$common_obj-> checkKitStatus_gb2($_POST['kit_number'],$_POST['kit_type'],$_POST['total_amount'],$contract_id);
		//	var_dump($kitValArray);
			if($kitValArray > 0){
				echo "true";exit;
			}else if($kitValArray < 0){
				echo "2";exit;
			}else{
				echo "false";exit;
			}
		}else{
			echo "false";exit;
		}
		// }else {
		//     echo "false";
		// }

	}
	
	
	public function checkkitvalueAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		  $contracts_obj=new Gbc_Model_DbTable_Contracts();
		$antixss = new Gbc_Model_Custom_StringLimit();
		$msg='';
		$totalAmount=0;
		if (isset($_POST['kitNo'])) {
			$token = $common_obj->cleanQueryParameter($_POST['token']);
	/*		if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
				$data=array('success'=>'','failure'=>'Invalid request found.');
				echo json_encode($data);exit;

			} 
*/
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
			try
			{
				$kitArray =$common_obj->getGlobalVar('allowed_kits', "");
				$allowed_kits = $kitArray[0]['allowed_kits'];
					
				if ($_POST['kitNo'] > $allowed_kits) {

					$msg = "Can not purchase more than " . $allowed_kits . " kits in a day";
				}
				else
				{
					$kitArray1 =$common_obj-> getKitPrice(trim($_POST['contract_id']));
					$flag = 1;
					if(isset($_POST['total_bal'])){
						$cost_kits = $kitArray1->total_price;
						$totalAmount = $cost_kits * $_POST['kitNo'];
						if ($totalAmount > $_POST['total_bal'] ) {

							$msg = "Can not purchase kits of  amount more than " . $_POST['total_bal'];
							$flag = 0;
						}
					}

					if(!empty($flag) && $flag==1){
						if (!empty($kitArray1) && isset($kitArray1) && sizeof($kitArray1)>0) {
							/*	if($kitArray1->total_price == 15){
							 	
							$KitPrice = 12.99;
							}else if($kitArray1->total_price == 31){
							$KitPrice = 25;
							}else{*/
							//$KitPrice = $kitArray1->price_paid;
							$KitPrice = $kitArray1->total_price;
							//	}
							$totalCost = $KitPrice * $_POST['kitNo'];
							
							  $contract_data = $contracts_obj->fetchRow($contracts_obj->select()
								->where("contract_id =?", trim($_POST['contract_id']))
								->where("status =?",1)
								);
							$discount = $contract_data['discount'];
							
							
							
							$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
							$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
							->setIntegrityCheck(false)
							->from(array('featured_permissions'),array('name','value'))
							->where("name =?",'btc_conversion_value'));

							if(!empty($permissions_data)){
								$btc_conversion_value = $permissions_data['value'];
							}else{
								$btc_conversion_value = 2.345;
							}
							
							//$btc_conversion_value = 12.345;
							
							//print_r($discount);
							//exit;
							$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
							if($Price_in_usd){
								$Price_in_usd = json_decode($Price_in_usd);
								$Price_in_usd = $Price_in_usd->bpi->USD->rate;
								
								$update_Price_in_usd = array('value' => $Price_in_usd);
								$udpatePrice = $permissions_obj->update($update_Price_in_usd,"name = 'btc_usd_price'");
								
							}else{
									$permissions_data1=$permissions_obj->fetchRow($permissions_obj->select()
										->setIntegrityCheck(false)
										->from(array('featured_permissions'),array('name','value'))
										->where("name =?",'btc_usd_price'));

										if(!empty($permissions_data1)){
											$Price_in_usd = $permissions_data1['value'];
										}else{
											$Price_in_usd = 0;
										}
							}
							
						//		print_r($Price_in_usd);
						//	$Price_in_usd = number_format($Price_in_usd,'4','.','');
							$Price_in_usd = str_replace(',','',$Price_in_usd);
							
							$totalAmount = round(($totalCost/$Price_in_usd),4);
						//	print_r($totalAmount);
							$totalAmount += round((($totalAmount*$btc_conversion_value)/100),4);
							
							$contractPrice = round(($KitPrice/$Price_in_usd),4);
							$contractPrice += round((($contractPrice*$btc_conversion_value)/100),4);
							
						//	print_r($Price_in_usd);
						//	print_r($totalAmount);
							
						//	$data = array('totalAmount' => $totalAmount);
							$data = array('totalAmount' => $totalCost);
							if($discount){
							//	$netAmount = round(($totalAmount - (($totalAmount * $discount) / 100)),4);
								$netAmount = round(($totalCost - (($totalCost * $discount) / 100)),4);
								$data['netAmount'] = $netAmount;
							}else{
								$data['netAmount'] = $totalCost;
							}
							$data['contractPrice'] = $KitPrice;
						//	$data = json_encode($data);
						//	$totalAmount = number_format($totalAmount,8);

						} else {

							$msg = "Can not purchase more than " . $allowed_kits . " kits in a time";
						}
					}
				}
			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}
		}
		else{
			$msg="invalid contract or user";
		}
		if($msg!='')
		{

			$arr=array('success'=>'','failure'=>$msg);
			echo json_encode($arr);exit;
		}
		else
		{

			$arr=array('success'=>'success','failure'=>'','data'=>$data);
			echo json_encode($arr);exit;
		}
	}

	public function requestpaymentAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$username = $common_obj->cleanQueryParameter($_POST['username']);
		$noOfKits = $common_obj->cleanQueryParameter($_POST['noOfKits']);
		$invoiceId = $common_obj->cleanQueryParameter($_POST['invoiceId']);
		$payment_mode = $common_obj->cleanQueryParameter($_POST['payment_mode']);
		$txid = $common_obj->cleanQueryParameter($_POST['txid']);
		$kits_pay_obj=new Gbc_Model_DbTable_Kitspayment();
/*
		$existingKitPayment= $kits_pay_obj->fetchRow($kits_pay_obj->select()
		->where("invoice_id ?=", $invoice_id));

		if(!empty($existingKitPayment) && sizeof($existingKitPayment)>0)
		{
			$InsertKitAdmin=array('txid'=>$txid,'updated_on'=>new Zend_Db_Expr('NOW()'));
			$InsertKitAdmin_qry=$kits_pay_obj->update($InsertKitAdmin,"invoice_id = '$invoice_id'");
		}
		else
		{
*/
			$InsertKitAdmin=array('username'=>$username,'invoice_id'=>$invoiceId,'no_of_kits'=>$noOfKits,'payment_mode'=>$payment_mode,'txid'=>$txid);
			$InsertKitAdmin_qry=$kits_pay_obj->insert($InsertKitAdmin);
//		}
		if(!empty($InsertKitAdmin)){
/*		
	$from = "admin@gainbitco.in";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// Additional headers
			//$headers .= 'To: ' .$to. "\r\n";
			$headers .= 'From: ' .$from. "\r\n";
			$headers .= "CC: thegainbitcoinhelp@gmail.com \r\n";
*/


	//$to = "thegainbitcoin@gmail.com";
	$to = "thegainbitcoinhelp@gmail.com";
			//$cc = array("virender@nexgenfmpl.com"=>"","op@nexgenfmpl.com"=>"","thegainbitcoinhelp@gmail.com" => "","ajay@nexgenfmpl.com"=>"","puneet@nexgenfmpl.com"=>"","kanwar@nexgenfmpl.com"=>"");
			$cc = "";
			
			$from = 'support@gainbitcoin.com';
			$replyTo = 'thegainbitcoinhelp@gmail.com';
			$subject = "Manual payment by  $username";
			$EmailMessage = "Manual payment has been made by username: '$username' for invoice_id: '$invoiceId' and Kits quantity: '$noOfKits' and Transaction id: $txid. Please check and activate kits.";
			$sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$EmailMessage,$EmailMessage,$cc);

			echo "success";exit;

}
		else{
			echo "fail";
		}
	}
}
