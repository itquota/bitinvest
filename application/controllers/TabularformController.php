<?php
class TabularformController extends Zend_Controller_Action{

	public function init(){


	}

	public function indexAction(){


		$this->_helper->layout()->setLayout("dashbord");//dashboard

		$common_obj = new Gbc_Model_Custom_CommonFunc();
		//$common_obj->cleanQueryParameter(($_REQUEST['username']));

		$db = Zend_Db_Table::getDefaultAdapter();
		$DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db->beginTransaction();
		$bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
		$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
		$Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
		//$username=$_REQUEST['username'];
		$date = date('d');
		$time = date('H:i:s');
		$count = 0;

		$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));

		if(!empty($_REQUEST['username']) && $_REQUEST['username']){
				
			$username = $_REQUEST['username'];
		}else{
			$username = $authUserNamespace->user;
		}

		$result1=$bin_user_ref->fetchAll($bin_user_ref->select()
			->where("parent_username = ?",$username)
			->where("parent_id <> ?",0)
			->order("child_position ASC"));

			$userDetails=array();
			$childArray = array();
			if(!empty($result1) && isset($result1) && sizeof($result1)>0)
			{

				for($i=0;$i<sizeof($result1);$i++)
				{
					$childArray[$i] = $result1[$i]['username'];

					$child_position[$i] = $result1[$i]['child_position'];

				}
					
			 $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($childArray[0],$childs_first);
			 $childs_first='';
			 $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($childArray[0],$childs_first);

			 $childs_second='';
			 $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($childArray[1],$childs_second);

			 	
			 $childs_first=array_merge(array($childArray[0]),array_filter($childs_first));

			 	
			 $childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
			 
						 if(!empty($childArray[0]) || !empty($childArray[1]) ){
			 	if($child_position[0] != "R"){
			 		if(!empty($childArray[0])){
			 			$LeftpairsDetail =$Gbc_Model_Custom_func_obj-> getArrayforuser($childArray[0],$startdate,$enddate);
			 		}
			 		//	echo "<pre>";
			 		//	print_r($LeftpairsDetail);exit;
			 		if(!empty($childArray[1])){
			 			$RightpairsDetail =$Gbc_Model_Custom_func_obj->getArrayforuser($childArray[1],$startdate,$enddate);
			 		}
			 	}
			
			 	else
			 	{
			 		if(!empty($childArray[0])){
			 			$RightpairsDetail =$Gbc_Model_Custom_func_obj->getArrayforuser($childArray[0],$startdate,$enddate);
			 		}
			 		 
			 		if(!empty($childArray[1])){
			 			$LeftpairsDetail =$Gbc_Model_Custom_func_obj->getArrayforuser($childArray[1],$startdate,$enddate);
			 		}
			 	}
						 $users[]= $username;

			$UserData =$Gbc_Model_Custom_func_obj-> checkNetworkDetails($username,2);

			$all_user=array();
			if(isset($UserData) && sizeof($UserData)>0){
				// echo "yes";
				$all_user = $UserData;
				//$all_user = $Gbc_Model_Custom_func_obj->leftRightDetails($users,$NetworkUsers,$count);
			}else{
				$UserData = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
				);
				if(!empty($UserData) && sizeof($UserData)>0)
				{
					for($i=0;$i<sizeof($UserData);$i++)
					{
						$NetworkUsers[] = $UserData[$i]['username'];
					}
				}

				$all_user = $Gbc_Model_Custom_func_obj->leftRightDetails($users,$NetworkUsers,$count);
			}
			//print_r($all_user);exit;
		}
		
		$bigarr=array();
		if(!empty($all_user) && sizeof($all_user)>0)
		{
			$dataarr=array('leftContracts'=>$all_user['leftContracts'],'totalLeftUsers'=>$all_user['totalLeftUsers'],'activeLeftUsers'=>$all_user['activeLeftUsers'],'inactiveLeftUsers'=>$all_user['inactiveLeftUsers'],'rightContracts'=>$all_user['rightContracts'],'totalRightUsers'=>$all_user['totalRightUsers'],'activeRightUsers'=>$all_user['activeRightUsers'],'inactiveRightUsers'=>$all_user['inactiveRightUsers']);
		}
		else
		{
			$dataarr=array();
		}
		
			 	/*echo "<pre>";
			 	print_r($LeftpairsDetail);exit;*/
				$succ['data']['userdata']=$dataarr;
				$this->view->binarydata=$succ['data']['binarydata'];
				$this->view->binarydata=$LeftpairsDetail; 
				//echo "<pre>";

					
						 }
			 	
			
		
	}
	
	}

}

