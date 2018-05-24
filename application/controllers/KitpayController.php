<?php
class KitpayController extends Zend_Controller_Action{

    public function init(){

        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

    }

    public function indexAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $this->_helper->layout()->setLayout("dashbord");//dashboard
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $invoiceId = $common_obj->cleanQueryParameter($_POST['invoice_id']);
        $hardware = $common_obj->cleanQueryParameter($_POST['flag']);
        $arr=array();
        $token = $_POST['token'];
        $admin_settings_obj= new Gbc_Model_DbTable_Adminsetting();
        $admin_result = $admin_settings_obj->fetchRow($admin_settings_obj->select()
        ->where("status=?",1));

        $antixss = new Gbc_Model_Custom_StringLimit();
                foreach($_POST as $key => $value)
                {

                    if(isset($value) && $value != ""){
                        $antixss->setEncoding($value, "UTF-8");
                        if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                            //$data=array('success'=>'','failure'=>'Invalid Input.');
                            //echo json_encode($data);exit;
                            $this->_redirect("/Profileerror/errormsg");

                        }

                    }

                }

        //if($authUserNamespace->token==$token){
            if(!empty($_POST['invoice_id'])){
                //$invoiceId = $_POST['invoice_id'];
                $invoiceDetails =$common_obj-> getKitInvoiceDetails($invoiceId);

                if(!empty($invoiceDetails) && isset($invoiceDetails) && sizeof($invoiceDetails)>0){

                    $price_in_btc = $invoiceDetails->contract_rate;
                    $noOfKits = $invoiceDetails->kits_qty;
                    $payment_url = "https://bitcoinpay.com/en/sci/invoice/btc/".$invoiceDetails->transactionid;
                    if(isset($payment_mode))
                    {
                        $payment_mode = $admin_result->payment_mode;
                        if(isset($price_in_btc) && $price_in_btc > 15 && $price_in_btc!='')
                        {
                            $payment_mode = 'static';
                        }
                    }
                    else
                    {
                        $payment_mode = 'static';
                    }

                    $arr=array("success"=>"success","failure"=>"","payment_mode"=>$payment_mode,"price"=>$price_in_btc,"no_of_kits"=>$noOfKits,"payment_url"=>$payment_url,'invoiceId'=>$invoiceId);


                } else {
                    print("Error fetching invoice details");
                    exit;
                }
            }
        }

        /*else{
            //$data=array('success'=>'','failure'=>'Invalid Request Found.');
            // echo json_encode($data);exit;
            //$messag =  'Invalid Request Found';
            //$authUserNamespace->errmsg=$messag;
            $this->_redirect("/Profileerror/errormsg");


        }*/
        $this->view->result=$arr;
        $username=$authUserNamespace->user;
        $url= BASE."/Contractmaster";
        $result=$common_obj->call_curl($url);
        $contract=(array)json_decode($result,true);
        //echo "<pre>";
        //print_r($contract);exit;
        $this->view->contract=$contract;
        $hardware_data=array();
        $sha_data=array();

        for($list=0;$list<sizeof($contract['data']);$list++)
        {
            $cont_data = array('contract_id'=>$contract['data'][$list]['contract_id'],'contract_ts'=>$contract['data'][$list]['contract_ts'],'contract_name'=>$contract['data'][$list]['contract_name'],'contract_type'=>$contract['data'][$list]['contract_type'],'contract_qty'=>$contract['data'][$list]['contract_qty'],'contract_descr'=>$contract['data'][$list]['contract_descr'],'contract_rate'=>$contract['data'][$list]['contract_rate'],'price_paid'=>$contract['data'][$list]['price_paid'],'total_price'=>$contract['data'][$list]['total_price'],'description'=>$contract['data'][$list]['description'],'ordering'=>$contract['data'][$list]['ordering'],'admin_limit'=>$contract['data'][$list]['admin_limit'],'available_limit'=>$contract['data'][$list]['available_limit'],'max_limit'=>$contract['data'][$list]['max_limit'],'direct_earning'=>$contract['data'][$list]['direct_earning']);
            if($contract['data'][$list]['contract_type']=='hardware')
            {
                array_push($hardware_data,$cont_data);
            }
            else
            {
                array_push($sha_data,$cont_data);
            }
        }
        //print_r($$hardware_data);exit;
        $this->view->sha_contract=$sha_data;
        $this->view->hardware_contract=$hardware_data;





        $countries_obj=new Gbc_Model_DbTable_Countries();
        $countries_data=$countries_obj->fetchAll($countries_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('countries')));

        $cities_obj=new Gbc_Model_DbTable_Cities();
        $cities_data=$cities_obj->fetchAll($cities_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('cities')));

        $states_obj=new Gbc_Model_DbTable_States();
        $states_data=$states_obj->fetchAll($states_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('states')));


        $users_obj=new Gbc_Model_DbTable_Userinfo();
        $users_data=$users_obj->fetchAll($users_obj->select()
        ->setIntegrityCheck(false)
        ->from(array('user_info'))
        ->where("username=?",$username));
        //echo "<pre>";
        //print_r($users_data);exit;
        $user_cat=array('countries_data'=>$countries_data,'cities_data'=>$cities_data,'states_data'=>$states_data,'users_data'=>$users_data);
        $this->view->user_cat=$user_cat;




    }

}
