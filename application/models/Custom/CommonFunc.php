<?php
class Gbc_Model_Custom_CommonFunc{	

    public function getRedisInstance(){
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis = new Redis();
        $redis->pconnect($resources['params'][APPLICATION_ENV]['host'], $resources['params'][APPLICATION_ENV]['port']);
        $redis->auth($resources['params'][APPLICATION_ENV]['pass']);
        $redis->select($resources['params'][APPLICATION_ENV]['tree_caching_db']);
        return $redis;
    }

    public function getTreeCachingRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['tree_caching_db']);
        return $redis;
    }

    public function getChildCachingRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['child_caching_db']);
        return $redis;
    }

    public function getLiveContractRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['live_contract_db']);
        return $redis;
    }

    public function getLiveChildRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['live_child_db']);
        return $redis;
    }

    public function getContractCachingRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['contract_caching_db']);
        return $redis;
    }

    function getSpareDbRedisInstance(){
        $redis = $this->getRedisInstance();
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $resources = $config->getOption("redis");
        $redis->select($resources['params'][APPLICATION_ENV]['spare_db']);
        return $redis;
    }

    function countDepthOfNodeInTree($tree, $node_username){
        $level = 0;
        $parent = "";
        $grand_parent_in_tree = false;
        try {
            foreach ($tree as $branch) {
                if ($branch->username == $node_username) {
                    $parent = $branch->parent;
                    $level++;
                    break;
                }
            }
        }catch (Exception $e){
            return 0;
        }
        if (!strlen($parent))
            return $level;
        else{
            foreach ($tree as $branch){
                if ($branch->username == $parent){
                    $grand_parent_in_tree = true;
                    break;
                }
            }
            if ($grand_parent_in_tree)
                return $level + $this->countDepthOfNodeInTree($tree, $parent);
            else
                return $level;
        }
    }

    public  function AvailableSilverKits($MaxSilverKits){
        $special_permission=new Gbc_Model_DbTable_SpecialPermission();
        $AvailableKits =$special_permission->fetchRow($special_permission->select()
        ->setIntegrityCheck(false)
        ->from(array('sp' =>'special_permissions'),array('sp.available_kits','sp.admin_kits')));

        return $AvailableKits;
    }
    function getSumOfDailyEarnings($username)
    {
        $final_ledger=new Gbc_Model_DbTable_FinalLedger();
        $returnArr = array();
        if (!empty($username) && isset($username) && $username!='')
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.daily_earning_amt),8) as sum_net_amt','round(SUM(fl.daily_earning_amt_new),8) as daily_earning_amt_new','round(SUM(fl.daily_earning_amt_new1),8) as daily_earning_amt_new1'))
         	->where("username= ?",$username));

        }
        else
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.daily_earning_amt),8) as sum_net_amt','round(SUM(fl.daily_earning_amt_new),8) as daily_earning_amt_new','round(SUM(fl.daily_earning_amt_new1),8) as daily_earning_amt_new1')));
        }

        return $returnArr;
    }

    function getSumOfBinaryEarnings($username)
    {
        $final_ledger=new Gbc_Model_DbTable_FinalLedger();
        $returnArr = array();
        if (isset($username) && $username!='')
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.binary_amt),8) as sum_bin_amt'))
            ->where("username=?",$username));

        }
        else
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.binary_amt),8) as sum_bin_amt')));
        }

        return $returnArr;
    }
    function getSumOfDirectEarnings($username)
    {
        $final_ledger=new Gbc_Model_DbTable_FinalLedger();
        $returnArr = array();
        if (isset($username) && $username!='')
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.ref_amt),8) as sum_direct_amt'))
            ->where("username=?",$username));

        }
        else
        {
            $returnArr =$final_ledger->fetchRow($final_ledger->select()
            ->setIntegrityCheck(false)
            ->from(array('fl' =>'final_ledger'),array('round(SUM(fl.ref_amt),8) as sum_direct_amt')));
        }

        return $returnArr;
    }
    function getTotalEarnings($username){
        $final_balance=new Gbc_Model_DbTable_FinalBalance();
        $returnArr = array();
        if (isset($username) && $username!='')
        {
            $returnArr =$final_balance->fetchRow($final_balance->select()
            ->setIntegrityCheck(false)
            ->from(array('fb' =>'final_balance'),array('fb.total_amt', 'fb.total_admin_fees', 'fb.total_withdrawal','fb.bal_amt','fb.new_token_amt'))
            ->where("username= ?",$username));
        }
        else
        {
            $returnArr =$final_balance->fetchRow($final_balance->select()
            ->setIntegrityCheck(false)
            ->from(array('fb' =>'final_balance'),array('fb.total_amt', 'fb.total_admin_fees', 'fb.total_withdrawal','fb.bal_amt','fb.new_token_amt')));
        }
        return $returnArr;
    }
    function getAllWithdrawals($username,$page_no,$no_of_records)
    {

        $withdrawals=new Gbc_Model_DbTable_Withdrawals();
            
            
        $returnArr =$withdrawals->fetchRow($withdrawals->select()
        ->setIntegrityCheck(false)
        ->from(array('w' =>'withdrawals'),array('w.username'))
        ->where("username=?",$username));
            
        echo $returnArr;exit;



    }
    function call_curl($url,$timeout=60)
    {
        $url = str_replace(' ','%20',$url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // Send to remote and return data to caller.
        $result = curl_exec($ch);
        return $result;
    }
    function userBusinessDetails($Postusername,$startdate,$enddate){

        try {
            $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();

            $result1=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->where("parent_username = ?",$Postusername)
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

             $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
				
             $childs_first='';
			if(!empty($childArray[0])){
             	$Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[0],$childs_first);
				   $childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
			}
             $childs_second='';
			if(!empty($childArray[1])){
            	 $Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[1],$childs_second);
				$childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
			}  
//var_dump($childArray);
                 
          

                 
             

                 

             // $RightpairsDetail=array();
             //$LeftpairsDetail=array();
             if(!empty($childArray[0]) || !empty($childArray[1]) ){

                 if($child_position[0] != "R"){
                      
                     if(!empty($childArray[0])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj-> getPairsforAdmin($childArray[0],$startdate,$enddate);
                     }
                     //    echo "<pre>";
                     //    print_r($LeftpairsDetail);exit;
                     if(!empty($childArray[1])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[1],$startdate,$enddate);
                     }
                 }
                 else
                 {
                      
                     if(!empty($childArray[0])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[0],$startdate,$enddate);
                     }
                      
                     if(!empty($childArray[1])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[1],$startdate,$enddate);
                     }
                 }
                 //echo "<pre>";
                 //print_r($LeftpairsDetail);exit;
                 $Leftpaircount = 0;
                 $Rightpaircount = 0;
                 $LeftActive = 0;
                 $RightActive = 0;
                 $LeftBusiness = $RightBusiness =$totalLeftBusiness = $totalRightBusiness = 0;
                 $pairDetails = array();
                 if(!empty($LeftpairsDetail)){
                     foreach($LeftpairsDetail as $pair){
                         // var_dump($pair);
                         if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) && (date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                             if(!empty($pair['created_on'])){

                                 // var_dump($pair);
                                 // echo date('Y-m-d',strtotime($pair['created_on']));
                                 // echo "<br>";
                                 // echo $pair['name'];
                                 // var_dump($pair['name']);
                                 // echo "<br>";
                                 // echo "<br>";
                                 // $pairArray[$pair['name']] = $pair['ContractPrice'];
                                 $pair['child_position'] = "L";
                                 $pairDetails[] = $pair;

                                 // $pairDetails['left'][]['username'] = $pair['name'];
                                 // $pairDetails['left'][]['contract'] = $pair['ContractPrice'];
                                 $LeftBusiness = $LeftBusiness + $pair['ContractPrice'];
                                 // echo $LeftBusiness;
                             }
                                 
                             if($pair['IsActive'] == 1){


                                 $LeftActive++;
                          }

                              
                          $Leftpaircount++;
                              
                         }else{
                             // echo "date left:";
                             // var_dump($pair['ContractPrice']);
                             // echo "<br>";
                         }
                         $totalLeftBusiness = $totalLeftBusiness + $pair['ContractPrice'];
                     }
                     /* fclose($fp); */
                 }

                    if(!empty($RightpairsDetail) && $RightpairsDetail!='' ){
                     // echo "right :";
                     // echo "<br>";
                        // var_dump($pair);
                        foreach($RightpairsDetail as $pair){
                            // echo "'";
                            /* if(!empty($pair['ContractPrice'])){
                             echo $pair['name']." ".$pair['ContractPrice'];
                             echo "<br>";
                             } */
                            if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) &&(date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                                if(!empty($pair['created_on'])){
                                    $pair['child_position'] = "R";
                                    $pairDetails[] = $pair;

                                }
                          if($pair['IsActive'] == 1){

                              $RightActive++;
                          }else{
                              // echo "right :";
                              // var_dump($pair);

                          }
                          $RightBusiness = $RightBusiness + $pair['ContractPrice'];
                          $Rightpaircount++;
                            }else{
                                // echo " date right :";
                                // var_dump($pair['ContractPrice']);
                                // echo "<br>";
                            }
                            $totalRightBusiness = $totalRightBusiness + $pair['ContractPrice'];
                        }
                    }

                    $userDetails['userDetails']['username'] = $Postusername;
                    $userDetails['userDetails']['totalUsers'] = count($LeftpairsDetail) + count($RightpairsDetail);
                    $userDetails['userDetails']['LeftActive'] = $LeftActive;
                    $userDetails['userDetails']['RightActive'] = $RightActive;
                    $userDetails['userDetails']['LeftBusiness'] = $LeftBusiness;
                    $userDetails['userDetails']['RightBusiness'] = $RightBusiness;
                    $userDetails['userDetails']['totalLeftBusiness'] = $totalLeftBusiness;
                    $userDetails['userDetails']['totalRightBusiness'] = $totalRightBusiness;
                    $userDetails['pairDetails'] = $pairDetails;

                    return $userDetails;
             }
            }

        }
        catch(Exception $e)
        {
			echo $e->getMessage();
            return $userDetails[0]="error";
        }

    }
    function userBusinessDetailsmob($Postusername,$startdate,$enddate,$start){
        try {
            $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();

            $result1=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->where("parent_username = ?",$Postusername)
            ->where("parent_id != ?",0)
            ->order("child_position ASC")
            ->limit(10,$start)
            );

            //print_r($result1);exit;
            $userDetails=array();
            $childArray = array();
            if(!empty($result1) && isset($result1) && sizeof($result1)>0)
            {

                for($i=0;$i<sizeof($result1);$i++)
                {
                    $childArray[$i] = $result1[$i]['username'];
                    $child_position[$i] = $result1[$i]['child_position'];
                }

             $childs_first='';
             $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
             $Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[0],$childs_first);

             $childs_second='';
             if(!empty($childArray[1])){
                 $Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[1],$childs_second);
             }
             $childs_first=array_merge(array($childArray[0]),array_filter($childs_first));

             if(!empty($childArray[1])){
                 $childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
             }
                 

             $RightpairsDetail=array();
             $LeftpairsDetail=array();
             if(!empty($childArray[0]) || !empty($childArray[1]) ){
                 if($child_position[0] != "R"){
                     if(!empty($childArray[0])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj-> getPairsforAdmin($childArray[0],$startdate,$enddate);
                     }
                     if(!empty($childArray[1])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[1],$startdate,$enddate);
                     }
                 }
                 else
                 {
                     if(!empty($childArray[0])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[0],$startdate,$enddate);
                     }
                      
                     if(!empty($childArray[1])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdmin($childArray[1],$startdate,$enddate);
                     }
                 }
                $Leftpaircount = 0;
                 $Rightpaircount = 0;
                 $LeftActive = 0;
                 $RightActive = 0;
                 $LeftBusiness = $RightBusiness =$totalLeftBusiness = $totalRightBusiness = 0;
                 $pairDetails = array();
                 if(!empty($LeftpairsDetail)){
                     foreach($LeftpairsDetail as $pair){
                         // var_dump($pair);
                         if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) && (date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                             if(!empty($pair['created_on'])){

                                 // var_dump($pair);
                                 // echo date('Y-m-d',strtotime($pair['created_on']));
                                 // echo "<br>";
                                 // echo $pair['name'];
                                 // var_dump($pair['name']);
                                 // echo "<br>";
                                 // echo "<br>";
                                 // $pairArray[$pair['name']] = $pair['ContractPrice'];
                                 $pair['child_position'] = "L";
                                 $pairDetails[] = $pair;

                                 // $pairDetails['left'][]['username'] = $pair['name'];
                                 // $pairDetails['left'][]['contract'] = $pair['ContractPrice'];
                                 $LeftBusiness += $pair['ContractPrice'];
                                 // echo $LeftBusiness;
                             }
                                 
                             if($pair['IsActive'] == 1){


                                 $LeftActive++;
                          }

                              
                          $Leftpaircount++;
                              
                         }else{
                             // echo "date left:";
                             // var_dump($pair['ContractPrice']);
                             // echo "<br>";
                         }
                         $totalLeftBusiness += $pair['ContractPrice'];
                     }
                     /* fclose($fp); */
                 }

                    if(!empty($RightpairsDetail) && $RightpairsDetail!='' ){
                     // echo "right :";
                     // echo "<br>";
                        // var_dump($pair);
                        foreach($RightpairsDetail as $pair){
                            // echo "'";
                            /* if(!empty($pair['ContractPrice'])){
                             echo $pair['name']." ".$pair['ContractPrice'];
                             echo "<br>";
                             } */
                            if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) &&(date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                                if(!empty($pair['created_on'])){
                                    $pair['child_position'] = "R";
                                    $pairDetails[] = $pair;

                                }
                          if($pair['IsActive'] == 1){

                              $RightActive++;
                          }else{
                              // echo "right :";
                              // var_dump($pair);

                          }
                          $RightBusiness += $pair['ContractPrice'];
                          $Rightpaircount++;
                            }else{
                                // echo " date right :";
                                // var_dump($pair['ContractPrice']);
                                // echo "<br>";
                            }
                            $totalRightBusiness += $pair['ContractPrice'];
                        }
                    }

                    $userDetails['userDetails']['username'] = $Postusername;
                    $userDetails['userDetails']['totalUsers'] = count($LeftpairsDetail) + count($RightpairsDetail);
                    $userDetails['userDetails']['LeftActive'] = $LeftActive;
                    $userDetails['userDetails']['RightActive'] = $RightActive;
                    $userDetails['userDetails']['LeftBusiness'] = $LeftBusiness;
                    $userDetails['userDetails']['RightBusiness'] = $RightBusiness;
                    $userDetails['userDetails']['totalLeftBusiness'] = $totalLeftBusiness;
                    $userDetails['userDetails']['totalRightBusiness'] = $totalLeftBusiness;
                    $userDetails['pairDetails'] = $pairDetails;


                    return $userDetails;
             }
            }

        }
        catch(Exception $e)
        {
            return $userDetails[0]="error";
        }

    }
    function getAllChildforBinary($username,&$result1='',$date = null)
    {
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $result=array();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        /*$result=$bin_user_ref->fetchAll($bin_user_ref->select()
         ->setIntegrityCheck(false)
         ->from(array('A'=>"binary_user_refences"),array('A.id','A.username'))
         ->from(array('B'=>"user_info"),array('A.id','B.username as unm'))
         //->where("A.parent_username = '$username' AND A.parent_id <> 0 and B.username=A.parent_username"));
         ->where("A.parent_username = ?",$username)
         ->where("A.parent_id <> ?",0)
         ->where("B.username =A.parent_username")

         );*/

        /*if(!empty($date)){
            $date = " and B.created_on < '$date'";
            }*/

        if(!empty($date) && $date!='')
        {
            $result=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"binary_user_refences"),array('A.id','A.username'))
            ->joinLeft(array('B'=>'invoices'),"B.username = A.username and B.invoice_status = 1 ",array('round(sum(B.contract_rate),4) as contracts'))
            ->where("A.parent_username = ?",$username)
            ->where("A.parent_id <> ?",0)
            ->where("B.created_on < ?",$date)
            ->group("A.username")
            );

        }
        else
        {
            $result=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"binary_user_refences"),array('A.id','A.username'))
            ->joinLeft(array('B'=>'invoices'),"B.username = A.username and B.invoice_status = 1 ",array('round(sum(B.contract_rate),4) as contracts'))
            ->where("A.parent_username = ?",$username)
            ->where("A.parent_id <> ?",0)
            ->group("A.username")
            );

        }


        $arrayCategories1=array();

        if(!empty($result) && sizeof($result)>0)
        {
            $arrayCategories1 = $result->toArray();
        }
        // $query="SELECT A.id,A.username FROM `binary_user_refences` A, `user_info` B WHERE A.`parent_username` = '$username' AND A.parent_id <> 0 and B.username=A.parent_username $date";

        /*$query="SELECT A.id,A.username,round(sum(B.contract_rate),2) as contracts FROM `binary_user_refences` A left join invoices B on B.username = A.username and B.invoice_status = 1 $date WHERE A.`parent_username` = '$username'  AND A.parent_id <> 0  group by A.username ";

        $resultqry1=$DB->query($query);
        $result = $resultqry1->fetchAll();*/
            
        if(!isset($result) || sizeof($result)<=0){

            $result1[] ='';

            return true;
        }
        //$usernm=$result[0]['username'];

        for($i=0;$i<sizeof($arrayCategories1);$i++)
        {

            $result1[] = strtolower($arrayCategories1[$i]['username']);
            $Gbc_Model_Custom_func_obj->getAllChildforBinary($arrayCategories1[$i]['username'],$result1);
        }


        return ($result1);

            
    }
    function getPairsforAdmin($username,$startdate,$enddate){
        ini_set('memory_limit', '-1');
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $result=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.parent_id','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"UserInfo.username = BinaryUserReferences.username",array('UserInfo.ref_sponsor_id','UserInfo.isActiveId'))
        ->joinLeft(array('Invoice'=>'invoices'),"Invoice.username = BinaryUserReferences.username and Invoice.invoice_status  = 1 and locked = '0'  and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate'",array('round(sum(Invoice.contract_rate),4) as ContractPrice', 'Invoice.created_on'))
        ->where("BinaryUserReferences.username= ?", $username)
        ->group("BinaryUserReferences.username"));
            


        /*$qury="Select BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.ref_sponsor_id,UserInfo.isActiveId, round(sum(Invoice.contract_rate),2) as ContractPrice, Invoice.created_on from binary_user_refences as BinaryUserReferences left join `user_info` as UserInfo on UserInfo.username = BinaryUserReferences.username left join invoices as Invoice on (Invoice.username = BinaryUserReferences.username and locked = '0'  and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate') where BinaryUserReferences.username='$username' group by BinaryUserReferences.username";
         $resultqry=$DB->query($qury);
         $result = $resultqry->fetchAll();*/
        $finalRes='';
        if(isset($result) && sizeof($result)>0)
        {
            $result = $result->toArray();
        }

        if(sizeof($result)<=0){
            return true;
        }
        $topmcode =$result[0]['id'];
        for($i=0;$i<sizeof($result);$i++)
        {

            $newArray=array();
            $id=$result[$i]['id'];
            $chiled_position=$result[$i]['child_position'];
            $newArray['name']= strtolower($result[$i]['username']);
            $newArray['parent']=strtolower($result[$i]['parent_username']);
            $newArray['IsActive']=strtolower($result[$i]['isActiveId']);
            $newArray['child_position']=strtolower($result[$i]['child_position']);
            $newArray['created_on']=strtolower($result[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($result[$i]['ContractPrice']);
            $retArray[]=$newArray;
            $finalRes = $retArray;

            //var_dump($finalRes);
            // fetchBTreeForPairs($id,$conn,$retArray);

        }

        $Gbc_Model_Custom_func_obj->fetchBTreeForPairsAdmin($topmcode,$finalRes,$startdate,$enddate);
        return $finalRes;
    }


    function fetchBTreeForPairsAdmin($parentID,&$retArray,$startdate,$enddate)
    {
        ini_set('memory_limit', '-1');
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $Categories1=array();

        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
        ->joinLeft(array('u'=>'user_info'),"a.username = u.username",array('u.ref_sponsor_id','u.isActiveId'))
        ->joinLeft(array('i'=>'invoices')," i.username = u.username and i.invoice_status = 1  and locked = '0' and i.created_on >= '$startdate' and i.created_on <= '$enddate'",array('round(sum(i.contract_rate),4) as ContractPrice','i.created_on'))
        ->where("a.parent_id = ?",$parentID)
        ->group("u.username")
        ->order("child_position ASC")
        );
            
        if(!empty($Categories1) && sizeof($Categories1)>0)
        {
            $Categories1 = $Categories1->toArray();
        }

        //$sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.isActiveId,  round(sum(Invoice.contract_rate),2) as ContractPrice, Invoice.created_on FROM `binary_user_refences` as BinaryUserReferences left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1  and locked = '0' and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate' WHERE BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username  order by child_position ASC";

        //$resultqry1=$DB->query($sql);
        //$Categories1 = $resultqry1->fetchAll();

        if(sizeof($Categories1)<=0){
            return true;
        }

        for($i=0;$i<sizeof($Categories1);$i++)
        {

            $newArray=array();
            $id=$Categories1[$i]['id'];
            $chiled_position=$Categories1[$i]['child_position'];
            $newArray['name']= strtolower($Categories1[$i]['username']);
            $newArray['parent']=strtolower($Categories1[$i]['parent_username']);
            $newArray['IsActive']=strtolower($Categories1[$i]['isActiveId']);
            $newArray['child_position']=strtolower($Categories1[$i]['child_position']);
            $newArray['created_on']=strtolower($Categories1[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($Categories1[$i]['ContractPrice']);
            $retArray[]=$newArray;

            $Gbc_Model_Custom_func_obj->fetchBTreeForPairsAdmin($id,$retArray,$startdate,$enddate);
        }

        return ($retArray) ;

    }
    function updateSession($username, $time="", $sessId){
        try {
            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $user_sess_obj = new Gbc_Model_DbTable_Usersessions();
            $user = new Gbc_Model_DbTable_Userinfo();
            $ip_address=$misc_obj->get_client_ip();
            $user_obj=new Gbc_Model_DbTable_Userinfo();

            $user_sess_row = $user_sess_obj->fetchRow($user_sess_obj->select()
            ->order("id desc")
            ->limit(1,0)
            );
            if(isset($user_sess_row) && sizeof($user_sess_row)>0)
            {
                $id=($user_sess_row->id)+1;
            }
            else
            {
                $id=0;
            }
            $insert_arr=array('id'=>$id,'username'=>$username,'ip_address'=>$ip_address,'created_on'=>new Zend_Db_Expr('NOW()'));
            $insert_data=$user_sess_obj->insert($insert_arr);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();exit;
        }
            
            
    }
    function get_rand_alphanumeric($length)
    {
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        if ($length>0)

        {

            $rand_id="";

            for ($i=1; $i<=$length; $i++)

            {

                mt_srand((double)microtime() * 1000000);

                $num = mt_rand(1,36);

                $rand_id .=$Gbc_Model_Custom_func_obj-> assign_rand_value($num);

            }

        }

        return $rand_id;

    }



    function get_rand_numbers($length)

    {
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();

        if ($length>0)

        {

            $rand_id="";

            for($i=1; $i<=$length; $i++)

            {

                mt_srand((double)microtime() * 1000000);

                $num = mt_rand(27,36);

                $rand_id .= $Gbc_Model_Custom_func_obj->assign_rand_value($num);

            }

        }

        return $rand_id;

    }

    function assign_rand_value($num)

    {

        // accepts 1 - 36

        switch($num)

        {

            case "1"  : $rand_value = "a"; break;

            case "2"  : $rand_value = "b"; break;

            case "3"  : $rand_value = "c"; break;

            case "4"  : $rand_value = "d"; break;

            case "5"  : $rand_value = "e"; break;

            case "6"  : $rand_value = "f"; break;

            case "7"  : $rand_value = "g"; break;

            case "8"  : $rand_value = "h"; break;

            case "9"  : $rand_value = "i"; break;

            case "10" : $rand_value = "j"; break;

            case "11" : $rand_value = "k"; break;

            case "12" : $rand_value = "l"; break;

            case "13" : $rand_value = "m"; break;

            case "14" : $rand_value = "n"; break;

            case "15" : $rand_value = "o"; break;

            case "16" : $rand_value = "p"; break;

            case "17" : $rand_value = "q"; break;

            case "18" : $rand_value = "r"; break;

            case "19" : $rand_value = "s"; break;

            case "20" : $rand_value = "t"; break;

            case "21" : $rand_value = "u"; break;

            case "22" : $rand_value = "v"; break;

            case "23" : $rand_value = "w"; break;

            case "24" : $rand_value = "x"; break;

            case "25" : $rand_value = "y"; break;

            case "26" : $rand_value = "z"; break;

            case "27" : $rand_value = "0"; break;

            case "28" : $rand_value = "1"; break;

            case "29" : $rand_value = "2"; break;

            case "30" : $rand_value = "3"; break;

            case "31" : $rand_value = "4"; break;

            case "32" : $rand_value = "5"; break;

            case "33" : $rand_value = "6"; break;

            case "34" : $rand_value = "7"; break;

            case "35" : $rand_value = "8"; break;

            case "36" : $rand_value = "9"; break;

        }

        return $rand_value;

    }

    function updateUserAccount($userInfo, $changePwd = false) {
        //print_r($userInfo['data']['country']);exit;

        try {
            $misc_obj=new Gbc_Model_Custom_Miscellaneous;
            $user_obj = new Gbc_Model_DbTable_Userinfo();
            $salt = $misc_obj->generateSalt();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
            $password = $misc_obj->encryptPassword($userInfo['data']["password"], $salt);
            $username=$userInfo['data']['username'];
            if(isset($userInfo['data']["imgs"]) && $userInfo['data']["imgs"] != ''){

                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'country'=>$userInfo['data']['country'],'phone'=>$userInfo['data']['phone'],'id_image'=>$userInfo['data']["imgs"],'updated_on'=>new Zend_Db_Expr('NOW()'));
            }else{

                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'country'=>$userInfo['data']['country'],'phone'=>$userInfo['data']['phone'],'updated_on'=>new Zend_Db_Expr('NOW()'));
            }
            if ($changePwd)
            {
                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'salt'=>$salt,'password'=>$password,'updated_on'=>new Zend_Db_Expr('NOW()'));
            }
         //$update_data=$user_obj->update($update_arr,"username=?",$username);
            //$DB->quoteInto()

            $update_data=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));


         return "success";
        }
        catch (Exception $e)
        {
            return "failed";
        }


    }
    function updateUserMobAccount($userInfo, $changePwd = false) {
        //print_r($userInfo['data']['country']);exit;

        try {
            $misc_obj=new Gbc_Model_Custom_Miscellaneous;
            $user_obj = new Gbc_Model_DbTable_Userinfo();
            $salt = $misc_obj->generateSalt();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
            $password = $misc_obj->encryptPassword($userInfo['data']["password"], $salt);
            $username=$userInfo['data']['username'];
            if(isset($userInfo['data']["imgs"]) && $userInfo['data']["imgs"] != ''){

                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'country'=>$userInfo['data']['country'],'phone'=>$userInfo['data']['phone'],'Profileimage'=>$userInfo['data']["imgs"]);
            }else{

                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'country'=>$userInfo['data']['country'],'phone'=>$userInfo['data']['phone']);
            }
            if ($changePwd)
            {
                $update_arr=array('comm_email'=>$userInfo['data']["comm_email"],'name'=>$userInfo['data']["name"],'salt'=>$salt,'password'=>$password);
            }
            //$DB->quoteInto()
         //$update_data=$user_obj->update($update_arr,"username=?",$username);
         $update_data=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));

         return "success";
        }
        catch (Exception $e)
        {
            return "failed";
        }


    }
    /////////////////////////////////
    //***** Change password *******//
    /////////////////////////////////
    function updateUserPassword($userInfo, $changePwd = false) {
        try {

            $misc_obj=new Gbc_Model_Custom_Miscellaneous;
            $user_obj = new Gbc_Model_DbTable_Userinfo();
            $salt = $misc_obj->generateSalt();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
            $password = $misc_obj->encryptPassword($userInfo['data']["password"], $salt);
            $username=$userInfo['data']['username'];

            //$update_arr=array();
            if($username!="")
            {
                $update_arr=array('salt'=>$salt,'password'=>$password);

            }
         //$update_data=$user_obj->update($update_arr,"username=?",$username);
         //$DB->quoteInto()
         $update_data=$user_obj->update($update_arr,$DB->quoteInto("username=?",$username));


         return "success";
        }
        catch (Exception $e)
        {
            echo "fail".$e;exit;
            return "failed";
        }
    }
    //////////////////////////////////
    //**** End Change password *****//
    //////////////////////////////////


    function saveUserLog($username,$table_name,$description){

        $userlogs_obj = new Gbc_Model_DbTable_Userlogs();
		 $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
		
		 $loggedin_user=$authUserNamespace->user;
		$ip_address=  $misc_obj->get_client_ip();
		 $insert_arr=array('username'=>$username,'description'=>$description,'table_name'=>$table_name,'loggedin_user'=>$loggedin_user,'ip_address'=>$ip_address);
        $insert_data=$userlogs_obj->insert($insert_arr);
        return "success";

    }
    function sendMSG($data){


        //print_r($data);exit;
        // var_dump($data);
        // echo 'http://onlinesms.in/api/sendValidSMSdataUrl.php?'.$data;
        // exit;
        // Send the POST request with cURL
        /*       $ch = curl_init('http://api.textlocal.in/send/');
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $response = curl_exec($ch);
         curl_close($ch); */


        // $ch = curl_init('http://api.textlocal.in/send/');
        $ch = curl_init('http://onlinesms.in/api/sendValidSMSdataUrl.php?'.$data);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
            
        curl_close($ch);
            
        // Process your response here
        // echo $response;


        return $response;



    }

    function getTotlaDownlineContract($users,$date='',$startdate='',$enddate='')
    {

        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        global $blanks;
        if(!empty($date)){

            //$date = "created_on < '$date'";
            $date = '"created_on < ?","$date"';

        }
        else
        {

        }
        $totalContract=0;
        $users=array_filter($users);

        foreach($users as $user)
        {

            if(!empty($date)){

                $result=$invoices_obj->fetchRow($invoices_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('A'=>"invoices"),array('round(sum(contract_rate),2) as qty'))
                //->where("username='".($user)."' AND contract_type='SHA' and locked = '0' AND invoice_status='1' $date"));
                ->where("username=?",$user)
                ->where("contract_type in('SHA','hardware','MS','ES')")
                ->where("locked= ?",'0')
                ->where("invoice_status= ?",'1')
                ->where("created_on < ?",$date)
                ->where("archive = ?",'0')
                );
                    
            }
            else
            {
                $result=$invoices_obj->fetchRow($invoices_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('A'=>"invoices"),array('round(sum(contract_rate),2) as qty'))
                //->where("username='".($user)."' AND contract_type='SHA' and locked = '0' AND invoice_status='1' $date"));
                ->where("username=?",$user)
                ->where("contract_type in('SHA','hardware','MS','ES')")
                ->where("locked= ?",'0')
                ->where("invoice_status= ?",'1')
                );
            }

         $totalContract=$totalContract + $result->qty;
        }
        return $totalContract;

    }


    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
            E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
        && !is_float($params[1])
        && !is_string($params[1])
        && $params[1] !== null
        && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
        && !is_int($params[2])
        && !is_float($params[2])
        && !is_string($params[2])
        && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
    function getBitAddr()
    {
        $admin_settings_obj= new Gbc_Model_DbTable_Adminsetting();
        $admin_result = $admin_settings_obj->fetchRow($admin_settings_obj->select()
        ->where("status=1"));

            
        return $admin_result;
    }
    function sslPrm()
    {
        return array("H23SDGF@#_%DhdsgfSG243","1234567891123456","aes-128-cbc");
    }

    function sslDec($msg)
    {
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        list ($pass, $iv, $method)=$common_obj->sslPrm();
        if(function_exists('openssl_decrypt'))
        return trim(urldecode(openssl_decrypt(urldecode($msg), $method, $pass, false, $iv)));
        else
        return trim(urldecode(exec("echo \"".urldecode($msg)."\" | openssl enc -".$method." -d -base64 -nosalt -K ".bin2hex($pass)." -iv ".bin2hex($iv))));
    }
    function getCotractNew($contractType,$id='')
    {
		if($contractType == "gb2"){
			$contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();
			$type = array('gb2');
		}else{
        	$contracts_obj=new Gbc_Model_DbTable_Contracts();
			$type = array('ES','MS');
		}
       // $type = array('SHA','hardware');
       // print_r($type);
        if(isset($id) && $id!='')
        {
            $contract_data = $contracts_obj->fetchAll($contracts_obj->select()
            //->where("contract_type = '" . ($contractType) . "' AND contract_id = '" . ($id) . "'" )
            ->where("contract_type = ?",$contractType)
            ->where("contract_id =?",$id)
            ->order("ordering ASC")
            );
        }
        else
        {


            $contract_data = $contracts_obj->fetchAll($contracts_obj->select()
            ->where("contract_type IN (?)", $type)
            ->where("status =?",1)
            ->order("ordering ASC")
            );

        }
	//print_r($contract_data);
        return $contract_data;
		//exit;

    }
	
     function checkKitStatus($kitNumber,$kit_type='',$kit_price='',$total_price =''){
		
        	$kits_obj=new Gbc_Model_DbTable_Kits();
			  $kits_data = $kits_obj->select();
				$kits_data->setIntegrityCheck(false)
				->from(array('A'=>"kits"),array('count(*) as countkit'));
		
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $and='';
		
		$kits_data1 = $kits_data;
        if(isset($kit_type) && $kit_type!='')
        {
            //$and= $and. "  AND kit_type = '".$kit_type."' ";
            $kits_data1->where('kit_type = ?',$kit_type);
        }

        if(isset($kit_price) && $kit_price!='')
        {
            //$and= $and. "  AND kit_price = '".$kit_price."' ";
          //  $kits_data1->where('kit_price = ?',$kit_price);
				$kits_data1->where("kit_price = '$kit_price' or kit_price = '$total_price'");
        }
        $kits_data1->where("kit_number LIKE ?",$kitNumber);
     
	//	echo $kits_data1->__toString();
        $rs = $kits_obj->fetchRow($kits_data1);
	//	var_dump($rs->countkit);
		if( $rs->countkit >0){
			
			if(isset($kit_type) && $kit_type!='')
			{
				//$and= $and. "  AND kit_type = '".$kit_type."' ";
				$kits_data->where('kit_type = ?',$kit_type);
			}

			if(isset($kit_price) && $kit_price!='')
			{
				//$and= $and. "  AND kit_price = '".$kit_price."' ";
			//	$kits_data->where('kit_price = ?',$kit_price);
				$kits_data->where("kit_price = '$kit_price' or kit_price = '$total_price'");
			}
			$kits_data->where("kit_number LIKE ?",$kitNumber);
			$kits_data->where("status = ?",'Active');
			$kits_data->where($db->quoteInto("kit_used_by =?", "") . ' OR ' . $db->quoteInto("kit_used_by is NULL"));
		//	echo $kits_data->__toString();
			$rs = $kits_obj->fetchRow($kits_data);
			//var_dump($rs->countkit);
		}else{
			$rs->countkit =-1;
		}
		

        return $rs->countkit;
    }
	
	function checkKitStatus_gb2($kitNumber,$kit_type='',$kit_price='',$contract_id =''){
		
        	$kits_obj=new Gbc_Model_DbTable_Gb2Kits();
			  $kits_data = $kits_obj->select();
				$kits_data->setIntegrityCheck(false)
				->from(array('A'=>"gb2_kits"),array('count(*) as countkit'));
		
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $and='';
		
		$kits_data1 = $kits_data;
        if(isset($kit_type) && $kit_type!='')
        {
            //$and= $and. "  AND kit_type = '".$kit_type."' ";
            $kits_data1->where('kit_type = ?',$kit_type);
        }

        if(isset($contract_id) && $contract_id!='')
        {
            //$and= $and. "  AND kit_price = '".$kit_price."' ";
          //  $kits_data1->where('kit_price = ?',$kit_price);
				$kits_data1->where("contract_id = ?",$contract_id);
        }
        $kits_data1->where("kit_number LIKE ?",$kitNumber);
     
	//	echo $kits_data1->__toString();
        $rs = $kits_obj->fetchRow($kits_data1);
	//	var_dump($rs->countkit);
		if( $rs->countkit >0){
			
			if(isset($kit_type) && $kit_type!='')
			{
				//$and= $and. "  AND kit_type = '".$kit_type."' ";
				$kits_data->where('kit_type = ?',$kit_type);
			}

		
			$kits_data->where("kit_number LIKE ?",$kitNumber);
			$kits_data->where("status = ?",'Active');
			$kits_data->where($db->quoteInto("kit_used_by =?", "") . ' OR ' . $db->quoteInto("kit_used_by is NULL"));
		//	echo $kits_data->__toString();
			$rs = $kits_obj->fetchRow($kits_data);
			//var_dump($rs->countkit);
		}else{
			$rs->countkit =-1;
		}
		

        return $rs->countkit;
    }
	
	
	
    function checkKitPrice($username,$kitNumber,$type =''){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
		if($type == "GB2"){
        	$kits_obj=new Gbc_Model_DbTable_Gb2Kits();
		}else{
			$kits_obj=new Gbc_Model_DbTable_Kits();
		}
            
        $kit_detail = $kits_obj->fetchRow($kits_obj->select()
        ->where("kit_number LIKE ?",$kitNumber)
        ->where("status = ?",'Active')
        ->where($db->quoteInto("kit_used_by= ?","") . ' OR ' . $db->quoteInto("kit_used_by is NULL"))
        );

        return $kit_detail;
    }

    function getContracts($contractId,$type='') {
		if($type == "GB2"){
        	$contracts_obj=new Gbc_Model_DbTable_Gb2Contracts();
		}else{
			$contracts_obj=new Gbc_Model_DbTable_Contracts();	
		}
            
        $contract_data = $contracts_obj->fetchRow($contracts_obj->select()
        //->where("1=1 and contract_id = '" . ($contractId) . "'")
        ->where("1=1")
        ->where("contract_id=?",$contractId)
        ->order("ordering")
        );
        return $contract_data;exit;
    }
    function createNewConHwInvoice($contractDetails, $username, $kitNumber,$status = 0,$type ='') {
		if($type == "GB2"){
			 $kits_obj=new Gbc_Model_DbTable_Gb2Kits();
			$invoices_obj = new Gbc_Model_DbTable_Gb2Invoices();
			 $now = "ETH_".time() . "_" . rand();
		}else{
			 $kits_obj=new Gbc_Model_DbTable_Kits();
			$invoices_obj = new Gbc_Model_DbTable_Invoices();	
			 $now = time() . "_" . rand();
		}
      
        $kit_created_qry = $kits_obj->fetchRow($kits_obj->select()
        ->where("kit_number =?",$kitNumber)
        );

        if(!empty($kit_created_qry) && sizeof($kit_created_qry)>0)
        {
            $kit_created=$kit_created_qry->created_on;
        }

		
       
        
        $insert_arr=array('invoice_id'=>$now,'use_kit_number'=>$kitNumber,'contract_id'=>$contractDetails["contract_id"],'contract_name'=>$contractDetails["contract_name"],'contract_type'=>$contractDetails["contract_type"],'contract_qty'=>$contractDetails["contract_qty"],'contract_rate'=>$contractDetails["total_price"],'invoice_status'=>$status,'username'=>$username,'isReferred'=>$contractDetails["isReferred"],'created_on'=>new Zend_Db_Expr('NOW()'),'kit_created'=>$kit_created);
        $insert_data=$invoices_obj->insert($insert_arr);
//print_r($insert_data);
        if(!empty($insert_data))
        {
            return $now;
        }
        else
        {
            return "failure";
        }



    }
	
    function getUserContracts($username, $contractId, $invoiceId = "", $pageno = "", $noOfRows = "",$contract_type="") {
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $and="";

        $data = $invoices_obj->select();
        if(isset($contractId) && $contractId!='')
        {
            //$and=$and. " AND contract_id = '" . ($contractId) . "'";
            $data->where('contract_id = ?',$contractId);

        }
        if(isset($username) && $username!='')
        {
            //$and=$and. " AND username = '" . ($username) . "'";
            $data->where('username = ?',$username);

        }
        if(isset($invoiceId) && $invoiceId!='')
        {
            //$and=$and. " AND invoice_id = '" . ($invoiceId) . "'";
            $data->where('invoice_id = ?',$invoiceId);

        }
        if(isset($contract_type) && $contract_type!='')
        {
            //$and=$and. " AND contract_type = '" . ($contract_type) . "'";
            $data->where('contract_type = ?',$contract_type);


        }
        $data->where('invoice_status <> ?',2);
        $data->order("invoice_id DESC");
        $inv_detail = $invoices_obj->fetchRow($data);


        return $inv_detail;
            
    }
	
	
    function getUserContracts_gb2($username, $contractId, $invoiceId = "", $pageno = "", $noOfRows = "",$contract_type="") {
        $invoices_obj = new Gbc_Model_DbTable_Gb2Invoices();
        $and="";

        $data = $invoices_obj->select();
        if(isset($contractId) && $contractId!='')
        {
            //$and=$and. " AND contract_id = '" . ($contractId) . "'";
            $data->where('contract_id = ?',$contractId);

        }
        if(isset($username) && $username!='')
        {
            //$and=$and. " AND username = '" . ($username) . "'";
            $data->where('username = ?',$username);

        }
        if(isset($invoiceId) && $invoiceId!='')
        {
            //$and=$and. " AND invoice_id = '" . ($invoiceId) . "'";
            $data->where('invoice_id = ?',$invoiceId);

        }
        if(isset($contract_type) && $contract_type!='')
        {
            //$and=$and. " AND contract_type = '" . ($contract_type) . "'";
            $data->where('contract_type = ?',$contract_type);


        }
        $data->where('invoice_status <> ?',2);
        $data->order("invoice_id DESC");
        $inv_detail = $invoices_obj->fetchRow($data);


        return $inv_detail;
            
    }
	


    function getUserByRefId($ref_id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $user = new Gbc_Model_DbTable_Userinfo();



        $inv_detail = $user->fetchRow($user->select()
        //->where("binaryUser is NOT NULL AND (sponsor_id='".$ref_id."' OR username='".$ref_id."')")
        ->where("binaryUser is NOT NULL")
        ->where($db->quoteInto("sponsor_id=?",$ref_id) . ' OR ' . $db->quoteInto("username=?",$ref_id))

        );




        return $inv_detail;
    }
    function getDepth($username ,&$result1='')
    {
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $arrayCategories1=array();

        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();

        $arrayCategories1=$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
        ->setIntegrityCheck(false)
        ->from(array('b'=>"binary_user_refences"),array('parent_username'))
        //->where("username = '$username'  and parent_id <> 0"));
        ->where("username =?  and parent_id <> 0", $username));


        if(empty($arrayCategories1) || sizeof($arrayCategories1)<=0)
        {
            return true;
        }

        for($i=0;$i<sizeof($arrayCategories1);$i++)
        {

            $result1[] = strtolower($arrayCategories1[$i]['parent_username']);
            $Gbc_Model_Custom_func_obj->getDepth($arrayCategories1[$i]['parent_username'],$result1);
        }

        return ($result1) ;

    }

    function getDepthOfBinaryUser($user_name){
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();

        $bin_data=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->setIntegrityCheck(false)
        ->from(array('b'=>"binary_user_refences"),array('depth'))
        ->where("username = ?",$user_name));

        return $bin_data;
    }

    function sendMail($to, $from, $subject, $message) {
        //printArr(func_get_args()); exit;
        $returnArr ='';
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'To: ' .$to. "\r\n";
        $headers .= 'From: ' .$from. "\r\n";
        // Mail it
        if(mail($to, $subject, $message, $headers)){
            $returnArr="Email Sent";
        } else {
            $returnArr="Email Send Error";
        }

        return $returnArr;
    }

	
    function getEarningsFor_gb2($username = "", $contractId = "", $invoiceId = "",$pageno = "",$noOfRows = "") {
        $earnings_obj=new Gbc_Model_DbTable_Gb2Earnings();
        $data = $earnings_obj->select();
        if(isset($username) && $username!='')
        {
            //$and.=" AND username = '" . ($username) . "'";
            $data->where('username = ?',$username);
        }
        if(isset($invoiceId) && $invoiceId!='')
        {
            //$and.=" AND invoice_id = '" . ($invoiceId) . "'";
            $data->where('invoice_id = ?',$invoiceId);
        }
        if(isset($contractId) && $contractId!='')
        {
            //$and.=" AND contract_id = '" . ($contractId) . "'";
            $data->where('contract_id = ?',$contractId);
        }
        $data->order("timestamp asc");
        $earn_detail = $earnings_obj->fetchAll($data);

        return $earn_detail;
    }

	
    function getEarningsFor($username = "", $contractId = "", $invoiceId = "",$pageno = "",$noOfRows = "") {
        $earnings_obj=new Gbc_Model_DbTable_Earnings();
        $data = $earnings_obj->select();
        if(isset($username) && $username!='')
        {
            //$and.=" AND username = '" . ($username) . "'";
            $data->where('username = ?',$username);
        }
        if(isset($invoiceId) && $invoiceId!='')
        {
            //$and.=" AND invoice_id = '" . ($invoiceId) . "'";
            $data->where('invoice_id = ?',$invoiceId);
        }
        if(isset($contractId) && $contractId!='')
        {
            //$and.=" AND contract_id = '" . ($contractId) . "'";
            $data->where('contract_id = ?',$contractId);
        }
        $data->order("timestamp asc");
        $earn_detail = $earnings_obj->fetchAll($data);

        return $earn_detail;
    }

    function getEarnings($username = "",$invoiceId = "",$start) {

        $earnings_obj=new Gbc_Model_DbTable_Earnings();
        $data = $earnings_obj->select();
        if(isset($username) && $username!='')
        {
            //$and.=" AND username = '" . ($username) . "'";
            $data->where('username = ?',$username);
        }
        if(isset($invoiceId) && $invoiceId!='')
        {
            //$and.=" AND invoice_id = '" . ($invoiceId) . "'";
            $data->where('invoice_id = ?',$invoiceId);
        }
        if(isset($contractId) && $contractId!='')
        {
            //$and.=" AND contract_id = '" . ($contractId) . "'";
            $data->where('contract_id = ?',$contractId);
        }
        $data->order("timestamp asc");

        $earn_detail = $earnings_obj->fetchAll($data);
        return $earn_detail;
    }
    function getGlobalVar($fieldName, $timestamp) {
        $golabal_var_obj=new Gbc_Model_DbTable_Globalvariables();
        if(isset($timestamp) && $timestamp!='')
        {

            $user_row = $golabal_var_obj->fetchAll($golabal_var_obj->select()
            ->where("timestamp<?",$timestamp)
            ->order("timestamp DESC")
            ->limit(1)
            );
        }
        else
        {
            $user_row = $golabal_var_obj->fetchAll($golabal_var_obj->select()
            ->order("timestamp DESC")
            ->limit(1)
            );
        }


        return $user_row;
    }
    function getKitPrice($contract_id='')
    {
        $contract_obj=new Gbc_Model_DbTable_Contracts();
        $row = $contract_obj->fetchRow($contract_obj->select()
        ->where("contract_id=?",$contract_id)
        );


        return $row;

    }
	
    function createInvoiceForKit_gb2($contractRate, $username, $status = 0, $shipAddress = '', $email = '', $noOfKits='',$referer_user='') {
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $now = "ETH_".time() . "_" . rand();
        $ip=$misc_obj->get_client_ip();
        $kit_invoices_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
	
			 $insert_arr=array('invoice_id'=>$now ,'kits_qty'=>$noOfKits,'contract_rate'=>$contractRate,'invoice_status'=>$status,'username'=>$username, 'referer_user' => $referer_user, 'ip_address'=>$ip,'creared_on'=>new Zend_Db_Expr('NOW()'));
		
     $insert_data=$kit_invoices_obj->insert($insert_arr);
     return $now;
    }
	
    function createKits_gb2($invoiceId, $noOfKits, $username, $price = '',$ContractPrice = '', $kit_type, $status = NULL,  $contract_id) {
        if(empty($status)){
            $status = "Inactive";
        }
        $comm_obj=new Gbc_Model_Custom_CommonFunc();
        $kits_obj=new Gbc_Model_DbTable_Gb2Kits();

        for ($i = 0; $i < $noOfKits; $i++) {
            $kitNumber =$comm_obj->getKitNumber($username,'GB2').$i;
		//	$price = "100";
			
			 $insert_arr=array('username'=>$username ,'kit_number'=>$kitNumber,'status'=>$status,'contract_id' => $contract_id, 'invoice_id'=>$invoiceId,'kit_price'=>$price,'contract_price' => $ContractPrice,'kit_type'=>$kit_type,'created_on'=>new Zend_Db_Expr('NOW()'));
			
           
		//	var_dump($insert_arr);
            $insert_data=$kits_obj->insert($insert_arr);
			
			
           //  echo $query;
          //   exit;
        }
		
    }
	
	
    function createInvoiceForKit($contractRate, $username, $status = 0, $shipAddress = '', $email = '', $noOfKits='',$contract_rateInMcap ='') {
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $now = time() . "_" . rand();
        $ip=$misc_obj->get_client_ip();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
		if($contract_rateInMcap){
			 $insert_arr=array('invoice_id'=>$now ,'kits_qty'=>$noOfKits, 'amtPaidinMcap' => $contract_rateInMcap, 'contract_rate'=>$contractRate,'invoice_status'=>$status,'username'=>$username,'shipping_address'=>$shipAddress,'shipping_mail'=>$email,'ip_address'=>$ip,'creared_on'=>new Zend_Db_Expr('NOW()'));
		}else{
			 $insert_arr=array('invoice_id'=>$now ,'kits_qty'=>$noOfKits,'contract_rate'=>$contractRate,'invoice_status'=>$status,'username'=>$username,'shipping_address'=>$shipAddress,'shipping_mail'=>$email,'ip_address'=>$ip,'creared_on'=>new Zend_Db_Expr('NOW()'));
		}
     $insert_data=$kit_invoices_obj->insert($insert_arr);
     return $now;
    }
	
    function createKits($invoiceId, $noOfKits, $username, $price = '',$ContractPrice = '', $kit_type, $status = NULL,  $contract_id,$price_in_mcap='') {
        if(empty($status)){
            $status = "Inactive";
        }
        $comm_obj=new Gbc_Model_Custom_CommonFunc();
        $kits_obj=new Gbc_Model_DbTable_Kits();

        for ($i = 0; $i < $noOfKits; $i++) {
            $kitNumber =$comm_obj->getKitNumber($username).$i;
		//	$price = "100";
			if($price_in_mcap){
				 $insert_arr=array('username'=>$username ,'kit_number'=>$kitNumber,'status'=>$status,'contract_id' => $contract_id, 'invoice_id'=>$invoiceId,'kit_price'=>$price,'kit_price_in_mcap'=>$price_in_mcap,'contract_price' => $ContractPrice,'kit_type'=>$kit_type,'created_on'=>new Zend_Db_Expr('NOW()'));
			}else{
				 $insert_arr=array('username'=>$username ,'kit_number'=>$kitNumber,'status'=>$status,'contract_id' => $contract_id, 'invoice_id'=>$invoiceId,'kit_price'=>$price,'contract_price' => $ContractPrice,'kit_type'=>$kit_type,'created_on'=>new Zend_Db_Expr('NOW()'));
			}
           
		//	var_dump($insert_arr);
            $insert_data=$kits_obj->insert($insert_arr);
			
			
           //  echo $query;
          //   exit;
        }
		
    }
	
	
    function getKitNumber($username,$type='') {
        $kits_obj=new Gbc_Model_DbTable_Kits();
		// $row=$kits_obj->fetchRow($kits_obj->select("max(id) as ID")
       // );
		 $row=$kits_obj->fetchRow($kits_obj->select()
					 ->from($kits_obj, 'max(id) as id')
      	  );

        $rand=md5(date('h:i:s').$row['id'].rand(1,9999));            // Pronto: Increased the random no. generation limit from 50 to 9999
		if($type == "GB2"){
			$KitNumber = "ETHGB" . strtoupper(substr($username, 0, 3)) .$rand;
		}else{
			$KitNumber = "GB" . strtoupper(substr($username, 0, 3)) .$rand;
		}
        return $KitNumber;
    }
	
    function getKitInvoiceDetails($invoiceId)
    {
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $user_row = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
        ->where("invoice_id=?",$invoiceId));
        return $user_row;
    }
    function fetchBTree_new($parentID,&$retArray,$limit){
        $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();
        /*$Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
         ->setIntegrityCheck(false)
         ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
         ->joinLeft(array('u'=>'user_info'),"u.username = a.username",array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
         ->joinLeft(array('i'=>'invoices'),"i.username = u.username",array('i.created_on'))
         ->joinLeft(array('c'=>'contracts'),"c.contract_id = i.contract_id",array('round(sum(c.total_price),2) as ContractPrice'))
         ->where("a.parent_id =$parentID")
         ->group("u.username")
         ->order("child_position ASC")
         );*/

        //$sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.child_position, UserInfo.name, UserInfo.ref_sponsor_id,UserInfo.isActiveId,UserInfo.lock_status, round(sum(Contract.total_price),2) as ContractPrice, Invoice.created_on FROM `binary_user_refences` as BinaryUserReferences left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username  left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1 left join contracts as Contract on Contract.contract_id = Invoice.contract_id WHERE BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username order by child_position ASC";
        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"BinaryUserReferences.username =  UserInfo.username",array('UserInfo.ref_sponsor_id','UserInfo.isActiveId','UserInfo.lock_status'))
        ->joinLeft(array('Invoice'=>'invoices')," Invoice.username = UserInfo.username and Invoice.invoice_status = 1",array('Invoice.created_on'))
        ->joinLeft(array('Contract'=>'contracts'),"Contract.contract_id = Invoice.contract_id",array('round(sum(Contract.total_price),2) as ContractPrice'))
        ->where("BinaryUserReferences.parent_id =$parentID")
        ->group("UserInfo.username")
        ->order("child_position ASC")
        );
        //$qry_res=$DBS->query($sql);
        //$Categories1 = $qry_res->fetchAll();
            

        if(isset($Categories1) && sizeof($Categories1)>0)
        {
            $Categories1 = $Categories1->toArray();
        }
        else
        {
            return true;
        }
            
        $limits =5;
        if($limit > $limits ){
            //echo $limit;exit;
            $limit =$limit- 1;
            return true;
        }
        $j = 1;

        for($i=0;$i<sizeof($Categories1);$i++){
            if(sizeof($Categories1) < 2){
                if($Categories1[$i]['child_position'] == "L"){
                    $newArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                    $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                    $newArray['limit']= $limit;
                    $retArray[]=$newArray;
                    //echo "<pre>";
                    //print_r($retArray);exit;
                    // var_dump($newArray);
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'L':'R';
                    $newArray['name']= 'N/A';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']= '';
                    $newArray['isactive']= '';
                    $newArray['lock_status']= '';
                    $newArray['amtpaid']= '';
                    $newArray['date']= '';
                    $newArray['limit']= $limit;
                    // var_dump($newArray);

                    $retArray[]=$newArray;
                    $limit =$limit+1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->fetchBTree_new($id,$retArray,$limit);

                }else{
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'L':'R';
                    $newArray['name']= 'N/A';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']= '';
                    $newArray['isactive']= '';
                    $newArray['lock_status']= '';
                    $newArray['amtpaid']= '';
                    $newArray['date']= '';
                    $newArray['limit']= $limit;
                    $retArray[]=$newArray;
                    // var_dump($newArray);

                    $newArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                    $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                    $newArray['limit']= $limit;
                    // var_dump($newArray);

                    $retArray[]=$newArray;
                    $limit = $limit + 1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->fetchBTree_new($id,$retArray,$limit);

                }

            }
            else{

                // echo "j ".$j;
                // echo "<br>";
                if($j ==1){
                    $limit =$limit + 1;
                }
                $j++;
                $newArray=array();
                $id=$Categories1[$i]['id'];
                $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                $newArray['child_position']=$child_position;
                $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                $newArray['limit']= $limit;

                // var_dump($newArray);

                $retArray[]=$newArray;
                    
                    
                //$limit +=1;
                // echo highlight_string("$id,$conn,$retArray,$limit");
                $Gbc_Model_Custom_func_obj->fetchBTree_new($id,$retArray,$limit);


            }
        }
        return ($retArray) ;
    }

    function checkNetworkDetails($user,$status=0){
        $bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
        $data = $bin_net_details_obj->select();
        if($status==2)
        {
            //$where="username = '".$user."'";
            $data->where('username = ?',$user);
        }
        else
        {
            //$where="username = '".$user."' and status = '$status'";
            $data->where('username = ?',$user);
            $data->where('status = ?',$status);

        }

        $UserData = $bin_net_details_obj->fetchAll($data);


        // echo "UserDataCount ".$UserDataCount;
        $all_user=array();
        if(isset($UserData) && sizeof($UserData)>0){
            for($k=0;$k<sizeof($UserData);$k++)
            {
                $all_user['leftContracts'] = $UserData[$k]['left_contracts'];
                $all_user['totalLeftUsers'] = $UserData[$k]['left_users'];
                $all_user['activeLeftUsers'] = $UserData[$k]['left_active_users'];
                $all_user['inactiveLeftUsers'] =  $UserData[$k]['left_inactive_users'];
                $all_user['rightContracts'] = $UserData[$k]['right_contracts'];
                $all_user['totalRightUsers'] = $UserData[$k]['right_users'];
                $all_user['activeRightUsers'] = $UserData[$k]['right_active_users'];
                $all_user['inactiveRightUsers'] =  $UserData[$k]['right_inactive_users'];
            }
        }
        return $all_user;
    }


    
