<?php
class TrendingchartapiController extends Zend_Controller_Action{

	public function init()
	{

	}
	public function indexAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		try {
				
			$kit_invoices_obj = new Gbc_Model_DbTable_Kitinvoices();
			$gb2_kit_invoices_obj = new Gbc_Model_DbTable_Gb2Kitinvoices();
			$invoices_obj = new Gbc_Model_DbTable_Invoices();
			$user_obj = new Gbc_Model_DbTable_Userinfo();
				
		/*	$salesResult = $invoices_obj->fetchAll($invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('i' =>'invoices'),array('round(sum(i.amtPaid),2) as btc','round(sum(i.contract_rate),2) as total_sales','count(i.invoice_id) as sales','i.created_on'))
			->where("contract_type in ('SHA','hardware','MS','ES') and created_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 6 DAY) AND ".new Zend_Db_Expr('NOW()')."")
			->group("day(created_on)")
			->order("created_on asc")
			);
		*/		

			$usersResult = $user_obj->fetchAll($user_obj->select()
			->setIntegrityCheck(false)
			->from(array('i' =>'user_info'),array('count(username) as users', 'created_on'))
			->where("created_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 6 DAY) AND ".new Zend_Db_Expr('NOW()')."")
			->group("day(created_on)")
			->order("created_on asc")
			);
			
			
			$mcapsalesResult = $kit_invoices_obj->fetchAll($kit_invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('i' =>'kit_invoices'),array('round(sum(i.amtPaidInMcap),2) as mcap','round(sum(i.contract_rate),2) as btc','count(i.invoice_id) as sales','i.creared_on as created_on'))
			->where("i.invoice_status=1 AND creared_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 6 DAY) AND ".new Zend_Db_Expr('NOW()')."")
			->group("day(creared_on)")
			->order("creared_on asc")
			);
			
			
			$ethsalesResult = $gb2_kit_invoices_obj->fetchAll($gb2_kit_invoices_obj->select()
			->setIntegrityCheck(false)
			->from(array('g' =>'gb2_kit_invoices'),array('round(sum(g.contract_rate),2) as mcap_eth','count(g.invoice_id) as sales','g.creared_on as created_on'))
			->where("g.invoice_status=1 AND creared_on BETWEEN DATE_SUB(".new Zend_Db_Expr('NOW()').", INTERVAL 6 DAY) AND ".new Zend_Db_Expr('NOW()')."")
			->group("day(creared_on)")
			->order("creared_on asc")
			);			
			
		//	echo "<pre>";
		//	print_r(($ethsalesResult));exit;
		//	print_r(($mcapsalesResult));exit;
			$rows = array();
			foreach ($mcapsalesResult as $row)
			{
				$rows[] = $row;
			}
			//$rows=$salesResult->toArray;
				
			//print_r($rows);exit;
				

			$usersArray = array();
			foreach ($usersResult as $row)
			{
				$usersArray[] = $row;
			}
			//	$usersArray=toArray->$usersResult[0];
			
			$mcapArray = array();
			foreach ($mcapsalesResult as $row)
			{
				$mcapArray[] = $row;
			}
			
			
			$ethArray = array();
			foreach ($ethsalesResult as $row)
			{
				$ethArray[] = $row;
			}			
			
			$i =0;
			$j =0;
			$k =0;
			$l =0;
			// var_dump($usersArray);
			$now = new DateTime( "6 days ago", new DateTimeZone('Asia/Kolkata'));
			$interval = new DateInterval( 'P1D'); // 1 Day interval
			$period = new DatePeriod( $now, $interval, 6); // 7 Days
			$dates = array();
			$datearr=array();
			foreach( $period as $day) {
			 $key = $day->format( 'd');
			 //$datearr[] = $day->format('d-m-Y');
			 $datesarr[] = date("jS M, Y", strtotime($day->format('d-m-Y')));
			 $dates[] = $key;
			}

