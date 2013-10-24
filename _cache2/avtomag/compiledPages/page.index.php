<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? ob_start(); ?><? module("page:style", 'baseStyle.css') ?><? module("page:style", 'style.css') ?>

<style>
.contentBorder{
	height:450px;
}
</style>

</head>

<body>
<? ob_start(); ?>
<div class="bkAdv">
  <div class="contentBox">
        <div class="contentBorder">
            <div class="contentBackgroud transparent"></div>
            <div class="dot left top"></div>
            <div class="dot right top"></div>
            <div class="dot left bottom"></div>
            <div class="dot right bottom"></div>

            <div class="content">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="25%" valign="top" class="left menu"><? $module_data = array(); $module_data["prop"]["!place"] = "menuLeft"; moduleEx("doc:read:menu", $module_data); ?></td>
    <td width="50%" valign="top" class="center">
<? module("read:title"); ?><? module("script:ajaxLink"); ?>
<div class="login">
  <div>[ <a href="<? module("getURL:login"); ?>" id="ajax" class="login">ВХОД</a> ] [ <a href="<? module("getURL:user_register"); ?>" id="ajax" class="register">РЕГИСТРАЦИЯ</a> ]</div>
  <div><a href="<? module("getURL:user_lost"); ?>" id="ajax" class="lost">забыл пароль</a></div>
</div>
    </td>
    <td width="25%" valign="top" class="right menu"><? $module_data = array(); $module_data["prop"]["!place"] = "menuRight"; moduleEx("doc:read:menu", $module_data); ?></td>
  </tr>
</table>

<? if (!testValue('search')){ ?>
<h1>Поиск по сайту</h1>
<? module("doc:searchPage:product:catalog"); ?><? } ?>
</div>
        </div>
        <div class="logo"><a href="<? module("getURL"); ?>"><img src="/design/logo.gif" width="165" height="145" border="0" /></a>    </div>
    </div>
  <div class="body"><? module("display"); ?></div>
  <div class="copyright">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="30%" valign="top"><? module("read:copyright"); ?></td>
    <td width="50%" valign="top"><? module("read:copyright2"); ?></td>
    <td width="20%" valign="top"><? module("read:counters"); ?></td>
  </tr>
</table>
</div>
</div>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>