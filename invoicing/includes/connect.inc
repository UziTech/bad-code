<?php
$dbhost = 'host';
$dbport = '3306';
$dbusername = 'root';
$dbpassword = 'password';
$dbdatabase = 'invoices';

$username = 'username';
$password = 'password';

//mysql_connect($dbhost.':'.$dbport, $dbusername, $dbpassword) or die ("<h1>Server unreachable</h1>");

//mysql_select_db($dbdatabase) or die ("<h1>cannot connect to database</h1>");

try{
	$db = new PDO("mysql:host={$dbhost};port={$dbport};dbname={$dbdatabase}", $dbusername, $dbpassword, array(PDO::ATTR_PERSISTENT => true));
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}

function check_input($value)
{
	global $db;
	if (get_magic_quotes_gpc())
	{
		$value = stripslashes($value);
	}
	$value = $db->quote($value);
	if(preg_match("/^\'(.*)\'$/s", $value))
	{
		return substr($value, 1, strlen($value) - 2);
	}
	else
	{
		return $value;
	}
}
foreach($_REQUEST as $key => $value)
{
	$_REQUEST[$key] = check_input($value);
}
?>
