<?php
class BinraynetwrkController extends Zend_Controller_Action{

	public function init()
	{

		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");
	}
	public function indexAction()
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$username=$authUserNamespace->user;
		
		
		
		$CountTotal = $db->query("SELECT count(*)  as count,A.child_position FROM `binary_user_refences` A inner join network_details b on b.root_user = A.username where A.parent_username='$username' group by A.username");
				$CountArray1 = $CountTotal->fetchAll();
			
		foreach($CountArray1 as $Countrows){
			if($Countrows['child_position'] == "R"){
				$MaxCount['right'] = $Countrows['count'];
			}else{
				$MaxCount['left'] = $Countrows['count'];
			}
		}
		
		
		$this->view->username=$username;
		$this->view->MaxCount = $MaxCount;
		$url= BASE."/Btreetableapi?username=".$username;
		$result=$common_obj->call_curl($url);
	//	echo "<pre>";
	//	print_r($result);exit;
		if(isset($result) && sizeof($result)>0)
		{
			$data=(array)json_decode($result,true);
			$this->view->binarydata=$data['data']['binarydata'];
	//		echo "<pre>";
	//		print_r($data['data']['binarydata']);exit;
			//$this->view->userdata=$data['data']['userdata'];
		}
		
		
		
		//	$UserBusinessCycleDetails=new Gbc_Model_DbTable_UserBusinessCycleDetails();
			$UserBusinessCycleDetails=new Gbc_Model_DbTable_NetworkBusinessDetails();	

			$details=$UserBusinessCycleDetails->fetchRow($UserBusinessCycleDetails->select()
				->setIntegrityCheck(false)
				  ->from(array('details' =>'network_business_details'),array('round(SUM(details.left_business),4) as leftContracts',
																				'SUM(details.left_active_users) as activeLeftUsers'
																			   ,'SUM(details.left_inactive_users) as inactiveLeftUsers'
																				,'round(SUM(details.right_business),4) as rightContracts'
																				,'SUM(details.right_active_users) as activeRightUsers'
																				,'SUM(details.right_inactive_users) as inactiveRightUsers'
																																							   
																			   ))
				->where("details.username = ?",$username)
				
				)->toArray();
			
			
			$details['totalLeftUsers'] =  $details['activeLeftUsers'] + $details['inactiveLeftUsers'];
			$details['totalRightUsers'] =  $details['activeRightUsers'] + $details['inactiveRightUsers'];
	//	print_r($details);
	//		exit;
			
