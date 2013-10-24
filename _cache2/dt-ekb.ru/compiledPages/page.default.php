<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'favicon.ico') ?><? module("page:style", 'baseStyle.css') ?><? module("page:style", 'style.css') ?><? ob_start(); ?>
</head>

<body>
<? ob_start(); ?>
<center>
<div class="head">
	<div class="head2">
    <div class="padding">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td class="left logo">
	 
 	<a href="<? module("getURL"); ?>" class="logoLink"></a>
 	
    </td>
    <td class="phone2"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:phone", $module_data); ?></td>
    <td class="address2"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:address", $module_data); ?></td>
    <td class="right">
	
        <div class="nav2 menu inline">
          <ul>
            <li class="home"><a href="<? module("getURL"); ?>"></a></li>
            <li class="map"><a href="<? module("getURL:map"); ?>"></a></li>
            <li class="feedback"><a href="<? module("getURL:feedback"); ?>"></a></li>
          </ul>
        </div>
    
    </td>

</tr>
</table>

<ul class="default logo">
    <li class="l10"><a href="<? module("getURL:natalie-tours"); ?>"></a></li>
    <li class="l11"><a href="<? module("getURL:coraltravel"); ?>"></a></li>
    <li class="l12"><a href="<? module("getURL:teztour"); ?>"></a></li>
    <li class="l13"><a href="<? module("getURL:anextour"); ?>"></a></li>
    <li class="l14"><a href="<? module("getURL:pangeya-travel"); ?>"></a></li>
</ul>

</div>
</div>
</div>
<div class="page padding">
<div class="mainMenu menu inline popup">
<? $module_data = array(); $module_data["prop"]["!place"] = "menu"; moduleEx("doc:read:menu2:id", $module_data); ?>
</div><br clear="all" />
<h1 class="title"><? module("page:title"); ?></h1>
<? module("display"); ?><br clear="all" />
</div>
<div class="copyright">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><? module("read:copyright"); ?></td>
    <td width="200" valign="bottom"><? module("read:counters"); ?></td>
  </tr>
</table>
</div>
</center>
<? module("read:SEO"); ?>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>