<?php

class BinaryuserwklycronController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$member_obj=new Gbc_Model_DbTable_Membershiplist();
		$Binaryuserreferenceobj = new Gbc_Model_DbTable_Binaryuserreferences();
		$bin_user_wkl_income=new Gbc_Model_DbTable_Binaryuserwelcome();
		$invoices_obj = new Gbc_Model_DbTable_Invoices();
		$date = !empty($_POST['CurrentDate'])?"".$_POST['CurrentDate']."":date('Y-m-d');
		$user = new Gbc_Model_DbTable_Userinfo();
		$cronName = "binaryUserwklyCron";
		$start = date('Y-m-d H:i:s');
		$common_obj->updateCronStatus($cronName,$start,'');


		$userCount = $user->fetchRow($user->select()
		->setIntegrityCheck(false)
		->from(array('u' =>'user_info'),array('count(username) as userCount'))
		->where("binaryUser IS NOT NULL and created_on < '$date'"));

		$count = 0;

		while($count < ($userCount->userCount - 1)){
			$limit = 500;
			$offset = !empty($count)?$count:'0';
			$Users = $common_obj->getBinaryUsersForCron($limit,$offset,'',$date);
			if (($key = array_search('admin', $Users)) !== false) {
				unset($Users[$key]);
			}
			if (($key = array_search('amitsabnetwork', $Users)) !== false) {
				unset($Users[$key]);
			}
			if(!empty($_GET['usr']) && $_GET['usr']){
				$Users = array($_GET['usr']);
			}
			//$binary_level=50;
			foreach($Users as $username){
				$childArray = array();
				$i = 0;$j=0;
				$refArray = $Binaryuserreferenceobj->fetchAll($Binaryuserreferenceobj->select()
				->where("parent_username = '".$username."' and parent_id<>0")
				->order("child_position ASC")
				);


				$membership_type_qry=$common_obj->getMembership($username);
				if(empty($membership_type_qry) || sizeof($membership_type_qry)<=0)
				{
					$membership_type_qry = $member_obj->fetchRow($member_obj->select()
					->order("id asc")
					->limit(1)
					);
				}
				$membership_type=$membership_type_qry->membership_type;
				$binary_matching_multiple=$membership_type_qry->binary_matching_multiple;
				$binary_matching=$membership_type_qry->binary_matching;
				$binary_capping=$membership_type_qry->binary_capping;
				$membership_type=$membership_type_qry->membership_type;
				$binary_level=$membership_type_qry->level_max;


				for($i=0;$i<sizeof($refArray);$i++)
				{
					$childArray[$i]= $refArray[$i]['username'];

				}
				if(!empty($childArray[0]) && !empty($childArray[1])){
					$childs_first=array();
					$common_obj->getAllChildforBinaryNew($childArray[0],$childs_first,$binary_level,$date);

					$childs_second=array();
					$common_obj->getAllChildforBinaryNew($childArray[1],$childs_second,$binary_level,$date);

					$childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
					$childs_second=array_merge(array($childArray[1]),array_filter($childs_second));


					if(!empty($childs_first)){
						$totalContractFirst=$common_obj->getTotlaDownlineContract($childs_first);
					}else{
						$totalContractFirst=0;
					}
					if(!empty($childs_second)){
						$totalContractSecnd=$common_obj->getTotlaDownlineContract($childs_second);
					}else{
						$totalContractSecnd=0;
					}

					if(empty($totalContractFirst) || $totalContractFirst=='')
					{
						$totalContractFirst=0;
					}
					if(empty($totalContractSecnd) || $totalContractSecnd=='')
					{
						$totalContractSecnd=0;
					}

					$row=$invoices_obj->fetchRow($invoices_obj->select()
					->setIntegrityCheck(false)
					->from(array('i'=>"invoices"),array('sum(contract_rate) as total_own'))
					->where("username='".($username)."' AND invoice_status='1' AND contract_type IN ('SHA','hardware') and created_on < '$date'")
					->group("username")
					);

					if(!empty($row) && sizeof($row)>0)
					{
						$totalOwn_contract=$row->total_own;
					}
					else
					{
						$totalOwn_contract=0;
					}
					$all_user[0]['username']=$childArray[0];
					$all_user[0]['contact']=$totalContractFirst;
					$all_user[1]['username']=$childArray[1];
					$all_user[1]['contact']=$totalContractSecnd;
					$contact = array_column($all_user, 'contact');

					$result = $bin_user_wkl_income->fetchAll($bin_user_wkl_income->select()
					->setIntegrityCheck(false)
					->where("parent_username='".($username)."' AND status='1'"));

					if(!empty($result) && sizeof($result)>0)
					{
						$row = array();
						$new_users=array();
						$i=0;
							

						for($j=0;$j<sizeof($result);$j++)
						{
							$row[$i]['username']=$result[$j]['username'];
							$row[$i]['parent_username']=$result[$j]['parent_username'];
							$row[$i]['last_total']=$result[$j]['last_total'];
							$row[$i]['current_toal']=$result[$j]['current_toal'];
							$row[$i]['less_value']=$result[$j]['less_value'];
							$row[$i]['total_earning']=$result[$j]['total_earning'];
							$row[$i]['old_total']=$result[$j]['old_total'];
							$row[$i]['created_on']=$result[$j]['created_on'];
							$row[$i]['status']=$result[$j]['status'];
							$row[$i]['isDaily']=$result[$j]['isDaily'];
							$row[$i]['updated_on']=$result[$j]['updated_on'];

						}
						foreach($all_user as $user){
							$key = array_search($user['username'],array_column($row,'username'));
							// var_dump($key);
							// echo "<br>";
							if(is_numeric($key)){
								$current_contact=0;
								// if(($user['contact'] > $row[$key]['last_total']) && $row[$key]['less_value']>=0){
								if(($user['contact'] > $row[$key]['last_total'])){
									$new_contact=round($user['contact']-$row[$key]['old_total'],8);
									$current_contact=$row[$key]['current_toal']+$new_contact;
									$new_users[$i]['username']=$user['username'];
									$new_users[$i]['contact']=$current_contact;
									$new_users[$i]['old_total']=$user['contact'];
								}else{
									$new_users[$i]['username']=$user['username'];
									$new_users[$i]['contact']=$row[$key]['current_toal'];
									$new_users[$i]['old_total']=$user['contact'];
								}
							}else{
								$new_users[$i]['username']=$user['username'];
								$new_users[$i]['contact']=$user['contact'];
								$new_users[$i]['old_total']=0;
							}
							$i++;
						}
						$top_user = $bin_user_wkl_income->fetchAll($bin_user_wkl_income->select()
						->setIntegrityCheck(false)
						->where("parent_username='' AND username='$username' AND status='1'"));
						$update_top=array('status'=>'0');
						$update_top_qry=$bin_user_wkl_income->update($update_top,"parent_username='' AND username='$username' AND date(created_on)<>'$date'");

						$update_lower_user=array('status'=>'0');
						$update_lower_user_qry=$bin_user_wkl_income->update($update_lower_user,"parent_username='$username' AND date(created_on)<>'$date'");

						$contact = array_column($new_users, 'contact');
						$lower_user = $new_users[array_search(min($contact), $contact)];

						$less_amount=$lower_user['contact'];
						$new_total_top_user_contact= $totalOwn_contract;
						/*
						 if($totalOwn_contract < 0.5){
							$top_earning=$lower_user['contact']*(4/100);
							}else{
							$top_earning=$lower_user['contact']*(8/100);
							}


							if($top_earning > $totalOwn_contract*15 ){
							$top_earning=$totalOwn_contract*15;
							}else if($top_earning >150){
							$top_earning=150;
							} */

						$top_earning= fmod($less_amount, $binary_matching_multiple);
						$top_earning==(($top_earning/100) * $binary_matching);

						if($top_earning>$binary_capping)
						{
							$top_earning = $binary_capping;
						}

						$top_earning=number_format($top_earning,8,'.','');

						$insert_data=array('username'=>$username,'last_total'=>$new_total_top_user_contact,'total_earning'=>$top_earning,'status'=>'1');
						$insert_qry=$bin_user_wkl_income->insert($insert_data);

						//$insert_temp_data=array('username'=>$username,'last_total'=>$new_total_top_user_contact,'total_earning'=>$top_earning,'status'=>'1');
						//$insert_temp_qry=$bin_user_wkl_income_temp->insert($insert_temp_data);

						foreach($new_users as $user){
							if($user['username']==$lower_user['username']){
								$current_contact=0;
							}else{
								$current_contact= round(($user['contact']-$less_amount),8);
							}
							$user['contact'] = number_format($user['contact'],8,'.','');
							$current_contact = number_format($current_contact,8,'.','');
							$less_amount = number_format($less_amount,8,'.','');
							$user['old_total'] = number_format($user['old_total'],8,'.','');

							$insert_data1=array('username'=>$user['username'],'parent_username'=>$username,'last_total'=>$user['contact'],'current_toal'=>$current_contact,'less_value'=>$less_amount,'old_total'=>$user['old_total'],'status'=>'1');
							$insert_qry1=$bin_user_wkl_income->insert($insert_data1);

							//$insert_temp_data1=array('username'=>$user['username'],'parent_username'=>$username,'last_total'=>$user['contact'],'current_toal'=>$current_contact,'less_value'=>$less_amount,'old_total'=>$user['old_total'],'status'=>'1');
							//$insert_temp_qry1=$bin_user_wkl_income_temp->insert($insert_temp_data1);
						}

					}
					else
					{
						$lower_user = $all_user[array_search(min($contact), $contact)];
						$less_amount=$lower_user['contact'];
							
						/*			if($totalOwn_contract < 0.5){
							$top_earning=$lower_user['contact']*(4/100);
							}else{
							$top_earning=$lower_user['contact']*(8/100);
							}
								
							if($top_earning > $totalOwn_contract*15 )
							{
							$top_earning=$totalOwn_contract*15;
							}
							if($top_earning >150)
							{
							$top_earning=150;
							}*/

						$top_earning= fmod($less_amount, $binary_matching_multiple);
						$top_earning==(($top_earning/100) * $binary_matching);

						if($top_earning>$binary_capping)
						{
							$top_earning = $binary_capping;
						}
							
						$totalOwn_contract = number_format($totalOwn_contract,8,'.','');
						$top_earning = number_format($top_earning,8,'.','');
							
						$insert_data=array('username'=>$username,'last_total'=>$totalOwn_contract,'total_earning'=>$top_earning,'status'=>'1');
						$insert_qry=$bin_user_wkl_income->insert($insert_data);

						//$insert_temp_data=array('username'=>$username,'last_total'=>$totalOwn_contract,'total_earning'=>$top_earning,'status'=>'1');
						//$insert_temp_qry=$bin_user_wkl_income_temp->insert($insert_temp_data);
							
						foreach($all_user as $user){
							if($user['username']==$lower_user['username']){
								$current_contact=0;
							}else{
								$current_contact= round(($user['contact']-$less_amount),8);
							}

							$user['contact'] = number_format($user['contact'],8,'.','');
							$current_contact = number_format($current_contact,8,'.','');
							$less_amount = number_format($less_amount,8,'.','');
							$user['old_total'] = number_format($user['old_total'],8,'.','');

							$insert_data1=array('username'=>$user['username'],'parent_username'=>$username,'last_total'=>$user['contact'],'current_toal'=>$current_contact,'less_value'=>$less_amount,'old_total'=>$user['contact'],'status'=>'1');
							$insert_qry1=$bin_user_wkl_income->insert($insert_data1);
						}
					}
				}
				$count++;
			}
			
		}
		echo "success";exit;
	}

}