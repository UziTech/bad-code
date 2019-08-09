<?php

function full_url($s, $use_forwarded_host = false) {
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME']);
	return $protocol . '://' . $host . $port . $s['REQUEST_URI'];
}
if (!headers_sent()) {
	header("Status: 404 Not Found");
}
require_once("./includes/connect.inc");
$title = "Error 404: Page Not Found";
$time = date("F jS, Y g:i:s a T");
$ip = check_input(isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR']);
$useragent = check_input($_SERVER["HTTP_USER_AGENT"]);
$referer = check_input($_SERVER["HTTP_REFERER"]);
$url = full_url($_SERVER);
$query = "INSERT 404error (time, ip, url, referer, useragent) VALUES (?, ?, ?, ?, ?)";
$params = [$time, $ip, $url, $referer, $useragent];
$result = $db->prepare($query);
$result->execute($params);
require_once("./includes/header.inc");
echo <<<END
<div id="text">
<h1 style="width:100%;text-align:center;">Error 404: Page Not Found</h1>
<h2 style="width:100%;text-align:center;text-transform:none">$url does not exist</h2>
</div>
END;
require_once("./includes/footer.inc");
exit;
