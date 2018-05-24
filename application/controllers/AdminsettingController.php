<?php
class AdminsettingController extends Zend_Controller_Action{

    public function init(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

    }


    public function indexAction()
    {
        $this->view->title="Gainbitcoin - Subadmin";
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(!empty($authUserNamespace->user) &&  $authUserNamespace->user=='admin')
        {
            $loggedIn==true;
        }
        else
        {
            $authUserNamespace->msg="You do not have sufficient privileges to access this area.";
            $this->_redirect("/Login");
        }
        $adminsettingObj = new Gbc_Model_DbTable_Adminsetting();



        $result=array();

        $this->_helper->layout()->setLayout("admindashbord");



        $result=$adminsettingObj->fetchRow($adminsettingObj->select()
            ->setIntegrityCheck(false)
            ->from(array('admin_setting')));

        $this->view->result = $result;
        try{
            $data=array();
            if($this->_request->isPost())
            {
                $adminsettingObj = new Gbc_Model_DbTable_Adminsetting();
                $antixss = new Gbc_Model_Custom_StringLimit();
                foreach($_POST as $key => $value)
                {

                    if(isset($value) && $value != ""){
                        $antixss->setEncoding($value, "UTF-8");
                        if ($antixss->setFilter($value, "black", "string") == "invalidInput"){

                            $data=array('success'=>'','failure'=>'Invalid Input.');
                            echo json_encode($data);exit;

                        }

                    }

                }
                $status=$_POST['static_status'];
                $paymentmode=$_POST['payment_mode'];
                $token=$_POST['token'];
                $paymentgate=$_POST['payment_gate'];
                //	if($authUserNamespace->token==$token){
                //if($status!='')

                //	if($paymentmode!='')
                //	{
                //	$data=array("static_flag"=>$status,"payment_mode"=>$paymentmode,"updated_on"=>new Zend_Db_Expr('NOW()'));

                if($paymentmode!='' && $paymentgate!='')
                {
                    $data=array("static_flag"=>$status,"payment_mode"=>$paymentmode,"payment_gateway"=>$paymentgate,"updated_on"=>new Zend_Db_Expr('NOW()'));



                    $where = array(
                        'id'=>'1',
                    );

                    $adminsettingObj->update($data,$where);
                    //$this->_redirect('/Adminsetting');
                    $msg = "Data Updated successfully";

                    if(!empty($msg)){
                        $authUserNamespace->msg=$msg;
                        $this->_redirect('/Adminsetting');
                    }


                }
                else
                {
                    $msg = "Can Not Blank Status";
                    $authUserNamespace->msg=$msg;

                }

                /*
                    }else{
                        //$data=array('success'=>'','failure'=>'Invalid Request Found.');
                        //echo json_encode($data);
                        $errmsg = "Invalid Request Found.";
                        $authUserNamespace->errmsg=$errmsg;
                    }*/
            }
        }
        catch(Exception $e)
        {
            $this->view->msg=$e->getMessage();
        }

        //echo "<pre>";
        //print_r($result);exit;
        //echo "<pre>";
        //print_r( $result['data']);exit;

    }


}
