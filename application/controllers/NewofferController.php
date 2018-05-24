<?php

class NewofferController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if((!isset($authUserNamespace->user) || $authUserNamespace->user=="") || (empty($authUserNamespace->user_type)|| $authUserNamespace->user_type !="binary"))$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		try{ 
			$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username = $authUserNamespace->user;
			$this->_helper->layout()->setLayout("dashbord");//dashboard
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$specialpermissionsObj = new Gbc_Model_DbTable_SpecialPermission();
			$special_off_obj=new Gbc_Model_DbTable_Specialoffer();
			$claim_offer_obj = new Gbc_Model_DbTable_Claimedoffers();
			$result=$specialpermissionsObj->fetchRow($specialpermissionsObj->select()
			->setIntegrityCheck(false)
			->from(array('s'=>'special_permissions'),array('seminar','offers')));
				
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			
				
			//$result1=sizeOf($result);
$html='';
			if(isset($result) && ($result->offers)!=0)
			{
			
				//$url= "http://".BASE."/images/banner_specialoffer.png";
				$daily_pairs = $common_obj->dailyPairs();
			
				$pairs = $daily_pairs['paircount'];
				//$offers = ("select id,pairs,duration,price,image,prize from special_offers where status = 1");

				$offers =$special_off_obj->fetchAll($special_off_obj->select()
					->from(array('s' =>'special_offers'),array('id','pairs','duration','price','image','prize'))
					->where("status = '1'")
					);
				//$qry=$DB->query($offers);
				//$offersCount = $qry->rowCount();
				$offersCount = sizeof($offers );
				

			if(!empty($offersCount)){
						$html=$html. '
<p style = "font-weight:bold; color:red;">
											PLEASE NOTE: REWARDS HAS BEEN DISCONTINUED STARTING FROM 1st JANURARY 2017
											</p>
						<p>Rewards are shown here based on calculation from 1st August to 30th November, 2016. Users can claim these rewards by clicking on claim button. Users will be notified about their claimed request within 30 days.</p>					
						
<table class="responsive-table bordered centered" cellspacing="0">
					<thead>
					<tr>
							<th>
							Rank No.
							</th>
							<th>
							Target RP
							</th>
							<th>
							Time Period
							</th>
							<th>
							USD
							</th>
							<th>
							Gift
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>';
					$i = 1;
					$row_offers1 = $offers->toArray();
					foreach($row_offers1 as $row_offers) {
						
						$html=$html. "<tr>";
						// echo "<td>$i</td>";
						
						foreach($row_offers as $key => $row_offer){
							
							if($key == "id"){
								$id = $row_offer;
								$html=$html. "<td>$i</td>";
							}else{
								if($key == 'pairs'){
									$target_pairs = $row_offer;
								}
								if($key == 'image'){
									$image = $row_offer;
								}else if($i > 2 && $key == 'pairs'){
									$html=$html. "<td>+$row_offer</td>";
								}else if($key == 'prize'){
									$html=$html. "<td style = 'text-align:left;'>";
									$html=$html. "<img src = '".BASEPATH."/images/offer_images/".$image."_SMALL.png' style = 'vertical-align: middle;margin-left: 5px;'>";
									$html=$html. '<span style="margin-left: 25px;">';
									$html=$html. $row_offer;
									$html=$html. '</span>';
									$html=$html. "</td>";
								}else{
									$html=$html. "<td>$row_offer</td>";
								}
							}
						}
						$html=$html. "<td>";
						
						if(!empty($pairs) && $pairs>=$target_pairs){
							// var_dump($daily_pairs['CompletionOffersArray']);
							//$rows = ("select * from claimed_offers where offer_id = '$id' and username = '$username' and status != 2");
								$rows =$claim_offer_obj->fetchAll($claim_offer_obj->select()
								->where("offer_id =?", $id)
								->where("username =?", $username)
								->where("status !=?", 2)
								);
							/* if(in_array($id,$daily_pairs['pre_achieved'])){
								echo 'Achieved';
							} */
							// var_dump($checkOfferStatus['status']);
							
						//$rows=$DB->query($checkOfferStatus);
						//echo "<pre>";
						//print_r($rows);exit;
						//$rows->setFetchMode(Zend_Db::FETCH_NUM);
						$checkOfferStatusres = $rows->toArray();
						$claimedRequest=sizeof($checkOfferStatusres);
							foreach($daily_pairs['CompletionOffersArray'] as $key => $daily_pair){
								if($key == $id){
									
								// var_dump($claimedRequest);
									if(!isset($checkOfferStatusres) || sizeof($checkOfferStatusres)<=0){
										$rand = rand(100000, 999999);
										$id = $rand.$id;
										$rand = rand(100000, 999999);
										$id = $id.$rand;
										$html=$html. "<a class = 'btn' href = '".BASEPATH."/Claimreq?o=$id' target = '_blank'>Claim</a>";
									}else{
										
								// var_dump($checkOfferStatus['status']);
										if($checkOfferStatus['status'] == 1){
											$html=$html. 'Achieved';
										}else {
											$html=$html. 'Claimed';
										}
									}
								
								}
							}
							
						}
						$html=$html. "</td>";
						$html=$html. "</tr>";
						$i++;
					}
				$html=$html. "<tr>";
				$html=$html. '<td colspan="6">
					<p><strong>Note : For Reward 1:1BCLRP (Reward Point)<br />
					Images are shown for illustrative purpose only.<br />
					Amounts shown for reward is only rough estimate </strong>, <strong>or equivalent to BTC. </strong></p>
					</td>';
				$html=$html. "</tr>";
				
				$html=$html. "</tbody></table>";
			}
			$this->view->html=$html;
			}
			else{
				$url= BASE."/images/coming_soon.jpg";
				$this->view->url=$url;
			}
			

		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}

	}
}
