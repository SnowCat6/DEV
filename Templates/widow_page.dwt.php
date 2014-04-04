<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/windows/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
{{!admin:toolbar}}
<div class="body">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="header">
<tr>
    <th align="left"><a href="{{getURL}}"><img src="../_sites/windows/design/logo.gif" width="231" height="74" hspace="70" vspace="50" border="0" alt="" /></a></th>
    <td width="100%">{{read:headerAdv=bottom}}</td>
</tr>
</table>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" class="gradient"><img src="../_sites/windows/design/nav.gif" width="108" height="27" border="0" usemap="#Map" /></td>
      <td class="menu top gradient"><h1>Пластиковые окна с климат-контролем.</h1></td>
      <td class="menu top gradient">&nbsp;</td>
    </tr>
    <tr>
      <td valign="top" class="panel left">
<div class="menu left">
{{doc:read:menu2=prop.place:left panel}}
</div>

<div class="head">
<h2 class="gradient">Искренний сервис</h2>
<div>fdsfasdfas</div>
</div>

<div class="head">
<h2 class="gradient">Ваш консультант</h2>
<div>{{read:conult}}</div>
</div>

<div class="head">
<h2 class="gradient"> Написать письмо Ген. директору</h2>
<div>{{feedback:display:mail:vertical}}</div>
</div>
<img src="../_templates/design/spacer.gif" width="223" height="1" alt="" />
        </td>
      <td width="100%" valign="top" class="center"><!-- TemplateBeginEditable name="body" -->
      <h1 class="page title">{{page:title}}</h1>
{{display}} <!-- TemplateEndEditable --></td>
      <td valign="top" class="panel right">
{{read:adv}}

<div class="head">
<h2 class="gradient"> Отзывы клиентов</h2>
<div>{{doc:read:feedback=prop.place:feedback}}</div>
</div>

<div class="head">
<h2 class="gradient"> Участник программы Малина </h2>
<div>{{read:propgramm}}</div>
</div>

<div class="head">
<h2 class="gradient">Принимаем к оплате </h2>
<div>{{read:pays}}</div>
</div>
<img src="../_templates/design/spacer.gif" width="223" height="1" alt="" />
      </td>
    </tr>
  </table>
<div class="panel bottom">
<div class="menu bottom inline">{{doc:read:menu3=prop.place:bottom menu}}</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td>{{read:copyright}}</td>
    <td align="right">{{read:counters}}</td>
</tr>
</table>
</div>
</div>

<map name="Map" id="Map">
  <area shape="rect" coords="-2,-1,35,27" href="{{getURL}}" alt="Главная" />
  <area shape="rect" coords="37,1,66,27" href="{{getURL:feedback}}" alt="Обратная связь" />
  <area shape="rect" coords="70,0,98,28" href="{{getURL:map}}" alt="Карта сайта" />
</map>
</body>
</html>