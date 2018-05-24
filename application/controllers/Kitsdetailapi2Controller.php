<?php
class Kitsdetailapi2Controller extends Zend_Controller_Action{

    public function init()
    {

    }
    public function indexAction()
    {
        try
        {
            //echo "fsdfdfsdf";exit;
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');

            $kits_obj=new Gbc_Model_DbTable_Gb2Kits();
            $kit_invoices_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            //$common_obj->cleanQueryParameter(($_REQUEST['username']));
            //$username=$_REQUEST['username'];
            //$username=$common_obj->cleanQueryParameter(($_REQUEST['username']));
            $username = $this->_request->getParam("username");

            $result=array();
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            /*$kit_result=$kits_obj->fetchAll($kits_obj->select()
            ->setIntegrityCheck(false)
            ->from(array('k'=>"kits"),array('k.id','k.username','k.kit_number','k.created_on','k.updated_on','k.status','k.invoice_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.kit_type','k.kit_share_status','k.kit_shared_with'))
            ->joinInner(array('k1'=>'kit_invoices'),"k.invoice_id = k1.invoice_id",array('k1.kits_qty','k1.confirmations'))
            ->where("k.username='$username' OR (k.kit_shared_with='$username')")
            ->order("k.invoice_id DESC"));*/

            $kit_result=$kits_obj->fetchAll($kits_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('k'=>"gb2_kits"),array('k.id','k.username','k.kit_number','k.created_on','k.updated_on','k.status','k.invoice_id','k.contract_id','k.kit_price','k.kit_used_by','k.kit_used_date','k.kit_type','k.kit_share_status','k.kit_shared_with'))
                ->joinInner(array('k1'=>'gb2_kit_invoices'),"k.invoice_id = k1.invoice_id",array('k1.kits_qty','k1.confirmations'))
                //->where("k.username = ? OR (k.kit_shared_with = ?)",$username)
               ->where("k.username = ? OR (k.kit_shared_with = ?) OR (k.kit_used_by = ?)",$username)
                ->order("k.invoice_id DESC"));

            $arr=array();
		//	echo "here";
		//	print_r($kit_result);
		//	exit;
            if(isset($kit_result) && sizeof($kit_result)>0)
            {
                for($i=0;$i<sizeof($kit_result);$i++)
                {
                    $subarr=array('username'=>$kit_result[$i]['username'],'confirmations'=>$kit_result[$i]['confirmations'],'kit_number'=>$kit_result[$i]['kit_number'],'created_on'=>$kit_result[$i]['created_on'],'status'=>$kit_result[$i]['status'],'contract_id' => $kit_result[$i]['contract_id'],'invoice_id'=>$kit_result[$i]['invoice_id'],'kit_price'=>$kit_result[$i]['kit_price'], 'kit_used_by'=>$kit_result[$i]['kit_used_by'],'kit_used_date'=>$kit_result[$i]['kit_used_date'],'kit_type'=>$kit_result[$i]['kit_type'],'kit_share_status'=>$kit_result[$i]['kit_share_status'],'kit_shared_with'=>$kit_result[$i]['kit_shared_with'],'updated_on'=>$kit_result[$i]['updated_on']);
                    array_push($arr,$subarr);
                }

            }
            $add=$common_obj->getBitAddr();
            if(isset($add) && (sizeof($add)>0))
            {
                if($add->static_flag==1){
                    $my_bitcoin_address=$add->bit_coin_static;
                }else{
                    $my_bitcoin_address=$common_obj->sslDec($add->bit_coin_address);
                }
            }
            else
            {
                $my_bitcoin_address='';
            }
            $db->commit();
            $data=array('success'=>'success','failure'=>'','data'=>$arr,'my_bitcoin_address'=>$my_bitcoin_address);
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {
            $db->rollBack();
            $data=array('success'=>'','failure'=>'Something went wrong! Please try again.');
            echo $e->getMessage();exit;
            echo json_encode($data);exit;
        }
    }
}

