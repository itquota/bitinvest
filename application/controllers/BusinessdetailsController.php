<?php

class BusinessdetailsController extends Zend_Controller_Action
{

    public function init()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if ((!isset($authUserNamespace->user) || $authUserNamespace->user == "") || (empty($authUserNamespace->user_type) || $authUserNamespace->user_type != "binary")) $this->_redirect("/Dashboard/logout");

    }

    public function indexAction()
    {
        try {

            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
			$username=$authUserNamespace->user;
            $this->_helper->layout()->setLayout("dashbord");

			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value','start','end'))
			->where("name =?",'business_cycle_date'))->toArray();

			$this->view->business_cycle_date=$permissions_data;
			//print_r($permissions_data);
			//exit;

            if ($this->_request->isPost()) {
                $common_obj = new Gbc_Model_Custom_CommonFunc();

                $antixss = new Gbc_Model_Custom_StringLimit();

                foreach ($_POST as $key => $value) {
                    if (isset($value) && $value != "") {
                        $antixss->setEncoding($value, "UTF-8");
                        if ($antixss->setFilter($value, "black", "string") == "invalidInput") {
                            $data = array('success' => '', 'failure' => 'Invalid Input.');
                            echo json_encode($data);
                            exit;
                        }
                    }
                }

                $token = $common_obj->cleanQueryParameter(($_POST['token']));

              //  if (!empty($authUserNamespace->user) && $authUserNamespace->user != '' && $authUserNamespace->token == $token) {

                    $username = $authUserNamespace->user;
                  //  $startdate = $common_obj->cleanQueryParameter(($_POST["startdate"]));
                   // $enddate = $common_obj->cleanQueryParameter(($_POST["enddate"]));
                    $year = $common_obj->cleanQueryParameter(($_POST["year"]));
					
					//var_dump($_POST["year"]);
					
					$UserBusinessCycleDetails=new Gbc_Model_DbTable_UserBusinessCycleDetails();
					

					$details=$UserBusinessCycleDetails->fetchAll($UserBusinessCycleDetails->select()
					->setIntegrityCheck(false)
					->where("year = ?",$year)
					->where("username = ?",$username)
					->order("month ASC", "cycle asc")
					)->toArray();
				//	print_r($details);
					//exit;
				
			/*
                    $live_contract_db = $common_obj->getLiveContractRedisInstance();
                    $child_caching_db = $common_obj->getChildCachingRedisInstance();
                    $date = date('Y_m_d', strtotime($startdate));

                    $data = array(
                        "username" => $username,
                        "totalUsers" => 0,
                        "LeftActive" => 0,
                        "RightActive" => 0,
                        "LeftBusiness" => 0,
                        "RightBusiness" => 0,
                        "totalLeftBusiness" => 0,
                        "totalRightBusiness" => 0,
                        "start_date" => $startdate,
                        "end_date" => $enddate
                    );

                    $left_children = $child_caching_db->get($username . "_left");
                    if ($left_children) {
                        $left_children = explode(",", $left_children);
                        unset($left_children[count($left_children) - 1]);
                    } else
                        $left_children = array();

                    $right_children = $child_caching_db->get($username . "_right");
                    if ($right_children) {
                        $right_children = explode(",", $right_children);
                        unset($right_children[count($right_children) - 1]);
                    } else
                        $right_children = array();

                    $data["LeftActive"] = count($left_children);
                    $data["RightActive"] = count($right_children);
                    $data["totalUsers"] = count($left_children) + count($right_children);

                    $today_left_business = 0;
                    $today_right_business = 0;

                    $contract_caching_db = $common_obj->getContractCachingRedisInstance();

                    while (strtotime($startdate) < strtotime($enddate)) {
//                        if ($date == date("Y_m_d", time())) {
//                            $today_transactions_keys = $live_contract_db->keys('*');
//                            $today_transaction_keys_flat = implode(",", $today_transactions_keys);
//                            foreach ($left_children as $child) {
//                                if (strpos($today_transaction_keys_flat, $child . "_" . $date) !== false) {
//                                    $today_left_business += floatval($live_contract_db->get($child . "_" . $date));
//                                }
//                            }
//                            foreach ($right_children as $child) {
//                                if (strpos($today_transaction_keys_flat, $child . "_" . $date) !== false) {
//                                    $today_right_business += floatval($live_contract_db->get($child . "_" . $date));
//                                }
//                            }
//                            $data["LeftBusiness"] = $today_left_business;
//                            $data["RightBusiness"] = $today_right_business;
//                        } else {
                            $data["LeftBusiness"] += floatval($contract_caching_db->get($username . "_left_" . $date));
                            $data["RightBusiness"] += floatval($contract_caching_db->get($username . "_right_" . $date));
                            $startdate = date("Y-m-d", strtotime($startdate) + (24 * 60 * 60));
                            $date = date('Y_m_d', strtotime($startdate));
//                        }
                    }


                    $user_contracts_left_keys = $contract_caching_db->keys($username . "_left*");
                    $user_left_contracts = $contract_caching_db->mget($user_contracts_left_keys);
                    $user_contracts_right_keys = $contract_caching_db->keys($username . "_right*");
                    $user_right_contracts = $contract_caching_db->keys($user_contracts_right_keys);

                    foreach ($user_left_contracts as $contract)
                        $data['totalLeftBusiness'] += $contract;
                    $data['totalLeftBusiness'] += $today_left_business;

                    foreach ($user_right_contracts as $contract)
                        $data['totalRightBusiness'] += $contract;
                    $data['totalRightBusiness'] += $today_right_business;

                    if (empty($date) || !isset($date)) {
                        $data = array('success' => '', 'failure' => "There is some issue. Please try again later.");
                        $data = json_encode($data);
                    } else {
                        $data = array('success' => 'success', 'failure' => '', 'data' => array("userDetails" => $data));
                        $data = json_encode($data);
                    }

                    if (!empty($data) && sizeof($data) > 0)
                        $result = (array)json_decode($data, true);
                    else
                        $result = array();
					*/
           /*     } else {
                    $msg = "Invalid Request Found";
                    $authUserNamespace->msg = $msg;
                }*/
                $this->view->result = $details;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}