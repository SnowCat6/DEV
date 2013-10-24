<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
<style>
body {
	padding:0;
	margin:0;
	font-family:Verdana, Geneva, sans-serif;
	font-size:12px;
}
/***********************************/
.body {
	min-width:800px;
	padding:0 20px;
	max-width:1200px;
	text-align:left;
}
.body2 {
}
.body3 {
}
/***************************************/
.logo {
	text-align:center;
	white-space:nowrap;
}
.logo * {
	margin:0;
	font-weight:normal;
	color:white;
	text-shadow:2px 2px 3px black;
	padding:0 5px;
}
.logo h1 {
	font-size:34px;
}
.logo h2 {
	font-size:16px;
}
.icon {
	padding-left:180px;
}
.info * {
	padding:0;
	margin:0;
	color:#184e8f;
}
.info h2 {
	font-weight:normal;
	font-size:14px;
	margin:5px 0;
}
/***********************************/
.head {
	background:url(../_sites/tis/design/head2.png) no-repeat 114px 0;
}
.head .menu a {
}
/*********************************/
.page .left .searchForm{
}
.searchForm table {
	border:solid 1px white;
	border-radius:10px;
	background:#fff;
}
.searchForm .input {
	padding:4px;
	font-size:18px;
	border:none;
	background:#fff;
}
.searchForm td {
	padding:2px 10px;
}
.searchForm th {
	padding:2px 2px 2px 5px;
}
.page .searchForm {
	display:block;
	background:#6b86aa;
	padding:5px 20px 5px 100px;
	margin:0;
	margin-bottom:20px;
}
/**********************************/
.page h1{
	font-weight:normal;
	margin:0;
}
.page .left{
}
.page .right {
	width:250px;
	min-width:250px;
	padding-left:40px;
}
.page h2 {
	font-size:23px;
	color:#666;
	font-weight:normal;
}
/**********************************/
.catalog a {
	font-size:16px;
	font-weight:bold;
	border-bottom:solid 2px #bf0000;
	text-decoration:none;
	color: #bf0000;
	display:block;
	padding-bottom:5px;
}
.catalog ul li{
	float:left;
	width:45%;
	margin-right:5%;
	margin-bottom:20px;
}
.catalog ul ul *{
	float:none;	width:auto;
	margin:0; border:none;
}
.catalog ul ul a {
	color:#4f79a1;
	font-weight:bold;
	font-size:14px;
	margin-top:10px; margin-bottom:5px;
	padding:0;
}
.catalog ul ul ul *{
	font-size:12px;
	color:#888;
	font-weight:normal;
	display:inline;
	line-height:1.5em;
}
.catalog ul ul ul a{ color:#555; white-space:nowrap; }
.catalog ul ul ul a:hover{ text-decoration:underline; }
.catalog ul ul ul li:before{ content: " | "; }
.catalog ul ul ul li#first:before{ content: ""; }

/*******************************/
.news b{
	display:block;
	color:#bf0000;
}
.news a{
	color:#555;
	text-decoration:none;
}
/*******************************/
.hotSale h2{
	color:red;
	margin:0;
}
/*****************************/
.copyright{
	padding:20px 40px;
	margin:40px 0 20px 0;
	border-top:solid 6px #800000;
}
.copyright, .copyright a{
}
/****************************/
.top{
	border-top:solid 6px #800000;
}
.top .right{
	width:250px;
}
.top td{
	padding:4px 0;
}
.top .menu a{
	text-decoration:none;
	padding-right:20px;
	color:#000180;
}
.top .button{
	background:#000180;
	border:none;
	border-radius:0;
	text-shadow:none;
	padding:4px 10px;
	box-shadow:none;
}
.top .input{
	border:solid 1px #000180;
	border-radius:0;
}
/*****************************/
.headUser{
	width:250px;
	min-width:250px;
	text-align:right;
	padding-left:20px;
	vertical-align:top;
}
.headUser table *{
	padding:2px 0;
}
.headUser .button{
	padding:2px 10px;
}
.headUser .input{
	padding:0;
	font-size:14px;
}
/************************/
.cabinet{
	text-align:left;
}
.cabinet .head{
	background:#000180;
	color:white;
	padding:4px 15px;
}
.cabinet .head a{
	display:block;
	float:right;
	color:white;
	text-decoration:none;
}
.cabinet h3{
	color:#000180;
}
.cabinet .menu a{
	text-decoration:none;
	color:#000180;
	border-left:solid 1px #888;
	padding:0 10px;
}
.cabinet #first{
	border:none;
	padding-left:0;
}
.cabinet .info{
	padding:0 15px;
}
.cabinet .info h3{
	padding:10px 0;
}
/************************/
.headBask{
	color:#000180;
	width:200px;
	min-width:200px;
}
.baskTitle{
	margin-bottom:10px;
}
/*************************/
.scroll td{
	width:200px;
}
.priceName{
	font-size:14px;
}
.price{
	color:#F00;
	font-weight:bold;
}
.productTable td, .productTable th{
	padding:5px 10px;
	font-weight:normal;
}
.productTable td{
	text-align:right;
	white-space:nowrap;
}
.productTable h3{
	margin:0;
}
.productTable a{
	color:#333;
	text-decoration:none;
}
</style>
</head>
<body>
{{!admin:toolbar}}
<center>
<div class="body">
  <div class="body2">
    <div class="body3">
      <table width="100%" border="0" cellpadding="0" cellspacing="0" class="head">
        <tr>
          <td width="114"><a href="{{url}}"><img src="../_sites/tis/design/logo2.gif" width="114" height="91" /></a></td>
          <td class="logo">
<h1>ТЕХИНСЕРВИС</h1>
<h2>инженерные системы и сети</h2>
            </td>
          <td width="100%">&nbsp;</td>
          <td class="headBask">{{bask:compact}}</td>
          <td class="headUser">{{user:compact}}</td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="top">
        <tr>
          <td class="menu inline">{{doc:read:menu=prop.!place:menu}}</td>
          <td class="right"><form id="form1" name="form1" method="post" action="{{getURL:search}}" class="padding">
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="100%"><input type="text" name="search[name]" value="" class="input w100" /></td>
                  <th><input type="submit" name="button" class="button" value="Найти" /></th>
                </tr>
              </table>
            </form></td>
        </tr>
      </table>
      <!-- TemplateBeginEditable name="body" -->
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="page">
        <tr>
          <td valign="top" class="left"><h2>Поиск по сайту:</h2>
            
            <div class="catalog menu">{{doc:read:menu3=type:catalog;prop.!place:map}}</div>
            <br clear="all" />
            <div class="hotSale">
              <h2>Хиты продаж</h2>
              {{doc:read:scroll=prop.!place:sales}} </div>
            <div class="padding"> {{display}} </div></td>
          <td valign="top" class="right"><h2>Новости:</h2>
            <div class="news">{{doc:read:news=type:article;prop.!place:news}}</div></td>
        </tr>
      </table>
      <!-- TemplateEndEditable --></div>
  </div>
</div>
<div class="copyright"> {{read:copyright}}<br />
</div>
</center>
</body>
</html>