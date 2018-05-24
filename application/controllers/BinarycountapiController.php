<?php
class BinarycountapiController extends Zend_Controller_Action{

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
			$User_info_obj=new Gbc_Model_DbTable_Userinfo();
			$bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
			$special_perm_obj=new Gbc_Model_DbTable_SpecialPermission();
			$special_off_obj=new Gbc_Model_DbTable_Specialoffer();
			//$username=$_REQUEST['username'];
			$username = $this->_request->getParam("username");
		

			$startdate =  date('Y-m-01');
			$enddate =   date('Y-m-t')." 23:59:59";
			$CurrentMonth = date('Y-m');

			$Permissions =$special_perm_obj->fetchRow($special_perm_obj->select()
			);

			$Categories1=array();
			$direct=$User_info_obj->fetchRow($User_info_obj->select()
			->setIntegrityCheck(false)
			->from(array('u'=>'user_info'),array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
			->joinLeft(array('i'=>'invoices'),"i.username = u.username and i.created_on like '$CurrentMonth%'",array('round(sum(i.contract_rate),2) as contract_rate'))
			->where("u.ref_sponsor_id = ?",$username)
			->group("u.ref_sponsor_id")
			);
			if(!empty($direct) && sizeof($direct)>0)
			{
				$direct=$direct->contract_rate;
			}
			else
			{
				$direct=0;
			}


			/*$result1 =$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
			->where("parent_username = '".$username."' and parent_id<>0")
			->order("child_position ASC")
			);*/
			
			$result1 =$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
			->where("parent_username = ?",$username)
			->where("parent_username <> ?",0)
			->order("child_position ASC")
			);
			
			$i = 0;$j=0;
			$childArray = array();
			if(!empty($result1) && sizeof($result1)>0)
			{
				for($i=0;$i<sizeof($result1);$i++)
				{
					$childArray[$i] =     $result1[$i]['username'];
					$child_position[$i] = $result1[$i]['child_position'];
					$i++;
				}

			}