function leftRightDetails($Users,$NetworkUsers,$count = null,$AllUsers = null)
    {
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $user = new Gbc_Model_DbTable_Userinfo();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        foreach($Users as $username){
            $totalContractFirst = $totalContractSecnd =$LeftActive = $RightActive = $maxLevel = 0;

            $result1=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"binary_user_refences"),array('A.id','A.username','A.child_position'))
            ->joinLeft(array('B'=>'invoices'),"B.username = A.username and B.contract_type IN ('SHA','hardware','MS','ES') AND B.invoice_status='1' ",array('round(sum(B.contract_rate),4) as contracts'))
            //->where("A.parent_username = '$username' and parent_id<>0")
            ->where("A.parent_username =? and parent_id<>0",$username)
            ->group("A.username")
            ->order("child_position ASC")
            );

            $childArray = array();
            $i = 0;$j=0;

            //var_dump(sizeof($result1));exit;
            for($cn=0;$cn<sizeof($result1);$cn++)
            {
            //    echo $result1[$cn]['username'];
            //    echo $i;
                $childArray[$i]['username'] = $result1[$cn]['username'];
                $childArray[$i]['contracts'] = $result1[$cn]['contracts'];
                $childArray[$i]['child_position'] = $result1[$cn]['child_position'];
                $i++;
            }
            //var_dump($childArray);exit;
            $childs_first=array();
            if(!empty($childArray[0])){
                $Gbc_Model_Custom_func_obj->getAllChildDetailsforBinary($childArray[0]['username'] ,$childs_first);

                $childs_first=array_merge(array($childArray[0] ),array_filter($childs_first));
                //print_r(($childs_first));
                //exit;
                //$totalContractsFirst = $Gbc_Model_Custom_func_obj->$childs_first;
                $totalContractFirst = array_sum(array_column($childs_first,'contracts'));
                
            //    print_r(($totalContractFirst));
                foreach($childs_first as $child){

                    $LeftactiveUser = $user->fetchRow($user->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' =>'user_info'),array('username'))
                    //->where("username = '".$child['username']."' and isActiveId = 1"));
                    ->where("username = ? and isActiveId = 1",$child['username']));


                    if(!empty($LeftactiveUser) && sizeof($LeftactiveUser)>0){
                            
                        $LeftActive++;
                    }
                }
            }

            
            $childs_second=array();
            if(!empty($childArray[1])){
                    
                $Gbc_Model_Custom_func_obj->getAllChildDetailsforBinary($childArray[1]['username'] ,$childs_second);
                $childs_second=array_merge(array($childArray[1] ),array_filter($childs_second));
                    //print_r(($childs_second));
                //exit;
                //$totalContractSecnd = array_sum($Gbc_Model_Custom_func_obj->array_column($childs_second,'contracts'));
                $totalContractSecnd = array_sum(array_column($childs_second,'contracts'));
                    //print_r(($totalContractSecnd));
                foreach($childs_second as $child){

                    $RightactiveUser = $user->fetchRow($user->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' =>'user_info'),array('username'))
                    ->where("username =? and isActiveId = 1",$child['username']));


                    if(!empty($RightactiveUser) && sizeof($RightactiveUser)>0){
                        $RightActive++;
                    }
                }
            }

            
            //print_r($RightactiveUser);
//print_r($RightActive);
            
            //exit;
            
            if($childArray[0]['child_position'] != "R"){
             $leftContracts = !empty($totalContractFirst)?$totalContractFirst:0;
             $rightContracts = !empty($totalContractSecnd)?$totalContractSecnd:0;
             $totalLeftUsers =!empty($childs_first[0])?count($childs_first):0;
             $activeLeftUsers = $LeftActive;
             $inactiveLeftUsers = !empty($childs_first[0])?count($childs_first)-$LeftActive:0;
             $totalRightUsers =!empty($childs_second[0])?count($childs_second):0;
             $activeRightUsers = $RightActive;
             $inactiveRightUsers = !empty($childs_second[0])?count($childs_second)-$RightActive:0;
             $leftNetwork = !empty($childs_first[0])?implode(',',$Gbc_Model_Custom_func_obj->array_column($childs_first,'username')):'';
             $rightNetwork = !empty($childs_second[0])?implode(',',$Gbc_Model_Custom_func_obj->array_column($childs_second,'username')):'';
            }else{
             $leftContracts = !empty($totalContractSecnd)?$totalContractSecnd:0;
             $rightContracts = !empty($totalContractFirst)?$totalContractFirst:0;
             $totalRightUsers =!empty($childs_first[0])?count($childs_first):0;
             $activeRightUsers = $LeftActive;
             $inactiveRightUsers = !empty($childs_first[0])?count($childs_first)-$LeftActive:0;
             $totalLeftUsers =!empty($childs_second[0])?count($childs_second):0;
             $activeLeftUsers = $RightActive;
             $inactiveLeftUsers = !empty($childs_second[0])?count($childs_second)-$RightActive:0;
             $rightNetwork = !empty($childs_first[0])?implode(',',$Gbc_Model_Custom_func_obj->array_column($childs_first,'username')):'';
             $leftNetwork = !empty($childs_second[0])?implode(',',$Gbc_Model_Custom_func_obj->array_column($childs_second,'username')):'';
            }
            //    print_r($leftContracts);
//print_r($leftContracts);
            
            $levels = $Gbc_Model_Custom_func_obj->CountNetworklevel($username);
            $maxLevel = max($levels);

            $depthquery = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
            ->where("username = ?",$username)
            );

            if(!empty($depthquery) && sizeof($depthquery)>0)
            {
                $depth = $depthquery[0]['depth'];
            }

            if((empty($depth) || !$depth) && $AllUsers="all"){
                $getDepth = array($username);
                $Gbc_Model_Custom_func_obj->getDepth($username,$getDepth);
                krsort($getDepth);
                $Depth = implode(',',$getDepth);
            }
            //$searchUser = array_search(strtolower($username), array_map('strtolower', $NetworkUsers),true);
            $searchUser = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
            ->where("username=?",$username)
            );
            //$searchUserType = gettype($searchUser);
            //if(($searchUserType) == "integer"){
            if(!empty($searchUser) && sizeof($searchUser)>0)
            {
                if(!$depth && $AllUsers="all"){
                    $upd_arr=array('network_levels'=>$maxLevel,'left_contracts'=>$leftContracts,'right_contracts'=>$rightContracts,'left_users'=>$totalLeftUsers,'right_users'=>$totalRightUsers,'left_active_users'=>$activeLeftUsers,'right_active_users'=>$activeRightUsers,'left_inactive_users'=>$inactiveLeftUsers,'right_inactive_users'=>$inactiveRightUsers,'status'=>'0','depth'=>$Depth,'left_network'=>$leftNetwork,'right_network'=>$rightNetwork,'updated_on'=>new Zend_Db_Expr('NOW()'));


                }else{
                    $upd_arr=array('network_levels'=>$maxLevel,'left_contracts'=>$leftContracts,'right_contracts'=>$rightContracts,'left_users'=>$totalLeftUsers,'right_users'=>$totalRightUsers,'left_active_users'=>$activeLeftUsers,'right_active_users'=>$activeRightUsers,'left_inactive_users'=>$inactiveLeftUsers,'right_inactive_users'=>$inactiveRightUsers,'status'=>'0','left_network'=>$leftNetwork,'right_network'=>$rightNetwork,'updated_on'=>new Zend_Db_Expr('NOW()'));


                }
                //$update=$bin_net_details_obj->update($upd_arr,"username = ?",$username);
                //$DB->quoteInto()

                    //    print_r($upd_arr);
                //    exit;
                
                $update=$bin_net_details_obj->update($upd_arr,$DB->quoteInto("username = ?",$username));

                    
            }
            else
            {
                    
                if((empty($depth) || !$depth) && (empty($Depth) || !$Depth)){
                    $getDepth = array($username);
                    $Gbc_Model_Custom_func_obj->getDepth($username ,$conn,$getDepth);
                    krsort($getDepth);
                    $Depth = implode(',',$getDepth);
                }
                $insert_arr=array('username'=>$username,'network_levels'=>$maxLevel,'left_contracts'=>$leftContracts,'right_contracts'=>$rightContracts,'left_users'=>$totalLeftUsers,'right_users'=>$totalRightUsers,'left_active_users'=>$activeLeftUsers,'right_active_users'=>$activeRightUsers,'left_inactive_users'=>$inactiveLeftUsers,'right_inactive_users'=>$inactiveRightUsers,'status'=>'0','depth'=>$Depth,'left_network'=>$leftNetwork,'right_network'=>$rightNetwork);
                $insert=$bin_net_details_obj->insert($insert_arr);
            }
            $count++;
        }

        if($_GET['usr'] || $count<=1){
            $all_user['leftContracts']=$leftContracts;
            $all_user['totalLeftUsers']=$totalLeftUsers;
            $all_user['activeLeftUsers']=$activeLeftUsers;
            $all_user['inactiveLeftUsers']=$inactiveLeftUsers;
            $all_user['rightContracts']=$rightContracts;
            $all_user['totalRightUsers']=$totalRightUsers;
            $all_user['activeRightUsers']=$activeRightUsers;
            $all_user['inactiveRightUsers']=$inactiveRightUsers;
            return $all_user;
        }else{
            return $count;
        }

    }

    /* function KitActivationAfterPayment($invoiceId){
        $paymentdetailsObj = new  Gbc_Model_DbTable_Paymentresponose();
        $misc_obj= new Gbc_Model_Custom_Miscellaneous();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $payment_response_detail = $payments_obj->fetchRow($payments_obj->select()
        ->where("invoice_id = '$invoiceId")
        );
        $paid_amount = $payment_response_detail->paid_amount;
        $username = $payment_response_detail->username;
        $upd_arr=array('amtPaid'=>$paid_amount,'updated_on'=>new Zend_Db_Expr('NOW()'));
        $profileData=$misc_obj->getUserInfo($username);
        $contact_phone=$profileData->phone;
        $numbers = array($contact_phone);
        $email = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".$rootURL."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p>";
        $userEmail = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".$rootURL."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p>";

        $invoiceDetails = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('kit_invoices'=>"kit_invoices"),array('sum(contract_rate) as total_paid', 'username'))
        ->where("invoice_id = '$invoiceId'"));

        if(isset($invoiceDetails) && sizeof($invoiceDetails)>0) {
        $amtDue = $invoiceDetails->total_paid;
        //$amtDue=$value;
        //printArr($value."-".$amtDue);
        //$amtDue = $value;
        if ($paid_amount < $amtDue) {
        $email .= "<p style='color: red'>The amount paid (" . $paid_amount . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
        $userEmail .= "<p style='color: red'>The amount paid (" . $paid_amount . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";

        $queryemail .= ", comment = '<div style = \"color:red; font-weight:bold;\">We have received only partial payment. Payment due:'".$amtDue - $paid_amount."'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>' ";
        $query['comment'] ='<div>We have received only partial payment. Payment due:'.$amtDue - $paid_amount.'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>';

        $query2=array('status'=> 'Partial Payment');
        $query2_data=$kits_obj->update($query2,"invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive'|| status='Partial Payment'");
        return "Partial payment paid. Kits not activated";
        } else {
        $queryemail .= ", invoice_status=1";
        $query['invoice_status']='1';

        $email .= "<p style='color: red'>This purchase has been activated by admin.</p>";
        $userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
        $query2=array('status'=> 'Active');
        $query2_data=$kits_obj->update($query2," WHERE invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive' || status='Partial Payment'");

        if(!empty($contact_phone)){

        $message = rawurlencode('Greetings from Gainbitcoin. Your payment is successful and your kit is now activated. Proceed to step 2 in My Purchases. Visit gainbitcoin.com');
        $numbers = implode(',', $numbers);
            
        // Prepare data for POST request
        $data = array('username' => $MSGusername, 'hash' => $MSGhash, 'numbers' => $numbers, "sender" => $MSGsender, "message" => $message);
        $data = 'login='.$MSGusername.'&pword='.$MSGhash.'&mobnum='.$numbers."&senderid=".$MSGsender."&msg=".$message;
        $MsgResponse =$Gbc_Model_Custom_func_obj-> sendMSG($data);
            
        if(!empty($MsgResponse)){
        $sms_log_obj=new Gbc_Model_DbTable_Smslog();
        $ins_data=array('username'=>$username,'mobile'=>$numbers,'msg'=>$message,'response_code'=>$MsgResponse);
        $saveMessage=$sms_log_obj->insert($ins_data);

        }
        }
        $ip_address= $Gbc_Model_Custom_func_obj->get_client_ip();
        $SessionUser = $_SESSION["user"];
        $data_ins=array('username'=>$username,'invoice_id'=>$invoiceId,'ip_address'=>$ip_address,'updated_by'=>$SessionUser);

        if($insertUpdate){
            
        return "Successfully activated";
        }
        }
        $buyerUsername = $invoiceDetails->username;

        $buyerUserInfo =$Gbc_Model_Custom_func_obj-> getUserInfo($buyerUsername);
        $buyerEmail = $Gbc_Model_Custom_func_obj->$buyerUserInfo->email_address;

        $Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);
        $Gbc_Model_Custom_func_obj->sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $userEmail);
            
        }
        $queryemail .= " where invoice_id='" . $invoiceId . "'";
        $que_data=$kit_invoices_obj->update($query,"invoice_id='" . $invoiceId . "'");
        $Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $queryemail);


     }*/
    function getModules()
    {
        $modules_obj=new Gbc_Model_DbTable_Modules();

        $result=$modules_obj->fetchAll($modules_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('c'=>"modules"),array('c.id as pid', 'c.module_name'))
        ->joinLeft(array('c2'=>'modules'),"c.id = c2.parent_id",array('c2.id','c2.module_name as subname'))
        ->where("c.parent_id=0")
        );
        return $result;
    }
    function getUserInfo($username) {

        $user_row = array();
        $user = new Gbc_Model_DbTable_Userinfo();
        $user_row = $user->fetchRow($user->select()
        ->setIntegrityCheck(false)
        ->from(array('u' =>'user_info'),array(  'username' ,'email_address','comm_email' ,'name' ,'country','phone' ,'salt' ,'password' ,'session_start_time' ,'session_id' ,'referrer_id' ,'otp','wallet_addr' ,'mcap_wallet_addr' ,'payout_option','sponsor_id' ,'ref_sponsor_id' ,'lock_status' ,'user_type','isActiveId','isLevelFull' ,'login_date','assign_date','ip_address' ,'created_on' ,'updated_on' ,'isVerified' ,'payment_status_hold' ,'b2_status','comment' ,'binaryUser' ,'authentication_type','placement','secret','login_pin','com_code','wallet_ver_code','profile_ver_code', 'withdraw_enabled'))
        ->where("username=?",$username));

        return $user_row;
    }


    
