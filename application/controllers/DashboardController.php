<?php
class DashboardController extends Zend_Controller_Action{

    public function init(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Logout");

    }

    public function indexAction(){
        try{
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $this->_helper->layout()->setLayout("dashbord");
            $this->view->title="Gainbitcoin - Dashboard";
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $bin_net_det_obj = new Gbc_Model_DbTable_Binarynetworkdetail();
            $username=$authUserNamespace->user;

            /* for chart data */
            /*$url= BASE."/Trendingchartapi";
             $result=$common_obj->call_curl($url);
             $result=(array)json_decode($result,true);
             $this->view->result=$result;*/

            /* for network details */
            $count = 0;
            $users[]=$username;

            $url= BASE."/Contractdetails?username=".$username;
            $contractDetails=$common_obj->call_curl($url);
            $contractDetails=(array)json_decode($contractDetails,true);
            $this->view->contractDetails=$contractDetails;
				//print_r($contractDetails);
		/*
            $UserData =$common_obj-> checkNetworkDetails($username,2);
            if(!isset($UserData) && sizeof($UserData)<=0)
            {
                $UserData = $bin_net_det_obj->fetchAll($bin_net_det_obj->select()
                );
                if(!empty($UserData) && sizeof($UserData)>0)
                {
                    for($i=0;$i<$UserData;$i++)
                    {
                        $NetworkUsers[] = $UserData[$i]['username'];
                    }
                }

//                $all_users=$common_obj->leftRightDetails($users,$NetworkUsers,$count);
                $child_caching_db    = $common_obj->getChildCachingRedisInstance();

                $left_business  = 0;
                $right_business = 0;

                $left_children = $child_caching_db->get($username . "_left");
                $right_children = $child_caching_db->get($username . "_right");

                $contract_caching_db = $common_obj->getContractCachingRedisInstance();

                $left_business_keys  = $contract_caching_db->mget($contract_caching_db->keys($username . "_left_*"));
                $right_business_keys = $contract_caching_db->mget($contract_caching_db->keys($username . "_right_*"));

                foreach ($left_business_keys as $amount)
                    $left_business += $amount;

                foreach ($right_business_keys as $amount)
                    $right_business += $amount;

                $data = array(
                    "leftContracts"   => $left_business,
                    "rightContracts"  => $right_business,
                    "totalLeftUsers"  => strlen($left_children) ? count(explode(",", $left_children)) - 1 : 0,
                    "totalRightUsers" => strlen($right_children) ? count(explode(",", $right_children)) - 1 : 0
                );
                $all_users = $data;
            }
            else
            {
                $all_users = $UserData;
            }
			 $this->view->all_users=$all_users;
			
		*/	
			
			//	$UserBusinessCycleDetails=new Gbc_Model_DbTable_UserBusinessCycleDetails();
				$UserBusinessCycleDetails=new Gbc_Model_DbTable_NetworkBusinessDetails();
					

				$details=$UserBusinessCycleDetails->fetchRow($UserBusinessCycleDetails->select()
				->setIntegrityCheck(false)
				  ->from(array('details' =>'network_business_details'),array('round(SUM(details.left_business),1) as leftContracts',
																				'SUM(details.left_active_users) as activeLeftUsers'
																			   ,'SUM(details.left_inactive_users) as inactiveLeftUsers'
																				,'round(SUM(details.right_business),1) as rightContracts'
																				,'SUM(details.right_active_users) as activeRightUsers'
																				,'SUM(details.right_inactive_users) as inactiveRightUsers'
																																							   
																			   ))
				->where("details.username = ?",$username)
				
				)->toArray();
			
			
			$details['totalLeftUsers'] =  $details['activeLeftUsers'] + $details['inactiveLeftUsers'];
			$details['totalRightUsers'] =  $details['activeRightUsers'] + $details['inactiveRightUsers'];
		//	print_r($details);
		//	exit;
			
            $this->view->all_users=$details;
           
			
			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value','start','end'))
			->where("name =?",'business_cycle_date'));

			$this->view->business_cycle_date=$permissions_data;
			
			
			$direct_sales_obj=new Gbc_Model_DbTable_Userinfo();
			$direct_sales_data=$direct_sales_obj->fetchAll($direct_sales_obj->select()
			->setIntegrityCheck(false)
			->from('user_info',array('date(invoices.created_on) as created'))
			->joinLeft('invoices',"invoices.username = user_info.username",array('round(sum(invoices.contract_rate),2) as sale'))
			->where("sponsor_id =?",$username)
			->orWhere("ref_sponsor_id =?",$username)
			->Where("date(user_info.created_on) > (CURRENT_DATE() - INTERVAL 30 day) ")
			->group("date(invoices.created_on)"));

			if(!empty($direct_sales_data)){
				$direct_sales_data = $direct_sales_data->toArray();
			}
			
			
			
			$now = new DateTime( "30 days ago", new DateTimeZone('Asia/Kolkata'));
			$interval = new DateInterval( 'P1D'); // 1 Day interval
			$period = new DatePeriod( $now, $interval, 30); // 7 Days
			$direct_sales = array();
			$index = 1;
			foreach( $period as $day) {
				//print_r($day);
				$key = $day->format( 'Y-m-d');
				$direct_sales[$index]['date'] = $key;
				//$key = "2017-03-08" ;
			$searchkey = array_search($key,array_column($direct_sales_data,'created'));
				if($searchkey){
					$direct_sales[$index]['sale'] = $direct_sales_data[$searchkey]['sale'];
				}else{
					$direct_sales[$index]['sale'] = 0;
				}
				$index++;
			}
		
		//	print_r($direct_sales);
		//	exit;
			$this->view->direct_sales=$direct_sales;
			
            /*if(empty($filter))
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
                $enddate=date('d-m-Y')."%2023:59:59";

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
                    
                }*/
            //    echo "<pre>";
            //print_r($datearr);exit;
            //$this->view->datesarr=$datearr;
            //$this->view->usergraph=$data;

            //$this->view->userbusiness=$business_det;

            //$url= BASE."/Binarypaircountapi?username=".$username;
            //$bcount=$common_obj->call_curl($url);
            //$bcount=(array)json_decode($bcount,true);
            $bcount=array();
            $this->view->bcount=$bcount;


            /* category popup start
                 
            $user_category_obj = new Gbc_Model_DbTable_Usercategory();
            $lov_obj = new Gbc_Model_DbTable_Lov();
            $user = new Gbc_Model_DbTable_Userinfo();
            $ver_row = $user->fetchRow($user->select()
            ->where("username='".$authUserNamespace->user."'"));


            $cat_count_query = $lov_obj->fetchRow($lov_obj->select()
            ->from(array('lov'),array('count(name) as cat_count'))
            ->where("name='category' and status='1'"));

            if(!empty($cat_count_query) && sizeof($cat_count_query)>0)
            {
            $category_count=$cat_count_query->cat_count;
            }
            else
            {
            $category_count=0;
            }

            try {
            $user_category_data=array();
            $user_category_data=$user_category_obj->fetchAll($user_category_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('u'=>"user_category"),array('u.id'))
            ->joinLeft(array('l'=>'lov'),"l.id = u.category_id",array('l.value'))
            ->where("u.username='".$authUserNamespace->user."' and l.name='category' and l.status='1'")
            );

            }catch(Exception $e)
            {
            echo $e->getMessage();exit;
            }
            $category='';
            if(!empty($user_category_data))
            {
            if($category_count!=sizeof($user_category_data))
            {
            $enablePop=1;
            if(!empty($user_category_data) && sizeof($user_category_data)>0)
            {

            for($uc=0;$uc<sizeof($user_category_data);$uc++)
            {
            if($uc==0)
            {
            $category=$category.$user_category_data[$uc]['value'];
            }
            else
            {
            $category=$category."and ". $user_category_data[$uc]['value'];
            }
            }
            }
            }
            else
            {
            $enablePop=0;
            }
            }
            else
            {
            $enablePop=0;
            }
            $cat_pop=array('enablePop'=>$enablePop,'category'=>$category);
            //    print_r($cat_pop);exit;
            /* category popup finish */
            /*    $this->view->cat_pop=$cat_pop; */
			
	        $user = new Gbc_Model_DbTable_Userinfo();

 		  	 $result=$user->fetchAll($user->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"user_info"),array('A.username'))
			->order("created_on DESC")	
			->limit(20));
			
			$this->view->newcount=$result;			
			

        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

    }
    public function savecontactAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        

        $common_obj = new Gbc_Model_Custom_CommonFunc();
        //$common_obj->cleanQueryParameter(($_POST['username']));

        //$name=trim($_POST['name']);
        $name=$common_obj->cleanQueryParameter(($_POST['name']));
        //$email=trim($_POST['email_address']);
        $email=$common_obj->cleanQueryParameter(($_POST['email_address']));

        //$mobile=trim($_POST['mobile']);
        $mobile=$common_obj->cleanQueryParameter(($_POST['mobile']));

        //$captcha=trim($_POST['captcha']);
        $captcha=$common_obj->cleanQueryParameter(($_POST['captcha']));
        //$ol_captcha=trim($_POST['ol_captcha']);
        //$ol_captcha=$common_obj->cleanQueryParameter(($_POST['ol_captcha']));

        //$country=trim($_POST['countryCode']);
        $country=$common_obj->cleanQueryParameter(($_POST['countryCode']));
        //    $username=trim($_POST['username']);
        $username=$common_obj->cleanQueryParameter(($_POST['username']));

        if(($name!='')|| ($email!='') || ($country!='') || ($mobile!='')|| ($captcha!=''))
        {
            $user = new Gbc_Model_DbTable_Userinfo();
            $check_user = $user->fetchRow($user->select()
            ->where("username=?",$authUserNamespace->user));

            /*if($captcha!==$ol_captcha)
             {
                $msg = "The CAPTCHA wasn't entered correctly. Try it again.";
                echo $msg;
                exit;
                }*/
            if(empty($check_user) || sizeof($check_user)<=0)
            {
                $msg = "You are not authorised user...";
                echo $msg;
                exit;
            }
            else
            {
                $upd_profile_arr=array('name'=>$name,'email_address'=>$email,'country'=>$country,'phone'=>$mobile,'updated_on'=>new Zend_Db_Expr('NOW()'),'isVerified'=>'1');
                $upd_profile=$user->update($upd_profile_arr,$DB->quoteInto("username=?",$username));
                if(!empty($upd_profile))
                {
                    echo "Profile contact successfully added..";exit;
                }
            }

        }
    }
    public function logoutAction()
    {

        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $username=$authUserNamespace->user;
        Zend_Session::destroy(true,true);
        $upd_arr=array('session_id'=>'');
        $upd_qry=$user_obj->update($upd_arr,$DB->quoteInto("username=?",$username));
        if (!empty($_COOKIE['remember']) && isset($_COOKIE['remember'])){

            setcookie('remember', '', time() - 3600, '/');
            setcookie ("remember", "", time() - 3600);
        }
        //    unset($_COOKIE['remember']);
            
        $this->_redirect("/Login");
        //echo "success";exit;
    }

   public function getcountryAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_POST['username']));


		//$code=$_POST['code'];
		$code=$common_obj->cleanQueryParameter(($_POST['code']));
		//$type=$_POST['type'];
		$type=$common_obj->cleanQueryParameter(($_POST['type']));

		$country_obj=new Gbc_Model_DbTable_Countries();
		if($type=='country')
		{
			$country_res=$country_obj->fetchRow($country_obj->select()
			->where("country=?",$code)
			->order('ccode ASC')
			);
			if(!empty($country_res) && sizeof($country_res)>0)
			{
				$ccode =  $country_res->ccode;
				$data=array('success'=>$ccode,'failure'=>'');
							echo json_encode($data);exit;
			}
			else
			{
//				echo "fail";exit;
				$data=array('success'=>'','failure'=>'fail');
							echo json_encode($data);exit;
			}
		}
		else
		{
			$country_res=$country_obj->fetchRow($country_obj->select()
			->where("ccode=?",$code)
			->order('country ASC')
			);
			if(!empty($country_res) && sizeof($country_res)>0)
			{
				$ccode =  $country_res->country;
				$data=array('success'=>$ccode,'failure'=>'');
							echo json_encode($data);exit;
			}
			else
			{
//				echo "fail";exit;
				$data=array('success'=>'','failure'=>'fail');
				echo json_encode($data);exit;
			}
		}
	}

    public function savecontAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        //$common_obj->cleanQueryParameter(($_POST['username']));


       // $rdochk=$common_obj->cleanQueryParameter(($_POST['rdochk']));

        $user_obj=new Gbc_Model_DbTable_Userinfo();
        //$name=$_POST['name'];
        $name=$common_obj->cleanQueryParameter(($_POST['name']));
        //$email=$_POST['email_address'];
        $email=$common_obj->cleanQueryParameter(($_POST['email_address']));

        //$mobile=$_POST['mobile'];
        $mobile=$common_obj->cleanQueryParameter(($_POST['mobile']));
        //$country=$_POST['countryCode'];
        $country=$common_obj->cleanQueryParameter(($_POST['countryCode']));
        //$country_name=$_POST['country_name'];
        $country_name=$common_obj->cleanQueryParameter(($_POST['country_name']));
        //$captcha=$_POST['captcha'];
        $captcha=$common_obj->cleanQueryParameter(($_POST['captcha']));
        //$og_captcha=$_POST['ol_captcha'];
        $og_captcha=$common_obj->cleanQueryParameter(($_POST['ol_captcha']));
        $username=$authUserNamespace->user;
        $check_user=$common_obj ->getUserInfo($username);
        //echo $rdochk;exit;


