<?php
class DailykitsreportController extends Zend_Controller_Action{

	public function init()
	{
		$authUserNamespace = new Zend_Session_Namespace('Gbc_Auth');
		if(!isset($authUserNamespace->user) || $authUserNamespace->user=="")$this->_redirect("/Dashboard/logout");

	}
	public function indexAction()
	{
		$common_obj = new Gbc_Model_Custom_CommonFunc();
		$kits_obj=new Gbc_Model_DbTable_Kits();
		$DB = Zend_Db_Table_Abstract::getDefaultAdapter();
		$cronName = "dailyKitsReport";
		$start = date('Y-m-d h:i:s');
		$common_obj->updateCronStatus($cronName,$start,'');

		$date = date('j-m-Y');

		$filename = "ActiveKitReports(".$date.").csv"; // File Name
		$file = FILE_UPLOAD_PATH."/res/files/reports/".$filename;
		if(!file_exists($file)){
			$query = "set @a=0";
			$result = mysql_query($query);
			$query = "SELECT @a := @a +1 as `Sr. No.`, kits.username as `Username`, kits.kit_number as `Kit Number`, kits.updated_on as `Activated On`,
kit_invoices.update_by `Kit Activated By`,kit_invoices.transactionid as `Payment Id`,kit_invoices.origtxid AS `Transaction Id`,
kits.invoice_id as `Invoice Id`,kits.kit_price as `Rate`,kit_invoices.kits_qty as `Kit Quantity`,kit_invoices.amtPaid as `Amount Paid`,
kit_invoices.comment as `Comments`
FROM `kits`
INNER JOIN user_info ON user_info.username = kits.username
AND user_info.binaryUser IS NOT NULL left join kit_invoices on kit_invoices.invoice_id = kits.invoice_id
WHERE date( kits.updated_on ) = date( now( ) - INTERVAL 1
DAY )
AND kits.status = 'active';";

			$res=$DB->query($query);
		 $result = $res->fetchAll();
		 if(!empty($result) && sizeof($result)>0)
		 {
		 	WriteCSV($result,$file,'encode');
		 }


		}

		$filename1 = "UsedKitReports(".$date.").csv"; // File Name
		$file1 = FILE_UPLOAD_PATH."/res/files/reports/".$filename1;
		if(!file_exists($file1)){

			$query = "set @a=0";
			$result = mysql_query($query);
			$query = "SELECT @a := @a +1 as `Sr. No.`, kits.username as `Username`, kits.kit_number as `Kit Number`, kits.kit_used_date as `kit Used Date`,
kits.kit_used_by `Kit used By`,
kits.invoice_id as `Invoice Id`,kits.kit_price as `Rate`,kit_invoices.kits_qty as `Kit Quantity`,kit_invoices.amtPaid as `Amount Paid`,
kit_invoices.comment as `Comments`
FROM `kits`
INNER JOIN user_info ON user_info.username = kits.username
AND user_info.binaryUser IS NOT NULL left join kit_invoices on kit_invoices.invoice_id = kits.invoice_id
WHERE date( kits.kit_used_date ) = date( now( ) - INTERVAL 1
DAY )
AND kits.status = 'used';";
			$res1=$DB->query1($query1);
			$result1 = $res1->fetchAll();

			if(!empty($result1) && sizeof($result1)>0)
			{
				WriteCSV($result1,$file1,'encode');
			}

		}

		$filename2 = "RefundedKitReports(".$date.").csv"; // File Name
		$file2 = FILE_UPLOAD_PATH."/res/files/reports/".$filename2;
		if(!file_exists($file2)){

			$query = "set @a=0";
			$result = mysql_query($query);
			$query = "SELECT @a := @a +1 as `Sr. No.`, kits.username as `Username`, kits.kit_number as `Kit Number`,
kits.invoice_id as `Invoice Id`,refund_requests.updated_on as `Updated On`, refund_requests.updated_by as `Updated By`
FROM `kits`
INNER JOIN user_info ON user_info.username = kits.username
AND user_info.binaryUser IS NOT NULL left join refund_requests on refund_requests.kit_number = kits.kit_number
WHERE date( refund_requests.updated_on ) = date( now( ) - INTERVAL 1
DAY )
AND kits.status = 'refunded';";

			WriteCSV($result2,$file2,'encode');
		}
		$filename2 = "RefundedKitReports(".$date.").csv"; // File Name
		$file2 = "../reports/".$filename2;
		if(!file_exists($file2)){

			$query = "set @a=0";
			$result = mysql_query($query);
			$query2 = "SELECT @a := @a +1 as `Sr. No.`, kits.username as `Username`, kits.kit_number as `Kit Number`,
kits.invoice_id as `Invoice Id`,refund_requests.updated_on as `Updated On`, refund_requests.updated_by as `Updated By`
FROM `kits`
INNER JOIN user_info ON user_info.username = kits.username
AND user_info.binaryUser IS NOT NULL left join refund_requests on refund_requests.kit_number = kits.kit_number
WHERE date( refund_requests.updated_on ) = date( now( ) - INTERVAL 1
DAY )
AND kits.status = 'refunded';";
			$res2=$DB->query($query2);
			$result2 = $res2->fetchAll();

			if(!empty($result2) && sizeof($result2)>0)
			{
				WriteCSV($result2,$file2,'encode');
			}

		}

		//  $email_to = "virenderchauhan2011@gmail.com";
		$email_to = "gbcajay@gmail.com"; // The email you are sending to (example)

		$email_from = "support@gainbitcoin.com"; // The email you are sending from (example)
		$email_subject = "Daily Kits Reports"; // The Subject of the email
		$email_message = ""; // Message that the email has in it
		$email_text = "Please find the attached files for active and used kits Daily Reports."; // Message that the email has in it
		//$filesarray = array("file_1.ext","file_2.ext","file_3.ext");
		$files = array($filename,$filename1,$filename2);
		$filepath = array($file,$file1,$file2);
		///var_dump($filesarray);
		// var_dump($files);
		// var_dump($filepath);


		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		$headers ="From: $email_from \n"; // Who the email is from (example)
		//$headers .="CC: gbcops01@gmail.com";
		$headers .="CC: gbckan@gmail.com";
		//$headers .="CC: virenderchauhan2011@gmail.com";
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
	 //echo $email_message;
	 if(mail($email_to,$email_subject,$email_message,$headers)){
	 	echo "Daily Active and Used kits reports has been sent";
	 	//ob_flush();
	 	//flush();
	 	$cronName = "dailyKitsReport";
	 	// updateCronStatus($cronName);
	 	$end = date('Y-m-d h:i:s');
	 	$common_obj->updateCronStatus($cronName,'',$end);
	 }
	 echo "cron finished";exit;
	}

}