function getUserReset($username, $key = false) {
    $returnArr = array();
    $user_row = array();
        $user = new Gbc_Model_DbTable_Resetpassword();
        
    
    if($key){
        //$query = "SELECT * FROM reset_password u WHERE u.reset_code = '$key'";
        $user_row = $user->fetchRow($user->select()
        ->setIntegrityCheck(false)
        ->from(array('u' =>'reset_password'),array(  '*'))
        ->where("reset_code=?",$key));
    }else{
        //$query = "SELECT * FROM reset_password u WHERE u.username='$username'";
        
        $user_row = $user->fetchRow($user->select()
        ->setIntegrityCheck(false)
        ->from(array('u' =>'reset_password'),array(  '*'))
        ->where("username=?",$username));
    }
    // echo $query;
    //print_r($userInfo);
    //$userInfo=(array)json_decode($user_row,true);
    //echo $user_row->username;
    
    if(!empty($user_row) || sizeof($user_row)>=0){
        if(empty($user_row->username)){
            //username does not exist
            $returnArr["errCode"][1]=1;
            $returnArr["errMsg"] = "Could not find username: ";
            
        } else {
            $returnArr["errCode"][-1]=-1;
            $returnArr["errMsg"] = $user_row;
        }
        
    } else {
        $returnArr["errCode"][3]=3;
        $returnArr["errMsg"] = "Could not get user info: ";
    }
    //print_r($returnArr);
    return $returnArr;
}


    function GetAccessRightByUserId($module_id,$user_id)
    {
        $access_obj=new Gbc_Model_DbTable_Accessright();
        $row = $access_obj->fetchRow($access_obj->select()
        ->where("module_id= ?",$module_id)
        ->where("user_id= ?",$user_id));
        return $row ;
    }
    function EditContractHardware($contractDetails) {

        try
        {   $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $contract_obj  = new Gbc_Model_DbTable_Contracts();
        $changecontractlogObj=new Gbc_Model_DbTable_Changecontractlog();
        if(($contractDetails["contract_type"])=='SHA')
        {
            try
            {
                $contract_type=$contractDetails['contract_type'];

                $data=array('contract_name'=>$contractDetails["contract_name"],'contract_qty'=>$contractDetails["contract_qty"],'description'=>$contractDetails["desc"],'total_price'=>$contractDetails["total_price"]);


                //    $upd_data=$contract_obj->update($data,"contract_id='1' AND contract_type='".$contract_type."'");
                // $DB->quoteInto()
                    
                $upd_data=$contract_obj->update($data,$DB->quoteInto("contract_id='1' AND contract_type=?",$contract_type));
                    

                $changedata=array('contract_type'=>$contractDetails["contract_type"],'old_name'=>$contractDetails["old_contract_name"],'new_name'=>$contractDetails["contract_name"],'old_qty'=>$contractDetails["old_contract_qty"],'new_qty'=>$contractDetails["contract_qty"],'old_price'=>$contractDetails["old_total_price"],'new_price'=>$contractDetails["total_price"]);
                $ins=$changecontractlogObj->insert($changedata);
                return "Contract updated successfully.";

            }
            catch(Exception $e)
            {
                echo $e->getMessage();exit;
            }








        }
        if(($contractDetails["contract_type"])=='hardware')
        {

            $data=array('contract_name'=>$contractDetails["contract_name"],'contract_qty'=>$contractDetails["contract_qty"],'description'=>$contractDetails["desc"],'total_price'=>$contractDetails["total_price"]);

            //$upd=$editcontractObj->update($data,"contract_id='2' AND contract_type='".($contractDetails["contract_type"])."' ");
            $upd=$editcontractObj->update($data, $DB->quoteInto("contract_id='2' AND contract_type=?",$contractDetails["contract_type"]));


            $changedata=array('contract_type'=>$contractDetails["contract_type"],'old_name'=>$contractDetails["old_contract_name"],'new_name'=>$contractDetails["contract_name"],'old_qty'=>$contractDetails["old_contract_qty"],'new_qty'=>$contractDetails["contract_qty"],'old_price'=>$contractDetails["old_total_price"],'new_price'=>$contractDetails["total_price"]);
            $ins=$changecontractlogObj->insert($changedata);

            if(!empty($upd)){

                return "A Contract successfully Updated.";
            } else {

                return "A Contract NOT Edited: ";
            }


        }
        }catch(Exception $e)
        {
            return $e->getMessage();
        }

    }
    function KitActivationAfterPayment($invoiceId){


     $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $payment_resp_obj=new Gbc_Model_DbTable_Paymentresponose();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $misc_obj=new Gbc_Model_Custom_Miscellaneous();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $sms_log=new Gbc_Model_DbTable_Smslog();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $pay_after_timeout=new Gbc_Model_DbTable_Payaftertimeout();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();


        $payment_response_details = $payment_resp_obj->fetchRow($payment_resp_obj->select()
        ->where("invoice_id = ?",$invoiceId));

        try{

            $paid_amount = $payment_response_details->paid_amount;

            $username = $payment_response_details->username;

            $query=array('amtPaid'=>$paid_amount,'updated_on'=>new Zend_Db_Expr('NOW()'));

            $profileData = $misc_obj->getUserInfo($username);

         $contact_phone = $profileData->phone;

         $numbers = array($contact_phone);

         $email = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".BASEPATH."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p></div>";

            $userEmail = "<div style='border: solid thin black; padding: 10px'><div style='padding: 0px;'><img src='".BASEPATH."/res/img/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A payment has been received with the following details:</p><p>Invoice ID: " . $invoiceId . "</p></div>";

            $invoiceDetails = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('kit_invoices'=>"kit_invoices"),array('sum(contract_rate) as total_paid', 'username'))
            ->where("invoice_id = ?",$invoiceId));

            if(isset($invoiceDetails) && sizeof($invoiceDetails)>0) {
                    
                $amtDue = $invoiceDetails->total_paid;
                    
                $queryemail='';

                //$amtDue=$value;
                //printArr($value."-".$amtDue);
                //$amtDue = $value;

                if ($paid_amount < $amtDue){

                    $email .= "<p style='color: red'>The amount paid (" . $paid_amount . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please manually activate this invoice from the admin panel.</p>";
                    $userEmail .= "<p style='color: red'>The amount paid (" . $paid_amount . " BTC) is not the same as the amount due (" . $amtDue . " BTC). This invoice is not activated automatically. Please contact us for further assistance.</p>";

                    $queryemail .= ", comment = '<div style = \"color:red; font-weight:bold;\">We have received only partial payment. Payment due:'".$amtDue - $paid_amount."'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>' ";

                    $query['comment'] ='<div>We have received only partial payment. Payment due:'.$amtDue - $paid_amount.'<div>Please contact support@gainbitcoin.com to activate kit.</div></div>';

                    $query2=array('status'=> 'Partial Payment');
                    //$query2_data=$kits_obj->update($query2,"invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive'|| status='Partial Payment')");
                    $query2_data=$kits_obj->update($query2,$DB->quoteInto("invoice_id =? AND (status='Pending' || status='Inactive'|| status='Partial Payment')",$invoiceId));

                    //    return "Partial payment paid. Kits not activated";
                    $msg="Partial payment paid. Kits not activated";
                    //$authUserNamespace->msg=$msg;
                    return $msg;

                } else{

                    $queryemail .= ", invoice_status=1";
                    $query['invoice_status']='1';

                    $email .= "<p style='color: red'>This purchase has been activated.</p>";
                    $userEmail .= "<p style='color: red'>This purchase has been activated and you will be able to see your contract details under the My Purchases section of your user dashboard.</p>";
                    $query2=array('status'=> 'Active');
                    //$query2_data=$kits_obj->update($query2,"invoice_id = '" . $invoiceId . "' AND (status='Pending' || status='Inactive' || status='Partial Payment')");
                    $query2_data=$kits_obj->update($query2,$DB->quoteInto("invoice_id = ?  AND (status='Pending' || status='Inactive' || status='Partial Payment')",$invoiceId));

                    if(!empty($contact_phone)){
                            
                        $message = rawurlencode('Greetings from Gainbitcoin. Your payment is successful and your kit is now activated. Proceed to step 2 in My Purchases. Visit gainbitcoin.com');
                        $numbers = implode(',', $numbers);
                        $data = array('username' => $MSGusername, 'hash' => $MSGhash, 'numbers' => $numbers, "sender" => $MSGsender, "message" => $message);
                        $data = 'login='.$MSGusername.'&pword='.$MSGhash.'&mobnum='.$numbers."&senderid=".$MSGsender."&msg=".$message;
                        $MsgResponse = $Gbc_Model_Custom_func_obj->sendMSG($data);
                            
                        if(!empty($MsgResponse)){
                            //$arr=array('username'=>$username,'mobile'=>$numbers,'msg'=>$message,'response_code'=>$MsgResponse);
                            //$ins_data=$sms_log->insert($arr);

                        }
                            
                    }

                    $ip_address= $misc_obj->get_client_ip();

                    $SessionUser = $authUserNamespace->user;

                    $pay_arr=array('username'=>$username,'invoice_id'=>$invoiceId,'ip_address'=>$ip_address,'updated_by'=>$SessionUser);

                    $insertUpdate=$pay_after_timeout->insert($pay_arr);

                    if(!empty($insertUpdate)){
                            
                        $returnMessage =  "Successfully activated";
                    }




                }
                    
                $buyerUsername='';
                $buyerEmail='';
                //$invoiceDetails = $kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                //->where("invoice_id = '".$invoice_id."'"));


                $buyerUsername = $invoiceDetails->username;

                $buyerUserInfo =$misc_obj-> getUserInfo($buyerUsername);
                $buyerEmail = $buyerUserInfo->comm_email;
                if(empty($buyerEmail) || $buyerEmail =='')
                {
                    $buyerEmail = $buyerUserInfo->email_address;
                }

                //$Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "A payment has been received", $email);

                //$Gbc_Model_Custom_func_obj->sendMail($buyerEmail, "admin@gainbitco.in", "Your contract purchase has been activated.", $userEmail);

              /*  $to = "thegainbitcoin@gmail.com";
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'A payment has been received';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
		*/

                $to = $buyerEmail;
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Your contract purchase has been activated';
                $message = $userEmail;
                $htmlMessage = $userEmail;
                $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
            }
            //$que_data=$kit_invoices_obj->update($query,"invoice_id='" . $invoiceId . "'");
            $que_data=$kit_invoices_obj->update($query,$DB->quoteInto("invoice_id=?",$invoiceId));

            //$Gbc_Model_Custom_func_obj->sendMail("thegainbitcoin@gmail.com","admin@gainbitco.in", "A payment has been received", $queryemail);

      /*      $to = "thegainbitcoin@gmail.com";
            $from = 'support@gainbitcoin.com';
            $replyTo = 'thegainbitcoinhelp@gmail.com';
            $subject = 'A payment has been received';
            $message = $queryemail;
            $htmlMessage = $queryemail;
            $sendMail = $Gbc_Model_Custom_func_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
	*/
            return "Successfully activated";

        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }

    function adminPayout($files,$file){


        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

        if(!empty($files['file']['tmp_name']) && (($files['file']['type'] == "application/vnd.ms-excel") || ($files['file']['type'] == "application/octet-stream")||($files['file']['type'] == "application/download")|| ($files['file']['type'] == "application/pdf") || ($files['file']['type'] == "text/csv"))) {

            $ext = pathinfo($files['file']['name'], PATHINFO_EXTENSION);

            if($ext=='csv'){

                $target_dir =  FILE_UPLOAD_PATH."/res/files/payout_files/";

                

                if(!file_exists($target_dir)){

                    mkdir($target_dir);

                }
                $target_file = $target_dir . $file;

                // return $target_file;exit;
                // echo $image;
                // exit;
                //return $files['file']['tmp_name'];

                if(move_uploaded_file($files['file']['tmp_name'], $target_file)){

                 $msg = "Successfully uploaded";


                 // $SaveEntry = PayToUserCSV($file);

                 $common_obj = new Gbc_Model_Custom_CommonFunc();
                 $SaveEntry=$common_obj->PayToUserCSV($file);


                 if($SaveEntry ){

                     $msg .= " and saved";
                 }
                }
            }else{
                //return "else";
                //echo $_FILES['size'];
                $message = "Only CSV file allowed";
                $authUserNamespace->message=$message;


            }
        }
        return $msg;
    }

    function PayToUserCSV($filename){

        // $filename = "OuterRight.csv";
        $file = FILE_UPLOAD_PATH."/res/files/payout_files/".$filename;

        //fWriteCSV($childs_second,$file);
            

        $flag = false;
        $fp = fopen($file, 'r');

        /* while($datas = fgetcsv($fp, 0,',')){
            $data[] = $datas;
            } */

        while (($result = fgetcsv($fp, 0,',')) !== false) {

            if (array(null) !== $result) {
                // ignore blank lines
                if($result[0] != ''){

                    $data[] = $result;

                }

            }
        }
        /*echo "<pre>";
         print_r($data);exit;*/
        // var_dump($data);
        if(!empty($data)){
        
        	
        	if(empty($data[1][3]) || (empty($data[1][4]))){
        		echo "Incorrect Format";
        		exit;
        	}
        	$withdrawalDetails['date']=$data[1][3];
        	$withdrawalDetails['withdrawal_type'] = strtolower($data[1][4]);
        	 
		//	echo $withdrawalDetails['withdrawal_type'];
            foreach ($data as $key => $row) {

                if ($key > 0) {

                    $username = $row[0];

                    $withdrawalDetails["username"] = $row[0];

                    $withdrawalDetails["addr"] = $row[1];
					
                    //$withdrawalDetails["btc_amt"] = round($row[2]*2,8);
					if(isset($withdrawalDetails['withdrawal_type']) && (($withdrawalDetails['withdrawal_type'] =="kit_generation") || ($withdrawalDetails['withdrawal_type'] =="no_token"))){
						$withdrawalDetails["btc_amt"] = round($row[2],8);
					}else{
						$withdrawalDetails["btc_amt"] = round($row[2]*2,8);
						$withdrawalDetails["new_token_amt"] = $row[2];
					}
					//echo $withdrawalDetails['btc_amt'];
                    //$withdrawalDetails['date']=$row[3];
                    //$withdrawalDetails['withdrawal_type'] = $row[4];
                    $withdrawalDetails['transaction_id'] = $row[5];
                    $withdrawalDetails["chosen_coin"] = "BTC";
                    $withdrawalDetails["status"] = 1;

                    $withdrawalDetails["nic_amt"] = 0;
                    $withdrawalDetails["flt_amt"] = 0;

                    //$updater = addNewWithdrawalByAdmin($withdrawalDetails);
                    $common_obj = new Gbc_Model_Custom_CommonFunc();
                    $updater=$common_obj->addNewWithdrawalByAdmin($withdrawalDetails);
					$user = str_replace('"','',$username);
                    if(!empty($updater) && $updater!='fails'){
						$db = Zend_Db_Table::getDefaultAdapter();
						
				//	if(isset($withdrawalDetails['withdrawal_type']) && $withdrawalDetails['withdrawal_type']!="kit_generation"){
					if(isset($withdrawalDetails['withdrawal_type']) && (($withdrawalDetails['withdrawal_type'] !="kit_generation") && ($withdrawalDetails['withdrawal_type'] !="no_token"))){
						$upd_qry=$db->query("UPDATE `final_balance` SET `new_token_amt` = new_token_amt+".$withdrawalDetails["new_token_amt"].", `updated_on` = NOW() WHERE (username='".$user."')");
					}
                        //account created. Commit info
                            
                        //$UpdateFinalLedger = UpdateFinalLedger($withdrawalDetails, $username);
                        $common_obj = new Gbc_Model_Custom_CommonFunc();
                        $UpdateFinalLedger=$common_obj->UpdateFinalLedger($withdrawalDetails, $username);
                            
                        //$email = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'><img src='".$rootURL."/res/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A withdrawal has been sent by admin with the following details: </p><ul><li>Username: ".$username."</li><li>Balances: Total Amount: ".number_format(($totalAmount),8)."<br/></li><li>Withdrawal: ".number_format($withdrawalDetails["btc_amt"],8)." BTC from total Outputs</li><li>Wallet Address: ".$userInfo['wallet_addr']."</li></ul></div>";
                        //sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "GainBitco: Withdrawal Requested", $email);


                        //email to user
                        //$Useremail = "<div style='padding: 10px; border: solid thin black'><div style='padding: 0px;'><img src='".$rootURL."/res/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div><p>A withdrawal has been sent by admin with the following details: </p><ul><li>Username: ".$username."</li><li>Balances: Total Amount: ".number_format(($totalAmount),8)."<br/></li><li>Withdrawal: ".number_format($withdrawalDetails["btc_amt"],8)." BTC from total Outputs</li><li>Wallet Address: ".$userInfo['wallet_addr']."</li></ul><p>Please check your wallet address.</p></div>";
                        /* $msg = "Your withdrawal request has been sent.";


                        $alertMessage = createAlertMsgBox($msg);
                        printArr($alertMessage); */

                    } else {
                        //Error creating account show error and die

                        $msg = "Error: Could not record your withdrawal request at the moment. Please try again later or contact us if the problem persists.";
                            
                        //printArr($msg);




                    }
                    $status = true;
                }

            }
        }
        fclose($fp);

     return $status;
    }

    function addNewWithdrawalByAdmin($withdrawalDetails)
    {
    
    	try{
    		//print_r($withdrawalDetails);exit;
    		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
    		$withdrawalDetails_obj= new Gbc_Model_DbTable_Withdrawals();
    
    /*
    		$resultwithdrawal=$withdrawalDetails_obj->fetchRow($withdrawalDetails_obj->select()
    				->setIntegrityCheck(false)
    				->from(array('withdrawals'),array('id'))
    				->order("id DESC"));
    
    		$result1=sizeof($resultwithdrawal);
    		if(isset($result1) && $result1>0)
    		{
    			$id=$resultwithdrawal->id+1;
    				
    		}
    		else
    		{
    			$id=1;
    		}
    
    
    */
    
    		$misc_obj = new Gbc_Model_Custom_Miscellaneous();
    		$LoggedinUser=$authUserNamespace->user;
    
    		$returnArr = array();
    
    
    		if(!empty($withdrawalDetails["date"])){
    
    			$withdrawalDate = str_replace('"','',$withdrawalDetails["date"]);
    			$date = date('Y-m-d H:i:s',strtotime($withdrawalDate));
    			//$dateTime = "'".$date."'";
    			$dateTime = $date;
    
    		}else{
    			//$dateTime = 'NOW()';
    			$dateTime= new Zend_Db_Expr('NOW()');
    		}
    		$created_on = new Zend_Db_Expr('NOW()');
    		if(!empty($withdrawalDetails["username"])){
    
    			$withdrawalDetails["username"] = str_replace('"','',$withdrawalDetails["username"]);
    				
    		}
    
    		$withdrawalHash = sha1(($withdrawalDetails["username"].time()));
    
    		//$ip=get_client_ip();
    
    		$ip=$misc_obj->get_client_ip();
    
    		if(!empty($withdrawalDetails["request_by"]) && isset($withdrawalDetails["request_by"]))
    		{
    			$requestby=$withdrawalDetails["request_by"];
    
    		}
    		else
    		{
    			$requestby=$LoggedinUser;
    
    		}
    		if(!empty($withdrawalDetails['transaction_id'])&& $withdrawalDetails['transaction_id']!="")
    		{
    			$transactionid=$withdrawalDetails['transaction_id'];
    
    		}
    		else {
    			$transactionid="";
    		}
    		$db = Zend_Db_Table::getDefaultAdapter();
    
    		try{
    			//echo $dateTime;exit;
    		//	$withdrawalsdata=array("id"=>$id,"username"=>$withdrawalDetails["username"],"btc_amt"=>$withdrawalDetails["btc_amt"],"withdrawal_type"=>$withdrawalDetails["withdrawal_type"],"alt_amt"=>$withdrawalDetails["btc_amt"],"chosen_coin"=>$withdrawalDetails["chosen_coin"],"addr"=>$withdrawalDetails["addr"],"status"=>$withdrawalDetails["status"],"timestamp"=>$dateTime,"withdrawalHash"=>$withdrawalHash,"ip_address"=>$ip,"request_by"=>$requestby,"transaction_id"=>$transactionid,"created_on"=>$created_on);
    			$withdrawalsdata=array("username"=>$withdrawalDetails["username"],"btc_amt"=>$withdrawalDetails["btc_amt"],"withdrawal_type"=>$withdrawalDetails["withdrawal_type"],"alt_amt"=>$withdrawalDetails["btc_amt"],"chosen_coin"=>$withdrawalDetails["chosen_coin"],"addr"=>$withdrawalDetails["addr"],"status"=>$withdrawalDetails["status"],"timestamp"=>$dateTime,"withdrawalHash"=>$withdrawalHash,"ip_address"=>$ip,"request_by"=>$requestby,"transaction_id"=>$transactionid,"created_on"=>$created_on);
    			//print_r($withdrawalsdata);
    			//exit;
    			$insertwithdrawaldata=$withdrawalDetails_obj->insert($withdrawalsdata);
    			//print_r($insertwithdrawaldata);exit;
    		}
    		catch(Exception $e)
    		{
    			echo $e->getMessage();exit;
    		}
    
    
    		/* $result = runTransactionedQuery($query, $conn);*/
    
    		if (!empty($insertwithdrawaldata)) {
    			// $returnArr["errCode"] = array("-1" => -1);
    			//  $returnArr["errMsg"] = "A New Withdrawal Request successfully added.";
    			$msg = "A New Withdrawal Request successfully added.";
    			return "success";
    
    				
    			//$returnArr["withdrawalHash"] = $withdrawalHash;
    		} else {
    			// $returnArr["errCode"] = array("5" => 5);
    			// $returnArr["errMsg"] = "A New Withdrawal Request NOT Added: " . $result["errMsg"];
    			$msg = "A New Withdrawal Request NOT Added: ";
    			return "fails";
    
    		}
    
    
    		//return $returnArr;
    
    	}
    	catch(Exception $e)
    	{
    		echo $e->getMessage();
    	}
    
    }
    function UpdateFinalLedger($withdrawalDetails, $userName) {
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        if(!empty($withdrawalDetails['user_to'])){
            $username = ($withdrawalDetails['user_to']);

            $UpdateFinalbalance=$common_obj->UpdateFinalbalance($username);
            //UpdateFinalbalance($username,$conn);
        }
        // $username = $userName;
        $username = str_replace('"','',$userName);
        //echo $username;exit;
        //UpdateFinalbalance($username,$conn);
        $UpdateFinalbalance=$common_obj->UpdateFinalbalance($username);

    }

    function UpdateFinalLedger_2($withdrawalDetails, $userName,$conn) {
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        if(!empty($withdrawalDetails['user_to'])){
            $username = ($withdrawalDetails['user_to']);

            $UpdateFinalbalance=$common_obj->UpdateFinalbalance_2($username);
            //UpdateFinalbalance($username,$conn);
        }
        // $username = $userName;
        $username = str_replace('"','',$userName);
        //echo $username;exit;
        //UpdateFinalbalance($username,$conn);
        $UpdateFinalbalance=$common_obj->UpdateFinalbalance_2($username);



    }
    
    function UpdateFinalbalance_2($username)
    {
        $date = !empty($_REQUEST['CurrentDate'])?"".$_REQUEST['CurrentDate']."":date('Y-m-d');
        if(!empty($_REQUEST['CurrentDate']) && $_REQUEST['CurrentDate']!='')
        $date=date('Y-m-d h:i:s');
        global $blanks;
        //********calculate Total Withdrawal********//
        $withdrawal_obj= new Gbc_Model_DbTable_Withdrawals();
        $FundTransfersObj= new Gbc_Model_DbTable_FundTransfers();
        $final_ledger_obj=new Gbc_Model_DbTable_Finalledger2();
        $invoice_Obj=new Gbc_Model_DbTable_Invoices();
        $final_balace=new Gbc_Model_DbTable_Finalbalance2();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        //$query = "SELECT round(SUM(btc_amt),8) as sum_net_withdrawn FROM withdrawals WHERE `status`<>2";
        $data = $withdrawal_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('w'=>"withdrawals"),array("round(SUM(btc_amt),8) as sum_net_withdrawn"));
        $data->where('status <> ?',2);

        if (!empty($username) && $username!=""){

            //$where .= " AND final_withdrawal= 'final_withdrawal'";
            //$where .= " AND username = '" . $username . "'";

            $data->where('final_withdrawal = ?','final_withdrawal');
            $data->where('username = ?',$username );
        }


        $resultwithdrawal = $withdrawal_obj->fetchRow($data);


        //$result = runQuery($query, $conn);
        $result=sizeof($resultwithdrawal);

        $totalDailyWithdrawn = 0;

        if(!empty($resultwithdrawal) && sizeof($resultwithdrawal) > 0){

            $totalDailyWithdrawn = $resultwithdrawal->sum_net_withdrawn;

        } else {
            $totalDailyWithdrawn = 0;
        }

        $FundTransfersres=$FundTransfersObj->fetchRow($FundTransfersObj->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"fund_transfers"),array("round(SUM(fund_amt),8) as total_fund"))
        ->where("user_to = ?",$username));

        $totalFund = 0;
        if (!empty($FundTransfersres)&& sizeof($FundTransfersres)>0) {
            //fetching each row in associative array format and assigning it to $row
            //$total_fund = mysql_fetch_assoc($result1["dbResource"]);
            $totalFund = $FundTransfersres->total_fund;


        } else {

            $totalFund = 0;
        }

        //********calculate Total Admin Fees********//
        // $query = "SELECT round(SUM(total_amt),8) as total_amt, round(SUM(adm_roi_payout),8) as adm_roi_payout, round(SUM(adm_bin_payout),8) as adm_bin_payout, round(SUM(adm_ref_payout),8) as adm_ref_payout FROM final_ledger WHERE username='$username'";
        $finalledgerobj=$final_ledger_obj->fetchRow($final_ledger_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"final_ledger_2"),array('round(SUM(total_amt),8) as total_amt','round(SUM(adm_roi_payout),8) as adm_roi_payout','round(SUM(adm_bin_payout),8) as adm_bin_payout','round(SUM(adm_ref_payout),8) as adm_ref_payout'))
        ->where("username=?",$username));

        if(!empty($finalledgerobj)&& sizeof($finalledgerobj)>0)
        {

            $total_amt=number_format($finalledgerobj->total_amt,8,'.','');
            $adm_roi_payout=$finalledgerobj->adm_roi_payout;
            $adm_bin_payout=$finalledgerobj->adm_bin_payout;
            $adm_ref_payout=$finalledgerobj->adm_ref_payout;

            //$sql="SELECT count(invoice_id) as check_direct, round(SUM(contract_rate),8) as contracts from invoices where username='".$username."' AND invoice_status=1  and created_on < '$date'";
            $invoiceObj=$invoice_Obj->fetchRow($invoice_Obj->select()
            ->setIntegrityCheck(false)
            ->from(array('i'=>"invoices"),array('count(invoice_id) as check_direct','round(SUM(contract_rate),8) as contracts'))
            ->where("username=?", $username)
            ->where("invoice_status=?", 1)
            ->where("created_on < ?", $date)
            );
            //->where("username=? AND invoice_status=1  and created_on < '$date'",$username));


            if (!empty($invoiceObj)&& sizeof($invoiceObj)>0){
                //$rw = mysql_fetch_assoc($res_chk["dbResource"]);
                $contractDetails['isBenfit']=$invoiceObj->contracts;

            }

            if(!empty($contractDetails['isBenfit']) && $contractDetails['isBenfit']>=0.5){
                // $total_amt=number_format(($daily_earning_amt + $ref_amt + $binary_amt),8,'.','');
                $total_admin_fees = $adm_roi_payout + $adm_bin_payout + $adm_ref_payout;

            }else{
                // $total_amt=number_format(($daily_earning_amt),8,'.','');
                $total_admin_fees = $adm_roi_payout;
            }
            //$total_amt=$row['total_amt']+$total_admin_fees;
            $total_amt=$finalledgerobj->total_amt+$total_admin_fees;


        }
        $bal_amt = number_format(($total_amt - $total_admin_fees - $totalDailyWithdrawn) + $totalFund,8,'.','');

        //$FindUserName = "select * from final_balance where username = '$username'";
        $FindUserName=$final_balace->fetchRow($final_balace->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"final_balance_2"))
        ->where("username = ?",$username));
        $totalDailyWithdrawn = number_format($totalDailyWithdrawn,8,'.','');
     if (!empty($FindUserName) && sizeof($FindUserName)>0)
     {
         $total_amt= number_format($total_amt,8,'.','');
         $total_admin_fees= number_format($total_admin_fees,8,'.','');
         $totalFund =   number_format($totalFund,8,'.','');

         if(!empty($FindUserName)){
             $username = $FindUserName->username;
             $upd_arr=array('total_amt'=>$total_amt,'total_admin_fees'=>$total_admin_fees,'total_withdrawal'=>$totalDailyWithdrawn,'bal_amt'=>$bal_amt,'total_fund'=>$totalFund,'updated_on'=>new Zend_Db_Expr('NOW()'));
             //$upd_qry=$final_balace->update($upd_arr,"username=?",$username);
             $upd_qry=$final_balace->update($upd_arr,$DB->quoteInto("username=?",$username));

         }
         else
         {
             $ins_arr=array('username'=>$username,'total_amt'=>$total_amt,'total_admin_fees'=>$total_admin_fees,'total_withdrawal'=>$totalDailyWithdrawn,'total_fund'=>$totalFund,'bal_amt'=>$bal_amt);
             $ins_qry=$final_balace->insert($ins_arr);
         }


     }



    }

    function UpdateFinalbalance($username)
    {
        $date=date('Y-m-d h:i:s');
        global $blanks;
        //********calculate Total Withdrawal********//
        $withdrawal_obj= new Gbc_Model_DbTable_Withdrawals();
        $FundTransfersObj= new Gbc_Model_DbTable_FundTransfers();
        $final_ledger_obj=new Gbc_Model_DbTable_FinalLedger();
        $invoice_Obj=new Gbc_Model_DbTable_Invoices();
        $final_balace=new Gbc_Model_DbTable_FinalBalance();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        //$query = "SELECT round(SUM(btc_amt),8) as sum_net_withdrawn FROM withdrawals WHERE `status`<>2";
        $data = $withdrawal_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('w'=>"withdrawals"),array("round(SUM(btc_amt),8) as sum_net_withdrawn"));
        $data->where('status <> ?',2);

        if (!empty($username) && $username!=""){

            //$where .= " AND final_withdrawal= 'final_withdrawal'";
            //$where .= " AND username = '" . $username . "'";

            $data->where('final_withdrawal = ?','final_withdrawal');
            $data->where('username = ?',$username );
        }


        $resultwithdrawal = $withdrawal_obj->fetchRow($data);


        //$result = runQuery($query, $conn);
        $result=sizeof($resultwithdrawal);

        $totalDailyWithdrawn = 0;

        if(!empty($resultwithdrawal) && sizeof($resultwithdrawal) > 0){

            $totalDailyWithdrawn = $resultwithdrawal->sum_net_withdrawn;

        } else {
            $totalDailyWithdrawn = 0;
        }

        $FundTransfersres=$FundTransfersObj->fetchRow($FundTransfersObj->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"fund_transfers"),array("round(SUM(fund_amt),8) as total_fund"))
        ->where("user_to = ?",$username));

        $totalFund = 0;
        if (!empty($FundTransfersres)&& sizeof($FundTransfersres)>0) {
            //fetching each row in associative array format and assigning it to $row
            //$total_fund = mysql_fetch_assoc($result1["dbResource"]);
            $totalFund = $FundTransfersres->total_fund;


        } else {

            $totalFund = 0;
        }

        //********calculate Total Admin Fees********//
        // $query = "SELECT round(SUM(total_amt),8) as total_amt, round(SUM(adm_roi_payout),8) as adm_roi_payout, round(SUM(adm_bin_payout),8) as adm_bin_payout, round(SUM(adm_ref_payout),8) as adm_ref_payout FROM final_ledger WHERE username='$username'";
        $finalledgerobj=$final_ledger_obj->fetchRow($final_ledger_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"final_ledger"),array('round(SUM(total_amt),8) as total_amt','round(SUM(adm_roi_payout),8) as adm_roi_payout','round(SUM(adm_bin_payout),8) as adm_bin_payout','round(SUM(adm_ref_payout),8) as adm_ref_payout'))
        ->where("username=?",$username));

        if(!empty($finalledgerobj)&& sizeof($finalledgerobj)>0)
        {

            $total_amt=number_format($finalledgerobj->total_amt,8,'.','');
            $adm_roi_payout=$finalledgerobj->adm_roi_payout;
            $adm_bin_payout=$finalledgerobj->adm_bin_payout;
            $adm_ref_payout=$finalledgerobj->adm_ref_payout;

            //$sql="SELECT count(invoice_id) as check_direct, round(SUM(contract_rate),8) as contracts from invoices where username='".$username."' AND invoice_status=1  and created_on < '$date'";
            $invoiceObj=$invoice_Obj->fetchRow($invoice_Obj->select()
            ->setIntegrityCheck(false)
            ->from(array('i'=>"invoices"),array('count(invoice_id) as check_direct','round(SUM(contract_rate),8) as contracts'))
            ->where("username=?", $username)
            ->where("invoice_status=?", 1)
            ->where("created_on < ?", $date)
            );
            //->where("username=? AND invoice_status=1  and created_on < '$date'",$username));


            if (!empty($invoiceObj)&& sizeof($invoiceObj)>0){
                //$rw = mysql_fetch_assoc($res_chk["dbResource"]);
                $contractDetails['isBenfit']=$invoiceObj->contracts;

            }

            if(!empty($contractDetails['isBenfit']) && $contractDetails['isBenfit']>=0.5){
                // $total_amt=number_format(($daily_earning_amt + $ref_amt + $binary_amt),8,'.','');
                $total_admin_fees = $adm_roi_payout + $adm_bin_payout + $adm_ref_payout;

            }else{
                // $total_amt=number_format(($daily_earning_amt),8,'.','');
                $total_admin_fees = $adm_roi_payout;
            }
            //$total_amt=$row['total_amt']+$total_admin_fees;
            $total_amt=$finalledgerobj->total_amt+$total_admin_fees;


        }
        $bal_amt = number_format(($total_amt - $total_admin_fees - $totalDailyWithdrawn) + $totalFund,8,'.','');

        //$FindUserName = "select * from final_balance where username = '$username'";
        $FindUserName=$final_balace->fetchRow($final_balace->select()
        ->setIntegrityCheck(false)
        ->from(array('f'=>"final_balance"))
        ->where("username = ?",$username));
        $totalDailyWithdrawn = number_format($totalDailyWithdrawn,8,'.','');
     if (!empty($FindUserName) && sizeof($FindUserName)>0)
     {
         $total_amt= number_format($total_amt,8,'.','');
         $total_admin_fees= number_format($total_admin_fees,8,'.','');
         $totalFund =   number_format($totalFund,8,'.','');

         if(!empty($FindUserName)){
             $username = $FindUserName->username;
             $upd_arr=array('total_amt'=>$total_amt,'total_admin_fees'=>$total_admin_fees,'total_withdrawal'=>$totalDailyWithdrawn,'bal_amt'=>$bal_amt,'total_fund'=>$totalFund,'updated_on'=>new Zend_Db_Expr('NOW()'));
             //$upd_qry=$final_balace->update($upd_arr,"username=?",$username);
             $upd_qry=$final_balace->update($upd_arr,$DB->quoteInto("username=?",$username));

         }
         else
         {
             $ins_arr=array('username'=>$username,'total_amt'=>$total_amt,'total_admin_fees'=>$total_admin_fees,'total_withdrawal'=>$totalDailyWithdrawn,'total_fund'=>$totalFund,'bal_amt'=>$bal_amt);
             $ins_qry=$final_balace->insert($ins_arr);
         }


     }



    }
    function SearchUsersByLevel($username,$level){

        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $res = $bin_user_ref->fetchRow($bin_user_ref->select()
        ->where("username=?",$username)
        ->limit(100)
        );

        $limit = 0;
        if(!empty($res) && sizeof($res)>0){

            $topmcode =$res->id;
            $finalRes='';
            if(!empty($topmcode)){
                $common_obj->SearchByLevel($topmcode,$finalRes,$limit,$level);
            }
        }
        return $finalRes;
    }

    function SearchByLevel($parentID,&$retArray,$limit,$level){
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();
        $Categories1 = $bin_user_ref->fetchAll($bin_user_ref->select()
        ->where("parent_id =?",$parentID)
        ->order("child_position")
        );


        if(empty($Categories1) || sizeof($Categories1)<=0 ){
            return true;
        }

        for($i=0;$i<sizeof($Categories1);$i++){
            if($i ==0){
                $limit +=1;
            }

            $newArray=array();
            $id=$Categories1[$i]['id'];
            if($limit == $level ){
                $newArray['id']= $id;
                $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                $retArray[]=$newArray;
            }

            $common_obj->SearchByLevel($id,$retArray,$limit,$level);

        }
            
        return ($retArray) ;
    }

    function CountNetworklevel($username){
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $res=array();
        $res = $bin_user_ref->fetchAll($bin_user_ref->select()
        ->where("username=?",$username)
        );



        $limit = 1;
        
        if(!empty($res) && sizeof($res)>0){
            $topmcode =$res[0]['id'];
            $finalRes='';
            if(!empty($topmcode)){
                $common_obj->Countlevels($topmcode,$finalRes,$limit);

            }

        }
        return $finalRes;

    }



    function Countlevels($parentID,&$limitArray,$limit){

        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();
        $Categories1 = $bin_user_ref->fetchAll($bin_user_ref->select()
        ->where("parent_id =?",$parentID)
        );





        if(sizeof($Categories1)<=0 ){
            return true;
        }

        $j = 1;
        for($i=0;$i<sizeof($Categories1);$i++){
            $id=$Categories1[$i]['id'];
            $limitArray[]=$limit;
            if($j ==1){
                $limit = $limit + 1;
            }
            $j++;
            $common_obj->Countlevels($id,$limitArray,$limit);
        }
        return ($limitArray) ;
    }

    function createUserrefrences($ref) {
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
        $user = new Gbc_Model_DbTable_Userinfo();

        $checkUnique=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->setIntegrityCheck(false)
        ->from(array('b'=>"binary_user_refences"),array('count(b.username) as uniq_child'))
        ->where("username=?",trim($ref["username"])));

        if (!empty($checkUnique) && $checkUnique->uniq_child != 0) {
            return 'User already exist';
            exit();
        }


        $check_user = $user->fetchRow($user->select()
        //    ->where("binaryUser is NOT NULL AND username='". trim($ref["parent_username"]) . "")
        ->where("binaryUser is NOT NULL AND username=?",trim($ref["parent_username"]))

        );

        $chk_depth= $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->where("parent_username=?",trim($ref["parent_username"])));

        $allRecords_row = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->setIntegrityCheck(false)
        ->from(array('b'=>"binary_user_refences"),array('count(username) as allrecord'))
        ->where("parent_username=?",trim($ref["parent_username"])));
            
        $allRecords=$allRecords_row->allrecord;

        if($ref["child_position"] =='ref_page' && $ref["parent_username"]!='amitsabnetwork'){
            getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){

                $check_dw=$user->fetchRow($user->select()
                ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))
                );
                if($check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }
                else
                {
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?",trim($finalRes)));


                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }else{

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));
                            

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                            
                        return "success";
                        exit();
                    }

                }

            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
                    
            }

        }


        if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==1)){

            getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                //->where("binaryUser is NOT NULL AND username='". trim($ref["finalRes"]) . "'")
                ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))

                );

                if($check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }else{
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?",trim($finalRes)));



                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }
                    else
                    {
                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));
                            

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                            
                        return "success";
                        exit();
                    }

                }
            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
            }

        }


        if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==2)){
            getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                //->where("binaryUser is NOT NULL AND username='". trim($ref["finalRes"]) . "'")
                ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))

                );

                if($check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }else{
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?",trim($finalRes)));



                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }
                    else
                    {
                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));
                            

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                            
                        return "success";
                        exit();
                    }

                }
            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
            }

        }

        if((count($allRecords) == 3)) {
            getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                //->where("binaryUser is NOT NULL AND username='". trim($ref["finalRes"]) . "'")
                ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))

                );

                if($check_dw->isActiveId==1){
                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }
                else
                {
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?",trim($finalRes)));
                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }
                    else{

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));
                            

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                            
                        return "success";
                        exit();
                    }
                }
            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
                    
            }
        }


        if ((($allRecords) <= 2)){
            getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($finalRes)));

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
                    
                return "success";
                exit();
            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?",trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
                    
                return "success";
                exit();
            }

        }
        else
        {
            if (($allRecords) == 1) {
                getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                if(!empty($finalRes)){
                    $check_dw=$user->fetchRow($user->select()
                    //->where("binaryUser is NOT NULL AND username='". trim($ref["finalRes"]) . "'")
                    ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))

                    );
                    if($check_dw->isActiveId==1){

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                        return "success";
                        exit();
                 }
                 else
                 {
                     $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                     ->setIntegrityCheck(false)
                     ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                     ->where("parent_username=?",trim($finalRes)));
                     if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                         if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                         getRight($finalRes,$choice, $final);
                         if(!empty($final)){

                             $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                             ->where("username=?",trim($final)));


                             $depth = $chk_depth1->depth . $ref["username"] . ',';
                             $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);
                             return "success";
                             exit();
                         }else{
                             $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                             ->where("username=?",trim($ref["parent_username"])));

                             $depth = $chk_depth1->depth . $ref["username"] . ',';

                             $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);

                             return "success";
                             exit();
                         }
                     }
                     else{

                         $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                         ->where("username=?",trim($finalRes)));


                         $depth = $chk_depth1->depth . $ref["username"] . ',';
                         $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                         $result=$bin_user_ref_object->insert($query);

                         return "success";
                         exit();
                     }

                 }
                }
                else
                {
                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($ref["parent_username"])));


                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);

                }
            }
            else if (count($allRecords) == 0 && $check_user['isActiveId']==1) {
                getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                if(!empty($finalRes)){
                    $check_dw=$user->fetchRow($user->select()
                    //->where("binaryUser is NOT NULL AND username='". trim($ref["finalRes"]) . "'")
                    ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))

                    );
                    if($check_dw->isActiveId==1){

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($finalRes)));

                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);
                        return "success";
                        exit();
                    }
                    else
                    {

                        $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->setIntegrityCheck(false)
                        ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                        ->where("parent_username=?",trim($finalRes)));


                        if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                            if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                            getRight($finalRes,$choice, $final);
                            if(!empty($final)){

                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?",trim($final)));


                                $depth = $chk_depth1->depth . $ref["username"] . ',';
                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                $result=$bin_user_ref_object->insert($query);
                                return "success";
                                exit();
                            }else{
                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?",trim($ref["parent_username"])));

                                $depth = $chk_depth1->depth . $ref["username"] . ',';

                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                $result=$bin_user_ref_object->insert($query);

                                return "success";
                                exit();
                            }
                        }else{

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($finalRes)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }


                    }
                }
                else
                {
                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?",trim($ref["parent_username"])));


                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);

                }
            }
            else
            {
                if ($check_user['isActiveId'] != 1) {
                    getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                    if(!empty($finalRes)){

                        $check_dw=$user->fetchRow($user->select()
                        ->where("binaryUser is NOT NULL AND username=?",trim($ref["finalRes"]))
                        );
                        if($check_dw->isActiveId==1){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?",trim($finalRes)));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                            $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }
                        else
                        {
                            $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->setIntegrityCheck(false)
                            ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                            ->where("parent_username=?",trim($finalRes)));

                            if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                                if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                                getRight($finalRes,$choice, $final);
                                if(!empty($final)){

                                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                    ->where("username=?",trim($final)));


                                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                    $result=$bin_user_ref_object->insert($query);
                                    return "success";
                                    exit();
                                }else{
                                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                    ->where("username=?",trim($ref["parent_username"])));

                                    $depth = $chk_depth1->depth . $ref["username"] . ',';

                                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                    $result=$bin_user_ref_object->insert($query);

                                    return "success";
                                    exit();
                                }
                            }
                            else{

                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?",trim($finalRes)));


                                $depth = $chk_depth1->depth . $ref["username"] . ',';
                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                                $result=$bin_user_ref_object->insert($query);

                                return "success";
                                exit();
                            }
                        }
                    }
                    else
                    {
                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?",trim($ref["parent_username"])));


                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        $result=$bin_user_ref_object->insert($query);

                    }
                }
            }
        }
        return "success";
    }

    function insertUpdateBinaryNetwork($ref){
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
        $chk_Parent = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->where("username=?",$ref["username"]));

        if(isset($chk_Parent) && sizeof($chk_Parent)>0)
        {
            $bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
            $chk_depth1 = $bin_net_details_obj->fetchRow($bin_net_details_obj->select()
            ->where("username=?",$chk_Parent->parent_username));
            $depth = str_replace(',',"','",$chk_depth1->depth);
            $insertDepth = $depth."','".$ref["username"];

            $checkExistUser = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
            ->where("username=?",$ref["username"]));

            if(isset($checkExistUser) && sizeof($checkExistUser)>0)
            {

            }
            else
            {
                $arr_ins=array('username'=>$ref["username"],'depth'=>$insertDepth,'status'=>'1','created_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $ins_data=$bin_net_details_obj->insert($arr_ins);

            }
            $arr_upd=array('status'=>'1','updated_on'=>new Zend_Db_Expr('NOW()'));
            $upd_data=$bin_net_details_obj->update($arr_upd,"username in ('$insertDepth')");
            return "success";
        }
        return "success";

    }
    function getRight($pusername, $pos, &$result1 = '') {
    //echo $pusername." and ".$pos;exit;
        $comm_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
        $misc_obj=new Gbc_Model_Custom_Miscellaneous();

        $row = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        //->where("parent_username='" .$pusername . "' AND child_position='".$pos."'")
        ->where("parent_username=?",$pusername)
        ->where("child_position=?",$pos)
        );
    

        if(empty($row) || sizeof($row)<=0)
        {
            $UsersDetails = $misc_obj->getUserInfo($pusername);
            if($UsersDetails ->isActiveId){
                return $pusername;
            }
            if($pos == "R"){$pos = "L";}else{$pos = "R";}
            


            $row = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
            //->where("parent_username='" .$result1 . "' AND child_position='".$pos."'")
            ->where("parent_username='" .$result1 . "'")
            ->where("child_position=?",$pos)


            );
    
    
            if(empty($row))
            return true;
        }
    
        $result1= $row->username;

        $comm_obj->getRight($row->username, $pos, $result1);
        // var_dump($row);
        // echo $pos;
        return $row->username;
    }

    function renameuser($user,$NewUsername){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

        try{


            $Binary_network_detailsObj= new    Gbc_Model_DbTable_Binarynetworkdetail();

            $BinaryuserreferenceObj= new Gbc_Model_DbTable_Binaryuserreference();
            $binaryuserincomeObj= new Gbc_Model_DbTable_Binaryuserincome();
            $bin_usr_wkl_incomeObj=new Gbc_Model_DbTable_Binaryuserwelcome();
            $FundTransfersObj= new Gbc_Model_DbTable_FundTransfers();
            $KitsObj=new Gbc_Model_DbTable_Kits();
            $userinfoObj=new  Gbc_Model_DbTable_Userinfo();
            $withdrawalsObj= new Gbc_Model_DbTable_Withdrawals();
            $profilecontactObj= new Gbc_Model_DbTable_Profilecontact();
            $AchieverObj    =new Gbc_Model_DbTable_Achiever();
            $binarynetworkdetailsObj= new Gbc_Model_DbTable_Binarynetworkdetail();
            $contactlistObj=new Gbc_Model_DbTable_Contactlist();
            $dailyledgerObj=new Gbc_Model_DbTable_Dailyledger();
            $EarningsObj=new Gbc_Model_DbTable_Earnings();
            $finalbalanceObj=new Gbc_Model_DbTable_FinalBalance();
            $finalledgerObj=new Gbc_Model_DbTable_FinalLedger();
            $invoiceObj=new Gbc_Model_DbTable_Invoices();
            $KitspaymentObj    =new Gbc_Model_DbTable_Kitspayment();
            $KitinvoicesObj    =new Gbc_Model_DbTable_Kitinvoices();
            $PaircountObj =new Gbc_Model_DbTable_Paircount();
            $userleadsObj= new Gbc_Model_DbTable_Userlead();
            $usernotificationstatusObj=new Gbc_Model_DbTable_Usernotificationstatus();
            $userwithdrawaltypesObj=new Gbc_Model_DbTable_Userwithdrawaltype();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            $renameObj=new Gbc_Model_DbTable_Renameuser();

            $returnArray = false;

            $startTime = microtime(true);



            $Binarynetworkresult=$Binary_network_detailsObj->fetchAll($Binary_network_detailsObj->select()
            ->setIntegrityCheck(false)
            ->from(array('binary_network_details'),array('id','depth','left_network','right_network'))
            ->where("depth like '%,$user,%' or left_network like '%,$user,%' or right_network like '%,$user,%'"));



            for($i=0;$i<sizeof($Binarynetworkresult);$i++)
            {
                $id = str_replace($user,$NewUsername,$Binarynetworkresult[$i]['id']);

                $depth = str_replace(','.$user.',',','.$NewUsername.',',$Binarynetworkresult[$i]['depth']);
                $left_network = str_replace(','.$user.',',','.$NewUsername.',',$Binarynetworkresult[$i]['left_network']);
                $right_network = str_replace(','.$user.',',','.$NewUsername.',',$Binarynetworkresult[$i]['right_network']);


                $updateBinary_network_details=array("depth"=>$depth,"left_network"=>$left_network,"right_network"=>$right_network);
                $updateBinary_network=$Binary_network_detailsObj->update($updateBinary_network_details,$DB->quoteInto("id=?",$id));
                //$DB->quoteInto()


            }

            $BinaryuserreferenceObj= new Gbc_Model_DbTable_Binaryuserreference();



            $Binary_user_refencesresult=$BinaryuserreferenceObj->fetchAll($BinaryuserreferenceObj->select()
            ->setIntegrityCheck(false)
            ->from(array('binary_user_refences'),array('username','id','depth','parent_username'))
            ->where("depth like '%,$user,%' or parent_username like '$user'"));



            for($j=0;$j<sizeof($Binary_user_refencesresult);$j++){
                    
                    
                $id = str_replace(','.$user.',',','.$NewUsername.',',$Binary_user_refencesresult[$j]['id']);

                $depth = str_replace(','.$user.',',','.$NewUsername.',',$Binary_user_refencesresult[$j]['depth']);
                $parent_username = ($Binary_user_refencesresult[$j]['parent_username'] ==$user) ?$NewUsername:$Binary_user_refencesresult[$j]['parent_username'];
                    
                //mysql_query("update binary_user_refences set depth = '$depth',parent_username = '$parent_username' where id = '$id'");
                    
                $updatebinary_user_refences=array("depth"=>$depth,"parent_username"=>$parent_username);
                $updatebinaryuserdata=$BinaryuserreferenceObj->update($updatebinary_user_refences,$DB->quoteInto("id=?",$id));
                // $DB->quoteInto()

                    
                    
            }




            try{


                $updatebinaryuserincome=array("from_username"=>$NewUsername);
                //$updatebinaryuserdata=$binaryuserincomeObj->update($updatebinaryuserincome,"from_username=?",$user);
                $updatebinaryuserdata=$binaryuserincomeObj->update($updatebinaryuserincome,$DB->quoteInto("from_username=?",$user));




                $updatebinusrwklincome=array("parent_username"=>$NewUsername);
                //$updatebinusrwklincomedata=$bin_usr_wkl_incomeObj->update($updatebinusrwklincome,"parent_username=?",$user);
                $updatebinusrwklincomedata=$bin_usr_wkl_incomeObj->update($updatebinusrwklincome,$DB->quoteInto("parent_username=?",$user));




                $updatefundtransfers=array("user_to"=>$NewUsername);
                //$updatefundtransfersdata=$FundTransfersObj->update($updatefundtransfers,"user_to=?",$user);
                $updatefundtransfersdata=$FundTransfersObj->update($updatefundtransfers,$DB->quoteInto("user_to=?",$user));




                $updatekits=array("kit_used_by"=>$NewUsername);
                //    $updatekitdata=$KitsObj->update($updatekits,"kit_used_by=?",$user);
                $updatekitdata=$KitsObj->update($updatekits,$DB->quoteInto("kit_used_by=?",$user));





                $updateuserinfo=array("ref_sponsor_id"=>$NewUsername);
                //$updateuserinfodata=$userinfoObj->update($updateuserinfo,"ref_sponsor_id=?",$user);
                $updateuserinfodata=$userinfoObj->update($updateuserinfo,$DB->quoteInto("ref_sponsor_id=?",$user));




                $updatewithdrawals=array("user_to"=>$NewUsername);
                //$updatewithdrawalsdata=$withdrawalsObj->update($updatewithdrawals,"user_to=?",$user);
                $updatewithdrawalsdata=$withdrawalsObj->update($updatewithdrawals,$DB->quoteInto("user_to=?",$user));


            }
            catch(Exception $e)
            {
                echo $e->getMessage();exit;
            }



            try{
                    
                $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
                    
                $DB->query("update user_info
        left join achievers on achievers.username=user_info.username 
        left join binaryuserincome on binaryuserincome.ben_username=user_info.username 
        left join binary_network_details on binary_network_details.username=user_info.username 
        left join binary_user_refences on binary_user_refences.username=user_info.username 
        left join bin_usr_wkl_income on bin_usr_wkl_income.username=user_info.username 
        set achievers.username = '$NewUsername',
        binaryuserincome.ben_username = '$NewUsername',binary_network_details.username = '$NewUsername',
        binary_user_refences.username = '$NewUsername',bin_usr_wkl_income.username = '$NewUsername'
        where user_info.username='$user'");
                    
                    
            }

            catch(Exception $e)
            {
                echo $e->getMessage();
            }

            try{
                $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
                    
                    
                $DB->query("update user_info
        left join earnings on earnings.username=user_info.username 
        left join final_balance on final_balance.username=user_info.username 
        set earnings.username = '$NewUsername',final_balance.username = '$NewUsername'
        where user_info.username='$user'");

            }
            catch(Exception $e)
            {
                echo $e->getMessage();exit;
            }


            try{
                $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

                $DB->query("update user_info
        left join invoices on invoices.username=user_info.username 
        left join kits on kits.username=user_info.username 
        left join kits_payments on kits_payments.username=user_info.username 
        left join kit_invoices on kit_invoices.username=user_info.username 
        set invoices.username = '$NewUsername',
        kits.username = '$NewUsername',kits_payments.username = '$NewUsername',kit_invoices.username = '$NewUsername'
        where user_info.username='$user'");
            }
            catch(Exception $e)
            {
                echo $e->getMessage();exit;
            }


            try{
                    
                $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

                $DB->query("update user_info
        left join pair_count on pair_count.username=user_info.username 
        left join user_leads on user_leads.username=user_info.username 
        left join user_notification_status on user_notification_status.username=user_info.username 
        left join user_withdrawal_types on user_withdrawal_types.username=user_info.username 
        left join withdrawals on withdrawals.username=user_info.username 
        set pair_count.username = '$NewUsername',user_leads.username = '$NewUsername',user_notification_status.username = '$NewUsername',
        user_withdrawal_types.username = '$NewUsername',withdrawals.username = '$NewUsername'
        where user_info.username='$user'");
            }
            catch(Exception $e)
            {
                echo $e->getMessage();exit;
            }


            $updatedata=array("username"=>$NewUsername);
            //$updateuser=$userinfoObj->update($updatedata,"username=?",$user);
            $updateuser=$userinfoObj->update($updatedata,$DB->quoteInto("username=?",$user));





            if(!empty($DB)|| $authUserNamespace->user=="admin")
            {

                $updated_by = $authUserNamespace->user;
                $insertrename=array("old_user"=>$user,"new_user"=>$NewUsername,"updated_by"=>$updated_by);
                $insertrenamedata=$renameObj->insert($insertrename);
                    
            }
        }

        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

        return $insertrenamedata;
    }

    function calculateBusiness($Postusername,$startdate,$enddate){
        try {
            $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();

            $result1=$bin_user_ref->fetchAll($bin_user_ref->select()
            //->where("parent_username = '".$Postusername."' and parent_id<>0")
            ->where("parent_username = ? and parent_id<>0",$Postusername)
            ->order("child_position ASC"));

            //print_r($result1);exit;
            $userDetails=array();
            $childArray = array();
            if(!empty($result1) && isset($result1) && sizeof($result1)>0)
            {

                for($i=0;$i<sizeof($result1);$i++)
                {
                    $childArray[$i] = $result1[$i]['username'];
                    $child_position[$i] = $result1[$i]['child_position'];
                }

             $childs_first='';
             $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
             $Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[0],$childs_first);

             /* $childs_second='';
              if(!empty($childArray[1])){
                 $Gbc_Model_Custom_func_obj->getAllChildforBinary($childArray[1],$childs_second);
                 }
                 $childs_first=array_merge(array($childArray[0]),array_filter($childs_first));

                 if(!empty($childArray[1])){
                 $childs_second=array_merge(array($childArray[1]),array_filter($childs_second));
                 }*/
                 

             $RightpairsDetail=array();
             $LeftpairsDetail=array();
             if(!empty($childArray[0]) || !empty($childArray[1]) ){
                 if($child_position[0] != "R"){
                     if(!empty($childArray[0])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj-> getPairsforAdminNew($childArray[0],$startdate,$enddate);
                     }
                     if(!empty($childArray[1])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdminNew($childArray[1],$startdate,$enddate);
                     }
                 }
                 else
                 {
                     if(!empty($childArray[0])){
                         $RightpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdminNew($childArray[0],$startdate,$enddate);
                     }
                      
                     if(!empty($childArray[1])){
                         $LeftpairsDetail =$Gbc_Model_Custom_func_obj->getPairsforAdminNew($childArray[1],$startdate,$enddate);
                     }
                 }

                 $Leftpaircount = 0;
                 $Rightpaircount = 0;
                 $LeftActive = 0;
                 $RightActive = 0;
                 $LeftBusiness = $RightBusiness =$totalLeftBusiness = $totalRightBusiness = 0;
                 $pairDetails = array();
                 if(!empty($LeftpairsDetail)){
                     foreach($LeftpairsDetail as $pair){
                         // var_dump($pair);
                         if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) && (date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                             if(!empty($pair['created_on'])){

                                 // var_dump($pair);
                                 // echo date('Y-m-d',strtotime($pair['created_on']));
                                 // echo "<br>";
                                 // echo $pair['name'];
                                 // var_dump($pair['name']);
                                 // echo "<br>";
                                 // echo "<br>";
                                 // $pairArray[$pair['name']] = $pair['ContractPrice'];
                                 $pair['child_position'] = "L";
                                 $pairDetails[] = $pair;

                                 // $pairDetails['left'][]['username'] = $pair['name'];
                                 // $pairDetails['left'][]['contract'] = $pair['ContractPrice'];
                                 $LeftBusiness += $pair['ContractPrice'];
                                 // echo $LeftBusiness;
                             }
                                 
                             if($pair['IsActive'] == 1){


                                 $LeftActive++;
                          }

                              
                          $Leftpaircount++;
                              
                         }else{
                             // echo "date left:";
                             // var_dump($pair['ContractPrice']);
                             // echo "<br>";
                         }
                         $totalLeftBusiness += $pair['ContractPrice'];
                     }
                     /* fclose($fp); */
                 }

                    if(!empty($RightpairsDetail) && $RightpairsDetail!='' ){
                     // echo "right :";
                     // echo "<br>";
                        // var_dump($pair);
                        foreach($RightpairsDetail as $pair){
                            // echo "'";
                            /* if(!empty($pair['ContractPrice'])){
                             echo $pair['name']." ".$pair['ContractPrice'];
                             echo "<br>";
                             } */
                            if((date('Y-m-d',strtotime($pair['created_on'])) >= date('Y-m-d',strtotime($startdate))) &&(date('Y-m-d',strtotime($pair['created_on'])) <= date('Y-m-d',strtotime($enddate)))){
                                if(!empty($pair['created_on'])){
                                    $pair['child_position'] = "R";
                                    $pairDetails[] = $pair;

                                }
                          if($pair['IsActive'] == 1){

                              $RightActive++;
                          }else{
                              // echo "right :";
                              // var_dump($pair);

                          }
                          $RightBusiness += $pair['ContractPrice'];
                          $Rightpaircount++;
                            }else{
                                // echo " date right :";
                                // var_dump($pair['ContractPrice']);
                                // echo "<br>";
                            }
                            $totalRightBusiness += $pair['ContractPrice'];
                        }
                    }

                    $userDetails['userDetails']['username'] = $Postusername;
                    $userDetails['userDetails']['totalUsers'] = count($LeftpairsDetail) + count($RightpairsDetail);
                    $userDetails['userDetails']['LeftActive'] = $LeftActive;
                    $userDetails['userDetails']['RightActive'] = $RightActive;
                    $userDetails['userDetails']['LeftBusiness'] = $LeftBusiness;
                    $userDetails['userDetails']['RightBusiness'] = $RightBusiness;
                    $userDetails['userDetails']['totalLeftBusiness'] = $totalLeftBusiness;
                    $userDetails['userDetails']['totalRightBusiness'] = $totalLeftBusiness;
                    $userDetails['pairDetails'] = $pairDetails;

                    return $userDetails;
             }
            }

        }
        catch(Exception $e)
        {
            return $userDetails[0]="error";
        }

    }
    function getAllChildforBinaryNew($username,&$result1='',$binary_capping,$date = null)
    {
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $result=array();
        if(!empty($date))
        {
            $result=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"binary_user_refences"),array('A.id','A.username'))
            ->joinLeft(array('B'=>'invoices'),"B.username = A.username",array('round(sum(B.contract_rate),2) as contracts'))
            ->where("A.parent_username = ?",$username)
            ->where("A.parent_id <> ?",0)
            ->where("B.created_on < ?",$date)
            ->group("A.username")
            );

        }
        else
        {
            $result=$bin_user_ref->fetchAll($bin_user_ref->select()
            ->setIntegrityCheck(false)
            ->from(array('A'=>"binary_user_refences"),array('A.id','A.username'))
            ->joinLeft(array('B'=>'invoices'),"B.username = A.username",array('round(sum(B.contract_rate),2) as contracts'))
            ->where("A.parent_username = ?",$username)
            ->where("A.parent_id <> ?",0)
            ->group("A.username")
            );

        }
        /*if(!empty($date)){
            $date = " and B.created_on < '$date'";
            }*/
            
        if(sizeof($result)<=0){
            $result1[] ='';
            return true;
        }
        $usernm=$result[0]['username'];
        for($i=0;$i<sizeof($result);$i++)
        {
            if($i<($binary_capping-1))
            {
                $result1[] = strtolower($result[$i]['username']);
                //print_r($result1);exit;

                $Gbc_Model_Custom_func_obj-> getAllChildforBinary($result[$i]['username'],$result1);
            }
        }


        return ($result1) ;
            
    }
    function getPairsforAdminNew($username,$startdate,$enddate){
        ini_set('memory_limit', '-1');
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $result=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.parent_id','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"UserInfo.username = BinaryUserReferences.username",array('UserInfo.ref_sponsor_id','UserInfo.isActiveId'))
        ->joinLeft(array('Invoice'=>'invoices'),"Invoice.username = BinaryUserReferences.username and locked = '0'  and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate'",array('round(sum(Invoice.contract_rate),2) as ContractPrice', 'Invoice.created_on'))
        ->where("BinaryUserReferences.username= ?", $username)
        ->group("BinaryUserReferences.username"));
            


        /*$qury="Select BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.ref_sponsor_id,UserInfo.isActiveId, round(sum(Invoice.contract_rate),2) as ContractPrice, Invoice.created_on from binary_user_refences as BinaryUserReferences left join `user_info` as UserInfo on UserInfo.username = BinaryUserReferences.username left join invoices as Invoice on (Invoice.username = BinaryUserReferences.username and locked = '0'  and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate') where BinaryUserReferences.username='$username' group by BinaryUserReferences.username";
         $resultqry=$DB->query($qury);
         $result = $resultqry->fetchAll();*/
        $finalRes='';
        if(isset($result) && sizeof($result)>0)
        {
            $result = $result->toArray();
        }

        if(sizeof($result)<=0){
            return true;
        }
        $topmcode =$result[0]['id'];
        for($i=0;$i<sizeof($result);$i++)
        {

            $newArray=array();
            $id=$result[$i]['id'];
            $chiled_position=$result[$i]['child_position'];
            $newArray['name']= strtolower($result[$i]['username']);
            $newArray['parent']=strtolower($result[$i]['parent_username']);
            $newArray['IsActive']=strtolower($result[$i]['isActiveId']);
            $newArray['child_position']=strtolower($result[$i]['child_position']);
            $newArray['created_on']=strtolower($result[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($result[$i]['ContractPrice']);
            $retArray[]=$newArray;
            $finalRes = $retArray;

            //var_dump($finalRes);
            // fetchBTreeForPairs($id,$conn,$retArray);

        }

        $Gbc_Model_Custom_func_obj->fetchBTreeForPairsAdminNew($topmcode,$finalRes,$startdate,$enddate);
        return $finalRes;
    }


    function fetchBTreeForPairsAdminNew($parentID,&$retArray,$startdate,$enddate)
    {
        ini_set('memory_limit', '-1');
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $Categories1=array();

        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
        ->joinLeft(array('u'=>'user_info'),"a.username = u.username",array('u.ref_sponsor_id','u.isActiveId'))
        ->joinLeft(array('i'=>'invoices')," i.username = u.username and i.invoice_status = 1  and locked = '0' and i.created_on >= '$startdate' and i.created_on <= '$enddate'",array('round(sum(i.contract_rate),2) as ContractPrice','i.created_on'))
        ->where("a.parent_id = ?",$parentID)
        ->group("u.username")
        ->order("child_position ASC")
        );
            
        if(!empty($Categories1) && sizeof($Categories1)>0)
        {
            $Categories1 = $Categories1->toArray();
        }

        //$sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.isActiveId,  round(sum(Invoice.contract_rate),2) as ContractPrice, Invoice.created_on FROM `binary_user_refences` as BinaryUserReferences left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1  and locked = '0' and Invoice.created_on >= '$startdate' and Invoice.created_on <= '$enddate' WHERE BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username  order by child_position ASC";

        //$resultqry1=$DB->query($sql);
        //$Categories1 = $resultqry1->fetchAll();

        if(sizeof($Categories1)<=0){
            return true;
        }

        for($i=0;$i<sizeof($Categories1);$i++)
        {

            $newArray=array();
            $id=$Categories1[$i]['id'];
            $chiled_position=$Categories1[$i]['child_position'];
            $newArray['name']= strtolower($Categories1[$i]['username']);
            $newArray['parent']=strtolower($Categories1[$i]['parent_username']);
            $newArray['IsActive']=strtolower($Categories1[$i]['isActiveId']);
            $newArray['child_position']=strtolower($Categories1[$i]['child_position']);
            $newArray['created_on']=strtolower($Categories1[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($Categories1[$i]['ContractPrice']);
            $retArray[]=$newArray;

            $Gbc_Model_Custom_func_obj->fetchBTreeForPairsAdminNew($id,$retArray,$startdate,$enddate);
        }

        return ($retArray) ;

    }

    function getPairs($username,$lastDate=null,$startDate=null){
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();


        /*    $qury="Select BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.ref_sponsor_id,UserInfo.isActiveId,
      (select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' ) as ContractPrice,
      (select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on < '$startDate') as preContractPrice,
      (select invoices.created_on from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' order by invoices.created_on desc limit 1) as created_on
      from binary_user_refences as BinaryUserReferences left join `user_info` as UserInfo on UserInfo.username = BinaryUserReferences.username
      left join invoices as Invoice on Invoice.username = BinaryUserReferences.username
      where BinaryUserReferences.username='$username' group by UserInfo.username"; */

        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.parent_id','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"UserInfo.username = BinaryUserReferences.username",array('UserInfo.ref_sponsor_id','UserInfo.isActiveId'))
            
        ->joinLeft(array('Invoice'=>'invoices'),"Invoice.username = BinaryUserReferences.username and Invoice.invoice_status = 1",array("(select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' ) as ContractPrice"," (select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on < '$startDate') as preContractPrice","(select invoices.created_on from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' order by invoices.created_on desc limit 1) as created_on"))
        ->where("BinaryUserReferences.username='$username'")
        ->group("UserInfo.username")
        );

        /*$qry=$DB->query($qury);

        $Categories1 = $qry->fetchAll();*/
        $finalRes='';

        if(!isset($Categories1) || sizeof($Categories1)<=0){
            return true;
        }
        else
        {
            $Categories1 = $Categories1->toArray();
        }
        $topmcode =$Categories1['0']['id'];
        for($i=0;$i<sizeof($Categories1);$i++)
        {
            $newArray=array();
            $id=$Categories1[$i]['id'];
            $chiled_position=$Categories1[$i]['child_position'];
            $newArray['name']= strtolower($Categories1[$i]['username']);
            $newArray['parent']=strtolower($Categories1[$i]['parent_username']);
            $newArray['IsActive']=strtolower($Categories1[$i]['isActiveId']);
            $newArray['child_position']=strtolower($Categories1[$i]['child_position']);
            $newArray['created_on']=strtolower($Categories1[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($Categories1[$i]['ContractPrice']);
            $newArray['preContractPrice']=strtolower($Categories1[$i]['preContractPrice']);
            $retArray[]=$newArray;
            $finalRes = $retArray;
            // fetchBTreeForPairs($id,$conn,$retArray);

        }

        $Gbc_Model_Custom_func_obj->fetchBTreeForPairs($topmcode,$finalRes,$startDate,$lastDate);

        return $finalRes;
    }

    function fetchBTreeForPairs($parentID,&$retArray,$startDate,$lastDate)
    {
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $Categories1=array();


        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.parent_id','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"UserInfo.username = BinaryUserReferences.username",array('UserInfo.ref_sponsor_id','UserInfo.isActiveId'))
            
        ->joinLeft(array('Invoice'=>'invoices'),"Invoice.username = BinaryUserReferences.username and Invoice.invoice_status = 1",array("(select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' and invoices.invoice_status = 1 ) as ContractPrice","(select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on < '$startDate' and invoices.invoice_status = 1 ) as preContractPrice","(select invoices.created_on from invoices where invoices.username = UserInfo.username and invoices.created_on >= '$startDate'  and invoices.created_on <= '$lastDate' and invoices.invoice_status = 1  order by invoices.created_on desc limit 1) as created_on"))
        ->where("BinaryUserReferences.parent_id ='".$parentID."'")
        ->group("UserInfo.username")
        ->order("child_position ASC")
        );

        /*    $sql="Select BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.parent_id,BinaryUserReferences.child_position, UserInfo.ref_sponsor_id,UserInfo.isActiveId,
      (select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on >= '$startDate' and invoices.created_on <= '$lastDate' and invoices.invoice_status = 1 ) as ContractPrice,
      (select round(sum(invoices.contract_rate),2) from invoices where invoices.username = UserInfo.username and locked = '0' and invoices.created_on < '$startDate' and invoices.invoice_status = 1 ) as preContractPrice,
      (select invoices.created_on from invoices where invoices.username = UserInfo.username and invoices.created_on >= '$startDate'  and invoices.created_on <= '$lastDate' and invoices.invoice_status = 1  order by invoices.created_on desc limit 1) as created_on
      from binary_user_refences as BinaryUserReferences left join `user_info` as UserInfo on UserInfo.username = BinaryUserReferences.username
      left join invoices as Invoice on Invoice.username = BinaryUserReferences.username and Invoice.invoice_status = 1
      where BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username  order by child_position ASC ";

      $qry1=$DB->query($sql);

      $Categories1 = $qry1->fetchAll();*/


        if(empty($Categories1) || sizeof($Categories1)<=0){
            return true;
        }
        else
        {
            $Categories1 = $Categories1->toArray();
        }

        for($i=0;$i<sizeof($Categories1);$i++)
        {
            $newArray=array();
            $id=$Categories1[$i]['id'];
            $chiled_position=$Categories1[$i]['child_position'];
            $newArray['name']= strtolower($Categories1[$i]['username']);
            $newArray['parent']=strtolower($Categories1[$i]['parent_username']);
            $newArray['IsActive']=strtolower($Categories1[$i]['isActiveId']);
            $newArray['child_position']=strtolower($Categories1[$i]['child_position']);
            $newArray['created_on']=strtolower($Categories1[$i]['created_on']);
            $newArray['ContractPrice']=strtolower($Categories1[$i]['ContractPrice']);
            $newArray['preContractPrice']=strtolower($Categories1[$i]['preContractPrice']);
            $retArray[]=$newArray;
            $Gbc_Model_Custom_func_obj->fetchBTreeForPairs($id,$retArray,$startDate,$lastDate);
        }

        return ($retArray) ;

    }
    function getMembership($username)
    {
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $member_obj=new Gbc_Model_DbTable_Membershiplist();
        $result_contract=$invoices_obj->fetchRow($invoices_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('i'=>"invoices"),array('sum(contract_rate) as total_own'))
        //->where("username='".($username)."' AND invoice_status='1'")
        ->where("username=? AND invoice_status='1'",$username)

        ->group("username")
        );

        if(!empty($result_contract) && sizeof($result_contract)>0)
        {

            if(!empty($result_contract->total_own) && $result_contract->total_own!='')
            {
                $total_business=$result_contract->total_own;
            }
            else
            {
                $total_business=0;
            }


            $membership_type_qry = $member_obj->fetchRow($member_obj->select()
            ->where("investment_start >= ?",$total_business)
            ->where("investment_end <= ?", $total_business)
            );
        }
        else
        {
            $membership_type_qry=array();
        }
        return $membership_type_qry;

    }
    function updateMembership($username,$ContractRate=0)
    {
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $member_obj=new Gbc_Model_DbTable_Membershiplist();
        $user_member_obj = new Gbc_Model_DbTable_Usermember();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $result_contract=$invoices_obj->fetchRow($invoices_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('i'=>"invoices"),array('sum(contract_rate) as total_own'))
        //->where("username='".($username)."' AND invoice_status='1'")
        ->where("username=? AND invoice_status='1'",$username)

        ->group("username")
        );

        if(!empty($result_contract) && sizeof($result_contract)>0)
        {
            if(!empty($result_contract->total_own) && $result_contract->total_own!='')
            {
                $total_business=($result_contract->total_own + $ContractRate);
            }
            else
            {
                $total_business=(0 + $ContractRate);
            }

        }
        else
        {
            $total_business=0;
        }


        $membership_type_qry = $member_obj->fetchRow($member_obj->select()
        ->where("investment_start <=".$total_business." AND ". $total_business. " <= investment_end "));



        $current_membership=$user_member_obj->fetchRow($user_member_obj->select()
        ->where("username=?",$username)
        ->order('date desc')
        ->limit(1)
        );

        if(!empty($current_membership) && sizeof($current_membership)>0)
        {
            if($current_membership->membership!=$membership_type_qry->membership_type)
            {
                $upd_arr=array('membership'=>$membership_type_qry->membership_type,'date'=>new Zend_Db_Expr('NOW()'));
                //$upd_qry=$user_member_obj->update($upd_arr,"username=?",$username);
                // $DB->quoteInto()

                $upd_qry=$user_member_obj->update($upd_arr,$DB->quoteInto("username=?",$username));

            }
            return "success";
        }
        else
        {
            $ins_arr=array('username'=>$username,'membership'=>$membership_type_qry->membership_type,'date'=>new Zend_Db_Expr('NOW()'));
            $ins_qry=$user_member_obj->insert($ins_arr);
            return "success";
        }
    }

    function getchildrenForNavigation($parent,$country)
    {
        $manage_link_obj=new Gbc_Model_DbTable_Managelinks();
        $manage_child=$manage_link_obj->fetchAll($manage_link_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('m'=>"manage_links"),array('m.id'))
        ->join(array('n'=>'navigation_master'),"n.id = m.nav_id",array('n.nav_link','n.nav_controller'))
        ->where("m.country_id=?",$country)
        ->where("m.status=?",'1')
        ->where("m.parent=?",$parent)
        ->where("m.nav_id!=m.parent")
        ->order("n.id asc"));





            
        if(empty($manage_child) || sizeof($manage_child)<=0)
        {

            $manage_child=array();
        }

        return $manage_child;
    }
    function setLimitFull()
    {
        $date = date('d');
        $contracts_obj=new Gbc_Model_DbTable_Contracts();
        $special_per_obj=new Gbc_Model_DbTable_SpecialPermission();
        //if(($date == 1) || ($date == 16)){
        $upd_cont_data=array('admin_limit'=>'0','available_limit'=>'max_limit');
        $upd_cont_qry=$contracts_obj->update($upd_cont_data,"1=1");
            

        $upd_perm_data=array('available_kits'=>'max_limit');
        $upd_perm_qry=$special_per_obj->update($upd_perm_data,"1=1");
        //}
    }

    function CalculateAllKits()
    {

        $special_per_obj=new Gbc_Model_DbTable_SpecialPermission();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $date = date('d');
        $ExistingSilverKits = $special_per_obj->fetchRow($special_per_obj->select()
        );
        $max_limit=$ExistingSilverKits->max_limit;
        $AvailableKits=$ExistingSilverKits->available_kits;
        if(in_array($date, range(1, 15))){
            $start_date = date('Y-m-d',strtotime('first day of this month'));
            $end_date = date('Y-m')."-15 23:59:59";
        }else{
            $start_date = date('Y-m')."-16";
            $end_date = date('Y-m-d',strtotime('last day of this month'))." 23:59:59";
        }

        $type = array('active','used');
        $ActiveKits=$kits_obj->fetchRow($kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('kits'=>"kits"),array('kits.id'))
        ->joinInner(array('contracts'=>'contracts'),"contracts.price_paid=kits.kit_price and contracts.contract_type in ('SHA','MS','ES')", array('round(sum(contracts.total_price), 1) as total_price'))
        ->where("kits.status IN (?)", $type)
        ->where("kits.count =?", '1')
        ->where("kits.updated_on between '$start_date' and '$end_date'")
        );


        if(!empty($ActiveKits) && sizeof($ActiveKits)>0 && $max_limit > 0)
        {
            $UsedActiveKits=$ActiveKits->total_price;
            $AvailableKits = round(($max_limit - $UsedActiveKits),2) ;
            $upd_perm_data=array('available_kits'=>$AvailableKits);
            $upd_perm_qry=$special_per_obj->update($upd_perm_data,"1=1");
        }

    }
    function CalculateAvailableKits($kitPrice)
    {
        $contracts_obj=new Gbc_Model_DbTable_Contracts();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $date = date('d');
        if(in_array($date, range(1, 15))){
            $start_date = date('Y-m-d',strtotime('first day of this month'));
            $end_date = date('Y-m')."-15 23:59:59";
        }else{
            $start_date = date('Y-m')."-16";
            $end_date = date('Y-m-d',strtotime('last day of this month'))." 23:59:59";
        }


        $ExistingKits=$contracts_obj->fetchRow($contracts_obj->select()
        ->setIntegrityCheck(false)
        ->where("price_paid = ?",$kitPrice));

        if(!empty($ExistingKits) && sizeof($ExistingKits)>0)
        {

            $AdminKits = $ExistingKits->admin_limit;
            $max_limit = $ExistingKits->max_limit;
            $AvailableKits = $ExistingKits->available_limit;
        }

        $type = array('active','used');
        $count_kits=$kits_obj->fetchRow($kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('kits'=>"kits"),array('count(id) as id'))
        ->joinInner(array('contracts'=>'contracts'),"contracts.price_paid = kits.kit_price")
        ->where("kits.status IN (?)", $type)
        ->where("kits.kit_price =?", $kitPrice)
        ->where("kits.updated_on between '$start_date' and '$end_date'")
        );

        $UsedKits=$count_kits->id;

        if(($max_limit > 0) && (($AdminKits <= 0) || ($UsedKits + $AvailableKits > $max_limit + $AdminKits))){
            $AvailableKits = $max_limit + $AdminKits - $UsedKits;
            $upd_data=array('available_limit'=>$AvailableKits);
            //$upd_qry=$contracts_obj->update($upd_data,"price_paid = ?",$kitPrice);
            //$DB->quoteInto()

            $upd_qry=$contracts_obj->update($upd_data,$DB->quoteInto("price_paid = ?",$kitPrice));

        }

    }

    function updateFinalLedgerAfterDeactivate($Users,$start)
    {
        $dailyledger_Obj=new Gbc_Model_DbTable_Dailyledger();
        $invoices_Obj=new Gbc_Model_DbTable_Invoices();
        $finalledger_Obj=new Gbc_Model_DbTable_FinalLedger();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        foreach($Users as $username)
        {
            //$query = "SELECT round(SUM(ref_amt),8) as ref_amt, round(SUM(binary_amt),8) as binary_amt, round(SUM(daily_earning_amt),8) as daily_earning_amt, round(SUM(total_amt),8) as total_amt FROM daily_ledger WHERE username='$username' AND status='0' and created_on like date('$start%')";
            $dailyledger=$dailyledger_Obj->fetchRow($dailyledger_Obj->select()
            ->setIntegrityCheck(false)
            ->from(array('d'=>"daily_ledger"),array('round(SUM(ref_amt),8) as ref_amt','round(SUM(binary_amt),8) as binary_amt','round(SUM(daily_earning_amt),8) as daily_earning_amt','round(SUM(total_amt),8) as total_amt'))
            ->where("username =?",$username)
            ->where("status =?",'0')
            ->where("created_on like date('$start%')")
            );

            if(!empty($dailyledger)&& sizeof($dailyledger)>0)
            {
                $ref_amt=$dailyledger->ref_amt;
                $binary_amt=$dailyledger->binary_amt;
                $daily_earning_amt=$dailyledger->daily_earning_amt;
                $total_amt=$dailyledger->total_amt;
                if(!empty($total_amt) || !empty($ref_amt) || !empty($binary_amt) || !empty($daily_earning_amt))
                {
                    $adminRef= 0;
                    $ref_amt=number_format(($ref_amt-$adminRef),8,'.','');

                    $adminBinary= 0;
                    $binary_amt=number_format(($binary_amt-$adminBinary),8,'.','');

                    $adminDaily= 0;
                    $daily_earning_amt=number_format(($daily_earning_amt-$adminDaily),8,'.','');

                    $date = date('Y-m-d');

                    //$sql="SELECT count(invoice_id) as check_direct, round(SUM(contract_rate),8) as contracts from invoices where username='".$username."' AND invoice_status=1 and created_on < '$date' ";
                    $invoicesres=$invoices_Obj->fetchRow($invoices_Obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('i'=>"invoices"),array('count(invoice_id) as check_direct','round(SUM(contract_rate),8) as contracts'))
                    ->where("username =?",$username)
                    ->where("invoice_status =?",'1')
                    ->where("created_on <?", $date)
                    );

                    if (!empty($invoicesres) && sizeof($invoicesres)>0){
                        // $rw = mysql_fetch_assoc($res_chk["dbResource"]);
                        $contractDetails['isBenfit']=$invoicesres->contracts;
                    }
                    if(!empty($contractDetails['isBenfit'])&& $contractDetails['isBenfit']>=0.5)
                    {
                        $total_amt=number_format(($daily_earning_amt + $ref_amt + $binary_amt),8,'.','');
                        $total_amount=ref_amt + binary_amt + daily_earning_amt;

                        //$update1= "UPDATE final_ledger SET total_amt = ref_amt + binary_amt + daily_earning_amt where username = '".$username."'";
                        $updateledgerdata=array("total_amt"=>$total_amount);
                        //$update_finalledger=$finalledger_Obj->update($updateledgerdata,"username = ?",$username);
                        $update_finalledger=$finalledger_Obj->update($updateledgerdata,$DB->quoteInto("username = ?",$username));
                        // $DB->quoteInto()


                    }else{
                        $total_amt=number_format(($daily_earning_amt),8,'.','');
                    }

                    if(!empty($total_amt))
                    {
                        //$UpdateFinalLedger = mysql_query("update final_ledger set ref_amt = '$ref_amt',binary_amt = '$binary_amt',daily_earning_amt = '$daily_earning_amt',total_amt = '$total_amt',adm_roi_payout = '$adminDaily',adm_bin_payout = '$adminBinary', adm_ref_payout = '$adminRef' where username = '$username' and created_on like date('$start%')");
                        $Updatefinaldata=array("ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"daily_earning_amt"=>$daily_earning_amt,"total_amt"=>$total_amt,"adm_roi_payout"=>$adminDaily,"adm_bin_payout"=>$adminBinary,"adm_ref_payout"=>$adminRef);
                        //$UpdateFinalLedger=$finalledger_Obj->update($Updatefinaldata,"username = '$username' and created_on like date('$start%')");
                        $UpdateFinalLedger=$finalledger_Obj->update($Updatefinaldata,$DB->quoteInto("username = ? and created_on like date('$start%')",$username));
                        // $DB->quoteInto()


                    }
                    //$UpdateFinalLedger = UpdateFinalLedger($withdrawalDetails, $username,$conn);

                    $common_obj = new Gbc_Model_Custom_CommonFunc();
                    $UpdateFinalLedger=$common_obj->UpdateFinalLedger($withdrawalDetails, $username);

                    if($UpdateFinalLedger){
                        $result = true;
                    }

                }
            }
            $count++;
            ob_flush();
            flush();

        }
        return $result;
    }
    function changeInvoiceLock($user,$username,$kit_number,$changeLock,$lock_value,$invoice_id,$contract_rate,$depth,$InvoiceDate,$start)
    {

        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $binaryuserincome_Obj= new Gbc_Model_DbTable_Binaryuserincome();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();


        $result = false;
        $description = "";
            
        //$changeKitLockStatus = mysql_query("update kits set locked = '$changeLock', updated_on = now() where kit_number = '$kit_number'");
        $changeKitLockStatus=array("locked"=>$changeLock,"updated_on"=>new Zend_Db_Expr('NOW()'));
        //$updatekitlockstatus=$kits_obj->update($changeKitLockStatus,"kit_number = ?",$kit_number);
        $updatekitlockstatus=$kits_obj->update($changeKitLockStatus,$DB->quoteInto("kit_number = ?",$kit_number));
        //$DB->quoteInto()


        if(!empty($updatekitlockstatus)){

            $description .= " Kit Number :'$kit_number', Table: 'kits'  Lock status has been changed to ".$changeLock;

        }
        //$changeInvoiceLockStatus = mysql_query("update invoices set locked = '$changeLock', updated_on = now() where invoice_id = '$invoice_id'");
        $changeInvoiceLockStatus=array("locked"=>$changeLock,"updated_on"=>new Zend_Db_Expr('NOW()'));
        //$updatechangeinvoicelock=$invoices_obj->update($changeInvoiceLockStatus,"invoice_id = ?",$invoice_id);
        $updatechangeinvoicelock=$invoices_obj->update($changeInvoiceLockStatus,$DB->quoteInto("invoice_id = ?",$invoice_id));


        if(!empty($updatechangeinvoicelock)){
            $description .= " Invoice ID :'$invoice_id', Table: 'invoices'  Lock status has been changed to ".$changeLock;

        }

        //if($changeLockStatus)
        //{

        //$ChangeDirectEarningStatus = mysql_query("update binaryuserincome set status = '".$_GET['lock_value']."', updated_on = now() where invoice_id = '$invoice_id'");
        $ChangeDirectEarningStatus=array("status"=>$_REQUEST['lock'],"updated_on"=>new Zend_Db_Expr('NOW()'));
        //$updatedirectearning=$binaryuserincome_Obj->update($ChangeDirectEarningStatus,"invoice_id =?",$invoice_id);
        $updatedirectearning=$binaryuserincome_Obj->update($ChangeDirectEarningStatus,$DB->quoteInto("invoice_id =?",$invoice_id));


        if(!empty($updatedirectearning)){

            $description .= " Table: 'binaryuserincome' direct earning status has been changed to ".$_REQUEST['lock'];


        }


        $Users = explode(',',$depth);

        if($InvoiceDate < $start)
        {

            //$binaryEarningAfterDeactivate = binaryEarningAfterDeactivate($Users,$conn);


            $binaryEarningAfterDeactivate=$common_obj->binaryEarningAfterDeactivate($Users);

            if(!empty($binaryEarningAfterDeactivate)){
                    
                $description .= " Binary Earnings updated of upper network of username: '$username'";
            }
        }
        //$updateDailyLedgerAfterDeactivate = updateDailyLedgerAfterDeactivate($Users,$start,$conn);

        $updateDailyLedgerAfterDeactivate=$common_obj->updateDailyLedgerAfterDeactivate($Users,$start);

        if(!empty($updateDailyLedgerAfterDeactivate)){
            $description .= " Daily Ledger updated of upper network of username: '$username'";
        }

        if($InvoiceDate < $Start)
        {
            //$updateFinalLedgerAfterDeactivate = updateFinalLedgerAfterDeactivate($Users,$start,$conn);

            $updateFinalLedgerAfterDeactivate=$common_obj->updateFinalLedgerAfterDeactivate($Users,$start);

            if(!empty($updateFinalLedgerAfterDeactivate)){
                $description .= " Final Ledger updated of upper network of username: '$username'";
            }

        }

        //}
        if(!empty($description)){

            $description .= " by '$user'";
            //$saveUserLog = saveUserLog($user,"multiple",$description);
            $saveUserLog=$common_obj-> saveUserLog($user,"multiple",$description);
            $result = true;

            //$this->_redirect("/Kitinvoice");
        }

        return $result;

    }

    function updateDailyLedgerAfterDeactivate($Users,$start)
    {
        $earnings_obj=new Gbc_Model_DbTable_Earnings();
        $binaryuserincome_obj=new Gbc_Model_DbTable_Binaryuserincome();
        $bin_usr_wkl_income_obj=new Gbc_Model_DbTable_Binaryuserwelcome();
        $daily_ledger_obj=new Gbc_Model_DbTable_Dailyledger();
        foreach($Users as $username)
        {
            $date = date('Y-m-d');
            //$query = "SELECT round(SUM(net_amt),8) as sum_net_amt FROM earnings WHERE username='$username' AND isDaily='Yes' and created_on like date('$start%') ";
            $earningsres=$earnings_obj->fetchRow($earnings_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('e'=>"earnings"),array('round(SUM(net_amt),8) as sum_net_amt'))
            ->where("username=?",$username)
            ->where("isDaily=?",'Yes')
            ->where("timestamp like date('$start%')")
            );

         if (!empty($earningsres) && sizeof($earningsres)>0){
             //$row = mysql_fetch_assoc($result["dbResource"]);
             $daily_earning_amt=number_format($earningsres->sum_net_amt,8)+0;
            }

            //$query1 = "SELECT round(SUM(amount),8) as direc_amt FROM binaryuserincome WHERE ben_username='$username' AND isDaily='Yes' and status = '1' and created_on like date('$start%') and locked = '0'";

            $binaryuserincome=$binaryuserincome_obj->fetchRow($binaryuserincome_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('b'=>"binaryuserincome"),array('round(SUM(amount),8) as direc_amt'))
            ->where("ben_username =?",$username)
            ->where("isDaily=?",'Yes')
            ->where("status =?", '1')
            ->where("created_on like date('$start%')")
            );

            if(!empty($binaryuserincome) && sizeof($binaryuserincome)>0) {
                // $row1 = mysql_fetch_assoc($result1["dbResource"]);
                $ref_amt=number_format($binaryuserincome->direc_amt,8)+0;
            }

            //$query2 = "SELECT round(SUM(total_earning),8) as sum_binary_amt FROM bin_usr_wkl_income WHERE  parent_username = '' AND username='$username' AND isDaily='Yes' and status = '1' and created_on like date('$start%')";

            $bin_usr_wkl_income=$bin_usr_wkl_income_obj->fetchRow($bin_usr_wkl_income_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('b'=>"bin_usr_wkl_income"),array('round(SUM(total_earning),8) as sum_binary_amt'))
            ->where("parent_username =?", '')
            ->where("username =?",$username)
            ->where("isDaily =?",'Yes')
            ->where("status =?", '1')
            ->where("created_on like date('$start%')")
            );

            if(!empty($bin_usr_wkl_income) && sizeof($bin_usr_wkl_income)>0){
                // $row2 = mysql_fetch_assoc($result2["dbResource"]);
                $binary_amt=number_format($bin_usr_wkl_income->sum_binary_amt,8)+0;
            }
            $total=number_format(($daily_earning_amt + $ref_amt + $binary_amt),8)+0;

            if(isset($total) && ($total != 0))
            {
                //$updateLedger = mysql_query("update daily_ledger set ref_amt = '$ref_amt', binary_amt = '$binary_amt', total_amt = '$total' where username = '$username' and created_on like date('$start%')");
                $updateLedgerdata=array("ref_amt"=>$ref_amt,"binary_amt"=>$binary_amt,"total_amt"=>$total);
                //$updateLedger=$daily_ledger_obj->update($updateLedgerdata,"username = '$username' and created_on like date('$start%')");
                $updateLedger=$daily_ledger_obj->update($updateLedgerdata,$DB->quoteInto("username = ? and created_on like date('$start%')",$username));
                //$DB->quoteInto()


                if($updateLedger){
                    $result = true;
                }
            }
            $count++;
            //ob_flush();
            //flush();

        }

        return $result;
    }
    function binaryEarningAfterDeactivate($Users){

        $bin_user_ref_obj=new Gbc_Model_DbTable_Binaryuserreferences();
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $bin_usr_wkl_incomeObj=new Gbc_Model_DbTable_Binaryuserwelcome();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $type = array('SHA','hardware','MS','ES');
        foreach($Users as $username)
        {
         //$refArray = "SELECT * FROM `binary_user_refences` WHERE `parent_username` = '".$username."' and parent_id<>0";
            $binuserref=$bin_user_ref_obj->fetchAll($bin_user_ref_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('b'=>"binary_user_refences"))
            //->where("`parent_username` = '".$username."' and parent_id<>0"));
            ->where("`parent_username` = ? and parent_id<>0",$username));



            $childArray = array();
            //$i = 0;
            for($i=0;$i<sizeof($binuserref);$i++){
                $childArray[] = $binuserref[$i]['username'];

            }

            if(!empty($childArray[0]) && !empty($childArray[1]))
            {
                $date="";
                $totalContractSecnd=0;
                $childs_first=array();
                //getAllChildforBinary($childArray[0],$childs_first,$date);

                $getAllChildforBinary_first =$common_obj->getAllChildforBinary($childArray[0],$childs_first,$date);

                $childs_second=array();
                //getAllChildforBinary($childArray[1],$childs_second,$date);

                $getAllChildforBinary_second =$common_obj->getAllChildforBinary($childArray[1],$childs_second,$date);

                $childs_first=array_merge(array($childArray[0]),array_filter($childs_first));
                $childs_second=array_merge(array($childArray[1]),array_filter($childs_second));

                    
                if(!empty($childs_first)){

                    //$totalContractFirst=getTotlaDownlineContract($childs_first,$conn,$date);
                    $totalContractFirst=$common_obj->getTotlaDownlineContract($childs_first,$date);
                }else{

                    $totalContractFirst=0;
                }
                    
                if(!empty($childs_second)){
                    $totalContractFirst=$common_obj->getTotlaDownlineContract($childs_second,$date);

                }else{
                    $totalContractSecnd=0;
                }

                //$query="SELECT round(SUM(contract_rate),8) as total_own from invoices WHERE username='".cleanQueryParameter($username)."' AND invoice_status='1' AND contract_type='SHA' and created_on < '$date' GROUP BY username";
                $invoices=$invoices_obj->fetchRow($invoices_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('i'=>"invoices"),array("round(SUM(contract_rate),8) as total_own"))
                ->where("username=?",$username)
                ->where("invoice_status=?",'1')
                ->where("contract_type IN (?)", $type)
                ->where("created_on <?", $date)
                ->group("username"));


                /*$result1 = runQuery($query, $conn);
                 if (noError($result1)) {
                    $row = mysql_fetch_assoc($result1["dbResource"]);
                    }*/
                if(!empty($invoices) && sizeof($invoices)>0){

                    $totalOwn_contract=$invoices->total_own;
                }
                else{

                    $totalOwn_contract=0;
                }

                $all_user[0]['username']=$childArray[0];
                $all_user[0]['contact']=$totalContractFirst;
                $all_user[1]['username']=$childArray[1];
                $all_user[1]['contact']=$totalContractSecnd;
                //$contact = array_column($all_user,'contact');
                $contact = array_column($all_user, 'contact');
                //$contact=$common_obj->array_column($all_user,'contact');

                //$query="SELECT * from bin_usr_wkl_income WHERE parent_username='".cleanQueryParameter($username)."' AND status='1' ";
                $binusrwklincome=$bin_usr_wkl_incomeObj->fetchAll($bin_usr_wkl_incomeObj->select()
                ->setIntegrityCheck(false)
                ->from(array('b'=>"bin_usr_wkl_income"))
                //->where("parent_username='$username' AND status='1'")
                ->where("parent_username=? AND status='1'",$username)

                );

                //$num_rows=mysql_num_rows($result["dbResource"]);
                if(!empty($binusrwklincome) && sizeof($binusrwklincome)>0)
                {
                    $row = array();

                    $new_users=array();
                    if(!empty($binusrwklincome))
                    {
                        $j=0;
                        //while ($data = mysql_fetch_assoc($result["dbResource"])) {
                        //$row[] = $data;
                        //}
                        $row1=array();
                        for($i=0;$i<sizeof($binusrwklincome);$i++)
                        {
                            $row1[$i] = $binusrwklincome[$i]['username'];
                            //$row[] = $data;
                            $row[$i]['username'] = $binusrwklincome[$i]['username'];

                            $row[$i]['parent_username'] = $binusrwklincome[$i]['parent_username'];
                            $row[$i]['last_total'] = $binusrwklincome[$i]['last_total'];
                            $row[$i]['current_toal'] = $binusrwklincome[$i]['current_toal'];
                            $row[$i]['less_value'] = $binusrwklincome[$i]['less_value'];
                            $row[$i]['total_earning'] = $binusrwklincome[$i]['total_earning'];
                            $row[$i]['old_total'] = $binusrwklincome[$i]['old_total'];
                            $row[$i]['created_on'] = $binusrwklincome[$i]['created_on'];
                            $row[$i]['status'] = $binusrwklincome[$i]['status'];
                            $row[$i]['isDaily'] = $binusrwklincome[$i]['isDaily'];


                        }

                        foreach($all_user as $user)
                        {

                            $key = array_search($user['username'],$row1);

                            if(is_numeric($key))
                            {
                                $current_contact=0;
                                if(($user['contact'] > $row[$key]['last_total']) && $row[$key]['less_value']>=0){

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
                            $j++;

                        }


                        //$query="SELECT * from bin_usr_wkl_income WHERE parent_username='' AND username='$username' AND status='1' ";
                        $binusrwklincome=$bin_usr_wkl_incomeObj->fetchRow($bin_usr_wkl_incomeObj->select()
                        ->setIntegrityCheck(false)
                        ->from(array('b'=>"bin_usr_wkl_income"))
                        //->where("parent_username='' AND username='$username' AND status='1'")
                        ->where("parent_username='' AND username = ? AND status='1'",$username)

                        );

                        $contact=array_column($new_users, 'contact');
                        //$contact = array_column($new_users, 'contact');
                        $lower_user = $new_users[array_search(min($contact), $contact)];

                        $less_amount=$lower_user['contact'];
                        $new_total_top_user_contact= $totalOwn_contract;
                        $top_earning=$lower_user['contact']*(8/100);

                        if($top_earning > $totalOwn_contract*15 ){
                            $top_earning=$totalOwn_contract*15;
                        }else if($top_earning >150){
                            $top_earning=150;
                        }
                            
                        $top_earning=number_format($top_earning,8,'.','');

                        //$updateUser = mysql_query("update bin_usr_wkl_income set last_total = '$new_total_top_user_contact',total_earning = '$top_earning',updated_on = now() where username = '$username' and status = '1' ");
                        $updatebinaryuser=array("last_total"=>$new_total_top_user_contact,"total_earning"=>$top_earning,"updated_on"=>new Zend_Db_Expr('NOW()'));
                        //    $updateUser=$bin_usr_wkl_incomeObj->update($updatebinaryuser,"username = '$username' and status = '1' ");
                        $updateUser=$bin_usr_wkl_incomeObj->update($updatebinaryuser,$DB->quoteInto("username = ? and status = '1' ",$username));



                        foreach($new_users as $user)
                        {
                            if($user['username']==$lower_user['username']){
                                $current_contact=0;
                            }else{
                                $current_contact=$user['contact']-$less_amount;
                            }

                            $user['contact'] = number_format($user['contact'],8,'.','');

                            $current_contact = number_format($current_contact,8,'.','');
                            $less_amount = number_format($less_amount,8,'.','');
                            $user['old_total'] = number_format($user['old_total'],8,'.','');

                            //$updateUsers = mysql_query("update bin_usr_wkl_income set last_total = '".$user['contact']."',current_toal = '".$current_contact ."',less_value = '".$less_amount ."', old_total = '".$user['old_total'] ."', updated_on = now() where username = '".$user['username']."' and status = '1' ");

                            $updatebinarywlcome=array("last_total"=>$user['contact'],"current_toal"=>$current_contact,"less_value"=>$less_amount,"old_total"=>$user['old_total'],"updated_on"=>new Zend_Db_Expr('NOW()'));
                            //    $updateUsers=$bin_usr_wkl_incomeObj->update($updatebinarywlcome,"username = '".$user['username']."' and status = '1' ");
                            $updateUsers=$bin_usr_wkl_incomeObj->update($updatebinarywlcome,$DB->quoteInto("username = ? and status = '1' ",$user['username']));



                        }

                    }

                }
                else {

                    $lower_user = $all_user[array_search(min($contact), $contact)];
                    $less_amount=$lower_user['contact'];
                    $top_earning=$lower_user['contact']*(8/100);
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

                    //$updateUser = mysql_query("update bin_usr_wkl_income set last_total = '$totalOwn_contract',total_earning = '$top_earning',updated_on = now() where username = '$username' and status = '1' ");

                    $updatebinaryincome=array("last_total"=>$totalOwn_contract,"total_earning"=>$top_earning,"updated_on"=>new Zend_Db_Expr('NOW()'));
                    //$updateUser=$bin_usr_wkl_incomeObj->update($updatebinaryincome,"username = '$username' and status = '1'");
                    $updateUser=$bin_usr_wkl_incomeObj->update($updatebinaryincome,$DB->quoteInto("username = ? and status = '1'",$username));


                    foreach($all_user as $user){

                        if($user['username']==$lower_user['username']){
                            $current_contact=0;
                        }else{
                            $current_contact=$user['contact']-$less_amount;
                        }

                        $user['contact'] = number_format($user['contact'],8,'.','');
                        $current_contact = number_format($current_contact,8,'.','');
                        $less_amount = number_format($less_amount,8,'.','');
                        $user['old_total'] = number_format($user['old_total'],8,'.','');

                        //$updateUsers = mysql_query("update bin_usr_wkl_income set last_total = '".$user['contact']."',current_toal = '".$current_contact ."',less_value = '".$less_amount ."', old_total = '".$user['old_total'] ."', updated_on = now() where username = '".$user['username']."' and status = '1' ");
                        $updatebinaryuser=array("last_total"=>$user['contact'],"current_toal"=>$current_contact,"less_value"=>$less_amount,"old_total"=>$user['old_total'],"updated_on"=>new Zend_Db_Expr('NOW()'));
                        //$updateUsers=$bin_usr_wkl_incomeObj->update($updatebinaryuser,"username = '".$user['username']."' and status = '1'");
                        $updateUsers=$bin_usr_wkl_incomeObj->update($updatebinaryuser, $DB->quoteInto("username = ? and status = '1'",$user['username']));


                        if($updateUsers){

                            $result = true;
                        }



                    }



                }


            }
            $count++;
        }

        return $result;


    }

    /*function getTotlaDownlineContract($users,$date='',$startdate='',$enddate='')
     {
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        global $blanks;

        if(!empty($date)){
        $date = " and created_on < '$date'";
        }
        $totalContract=0;
        $users=array_filter($users);

        for($i=0;$i<sizeof($users);$i++)
        {
        // $query="SELECT round(sum(contract_rate),2) as qty from invoices WHERE username='".cleanQueryParameter($user)."' AND contract_type='SHA' and locked = '0' AND invoice_status='1' $date";
        $invoicesresult=$invoices_obj->fetchRow($invoices_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('i'=>"invoices"))
        ->where("username='$users' AND contract_type='SHA' and locked = '0' AND invoice_status='1' $date")
        );

        if(!in_array($date, $blanks)){

        $invoicesresult .= " AND archive='0'";
        }

        if (!empty($invoicesresult) && sizeof($invoicesresult)>0) {

        $invoiceqty=$invoicesresult->qty;

        }
        $totalContract=$totalContract + $invoiceqty;

        }
        return $totalContract;

        }*/
    function KitFundUpdate($data)
    {
        $value = $data['radio'];
        $admin_set_obj = new Gbc_Model_DbTable_Adminsetting();
        $upd_arr=array('kit_fund_mode'=>$value);
        $upd_qry=$admin_set_obj->update($upd_arr,"1=1");

        if(!empty($upd_qry)){
            $msg = "Kit and Fund Transfer mode updated";
        }else{
            $msg = "Not updated";
        }
        return $msg;



    }

    public function getKitFundMode(){
        $admin_set_obj = new Gbc_Model_DbTable_Adminsetting();
        $admin_set=$admin_set_obj->fetchRow($admin_set_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('a'=>"admin_setting")));
        //$KitFundModeResult = mysql_query("select kit_fund_mode from admin_setting");
        //$KitFundModeAssoc = mysql_fetch_assoc($KitFundModeResult);
        if(!empty($admin_set) && sizeof($admin_set)>0)
        {
            $KitFundMode = $admin_set->kit_fund_mode;
        }
        return $KitFundMode;
    }
    public function validateUserSession($username)
    {

        $user = new Gbc_Model_DbTable_Userinfo();
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $ip_address=$misc_obj->get_client_ip();
            
        $user_row = $user->fetchRow($user->select()
        ->setIntegrityCheck(false)
        ->from(array("u"=>"user_info"),array("session_id"))
        ->where("u.username=?",$username)
        ->where("u.ip_address=?",$ip_address));
        return $user_row;
    }
    public function validateUserSessionAdmin($username)
    {

        $admin_obj=new Gbc_Model_DbTable_Adminsetting();
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $ip_address=$misc_obj->get_client_ip();
            
        $user_row = $admin_obj->fetchRow($admin_obj->select()
        ->setIntegrityCheck(false)
        ->from(array("u"=>"admin_setting"),array("session_id"))
        ->where("u.admin_user_name=?",$username)
        ->where("u.ip_address=?",$ip_address));
        return $user_row;
    }

    public function validateUserSessionSubadmin($username)
    {

        $sub_admin_obj=new Gbc_Model_DbTable_Subadminuser();
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $ip_address=$misc_obj->get_client_ip();
            
        $user_row = $sub_admin_obj->fetchRow($sub_admin_obj->select()
        ->setIntegrityCheck(false)
        ->from(array("u"=>"sub_admin_users"),array("session_id"))
        ->where("u.email=?",$username)
        ->where("u.ip_address=?",$ip_address));
        return $user_row;
    }
    function kitEligibleforrefund($username, $status,$kit_number = null) {
	
        $returnArr = array();
        $date = date('Y-m-d');
        $kits_obj=new Gbc_Model_DbTable_Kits();
		$status = array("Active","Used");

        $data = $kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('kits'=>"kits"),array('created_on'));

        $data->where('kits.created_on > ?',REFUND_DATE);
        $data->where('kits.status in (?)',$status);
        $data->where('kits.created_on BETWEEN DATE_SUB(NOW(), INTERVAL 29 DAY) AND NOW()');

        if(!empty($username) && $username!='')
        {
            //$where  = $where . " AND kits.username = '" . ($username) . "'";
            $data->where('kits.username = ?',$username);
        }
        if(!empty($kit_number) && $kit_number!='')
        {
            //$where  = $where . " AND kits.kit_number = '" . ($kit_number) . "'";
            $data->where('kits.kit_number = ?',$kit_number);
        }


        $kits_data = $kits_obj->fetchRow($data);
        if(!empty($kits_data) && sizeof($kits_data)>0)
        {
            $KitCreated =  date('Y-m-d',strtotime($kits_data->created_on));
            return $KitCreated;
        }

    }

    function userEligibleforrefund($username, $status,$type,$invoice_id = null) {
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $returnArr = array();
        $date = date('Y-m-d');

        $data = $invoices_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('invoices'=>"invoices"),array('kit_created'));

        if(!empty($username) && $username!='')
        {
            //$where  = $where . " AND invoices.username = '" . ($username) . "'";
            $data->where('invoices.username = ?',$username);
        }
        if(!empty($invoice_id) && $invoice_id!='')
        {
            //$where  = $where . " AND invoices.invoice_id = '" . ($invoice_id) . "'";
            $data->where('invoices.invoice_id = ?',$invoice_id);
        }
        $data->where("invoices.contract_type =?",$type);
        $data->where("invoices.invoice_status =?",$status);
        $data->where("invoices.created_on >?",REFUND_DATE);
        $data->where("invoices.kit_created BETWEEN DATE_SUB(NOW(), INTERVAL 29 DAY) AND NOW()");
        $data->group("invoices.invoice_id");
        $data->limit(1);

        $result = $invoices_obj->fetchRow($data);

        if(!empty($result) && sizeof($result)>0)
        {
            $KitCreated =  date('Y-m-d',strtotime($kits_data->kit_created));
            return $KitCreated;
        }

    }

    public function CheckTransactionforrefund($txid){
        $result = file_get_contents("https://blockchain.info/q/txtotalbtcoutput/".$txid);
        // var_dump($result);
        if(!empty($result)){
            $msg =  "success";
        }else{
            $msg = "fail";
        }
        return $msg;
    }
    function checkInvoice($data){
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $username = $data['username'];
        $kit_number = $data['kit_number'];
        $kitEligibleforrefund = $common_obj->kitEligibleforrefund($username,'Active',$kit_number);

        if(!empty($kitEligibleforrefund)){
            $msg =  "success";
        }else{
            $msg = "fail";
        }
        return $msg;
    }

    function getBinaryUsersForCron($limit,$offset,$BinaryDirect=null,$CurrentDate = null)
    {
        $user = new Gbc_Model_DbTable_Userinfo();
        $date = !empty($CurrentDate)?"".$CurrentDate."":date('Y-m-d');
        $returnArr = array();
        // $query = "SELECT A.username FROM user_info A WHERE  binaryUser IS NOT NULL";
        // $query = "SELECT A.username FROM `binary_user_refences` A";
        // $query = "SELECT A.username FROM user_info A WHERE  binaryUser IS NOT NULL and username like 't%'";
        if($BinaryDirect == 1){
            $user_row = $user->fetchAll($user->select()
            ->setIntegrityCheck(false)
            ->from(array('u' =>'user_info'),array('username'))
            ->where("binaryUser IS NOT NULL")
            ->where("binary_direct =?", '0')
            ->where("created_on <?", $date)
            ->limit($limit,$offset));

        }else if($BinaryDirect == 2){
            $user_row = $user->fetchAll($user->select()
            ->setIntegrityCheck(false)
            ->from(array('u' =>'user_info'),array('username'))
            ->where("binaryUser IS NOT NULL")
            ->where("binary_direct =?", 1)
            ->where("created_on <?", $date)
            ->limit($limit,$offset));
        }else {
            $user_row = $user->fetchAll($user->select()
            ->setIntegrityCheck(false)
            ->from(array('u' =>'user_info'),array('username'))
            ->where("binaryUser IS NOT NULL")
            ->where("created_on <?", $date)
            ->limit($limit,$offset));
        }

        for($i=0;$i<sizeof($user_row);$i++)
        {
            $res[] = $user_row[$i]['username'];
        }



        return $res;
    }

    function checkOfferDetails(){
        $special_off_obj=new Gbc_Model_DbTable_Specialoffer();
        $returnArray = array();
        $offers =$special_off_obj->fetchAll($special_off_obj->select()
        ->where("status = '1'")
        );

        $target_pairs = 0;
        if(!empty($offers) && sizeof($offers)>0)
        {
            for($i=0;$i<sizeof($offers);$i++)
            {
                $id = $offers[$i]['id'];
                $returnArray[$i]['offer_id'] = $id;
                if($id== 15 || $id== 16){
                    $target_pairs = $offers[$i]['pairs'];
                }else if($id=== 24){
                    $target_pairs += $offers[$i]['pairs'];
                }else{
                    $target_pairs += $offers[$i]['pairs'];
                }
                $returnArray[$i]['target'] = $target_pairs;
            }
        }
        return $returnArray;
    }
    /* cron */


