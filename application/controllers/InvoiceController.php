<?php
class InvoiceController extends Zend_Controller_Action{

    public function init(){

        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

    }

    public function indexAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $contract_obj=new Gbc_Model_DbTable_Contracts();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $req_resp_obj=new Gbc_Model_DbTable_Requestresponse();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
		 $misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $kits_pay_obj=new Gbc_Model_DbTable_Kitspayment();
        $antixss = new Gbc_Model_Custom_StringLimit();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	//	print_r($_POST);
	//	exit;
       // $base="https://gainbitcoin.com/gbc";
        $base=BASE;
        $token = $common_obj->cleanQueryParameter(($_POST['token']));
        /*	if(!isset($authUserNamespace->token) || $authUserNamespace->token!=$token){
                $data=array('success'=>'','failure'=>'Invalid request found.');
                echo json_encode($data);exit;

            }*/
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
        $username=$authUserNamespace->user;
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            $this->_helper->layout()->setLayout("dashbord");
            if(trim($_POST["total_amount"])==0 && trim($_POST["real_total_amount"])==0)
            {
                $db->rollBack();
                $data=array('success'=>'','failure'=>'There are something errors!. Go back and try it again.');
                echo json_encode($data);exit;
            }

          
            if($_POST['kit_type']==11 ||$_POST['kit_type']==10 ||$_POST['kit_type']==9 ||$_POST['kit_type']==8){
				$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();

				$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
				->setIntegrityCheck(false)
				->from(array('featured_permissions'),array('value','end'))
				->where("name=?",'amaze_q_max_date'));

				//$this->view->permissions_data=$permissions_data;
		
                $ExpiryDate = date("Y-m-d h:i:s",strtotime($permissions_data['end']));
                $currentDateTime = date("Y-m-d h:i:s");
                if($currentDateTime > $ExpiryDate){
                    $db->rollBack();
                    $data=array('success'=>'','failure'=>'Kit not available.');
                    echo json_encode($data);exit;
                }
            }

            if($_POST['kit_type']==4){
                $kit_type='Silver';
                // $paid_amount = 12.5;
                // $paid_amount = 13.75;

                /* $AvailableKits = AvailableSilverKits($MaxSilverKits);
                 if($AvailableKits < 1){
                 $msg = "Kit not available";
                 printArr($msg);
                 // $redirectURL = $_SERVER["HTTP_REFERER"];
                 $redirectURL = "../view/add_kit.php";
                 print("<script data-cfasync='false'>");
                 print("var t = setTimeout(\"window.location='".$redirectURL."';\", 3000);");
                 print("</script>");
                 print("<a href=".$redirectURL.">Click here if you are not redirected automatically by your browser within 3 seconds</a>");
                 die;
                 } */
            }else if(($_POST['kit_type']==2) ||($_POST['kit_type']==7)){
                $kit_type='ROI';
            }else{
                $kit_type='Referral';
            }
			$contract_id = $_POST['kit_type'];

            $CheckKitsValue = $contract_obj->fetchRow($contract_obj->select()
                ->where("contract_id = ?",$_POST['kit_type'])
			  	->where("status =?",1)
            );
            if($CheckKitsValue->max_limit >0){

                if($CheckKitsValue->available_limit != 'max_limit' && $CheckKitsValue->available_limit == 0){
                    $db->rollBack();
                    $data=array('success'=>'','failure'=>'Kit not available.');
                    echo json_encode($data);exit;
                }
            }

