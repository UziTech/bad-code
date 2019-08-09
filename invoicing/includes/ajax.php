<?php
session_start();
require_once("connect.inc");
if (isset($_REQUEST['task']) && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 'yes') {
	switch ($_REQUEST['task']) {
		case "add":
			add();
			break;
		case "update":
			update();
			break;
		case "remove":
			remove();
			break;
		case "get":
			get();
			break;
		case "sql":
			sql();
			break;
		default:
			echo error("{$_REQUEST['task']} is an invalid task");
	}
} else {
	error("Not Logged In");
}
function add() {

	if (isset($_REQUEST['table'])) {
		global $db;
		$query = "insert into {$_REQUEST['table']} ";
		switch ($_REQUEST['table']) {
			case "invoices":
				$query .= "(contactid, customerid, invoicejob, invoicecheckno, paymentid, technicianid, languageid, invoicetaxpercentage, invoicedate, invoicepaid, invoicenotes) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$params = [$_REQUEST['contactid'], $_REQUEST['customerid'], $_REQUEST['invoicejob'], $_REQUEST['invoicecheckno'], $_REQUEST['paymentid'], $_REQUEST['technicianid'], $_REQUEST['languageid'], $_REQUEST['invoicetaxpercentage'], $_REQUEST['invoicedate'], $_REQUEST['invoicepaid'], $_REQUEST['invoicenotes']];
				break;
			case "customers":
				$query .= "(customername, customeraddress, customercity, customerstate, customerzip, customertax, customerphone, customerdate) values (?, ?, ?, ?, ?, ?, ?, ?)";
				$params = [$_REQUEST['customername'], $_REQUEST['customeraddress'], $_REQUEST['customercity'], $_REQUEST['customerstate'], $_REQUEST['customerzip'], $_REQUEST['customertax'], $_REQUEST['customerphone'], $_REQUEST['customerdate']];
				break;
			case "contacts":
				$query .= "(customerid, contactname, contactphone, contactemail) values (?, ?, ?, ?)";
				$params = [$_REQUEST['customerid'], $_REQUEST['contactname'], $_REQUEST['contactphone'], $_REQUEST['contactemail']];
				break;
			case "items":
				$query .= "(itemdescription, itemprice, itemtaxable) values (?, ?, ?)";
				$params = [$_REQUEST['itemdescription'], $_REQUEST['itemprice'], $_REQUEST['itemtaxable']];
				break;
			case "payments":
				$query .= "(paymentdescription) values (?)";
				$params = [$_REQUEST['paymentdescription']];
				break;
			case "lineitems":
				$query .= "(invoiceid, lineitemquantity, itemid, lineitemdescription, lineitemprice, lineitemtaxable, lineitemdiscount) values (?, ?, ?, ?, ?, ?, ?)";
				$params = [$_REQUEST['invoiceid'], $_REQUEST['lineitemquantity'], $_REQUEST['itemid'], $_REQUEST['lineitemdescription'], $_REQUEST['lineitemprice'], $_REQUEST['lineitemtaxable'], $_REQUEST['lineitemdiscount']];
				break;
			case "technicians":
				$query .= "(technicianid, technicianfirstname, technicianlastname, technicianusername, technicianpassword, technicianemailaddress) values (?, ?, ?, ?, ?, ?)";
				$params = [$_REQUEST['technicianid'], $_REQUEST['technicianfirstname'], $_REQUEST['technicianlastname'], $_REQUEST['technicianusername'], $_REQUEST['technicianpassword'], $_REQUEST['technicianemailaddress']];
				break;
			case "languages":
				$result = $db->prepare("select * from languages where languageid = ?");
				$result->execute([$_REQUEST['languageid']]);
				if ($result->rowCount() > 0) {
					error("That language id already exists.");
				}
				$query .= "(languageid, paid, invoice, languages.to, technician, job, date, payment, qty, description, unit_price, discount, line_total, total_discount, subtotal, sales_tax, total, invoice_number, customer_id, make_all_checks_payable_to, send_money_with_paypal, thank_you_msg) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$params = [$_REQUEST['languageid'], $_REQUEST['paid'], $_REQUEST['invoice'], $_REQUEST['to'], $_REQUEST['technician'], $_REQUEST['job'], $_REQUEST['date'], $_REQUEST['payment'], $_REQUEST['qty'], $_REQUEST['description'], $_REQUEST['unit_price'], $_REQUEST['discount'], $_REQUEST['line_total'], $_REQUEST['total_discount'], $_REQUEST['subtotal'], $_REQUEST['sales_tax'], $_REQUEST['total'], $_REQUEST['invoice_number'], $_REQUEST['customer_id'], $_REQUEST['make_all_checks_payable_to'], $_REQUEST['send_money_with_paypal'], $_REQUEST['thank_you_msg']];
				break;
			default:
				error($_REQUEST['table']." is not a valid table");
		}
		$result = $db->prepare($query);
		if ($result->execute($params)) {
			echo 1;
		} else {
			$error = $result->errorInfo();
			error("$query\n\n".$error[2]);
		}
	} else {
		error("table not set");
	}
}
function update() {

	if (isset($_REQUEST['table'])) {
		global $db;
		$query = "update ".$_REQUEST['table']." set ";
		switch ($_REQUEST['table']) {
			case "invoices":
				$query .= "contactid = ?, customerid = ?, invoicejob = ?, invoicecheckno = ?, paymentid = ?, technicianid = ?, languageid = ?, invoicetaxpercentage = ?, invoicedate = ?, invoicepaid = ?, invoicenotes = ? where invoiceid = ?";
				$params = [$_REQUEST['contactid'], $_REQUEST['customerid'], $_REQUEST['invoicejob'], $_REQUEST['invoicecheckno'], $_REQUEST['paymentid'], $_REQUEST['technicianid'], $_REQUEST['languageid'], $_REQUEST['invoicetaxpercentage'], $_REQUEST['invoicedate'], $_REQUEST['invoicepaid'], $_REQUEST['invoicenotes'], $_REQUEST['invoiceid']];
				break;
			case "customers":
				$query .= "customername = ?, customeraddress = ?, customercity = ?, customerstate = ?, customerzip = ?, customertax = ?, customerphone = ?, customerdate = ? where customerid = ?";
				$params = [$_REQUEST['customername'], $_REQUEST['customeraddress'], $_REQUEST['customercity'], $_REQUEST['customerstate'], $_REQUEST['customerzip'], $_REQUEST['customertax'], $_REQUEST['customerphone'], $_REQUEST['customerdate'], $_REQUEST['customerid']];
				break;
			case "contacts":
				$query .= "customerid = ?, contactname = ?, contactphone = ?, contactemail = ? where contactid = ?";
				$params = [$_REQUEST['customerid'], $_REQUEST['contactname'], $_REQUEST['contactphone'], $_REQUEST['contactemail'], $_REQUEST['contactid']];
				break;
			case "items":
				$query .= "itemdescription = ?, itemprice = ?, itemtaxable = ? where itemid = ?";
				$params = [$_REQUEST['itemdescription'], $_REQUEST['itemprice'], $_REQUEST['itemtaxable'], $_REQUEST['itemid']];
				break;
			case "payments":
				$query .= "paymentdescription = ? where paymentid = ?";
				$params = [$_REQUEST['paymentdescription'], $_REQUEST['paymentid']];
				break;
			case "lineitems":
				$query .= "invoiceid = ?, lineitemquantity = ?, itemid = ?, lineitemdescription = ?, lineitemprice = ?, lineitemtaxable = ?, lineitemdiscount = ? where lineitemid = ?";
				$params = [$_REQUEST['invoiceid'], $_REQUEST['lineitemquantity'], $_REQUEST['itemid'], $_REQUEST['lineitemdescription'], $_REQUEST['lineitemprice'], $_REQUEST['lineitemtaxable'], $_REQUEST['lineitemdiscount'], $_REQUEST['lineitemid']];
				break;
			case "technicians":
				$query .= "technicianfirstname = ?, technicianlastname = ?, technicianusername = ?, technicianemailaddress = ?";
				$params = [$_REQUEST['technicianfirstname'], $_REQUEST['technicianlastname'], $_REQUEST['technicianusername'], $_REQUEST['technicianemailaddress']];
				if ($_REQUEST['technicianpassword'] != "null") {
					$query .= ", technicianpassword = ?";
					$params[] = $_REQUEST['technicianpassword'];
				}
				$query .= " where technicianid = ?";
				$params[] = $_REQUEST['technicianid'];
				break;
			case "settings":
				$query .= "checks_payable_to = ?, paypal_url = ?, company_name = ?, company_slogan = ?, company_address = ?, company_phone = ?, company_email_address = ?, from_email_address = ?, from_name = ?, reply_to_email_address = ?, languageid = ?";
				$params = [$_REQUEST['checks_payable_to'], $_REQUEST['paypal_url'], $_REQUEST['company_name'], $_REQUEST['company_slogan'], $_REQUEST['company_address'], $_REQUEST['company_phone'], $_REQUEST['company_email_address'], $_REQUEST['from_email_address'], $_REQUEST['from_name'], $_REQUEST['reply_to_email_address'], $_REQUEST['languageid']];
				break;
			case "languages":
				$query .= "paid = ?, invoice = ?, `to` = ?, technician = ?, job = ?, date = ?, payment = ?, qty = ?, description = ?, unit_price = ?, discount = ?, line_total = ?, total_discount = ?, subtotal = ?, sales_tax = ?, total = ?, invoice_number = ?, customer_id = ?, make_all_checks_payable_to = ?, send_money_with_paypal = ?, thank_you_msg = ?";
				$params = [$_REQUEST['paid'], $_REQUEST['invoice'], $_REQUEST['to'], $_REQUEST['technician'], $_REQUEST['job'], $_REQUEST['date'], $_REQUEST['payment'], $_REQUEST['qty'], $_REQUEST['description'], $_REQUEST['unit_price'], $_REQUEST['discount'], $_REQUEST['line_total'], $_REQUEST['total_discount'], $_REQUEST['subtotal'], $_REQUEST['sales_tax'], $_REQUEST['total'], $_REQUEST['invoice_number'], $_REQUEST['customer_id'], $_REQUEST['make_all_checks_payable_to'], $_REQUEST['send_money_with_paypal'], $_REQUEST['thank_you_msg']];
				break;
			default:
				error($_REQUEST['table']." is not a valid table");
		}
		$result = $db->prepare($query);
		if ($result->execute($params)) {
			echo 1;
		} else {
			$error = $result->errorInfo();
			error("$query\n\n".$error[2]);
		}
	} else {
		error("table not set");
	}
}
function remove() {

	if (isset($_REQUEST['table'], $_REQUEST['id'])) {
		global $db;
		switch ($_REQUEST['table']) {
			case "invoices":
				$query = "delete from invoices where invoiceid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "customers":
				$query = "delete from customers where customerid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "contacts":
				$query = "delete from contacts where contactid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "payments":
				$query = "delete from payments where paymentid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "items":
				$query = "delete from items where itemid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "lineitems":
				$query = "delete from lineitems where lineitemid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "technicians":
				$query = "delete from technicians where technicianid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "invoicelineitems":
				$query = "delete from lineitems where invoiceid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "customercontacts":
				$query = "delete from contacts where customerid = ?";
				$params = [$_REQUEST['id']];
				break;
			case "languages":
				$query = "delete from languages where languageid = ?";
				$params = [$_REQUEST['id']];
				break;
			default:
				error($_REQUEST['table']." is not a valid table");
		}
		$result = $db->prepare($query);
		if ($result->execute($params)) {
			echo 1;
		} else {
			$error = $result->errorInfo();
			error("$query\n\n".$error[2]);
		}
	} else {
		error("table or id not set");
	}
}
function get() {

	if (isset($_REQUEST['table'])) {
		global $db;
		if (isset($_REQUEST['id'])) {
			$query = "select * from ".$_REQUEST['table']."s where ".$_REQUEST['table']."id = ?";
			$params = [$_REQUEST['id']];
			$result = $db->prepare($query);
			if ($result->execute($params)) {
				if ($result->rowCount() > 0) {
					$row = $result->fetch(PDO::FETCH_ASSOC);
					foreach ($row as $key => $value) {
						$row[$key] = stripslashes($value);
					}
					echo json_encode($row);
				} else {
					error("$query returned 0 rows");
				}
			} else {
				$error = $result->errorInfo();
				error("$query\n\n".$error[2]);
			}
		} else {
			$query = "select * from ".$_REQUEST['table'];
			switch ($_REQUEST['table']) {
				case "invoices":
					$query .= " join customers on invoices.customerid = customers.customerid";
					$params = [];
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$startquery = true;
						if (strpos($q, "=") !== false) {
							$arr = explode("=", $q, 2);
							$field = $arr[0];
							$fq = $arr[1];
							switch ($field) {
								case "customerid":
									$field = "invoices.customerid";
								case "contactid":
								case "invoicejob":
								case "invoicechackno":
								case "paymentid":
								case "technicianid":
								case "languageid":
								case "invoicetaxpercentage":
								case "invoicedate":
								case "invoicepaid":
								case "invoicenotes":
								case "customername":
								case "customeraddress":
								case "customercity":
								case "customerstate":
								case "customerzip":
								case "customerphone":
								case "customerdate":
									$query .= "({$field} = ?) ";
									$params[] = $fq;
									$startquery = false;
									break;
							}
						}
						if ($startquery && strpos($q, ":") !== false) {
							$arr = explode(":", $q, 2);
							$field = $arr[0];
							$fq = $arr[1];
							switch ($field) {
								case "customerid":
									$field = "invoices.customerid";
								case "contactid":
								case "invoicejob":
								case "invoicechackno":
								case "paymentid":
								case "technicianid":
								case "languageid":
								case "invoicetaxpercentage":
								case "invoicedate":
								case "invoicepaid":
								case "invoicenotes":
								case "customername":
								case "customeraddress":
								case "customercity":
								case "customerstate":
								case "customerzip":
								case "customerphone":
								case "customerdate":
									$query .= "({$field} like ?) ";
									$params[] = "%{$fq}%";
									$startquery = false;
									break;
							}
						}
						if ($startquery) {
							$query .= "(invoiceid like ? or invoicejob like ? or invoicecheckno like ? or invoicedate like ? or customername like ? or customeraddress like ? or customercity like ? or customerstate like ? or customerzip like ? or customerphone like ? or customerdate like ?) ";//or contactname like '%$q%' or contactphone like '%$q%' or contactemail like '%$q%' or paymentdescription like '%$q%' or lineitemdescription like '%$q%' or lineitemprice like '%$q%' or lineitemdiscount like '%$q%') ";
							for ($i = 0; $i < 11; $i++) {
								$params[] = "%{$q}%";
							}
						}
					}
					$query .= " order by invoicedate desc";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['invoiceid'],
								'text' => "#".$row['invoiceid']." ".$row['customername']." ".$row['invoicedate'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "customers":
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$query .= "(customerid like ? or customername like ? or customeraddress like ? or customercity like ? or customerstate like ? or customerzip like ? or customertax like ? or customerphone like ? or customerdate like ?) ";
						for ($i = 0; $i < 9; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$query .= " order by customername";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['customerid'],
								'text' => $row['customername'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "contacts":
					if (isset($_REQUEST['customerid']) && $_REQUEST['customerid'] != -1) {
						$query .= " where customerid = {$_REQUEST['customerid']}";
					}
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							if (isset($_REQUEST['customerid']) && $_REQUEST['customerid'] != -1) {
								$query .= " and ";
							} else {
								$query .= " where ";
							}
						} else {
							$query .= "and ";
						}
						$query .= "(contactname like ? or contactphone like ? or contactemail like ?) ";
						for ($i = 0; $i < 3; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$query .= " order by contactname";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['contactid'],
								'text' => $row['contactname'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "items":
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$query .= "(itemdescription like ? or itemprice like ? or itemtaxable like ?) ";
						for ($i = 0; $i < 3; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$query .= " order by itemdescription";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['itemid'],
								'text' => $row['itemdescription'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "payments":
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$query .= "(paymentdescription like ?) ";
						for ($i = 0; $i < 1; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$query .= " order by paymentdescription";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['paymentid'],
								'text' => $row['paymentdescription'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "lineitems":
					$params = [];
					if (isset($_REQUEST['invoiceid']) && $_REQUEST['invoiceid'] != -1) {
						$query .= " where invoiceid = ?";
						$params = [$_REQUEST['invoiceid']];
					}
					$query .= " order by lineitemid";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['lineitemid'],
								'text' => $row['lineitemquantity']." ".$row['lineitemdescription'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "technicians":
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$query .= "(technicianfirstname like ? or technicianlastname like ? or technicianusername like ? or technicianemailaddress like ?) ";
						for ($i = 0; $i < 4; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$query .= " order by technicianfirstname, technicianlastname";
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							foreach ($row as $key => $value) {
								$row[$key] = stripslashes($value);
							}
							$arr[] = [
								'value' => $row['technicianid'],
								'text' => $row['technicianfirstname'] . " " . $row['technicianlastname'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "languages":
					$qlist = (isset($_REQUEST['q']) && $_REQUEST['q'] !== "")? explode(" ", $_REQUEST['q']) : [];
					foreach ($qlist as $key => $q) {
						if ($key == 0) {
							$query .= " where ";
						} else {
							$query .= "and ";
						}
						$query .= "(languageid like ?) ";
						for ($i = 0; $i < 1; $i++) {
							$params[] = "%{$q}%";
						}
					}
					$result = $db->prepare($query);
					if ($result->execute($params)) {
						$arr = [((isset($_REQUEST['sid']))? $_REQUEST['sid'] : "")];
						while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
							$arr[] = [
								'value' => $row['languageid'],
								'text' => $row['languageid'],
							];
						}
						echo json_encode($arr);
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				case "settings":
					$result = $db->prepare($query);
					if ($result->execute()) {
						if ($result->rowCount() > 0) {
							echo json_encode($result->fetch(PDO::FETCH_ASSOC));
						} else {
							error("$query returned 0 rows");
						}
					} else {
						$error = $result->errorInfo();
						error("$query\n\n".$error[2]);
					}
					break;
				default:
					error($_REQUEST['table']." is not a valid table");
			}
		}
	} else {
		error("table not set");
	}
}

function sql() {

	global $db;
	$result = $db->query(stripslashes($_POST['query']));
	if ($result !== false) {
		echo 1;
	} else {
		error(stripslashes($_POST['query']));
	}
}

function error($msg, $severity = "error") {

	switch ($severity) {
		case "error":
			echo "Error: {$msg}";
			break;
		case "info":
			echo $msg;
			break;
		default:
			echo "Error: {$severity} is an invalid severity; {$msg}";
			break;
	}
	exit;
}
function xjson_encode($arr) {

	$test = false;
	$s = "{";
	foreach ($arr as $key => $value) {
		$test = true;
		if (strpos($value, "{") == 0 && strrpos($value, "}") == strlen($value) - 1) {
			$s.="\"".$key."\":".$value.", ";
		} else {
			$s.="\"".$key."\":\"".$value."\", ";
		}
	}
	if ($test) {
		$s = substr($s, 0, strlen($s) - 2);
	}
	$s .= "}";
	return $s;
}
