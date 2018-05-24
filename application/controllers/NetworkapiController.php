<?php
class NetworkapiController extends Zend_Controller_Action{
	public function init(){


	}

	public function loadnodedataAction(){

        $Gbc_Model_Custom_func_obj = new Gbc_Model_Custom_CommonFunc();
        $username = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["username"]);
        $bin_user_ref = new Gbc_Model_DbTable_Binaryuserreferences();

        $res = $bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('u' => 'user_info'), array('u.ref_sponsor_id as sponsor', 'u.lock_status as lock_status', 'u.created_on as date'))
            ->joinLeft(array('i' => 'invoices'), "i.username = u.username", array('round(sum(i.contract_rate),2) as amtpaid'))
            ->where("u.username = ?", $username)
        );

        if (count($res)) {
            $date = $res[0]['date'];
            $date = date("d-m-Y", strtotime($date));
            if ($res[0]['lock_status'] == 'lock')
                $date = 'Locked User';
            echo '{"sponsor" : "' . $res[0]['sponsor'] . '", "amtpaid" : "' . $res[0]['amtpaid'] . '", "created_on" : "'.$date.'"}';
        }else
            echo "0";
        exit();

    }

    function get_medal($username){
        $bin_user_ref = new Gbc_Model_DbTable_Binaryuserreferences();
        $res = $bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('invoices'), array('round(sum(contract_rate),2) as amtpaid'))
            ->where("created_on > date('2017-05-05 23:59')")
            ->where("username = ?", $username)
        );

        if ($res[0]['amtpaid'] >= 15)
            return 'g';
        elseif ($res[0]['amtpaid'] >= 5)
            return 's';
        elseif ($res[0]['amtpaid'] >= 1)
            return 'b';
        else
            return 'r';
    }

    function filterNodes($tree, $username){
        $queue = array();
        $nodes = array();
        $queue[] = $username;
        while (count($queue)) {
            $username = $queue[0];
            unset($queue[0]);
            if (!strlen($username))
                break;
            foreach ($tree as $branch) {
                if(strtolower($branch->parent) == strtolower($username)){
                    $nodes[] = $branch;
                    $queue[] = $branch->username;
                }
            }
            $queue = array_values($queue);
        }
        return $nodes;
    }

	public function indexAction()
    {
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $bin_user_ref = new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj = new Gbc_Model_Custom_CommonFunc();
		
				
        $username = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["username"]);$username = $authUserNamespace->user;	
		$user = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["username"]);
		
		if(!empty($user)){
			$SearchParent ='';
			$Gbc_Model_Custom_func_obj->getAllChildforBinary($username,$SearchParent);
			// var_dump($SearchParent);
			if(in_array(strtolower($user),array_map('strtolower', $SearchParent))){
				$username = strtolower($user);
			}
		}
		
		// $username = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["username"]);


        $res = $bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array("binary_user_refences"), array('redis_tree_root_user'))
            ->where("username= ?", $username)
        );

        if (count($res)) {
            $tree = json_decode($Gbc_Model_Custom_func_obj->getRedisInstance()->get($res[0]['redis_tree_root_user']));

            if($username != $res[0]['redis_tree_root_user']){
                $new_tree = array();
                foreach ($tree as $branch){
                    if (strtolower($branch->username) == strtolower($username)) {
                        $new_tree[] = $branch;
                        break;
                    }
                }
                $branches = $this->filterNodes($tree, $username);
                foreach ($branches as $branch)
                    $new_tree[] = $branch;

                $tree = $new_tree;
            }

            // Adding medals

            $tree_clone = $tree;
            $tree = array();
            foreach ($tree_clone as $branch){
                $branch->medal = $this->get_medal(strtolower($branch->username));
                $tree[] = $branch;
            }

            $succ['binarydata'] = $tree;

            $arra = array('success' => 'success', 'failure' => '', 'data' => $succ);
            echo json_encode($arra);
            exit();
        } else {

            try {
                $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
                $common_obj = new Gbc_Model_Custom_CommonFunc();
                $db = Zend_Db_Table::getDefaultAdapter();
                $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
                $db->beginTransaction();
                $bin_user_ref = new Gbc_Model_DbTable_Binaryuserreferences();
                $bin_net_details_obj = new Gbc_Model_DbTable_Binarynetworkdetail();
                $Gbc_Model_Custom_func_obj = new Gbc_Model_Custom_CommonFunc();
                $username = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST["username"]);
                $token = $Gbc_Model_Custom_func_obj->cleanQueryParameter($_POST['token']);
                $date = date('d');
                $time = date('H:i:s');
                $count = 0;

                $antixss = new Gbc_Model_Custom_StringLimit();
                foreach ($_POST as $key => $value) {
                    if (isset($value) && $value != "") {
                        $antixss->setEncoding($value, "UTF-8");
                        if ($antixss->setFilter($value, "black", "string") == "invalidInput") {

                            $data = array('success' => '', 'failure' => 'Invalid Input.', 'data' => 'Invalid Input.');
                            echo json_encode($data);
                            exit;

                        }

                    }

                }

                if ($username == '') {
                    $arr = array('Success' => ' ', 'Failure' => 'Username cannot be blank');
                    echo json_encode($arr);
                    exit;
                }

                $user = $this->_request->getParam("user");

                if (!empty($user)) {
                    $SearchParent = '';
                    $Gbc_Model_Custom_func_obj->getAllChildforBinary($username, $SearchParent);
                    if (in_array(strtolower($user), array_map('strtolower', $SearchParent))) {
                        $username = strtolower($user);
                    }
                }

                $res = array();

                $res = $bin_user_ref->fetchAll($bin_user_ref->select()
                    ->setIntegrityCheck(false)
                    ->from(array('a' => "binary_user_refences"), array('a.id', 'a.username', 'a.parent_username', 'a.parent_id', 'a.child_position'))
                    ->joinLeft(array('u' => 'user_info'), "u.username = a.username", array('u.name', 'u.ref_sponsor_id', 'u.isActiveId', 'u.lock_status'))
                    ->joinLeft(array('i' => 'invoices'), "i.username = a.username", array('round(sum(i.amtPaid),2) as amtPaid', 'i.created_on', 'round(sum(i.contract_rate),2) as ContractPrice'))
                //   ->joinLeft(array('c' => 'contracts'), "u.username = a.username", array('c.contract_id'))
                    ->where("a.username= ?", $username)
                );

                $limit = 1;

                if (isset($res) && sizeof($res) > 0) {

                    if (!empty($res[0]['ContractPrice']) && $res[0]['ContractPrice'] != '')
                        $res[0]['amtPaid'] = $res[0]['ContractPrice'];
                    else
                        $res[0]['amtPaid'] = 0;

                    $topmcode = $res[0]['id'];
                    $finalRes = '';

                    if (!empty($topmcode) && $topmcode != '')
                        $Gbc_Model_Custom_func_obj->fetchBTree_new($topmcode, $finalRes, $limit);
                }

                if (!empty($finalRes)) {

                    $a = array('0' => array('name' => $username, 'parent' => 'null', 'isactive' => $res[0]['isActiveId'], 'lock_status' => $res[0]['lock_status'], 'child_position' => '', 'amtpaid' => $res[0]['amtPaid'], 'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on']));
                    $tree = array_merge($a, $finalRes);

                    $master_arr = array();

                    for ($k = 0; $k < (sizeof($tree)); $k++) {

                        array_push($master_arr, $tree[$k]['name']);
                    }
                    for ($k = 0; $k < (sizeof($tree)); $k++) {
                        $tree[$k]['key'] = $k + 1;
                        $index = array_search($tree[$k]['parent'], $master_arr);
                        $tree[$k]['parent_id'] = $index + 1;

                    }

                } else {
                    if (!empty($topmcode)) {
                        $tree = array('0' => array('name' => $username, 'parent' => 'null', 'isactive' => $res[0]['isActiveId'], 'full_name' => $tree['name'], 'lock_status' => $res[0]['lock_status'], 'child_position' => '', 'amtpaid' => $res[0]['amtPaid'], 'sponsor' => $res[0]['ref_sponsor_id'], 'date' => $res[0]['created_on'], 'key' => 1, 'parent_id' => 1));
                    } else {
                        $tee = array();
                    }
                }
            } catch (Exception $e) {
                $db->rollBack();
                echo $e->getMessage();
                exit;
            }
            $bigarr = array();
            if (!empty($all_user) && sizeof($all_user) > 0) {
                $dataarr = array('leftContracts' => $all_user['leftContracts'], 'totalLeftUsers' => $all_user['totalLeftUsers'], 'activeLeftUsers' => $all_user['activeLeftUsers'], 'inactiveLeftUsers' => $all_user['inactiveLeftUsers'], 'rightContracts' => $all_user['rightContracts'], 'totalRightUsers' => $all_user['totalRightUsers'], 'activeRightUsers' => $all_user['activeRightUsers'], 'inactiveRightUsers' => $all_user['inactiveRightUsers']);
            } else {
                $dataarr = array();
            }
            for ($n = 0; $n < sizeof($tree); $n++) {
                $subarr = array('name' => $tree[$n]['name'], 'parent' => $tree[$n]['parent'], 'full_name' => $tree['name'], 'isactive' => $tree[$n]['isactive'], 'lock_status' => $tree[$n]['lock_status'], 'child_position' => $tree[$n]['child_position'], 'amtpaid' => $tree[$n]['amtpaid'], 'sponsor' => $tree[$n]['sponsor'], 'date' => $tree[$n]['date'], 'key' => $tree[$n]['key'], 'parent_id' => $tree[$n]['parent_id']);
                array_push($bigarr, $subarr);
            }
            $db->commit();
            $succ = array();
            $succ['binarydata'] = $bigarr;
            $arra = array('success' => 'success', 'failure' => '', 'data' => $succ);
            echo json_encode($arra);
            exit;
            //$this->view->result=$tree;

        }
    }
}