//            $paid_amount = $CheckKitsValue->price_paid;
            $paid_amount = $CheckKitsValue->total_price;

            $noOfKits = $_POST["no_of_kits"];
            $KitsPrice = round($paid_amount * $noOfKits,4);
			/*
		//	$totalCost = $KitPrice * $_POST['kitNo'];
			$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
			$Price_in_usd = json_decode($Price_in_usd);
			$Price_in_usd = $Price_in_usd->bpi->USD->rate;
		//		print_r($Price_in_usd);
		//	$Price_in_usd = number_format($Price_in_usd,'4','.','');
			$Price_in_usd = str_replace(',','',$Price_in_usd);

			$KitsPrice = round(($KitsPrice/$Price_in_usd),2);
		//	echo $KitsPrice;
		//	echo $KitsPrice;
			*/

          //  if(($KitsPrice == $_POST["total_amount"]) || ($KitsPrice == $_POST["real_total_amount"] )){
                $contract_rate = $price_in_btc = $_POST["total_amount"];
				$contractPrice = $_POST["real_total_amount"];
                $realPrice = round(($_POST["total_amount"]/$noOfKits),4);
                
        /*    }else{
                $db->rollBack();
                $msg = "There is something error!. Go back and try it again.";
                $data=array('success'=>'','failure'=>'There is something error!. Go back and try it again.');
                echo json_encode($data);exit;
            }
			*/
            $shipAddress = $_POST["shippirng_text"];
            $email = $_POST["email"];
			
			/*
			 $contracts_obj=new Gbc_Model_DbTable_Contracts();
			 $contract_data = $contracts_obj->fetchRow($contracts_obj->select()
				->where("contract_id =?", $contract_id)
				->where("status =?",1)
				);
				*/
				
			$contract_data = $CheckKitsValue;
				$discount = $contract_data['discount'];
			//	echo $contract_data->__toString();
				
			$contract_rate = $price_in_btc = round(($contract_data['total_price'] * $noOfKits),4) ;
			$realPrice = round(($contract_data['total_price']),4) ;
			if($discount > 0 ){
				$realPrice -= round(($realPrice * $discount)/100,4) ;
			}
			$contractPrice = $contract_data['total_price'];


			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
			
	/*		$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value'))
			->where("name =?",'btc_conversion_value'));

			if(!empty($permissions_data)){
				$btc_conversion_value = $permissions_data['value'];
			}else{
				$btc_conversion_value = 2.345;
			}

		*/	
			
			
			
			/*    USD to BTC conversion         */
			
			$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
			if($Price_in_usd){
				$Price_in_usd = json_decode($Price_in_usd);
				$Price_in_usd = $Price_in_usd->bpi->USD->rate;
				
				$update_Price_in_usd = str_replace(',','',$Price_in_usd);
				$update_Price_in_usd = array('value' => $update_Price_in_usd);
				$udpatePrice = $permissions_obj->update($update_Price_in_usd,"name = 'btc_usd_price'");

			}else{
					$permissions_data1=$permissions_obj->fetchRow($permissions_obj->select()
						->setIntegrityCheck(false)
						->from(array('featured_permissions'),array('name','value'))
						->where("name =?",'btc_usd_price'));

						if(!empty($permissions_data1)){
							$Price_in_usd = $permissions_data1['value'];
						}else{
							$Price_in_usd = 0;
						}
			}
			$Price_in_usd = str_replace(',','',$Price_in_usd);
			$totalAmount = round(($realPrice/$Price_in_usd),4);
	//		$totalAmount += round((($totalAmount*$btc_conversion_value)/100),4);
		//	if($discount > 0){
			//	$contract_rate = round(($contract_rate/$Price_in_usd),4);
	//		}else{
				$contract_rate = round(($totalAmount*$noOfKits),4);
		//	}
	//		$contract_rate += round((($contract_rate*$btc_conversion_value)/100),4);
			$contractPrice = round(($contractPrice/$Price_in_usd),4);
	//		$contractPrice += round((($contractPrice*$btc_conversion_value)/100),4);
						
			$price_in_btc = $contract_rate;
			$realPrice = $totalAmount;
			
			
			/*    USD to BTC conversion         */
			
						
			
			if(isset($_POST['type']) && ($_POST['type'] == "kit_generation")){
				
				if($_POST['kit_type'] == 13){
					$data=array('success'=>'','failure'=>'Purchase of "Power of One" kit via GB wallet balance is not allowed');
						 echo json_encode($data);exit;
				}
			
				$final_balance_obj=new Gbc_Model_DbTable_FinalBalance();
				$BalanceDetail=$final_balance_obj->fetchRow($final_balance_obj->select()
				->setIntegrityCheck(false)
				->from(array('f'=>'final_balance'),array('bal_amt'))
				->joinLeft(array('m'=>'manual_withdrawal_request'),"m.username = f.username and m.status = 'Requested'",array('amount'))
				->where("f.username =? ",$username)
				);
		

				$BalanceAmt['bal_amt'] = round(($BalanceDetail['bal_amt'] - $BalanceDetail['amount']),8);
		
				if(!empty($BalanceAmt["bal_amt"])){
					$PayableAmt = round(($noOfKits * $paid_amount),8);

					if(($_POST["total_amount"] <= doubleval($BalanceAmt["bal_amt"])) && ($_POST["total_amount"] == $PayableAmt)){
						
						$invoiceArray=$common_obj->createInvoiceForKit($contract_rate, $username, 1, $shipAddress, $email, $noOfKits);
						if(!empty($invoiceArray))
							{
								$invoiceId = $invoiceArray;
								$userNameArra =$common_obj-> createKits($invoiceId, $noOfKits, $username, $realPrice, $kit_type,'Active', $contract_id);
								$updateCommentArray = array("comment" => "kit_generation by $username","payment_gateway" => "0","updated_on" => new Zend_Db_Expr('NOW()') );
								$update_data=$kits_obj->update($updateCommentArray,$DB->quoteInto("invoice_id=?",$invoiceId));
								$updateCommentArray = array("comment" => "auto kit_generation by $username", "updated_on" => new Zend_Db_Expr('NOW()') );
								$update_data=$kit_invoices_obj->update($updateCommentArray,$DB->quoteInto("invoice_id=?",$invoiceId));
							}
							else
							{
								$db->rollBack();
								$msg = "Error creating invoice.";
								$data=array('success'=>'','failure'=>$msg);
								echo json_encode($data);exit;
							}
						$ip=$misc_obj->get_client_ip();
						 $withdrawalDetails["transaction_id"] = "GBCW-".$authUserNamespace->user."-".time();
						$withdrawalDetails["username"] = $authUserNamespace->user;
						$withdrawalDetails["chosen_coin"] = "BTC";
						$withdrawalDetails["btc_amt"] = $_POST["total_amount"];
					//	$withdrawalDetails["new_token_amt"] = $_POST["total_amount"];
						$withdrawalDetails["status"] = 1;
						$withdrawalDetails['withdrawal_type']='kit_generation';
						
						 $insert_arr=array("chosen_coin"=>$withdrawalDetails["chosen_coin"],'username'=>$withdrawalDetails["username"],'btc_amt'=>$withdrawalDetails["btc_amt"],'status'=>$withdrawalDetails["status"],'withdrawal_type'=>$withdrawalDetails["withdrawal_type"],'transaction_id'=>$withdrawalDetails["transaction_id"],'timestamp'=>new Zend_Db_Expr('NOW()'),'created_on'=>new Zend_Db_Expr('NOW()'),'ip_address'=>$ip);
               			 $insert_data=$withdrawal_obj->insert($insert_arr);
						
						//$upd_qry=$db->query("UPDATE `final_balance` SET `new_token_amt` = new_token_amt+".$withdrawalDetails["new_token_amt"].", `updated_on` = NOW() WHERE (username='".$username."')");
						
						 $UpdateFinalLedger = $common_obj-> UpdateFinalLedger($withdrawalDetails, $username);
						 $db->commit();
						
						
						$email = "<div style='padding: 10px; margin: 10px;'><div style='padding: 0px;'>
						<img src='".BASE."/images/GainBTCLogo_large.png' style='width: 30%; min-width:300px' /></div>
						<p>Dear User,</p>
						<p>
						As per the request generated by you from the system, the kits of amount ".number_format($withdrawalDetails["btc_amt"],8)." BTC has been generated in your account & the balance for the same has been deducted from your GainBitcoin wallet.
						</p>
						<p>
						Thanks & Regards,<br>
						Team GainBitcoin
						</p>";				
						
					$userInfo = $misc_obj->getUserInfo($username);
                	$userInfo=$userInfo->toArray();
					$to = $userInfo['comm_email'];
					$from = 'support@gainbitcoin.com';
					$replyTo = 'thegainbitcoinhelp@gmail.com';
					$subject = 'GainBitCoin: Kit generated';
					$message = $email;
					$htmlMessage = $email;
					$sendMail = $common_obj-> sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
						
						  $data=array('success'=>'success','failure'=>'');
                  		  echo json_encode($data);exit;
					}else{
						$data=array('success'=>'','failure'=>'Your GBC wallet balance is less than the requested kits amount');
						 echo json_encode($data);exit;
					}
				}else{
						$data=array('success'=>'','failure'=>'Your GBC wallet balance is not greater than the minimum kit amount');
						 echo json_encode($data);exit;
					}
			
		}else {
                //$invoiceArray = createInvoiceForKit($conn, $contract_rate, $username, 0, $shipAddress, $email, $noOfKits);
				
				
				$invoiceArray=$common_obj->createInvoiceForKit($contract_rate, $username, 0, $shipAddress, $email, $noOfKits);
						if(!empty($invoiceArray))
							{
								$invoiceId = $invoiceArray;
								$userNameArra =$common_obj-> createKits($invoiceId, $noOfKits, $username, $realPrice, $contractPrice, $kit_type,  '', $contract_id);
						
                    $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $kitsresult= $DB->query("select created_on from kits where invoice_id = '$invoiceId'");
                    $kitdata = $kitsresult->fetchAll();
                    if(!empty($kitdata)){
                        $kitCreated = $kitdata[0]['created_on'];
                    }

                    $diff = strtotime(date('Y-m-d H:i:s'))- strtotime($kitCreated);

                    $maxtime = 18000;
                    $remaining = $maxtime - $diff;
                    $remaining = floor($remaining);


                }
                else
                {
                    $db->rollBack();
                    $msg = "Error creating invoice.";
                    $data=array('success'=>'','failure'=>$msg);
                    echo json_encode($data);exit;
                }
          

            $add=$common_obj->getBitAddr();
            if(!empty($add) && ($add->static_flag==1))
            {
                $my_bitcoin_address=$add->bit_coin_static;
            }
            else{
                $my_bitcoin_address=$common_obj->sslDec($add->bit_coin_address);
            }
            $payment_mode = $add->payment_mode;
            $payment_gateway = $add->payment_gateway;

            $BasePaymentUrl = "https://api.paybitz.com";
            //echo "$BasePaymentUrl/merchant/invoice/";

            //	echo "inside paybitz";
            $ch = curl_init();
            //curl_setopt($ch, CURLOPT_URL, "http://139.59.16.154:7860/merchant/invoice/");
            curl_setopt($ch, CURLOPT_URL, "$BasePaymentUrl/merchant/invoice/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //$data = array("merchant_id" => "58ae9380a13b5bea055cb86f",
            $data = array("merchant_id" => "58c11d4ad6b43120b5997c00",
                "amount" => $price_in_btc,// in BTC
                //"redirect_url" => "http://www.google.com",
                "redirect_url" => BASE."/thankyou/index.php",

                //"notify_url" => "http://localhost/PayBitzPHP-SDK/notify.php",
                "notify_url" => "$base/Invoicestatusapi",
                "required_confirmations" => "3",
                "order_id"=> $invoiceId,
                "username"=> $username);
            $data_string = json_encode($data);

            curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: Token IdJtt10Q7efe3Q4O1vcFhZeR"
            ));

            $response = json_decode(curl_exec($ch));
            curl_close($ch);
            //var_dump($response);
            //header("Location: $response->payment_url");
			
            $url = $response->data->payment_url;
            $inputAddress = $response->data->address;
            if(is_null($inputAddress)){
                $inputAddress = $my_bitcoin_address;
            }
            $payment_gateway = '2';
            //	$url = '';

            //echo "iside if";exit;
		/*		
            if((($payment_gateway == '1') || (empty($url))) && ($price_in_btc <=15)){
                $payment_gateway = '1';
                //echo "inside else";exit;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://www.bitcoinpay.com/api/v1/payment/btc");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);

                curl_setopt($ch, CURLOPT_POST, TRUE);

                curl_setopt($ch, CURLOPT_POSTFIELDS, "{
					  \"settled_currency\": \"BTC\",
					  \"return_url\": \"$base/Invoicestatusapi?invoice_id=$invoiceId\",
					  \"notify_url\": \"$base/Invoicestatusapi?invoice_id=$invoiceId\",
					  \"notify_email\": \"thegainbitcoinhelp@gmail.com\",
					  \"price\": $price_in_btc,
					  \"currency\": \"BTC\",
					  \"reference\": {
						\"invoice-id\": \"$invoiceId\",
						\"username\": \"$username\"

					  },
					  \"item\": \"Invoice #$invoiceId\",
					  \"description\": \"Please send \"
					}");

                //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: Token IzykjQHl8Kv9IlgbPSZzQLRI"
                ));

                $Response = curl_exec($ch);
                curl_close($ch);

                // $check_payment_status = json_decode($response);
                // $payment_url = $Response->data->payment_url; // echo $payment_url; //die;
                // $payment_id = $check_payment_status->data->payment_id;
                // $paid_amount_in_btc = $check_payment_status->data->paid_amount;
                // $address_id = $check_payment_status->data->address;

                // print_r($Response);
                $check_payment_status = json_decode($Response);
                // var_dump($check_payment_status);
                $payment_url = $check_payment_status->data->payment_url;
                $payment_id = $check_payment_status->data->payment_id;

                $inputAddress = $check_payment_status->data->address;
                if(is_null($inputAddress)){
                    $inputAddress = $my_bitcoin_address;
                }

                $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=bitcoin:".$inputAddress."?amount=".$price_in_btc."&choe=UTF-8";
            }
*/
            if (!empty($url)){

                $ExistingPaymentId =$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('kv' =>'kit_invoices'),array('kv.transactionid'))
                    ->where("invoice_id=?",$invoiceId)
                    ->where("transactionid != ?",''));

                //$update=date('Y-m-d h:i:s');
                // var_dump($ExistingPaymentId);
                if(!isset($ExistingPaymentId) || empty($ExistingPaymentId) || sizeof($ExistingPaymentId)<=0){

                    $update_arr=array('middleAddr'=>$inputAddress ,'transactionid'=>$payment_id,'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $update_data=$kit_invoices_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));


                }else{

                    //echo "here";exit;
                    $insrt_arr=array('invoice_id'=>$invoiceId ,'response'=>$Response);
                    $insrt_data=$req_resp_obj->insert($insrt_arr);



                }
                //  $updat_arr=array('status'=>'Pending','updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_arr=array('status'=>'Pending','payment_gateway'=>$payment_gateway,'updated_on'=>new Zend_Db_Expr('NOW()'));

                $updat_data=$kits_obj->update($updat_arr,$DB->quoteInto("invoice_id= ?",$invoiceId));

                /*
                      // echo $query1;
                      // var_dump($result);
                      $email = "<div>Buy Now has been clicked for the following invoice id: ".$invoiceId.". Coins will be sent to: ".$inputAddress." by the customer.</div>";
                      //$common_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "Buy Now Clicked", $email);
                      $to = "thegainbitcoin@gmail.com";
                      $from = 'admin@gainbitco.in';
                      $replyTo = 'thegainbitcoinhelp@gmail.com';
                      $subject = 'Buy Now Clicked';
                      $message = $email;
                      $htmlMessage = $email;
                      $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                */

                $db->commit();

                //$data=array('input_address'=>$inputAddress,'payment_mode'=>$payment_mode,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>$url,'created'=>$KitCreated,'remaining'=>$remaining);

                $data=array('input_address'=>$inputAddress,'payment_mode'=>$payment_mode,'payment_gateway'=>$payment_gateway,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>$url,'created'=>$KitCreated,'remaining'=>$remaining);


                $arr=array('success'=>'success','failure'=>'','data'=>$data);
                $authUserNamespace->kit_type=$CheckKitsValue->contract_type;
                echo json_encode($data);exit;
            }

            //var_dump($url);
            //exit;
           // if((($payment_mode =="static") || ($price_in_btc >15)) && (empty($url))){
            if(empty($url)){
                //echo "iside if";exit;
			/*
                $inputAddress = $my_bitcoin_address;
                $update=date('Y-m-d h:i:s');
                $payment_mode = "static";

                $upd_arr=array('payment_mode'=>$payment_mode,'created'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data=$kits_pay_obj->update($upd_arr,$DB->quoteInto("invoice_id=?",$invoiceId));



                $updat_arr=array('middleAddr'=>$inputAddress,'creared_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kit_invoices_obj->update($updat_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

                $update_arr=array('status'=>'Pending','created_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                //$update_data=$kits_obj->update($update_arr,"invoice_id='".$invoiceId."'");
                $update_data=$kits_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

                $data=array('payment_mode'=>$payment_mode,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>'');
                $arr=array('success'=>'success','failure'=>'','data'=>$data);
                $db->commit();
                $authUserNamespace->kit_type=$CheckKitsValue->contract_type;
                echo json_encode($data);exit;
			*/
				  $db->rollBack();
				 $data=array('success'=>'','failure'=>"There seems to be server problem, please try after 5 minutes");
            echo json_encode($data);exit;
            }


			}


        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg = "The system behaved unexpectedly, Please try again";
		//	$msg = $e->getmessage();
            //$data=array('success'=>'','failure'=>$msg);
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }
	
	
	
	 public function mcapAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $username=$authUserNamespace->user;
		 $contract_obj=new Gbc_Model_DbTable_Contracts();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $req_resp_obj=new Gbc_Model_DbTable_Requestresponse();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
		 $misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $kits_pay_obj=new Gbc_Model_DbTable_Kitspayment();
        $antixss = new Gbc_Model_Custom_StringLimit();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
	//	print_r($_POST);
	//	exit;
       // $base="https://gainbitcoin.com/gbc";
        $base=BASE;
        $token = $common_obj->cleanQueryParameter(($_POST['token']));
       
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
       
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            $this->_helper->layout()->setLayout("dashbord");
            if(trim($_POST["total_amount"])==0 && trim($_POST["real_total_amount"])==0)
            {
                $db->rollBack();
                $data=array('success'=>'','failure'=>'There are something errors!. Go back and try it again.');
                echo json_encode($data);exit;
            }


			$kit_type='Referral';
           	$contract_id = $_POST['kit_type'];

            $CheckKitsValue = $contract_obj->fetchRow($contract_obj->select()
                ->where("contract_id = ?",$_POST['kit_type'])
				->where("status =?",1)
            );
            if($CheckKitsValue->max_limit >0){

                if($CheckKitsValue->available_limit != 'max_limit' && $CheckKitsValue->available_limit == 0){
                    $db->rollBack();
                    $data=array('success'=>'','failure'=>'Kit not available.');
                    echo json_encode($data);exit;
                }
            }
            $paid_amount = $CheckKitsValue->total_price;
			$noOfKits = $_POST["no_of_kits"];
            $KitsPrice = round($paid_amount * $noOfKits,4);
		
			$contract_rate = $price_in_btc = $_POST["total_amount"];
			$contractPrice = $_POST["real_total_amount"];
			$realPrice = round(($_POST["total_amount"]/$noOfKits),4);
            /*
            $shipAddress = $_POST["shippirng_text"];
            $email = $_POST["email"];
						
			 $contracts_obj=new Gbc_Model_DbTable_Contracts();
			 $contract_data = $contracts_obj->fetchRow($contracts_obj->select()
				->where("contract_id =?", trim($_POST['contract_id']))
				->where("status =?",1)
				);
			$discount = $contract_data['discount'];
*/
			
			$contract_data = $CheckKitsValue;
				$discount = $contract_data['discount'];
			//	echo $contract_data->__toString();
				
			$contract_rate = $price_in_btc = round(($contract_data['total_price'] * $noOfKits),4) ;
			$realPrice = round(($contract_data['total_price']),4) ;
			if($discount > 0 ){
				$realPrice -= round(($realPrice * $discount)/100,4) ;
			}
			$contractPrice = $contract_data['total_price'];


			$permissions_obj=new Gbc_Model_DbTable_FeaturedPermission();
		
		/*	$permissions_data=$permissions_obj->fetchRow($permissions_obj->select()
			->setIntegrityCheck(false)
			->from(array('featured_permissions'),array('name','value'))
			->where("name =?",'btc_conversion_value'));

			if(!empty($permissions_data)){
				$btc_conversion_value = $permissions_data['value'];
			}else{
				$btc_conversion_value = 2.345;
			}

		*/	
			
			/*    USD to BTC conversion         */
			
			$Price_in_usd = file_get_contents("http://api.coindesk.com/v1/bpi/currentprice.json");
			if($Price_in_usd){
				$Price_in_usd = json_decode($Price_in_usd);
				$Price_in_usd = $Price_in_usd->bpi->USD->rate;

				$update_Price_in_usd = str_replace(',','',$Price_in_usd);
				$update_Price_in_usd = array('value' => $update_Price_in_usd, 'updated_on'=>new Zend_Db_Expr('NOW()'));
				$udpatePrice = $permissions_obj->update($update_Price_in_usd,"name = 'btc_usd_price'");

			}else{
					$permissions_data1=$permissions_obj->fetchRow($permissions_obj->select()
						->setIntegrityCheck(false)
						->from(array('featured_permissions'),array('name','value'))
						->where("name =?",'btc_usd_price'));

						if(!empty($permissions_data1)){
							$Price_in_usd = $permissions_data1['value'];
						}else{
							$Price_in_usd = 0;
						}
			}
			$Price_in_usd = str_replace(',','',$Price_in_usd);
			$totalAmount = round(($realPrice/$Price_in_usd),4);
	//		$totalAmount += round((($totalAmount*$btc_conversion_value)/100),4);
			$contract_rate = round(($totalAmount*$noOfKits),4);
	//		$contract_rate = round(($contract_rate/$Price_in_usd),4);
