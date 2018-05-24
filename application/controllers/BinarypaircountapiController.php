<?php
class BinarypaircountapiController extends Zend_Controller_Action{

	public function init()
	{
		//$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		//if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{

		try
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			//$this->_helper->layout()->setLayout("dashbord");//dashboard
			//if($this->_request->isPost())
			//{
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_REQUEST['username']));
			
			$User_info_obj=new Gbc_Model_DbTable_Userinfo();
			$bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
			$special_perm_obj=new Gbc_Model_DbTable_SpecialPermission();
			$special_off_obj=new Gbc_Model_DbTable_Specialoffer();
			$invoices_obj = new Gbc_Model_DbTable_Invoices();
			//$username=$_REQUEST['username'];
			//$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
			$username = $this->_request->getParam("username");
			$startdate =  date('2016-08-01 00:00:00');
			// $enddate =   date("2016-08-31 23:59:59");
			$enddate =   date("2018-07-31 23:59:59");
			$CurrentMonth = date('Y-m');
			$offerDetails = $common_obj->checkOfferDetails();

			//$offerDetails=$offerDetails->toArray();
			$FirstQMonth =  date('2016-04');
			$LastQMonth =   date('2016-06');

			$Permissions =$special_perm_obj->fetchRow($special_perm_obj->select()
			);

			/*$UserActivationTime=$invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('invoices'=>"invoices"),array('created_on'))
			->where("1=1 AND invoices.username = '$username'")
			->limit(1)
			);*/

			
			$UserActivationTime=$invoices_obj->fetchRow($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('invoices'=>"invoices"),array('created_on'))
			->where("1=1")
			->where("invoices.username=?",$username)
			->limit(1)
			);
			

			$ActivationTime =  $UserActivationTime->created_on;

			/*$refArray =$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
			->where("parent_username = '".$username."' and parent_id<>0")
			->order("child_position ASC")
			);*/
			
				$refArray =$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
			->where("parent_username = ?",$username)
			->where("parent_id <> ?",0)
			->order("child_position ASC")
			);
			

			$i = 0;$j=0;
			$childArray = array();
			if(!empty($refArray) && sizeof($refArray)>0)
			{
				for($i=0;$i<sizeof($refArray);$i++)
				{
					$childArray[$i] =     $refArray[$i]['username'];
					$child_position[$i] = $refArray[$i]['child_position'];
					
				}

			}

			if(!empty($childArray[0]) || !empty($childArray[1]) ){
		
				if($child_position[0] != "R"){
					$LeftpairsDetail = $common_obj->getPairs($childArray[0],$enddate,$startdate);
					$RightpairsDetail = $common_obj->getPairs($childArray[1],$enddate,$startdate);
				}else{
					$RightpairsDetail = $common_obj->getPairs($childArray[0],$enddate,$startdate);
					$LeftpairsDetail = $common_obj->getPairs($childArray[1],$enddate,$startdate);
				}


				$Leftpaircount = 0;
				$Rightpaircount = 0;
				$QLeftpaircount = 0;
				$QRightpaircount = 0;
				$ord = array();
				foreach ($LeftpairsDetail as $key => $value){
					$ord[] = strtotime($value['created_on']);
				}
				array_multisort($ord, SORT_ASC, $LeftpairsDetail);
					
					
				$ord = array();
				foreach ($RightpairsDetail as $key => $value){
					$ord[] = strtotime($value['created_on']);
				}
				array_multisort($ord, SORT_ASC, $RightpairsDetail);

				$LeftBusiness = $RightBusiness = $LastLeftBusiness =  $LastRightBusiness = 0;
				$offersArray = array();
				$pairtime = $diff = $OfferCompletionTime = '';

				$preLeftContractPrice = $common_obj->array_column($LeftpairsDetail, 'preContractPrice');
				$LastLeftBusiness = array_sum($preLeftContractPrice);
				$preRightContractPrice = $common_obj->array_column($RightpairsDetail, 'preContractPrice');
				$LastRightBusiness = array_sum($preRightContractPrice);
	
				$Lastminimum = min($LastLeftBusiness,$LastRightBusiness);
				$pre_pairs = intval($Lastminimum);
				
				if($Lastminimum == $LastLeftBusiness ){
					// $LeftCarryForward = $LastRightBusiness - $LastLeftBusiness;
					$CarryForward = round(($LastRightBusiness - $LastLeftBusiness),2);
					$RightBusiness = $CarryForward;
				}else{
					// $RightCarryForward = $LastLeftBusiness - $LastRightBusiness;
					$CarryForward = round(($LastLeftBusiness - $LastRightBusiness),2);
					$LeftBusiness = $CarryForward;
				}
	
				foreach($offerDetails as $key => $offerDetail){
					if($pre_pairs  >= $offerDetail['target']){
				
						$pre_achieved[] = $offerDetail['offer_id'];
					}
				}

				foreach($LeftpairsDetail as $pair){
				 // var_dump($pair);
					// if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth)) && ($pair['IsActive'] == 1)){
					// if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth))){
					$pairTime = $pair['created_on'];
				
					if($ActivationTime >= $startdate){
							
						$diff = floor((strtotime($pairTime)-strtotime($ActivationTime))/(3600*24));
						$LeftBusiness += $pair['ContractPrice'];
						// echo $LeftBusiness;
						// echo "<br>";
						foreach($offerDetails as $key => $offerDetail){
							if(($offerDetail['offer_id'] == 15) && ($LeftBusiness >= $offerDetail['target']) && ($diff <= 15 )){
								$id = $offerDetail['offer_id'];
								if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
									$OfferCompletionTime = $pairTime;
								}
								$offersArray[$id]['left'] = $OfferCompletionTime;
							}else if(($offerDetail['offer_id'] != 15) && ($LeftBusiness >= $offerDetail['target'])){
								$id = $offerDetail['offer_id'];
								if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
									$OfferCompletionTime = $pairTime;
								}
								$offersArray[$id]['left'] = $OfferCompletionTime;
							}
						}
					}else{
						$LeftBusiness = $LeftBusiness + $pair['ContractPrice'];
						foreach($offerDetails as $key => $offerDetail){
						 if(($offerDetail['offer_id'] != 15) && ($LeftBusiness >= $offerDetail['target']) && (!in_array($offerDetail['offer_id'],$pre_achieved)) ){
						 	$id = $offerDetail['offer_id'];
						
							if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
						 		$OfferCompletionTime = $pairTime;
						 	}
						 	$offersArray[$id]['left'] = $OfferCompletionTime;
						 }
						}
					}
					// $LastLeftBusiness += $pair['preContractPrice'];
				}
			
				$pairtime = $diff = $OfferCompletionTime = '';

				foreach($RightpairsDetail as $pair){
					// if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth))){
					$pairTime = $pair['created_on'];
					if($ActivationTime >= $startdate){
							
						$diff = floor((strtotime($pairTime)-strtotime($ActivationTime))/(3600*24));
						$RightBusiness = $RightBusiness + $pair['ContractPrice'];
						// echo $RightBusiness;
						// echo "<br>";
						foreach($offerDetails as $key => $offerDetail){
							if(($offerDetail['offer_id'] == 15) && ($RightBusiness >= $offerDetail['target']) && ($ActivationTime >= $startdate) && ($diff <= 15 )){
								$id = $offerDetail['offer_id'];
								if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
									$OfferCompletionTime = $pairTime;
								}
								$offersArray[$id]['right'] = $OfferCompletionTime;
							}else if(($offerDetail['offer_id'] != 15) && ($RightBusiness >= $offerDetail['target'])){
								$id = $offerDetail['offer_id'];
								if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
									$OfferCompletionTime = $pairTime;
								}
								$offersArray[$id]['right'] = $OfferCompletionTime;
							}
						}
					}else{
						$RightBusiness += $pair['ContractPrice'];
						foreach($offerDetails as $key => $offerDetail){
							if(($offerDetail['offer_id'] != 15) && ($RightBusiness >= $offerDetail['target'])  && (!in_array($offerDetail['offer_id'],$pre_achieved))){
								$id = $offerDetail['offer_id'];
								if(empty($OfferCompletionTime) || $OfferCompletionTime==''){
									$OfferCompletionTime = $pairTime;
								}
								$offersArray[$id]['right'] = $OfferCompletionTime;
							}
						}
					}
				}
				$minValue = min($LeftBusiness,$RightBusiness);
				$paircount = intval($minValue);


			}
			else
			{
				$paircount = 0;
			}
			$SpecialOffers =$special_off_obj->fetchAll($special_off_obj->select()
			->where("status = ?",'1')
			->order(" pairs asc ")
			);
			
			$SpecialOffers=$SpecialOffers->toArray();
			
			$offer_arr=array();
			if(!empty($SpecialOffers) && sizeof($SpecialOffers)>0)
			{
				for($i=0;$i<sizeof($SpecialOffers);$i++)
				{
					/*$id = $SpecialOffers[$i]['id'];*/
				
						
					$offer_arr[$i]['prize'] = $SpecialOffers[$i]['prize'];
					$offer_arr[$i]['pairs'] = $SpecialOffers[$i]['pairs'];
					$offer_arr[$i]['direct_pairs'] = $SpecialOffers[$i]['direct_pairs'];
					$offer_arr[$i]['image'] = $SpecialOffers[$i]['image'];
					$offer_arr[$i]['duration'] = $SpecialOffers[$i]['duration'];
					$offer_arr[$i]['price'] = $SpecialOffers[$i]['price'];
					$offer_arr[$i]['created'] = $SpecialOffers[$i]['created'];
					$offer_arr[$i]['updated_on'] = $SpecialOffers[$i]['updated_on'];
					$offer_arr[$i]['status'] = $SpecialOffers[$i]['status'];
					$id = $SpecialOffers[$i]['id'];
					$rows[$id] = $SpecialOffers[$i];
				}
			}

			if(!empty($offersArray)){
				foreach($offersArray as $key => $offersArr){
					if(!empty($offersArray[$key]['left']) && !empty($offersArray[$key]['right'])){
							
						if(strtotime($offersArray[$key]['left']) > strtotime($offersArray[$key]['right'])){
							$CompletionOffersArray[$key]['completionTime'] = $offersArray[$key]['left'];
						}else{
							$CompletionOffersArray[$key]['completionTime'] = $offersArray[$key]['right'];
						}
						$diff = '';
						if($key == 24){
							$diff = floor((strtotime($CompletionOffersArray[24]['completionTime'])-strtotime($CompletionOffersArray[23]['completionTime']))/(3600*24));
							if($diff > 365){
								unset($CompletionOffersArray[24]);
							}
						}
							
					}

				}
					
			}

			$flag = true;
			$achieved = '';
			$Achievable = '';

			foreach($offerDetails as $key => $offers_detail){
				$offer_id = $offers_detail['offer_id'];
				if(($paircount < $offers_detail['target']) && $flag == true){
					// var_dump($rows[$offer_id]);
					$pending = $rows[$offer_id]['pairs'] - $paircount;
					$Achievable = $rows[$offer_id]['image'];
					// echo $Achievable;
					$message = "You have ".$pending." pairs pending to win ".$rows[$offer_id]['prize'];
					$flag = false;
				}

				if(($offers_detail['target'] <= $paircount )){
			
					// echo "here";
					// echo "<br>";
					if($offers_detail['offer_id'] == 24){
					
						if(!empty($CompletionOffersArray[24])){
							$achieved = $rows[$offer_id]['prize'];
						}
					}else if($offers_detail['offer_id'] == 15){
					
						if(!empty($CompletionOffersArray[15])){
							$achieved = $rows[$offer_id]['prize'];
						}
					}else{
				
						$achieved = $rows[$offer_id]['prize'];
					}
				}
			}


				if(!empty($Achievable) && $Achievable!='')
			 {
			 	
			 }
			 else
			 {
				$Achievable='Contact to Admin';
				}

			$arr=array("offer_data"=>$offer_arr,"offer_flag"=>'1',"quarterly_offer_flag"=>$Permissions['quarterly_offers'],"no_of_binary_pairs"=>$paircount,'current_achieved'=>$achieved,'next_achievable'=>$Achievable);
			echo json_encode($arr);exit;

		}

		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}
}