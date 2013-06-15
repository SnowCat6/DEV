<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/dt-ekb.ru/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
<!-- TemplateParam name="logoAlign" type="text" value="top" -->
<!-- TemplateParam name="logoClass" type="text" value="" -->
{{!page:header}}
</head>

<body>
{{!admin:toolbar}}
<center>
<div class="head">
	<div class="head2">
    <div class="padding">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td class="left logo">
	<!-- TemplateBeginEditable name="logo" --> <a href="<?= getURL(docURL(currentPageRoot()))?>" class="l<?= currentPageRoot()?>"></a> <!-- TemplateEndEditable -->
    </td>
    <td class="phone2">{{read:phone=bottom}}</td>
    <td class="address2">{{read:address=bottom}}</td>
    <td class="right">
	<!-- TemplateBeginEditable name="logo2" --> <a href="{{getURL}}" class="logoLink"></a>
        <div class="nav2 menu horizontal">
          <ul>
            <li class="home"><a href="{{getURL}}"></a></li>
            <li class="map"><a href="{{getURL:map}}"></a></li>
            <li class="feedback"><a href="{{getURL:feedback}}"></a></li>
          </ul>
        </div>
    <!-- TemplateEndEditable -->
    </td>

</tr>
</table>
<!-- TemplateBeginEditable name="pageHead" --><!-- TemplateEndEditable -->
</div>
</div>
</div>
<div class="page padding"><!-- TemplateBeginEditable name="body" --> {{display}} <!-- TemplateEndEditable --></div>
<div class="copyright">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">{{read:copyright}}</td>
    <td width="200" valign="bottom">{{read:counters}}</td>
  </tr>
</table>
</div>
</center>
</body>
</html>