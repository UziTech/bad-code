<?php
session_start();
if(isset($_SESSION['loggedin'], $_REQUEST['id']) && $_SESSION['loggedin'] == 'yes')
{
	$totaldiscount = 0;
	$subtotal = 0;
	$salestax = 0;
	require_once("./includes/connect.inc");
	$query = 	"select * from invoiceinfo where invoiceid = ?";//CREATE VIEW invoiceinfo AS SELECT inv.invoiceid AS invoiceid, invoicejob, invoicetaxpercentage, invoicecheckno, invoicedate, invoicepaid, invoicenotes, cus.customerid AS customerid, customername, customeraddress, customercity, customerstate, customerzip, customerphone, customerdate, contactname, contactphone, contactemail, paymentdescription, technicianfirstname, technicianlastname, languageid FROM invoices AS inv LEFT JOIN customers AS cus ON cus.customerid = inv.customerid LEFT JOIN payments AS pay ON pay.paymentid = inv.paymentid LEFT JOIN contacts AS con ON con.contactid = inv.contactid LEFT JOIN technicians AS tec ON inv.technicianid = tec.technicianid
	$result = $db->prepare($query);
	if($result->execute(array($_REQUEST['id'])))
	{
		if($result->rowCount() == 1)
		{
			while($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				foreach($row as $key => $value)
				{
					$row[$key] = stripslashes($value);
				}
				$arr['invoiceinfo'] = $row;
			}
		}
		else
		{
      echo "Error: ".__LINE__;
			exit;
		}
	}
	else
	{
		echo "Error: ".__LINE__;
		exit;
	}
	$query = 	"select * from settings";
	$result = $db->prepare($query);
	if($result->execute())
	{
		if($result->rowCount() == 1)
		{
			while($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				foreach($row as $key => $value)
				{
					$row[$key] = stripslashes($value);
				}
				$arr['settings'] = $row;
			}
		}
		else
		{
			echo "Error: ".__LINE__;
			exit;
		}
	}
	else
	{
		echo "Error: ".__LINE__;
		exit;
	}
	$query = 	"select * from languages where languageid = ?";
	$result = $db->prepare($query);
	if($result->execute(array($arr['invoiceinfo']['languageid'])))
	{
		if($result->rowCount() == 1)
		{
			while($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				foreach($row as $key => $value)
				{
					$row[$key] = stripslashes($value);
				}
				$arr['language'] = $row;
			}
		}
		else
		{
			echo "Error: ".__LINE__;
			exit;
		}
	}
	else
	{
		echo "Error: ".__LINE__;
		exit;
	}
	$query = 	"select * from lineitems where invoiceid = ?";
	$result = $db->prepare($query);
	if($result->execute(array($_REQUEST['id'])))
	{
		if($result->rowCount() > 0)
		{
			while($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				foreach($row as $key => $value)
				{
					$row[$key] = stripslashes($value);
				}
				$arr['lineitems'][] = $row;
				$totaldiscount += $row['lineitemdiscount'] * $row['lineitemquantity'];
				$subtotal += ($row['lineitemprice'] - $row['lineitemdiscount']) * $row['lineitemquantity'];
				$salestax += (($row['lineitemtaxable'] == 'Y')? $arr['invoiceinfo']['invoicetaxpercentage'] * $row['lineitemprice'] * $row['lineitemquantity'] * .01: 0);
			}
		}
	}
	else
	{
		echo "Error: ".__LINE__;
		exit;
	}
	require_once("./dompdf/dompdf_config.inc.php");
	$totalPaypal = "";
	if($subtotal + $salestax == 0)
	{
		$total = "";
	}
	else
	{
		$total = '$'.number_format($subtotal + $salestax, 2);
		$totalPaypal = $subtotal + $salestax;
	}
	$datearray = explode("-", $arr['invoiceinfo']['invoicedate']);
	if(count($datearray) == 3)
	{
		$invoicetimestamp = mktime(0, 0, 0, $datearray[1], $datearray[2], $datearray[0]);
		$date = "{$arr['language']['date']}: ".date("F j, Y", $invoicetimestamp);
	}
	else
	{
		$date = "";
	}
	$pdftitle = $arr['invoiceinfo']['customername'].".".(($date == "") ? date("m-d-y") : date("m-d-Y", $invoicetimestamp));
	$paymentdescription = (($arr['invoiceinfo']['paymentdescription'] == "Check")? $arr['invoiceinfo']['paymentdescription'] . " # " . (($arr['invoiceinfo']['invoicecheckno'] != 0)? $arr['invoiceinfo']['invoicecheckno'] : "") : $arr['invoiceinfo']['paymentdescription']);

	if($subtotal == 0)
	{
		$subtotal = "";
	}
	else
	{
		$subtotal = number_format($subtotal, 2);
	}
	if($totaldiscount == 0)
	{
		$totaldiscount = "";
	}
	else
	{
		$totaldiscount = number_format($totaldiscount, 2);
	}
	if($salestax == 0)
	{
		$salestax = "";
	}
	else
	{
		$salestax = number_format($salestax, 2);
	}
	$html = <<<END
<!DOCTYPE html>
<html>
	<head>
		<title>{$pdftitle}</title>
		<meta name="viewport" content="width=device-width" />
		<style type="text/css">
			html
			{
				padding: 0px;
				margin: 0px;
			}
			body
			{
				padding: 0px;
				margin: 0 auto;
				height: 1056px;
				width: 816px;
			}
			.header
			{
				font-weight: bold;
			}
			#main
			{
				color: #222;
				font-family: sans-serif;
				font-size: 8pt;
				height: 1056px;
				width: 816px;

END;
	if(isset($_REQUEST['html']))
	{
		$html .= <<<END
				position: relative;
				border: 1px solid #eee;
END;
	}
	$html .= <<<END

			}
			#invoiceimage
			{
				border: 0 none;
				left: 0px;
				position: absolute;
				top: 0px;
			}
			#companylogo
			{
				border: 0 none;
				left: 81px;
				position: absolute;
				top: 14px;
			}

END;
	if($arr['invoiceinfo']['invoicepaid'] == "Y")
	{
		$html .= <<<END
			#invoicepaid
			{
				color: #FF0000;
				font-size: 14pt;
				font-weight: bold;
				left: 560px;
				position: absolute;
				top: 341px;
			}
END;
	}
	$html .= <<<END

			#invoicetxt
			{
				right: 20px;
				position: absolute;
				top: 14px;
				color: #3B5E91;
				font-size: 19pt;
				text-align: right;
			}
			#totxt
			{
				left: 81px;
				position: absolute;
				top: 205px;
			}
			#techniciantxt
			{
				left: 72px;
				position: absolute;
				text-align: center;
				top: 320px;
				width: 136px;
			}
			#jobtxt
			{
				left: 210px;
				position: absolute;
				text-align: center;
				top: 320px;
				width: 197px;
			}
			#datetxt
			{
				left: 408px;
				position: absolute;
				text-align: center;
				top: 320px;
				width: 143px;
			}
			#paymenttxt
			{
				left: 552px;
				position: absolute;
				text-align: center;
				top: 320px;
				width: 191px;
			}
			#qtytxt
			{
				left: 72px;
				position: absolute;
				text-align: center;
				top: 388px;
				width: 71px;
			}
			#descriptiontxt
			{
				left: 144px;
				position: absolute;
				text-align: center;
				top: 388px;
				width: 365px;
			}
			#unitpricetxt
			{
				left: 510px;
				position: absolute;
				text-align: center;
				top: 388px;
				width: 77px;
			}
			#discounttxt
			{
				left: 588px;
				position: absolute;
				text-align: center;
				top: 388px;
				width: 77px;
			}
			#linetotaltxt
			{
				left: 666px;
				position: absolute;
				text-align: center;
				top: 388px;
				width: 77px;
			}
			#totaldiscounttxt
			{
				position: absolute;
				right: 237px;
				text-align: right;
				top: 840px;
			}
			#subtotaltxt
			{
				position: absolute;
				right: 157px;
				text-align: right;
				top: 865px;
			}
			#salestaxtxt
			{
				position: absolute;
				right: 157px;
				text-align: right;
				top: 890px;
			}
			#totaltxt
			{
				position: absolute;
				right: 157px;
				text-align: right;
				top: 916px;
			}
			#checkspayabletotxt
			{
				left: 0px;
				position: absolute;
				text-align: center;
				top: 1004px;
				width: 816px;
			}
			#paypalpayabletotxt
			{
				left: 0px;
				position: absolute;
				text-align: center;
				top: 989px;
				width: 816px;
			}
			#thankyoutxt
			{
				font-size: 10pt;
				left: 0px;
				position: absolute;
				text-align: center;
				top: 1019px;
				width: 816px;
			}
			#invoiceno
			{
				position: absolute;
				right: 20px;
				text-align: right;
				top: 100px;
			}
			#date
			{
				position: absolute;
				right: 20px;
				text-align: right;
				top: 115px;
			}
			#companyname
			{
				font-size: 12pt;
				font-weight: bold;
				left: 81px;
				position: absolute;
				top: 123px;
			}
			#companyslogan
			{
				font-size: 7.5pt;
				font-style: italic;
				left: 81px;
				position: absolute;
				top: 141px;
			}
			#companyaddress
			{
				left: 81px;
				position: absolute;
				top: 158px;
			}
			#companyphone
			{
				left: 81px;
				position: absolute;
				top: 170px;
			}
			#companyemail
			{
				left: 81px;
				position: absolute;
				top: 182px;
			}
			#contactname
			{
				left: 121px;
				position: absolute;
				top: 205px;
			}
			#customername
			{
				left: 121px;
				position: absolute;
				top: 217px;
			}
			#customeraddress
			{
				left: 121px;
				position: absolute;
				top: 229px;
			}
			#customercitystatezip
			{
				left: 121px;
				position: absolute;
				top: 241px;
			}
			#customerphone
			{
				left: 121px;
				position: absolute;
				top: 253px;
			}
			#customerid
			{
				left: 121px;
				position: absolute;
				top: 265px;
			}
			#technicianname
			{
				left: 72px;
				overflow: hidden;
				position: absolute;
				text-align: center;
				top: 345px;
				white-space: nowrap;
				width: 136px;
			}
			#invoicejob
			{
				left: 210px;
				overflow: hidden;
				position: absolute;
				text-align: center;
				top: 345px;
				white-space: nowrap;
				width: 197px;
			}
			#invoicedate
			{
				left: 408px;
				overflow: hidden;
				position: absolute;
				text-align: center;
				top: 345px;
				white-space: nowrap;
				width: 143px;
			}
			#paymentdescription
			{
				left: 552px;
				overflow: hidden;
				position: absolute;
				text-align: center;
				top: 345px;
				white-space: nowrap;
				width: 191px;
			}
			.lineitemquantity
			{
				position: absolute;
				right: 681px;
				text-align: right;
			}
			.lineitemdescription
			{
				position: absolute;
				left: 151px;
			}
			.lineitemprice
			{
				position: absolute;
				right: 237px;
				text-align: right;
			}
			.lineitemdiscount
			{
				position: absolute;
				right: 161px;
				text-align: right;
			}
			.lineitemtotal
			{
				position: absolute;
				right: 81px;
				text-align: right;
			}
END;
  if(isset($arr['lineitems']))
  {
    foreach($arr['lineitems'] as $key => $value)
    {
      $top = 15 * $key + 411;
      $html .= <<<END

        .lineitem{$key}
        {
          top: {$top}px;
        }
END;
    }
  }
	$html .= <<<END

			#totaldiscount
			{
				position: absolute;
				right: 157px;
				text-align: right;
				top: 840px;
			}
			#subtotal
			{
				position: absolute;
				right: 77px;
				text-align: right;
				top: 865px;
			}
			#salestax
			{
				position: absolute;
				right: 77px;
				text-align: right;
				top: 890px;
			}
			#total
			{
				position: absolute;
				right: 77px;
				text-align: right;
				top: 916px;
			}
		</style>
	</head>
	<body>
		<div id="main">
			<div id="invoiceimage"><img src="images/invoicebackground.png" alt="" /></div>
			<!--div id="companylogo"><img src="images/uzitech.gif" alt="" /></div-->

END;
	if($arr['invoiceinfo']['invoicepaid'] == "Y")
	{
		$html .= <<<END
			<div id="invoicepaid">{$arr['language']['paid']}</div>
END;
	}
	$html .= <<<END

			<div id="invoicetxt" class="header">{$arr['language']['invoice']}</div>
			<div id="totxt" class="header">{$arr['language']['to']}</div>
			<div id="techniciantxt" class="header">{$arr['language']['technician']}</div>
			<div id="jobtxt" class="header">{$arr['language']['job']}</div>
			<div id="datetxt" class="header">{$arr['language']['date']}</div>
			<div id="paymenttxt" class="header">{$arr['language']['payment']}</div>
			<div id="qtytxt" class="header">{$arr['language']['qty']}</div>
			<div id="descriptiontxt" class="header">{$arr['language']['description']}</div>
			<div id="unitpricetxt" class="header">{$arr['language']['unit_price']}</div>
			<div id="discounttxt" class="header">{$arr['language']['discount']}</div>
			<div id="linetotaltxt" class="header">{$arr['language']['line_total']}</div>
			<div id="totaldiscounttxt" class="header">{$arr['language']['total_discount']}</div>
			<div id="subtotaltxt" class="header">{$arr['language']['subtotal']}</div>
			<div id="salestaxtxt" class="header">{$arr['language']['sales_tax']}</div>
			<div id="totaltxt" class="header">{$arr['language']['total']}</div>
			<div id="checkspayabletotxt" class="header">{$arr['language']['make_all_checks_payable_to']} {$arr['settings']['checks_payable_to']}</div>
			<div id="paypalpayabletotxt" class="header">{$arr['language']['send_money_with_paypal']} {$arr['settings']['paypal_url']}/{$totalPaypal}</div>
			<div id="thankyoutxt" class="header">{$arr['language']['thank_you_msg']}</div>
			<div id="invoiceno">{$arr['language']['invoice_number']} {$arr['invoiceinfo']['invoiceid']}</div>
			<div id="date">{$date}</div>
			<div id="companyname">{$arr['settings']['company_name']}</div>
			<div id="companyslogan">{$arr['settings']['company_slogan']}</div>
			<div id="companyaddress">{$arr['settings']['company_address']}</div>
			<div id="companyphone">{$arr['settings']['company_phone']}</div>
			<div id="companyemail">{$arr['settings']['company_email_address']}</div>
			<div id="contactname">{$arr['invoiceinfo']['contactname']}</div>
			<div id="customername">{$arr['invoiceinfo']['customername']}</div>
			<div id="customeraddress">{$arr['invoiceinfo']['customeraddress']}</div>
			<div id="customercitystatezip">{$arr['invoiceinfo']['customercity']}, {$arr['invoiceinfo']['customerstate']} {$arr['invoiceinfo']['customerzip']}</div>
			<div id="customerphone">{$arr['invoiceinfo']['customerphone']}</div>
			<div id="customerid">{$arr['language']['customer_id']} {$arr['invoiceinfo']['customerid']}</div>
			<div id="technicianname">{$arr['invoiceinfo']['technicianfirstname']} {$arr['invoiceinfo']['technicianlastname']}</div>
			<div id="invoicejob">{$arr['invoiceinfo']['invoicejob']}</div>
			<div id="invoicedate">{$arr['invoiceinfo']['invoicedate']}</div>
			<div id="paymentdescription">{$paymentdescription}</div>
END;
  if(isset($arr['lineitems']))
  {
    foreach($arr['lineitems'] as $key => $value)
    {
      if(!is_null($value['lineitemquantity']))
      {
        $lineitemtotal = number_format($value['lineitemquantity'] * ($value['lineitemprice'] - $value['lineitemdiscount']), 2);

        $value['lineitemquantity'] = number_format($value['lineitemquantity'], 0);
        $value['lineitemprice'] = number_format($value['lineitemprice'], 2);
        if($value['lineitemdiscount'] == 0)
        {
          $value['lineitemdiscount'] = '';
        }
        else
        {
          $value['lineitemdiscount'] = number_format($value['lineitemdiscount'], 2);
        }

        $html .= <<<END

        <div class="lineitem{$key} lineitemquantity">{$value['lineitemquantity']}</div>
        <div class="lineitem{$key} lineitemdescription">{$value['lineitemdescription']}</div>
        <div class="lineitem{$key} lineitemprice">{$value['lineitemprice']}</div>
        <div class="lineitem{$key} lineitemdiscount">{$value['lineitemdiscount']}</div>
        <div class="lineitem{$key} lineitemtotal">{$lineitemtotal}</div>
END;
      }
    }
  }
	$html .= <<<END

			<div id="totaldiscount">{$totaldiscount}</div>
			<div id="subtotal">{$subtotal}</div>
			<div id="salestax">{$salestax}</div>
			<div id="total">{$total}</div>
		</div>
	</body>
</html>
END;
	if(isset($_REQUEST['html']))
	{
		echo $html;
	}
	else
	{
		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->render();
		if(isset($_REQUEST['download']))
		{
			$dompdf->stream("{$pdftitle}.pdf");
		}
		else if(isset($_REQUEST['email'], $_REQUEST['message']))
		{
      $msg = str_replace("\\n", "\n", $_REQUEST['message']);
			$pdf = $dompdf->output();
			$boundary1   =rand(0,9)."-"
			.rand(10000000000,9999999999)."-"
			.rand(10000000000,9999999999)."=:"
			.rand(10000,99999);
			$boundary2   =rand(0,9)."-".rand(10000000000,9999999999)."-"
			.rand(10000000000,9999999999)."=:"
			.rand(10000,99999);
			$pdfname = "{$pdftitle}.pdf";
			$attachment = chunk_split(base64_encode($pdf));
			$headers = <<<AKAM
From: {$arr['settings']['from_name']} <{$arr['settings']['from_email_address']}>
Reply-To: {$arr['settings']['reply_to_address']}
MIME-Version: 1.0
Content-Type: multipart/mixed;
		boundary="$boundary1"
AKAM;

			$attachment = <<<END
--$boundary1
Content-Type: application/pdf;
		name="{$pdftitle}.pdf"
Content-Transfer-Encoding: base64
Content-Disposition: attachment;
		filename="{$pdftitle}.pdf"

$attachment

END;

			$message = <<<END
This is a multi-part message in MIME format.

--$boundary1
Content-Type: multipart/alternative;
		boundary="$boundary2"

--$boundary2
Content-Type: text/plain;
		charset="windows-1256"
Content-Transfer-Encoding: quoted-printable

$msg
--$boundary2--

$attachment
--$boundary1--
END;

			if(mail($_REQUEST['email'], "Invoice {$pdftitle}", $message, $headers))
			{
				echo 1;
			}
			else
			{
				echo "There was an error sending the email to {$_REQUEST['email']}";
			}
		}
		else
		{
			$dompdf->stream("{$pdftitle}.pdf", array("Attachment" => 0));
		}
	}
}
else
{
	header("location: index.php");
}
?>
