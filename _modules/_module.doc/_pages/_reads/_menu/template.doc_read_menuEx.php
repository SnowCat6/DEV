<? function doc_read_menuEx($db, $val, $search)
{
	m('script:menuEx');
	$bDrop	= access('write', 'doc:0');
	$ddb	= module('doc');
?>
<div class="menu menuEx">
<? if ($bDrop) startDrop($search, 'menuEx') ?>
<ul>
<?
while($data = $db->next()){
	$id			= $db->id();
	$hasCurrent = false;
	ob_start();

	$s = array('type' => @$search['type'], 'parent'=>$id);
	$ddb->open(doc2sql($s));
	if ($ddb->rows()){
?>
<ul><div class="holder">
<? if ($val){ ?><h2>{$data[title]}</h2><? } ?>
<?
if ($bDrop) startDrop($s, 'menuEx');
while($d = $ddb->next())
{
	$iid	= $ddb->id();
	$url	= $ddb->url();
	@$fields= $d['fields'];
	@$note	= $fields['note'];
	if ($note) $note = "<div>$note</div>";
	$draggable	=docDraggableID($iid, $d);
	$class	= $ddb->ndx == 1?' id="first"':'';
	$bNow	= currentPage() == $iid;
	if ($bNow) $class .= ' class="current"';
	$hasCurrent |= $bNow;
?><li {!$class}>
<a href="{{getURL:$url}}"{!$draggable}><span>{$d[title]}</span>{!$note}</a>
</li><? } ?>
<? if ($bDrop) endDrop($s, 'menuEx') ?>
</div></ul>
<? } ?>
<?
	$p		= ob_get_clean();
	$url	= $db->url();
	@$fields= $data['fields'];
	@$note	= $fields['note'];
	if ($note) $note = "<div>$note</div>";
	$draggable	=docDraggableID($id, $data);
	$class	= $db->ndx == 1?' id="first"':'';
	if ($hasCurrent) $class .= ' class="parent"';
?>
    <li {!$class}>
    <a href="{{getURL:$url}}"{!$draggable}><span>{$data[title]}</span>{!$note}</a>
    {!$p}
    </li>
<? } ?>
</ul>
<? if ($bDrop) endDrop($search, 'menuEx') ?>
</div>
<?  } ?>
<? function script_menuEx($val){ ?>
<style>
.menuEx ul ul{
	display:none;
	position:absolute;
	left:100%;
}
</style>
<noscript>
<style>
.menuEx ul li:hover ul{
	display:block;
}
</style>
</noscript>
{{script:jq}}
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
var mouseX = mouseY = 0;
var diffX = diffY = 0;
var menuOver = null;
var bScrollMenu = true;
var menuTimeout = 0;
var menuHideAll = false;
$(function(){
	$(".menuEx ul ul").hover(function()
	{
		clearMenuTimer();
		bScrollMenu = false;
		menuOver = null;
	}, function(){
		clearMenuTimer(hideMenuEx);
	});
	
	$(".menuEx ul > li > a").hover(function(ev)
	{
		if (menuOver && diffX > diffY/2){
			menuOver = $(this);
			return clearMenuTimer(showMenuEx);
		}
		menuOver = $(this);
		clearMenuTimer();
		showMenuEx()
	}, function(){
		if (menuHideAll) return;
		clearMenuTimer(hideMenuEx);
	}).click(function(){
		return $(this).parent().find("ul").length == 0;
	});
	
	$(".menuEx").mousemove(function(e){
		diffX = (e.pageX > mouseX)?(diffX*2 + e.pageX - mouseX)/3:0;
		diffY = (diffY*2 + Math.abs(e.pageY - mouseY))/3;
		mouseX = e.pageX; mouseY = e.pageY;
	});
});
function clearMenuTimer(fn){
	if (menuTimeout) clearTimeout(menuTimeout);
	if (fn) menuTimeout = setTimeout(fn, 800);
	else menuTimeout = 0;
}
function showMenuEx()
{
	$(".menuEx ul ul").stop(true, true).hide();
	var p = menuOver.parent().find("ul");
	if (p.length == 0) return hideMenuEx();
	if (menuOver == null) return;

	p.show();
	if (bScrollMenu){
		var w = p.width();
		var holder = p.find(".holder");
		var w2 = holder.width();
		holder.width(w2);
		p	.css({width: 0, "overflow": "hidden", "min-width": 0})
			.animate({width: w}, 150);
	}
	bScrollMenu = false;
}
function hideMenuEx(){
	clearMenuTimer();
	menuOver = null;
	bScrollMenu = true;
	$(".menuEx ul ul").stop(true, true).hide();
}
 /*]]>*/
</script>
<? } ?>
