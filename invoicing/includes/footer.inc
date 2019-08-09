<?php
echo <<<END

</div>
<!-- end main -->
<!-- footer -->
<div id="footer">

<div id="menu_footer">

<a href="/index.php">home</a>
</div>


<div id="left_footer">Copyright &copy; 2006
END;
if(date("Y") != 2006){
	echo "-".date("Y");
}
//TODO validated in right footer
echo <<<END
 UziTech
<div id="right_footer">

</div>

</div>
</div>
<!-- end footer -->
</div>
</body>
</html>
END;
?>
