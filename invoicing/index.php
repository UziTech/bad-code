<?php
/*////TODO
			1. validation
			2. add tax amount to customer table
			3. load tax amount in invoice
/*/

/*
	Database Schema:

	404error
		errorid   int
		time      string
		ip        string
		url       string
		referer   string
		useragent string

	contacts
		customerid   int
		contactname  string
		contactphone string
		contactemail string
		contactid    int

	customers
		customername      string
		customeraddress   string
		customercity      string
		customerstate     string
		customerzip       string
		customertax       decimal
		customerphone     string
		customerdate      string
		customeridPrimary int

	invoices
		contactid            int
		customerid           int
		invoicejob           string
		invoicecheckno       int
		paymentid            int
		technicianid         int
		languageid           string
		invoicetaxpercentage decimal
		invoicedate          string
		invoicepaid          string
		invoicenotes         string
		invoiceidPrimary     int

	items
		itemdescriptionIndex string
		itemprice            decimal
		itemtaxable          string
		itemidPrimary        int

	languages
		languageidPrimary          string
		paid                       string
		invoice                    string
		to                         string
		technician                 string
		job                        string
		date                       string
		payment                    string
		qty                        string
		description                string
		unit_price                 string
		discount                   string
		line_total                 string
		total_discount             string
		subtotal                   string
		sales_tax                  string
		total                      string
		invoice_number             string
		customer_id                string
		make_all_checks_payable_to string
		send_money_with_paypal     string
		thank_you_msg              string

	lineitems
		invoiceid           int
		lineitemquantity    int
		itemid              int
		lineitemdescription string
		lineitemprice       decimal
		lineitemtaxable     string
		lineitemdiscount    decimal
		lineitemidPrimary   int

	payments
		paymentdescriptionIndex string
		paymentidPrimary        int

	settings
		idPrimary              int
		checks_payable_to      string
		paypal_url             string
		company_name           string
		company_slogan         string
		company_address        string
		company_phone          string
		company_email_address  string
		from_email_address     string
		from_name              string
		reply_to_email_address string
		languageid             string

	technicians
		technicianfirstname    string
		technicianlastname     string
		technicianusername     string
		technicianpassword     string
		technicianemailaddress string
		technicianidPrimary    int

	invoiceinfo
		CREATE view `invoiceinfo` AS
		SELECT `inv`.`invoiceid`            AS `invoiceid`,
		       `inv`.`invoicejob`           AS `invoicejob`,
		       `inv`.`invoicetaxpercentage` AS `invoicetaxpercentage`,
		       `inv`.`invoicecheckno`       AS `invoicecheckno`,
		       `inv`.`invoicedate`          AS `invoicedate`,
		       `inv`.`invoicepaid`          AS `invoicepaid`,
		       `inv`.`invoicenotes`         AS `invoicenotes`,
		       `cus`.`customerid`           AS `customerid`,
		       `cus`.`customername`         AS `customername`,
		       `cus`.`customeraddress`      AS `customeraddress`,
		       `cus`.`customercity`         AS `customercity`,
		       `cus`.`customerstate`        AS `customerstate`,
		       `cus`.`customerzip`          AS `customerzip`,
		       `cus`.`customerphone`        AS `customerphone`,
		       `cus`.`customerdate`         AS `customerdate`,
		       `con`.`contactname`          AS `contactname`,
		       `con`.`contactphone`         AS `contactphone`,
		       `con`.`contactemail`         AS `contactemail`,
		       `pay`.`paymentdescription`   AS `paymentdescription`,
		       `tec`.`technicianfirstname`  AS `technicianfirstname`,
		       `tec`.`technicianlastname`   AS `technicianlastname`,
		       `inv`.`languageid`           AS `languageid`
		FROM   `invoices` `inv`
		       LEFT JOIN `customers` `cus`
		         ON `cus`.`customerid` = `inv`.`customerid`
		       LEFT JOIN `payments` `pay`
		         ON `pay`.`paymentid` = `inv`.`paymentid`
		       LEFT JOIN `contacts` `con`
		         ON `con`.`contactid` = `inv`.`contactid`
		       LEFT JOIN `technicians` `tec`
		         ON `inv`.`technicianid` = `tec`.`technicianid`;

 */
