<?php
class BinaryuserwkycronafterrefundController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		if (PHP_SAPI === 'cli' && isset($argv[1])) {
			list($key, $val) = explode("=", $argv[1]);
			$key = $val;
		} else {
			$key = $_REQUEST['key'];
		}

		if (PHP_SAPI === 'cli' && isset($argv[2])) {
			list($key, $val) = explode("=", $argv[2]);
			$curr_date= $val;
		} else {
			if(isset($_REQUEST['CurrentDate']))
			{
				$curr_date = $_REQUEST['CurrentDate'];
			}

		}

		if(isset($key) && ($key == HASHKEY)){
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$user = new Gbc_Model_DbTable_Userinfo();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$cronName = "binaryUserwklyCronRefund";
			$start = date('Y-m-d H:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$date = !empty($_REQUEST['CurrentDate'])?"".$_REQUEST['CurrentDate']."":date('Y-m-d');
			if(!empty($curr_date) && $curr_date!=''){
				$Curdate = date('d',strtotime($_REQUEST['CurrentDate']));
				$month = date('m',strtotime($_REQUEST['CurrentDate']));

				//$LastMonth = $month;

				if($Curdate==16){
					$LastMonth = $month;
					$preDate = '01';
					$PreviousBinaryDate = date('Y')."-".$LastMonth."-".$preDate;
				}else{
					$LastMonth = $month-1;
					$preDate = '16';
					$PreviousBinaryDate = date('Y')."-".$LastMonth."-".$preDate;

				}

			}else{
				$Curdate = date('d');
				$month = date('m');
				if($month>1){
					$LastMonth = $month-1;
				}else{
					$LastMonth = "12";
				}

				if($Curdate>1 && $Curdate<=16){

					$preDate = '01';
					$PreviousBinaryDate = date('Y')."-".$LastMonth."-".$preDate;


				}else{

					$preDate = '16';
					$PreviousBinaryDate = date('Y')."-".$LastMonth."-".$preDate;
				}
			}

			/*				Table Backup				*/

			$DB->query("CREATE TABLE `bin_usr_wkl_income_2` LIKE bin_usr_wkl_income");
			$DB->query("INSERT `bin_usr_wkl_income_2` SELECT * FROM bin_usr_wkl_income;");

			/*				Table Backup				*/

			$userCount = $user->fetchRow($user->select()
			->setIntegrityCheck(false)
			->from(array('u' =>'user_info'),array('count(username) as userCount'))
			->where("binaryUser IS NOT NULL"));

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
				if(!empty($_GET['usr']) && $_GET['usr']!=''){
					$Users = array($_GET['usr']);
				}
				if(empty($Users) || !isset(empty($Users))){
					$count = $userCount->userCount ;
				}
				foreach($Users as $username){
					/* 	echo "count ".$count;
					 echo "<br><br>";
					 echo $username;
					 echo "<br><br>";
					 if($count%90 ==0){
					 	
					 echo "<br><br>";
					 } */

					/* $LeftDirect = mysql_query("SELECT count(user_info.username) from user_info WHERE user_info.ref_sponsor_id like '$username' and user_info.isActiveId = 1 and FIND_IN_SET(user_info.`username`,(SELECT left_network FROM `binary_network_details` WHERE `username` LIKE '$username'))");
					 $LeftDirect = mysql_result($LeftDirect,0);
					 	
					 $RightDirect = mysql_query("SELECT count(user_info.username) from user_info WHERE user_info.ref_sponsor_id like '$username' and user_info.isActiveId = 1 and FIND_IN_SET(user_info.`username`,(SELECT right_network FROM `binary_network_details` WHERE `username` LIKE '$username'))");
					 $RightDirect = mysql_result($RightDirect,0);	 */

					$bin_user_ref_obj = new Gbc_Model_DbTable_Binaryuserreferences();
					$refArray=$kits_obj->fetchAll($kits_obj->select()
					->setIntegrityCheck(false)
					->where("parent_username =?",$username)
					->where("parent_id <>?",'0')
					);

					//$refArray = "SELECT * FROM `binary_user_refences` WHERE `parent_username` = '".$username."' and parent_id<>0";
					//$result1 = runQuery($refArray, $conn);
					$childArray = array();

					$i = 0;
					for($i=0;$i<sizeof($refArray);$i++){
						$childArray[$i] = $refArray[$i]['username'];
					}

					if(!empty($childArray[0]) && !empty($childArray[1])){
						$childs_first=array();
						$common_obj->getAllChildforBinary($childArray[0],$childs_first,$date);

						$childs_second=array();
						$common_obj->getAllChildforBinary($childArray[1],$childs_second,$date);

						$childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
						$childs_second=array_merge(array($childArray[1]),array_filter($childs_second));

						// var_dump($childs_first);
						// echo "<br>";
						// echo "<br>";
						// echo "<br>";

						// var_dump($childs_second);
						// echo "<br>";
						// echo "<br>";

						if(!empty($childs_first)){
							$common_obj->$totalContractFirst=getTotlaDownlineContract($childs_first,$date);
						}else{
							$totalContractFirst=0;
						}
						if(!empty($childs_second)){
							$common_obj->$totalContractSecnd=getTotlaDownlineContract($childs_second,$date);
						}else{
							$totalContractSecnd=0;
						}
						$type = array('SHA','hardware');
						$Invoices_obj=new Gbc_Model_DbTable_Invoices();
						$query = $Invoices_obj->fetchRow($Invoices_obj->select()
						->from(array('invoices'=>"invoices"),array('round(SUM(contract_rate),8) as total_own'))
						->where("username = ?",$username)
						->where("invoice_status = ?",'1')
						->where("contract_type IN (?)",$type)
						->where("created_on < '$date'")
						->group("username")
						);

						//$query="SELECT round(SUM(contract_rate),8) as total_own from invoices WHERE username='".cleanQueryParameter($username)."' AND invoice_status='1' AND contract_type='SHA' and created_on < '$date' GROUP BY username";
						//$result1 = runQuery($query, $conn);

						if(isset($query) && sizeof($query)>0){
							$totalOwn_contract=$query->total_own;
						}
						else
						{
							$totalOwn_contract=0;
						}

						$all_user[0]['username']=$childArray[0];
						$all_user[0]['contact']=$totalContractFirst;
						$all_user[1]['username']=$childArray[1];
						$all_user[1]['contact']=$totalContractSecnd;
						$contact = $common_obj->array_column($all_user, 'contact');

						// var_dump($all_user);
						// echo "<br>";
						$query = $Invoices_obj->fetchRow($Invoices_obj->select()
						->from(array('invoices'=>"invoices"),array('round(SUM(contract_rate),8) as total_own'))
						->where("username = ?",$username)
						->where("invoice_status = ?",'1')
						->where("contract_type IN (?)",$type)
						->where("created_on < '$date'")
						->group("username")
						);

						$result = new Gbc_Model_DbTable_Binaryuserwelcome2();
						$query4=$Binaryuserwelcome2->fetchAll($Binaryuserwelcome2->select()
						->where("parent_username =?",$username)
						->where("created_on like '%$PreviousBinaryDate%'")
						);

						//$query="SELECT * from bin_usr_wkl_income_2 WHERE parent_username='".cleanQueryParameter($username)."' AND created_on like '%$PreviousBinaryDate%' ";
						//$result = runQuery($query, $conn);

						// echo $query;
						// echo "<br>";

						//$num_rows=mysql_num_rows($result["dbResource"]);
						$row = array();
						if(isset($result) && sizeof($result)>0){
							$new_users=array();
							if(isset($result) && sizeof($result)>0){
								$result = $result->toArray();
								$i=0;
								for($k=0;$k<sizof($result);$k++)
								$row[] = $result[$i];

								// unset($row[1]);
								// $row[1]['less_value'] = -0.5;
								// var_dump($row);
								// echo "<br>";
								// echo "<br>";
								// echo "<br>";
								foreach($all_user as $user){
									$key = array_search($user['username'],$common_obj->array_column($row,'username'));
									// var_dump($key);
									// echo "<br>";
									if(is_numeric($key)){
										$current_contact=0;
										if(($user['contact'] > $row[$key]['last_total']) && $row[$key]['less_value']>=0){
											//if(($user['contact'] > $row[$key]['last_total'])){
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

								// var_dump($new_users);
								// echo "<br>";


								if(!empty($_GET['usr']) && $_GET['usr']!=''){
									var_dump($all_user);
									echo "<br>";
									var_dump($new_users);
									echo "<br>";
									$contact = $common_obj->array_column($new_users, 'contact');
									$lower_user = $new_users[array_search(min($contact), $contact)];
									if($totalOwn_contract < 0.5){
										$top_earning=$lower_user['contact']*(4/100);
									}else{
										$top_earning=$lower_user['contact']*(8/100);
									}
									echo $top_earning;


									exit;
								}
								// $date=date('Y-m-d');

								/* $update_top="UPDATE bin_usr_wkl_income SET status='0' WHERE parent_username='' AND username='$username' AND date(created_on)<>'$date'";
								 runQuery($update_top, $conn);

								 $update_lower_user="UPDATE bin_usr_wkl_income SET status='0' WHERE parent_username='$username' AND date(created_on)<>'$date' ";
								 runQuery($update_lower_user, $conn); */

								$contact = $common_obj->array_column($new_users, 'contact');
								$lower_user = $new_users[array_search(min($contact), $contact)];

								$less_amount=$lower_user['contact'];
								$new_total_top_user_contact= $totalOwn_contract;
								if($totalOwn_contract < 0.5){
									$top_earning=$lower_user['contact']*(4/100);
								}else{
									$top_earning=$lower_user['contact']*(8/100);
								}


								if($top_earning > $totalOwn_contract*15 ){
									$top_earning=$totalOwn_contract*15;
								}else if($top_earning >150){
									$top_earning=150;
								}
								$top_earning=number_format($top_earning,8,'.','');
								/* $insert = "INSERT INTO bin_usr_wkl_income(username,last_total, total_earning ,status)"
								 . " VALUES ('" . cleanQueryParameter($username) . "',"
								 . "'" . cleanQueryParameter($new_total_top_user_contact) ."',"
								 . "'" . cleanQueryParameter($top_earning) ."',"
								 . "1"
								 . ")";
								 $result = runTransactionedQuery($insert, $conn); */
								$upd_arr = array('last_total'=>$new_total_top_user_contact,'total_earning'=>$top_earning,'updated_on'=>new Zend_Db_Expr('NOW()'));
								$update_top = $Binaryuserwelcome2->update($upd_arr,"parent_username='' AND username='$username' AND created_on like '%$date%'");
								//$update_top="UPDATE bin_usr_wkl_income_2 SET last_total = '$new_total_top_user_contact', total_earning = '$top_earning',updated_on = now() WHERE parent_username='' AND username='$username' AND created_on like '%$date%'";
								//runQuery($update_top, $conn);


								// echo $insert;
								// echo "<br>";

								/* 		Insert into temporary table			 */



								/* 		Insert into temporary table			 */







								foreach($new_users as $user){
									if($user['username']==$lower_user['username']){
										$current_contact=0;
									}else{
										$current_contact= round(($user['contact']-$less_amount),8);
									}
									$user['contact'] = number_format($user['contact'],8,'.','');

									$current_contact = number_format($current_contact,8,'.','');
									$less_amount = number_format($less_amount,8,'.','');
									if(!isset($user['old_total']) || empty($user['old_total']))
									{
										$user['old_total'] = 0;
									}
									$user['old_total'] = number_format($user['old_total'],8,'.','');

									/* $insert = "INSERT INTO bin_usr_wkl_income(username, parent_username,last_total,current_toal,less_value,old_total,status)"
									 . " VALUES ('" . cleanQueryParameter($user['username']) . "',"
									 . " '" . cleanQueryParameter($username) ."' ,"
									 . "'" . cleanQueryParameter($user['contact']) ."',"
									 . "'" . cleanQueryParameter($current_contact) ."',"
									 . "'" . cleanQueryParameter($less_amount) ."',"
									 . "'" . cleanQueryParameter($user['old_total']) ."',"
									 . "1"
									 . ")";
									 $result = runTransactionedQuery($insert, $conn);
									 */
									$top_data = array('last_total'=>$user['contact'],'current_toal'=>$current_contact,'less_value'=>$less_amount,'old_total'=>$user['old_total'],'updated_on'=>new Zend_Db_Expr('NOW()'));
									$update_top = $Binaryuserwelcome2->update($top_data,"parent_username='$username' AND username='".$user['username']."' AND created_on like '%$date%'");

									//$update_top="UPDATE bin_usr_wkl_income_2 SET last_total = '".$user['contact']."', current_toal = '$current_contact', less_value = '$less_amount', old_total = '".$user['old_total']."',updated_on = now() WHERE parent_username='$username' AND username='".$user['username']."' AND created_on like '%$date%'";
									//runQuery($update_top, $conn);

									// echo $insert;
									// echo "<br>";


								}
							}

						}else{
							$contact = $common_obj->array_column($all_user, 'contact');
							$lower_user = $all_user[array_search(min($contact), $contact)];
							$less_amount=$lower_user['contact'];

							if(!empty($_GET['usr']) && $_GET['usr']!=''){
								var_dump($all_user);
								echo "<br>";
								var_dump($new_users);
								echo "<br>";

								if($totalOwn_contract < 0.5){
									$top_earning=$lower_user['contact']*(4/100);
								}else{
									$top_earning=$lower_user['contact']*(8/100);
								}
								echo $top_earning;
								exit;
							}



							if($totalOwn_contract < 0.5){
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
							}
							$totalOwn_contract = number_format($totalOwn_contract,8,'.','');
							$top_earning = number_format($top_earning,8,'.','');
							/*
							 $insert = "INSERT INTO bin_usr_wkl_income(username,last_total, total_earning ,status)"
							 . " VALUES ('" . cleanQueryParameter($username) . "',"
							 . "'" . cleanQueryParameter($totalOwn_contract) ."',"
							 . "'" . cleanQueryParameter($top_earning) ."',"
							 . "1"
							 . ")";
							 $result = runTransactionedQuery($insert, $conn); */
							$top_data = array('last_total'=>$totalOwn_contract,'total_earning'=>$top_earning,'updated_on'=>new Zend_Db_Expr('NOW()'));
							$update_top = $Binaryuserwelcome2->update($top_data,"parent_username='' AND username='$username' AND created_on like '%$date%'");
							// $update_top="UPDATE bin_usr_wkl_income_2 SET last_total = '$totalOwn_contract', total_earning = '$top_earning',updated_on = now() WHERE parent_username='' AND username='$username' AND created_on like '%$date%'";
							//runQuery($update_top, $conn);

							// echo $insert;
							// echo "<br>";



							foreach($all_user as $user){
								if($user['username']==$lower_user['username']){
									$current_contact=0;
								}else{
									$current_contact= round(($user['contact']-$less_amount),8);
								}

								$user['contact'] = number_format($user['contact'],8,'.','');
								$current_contact = number_format($current_contact,8,'.','');
								$less_amount = number_format($less_amount,8,'.','');
								if(!isset($user['old_total']) || empty($user['old_total']))
									{
										$user['old_total'] = 0;
									}
								$user['old_total'] = number_format($user['old_total'],8,'.','');
								/*  $insert = "INSERT INTO bin_usr_wkl_income(username, parent_username,last_total,current_toal,less_value,old_total,status)"
								 . " VALUES ('" . cleanQueryParameter($user['username']) . "',"
								 . " '" . cleanQueryParameter($username) ."' ,"
								 . "'" . cleanQueryParameter($user['contact']) ."',"
								 . "'" . cleanQueryParameter($current_contact) ."',"
								 . "'" . cleanQueryParameter($less_amount) ."',"
								 . "'" . cleanQueryParameter($user['contact']) ."',"
								 . "1"
								 . ")";
								 $result = runTransactionedQuery($insert, $conn); */
								$top_arr = array('last_total'=>$user['contact'],'current_toal'=>$current_contact,'less_value'=>$less_amount,'old_total'=>$user['contact'],'updated_on'=>new Zend_Db_Expr('NOW()'));
								$upd_top = $Binaryuserwelcome2->update($top_arr,"parent_username='$username' AND username='".$user['username']."' AND created_on like '%$date%'");
								//$update_top="UPDATE bin_usr_wkl_income_2 SET last_total = '".$user['contact']."', current_toal = '$current_contact', less_value = '$less_amount', old_total = '".$user['contact']."',updated_on = now() WHERE parent_username='$username' AND username='".$user['username']."' AND created_on like '%$date%'";
								//runQuery($update_top, $conn);



							}
						}
					}
					$count++;
					//ob_flush();
					//flush();
				}

			}
			if($count >= ($userCount->userCount - 1)){

				//mysql_query("CREATE TABLE `daily_ledger_2` LIKE daily_ledger");
				//mysql_query("INSERT `daily_ledger_2` SELECT * FROM daily_ledger");

				$cronName = "binaryUserwklyCronRefund";
				// updateCronStatus($cronName);
				$end = date('Y-m-d H:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}


		}//end of key
		echo "cron finished";exit;

	}

}