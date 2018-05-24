<?php
class LeftrightcronController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	}
	public function indexAction()
	{
		$bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$user = new Gbc_Model_DbTable_Userinfo();

		$UserData = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
		);
		if(!empty($UserData) && sizeof($UserData)>0)
		{
			for($i=0;$i<sizeof($UserData);$i++)
			{
				$NetworkUsers[] = $UserData[$i]['username'];
			}
		}
		$date = date('d');
		$time = date('H:i:s');
		$count = 0;

		if(($_POST['users'] == 'all') || (($time < date('H:i:s',strtotime('02:59:00'))))){
			$cronName = "left_right_details_all";
		}else{
			$cronName = "left_right_details";
		}
		$start = date('Y-m-d H:i:s');
		$common_obj->updateCronStatus($cronName,$start,'');
		$BigUserLimit = 5000;
		if(($_POST['users'] == 'all') || (($time < date('H:i:s',strtotime('02:59:00'))))){

			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->where("binaryUser IS NOT NULL and binary_direct =0"));

			$userCount=$userCount->userCount;
			// $userCount = mysql_query("select count(username) as userCount from user_info where binaryUser IS NOT NULL");

			$EndLimit = 9999;
		}else{
			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->joinLeft(array('b'=>'binary_network_details'),"b.username = u.username",array())
			->where("user_info.binaryUser IS NOT NULL and (binary_network_details.left_users >=$BigUserLimit or binary_network_details.right_users >=$BigUserLimit) and binary_network_details.status = '1'"));

			$EndLimit = ($userCount->userCount) - 1;
		}
		while($count < $EndLimit){
			$limit = 100;
			$offset = !empty($count)?$count:'0';

			if(($_POST['users'] == 'all') || (($time < date('H:i:s',strtotime('02:59:00'))))){
				$Users = $common_obj->getBinaryUsersForCron($limit,$offset,1);

			}
			else{

				$BigUsers = $user->fetchAll($user->select()
				->setIntegrityCheck(false)
				->from(array('u' =>'user_info'),array('count(username) as userCount'))
				->joinLeft(array('b'=>'binary_network_details'),"b.username = u.username",array())
				->where("user_info.binaryUser IS NOT NULL and (binary_network_details.left_users >=$BigUserLimit or binary_network_details.right_users >=$BigUserLimit) and binary_network_details.status = '1'")
				->limit($limit,$offset)
				);

				$res = array();
				for($i=0;$i<sizeof($BigUsers);$i++)
				$Users[] = $BigUsers[$i]['username'];
			}
			if (($key = array_search('admin', $Users)) !== false) {
				unset($Users[$key]);
			}
			if (($key = array_search('amitsabnetwork', $Users)) !== false) {
				unset($Users[$key]);
			}
			if(($_POST['users'] == 'all') || (($time < date('H:i:s',strtotime('02:59:00'))))){
				if(!empty($Users)){
					$count = $common_obj->leftRightDetails($Users,$NetworkUsers,$count,'all');
				}else{
					$count = $userCount['userCount'];
				}
			}else{
				$count = $common_obj->leftRightDetails($Users,$NetworkUsers,$count);
			}
		}
		
			if($count >= $EndLimit){
			
			
			// echo "1) ".basename($_SERVER['PHP_SELF'], ".php").PHP_EOL;
			// $cronName = basename($_SERVER['PHP_SELF'], ".php");

			// if(($_POST['users'] == 'all') || (($date == 01 || $date == 16) && ($time < date('H:i:s',strtotime('02:00:00'))))){
			if(($_POST['users'] == 'all') || (($time < date('H:i:s',strtotime('02:59:00'))))){
				// echo "here";
				$cronName = "left_right_details_all";
			}else{
				$cronName = "left_right_details";
			}
			// echo $time;
			echo $cronName;
			$end = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,'',$end);
		}
	}
}