//echo  $authUserNamespace->code;
//echo $og_captcha;    
//echo " ".$captcha;

      //  if(($name!='')|| ($email!='') || ($country!='') || ($mobile!='')|| ($captcha!='')||($rdochk!=''))
       
	if(($name!='')|| ($email!='') || ($country!='') || ($mobile!='')|| ($captcha!=''))
	 {
//            if($captcha!=$authUserNamespace->code)

//            {
//                $msg = "The reCAPTCHA wasn't entered correctly. Go back and try it again.";
//                echo $msg;exit;
//            }
//            else
if(empty($check_user) || sizeof($check_user)<=0)
            {
                $msg = "You are not authorised user...";
                echo $msg;exit;
            }
            else
            {
                $updated_on=date('Y-m-d h:i:s');
     //           $upd_arr=array('isVerified'=>1,'country'=>$country,'country_name'=>$country_name,'comm_email'=>$email,'name'=>$name,'phone'=>$mobile,'updated_on'=>$updated_on,'referral_flag'=>$rdochk);
       
	$upd_arr=array('isVerified'=>1,'country'=>$country,'country_name'=>$country_name,'comm_email'=>$email,'name'=>$name,'phone'=>$mobile,'updated_on'=>$updated_on);
         $upd_stmnt=$user_obj->update($upd_arr,$DB->quoteInto("username= ?",$username));
                echo "Updated successfully";exit;
            }


        }
        else
        {
            $msg = "You are not authorised user...";
            echo $msg;exit;
        }
    }
    public function refreshnetworkAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $bin_net_det_obj = new Gbc_Model_DbTable_Binarynetworkdetail();
        //$username=$common_obj->cleanQueryParameter($_POST['user']);
       // $token = $common_obj->cleanQueryParameter(($_POST['token']));
        //if(isset($authUserNamespace->user) && isset($authUserNamespace->token) && $authUserNamespace->token==$token){
        if(isset($authUserNamespace->user))
        {
            $username = $authUserNamespace->user;
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
                
            //$UserData = $bin_net_det_obj->fetchAll($bin_net_det_obj->select()
            //);
            $users[]=$username;
            $count = 0;
            /*if(!empty($UserData) && sizeof($UserData)>0)
             {
             for($i=0;$i<$UserData;$i++)
             {
             $NetworkUsers[] = $UserData[$i]['username'];
             }
             }*/
            $NetworkUsers=array();
//            $all_users=$common_obj->leftRightDetails($users,$NetworkUsers,$count);

            $child_caching_db    = $common_obj->getChildCachingRedisInstance();

            $left_business  = 0;
            $right_business = 0;

            $left_children = $child_caching_db->get($username . "_left");
            $right_children = $child_caching_db->get($username . "_right");

            $contract_caching_db = $common_obj->getContractCachingRedisInstance();

            $left_business_keys  = $contract_caching_db->mget($contract_caching_db->keys($username . "_left_*"));
            $right_business_keys = $contract_caching_db->mget($contract_caching_db->keys($username . "_right_*"));

            foreach ($left_business_keys as $amount)
                $left_business += $amount;

            foreach ($right_business_keys as $amount)
                $right_business += $amount;

            $data = array(
                "leftContracts"   => $left_business,
                "rightContracts"  => $right_business,
                "totalLeftUsers"  => strlen($left_children) ? count(explode(",", $left_children)) - 1 : 0,
                "totalRightUsers" => strlen($right_children) ? count(explode(",", $right_children)) - 1 : 0
            );
            $all_users = $data;
            if(empty($all_users) || sizeof($all_users)<=0)
            {
                $all_users=array();
            }
            echo json_encode($all_users);exit;
        }