//			$contract_rate += round((($contract_rate*$btc_conversion_value)/100),4);
			$contractPrice = round(($contractPrice/$Price_in_usd),4);
	//		$contractPrice += round((($contractPrice*$btc_conversion_value)/100),4);
						
			$price_in_btc = $contract_rate;
			$realPrice = $totalAmount;
			
			
			
			$McapPrice_in_BTC = file_get_contents("https://bitcoingrowthfund.com/api/market_states/mcap/btc");
			if($McapPrice_in_BTC){
				$McapPrice_in_BTC = json_decode($McapPrice_in_BTC);
				$McapPrice_in_BTC = $McapPrice_in_BTC->last_price;
				
				
				$update_McapPrice_in_BTC = array('value' => $McapPrice_in_BTC,'updated_on'=>new Zend_Db_Expr('NOW()') );
				$udpatePrice = $permissions_obj->update($update_McapPrice_in_BTC,"name = 'mcap_btc_price'");
			}else{
				
				$permissions_data2=$permissions_obj->fetchRow($permissions_obj->select()
					->setIntegrityCheck(false)
					->from(array('featured_permissions'),array('name','value'))
					->where("name =?",'mcap_btc_price'));

					if(!empty($permissions_data2)){
						$mcap_btc_price = $permissions_data2['value'];
					}else{
						$mcap_btc_price = 0;
					}
			//	$mcap_btc_price = 0;
				$McapPrice_in_BTC = $mcap_btc_price;
			}
			
		//	var_dump($McapPrice_in_BTC);
		//	var_dump($contract_rate);
			
				$realPriceInMcap = round(($totalAmount/$McapPrice_in_BTC),4);
				$contract_rateInMcap = round(($contract_rate/$McapPrice_in_BTC),4);
			
		//	echo $realPriceInMcap;
			/*    USD to BTC conversion         */
			
			if($contract_rateInMcap <= 0){
			 	$data=array('success'=>'','failure'=>"There seems to be problem, please try again");
            	echo json_encode($data);exit;
			}
				
				$invoiceArray=$common_obj->createInvoiceForKit($contract_rate, $username, 0, $shipAddress, $email, $noOfKits,$contract_rateInMcap);
						if(!empty($invoiceArray))
							{
								$invoiceId = $invoiceArray;
								$userNameArra =$common_obj-> createKits($invoiceId, $noOfKits, $username, $realPrice, $contractPrice, $kit_type,  '', $contract_id, $realPriceInMcap);
						
                    $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $kitsresult= $DB->query("select created_on from kits where invoice_id = '$invoiceId'");
                    $kitdata = $kitsresult->fetchAll();
                    if(!empty($kitdata)){
                        $kitCreated = $kitdata[0]['created_on'];
                    }

                    $diff = strtotime(date('Y-m-d H:i:s'))- strtotime($kitCreated);

                    $maxtime = 1800;
                  //  $maxtime = 60;
                    $remaining = $maxtime - $diff;
                    $remaining = floor($remaining);


                }
                else
                {
                    $db->rollBack();
                    $msg = "Error creating invoice.";
                    $data=array('success'=>'','failure'=>$msg);
                    echo json_encode($data);exit;
                }
          

       
            $payment_gateway = '3';
            //	$url = '';

            //echo "iside if";exit;
				
          $data = array('username' => $username, 'invoice_id' => $invoiceId, 'invoice_amount' => $contract_rateInMcap );

		//	var_dump($data);
			  $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/addgb");
            	 //   curl_setopt($ch, CURLOPT_URL, "http://localhost/test.php");
               
                curl_setopt($ch, CURLOPT_POST, TRUE);
  				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                $Response = curl_exec($ch);
                curl_close($ch);
			
				$Res = json_decode($Response);
		//	print_r($Res);
			    $inputAddress = $Res->address;
                
                $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=mcap:".$inputAddress."?amount=".$contract_rateInMcap."&choe=UTF-8";
          
		//	echo $url;
            if (!empty($url)){

                $ExistingPaymentId =$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('kv' =>'kit_invoices'),array('kv.transactionid'))
                    ->where("invoice_id=?",$invoiceId)
                    ->where("transactionid != ?",''));

                //$update=date('Y-m-d h:i:s');
                // var_dump($ExistingPaymentId);
                if(!isset($ExistingPaymentId) || empty($ExistingPaymentId) || sizeof($ExistingPaymentId)<=0){

                    $update_arr=array('middleAddr'=>$inputAddress ,'transactionid'=>$payment_id,'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $update_data=$kit_invoices_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));


                }else{

                    //echo "here";exit;
                    $insrt_arr=array('invoice_id'=>$invoiceId ,'response'=>$Response);
                    $insrt_data=$req_resp_obj->insert($insrt_arr);



                }
                //  $updat_arr=array('status'=>'Pending','updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_arr=array('status'=>'Pending','payment_gateway'=>$payment_gateway,'updated_on'=>new Zend_Db_Expr('NOW()'));

                $updat_data=$kits_obj->update($updat_arr,$DB->quoteInto("invoice_id= ?",$invoiceId));

                /*
                      // echo $query1;
                      // var_dump($result);
                      $email = "<div>Buy Now has been clicked for the following invoice id: ".$invoiceId.". Coins will be sent to: ".$inputAddress." by the customer.</div>";
                      //$common_obj->sendMail("thegainbitcoin@gmail.com", "admin@gainbitco.in", "Buy Now Clicked", $email);
                      $to = "thegainbitcoin@gmail.com";
                      $from = 'admin@gainbitco.in';
                      $replyTo = 'thegainbitcoinhelp@gmail.com';
                      $subject = 'Buy Now Clicked';
                      $message = $email;
                      $htmlMessage = $email;
                      $sendMail = $common_obj->sendMailviaAPI($to,$from,$replyTo,$subject,$message,$htmlMessage);
                */

                $db->commit();

                //$data=array('input_address'=>$inputAddress,'payment_mode'=>$payment_mode,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>$url,'created'=>$KitCreated,'remaining'=>$remaining);

                $data=array('input_address'=>$inputAddress,'payment_mode'=>$payment_mode,'payment_gateway'=>$payment_gateway,'price_in_btc'=>$contract_rateInMcap,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>$url,'created'=>$KitCreated,'remaining'=>$remaining);


                $arr=array('success'=>'success','failure'=>'','data'=>$data);
                $authUserNamespace->kit_type=$CheckKitsValue->contract_type;
                echo json_encode($data);exit;
            }

            //var_dump($url);
            //exit;
           // if((($payment_mode =="static") || ($price_in_btc >15)) && (empty($url))){
            if(empty($url)){
                //echo "iside if";exit;
			/*
                $inputAddress = $my_bitcoin_address;
                $update=date('Y-m-d h:i:s');
                $payment_mode = "static";

                $upd_arr=array('payment_mode'=>$payment_mode,'created'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $upd_data=$kits_pay_obj->update($upd_arr,$DB->quoteInto("invoice_id=?",$invoiceId));



                $updat_arr=array('middleAddr'=>$inputAddress,'creared_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_data=$kit_invoices_obj->update($updat_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

                $update_arr=array('status'=>'Pending','created_on'=>new Zend_Db_Expr('NOW()'),'updated_on'=>new Zend_Db_Expr('NOW()'));
                //$update_data=$kits_obj->update($update_arr,"invoice_id='".$invoiceId."'");
                $update_data=$kits_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));

                $data=array('payment_mode'=>$payment_mode,'price_in_btc'=>$price_in_btc,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>'');
                $arr=array('success'=>'success','failure'=>'','data'=>$data);
                $db->commit();
                $authUserNamespace->kit_type=$CheckKitsValue->contract_type;
                echo json_encode($data);exit;
			*/
				  $db->rollBack();
				 $data=array('success'=>'','failure'=>"There seems to be server problem, please try after 5 minutes");
            echo json_encode($data);exit;
            }


			


        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg = "The system behaved unexpectedly, Please try again";
            //$data=array('success'=>'','failure'=>$msg);
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }
	
	
	
	
	 public function miningkitsAction(){
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $username=$authUserNamespace->user;
		 $contract_obj=new Gbc_Model_DbTable_Gb2Contracts();
        $kit_invoices_obj=new Gbc_Model_DbTable_Gb2Kitinvoices();
        $req_resp_obj=new Gbc_Model_DbTable_Requestresponse();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
		 $misc_obj = new Gbc_Model_Custom_Miscellaneous();
		$withdrawal_obj = new Gbc_Model_DbTable_Withdrawals();
        $kits_obj=new Gbc_Model_DbTable_Gb2Kits();
		 $user_obj = new Gbc_Model_DbTable_Userinfo();
        $kits_pay_obj=new Gbc_Model_DbTable_Kitspayment();
        $antixss = new Gbc_Model_Custom_StringLimit();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		
       // $base="https://gainbitcoin.com/gbc";
        $base=BASE;
        $token = $common_obj->cleanQueryParameter(($_POST['token']));
       
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
       
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
            $this->_helper->layout()->setLayout("dashbord");
       /*     if(trim($_POST["total_amount"])==0 && trim($_POST["real_total_amount"])==0)
            {
                $db->rollBack();
                $data=array('success'=>'','failure'=>'There are something errors!. Go back and try it again.');
                echo json_encode($data);exit;
            }
		*/
			
			
			  if(empty($_POST['no_of_kits']) || empty($_POST['kit_type'])){
					$data=array('success'=>'','failure'=>'All fields required');
					echo json_encode($data);exit;
			  }

		  if(!empty($_POST['referer_user'])){
			  $userInfo_chk = $user_obj->fetchRow($user_obj->select()
				->where("binaryUser=?",'1')
				->where("username=?",trim($_POST["referer_user"])));

			  
			  if(($_POST['referer_user'] == $username) || (sizeof($userInfo_chk)<=0)){
				$data=array('success'=>'','failure'=>'Please enter valid Gainbitcoin username');
                echo json_encode($data);exit;
			  }
			  
			 // exit;
		  }


			$kit_type='ROI';
           	$contract_id = $_POST['kit_type'];

            $CheckKitsValue = $contract_obj->fetchRow($contract_obj->select()
                ->where("contract_id = ?",$_POST['kit_type'])
				->where("status =?",1)
            );
          
            $from_price = $CheckKitsValue->from_price;
            $to_price = $CheckKitsValue->to_price;
			$contract_price = rand($from_price,$to_price);
			$noOfKits = $_POST["no_of_kits"];
			$referer_user = $_POST["referer_user"];
            $KitsPrice = round($paid_amount * $noOfKits,4);
		
			
			function rand_float($st_num=0,$end_num=1,$mul=1000000)
			{
				if ($st_num>$end_num) return false;
				return mt_rand($st_num*$mul,$end_num*$mul)/$mul;
			}
			$randomPrice = rand_float($from_price,$to_price);
			$contract_rate =  round(( $randomPrice * $noOfKits),4);
			 $realPrice = $contractPrice = round($randomPrice,4);
			
			/*
			$contract_data = $CheckKitsValue;
				$discount = $contract_data['discount'];
			//	echo $contract_data->__toString();
				
			$contract_rate = $price_in_btc = round(($contract_data['total_price'] * $noOfKits),4) ;
			$realPrice = round(($contract_data['total_price']),4) ;
			if($discount > 0 ){
				$realPrice -= round(($realPrice * $discount)/100,4) ;
			}
			$contractPrice = $contract_data['total_price'];
			$contract_rate -= round(($contract_rate * $discount)/100,4) ;
			*/

			
			if($contract_rate <= 0){
			 	$data=array('success'=>'','failure'=>"There seems to be problem, please try again");
            	echo json_encode($data);exit;
			}
				
				$invoiceArray=$common_obj->createInvoiceForKit_gb2($contract_rate, $username, 0, $shipAddress, $email, $noOfKits,$referer_user);
						if(!empty($invoiceArray))
							{
								$invoiceId = $invoiceArray;
								$userNameArra =$common_obj-> createKits_gb2($invoiceId, $noOfKits, $username, $realPrice, $contractPrice, $kit_type,  '', $contract_id);
						
                    $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $kitsresult= $DB->query("select created_on from gb2_kits where invoice_id = '$invoiceId'");
                    $kitdata = $kitsresult->fetchAll();
                    if(!empty($kitdata)){
                        $kitCreated = $kitdata[0]['created_on'];
                    }

                    $diff = strtotime(date('Y-m-d H:i:s'))- strtotime($kitCreated);

                    $maxtime = 1800;
                  //  $maxtime = 60;
                    $remaining = $maxtime - $diff;
                    $remaining = floor($remaining);


                }
                else
                {
                    $db->rollBack();
                    $msg = "Error creating invoice.";
                    $data=array('success'=>'','failure'=>$msg);
                    echo json_encode($data);exit;
                }
          

       
            $payment_gateway = '3';
          
          $data = array('username' => $username, 'invoice_id' => $invoiceId, 'invoice_amount' => $contract_rate );

		//	var_dump($data);
			  $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://mcapwallet.io/api/addgb");
            	 //   curl_setopt($ch, CURLOPT_URL, "http://localhost/test.php");
               
                curl_setopt($ch, CURLOPT_POST, TRUE);
  				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                $Response = curl_exec($ch);
                curl_close($ch);
			
				$Res = json_decode($Response);
		//	print_r($Res);
			    $inputAddress = $Res->address;
                
                $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=mcap:".$inputAddress."?amount=".$contract_rate."&choe=UTF-8";
          
		//	echo $url;
            if (!empty($url)){

                $ExistingPaymentId =$kit_invoices_obj->fetchRow($kit_invoices_obj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('kv' =>'kit_invoices'),array('kv.transactionid'))
                    ->where("invoice_id=?",$invoiceId)
                    ->where("transactionid != ?",''));

                //$update=date('Y-m-d h:i:s');
                // var_dump($ExistingPaymentId);
                if(!isset($ExistingPaymentId) || empty($ExistingPaymentId) || sizeof($ExistingPaymentId)<=0){

                    $update_arr=array('middleAddr'=>$inputAddress ,'transactionid'=>$payment_id,'updated_on'=>new Zend_Db_Expr('NOW()'));
                    $update_data=$kit_invoices_obj->update($update_arr,$DB->quoteInto("invoice_id=?",$invoiceId));


                }else{

                    //echo "here";exit;
                    $insrt_arr=array('invoice_id'=>$invoiceId ,'response'=>$Response);
                    $insrt_data=$req_resp_obj->insert($insrt_arr);



                }
                //  $updat_arr=array('status'=>'Pending','updated_on'=>new Zend_Db_Expr('NOW()'));
                $updat_arr=array('status'=>'Pending','payment_gateway'=>$payment_gateway,'updated_on'=>new Zend_Db_Expr('NOW()'));

                $updat_data=$kits_obj->update($updat_arr,$DB->quoteInto("invoice_id= ?",$invoiceId));

                $db->commit();
             $data=array('input_address'=>$inputAddress,'payment_mode'=>$payment_mode,'payment_gateway'=>$payment_gateway,'price_in_btc'=>$contract_rate,'invoiceId'=>$invoiceId,'noOfKits'=>$noOfKits,'payment_url'=>$url,'created'=>$KitCreated,'remaining'=>$remaining);


                $arr=array('success'=>'success','failure'=>'','data'=>$data);
                $authUserNamespace->kit_type=$CheckKitsValue->contract_type;
                echo json_encode($data);exit;
            }

            if(empty($url)){
            
				  $db->rollBack();
				 $data=array('success'=>'','failure'=>"There seems to be server problem, please try after 5 minutes");
            echo json_encode($data);exit;
            }

        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg = "The system behaved unexpectedly, Please try again";
			$msg = $e->getMessage();
            //$data=array('success'=>'','failure'=>$msg);
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }
	
	
	
	
	 public function purchasekitAction(){
        $authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
        $contract_obj=new Gbc_Model_DbTable_Contracts();
        $kit_invoices_obj=new Gbc_Model_DbTable_Kitinvoices();
        $common_obj = new Gbc_Model_Custom_CommonFunc();
		 $misc_obj = new Gbc_Model_Custom_Miscellaneous();
        $kits_obj=new Gbc_Model_DbTable_Kits();
        $antixss = new Gbc_Model_Custom_StringLimit();
		 $adminsetting_obj=new Gbc_Model_DbTable_Adminsetting();
        $DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		 $loggedIn_user = $authUserNamespace->user;
 		$username = "om2";
               
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
      
        try
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->beginTransaction();
			  if(!empty($_POST) && ($_POST['kit_type']) && ($_POST['total_amount']) && ($_POST['real_total_amount']) && ($_POST['comment']) && ($_POST['password'])){
				  
				  $pass=(strip_tags($_POST['password']));

					// $Sql_adm="SELECT * from admin_setting";
					$resultadminsetting=$adminsetting_obj->fetchRow($adminsetting_obj->select()
					->setIntegrityCheck(false)
					->from(array('admin_setting'))
					);

					if(!empty($resultadminsetting) && sizeof($resultadminsetting)>0)
					{
						$static=$resultadminsetting->static_salt;
							//echo "in";exit;
					}


					$stcPss = $misc_obj->encryptPassword($pass,$static);

					if($stcPss!=$resultadminsetting->kit_g_pwd)
					{
						$data=array('success'=>'','failure'=>'Password is not correct.');
						echo json_encode($data);exit;
					}
				  
				if(trim($_POST["total_amount"])==0 && trim($_POST["real_total_amount"])==0)
				{
					$db->rollBack();
					$data=array('success'=>'','failure'=>'There are something errors!. Go back and try it again.');
					echo json_encode($data);exit;
				}


				if($_POST['kit_type']==4){
					$kit_type='Silver';
				}else if(($_POST['kit_type']==2) ||($_POST['kit_type']==7)){
					$kit_type='ROI';
				}else{
					$kit_type='Referral';
				}

			 $CheckKitsValue = $contract_obj->fetchRow($contract_obj->select()
					->where("contract_id = ?",$_POST['kit_type'])
				);
			//  $paid_amount = $CheckKitsValue->price_paid;
				$paid_amount = $CheckKitsValue->total_price;

				$noOfKits = $_POST["no_of_kits"];
				$KitsPrice = round($paid_amount * $noOfKits,4);
			// var_dump($KitsPrice);
				if(($KitsPrice == $_POST["total_amount"]) && ($KitsPrice == $_POST["real_total_amount"] )){
					$contract_rate = $price_in_btc = $_POST["total_amount"];
					$realPrice = $_POST["real_total_amount"]/$noOfKits;
				}else{
					$db->rollBack();
					$msg = "There is something error!. Go back and try it again.";
					$data=array('success'=>'','failure'=>'There is something error!. Go back and try it again.');
					echo json_encode($data);exit;
				}
				$shipAddress = "";
				$email = "";

					
					$invoiceArray=$common_obj->createInvoiceForKit($contract_rate, $username, 1, $shipAddress, $email, $noOfKits);
					$ip=$misc_obj->get_client_ip();
					if(!empty($invoiceArray))
						{
							
							$comment = isset($_POST['comment'])?$_POST['comment']:'';
							$invoiceId = $invoiceArray;
							$userNameArra =$common_obj-> createKits($invoiceId, $noOfKits, $username,$realPrice,$kit_type,'Active');
							$updateCommentArray = array("comment" => $comment,"payment_gateway" => "0","updated_on" => new Zend_Db_Expr('NOW()') );
							$update_data=$kits_obj->update($updateCommentArray,$DB->quoteInto("invoice_id=?",$invoiceId));
							$updateCommentArray = array("comment" => $comment,"update_by" => $loggedIn_user,  "ip_address" => $ip, "updated_on" => new Zend_Db_Expr('NOW()') );
							$update_data=$kit_invoices_obj->update($updateCommentArray,$DB->quoteInto("invoice_id=?",$invoiceId));
						}
						else
						{
							$db->rollBack();
							$msg = "Error creating invoice.";
							$data=array('success'=>'','failure'=>$msg);
							echo json_encode($data);exit;
						}

					 $db->commit();
					  $data=array('success'=>'success','failure'=>'');
					  echo json_encode($data);exit;

				
			 }else{
				   $data=array('success'=>'','failure'=>'All fields are required');
					  echo json_encode($data);exit;
			  }
        }
        catch(Exception $e)
        {
            $db->rollBack();
            $msg = "The system behaved unexpectedly, Please try again";
            //$data=array('success'=>'','failure'=>$msg);
            $data=array('success'=>'','failure'=>$msg);
            echo json_encode($data);exit;
        }
    }

}
