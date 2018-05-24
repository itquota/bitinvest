<?php

class RegisterapiController extends Zend_Controller_Action
{

    public function init()
    {

    }

    public function indexAction()
    {

        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $antixss = new Gbc_Model_Custom_StringLimit();
        foreach ($_REQUEST as $key => $value) {

            if (isset($value) && $value != "") {
                $antixss->setEncoding($value, "UTF-8");
                if ($antixss->setFilter($value, "black", "string") == "invalidInput") {

                    //$data=array('success'=>'','failure'=>'Invalid Input.');
                    //echo json_encode($data);exit;

                }

                //echo $key . " - " . $value . " - " . $antixss->setFilter($value, "black", "string")."\n";
            }

        }
        if (empty($_REQUEST['username']) || empty($_REQUEST['email_addr']) || empty($_REQUEST['password']) || empty($_REQUEST['ref_sponser_id']) || empty($_REQUEST['captcha'])) {
            $data = array('success' => '', 'failure' => 'All fields are required!!');
            echo json_encode($data);
            exit;
        } else {
            try {
                $db = Zend_Db_Table::getDefaultAdapter();
                $db->beginTransaction();
                $user = new Gbc_Model_DbTable_Userinfo();
                $binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
                $misc_obj = new Gbc_Model_Custom_Miscellaneous();
                $comm_obj = new Gbc_Model_Custom_CommonFunc();
                $binary_new_det = new Gbc_Model_DbTable_Binarynetworkdetail();

                $chk = $user->fetchRow($user->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'user_info'), array('u.username', 'u.sponsor_id', 'u.isActiveId'))
                    ->where("binaryUser = 1")
                    ->where("lock_status = 'unlock'")
                    ->where($db->quoteInto("sponsor_id=?", trim($_REQUEST["ref_sponser_id"])) . ' OR ' . $db->quoteInto("username=?", trim($_REQUEST["ref_sponser_id"]))));
				//print_r($chk);
				//exit;

                if ($chk != "" && sizeof($chk) > 0) {
					$current_users = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                        ->where("parent_username = ?", trim($chk->username)));

					$totalchild = sizeof($current_users);

					if($totalchild>=1 && !empty($_REQUEST['plc'])){
						$placement = ($_REQUEST['plc'] ==  "R")?"R":"L";
					}else{
						$placement_data = $misc_obj->getUserInfo($_REQUEST['ref_sponser_id']);
						//print_r($placement_data);

						if (!empty($placement_data) && sizeof($placement_data) > 0) {
							$placement = $placement_data->placement;
						}
					}
					
					$ref['choice_leg'] = $placement;
					$ref['parent_username'] = $chk->username;
                } else {
                    $msg = "Given referred id not valid!";
                    $data = array('success' => '', 'failure' => $msg);
                    echo json_encode($data);
                    exit;
                }

