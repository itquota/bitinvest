<?php

class UpdatereferencemissingController extends Zend_Controller_Action{

    public function init(){
        
    }
    public function indexAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $user_obj = new Gbc_Model_DbTable_Userinfo();
        $binary_usr_ref = new Gbc_Model_DbTable_Binaryuserreferences();
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        
        $userslist = $user_obj->fetchAll($user_obj->select()
                        ->setIntegrityCheck(false)
                        ->from(array('u'=>"user_info"))
//                        ->where("username =?" ,"sunny4hunny" )    
                        ->where("created_on like '%2017-02-16%'" )
			->order('created_on ASC')                    
			->limit (20,0)
//                        ->where("username =?"   ,"palakpoddar" )                    
                        );
            
        if(sizeof($userslist)>0)
        {
            foreach($userslist as $user){
                
                
                //Check if user present in binary ref table
                $check_presence = $binary_usr_ref->fetchAll($binary_usr_ref->select()
                                    ->setIntegrityCheck(false)
                                    ->from(array('b'=>"binary_user_refences"))
                                    ->where("username =?" ,$user['username'] )
                                    );


                if(sizeof($check_presence)==0){
                    
                    $row = $user_obj->fetchRow($user_obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('u' =>'user_info'),array('max(SUBSTR(u.sponsor_id,6)) as sponsor_id'))
                    ->where("binaryUser is NOT NULL"));
                    
                    $i=1;
                    if(!empty($row) && sizeof($row)>0)
                    {
                        $randStr = $s = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",8)), 0, 3);
                        $randStr1 = $s = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",8)), 0, 3);
                        $sponsor_id='GBBN'.$randStr.time().($row['sponsor_id'] + $i).$randStr1;
                    }
                    else
                    {
                        $sponsor_id='GBBN1000001';
                    }
                    
                    
                    if($user['placement']!=""){
                        $ref['choice_leg'] = $user['placement'];
                    }
                    else{
                        $ref['choice_leg'] = 'L';
                    }
                    $ref['username'] = $user['username'];
                    $ref['parent_username'] = $user['ref_sponsor_id'];
                    $ref['parent_sponser_id'] = $user['ref_sponsor_id'];             
                    $ref['sponsor_id'] = $sponsor_id;
                    $ref["child_position"] = '';
                    
                    $refrenceCreator= $this->createUserrefrences($ref);

                    if(!empty($refrenceCreator) && isset($refrenceCreator) && $refrenceCreator!='')
                    {
                        $updateBinaryNetwork = $this->insertUpdateBinaryNetwork($ref);
                        echo $updateBinaryNetwork;
                    }
                    
                }
            }
        }
        echo "done";exit;
    }
    function createUserrefrences($ref) {
    
        $bin_user_ref_object=new Gbc_Model_DbTable_Binaryuserreferences();
        $comm_obj=new Gbc_Model_Custom_CommonFunc();
        $user = new Gbc_Model_DbTable_Userinfo();

        $checkUnique=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->setIntegrityCheck(false)
        ->from(array('b'=>"binary_user_refences"),array('count(b.username) as uniq_child'))
        ->where("username=?", trim($ref["username"])));

        if (!empty($checkUnique) && $checkUnique->uniq_child != 0) {
            return 'User already exist';
            exit();
        }


        $check_user = $user->fetchRow($user->select()
        ->where("binaryUser is NOT NULL")
        ->where("username=?",trim($ref["parent_username"]))
        );

        $chk_depth= $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
        ->where("parent_username=?" , trim($ref["parent_username"])));

        $allRecords_row =  $bin_user_ref_object->fetchAll($bin_user_ref_object->select()
        ->where("parent_username=?",trim($ref["parent_username"])));
            
        $allRecords=sizeof($allRecords_row);
