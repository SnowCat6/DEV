<!doctype html>
<html>
<head>
<meta charset="utf-8">
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{head}
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="../_sites/comfort/style.css">
</head>

<body>
{admin}
<div style="position:relative">
<!-- TemplateBeginEditable name="adv" --><!-- TemplateEndEditable -->
    <div class="body">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="logo">
  <tr>
    <td>
    <a href="{{url}}"><img src="../_sites/comfort/design/logo.gif" width="491" height="74"  alt=""/></a>
    </td>
    <td width="100%" align="right">{{read:header=bottom}}</td>
  </tr>
</table>
<div class="topMenu">{{doc:read:menuTable=prop.!place:topMenu}}</div>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="panel">
  <tr>
    <td class="catalog"><div class="content border">
        	<a href="" class="block">Каталог</a>
            <div class="menu">
              <div class="content3">
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="menu2">
  <tr>
    <td class="left">
    {{doc:read:cols=type:catalog;prop.!place:map}}
    </td>
    <td class="right">
    <h2>Брэнды</h2>
    {{prop:read:count:Бренд=type:product;cols:2}}
    </td>
  </tr>
</table>
              </div>
            </div>
        </div></td>
    <th></th>
    <td width="100%"><div class="border">
<form method="post" action="{{url:search}}">{{script:ajaxLink}}
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td class="content">Поиск</td>
      <td width="100%" class="content2"><input type="text" name="textfield" class="input w100"></td>
      <td><input type="submit" class="button2" value=""></td>
      </tr>
    </table>
</form>
    </div></td>
    <th></th>
    <td><div>
<div class="content border register">
  <a href="{{url:user_register}}" id="ajax" class="block">Регистрация</a>
  <span class="split"></span>
  <a href="{{url:user_login}}" id="ajax" class="block">Вход</a>
</div>
    </div></td>
    <th></th>
    <td><div class="content border bask">
      <a href="{{url:bask}}">{{bask:count}}</a>
</div>    </td>
  </tr>
</table>
    </div>
	<center>
    <div class="page"><!-- TemplateBeginEditable name="body" -->{{display}} <!-- TemplateEndEditable --></div>
    </center>
</div>
<center class="copyright">
	<div class="content4">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td width="50%">{{read:copyright}}</td>
    <td width="50%" class="menu menu3">{{doc:read:cols=prop.!place:bottomMenu}}</td>
  </tr>
</table>
    </div>
</center>
</body>
</html>