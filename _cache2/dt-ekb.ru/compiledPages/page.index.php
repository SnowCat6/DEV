<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'favicon.ico') ?><? module("page:style", 'style.css') ?><? module("page:style", 'baseStyle.css') ?><? ob_start(); ?>
</head>

<body>
<? ob_start(); ?>
<center>
<div class="body1"> 
  <div class="body2">
   	  <div class="body3">
        <div class="body4">
<ul class="index logo">
    <li class="l10"><a href="<? module("getURL:natalie-tours"); ?>"></a></li>
    <li class="l11"><a href="<? module("getURL:coraltravel"); ?>"></a></li>
    <li class="l12"><a href="<? module("getURL:teztour"); ?>"></a></li>
    <li class="l13"><a href="<? module("getURL:anextour"); ?>"></a></li>
    <li class="l14"><a href="<? module("getURL:pangeya-travel"); ?>"></a></li>
    <li class="l0"><a href="<? module("url:contacts"); ?>"></a></li>
</ul>
<div class="address1"><? module("read:address"); ?></div>
<div class="phone1"><? module("read:phone"); ?></div> 
<div class="nav menu inline">
    <ul>
        <li class="home"><a href="<? module("getURL"); ?>"></a></li>
        <li class="map"><a href="<? module("getURL:map"); ?>"></a></li>
        <li class="feedback"><a href="<? module("getURL:feedback"); ?>"></a></li>
    </ul>
</div>
<div class="social">
    <img src="/design/social.gif" alt="Social" width="149" height="36" border="0" usemap="#social" />
    <map name="social" id="social">
        <area shape="circle" coords="130,19,16" href="#" alt="odnoklassniki.ru" />
        <area shape="circle" coords="91,18,16" href="#" alt="twitter.com" />
        <area shape="circle" coords="56,19,16" href="#" alt="facebook.com" />
        <area shape="circle" coords="17,18,16" href="http://vk.com/dtekb" target="_blank" alt="vk.com/dtekb" />
    </map>
</div>
       </div>
      </div>
  </div>
</div>
<div class="page padding">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="top" class="left">
<p>Выберите оператора:</p>
<div class="menu">
<? $module_data = array(); $module_data["prop"]["!place"] = "operator"; moduleEx("doc:read:menu", $module_data); ?>
</div>
      </td>
      <td valign="top" class="center">
<div class="mainMenu menu inline popup">
<? $module_data = array(); $module_data["prop"]["!place"] = "menu"; moduleEx("doc:read:menu2:id", $module_data); ?>
</div><br clear="all" />
<? module("doc:page:index"); ?>
      </td>
      <td valign="top" class="right">
<h1><a href="<? module("getURL:news"); ?>">Новости</a></h1>
<? $module_data = array(); $module_data["parent"] = "news"; $module_data["max"] = "3"; moduleEx("doc:read:news3", $module_data); ?>
      </td>
    </tr>
  </table>
</div>
<div class="copyright">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><? module("read:copyright"); ?></td>
    <td width="200" valign="bottom"><? module("read:counters"); ?></td>
  </tr>
</table>
</div>
<? module("read:SEO"); ?>
</center>
<? module("script:jq"); ?>
<div id="overlay">This overlay popup</div>
<div id="contents" style="display:none">
<?
if (beginCache($cache = 'indexOperatorNote')){
	$db = module('doc');
	$db->open(doc2sql(array('type'=>'catalog', 'template'=>'operator')));
	while($data = $db->next()){
			@$document = getDocument($data);
			if (!$document) continue;
			$id = $db->id();
			echo "<div id=\"l$id\">$document</div>\r\n";
	}
	endCache();
}
?>
</div>
<style>
#overlay {
    position: absolute;
    background: #fff;
    color: #333;
	min-width:200px; min-height:50px;
	max-width:450px;
	display:none;
	padding:10px;
	border:solid 1px #ddd;
	border-radius:10px;
	box-shadow:5px 5px 10px rgba(0, 0, 0, 0.4);
}
</style>
<script>
$('.logo li').each(function(){
    var area = $(this), alt = area.attr("class");
	var html = $('#contents #' + alt).html();
	if (!html) return;
	area.mouseenter(function(){
		$('#overlay')
		.html(html)
		.css({display:"none"})
		.fadeIn(150);
		$('.hilite #' + alt).css("display", "block");
	}).mouseleave(function(){
		$('#overlay').fadeOut(50);
		$('.hilite #' + alt).css("display", "none");
	});
});
$("body").mousemove(function(kmouse){
	var overlay = $('#overlay');
	var x = kmouse.pageX+15, y = kmouse.pageY+15;
	var w = $(window).width() - 25 + $(window).scrollLeft(), h = $(window).height() - 25 + $(window).scrollTop();
	if (x + overlay.width() > w) x = Math.max(0, w - overlay.width());
	if (y + overlay.height()> h) y = Math.max(0, h - overlay.height());
	overlay.css({left:x, top:y});
});
</script>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>