<? function doc_read_menuEx($db, $val, $search){?>
<div class="menu menuEx">
<ul>
<?
$ddb = module('doc');
while($data = $db->next()){
	$id			= $db->id();
	$hasCurrent = false;
	ob_start();

	$s = array('type' => @$search['type'], 'parent'=>$id);
	$ddb->open(doc2sql($s));
	if ($ddb->rows()){
?>
<ul>
<h2>{$data[title]}</h2>
<?
startDrop($s, 'menuEx');
while($d = $ddb->next())
{
	$iid	= $ddb->id();
	$url	= $ddb->url();
	$draggable	=docDraggableID($iid, $d);
	$class	= $ddb->ndx == 1?' id="first"':'';
	$bNow	= currentPage() == $iid;
	if ($bNow) $class .= ' class="current"';
	$hasCurrent |= $bNow;
?><li {!$class}><a href="{{getURL:$url}}"{!$draggable}>{$d[title]}</a></li><? } ?>
<? endDrop($s, 'menuEx') ?>
</ul>
<? } ?>
<?
	$p		= ob_get_clean();
	$url	= $db->url();
	$draggable	=docDraggableID($id, $data);
	$class	= $db->ndx == 1?' id="first"':'';
	if ($hasCurrent) $class .= ' class="parent"';
?>
    <li {!$class}>
    <a href="{{getURL:$url}}"{!$draggable}>{$data[title]}</a>
    {!$p}
</li>
<? } ?>
</ul>
</div>
<? return $search; } ?>
<style>
.menuEx a{
	display:block;
}
.menuEx{
	display:block;
	position:relative;
}
.menuEx ul ul{
	display:none;
	position:absolute;
	left:95%;
}
.catalogMenu{
	display:none;
}
</style>
<noscript>
<style>
.menuEx li:hover ul{ display:block; }
.catalogSelect:hover .catalogMenu{
	display:block;
}
</style>
</noscript>
{{script:jq}}
<script>
var mouseX = mouseY = 0;
var diffX = diffY = 0;
var menuOver = null;
var bScrollMenu = true;
var menuTimeout = 0;
$(function(){
	$(".catalogSelect").hover(function()
	{
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) == null){
			$(".catalogSelect .catalogMenu")
				.show("clip", { direction: "vertical"}, 100)
				.addClass("hasShadow");
		}else{
			$(".catalogSelect .catalogMenu").show();
		}
		clearMenuTimer();
	}, function()
	{
		clearMenuTimer(hideMenu);
	});
	$(".menuEx ul ul").hover(function()
	{
		bScrollMenu = false;
		menuOver = null;
	});
	$(".menuEx > ul > li").hover(function(ev)
	{
		if (menuOver && diffX > diffY/2){
			menuOver = $(this);
			return clearMenuTimer(showMenu);
		}
		menuOver = $(this);
		clearMenuTimer();
		showMenu()
	}, function(){
		clearMenuTimer(hideMenu);
	});
	$(".menuEx").mousemove(function(e){
		diffX = diffX*0.8 + Math.abs(e.pageX - mouseX);
		diffY = diffY*0.8 + Math.abs(e.pageY - mouseY);
		mouseX = e.pageX;
		mouseY = e.pageY;
	});
});
function clearMenuTimer(fn){
	if (menuTimeout) clearTimeout(menuTimeout);
	if (fn) menuTimeout = setTimeout(fn, 500);
	else menuTimeout = 0;
}
function showMenu()
{
	$(".menuEx ul ul").hide().clearQueue();
	var p = menuOver.find("ul");
	if (p.length == 0) menuOver = null;
	if (menuOver == null) return;

	p.show();
	if (bScrollMenu){
		var w = p.width();
		p	.css({width: 0, "min-width": "inherit", "overflow": "hidden"})
			.animate({width: w}, 150);
	}
	bScrollMenu = false;
}
function hideMenu(){
	clearMenuTimer();
	menuOver = null;
	bScrollMenu = true;
	$(".menuEx ul ul").hide();
	$(".catalogSelect .catalogMenu").hide();
}
</script>

