<?php
class ManualwithdrawalsController extends Zend_Controller_Action{

    public function init()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if((!isset($authUserNamespace->user)) || (!isset($authUserNamespace->user_type)) || $authUserNamespace->user=="" || ($authUserNamespace->user_type!='admin' && $authUserNamespace->user_type!='subadmin'))$this->_redirect("/Login");

    }
    public function indexAction()
    {
        try
        {
            $this->view->title="Gainbitcoin - Manual Withdrawals";
            $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
            $misc_obj=new Gbc_Model_Custom_Miscellaneous();
            $antixss = new Gbc_Model_Custom_StringLimit();
            $user_id=$authUserNamespace->user_id;

            $data1=$misc_obj->GetAccessRightByUserId('42',$user_id);
            if(!empty($data1->view) && ($data1->view==1) || $authUserNamespace->user=='admin')
            {

            }
            else
            {
                $authUserNamespace->msg="You do not have sufficient privileges to access this area.";
                $this->_redirect("/Admindashboard");
            }

            $this->_helper->layout()->setLayout("admindashbord");//dashboard
            //if($this->_request->isPost())
            //{
            $requestwithdrawalsobj = new Gbc_Model_DbTable_Manualwithdrawal();

            $PaginateLimit=100;
            $startLimit=0;
            $manwithCountRes=$requestwithdrawalsobj->fetchRow($requestwithdrawalsobj->select()
                ->setIntegrityCheck(false)
                ->from(array('manual_withdrawal_request'),array('count(username) as count'))
                ->order("request_date desc ")
            );

            $UserCount=$manwithCountRes->count;
            $pages = ceil($UserCount/$PaginateLimit);

            $this->view->pages=$pages;

            if(!empty($_GET['page']))
            {
                $value = $_GET['page'];
                $antixss->setEncoding($_GET['page'], "UTF-8");
                if ($antixss->setFilter($value, "black", "string") == "invalidInput"){
                    $this->_redirect("/Profileerror/errormsg");

                }
                $startLimit=($_GET['page']*$PaginateLimit);
                $startLimit=$startLimit-$PaginateLimit;
            }
            else
            {
                $startLimit=0;
            }

            $result=$requestwithdrawalsobj->fetchAll($requestwithdrawalsobj->select()
                ->setIntegrityCheck(false)
                ->from(array('m'=>'manual_withdrawal_request'))
                ->joinLeft(array('w'=>'withdrawals'),"w.id = m.withdrawal_id",array('addr','transaction_id'))
                ->order("request_date desc ")
                ->limit($PaginateLimit,$startLimit)
            );
            //   echo "<pre>";print_r($result);exit;
            $this->view->result=$result;
            //	$records_per_page = $this->_request->getParam('getPageValue',10);
            //	$this->view->records_per_page = $records_per_page;


        }catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }
    }

    public function changewithdrawalstatusAction()
    {
        try{

            $misc_obj = new Gbc_Model_Custom_Miscellaneous();
            $finalbalance=new Gbc_Model_DbTable_FinalBalance();
            $common_obj = new Gbc_Model_Custom_CommonFunc();
            $ip=$misc_obj->get_client_ip();
            $requestwithdrawalsobj = new Gbc_Model_DbTable_Manualwithdrawal();
            $withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
            $manualswithdrawalsobj = new Gbc_Model_DbTable_Manualwithdrawal();
            $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

            $transaction_id=$_POST["transactionid"];
            $withdrawalsid=$_POST["withdrawalsid"];
            $username=$_POST["username"];
            $amount=$_POST["amount"];
            $walletaddress=$_POST["walletaddress"];
            $adminwallet=$_POST["adminwallet"];
            $userInfo = $misc_obj->getUserInfo($username);
            $userInfo = $userInfo->toArray();

            $balanceresult=$finalbalance->fetchRow($finalbalance->select()
                ->setIntegrityCheck(false)
                ->from(array('final_balance'))
                ->where("username= ?",$username)
            );

            if(!empty($balanceresult) && sizeof($balanceresult)>0)
            {
                $balanceamount = $balanceresult->bal_amt;
                $balanceamount=$balanceamount-$amount;

                $totalwithdrawal = $balanceresult->total_withdrawal;
                $totalwithdrawal = $totalwithdrawal+$amount;

                $withdrawalDetails["username"] = $username;
                $withdrawalDetails["wallet_addr"] = $walletaddress;
                $withdrawalDetails["btc_amt"] = $amount;
                $withdrawalDetails["status"] = 1;
                $withdrawalDetails["transaction_id"] = $transaction_id;
                $withdrawalDetails["addr"] = $adminwallet;
                $withdrawalDetails['withdrawal_type']='manual_fund_transfer';

                $observationCountTotal=$withdrawal_obj->fetchRow($withdrawal_obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('withdrawals'),array('max(id) as idcount'))
                );
                $rowCount = $observationCountTotal->idcount;

                $insert_arr=array('id'=>$rowCount+1,"chosen_coin"=>'BTC','username'=>$withdrawalDetails["username"],'btc_amt'=>$withdrawalDetails["btc_amt"],'wallet_addr'=>$withdrawalDetails["wallet_addr"],'addr'=>$withdrawalDetails["addr"],'status'=>$withdrawalDetails["status"],'withdrawal_type'=>$withdrawalDetails["withdrawal_type"],'transaction_id'=>$withdrawalDetails["transaction_id"],'timestamp'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip);
                $insert_data=$withdrawal_obj->insert($insert_arr);
                $withdrawid = $withdrawal_obj->getAdapter()->lastInsertId();

                $upd_arr=array('bal_amt'=>$balanceamount,'total_withdrawal'=>$totalwithdrawal,'updated_on'=>new Zend_Db_Expr('NOW()'));
                $update=$finalbalance->update($upd_arr,$DB->quoteInto("username = ?",$username));

                $upd_arr=array('status'=>'Processed','withdrawal_id'=>$withdrawid,'updated_date'=>new Zend_Db_Expr('NOW()'));
                $update=$requestwithdrawalsobj->update($upd_arr,$DB->quoteInto("id = ?",$withdrawalsid));

                $email = "Dear ".$username.", <br/><p>Your request for withdrawal of ".$amount." BTC has been processed via transaction id ".$transaction_id.".
				</p> <p>Best Regards, <br>Team Gainbitcoin Support</p></div>";

                $tomail = $userInfo["comm_email"];
                if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
                {
                    $tomail = $userInfo["email_address"];
                }

                $to = $tomail;
                $from = 'support@gainbitcoin.com';
                $replyTo = 'thegainbitcoinhelp@gmail.com';
                $subject = 'Withdrawal Request Processed';
                $message = $email;
                $htmlMessage = $email;
                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

                $data=array('success'=>'success','failure'=>'');
                echo json_encode($data);exit;
            }
            $data=array('success'=>'','failure'=>'failure');
            echo json_encode($data);exit;
        }
        catch(Exception $e)
        {

            echo $e->getMessage();exit;
        }
    }

    public function rejectwithdrawalAction()
    {
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $manualwithdrawal_obj = new Gbc_Model_DbTable_Manualwithdrawal();
        $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
        $withdrawals_id = $_POST["withdrawals_id"];
        $comment = $_POST["comment"];


        if(isset($withdrawals_id) && $withdrawals_id!="")
        {
            $withdrawal_data = $manualwithdrawal_obj->fetchAll($manualwithdrawal_obj->select()
                ->setIntegrityCheck(false)
                ->from(array('m'=>'manual_withdrawal_request'))
                ->where("id= ?",$withdrawals_id)
            );

            $username =  $withdrawal_data[0]->username;
            $userInfo = $misc_obj->getUserInfo($username);
            $userInfo = $userInfo->toArray();

            $arr=array('status'=>'Rejected','comment'=>"$comment",'updated_date'=>new Zend_Db_Expr('NOW()'));
            $upd=$manualwithdrawal_obj->update($arr,$DB->quoteInto("id = ?",$withdrawals_id));

            $email = "Dear ".$username.", <br/><p>Your withdrawal request for ".$withdrawal_data[0]->amount." BTC has been rejected with the following comments from the Admin :</p> 
					<p>".$comment."</p>	
					<p>In case of any issue, we request you to raise a support ticket in the system and we will revert to you within 24 hours.</p>
					<p>Best Regards, <br>Team Gainbitcoin Support</p></div>";

            $tomail = $userInfo["comm_email"];
            if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
            {
                $tomail = $userInfo["email_address"];
            }

            $to = $tomail;
            $from = 'support@gainbitcoin.com';
            $replyTo = 'thegainbitcoinhelp@gmail.com';
            $subject = 'Withdrawal Request Rejected';
            $message = $email;
            $htmlMessage = $email;
            $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

            $data=array('success'=>'success','failure'=>'');
            echo json_encode($data);exit;
        }else{
            $data=array('success'=>'','failure'=>'An error occured. Please contact your admin');
            echo json_encode($data);exit;
        }
    }

    public function uploadcsvAction(){
        $this->_helper->layout()->setLayout("admindashbord");
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $misc_obj=new Gbc_Model_Custom_Miscellaneous();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();

        $finalbalance=new Gbc_Model_DbTable_FinalBalance();

        $ip=$misc_obj->get_client_ip();
        $requestwithdrawalsobj = new Gbc_Model_DbTable_Manualwithdrawal();
        $withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
        $manualwithdrawal_obj = new Gbc_Model_DbTable_Manualwithdrawal();

        if(!empty($_POST['manual_withdrawal']))
        {

            $fileName = $_FILES['file']['name'];
            $temp_name = $_FILES['file']['tmp_name'];
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            if($ext == 'csv')
            {
                include_once dirname(dirname(__FILE__))."/models/Custom/PHPExcel/PHPExcel.php";
                include_once dirname(dirname(__FILE__))."/models/Custom/PHPExcel/PHPExcel/IOFactory.php";
                try
                {
                    $inputFileType = PHPExcel_IOFactory::identify($temp_name);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($temp_name);
                }
                catch(Exception $e)
                {
                    die('Error loading file "'.pathinfo($temp_name,PATHINFO_BASENAME).'": '.$e->getMessage());
                }

                //Create new PHPExcel object
                $objPHPExcel1 = new PHPExcel();

                //Set properties
                $objPHPExcel->getProperties()->setCreator("Gainbitcoin")
                    ->setLastModifiedBy("Gainbitcoin")
                    ->setTitle("Office 2007 XLSX Test Document")
                    ->setSubject("Office 2007 XLSX Test Document")
                    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                    ->setKeywords("office 2007 openxml php")
                    ->setCategory("Manual Withdrawal Bulk Upload");

                $sheet1=$objPHPExcel1->setActiveSheetIndex(0);

                //  Get worksheet dimensions

                $sheet =$objPHPExcel->setActiveSheetIndex(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex =PHPExcel_Cell::columnIndexFromString($highestColumn);

                //  Loop through each row of the worksheet in turn
                for ($row = 2; $row <= $highestRow; $row++){
                    //  Read a row of data into an array

                    $uname_cell = $sheet->getCellByColumnAndRow(0, $row);
                    $uname = $uname_cell->getValue();

                    $wallet_addr_cell = $sheet->getCellByColumnAndRow(1, $row);
                    $wallet_addr = $wallet_addr_cell->getValue();

                    $requested_amount_cell = $sheet->getCellByColumnAndRow(2, $row);
                    $requested_amount = $requested_amount_cell->getValue();
					
//					 $withdrawal_date_cell = $sheet->getCellByColumnAndRow(2, $row);
  //                  $withdrawal_date = $withdrawal_date_cell->getValue();



    //                $trans_type_cell = $sheet->getCellByColumnAndRow(4, $row);
      //              $trans_type = $trans_type_cell->getValue();
					
										
					 $withdrawal_date_cell = $sheet->getCellByColumnAndRow(3, 2);
                    $withdrawal_date = $withdrawal_date_cell->getValue();
				
					if(!empty($withdrawal_date)){
    
						$withdrawalDate = str_replace('"','',$withdrawal_date);
						$date = date('Y-m-d H:i:s',strtotime($withdrawalDate));
						//$dateTime = "'".$date."'";
						$withdrawal_date = $date;

					}else{
						//$dateTime = 'NOW()';
						$withdrawal_date= new Zend_Db_Expr('NOW()');
					}
			

                    $trans_type_cell = $sheet->getCellByColumnAndRow(4, 2);
                    $trans_type = $trans_type_cell->getValue();

					
                    $trans_id_cell = $sheet->getCellByColumnAndRow(5, $row);
                    $trans_id = $trans_id_cell->getValue();

                    $status_cell = $sheet->getCellByColumnAndRow(6, $row);
                    $status = $status_cell->getValue();

                    $reason_cell = $sheet->getCellByColumnAndRow(7, $row);
                    $reason = $reason_cell->getValue();

                    $uname = str_replace('"','',$uname); //username

                    $withdrawal_data = $manualwithdrawal_obj->fetchAll($manualwithdrawal_obj->select()
                        ->setIntegrityCheck(false)
                        ->from(array('m'=>'manual_withdrawal_request'))
                        ->where("username= ?",$uname)
                        ->where("amount= ?",$requested_amount)
                        ->where("status= ?","Requested")
                        ->order("id DESC")
                        ->limit ('1')
                    );
                    if(!empty($withdrawal_data) && sizeof($withdrawal_data)>0)
                    {

                        $manualwithdrawal_id = $withdrawal_data[0]->id;
                        $withdrawal_amount = $withdrawal_data[0]->amount;
                        $wallet_address = $withdrawal_data[0]->wallet_address;
                        $adminwallet=$wallet_addr;

                        $transaction_id=$trans_id;
                        $withdrawalsid=$manualwithdrawal_id;
                        $username=$uname;
                        $amount=$withdrawal_amount;
                        $walletaddress=$wallet_address;
                        $comment = $reason;


                        $userInfo = $misc_obj->getUserInfo($username);
                        $userInfo = $userInfo->toArray();

                        $balanceresult=$finalbalance->fetchRow($finalbalance->select()
                            ->setIntegrityCheck(false)
                            ->from(array('final_balance'))
                            ->where("username= ?",$username)
                        );

                        if(trim(strtolower($status))=="processed"){
                            if(!empty($balanceresult) && sizeof($balanceresult)>0)
                            {
                                $balanceamount = $balanceresult->bal_amt;
                               

                                $totalwithdrawal = $balanceresult->total_withdrawal;
                                $totalwithdrawal = $totalwithdrawal+$amount;

                                $withdrawalDetails["username"] = $username;
                                $withdrawalDetails["wallet_addr"] = $walletaddress;
                                //$withdrawalDetails["btc_amt"] = $amount;
								$withdrawalDetails["timestamp"] = $withdrawal_date;
								
								$withdrawalDetails["btc_amt"] = round($amount*2,8);
								 $balanceamount=round($balanceamount-$withdrawalDetails["btc_amt"],8);
								
								$withdrawalDetails["new_token_amt"] = $amount;
                                $withdrawalDetails["status"] = 1;
                                $withdrawalDetails["transaction_id"] = $transaction_id;
                                $withdrawalDetails["addr"] = $adminwallet;
                              //  $withdrawalDetails['withdrawal_type']='blockchain';
                                if(isset($trans_type) && $trans_type!=""){
                                    $withdrawalDetails['withdrawal_type'] = $trans_type;
                                }else{
                                    $withdrawalDetails['withdrawal_type'] = 'blockchain';
                                }

                                $observationCountTotal=$withdrawal_obj->fetchRow($withdrawal_obj->select()
                                    ->setIntegrityCheck(false)
                                    ->from(array('withdrawals'),array('max(id) as idcount'))
                                );
                                $rowCount = $observationCountTotal->idcount;


                                $insert_arr=array('id'=>$rowCount+1,"chosen_coin"=>'BTC','username'=>$withdrawalDetails["username"],'btc_amt'=>$withdrawalDetails["btc_amt"],'wallet_addr'=>$withdrawalDetails["wallet_addr"],'addr'=>$withdrawalDetails["addr"],'status'=>$withdrawalDetails["status"],'withdrawal_type'=>$withdrawalDetails["withdrawal_type"],'transaction_id'=>$withdrawalDetails["transaction_id"],'timestamp' => $withdrawalDetails["timestamp"],'created_on'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip);

								//var_dump($insert_arr);
								//exit;
                                $insert_data=$withdrawal_obj->insert($insert_arr);

                                $withdrawid = $insert_data;
								$user = str_replace('"','',$username);
						//		echo $user;
								$upd_qry=$DB->query("UPDATE `final_balance` SET `new_token_amt` = new_token_amt+".$withdrawalDetails["new_token_amt"].", `updated_on` = NOW() WHERE (username='".$user."')");
						
								
                                $upd_arr=array('bal_amt'=>$balanceamount,'total_withdrawal'=>$totalwithdrawal,'updated_on'=>new Zend_Db_Expr('NOW()'));
                                $update=$finalbalance->update($upd_arr,$DB->quoteInto("username = ?",$username));


                                $upd_arr=array('status'=>'Processed','withdrawal_id'=>$withdrawid,'updated_date'=>new Zend_Db_Expr('NOW()'));
                                $update=$requestwithdrawalsobj->update($upd_arr,$DB->quoteInto("id = ?",$withdrawalsid));

                                $email = "Dear ".$username.", <br/><p>Your request for withdrawal of ".$amount." BTC has been processed via transaction id ".$transaction_id.".
								</p> <p>Best Regards, <br>Team Gainbitcoin Support</p></div>";

                                $tomail = $userInfo["comm_email"];
                                if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
                                {
                                    $tomail = $userInfo["email_address"];
                                }
                                $to = $tomail;
                                $from = 'support@gainbitcoin.com';
                                $replyTo = 'thegainbitcoinhelp@gmail.com';
                                $subject = 'Withdrawal Request Processed';
                                $message = $email;
                                $htmlMessage = $email;
                                $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);

                            }
                        }else if(trim(strtolower($status))=="rejected"){

                            $withdrawal_data = $manualwithdrawal_obj->fetchAll($manualwithdrawal_obj->select()
                                ->setIntegrityCheck(false)
                                ->from(array('m'=>'manual_withdrawal_request'))
                                ->where("id= ?",$manualwithdrawal_id)
                            );

                            $username =  $withdrawal_data[0]->username;
                            $userInfo = $misc_obj->getUserInfo($username);
                            $userInfo = $userInfo->toArray();

                            $arr=array('status'=>'Rejected','comment'=>"$comment",'updated_date'=>new Zend_Db_Expr('NOW()'));
                            $upd=$manualwithdrawal_obj->update($arr,$DB->quoteInto("id = ?",$manualwithdrawal_id));

                            $email = "Dear ".$username.", <br/><p>Your withdrawal request for ".$withdrawal_data[0]->amount." BTC has been rejected with the following comments from the Admin :</p> 
									<p>".$comment."</p>	
									<p>In case of any issue, we request you to raise a support ticket in the system and we will revert to you within 24 hours.</p>
									<p>Best Regards, <br>Team Gainbitcoin Support</p></div>";

                            $tomail = $userInfo["comm_email"];
                            if(!isset($userInfo["comm_email"]) || $userInfo["comm_email"]=='')
                            {
                                $tomail = $userInfo["email_address"];
                            }
                            $to = $tomail;
                            $from = 'support@gainbitcoin.com';
                            $replyTo = 'thegainbitcoinhelp@gmail.com';
                            $subject = 'Withdrawal Request Rejected';
                            $message = $email;
                            $htmlMessage = $email;
                            $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                        }
                    }
                }
            }
            $this->_redirect("/Manualwithdrawals");
        }
    }
}