            $this->view->userdata=$details;
			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value','start','end'))
			->where("name =?",'business_cycle_date'))->toArray();

			$this->view->business_cycle_date=$permissions_data;
			
		
			$this->view->title="Gainbitcoin - Binary Network";
	}
	
	
	
	public function paginatedataAction()
	{
		
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
	
		$this->_helper->layout()->setLayout("dashbord");//dashboard
		$common_obj=new Gbc_Model_Custom_CommonFunc();
		$username=$authUserNamespace->user;
		$page = $_POST['page']?$_POST['page']:1;
		$child = $_POST['child']?$_POST['child']:1;
				
		$db = Zend_Db_Table::getDefaultAdapter();
				
		$CountTotal = $db->query("SELECT count(*)  as count,A.child_position FROM `binary_user_refences` A inner join network_details b on b.root_user = A.username where A.parent_username='$username' group by A.username");
				$CountArray1 = $CountTotal->fetchAll();
			
		foreach($CountArray1 as $Countrows){
			if($Countrows['child_position'] == "R"){
				$MaxCount['right'] = $Countrows['count'];
			}else{
				$MaxCount['left'] = $Countrows['count'];
			}
		}

		$url= BASE."/Btreetableapi?username=".$username."&page=".$page."&child=".$child;
		//echo $url;
		$result=$common_obj->call_curl($url);
	//	echo "<pre>";
	//		print_r($result);exit;
		if(isset($result) && sizeof($result)>0)
		{
			$data=(array)json_decode($result,true);
			$this->view->binarydata=$data['data']['binarydata'];
	//		echo "<pre>";
	//		print_r($data['data']['binarydata']);exit;
			//$this->view->userdata=$data['data']['userdata'];
		}
		
		
						echo '
									<div class="row">
										<div class="col-md-12 col-sm-12">
											<div class="">
												<div class="bs-example table-responsive">
													<table id="data-table-simple-3"  class="table  table-responsive table-bordered innertbl display" cellspacing="0">
													   <thead>
													   <tr>
																

																  <th class="sortboth">Username</th>
																  <th class="sortboth">Sponsor ID</th>
																  <th class="sortboth">Contracts</th>
																  <th class="sortboth">Activated on</th>

																</tr>
															  </thead>
															  <tbody>';

															
																foreach ($data['data']['binarydata'] as $leftdata){
																	if($leftdata['name']!='N/A'){
																			$amtpaid = $leftdata['amtpaid']?$leftdata['amtpaid']:0;
																			$date = $leftdata['date']?$leftdata['date']:"-";
																			echo '<tr>
																			  <td>'.$leftdata['name'].'</td>
																			  <td>'.$leftdata['sponsor'].'</td>
																			  <td>'.$amtpaid.'</td>
																			  <td>'.$date.'</td>
																			 </tr>';
																		}	
																	}
															
														echo '</tbody>
															</table>';
	//	echo "here";
	//	exit;
		

					if($child == "L"){
						$MaxLimit = ceil($MaxCount['left']/500);
						echo '<div class="dataTables_paginate paging_simple_numbers" id="data-table-simple-12_paginate">';
					}else{
						$MaxLimit = ceil($MaxCount['right']/500);
						echo '<div class="dataTables_paginate paging_simple_numbers" id="data-table-simple-21_paginate">';
					}
		
					if($page >= 5 && $page <= $MaxLimit - 4){
						$offset = $page - 1;
					}else if($page >= 5 && $page > $MaxLimit - 4){
						$offset = $MaxLimit - 4;
					}else{
						$offset = 1;
					}
						//$offset = $page > 4 ? $page - 1 : 1;
					if($page > 1){
						echo '<a class="paginate_button previous" onclick = "paginateData(\''.($page-1).'\',\''.$child.'\')" >&lt;&lt;</a><span>';
					}else{
						echo '<a class="paginate_button previous">&lt;&lt;</a><span>';
					}
		
						if($page == 1){
							echo '<a class="paginate_button current" onclick = "paginateData(\'1\',\''.$child.'\')">1</a>';
						}else{
							echo '<a class="paginate_button " onclick = "paginateData(\'1\',\''.$child.'\')">1</a>';
						}
							$flag = 1;	
						$CounterLimit = $MaxLimit >$offset + 4 ?  $offset + 4  : $MaxLimit;
						
						for($i = $offset; $i <= $CounterLimit; $i++){
							if($i <= $MaxLimit){
								if($offset >= 4 && $MaxLimit > 5 && $flag == 1){
									$flag = 2;	
									echo '<span class="ellipsis">…</span>';
								}else{
								if($i!=1 && $i != $MaxLimit){
									//	echo $i;
									if($i == $page){
										echo '<a class="paginate_button current"  onclick = "paginateData(\''.$i.'\',\''.$child.'\')">'.$i.'</a>';
									}else{
										echo '<a class="paginate_button "  onclick = "paginateData(\''.$i.'\',\''.$child.'\')">'.$i.'</a>';	
									}
								}
								}
								if($MaxLimit > 5 && $i == $CounterLimit && $page <= $MaxLimit-4 && $flag != 3){
									$flag = 3;	
									echo '<span class="ellipsis">…</span>';
								}
							}
						}
					if($MaxLimit > 5){
						echo '<a class="paginate_button " onclick = "paginateData(\''.$MaxLimit.'\',\''.$child.'\')">'.$MaxLimit.'</a></span>';
					}
					if($page < $MaxLimit){
						echo '<a class="paginate_button next"  onclick = "paginateData(\''.($page+1).'\',\''.$child.'\')">&gt;&gt;</a>';
					}else{
						echo '<a class="paginate_button next">&gt;&gt;</a>';	
					}
						echo '				</div>
										</div>
									</div>
								</div>
							</div>';

		
		exit;
		
		
		
	}
	
	
}