//        else
//        {
//            $data=array('success'=>'','failure'=>'Invalide request found');
//            echo json_encode($data);exit;    
//        }
    }
    public function getbinarypairAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $token = $common_obj->cleanQueryParameter(($_POST['token']));
//        if(isset($authUserNamespace->user) && isset($authUserNamespace->token) && $authUserNamespace->token==$token){
        if(isset($authUserNamespace->user))
        {
            try
            {
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

                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                    
                $User_info_obj=new Gbc_Model_DbTable_Userinfo();
                $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
                $special_perm_obj=new Gbc_Model_DbTable_SpecialPermission();
                $special_off_obj=new Gbc_Model_DbTable_Specialoffer();
                $invoices_obj = new Gbc_Model_DbTable_Invoices();

                $username = $authUserNamespace->user;
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

                $UserActivationTime=$invoices_obj->fetchRow($invoices_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('invoices'=>"invoices"),array('created_on'))
                ->where("1=1")
                ->where("invoices.username = ?",$username)
                ->limit(1)
                );


                $ActivationTime =  $UserActivationTime->created_on;

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

                $arr=array("success"=>"success","failure"=>"","offer_data"=>$offer_arr,"offer_flag"=>'1',"quarterly_offer_flag"=>$Permissions['quarterly_offers'],"no_of_binary_pairs"=>$paircount,'current_achieved'=>$achieved,'next_achievable'=>$Achievable);
                echo json_encode($arr);exit;

            }

            catch(Exception $e)
            {
                $db->rollBack();
                echo $e->getMessage();exit;
            }
        }
//        else
//        {
//            $arr=array('success'=>'','failure'=>'Invalid Request found');
//            echo json_encode($arr);exit;
//        }
    }
}

