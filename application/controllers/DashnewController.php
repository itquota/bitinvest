<?php
class DashnewController extends Zend_Controller_Action{

	public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}

	public function indexAction(){
		try{
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$this->_helper->layout()->setLayout("testdashbord");
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$username=$authUserNamespace->user;

			$url= BASE."/Trendingchartapi";
			$result=$common_obj->call_curl($url);
			$result=(array)json_decode($result,true);
			$this->view->result=$result;
			//echo "<pre>";
			//print_r($result);exit;
			$url= BASE."/Contractdetails?username=".$username;
			$contractDetails=$common_obj->call_curl($url);
			$contractDetails=(array)json_decode($contractDetails,true);
			$this->view->contractDetails=$contractDetails;

			if(empty($_POST['filter']))
			{
				$i =0;
				$j =0;
				// var_dump($usersArray);
				$now = new DateTime( "6 days ago", new DateTimeZone('Asia/Kolkata'));
				$interval = new DateInterval( 'P1D'); // 1 Day interval
				$period = new DatePeriod( $now, $interval, 6); // 7 Days
				$dates = array();
				$datearr=array();
				foreach( $period as $day) {
				 $key = $day->format( 'd');
				 // $datearr[] = $day->format('d-m-Y');
				 $datearr[] = date("jS M, Y", strtotime($day->format('d-m-Y')));
				 $dates[] = $key;
				}
					


				$startdate= date('d-m-Y', strtotime('-6 days'));
				$enddate=date('d-m-Y')." 23:59:59";

				$data=array('0','0','0','0','0','0','0');
				$business_det=array();
				$url= BASE."/Userbusinessdetailsapi?username=".$username."&startdate=".$startdate."&enddate=".$enddate;
				//echo $url;exit;
				$result=$common_obj->call_curl($url);
				$result=(array)json_decode($result,true);
				//echo "<pre>";
				//print_r($result);exit;
				for($i=0;$i<sizeof( $result['data']['pairDetails']);$i++)
				{
					$created_date= $result['data']['pairDetails'][$i]['created_on'];
					$pieces = explode(" ", $created_date);
					$day_data_arr=explode("-", $pieces[0]);
					$day_data=$day_data_arr[2];
						
					if (in_array($day_data,$dates))
					{
						$index=array_search($day_data,$dates);
						$data[$index]=$data[$i]+$result['data']['pairDetails'][$i]['ContractPrice'];
					}
					//print_r($data);exit;
						
						
				}
				for($j=0;$j<sizeof($data);$j++)
				{
					$k=0;
					if(!empty($data[$j]) && $data[$j]!=0)
					{
						$business_det[$k]['date']=date($dates[$j].'-m-Y');
						$business_det[$k]['contract']=$data[$j];
					}
				}
					
			}
			//	echo "<pre>";
			//print_r($datearr);exit;
			$this->view->datesarr=$datearr;
			$this->view->usergraph=$data;
				
			$this->view->userbusiness=$business_det;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;
		}

	}
	public function savecontactAction()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$name=trim($_POST['name']);
		$email=trim($_POST['email_address']);
		$mobile=trim($_POST['mobile']);
		$captcha=trim($_POST['captcha']);
		$ol_captcha=trim($_POST['ol_captcha']);
		$country=trim($_POST['countryCode']);
		$username=trim($_POST['username']);

		if(($name!='')|| ($email!='') || ($country!='') || ($mobile!='')|| ($captcha!=''))
		{
			$user = new Gbc_Model_DbTable_Userinfo();
			$check_user = $user->fetchRow($user->select()
			->where("username='".$authUserNamespace->user."'"));

			if($captcha!==$ol_captcha)
			{
				$msg = "The CAPTCHA wasn't entered correctly. Try it again.";
				echo $msg;
				exit;
			}
			elseif(empty($check_user) || sizeof($check_user)<=0)
			{
				$msg = "You are not authorised user...";
				echo $msg;
				exit;
			}
			else
			{
				$upd_profile_arr=array('name'=>$name,'email_address'=>$email,'country'=>$country,'phone'=>$mobile,'updated_on'=>new Zend_Db_Expr('NOW()'),'isVerified'=>'1');
				$upd_profile=$user->update($upd_profile_arr,"username='".$username."'");
				if(!empty($upd_profile))
				{
					echo "Profile contact successfully added..";exit;
				}
			}

		}
	}
}