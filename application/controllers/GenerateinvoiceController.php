<?php

class GenerateinvoiceController extends Zend_Controller_Action
{
    public function init(){

    }

    public function indexAction(){
        try {
            require("library/fpdf/fpdf.php");
            $invoicesObj=new Gbc_Model_DbTable_Invoices();
            $userinfoObj=new Gbc_Model_DbTable_Userinfo();
            $profilecontactObj=new Gbc_Model_DbTable_Profilecontact();



            $v = md5($_POST['invoiceid']);
            /*if(!empty($_POST['invoiceid']) && !empty($v)){
             echo header("location:../invoices/".$_POST['invoiceid'].".pdf");
             }*/

            if (isset($authUserNamespace->user)) {

                $loggedIn = true;



                //get user info

                $username = $authUserNamespace->user;
                if($username == "admin")
                    $username = $authUserNamespace->user;

                $userInfo = getUserInfo($username);

                if (noError($userInfo)){

                    //$userInfo = $userInfo["errMsg"];

                } else {

                    // printArr("Error fetching user info");
                    $msg=("Error fetching user info");
                }

            }

            $invoiceId = $_POST['invoiceid'];


            // $query = "SELECT * FROM invoices WHERE 1=1 AND invoice_id='$invoiceId'";
            $queryinvoice=$invoicesObj->fetchRow($invoicesObj->select()
                ->setIntegrityCheck(false)
                ->from(array('invoices'))
                ->where("1=1")
                ->where("invoice_id=?",$invoiceId));

            //$result = runQuery($query, $conn);

            // $row = mysql_fetch_assoc($result["dbResource"])	;

            $result=sizeof($queryinvoice);
            if(isset($result) && $result>0)
            {
                $user=$queryinvoice->username;
                $contractName=$queryinvoice->contract_name;
                $contractType=$queryinvoice->contract_type;
                $contractQty=$queryinvoice->contract_qty;
                $contractRate=$queryinvoice->contract_rate;
                $totalPaid=$queryinvoice->amtPaid;
                $InvoiceID=$queryinvoice->invoice_id;
                $ContractID=$queryinvoice->contract_id;
                $TransactionID=$queryinvoice->origtxid;
                $dateTime=$queryinvoice->created_on;

                //$query1 = "SELECT email_address FROM user_info WHERE 1=1 AND username='$user'";
                $query1userinfo=$userinfoObj->fetchRow($userinfoObj->select()
                    ->setIntegrityCheck(false)
                    ->from(array('user_info'))
                    ->where("1=1")
                    ->where("username=?",$user));

                //$result = runQuery($query1, $conn);


                // $row = mysql_fetch_assoc($result["dbResource"])	;



                if(!empty($query1userinfo->comm_email) && $query1userinfo->comm_email!=""){
                    $to_email=$query1userinfo->comm_email;
                }
                else{
                    $to_email=$query1userinfo->email_address;
                }
                $full_name=$query1userinfo->name;
                $country=$query1userinfo->country;
                $phone=$query1userinfo->phone;
                // $query2 = "SELECT * FROM profile_contact WHERE 1=1 AND username='$user'";
                /*$query2profilecontact=$profilecontactObj->fetchRow($profilecontactObj->select()
                 ->setIntegrityCheck(false)
                 ->from(array('profile_contact'))
                 ->where("1=1 AND username='$user'"));*/

                //$result = runQuery($query2, $conn);


                // $row = mysql_fetch_assoc($result["dbResource"])	;

                //$full_name=$query2profilecontact->full_name;
                //$country=$query2profilecontact->country;
                //$phone=$query2profilecontact->contact_phone;
            }
            // Begin configuration

            $textColour = array( 0, 0, 0 );
            $headerColour = array( 100, 100, 100 );
            $tableHeaderTopTextColour = array( 255,255,255 );
            $tableHeaderTopFillColour = array( 140,140,140 );
            $tableHeaderTopProductTextColour = array( 255,255,255 );
            $tableHeaderTopProductFillColour = array( 140,140,140 );
            $tableHeaderLeftTextColour = array( 99, 42, 57 );
            $tableHeaderLeftFillColour = array( 184, 207, 229 );
            $tableBorderColour = array( 50, 50, 50 );
            $tableRowFillColour = array( 213, 170, 170 );
            $reportName = "Invoice";
            $reportNameYPos = 160;
            $logoFile = BASE."/images/GainBTCLogo_large.png";
            $logoXPos = 60;
            $logoYPos = 108;
            $logoWidth = 110;
            $columnLabels = array( "Quantity","Price Per Unit ","Total");
            $columnLabels1 = array( "Invoice ID","$InvoiceID");
            $columnLabels2 = array( "Qty","Price","Paid Amount");

            $rowLabels = array();


            // End configuration


            //function for header



            /**
            Create the title page
             **/

            $pdf = new FPDF( 'P', 'mm', 'A4' );

            $pdf->AliasNbPages();
            $pdf->SetTextColor( $textColour[0], $textColour[1], $textColour[2] );


            /**
            Create the page header, main heading, and intro text
             **/

            $pdf->AddPage();

            $pdf->SetTextColor( $textColour[0], $textColour[1], $textColour[2] );
            $pdf->SetFont( 'Arial', 'B', 20 );
            $pdf->Ln( 15 );

            $pdf->cell( 0,25,'INVOICE',0,0,'C' );

            $pdf->Ln( 15 );

            $pdf->SetDrawColor(167,167,167);

            /* First Section of Invoice */
            $pdf->SetFont( 'Arial', '', 10 );
            $pdf->Ln(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);




            // Remaining header cells
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $col1="To,\nMr. $full_name\nPhone:+$country-$phone\nE-mail:$to_email\n ";
            $pdf->MultiCell(100, 6, $col1, 1, 1);

            $pdf->SetXY($x + 100, $y);

            $col2="Invoice ID";
            $pdf->MultiCell(40, 6, $col2, 1,1);
            $pdf->SetXY($x + 140, $y);
            //$pdf->Ln(0);
            // $InvoiceID = "1426744899_435741247";
            $col3=$InvoiceID;
            $pdf->MultiCell(50, 6, $col3, 1,1);

            $pdf->SetXY($x + 100, $y+6);
            $pdf->MultiCell(40, 6, 'Invoice Date', 1,1);

            $pdf->SetXY($x + 100, $y+12);
            $pdf->MultiCell(40, 6, '', 1,1);

            $pdf->SetXY($x + 140, $y+6);
            $pdf->MultiCell(50, 6, $dateTime, 1,1);

            $pdf->SetXY($x + 140, $y+12);
            $pdf->MultiCell(50, 6, '', 1,1);

            $pdf->SetXY($x + 100, $y+18);
            $pdf->MultiCell(40, 6, '', 1,1);

            $pdf->SetXY($x + 100, $y+24);
            $pdf->MultiCell(40, 6, '', 1,1);

            $pdf->SetXY($x + 140, $y+18);
            $pdf->MultiCell(50, 6, '', 1,1);

            $pdf->SetXY($x + 140, $y+24);
            $pdf->MultiCell(50, 6, '', 1,1);


            $pdf->SetXY($x, $y+30);
            $pdf->MultiCell(190, 6, '', 1, 1);




            /* ------------------------------------------------------------------------------------------------ */

            /* Second Section of Invoice */



            $pdf->Ln(6);
            $pdf->SetDrawColor(167,167,167);
            $pdf->Ln( 5);

            $pdf->SetFont( 'Arial', '', 10 );

            $pdf->SetTextColor( $tableHeaderTopProductTextColour[0], $tableHeaderTopProductTextColour[1], $tableHeaderTopProductTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopProductFillColour[0], $tableHeaderTopProductFillColour[1], $tableHeaderTopProductFillColour[2] );
            $pdf->Cell( 82, 12, " Item description", 0, 0, 'C', true );

            $pdf->SetTextColor( $tableHeaderTopTextColour[0], $tableHeaderTopTextColour[1], $tableHeaderTopTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopFillColour[0], $tableHeaderTopFillColour[1], $tableHeaderTopFillColour[2] );

            for ( $i=0; $i<count($columnLabels); $i++ ) {
                $pdf->Cell( 36, 12, $columnLabels[$i], 0, 0, 'C', true );
            }

            $pdf->Ln( 12 );


            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
			if($ContractID > 13){
				$pdf->Multicell( 82, 6, "1. $contractName" , 1, 'L', false );
			}else{
            	$pdf->Multicell( 82, 6, "1. BITCOIN CLOUD MINING CONTRACT \n   ( $contractQty GHS)" , 1, 'L', false );
			}
            $pdf->SetXY($x + 82, $y+59);

            $pdf->Cell( 36, 12, '1', 1, 0, 'C', false );
			if($ContractID > 13){
				$pdf->Cell( 36, 12, "$contractRate BTC", 1, 0, 'C', false );
				$pdf->Cell( 36, 12, "$contractRate BTC", 1, 0, 'C', false );
			}else{
				$pdf->Cell( 36, 12, "$contractRate BTC", 1, 0, 'C', false );
				$pdf->Cell( 36, 12, "$totalPaid BTC", 1, 0, 'C', false );
			}

            $pdf->Ln( 12 );

            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(248,246,220);
            $pdf->Multicell( 82, 12,'' , 1, 'L', false );


            $pdf->SetXY($x + 82, $y+71);

            for ( $i=0; $i<count($columnLabels); $i++ ) {
                $pdf->Cell( 36, 12, '', 1, 0, 'C', false );
            }

            $pdf->Ln( 12 );

            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell( 82, 12, " ", 1, 0, 'L', false );

            // Remaining header cells
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);

            for ( $i=0; $i<count($columnLabels); $i++ ) {
                $pdf->Cell( 36, 12, '', 1, 0, 'C', false );
            }

            $pdf->Ln( 12 );

            $pdf->SetTextColor(120,120,120);
            $pdf->SetFillColor(204,204,204);
            $pdf->Cell( 82, 12, " ", 1, 0, 'L', false );

            // Remaining header cells

            $pdf->Cell( 36, 12, '', 1, 0, 'C', false );
		/*	if($ContractID > 13){
				$pdf->Cell( 36, 12, 'Subtotal', 1, 0, 'C', false );
				$pdf->Cell( 36, 12, '$ '.$totalPaid, 1, 0, 'C', false );
			}else{
			*/
				$pdf->Cell( 36, 12, 'Subtotal', 1, 0, 'C', false );
				$pdf->Cell( 36, 12, 'BTC  '.$totalPaid, 1, 0, 'C', false );
			//}

            $pdf->Ln( 12 );

            $pdf->Cell( 82, 12, "", 1, 0, 'L', false );

            // Remaining header cells

            $pdf->Cell( 36, 12, '', 1, 0, 'C', false );
            $pdf->Cell( 36, 12, 'Applicable Taxes', 1, 0, 'C', false );
            $pdf->Cell( 36, 12, 'Inclusive', 1, 0, 'C', false );

            $pdf->Ln( 12 );

            $pdf->SetTextColor( $tableHeaderTopProductTextColour[0], $tableHeaderTopProductTextColour[1], $tableHeaderTopProductTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopProductFillColour[0], $tableHeaderTopProductFillColour[1], $tableHeaderTopProductFillColour[2] );
            $pdf->Cell( 82, 12, '', 0, 0, 'L', true );

            // Remaining header cells
            $pdf->SetTextColor( $tableHeaderTopTextColour[0], $tableHeaderTopTextColour[1], $tableHeaderTopTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopFillColour[0], $tableHeaderTopFillColour[1], $tableHeaderTopFillColour[2] );

            $pdf->Cell( 36, 12, '', 0, 0, 'C', true );
       /*     if($ContractID > 13){
				$pdf->Cell( 36, 12, 'TOTAL', 0, 0, 'C', true );
				$pdf->Cell( 36, 12, '$ '.$totalPaid, 0, 0, 'C', true );
			}else{
			*/
				$pdf->Cell( 36, 12, 'TOTAL', 0, 0, 'C', true );
				$pdf->Cell( 36, 12, 'BTC  '.$totalPaid, 0, 0, 'C', true );
		//
            $pdf->Ln( 12 );

            $pdf->SetTextColor( $tableHeaderTopProductTextColour[0], $tableHeaderTopProductTextColour[1], $tableHeaderTopProductTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopProductFillColour[0], $tableHeaderTopProductFillColour[1], $tableHeaderTopProductFillColour[2] );
            $pdf->Cell( 82, 12, " **Amount In BTC Only", 0, 0, 'L', true );

            // Remaining header cells
            $pdf->SetTextColor( $tableHeaderTopTextColour[0], $tableHeaderTopTextColour[1], $tableHeaderTopTextColour[2] );
            $pdf->SetFillColor( $tableHeaderTopFillColour[0], $tableHeaderTopFillColour[1], $tableHeaderTopFillColour[2] );
            $pdf->Cell( 36, 12, '', 0, 0, 'C', true );
            $pdf->Cell( 36, 12, '', 0, 0, 'C', true );
            $pdf->Cell( 36, 12, '', 0, 0, 'C', true );

            $pdf->Ln( 9 );
            // Create the table data rows

            $fill = false;
            $row = 0;


            /*-----------------------------------------------------------------------------------------------------*/


            $pdf->Ln(8);

            $pdf->SetTextColor(128,0,128);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',10);
            // $pdf->SetFont('','U');
            $Terms = "Terms and Conditions: ";
            $pdf->Multicell(100,4,$Terms,0,1,'L',false);
            $pdf->SetXY($x + 46, $y + 148);
            $pdf->SetFont('','U');

            $Terms = "https://gainbitcoin.com/gbc/Terms";
            $pdf->Multicell(100,4,$Terms,0,1,'L',false);
            // $pdf->Link(20, 4, 2, 2, 'gainbitcoin.com/terms-and-conditions.php');




            /*-----------------------------------------------------------------------------------------------------*/

            /* Third Section of Invoice */

            $pdf->Ln(2);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont( 'Arial', 'B', 11 );
            $content="\n Payment Need to \n be made in \n BITCOIN. \n \n \n \n \n \n";
            $pdf->Multicell( 36, 5, $content , 1, 'L', false );
            $pdf->SetFont( 'Arial', 'B', 10 );


            $pdf->SetXY($x + 36, $y + 154);

            // Remaining header cells
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(255,255,255);


            $address="Transaction ID \n$TransactionID \n\n";

            $pdf->Multicell( 82,15, $address, 1, 'C', false );

            $pdf->SetFont( 'Arial', 'B', 11 );


            $pdf->SetXY($x + 118, $y + 154);

            $image= $pdf->Image(BASE.'/images/amit.png',150,225,35);

            $pdf->Multicell( 72, 15, "For VariableTechPte Ltd       \n  $image \n  Authorized Signatory     ", 1,  'R', false );

            $pdf->SetFont( 'Arial', '', 10 );


            /* Second Page Annexture*/

            /*  $pdf->AddPage();
             $pdf->SetFont( 'Arial', 'B', 20 );
             $pdf->Ln( 27 );

             $pdf->cell( 0,25,'Terms & Conditions',0,0,'C' );

             $pdf->Ln( 30 );

             $pdf->SetFont( 'Arial', '', 11 );
             $pdf->Multicell(0,5,"1.	VariableTechPte Ltd, Singapore is responsible for buying and managing bitcoin mining servers for the
             customers as per the above invoice.");


             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"2.	The Servers are on lease to the customer for the defined term.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"3.	The Bitcoin generated from the mining servers would be transferred to the customer on pro-rata basis.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"4.	The bitcoin mining servers produces an outcome of approximately 3-4% bitcoins per month based on
             the number of servers installed.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"5.	On completion of the agreed term VariableTech will buyback the servers on the original price in Bitcoin.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"6.	The assets created in the meanwhile are assets of VariableTech and the customer can in no means
             demand the ownership of the same.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"7.	The assets are leased only to the customer for the said term and lease can be extended only by the
             consent of the company.");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"8.	The company will not hold any liability for the customer profit or loss in case of bitcoin price fluctuations
             unless agreed upon separately");

             $pdf->Ln( 5 );
             $pdf->Multicell(0,5,"9.	Also through the bitcoin mining process, in this agreement, company never guarantees any profit
             commitment in terms of fiat currency.");

             $pdf->Ln( 15 );
             $pdf->SetFont( 'Arial', 'I', 14 );

             $pdf->cell( 0,0,'For VariableTechPte Ltd',0,0,'R' );

             $pdf->Ln( 15 );

             $pdf->Image('../res/images/amit.png',150,220,35);

             $pdf->Ln( 25 );

             $pdf->cell( 0,0,'Authorized Signatory',0,0,'R' ); */

            $fileLocation="library/invoices/$InvoiceID.pdf";


            $pdf->Output($fileLocation,'F');
            /* Generating Pdf File */
            $pdf->Output($InvoiceID.".pdf",'D');

            exit;


        }
        catch(Exception $e)
        {
            echo $e->getMessage();exit;
        }


    }
}