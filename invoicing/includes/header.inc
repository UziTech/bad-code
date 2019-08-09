<?php
echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
END;
echo "<title>$title</title>";
if(!isset($description))
{
  $description = "Any computer. Any time. Computer repair and modification. We write programs to better the world. We charge money to support our families.";
}
echo <<<END
<link rel="shortcut icon" href="/favicon.ico" />
<meta name="keywords" content="computer,tech,programs,programming" >
<meta name="description" content="{$description}" >
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link href="/includes/style.css" rel="stylesheet" type="text/css"/>
<link href="/includes/jquery-ui-1.8.15.custom.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/includes/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/includes/jquery-ui-1.8.15.custom.min.js") ?>"></script>
<script type="text/javascript" src="/includes/jquery-outline-1.5.js"></script>
<script type="text/javascript">
var msgQ = 0;
var msgShowing = 0;
function message(msg)
{
	if(msgShowing < msgQ)
	{
		msgQ = 0;
	}
	else
	{
		msgQ++;
	}
	msgShowing++;
	$("<div id='msg" + msgQ + "' style='position: fixed; top: " + (30 + (msgQ * 20)) + "px; right: 20px; color: #fff; font-weight: bold; font-size: 20px;'>"+msg+"</div>").appendTo("body");
	setTimeout("$('#msg" + msgQ + "').animate({top:'-=20',opacity:0},1000,function(){ $(this).remove();msgShowing--;});", 1000);
}
</script>
END;
if(isset($script))
{
echo "<script type='text/javascript'>\n{$script}\n</script>";
}
if(isset($style))
{
echo "<style type='text/css'>\n{$style}\n</style>";
}
echo <<<END
</head>
<body>
<div id="container">
<!-- header -->
<div id="header">
<img src="/images/computer.png" style="float:left;height:128px" alt="UziTech"/>

<div>
<img src="/images/qrcode.png" alt="http://uzitech.com" style="float: right; margin-right: 15px;"/>
<a href="/">U<span style="font-weight:bold;font-size:16px;">zi</span>T<span style="font-weight:bold;font-size:16px;">ech</span></a> <div class="g-plusone" data-annotation="none"></div>
<p style="color:#FFFFFF; font-weight:bold;">Any Computer. Any Time.</p>
</div>

</div>

<div id="menu">
<ul>
<li><a href="/index.php">home</a></li>
</ul>
</div>
<!--end header -->
<!-- main -->
<div id="main">
END;
?>
