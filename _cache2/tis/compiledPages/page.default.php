<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>


<? ob_start(); ?>
<style>
body {
	padding:0;
	margin:0;
	font-family:Verdana, Geneva, sans-serif;
	font-size:12px;
}
/***********************************/
.body {
	background:url(design/headBG.jpg) repeat-x;
	min-width:800px;
}
.body2 {
	background:url(design/headBGl.jpg) no-repeat;
}
.body3 {
	min-height:228px;
	background:url(design/headBGr.jpg) no-repeat;
	background-position:right 0;
}
/***************************************/
.logo {
	padding:0 50px 0 120px;
	text-align:center;
	white-space:nowrap;
}
.logo * {
	margin:0;
	font-weight:normal;
	color:#184e8f;
}
.logo h1 {
	font-size:28px;
}
.logo h2 {
	font-size:12px;
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
	margin-bottom:30px;
}
.head .menu a {
	color:white;
	text-decoration:none;
	white-space:nowrap;
	margin-right:20px;
	text-shadow:1px 1px 0px RGB(0, 0, 0);
}
/*********************************/
.page .left .searchForm{
	margin-left:-1--px;
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
	padding-left:60px;
}
.page .right {
	width:250px;
	min-width:250px;
	padding-left:40px;
	padding-right:100px;
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
	background:#232835;
	padding:20px 40px;
	margin:40px 0 20px 0;
}
.copyright, .copyright a{
	color:white;
}
</style>
</head>
<body>
<? ob_start(); ?>
<div class="body">
  <div class="body2">
    <div class="body3">
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="head">
        <tr>
          <td height="60" class="logo"><h1>ТЕХИНСЕРВИС</h1>
            <h2>инженерные системы и сети</h2></td>
          <td width="100%" class="info"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:info", $module_data); ?></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td height="30" class="icon"><img src="design/icon.gif" width="82" height="15" border="0" usemap="#navIcons" />
            <map name="navIcons" id="navIcons">
              <area shape="rect" coords="0,-3,22,17" href="<? module("getURL"); ?>" />
              <area shape="rect" coords="29,0,53,17" href="<? module("getURL:map"); ?>" />
              <area shape="rect" coords="62,-1,82,16" href="<? module("getURL:feedback"); ?>" />
            </map></td>
          <td colspan="2" class="menu"><? $module_data = array(); $module_data["prop"]["!place"] = "menu"; moduleEx("doc:read:menuLink", $module_data); ?></td>
        </tr>
      </table>
      
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="page">
        <tr>
          <td valign="top" class="left padding">
          <h1><? module("page:title"); ?></h1>
          <? module("display"); ?>
          </td>
          <td valign="top" class="right"><h2>Новости:</h2>
            <div class="news"><? $module_data = array(); $module_data["type"] = "article"; $module_data["prop"]["!place"] = "news"; moduleEx("doc:read:news", $module_data); ?></div></td>
        </tr>
      </table>
      </div>
  </div>
</div>
<div class="copyright"> <? module("read:copyright"); ?><br />
</div>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>