                $row = $user->fetchRow($user->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' => 'user_info'), array('max(SUBSTR(u.sponsor_id,6)) as sponsor_id'))
                    ->where("binaryUser =1"));
                $i = 1;
                if (!empty($row) && sizeof($row) > 0) {
                    $randStr = $s = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 8)), 0, 3);
                    $randStr1 = $s = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 8)), 0, 3);
                    $sponsor_id = 'GBBN' . $randStr . time() . ($row['sponsor_id'] + $i) . $randStr1;
                } else {
                    $sponsor_id = 'GBBN1000001';
                }
                $ref['username']            = trim($_REQUEST["username"]);
                $ref['parent_sponser_id']   = trim($_REQUEST["ref_sponser_id"]);
                $userInfo["username"]       = trim($_REQUEST["username"]);
                $userInfo["email_addr"]     = trim($_REQUEST["email_addr"]);
                $userInfo["password"]       = trim($_REQUEST["password"]);

                $userInfo["ref_sponsor_id"] = trim($_REQUEST["ref_sponser_id"]);
                $userInfo["sponsor_id"]     = $sponsor_id;
                $userInfo["ip_address"]     = $misc_obj->get_client_ip();
                $userInfo["binary_user"]    = '1';
                $ref['sponsor_id']          = $sponsor_id;
                $userInfo["isActiveId"]     = '0';
                $userInfo["plc"]            = $placement;

                if (!empty($_REQUEST['child_position']) && isset($_REQUEST['child_position'])) {
                    $ref["child_position"] = $_REQUEST['child_position'];
                } else {
                    $ref["child_position"] = '';
                }

                if (!empty($_REQUEST['reffered_id'])) {
                    $userInfo["referrer_id"] = $_REQUEST['reffered_id'];
                } else {
                    $userInfo["referrer_id"] = '0';
                }
                $userInfo["type"] = 2;

                $salt = $misc_obj->generateSalt();
                $password = $misc_obj->encryptPassword($userInfo["password"], $salt);
				
                $address = array('username' => $userInfo["username"], 'salt' => $salt, 'password' => $password, 'email_address' => $userInfo["email_addr"], 'sponsor_id' => $userInfo["sponsor_id"], 'ref_sponsor_id' => $userInfo["ref_sponsor_id"], 'referrer_id' => $userInfo["referrer_id"], 'ip_address' => $userInfo["ip_address"], 'isActiveId' => $userInfo["isActiveId"], 'binaryUser' => $userInfo["binary_user"], 'lock_status' => 'unlock', 'placement' => $userInfo["plc"], 'created_on' => new Zend_Db_Expr('NOW()'));
				
				
                $insert_data = $user->insert($address);

                $refrenceCreator = $misc_obj->createUserrefrences($ref);

                if (!empty($refrenceCreator) && isset($refrenceCreator) && $refrenceCreator != '') {

                    $updateBinaryNetwork = $misc_obj->insertUpdateBinaryNetwork($ref);

                    $res_username = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                        ->setIntegrityCheck(false)
                        ->from(array("binary_user_refences"), array('parent_username', 'child_position'))
                        ->where("username = ?", $ref['username'])
                    );

                    $parent_username = $res_username[0]['parent_username'];

                    $child_position = $res_username[0]['child_position'] == 'L' ? 'left' : 'right';
                    $live_child_caching_redis = $comm_obj->getLiveChildRedisInstance();

                    $children = $live_child_caching_redis->get($parent_username . "_" . $child_position);

                    if($children)
                        $children .= $ref['username'] . ",";
                    else
                        $children = $ref['username'] . ",";

                    $live_child_caching_redis->set($parent_username . "_" . $child_position, $children);


                    $res = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                        ->setIntegrityCheck(false)
                        ->from(array("binary_user_refences"), array('redis_tree_root_user', 'child_position'))
                        ->where("username = ?", $parent_username)
                    );

                    $redis = $comm_obj->getRedisInstance();

                    $redis_tree_root_user = "";
                    if (isset($res[0]['redis_tree_root_user']))
                        $redis_tree_root_user = $res[0]['redis_tree_root_user'];

                    if (strlen($redis_tree_root_user)){
                        $tree = json_decode($redis->get($redis_tree_root_user));
                        $depth = 0;
                        try {
                            $depth = $comm_obj->countDepthOfNodeInTree($tree, $parent_username);
                        }catch (Exception $e){
                            $depth = 0;
                        }

                        $depth = (int)$depth;

                        $parent_user_children_count = 0;

                        if (count($tree) == 1){
                            if($tree->username == $parent_username) {
                                $tree->children_count++;
                                $parent_user_children_count = $tree->children_count - 1;
                            }
                        }else if (count($tree) > 1) {
                            for ($i = 0; $i < count($tree); $i++) {
                                if ($tree[$i]->username == $parent_username) {
                                    if (isset($tree[$i]->children_count))
                                        $tree[$i]->children_count++;
                                    else
                                        $tree[$i]->children_count = 1;
                                    $parent_user_children_count = $tree[$i]->children_count - 1;
                                    break;
                                }
                            }
                        }

                        if ($depth < 13 || ($depth == 13 && $parent_user_children_count < 2)){

                            $obj = new stdClass();
                            $obj->username       = strtolower($ref['username']);
                            $obj->parent = strtolower($parent_username);
                            $obj->child_position = $res_username[0]['child_position'];
                            $obj->isactive       = 0;
                            $obj->children_count = 0;

                            if(count($tree) == 1){
                                $tmp_tree = $tree;
                                $tree = array();
                                $tree[] = $tmp_tree;
                                $tree[] = $obj;
                            }elseif (count($tree) > 1) {
                                $tree[] = $obj;
                            }

                            if ($depth == 13){
                                $redis->set($redis_tree_root_user, json_encode($tree));
                                $redis_tree_root_user = $parent_username;
                                $tree = array(
                                    "username"       => strtolower($ref['username']),
                                    "parent"         => strtolower($parent_username),
                                    "child_position" => $res_username[0]['child_position'],
                                    "isactive"       => 0,
                                    "children_count" => 0
                                );
                            }

                        }else{
                            $redis->set($redis_tree_root_user, json_encode($tree));
                            $redis_tree_root_user = $parent_username;
                            $tree = array(
                                "username"       => strtolower($ref['username']),
                                "parent"         => strtolower($parent_username),
                                "child_position" => $res_username[0]['child_position'],
                                "isactive"       => 0,
                                "children_count" => 0
                            );
                        }

                    }else {

                        $redis_tree_root_user = $ref['username'];

                        $tree = array(
                            "username"       => strtolower($ref['username']),
                            "parent"         => "",
                            "child_position" => "",
                            "isactive"       => 0,
                            "children_count" => 0
                        );
                        $comm_obj->getSpareDbRedisInstance()->set($ref['username'],'');
                    }

                }
				/*
                $email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'><img src='" . BASE . "/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>Congratulations! You have successfully created a new account at GainBitCoin.</p><p>Your username is: " . $userInfo["username"] . "</p><p>We hope your efforts with us are profitable</p></div>";

                $to = $userInfo["email_addr"];
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'GainBitcoin: Account Created Successfully';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $comm_obj->sendMailviaAPI($to, $from, $replyTo, $subject, $message, $htmlMessage);
	*/
                $db->commit();
                $sql = "UPDATE binary_user_refences SET redis_tree_root_user = '$redis_tree_root_user' WHERE username = '{$ref['username']}'";
                $db_obj = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db_obj->query($sql);
                $redis->set($redis_tree_root_user, json_encode($tree));
                $data = array('success' => 'success', 'failure' => '');


                echo json_encode($data);
                exit;

            } catch (Exception $e) {
                $db->rollBack();
                $data = array('success' => '', 'failure' => $e->getMessage(), 'data1' => $e->getTrace(), 'data2' => $e->getCode(), 'data3' => $e->getFile(), 'data4' => $e->getLine());
                echo json_encode($data);
                exit;
            }

        }
    }


}