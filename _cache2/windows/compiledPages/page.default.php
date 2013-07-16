<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", 'style.css') ?>


<? ob_start(); ?>
</head>

<body>
<? ob_start(); ?>
<div class="body">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="header">
<tr>
    <th align="left"><a href="<? module("getURL"); ?>"><img src="design/logo.gif" width="231" height="74" hspace="70" vspace="50" border="0" alt="" /></a></th>
    <td width="100%"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:headerAdv", $module_data); ?></td>
</tr>
</table>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" class="gradient"><img src="design/nav.gif" width="108" height="27" border="0" usemap="#Map" /></td>
      <td class="menu top gradient"><h1>Пластиковые окна с климат-контролем.</h1></td>
      <td class="menu top gradient">&nbsp;</td>
    </tr>
    <tr>
      <td valign="top" class="panel left">
<div class="menu left">
<? $module_data = array(); $module_data["prop"]["place"] = "left panel"; moduleEx("doc:read:menu2", $module_data); ?>
</div>

<div class="head">
<h2 class="gradient">Искренний сервис</h2>
<div>fdsfasdfas</div>
</div>

<div class="head">
<h2 class="gradient">Ваш консультант</h2>
<div><? module("read:conult"); ?></div>
</div>

<div class="head">
<h2 class="gradient"> Написать письмо Ген. директору</h2>
<div><? module("feedback:display:mail:vertical"); ?></div>
</div>
<img src="design/spacer.gif" width="223" height="1" alt="" />
        </td>
      <td width="100%" valign="top" class="center">
      <h1 class="page title"><? module("page:title"); ?></h1>
<? module("display"); ?> </td>
      <td valign="top" class="panel right">
<? module("read:adv"); ?>

<div class="head">
<h2 class="gradient"> Отзывы клиентов</h2>
<div><? $module_data = array(); $module_data["prop"]["place"] = "feedback"; moduleEx("doc:read:feedback", $module_data); ?></div>
</div>

<div class="head">
<h2 class="gradient"> Участник программы Малина </h2>
<div><? module("read:propgramm"); ?></div>
</div>

<div class="head">
<h2 class="gradient">Принимаем к оплате </h2>
<div><? module("read:pays"); ?></div>
</div>
<img src="design/spacer.gif" width="223" height="1" alt="" />
      </td>
    </tr>
  </table>
<div class="panel bottom">
<div class="menu bottom"><? $module_data = array(); $module_data["prop"]["place"] = "bottom menu"; moduleEx("doc:read:menu3", $module_data); ?></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td><? module("read:copyright"); ?></td>
    <td align="right"><? module("read:counters"); ?></td>
</tr>
</table>
</div>
</div>

<map name="Map" id="Map">
  <area shape="rect" coords="-2,-1,35,27" href="<? module("getURL"); ?>" alt="Главная" />
  <area shape="rect" coords="37,1,66,27" href="<? module("getURL:feedback"); ?>" alt="Обратная связь" />
  <area shape="rect" coords="70,0,98,28" href="<? module("getURL:map"); ?>" alt="Карта сайта" />
</map>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>