function updateUserAccountPassword($userInfo, $changePwd = false,$key=false) {
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        
    //    print_r($userInfo);
        if($userInfo["password"] && $userInfo["salt"] && $key){
            $salt = $userInfo["salt"];
            $password = $userInfo["password"];
        }else{
            $salt =$misc_obj-> generateSalt();
            $password =$misc_obj-> encryptPassword($userInfo["password"], $salt);
        }
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        

        //$upd_arr=array('email_address'=>$userInfo["email_address"],'name'=>$userInfo["name"]);
        $upd_arr=array();
    //    if ($changePwd) {
            $upd_arr['salt']=$salt;
            $upd_arr['password']=$password;
    //    }
        //$upd_qry=$user_obj->update($upd_arr,"username='" . ($userInfo["username"]) . "'");
        $upd_qry=$user_obj->update($upd_arr, $DB->quoteInto("username=?",$userInfo["username"]));
//print_r($upd_qry);
        if(!empty($upd_qry))
        {
            return "success";
        }

    }


    /*    function updateCronStatus($cronName,$start = null,$end = null){


    try {
    $db = Zend_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $cronstatus_obj = new Gbc_Model_DbTable_Cronstatus();
    $ExistingQuery = $cronstatus_obj->fetchRow($cronstatus_obj->select()
    ->where("cron='".trim($cronName)."'"));

    // echo "select * from cron_status where cron = '$cronName'";

    $date =  new Zend_Db_Expr('NOW()');

    $ExistingCount = sizeof($ExistingQuery);

    if(!empty($start)){
    $cronTime = "start = '$start'";
    }else if(!empty($end)){
    $cronTime = "end = '$end',updated_on = '$date'";
    }else{
    $cronTime = "updated_on = '$date'";
    }
    // echo $ExistingCount;
    if($ExistingCount){

    $status=$cronName.' cron updated successfully';
    $crondata=array("status"=>$status,"updated_on"=>$date);
    // echo "<pre>";
    //print_r($upbannerdata);exit;
    $updatemanagebanner=$cronstatus_obj->update($crondata,"cron='".$cronName."'");
    $db->commit();
    echo "update";
    }else{
    $status=$cronName.' cron updated successfully';
    $crondata=array("status"=>$status,"created_on"=>$date,"cron"=>$cronName,"updated_on"=>$date);

    $updatemanagebanner=$cronstatus_obj->insert($crondata);
    $db->commit();
    echo "else insert";
    }
    //    $db->commit();
    }
    catch(Exception $e)
    {
    $data=array('success'=>'','failure'=> $e->getMessage());
    echo json_encode($data);exit;
    }
    }*/
    function getGlobalearning($fieldName, $timestamp) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        $golabal_var_obj=new Gbc_Model_DbTable_Globalvariables();
        if(isset($timestamp) && $timestamp!='')
        {
            $user_row = $golabal_var_obj->fetchAll($golabal_var_obj->select()
            ->where("timestamp<?",$timestamp)
            ->order("timestamp DESC")
            ->limit(1)
            );
        }
        else
        {
            $user_row = $golabal_var_obj->fetchAll($golabal_var_obj->select()
            ->order("timestamp DESC")
            ->limit(1)
            );
        }
        $db->commit();
        return $user_row;
    }

    function getUserContractsOnly($username, $status,$type,$CurrentDate=null) {
        //using global blanks array

            
        //initializing blank return array
        $Invoices_obj=new Gbc_Model_DbTable_Invoices();
        $returnArr = array();
        $date = !empty($CurrentDate)?"".$CurrentDate."":date('Y-m-d');

        $contract_type = array('SHA','hardware','MS','ES');
        
        $returnArr = $Invoices_obj->fetchAll($Invoices_obj->select()
        ->where("username = ?",$username)
        ->where("contract_type IN (?)",$contract_type)
        ->where("invoice_status = ?",$status)
        ->where("created_on < '$date'")
        ->group("invoice_id")
        );

        return $returnArr;
    }
    function insertEarningTemp($earning_info, $earning_type, $earning_coin, $temp,$CurrentDate=null) {
        //echo "<pre>";
        //print_r($earning_info);exit;
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        if(empty($CurrentDate) || $CurrentDate=='' || $CurrentDate==null)
        {
            $date = date('Y-m-d');
        }
        else 
        {
            $date = $CurrentDate;
        }
        //$date = !empty($CurrentDate)?"".$_REQUEST['CurrentDate']."":date('Y-m-d');
        $now = time() . "_" . rand();
        $type = "";
        $earningtemp_obj=new Gbc_Model_DbTable_Earningstemp();
        if ($earning_type == 0) {
            $type = 'pool';
        } else {
            $type = 'aff';
        }
        $coin = "";
        if ($earning_coin == 0) {
            $coin = 'BTC';
        } else {
            $coin = 'NIC';
        }
        if(!empty($temp) && $temp!=''){

        }else{
            $temp=0;
        }
        if(!empty($CurrentDate) && $CurrentDate!='' && $CurrentDate!=null)
        {
            $date=($date) . " 00:00:00'";
        }
        try{
            $earning_info["pool_fees"] = number_format($earning_info["pool_fees"],8)+0;
            $earning_info["total_amt"] = number_format($earning_info["total_amt"],8)+0;
            $earning_info["net_amt"] = number_format($earning_info["net_amt"],8)+0;

            if(!empty($CurrentDate) && $CurrentDate!='' && $CurrentDate!=null)
            {

                $insertearning=array("username"=>$earning_info["username"],"type"=>$type,"pool_fees"=>$earning_info["pool_fees"],"total_amt"=>$earning_info["total_amt"],"net_amt"=>$earning_info["net_amt"],"coin"=>$coin,"contract_id"=>$earning_info["contract_id"],"invoice_id"=>$earning_info["invoice_id"],"temp"=>$temp,"timestamp"=>$date);

                //  $query = "INSERT INTO earnings_temp(username, type, pool_fees, total_amt, net_amt,coin,contract_id, invoice_id, temp,timestamp) VALUES (";
            }else{

                $insertearning=array("username"=>$earning_info["username"],"type"=>$type,"pool_fees"=>$earning_info["pool_fees"],"total_amt"=>$earning_info["total_amt"],"net_amt"=>$earning_info["net_amt"],"coin"=>$coin,"contract_id"=>$earning_info["contract_id"],"invoice_id"=>$earning_info["invoice_id"],"temp"=>$temp);
                // $query = "INSERT INTO earnings_temp(username, type, pool_fees, total_amt, net_amt,coin,contract_id, invoice_id, temp) VALUES (";
            }


            $insertearingtemp=$earningtemp_obj->insert($insertearning);

            $db->commit();
            return "insert";

        }catch(Exception $e)
        {
            $data=array('success'=>'','failure'=> $e->getMessage());
            echo json_encode($data);exit;
        }
    }

    function insertEarning($earning_info, $earning_type, $earning_coin, $temp, $CurrentDate=null) {
        //echo "<pre>";
        //    print_r($earning_info);
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        if(empty($CurrentDate) || $CurrentDate=='' || $CurrentDate==null)
        {
            $date = date('Y-m-d');
        }
        else 
        {
            $date = $CurrentDate;
        }
        //$date = !empty($_REQUEST['CurrentDate'])?"".$_REQUEST['CurrentDate']."":date('Y-m-d');
        $now = time() . "_" . rand();
        $type = "";
        $earning_obj=new Gbc_Model_DbTable_Earnings();
        if ($earning_type == 0) {
            $type = 'pool';
        } else {
            $type = 'aff';
        }
        $coin = "";
        if ($earning_coin == 0) {
            $coin = 'BTC';
        } else {
            $coin = 'NIC';
        }
        if(!empty($temp) && $temp!=''){

        }else{
            $temp=0;
        }
        if(!empty($CurrentDate) && $CurrentDate!='' && $CurrentDate!=null){
            $date=($date) . " 00:00:00'";
        }
        try{
            $earning_info["pool_fees"] = number_format($earning_info["pool_fees"],8)+0;
            $earning_info["total_amt"] = number_format($earning_info["total_amt"],8)+0;
            $earning_info["net_amt"] = number_format($earning_info["net_amt"],8)+0;
            
            if(!empty($CurrentDate) && $CurrentDate!='' && $CurrentDate!=null){
                $insertearning=array("username"=>$earning_info["username"],"type"=>$type,"pool_fees"=>$earning_info["pool_fees"],"total_amt"=>$earning_info["total_amt"],"net_amt"=>$earning_info["net_amt"],"coin"=>$coin,"contract_id"=>$earning_info["contract_id"],"invoice_id"=>$earning_info["invoice_id"],"temp"=>$temp,"timestamp"=>$date);

                //  $query = "INSERT INTO earnings_temp(username, type, pool_fees, total_amt, net_amt,coin,contract_id, invoice_id, temp,timestamp) VALUES (";
            }else{
                $insertearning=array("username"=>$earning_info["username"],"type"=>$type,"pool_fees"=>$earning_info["pool_fees"],"total_amt"=>$earning_info["total_amt"],"net_amt"=>$earning_info["net_amt"],"coin"=>$coin,"contract_id"=>$earning_info["contract_id"],"invoice_id"=>$earning_info["invoice_id"],"temp"=>$temp);
                // $query = "INSERT INTO earnings_temp(username, type, pool_fees, total_amt, net_amt,coin,contract_id, invoice_id, temp) VALUES (";
            }
            //  print_r($insertearning);
            $insertearingtemp=$earning_obj->insert($insertearning);

            $db->commit();
            return "insert";

        }catch(Exception $e)
        {
            $data=array('success'=>'','failure'=> $e->getMessage());
            echo json_encode($data);exit;
        }

    }



    function getAllUserReports($data=null,$PaginateLimit,$filename=null,$path=null){

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $fin_bal_obj = new Gbc_Model_DbTable_FinalBalance();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $allUsers = array();
        if(!empty($data)){

            $retArray = $common_obj->getData($data,$PaginateLimit,$filename,$path);
            // exit;
            return $retArray;
        }else{

            $fields = "*";
            $AllUserData=$fin_bal_obj->fetchAll($fin_bal_obj->select()
            );
            if(isset($AllUserData) && sizeof($AllUserData)>0)
            {
                $allUsers = $AllUserData->toArray();
            }
            /*$AllUserData = ("select $fields from final_balance");
             $result = $DB->query($AllUserData);
             $allUsers[] = $result->fetchAll();*/
            // var_dump($retArray);
         // exit;
            return $allUsers;
        }
        // var_dump($retArray);
    }










    function getData($data=null,$PaginateLimit,$filename=null,$path=null){
        $MaxRowsCount=100;
        // var_dump($data);

        $report = $data['reports'];
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        if(!empty($report)){
            if($report == 1){
                $fields = "username,total_amt,timestamp";
                $created = "timestamp";
                $table = "earnings";
                $tableFields = "Username,Amount,Created";
            }else if($report == 2){
                $fields = "ben_username,from_username,amount,created_on";
                $table = "binaryuserincome";
                $tableFields = "Username,Child User,Amount,Created";
            }else if($report == 3){
                $fields = "username,parent_username,total_earning,created_on";
                $table = "bin_usr_wkl_income";
                $tableFields = "Username,Parent Username,Amount,Created";
            }else if($report == 4){
                $fields = "username,ref_amt,binary_amt,daily_earning_amt,daily_earning_amt_new,daily_earning_amt_new1,total_amt,created_on";
                $table = "daily_ledger";
                $tableFields = "Username,Direct Earning, Binary Earning, Daily Earning, Daily Earning New, Daily Earning New1,Total Amount,Created";
            }else if($report == 5){
                $fields = "username,ref_amt,binary_amt,daily_earning_amt,daily_earning_amt_new,daily_earning_amt_new1,total_amt,adm_roi_payout,adm_bin_payout,adm_ref_payout,created_on";
                $table = "final_ledger";
                $tableFields = "Username,Direct Earning, Binary Earning, Daily Earning, Daily Earning New, Daily Earning New1,Total Amount, ROI Admin Fees,RSI Admin Fees,DSI Admin Fees,Created";
            }else if($report == 6){
                $fields = "username,total_amt,total_admin_fees,total_withdrawal,total_fund,bal_amt,created_on,updated_on";
                $table = "final_balance";
                $tableFields = "Username,Total Amount,Total Admin Fees,Total Withdrawal,Total Fund,Balance Amount,Created,Updated ";

            }else if($report == 7){
                $fields = "username,kit_number,invoice_id,kit_price,status,kit_used_by,comment,kit_type,created_on,kit_used_date";
                $table = "kits";
                $tableFields = "Username,Kit Number,Invoice Id,Kit Price, Status, Kit Used By,Comment,Kit Type,Created,Kit Used Date ";
            }else if($report == 8){
                $fields = "username,invoice_id,amtPaid,contract_rate,comment,invoice_status,update_by,creared_on,updated_on";
                $table = "kit_invoices";
                $created = "creared_on";
                $tableFields = "Username,Invoice Id,Amount Paid,Contract Rate,Comment,Status,Updated By,Created,Updated";
            }else if($report == 9){
                $fields = "invoices.username,invoices.invoice_id,use_kit_number,amtPaid,invoices.contract_rate,(select total_price from contracts where contract_id = invoices.contract_id and contract_id >=14) as total_price,kit_price_in_mcap,invoice_status,invoices.created_on";
                 $created = "invoices.created_on";
				$table = "invoices";
                $tableFields = "Username,Invoice Id,Kit Number,Amount Paid,Contract Rate,Price in USD, Price in MCAP, Status,Created";
            }else if($report == 10){
                $fields = "username,user_to,btc_amt,withdrawal_type,wallet_addr,addr,transaction_id,request_by,status,timestamp as created,updated_on";
                $table = "withdrawals";
                $tableFields = "Username,User To,Amount,Withdrawal Type,Wallet Address,Transaction ID,Request By,Status,Created,Updated ";
            } else if($report == 11){
                $fields = "queries.username,queries.id,queries.subject,queries.query,queries.created_on,'' as message,queries.assigned_to as replied_by,queries.reply_date as updated_on";
                $created = "queries.created_on";
                $table = "help_query as queries";
                $tableFields = "Username,Ticket Id,Subject,Query,Created,Reply,Replied By,Replied ";
            } else if($report == 12){
                // $fields = "Payment.username,Payment.invoice_id,Payment.paid_amount,Payment.price,Payment.confirmations,Payment.status,Payment.payment_url,Payment.txid,Payment.created_on";
                $fields = "Payment.username,Payment.invoice_id,Payment.paid_amount,Payment.price,Payment.confirmations,Payment.status,Payment.address,Payment.txid,Payment.created_on";
                $table = "payment_response as Payment";
                // $tableFields = "Username,Invoice Id,Paid Amount,Price,Confirmations,Status,Payment URL,Txid,Created ";
                $tableFields = "Username,Invoice Id,Paid Amount,Price,Confirmations,Status,Wallet,Txid,Created ";
            }else if($report == 13){
                $fields = "requests.wallet_address, requests.amount, requests.mcap_address, requests.mcap_rate, requests.mcap_value, requests.status, requests.request_date, requests.txid, requests.comment";
                $created = "requests.created_on";
                $table = "daily_payout_withdrawal_requests as requests";
                $tableFields = "Wallet Address, Amount, MCAP Address, MCAP Rate, MCAP Value, Status, Requested, Transaction Id ,Comment";
            }else if($report == 14){
                $fields = "payments.username, payments.address, payments.invoice_id, payments.confirmations, payments.paid_amount, payments.txid, payments.status,payments.created_on";
                $created = "payments.created_on";
                $table = "query_payment_details as payments";
                $tableFields = "Username, Wallet Address, Invoice ID, Confirmations, Amount, Transaction Id, Status, Created On";
            }else if($report == 15){
                $fields = "username, wallet_addr";
                $created = "created_on";
                $table = "daily_payout_data";
                $tableFields = "Username, Wallet Address";
            }else if($report == 16){
                $fields = "wallet_addr,total_btc_amt,total_btc_withdrawal,balance_btc";
                $created = "created_on";
                $table = "gbc_wallet_data";
                $tableFields = "Wallet Address, Total Amount, Withdrawal , Balance";
            }else if($report == 17){
                $fields = "username,wallet_addr,btc_amount,created_on";
                $table = "daily_payout_data";
                $tableFields = "Username,Wallet address, Amount,Created On";
            }
            $SearchQuery = '&reports='.$report;

        }else{
            $fields = "username,total_amt,total_admin_fees,total_withdrawal,total_fund,bal_amt,created_on,updated_on";
            $table = "final_balance";
            $tableFields = "Username,Total Amount,Total Admin Fees,Total Withdrawal,Total Fund,Balance Amount,Created,Updated ";
        }

        $created = !empty($created)?$created:"created_on";
        // echo $table;
        if($table == "help_query as queries"){
            $query = "select $fields from $table  where 1=1 ";
        }else if($table == "invoices"){
			$query = "select $fields from $table left join kits on kits.kit_number= $table.use_kit_number  where 1=1 ";
		}else{
            $query = "select $fields from $table where 1=1 ";
        }

        $whereQuery = "";


        if(!empty($data['user'])){
            $user = $data['user'];
            if($table =="binaryuserincome"){
                $whereQuery .= "and ben_username='$user'";
            }else if($table == "invoices"){
				$whereQuery .= "and invoices.username='$user'";
			}else {
                $whereQuery .= "and username='$user'";
            }

            $SearchQuery .= '&user='.$user;
        }
        if($table =="bin_usr_wkl_income"){
            $whereQuery .= "and parent_username = ''";
        }

        if(!empty($data['start_date'])){
            $data['start_date'] = str_replace(",","",$data['start_date']);
            $start_date = date('Y-m-d',strtotime($data['start_date']))." 00:00:00";
            $whereQuery .= " and $created>='$start_date'";
            $SearchQuery .= '&start_date='.$start_date;
        }
        if(!empty($data['end_date'])){
            $data['end_date'] = str_replace(",","",$data['end_date']);
            $end_date =  date('Y-m-d',strtotime($data['end_date']))." 23:59:59";
            $whereQuery .= " and $created<='$end_date'";
            $SearchQuery .= '&end_date='.$end_date;
        }
		 if(!empty($data['wallet_addr'])){
			   $wallet_addr = $data['wallet_addr'];
			   if($table == "daily_payout_data" || $table == "gbc_wallet_data" ){
				 $whereQuery .= "and wallet_addr='$wallet_addr'";
				}else{
				 $whereQuery .= "and wallet_address='$wallet_addr'";
			   }          
			 $SearchQuery .= '&wallet_addr='.$wallet_addr;
        }



        if(!$data['full_report']){


            $CountQuery = "select count(*) as count from $table where 1=1 ";
            $CountQuery .= $whereQuery;
            //echo $CountQuery;exit;
            $CountQuery .= " limit $MaxRowsCount,1";
            //echo $CountQuery;exit;

            $UserCountRes=$DB->query($CountQuery);

            $UserCountResult = $UserCountRes->fetch();
            $UserCount=$UserCountResult['count'];

            // echo $MaxRowsCount;exit;

            if($UserCount){
                $UserCount = $MaxRowsCount;
            }else{
                $UserCountQuery = "SELECT count(*) as count FROM $table where 1=1 ";
                //echo $whereQuery;exit;
                $UserCountQuery .= $whereQuery;
                //echo $UserCountQuery;exit;

                $UserCountRes=$DB->query($UserCountQuery);
                $UserCountResult = $UserCountRes->fetch();
                    
                $UserCount=$UserCountResult['count'];
            }


            $pages = ceil($UserCount/$PaginateLimit);

            $startLimit = !empty($data['page']) && $data['page'] > 1?($data['page']-1)*$PaginateLimit:0;

            if(!empty($PaginateLimit) && !empty($startLimit)){
                $Limit = "limit ".$startLimit.", ".$PaginateLimit;
            }else if(!empty($PaginateLimit)){
                $Limit = "limit ".$PaginateLimit;
            }else{
                $Limit ="";
            }
			
			   if($table == "daily_payout_data" && $report == 15){
      		      $whereQuery .= " group by username";
			   }			
            //$whereQuery .= " order by $created desc";
            $whereQuery .= " $Limit";

            $query .= $whereQuery;
            //    echo $query;exit;

            // echo $query;
            // exit;

            // echo "pages ".$pages;
            // echo "<br>";
            $PaginationCount = 5;
            $PaginationCountFactor = intval($PaginationCount/2);
            $currentPage = !empty($data['page'])?$data['page']:1;
            $Pagination = '<ul class="pagination">';

            $StartPage = !empty($data['page'])?$data['page']:1;

            if($pages <= $PaginationCount){
                $StartPage = 1;
                $limitPage = $pages;
            }else if($pages > $PaginationCount){
                $limitPage = $StartPage + (($PaginationCount+1) - $PaginationCountFactor);

                if(!empty($data['page']) && $data['page'] < $pages-(($PaginationCount+1)-$PaginationCountFactor)){
                 // if(!empty($data['page']) ){
                    $StartPage = ($data['page']-$PaginationCountFactor > 0)?$data['page']-$PaginationCountFactor:1;
                }else if(!empty($data['page']) && $data['page'] >= $pages-($PaginationCount+1)){
                    $StartPage = ($pages - $PaginationCount > 0)?$pages - $PaginationCount:1;
                    $limitPage = ($data['page']>1)?$pages -1:1;
                }
            }
            // echo "StartPage ".$StartPage;
            // echo "<br>";
            // echo "limitPage ".$limitPage;
            // echo "<br>";

            if(!empty($data['page']) && $data['page'] > 1){
                $SearchQuery1 = $SearchQuery.'&page=1';
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery1).'"></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($data['page']-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery2).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
            }

            for($j=$StartPage;$j<=$limitPage;$j++){
                $SearchQuery3[$j] = $SearchQuery.'&page='.$j;
                // var_dump($SearchQuery3);
                // echo "yes";
                if( $j == $currentPage){
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery3[$j]).'" style = "color: #000;">'.$j.'</a></li>';
                }else{
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery3[$j]).'">'.$j.'</a></li>';
                }
            }

            if($currentPage < $pages-1){
                $SearchQuery1 = $SearchQuery.'&page='.($currentPage+1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery1).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($pages-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/viewreport?search='.($SearchQuery2).'"></a></li>';
            }
            $Pagination .= '</ul>';
            if(!empty($data['page']) && $data['page'] >1){
                $startRow = ($data['page']-1)*$PaginateLimit +1;
            }
        }


        $UserCountRes=$DB->query($query);
        //echo $UserCountRes;exit;
        $AllUserData = $UserCountRes->fetchAll();



        $AllData = $common_obj->sendData($AllUserData,$tableFields,$startRow,$Pagination,$filename,$path);
        //print_r($AllData);exit;

        return $AllData;
    }
    function sendData($AllUserData=null,$tableFields,$startRow=null,$Pagination,$filename=null,$path=null){
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(!empty($_GET) && ($_GET['export'] == "report") && !empty($authUserNamespace->report) && !empty($filename) && !empty($path)){
            $common_obj->downloadCSV($AllUserData,$filename,$path);
            exit;
        }
        if(!empty($AllUserData)){
            // var_dump($AllUserData);
            // exit;
            $thFields = '';
            $tableFields = explode(',',$tableFields);
            $data =  ' <div class="col-lg-12 ">
                    <div class="clearfix lightgraybg">
                    <div class="bs-example table-responsive" data-example-id="bordered-table" id="parentTable">
                    <table id="data-table-simple"  class="table table-bordered innertbl display" cellspacing="0">';
            $data .=  "<thead><tr><th>Sr. No.</th>";
            foreach($tableFields as $tableField){
                $thFields .= "<th>$tableField</th>";
                $data .= "<th>$tableField</th>";
            }
            $data .=  "</tr></thead>";
            $data .=  "<tfoot><tr><th>Sr. No.</th>";
            $data .=  $thFields;
            $data .=  "</tr></tfoot><tbody>";
            $i=!empty($startRow)?$startRow:1;

            foreach($AllUserData as $row){
                $data .=  "<tr>";
                $data .=  "<td>".$i."</td>";
                $flag = true;
                // var_dump($row);
                foreach($row as $key => $field){
                    // echo $field;
					
                    if($key == "created_on" || $key == "updated_on" || $key == "timestamp"){
                        if($flag && !empty($field)){
                            $flag = false;
                            $data .=  "<td>".date('jS-M-Y h:i:s A',strtotime($field))."</td>";
                        }else{
                            $data .=   "<td></td>";
                        }
                    }
					else if(($key == "addr" || $key == "wallet_addr") && ($_POST['reports'] == "10" || $_POST['reports'] == "13" )) {
                        if($flag && !empty($field)){
                            $flag = false;
                            $data .=  "<td>".$field."</td>";
                        }
                    }
					else if($key == "status" && $_POST['reports'] == "13"){
						if($field == "1"){
							$field = "Pending";
						}else if($field == "2"){
							$field = "Paid";
						}else{
							$field = "Invalid";
						}
							
						$data .=  "<td>".$field."</td>";
					}else{
                        if(!empty($field)){
                            $data .=  "<td>".$field."</td>";
                        }else{
                         $data .=  "<td></td>";
                        }
                    }

                }
                    
                $data .=  "</tr>";
                $i++;
            }
            $data .=  "</tbody>";
            $data .=  '</table></div></div></div>';
        }
        $Data['data'] = $data;
        $Data['Pagination'] = $Pagination;


        return $Data;
    }

    function downloadCSV($AllUserData,$filename,$path){

        $common_obj = new Gbc_Model_Custom_CommonFunc();


        if(!file_exists($path)){

         mkdir($path);
        }
        $file = $path."".$filename;

        //$response =WriteCSV($AllUserData,$file);
        $response=$common_obj->WriteCSV($AllUserData,$file);

        header("Content-Disposition: attachment; filename=".urlencode($filename));
        header("Content-Type: application/csv");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        flush(); // this doesn't really matter.
        $fp = fopen($file, "r");
        while (!feof($fp))
        {
            echo fread($fp, 65536);
            flush(); // this is essential for large downloads
        }
        fclose($fp);
        // unset($_SESSION['report']);

    }
    function WriteCSV($user_query,$file,$encode=null){

        //var_dump($user_query);

        //exit;
        $flag = false;
        $fp = fopen($file, 'w');
        if(!$fp){
            die('can not open file 2');
        }

        if(is_array($user_query)){
            //exit;
            foreach($user_query as $row){
                if (!$flag) {
                    // display field/column names as first row

                 fputcsv($fp, array_keys($row),',');
                 // echo  array_keys($row) . "\r\n";
                 $flag = true;
                }

                fputcsv($fp, array_values($row),',');
                    
            }

        }else{

            while ($row = mysql_fetch_assoc($user_query)) {
                //var_dump($row);
                if($encode == 'encode'){
                    $row['username'] = '"'.$row['username'].'"';
                }

                if (!$flag) {
                    // display field/column names as first row

                    // fputcsv($fp, array_keys($row),',');
                 if(!fputcsv($fp, array_keys($row),',')){
                        die('can not create file 2');
                 }
                 // echo  array_keys($row) . "\r\n";
                 $flag = true;
                }

                //fputcsv($fp, array_values($row),',');
             if(!fputcsv($fp, array_values($row),',')){
                    die('can not create file 21');
             }
              
            }
        }
        //exit;
        fclose($fp);
     //return $fp;

    }
    function dailyPairs($Users = null,$count = null){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $User_info_obj=new Gbc_Model_DbTable_Userinfo();
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
        $special_perm_obj=new Gbc_Model_DbTable_SpecialPermission();
        $special_off_obj=new Gbc_Model_DbTable_Specialoffer();
        $invoices_obj = new Gbc_Model_DbTable_Invoices();
        $pair_count=new Gbc_Model_DbTable_Paircount();
        if(!empty($_REQUEST['username']) && $_REQUEST['username']!='')
        {
            $username=$common_obj ->cleanQueryParameter($_REQUEST['username']);
        }
        else
        {
            $username=$authUserNamespace->user;
        }

            
        $startdate =  date('2016-08-01 00:00:00');
        $enddate =   date("2016-08-31 23:59:59");
        $end_date =   date("2018-07-31 23:59:59");


        $offerDetails = $common_obj-> checkOfferDetails();
        $CurrentMonth = date('Y-m');
        $FirstQMonth =  date('2016-04');
        $LastQMonth =   date('2016-06');

        $Permissions =$special_perm_obj->fetchRow($special_perm_obj->select()
        );
        $Permissions=$Permissions->toArray();

        if(empty($Users) || $Users==''){

            $PairsResult =$pair_count->fetchAll($pair_count->select()
            ->where("username = ?",$username)
            ->where("start_date = ?",$startdate)
            ->where("end_date = ?",$end_date)
            );


            if(!empty($PairsResult) && sizeof($PairsResult)>0)
            {
                for($i=0;$i<sizeof($PairsResult);$i++)
                {
                    $returnArray['paircount'] = $PairsResult[$i]['pairs'];
                    $offer_id = $PairsResult[$i]['offer_id'];
                    $CompletionOffersArray[$offer_id]['offer_completion_time'] = $PairsResult[$i]['offer_completion_time'];
                }
                $returnArray['CompletionOffersArray'] = $CompletionOffersArray;
            }

            return $returnArray;
        }
        $SpecialOffers =$special_off_obj->fetchAll($special_off_obj->select()
        ->where("status =?", '1')
        ->order("pairs asc")
        );

        if(!empty($SpecialOffers) && sizeof($SpecialOffers)>0)
        {
            $SpecialOffers=$SpecialOffers->toArray();
            for($i=0;$i<sizeof($SpecialOffers);$i++)
            {
                $id = $SpecialOffers[$i]['id'];
                $rows[$id] = $SpecialOffers[$i];
            }

        }
            
        foreach($Users as $username){
            $CompletionOffersArray = $LeftpairsDetail = $RightpairsDetail = array();

            $UserCreationTimeQuery = $User_info_obj->fetchRow($User_info_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('u' =>'user_info'),array('created_on'))
            ->where("username=?",$username)
            ->limit(1)
            );
            $UserCreated ="";
            if(!empty($UserCreationTimeQuery) && sizeof($UserCreationTimeQuery)>0)
            {
                $UserCreated=$UserCreationTimeQuery->created_on;
            }


            $result1 =$bin_user_ref_object->fetchAll($bin_user_ref_object->select()
            //->where("parent_username = '".$username."' and parent_id<>0")
            ->where("parent_username = ? and parent_id<>0",$username)

            ->order("child_position ASC")
            );

            $i = 0;$j=0;
            $childArray = array();
            if(!empty($result1) && sizeof($result1)>0)
            {
                for($i=0;$i<sizeof($result1);$i++)
                {
                    $childArray[$i] =     $result1[$i]['username'];
                    $child_position[$i] = $result1[$i]['child_position'];
                    
                }

            }
            if(!empty($childArray[0]) || !empty($childArray[1])){
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

                $preLeftContractPrice = array_column($LeftpairsDetail, 'preContractPrice');
                $LastLeftBusiness = array_sum($preLeftContractPrice);
                $preRightContractPrice = array_column($RightpairsDetail, 'preContractPrice');
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
                    if($UserCreated >= $startdate){
                            
                        $diff = floor((strtotime($pairTime)-strtotime($UserCreated))/(3600*24));
                        $LeftBusiness = $LeftBusiness + $pair['ContractPrice'];
                        // echo $LeftBusiness;
                        // echo "<br>";
                        foreach($offerDetails as $key => $offerDetail){
                            if(($offerDetail['offer_id'] == 15) && ($LeftBusiness >= $offerDetail['target']) && ($diff <= 15 )){
                                $id = $offerDetail['offer_id'];
                                    
                                $OfferCompletionTime = $pairTime;
                                if(empty($offersArray[$id]['left']) || !isset($offersArray[$id]['left'])){
                                    $offersArray[$id]['left'] = $OfferCompletionTime;
                                }
                            }else if(($offerDetail['offer_id'] != 15) && ($LeftBusiness >= $offerDetail['target'])){
                                $id = $offerDetail['offer_id'];

                                $OfferCompletionTime = $pairTime;
                                if(empty($offersArray[$id]['left']) || !isset($offersArray[$id]['left'])){
                                    $offersArray[$id]['left'] = $OfferCompletionTime;
                                }
                            }
                        }
                    }else{
                        $LeftBusiness = $LeftBusiness + $pair['ContractPrice'];
                        foreach($offerDetails as $key => $offerDetail){
                            // if(($offerDetail['offer_id'] != 15) && ($LeftBusiness >= $offerDetail['target']) && (!in_array($offerDetail['offer_id'],$pre_achieved)) ){
                            if(($offerDetail['offer_id'] != 15) && ($LeftBusiness >= $offerDetail['target']) ){
                                $id = $offerDetail['offer_id'];
                                    
                                $OfferCompletionTime = $pairTime;
                                if(empty($offersArray[$id]['left']) || !isset($offersArray[$id]['left'])){
                                    $offersArray[$id]['left'] = $OfferCompletionTime;
                                }
                         }
                        }
                    }
                    // $LastLeftBusiness += $pair['preContractPrice'];
                }

                $pairtime = $diff = $OfferCompletionTime = '';

                foreach($RightpairsDetail as $pair){
                    // if(date('Y-m',strtotime($pair['created_on'])) == date('Y-m',strtotime($CurrentMonth))){
                    $pairTime = $pair['created_on'];
                    if($UserCreated >= $startdate){
                            
                        $diff = floor((strtotime($pairTime)-strtotime($UserCreated))/(3600*24));
                        $RightBusiness = $RightBusiness + $pair['ContractPrice'];
                        // echo $RightBusiness;
                        // echo "<br>";
                        foreach($offerDetails as $key => $offerDetail){
                            if(($offerDetail['offer_id'] == 15) && ($RightBusiness >= $offerDetail['target']) && ($UserCreated >= $startdate) && ($diff <= 15 )){
                                $id = $offerDetail['offer_id'];
                                // if(!$OfferCompletionTime){
                                $OfferCompletionTime = $pairTime;
                                // }
                                if(empty($offersArray[$id]['right']) || !isset($offersArray[$id]['right'])){
                                    $offersArray[$id]['right'] = $OfferCompletionTime;
                                }
                            }else if(($offerDetail['offer_id'] != 15) && ($RightBusiness >= $offerDetail['target'])){
                                $id = $offerDetail['offer_id'];
                                // if(!$OfferCompletionTime){
                                $OfferCompletionTime = $pairTime;
                                // }
                                if(empty($offersArray[$id]['right']) || !isset($offersArray[$id]['right'])){
                                    $offersArray[$id]['right'] = $OfferCompletionTime;
                                }
                                    
                            }
                        }
                    }else{
                        $RightBusiness = $RightBusiness + $pair['ContractPrice'];
                        // echo "else";
                        // echo "<br>";
                        // echo $LeftBusiness;
                        // echo "<br>";
                        foreach($offerDetails as $key => $offerDetail){
                            // if(($offerDetail['offer_id'] != 15) && ($RightBusiness >= $offerDetail['target'])  && (!in_array($offerDetail['offer_id'],$pre_achieved))){
                            if(($offerDetail['offer_id'] != 15) && ($RightBusiness >= $offerDetail['target']) ){
                                $id = $offerDetail['offer_id'];
                                // if(!$OfferCompletionTime){
                                $OfferCompletionTime = $pairTime;
                                // }
                                if(empty($offersArray[$id]['right']) || !isset($offersArray[$id]['right'])){
                                    $offersArray[$id]['right'] = $OfferCompletionTime;
                                }
                            }
                        }
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

                $minValue = min($LeftBusiness,$RightBusiness);
                $paircount = intval($minValue);
                $offer_id = '';
                foreach($CompletionOffersArray as $key => $CompletionOffer){
                    // var_dump($CompletionOffer);
                    $offer_id = $key;
                    $PairsResult=$pair_count->fetchAll($pair_count->select()
                    ->where("username =?", $username)
                    ->where("offer_id = ?", $offer_id)
                    ->where("start_date =?", $startdate)
                    ->where("end_date =?", $end_date)
                    );

                
                        if((sizeof($PairsResult)<1) && ($paircount>=10)){
                            $ins_arr=array('username'=>$username,'pairs'=>$paircount,'offer_id'=>$offer_id,'offer_completion_time'=>$CompletionOffer['completionTime'],'start_date'=>$startdate,'end_date'=>$end_date);
                            $insertPairResult=$pair_count->insert($ins_arr);
                        }
                    

                }

            }
            else{
                $paircount = 0;
                    
            }
            $count++;
        }
        $UserCount = count($Users);
        if($UserCount < 2){
            $flag = true;
            $achieved = '';
            $Achievable = '';
            foreach($offerDetails as $key => $offers_detail){
                $offer_id = $offers_detail['offer_id'];
                if(($paircount < $offers_detail['target']) && $flag==true){
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
                        if(isset($CompletionOffersArray[24])){
                            $achieved = $rows[$offer_id]['prize'];
                        }
                    }else if($offers_detail['offer_id'] == 15){
                        if(isset($CompletionOffersArray[15])){
                            $achieved = $rows[$offer_id]['prize'];
                        }
                    }else{
                        $achieved = $rows[$offer_id]['prize'];
                    }
                }
            }
            $returnArray['paircount'] = $paircount;
            if(!empty($Permissions['offers'])){
                $returnArray['achieved'] = $achieved;
                $returnArray['Achievable'] = $Achievable;
            }
            $returnArray['pre_achieved'] = $pre_achieved;
            $returnArray['CompletionOffersArray'] = $CompletionOffersArray;
            return $returnArray;
        }
        else
        {
            return $count;
        }
    }
    public function decryptpass($password)
    {
        $comm_obj = new Gbc_Model_Custom_CommonFunc();
        $aesctr=new Gbc_Model_AesCtr();
        $pass=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
        if(empty($pass) || $pass=='')
        {
            $pass=$aesctr->decrypt($password, 'I4CCEATSICDBIKET', 256);
        }
        return $password;
    }

    function getAllChildDetailsforBinary ($username,&$result1)
    {

        $arrayCategories1=array();
        $common_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $arrayCategories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('A'=>"binary_user_refences"),array('A.id','A.username','A.child_position'))
        ->joinLeft(array('B'=>'invoices'),"B.username = A.username",array('round(sum(B.contract_rate),4) as contracts'))
        ->where("A.parent_username = ?",$username)
        ->group("A.username")
        );

        if(empty($arrayCategories1) && sizeof($arrayCategories1)<=0){
         //$result1[] ='';
            return true;
        }
        $arrayCategories1=$arrayCategories1->toArray();


        for($i=0;$i<sizeof($arrayCategories1);$i++)
        {

            //$subarr=array('id'=>$arrayCategories1[$i]['id'],'username'=>$arrayCategories1[$i]['username'],'contracts'=>$arrayCategories1[$i]['contracts']);
            $result1[] = $arrayCategories1[$i];
            //array_push($result1,$subarr);

            $common_obj->getAllChildDetailsforBinary($arrayCategories1[$i]['username'],$result1);
        }

        // var_dump($result1);
        // exit;

        return ($result1) ;

    }

    function getDataClaim($data=null,$PaginateLimit,$filename=null,$path=null){
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $filter_by = $data['filter_by'];
        $filter_by = $data['filter_by'];
        if(!empty($filter_by)){
            if($filter_by == 1){
                $created = "created_on";
            }else if($filter_by == 2){
                $created = "updated_on";
            }
            $SearchQuery = 'filter_by='.$filter_by;
            $created = !empty($created)?$created:"created_on";
            $query = "SELECT * from claimed_offers where 1=1 ";
            $whereQuery = "";
            if(!empty($data['user'])){
                $user = $data['user'];
                $whereQuery .= "and k.username='$user'";
                $SearchQuery .= '&username='.$user;
            }
            if(!empty($data['start_date'])){
                $data['start_date'] = str_replace(",","",$data['start_date']);
                $start_date = date('Y-m-d',strtotime($data['start_date']));
                $whereQuery .= " and $created>='$start_date'";
                $SearchQuery .= '&start_date='.$start_date;
            }
            if(!empty($data['end_date'])){
                $data['end_date'] = str_replace(",","",$data['end_date']);
                $end_date =  date('Y-m-d',strtotime($data['end_date']))." 23:59:59";
                $whereQuery .= " and $created<='$end_date'";
                $SearchQuery .= '&end_date='.$end_date;
            }
            if(!$data['full_report']){
                $UserCountQuery = "SELECT count(id) FROM claimed_offers  where 1=1 ";
                $countquery= "SELECT * FROM claimed_offers  where 1=1 ";
                $countquery=$countquery.$whereQuery;

                $qry=$DB->query($countquery);
                $UserCount = $qry->rowCount();
                //$UserCountQuery .= $whereQuery;

                //    $UserCountResult = mysql_query($UserCountQuery);
                //    $UserCount = mysql_result ($UserCountResult,0);
                // echo $UserCountQuery;
                // echo $UserCount;
                $pages = ceil($UserCount/$PaginateLimit);
                $startLimit = !empty($data['page']) && $data['page'] > 1?($data['page']-1)*$PaginateLimit:0;

                if(!empty($PaginateLimit) && !empty($startLimit)){
                    $Limit = "limit ".$startLimit.", ".$PaginateLimit;
                }else if(!empty($PaginateLimit)){
                    $Limit = "limit ".$PaginateLimit;
                }else{
                    $Limit ="";
                }

                $whereQuery .= " $Limit";
                $query .= $whereQuery;
                // echo $query;
                // exit;

                // echo "pages ".$pages;
                // echo "<br>";
                $PaginationCount = 5;
                $PaginationCountFactor = intval($PaginationCount/2);
                $currentPage = !empty($data['page'])?$data['page']:1;
                $Pagination = '<ul class="pagination">';

             $StartPage = !empty($data['page'])?$data['page']:1;
             if($pages <= $PaginationCount){
                 $StartPage = 1;
                 $limitPage = $pages;
             }else if($pages > $PaginationCount){
                 $limitPage = $StartPage + (($PaginationCount+1) - $PaginationCountFactor);
                 if(!empty($data['page']) && $data['page'] < $pages-(($PaginationCount+1)-$PaginationCountFactor)){
                     // if(!empty($data['page']) ){
                     $StartPage = ($data['page']-$PaginationCountFactor > 0)?$data['page']-$PaginationCountFactor:1;
                 }else if(!empty($data['page']) && $data['page'] >= $pages-($PaginationCount+1)){
                     $StartPage = ($pages - $PaginationCount > 0)?$pages - $PaginationCount:1;
                     $limitPage = ($data['page']>1)?$pages -1:1;
                 }
             }
                 
             // echo "StartPage ".$StartPage;
             // echo "<br>";
             // echo "limitPage ".$limitPage;
             // echo "<br>";
             if(!empty($data['page']) && $data['page'] > 1){
                 $SearchQuery1 = $SearchQuery.'&page=1';
                 $Pagination .= '<li class="waves-effect"><a href="claimed_requests.php?search='.($SearchQuery1).'">First</a></li>';
                 $SearchQuery2 = $SearchQuery.'&page='.($data['page']-1);
                 $Pagination .= '<li class="waves-effect"><a href="claimed_requests.php?search='.($SearchQuery2).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
             }
                 
             for($j=$StartPage;$j<=$limitPage;$j++){
                 $SearchQuery3[$j] = $SearchQuery.'&page='.$j;
                 // var_dump($SearchQuery3);
                 // echo "yes";
                 // echo $currentPage;
                 if( $j == $currentPage){
                     $Pagination .= '<li class="active"><a href="claimed_requests.php?search='.($SearchQuery3[$j]).'" style = "color: #000;">'.$j.'</a></li>';
                 }else{
                     $Pagination .= '<li class="active"><a href="claimed_requests.php?search='.($SearchQuery3[$j]).'">'.$j.'</a></li>';
                 }
             }

             if($currentPage < $pages-1){
                 $SearchQuery1 = $SearchQuery.'&page='.($currentPage+1);

                 $Pagination .= '<li class="waves-effect"><a href="claimed_requests.php?search='.($SearchQuery1).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
                 $SearchQuery2 = $SearchQuery.'&page='.($pages-1);
                 $Pagination .= '<li class="waves-effect"><a href="claimed_requests.php?search='.($SearchQuery2).'">Last</a></li>';
             }
             $Pagination .= '</ul>';
             if(!empty($data['page']) && $data['page'] >1){
                 $startRow = ($data['page']-1)*$PaginateLimit +1;
             }
            }

            $AllUserData = $DB->query($query);
            while ($row = $AllUserData->fetch()) {
                $AllDatarows[]=$row;
            }

         $AllData['data'] = $AllDatarows;
         $AllData['Pagination'] = $Pagination;
         return $AllData;
        }

    }

    function getAllRefundRequests($data=null,$PaginateLimit,$filename=null,$path=null){
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $ref_req_obj = new Gbc_Model_DbTable_Refrequest();
        if(!empty($data)){
            $retArray = $common_obj-> getDataRefund($data,$PaginateLimit,$filename,$path);
            // exit;
            return $retArray;
        }else{

            $AllUserData=$ref_req_obj->fetchAll($ref_req_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('a'=>"refund_requests"))
            ->limit(100)
            );
            $AllUserData = mysql_query("SELECT * from refund_requests limit 100");
            for($i=0;$i<sizeof($AllUserData);$i++)
            {
                $retArray[$i]['username']=$AllUserData[$i]['username'];
                $retArray[$i]['full_name']=$AllUserData[$i]['full_name'];
                $retArray[$i]['email']=$AllUserData[$i]['email'];
                $retArray[$i]['phone']=$AllUserData[$i]['phone'];
                $retArray[$i]['invoice_id']=$AllUserData[$i]['invoice_id'];
                $retArray[$i]['kit_number']=$AllUserData[$i]['kit_number'];
                $retArray[$i]['txid']=$AllUserData[$i]['txid'];
                $retArray[$i]['reason_to_refund']=$AllUserData[$i]['reason_to_refund'];
                $retArray[$i]['wallet_addr']=$AllUserData[$i]['wallet_addr'];
                $retArray[$i]['ip_address']=$AllUserData[$i]['ip_address'];
                $retArray[$i]['status']=$AllUserData[$i]['status'];
                $retArray[$i]['updated_by']=$AllUserData[$i]['updated_by'];
                $retArray[$i]['created_on']=$AllUserData[$i]['created_on'];
            }
         return $retArray;
        }
        // var_dump($retArray);
    }

    function getDataRefund($data=null,$PaginateLimit,$filename=null,$path=null){
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        // var_dump($data);
        $filter_by = $data['filter_by'];
        if(!empty($filter_by)){
            if($filter_by == 1){
                $created = "created_on";
            }else if($filter_by == 2){
                $created = "updated_on";
            }
            $SearchQuery = 'filter_by='.$filter_by;
        }

        $created = !empty($created)?$created:"created_on";

        $query = "SELECT * from refund_requests where 1=1 ";
        $whereQuery = "";
        if(!empty($data['user'])){
            $user = $data['user'];
            $whereQuery .= "and k.username='$user'";
            $SearchQuery .= '&username='.$user;
        }

        if(!empty($data['created'])){
            $data['created'] = str_replace(",","",$data['created']);
            $created_on = date('Y-m-d',strtotime($data['created']));
         $whereQuery .= " and $created>='$created_on'";
         $SearchQuery .= '&created='.$created_on;
        }

        // echo $query;
        // var_dump($data);
        if(!$data['full_report']){
            $UserCountQuery = "SELECT count(id) FROM refund_requests  where 1=1 ";
            $UserCountQuery .= $whereQuery;

            $UserCntQuery = "SELECT * FROM refund_requests  where 1=1 ";
            $UserCntQuery=$UserCntQuery.$whereQuery;

            $qry=$DB->query($UserCntQuery);
            $UserCount = $qry->rowCount();

            // echo $UserCountQuery;
            // echo $UserCount;
            $pages = ceil($UserCount/$PaginateLimit);
            $startLimit = !empty($data['page']) && $data['page'] > 1?($data['page']-1)*$PaginateLimit:0;

            if(!empty($PaginateLimit) && !empty($startLimit)){
                $Limit = "limit ".$startLimit.", ".$PaginateLimit;
            }else if(!empty($PaginateLimit)){
                $Limit = "limit ".$PaginateLimit;
            }else{
                $Limit ="";
            }

            $whereQuery .= "order by  ".$created." desc ".$Limit;
            $query .= $whereQuery;
            // echo $query;
            // exit;

            // echo "pages ".$pages;
            // echo "<br>";
            $PaginationCount = 5;
            $PaginationCountFactor = intval($PaginationCount/2);
            $currentPage = !empty($data['page'])?$data['page']:1;
            $Pagination = '<ul class="pagination">';

            $StartPage = !empty($data['page'])?$data['page']:1;
            if($pages <= $PaginationCount){
                $StartPage = 1;
                $limitPage = $pages;
            }else if($pages > $PaginationCount){
                $limitPage = $StartPage + (($PaginationCount+1) - $PaginationCountFactor);
                if(!empty($data['page']) && $data['page'] < $pages-(($PaginationCount+1)-$PaginationCountFactor)){
                 // if(!empty($data['page']) ){
                    $StartPage = ($data['page']-$PaginationCountFactor > 0)?$data['page']-$PaginationCountFactor:1;
                }else if(!empty($data['page']) && $data['page'] >= $pages-($PaginationCount+1)){
                    $StartPage = ($pages - $PaginationCount > 0)?$pages - $PaginationCount:1;
                    $limitPage = ($data['page']>1)?$pages -1:1;
                }
            }

            // echo "StartPage ".$StartPage;
            // echo "<br>";
            // echo "limitPage ".$limitPage;
            // echo "<br>";
            if(!empty($data['page']) && $data['page'] > 1){
                $SearchQuery1 = $SearchQuery.'&page=1';
                $Pagination .= '<li class="waves-effect"><a href="refund_requests.php?search='.($SearchQuery1).'">First</a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($data['page']-1);
                $Pagination .= '<li class="waves-effect"><a href="refund_requests.php?search='.($SearchQuery2).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
            }

            for($j=$StartPage;$j<=$limitPage;$j++){
                $SearchQuery3[$j] = $SearchQuery.'&page='.$j;
                // var_dump($SearchQuery3);
                // echo "yes";
                // echo $currentPage;
                if( $j == $currentPage){
                    $Pagination .= '<li class="active"><a href="refund_requests.php?search='.($SearchQuery3[$j]).'" style = "color: #000;">'.$j.'</a></li>';
                }else{
                    $Pagination .= '<li class="active"><a href="refund_requests.php?search='.($SearchQuery3[$j]).'">'.$j.'</a></li>';
                }
            }

            if($currentPage < $pages-1){
                $SearchQuery1 = $SearchQuery.'&page='.($currentPage+1);

                $Pagination .= '<li class="waves-effect"><a href="refund_requests.php?search='.($SearchQuery1).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($pages-1);
                $Pagination .= '<li class="waves-effect"><a href="refund_requests.php?search='.($SearchQuery2).'">Last</a></li>';
            }
            $Pagination .= '</ul>';
            if(!empty($data['page']) && $data['page'] >1){
                $startRow = ($data['page']-1)*$PaginateLimit +1;
            }
        }

        $AllUserData = $DB->query($query);
        while ($row = $AllUserData->fetch()) {
            $AllDatarows[]=$row;
        }

        $AllData['data'] = $AllDatarows;
        $AllData['Pagination'] = $Pagination;

        return $AllData;
    }
    function updateCronStatus($cronName,$start = null,$end = null){
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $cron_status= new Gbc_Model_DbTable_Cronstatus();
        $Existing=$cron_status->fetchAll($cron_status->select()
        ->where("cron = '$cronName'")
        );
        $upd_arr=array();
        if(!empty($start)){
            $upd_arr['start'] = $start;
        }else if(!empty($end)){
            $upd_arr['end']= $end ;
            $upd_arr['updated_on']= new Zend_Db_Expr('NOW()') ;
        }else{
            $upd_arr['updated_on']= new Zend_Db_Expr('NOW()') ;
        }

        if(!empty($Existing) && sizeof($Existing)>0)
        {
            $upd_arr['status']= $cronName.' cron updated successfully.' ;
            //$upd_qry = $cron_status->update($upd_arr,"cron = ?",$cronName);
            $upd_qry = $cron_status->update($upd_arr, $DB->quoteInto("cron = ?",$cronName));

            echo "update";
        }
        else
        {
            $insert_arr=array('cron'=>$cronName,'status'=>$cronName.' cron updated successfully.','start'=>new Zend_Db_Expr('NOW()'));
            $insert_qry= $cron_status->insert($insert_arr);
            echo "else insert";
        }
    }
    function WriteCSVCron($user_query,$file,$encode=null){
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $flag = false;
        $fp = fopen($file, 'w');
        while ($row = $user_query->fetch()) {
            if($encode == 'encode'){
                $row['username'] = '"'.$row['username'].'"';
            }

            if (!$flag) {
                // display field/column names as first row

             fputcsv($fp, array_keys($row),',');
             // echo  array_keys($row) . "\r\n";
             $flag = true;
            }

            fputcsv($fp, array_values($row),',');
        }
        fclose($fp);
        // return $fp;
    }
    function cleanQueryParameter($string) {

        if(is_array($string)){

            foreach($string as $key => $value){
                $value =     trim($value);
                // prevents duplicate backslashes
                //if(get_magic_quotes_gpc()) {
                //    $value = stripslashes($value);
                //}

                //escape the string with backward compatibility
                    
                //$value = mysql_escape_string($value);
                    
                    
                $string[$key] = $value;
            }
        }else{

            //remove extraneous spacess
            $string = trim($string);

            // prevents duplicate backslashes
            //if(get_magic_quotes_gpc()) {
            //    $string = stripslashes($string);
            //}


            //$string = mysql_escape_string($string);


        }

        return $string;
    }
    function getkitsdata($limit,$startLimit,$searchQuery=null)
    {
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $kits_obj=new Gbc_Model_DbTable_Kits();

        $kit_status = array('active','used');
        $query=$kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('k'=>"kits"),array('k.username','k.kit_number','k.created_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.comment','k.kit_type'))
        ->joinLeft(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment'));


        //$query="SELECT k.*, k1.invoice_status FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id";
        if(!empty($searchQuery)){
            //$query.=" where k.username='$searchQuery'";
              $query->where('k.username = ?',$searchQuery);
			$query->ORwhere("k.kit_number = ?",$searchQuery);
			$query->ORwhere("k.invoice_id = ?",$searchQuery);
			$query->ORwhere("k.comment = ?",$searchQuery);
			$query->ORwhere("k1.comment = ?",$searchQuery);
        }
		
		  $query->order("created_on DESC");
		
        //$query.=" $Limit";
        if(!empty($limit) && !empty($startLimit))
        {
            $query->limit($limit,$startLimit);
        }
        else if(!empty($limit)){
            $query->limit($limit);
        }

        $res = $kits_obj->fetchAll($query);
        if(isset($res) && sizeof($res)>0)
        {
            $result = $res->toArray();
        }
        /*$res=$DB->query($query);
         $result = $res->fetchAll();*/

        return     $result;

    }
    function getBinaryUsersforAdmin($Limit=null,$startLimit = null)
    {
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $user_obj = new Gbc_Model_DbTable_Userinfo();



        $query=$user_obj->select()
        ->where("binaryUser =?",'1')
        ->where("username <>?",'admin');


        if(!empty($Limit) && !empty($startLimit))
        {
            $query->limit($Limit,$startLimit);
        }
        else if(!empty($Limit)){
            $query->limit($Limit);
        }

        $result = $user_obj->fetchAll($query);
        //$qury = "SELECT * FROM user_info where binaryUser=1 and username<>'admin' $Limit";

        if(!empty($query) && sizeof($query)>0)
        {
            $returnArr = $result->toArray();
        }
        else
        {
            $returnArr = array();
        }

        return $returnArr;
    }


	
