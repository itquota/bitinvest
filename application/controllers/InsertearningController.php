<?php
class InsertearningController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction()
	{

		$this->getResponse()->setHeader('Content-Type', 'application/json');
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		echo "started " . date('Y-m-d h:i:s')."\n";
		if (PHP_SAPI === 'cli' && isset($argv[1])) {
			list($key, $val) = explode("=", $argv[1]);
			$key = $val;
		} else {
			$key = $_REQUEST['key'];
		}

		if (PHP_SAPI === 'cli' && isset($argv[2])) {
			list($key, $val) = explode("=", $argv[2]);
			$curr_date= $val;
		} else {
			if(isset($_REQUEST['CurrentDate']))
			{
				$curr_date = $_REQUEST['CurrentDate'];
			}
			
		}	
		
		if (PHP_SAPI === 'cli' && isset($argv[3])) {
			list($key, $val) = explode("=", $argv[3]);
			$ref_date= $val;
		} else {
			if(isset($_REQUEST['refundDate']))
			{
				$ref_date = $_REQUEST['refundDate'];
			}
			
		}
				try{

			if(isset($key) && ($key == HASHKEY)){

				$cronName = "insertEarnings";
				// $start =  new Zend_Db_Expr('NOW()');
				$start = date('Y-m-d H:i:s');

	   $common_obj = new Gbc_Model_Custom_CommonFunc();
	   $user = new Gbc_Model_DbTable_Userinfo();
	    
	   $common_obj->updateCronStatus($cronName,$start,'');
		echo "after cron status " . date('Y-m-d h:i:s')."\n";
	   $date=date("Y-m-d h:i:s");
	   $CurrentDate = !empty($curr_date)?"".$curr_date."":date('Y-m-d');
		$currentDate = date('d');
		if($currentDate == "1" || $currentDate == "16"){
			/* mysql_query("CREATE TABLE `earnings_$date` LIKE earnings"); 
			mysql_query("INSERT `earnings_$date` SELECT * FROM earnings;"); */
		}
	   $refundDate=!empty($ref_date)?$ref_date:REFUND_DATE;;
	   $allGlobalVars =$common_obj->getGlobalVar('*','');
	  
	   if(!empty($allGlobalVars) && sizeof($allGlobalVars)>0){

	   	$allowed_kits = $allGlobalVars[0]['allowed_kits'];
	   	$latestTimestamp = $allGlobalVars[0]["timestamp"];
	   	//$currScryptCoin = $allGlobalVars["current_coin_scrypt"];
	   	$currHarwareCoin = $allGlobalVars[0]["current_coin_hardware"];
	   	$currSHACoin = $allGlobalVars[0]["current_coin_sha"];
	   	//$currScryptHashrate = $allGlobalVars["hash_rate_mhz"];
	   	$currhardwareHashrate = $allGlobalVars[0]["hash_rate_hardware"];
	   	$currSHAHashrate = $allGlobalVars[0]["hash_rate"];
	   	$payout_ghz = $allGlobalVars[0]["payout_ghz"];
	   	//$payout_mhz = $allGlobalVars["payout_mhz"];
	   	$payout_hardware = $allGlobalVars[0]["payout_hardware"];

	   }
	    
	   $page = file_get_contents('https://bitpay.com/api/rates');
	   $my_array = json_decode($page, true);
	   $exchange_rate = $my_array[1]["rate"];

	    
	   $userCount = $user->fetchRow($user->select()
	   ->setIntegrityCheck(false)
	   ->from(array('u'=>'user_info'),array('count(u.username) as userCount'))
	   ->where("binaryUser is NOT NULL")
	   ->where("created_on < ?",$CurrentDate));

	 //  $userCounttotal=$userCount->countuser;
	   $count = 0;
	   while($count < (($userCount->userCount) - 1)){

	   	$limit = 200;
	    $offset = !empty($count)?$count:'0';
	   	$Users = $common_obj->getBinaryUsersForCron($limit,$offset,'',$CurrentDate);
	 echo "after user select " . date('Y-m-d h:i:s')."\n";
	   	//$Users=$Users->toArray();
	   	
	
	   	foreach($Users as $username){
	 
	   		//$username=$Users[$j];
	   		$allActiveInvoices = $common_obj->getUserContractsOnly($username,1,'SHA',$CurrentDate);
			
	   		for($k=0;$k<sizeof($allActiveInvoices);$k++){

	   			$totalBTCEarning = 0;
	   			$poolFees = 0;
	   			$contractRate = 0;

	   			$qnty = $allActiveInvoices[$k]['contract_qty'];
	   			//get daily payout per hash for this type of contract
	   			if($allActiveInvoices[$k]['contract_type']=='SHA') {
	   				$payoutPerDayPerHash = $payout_ghz;
	   				//$fixedPoolFees = $fixedPoolFeesSHA;
	   			} else if($allActiveInvoices[$k]['contract_type']=='Scrypt'){
	   				$payoutPerDayPerHash = $payout_hardware;
	   				//$fixedPoolFees = $fixedPoolFeesScrypt;
	   			} else if($allActiveInvoices[$k]['contract_type']=='hardware'){
	   				$payoutPerDayPerHash = $payout_hardware;
	   				//$fixedPoolFees = $fixedPoolFeesScrypt;
	   			}

	   			$totalBTCEarning=number_format(($payoutPerDayPerHash*$qnty),8,'.','');
	   			$poolFees=0;
			

	   			$earning_details = array(
						"username" => $allActiveInvoices[$k]['username'],
						"pool_fees" => $poolFees,
						"total_amt" => $totalBTCEarning,
						"net_amt" => ($totalBTCEarning-$poolFees),
						"debt" => ($totalBTCEarning-$poolFees),
						"contract_id" => $allActiveInvoices[$k]['contract_id'],
						"invoice_id" =>$allActiveInvoices[$k]['invoice_id']
					);
					
					
					

							$KitCreated = 	date('Y-m-d',strtotime($allActiveInvoices[$k]['kit_created']));
							$diff = floor((strtotime($CurrentDate)-strtotime($KitCreated))/(3600*24));
							$temp = 0;

							if(!empty($KitCreated) && (($KitCreated >= date('Y-m-d',strtotime($refundDate))))){
								$temp =1;
								$result = $common_obj->insertEarningTemp($earning_details,0,0,$temp);

							}

							$result = $common_obj->insertEarning($earning_details,0,0,$temp);

							echo "after insert " . date('Y-m-d h:i:s')."\n";
	   		}
			
	   		$count++;

	   	}

	   }
				if($count >= $userCount->userCount - 1){
					$cronName = "insertEarnings";
					// updateCronStatus($cronName);
					$end = date('Y-m-d H:i:s');
					$common_obj->updateCronStatus($cronName,'',$end);
				}
				 
			}
		}catch(Exception $e)
		{
			$data=array('success'=>'','failure'=> $e->getMessage());
			echo json_encode($data);exit;
		}

	}

}