		 if(!empty($childArray[0]) || !empty($childArray[1]) ){
		 		
		 	if($child_position[0] != "R"){

		 		$LeftpairsDetail =$common_obj-> getPairs($childArray[0],$enddate,$startdate);
		 		$RightpairsDetail =$common_obj-> getPairs($childArray[1],$enddate,$startdate);
		 	}else{

		 		$RightpairsDetail =$common_obj-> getPairs($childArray[0],$enddate,$startdate);
		 		$LeftpairsDetail =$common_obj-> getPairs($childArray[1],$enddate,$startdate);
		 	}


		 	$Leftpaircount = 0;
		 	$Rightpaircount = 0;
		 	$QLeftpaircount = 0;
		 	$QRightpaircount = 0;
		 	$LeftBusiness = $RightBusiness = $LastLeftBusiness =  $LastRightBusiness = $QLeftBusiness = $QRightBusiness = 0;
		 	foreach($LeftpairsDetail as $pair){
		 		// if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth)) && ($pair['IsActive'] == 1)){
		 		if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth))){
		 			$LeftBusiness += $pair['ContractPrice'];
		 			$Leftpaircount++;
		 		}
					$LastLeftBusiness += $pair['preContractPrice'];

		 	}

		 	foreach($RightpairsDetail as $pair){

		 		if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth))){
					 $RightBusiness += $pair['ContractPrice'];
					 	
					 $Rightpaircount++;
		 		}
					$LastRightBusiness += $pair['preContractPrice'];
						
		 	}
		 		
		 	$Lastminimum = min($LastLeftBusiness,$LastRightBusiness);
		 		
		 	if($Lastminimum == $LastLeftBusiness ){
		 		// $LeftCarryForward = $LastRightBusiness - $LastLeftBusiness;
		 		$CarryForward = $LastRightBusiness - $LastLeftBusiness;
		 	}else{
		 		// $RightCarryForward = $LastLeftBusiness - $LastRightBusiness;
		 		$CarryForward = $LastLeftBusiness - $LastRightBusiness;
		 	}
		 		
		 		
		 	if(!empty($Permissions) && sizeof($Permissions)>0 && !empty($Permissions->quarterly_offers)){
		 		foreach($LeftpairsDetail as $pair){
		 			// if(date('Y-m',strtotime($pair['created_on'])) >= date('Y-m',strtotime($FirstQMonth)) && date('Y-m',strtotime($pair['created_on'])) <= date('Y-m',strtotime($LastQMonth)) && ($pair['IsActive'] == 1)){
		 			if(date('Y-m',strtotime($pair['created_on'])) >= date('Y-m',strtotime($FirstQMonth)) && date('Y-m',strtotime($pair['created_on'])) <= date('Y-m',strtotime($LastQMonth)) ){
		 				$QLeftBusiness += $pair['ContractPrice'];
		 				$QLeftpaircount++;
		 			}
		 		}
		 		foreach($RightpairsDetail as $pair){
		 			if(date('Y-m',strtotime($pair['created_on'])) >= date('Y-m',strtotime($FirstQMonth)) && date('Y-m',strtotime($pair['created_on'])) <= date('Y-m',strtotime($LastQMonth)) ){
						 $QRightBusiness += $pair['ContractPrice'];
						 $QRightpaircount++;
		 			}
		 		}
		 		$Qpaircount = intval(min($QLeftBusiness,$QRightBusiness)) ;
		 	}
			 else{
					$Qpaircount = 0;
			 }

		 	if($Lastminimum == $LastLeftBusiness ){
		 		$RightBusiness = round(($RightBusiness + $CarryForward),2);
		 	}else{
		 		$LeftBusiness =  round(($LeftBusiness + $CarryForward),2);
		 	}

		 	$minValue = min($LeftBusiness,$RightBusiness);

		 	$paircount = intval($minValue);
		 }
		 else{
		 	$paircount = 0;
		 	$Qpaircount = 0;
		 }

		 $result2 =$special_off_obj->fetchAll($special_off_obj->select()
		 ->where("status = ?",'1')
		 ->order("pairs asc")
		 );


		 $flag = $Qflag = true;
		 $achieved = 0;
		 if(!empty($result2) && sizeof($result2)>0)
		 {
		 	for($k=0;$k<sizeof($result2);$k++)
		 	{

					if((($paircount < $result2[$k]['pairs']) ||($direct < $result2[$k]['direct_pairs'])) && $flag){
						$pending = $result2[$k]['pairs'] - $paircount;
						$Achievable = $result2[$k]['image'];
						// echo $Achievable;
						$message = "You have ".$pending." pairs pending to win ".$result2[$k]['prize'];
						$flag = false;
					}
					if(($result2[$k]['pairs'] <= $paircount ) && ($result2[$k]['direct_pairs'] <= $direct )){
						//echo "here";
						$achieved = $result2[$k]['prize'];
					}
						
					if(!empty($Permissions->quarterly_offers)){
						if($Qpaircount < $result2[$k]['pairs'] && $Qflag){
							$Qpending = $result2[$k]['pairs'] - $paircount;
							$QAchievable = $result2[$k]['image'];
							// echo $QAchievable;
							$Qmessage = "You have ".$Qpending." pairs pending to win ".$result2[$k]['prize'];
							$Qflag = false;
						}else if($row['pairs'] <= $Qpaircount ){
							//echo "here";
							$Qachieved = $result2[$k]['prize'];
						}
					}
						


		 	}
		 }
		 if(!empty($Permissions) && sizeof($Permissions)>0)
		 {
		 	if(!empty($Permissions->quarterly_offers) && ($Permissions->quarterly_offers)!='')
		 	{
		 		$querterly_offer_flag=1;
		 	}
		 	else
		 	{
		 		$querterly_offer_flag=0;
		 	}
		 }
		 else
		 {
		 	$querterly_offer_flag=0;
		 }
		 $db->commit();	
		 $quarterly_offers=array('no_of_pairs'=>$paircount,'current_achieved'=>$achieved,'next_achievable'=>$Achievable,'q_no_of_pairs'=>$Qpaircount,'q_current_achieved'=>$Qachieved,'q_next_achievable'=>$QAchievable);
		 $nomal_offers=array('no_of_pairs'=>$paircount,'no_of_direct_pairs'=>$direct,'current_achieved'=>$achieved,'next_achievable'=>$Achievable);
		 $arr=array('success'=>'success','failure'=>'','qflag'=>$querterly_offer_flag,'quarterly_offers'=>$quarterly_offers,'nomal_offers'=>$nomal_offers);
		 print_r($arr);exit;
		}

		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();exit;
		}
	}
}