echo $allRecords; 
        if($ref["child_position"] =='ref_page' && $ref["parent_username"]!='amitsabnetwork'){
            $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){

                $check_dw=$user->fetchRow($user->select()
                ->where("binaryUser is NOT NULL")
                ->where("username=?",trim($ref["finalRes"]))
                );
                if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?" , trim($finalRes)));

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
                    ->where("parent_username=?" , trim($finalRes)));


                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        $comm_obj->getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }else{

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username='" . trim($finalRes) . "'"));
                            

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
                ->where("username=?" , trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
             $result=$bin_user_ref_object->insert($query);
                    
            }

        }


        if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==1)){

            $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                ->where("binaryUser is NOT NULL")
                ->where("username=?", trim($ref["finalRes"]))
                );

                if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?" , trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                    $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }else{
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?" , trim($finalRes)));



                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        $comm_obj->getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                         $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($ref["parent_username"])));

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
                        ->where("username=?" , trim($finalRes)));
                            

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
                ->where("username=?" , trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                $result=$bin_user_ref_object->insert($query);
            }

        }


        if($ref["parent_username"]=='amitsabnetwork' && (($allRecords)==2)){
            $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                ->where("binaryUser is NOT NULL")
                ->where("username=?", trim($ref["finalRes"]))
                );

                if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?" , trim($finalRes)));

                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                     $result=$bin_user_ref_object->insert($query);
                    return "success";
                    exit();
                }else{
                    $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->setIntegrityCheck(false)
                    ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                    ->where("parent_username=?" , trim($finalRes)));



                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        $comm_obj->getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($ref["parent_username"])));

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
                        ->where("username=?" , trim($finalRes)));
                            

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
                ->where("username=?" , trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
             $result=$bin_user_ref_object->insert($query);
            }

        }

        if((count($allRecords) == 3)) {
            $comm_obj->getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $check_dw=$user->fetchRow($user->select()
                ->where("binaryUser is NOT NULL")
                ->where("username=?", trim($ref["finalRes"]))
                );

                if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){
                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                    ->where("username=?" , trim($finalRes)));

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
                    ->where("parent_username=?" , trim($finalRes)));
                    if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                        if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                        $comm_obj->getRight($finalRes,$choice, $final);
                        if(!empty($final)){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($final)));


                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }else{
                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($ref["parent_username"])));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';

                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                             $result=$bin_user_ref_object->insert($query);

                            return "success";
                            exit();
                        }
                    }
                    else{

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?" , trim($finalRes)));
                            

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
                ->where("username=?" , trim($ref["parent_username"])));
                    

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
 $result=$bin_user_ref_object->insert($query);
                    
            }
        }


        if ((($allRecords) == 2)){
            $comm_obj->getRight($ref["parent_username"], $ref["choice_leg"], $finalRes);
            if(!empty($finalRes)){
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?" , trim($finalRes)));

                $depth = $chk_depth1->depth . $ref["username"] . ',';
                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
             $result=$bin_user_ref_object->insert($query);
                    
                return "success";
                exit();
            }
            else
            {
                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                ->where("username=?" , trim($ref["parent_username"])));
                    

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
                $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                if(!empty($finalRes)){
                    $check_dw=$user->fetchRow($user->select()
                    ->where("binaryUser is NOT NULL")
                    ->where("username=?", trim($ref["finalRes"]))
                    );
                    if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?" , trim($finalRes)));

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
                     ->where("parent_username=?" , trim($finalRes)));
                     if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                         if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                         $comm_obj->getRight($finalRes,$choice, $final);
                         if(!empty($final)){

                             $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                             ->where("username=?" , trim($final)));


                             $depth = $chk_depth1->depth . $ref["username"] . ',';
                             $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                          $result=$bin_user_ref_object->insert($query);
                             return "success";
                             exit();
                         }else{
                             $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                             ->where("username=?" , trim($ref["parent_username"])));

                             $depth = $chk_depth1->depth . $ref["username"] . ',';

                             $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                              $result=$bin_user_ref_object->insert($query);

                             return "success";
                             exit();
                         }
                     }
                     else{

                         $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                         ->where("username=?" , trim($finalRes)));


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
                    ->where("username=?" , trim($ref["parent_username"])));


                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                     $result=$bin_user_ref_object->insert($query);

                }
            }
            else if (count($allRecords) == 0 && $check_user['isActiveId']==1) {
            echo "here";exit;
                $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                if(!empty($finalRes)){
                    $check_dw=$user->fetchRow($user->select()
                    ->where("binaryUser is NOT NULL")
                    ->where("username=?", trim($ref["finalRes"]))
                    );
                    if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?" , trim($finalRes)));

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
                        ->where("parent_username=?" , trim($finalRes)));


                        if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                            if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                            $comm_obj->getRight($finalRes,$choice, $final);
                            if(!empty($final)){

                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?" , trim($final)));


                                $depth = $chk_depth1->depth . $ref["username"] . ',';
                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                         $result=$bin_user_ref_object->insert($query);
                                return "success";
                                exit();
                            }else{
                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?" , trim($ref["parent_username"])));

                                $depth = $chk_depth1->depth . $ref["username"] . ',';

                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                         $result=$bin_user_ref_object->insert($query);

                                return "success";
                                exit();
                            }
                        }else{

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($finalRes)));


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
                    ->where("username=?" , trim($ref["parent_username"])));


                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                 $result=$bin_user_ref_object->insert($query);

                }
            }
            else
            {
            echo "inactive".$check_user['isActiveId'];
                if ($check_user['isActiveId'] == 1) {
                echo "inactiveActiveID";
                    $comm_obj->getRight($ref["parent_username"],$ref["choice_leg"], $finalRes);
                    if(!empty($finalRes)){
echo "in final res";
                        $check_dw=$user->fetchRow($user->select()
                        ->where("binaryUser is NOT NULL")
                        ->where("username=?", trim($ref["finalRes"]))
                        );
                        if(!empty($check_dw) && (sizeof($check_dw)>0) && $check_dw->isActiveId==1){

                            $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->where("username=?" , trim($finalRes)));

                            $depth = $chk_depth1->depth . $ref["username"] . ',';
                            $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                            echo "ddd<pre>";
                                    print_r($query);echo "<br>";
                     $result=$bin_user_ref_object->insert($query);
                            return "success";
                            exit();
                        }
                        else
                        {
                            $chk_depth_leg=$bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                            ->setIntegrityCheck(false)
                            ->from(array('b'=>"binary_user_refences"),array('count(username) as total'))
                            ->where("parent_username=?" , trim($finalRes)));

                            if(!empty($chk_depth_leg) && ($chk_depth_leg->total)>0){
                                if(($ref["choice_leg"])=='R') { $choice='L';} else{ $choice='R'; }
                                $comm_obj->getRight($finalRes,$choice, $final);
                                if(!empty($final)){

                                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                    ->where("username=?" , trim($final)));


                                    $depth = $chk_depth1->depth . $ref["username"] . ',';
                                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                    echo "ccc<pre>";
                                    print_r($query);echo "<br>";
                                 $result=$bin_user_ref_object->insert($query);
                                    return "success";
                                    exit();
                                }else{
                                    $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                    ->where("username='" . trim($ref["parent_username"]) . "'"));

                                    $depth = $chk_depth1->depth . $ref["username"] . ',';

                                    $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$choice,'depth'=>$depth);
                                    echo "bbb<pre>";
                                    print_r($query);echo "<br>";
                                     $result=$bin_user_ref_object->insert($query);

                                    return "success";
                                    exit();
                                }
                            }
                            else{

                                $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                                ->where("username=?" , trim($finalRes)));


                                $depth = $chk_depth1->depth . $ref["username"] . ',';
                                $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                                echo "aaa<pre>";
                                    print_r($query);echo "<br>";
                             $result=$bin_user_ref_object->insert($query);

                                return "success";
                                exit();
                            }
                        }
                    }
                    else
                    {
                    echo "Getting into action";
                        $chk_depth1 = $bin_user_ref_object->fetchRow($bin_user_ref_object->select()
                        ->where("username=?" , trim($ref["parent_username"])));


                        $depth = $chk_depth1->depth . $ref["username"] . ',';
                        $query=array('username'=>$ref["username"],'parent_username'=>$chk_depth1->username,'parent_id'=>$chk_depth1->id,'child_position'=>$ref["choice_leg"],'depth'=>$depth);
                        echo "<pre>";
                        print_r($query);echo "<br>";
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
        ->where("username=?" ,$ref["username"]));

        if(isset($chk_Parent) && sizeof($chk_Parent)>0)
        {
            $bin_net_details_obj=new Gbc_Model_DbTable_Binarynetworkdetail();
            $chk_depth1 = $bin_net_details_obj->fetchRow($bin_net_details_obj->select()
            ->where("username=?",$chk_Parent->parent_username));
            $depth = str_replace(',',"','",$chk_depth1->depth);
            $insertDepth = $depth."','".$ref["username"];

            $checkExistUser = $bin_net_details_obj->fetchAll($bin_net_details_obj->select()
            ->where("username=?" , $ref["username"]));

            if(isset($checkExistUser) && sizeof($checkExistUser)>0)
            {

            }
            else
            {
                $arr_ins=array('username'=>$ref["username"],'depth'=>$insertDepth,'status'=>'1');
            $ins_data=$bin_net_details_obj->insert($arr_ins);

            }
            $arr_upd=array('status'=>'1','updated_on'=>new Zend_Db_Expr('NOW()'));
        $upd_data=$bin_net_details_obj->update($arr_upd,"username in ('$insertDepth')");
            return "success";
        }
        return "success";

    }
}
