<?php
class GenerategraphController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("dashbord");
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$username=$authUserNamespace->user;
			$filter = $common_obj->cleanQueryParameter($_POST['filter']);
			$token = $common_obj->cleanQueryParameter(($_POST['token']));
		/*	if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
				$data=array('success'=>'','failure'=>'Invalid request found.');
				echo json_encode($data);exit;

			}
*/
			$antixss = new Gbc_Model_Custom_StringLimit();
			foreach($_POST as $key => $value)
			{

				if(isset($value) && $value != ""){
					$antixss->setEncoding($value, "UTF-8");
					if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
						$db->rollBack();
						$data=array('success'=>'','failure'=>'Invalid Input.');
						echo json_encode($data);exit;

					}

				}

			}

			if(!empty($filter)  && $filter=='week')
			{
				$i =0;
				$j =0;
				// var_dump($usersArray);
				$now = new DateTime( "6 days ago", new DateTimeZone('Asia/Kolkata'));
				$interval = new DateInterval( 'P1D'); // 1 Day interval
				$period = new DatePeriod( $now, $interval, 6); // 7 Days
				$dates = array();
				foreach( $period as $day) {
				 $key = $day->format( 'd');
				 $dates[] = $key;
				 $datesarr[]= date("jS M, Y", strtotime($day->format('d-m-Y')));
				}
				$business_det=array();
				$startdate= date('Y-m-d', strtotime('-6 days'));
				$enddate=date('Y-m-d')." 23:59:59";

				$data=array('0','0','0','0','0','0','0');
				$result=$common_obj->userBusinessDetails($username,$startdate,$enddate);
				//$url= BASE."/Userbusinessdetailsapi?username=".$username."&startdate=".$startdate."&enddate=".$enddate;
				//echo $url;exit;
				//$result=$common_obj->call_curl($url);
				//$result=(array)json_decode($result,true);
				//echo "<pre>";
				//print_r($result);exit;



				for($i=0;$i<sizeof( $result['pairDetails']);$i++)
				{
					$created_date= $result['pairDetails'][$i]['created_on'];
					$pieces = explode(" ", $created_date);
					$day_data_arr=explode("-", $pieces[0]);
					$day_data=$day_data_arr[2];

					if (in_array($day_data,$dates))
					{
						$index=array_search($day_data,$dates);
						$data[$index]=$data[$i]+$result['pairDetails'][$i]['ContractPrice'];
					}
					//print_r($data);exit;


				}
			}
			else if(!empty($filter) && ($filter)=='month')
			{
				$now = new DateTime( "29 days ago", new DateTimeZone('Asia/Kolkata'));
				$interval = new DateInterval( 'P1D'); // 1 Day interval
				$period = new DatePeriod( $now, $interval, 29); // 7 Days
				$dates = array();
				$datesarr=array();
				foreach( $period as $day) {
					$key = $day->format( 'd');
					$dates[] = $key;
					$datesarr[]= date("jS M, Y", strtotime($day->format('d-m-Y')));
				}
					
				$data=array();
				for($dt=0;$dt<30;$dt++)
				{
					$data[]=0;
				}
				//$startdate= date($datesarr[0]);
				//$enddate= date($datesarr[29])."%2023:59:59";

				$startdate= date('Y-m-d', strtotime('-29 days'));
				$enddate=date('Y-m-d')." 23:59:59";

				$last_date_arr=explode("-",$last_date_of_month);
				$last_record=$last_date_arr[0];
				$result=$common_obj->userBusinessDetails($username,$startdate,$enddate);
				//$url= BASE."/Userbusinessdetailsapi?username=".$username."&startdate=".$startdate."&enddate=".$enddate;
				//echo $url;exit;
				//$result=$common_obj->call_curl($url);
				//$result=(array)json_decode($result,true);
				//echo "<pre>";
				//print_r($result);exit;

				for($i=0;$i<sizeof($result['pairDetails']);$i++)
				{
					$created_date= $result['pairDetails'][$i]['created_on'];
					$pieces = explode(" ", $created_date);
					$day_data_arr=explode("-", $pieces[0]);
					$day_data=$day_data_arr[2];
					if (in_array($day_data,$dates))
					{
						$index=array_search($day_data,$dates);
						if(!empty($index))
						{
							$data[$index]=$data[$index]+$result['pairDetails'][$i]['ContractPrice'];
						}
					}



					/*if($day_data>=1 && $day_data<=5)
						{
						$index=array_search($day_data,$dates);
						$data[0]=$data[0]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}
						else if($day_data>5 && $day_data<=10)
						{
						$index=array_search($day_data,$dates);
						$data[1]=$data[1]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}
						else if($day_data>10 && $day_data<=15)
						{
						$index=array_search($day_data,$dates);
						$data[2]=$data[2]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}
						else if($day_data>15 && $day_data<=20)
						{
						$index=array_search($day_data,$dates);
						$data[3]=$data[3]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}
						else if($day_data>20 && $day_data<=25)
						{
						$index=array_search($day_data,$dates);
						$data[4]=$data[4]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}
						else if($day_data>25 && $day_data<=$last_record)
						{
						$index=array_search($day_data,$dates);
						$data[5]=$data[5]+$result['data']['pairDetails'][$i]['ContractPrice'];
						}*/

				}
			}
			/*for($j=0;$j<sizeof($data);$j++)
				{
				$k=0;
				if(!empty($data[$j]) && $data[$j]!=0)
				{
				$business_det[$k]['date']=date($dates[$j].'-m-Y');
				$business_det[$k]['contract']=$data[$j];
				}
				}*/

			$arr=array('success'=>'success','failure'=>'','dates'=>$dates,'data'=>$data,'datesarr'=>$datesarr);
			echo json_encode($arr);exit;



		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}
	}
	public function graphAction()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$this->_helper->layout()->setLayout("dashbord");
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$username=$authUserNamespace->user;

		$token = $common_obj->cleanQueryParameter(($_POST['token']));
	/*	if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
			$data=array('success'=>'','failure'=>'Invalid request found.');
			echo json_encode($data);exit;

		} */
		$antixss = new Gbc_Model_Custom_StringLimit();
		foreach($_POST as $key => $value)
		{

			if(isset($value) && $value != ""){
				$antixss->setEncoding($value, "UTF-8");
				if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
					$db->rollBack();
					$data=array('success'=>'','failure'=>'Invalid Input.');
					echo json_encode($data);exit;

				}

			}

		}
		$data=array('0','0','0','0','0','0','0');
		
		/*
		 $data = array(
			  array(
                'y'=> '2006',
                'a'=> '100',
                'b'=> '90'
            ),
			 array(
					'y'=> '2007',
					'a'=> '75',
					'b'=> '65'
				),
			 array(
					'y'=> '2008',
					'a'=> '50',
					'b'=> '40'
				),
			  array(
                'y'=> '2009',
                'a'=> '75',
                'b'=> '65'
            ),
			  array(
                'y'=> '2010',
                'a'=> '50',
                'b'=> '40'
            ),
			  array(
                'y'=> '2011',
                'a'=> '75',
                'b'=> '65'
            ),
			  array(
                'y'=> '2012',
                'a'=> '100',
                'b'=> '90'
            ),
			  array(
                'y'=> '2013',
                'a'=> '65',
                'b'=> '80'
            ),
			 array(
                'y'=> '2014',
                'a'=> '50',
                'b'=> '90'
            ), array(
                'y'=> '2015',
                'a'=> '65',
                'b'=> '75'
            )
		 
		 
		 );
		echo json_encode($data);exit;
var_dump($data);				 
exit;
		*/
		if(!empty($_POST['filter'])  && $_POST['filter']=='week')
		{
			$this->_helper->layout()->setLayout("dashbord");
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$username=$authUserNamespace->user;

			$url= BASE."/Trendingchartapi";
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			echo json_encode($result);exit;

		}
		else if(!empty($_POST['filter']) && ($_POST['filter'])=='month')
		{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			try {
				$now = new DateTime( "29 days ago", new DateTimeZone('Asia/Kolkata'));
				$interval = new DateInterval( 'P1D'); // 1 Day interval
				$period = new DatePeriod( $now, $interval, 29); // 7 Days
				$dates = array();
				$datesarr=array();
				foreach( $period as $day) {
					$key = $day->format( 'd');
					$dates[] = $key;
					$datesarr[]= date("jS M, Y", strtotime($day->format('d-m-Y')));
				}
					
					
					
				$invoices_obj = new Gbc_Model_DbTable_Invoices();
				$user_obj = new Gbc_Model_DbTable_Userinfo();
					
				$salesResult = $invoices_obj->fetchAll($invoices_obj->select()
				->setIntegrityCheck(false)
				->from(array('inv' =>'invoices'),array('round(sum(inv.contract_rate),2) as total_sales','count(inv.invoice_id) as sales','inv.created_on'))
				->where("contract_type in ('SHA','hardware','MS','ES') and created_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 29 DAY) AND ".new Zend_Db_Expr('NOW()'))
				->group("date(inv.created_on)")
				->order("inv.created_on asc")
				);
					

				//	print_r(sizeof($salesResult));exit;
				
				$usersResult = $user_obj->fetchAll($user_obj->select()
				->setIntegrityCheck(false)
				->from(array('u' =>'user_info'),array('count(username) as users', 'created_on'))
				->where("created_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 29 DAY) AND ".new Zend_Db_Expr('NOW()')."")
				->group("date(created_on)")
				->order("created_on asc")
				);
					
				//echo "<pre>";
				//print_r(sizeof($usersResult));exit;
				//print_r(($salesResult->toArray()));exit;
				$rows = array();
				foreach ($salesResult as $row)
				{
					$rows[] = $row;
				}
				//$rows=$salesResult->toArray();
				//	echo date('y-m-d h:i:s');
				//print_r($rows);exit;
					

				$usersArray = array();
				foreach ($usersResult as $row)
				{
					$usersArray[] = $row;
				}
				//	$usersArray=toArray->$usersResult[0];
				//print_r(sizeof($rows));exit;
				$i =0;
				$j=0;
				for($dt=0;$dt<30;$dt++)
				{
					$data[]=0;
					$sales[]=0;
					$users[]=0;
				}
			//	print_R($dates);
				//exit;
				if(!empty($dates)){

					foreach($dates as $date){
						//echo "here";
					//	echo $date;
						if(!empty($rows)){
							//echo $rows[$i]['created_on']."<br>";
							if(!empty($rows[$i]['created_on']))
							{
								if (in_array(date('d',strtotime($rows[$i]['created_on'])),$dates))
								{

									$index=array_search(date('d',strtotime($rows[$i]['created_on'])) ,$dates);
									if(array_key_exists($index,$sales)){
										$index=array_search(date('d',strtotime($rows[$i]['created_on'])) ,array_reverse($dates,true));
									}
									if(!empty($index))
									{
										
										//echo $rows[$i]['created_on']."<br>";
										if($rows[$i]['created_on'] > '2017-03-27'){
											$sales[$index] = $rows[$i]['sales'] + 350;
										}else{
											$sales[$index] = $rows[$i]['sales'];
										}

									}
									$i++;
								}
								/*else if( date('d',strtotime($rows[$i]['created_on'])) >=6 && date('d',strtotime($rows[$i]['created_on']))<=10)
								 {

								 $sales[1]=$sales[1]+$rows[$i]['sales'];
								 $i++;
								 }
								 else if( date('d',strtotime($rows[$i]['created_on'])) >=11 && date('d',strtotime($rows[$i]['created_on']))<=15)
								 {

								 $sales[2]=$sales[2]+$rows[$i]['sales'];
								 $i++;
								 }
								 else if( date('d',strtotime($rows[$i]['created_on'])) >=16 && date('d',strtotime($rows[$i]['created_on']))<=20)
								 {

								 $sales[3]=$sales[3]+$rows[$i]['sales'];
								 $i++;
								 }
								 else if( date('d',strtotime($rows[$i]['created_on'])) >=21 && date('d',strtotime($rows[$i]['created_on']))<=25)
								 {

								 $sales[4]=$sales[4]+$rows[$i]['sales'];
								 $i++;
								 }
								 else if( date('d',strtotime($rows[$i]['created_on'])) >=26 && date('d',strtotime($rows[$i]['created_on']))<=$last_record)
								 {

								 $sales[5]=$sales[5]+$rows[$i]['sales'];
								 $i++;
								 }*/
									
							}

						}
		//	print_r($sales);
						if(!empty($usersArray)){
							if(!empty($usersArray[$j]['created_on']))
							{
								if (in_array(date('d',strtotime($usersArray[$j]['created_on'])),$dates))
								{
									$index=array_search(date('d',strtotime($usersArray[$j]['created_on'])) ,$dates);
									if(!empty($index))
									{
										$users[$index]=$users[$index]+$usersArray[$j]['users'];
									}
									$j++;
								}
									
								/* if( date('d',strtotime($usersArray[$j]['created_on'])) >=1 && date('d',strtotime($usersArray[$j]['created_on']))<=5)
								 {

								 $users[0]=$users[0]+$usersArray[$j]['users'];
								 $j++;
								 }
								 else if( date('d',strtotime($usersArray[$j]['created_on'])) >=6 && date('d',strtotime($usersArray[$j]['created_on']))<=10)
								 {

								 $users[1]=$users[1]+$usersArray[$j]['users'];
								 $j++;
								 }
								 else if( date('d',strtotime($usersArray[$j]['created_on'])) >=11 && date('d',strtotime($usersArray[$j]['created_on']))<=15)
								 {

								 $users[2]=$users[2]+$usersArray[$j]['users'];
								 $j++;
								 }
								 else if( date('d',strtotime($usersArray[$j]['created_on'])) >=16 && date('d',strtotime($usersArray[$j]['created_on']))<=20)
								 {

								 $users[3]=$users[3]+$usersArray[$j]['users'];
								 $j++;
								 }
								 else if( date('d',strtotime($usersArray[$j]['created_on'])) >=21 && date('d',strtotime($usersArray[$j]['created_on']))<=25)
								 {

								 $users[4]=$users[4]+$usersArray[$j]['users'];
								 $j++;
								 }
								 else if( date('d',strtotime($usersArray[$j]['created_on'])) >=26 && date('d',strtotime($usersArray[$j]['created_on']))<=$last_record)
								 {

								 $users[5]=$users[5]+$usersArray[$j]['users'];
								 $j++;
								 }	*/
									
									
							}

						}

					}
				}

				/*$subarr=array();
				 for($x=0;$x<sizeof($rows);$x++)
				 {
				 $address = array('total_sales'=> $rows[$x]['total_sales'],'sales' => $rows[$x]['sales'],'created_on' => $rows[$x]['created_on']);
				 array_push($subarr,$address);
				 }
				 for($k=0;$k<sizeof($dates);$k++)
				 {
				 if($sales[$k]==0 && $users[$k]==0)
				 {
				 $sales[$k]='';
				 $users[$k]='';
				 }
				 }*/
					
				$data_arr=array("sales"=>$sales,"users"=>$users,"dates"=>$dates,'datesarr'=>$datesarr);
				$data=array('success'=>'success','failure'=>'','data'=>$data_arr);
				echo json_encode($data);exit;
					


			}
			catch(Exception $e)
			{
				echo $e->getMessage();exit;
			}

		}

	}


}

