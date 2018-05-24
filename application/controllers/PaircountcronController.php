<?php

class PaircountcronController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
		try {
			$user = new Gbc_Model_DbTable_Userinfo();
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$cronName = "pairs_count";
			$start = date('Y-m-d H:i:s');

			$common_obj->updateCronStatus($cronName,$start,'');

			$date = date('Y-m-d');
			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->where("binaryUser IS NOT NULL and created_on < '$date'"));

			$count = 0;
			$CurrentMonth = date('m');
			$FirstQuarter = array(0,1,2);
			$SecondQuarter = array(3,4,5);
			$ThirdQuarter = array(6,7,8);
			$FourthQuarter = array(9,10,11);
			if(in_array($CurrentMonth-1,$FirstQuarter)){
				$FirstQMonth =  date('2016-01');
				$LastQMonth =   date('2016-03');
			}else if(in_array($CurrentMonth-1,$SecondQuarter)){
				$FirstQMonth =  date('2016-04');
				$LastQMonth =   date('2016-06');
			}else if(in_array($CurrentMonth-1,$ThirdQuarter)){
				$FirstQMonth =  date('2016-07');
				$LastQMonth =   date('2016-09');
			}else{
				$FirstQMonth =  date('2016-10');
				$LastQMonth =   date('2016-12');
			}
			$month_ini = new DateTime("first day of last month");
			$month_end = new DateTime("last day of last month");

			$FirstDayStartMonth = $month_ini->format('Y-m-d'); // 2012-02-01
			$LastDayLastMonth = $month_end->format('Y-m-d');
			$LastMonth = $month_end->format('Y-m');
			$start_date = !empty($_GET['start_date'])?$_GET['start_date']:$FirstDayStartMonth;
			$end_date = !empty($_GET['end_date'])?$_GET['end_date']:$LastDayLastMonth;
			$month = !empty($_GET['month'])?$_GET['month']:$LastMonth;

			while($count < ($userCount->userCount - 1)){
				$limit = 100;
				$offset = !empty($count)?$count:'0';
				$Users = $common_obj->getBinaryUsersForCron($limit,$offset);
				$CountPairs = $common_obj-> dailyPairs($Users,$count);
				$count = $CountPairs;
			}
			if($count >= $userCount->userCount - 1){

				$cronName = "pairs_count";
				// updateCronStatus($cronName);
				$end = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}
			echo "success";exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}

}