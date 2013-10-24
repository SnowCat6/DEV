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
	
    <? $root = currentPageRoot() ?>
    <div class="l<? if(isset($root)) echo htmlspecialchars($root) ?>"><a href="<?= getURL(docURL($root))?>"></a></div>
    
    </td>
    <td class="phone2"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:phone", $module_data); ?></td>
    <td class="address2"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:address", $module_data); ?></td>
    <td class="right">
	 <a href="<? module("getURL"); ?>" class="logoLink"></a>
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

</div>
</div>
</div>
<div class="page padding">
<? if (!defined('_hasNavMap_')){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">
    <div class="operator mainMenu menu inline popup">
    <? $module_data = array(); $module_data["parent"] = "$root"; $module_data["type"] = "page,catalog"; moduleEx("doc:read:menu2", $module_data); ?>
    </div><br clear="all" />
    <? module("display"); ?>&nbsp;
    </td>
    <td valign="top" class="adv">
<div class="advFlow">
    <div><? module("operator:manager:$root"); ?></div>
    <div><? module("read:adv"); ?></div>
    <div><? module("read:advBottom$root"); ?></div>
</div>
    </td>
  </tr>
</table>
<? }else{ ?>
    <div class="operator mainMenu menu inline popup">
    <? $module_data = array(); $module_data["parent"] = "$root"; $module_data["type"] = "page,catalog"; moduleEx("doc:read:menu2", $module_data); ?>
    </div><br clear="all" />
    <? module("display"); ?><? } ?>
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