<?
function doc_read_oldMaster_beginCache($db, &$val, &$search)
{
	m('script:jq');
	$s	= getValue('search');
	if ($s['prop']) $search['prop'] = $s['prop'];
	$search['page']	= getValue('page');
	return hashData($search);
}

function doc_read_oldMaster($db, &$val, &$search)
{
	if (!$db->rows()) return $search;
	
	$max	= $db->rows() - 1;
	$cols	= 3;
	$row	= 0;
	$p		= dbSeek($db, 3*$cols+1, array('search' => getValue('search')));
?><? module("page:style", '../../style.css') ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td style="padding-right:30px">
<?
$data	= $db->next();
$id		= $db->id();
$link	= getURL($db->url());
$folder	= $db->folder();

$files	= getFiles(array("$folder/Title", "$folder/Gallery"));
?>
<div class="oldMasterSlot">
    <a href="<? if(isset($link)) echo $link ?>" id="gallery" title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>">
<?
$class	= 'class="current"';
foreach($files as $name => $path)
{
	list($w, $h) = getimagesize($path);
	if (count($files) > 1 && (!$h || $w/$h < 1.2 || $ix >= 10))
		continue;
?>
<div <? if(isset($class)) echo $class ?>><? displayThumbImageMask($path, 'design/oldMasterMask2.png')?></div>
<? $class = ''; } ?>
    </a>
    <div class="info">
    	<div class="place"></div>
        <h2><a href="<? if(isset($link)) echo $link ?>" title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h2>
    </div>
</div>
    </td>
	<td class="oldMasterInfo">
<?
$s1		= getValue('search');
//	Получить названия свойств для поиска
$props	= module("prop:name:productSearch");
$n		= implode(',', array_keys($props));
//	Получить названия и количество документов перечисленных свойств
$props	= $n?module("prop:count:$n", $search):array();
//	Вывести названия и кол-во с сылками на быстрый поиск
foreach($props as $n => &$prop){?>
<h3><? if(isset($n)) echo htmlspecialchars($n) ?></h3>
<div>
<?
//	Текущий параметр поиска
$thisValue	= $search['prop'][$n];
foreach($prop as $name => $count)
{
	$class	= $name == $thisValue?' class="current"':'';
	
	$s		= array();
	$s['prop']	= $s1['prop'];
	if (!$class) $s['prop'][$n] = $name;
	else  $s['prop'][$n] = '';
	
	removeEmpty($s);
	$s		= makeQueryString($s, 'search');
	
	$url	= getURL($db->url(currentPage()), $s);
?>
<a href="<? if(isset($url)) echo $url ?>" <? if(isset($class)) echo $class ?> title="<? if(isset($name)) echo htmlspecialchars($name) ?>"><span><? if(isset($name)) echo htmlspecialchars($name) ?></span><sup><? if(isset($count)) echo htmlspecialchars($count) ?></sup></a>
<? } ?>
</div>
<? } ?>
    </td>
</tr>
</table>
<script>
var oldMasterTimeout = 0;
$(function(){
	var items = $(".oldMasterSlot #gallery div");
	if (items.length < 2) return;
	oldMasterTimeout	= setTimeout(oldMasterNext, 2000);
});
function oldMasterNext()
{
	var i = $(".oldMasterSlot #gallery div.current")
		.removeClass("current")
		.show()
		.fadeOut(1000)
		.next();
		
	if (i.length == 0) i = $($(".oldMasterSlot #gallery div").get(0));
	i.addClass("current").hide().fadeIn(1000);

	clearTimeout(oldMasterTimeout);
	oldMasterTimeout	= setTimeout(oldMasterNext, 2000);
}
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="oldMaster">
<? while($db->next()){ ?>
    <tr>
        <td class="left"><? readOldMaster($db)?></td>
<? $db->next() ?>
        <td class="center"><? readOldMaster($db)?></td>
<? $db->next() ?>
        <td class="right"><? readOldMaster($db)?></td>
    </tr>
<? $row++; } ?>
</table>
<? if(isset($p)) echo $p ?><? return $searh; } ?><? function readOldMaster(&$db)
{
	$data = $db->data;
	if (!$data) return;
	
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
?>
<div>
    <a href="<? if(isset($link)) echo $link ?>" title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><? $module_data = array(); $module_data["mask"] = "design/oldMasterMask.png"; $module_data["popup"] = "false"; moduleEx("doc:titleImage:$id:mask", $module_data); ?></a>
    <h2><a href="<? if(isset($link)) echo $link ?>" title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h2>
    <blockquote><? if(isset($note)) echo $note ?></blockquote>
</div>
<? return $search; } ?>