require_once('./includes/mobile_device_detect.inc');
$ismobile = false;
$mobile = mobile_device_detect(true,false,true,true,true,true,true,false,false);
if($mobile && !isset($_GET['m']) || !$mobile && isset($_GET['m']))
{
	$ismobile = true;
}
session_start();
if(isset($_REQUEST['logout']))
{
	$_SESSION['loggedin'] = "no";
	session_destroy();
}
$mtext = ($ismobile? "true" : "false");
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'yes'){
	$title = 'Invoices';
	$script = <<<END
var mobile = {$mtext};
var dwidth = (mobile)? "600" : "auto"
var loadingQueue = 1;
var invoiceSID = -1;
var customerSID = -1;
var contactSID = -1;
var itemSID = -1;
var paymentSID = -1;
var technicianSID = -1;
var languageSID = -1;
var lineitemSID = -1;
$(document).ready(function(){
	setInvoiceOptions("#page #selectinvoice", undefined, false);
	setCustomerOptions("#page #selectcustomer", undefined, false);
	setContactOptions(-1, "#page #selectcontact", undefined, false);
	setItemOptions("#page #selectitem", undefined, false, undefined, "");
	setPaymentOptions("#page #selectpayment", undefined, false);
	setTechnicianOptions("#page #selecttechnician", undefined, false);
	setLanguageOptions("#page #selectlanguage", undefined, false);
	$("#viewpdfinvoice").click(function(){
		if($("#selectinvoice").val() != '')
		{
			window.open("invoice.php?id=" + $("#selectinvoice").val(), "_blank");
		}
		else
		{
			message("Can't Do It!");
		}
	});
	$("#downloadpdfinvoice").click(function(){
		if($("#selectinvoice").val() != '')
		{
			location.href = "invoice.php?id=" + $("#selectinvoice").val() + "&download";
		}
		else
		{
			message("Can't Do It!");
		}
	});
	$("#htmlpdfinvoice").click(function(){
		if($("#selectinvoice").val() != '')
		{
			window.open("invoice.php?id=" + $("#selectinvoice").val() + "&html", "_blank");
		}
		else
		{
			message("Can't Do It!");
		}
	});
	$("#emailpdfinvoice").click(function(){
		if($("#selectinvoice").val() != '')
		{
			startLoading("Loading Email Box...");
			$.post("ajax.php", {task: "get", table: "invoice", id: $("#selectinvoice").val()}, function(data){
				$("<div id='emailinvoice'>"+
						"<table><tbody>"+
							"<tr><td><label for='contactid'>Contact</label></td><td><select name='contactid' id='contactid'><option value=''>Loading Contacts...</option></select> <button id='editcontact'>edit</button> <button id='removecontact'>X</button></td></tr>"+
							"<tr><td><label for='contactemail'>Email</label></td><td><input type='email' name='contactemail' id='contactemail' /></td></tr>"+
							"<tr><td><label for='message'>Message</label></td><td><textarea name='message' id='message'>"+
							"Invoice is attached.\\n"+
							"\\n"+
							"Thanks,\\n"+
							"\\n"+
							"Tony Brix\\n"+
							"Owner\\n"+
							"UziTech.com\\n"+
							"Cell: (320) 249-1820\\n"+
							"TBrix13@UziTech.com"+
							"</textarea></td></tr>"+
						"</tbody></table>"+
					"</div>")
				.appendTo("body")
				.dialog({
					buttons: { "Send Invoice": function(){ if(sendInvoice()){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
					close: function(event, ui){ $(this).remove(); },
					height: 'auto',
					modal: true,
					resizable: false,
					title: "Send Invoice",
					width: dwidth
				});
				$("#loading").parent().css({'z-index': parseInt($("#emailinvoice").parent().css("z-index")) + 2}).next().css({'z-index': parseInt($("#emailinvoice").parent().css("z-index")) + 1});
				$("#emailinvoice button").button();
				setContactOptions(data['customerid'], "#emailinvoice #contactid", ($("#selectcontact").val()? $("#selectcontact").val() : "(0)"), true, {close: function(){
					if($("#emailinvoice #contactid").val() != '')
					{
						startLoading("Loading Contact Email...");
						$.post("ajax.php", {task: "get", table: "contact", id: $("#emailinvoice #contactid").val()}, function(data){
							$("#emailinvoice #contactemail").val(data['contactemail']);
							endLoading("Loading Contact Email...");
						}, "json");
					}
				}});
				$("#emailinvoice #contactid").change(function(){
					if($(this).val() == "newcontact")
					{
						$(this).val("");
						newContact($("#emailinvoice #customerid").val(), {close: function(){
							$("#emailinvoice #contactid option[value='newcontact']").before("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
							$("#emailinvoice #contactemail").val($("#newcontact #contactemail").val());
							$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
						}});
					}
					else if($(this).val() != "")
					{
						startLoading("Loading Contact Email...");
						$.post("ajax.php", {task: "get", table: "contact", id: $(this).val()}, function(data){
							$("#emailinvoice #contactemail").val(data['contactemail']);
							endLoading("Loading Contact Email...");
						}, "json");
					}
					else
					{
						$("#emailinvoice #contactemail").val("");
					}
				});
				$("#emailinvoice #editcontact").click(function(){
					if(!isNaN(parseInt($("#emailinvoice #contactid").val())) && parseInt($("#emailinvoice #contactid").val()) > 0)
					{
						editContact($("#emailinvoice #contactid").val(), {close: function(){
							$("#emailinvoice #contactid option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
							$("#emailinvoice #contactemail").val($("#editcontact #contactemail").val());
							$("#page #selectcontact option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
						}});
					}
					return false;
				});
				$("#emailinvoice #removecontact").click(function(){
					if(!isNaN(parseInt($("#emailinvoice #contactid").val())) && parseInt($("#emailinvoice #contactid").val()) > 0)
					{
						removeContact($("#emailinvoice #contactid").val(), {close: function(){
							$("#page #selectcontact option[value='" + $("#emailinvoice #contactid").val() + "']").remove();
							$("#emailinvoice #contactid option[value='" + $("#emailinvoice #contactid").val() + "']").remove();
							$("#emailinvoice #contactemail").val("");
						}});
					}
					return false;
				});
				endLoading("Loading Email Box...");
			}, "json");
		}
		else
		{
			message("Can't Do It!");
		}
	});
	$("#editsettings").click(function(){
END;
if($_SESSION['userid'] != 0)
{
	$script .= <<<END

		alert("You do not have permission for this!");
END;
}
else
{
	$script .= <<<END

		startLoading("Loading Settings...");
		$.post("ajax.php", {task: "get", table: "settings"}, function(data){
			$("<div id='editsettings'>"+
					"<table><tbody>"+
						"<tr><td><label for='checks_payable_to'>Checks Payable To</label></td><td><input type='text' name='checks_payable_to' id='checks_payable_to' value='"+data['checks_payable_to']+"' /></td></tr>"+
						"<tr><td><label for='paypal_url'>PayPal URL</label></td><td><input type='text' name='paypal_url' id='paypal_url' value='"+data['paypal_url']+"' /></td></tr>"+
						"<tr><td><label for='company_name'>Company Name</label></td><td><input type='text' name='company_name' id='company_name' value='"+data['company_name']+"' /></td></tr>"+
						"<tr><td><label for='company_slogan'>Company Slogan</label></td><td><input type='text' name='company_slogan' id='company_slogan' value='"+data['company_slogan']+"' /></td></tr>"+
						"<tr><td><label for='company_address'>Company Address</label></td><td><input type='text' name='company_address' id='company_address' value='"+data['company_address']+"' /></td></tr>"+
						"<tr><td><label for='company_phone'>Company Phone</label></td><td><input type='phone' name='company_phone' id='company_phone' value='"+data['company_phone']+"' /></td></tr>"+
						"<tr><td><label for='company_email_address'>Company Email Address</label></td><td><input type='email' name='company_email_address' id='company_email_address' value='"+data['company_email_address']+"' /></td></tr>"+
						"<tr><td><label for='from_email_address'>From Email Address</label></td><td><input type='email' name='from_email_address' id='from_email_address' value='"+data['from_email_address']+"' /></td></tr>"+
						"<tr><td><label for='from_name'>From Name</label></td><td><input type='text' name='from_name' id='from_name' value='"+data['from_name']+"' /></td></tr>"+
						"<tr><td><label for='reply_to_email_address'>Reply To Email Address</label></td><td><input type='email' name='reply_to_email_address' id='reply_to_email_address' value='"+data['reply_to_email_address']+"' /></td></tr>"+
						"<tr><td><label for='languageid'>Default Language</label></td><td><select name='languageid' id='languageid'><option value=''>Loading Languages...</option></select> <button id='editlanguage'>edit</button> <button id='removelanguage'>X</button></td></tr>"+
					"</tbody></table>"+
				"</div>")
			.appendTo("body")
			.dialog({
				buttons: { "Update Settings": function(){ if(updateSettings()){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
				close: function(event, ui){ $(this).remove(); },
				height: 'auto',
				modal: true,
				resizable: false,
				title: "Edit Settings",
				width: dwidth
			});
			$("#loading").parent().css({'z-index': parseInt($("#editsettings").parent().css("z-index")) + 2}).next().css({'z-index': parseInt($("#editsettings").parent().css("z-index")) + 1});
			$("#editsettings button").button();
			setLanguageOptions("#editsettings #languageid", data['languageid'], true);
			$("#editsettings #languageid").change(function(){
				if($(this).val() == "newlanguage")
				{
					$(this).val("");
					newLanguage({close: function(){
						$("#editsettings #languageid option[value='newlanguage']").before("<option value='"+$("#newlanguage #languageid").val()+"' selected='selected'>"+$("#newlanguage #languageid").val()+"</option>");
						$("#page #selectlanguage").append("<option value='" + $("#newlanguage #languageid").val() + "'>" + $("#newlanguage #languageid").val() + "</option>");
					}});
				}
			});
			$("#editsettings #editlanguage").click(function(){
				if($("#editsettings #languageid").val() != '' && $("#editsettings #languageid").val() != 'newlanguage')
				{
					editLanguage($("#editsettings #languageid").val(), {close: function(){
						$("#editsettings #languageid option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
						$("#page #selectlanguage option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
					}});
				}
				else
				{
					message("No Language Selected!");
				}
				return false;
			});
			$("#editsettings #removelanguage").click(function(){
				if($("#editsettings #languageid").val() != '' && $("#editsettings #languageid").val() != 'newlanguage')
				{
					removeLanguage($("#editsettings #languageid").val(), {close: function(){
						$("#page #selectlanguage option[value='" + $("#editsettings #languageid").val() + "']").remove();
						$("#editsettings #languageid option[value='" + $("#editsettings #languageid").val() + "']").remove();
					}});
				}
				else
				{
					message("No Language Selected!");
				}
				return false;
			});
			endLoading("Loading Settings...");
		}, "json");
END;
}
$script .= <<<END

	});
	$("#page #searchinvoices").keyup(function(e){
		setInvoiceOptions("#page #selectinvoice", undefined, false, undefined, $("#page #searchinvoices").val(), false);
	});
	$("#page #searchcustomers").keyup(function(e){
		setCustomerOptions("#page #selectcustomer", undefined, false, undefined, $("#page #searchcustomers").val(), false);
	});
	$("#page #searchcontacts").keyup(function(e){
		setContactOptions(-1, "#page #selectcontact", undefined, false, undefined, $("#page #searchcontacts").val(), false);
	});
	$("#page #searchitems").keyup(function(e){
		setItemOptions("#page #selectitem", undefined, false, undefined, $("#page #searchitems").val(), false);
	});
	$("#page #searchpayments").keyup(function(e){
		setPaymentOptions("#page #selectpayment", undefined, false, undefined, $("#page #searchpayments").val(), false);
	});
	$("#page #searchtechnicians").keyup(function(e){
		setTechnicianOptions("#page #selecttechnician", undefined, false, undefined, $("#page #searchtechnicians").val(), false);
	});
	$("#page #searchlanguages").keyup(function(e){
		setLanguageOptions("#page #selectlanguage", undefined, false, undefined, $("#page #searchlanguages").val(), false);
	});
	$("#page #newinvoice").click(function(){
		newInvoice({close: function(){
			$("#page #selectinvoice option[value='']").after("<option value='" + $("#newinvoice #invoiceid").val() + "'>#" + $("#newinvoice #invoiceid").val() + " " + (($("#newinvoice #customerid").val() == "")? "No Customer" : $("#newinvoice #customerid option[value='" + $("#newinvoice #customerid").val() + "']").text()) + " " + $("#newinvoice #invoicedate").val() + "</option>");
			if($("#page #searchinvoices").val() != "")
			{
				setInvoiceOptions("#page #selectinvoice", undefined, false, undefined, $("#page #searchinvoices").val(), false);
			}
		}});
		return false;
	});
	$("#page #newcustomer").click(function(){
		newCustomer({close: function(){
				$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid").val() + "'>" + $("#newcustomer #customername").val() + "</option>");
				newContact($("#newcustomer #customerid").val(), {close: function(){
					$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
				}});
		}});
		return false;
	});
	$("#page #newcontact").click(function(){
		newContact({close: function(){
				$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
		}});
		return false;
	});
	$("#page #newitem").click(function(){
		newItem({close: function(){
				$("#page #selectitem").append("<option value='" + $("#newitem #itemid").val() + "'>" + $("#newitem #itemdescription").val() + "</option>");
		}});
		return false;
	});
	$("#page #newpayment").click(function(){
		newPayment({close: function(){
				$("#page #selectpayment").append("<option value='" + $("#newpayment #paymentid").val() + "'>" + $("#newpayment #paymentdescription").val() + "</option>");
		}});
		return false;
	});
	$("#page #newtechnician").click(function(){
		newTechnician({close: function(){
				$("#page #selecttechnician").append("<option value='" + $("#newtechnician #technicianid").val() + "'>" + $("#newtechnician #technicianfirstname").val() + " " + $("#newtechnician #technicianlastname").val() + "</option>");
		}});
		return false;
	});
	$("#page #newlanguage").click(function(){
		newLanguage({close: function(){
				$("#page #selectlanguage").append("<option value='" + $("#newlanguage #languageid").val() + "'>" + $("#newlanguage #languageid").val() + "</option>");
		}});
		return false;
	});
	$("#page #editinvoice").click(function(){
		if($("#page #selectinvoice").val() != "")
		{
			editInvoice($("#page #selectinvoice").val(), {close: function(){
				$("#page #selectinvoice option[value='" + $("#editinvoice #invoiceid").val() + "']").text("#" + $("#editinvoice #invoiceid").val() + " " + $("#editinvoice #customerid option[value='" + $("#editinvoice #customerid").val() + "']").text() + " " + $("#editinvoice #invoicedate").val());
				if($("#page #searchinvoices").val() != "")
				{
					setInvoiceOptions("#page #selectinvoice", undefined, false, undefined, $("#page #searchinvoices").val(), false);
				}
			}});
		}
		else
		{
			message("No Invoice Selected!");
		}
		return false;
	});
	$("#page #editcustomer").click(function(){
		if($("#page #selectcustomer").val() != "")
		{
			editCustomer($("#page #selectcustomer").val(), {close: function(){
				$("#page #selectcustomer option[value='" + $("#editcustomer #customerid").val() + "']").text($("#editcustomer #customername").val());
			}});
		}
		else
		{
			message("No Customer Selected!");
		}
		return false;
	});
	$("#page #editcontact").click(function(){
		if($("#page #selectcontact").val() != "")
		{
			editContact($("#page #selectcontact").val(), {close: function(){
				$("#page #selectcontact option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
			}});
		}
		else
		{
			message("No Contact Selected!");
		}
		return false;
	});
	$("#page #edititem").click(function(){
		if($("#page #selectitem").val() != "" && $("#page #selectitem").val() != "0")
		{
			editItem($("#page #selectitem").val(), {close: function(){
				$("#page #selectitem option[value='" + $("#edititem #itemid").val() + "']").text($("#edititem #itemdescription").val());
			}});
		}
		else
		{
			message("No Item Selected!");
		}
		return false;
	});
	$("#page #editpayment").click(function(){
		if($("#page #selectpayment").val() != "")
		{
			editPayment($("#page #selectpayment").val(), {close: function(){
				$("#page #selectpayment option[value='" + $("#editpayment #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
			}});
		}
		else
		{
			message("No Payment Selected!");
		}
		return false;
	});
	$("#page #edittechnician").click(function(){
		if($("#page #selecttechnician").val() != "")
		{
			editTechnician($("#page #selecttechnician").val(), {close: function(){
				$("#page #selecttechnician option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
			}});
		}
		else
		{
			message("No Technician Selected!");
		}
		return false;
	});
	$("#page #editlanguage").click(function(){
		if($("#page #selectlanguage").val() != "")
		{
			editLanguage($("#page #selectlanguage").val(), {close: function(){
				$("#page #selectlanguage option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
			}});
		}
		else
		{
			message("No Language Selected!");
		}
		return false;
	});
	$("#page #deleteinvoice").click(function(){
		if($("#page #selectinvoice").val() != "")
		{
			removeInvoice($("#page #selectinvoice").val(), {close: function(){
				$("#page #selectinvoice option[value='" + $("#page #selectinvoice").val() + "']").remove();
			}});
		}
		else
		{
			message("No Invoice Selected!");
		}
		return false;
	});
	$("#page #deletecustomer").click(function(){
		if($("#page #selectcustomer").val() != "")
		{
			removeCustomer($("#page #selectcustomer").val(), {close: function(){
				$("#page #selectcustomer option[value='" + $("#page #selectcustomer").val() + "']").remove();
			}});
		}
		else
		{
			message("No Customer Selected!");
		}
		return false;
	});
	$("#page #deletecontact").click(function(){
		if($("#page #selectcontact").val() != "")
		{
			removeContact($("#page #selectcontact").val(), {close: function(){
				$("#page #selectcontact option[value='" + $("#page #selectcontact").val() + "']").remove();
			}});
		}
		else
		{
			message("No Contact Selected!");
		}
		return false;
	});
	$("#page #deleteitem").click(function(){
		if($("#page #selectitem").val() != "" && $("#page #selectitem").val() != "0")
		{
			removeItem($("#page #selectitem").val(), {close: function(){
				$("#page #selectitem option[value='" + $("#page #selectitem").val() + "']").remove();
			}});
		}
		else
		{
			message("No Item Selected!");
		}
		return false;
	});
	$("#page #deletepayment").click(function(){
		if($("#page #selectpayment").val() != "")
		{
			removePayment($("#page #selectpayment").val(), {close: function(){
				$("#page #selectpayment option[value='" + $("#page #selectpayment").val() + "']").remove();
			}});
		}
		else
		{
			message("No Payment Selected!");
		}
		return false;
	});
	$("#page #deletetechnician").click(function(){
		if($("#page #selecttechnician").val() != "")
		{
			removeTechnician($("#page #selecttechnician").val(), {close: function(){
				$("#page #selecttechnician option[value='" + $("#page #selecttechnician").val() + "']").remove();
			}});
		}
		else
		{
			message("No Technician Selected!");
		}
		return false;
	});
	$("#page #deletelanguage").click(function(){
		if($("#page #selectlanguage").val() != "")
		{
			removeLanguage($("#page #selectlanguage").val(), {close: function(){
				$("#page #selectlanguage option[value='" + $("#page #selectlanguage").val() + "']").remove();
			}});
		}
		else
		{
			message("No Language Selected!");
		}
		return false;
	});
	$("button").button();
	endLoading("Loading DOM...");
	message("Done Loading!");
}); ///////////////////////////////////end jquery document ready/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/*/
function sendInvoice()
{
	rt = true;
	startLoading("Sending Invoice...");
	$.ajax({async: false, type: "POST", url: "invoice.php", data: {id: $("#selectinvoice").val(), email: $("#emailinvoice #contactemail").val(), message: $("#emailinvoice #message").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		endLoading("Sending Invoice...");
	}});
	return rt;
}
/*function invoice(event, edit)
{
	var d = new Date();
	var dString	= d.getFullYear();
	dString		 += "-";
	dString    += (d.getMonth() + 1 < 10)? "0" + (d.getMonth() + 1) : (d.getMonth() + 1);
	dString		 += "-";
	dString		 += (d.getDate() < 10)? "0" + d.getDate() : d.getDate();
	$("<div id='invoice'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='invoiceid'>Invoice ID</label></td><td><input type='text' name='invoiceid' id='invoiceid' readonly='readonly'/></td></tr>"+
					"<tr><td><label for='invoicejob'>Job</label></td><td><input type='text' name='invoicejob' id='invoicejob' /></td></tr>"+
					"<tr><td><label for='invoicetaxpercentage'>Tax Percentage</label></td><td><input type='number' name='invoicetaxpercentage' id='invoicetaxpercentage' />%</td></tr>"+
					"<tr><td><label for='invoicedate'>Invoice Date</label></td><td><input type='date' name='invoicedate' id='invoicedate' placeholder='yyyy-mm-dd' value='"+dString+"' /></td></tr>"+
					"<tr><td><label for='customerid'>Customer</label></td><td><select name='customerid' id='customerid'><option value=''>Loading Customers...</option></select> <button id='editcustomer'>edit</button> <button id='removecustomer'>X</button></td></tr>"+
					"<tr><td><label for='contactid'>Contact</label></td><td><select name='contactid' id='contactid'><option value=''>Select a Customer</option></select> <button id='editcontact'>edit</button> <button id='removecontact'>X</button></td></tr>"+
					"<tr><td><label for='paymentid'>Payment</label></td><td><select name='paymentid' id='paymentid'><option value=''>Loading Payments...</option></select> <button id='editpayment'>edit</button> <button id='removepayment'>X</button></td></tr>"+
					"<tr><td><label for='invoicecheckno'>Check No</label></td><td><input type='number' name='invoicecheckno' id='invoicecheckno' /></td></tr>"+
					"<tr><td><label for='technicianid'>Technician</label></td><td><select name='technicianid' id='technicianid'><option value=''>Loading Technician...</option></select> <button id='edittechnician'>edit</button> <button id='removetechnician'>X</button></td></tr>"+
					"<tr><td><label for='languageid'>Language</label></td><td><select name='languageid' id='languageid'><option value=''>Loading Language...</option></select> <button id='editlanguage'>edit</button> <button id='removelanguage'>X</button></td></tr>"+
					"<tr><td><label for='invoicepaid'>Paid</label></td><td><select name='invoicepaid' id='invoicepaid'><option value='N' selected='selected'>N</option><option value='Y'>Y</option></select></td></tr>"+
					"<tr><td><label for='lineitems'>Line Items</label></td><td><select name='lineitems' id='lineitems'><option value=''>Line Items</option><option value='newlineitem'>New Line Item</option></select> <button id='editlineitem'>edit</button> <button id='removelineitem'>X</button></td></tr>"+
					"<tr><td><label for='invoicenotes'>Notes</label></td><td><textarea name='invoicenotes' id='invoicenotes'></textarea></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { ((edit)?"Update Invoice" : "Add Invoice"): function(){ if(edit){if(updateInvoice(event)){ $(this).dialog("close"); }}else{{if(addInvoice(event)){ $(this).dialog("close"); }}}, "Cancel": function(){ if(!edit){removeInvoiceLineItems($("#newinvoice #invoiceid").val());} $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: ((edit)?"Edit Invoice" : "New Invoice"),
		width: dwidth
	});
	startLoading("Loading Invoice...");
	$("#invoice button").button();
	if(edit)
	{

	}
	else
	{
		$.post("ajax.php", {task: "getnextautoid", table: "invoices"}, function(data){
			if(!isNaN(parseInt(data)))
			{
				$("#invoice #invoiceid").val(data);
			}
			else
			{
				alert(data);
			}
			endLoading("Loading Invoice...");
		}, "text");
	}
	setCustomerOptions("#newinvoice #customerid");
	setPaymentOptions("#newinvoice #paymentid");
	setTechnicianOptions("#newinvoice #technicianid"
END;
if($_SESSION['userid'] > 0)
{
	$script .= ", {$_SESSION['userid']}";
}
$script .= <<<END
);
	startLoading("Getting Default Language...");
	$.post("ajax.php", {task: "get", table: "settings"}, function(data){
		setLanguageOptions("#newinvoice #languageid", data['languageid']);
		endLoading("Getting Default Language...");
	}, "json");
	$("#newinvoice #customerid").change(function(){
		if($(this).val() == "newcustomer")
		{
			$(this).val("");
			newCustomer({close: function(){
				$("#newinvoice #customerid option[value='newcustomer']").before("<option value='"+$("#newcustomer #customerid").val()+"' selected='selected'>"+$("#newcustomer #customername").val()+"</option>");
				$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid").val() + "'>" + $("#newcustomer #customername").val() + "</option>");
				newContact($("#newcustomer #customerid").val(), {close: function(){
					$("#newinvoice #contactid").append("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
					$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
				}});
			}});
		}
		else if($(this).val() == "")
		{
			$("#newinvoice #contactid").html("<option value='' selected='selected'>Select a Customer</option>");
		}
		else
		{
			setContactOptions($(this).val(), "#newinvoice #contactid");
		}
	});
	$("#newinvoice #contactid").change(function(){
		if($(this).val() == "newcontact")
		{
			$(this).val("");
			newContact($("#newinvoice #customerid").val(), {close: function(){
				$("#newinvoice #contactid option[value='newcontact']").before("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
				$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #paymentid").change(function(){
		if($(this).val() == "newpayment")
		{
			$(this).val("");
			newPayment({close: function(){
				$("#newinvoice #paymentid option[value='newpayment']").before("<option value='"+$("#newpayment #paymentid").val()+"' selected='selected'>"+$("#newpayment #paymentdescription").val()+"</option>");
				$("#page #selectpayment").append("<option value='" + $("#newpayment #paymentid").val() + "'>" + $("#newpayment #paymentdescription").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #technicianid").change(function(){
		if($(this).val() == "newtechnician")
		{
			$(this).val("");
			newTechnician({close: function(){
				$("#newinvoice #technicianid option[value='newtechnician']").before("<option value='"+$("#newtechnician #technicianid").val()+"' selected='selected'>"+$("#newtechnician #technicianfirstname").val()+" "+$("#newtechnician #technicianlastname").val()+"</option>");
				$("#page #selecttechnician").append("<option value='" + $("#newtechnician #technicianid").val() + "'>" + $("#newtechnician #technicianfirstname").val() + " " + $("#newtechnician #technicianlastname").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #languageid").change(function(){
		if($(this).val() == "newlanguage")
		{
			$(this).val("");
			newLanguage({close: function(){
				$("#newinvoice #languageid option[value='newlanguage']").before("<option value='"+$("#newlanguage #languageid").val()+"' selected='selected'>"+$("#newlanguage #languageid").val()+"</option>");
				$("#page #selectlanguage").append("<option value='" + $("#newlanguage #languageid").val() + "'>" + $("#newlanguage #languageid").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #lineitems").change(function(){
		if($(this).val() == "newlineitem")
		{
			$(this).val("");
			newLineItem($("#newinvoice #invoiceid").val(), {close: function(){
				$("#newinvoice #lineitems option[value='newlineitem']").before("<option value='"+$("#newlineitem #lineitemid").val()+"' selected='selected'>"+$("#newlineitem #lineitemquantity").val()+" "+$("#newlineitem #lineitemdescription").val()+"</option>");
			}});
		}
	});
	$("#newinvoice #editcustomer").click(function(){
		if(!isNaN(parseInt($("#newinvoice #customerid").val())) && parseInt($("#newinvoice #customerid").val()) > 0)
		{
			editCustomer($("#newinvoice #customerid").val(), {close: function(){
				$("#newinvoice #customerid option[value='" + $("#editcustomer #customerid").val() + "']").text($("#editcustomer #customername").val());
				$("#page #selectcustomer option[value='" + $("#editcustomer #customerid").val() + "']").text($("#editcustomer #customername").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editcontact").click(function(){
		if(!isNaN(parseInt($("#newinvoice #contactid").val())) && parseInt($("#newinvoice #contactid").val()) > 0)
		{
			editContact($("#newinvoice #contactid").val(), {close: function(){
				$("#newinvoice #contactid option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
				$("#page #selectcontact option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editpayment").click(function(){
		if(!isNaN(parseInt($("#newinvoice #paymentid").val())) && parseInt($("#newinvoice #paymentid").val()) > 0)
		{
			editPayment($("#newinvoice #paymentid").val(), {close: function(){
				$("#newinvoice #paymentid option[value='" + $("#editpayment #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
				$("#page #selectpayment option[value='" + $("#editpayment #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #edittechnician").click(function(){
		if(!isNaN(parseInt($("#newinvoice #technicianid").val())) && parseInt($("#newinvoice #technicianid").val()) > 0)
		{
			editTechnician($("#newinvoice #technicianid").val(), {close: function(){
				$("#newinvoice #technicianid option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
				$("#page #selecttechnician option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editlanguage").click(function(){
		if($("#newinvoice #languageid").val() != '' && $("#newinvoice #languageid").val() != 'newlanguage')
		{
			editLanguage($("#newinvoice #languageid").val(), {close: function(){
				$("#newinvoice #languageid option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
				$("#page #selectlanguage option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editlineitem").click(function(){
		if(!isNaN(parseInt($("#newinvoice #lineitems").val())) && parseInt($("#newinvoice #lineitems").val()) > 0)
		{
			editLineItem($("#newinvoice #lineitems").val(), {close: function(){
				$("#newinvoice #lineitems option[value='" + $("#editlineitem #lineitemid").val() + "']").text($("#editlineitem #lineitemquantity").val() + " " + $("#editlineitem #lineitemdescription").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removecustomer").click(function(){
		if(!isNaN(parseInt($("#newinvoice #customerid").val())) && parseInt($("#newinvoice #customerid").val()) > 0)
		{
			removeCustomer($("#newinvoice #customerid").val(), {close: function(){
				$("#page #selectcustomer option[value='" + $("#newinvoice #customerid").val() + "']").remove();
				$("#newinvoice #customerid option[value='" + $("#newinvoice #customerid").val() + "']").remove();
				$("#newinvoice #contactid").html("<option value=''>Select a Customer</option>");
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removecontact").click(function(){
		if(!isNaN(parseInt($("#newinvoice #contactid").val())) && parseInt($("#newinvoice #contactid").val()) > 0)
		{
			removeContact($("#newinvoice #contactid").val(), {close: function(){
				$("#page #selectcontact option[value='" + $("#newinvoice #contactid").val() + "']").remove();
				$("#newinvoice #contactid option[value='" + $("#newinvoice #contactid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removepayment").click(function(){
		if(!isNaN(parseInt($("#newinvoice #paymentid").val())) && parseInt($("#newinvoice #paymentid").val()) > 0)
		{
			removePayment($("#newinvoice #paymentid").val(), {close: function(){
				$("#page #selectpayment option[value='" + $("#newinvoice #paymentid").val() + "']").remove();
				$("#newinvoice #paymentid option[value='" + $("#newinvoice #paymentid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removetechnician").click(function(){
		if(!isNaN(parseInt($("#newinvoice #technicianid").val())) && parseInt($("#newinvoice #technicianid").val()) > 0)
		{
			removeTechnician($("#newinvoice #technicianid").val(), {close: function(){
				$("#page #selecttechnician option[value='" + $("#newinvoice #technicianid").val() + "']").remove();
				$("#newinvoice #technicianid option[value='" + $("#newinvoice #technicianid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removelanguage").click(function(){
		if($("#newinvoice #languageid").val() != '' && $("#newinvoice #languageid").val() != 'newlanguage')
		{
			removeLanguage($("#newinvoice #languageid").val(), {close: function(){
				$("#page #selectlanguage option[value='" + $("#newinvoice #languageid").val() + "']").remove();
				$("#newinvoice #languageid option[value='" + $("#newinvoice #languageid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removelineitem").click(function(){
		if(!isNaN(parseInt($("#newinvoice #lineitems").val())) && parseInt($("#newinvoice #lineitems").val()) > 0)
		{
			removeLineItem($("#newinvoice #lineitems").val(), {close: function(){
				$("#newinvoice #lineitems option[value='" + $("#newinvoice #lineitems").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #invoicedate").datepicker({ showOn: 'both', dateFormat: 'yy-mm-dd' });
}*/
function newInvoice(event)
{
	var d = new Date();
	var dString	= d.getFullYear();
	dString		 += "-";
	dString    += (d.getMonth() + 1 < 10)? "0" + (d.getMonth() + 1) : (d.getMonth() + 1);
	dString		 += "-";
	dString		 += (d.getDate() < 10)? "0" + d.getDate() : d.getDate();
	$("<div id='newinvoice'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='invoiceid'>Invoice ID</label></td><td><input type='text' name='invoiceid' id='invoiceid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='invoicejob'>Job</label></td><td><input type='text' name='invoicejob' id='invoicejob' /></td></tr>"+
					"<tr><td><label for='invoicetaxpercentage'>Tax Percentage</label></td><td><input type='number' name='invoicetaxpercentage' id='invoicetaxpercentage' />%</td></tr>"+
					"<tr><td><label for='invoicedate'>Invoice Date</label></td><td><input type='date' name='invoicedate' id='invoicedate' placeholder='yyyy-mm-dd' value='"+dString+"' /></td></tr>"+
					"<tr><td><label for='customerid'>Customer</label></td><td><select name='customerid' id='customerid'><option value=''>Loading Customers...</option></select> <button id='editcustomer'>edit</button> <button id='removecustomer'>X</button></td></tr>"+
					"<tr><td><label for='contactid'>Contact</label></td><td><select name='contactid' id='contactid'><option value=''>Select a Customer</option></select> <button id='editcontact'>edit</button> <button id='removecontact'>X</button></td></tr>"+
					"<tr><td><label for='paymentid'>Payment</label></td><td><select name='paymentid' id='paymentid'><option value=''>Loading Payments...</option></select> <button id='editpayment'>edit</button> <button id='removepayment'>X</button></td></tr>"+
					"<tr><td><label for='invoicecheckno'>Check No</label></td><td><input type='number' name='invoicecheckno' id='invoicecheckno' /></td></tr>"+
					"<tr><td><label for='technicianid'>Technician</label></td><td><select name='technicianid' id='technicianid'><option value=''>Loading Technician...</option></select> <button id='edittechnician'>edit</button> <button id='removetechnician'>X</button></td></tr>"+
					"<tr><td><label for='languageid'>Language</label></td><td><select name='languageid' id='languageid'><option value=''>Loading Language...</option></select> <button id='editlanguage'>edit</button> <button id='removelanguage'>X</button></td></tr>"+
					"<tr><td><label for='invoicepaid'>Paid</label></td><td><select name='invoicepaid' id='invoicepaid'><option value='N' selected='selected'>N</option><option value='Y'>Y</option></select></td></tr>"+
					"<tr><td><label for='lineitems'>Line Items</label></td><td><select name='lineitems' id='lineitems'><option value=''>Line Items</option><option value='newlineitem'>New Line Item</option></select> <button id='editlineitem'>edit</button> <button id='removelineitem'>X</button></td></tr>"+
					"<tr><td><label for='invoicenotes'>Notes</label></td><td><textarea name='invoicenotes' id='invoicenotes'></textarea></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Invoice": function(){ if(addInvoice(event)){ $(this).dialog("close"); }}, "Cancel": function(){ removeInvoiceLineItems($("#newinvoice #invoiceid").val()); $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Invoice",
		width: dwidth
	});
	startLoading("Loading New Invoice...");
	$("#newinvoice button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "invoices"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newinvoice #invoiceid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Invoice...");
	}, "text");
	setCustomerOptions("#newinvoice #customerid");
	setPaymentOptions("#newinvoice #paymentid");
	setTechnicianOptions("#newinvoice #technicianid"
END;
if($_SESSION['userid'] > 0)
{
	$script .= ", {$_SESSION['userid']}";
}
$script .= <<<END
);
	startLoading("Getting Default Language...");
	$.post("ajax.php", {task: "get", table: "settings"}, function(data){
		setLanguageOptions("#newinvoice #languageid", data['languageid']);
		endLoading("Getting Default Language...");
	}, "json");
	$("#newinvoice #customerid").change(function(){
		if($(this).val() == "newcustomer")
		{
			$(this).val("");
			newCustomer({close: function(){
				$("#newinvoice #customerid option[value='newcustomer']").before("<option value='"+$("#newcustomer #customerid").val()+"' selected='selected'>"+$("#newcustomer #customername").val()+"</option>");
				$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid").val() + "'>" + $("#newcustomer #customername").val() + "</option>");
				$("#newinvoice #invoicetaxpercentage").val($("#newcustomer #customertax").val());
				newContact($("#newcustomer #customerid").val(), {close: function(){
					$("#newinvoice #contactid").append("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
					$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
				}});
			}});
		}
		else if($(this).val() == "")
		{
			$("#newinvoice #contactid").html("<option value='' selected='selected'>Select a Customer</option>");
			$("#newinvoice #invoicetaxpercentage").val("");
		}
		else
		{
			setCustomerTax($(this).val(), "#newinvoice #invoicetaxpercentage");
			setContactOptions($(this).val(), "#newinvoice #contactid");
		}
	});
	$("#newinvoice #contactid").change(function(){
		if($(this).val() == "newcontact")
		{
			$(this).val("");
			newContact($("#newinvoice #customerid").val(), {close: function(){
				$("#newinvoice #contactid option[value='newcontact']").before("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
				$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #paymentid").change(function(){
		if($(this).val() == "newpayment")
		{
			$(this).val("");
			newPayment({close: function(){
				$("#newinvoice #paymentid option[value='newpayment']").before("<option value='"+$("#newpayment #paymentid").val()+"' selected='selected'>"+$("#newpayment #paymentdescription").val()+"</option>");
				$("#page #selectpayment").append("<option value='" + $("#newpayment #paymentid").val() + "'>" + $("#newpayment #paymentdescription").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #technicianid").change(function(){
		if($(this).val() == "newtechnician")
		{
			$(this).val("");
			newTechnician({close: function(){
				$("#newinvoice #technicianid option[value='newtechnician']").before("<option value='"+$("#newtechnician #technicianid").val()+"' selected='selected'>"+$("#newtechnician #technicianfirstname").val()+" "+$("#newtechnician #technicianlastname").val()+"</option>");
				$("#page #selecttechnician").append("<option value='" + $("#newtechnician #technicianid").val() + "'>" + $("#newtechnician #technicianfirstname").val() + " " + $("#newtechnician #technicianlastname").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #languageid").change(function(){
		if($(this).val() == "newlanguage")
		{
			$(this).val("");
			newLanguage({close: function(){
				$("#newinvoice #languageid option[value='newlanguage']").before("<option value='"+$("#newlanguage #languageid").val()+"' selected='selected'>"+$("#newlanguage #languageid").val()+"</option>");
				$("#page #selectlanguage").append("<option value='" + $("#newlanguage #languageid").val() + "'>" + $("#newlanguage #languageid").val() + "</option>");
			}});
		}
	});
	$("#newinvoice #lineitems").change(function(){
		if($(this).val() == "newlineitem")
		{
			$(this).val("");
			newLineItem($("#newinvoice #invoiceid").val(), {close: function(){
				$("#newinvoice #lineitems option[value='newlineitem']").before("<option value='"+$("#newlineitem #lineitemid").val()+"' selected='selected'>"+$("#newlineitem #lineitemquantity").val()+" "+$("#newlineitem #lineitemdescription").val()+"</option>");
			}});
		}
	});
	$("#newinvoice #editcustomer").click(function(){
		if(!isNaN(parseInt($("#newinvoice #customerid").val())) && parseInt($("#newinvoice #customerid").val()) > 0)
		{
			editCustomer($("#newinvoice #customerid").val(), {close: function(){
				$("#newinvoice #customerid option[value='" + $("#editcustomer #customerid").val() + "']").text($("#editcustomer #customername").val());
				$("#page #selectcustomer option[value='" + $("#editcustomer #customerid").val() + "']").text($("#editcustomer #customername").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editcontact").click(function(){
		if(!isNaN(parseInt($("#newinvoice #contactid").val())) && parseInt($("#newinvoice #contactid").val()) > 0)
		{
			editContact($("#newinvoice #contactid").val(), {close: function(){
				$("#newinvoice #contactid option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
				$("#page #selectcontact option[value='" + $("#editcontact #contactid").val() + "']").text($("#editcontact #contactname").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editpayment").click(function(){
		if(!isNaN(parseInt($("#newinvoice #paymentid").val())) && parseInt($("#newinvoice #paymentid").val()) > 0)
		{
			editPayment($("#newinvoice #paymentid").val(), {close: function(){
				$("#newinvoice #paymentid option[value='" + $("#editpayment #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
				$("#page #selectpayment option[value='" + $("#editpayment #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #edittechnician").click(function(){
		if(!isNaN(parseInt($("#newinvoice #technicianid").val())) && parseInt($("#newinvoice #technicianid").val()) > 0)
		{
			editTechnician($("#newinvoice #technicianid").val(), {close: function(){
				$("#newinvoice #technicianid option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
				$("#page #selecttechnician option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editlanguage").click(function(){
		if($("#newinvoice #languageid").val() != '' && $("#newinvoice #languageid").val() != 'newlanguage')
		{
			editLanguage($("#newinvoice #languageid").val(), {close: function(){
				$("#newinvoice #languageid option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
				$("#page #selectlanguage option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #editlineitem").click(function(){
		if(!isNaN(parseInt($("#newinvoice #lineitems").val())) && parseInt($("#newinvoice #lineitems").val()) > 0)
		{
			editLineItem($("#newinvoice #lineitems").val(), {close: function(){
				$("#newinvoice #lineitems option[value='" + $("#editlineitem #lineitemid").val() + "']").text($("#editlineitem #lineitemquantity").val() + " " + $("#editlineitem #lineitemdescription").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removecustomer").click(function(){
		if(!isNaN(parseInt($("#newinvoice #customerid").val())) && parseInt($("#newinvoice #customerid").val()) > 0)
		{
			removeCustomer($("#newinvoice #customerid").val(), {close: function(){
				$("#page #selectcustomer option[value='" + $("#newinvoice #customerid").val() + "']").remove();
				$("#newinvoice #customerid option[value='" + $("#newinvoice #customerid").val() + "']").remove();
				$("#newinvoice #contactid").html("<option value=''>Select a Customer</option>");
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removecontact").click(function(){
		if(!isNaN(parseInt($("#newinvoice #contactid").val())) && parseInt($("#newinvoice #contactid").val()) > 0)
		{
			removeContact($("#newinvoice #contactid").val(), {close: function(){
				$("#page #selectcontact option[value='" + $("#newinvoice #contactid").val() + "']").remove();
				$("#newinvoice #contactid option[value='" + $("#newinvoice #contactid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removepayment").click(function(){
		if(!isNaN(parseInt($("#newinvoice #paymentid").val())) && parseInt($("#newinvoice #paymentid").val()) > 0)
		{
			removePayment($("#newinvoice #paymentid").val(), {close: function(){
				$("#page #selectpayment option[value='" + $("#newinvoice #paymentid").val() + "']").remove();
				$("#newinvoice #paymentid option[value='" + $("#newinvoice #paymentid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removetechnician").click(function(){
		if(!isNaN(parseInt($("#newinvoice #technicianid").val())) && parseInt($("#newinvoice #technicianid").val()) > 0)
		{
			removeTechnician($("#newinvoice #technicianid").val(), {close: function(){
				$("#page #selecttechnician option[value='" + $("#newinvoice #technicianid").val() + "']").remove();
				$("#newinvoice #technicianid option[value='" + $("#newinvoice #technicianid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removelanguage").click(function(){
		if($("#newinvoice #languageid").val() != '' && $("#newinvoice #languageid").val() != 'newlanguage')
		{
			removeLanguage($("#newinvoice #languageid").val(), {close: function(){
				$("#page #selectlanguage option[value='" + $("#newinvoice #languageid").val() + "']").remove();
				$("#newinvoice #languageid option[value='" + $("#newinvoice #languageid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #removelineitem").click(function(){
		if(!isNaN(parseInt($("#newinvoice #lineitems").val())) && parseInt($("#newinvoice #lineitems").val()) > 0)
		{
			removeLineItem($("#newinvoice #lineitems").val(), {close: function(){
				$("#newinvoice #lineitems option[value='" + $("#newinvoice #lineitems").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newinvoice #invoicedate").datepicker({ showOn: 'both', dateFormat: 'yy-mm-dd' });
}
function newCustomer(event)
{
	var d = new Date();
	var dString = d.getFullYear();
	dString		 += "-";
	dString    += (d.getMonth() + 1 < 10)? "0" + (d.getMonth() + 1) : (d.getMonth() + 1);
	dString		 += "-";
	dString		 += (d.getDate() < 10)? "0" + d.getDate() : d.getDate();
	$("<div id='newcustomer'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='customerid'>Customer ID</label></td><td><input type='text' name='customerid' id='customerid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='customername'>Name</label></td><td><input type='text' name'customername' id='customername' /></td></tr>"+
					"<tr><td><label for='customeraddress'>Address</label></td><td><input type='text' name='customeraddress' id='customeraddress' /></td></tr>"+
					"<tr><td><label for='customercity'>City</label></td><td><input type='text' name='customercity' id='customercity' /></td></tr>"+
					"<tr><td><label for='customerstate'>State</label></td><td><select name='customerstate' id='customerstate'>"+getStateOptions()+"</select></td></tr>"+
					"<tr><td><label for='customerzip'>Zip</label></td><td><input type='text' name='customerzip' id='customerzip' /></td></tr>"+
					"<tr><td><label for='customertax'>Tax<a class='taxlink' style='vertical-align:super; font-size:10px; text-decoration:none; color:#00f;' target='_blank' href='http://www.revenue.state.mn.us/businesses/sut/Pages/SalesTaxCalculator.aspx'>?</a></label></td><td><input type='text' name='customertax' id='customertax' /></td></tr>"+
					"<tr><td><label for='customerphone'>Phone</label></td><td><input type='phone' name='customerphone' id='customerphone' /></td></tr>"+
					"<tr><td><label for='customerdate'>Date</label></td><td><input type='date' name='customerdate' id='customerdate' placeholder='yyyy-mm-dd' value='"+dString+"' /></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Customer": function(){ if(addCustomer(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Customer",
		width: dwidth
	});
	startLoading("Loading New Customer...");
	$("#newcustomer button").button();
	$("#newcustomer #customerdate").datepicker({ showOn: 'both', dateFormat: 'yy-mm-dd' });
	$.post("ajax.php", {task: "getnextautoid", table: "customers"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newcustomer #customerid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Customer...");
	}, "text");
}
function newContact(customerid, event)
{
	$("<div id='newcontact'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='contactid'>Contact ID</label></td><td><input type='text' name='contactid' id='contactid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='customerid'>Customer</label></td><td><select name='customerid' id='customerid'><option value=''>Loading Customers...</option></select> <button id='editcustomer'>edit</button> <button id='removecustomer'>X</button></td></tr>"+
					"<tr><td><label for='contactname'>Name</label></td><td><input type='text' name='contactname' id='contactname' /></td></tr>"+
					"<tr><td><label for='contactphone'>Phone</label></td><td><input type='phone' name='contactphone' id='contactphone' /></td></tr>"+
					"<tr><td><label for='contactemail'>Email</label></td><td><input type='email' name='contactemail' id='contactemail' /></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Contact": function(){ if(addContact(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Contact",
		width: dwidth
	});
	startLoading("Loading New Contact...");
	$("#newcontact button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "contacts"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newcontact #contactid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Contact...");
	}, "text");
	setCustomerOptions("#newcontact #customerid", customerid);
	$("#newcontact #customerid").change(function(){
		if($(this).val() == "newcustomer")
		{
			$(this).val("");
			newCustomer({close: function(){
				$("#newcontact #customerid option[value='newcustomer']").before("<option value='"+$("#newcustomer #customerid").val()+"' selected='selected'>"+$("#newcustomer #customername").val()+"</option>");
				$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid") + "'>" + $("#newcustomer #customername").val() + "</option>");
			}});
		}
	});
	$("#newcontact #editcustomer").click(function(){
		if(!isNaN(parseInt($("#newcontact #customerid").val())) && parseInt($("#newcontact #customerid").val()) > 0)
		{
			editCustomer($("#newcontact #customerid").val(), {close: function(){
				$("#newcontact #customerid option[value='" + $("#newcontact #customerid").val() + "']").text($("#editcustomer #customername").val());
				$("#page #selectcustomer option[value='" + $("#newcontact #customerid").val() + "']").text($("#editcustomer #customername").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newcontact #removecustomer").click(function(){
		if(!isNaN(parseInt($("#newcontact #customerid").val())) && parseInt($("#newcontact #customerid").val()) > 0)
		{
			removeCustomer($("#newcontact #customerid").val(), {close: function(){
				$("#page #selectcustomer option[value='" + $("#newcontact #customerid").val() + "']").remove();
				$("#newcontact #customerid option[value='" + $("#newcontact #customerid").val() + "']").remove();
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
}
function newItem(event)
{
	$("<div id='newitem'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='itemid'>Item ID</label></td><td><input type='text' name='itemid' id='itemid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='itemdescription'>Description</label></td><td><input type='text' name='itemdescription' id='itemdescription' /></td></tr>"+
					"<tr><td><label for='itemprice'>Price</label></td><td><input type='text' name='itemprice' id='itemprice' /></td></tr>"+
					"<tr><td><label for='itemtaxable'>Taxable</label></td><td><select name='itemtaxable' id='itemtaxable'><option value='Y'>Y</option><option value='N' selected='selected'>N</option></select></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Item": function(){ if(addItem(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Item",
		width: dwidth
	});
	startLoading("Loading New Item...");
	$("#newitem button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "items"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newitem #itemid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Item...");
	}, "text");
}
function newPayment(event)
{
	$("<div id='newpayment'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='paymentid'>Payment ID</label></td><td><input type='text' name='paymentid' id='paymentid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='paymentdescription'>Description</label></td><td><input type='text' name='paymentdescription' id='paymentdescription' /></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Payment": function(){ if(addPayment(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Payment",
		width: dwidth
	});
	startLoading("Loading New Payment...");
	$("#newpayment button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "payments"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newpayment #paymentid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Payment...");
	}, "text");
}
function newTechnician(event)
{
	$("<div id='newtechnician'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='technicianid'>Technician ID</label></td><td><input type='text' name='technicianid' id='technicianid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='technicianfistname'>First Name</label></td><td><input type='text' name='technicianfirstname' id='technicianfirstname' /></td></tr>"+
					"<tr><td><label for='technicianlastname'>Last Name</label></td><td><input type='text' name='technicianlastname' id='technicianlastname' /></td></tr>"+
					"<tr><td><label for='technicianusername'>Username</label></td><td><input type='text' name='technicianusername' id='technicianusername' /></td></tr>"+
					"<tr><td><label for='technicianchangepassword'>Change Password</label></td><td><input type='checkbox' name='technicianchangepassword' id='technicianchangepassword' checked='checked' disabled='disabled'/></td></tr>"+
					"<tr><td><label for='technicianpassword'>Password</label></td><td><input type='password' name='technicianpassword' id='technicianpassword' /></td></tr>"+
					"<tr><td><label for='technicianconfirmpassword'>Confirm Password</label></td><td><input type='password' name='technicianconfirmpassword' id='technicianconfirmpassword' /></td></tr>"+
					"<tr><td><label for='technicianemailaddress'>Email Address</label></td><td><input type='text' name='technicianemailaddress' id='technicianemailaddress' /></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Technician": function(){ if(addTechnician(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Technician",
		width: dwidth
	});
	startLoading("Loading New Technician...");
	$("#newtechnician button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "technicians"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newtechnician #technicianid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Technician...");
	}, "text");
	$("#newtechnician #technicianusername").change(function(){
		if($("#newtechnician #technicianemailaddress").val() == "")
		{
			$("#newtechnician #technicianemailaddress").val($("#newtechnician #technicianusername").val() + "@uzitech.com");
		}
	});
}
function newLanguage(event)
{
	$("<div id='newlanguage'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='languageid'>Language ID</label></td><td><input type='text' name='languageid' id='languageid'/></td></tr>"+
					"<tr><td><label for='paid'>PAID</label></td><td><input type='text' name='paid' id='paid'/></td></tr>"+
					"<tr><td><label for='invoice'>INVOICE</label></td><td><input type='text' name='invoice' id='invoice'/></td></tr>"+
					"<tr><td><label for='to'>TO</label></td><td><input type='text' name='to' id='to'/></td></tr>"+
					"<tr><td><label for='technician'>TECHNICIAN</label></td><td><input type='text' name='technician' id='technician'/></td></tr>"+
					"<tr><td><label for='job'>JOB</label></td><td><input type='text' name='job' id='job'/></td></tr>"+
					"<tr><td><label for='date'>DATE</label></td><td><input type='text' name='date' id='date'/></td></tr>"+
					"<tr><td><label for='payment'>PAYMENT</label></td><td><input type='text' name='payment' id='payment'/></td></tr>"+
					"<tr><td><label for='qty'>QTY</label></td><td><input type='text' name='qty' id='qty'/></td></tr>"+
					"<tr><td><label for='description'>DESCRIPTION</label></td><td><input type='text' name='description' id='description'/></td></tr>"+
					"<tr><td><label for='unit_price'>UNIT PRICE</label></td><td><input type='text' name='unit_price' id='unit_price'/></td></tr>"+
					"<tr><td><label for='discount'>DISCOUNT</label></td><td><input type='text' name='discount' id='discount'/></td></tr>"+
					"<tr><td><label for='line_total'>LINE TOTAL</label></td><td><input type='text' name='line_total' id='line_total'/></td></tr>"+
					"<tr><td><label for='total_discount'>TOTAL DISCOUNT</label></td><td><input type='text' name='total_discount' id='total_discount'/></td></tr>"+
					"<tr><td><label for='subtotal'>SUBTOTAL</label></td><td><input type='text' name='subtotal' id='subtotal'/></td></tr>"+
					"<tr><td><label for='sales_tax'>SALES TAX</label></td><td><input type='text' name='sales_tax' id='sales_tax'/></td></tr>"+
					"<tr><td><label for='total'>TOTAL</label></td><td><input type='text' name='total' id='total'/></td></tr>"+
					"<tr><td><label for='invoice_number'>INVOICE #</label></td><td><input type='text' name='invoice_number' id='invoice_number'/></td></tr>"+
					"<tr><td><label for='customer_id'>Customer ID</label></td><td><input type='text' name='customer_id' id='customer_id'/></td></tr>"+
					"<tr><td><label for='make_all_checks_payable_to'>Make all checks payable to</label></td><td><input type='text' name='make_all_checks_payable_to' id='make_all_checks_payable_to'/></td></tr>"+
					"<tr><td><label for='send_money_with_paypal'>Send money with PayPal:</label></td><td><input type='text' name='send_money_with_paypal' id='send_money_with_paypal'/></td></tr>"+
					"<tr><td><label for='thank_you_msg'>THANK YOU FOR YOUR BUSINESS!</label></td><td><input type='text' name='thank_you_msg' id='thank_you_msg'/></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Language": function(){ if(addLanguage(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Language",
		width: dwidth
	});
	$("#newlanguage button").button();
}
function newLineItem(invoiceid, event)
{
	$("<div id='newlineitem'>"+
			"<form action='return false;' method='#'>"+
				"<table><tbody>"+
					"<tr><td><label for='lineitemid'>Line Item ID</label></td><td><input type='text' name='lineitemid' id='lineitemid' readonly='readonly' value='...'/></td></tr>"+
					"<tr><td><label for='invoiceid'>Invoice ID</label></td><td><input type='text' name='invoiceid' id='invoiceid' readonly='readonly' value='" + invoiceid + "'/></td></tr>"+
					"<tr><td><label for='lineitemquantity'>Quantity</label></td><td><input type='number' name='lineitemquantity' id='lineitemquantity' /></td></tr>"+
					"<tr><td><label for='itemid'>Item</label></td><td><select name='itemid' id='itemid'><option value=''>Loading Items...</option></select> <button id='edititem'>edit</button> <button id='removeitem'>X</button></td></tr>"+
					"<tr><td><label for='lineitemdescription'>Description</label></td><td><input type='text' name='lineitemdescription' id='lineitemdescription' readonly='readonly' value=''/></td></tr>"+
					"<tr><td><label for='lineitemprice'>Price</label></td><td><input type='text' name='lineitemprice' id='lineitemprice' readonly='readonly' value=''/></td></tr>"+
					"<tr><td><label for='lineitemtaxable'>Taxable</label></td><td><select name='lineitemtaxable' id='lineitemtaxable' disabled='disabled'><option value='N' selected='selected'>N</option><option value='Y'>Y</option></select></td></tr>"+
					"<tr><td><label for='lineitemdiscount'>Discount</label></td><td><input type='number' name='lineitemdiscount' id='lineitemdiscount' /></td></tr>"+
				"</tbody></table>"+
			"</form>"+
		"</div>")
	.appendTo("body")
	.dialog({
		buttons: { "Add Line Item": function(){ if(addLineItem(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
		close: function(event, ui){ $(this).remove(); },
		height: 'auto',
		modal: true,
		resizable: false,
		title: "New Line Item",
		width: dwidth
	});
	startLoading("Loading New Line Item...");
	$("#newlineitem button").button();
	$.post("ajax.php", {task: "getnextautoid", table: "lineitems"}, function(data){
		if(!isNaN(parseInt(data)))
		{
			$("#newlineitem #lineitemid").val(data);
		}
		else
		{
			alert(data);
		}
		endLoading("Loading New Line Item...");
	}, "text");
	setItemOptions("#newlineitem #itemid");
	$("#newlineitem #itemid").change(function(){
		if($(this).val() == "newitem")
		{
			$(this).val("");
			newItem({close: function(){
				$("#newlineitem #itemid option[value='newitem']").before("<option value='"+$("#newitem #itemid").val()+"' selected='selected'>"+$("#newitem #itemdescription").val()+"</option>");
				$("#page #selectitem").append("<option value='" + $("#newitem #itemid").val() + "'>" + $("#newitem #itemdescription").val() + "</option>");
				$("#newlineitem #lineitemdescription").val($("#newitem #itemdescription").val()).prop("readOnly", true);
				$("#newlineitem #lineitemprice").val($("#newitem #itemprice").val()).prop("readOnly", true);
				$("#newlineitem #lineitemtaxable").val($("#newitem #itemtaxable").val()).prop("disabled", true);
			}});
		}
		else if($(this).val() == "0")
		{
			$("#newlineitem #lineitemdescription").val("").prop("readOnly", false);
			$("#newlineitem #lineitemprice").val("").prop("readOnly", false);
			$("#newlineitem #lineitemtaxable").val("N").prop("disabled", false);
		}
		else if($(this).val() != "")
		{
			startLoading("Getting Item Details...");
			$.post("ajax.php", {task: "get", table: "item", id: $(this).val()}, function(data){
				$("#newlineitem #lineitemdescription").val(data['itemdescription']).prop("readOnly", true);
				$("#newlineitem #lineitemprice").val(data['itemprice']).prop("readOnly", true);
				$("#newlineitem #lineitemtaxable").val(data['itemtaxable']).prop("disabled", true);
				endLoading("Getting Item Details...");
			}, "json");
		}
		else
		{
			$("#newlineitem #lineitemdescription").val("").prop("readOnly", true);
			$("#newlineitem #lineitemprice").val("").prop("readOnly", true);
			$("#newlineitem #lineitemtaxable").val("").prop("disabled", true);
		}
	});
	$("#newlineitem #edititem").click(function(){
		if(!isNaN(parseInt($("#newlineitem #itemid").val())) && parseInt($("#newlineitem #itemid").val()) > 0)
		{
			editItem($("#newlineitem #itemid").val(), {close: function(){
				$("#newlineitem #itemid option[value='" + $("#newlineitem #itemid").val() + "']").text($("#edititem #itemdescription").val());
				$("#newlineitem #lineitemdescription").val($("#edititem #itemdescription").val());
				$("#newlineitem #lineitemprice").val($("#edititem #itemprice").val());
				$("#newlineitem #lineitemtaxable").val($("#edititem #itemtaxable").val());
				$("#page #selectitem option[value='" + $("#newlineitem #itemid").val() + "']").text($("#edititem #itemdescription").val());
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
	$("#newlineitem #removeitem").click(function(){
		if(!isNaN(parseInt($("#newlineitem #itemid").val())) && parseInt($("#newlineitem #itemid").val()) > 0)
		{
			removeItem($("#newlineitem #itemid").val(), {close: function(){
				$("#page #selectitem option[value='" + $("#newlineitem #itemid").val() + "']").remove();
				$("#newlineitem #itemid option[value='" + $("#newlineitem #itemid").val() + "']").remove();
				$("#newlineitem #lineitemdescription").val("");
				$("#newlineitem #lineitemprice").val("");
				$("#newlineitem #lineitemtaxable").val("");
			}});
		}
		else
		{
			message("Can't Do It!");
		}
		return false;
	});
}
function editInvoice(invoiceid, event)
{
	startLoading("Getting Invoice Details...");
	$.post("ajax.php", {task: "get", table: "invoice", id: invoiceid}, function(data){
		$("<div id='editinvoice'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='invoiceid'>Invoice ID</label></td><td><input type='text' name='invoiceid' id='invoiceid' readonly='readonly' value='"+invoiceid+"'/></td></tr>"+
						"<tr><td><label for='invoicejob'>Job</label></td><td><input type='text' name='invoicejob' id='invoicejob' value='"+data['invoicejob']+"' /></td></tr>"+
						"<tr><td><label for='invoicetaxpercentage'>Tax Percentage</label></td><td><input type='number' name='invoicetaxpercentage' id='invoicetaxpercentage' value='"+((data['invoicetaxpercentage'] == "0.00000")? "0" : data['invoicetaxpercentage'])+"' />%</td></tr>"+
						"<tr><td><label for='invoicedate'>Invoice Date</label></td><td><input type='date' name='invoicedate' id='invoicedate' placeholder='yyyy-mm-dd' value='"+data['invoicedate']+"' /></td></tr>"+
						"<tr><td><label for='customerid'>Customer</label></td><td><select name='customerid' id='customerid'><option value=''>Loading Customers...</option></select> <button id='editcustomer'>edit</button> <button id='removecustomer'>X</button></td></tr>"+
						"<tr><td><label for='contactid'>Contact</label></td><td><select name='contactid' id='contactid'><option value=''>Select a Customer</option></select> <button id='editcontact'>edit</button> <button id='removecontact'>X</button></td></tr>"+
						"<tr><td><label for='paymentid'>Payment</label></td><td><select name='paymentid' id='paymentid'><option value=''>Loading Payments...</option></select> <button id='editpayment'>edit</button> <button id='removepayment'>X</button></td></tr>"+
						"<tr><td><label for='invoicecheckno'>Check No</label></td><td><input type='number' name='invoicecheckno' id='invoicecheckno' value='"+((data['invoicecheckno'] == "0")? "" : data['invoicecheckno'])+"' /></td></tr>"+
						"<tr><td><label for='technicianid'>Technician</label></td><td><select name='technicianid' id='technicianid'><option value=''>Loading Technician...</option></select> <button id='edittechnician'>edit</button> <button id='removetechnician'>X</button></td></tr>"+
						"<tr><td><label for='languageid'>Language</label></td><td><select name='languageid' id='languageid'><option value=''>Loading Language...</option></select> <button id='editlanguage'>edit</button> <button id='removelanguage'>X</button></td></tr>"+
						"<tr><td><label for='invoicepaid'>Paid</label></td><td><select name='invoicepaid' id='invoicepaid'><option value='N'"+((data['invoicepaid'] != 'Y')? " selected='selected'" : "")+">N</option><option value='Y'"+((data['invoicepaid'] == 'Y')? " selected='selected'" : "")+">Y</option></select></td></tr>"+
						"<tr><td><label for='lineitems'>Line Items</label></td><td><select name='lineitems' id='lineitems'><option value=''>Line Items</option><option value='newlineitem'>New Line Item</option></select> <button id='editlineitem'>edit</button> <button id='removelineitem'>X</button></td></tr>"+
						"<tr><td><label for='invoicenotes'>Notes</label></td><td><textarea name='invoicenotes' id='invoicenotes'>"+data['invoicenotes']+"</textarea></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Invoice": function(){ if(updateInvoice(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Invoice",
			width: dwidth
		});
		$("#loading").parent().css({'z-index': parseInt($("div#editinvoice").parent().css("z-index")) + 2}).next().css({'z-index': parseInt($("div#editinvoice").parent().css("z-index")) + 1});
		$("#editinvoice button").button();
		setCustomerOptions("#editinvoice #customerid", data['customerid'], true, {close: function(){
			setContactOptions(data['customerid'], "#editinvoice #contactid", data['contactid']);
		}});
		setPaymentOptions("#editinvoice #paymentid", data['paymentid']);
		setTechnicianOptions("#editinvoice #technicianid", data['technicianid']);
		setLanguageOptions("#editinvoice #languageid", data['languageid']);
		setLineItemsOptions(invoiceid, "#editinvoice #lineitems");
		$("#editinvoice #customerid").change(function(){
			if($(this).val() == "newcustomer")
			{
				newCustomer({close: function(){
					$("#editinvoice #customerid option[value='newcustomer']").before("<option value='"+$("#newcustomer #customerid").val()+"' selected='selected'>"+$("#newcustomer #customername").val()+"</option>");
					$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid").val() + "'>" + $("#newcustomer #customername").val() + "</option>");
					$("#editinvoice #invoicetaxpercentage").val($("#newcustomer #customertax").val());
					newContact($("#newcustomer #customerid"), {close: function(){
						$("#editinvoice #contactid option[value='newcontact']").before("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
						$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
					}});
				}});
			}
			else if($(this).val() == "")
			{
				$("#editinvoice #contactid").html("<option value=''>Select a Customer</option>");
				$("#editinvoice #invoicetaxpercentage").val("");
			}
			else
			{
				setCustomerTax($(this).val(), "#editinvoice #invoicetaxpercentage");
				setContactOptions($(this).val(), "#editinvoice #contactid");
			}
		});
		$("#editinvoice #paymentid").change(function(){
			if($(this).val() == "newpayment")
			{
				newPayment({close: function(){
					$("#editinvoice #paymentid option[value='newpayment']").before("<option value='"+$("#newpayment #paymentid").val()+"' selected='selected'>"+$("#newpayment #paymentdescription").val()+"</option>");
					$("#page #selectpayment").append("<option value='" + $("#newpayment #paymentid").val() + "'>" + $("#newpayment #paymentdescription").val() + "</option>");
				}});
			}
		});
		$("#editinvoice #contactid").change(function(){
			if($(this).val() == "newcontact")
			{
				newContact($("#editinvoice #customerid").val(), {close: function(){
					$("#editinvoice #contactid option[value='newcontact']").before("<option value='"+$("#newcontact #contactid").val()+"' selected='selected'>"+$("#newcontact #contactname").val()+"</option>");
					$("#page #selectcontact").append("<option value='" + $("#newcontact #contactid").val() + "'>" + $("#newcontact #contactname").val() + "</option>");
				}});
			}
		});
		$("#editinvoice #technicianid").change(function(){
			if($(this).val() == "newtechnician")
			{
				$(this).val("");
				newTechnician({close: function(){
					$("#editinvoice #technicianid option[value='newtechnician']").before("<option value='"+$("#newtechnician #technicianid").val()+"' selected='selected'>"+$("#newtechnician #technicianfirstname").val()+" "+$("#newtechnician #technicianlastname").val()+"</option>");
					$("#page #selecttechnician").append("<option value='" + $("#newtechnician #technicianid").val() + "'>" + $("#newtechnician #technicianfirstname").val() + " " + $("#newtechnician #technicianlastname").val() + "</option>");
				}});
			}
		});
		$("#editinvoice #languageid").change(function(){
			if($(this).val() == "newlanguage")
			{
				$(this).val("");
				newLanguage({close: function(){
					$("#editinvoice #languageid option[value='newlanguage']").before("<option value='"+$("#newlanguage #languageid").val()+"' selected='selected'>"+$("#newlanguage #languageid").val()+"</option>");
					$("#page #selectlanguage").append("<option value='" + $("#newlanguage #languageid").val() + "'>" + $("#newlanguage #languageid").val() + "</option>");
				}});
			}
		});
		$("#editinvoice #lineitems").change(function(){
			if($(this).val() == "newlineitem")
			{
				$(this).val("");
				newLineItem($("#editinvoice #invoiceid").val(), {close: function(){
					$("#editinvoice #lineitems option[value='newlineitem']").before("<option value='"+$("#newlineitem #lineitemid").val()+"' selected='selected'>"+$("#newlineitem #lineitemquantity").val()+" "+$("#newlineitem #lineitemdescription").val()+"</option>");
				}});
			}
		});
		$("#editinvoice #editcustomer").click(function(){
			if(!isNaN(parseInt($("#editinvoice #customerid").val())) && parseInt($("#editinvoice #customerid").val()) > 0)
			{
				editCustomer($("#editinvoice #customerid").val(), {close: function(){
					$("#editinvoice #customerid option[value='" + $("#editinvoice #customerid").val() + "']").text($("#editcustomer #customername").val());
					$("#page #selectcustomer option[value='" + $("#editinvoice #customerid").val() + "']").text($("#editcustomer #customername").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #editcontact").click(function(){
			if(!isNaN(parseInt($("#editinvoice #contactid").val())) && parseInt($("#editinvoice #contactid").val()) > 0)
			{
				editContact($("#editinvoice #contactid").val(), {close: function(){
					$("#editinvoice #contactid option[value='" + $("#editinvoice #contactid").val() + "']").text($("#editcontact #contactname").val());
					$("#page #selectcontact option[value='" + $("#editinvoice #contactid").val() + "']").text($("#editcontact #contactname").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #editpayment").click(function(){
			if(!isNaN(parseInt($("#editinvoice #paymentid").val())) && parseInt($("#editinvoice #paymentid").val()) > 0)
			{
				editPayment($("#editinvoice #paymentid").val(), {close: function(){
					$("#editinvoice #paymentid option[value='" + $("#editinvoice #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
					$("#page #selectpayment option[value='" + $("#editinvoice #paymentid").val() + "']").text($("#editpayment #paymentdescription").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #edittechnician").click(function(){
			if(!isNaN(parseInt($("#editinvoice #technicianid").val())) && parseInt($("#editinvoice #technicianid").val()) > 0)
			{
				editTechnician($("#editinvoice #technicianid").val(), {close: function(){
					$("#editinvoice #technicianid option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
					$("#page #selecttechnician option[value='" + $("#edittechnician #technicianid").val() + "']").text($("#edittechnician #technicianfirstname").val() + " " + $("#edittechnician #technicianlastname").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #editlanguage").click(function(){
			if($("#editinvoice #languageid").val() != '' && $("#editinvoice #languageid").val() != 'newlanguage')
			{
				editLanguage($("#editinvoice #languageid").val(), {close: function(){
					$("#editinvoice #languageid option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
					$("#page #selectlanguage option[value='" + $("#editlanguage #languageid").val() + "']").text($("#editlanguage #languageid").val());
				}});
			}
			else
			{
				message("No Language Selected!");
			}
			return false;
		});
		$("#editinvoice #editlineitem").click(function(){
			if(!isNaN(parseInt($("#editinvoice #lineitems").val())) && parseInt($("#editinvoice #lineitems").val()) > 0)
			{
				editLineItem($("#editinvoice #lineitems").val(), {close: function(){
					$("#editinvoice #lineitems option[value='" + $("#editinvoice #lineitems").val() + "']").text($("#editlineitem #lineitemquantity").val() + " " + $("#editlineitem #lineitemdescription").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #removecustomer").click(function(){
			if(!isNaN(parseInt($("#editinvoice #customerid").val())) && parseInt($("#editinvoice #customerid").val()) > 0)
			{
				removeCustomer($("#editinvoice #customerid").val(), {close: function(){
					$("#editinvoice #customerid option[value='" + $("#editinvoice #customerid").val() + "']").remove();
					$("#page #selectcustomer option[value='" + $("#editinvoice #customerid").val() + "']").remove();
					$("#editinvoice #contactid").html("<option value=''>Select a Customer</option>");
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #removecontact").click(function(){
			if(!isNaN(parseInt($("#editinvoice #contactid").val())) && parseInt($("#editinvoice #contactid").val()) > 0)
			{
				removeContact($("#editinvoice #contactid").val(), {close: function(){
					$("#editinvoice #contactid option[value='" + $("#editinvoice #contactid").val() + "']").remove();
					$("#page #selectcontact option[value='" + $("#editinvoice #contactid").val() + "']").remove();
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #removepayment").click(function(){
			if(!isNaN(parseInt($("#editinvoice #paymentid").val())) && parseInt($("#editinvoice #paymentid").val()) > 0)
			{
				removePayment($("#editinvoice #paymentid").val(), {close: function(){
					$("#page #selectpayment option[value='" + $("#editinvoice #paymentid").val() + "']").remove();
					$("#editinvoice #paymentid option[value='" + $("#editinvoice #paymentid").val() + "']").remove();
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #removetechnician").click(function(){
			if(!isNaN(parseInt($("#editinvoice #technicianid").val())) && parseInt($("#editinvoice #technicianid").val()) > 0)
			{
				removeTechnician($("#editinvoice #technicianid").val(), {close: function(){
					$("#page #selecttechnician option[value='" + $("#editinvoice #technicianid").val() + "']").remove();
					$("#editinvoice #technicianid option[value='" + $("#editinvoice #technicianid").val() + "']").remove();
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #removelanguage").click(function(){
			if($("#editinvoice #languageid").val() != '' && $("#editinvoice #languageid").val() != 'newlanguage')
			{
				removeLanguage($("#editinvoice #languageid").val(), {close: function(){
					$("#page #selectlanguage option[value='" + $("#editinvoice #languageid").val() + "']").remove();
					$("#editinvoice #languageid option[value='" + $("#editinvoice #languageid").val() + "']").remove();
				}});
			}
			else
			{
				message("No Language Selected!");
			}
			return false;
		});
		$("#editinvoice #removelineitem").click(function(){
			if(!isNaN(parseInt($("#editinvoice #lineitems").val())) && parseInt($("#editinvoice #lineitems").val()) > 0)
			{
				removeLineItem($("#editinvoice #lineitems").val(), {close: function(){
					$("#editinvoice #lineitems option[value='" + $("#editinvoice #lineitems").val() + "']").remove();
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editinvoice #invoicedate").datepicker({ showOn: 'both', dateFormat: 'yy-mm-dd' });
		endLoading("Getting Invoice Details...");
	}, "json");
}
function editCustomer(customerid, event)
{
	startLoading("Getting Customer Details...");
	$.post("ajax.php", {task: "get", table: "customer", id: customerid}, function(data){
		$("<div id='editcustomer'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='customerid'>Customer ID</label></td><td><input type='text' name='customerid' id='customerid' readonly='readonly' value='"+customerid+"'/></td></tr>"+
						"<tr><td><label for='customername'>Name</label></td><td><input type='text' name='customername' id='customername' value='' /></td></tr>"+
						"<tr><td><label for='customeraddress'>Address</label></td><td><input type='text' name='customeraddress' id='customeraddress' value='"+data['customeraddress']+"' /></td></tr>"+
						"<tr><td><label for='customercity'>City</label></td><td><input type='text' name='customercity' id='customercity' value='"+data['customercity']+"' /></td></tr>"+
						"<tr><td><label for='customerstate'>State</label></td><td><select name='customerstate' id='customerstate'>"+getStateOptions(data['customerstate'])+"</select></td></tr>"+
						"<tr><td><label for='customerzip'>Zip</label></td><td><input type='text' name='customerzip' id='customerzip' value='"+data['customerzip']+"' /></td></tr>"+
						"<tr><td><label for='customertax'>Tax<a class='taxlink' style='vertical-align:super; font-size:10px; text-decoration:none; color:#00f;' target='_blank' href='http://www.revenue.state.mn.us/businesses/sut/Pages/SalesTaxCalculator.aspx'>?</a></label></td><td><input type='text' name='customertax' id='customertax' value='"+data['customertax']+"' /></td></tr>"+
						"<tr><td><label for='customerphone'>Phone</label></td><td><input type='phone' name='customerphone' id='customerphone' value='"+data['customerphone']+"' /></td></tr>"+
						"<tr><td><label for='customerdate'>Date</label></td><td><input type='date' name='customerdate' id='customerdate' placeholder='yyyy-mm-dd' value='"+data['customerdate']+"' /></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Customer": function(){ if(updateCustomer(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Customer",
			width: dwidth
		});
		$("#editcustomer #customername").val(data['customername']);
		$("#editcustomer button").button();
		$("#editcustomer #customerdate").datepicker({ showOn: 'both', dateFormat: 'yy-mm-dd' });
		endLoading("Getting Customer Details...");
	}, "json");
}
function editContact(contactid, event)
{
	startLoading("Getting Contact Details...");
	$.post("ajax.php", {task: "get", table: "contact", id: contactid}, function(data){
		$("<div id='editcontact'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='contactid'>Contact ID</label></td><td><input type='text' name='contactid' id='contactid' readonly='readonly' value='"+contactid+"'/></td></tr>"+
						"<tr><td><label for='customerid'>Customer</label></td><td><select name='customerid' id='customerid'><option value=''>Loading Customers...</option></select> <button id='editcustomer'>edit</button> <button id='removecustomer'>X</button></td></tr>"+
						"<tr><td><label for='contactname'>Name</label></td><td><input type='text' name='contactname' id='contactname' value='"+data['contactname']+"' /></td></tr>"+
						"<tr><td><label for='contactphone'>Phone</label></td><td><input type='phone' name='contactphone' id='contactphone' value='"+data['contactphone']+"' /></td></tr>"+
						"<tr><td><label for='contactemail'>Email</label></td><td><input type='email' name='contactemail' id='contactemail' value='"+data['contactemail']+"' /></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Contact": function(){ if(updateContact(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Contact",
			width: dwidth
		});
		$("#loading").parent().css({'z-index': parseInt($("div#editcontact").parent().css("z-index")) + 2}).next().css({'z-index': parseInt($("div#editcontact").parent().css("z-index")) + 1});
		$("#editcontact button").button();
		setCustomerOptions("#editcontact #customerid", data['customerid']);
		$("#editcontact #customerid").change(function(){
			if($(this).val() == "newcustomer")
			{
				newCustomer({close: function(){
					$("#editcontact #customerid option[value='newcustomer']").before("<option value='"+$("#newcustomer #customerid").val()+"' selected='selected'>"+$("#newcustomer #customername").val()+"</option>");
					$("#page #selectcustomer").append("<option value='" + $("#newcustomer #customerid").val() + "'>" + $("#newcustomer #customername").val() + "</option>");
				}});
			}
		});
		$("#editcontact #editcustomer").click(function(){
			if(!isNaN(parseInt($("#editcontact #customerid").val())) && parseInt($("#editcontact #customerid").val()) > 0)
			{
				editCustomer($("#editcontact #customerid").val(), {close: function(){
					$("#editcontact #customerid option[value='" + $("#editcontact #customerid").val() + "']").text($("#editcustomer #customername").val());
					$("#page #selectcustomer option[value='" + $("#editcontact #customerid").val() + "']").text($("#editcustomer #customername").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editcontact #removecustomer").click(function(){
			if(!isNaN(parseInt($("#editcontact #customerid").val())) && parseInt($("#editcontact #customerid").val()) > 0)
			{
				removeCustomer($("#editcontact #customerid").val(), {close: function(){
					$("#page #selectcustomer option[value='" + $("#editcontact #customerid").val() + "']").remove();
					$("#editcontact #customerid option[value='" + $("#editcontact #customerid").val() + "']").remove();
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		endLoading("Getting Contact Details...");
	}, "json");
}
function editItem(itemid, event)
{
	startLoading("Getting Item Details...");
	$.post("ajax.php", {task: "get", table: "item", id: itemid}, function(data){
		$("<div id='edititem'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='itemid'>Item ID</label></td><td><input type='text' name='itemid' id='itemid' readonly='readonly' value='"+itemid+"'/></td></tr>"+
						"<tr><td><label for='itemdescription'>Description</label></td><td><input type='text' name='itemdescription' id='itemdescription' value='"+data['itemdescription']+"' /></td></tr>"+
						"<tr><td><label for='itemprice'>Price</label></td><td><input type='text' name='itemprice' id='itemprice' value='"+data['itemprice']+"' /></td></tr>"+
						"<tr><td><label for='itemtaxable'>Taxable</label></td><td><select name='itemtaxable' id='itemtaxable'><option value='Y'"+((data['itemtaxable'] == "Y")? " selected='selected'" : "")+">Y</option><option value='N'"+((data['itemtaxable'] != "Y")? " selected='selected'" : "")+">N</option></select></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Item": function(){ if(updateItem(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Item",
			width: dwidth
		});
		$("#edititem button").button();
		endLoading("Getting Item Details...");
	}, "json");
}
function editPayment(paymentid, event)
{
	startLoading("Getting Payment Details...");
	$.post("ajax.php", {task: "get", table: "payment", id: paymentid}, function(data){
		$("<div id='editpayment'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='paymentid'>Payment ID</label></td><td><input type='text' name='paymentid' id='paymentid' readonly='readonly' value='"+paymentid+"'/></td></tr>"+
						"<tr><td><label for='paymentdescription'>Description</label></td><td><input type='text' name='paymentdescription' id='paymentdescription' value='"+data['paymentdescription']+"'/></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Payment": function(){ if(updatePayment(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Payment",
			width: dwidth
		});
		$("#editpayment button").button();
		endLoading("Getting Payment Details...");
	}, "json");
}
function editTechnician(technicianid, event)
{
	startLoading("Getting Technician Details...");
	$.post("ajax.php", {task: "get", table: "technician", id: technicianid}, function(data){
		$("<div id='edittechnician'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='technicianid'>Technician ID</label></td><td><input type='text' name='technicianid' id='technicianid' readonly='readonly' value='"+technicianid+"'/></td></tr>"+
						"<tr><td><label for='technicianfistname'>First Name</label></td><td><input type='text' name='technicianfirstname' id='technicianfirstname' value='"+data['technicianfirstname']+"' /></td></tr>"+
						"<tr><td><label for='technicianlastname'>Last Name</label></td><td><input type='text' name='technicianlastname' id='technicianlastname' value='"+data['technicianlastname']+"' /></td></tr>"+
						"<tr><td><label for='technicianusername'>Username</label></td><td><input type='text' name='technicianusername' id='technicianusername' value='"+data['technicianusername']+"' /></td></tr>"+
						"<tr><td><label for='technicianchangepassword'>Change Password</label></td><td><input type='checkbox' name='technicianchangepassword' id='technicianchangepassword' /></td></tr>"+
						"<tr><td><label for='technicianpassword'>Password</label></td><td><input type='password' name='technicianpassword' id='technicianpassword' readonly='readonly' /></td></tr>"+
						"<tr><td><label for='technicianconfirmpassword'>Confirm Password</label></td><td><input type='password' name='technicianconfirmpassword' id='technicianconfirmpassword' readonly='readonly' /></td></tr>"+
						"<tr><td><label for='technicianemailaddress'>Email Address</label></td><td><input type='text' name='technicianemailaddress' id='technicianemailaddress' value='"+data['technicianemailaddress']+"' /></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Technician": function(){ if(updateTechnician(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Technician",
			width: dwidth
		});
		$("#edittechnician button").button();
		$("#edittechnician #technicianchangepassword").click(function(){
			if(this.checked)
			{
				$("#edittechnician #technicianpassword").prop('readonly', false);
				$("#edittechnician #technicianconfirmpassword").prop('readonly', false);
			}
			else
			{
				$("#edittechnician #technicianpassword").val("");
				$("#edittechnician #technicianconfirmpassword").val("");
				$("#edittechnician #technicianpassword").prop('readonly', true);
				$("#edittechnician #technicianconfirmpassword").prop('readonly', true);
			}
		});
		$("#edittechnician #technicianusername").change(function(){
			if($("#edittechnician #technicianemailaddress").val() == "")
			{
				$("#edittechnician #technicianemailaddress").val($("#edittechnician #technicianusername").val() + "@uzitech.com");
			}
		});
		endLoading("Getting Technician Details...");
	}, "json");
}
function editLanguage(languageid, event)
{
	startLoading("Getting Language Details...");
	$.post("ajax.php", {task: "get", table: "language", id: languageid}, function(data){
		$("<div id='editlanguage'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='languageid'>Language ID</label></td><td><input type='text' name='languageid' id='languageid' readonly='readonly' value='"+languageid+"'/></td></tr>"+
						"<tr><td><label for='paid'>PAID</label></td><td><input type='text' name='paid' id='paid' value='"+data['paid']+"' /></td></tr>"+
						"<tr><td><label for='invoice'>INVOICE</label></td><td><input type='text' name='invoice' id='invoice' value='"+data['invoice']+"' /></td></tr>"+
						"<tr><td><label for='to'>TO</label></td><td><input type='text' name='to' id='to' value='"+data['to']+"' /></td></tr>"+
						"<tr><td><label for='technician'>TECHNICIAN</label></td><td><input type='text' name='technician' id='technician' value='"+data['technician']+"' /></td></tr>"+
						"<tr><td><label for='job'>JOB</label></td><td><input type='text' name='job' id='job' value='"+data['job']+"' /></td></tr>"+
						"<tr><td><label for='date'>DATE</label></td><td><input type='text' name='date' id='date' value='"+data['date']+"' /></td></tr>"+
						"<tr><td><label for='payment'>PAYMENT</label></td><td><input type='text' name='payment' id='payment' value='"+data['payment']+"' /></td></tr>"+
						"<tr><td><label for='qty'>QTY</label></td><td><input type='text' name='qty' id='qty' value='"+data['qty']+"' /></td></tr>"+
						"<tr><td><label for='description'>DESCRIPTION</label></td><td><input type='text' name='description' id='description' value='"+data['description']+"' /></td></tr>"+
						"<tr><td><label for='unit_price'>UNIT PRICE</label></td><td><input type='text' name='unit_price' id='unit_price' value='"+data['unit_price']+"' /></td></tr>"+
						"<tr><td><label for='discount'>DISCOUNT</label></td><td><input type='text' name='discount' id='discount' value='"+data['discount']+"' /></td></tr>"+
						"<tr><td><label for='line_total'>LINE TOTAL</label></td><td><input type='text' name='line_total' id='line_total' value='"+data['line_total']+"' /></td></tr>"+
						"<tr><td><label for='total_discount'>TOTAL DISCOUNT</label></td><td><input type='text' name='total_discount' id='total_discount' value='"+data['total_discount']+"' /></td></tr>"+
						"<tr><td><label for='subtotal'>SUBTOTAL</label></td><td><input type='text' name='subtotal' id='subtotal' value='"+data['subtotal']+"' /></td></tr>"+
						"<tr><td><label for='sales_tax'>SALES TAX</label></td><td><input type='text' name='sales_tax' id='sales_tax' value='"+data['sales_tax']+"' /></td></tr>"+
						"<tr><td><label for='total'>TOTAL</label></td><td><input type='text' name='total' id='total' value='"+data['total']+"' /></td></tr>"+
						"<tr><td><label for='invoice_number'>INVOICE #</label></td><td><input type='text' name='invoice_number' id='invoice_number' value='"+data['invoice_number']+"' /></td></tr>"+
						"<tr><td><label for='customer_id'>Customer ID</label></td><td><input type='text' name='customer_id' id='customer_id' value='"+data['customer_id']+"' /></td></tr>"+
						"<tr><td><label for='make_all_checks_payable_to'>Make all checks payable to</label></td><td><input type='text' name='make_all_checks_payable_to' id='make_all_checks_payable_to' value='"+data['make_all_checks_payable_to']+"' /></td></tr>"+
						"<tr><td><label for='send_money_with_paypal'>Send money with PayPal:</label></td><td><input type='text' name='send_money_with_paypal' id='send_money_with_paypal' value='"+data['send_money_with_paypal']+"' /></td></tr>"+
						"<tr><td><label for='thank_you_msg'>THANK YOU FOR YOUR BUSINESS!</label></td><td><input type='text' name='thank_you_msg' id='thank_you_msg' value='"+data['thank_you_msg']+"' /></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Language": function(){ if(updateLanguage(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Language",
			width: dwidth
		});
		$("#editlanguage button").button();
		endLoading("Getting Language Details...");
	}, "json");
}
function editLineItem(lineitemid, event)
{
	startLoading("Getting Line Item Details...");
	$.post("ajax.php", {task: "get", table: "lineitem", id: lineitemid}, function(data){
		$("<div id='editlineitem'>"+
				"<form action='return false;' method='#'>"+
					"<table><tbody>"+
						"<tr><td><label for='lineitemid'>Line Item ID</label></td><td><input type='text' name='lineitemid' id='lineitemid' readonly='readonly' value='"+lineitemid+"'/></td></tr>"+
						"<tr><td><label for='invoiceid'>Invoice ID</label></td><td><input type='text' name='invoiceid' id='invoiceid' readonly='readonly' value='"+data['invoiceid']+"' /></td></tr>"+
						"<tr><td><label for='lineitemquantity'>Quantity</label></td><td><input type='number' name='lineitemquantity' id='lineitemquantity' value='"+data['lineitemquantity']+"' /></td></tr>"+
						"<tr><td><label for='itemid'>Item</label></td><td><select name='itemid' id='itemid'><option value=''>Loading Items...</option></select> <button id='edititem'>edit</button> <button id='removeitem'>X</button></td></tr>"+
						"<tr><td><label for='lineitemdescription'>Description</label></td><td><input type='text' name='lineitemdescription' id='lineitemdescription'"+((data['itemid'] != "0")? " readonly='readonly'" : "")+" value='"+data['lineitemdescription']+"'/></td></tr>"+
						"<tr><td><label for='lineitemprice'>Price</label></td><td><input type='text' name='lineitemprice' id='lineitemprice'"+((data['itemid'] != "0")? " readonly='readonly'" : "")+" value='"+data['lineitemprice']+"'/></td></tr>"+
						"<tr><td><label for='lineitemtaxable'>Taxable</label></td><td><select name='lineitemtaxable' id='lineitemtaxable'"+((data['itemid'] != "0")? " disabled='disabled'" : "")+"><option value='N'"+((data['lineitemtaxable'] != "Y")? " selected='selected'" : "")+">N</option><option value='Y'"+((data['lineitemtaxable'] == "Y")? " selected='selected'" : "")+">Y</option></select></td></tr>"+
						"<tr><td><label for='lineitemdiscount'>Discount</label></td><td><input type='number' name='lineitemdiscount' id='lineitemdiscount' value='"+data['lineitemdiscount']+"' /></td></tr>"+
					"</tbody></table>"+
				"</form>"+
			"</div>")
		.appendTo("body")
		.dialog({
			buttons: { "Update Line Item": function(){ if(updateLineItem(event)){ $(this).dialog("close"); }}, "Cancel": function(){ $(this).dialog("close"); }},
			close: function(event, ui){ $(this).remove(); },
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Edit Line Item",
			width: dwidth
		});
		$("#loading").parent().css({'z-index': parseInt($("div#editlineitem").parent().css("z-index")) + 2}).next().css({'z-index': parseInt($("div#editlineitem").parent().css("z-index")) + 1});
		$("#editlineitem button").button();
		setItemOptions("#editlineitem #itemid", data['itemid']);
		$("#editlineitem #itemid").change(function(){
			if($(this).val() == "newitem")
			{
				newItem({close: function(){
					$("#editlineitem #itemid option[value='newitem']").before("<option value='"+$("#newitem #itemid").val()+"' selected='selected'>"+$("#newitem #itemdescription").val()+"</option>");
					$("#page #selectitem").append("<option value='" + $("#newitem #itemid").val() + "'>" + $("#newitem #itemdescription").val() + "</option>");
					$("#editlineitem #lineitemdescription").val($("#newitem #itemdescription").val()).prop("readOnly", true);
					$("#editlineitem #lineitemprice").val($("#newitem #itemprice").val()).prop("readOnly", true);
					$("#editlineitem #lineitemtaxable").val($("#newitem #itemtaxable").val()).prop("disabled", true);
				}});
			}
			else if($(this).val() == "0")
			{
				$("#editlineitem #lineitemdescription").val("").prop("readOnly", false);
				$("#editlineitem #lineitemprice").val("").prop("readOnly", false);
				$("#editlineitem #lineitemtaxable").val("").prop("disabled", false);
			}
			else if($(this).val() != "")
			{
				startLoading("Getting Item Details...");
				$.post("ajax.php", {task: "get", table: "item", id: $(this).val()}, function(data){
					$("#editlineitem #lineitemdescription").val(data['itemdescription']).prop("readOnly", true);
					$("#editlineitem #lineitemprice").val(data['itemprice']).prop("readOnly", true);
					$("#editlineitem #lineitemtaxable").val(data['itemtaxable']).prop("disabled", true);
					endLoading("Getting Item Details...");
				}, "json");
			}
			else
			{
				$("#editlineitem #lineitemdescription").val("").prop("readOnly", true);
				$("#editlineitem #lineitemprice").val("").prop("readOnly", true);
				$("#editlineitem #lineitemtaxable").val("").prop("disabled", true);
			}
		});
		$("#editlineitem #edititem").click(function(){
			if(!isNaN(parseInt($("#editlineitem #itemid").val())) && parseInt($("#editlineitem #itemid").val()) > 0)
			{
				editItem($("#editlineitem #itemid").val(), {close: function(){
					$("#editlineitem #itemid option[value='" + $("#editlineitem #itemid").val() + "']").text($("#edititem #itemdescription").val());
					$("#editlineitem #lineitemdescription").val($("#edititem #itemdescription").val());
					$("#editlineitem #lineitemprice").val($("#edititem #itemprice").val());
					$("#editlineitem #lineitemtaxable").val($("#edititem #itemtaxable").val());
					$("#page #selectitem option[value='" + $("#editlineitem #itemid").val() + "']").text($("#edititem #itemdescription").val());
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		$("#editlineitem #removeitem").click(function(){
			if(!isNaN(parseInt($("#editlineitem #itemid").val())) && parseInt($("#editlineitem #itemid").val()) > 0)
			{
				removeItem($("#editlineitem #itemid").val(), {close: function(){
					$("#page #selectitem option[value='" + $("#editlineitem #itemid").val() + "']").remove();
					$("#editlineitem #itemid option[value='" + $("#editlineitem #itemid").val() + "']").remove();
					$("#editlineitem #lineitemdescription").val("");
					$("#editlineitem #lineitemprice").val("");
					$("#editlineitem #lineitemtaxable").val("");
				}});
			}
			else
			{
				message("Can't Do It!");
			}
			return false;
		});
		endLoading("Getting Line Item Details...");
	}, "json");
}
function setInvoiceOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Invoices...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	invoiceSID = Math.random();
	$.post("ajax.php", {task: "get", table: "invoices", q: ((q != undefined)? q : ""), sid: invoiceSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == invoiceSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select an Invoice</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newinvoice'>New Invoice</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Invoices...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setCustomerOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Customers...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	customerSID = Math.random();
	$.post("ajax.php", {task: "get", table: "customers", q: ((q != undefined)? q : ""), sid: customerSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == customerSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select a Customer</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newcustomer'>New Customer</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Customers...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setContactOptions(customerid, selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Contacts...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	contactSID = Math.random();
	$.post("ajax.php", {task: "get", table: "contacts", customerid: customerid, q: ((q != undefined)? q : ""), sid: contactSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == contactSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select a Contact</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal || "(" + i + ")" == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newcontact'>New Contact</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Contacts...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setItemOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Items...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	itemSID = Math.random();
	$.post("ajax.php", {task: "get", table: "items", q: ((q != undefined)? q : ""), sid: itemSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == itemSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select an Item</option>";

				if(q == undefined)
				{
					options += "<option value='0'"+((selectedVal == "0")? " selected='selected'" : "" )+">MISC</option>";
				}

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newitem'>New Item</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Items...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setPaymentOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Payments...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	paymentSID = Math.random();
	$.post("ajax.php", {task: "get", table: "payments", q: ((q != undefined)? q : ""), sid: paymentSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == paymentSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select a Payment</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newpayment'>New Payment</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Payments...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setTechnicianOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Technicians...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	technicianSID = Math.random();
	$.post("ajax.php", {task: "get", table: "technicians", q: ((q != undefined)? q : ""), sid: technicianSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == technicianSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select a Technician</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newtechnician'>New Technician</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Technicians...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setLanguageOptions(selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Languages...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	languageSID = Math.random();
	$.post("ajax.php", {task: "get", table: "languages", q: ((q != undefined)? q : ""), sid: languageSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == languageSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Select a Language</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newlanguage'>New Language</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Languages...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function setLineItemsOptions(invoiceid, selector, selectedVal, newVal, event, q, load)
{
	if(load !== false)
	{
		startLoading("Getting Line Items...");
	}
	else
	{
		$(selector).prop("disabled", true);
	}
	lineitemSID = Math.random();
	$.post("ajax.php", {task: "get", table: "lineitems", invoiceid: invoiceid, q: ((q != undefined)? q : ""), sid: lineitemSID}, function(data){
		if($.isArray(data))
		{
			if(data.shift() == lineitemSID)
			{
				var options = "<option value=''"+((selectedVal == undefined)? " selected='selected'" : "" )+">Line Items</option>"

				for(var i = 0; i < data.length; i++)
				{
					options += "<option value='"+data[i]['value']+"'"+((data[i]['value'] == selectedVal)? " selected='selected'" : "" )+">"+data[i]['text']+"</option>";
				}

				if(newVal != false)
				{
					options += "<option value='newlineitem'>New Line Item</option>";
				}
				$(selector).html(options);
				if(event != undefined && $.isFunction(event['close']))
				{
					event['close']();
				}
				if(load !== false)
				{
					endLoading("Getting Line Items...");
				}
				else
				{
					$(selector).prop("disabled", false);
				}
			}
		}
		else
		{
			alert(data);
		}
	}, "json");
}
function addInvoice(event)
{
	startLoading("Adding Invoice...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "invoices", contactid: $("#newinvoice #contactid").val(), customerid: $("#newinvoice #customerid").val(), invoicejob: $("#newinvoice #invoicejob").val(), invoicecheckno: $("#newinvoice #invoicecheckno").val(), paymentid: $("#newinvoice #paymentid").val(), technicianid: $("#newinvoice #technicianid").val(), languageid: $("#newinvoice #languageid").val(), invoicetaxpercentage: $("#newinvoice #invoicetaxpercentage").val(), invoicedate: $("#newinvoice #invoicedate").val(), invoicepaid: $("#newinvoice #invoicepaid").val(), invoicenotes: $("#newinvoice #invoicenotes").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Invoice...");
	}});
	return rt;
}
function addCustomer(event)
{
	startLoading("Adding Customer...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "customers", customername: $("#newcustomer #customername").val(), customeraddress: $("#newcustomer #customeraddress").val(), customercity: $("#newcustomer #customercity").val(), customerstate: $("#newcustomer #customerstate").val(), customerzip: $("#newcustomer #customerzip").val(), customertax: $("#newcustomer #customertax").val(), customerphone: $("#newcustomer #customerphone").val(), customerdate: $("#newcustomer #customerdate").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Customer...");
	}});
	return rt;
}
function addContact(event)
{
	startLoading("Adding Contact...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "contacts", customerid: $("#newcontact #customerid").val(), contactname: $("#newcontact #contactname").val(), contactphone: $("#newcontact #contactphone").val(), contactemail: $("#newcontact #contactemail").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Contact...");
	}});
	return rt;
}
function addItem(event)
{
	startLoading("Adding Item...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "items", itemdescription: $("#newitem #itemdescription").val(), itemprice: $("#newitem #itemprice").val(), itemtaxable: $("#newitem #itemtaxable").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Item...");
	}});
	return rt;
}
function addPayment(event)
{
	startLoading("Adding Payment...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "payments", paymentdescription: $("#newpayment #paymentdescription").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Payment...");
	}});
	return rt;
}
function addTechnician(event)
{
	$("#newtechnician #technicianchangepassword").prop('checked', true);
	$("#newtechnician #technicianchangepassword").prop('disabled', true);
	if($("#newtechnician #technicianpassword").val().length < 7)
	{
		alert("Password must be at least 7 characters.");
		$("#newtechnician #technicianpassword").focus();
		$("#newtechnician #technicianpassword").select();
		return false;
	}
	else if($("#newtechnician #technicianpassword").val() != $("#newtechnician #technicianconfirmpassword").val())
	{
		alert("Passwords are not the same.");
		$("#newtechnician #technicianpassword").focus();
		$("#newtechnician #technicianpassword").select();
		return false;
	}
	startLoading("Adding Technician...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "technicians", technicianfirstname: $("#newtechnician #technicianfirstname").val(), technicianlastname: $("#newtechnician #technicianlastname").val(), technicianusername: $("#newtechnician #technicianusername").val(), technicianpassword: $.sha1($("#newtechnician #technicianpassword").val()), technicianemailaddress: $("#newtechnician #technicianemailaddress").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Technician...");
	}});
	return rt;
}
function addLanguage(event)
{
	startLoading("Adding Language...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "languages", languageid: $("#newlanguage #languageid").val(), paid: $("#newlanguage #paid").val(), invoice: $("#newlanguage #invoice").val(), to: $("#newlanguage #to").val(), technician: $("#newlanguage #technician").val(), job: $("#newlanguage #job").val(), date: $("#newlanguage #date").val(), payment: $("#newlanguage #payment").val(), qty: $("#newlanguage #qty").val(), description: $("#newlanguage #description").val(), unit_price: $("#newlanguage #unit_price").val(), discount: $("#newlanguage #discount").val(), line_total: $("#newlanguage #line_total").val(), total_discount: $("#newlanguage #total_discount").val(), subtotal: $("#newlanguage #subtotal").val(), sales_tax: $("#newlanguage #sales_tax").val(), total: $("#newlanguage #total").val(), invoice_number: $("#newlanguage #invoice_number").val(), customer_id: $("#newlanguage #customer_id").val(), make_all_checks_payable_to: $("#newlanguage #make_all_checks_payable_to").val(), send_money_with_paypal: $("#newlanguage #send_money_with_paypal").val(), thank_you_msg: $("#newlanguage #thank_you_msg").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Language...");
	}});
	return rt;
}
function addLineItem(event)
{
	startLoading("Adding Line Item...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "add", table: "lineitems", invoiceid: $("#newlineitem #invoiceid").val(), lineitemquantity: $("#newlineitem #lineitemquantity").val(), itemid: $("#newlineitem #itemid").val(), lineitemdescription: $("#newlineitem #lineitemdescription").val(), lineitemprice: $("#newlineitem #lineitemprice").val(), lineitemtaxable: $("#newlineitem #lineitemtaxable").val(), lineitemdiscount: $("#newlineitem #lineitemdiscount").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Adding Line Item...");
	}});
	return rt;
}
function updateInvoice(event)
{
	startLoading("Updating Invoice...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "invoices", invoiceid: $("#editinvoice #invoiceid").val(), contactid: $("#editinvoice #contactid").val(), customerid: $("#editinvoice #customerid").val(), invoicejob: $("#editinvoice #invoicejob").val(), invoicecheckno: $("#editinvoice #invoicecheckno").val(), paymentid: $("#editinvoice #paymentid").val(), technicianid: $("#editinvoice #technicianid").val(), languageid: $("#editinvoice #languageid").val(), invoicetaxpercentage: $("#editinvoice #invoicetaxpercentage").val(), invoicedate: $("#editinvoice #invoicedate").val(), invoicepaid: $("#editinvoice #invoicepaid").val(), invoicenotes: $("#editinvoice #invoicenotes").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Invoice...");
	}});
	return rt;
}
function updateCustomer(event)
{
	startLoading("Updating Customer...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "customers", customerid: $("#editcustomer #customerid").val(), customername: $("#editcustomer #customername").val(), customeraddress: $("#editcustomer #customeraddress").val(), customercity: $("#editcustomer #customercity").val(), customerstate: $("#editcustomer #customerstate").val(), customerzip: $("#editcustomer #customerzip").val(), customertax: $("#editcustomer #customertax").val(), customerphone: $("#editcustomer #customerphone").val(), customerdate: $("#editcustomer #customerdate").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Customer...");
	}});
	return rt;
}
function updateContact(event)
{
	startLoading("Updating Contact...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "contacts", contactid: $("#editcontact #contactid").val(), customerid: $("#editcontact #customerid").val(), contactname: $("#editcontact #contactname").val(), contactphone: $("#editcontact #contactphone").val(), contactemail: $("#editcontact #contactemail").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Contact...");
	}});
	return rt;
}
function updateItem(event)
{
	startLoading("Updating Item...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "items", itemid: $("#edititem #itemid").val(), itemdescription: $("#edititem #itemdescription").val(), itemprice: $("#edititem #itemprice").val(), itemtaxable: $("#edititem #itemtaxable").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Item...");
	}});
	return rt;
}
function updatePayment(event)
{
	startLoading("Updating Payment...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "payments", paymentid: $("#editpayment #paymentid").val(), paymentdescription: $("#editpayment #paymentdescription").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Payment...");
	}});
	return rt;
}
function updateTechnician(event)
{
	var pass = -1;
	if($("#edittechnician #technicianchangepassword").prop('checked'))
	{
		if($("#edittechnician #technicianpassword").val().length < 7)
		{
			alert("Password must be at least 7 characters.");
			$("#edittechnician #technicianpassword").focus();
			$("#edittechnician #technicianpassword").select();
			return false;
		}
		else if($("#edittechnician #technicianpassword").val() == $("#edittechnician #technicianconfirmpassword").val())
		{
			pass = $("#edittechnician #technicianpassword").val();
		}
		else
		{
			alert("Passwords are not the same.");
			$("#edittechnician #technicianpassword").focus();
			$("#edittechnician #technicianpassword").select();
			return false;
		}
	}
	startLoading("Updating Technician...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "technicians", technicianid: $("#edittechnician #technicianid").val(), technicianfirstname: $("#edittechnician #technicianfirstname").val(), technicianlastname: $("#edittechnician #technicianlastname").val(), technicianusername: $("#edittechnician #technicianusername").val(), technicianpassword: ((pass === -1)? null : $.sha1(pass)), technicianemailaddress: $("#edittechnician #technicianemailaddress").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Technician...");
	}});
	return rt;
}
function updateLineItem(event)
{
	startLoading("Updating Line Item...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "lineitems", lineitemid: $("#editlineitem #lineitemid").val(), invoiceid: $("#editlineitem #invoiceid").val(), lineitemquantity: $("#editlineitem #lineitemquantity").val(), itemid: $("#editlineitem #itemid").val(), lineitemdescription: $("#editlineitem #lineitemdescription").val(), lineitemprice: $("#editlineitem #lineitemprice").val(), lineitemtaxable: $("#editlineitem #lineitemtaxable").val(), lineitemdiscount: $("#editlineitem #lineitemdiscount").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Line Item...");
	}});
	return rt;
}
function updateLanguage(event)
{
	startLoading("Updating Language...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "languages", languageid: $("#editlanguage #languageid").val(), paid: $("#editlanguage #paid").val(), invoice: $("#editlanguage #invoice").val(), to: $("#editlanguage #to").val(), technician: $("#editlanguage #technician").val(), job: $("#editlanguage #job").val(), date: $("#editlanguage #date").val(), payment: $("#editlanguage #payment").val(), qty: $("#editlanguage #qty").val(), description: $("#editlanguage #description").val(), unit_price: $("#editlanguage #unit_price").val(), discount: $("#editlanguage #discount").val(), line_total: $("#editlanguage #line_total").val(), total_discount: $("#editlanguage #total_discount").val(), subtotal: $("#editlanguage #subtotal").val(), sales_tax: $("#editlanguage #sales_tax").val(), total: $("#editlanguage #total").val(), invoice_number: $("#editlanguage #invoice_number").val(), customer_id: $("#editlanguage #customer_id").val(), make_all_checks_payable_to: $("#editlanguage #make_all_checks_payable_to").val(), send_money_with_paypal: $("#editlanguage #send_money_with_paypal").val(), thank_you_msg: $("#editlanguage #thank_you_msg").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Language...");
	}});
	return rt;
}
function updateSettings(event)
{
	startLoading("Updating Settings...");
	rt = true;
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "update", table: "settings", checks_payable_to: $("#editsettings #checks_payable_to").val(), paypal_url: $("#editsettings #paypal_url").val(), company_name: $("#editsettings #company_name").val(), company_slogan: $("#editsettings #company_slogan").val(), company_address: $("#editsettings #company_address").val(), company_phone: $("#editsettings #company_phone").val(), company_email_address: $("#editsettings #company_email_address").val(), from_email_address: $("#editsettings #from_email_address").val(), from_name: $("#editsettings #from_name").val(), reply_to_email_address: $("#editsettings #reply_to_email_address").val(), languageid: $("#editsettings #languageid").val()}, success: function(data){
		if(data != "1")
		{
			alert(data);
			rt = false;
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Updating Settings...");
	}});
	return rt;
}
function removeInvoice(invoiceid, event)
{
	if(confirm("Are you sure you want to delete this invoice?"))
	{
		startLoading("Removing Invoice...");
		rt = true;
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "invoices", id: invoiceid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Invoice...");
		}});
		removeInvoiceLineItems(invoiceid);
	}
}
function removeCustomer(customerid, event)
{
	if(confirm("Are you sure you want to delete this customer?"))
	{
		startLoading("Removing Customer...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "customers", id: customerid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Customer...");
		}});
		removeCustomerContacts(customerid);
	}
}
function removeContact(contactid, event)
{
	if(confirm("Are you sure you want to delete this contact?"))
	{
		startLoading("Removing Contact...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "contacts", id: contactid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Contact...");
		}});
	}
}
function removeItem(itemid, event)
{
	if(confirm("Are you sure you want to delete this item?"))
	{
		startLoading("Removing Item...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "items", id: itemid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Item...");
		}});
	}
}
function removePayment(paymentid, event)
{
	if(confirm("Are you sure you want to delete this payment?"))
	{
		startLoading("Removing Payment...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "payments", id: paymentid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Payment...");
		}});
	}
}
function removeTechnician(technicianid, event)
{
	if(confirm("Are you sure you want to delete this technician?"))
	{
		startLoading("Removing Technician...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "technicians", id: technicianid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Technician...");
		}});
	}
}
function removeLanguage(languageid, event)
{
	if(confirm("Are you sure you want to delete this language?"))
	{
		startLoading("Removing Language...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "languages", id: languageid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Language...");
		}});
	}
}
function removeLineItem(lineitemid, event)
{
	if(confirm("Are you sure you want to delete this lineitem?"))
	{
		startLoading("Removing Line Item...");
		$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "lineitems", id: lineitemid}, success: function(data){
			if(data != "1")
			{
				alert(data);
			}
			else if(event != undefined && $.isFunction(event['close']))
			{
				event['close']();
			}
			endLoading("Removing Line Item...");
		}});
	}
}
function removeInvoiceLineItems(invoiceid, event)
{
	startLoading("Removing Invoice Line Items...");
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "invoicelineitems", id: invoiceid}, success: function(data){
		if(data != "1")
		{
			alert(data);
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Removing Invoice Line Items...");
	}});
}
function removeCustomerContacts(customerid, event)
{
	startLoading("Removing Customer Contacts...");
	$.ajax({async: false, type: "POST", url: "ajax.php", data: {task: "remove", table: "customercontacts", id: customerid}, success: function(data){
		if(data != "1")
		{
			alert(data);
		}
		else if(event != undefined && $.isFunction(event['close']))
		{
			event['close']();
		}
		endLoading("Removing Customer Contacts...");
	}});
}
function startLoading(s)
{
	loadingQueue++;
	if(loadingQueue == 1)
	{
		$("<div id='loading' style='text-align: center;'><div id='loadingmessages'><p data='"+s+"'>"+s+"</p></div><img src='/images/loading.gif' alt=' ' /></div>")
		.appendTo("body")
		.dialog({
			close: function(event, ui){ $(this).remove(); },
			closeOnEscape: false,
			draggable: false,
			height: 'auto',
			modal: true,
			resizable: false,
			title: "Loading...",
			width: dwidth
		});
		$("div[aria-labelledby='ui-dialog-title-loading'] .ui-dialog-titlebar-close").hide();
	}
	else
	{
		$("#loadingmessages").append("<p data='"+s+"'>"+s+"</p>");
	}
}
function endLoading(s)
{
	if(loadingQueue > 0)
	{
		loadingQueue--;
		if(loadingQueue == 0)
		{
			$("#loading").dialog("close");
		}
		else
		{
			$("#loadingmessages p[data='"+s+"']").remove();
		}
	}
}
function setCustomerTax(customerid, selector)
{
	startLoading("Getting Customer Tax...");
	$.post("ajax.php", {task: "get", table: "customer", id: customerid}, function(data){
		$(selector).val(data.customertax);
		endLoading("Getting Customer Details...");
	}, "json");
}
function getStateOptions(selectedstate)
{
	return 	"<option value=''"+((selectedstate == undefined)? " selected='selected'" : "")+">Select a State</option>"+
					"<option value='AL'"+((selectedstate == 'AL')? " selected='selected'" : "")+">Alabama</option>"+
					"<option value='AK'"+((selectedstate == 'AK')? " selected='selected'" : "")+">Alaska</option>"+
					"<option value='AZ'"+((selectedstate == 'AZ')? " selected='selected'" : "")+">Arizona</option>"+
					"<option value='AR'"+((selectedstate == 'AR')? " selected='selected'" : "")+">Arkansas</option>"+
					"<option value='CA'"+((selectedstate == 'CA')? " selected='selected'" : "")+">California</option>"+
					"<option value='CO'"+((selectedstate == 'CO')? " selected='selected'" : "")+">Colorado</option>"+
					"<option value='CT'"+((selectedstate == 'CT')? " selected='selected'" : "")+">Connecticut</option>"+
					"<option value='DE'"+((selectedstate == 'DE')? " selected='selected'" : "")+">Delaware</option>"+
					"<option value='DC'"+((selectedstate == 'DC')? " selected='selected'" : "")+">District Of Columbia</option>"+
					"<option value='FL'"+((selectedstate == 'FL')? " selected='selected'" : "")+">Florida</option>"+
					"<option value='GA'"+((selectedstate == 'GA')? " selected='selected'" : "")+">Georgia</option>"+
					"<option value='HI'"+((selectedstate == 'HI')? " selected='selected'" : "")+">Hawaii</option>"+
					"<option value='ID'"+((selectedstate == 'ID')? " selected='selected'" : "")+">Idaho</option>"+
					"<option value='IL'"+((selectedstate == 'IL')? " selected='selected'" : "")+">Illinois</option>"+
					"<option value='IN'"+((selectedstate == 'IN')? " selected='selected'" : "")+">Indiana</option>"+
					"<option value='IA'"+((selectedstate == 'IA')? " selected='selected'" : "")+">Iowa</option>"+
					"<option value='KS'"+((selectedstate == 'KS')? " selected='selected'" : "")+">Kansas</option>"+
					"<option value='KY'"+((selectedstate == 'KY')? " selected='selected'" : "")+">Kentucky</option>"+
					"<option value='LA'"+((selectedstate == 'LA')? " selected='selected'" : "")+">Louisiana</option>"+
					"<option value='ME'"+((selectedstate == 'ME')? " selected='selected'" : "")+">Maine</option>"+
					"<option value='MD'"+((selectedstate == 'MD')? " selected='selected'" : "")+">Maryland</option>"+
					"<option value='MA'"+((selectedstate == 'MA')? " selected='selected'" : "")+">Massachusetts</option>"+
					"<option value='MI'"+((selectedstate == 'MI')? " selected='selected'" : "")+">Michigan</option>"+
					"<option value='MN'"+((selectedstate == 'MN')? " selected='selected'" : "")+">Minnesota</option>"+
					"<option value='MS'"+((selectedstate == 'MS')? " selected='selected'" : "")+">Mississippi</option>"+
					"<option value='MO'"+((selectedstate == 'MO')? " selected='selected'" : "")+">Missouri</option>"+
					"<option value='MT'"+((selectedstate == 'MT')? " selected='selected'" : "")+">Montana</option>"+
					"<option value='NE'"+((selectedstate == 'NE')? " selected='selected'" : "")+">Nebraska</option>"+
					"<option value='NV'"+((selectedstate == 'NV')? " selected='selected'" : "")+">Nevada</option>"+
					"<option value='NH'"+((selectedstate == 'NH')? " selected='selected'" : "")+">New Hampshire</option>"+
					"<option value='NJ'"+((selectedstate == 'NJ')? " selected='selected'" : "")+">New Jersey</option>"+
					"<option value='NM'"+((selectedstate == 'NM')? " selected='selected'" : "")+">New Mexico</option>"+
					"<option value='NY'"+((selectedstate == 'NY')? " selected='selected'" : "")+">New York</option>"+
					"<option value='NC'"+((selectedstate == 'NC')? " selected='selected'" : "")+">North Carolina</option>"+
					"<option value='ND'"+((selectedstate == 'ND')? " selected='selected'" : "")+">North Dakota</option>"+
					"<option value='OH'"+((selectedstate == 'OH')? " selected='selected'" : "")+">Ohio</option>"+
					"<option value='OK'"+((selectedstate == 'OK')? " selected='selected'" : "")+">Oklahoma</option>"+
					"<option value='OR'"+((selectedstate == 'OR')? " selected='selected'" : "")+">Oregon</option>"+
					"<option value='PA'"+((selectedstate == 'PA')? " selected='selected'" : "")+">Pennsylvania</option>"+
					"<option value='RI'"+((selectedstate == 'RI')? " selected='selected'" : "")+">Rhode Island</option>"+
					"<option value='SC'"+((selectedstate == 'SC')? " selected='selected'" : "")+">South Carolina</option>"+
					"<option value='SD'"+((selectedstate == 'SD')? " selected='selected'" : "")+">South Dakota</option>"+
					"<option value='TN'"+((selectedstate == 'TN')? " selected='selected'" : "")+">Tennessee</option>"+
					"<option value='TX'"+((selectedstate == 'TX')? " selected='selected'" : "")+">Texas</option>"+
					"<option value='UT'"+((selectedstate == 'UT')? " selected='selected'" : "")+">Utah</option>"+
					"<option value='VT'"+((selectedstate == 'VT')? " selected='selected'" : "")+">Vermont</option>"+
					"<option value='VA'"+((selectedstate == 'VA')? " selected='selected'" : "")+">Virginia</option>"+
					"<option value='WA'"+((selectedstate == 'WA')? " selected='selected'" : "")+">Washington</option>"+
					"<option value='WV'"+((selectedstate == 'WV')? " selected='selected'" : "")+">West Virginia</option>"+
					"<option value='WI'"+((selectedstate == 'WI')? " selected='selected'" : "")+">Wisconsin</option>"+
					"<option value='WY'"+((selectedstate == 'WY')? " selected='selected'" : "")+">Wyoming</option>";
}
END;
	if(($mobile && !isset($_GET['m'])) || (!$mobile && isset($_GET['m'])))
	{
		echo <<<END
<!DOCTYPE html>
<html>
<head>
<title>Invoices</title>
<link rel="shortcut icon" href="/favicon.ico" />
<meta name="viewport" content="width=600"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="/includes/jquery-ui-1.8.15.custom.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/includes/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/includes/jquery-ui-1.8.15.custom.min.js"></script>
<script type="text/javascript">
{$script}

function message(msg)
{
	$("<div style='position: fixed; top: 100px; right: 20px; color: #fff; font-weight: bold; font-size: 20px;'>"+msg+"</div>")
	.appendTo("body")
	.animate({
		top: [50, 'linear']
	}, 1000, function(){
		$(this).animate({
			top: [10, 'linear'],
			opacity: 0,
		}, 800, function(){
			$(this).remove();
		});
	});
}
</script>
<style type="text/css">
body
{
	background-color: #96B6DD;
	max-width: 600px;
	margin: 5px auto;
}
select
{
	font-size: 30px !important;
	width: 100%;
}
input
{
	font-size: 30px !important;
	width: 100%;
}
textarea
{
	height: 300px;
	width: 100%;

}
#page
{
	white-space: pre;
	text-align: center;
}
*
{
	margin: 10px
}
button
{
	min-height: 100px;
	min-width: 100px;
	font-size: 30px !important;
}
.taxlink {
	vertical-align: super;
	font-size: 10px;
	text-decoration: none;
	color: #00f;
}
</style>
</head>
<body>
<div id="page">
Invoices
<input type="text" name="searchinvoices" id="searchinvoices" placeholder="Search Invoices" />
<select id="selectinvoice"><option value="">Loading Invoices...</option></select>
<button id="newinvoice">New</button><button id="editinvoice">Edit</button><button id="deleteinvoice">X</button>
<button id="viewpdfinvoice">View</button><button id="downloadpdfinvoice">Download</button>
<button id="htmlpdfinvoice">Html</button><button id="emailpdfinvoice">Email</button>

Customers
<input type="text" name="searchcustomers" id="searchcustomers" placeholder="Search Customers" />
<select id="selectcustomer"><option value="">Loading Customers...</option></select>
<button id="newcustomer">New</button><button id="editcustomer">Edit</button><button id="deletecustomer">X</button>

contacts
<input type="text" name="searchcontacts" id="searchcontacts" placeholder="Search Contacts" />
<select id="selectcontact"><option value="">Loading Contacts...</option></select>
<button id="newcontact">New</button><button id="editcontact">Edit</button><button id="deletecontact">X</button>

Items
<input type="text" name="searchitems" id="searchitems" placeholder="Search Items" />
<select id="selectitem"><option value="">Loading Items...</option></select>
<button id="newitem">New</button><button id="edititem">Edit</button><button id="deleteitem">X</button>

Payments
<input type="text" name="searchpayments" id="searchpayments" placeholder="Search Payments" />
<select id="selectpayment"><option value="">Loading Payments...</option></select>
<button id="newpayment">New</button><button id="editpayment">Edit</button><button id="deletepayment">X</button>

Technicians
<input type="text" name="searchtechnicians" id="searchtechnicians" placeholder="Search Technicians" />
<select id="selecttechnician"><option value="">Loading Technicians...</option></select>
<button id="newtechnician">New</button><button id="edittechnician">Edit</button><button id="deletetechnician">X</button>

Languages
<input type="text" name="searchlanguages" id="searchlanguages" placeholder="Search Languages" />
<select id="selectlanguage"><option value="">Loading Languages...</option></select>
<button id="newlanguage">New</button><button id="editlanguage">Edit</button><button id="deletelanguage">X</button>

<button id="editsettings">Edit Settings</button><button id="logout" onclick="location.href='index.php?logout'">Logout</button>
</div>
<div id='loading' style='text-align: center;'><div id="loadingmessages"><p data="Loading DOM...">Loading DOM...</p></div><img src='/images/loading.gif' alt=' ' style="width:300px;"/></div>
<script type="text/javascript">
	$("#loading").dialog({
		close: function(event, ui){ $(this).remove(); },
		closeOnEscape: false,
		draggable: false,
		height: 'auto',
		modal: true,
		resizable: false,
		title: "Loading...",
		width: dwidth
	});
	$("div[aria-labelledby='ui-dialog-title-loading'] .ui-dialog-titlebar-close").hide();
</script>
</body>
</html>
END;
		exit;
	}
	else
	{
		require_once('./includes/connect.inc');
		require_once('./includes/header.inc');
		echo <<<END
<table id="page"><tbody>
<tr><td><button id="newinvoice">New Invoice</button></td><td><input type="text" name="searchinvoices" id="searchinvoices" placeholder="Search Invoices" /></td><td><select id="selectinvoice"><option value="">Loading Invoices...</option></select></td><td><button id="editinvoice">edit / view</button></td><td><button id="deleteinvoice">X</button></td></tr>
<tr><td><button id="newcustomer">New Customer</button></td><td><input type="text" name="searchcustomers" id="searchcustomers" placeholder="Search Customers" /></td><td><select id="selectcustomer"><option value="">Loading Customers...</option></select></td><td><button id="editcustomer">edit / view</button></td><td><button id="deletecustomer">X</button></td></tr>
<tr><td><button id="newcontact">New Contact</button></td><td><input type="text" name="searchcontacts" id="searchcontacts" placeholder="Search Contacts" /></td><td><select id="selectcontact"><option value="">Loading Contacts...</option></select></td><td><button id="editcontact">edit / view</button></td><td><button id="deletecontact">X</button></td></tr>
<tr><td><button id="newitem">New Item</button></td><td><input type="text" name="searchitems" id="searchitems" placeholder="Search Items" /></td><td><select id="selectitem"><option value="">Loading Items...</option></select></td><td><button id="edititem">edit / view</button></td><td><button id="deleteitem">X</button></td></tr>
<tr><td><button id="newpayment">New Payment</button></td><td><input type="text" name="searchpayments" id="searchpayments" placeholder="Search Payments" /></td><td><select id="selectpayment"><option value="">Loading Payments...</option></select></td><td><button id="editpayment">edit / view</button></td><td><button id="deletepayment">X</button></td></tr>
<tr><td><button id="newtechnician">New Techinician</button></td><td><input type="text" name="searchtechnicians" id="searchtechnicians" placeholder="Search Technicians" /></td><td><select id="selecttechnician"><option value="">Loading Technicians...</option></select></td><td><button id="edittechnician">edit / view</button></td><td><button id="deletetechnician">X</button></td></tr>
<tr><td><button id="newlanguage">New Language</button></td><td><input type="text" name="searchlanguages" id="searchlanguages" placeholder="Search Languages" /></td><td><select id="selectlanguage"><option value="">Loading Languages...</option></select></td><td><button id="editlanguage">edit / view</button></td><td><button id="deletelanguage">X</button></td></tr>
<tr><td colspan="5"><button id="viewpdfinvoice">View Invoice</button> <button id="downloadpdfinvoice">Download Invoice</button> <button id="htmlpdfinvoice">View Html Invoice</button> <button id="emailpdfinvoice">Email Invoice</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="editsettings">Edit Settings</button><button id="logout" onclick="location.href='index.php?logout'">Logout</button></td></tr>
</tbody></table>
<div id='loading' style='text-align: center;'><div id="loadingmessages"><p data="Loading DOM...">Loading DOM...</p></div><img src='/images/loading.gif' alt=' ' /></div>
<script type="text/javascript">
	$("#loading").dialog({
		close: function(event, ui){ $(this).remove(); },
		closeOnEscape: false,
		draggable: false,
		height: 'auto',
		modal: true,
		resizable: false,
		title: "Loading...",
		width: dwidth
	});
	$("div[aria-labelledby='ui-dialog-title-loading'] .ui-dialog-titlebar-close").hide();
</script>
END;
		require_once('./includes/footer.inc');
		exit;
	}
}
else if(isset($_REQUEST['username']) && isset($_REQUEST['password'])){
	if($_REQUEST['username'] == $username && $_REQUEST['password'] == $password){
		$_SESSION['loggedin'] = "yes";
		$_SESSION['userid'] = 0;
		header("location:index.php");
	}
	else
	{
		$query = "select * from technicians where technicianusername = ? and technicianpassword = ?";
		$result = $db->prepare($query);
		if($result->execute(array($_REQUEST['username'], sha1($_REQUEST['password']))))
		{
			if($result->rowCount() == 1)
			{
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$_SESSION['loggedin'] = "yes";
				$_SESSION['userid'] = $row['technicianid'];
				$_SESSION['username'] = $_REQUEST['username'];
				header("location:index.php");
				exit;
			}
		}
	}
}
	$title = 'Login';
	require_once('./includes/connect.inc');
	require_once('./includes/header.inc');
	echo <<<END
	<div id="text">
	<form action="index.php" method="post">
	<p>Username: <input type="text" name="username" id="username"/></p>
	<p>Password: <input type="password" name="password" id="password"/></p>
	<p><input type="submit" name="button" id="button" value="Submit" /></p>
	</form>
	<script type='text/javascript'>
	document.getElementById('username').focus()
	</script>
	</div>
END;
	require_once('./includes/footer.inc');
?>
