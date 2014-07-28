<?
function doc_read_menuEx_beginCache($db, $val, $search)
{
	m('script:menuEx');
	if (userID()) return;
	return hashData($search);
}
function doc_read_menuEx($db, $val, $search)
{
	m('script:menuEx');
	$bDrop	= access('write', 'doc:0');
	
	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);

	$s		= array('parent' => $ids, 'type' => @$search['type']);
	
	$key	= hashData($s);
	$key	= "doc:childs:$key";
	
	$tree	= getCache($key);
	if (!$tree){
		$tree	= module('doc:childs:1', $s);
		setCache($key, $tree);
	}
	$d		= &$tree[':data'];
	
	$ddb	= module('doc');
?>
<div class="menu menuEx">
<? if ($bDrop) startDrop($search, 'menuEx', true) ?>
<ul>
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	@$fields= $data['fields'];
	@$note	= $fields['note'];
	if ($note) $note = "<div>$note</div>";
	$draggable	= $bDrop?docDraggableID($id, $data):'';
	@$childs	= $tree[$id];
	
	$class = $id == currentPage()?'current':'';
	if (!$class && isset($childs[currentPage()])) $class = 'parent';
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
?>
    <li {!$class}><a href="{{getURL:$url}}"{!$draggable}><span>{$data[title]}</span>{!$note}</a>
<? showMenuEx($ddb, $childs, $val?htmlspecialchars($data['title']):'', $bDrop, $d); ?>
    </li>
<? } ?>
</ul>
<? if ($bDrop) endDrop($search, 'menuEx') ?>
</div>
<?  } ?>

<? function showMenuEx($db, &$tree, $title, $bDrop, &$d)
{
	if (!$tree) return;

	echo '<ul><div class="holder">';
	if ($title) echo "<h3>$title</h3>";
	foreach($tree as $id => &$childs)
	{
		$data	= $d[$id];
		if ($data) $db->setData($data);
		$data 	= $db->openID($id);
		$url	= getURL($db->url($id));

		@$fields= $data['fields'];
		@$note	= $fields['note'];
		if ($note) $note = "<div>$note</div>";
		$draggable	= $bDrop?docDraggableID($id, $data):'';
		$class	= currentPage() == $id?' current':'';
		if (@$c	= $fields['class']) $class .= " $c";
		if ($class) $class = " class=\"$class\"";
		$class	.= $db->ndx == 1?' id="first"':'';
	
		echo "<li$class><a href=\"$url\"$draggable><span>$data[title]</span></a></li>";
	}
	echo '</div></ul>';
}?>

<? function style_menuEx($val){ ?>
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
<? } ?>
<? function script_menuEx($val){ ?>
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
//		return $(this).parent().find("ul").length == 0;
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
