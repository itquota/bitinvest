<?php
class PayoutcronController extends Zend_Controller_Action{

	public function init(){

	}

	public function indexAction(){
		try {
			$common_obj = new Gbc_Model_Custom_CommonFunc();
			$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
			$cronName = "payoutCron";
			$start = date('Y-m-d h:i:s');
			$common_obj->updateCronStatus($cronName,$start,'');
			$date = date('j-m-Y');
			$filename = "finalPayout_Last15Days(".$date.").csv"; // File Name
			
			$file = FILE_UPLOAD_PATH."/res/files/final payout/".$filename;
			if((!empty($_GET['startDate'])) && (!empty($_GET['endDate']))){
				$startDate = $_GET['startDate'].' 23:59:59';
				$endDate = $_GET['endDate']." 23:59:59";
			}else{
				$startDate = date('Y-m-d', strtotime('-12 day')).' 23:59:59';
				$endDate = date('Y-m-d')." 23:59:59";
			}

			if(!file_exists($file)){
				
				// $Last15Days_query = mysql_query("SELECT UserInfo.username, UserInfo.ref_sponsor_id, Invoice.created_on as InvoiceDate, round(sum(Invoice.contract_rate),2) as investment, UserInfo.wallet_addr, FinalLedger.ref_amt,FinalLedger.adm_ref_payout,FinalLedger.binary_amt, FinalLedger.adm_bin_payout,FinalLedger.daily_earning_amt,FinalLedger.adm_roi_payout,FinalLedger.total_amt,FinalLedger.created_on as FinalLedgerDate,ProfileContact.contact_phone ,ProfileContact.contact_email,  UserInfo.payment_status as PaymentStatus_hold, UserInfo.b2_status, WithdrawalType.withdrawal_type, UserInfo.isActiveId FROM `user_info` as UserInfo left join profile_contact as ProfileContact on ProfileContact.username = UserInfo.username left join final_ledger as FinalLedger on FinalLedger.username = UserInfo.username left join invoices as Invoice on Invoice.username = UserInfo.username left join user_withdrawal_types as UserWithdrawalType on UserWithdrawalType.username  = UserInfo.username left join withdrawal_types as WithdrawalType on WithdrawalType.id = UserWithdrawalType.withdrawal_type WHERE (UserInfo.isActiveId =1) and (FinalLedger.ref_amt != 0  or FinalLedger.binary_amt != 0  or FinalLedger.daily_earning_amt != 0)  and ((FinalLedger.created_on > '$startDate') and (FinalLedger.created_on <= '$endDate')) group by FinalLedger.username ORDER BY `FinalLedger`.`created_on` asc");
				$Last15Days= ("SELECT UserInfo.username, UserInfo.ref_sponsor_id, (select created_on from invoices where invoices.username=UserInfo.username limit 1) as InvoiceDate, (select round(sum(contract_rate),2) from invoices where invoices.username=UserInfo.username) as investment,  UserInfo.wallet_addr, (select contact_phone from profile_contact as ProfileContact  where ProfileContact.username=UserInfo.username limit 1) as contact_phone , (select contact_email from profile_contact as ProfileContact  where ProfileContact.username=UserInfo.username limit 1) as contact_email , FinalLedger.ref_amt,FinalLedger.adm_ref_payout,FinalLedger.binary_amt, FinalLedger.adm_bin_payout,FinalLedger.daily_earning_amt,FinalLedger.adm_roi_payout,FinalLedger.total_amt,FinalLedger.created_on as FinalLedgerDate, UserInfo.payment_status as PaymentStatus_hold, UserInfo.b2_status, (select WithdrawalType.withdrawal_type from user_withdrawal_types as UserWithdrawalType left join withdrawal_types as WithdrawalType on WithdrawalType.id = UserWithdrawalType.withdrawal_type where UserWithdrawalType.username = UserInfo.username  order by UserWithdrawalType.id desc limit 1) as withdrawal_type, UserInfo.isActiveId FROM `user_info` as UserInfo left join final_ledger as FinalLedger on FinalLedger.username = UserInfo.username WHERE (UserInfo.isActiveId =1) and (FinalLedger.ref_amt != 0  or FinalLedger.binary_amt != 0  or FinalLedger.daily_earning_amt != 0)  and ((FinalLedger.created_on > '$startDate') and (FinalLedger.created_on <'$endDate'))  ORDER BY `FinalLedger`.`created_on` asc");
				// echo "SELECT UserInfo.username, UserInfo.ref_sponsor_id, (select created_on from invoices where invoices.username=UserInfo.username limit 1) as InvoiceDate, (select round(sum(contract_rate),2) from invoices where invoices.username=UserInfo.username) as investment,  UserInfo.wallet_addr, (select contact_phone from profile_contact as ProfileContact  where ProfileContact.username=UserInfo.username limit 1) as contact_phone , (select contact_email from profile_contact as ProfileContact  where ProfileContact.username=UserInfo.username limit 1) as contact_email , FinalLedger.ref_amt,FinalLedger.adm_ref_payout,FinalLedger.binary_amt, FinalLedger.adm_bin_payout,FinalLedger.daily_earning_amt,FinalLedger.adm_roi_payout,FinalLedger.total_amt,FinalLedger.created_on as FinalLedgerDate, UserInfo.payment_status as PaymentStatus_hold, UserInfo.b2_status, (select WithdrawalType.withdrawal_type from user_withdrawal_types as UserWithdrawalType left join withdrawal_types as WithdrawalType on WithdrawalType.id = UserWithdrawalType.withdrawal_type where UserWithdrawalType.username = UserInfo.username  order by UserWithdrawalType.id desc limit 1) as withdrawal_type, UserInfo.isActiveId FROM `user_info` as UserInfo left join final_ledger as FinalLedger on FinalLedger.username = UserInfo.username WHERE (UserInfo.isActiveId =1) and (FinalLedger.ref_amt != 0  or FinalLedger.binary_amt != 0  or FinalLedger.daily_earning_amt != 0)  and ((FinalLedger.created_on > '$startDate') and (FinalLedger.created_on <'$endDate'))  ORDER BY `FinalLedger`.`created_on` asc";
				$Last15Days_query = $DB->query($Last15Days);

				// var_dump($Last15Days_query);
				$common_obj->WriteCSVCron($Last15Days_query,$file,'encode');
			}
			$filename1 = "finalPayout(".$date.").csv"; // File Name
			$file1 = FILE_UPLOAD_PATH."/res/files/final payout/".$filename1;
			if(!file_exists($file1)){
				// $FinalPayout_query = mysql_query("SELECT UserInfo.username,UserInfo.ref_sponsor_id, Invoice.created_on as InvoiceDate, round(sum(Invoice.contract_rate),2) as investment, UserInfo.wallet_addr, FinalBalance.total_amt as TotalBalance,FinalBalance.total_admin_fees as TotalAdminFees,FinalBalance.total_withdrawal as Withdrawal,FinalBalance.bal_amt as CurrentBalance,  UserInfo.payment_status as PaymentStatus_hold, UserInfo.b2_status, WithdrawalType.withdrawal_type, UserInfo.isActiveId FROM `user_info` as UserInfo left join final_balance as FinalBalance on FinalBalancess.username = UserInfo.username left join invoices as Invoice on Invoice.username = UserInfo.username  left join user_withdrawal_types as UserWithdrawalType on UserWithdrawalType.username = UserInfo.username  left join withdrawal_types as WithdrawalType on WithdrawalType.id = UserWithdrawalType.withdrawal_type WHERE (UserInfo.isActiveId =1) and (FinalBalance.total_amt != 0 ) group by FinalBalance.username");
				$FinalPayout = ("SELECT UserInfo.username,UserInfo.ref_sponsor_id,(select created_on from invoices where invoices.username=UserInfo.username limit 1) as InvoiceDate, (select round(sum(contract_rate),2) from invoices where invoices.username=UserInfo.username) as investment, UserInfo.wallet_addr, FinalBalance.total_amt as TotalBalance,FinalBalance.total_admin_fees as TotalAdminFees,FinalBalance.total_withdrawal as Withdrawal,FinalBalance.bal_amt as CurrentBalance, UserInfo.payment_status as PaymentStatus_hold, UserInfo.b2_status, (select WithdrawalType.withdrawal_type from user_withdrawal_types as UserWithdrawalType left join withdrawal_types as WithdrawalType on WithdrawalType.id = UserWithdrawalType.withdrawal_type where UserWithdrawalType.username = UserInfo.username  order by UserWithdrawalType.id desc limit 1) as withdrawal_type, UserInfo.isActiveId FROM `user_info` as UserInfo left join final_balance as FinalBalance on FinalBalance.username = UserInfo.username WHERE (UserInfo.isActiveId =1) and (FinalBalance.total_amt != 0 )");
				$FinalPayout_query = $DB->query($FinalPayout);
				$common_obj->WriteCSVCron($FinalPayout_query,$file1,'encode');
			}
		 // $email_to = "virenderchauhan2011@gmail.com";
			$email_to = "thegainbitcoin@gmail.com"; // The email you are sending to (example)

			$email_from = "admin@gainbitco.in"; // The email you are sending from (example)
			$email_subject = "final payout sent"; // The Subject of the email
			$email_message = ""; // Message that the email has in it
			$email_text = "Please find the attached files for last 15 days payout and final payout."; // Message that the email has in it
			//$filesarray = array("file_1.ext","file_2.ext","file_3.ext");
			$files = array($filename,$filename1);
			$filepath = array($file,$file1);
			///var_dump($filesarray);
			// var_dump($files);
			// var_dump($filepath);


			$semi_rand = md5(time());
			$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
			$headers ="From: $email_from \n"; // Who the email is from (example)
			$headers .="CC: thegainbitcoinhelp@gmail.com"; // Who the email is from (example)
			$headers .= "\nMIME-Version: 1.0\n" .
						"Content-Type: multipart/mixed;\n" .
						" boundary=\"{$mime_boundary}\"";

			$fileData = fopen($filepath[1],"rb");
			$data = fread($fileData,filesize($filepath[1]));
			flush();
			$email_message .= "This is a multi-part message in MIME format.\n\n" .
								"--{$mime_boundary}\n" .
								"Content-Type:text/html;\n" .
								"Content-Transfer-Encoding: \n\n" . $email_text. "\n\n";
			$email_message .= "--{$mime_boundary}\n";
			for($x=0;$x<count($files);$x++){
				$fileData = fopen($filepath[$x],"rb");
				$data = fread($fileData,filesize($filepath[$x]));
				flush();
				fclose($fileData);
				$data = chunk_split(base64_encode($data));
				$email_message .= "Content-Type: {\"application/csv\"};\n" . " name=\"$files[$x]\"\n" .
								    "Content-Disposition: attachment;\n" . " filename=\"$files[$x]\"\n" .
								    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
				$email_message .= "--{$mime_boundary}\n";

			}
			if(mail($email_to,$email_subject,$email_message,$headers)){
				echo "Final Payout sheet and Payout Sheet for 15 days has been sent";
				ob_flush();
				flush();
				$cronName = "payoutCron";
				// updateCronStatus($cronName);
				$end = date('Y-m-d h:i:s');
				$common_obj->updateCronStatus($cronName,'',$end);
			}
			echo "success";exit;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();exit;	
		}

	}

}