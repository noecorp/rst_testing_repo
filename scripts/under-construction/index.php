<?php 
global $time;
if(!isset($time)) {
    $time = '2013-7-01 16:00:00 GMT+05:30';
}
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
    <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> 
    <meta http-equiv="refresh" content="60">        
    <TITLE>Under Construction</TITLE> 
    <LINK href="/css/under-construction/styles.css" rel="stylesheet" type="text/css"> 
<SCRIPT src="http://code.jquery.com/jquery-latest.pack.js" type="text/javascript"></SCRIPT>
 
<SCRIPT src="/js/under-construction/countdown.js" defer="defer" type="text/javascript"></SCRIPT>
  
</HEAD> 
<BODY>
<DIV id="page">
<DIV class="logo">
    <A href="#"><IMG alt="Logo" src="/css/under-construction/logo-shmart.png"></A>	 
</DIV>
<DIV class="contentbox">
<H1>Site is Down for Maintenance. </H1>
<H2>Will be back Live Soon </H2>
<DIV id="countdown">
   <SPAN id="countdown1"><?php echo $time ?></SPAN>
</DIV>
</DIV><!-- end:Contentbox --> 
</DIV><!-- end:Page --> 
</BODY>
</HTML>
