<?php

class AchieverapiController extends Zend_Controller_Action{

	public function init()
	{

	}

	public function indexAction(){

		try {
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			$this->getResponse()->setHeader('Content-Type', 'application/json');
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			//$common_obj->cleanQueryParameter(($_REQUEST['username']));

			$quarterShowObj = new Gbc_Model_DbTable_SpecialPermission();

			$resultuser=$quarterShowObj->fetchAll($quarterShowObj->select()
			->setIntegrityCheck(false)
			->from(array('s'=>'special_permissions'),array('quarterly_offers')));

			//$UserName=$_REQUEST['username'];

			//$UserName=$common_obj->cleanQueryParameter(($_REQUEST['username']));
			$UserName = $this->_request->getParam("username");
				
			if($UserName != ''){

				$UserName=$UserName;
					
			}else{
				$arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
				echo json_encode($arr);
				exit;
			}

			$result=sizeOf($resultuser);
			$currentMonth = date('m');
			$months = array();

			for ($i = 4; $i > 0; $i--) {
				array_push($months, date('m', strtotime("first day of -".$i."month")));

			}

			if(isset($result) && $result>0)
			{


				//$UserName = $_SESSION["user"];





				//echo "<pre>";
				//print_r($months);
				if($resultuser[0]['quarterly_offers'] == 1){



					if($currentMonth > 3){


						$Tab1 = date('F',strtotime(date('Y').'-01'));
						$Param1 = '01';
						$Tab2 = date('F',strtotime(date('Y').'-02'));
						$Param2 = '02';
						$Tab3 = date('F',strtotime(date('Y').'-03'));
						$Param3 = '03';

						$Tab4 = "Quarter(1st Jan - 31st Mar)";
						$Param4 = '1st';


					}else if($currentMonth > 6){
						$Tab1 = date('F',strtotime(date('Y').'-04'));
						$Param1 = '04';
						$Tab2 = date('F',strtotime(date('Y').'-05'));
						$Param2 = '05';
						$Tab3 = date('F',strtotime(date('Y').'-06'));
						$Param3 = '06';

						$Tab4 = "Quarter(1st April - 30th June)";
						$Param4 = '2nd';


					}else if($currentMonth > 9){
						$Tab1 = date('F',strtotime(date('Y').'-07'));
						$Param1 = '07';
						$Tab2 = date('F',strtotime(date('Y').'-08'));
						$Param2 = '08';
						$Tab3 = date('F',strtotime(date('Y').'-09'));
						$Param3 = '09';
						$Tab4 = "Quarter(1st July - 30th Sept)";
						$Param4 = '3rd';
					}else{
						$Tab1 = date('F',strtotime(date('Y').'-10'));
						$Param1 = '10';
						$Tab2 = date('F',strtotime(date('Y').'-11'));
						$Param2 = '11';
						$Tab3 = date('F',strtotime(date('Y').'-12'));
						$Param3 = '12';

						$Tab4 = "Quarter(1st Oct - 30th Dec)";
						$Param4 = '4th';

					}


				}


				else{

					$Tab1 = date('F',strtotime(date('Y').'-'.$months[0]));
					$Param1 = $months[0];
					$Tab2 = date('F',strtotime(date('Y').'-'.$months[1]));
					$Param2 = $months[1];
					$Tab3 = date('F',strtotime(date('Y').'-'.$months[2]));
					$Param3 = $months[2];
					$Tab4 = date('F',strtotime(date('Y').'-'.$months[3]));
					$Param4 = $months[3];
					// var_dump($months[3]);
					// var_dump($Tab1);
					// var_dump($Param1);
				}

				$Year = date('Y');


				$achievedObj= new Gbc_Model_DbTable_Achiever();

				$resultachieved=$achievedObj->fetchAll($achievedObj->select()
				->setIntegrityCheck(false)
				->from(array('achievers'))
				->where(" created like '$Year%'"));

				//echo "<pre>";
				//print_r($resultachieved[0]['id']);exit;

				$result=sizeOf($resultachieved);
				if(isset($result) && $result>0){

					for($i=0;$i<$result;$i++){
						//echo "<pre>";
						//print_r($resultachieved[$i]);exit;
						/*  if($row['month'] == "January"){
						 $Achievers['January'][] = $row;
						 }else if($row['month'] == "February"){
						 $Achievers['February'][] = $row;
						 }else if($row['month'] == "March"){
						 $Achievers['March'][] = $row;
						 } */

						if(($resultachieved[$i]['month'] == "1st") || ($resultachieved[$i]['month'] == "2nd")|| ($resultachieved[$i]['month'] == "3rd")|| ($resultachieved[$i]['month'] == "4th")){
							$Achievers['Quarterly'][] = $resultachieved[$i];
						}else{
							$Achievers[$resultachieved[$i]['month']][] = $resultachieved[$i];

						}


					}
				}

				//$a=$common_obj->cleanQueryParameter(($_REQUEST['a']));
				$a = $this->_request->getParam("a");



				if(isset($a) && is_numeric($a) && isset($a)!="" && !empty($a))
				{
					$month = date('F',strtotime(date('Y').'-'.$a));
					// echo $month;
					$Achievers = $Achievers[$month];

				}
				else if(isset($a)&& isset($a)!="" && !empty($a) && $a == "q"){
						
					$Achievers = $Achievers["Quarterly"];
					// echo $month;
				}else{

					$Achievers= $Achievers[$Tab1];

				}
				//echo "<pre>";
				//print_r($Achievers[0]['username']);exit;

				//$achieved = "SELECT * FROM `achievers` where username = '$UserName' and created like '$Year%'";
				//$achievedAchievers = runQuery($achieved, $conn);
				//$NoOfPrizes = mysql_num_rows($achievedAchievers['dbResource']);
				// var_dump($achievedAchievers);

				$achievedObj = new Gbc_Model_DbTable_Achiever();
				$resultachieved=$achievedObj->fetchAll($achievedObj->select()
				->setIntegrityCheck(false)
				->from(array('achievers'),array('username','pairs','direct_pairs','prize'))
				->where("username='$UserName' and created like'$Year%'"));
				$address=array();
				$result=sizeof($resultachieved);
				/*if($result && $result>0)
				 {
				 foreach($resultachieved as $row)
				 {
				 $address[]=array("Username"=>$row->username,"BinaryPairs"=>$row->pairs,"DirectPairs"=>$row->direct_pairs,"Prize"=>$row->prize);
				 }
				 $arr=array('Success'=>'','Failure'=>'','data'=>$address);
				 echo  json_encode($arr);

				 }*/
				$db->commit();
				$subarr=array();
				for($j=0;$j<sizeof($Achievers);$j++)
				{
					$address = array('id'=> $Achievers[$j]['id'],'username' => $Achievers[$j]['username'],'pairs' => $Achievers[$j]['pairs'],'direct_pairs' =>$Achievers[$j]['direct_pairs'],'prize' =>$Achievers[$j]['prize'],'month' =>$Achievers[$j]['month'],'created' =>$Achievers[$j]['created'],'updated' =>$Achievers[$j]['updated']);
					array_push($subarr,$address);
				}
				$arr=array('success'=>'success','Param1'=>$Param1,'Param2'=>$Param2,'Param3'=>$Param3,'Param4'=>$Param4,'month1'=>$Tab1,'month2'=>$Tab2,'month3'=>$Tab3,'month4'=>$Tab4,'data'=>$subarr);
				echo  json_encode($arr);exit;

			}
		}catch(Exception $e)
		{
			$db->rollBack();
			$arr=array('success'=>'','failure'=>$e->getMessage());
			echo json_encode($arr);exit;
		}





	}




}