function sendMailviaAPI($to='',$from='',$replyTo='',$subject='',$message='',$htmlMessage = '',$cc = '',$bcc =''){
    global $conn;
    $email = $to;
   // $from = "support@gainbitcoin.com";
    $from = "no-reply@gainbitcoin.com";
//    $to = "virender@nexgenfmpl.com";
//$to = "sini@nexgenfmpl.com";
//$cc = array("virender@nexgenfmpl.com"=>"","vikas@nexgenfmpl.com"=>"");

//$TestSubject = "Test: ".$subject;


    //var_dump($email); 
        //$checkedBlockedEmail = mysql_query("select * from blocked_emails where email like '$email' ");
    //    $result = mysql_fetch_assoc($checkedBlockedEmail);
        //if(!$result){

           /* if(file_exists('mailin-api/Mailin.php')){
            require_once 'mailin-api/Mailin.php';
            }else{
            require_once 'mailin-api/Mailin.php';
            }*/
    
    $mailinbasepath=$_SERVER['DOCUMENT_ROOT'].BASEPATH;
    if(file_exists($mailinbasepath.'/mailin-api/Mailin.php')){
    	require_once  $mailinbasepath.'/mailin-api/Mailin.php';
    }else{
    	require_once  $mailinbasepath.'/mailin-api/Mailin.php';
    }


         //include '../mailin-api/Mailin.php';
            $mailin = new Mailin("https://api.sendinblue.com/v2.0","E56mNqc2Wa3ZkgDP");

            $data = array( "to" => array($to=>""),
                "from" => array($from,"Gainbitcoin"),
                "cc" => $cc,
                "bcc" => $bcc,
              //  "replyto" => array($replyTo,"Gainbitcoin"),
                "subject" => $subject,
                "html" => $htmlMessage
            );

        $res = $mailin->send_email($data);
	
        //$res = json_decode($res);
        //  var_dump( $res );
        if($res['code'] == "success"){
            $returnArr["errCode"][-1]=-1;
            $returnArr["errMsg"]="Email Sent";
        } else {
            $returnArr["errCode"][8]=8;
            $returnArr["errMsg"]="Email Send Error";
        }
    //}
    
    return $returnArr;
    
}

    function sendMailviaAPI_old($to='',$from='',$replyTo='',$subject='',$message='',$htmlMessage = '',$cc = '',$bcc =''){

    if(file_exists('mailin-api/Mailin.php')){
    require_once 'mailin-api/Mailin.php';
    }else{
    require_once 'mailin-api/Mailin.php';
    }

    $mailin = new Mailin('nexgenfmpl123@gmail.com', '7J2y1GFhKYVdbfpX');
    $TestSubject = "Test: ".$subject;

//    $testMail = "thegainbitcoinhelp@gmail.com";
    $testMail = "sini@nexgenfmpl.com";
    $mailin->addTo($testMail, $to);
//    $cc="amol.korde@prontoinfotech.com";
    if($cc){
    $mailin->addCc($cc,'');
    }
    if($bcc){
    $mailin->addBcc($bcc,'');
    }
    $mailin->setFrom($from, 'Gainbitcoin')->
    setReplyTo($replyTo,'Gainbitcoin')->
    setSubject($TestSubject)->
    setText($message)->
    setHtml($htmlMessage);
    $res = $mailin->send();
    $res = json_decode($res);
    //var_dump( $res );
    if($res->result == "true"){
    $returnArr["errCode"][-1]=-1;
    $returnArr["errMsg"]="Email Sent";
    return "email sent";
    } else {
    //echo "error sending email";exit;
    $returnArr["errCode"][8]=8;
    $returnArr["errMsg"]="Email Send Error";
    }
    //return "email sent";
    //return $returnArr;
    //echo "fdsfdsf";exit;
    }

    /*function sendMailviaAPI($to='',$from='',$replyTo='',$subject='',$message='',$htmlMessage = '',$cc = '',$bcc =''){

        /*    if(file_exists('mailin-api/Mailin.php')){
         require_once 'mailin-api/Mailin.php';
            }else{
            require_once 'mailin-api/Mailin.php';
            }

            $mailin = new Mailin('nexgenfmpl123@gmail.com', '7J2y1GFhKYVdbfpX');
            $TestSubject = "Test: ".$subject;

            $testMail = "thegainbitcoinhelp@gmail.com";
            //$testMail = "priyankamaurya026@gmail.com";
            $mailin->addTo($testMail, $to);
            $cc="amol.korde@prontoinfotech.com";
            if($cc){
            $mailin->addCc($cc,'');
            }
            if($bcc){
            $mailin->addBcc($bcc,'');
            }
            $mailin->setFrom($from, 'Gainbitcoin')->
            setReplyTo($replyTo,'Gainbitcoin')->
            setSubject($TestSubject)->
            setText($message)->
            setHtml($htmlMessage);
            $res = $mailin->send();
            $res = json_decode($res);
            //var_dump( $res );
            if($res->result == "true"){
            $returnArr["errCode"][-1]=-1;
            $returnArr["errMsg"]="Email Sent";
            return "email sent";
            } else {
            //echo "error sending email";exit;
            $returnArr["errCode"][8]=8;
            $returnArr["errMsg"]="Email Send Error";
            }*/
        //return "email sent";
        //return $returnArr;
        //echo "fdsfdsf";exit;
    /*    $to = "nishant.k@prontoinfotech.com";
        $cc='priyankamaurya026@gmail.com';
        $fromName='';
        $htmlMessage=urlencode($htmlMessage);
        $string=('https://api.elasticemail.com/v2/email/send?apikey=10b9bb3c-47bd-454c-bc1f-0907aa4995c7&msgTo='.$to.'&msgCC='.$cc.'&subject='.$subject.'&from='.$from.'&bodyHtml='.$htmlMessage.'&replyTo=&fromName='.$fromName);
        $string=str_replace(' ', '%20', $string);
        $lines = file( $string);
        if(!empty($lines))
        {
            $data1 = ($lines[0]);
            $array =  (array) json_decode($data1);
            //echo $array['success'];
        }
        if(!empty($array['success']))
        {
            return "email sent";
        }
    }*/


    function getDataForClaim($data=null,$PaginateLimit,$filename=null,$path=null){
        $claim_obj = new Gbc_Model_DbTable_Claimedoffers();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        // var_dump($data);
        $filter_by = $data['filter_by'];
        if(!empty($filter_by)){
            if($filter_by == 1){
                $created = "created_on";
            }else if($filter_by == 2){
                $created = "updated_on";
            }
            $SearchQuery = '&filter_by='.$filter_by;
        }

        $created = !empty($created)?$created:"created_on";

        //$query = "SELECT * from claimed_offers where 1=1 ";
        $query = $claim_obj->select();

        $whereQuery = "";
        if(!empty($data['user'])){
            $user = $data['user'];
            //$whereQuery .= "and username='$user'";
            $query->where('username = ?',$user);
            $SearchQuery .= '&username='.$user;
        }

        if(!empty($data['start_date'])){
            $data['start_date'] = str_replace(",","",$data['start_date']);
            $start_date = date('Y-m-d',strtotime($data['start_date']));
            // $whereQuery .= " and $created>='$start_date'";
         $query->where("$created>='$start_date'");
         $SearchQuery .= '&start_date='.$start_date;
        }
        if(!empty($data['end_date'])){
            $data['end_date'] = str_replace(",","",$data['end_date']);
            $end_date =  date('Y-m-d',strtotime($data['end_date']))." 23:59:59";
            //$whereQuery .= " and $created<='$end_date'";
            $query->where("$created<='$end_date'");
            $SearchQuery .= '&end_date='.$end_date;
        }
        // echo $query;
        // var_dump($data);
        if(empty($data['full_report'])){



            /*$CountQuery = "select count(id) from claimed_offers  where 1=1 ";
             $CountQuery .= $whereQuery;*/

            $UserCountResult = $claim_obj->fetchAll($query);
            $UserCount = sizeof($UserCountResult);
            //echo $CountQuery;exit;

            // echo $CountQuery;

            /*$UserCountRes=$DB->query($CountQuery);
             $UserCountResult = $UserCountRes->fetch();
             $UserCount=$UserCountResult['count'];*/

            // echo $UserCountQuery;
            // echo $UserCount;
            $pages = ceil($UserCount/$PaginateLimit);
            $startLimit = !empty($data['page']) && $data['page'] > 1?($data['page']-1)*$PaginateLimit:0;



            if(!empty($PaginateLimit) && !empty($startLimit))
            {
                $query->limit($PaginateLimit,$startLimit);
            }
            else if(!empty($PaginateLimit)){
                $query->limit($PaginateLimit);
            }
            //$whereQuery .= " $Limit";

            //$query .= $whereQuery;
            // echo $query;
            // exit;

            // echo "pages ".$pages;
            // echo "<br>";
            $PaginationCount = 5;
            $PaginationCountFactor = intval($PaginationCount/2);
            $currentPage = !empty($data['page'])?$data['page']:1;
            $Pagination = '<ul class="pagination">';

            $StartPage = !empty($data['page'])?$data['page']:1;
            if($pages <= $PaginationCount){
                $StartPage = 1;
                $limitPage = $pages;
            }else if($pages > $PaginationCount){
                $limitPage = $StartPage + (($PaginationCount+1) - $PaginationCountFactor);
                if(!empty($data['page']) && $data['page'] < $pages-(($PaginationCount+1)-$PaginationCountFactor)){
                 // if(!empty($data['page']) ){
                    $StartPage = ($data['page']-$PaginationCountFactor > 0)?$data['page']-$PaginationCountFactor:1;
                }else if(!empty($data['page']) && $data['page'] >= $pages-($PaginationCount+1)){
                    $StartPage = ($pages - $PaginationCount > 0)?$pages - $PaginationCount:1;
                    $limitPage = ($data['page']>1)?$pages -1:1;
                }
            }

            // echo "StartPage ".$StartPage;
            // echo "<br>";
            // echo "limitPage ".$limitPage;
            // echo "<br>";
            if(!empty($data['page']) && $data['page'] > 1){
                $SearchQuery1 = $SearchQuery.'&page=1';
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery1).'"></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($data['page']-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery2).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
            }

            for($j=$StartPage;$j<=$limitPage;$j++){
                $SearchQuery3[$j] = $SearchQuery.'&page='.$j;
                // var_dump($SearchQuery3);
                // echo "yes";
                // echo $currentPage;
                if( $j == $currentPage){
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery3[$j]).'" style = "color: #000;">'.$j.'</a></li>';
                }else{
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery3[$j]).'">'.$j.'</a></li>';
                }
            }

            if($currentPage < $pages-1){
                $SearchQuery1 = $SearchQuery.'&page='.($currentPage+1);

                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery1).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($pages-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Claimlist?search='.($SearchQuery2).'">Last</a></li>';
            }
            $Pagination .= '</ul>';
            if(!empty($data['page']) && $data['page'] >1){
                $startRow = ($data['page']-1)*$PaginateLimit +1;
            }
        }

        /*$UserCountRes=$DB->query($query);
         $AllUserData = $UserCountRes->fetchAll();*/
        $UserCountRes = $claim_obj->fetchAll($query);
        if(!empty($UserCountRes) && sizeof($UserCountRes)>0)
        {
            $AllUserData = $UserCountRes->toArray();
        }
        for($i=0;$i<sizeof($AllUserData);$i++)
        {
            $AllDatarows[]=$AllUserData[$i];
        }

        $AllData['data'] = $AllDatarows;
        $AllData['Pagination'] = $Pagination;

        // $AllData = sendData($AllUserData,$tableFields,$startRow,$Pagination,$filename,$path);
        // var_dump($AllData);
        return $AllData;
    }

    function getAllClaimedRequests($data=null,$PaginateLimit,$filename=null,$path=null){
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $claim_obj = new Gbc_Model_DbTable_Claimedoffers();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        if(!empty($data)){
            $retArray = $common_obj->getDataForClaim($data,$PaginateLimit,$filename,$path);
            // exit;
            return $retArray;
        }else{

            $AllUserData = $claim_obj->fetchAll($claim_obj->select()
            ->limit(100));

            //$query = ("SELECT * from claimed_offers limit 100");

            //$UserCountRes=$DB->query($query);
            //$AllUserData = $UserCountRes->fetchAll();
            if(!empty($AllUserData) && sizeof($AllUserData)>0)
            {
                $AllUserData = $AllUserData->toArray();
            }

            for($i=0;$i<sizeof($AllUserData);$i++)
            {
                $retArray[]=$AllUserData[$i];
            }

            return $retArray;
        }
        // var_dump($retArray);
    }
    function time_elapsed_string($datetime, $full = false) {

        $now = new DateTime;
        $then = new DateTime( $datetime );
        $diff = (array) $now->diff( $then );

        $diff['w']  = floor( $diff['d'] / 7 );
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
        );

        foreach( $string as $k => & $v )
        {
            if ( $diff[$k] )
            {
                $v = $diff[$k] . ' ' . $v .( $diff[$k] > 1 ? 's' : '' );
            }
            else
            {
                unset( $string[$k] );
            }
        }

        if ( ! $full ) $string = array_slice( $string, 0, 1 );
        return $string ? implode( ', ', $string ) . ' ago' : 'just now';
    }
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    function getKitAuditData($data=null,$PaginateLimit,$filename=null,$path=null){
        // var_dump($data);
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $filter_by = $data['filter_by'];
        if(!empty($filter_by)){
            if($filter_by == 1){
                $created = "created_on";
            }else if($filter_by == 2){
                $created = "updated_on";
            }else if($filter_by == 3){
                $created = "kit_used_date";
            }
            $SearchQuery = '&filter_by='.$filter_by;
        }

        $created = !empty($created)?$created:"created_on";
        $kit_status = array('active','used');
        $query=$kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('k'=>"kits"),array('k.username','k.kit_number','k.created_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.comment','k.kit_type','(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode','(select status from kit_confirmations where kit_no = k.kit_number order by id desc limit 1) as audit_status'))
        ->joinInner(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment'))        
        ->where("k.status IN (?)", $kit_status);


        $query_count=$kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('k'=>"kits"),array('count(k.id) as total_count'))
        ->joinInner(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment'))        
        ->where("k.status IN (?)", $kit_status);

        //$query = "SELECT k.*, k1.invoice_status,k1.comment,(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id where k.status in ('active','used') ";
        $whereQuery = "";
        if(!empty($data['user'])){
            $user = $data['user'];
            //$whereQuery .= "and k.username='$user'";
            $query->where('k.username = ?',$user);
            $query_count->where('k.username = ?',$user);
            $SearchQuery .= '&user='.$user;
        }

        if(!empty($data['start_date'])){
            $data['start_date'] = str_replace(",","",$data['start_date']);
            $start_date = date('Y-m-d',strtotime($data['start_date']));
            // $whereQuery .= " and k.$created>='$start_date'";
         $query->where("k.$created>='$start_date'");
         $query_count->where("k.$created>='$start_date'");
         $SearchQuery .= '&start_date='.$start_date;
        }
        if(!empty($data['end_date'])){
            $data['end_date'] = str_replace(",","",$data['end_date']);
            $end_date =  date('Y-m-d',strtotime($data['end_date']))." 23:59:59";
            //$whereQuery .= " and k.$created<='$end_date'";
            $query->where("k.$created<='$end_date'");
            $query_count->where("k.$created<='$end_date'");
            $SearchQuery .= '&end_date='.$end_date;
        }
        // echo $query;
        // var_dump($data);
        if(empty($data['full_report'])){
            //$UserCountQuery = "SELECT count(id) as count FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id where k.status in ('active','used') ";
            //$UserCountQuery .= $whereQuery;

            //$UserCountResult = mysql_query($UserCountQuery);
            //$UserCount = mysql_result ($UserCountResult,0);

            $UserCountResult = $kits_obj->fetchRow($query_count);
            $UserCount = ($UserCountResult->total_count);

            /*$UserCountRes=$DB->query($UserCountQuery);
             $UserCountResult = $UserCountRes->fetch();
             $UserCount=$UserCountResult['count'];*/
            // echo $UserCountQuery;
            // echo $UserCount;
            $pages = ceil($UserCount/$PaginateLimit);
            $startLimit = !empty($data['page']) && $data['page'] > 1?($data['page']-1)*$PaginateLimit:0;


            if(!empty($PaginateLimit) && !empty($startLimit))
            {
                $query->limit($PaginateLimit,$startLimit);
            }
            else if(!empty($PaginateLimit)){
                $query->limit($PaginateLimit);
            }
            //$whereQuery .= " $Limit";
            //$query .= $whereQuery;
            // echo $query;
            // exit;

            // echo "pages ".$pages;
            // echo "<br>";
            $PaginationCount = 5;
            $PaginationCountFactor = intval($PaginationCount/2);
            $currentPage = !empty($data['page'])?$data['page']:1;
            $Pagination = '<ul class="pagination">';

            //$StartPage = !empty($data['page'])?$data['page']:1;
            if(!empty($StartPage) && $StartPage!='')
            {
                $StartPage = $data['page'];
            }
            else
            {
                $StartPage = 1;
            }
            if($pages <= $PaginationCount){
                $StartPage = 1;
                $limitPage = $pages;
            }else if($pages > $PaginationCount){
                $limitPage = $StartPage + (($PaginationCount+1) - $PaginationCountFactor);
                if(!empty($data['page']) && $data['page'] < $pages-(($PaginationCount+1)-$PaginationCountFactor)){
                 // if(!empty($data['page']) ){
                    $StartPage = ($data['page']-$PaginationCountFactor > 0)?$data['page']-$PaginationCountFactor:1;
                }else if(!empty($data['page']) && $data['page'] >= $pages-($PaginationCount+1)){
                    $StartPage = ($pages - $PaginationCount > 0)?$pages - $PaginationCount:1;
                    $limitPage = ($data['page']>1)?$pages -1:1;
                }
            }

            // echo "StartPage ".$StartPage;
            // echo "<br>";
            // echo "limitPage ".$limitPage;
            // echo "<br>";
            if(!empty($data['page']) && $data['page'] > 1){
                $SearchQuery1 = $SearchQuery.'&page=1';
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery1).'"></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($data['page']-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery2).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
            }

            for($j=$StartPage;$j<=$limitPage;$j++){
                $SearchQuery3[$j] = $SearchQuery.'&page='.$j;
                // var_dump($SearchQuery3);
                // echo "yes";
                // echo $currentPage;
                if( $j == $currentPage){
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery3[$j]).'" style = "color: #000;">'.$j.'</a></li>';
                }else{
                    $Pagination .= '<li class="active"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery3[$j]).'">'.$j.'</a></li>';
                }
            }

            if($currentPage < $pages-1){
                $SearchQuery1 = $SearchQuery.'&page='.($currentPage+1);

                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery1).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
                $SearchQuery2 = $SearchQuery.'&page='.($pages-1);
                $Pagination .= '<li class="waves-effect"><a href="'.BASEPATH.'/Kitaudit?search='.($SearchQuery2).'"></a></li>';
            }
            $Pagination .= '</ul>';
            if(!empty($data['page']) && $data['page'] >1){
                $startRow = ($data['page']-1)*$PaginateLimit +1;
            }
        }

        /*$AllUserData=$DB->query($query);
         $UserResult = $AllUserData->fetchAll();*/
        $AllUserData = $kits_obj->fetchAll($query);
        if(!empty($AllUserData) && sizeof($AllUserData)>0)
        {
            $UserResult = $AllUserData->toArray();
        }

        for($i=0;$i<sizeof($UserResult);$i++){
            $AllDatarows[]=$UserResult[$i];
        }
        $AllData['data'] = $AllDatarows;
        $AllData['Pagination'] = $Pagination;

        // $AllData = sendData($AllUserData,$tableFields,$startRow,$Pagination,$filename,$path);
        // var_dump($AllData);
        return $AllData;
    }

    function getAllUserReportsForKitAudit($data=null,$PaginateLimit,$filename=null,$path=null){
        $comm_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        if(!empty($data)){
            $retArray = $comm_obj->getKitAuditData($data,$PaginateLimit,$filename,$path);
            // exit;
            return $retArray;
        }else{
            $kit_status = array('active','used');
            $AllUserData=$kits_obj->fetchAll($kits_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('k'=>"kits"),array('k.username','k.kit_number','k.created_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.comment','k.kit_type','(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode','(select status from kit_confirmations where kit_no = k.kit_number order by id desc limit 1) as audit_status'))
            ->joinInner(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment'))            
            ->where("k.status IN (?)", $kit_status)
            ->limit(100)
            );



            //    $AllUserData = ("SELECT k.*, k1.invoice_status,k1.comment,(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id where k.status in ('active','used') limit 100");

            //$UserRes=$DB->query($AllUserData);
            if(!empty($AllUserData) && sizeof($AllUserData)>0)
            {
                $UserResult = $AllUserData->toArray();
            }
            //$UserResult = $UserRes->fetchAll();
            for($i=0;$i<sizeof($UserResult);$i++){
             $retArray[]=$UserResult[$i];
            }
         // var_dump($retArray);
         // exit;
         return $retArray;
        }
        // var_dump($retArray);
    }

    function getKitsDataForAudit($limit,$startLimit)
    {
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $returnArr = array();

        $kit_status = array('active','used');
        $query=$kits_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('k'=>"kits"),array('k.username','k.kit_number','k.created_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.comment','k.kit_type','(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode'))
        ->joinLeft(array('k1'=>'kit_invoices'),'(k.invoice_id=k1.invoice_id)',array('k1.invoice_status','k1.comment','(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode'))
        ->where("k.status IN (?)", $kit_status);




        if(!empty($limit) && !empty($startLimit))
        {
            $query->limit($limit,$startLimit);
        }
        else if(!empty($limit)){
            $query->limit($limit);
        }
        $UserRes = $kits_obj->fetchAll($query);
        //$query="SELECT k.*, k1.invoice_status,k1.comment,(select payment_mode from kits_payments where invoice_id = k.invoice_id) as payment_mode FROM kits k INNER JOIN kit_invoices k1 ON k.invoice_id=k1.invoice_id where k.status in ('active','used') $Limit";
        //$result = runQuery($query, $conn);
        if(isset($UserRes) && sizeof($UserRes)>0)
        {
            $AllUserData = $UserRes->toArray();
        }
        else
        {
            $AllUserData = array();
        }
        //$UserRes=$DB->query($query);
        //$AllUserData = $UserRes->fetchAll();
        // echo $query;

        // $set = null;
        // $returnArr = null;
            
        return $AllUserData;
    }


   /* function getOverAllData(){

        $startedFrom = '2016-10-16';

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $overAllQuery = "select  count(*) as total,(select count(*) from help_query h1 where h1.reply_status = '3' and h1.created_on >= '$startedFrom') as open_counts,
            (select count(*) from help_query h1 where h1.reply_status = '2'  and h1.created_on >= '$startedFrom') as status_counts,
            (select count(*) from help_query h1 where (h1.reply_status = '1' || h1.reply_status = '2')  and h1.created_on >= '$startedFrom') as replied_counts 
            from help_query h";
        //$overAllResult = mysql_query($overAllQuery);
        $overAllResult=$DB->query($overAllQuery);
        $AllResult = $overAllResult->fetchAll();
        return $AllResult;
    }
*/

    function fetchBTree_forTabularForm($parentID,&$retArray,$limit){
        $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();
        /*$Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
         ->setIntegrityCheck(false)
         ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
         ->joinLeft(array('u'=>'user_info'),"u.username = a.username",array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
         ->joinLeft(array('i'=>'invoices'),"i.username = u.username",array('i.created_on'))
         ->joinLeft(array('c'=>'contracts'),"c.contract_id = i.contract_id",array('round(sum(c.total_price),2) as ContractPrice'))
         ->where("a.parent_id =$parentID")
         ->group("u.username")
         ->order("child_position ASC")
         );*/

        //$sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.child_position, UserInfo.name, UserInfo.ref_sponsor_id,UserInfo.isActiveId,UserInfo.lock_status, round(sum(Contract.total_price),2) as ContractPrice, Invoice.created_on FROM `binary_user_refences` as BinaryUserReferences left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username  left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1 left join contracts as Contract on Contract.contract_id = Invoice.contract_id WHERE BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username order by child_position ASC";
        $Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>"binary_user_refences"),array('BinaryUserReferences.id', 'BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),"BinaryUserReferences.username =  UserInfo.username",array('UserInfo.name', 'UserInfo.ref_sponsor_id','UserInfo.isActiveId','UserInfo.lock_status'))
        ->joinLeft(array('Invoice'=>'invoices')," Invoice.username = UserInfo.username and Invoice.invoice_status = 1",array('Invoice.created_on'))
        ->joinLeft(array('Contract'=>'contracts'),"Contract.contract_id = Invoice.contract_id",array('round(sum(Contract.total_price),2) as ContractPrice'))
        ->where("BinaryUserReferences.parent_id =$parentID")
        ->group("UserInfo.username")
        ->order("child_position ASC")
        );
        //$qry_res=$DBS->query($sql);
        //$Categories1 = $qry_res->fetchAll();
            

        if(isset($Categories1) && sizeof($Categories1)>0)
        {
            $Categories1 = $Categories1->toArray();
        }
        else
        {
            return true;
        }
            
        $limits = 10000000;
        /*if($limit > $limits ){
            //echo $limit;exit;
            $limit =$limit- 1;
            return true;
            }*/
        $j = 1;

        for($i=0;$i<sizeof($Categories1);$i++){
            if(sizeof($Categories1) < 2){
                if($Categories1[$i]['child_position'] == "L"){
                    $newArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                    $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                    $newArray['limit']= $limit;
                    $retArray[]=$newArray;
                    //echo "<pre>";
                    //print_r($retArray);exit;
                    // var_dump($newArray);
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'L':'R';
                    $newArray['name']= 'N/A';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']= '';
                    $newArray['isactive']= '';
                    $newArray['lock_status']= '';
                    $newArray['amtpaid']= '';
                    $newArray['date']= '';
                    $newArray['limit']= $limit;
                    // var_dump($newArray);

                    $retArray[]=$newArray;
                    $limit =$limit+1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($id,$retArray,$limit);

                }else{
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'L':'R';
                    $newArray['name']= 'N/A';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']= '';
                    $newArray['isactive']= '';
                    $newArray['lock_status']= '';
                    $newArray['amtpaid']= '';
                    $newArray['date']= '';
                    $newArray['limit']= $limit;
                    $retArray[]=$newArray;
                    // var_dump($newArray);

                    $newArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $newArray['child_position']=$child_position;
                    $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                    $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                    $newArray['limit']= $limit;
                    // var_dump($newArray);

                    $retArray[]=$newArray;
                    $limit = $limit + 1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($id,$retArray,$limit);

                }

            }
            else{

                // echo "j ".$j;
                // echo "<br>";
                if($j ==1){
                    $limit =$limit + 1;
                }
                $j++;
                $newArray=array();
                $id=$Categories1[$i]['id'];
                $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                $newArray['child_position']=$child_position;
                $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'';
                $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'';
                $newArray['limit']= $limit;

                // var_dump($newArray);

                $retArray[]=$newArray;
                    
                    
                //$limit +=1;
                // echo highlight_string("$id,$conn,$retArray,$limit");
                $Gbc_Model_Custom_func_obj->fetchBTree_forTabularForm($id,$retArray,$limit);


            }
        }
        return ($retArray) ;
    }

    function fetchBTree_count($parentID,&$retArray,$limit=''){
        $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();


        $sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.child_position FROM `binary_user_refences` as BinaryUserReferences  WHERE BinaryUserReferences.parent_id ={$parentID} order by child_position asc";

        $qry_res=$DBS->query($sql);
        $Categories1 = $qry_res->fetchAll();
            

        if(isset($Categories1) && sizeof($Categories1)>0)
        {

        }
        else
        {
            return true;
        }

        for($i=0;$i<sizeof($Categories1);$i++){
            if(sizeof($Categories1) < 2){
                if($Categories1[$i]['child_position'] == "L"){
                    $newArray=array();
                    $id=$Categories1[$i]['id'];

                    $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';

                    $Gbc_Model_Custom_func_obj->fetchBTree_count($id,$retArray);

                }else{
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'L':'R';
                    $newArray['name']= 'N/A';

                    $retArray[]=$newArray;
                    // var_dump($newArray);

                    $newArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';

                    // var_dump($newArray);

                    $retArray[]=$newArray;

                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->fetchBTree_count($id,$retArray);

                }

            }
            else{


                $newArray=array();
                $id=$Categories1[$i]['id'];
                $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';

                $retArray[]=$newArray;

                $Gbc_Model_Custom_func_obj->fetchBTree_count($id,$retArray);


            }
        }
        //print_r($retArray);exit;
        return ($retArray) ;
    }

	
    function getNetworkDetails($parentID, &$finalArray, $page,$child = ''){
		
		  $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();

		if($page == 1){
			$limit = "limit 499";
		}else{
			$offset = ($page-1)*500+1;
			$limit = "limit ".$offset.",500";
		}
		
		
		
        $children=array();
      	$children=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
		-> from(array('BinaryUserReferences'=>'binary_user_refences'),array('BinaryUserReferences.id','BinaryUserReferences.username','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),'(BinaryUserReferences.username = UserInfo.username)',array('UserInfo.ref_sponsor_id'))
        ->joinLeft(array('Invoice'=>'invoices'),'(Invoice.username = UserInfo.username and Invoice.invoice_status in (1,3))',array('Invoice.created_on','round(sum(Invoice.contract_rate),4) as ContractPrice'))
        ->where("BinaryUserReferences.parent_id=?",$parentID)
        ->group("UserInfo.username")
        ->order("child_position ASC")
        );

        if(isset($children) && sizeof($children)>0)
        {
            $children = $children->toArray();

        }

        $rightArray = array();
        $leftArray = array();
        $rightArray1 = array();
        $leftArray1 = array();

        for($i=0;$i<sizeof($children);$i++){

            if($children[$i]['child_position'] == 'R')
            {
                $rightArray['name']= !empty($children[$i]['username'])?strtolower($children[$i]['username']):'';
            //    $rightArray['parent']= !empty($children[$i]['parent_username'])?strtolower($children[$i]['parent_username']):'';
              //  $rightArray['child_position']=(!empty($children[$i]['child_position']) )?$children[$i]['child_position']:'R';
                $rightArray['sponsor']=!empty($children[$i]['ref_sponsor_id'])?strtolower($children[$i]['ref_sponsor_id']):'';
              //  $rightArray['isactive']= !empty($children[$i]['isActiveId'])?$children[$i]['isActiveId']:'';
             //   $rightArray['lock_status']= !empty($children[$i]['lock_status'])?$children[$i]['lock_status']:'';
                $rightArray['amtpaid']= !empty($children[$i]['ContractPrice'])?$children[$i]['ContractPrice']:'0';
                $rightArray['date']= !empty($children[$i]['created_on'])?$children[$i]['created_on']:null;
           //     $rightArray['limit']= $limit;
                $rightArray['pos']= 'R';
                //$retArray[]=$rightArray;
                    
                    
           //     $Gbc_Model_Custom_func_obj->getImmediateChild($children[$i][id],$rightArray1,$limit,'R');
				
				$total = $db->query("SELECT U.username as name, U.ref_sponsor_id as sponsor, round(sum(B.contract_rate),4) as amtpaid, B.created_on as date, B1.child_position as pos FROM `network_details` A inner join binary_user_refences B1 on B1.username = A.root_user inner join user_info U on U.username = A.username left join invoices B on ((B.username = A.username) and (B.invoice_status in (1,3))) where A.root_user='".$children[$i]['username']."' group by A.username $limit");
				$rightArray1 = $total->fetchAll();
				
			//	$totalContractFirst=$results[0]['contracts'];
                    
                $a=array('0'=>array('name'=>$rightArray['name'],'parent'=>$rightArray['parent'],'child_position' => $rightArray['child_position'],'sponsor' => $rightArray['sponsor'],'isactive' => $rightArray['isactive'], 'lock_status' => $rightArray['lock_status'],'amtpaid' => $rightArray['amtpaid'], 'date' => $rightArray['date'], 'limit' => $rightArray['limit'], 'pos' => $rightArray['pos']));
				if($page == 1){
                $rightArray1 = array_merge($a,$rightArray1);
				}
			//	$CountTotal = $db->query("SELECT count(*) as count FROM `network_details` A where A.root_user='".$children[$i]['username']."'");
			//	$CountrightArray1 = $CountTotal->fetchAll();
			//	$rightArray1['countRight'] = $CountrightArray1[0]['count'];
            }
            else
            {
                $leftArray['name']= !empty($children[$i]['username'])?strtolower($children[$i]['username']):'';
              //  $leftArray['parent']= !empty($children[$i]['parent_username'])?strtolower($children[$i]['parent_username']):'';
             //   $leftArray['child_position']=(!empty($children[$i]['child_position']) )?$children[$i]['child_position']:'L';
                $leftArray['sponsor']=!empty($children[$i]['ref_sponsor_id'])?strtolower($children[$i]['ref_sponsor_id']):'';
             //   $leftArray['isactive']= !empty($children[$i]['isActiveId'])?$children[$i]['isActiveId']:'';
              //  $leftArray['lock_status']= !empty($children[$i]['lock_status'])?$children[$i]['lock_status']:'';
                $leftArray['amtpaid']= !empty($children[$i]['ContractPrice'])?$children[$i]['ContractPrice']:'0';
                $leftArray['date']= !empty($children[$i]['created_on'])?$children[$i]['created_on']:null;
              //  $leftArray['limit']= $limit;
                $leftArray['pos']= 'L';

                //$retArray[]=$leftChildArray;
                    
              //  $Gbc_Model_Custom_func_obj->getImmediateChild($children[$i][id],$leftArray1,$limit,'L');
             
				$total = $db->query("SELECT U.username as name, U.ref_sponsor_id as sponsor, round(sum(B.contract_rate),4) as amtpaid, B.created_on as date, B1.child_position as pos FROM `network_details` A inner join binary_user_refences B1 on B1.username = A.root_user  inner join user_info U on U.username = A.username left join invoices B on ((B.username = A.username) and (B.invoice_status in (1,3))) where A.root_user='".$children[$i]['username']."' group by A.username $limit");
				$leftArray1 = $total->fetchAll();
			
				
				/*    echo "<pre>";
                    print_r($leftArray1);
                    */
                    
                $a=array('0'=>array('name'=>$leftArray['name'],'parent'=>$leftArray['parent'],'child_position' => $leftArray['child_position'],'sponsor' => $leftArray['sponsor'],'isactive' => $leftArray['isactive'], 'lock_status' => $leftArray['lock_status'],'amtpaid' => $leftArray['amtpaid'], 'date' => $leftArray['date'], 'limit' => $leftArray['limit'], 'pos' => $leftArray['pos']));
				if($page == 1){
					 $leftArray1 = array_merge($a,$leftArray1);
				}
			//	$CountTotal = $db->query("SELECT count(*) as count FROM `network_details` A where A.root_user='".$children[$i]['username']."'");
			//	$CountLeftArray1 = $CountTotal->fetchAll();
			//	$leftArray1['countLeft'] = $CountLeftArray1[0]['count'];
			//	$leftArray1['countLeft'] = count($leftArray1);
             
            }
        }

		if($child == "L"){
			$finalArray = $leftArray1;
		}else if($child == "R"){
			$finalArray = $rightArray1;
		}else{
			$finalArray = array_merge($rightArray1,$leftArray1);
		}
			
			
			
        
	//	print_r($finalArray);
        return ($finalArray);
		
	}
	
	
	
    function getDirectChildren($parentID, &$finalArray, $limit){

        $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();


        $children=array();
        /*$sql="Select id, username, parent_username, parent_id, child_position from binary_user_refences where parent_id='$parentID' ";
         $sql="SELECT
         * BinaryUserReferences.id,
         * BinaryUserReferences.username,
         * BinaryUserReferences.parent_username,
         * BinaryUserReferences.child_position,
         * UserInfo.name,
         * UserInfo.ref_sponsor_id,
         * UserInfo.isActiveId,
         * UserInfo.lock_status,
         * round(sum(Contract.total_price),2) as ContractPrice,
         *  Invoice.created_on
         *  FROM `binary_user_refences` as BinaryUserReferences
         *  left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username
         *  left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1
         *  left join contracts as Contract on Contract.contract_id = Invoice.contract_id
         *  WHERE BinaryUserReferences.parent_id ={$parentID}
         *  group by UserInfo.username
         *  order by child_position ASC";
         $children=array();
         $qry_res=$DBS->query($sql);

         $children = $qry_res->fetchAll();*/
        $children=$bin_user_ref->fetchAll($bin_user_ref->select()
        ->setIntegrityCheck(false)
        ->from(array('BinaryUserReferences'=>'binary_user_refences'),array('BinaryUserReferences.id','BinaryUserReferences.username','BinaryUserReferences.parent_username','BinaryUserReferences.child_position'))
        ->joinLeft(array('UserInfo'=>'user_info'),'(BinaryUserReferences.username = UserInfo.username)',array('UserInfo.name','UserInfo.ref_sponsor_id','UserInfo.isActiveId','UserInfo.lock_status'))
        ->joinLeft(array('Invoice'=>'invoices'),'(Invoice.username = UserInfo.username and Invoice.invoice_status = 1)',array('Invoice.created_on'))
        ->joinLeft(array('Contract'=>'contracts'),'(Contract.contract_id = Invoice.contract_id)',array('round(sum(Contract.total_price),2) as ContractPrice'))
        ->where("BinaryUserReferences.parent_id=?",$parentID)
        ->group("UserInfo.username")
        ->order("child_position ASC")
        );

        if(isset($children) && sizeof($children)>0)
        {
            $children = $children->toArray();

        }

        $rightArray = array();
        $leftArray = array();
        $rightArray1 = array();
        $leftArray1 = array();

        for($i=0;$i<sizeof($children);$i++){

            if($children[$i]['child_position'] == 'R')
            {
                $rightArray['name']= !empty($children[$i]['username'])?strtolower($children[$i]['username']):'';
                $rightArray['parent']= !empty($children[$i]['parent_username'])?strtolower($children[$i]['parent_username']):'';
                $rightArray['child_position']=(!empty($children[$i]['child_position']) )?$children[$i]['child_position']:'R';
                $rightArray['sponsor']=!empty($children[$i]['ref_sponsor_id'])?strtolower($children[$i]['ref_sponsor_id']):'';
                $rightArray['isactive']= !empty($children[$i]['isActiveId'])?$children[$i]['isActiveId']:'';
                $rightArray['lock_status']= !empty($children[$i]['lock_status'])?$children[$i]['lock_status']:'';
                $rightArray['amtpaid']= !empty($children[$i]['ContractPrice'])?$children[$i]['ContractPrice']:'0';
                $rightArray['date']= !empty($children[$i]['created_on'])?$children[$i]['created_on']:'-';
                $rightArray['limit']= $limit;
                $rightArray['pos']= 'R';
                //$retArray[]=$rightArray;
                    
                    
                $Gbc_Model_Custom_func_obj->getImmediateChild($children[$i][id],$rightArray1,$limit,'R');
                    
                $a=array('0'=>array('name'=>$rightArray['name'],'parent'=>$rightArray['parent'],'child_position' => $rightArray['child_position'],'sponsor' => $rightArray['sponsor'],'isactive' => $rightArray['isactive'], 'lock_status' => $rightArray['lock_status'],'amtpaid' => $rightArray['amtpaid'], 'date' => $rightArray['date'], 'limit' => $rightArray['limit'], 'pos' => $rightArray['pos']));
                $rightArray1 = array_merge($a,$rightArray1);
            }
            else
            {
                $leftArray['name']= !empty($children[$i]['username'])?strtolower($children[$i]['username']):'';
                $leftArray['parent']= !empty($children[$i]['parent_username'])?strtolower($children[$i]['parent_username']):'';
                $leftArray['child_position']=(!empty($children[$i]['child_position']) )?$children[$i]['child_position']:'L';
                $leftArray['sponsor']=!empty($children[$i]['ref_sponsor_id'])?strtolower($children[$i]['ref_sponsor_id']):'';
                $leftArray['isactive']= !empty($children[$i]['isActiveId'])?$children[$i]['isActiveId']:'';
                $leftArray['lock_status']= !empty($children[$i]['lock_status'])?$children[$i]['lock_status']:'';
                $leftArray['amtpaid']= !empty($children[$i]['ContractPrice'])?$children[$i]['ContractPrice']:'0';
                $leftArray['date']= !empty($children[$i]['created_on'])?$children[$i]['created_on']:'-';
                $leftArray['limit']= $limit;
                $leftArray['pos']= 'L';

                //$retArray[]=$leftChildArray;
                    
                $Gbc_Model_Custom_func_obj->getImmediateChild($children[$i][id],$leftArray1,$limit,'L');
                /*    echo "<pre>";
                    print_r($leftArray1);
                    */
                    
                $a=array('0'=>array('name'=>$leftArray['name'],'parent'=>$leftArray['parent'],'child_position' => $leftArray['child_position'],'sponsor' => $leftArray['sponsor'],'isactive' => $leftArray['isactive'], 'lock_status' => $leftArray['lock_status'],'amtpaid' => $leftArray['amtpaid'], 'date' => $leftArray['date'], 'limit' => $leftArray['limit'], 'pos' => $leftArray['pos']));
                $leftArray1 = array_merge($a,$leftArray1);
                    
                /*    echo "after merge";
                    echo "<pre>";
                    print_r($leftArray1);exit;*/
            }
        }


        $finalArray = array_merge($rightArray1,$leftArray1);
        /*echo "after merge";
         echo "<pre>";
         print_r($finalArray);exit;*/
            
        return ($finalArray);
    }

    function getImmediateChild($parentID,&$retArray,$limit,$pos){
        //    echo $parentID; exit;
        $DBS = Zend_Db_Table_Abstract::getDefaultAdapter();
        $bin_user_ref=new Gbc_Model_DbTable_Binaryuserreferences();
        $Gbc_Model_Custom_func_obj=new Gbc_Model_Custom_CommonFunc();
        $Categories1=array();
        /*$Categories1=$bin_user_ref->fetchAll($bin_user_ref->select()
         ->setIntegrityCheck(false)
         ->from(array('a'=>"binary_user_refences"),array('a.id','a.username','a.parent_username','a.parent_id','a.child_position'))
         ->joinLeft(array('u'=>'user_info'),"u.username = a.username",array('u.ref_sponsor_id','u.isActiveId','u.lock_status'))
         ->joinLeft(array('i'=>'invoices'),"i.username = u.username",array('i.created_on'))
         ->joinLeft(array('c'=>'contracts'),"c.contract_id = i.contract_id",array('round(sum(c.total_price),2) as ContractPrice'))
         ->where("a.parent_id =$parentID")
         ->group("u.username")
         ->order("child_position ASC")
         );*/

        $sql="SELECT BinaryUserReferences.id, BinaryUserReferences.username,BinaryUserReferences.parent_username,BinaryUserReferences.child_position, UserInfo.name, UserInfo.ref_sponsor_id,UserInfo.isActiveId,UserInfo.lock_status, round(sum(Contract.total_price),2) as ContractPrice, Invoice.created_on FROM `binary_user_refences` as BinaryUserReferences left join `user_info` as UserInfo on BinaryUserReferences.username =  UserInfo.username  left join invoices as Invoice on Invoice.username = UserInfo.username and Invoice.invoice_status = 1 left join contracts as Contract on Contract.contract_id = Invoice.contract_id WHERE BinaryUserReferences.parent_id ={$parentID} group by UserInfo.username order by child_position ASC";

        $qry_res=$DBS->query($sql);
        $Categories1 = $qry_res->fetchAll();
            

        if(isset($Categories1) && sizeof($Categories1)>0)
        {

        }
        else
        {
            return true;
        }
            
        $limits =40;
        if($limit > $limits ){
            //echo $limit;exit;
            $limit =$limit- 1;
            return true;
        }
        $j = 1;


        for($i=0;$i<sizeof($Categories1);$i++){
            if(sizeof($Categories1) < 2){
                if($Categories1[$i]['child_position'] == "L"){
                    $leftChildArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $leftChildArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $leftChildArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $leftChildArray['child_position']=$child_position;
                    $leftChildArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $leftChildArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $leftChildArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $leftChildArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'0';
                    $leftChildArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'-';
                    $leftChildArray['limit']= $limit;
                    $leftChildArray['pos']= $pos;
                    $retArray[]=$leftChildArray;
                    //echo "<pre>";
                    //print_r($retArray);exit;
                    // var_dump($newArray);
                    $leftChildArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'R':'L';
                    $leftChildArray['name']= 'N/A';
                    $leftChildArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $leftChildArray['child_position']=$child_position;
                    $leftChildArray['sponsor']= '';
                    $leftChildArray['isactive']= '';
                    $leftChildArray['lock_status']= '';
                    $leftChildArray['amtpaid']= '0';
                    $leftChildArray['date']= '-';
                    $leftChildArray['limit']= $limit;
                    $leftChildArray['pos']= $pos;
                    // var_dump($newArray);

                    $retArray[]=$leftChildArray;
                    $limit =$limit+1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->getImmediateChild($id,$retArray,$limit,$pos);

                }else{
                    $newArray=array();
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?'R':'L';
                    $rightChildArray['name']= 'N/A';
                    $rightChildArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $rightChildArray['child_position']=$child_position;
                    $rightChildArray['sponsor']= '';
                    $rightChildArray['isactive']= '';
                    $rightChildArray['lock_status']= '';
                    $rightChildArray['amtpaid']= '0';
                    $rightChildArray['date']= '-';
                    $rightChildArray['limit']= $limit;
                    $rightChildArray['pos']= $pos;
                    $retArray[]=$rightChildArray;
                    // var_dump($newArray);

                    $rightChildArray=array();
                    $id=$Categories1[$i]['id'];
                    $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                    $rightChildArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                    $rightChildArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                    $rightChildArray['child_position']=$child_position;
                    $rightChildArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                    $rightChildArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                    $rightChildArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                    $rightChildArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'0';
                    $rightChildArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'-';
                    $rightChildArray['limit']= $limit;
                    $rightChildArray['pos']= $pos;
                    // var_dump($newArray);

                    $retArray[]=$rightChildArray;
                    $limit = $limit + 1;
                    // echo highlight_string("$id,$conn,$retArray,$limit");
                    $Gbc_Model_Custom_func_obj->getImmediateChild($id,$retArray,$limit,$pos);

                }

            }
            else{

                // echo "j ".$j;
                // echo "<br>";
                if($j ==1){
                    $limit =$limit + 1;
                }
                $j++;
                $newArray=array();
                $id=$Categories1[$i]['id'];
                $child_position= (!empty($Categories1[$i]['child_position']) && $Categories1[$i]['child_position'] == "R")?$Categories1[$i]['child_position']:'L';
                $newArray['name']= !empty($Categories1[$i]['username'])?strtolower($Categories1[$i]['username']):'';
                $newArray['parent']= !empty($Categories1[$i]['parent_username'])?strtolower($Categories1[$i]['parent_username']):'';
                $newArray['child_position']=$child_position;
                $newArray['sponsor']=!empty($Categories1[$i]['ref_sponsor_id'])?strtolower($Categories1[$i]['ref_sponsor_id']):'';
                $newArray['isactive']= !empty($Categories1[$i]['isActiveId'])?$Categories1[$i]['isActiveId']:'';
                $newArray['lock_status']= !empty($Categories1[$i]['lock_status'])?$Categories1[$i]['lock_status']:'';
                $newArray['amtpaid']= !empty($Categories1[$i]['ContractPrice'])?$Categories1[$i]['ContractPrice']:'0';
                $newArray['date']= !empty($Categories1[$i]['created_on'])?$Categories1[$i]['created_on']:'-';
                $newArray['limit']= $limit;
                $newArray['pos']= $pos;//$Categories1[$i]['child_position'];
                // var_dump($newArray);

                $retArray[]=$newArray;
                    
                    
                //$limit +=1;
                // echo highlight_string("$id,$conn,$retArray,$limit");
                $Gbc_Model_Custom_func_obj->getImmediateChild($id,$retArray,$limit,$pos);


            }
        }
        return ($retArray) ;
    }
	
	
/* Functions for CRM Dashboard Starts*/
	
    function getOverAllData(){

        $startedFrom = '2017-02-08';

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $overAllQuery = "select  count(*) as total,(select count(*) from help_query h1 where h1.status = '1' and h1.created_on >= '$startedFrom') as open,
            (select count(*) from help_query h1 where h1.status = '0'  and h1.created_on >= '$startedFrom') as closed,
            (select count(*) from help_query h1 where h1.status = '3'  and h1.created_on >= '$startedFrom') as resolved,
            (select count(*) from help_query h1 where h1.status = '11'  and h1.created_on >= '$startedFrom') as pending_kit_activation,
            (select count(*) from help_query h1 where h1.status = '12'  and h1.created_on >= '$startedFrom') as pending_refund,
            (select count(*) from help_query h1 where h1.status IN('2','4','5','6','7','8','9','10','13')  and h1.created_on >= '$startedFrom') as pending_others
			
            from help_query h1 where h1.created_on >= '$startedFrom'";
        $overAllResult=$DB->query($overAllQuery);
        $AllResult = $overAllResult->fetchAll();
        return $AllResult;
    }

    function getTodayData(){

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $todayDataQuery = 	"select  count(*) as total,(select count(*) from help_query h1 where h1.status = '1' and date(created_on) = date(now())) as open,
            (select count(*) from help_query h1 where h1.status = '0'  and date(created_on) = date(now())) as closed,
            (select count(*) from help_query h1 where h1.status = '3'  and date(created_on) = date(now())) as resolved,
            (select count(*) from help_query h1 where h1.status = '11'  and date(created_on) = date(now())) as pending_kit_activation,
            (select count(*) from help_query h1 where h1.status = '12'  and date(created_on) = date(now())) as pending_refund,
            (select count(*) from help_query h1 where h1.status IN('2','4','5','6','7','8','9','10','13')  and date(created_on) = date(now())) as pending_others
			
            from help_query h1 where date(created_on) = date(now())";
        $todayData=$DB->query($todayDataQuery);
        $todayResult = $todayData->fetchAll();
        return $todayResult;
    }
	
    function getDataRange($startDate,$endDate){

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $todayDataQuery = 	"select  count(*) as total,
			(select count(*) from help_query h1 where h1.status = '1' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as open,
            (select count(*) from help_query h1 where h1.status = '0'   and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as closed,
            (select count(*) from help_query h1 where h1.status = '3'	and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as resolved,
            (select count(*) from help_query h1 where h1.status = '11'  and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_kit_activation,
            (select count(*) from help_query h1 where h1.status = '12'  and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_refund,
            (select count(*) from help_query h1 where h1.status IN('2','4','5','6','7','8','9','10','13')  and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_others
			
            from help_query h1 where date(created_on) >= '$startDate' and date(created_on) <= '$endDate'";
        $todayData=$DB->query($todayDataQuery);
        $todayResult = $todayData->fetchAll();
        return $todayResult;
    }

		
    function getSummaryData(){

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $SummaryQuery = "select (select count(distinct ticket_id) from help_query where assigned_to = s1.first_name and help_query.created_on > '2017-02-08' ) as total,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='1' and date(h1.created_on) >= '2017-02-08') as open,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='0' and date(h1.created_on) >= '2017-02-08') as close,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='3' and date(h1.created_on) >= '2017-02-08') as resolved,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='11' and date(h1.created_on) >= '2017-02-08') as pending_kit_activation ,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='12' and date(h1.created_on) >= '2017-02-08') as pending_refund_related,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status IN ('2','4','5','6','7','8','9','10','13') and date(h1.created_on) >= '2017-02-08') as pending_others,
						 s1.email from sub_admin_users as s1 having total > 0";
        $summaryData=$DB->query($SummaryQuery);
        $summaryResult = $summaryData->fetchAll();
        return $summaryResult;
    }	
	
    function getSummaryDataRange($startDate,$endDate){

        $returnArr = array();

        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $SummaryQuery = "select (select count(distinct ticket_id) from help_query where assigned_to = s1.first_name and help_query.created_on >= '$startDate' and help_query.created_on <= '$endDate') as total,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='1' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as open,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='0' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as close,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='3' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as resolved,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='11' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_kit_activation ,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status='12' and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_refund_related,
						(select  count(distinct ticket_id) as total from help_query h1  where assigned_to = s1.first_name and h1.status IN ('2','4','5','6','7','8','9','10','13') and date(h1.created_on) >= '$startDate' and date(h1.created_on) <= '$endDate') as pending_others,
						 s1.email from sub_admin_users as s1 having total > 0";
        $summaryData=$DB->query($SummaryQuery);
        $summaryResult = $summaryData->fetchAll();
        return $summaryResult;
    }	

		
	function getRepliedData()
	{
		$returnArr = array();
		
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$replySummaryQuery = "select (select count(distinct ticket_id) from  help_query_comments h1 where s1.email = h1.comments_by and date(comment_date) = date(now())) as total_replied,
						 s1.email from sub_admin_users s1 having total_replied >0 ";
		$replySummaryData=$DB->query($replySummaryQuery);
        $replySummaryResult = $replySummaryData->fetchAll();
        return $replySummaryResult;
	}
	
	
	function getRepliedDataRange($startDate,$endDate)
	{
		$returnArr = array();
		
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$replySummaryQuery = "select (select count(distinct ticket_id) from  help_query_comments h1 where s1.email = h1.comments_by and date(comment_date) >= '$startDate' and date(comment_date) <= '$endDate') as total_replied,
						 s1.email from sub_admin_users s1 having total_replied >0 ";
		$replySummaryData=$DB->query($replySummaryQuery);
        $replySummaryResult = $replySummaryData->fetchAll();
        return $replySummaryResult;
	}	
/* Functions for CRM Dashboard Ends*/	
	

    function saveUserLog2fa($username,$table_name,$description,$logged_in_user,$ip_address){
        $userlogs_obj = new Gbc_Model_DbTable_Userlogs();
        $insert_arr=array('username'=>$username,'description'=>$description,'table_name'=>$table_name,'loggedin_user'=>$logged_in_user,'ip_address'=>$ip_address);
        $insert_data=$userlogs_obj->insert($insert_arr);
		return "success";

    }	

}
?>