		 if(!empty($dates)){

		 	foreach($dates as $date){
		 		//echo "here";
		 		if(!empty($rows)){
		 			if(!empty($rows[$i]['created_on']))
		 			{
			 			if($date == date('d',strtotime($rows[$i]['created_on']))){
			 				if($rows[$i]['created_on'] > '2017-03-27'){
								$sales[] = $rows[$i]['sales'];
								$sales[] = $rows[$i]['btc'];
							}else{
								$sales[] = $rows[$i]['sales'];
								$sales[] = $rows[$i]['btc'];
							}
			 				$i++;
			 			}else{
			 				$sales[] = '0';
			 			}
		 			}
		 			else
		 			{
		 				$sales[] = '0';
		 			}
		 		}
		 		else{
		 			$sales[] = '0';
		 		}

			//print_r($usersArray);
		 		if(!empty($usersArray)){
		 			if(!empty($usersArray[$j]['created_on']))
		 			{
			 			if($date == date('d',strtotime($usersArray[$j]['created_on']))){
			 				$users[] = $usersArray[$j]['users'];
			 				$j++;
			 			}else{
			 				$users[] = '0';
			 			}
		 			}
		 			else
		 			{
		 				$users[] = '0';
		 			}
		 		}else{
		 			$users[] = '0';
		 		}
				
			//Mcap Data
				
		 		if(!empty($mcapArray)){
		 			if(!empty($mcapArray[$k]['created_on']))
		 			{
			 			if($date == date('d',strtotime($mcapArray[$k]['created_on']))){
			 				$mcap[] = $mcapArray[$k]['mcap'];
							$sales[] = $rows[$i]['sales'];
							$sales[] = $rows[$i]['btc'];
			 				$k++;
			 			}else{
			 				$mcap[] = '0';
								$sales[] = '0';
			 			}
		 			}
		 			else
		 			{
		 				$mcap[] = '0';
							$sales[] = '0';
		 			}
		 		}else{
		 			$mcap[] = '0';
						$sales[] = '0';
		 		}
				
		 		if(!empty($ethArray)){
		 			if(!empty($ethArray[$l]['created_on']))
		 			{
			 			if($date == date('d',strtotime($ethArray[$l]['created_on']))){
			 				$mcap_eth[] = $ethArray[$l]['mcap_eth'];
							$sales[] = $ethArray[$l]['sales'];
			 				$l++;
			 			}else{
			 				$mcap_eth[] = '0';
							$sales[] = '0';
									
			 			}
		 			}
		 			else
		 			{
		 				$mcap_eth[] = '0';
														$sales[] = '0';						
		 			}
		 		}else{
		 			$mcap_eth[] = '0';
														$sales[] = '0';			
		 		}				
				
				
				
		 	}
		 	 
		 }

		 // $rows=json_decode(json_encode($rows));
		 //echo "<pre>";
		 //$rows= json_decode(json_encode($rows), true);
		 $subarr=array();
		 for($x=0;$x<sizeof($rows);$x++)
		 {
		 //	$address = array('total_sales'=> $rows[$x]['total_sales'],'sales' => $rows[$x]['sales'],'created_on' => $rows[$x]['created_on']);
		 	$address = array('total_sales'=> $rows[$x]['sales'],'sales' => $rows[$x]['sales'],'created_on' => $rows[$x]['created_on']);
		 	array_push($subarr,$address);
		 }

		 $data_arr=array("sales"=>$sales,"users"=>$users,"dates"=>$dates,"revenueByDay"=>$subarr,'datesarr'=>$datesarr);
		 
		 $subarr1=array();
			
		 for($x=0;$x<sizeof($rows);$x++)
		 {
			if(!empty($rows[$x]['created_on'] )){ 
			//	print_r($x);
			//	print_r($mcap_eth);
			//	print_r($ethArray[$x]);
		 		$data1 = array('d'=> date('Y-m-d',strtotime($rows[$x]['created_on'])),'a' => $rows[$x]['sales'],'b' => $usersArray[$x]['users'],'c' => $rows[$x]['btc'],'e' => round($mcapArray[$x]['mcap']/1000,2),'f' => round($mcap_eth[$x]/1000,2));
		 		array_push($subarr1,$data1);
			}
		 }			
		// $data=array('date'=>'2107-04-23','users'=>'1','sales'=>'10');
		// $data1=array('success'=>'success','failure'=>'','data'=>$data);
		 $data1=array($subarr1);
			
		 echo json_encode($subarr1);exit;



		}
		catch(Exception $e)
		{
			$data=array('success'=>'','failure'=>'');
			echo json_encode($e->getMessage());exit;
		}
	}
}