<? function doc_read_menuEx($db, $val, $search){?>
<div class="menu menuEx">
<ul>
<?
$ddb = module('doc');
while($data = $db->next()){
	$id			= $db->id();
	$hasCurrent = false;
	ob_start();

	$ddb->open(doc2sql(array('type' => @$search['type'], 'parent'=>$id)));
	if ($ddb->rows()){
?>
<ul>
<? while($d = $ddb->next())
{
	$iid	= $ddb->id();
	$url	= $ddb->url();
	$class	= $ddb->ndx == 1?' id="first"':'';
	$bNow	= currentPage() == $iid;
	if ($bNow) $class .= ' class="current"';
	$hasCurrent |= $bNow;
?><li{!$class}><a href="{{getURL:$url}}">{$d[title]}</a></li><? } ?>
</ul>
<? } ?>
<?
	$p		= ob_get_clean();
	$url	= $db->url();
	$class	= $db->ndx == 1?' id="first"':'';
	if ($hasCurrent) $class .= ' class="parent"';
?>
    <li{!$class}><a href="{{getURL:$url}}">{$data[title]}</a>
    {!$p}
</li>
<? } ?>
</ul>
<div class="debug"></div>
</div>
<? return $search; } ?>
<style>
.menuEx a{
	display:block;
	padding:5px 0px;
	text-decoration:none;
	color:#333;
}
.menuEx ul ul a{
	padding:5px 40px;
}
.menuEx .parent ul a{
	color:#333;
}
.menuEx ul .current a, .menuEx .parent a{
	color:#F60;
}
.menuEx ul ul a:hover{
	background:#eee;
}
.menuEx a:hover{
	background:#eee;
}
.menuEx{
	display:block;
	padding:10px;
	position:relative;
}
.menuEx ul ul{
	display:none;
	position:absolute;
	left:95%; top:-20px;
	padding:20px 0;
	z-index:100;
	background:white;
	border:solid 1px #eee;
	box-shadow:0 0 20px rgba(0, 0, 0, 0.2);
	white-space:nowrap;
	min-width:300px;
}
</style>
<noscript>
<style>
.menuEx li:hover ul{
	display:block;
}
</style>
</noscript>
{{script:jq}}
<script>
var mouseX = mouseY = 0;
var diffX = diffY = 0;
var menuOver = null;
var menuTimeout = 0;
$(function(){
	$(".menuEx > ul > li").hover(function(ev)
	{
		clearTimeout(menuTimeout);
		if (menuOver && diffX > diffY/2){
			menuOver = $(this);
			menuTimeout = setTimeout(showMenu, 500);
			return;
		}
		menuOver = $(this);
		showMenu()
	}, function(){
		clearTimeout(menuTimeout);
		menuTimeout = setTimeout(hideMenu, 500);
	});
	$(".menuEx").mousemove(function(e){
		diffX = diffX/2 + Math.abs(e.pageX - mouseX);
		diffY = diffY/2 + Math.abs(e.pageY - mouseY);
		mouseX = e.pageX;
		mouseY = e.pageY;
	});
});
function showMenu(){
	$(".menuEx ul ul").hide();
	if (menuOver) menuOver.find("ul").show();
}
function hideMenu(){
	menuOver = null;
	$(".menuEx ul ul").hide();
}
</script>

