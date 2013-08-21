<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{{!page:header}}
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/avtomag/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
</head>

<body>
{{!admin:toolbar}}
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
    <td width="25%" valign="top" class="left menu">{{doc:read:menu=prop.!place:menuLeft}}</td>
    <td width="50%" valign="top" class="center"><!-- TemplateBeginEditable name="title" -->
    <p>Продажа запчастей, расходников по низким ценам.
      Размещение объявлений на покупку и продажу. </p>
{{script:ajaxLink}}
<div class="login">
  <div>[ <a href="{{getURL:login}}" id="ajax" class="login">ВХОД</a> ] [ <a href="{{getURL:user_register}}" id="ajax" class="register">РЕГИСТРАЦИЯ</a> ]</div>
  <div><a href="{{getURL:user_lost}}" id="ajax" class="lost">забыл пароль</a></div>
</div>
    <!-- TemplateEndEditable --></td>
    <td width="25%" valign="top" class="right menu">{{doc:read:menu=prop.!place:menuRight}}</td>
  </tr>
</table>
<!-- TemplateBeginEditable name="search" -->
<? if (!testValue('search')){ ?>{{doc:searchPage}}<? } ?>
<!-- TemplateEndEditable --></div>
        </div>
        <div class="logo"><a href="{{getURL}}"><img src="../_sites/avtomag/design/logo.gif" width="165" height="145" border="0" /></a>    </div>
    </div>
  <!-- TemplateBeginEditable name="body" --><div class="body">{{display}}</div>  <!-- TemplateEndEditable -->
  <div class="copyright">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="30%" valign="top">{{read:copyright}}</td>
    <td width="50%" valign="top">{{read:copyright2}}</td>
    <td width="20%" valign="top">{{read:counters}}</td>
  </tr>
</table>
</div>
</div>
</body>
</html>