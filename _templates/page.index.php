<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="cmsStyle.css"/>
{{page:header}}
</head>

<body>
{{!admin:toolbar}}
<div class="header">
  <div style="float:right">
	{{!user:loginForm}}
</div>
<div class="menu horizontal">{{doc:read:menu=prop.parent:topMenu}}</div>
<div class="DEV_adv"></div>
<div class="clear"></div>
</div>

<div class="body shadow">
<div><h1>Главная страница</h1></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">{{display}}</td>
    <td width="250" valign="top" class="rightPlace">
<h2>Новости</h2>
{{doc:read:news=type:article;prop.parent:news}}
<h2>Метки</h2>
{{prop:read=group:Теги}}
</td>
  </tr>
</table>
</div>

<div class="copyright">{{!read:copyright}}</div>
</body>
</html>