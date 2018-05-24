<?php
class UsercontractController extends Zend_Controller_Action{

    public function init()
    {

    }
    public function indexAction()
    {
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        //$common_obj->cleanQueryParameter(($_POST['username']));

        try
        {
            //$username=$_POST['username'];
            $username=$common_obj->cleanQueryParameter(($_GET['username']));
            if($username != ''){

                $username=$username;

            }else{
                $arr=array('Success'=>' ','Failure'=>'Username cannot be blank');
                echo json_encode($arr);
                exit;
            }
            $inv_obj = new Gbc_Model_DbTable_Invoices();
            /*$invoices_obj = $inv_obj->fetchAll($inv_obj->select()
            ->where("1=1 AND username = '" . ($username) . "' AND contract_type in ('SHA','hardware')")
            ->order("invoice_id DESC")
            );*/

            $invoices_obj = $inv_obj->fetchAll($inv_obj->select()
              //  ->where("1=1 AND username = ? AND contract_type in ('SHA','hardware')",$username)
                ->where("1=1 AND username = ? AND contract_type in ('SHA','hardware','MS','ES')",$username)
                ->order("invoice_id DESC")
            );

            $arr=array();
            if(isset($invoices_obj) && sizeof($invoices_obj)>0)
            {
                for($i=0;$i<sizeof($invoices_obj);$i++)
                {
                    $subarr=array('invoice_id'=>$invoices_obj[$i]['invoice_id'],'confirmations'=>$invoices_obj[$i]['confirmations'],'middleAddr'=>$invoices_obj[$i]['middleAddr'],'amtPaid'=>$invoices_obj[$i]['amtPaid'],'transactionid'=>$invoices_obj[$i]['transactionid'],'origtxid'=>$invoices_obj[$i]['origtxid'],'contract_id'=>$invoices_obj[$i]['contract_id'],'contract_name'=>$invoices_obj[$i]['contract_name'],'contract_type'=>$invoices_obj[$i]['contract_type'],'contract_qty'=>$invoices_obj[$i]['contract_qty'],'contract_rate'=>$invoices_obj[$i]['contract_rate'],'invoice_status'=>$invoices_obj[$i]['invoice_status'],'username'=>$invoices_obj[$i]['username'],'use_kit_number'=>$invoices_obj[$i]['use_kit_number']);
                    array_push($arr,$subarr);
                }
                $data=array('success'=>'success','failure'=>'','data'=>$arr);
                echo json_encode($data);exit;

            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

    }

    public function contractAction()
    {

        try{
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            //$common_obj->cleanQueryParameter(($_POST['username']));
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

            $username=$authUserNamespace->user;

            //	$id=$_POST['kit_number'];
            $id=$common_obj->cleanQueryParameter($_POST['kit_number']);
            $token = $common_obj->cleanQueryParameter($_POST['token']);
            /*		if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
                        $data=array('success'=>'','failure'=>'Invalid request found.');
                        echo json_encode($data);exit;

                    } */
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



            $invoiceobj = new Gbc_Model_DbTable_Invoices();
            $contractobj = new Gbc_Model_DbTable_Contracts();

            /*$invoices_obj = $invoiceobj->fetchAll($invoiceobj->select()
            ->where("1=1 AND username='".($username)."' AND contract_type IN ('SHA','hardware') AND use_kit_number='".$id."' ")
            ->order("invoice_id DESC")
            );*/
            $invoices_obj = $invoiceobj->fetchAll($invoiceobj->select()
                ->where("1=1")
                ->where("username=?",$username)
                ->where("contract_type IN ('SHA','hardware','MS','ES')")
                ->where("use_kit_number=?",$id)
                ->order("invoice_id DESC")
            );

            $arr=array();

            //$result=sizeof($invoices_obj);
            if(isset($invoices_obj) && sizeof($invoices_obj)>0)
            {
                //$datearr[] = date("jS M, Y", strtotime($day->format('d-m-Y')));

                $contract_id = $invoices_obj[0]['contract_id'];
                $contractobj = $contractobj->fetchAll($contractobj->select()
                    ->where("1=1")
                    ->where("contract_id=?",$contract_id)
                );
                //print_r($contractobj);
                if($invoices_obj[0]['created_on'] >  '2017-02-08 00:00:00'){
                    $contract_qty = $contractobj[0]['contract_qty'];
                }else{
                    $contract_qty = $contractobj[0]['contract_qty1'];
                }

                for($j=0;$j<sizeof($invoices_obj);$j++)
                {
                    $date=date("d-m-Y h:i:s", strtotime($invoices_obj[$j]['created_on']));
                    //$address[]=array('Date/Time'=>$row->created_on,'InvoiceId'=>$row->invoice_id,'Qty'=>$row->contract_qty,'Rate'=>$row->contract_rate,'PaidAmount'=>$row->amtPaid,'Status'=>$row->invoice_status);
                    $subarr=array('Date/Time'=>$date,'InvoiceId'=>$invoices_obj[$j]['invoice_id'],'contract_qty'=>$contract_qty,'contract_rate'=>$invoices_obj[$j]['contract_rate'],'amtPaid'=>$invoices_obj[$j]['amtPaid'],'invoice_status'=>$invoices_obj[$j]['invoice_status']);
                    array_push($arr,$subarr);
                }

                $data1=array('success'=>'success','failure'=>'','data'=>$arr);
                echo  json_encode($data1);exit;

            }


        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }

    }

}
