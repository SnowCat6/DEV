<? //	Template admin_edit loaded from  _modules/_module.admin/template.admin_edit.php3 ?>
<?
function admin_edit($val, &$data)
{
	@$layout= $data[':layout'];
	@$bTop	= $data[':useTopMenu'];
	@$dragID= $data[':draggable'];
	if ($dragID) module('script:draggable');
	module('script:ajaxLink');
	@define('noCache', true);
?>
<? module("page:style", 'admin.css') ?>
<div class="adminEditArea">
<? if ($bTop){ ?>
<div class="adminEditMenu">
<? if ($dragID){ ?><div class="ui-icon ui-icon-arrow-4-diag"<? if(isset($dragID)) echo $dragID ?>></div><? } ?>
<? foreach($data as $name => $url){
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="<? if(isset($url)) echo $url ?>"<? if(isset($iid)) echo $iid ?>><? if(isset($name)) echo htmlspecialchars($name) ?></a><? } ?>
</div>
<?= $layout ?>
<? }else{ ?>
<?= $layout ?>
<div class="adminEditMenu adminBottom"<? if(isset($dragID)) echo $dragID ?>>
<?
foreach($data as $name => $url){
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="<? if(isset($url)) echo $url ?>"<? if(isset($iid)) echo $iid ?>><? if(isset($name)) echo htmlspecialchars($name) ?></a><? } ?>
</div>
<? } ?>
</div>
<? } ?>
<? //	Template admin_toolbar loaded from  _modules/_module.admin/template.admin_toolbar.php3 ?>
<? function admin_toolbar()
{
	if (defined('admin_toolbar')) return;
	define('admin_toolbar', true);
	
	if (!access('use', 'adminPanel')) return;
	module('admin:tabUpdate:admin_panel');
?>
<? module("script:jq_ui"); ?><? module("script:ajaxLink"); ?>
<? module("page:style", 'admin.css') ?>
<? module("page:style", 'baseStyle.css') ?>
<div class="adminToolbar"></div>
<div class="adminHover">
<div class="adminPanel">Панель управления сайтом</div>
<div class="adminTools adminForm">
	<div style="padding:0 0 30px 50px; margin-left:-50px;">
	<? module("admin:tab:admin_panel"); ?>
    </div>
</div>
</div>
<? } ?>

<? //	Template module_ajax loaded from  _modules/_module.ajax/template.module_ajax.php3 ?>
<?
function module_ajax($val, &$data)
{
	setTemplate('');
	$fn = getFn("ajax_$val");
	return $fn?$fn($data):NULL;
}
function ajax_read($data){
	@$template = $data[1];
	module("doc:read:$template", getValue('search'));
}
?>
<? function script_ajaxLayout($val){ module('script:jq'); ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
var ajaxLayoutHTML = new Array();
$(function(){
	$(".ajaxLayout input").keyup(function(){
		if ($(this).val() == $(this).attr("oldValue")) return;
		$(this).attr("oldValue", $(this).val());
		loadAjaxLayout($(this).parents("form.ajaxLayout"));
	})
	.change(function(){
		if ($(this).val() == $(this).attr("oldValue")) return;
		$(this).attr("oldValue", $(this).val());
		loadAjaxLayout($(this).parents("form.ajaxLayout"));
	});
});
function loadAjaxLayout(f)
{
	if (f.hasClass("loading")) return f.addClass("needUpdate");

	var ajaxLayoutEmpty = true;
	$(f.find("input")).each(function(){
		if ($(this).attr("type").toLowerCase() == "submit") return;
		if ($(this).attr("type").toLowerCase() == "hidden") return;

		if ($(this).val() != ""){
			ajaxLayoutEmpty = false;
		}
	});

	var id = f.attr("id");
	var layout = $("div#" + id);
	var ctx = layout.find(".layoutContent");
	if (ctx.length == 0) ctx = layout;
	
	if (ajaxLayoutHTML[id] == null){
		ajaxLayoutHTML[id] = layout.html();
		if (ajaxLayoutEmpty) return;
	}else{
		if (ajaxLayoutEmpty){
			layout.html(ajaxLayoutHTML[id]);
			$(document).trigger("jqReady");
			return;
		}
	}
	
	var url = "ajax_read_" + layout.attr("template") + ".htm";
	var data = f.serialize();
	var r = ("" + f.attr("replace")).split(":");
	if (r.length==2) data = data.replace(new RegExp(r[0], 'g'),r[1]);
	f.addClass("loading");
	
	$(layout.find(".layoutTitle")).show();
	$(layout.find(".layoutError")).hide();
	ctx.html('<div class="layoutLoading">Загрузка результата.</div>');
	ctx.load(url, data, function(text){
		//	on load
		f.removeClass("loading");
		if (f.hasClass("needUpdate")){
			return loadAjaxLayout(f.removeClass("needUpdate"));
		}
		if (ctx.text().replace(/\s+/, '') == ""){
			$(layout.find(".layoutError")).show();
		}
		$(document).trigger("jqReady");
	});
}
 /*]]>*/
</script>
<style>
.layoutError, .layoutTitle{
	display:none;
}
</style>
<? } ?>
<? function ajax_edit(&$data)
{
	@$id = (int)$data[1];
	switch(getValue('ajax')){
	//	Добавть к родителю
	case 'itemAdd';
		$s	= getValue('data');
		if (@$s['parent']){
			$s['prop'][':parent'] = alias2doc($s['parent']);
			unset($s['parent']);
		}
		if (@$s['parent*']){
			$s['prop'][':parent'] = alias2doc((int)$s['parent*']);
			unset($s['parent*']);
		}
		
		if (is_array(@$s['prop']))
		{
			$prop		= module("prop:get:$id");
			foreach($s['prop'] as $name => &$val){
				@$v = $prop[$name];
				if (!$v) continue;
				$val = "$val, $v[property]";
			}
			@$s[':property'] = $s['prop'];
			
			module("doc:update:$id:edit", $s);
			module('display:message');
		}
		
		setTemplate('');
		$template	= getValue('template');
		return module("doc:read:$template",  getValue('data'));
	//	Удалить от родителя
	case 'itemRemove':
		$s			= getValue('data');
		if (@$s['parent']){
			$s['prop'][':parent'] = alias2doc($s['parent']);
			unset($s['parent']);
		}

		if (is_array(@$s['prop']))
		{
			$prop		= module("prop:get:$id");
			foreach($s['prop'] as $name => &$val){
				@$v = $prop[$name];
				if (!$v) continue;
				$props = explode(', ', $v['property']);
				foreach($props as &$propVal){
					if ($val == $propVal) $propVal = '';
				};
				$val = implode(', ', $props);
			}
			@$s[':property'] = $s['prop'];
			
			module("doc:update:$id:edit", $s);
			module('display:message');
		}
		
		setTemplate('');
		$template	= getValue('template');
		return module("doc:read:$template",  getValue('data'));
	case 'itemOrder':
	break;
	}
}?>

<? //	Template doc_page_catalog loaded from  _modules/_module.doc/_pages/_pages/template.doc_page_catalog.php3 ?>
<? function doc_page_catalog(&$db, &$menu, &$data){
	$id = $db->id();
?>
<? module("page:style", 'baseStyle.css') ?>
<? beginAdmin() ?>
<? document($data) ?>
<? endAdmin($menu, true) ?>

<? $search = module("doc:search:$id", getValue('search')) ?>
<div class="product list">
<?	if ($search){ ?>
<h2>Поиск по каталогу</h2>
<? module('doc:read:catalog', $search) ?>
<? }else{ ?><? $module_data = array(); $module_data["parent*"] = "$id:catalog"; $module_data["type"] = "product"; $module_data["url"] = ""; moduleEx("doc:read:catalog", $module_data); ?><? } ?>
</div>
<? } ?><? //	Template doc_page_page_index loaded from  _modules/_module.doc/_pages/_pages/template.doc_page_page_index.php3 ?>
<? function doc_page_page_index($db, $val, $data){
	if (!testValue('ajax')) setTemplate('index'); 
} ?>
<? //	Template doc_page_product loaded from  _modules/_module.doc/_pages/_pages/template.doc_page_product.php3 ?>
<?
function doc_page_product(&$db, &$menu, &$data){
	$id		= $db->id();
	$folder	= $db->folder();
	$price	= docPriceFormat2($data);
	m('script:scroll');
	m('script:lightbox');
?>
<? beginAdmin() ?>
<div class="product page">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th width="250" valign="top">
<?  if (beginCompile($data, "productPageImage")){ ?>
<? displayThumbImage($title = docTitleImage($id), array(250, 350), ' class="thumb"', '', $title) ?>
<? $module_data = array(); $module_data["src"] = "$folder/Gallery"; moduleEx("gallery:small", $module_data); ?>
<?  endCompile($data, "productPageImage"); } ?>
    </th>
    <td width="100%" valign="top">
    <? if(isset($price)) echo $price ?>
    <? module("bask:button:$id"); ?><br />
<? if ($p = m('prop:read', array('id'=>$id))){ ?>
    <h2>Характеристики</h2>
    <? if(isset($p)) echo $p ?>
<? } ?>
    </td>
</tr>
</table>
<p><? document($data) ?></p>
</div>
<? endAdmin($menu, true) ?>
<? event('document.comment',	$id)?>
<? } ?><? //	Template doc_read_catalog loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_catalog.php3 ?>
<? function doc_read_catalog(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;

	module('script:lightbox');
	module('script:ajaxLink');
	$maxCol	= 2;
	$percent= round(100/$maxCol);
	$p		= dbSeek($db, 15*$maxCol, array('search' => $search));
?>
<? if(isset($p)) echo $p ?>
<table class="productTable">
<? while(true){
	$table	= array();
	for($ix = 0; $ix < $maxCol; ++$ix){
		if ($table[$ix] = $db->next()) continue;
		if ($ix == 0) break;
	}
	if ($ix == 0) break;
?>
<tr>
<? foreach($table as &$data){
	$db->data	= $data;
	$id			= $db->id();
	$menu		= doc_menu($id, $data);
	$url		= getURL($db->url());
	$price		= docPriceFormat2($data);
?>
<th><?  if (beginCompile($data, "catalogThumb2")){ ?>
<? if($id) displayThumbImage($title = docTitleImage($id), array(120, 150), '', '', $title); else echo '&nbsp;'; ?>
<?  endCompile($data, "catalogThumb2"); } ?></th>
<td width="<? if(isset($percent)) echo htmlspecialchars($percent) ?>%"><? if ($id){ ?><? beginAdmin() ?>
<h3><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h3>
<? if(isset($price)) echo $price ?>
<? module("bask:button:$id"); ?>
<? endAdmin($menu, true) ?>
<? }else echo '&nbsp;'; ?></td>
<? }//	foreach ?></tr>
<? }//	while ?>
</table>
<? if(isset($p)) echo $p ?>
<? return $search; } ?>
<? //	Template doc_read_default loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_default.php3 ?>
<?
function doc_read_default(&$db, $val, &$search){
	if (!$db->rows())  return $search;
?>
<? while($data = $db->next()){
	$fn		= getFn("doc_read_$data[doc_type]_$data[template]");
	if ($fn){
		$fn($db, $val, $search);
		continue;
	}
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?>
<? beginAdmin() ?>
<div><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></div>
<? endAdmin($menu, true) ?>
<? } ?>
<? return $search; } ?>
<? //	Template doc_read_news loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news.php3 ?>
<?
function doc_read_news(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	
	$date	= makeDate($data['datePublish']);
	if ($date){
		$date	= date('d.m.Y', $date);
		$date	= "<b>$date</b> ";
	}
?>
<p>
<? beginAdmin() ?>
<? if(isset($date)) echo $date ?><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
<? endAdmin($menu, true) ?>
</p>
<? } ?>
<? return $search; } ?>
<? //	Template doc_read_news2 loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news2.php3 ?>
<?
function doc_read_news2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= docNote($data);
?>
<? beginAdmin() ?>
<h3><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h3>
<p><? if(isset($note)) echo $note ?></p>
<? endAdmin($menu, true) ?>
<? } ?>
<? return $search; } ?>
<? //	Template doc_read_news3 loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news3.php3 ?>
<?
function doc_read_news3_before(&$db, $val, &$search){
	$search[':order'] = '`datePublish` DESC, `sort`';
}
function doc_read_news3(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<div class="news3">
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= docNote($data);
	
	$date	= makeDate($data['datePublish']);
	if ($date){
		$date	= date('d.m.Y', $date);
		$date	= "<b>$date</b> ";
	}
?>
<div>
<? beginAdmin() ?>
<?  if (beginCompile($data, "news3")){ ?>
<a href="<? if(isset($url)) echo $url ?>"><? displayThumbImageMask($folder = docTitleImage($id), 'design/maskNews.png') ?></a>
<?  endCompile($data, "news3"); } ?>
<date><? if(isset($date)) echo $date ?></date>
<a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
<blockquote><? if(isset($note)) echo $note ?></blockquote>
<? endAdmin($menu, true) ?>
</div>
<? } ?>
</div>
<? return $search; } ?>
<? //	Template doc_read_menu loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menu.php3 ?>
<?
function doc_read_menu(&$db, $val, &$search){ return showDocMenuDeep($db, $search,  0); }
function doc_read_menu2(&$db, $val, &$search){ return showDocMenuDeep($db, $search, 1); }
function doc_read_menu3(&$db, $val, &$search){ return showDocMenuDeep($db, $search, 2); }

function showDocMenuDeep($db, &$search, $deep)
{
	$db2	= module('doc');
	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);

	$tree = module('doc:childs:' . $deep, array('parent' => $ids, 'type' => @$search['type']));
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
	$url	= $db->url();
	@$fields= $data['fields'];
	$draggable	=docDraggableID($id, $data);
	$class = $id == currentPage()?'current':'';
	
	ob_start();
	@$childs	= &$tree[$id];
	if (showDocMenuDeepEx($db2, $childs)) $class = 'parent';
	$p = ob_get_clean();
	
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
	if ($db->ndx == 1) $class .= ' id="first"';
?>
    <li <? if(isset($class)) echo $class ?>><a href="<? module("getURL:$url"); ?>"<? if(isset($draggable)) echo $draggable ?>title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><span><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></span><? if(isset($note)) echo $note ?></a><? if(isset($p)) echo $p ?></li>
<? } ?>
</ul>
<? return $search; } ?>
<? function showDocMenuDeepEx($db2, &$tree)
{
	if (!$tree) return;
	
	$bFirst		= true;
	$bCurrent	= false;
	echo '<ul>';
	foreach($tree as $id => &$childs)
	{
		$data	= $db2->openID($id);
		$url	= getURL($db2->url($id));
		@$fields= $data['fields'];
		$title	= htmlspecialchars($data['title']);
		
		ob_start();
		$class = $id == currentPage()?'current':'';
		if (showDocMenuDeepEx($db2, $childs)) $class = 'parent';
		if ($class) $bCurrent = true;
		$p = ob_get_clean();
		
		if (@$c	= $fields['class']) $class .= " $c";
		if ($class) $class = " class=\"$class\"";
		if ($bFirst) $class .= ' id="first"';
		$bFirst = false;
		echo "<li$class><a href=\"$url\" title=\"$title\"><span>$title</span></a>$p</li>";
	}
	echo '</ul>';
	return $bCurrent;
}?><? //	Template doc_read_menuEx loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menuEx.php3 ?>
<? function doc_read_menuEx($db, $val, $search)
{
	m('script:menuEx');
	$bDrop	= access('write', 'doc:0');

	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);
	
	$tree	= module('doc:childs:1', array('parent' => $ids, 'type' => @$search['type']));
	$ddb	= module('doc');
?>
<div class="menu menuEx">
<? if ($bDrop) startDrop($search, 'menuEx', true) ?>
<ul>
<? while($data = $db->next()){
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
    <li <? if(isset($class)) echo $class ?>><a href="<? module("getURL:$url"); ?>"<? if(isset($draggable)) echo $draggable ?>><span><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></span><? if(isset($note)) echo $note ?></a>
<? showMenuEx($ddb, $childs, $val?htmlspecialchars($data[title]):'', $bDrop) ?>
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
<? module("script:jq"); ?>
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
<? function showMenuEx(&$db, &$tree, $title, $bDrop)
{
	if (!$tree) return;

	echo '<ul><div class="holder">';
	if ($title) echo "<h3>$title</h3>";
	foreach($tree as $id => &$childs){
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
}?><? //	Template doc_read_menuLink loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menuLink.php3 ?>
<?
function doc_read_menuLink(&$db, $val, &$search)
{
	$split = ' id="first"';
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$class	= currentPage() == $id?'current':'';
		@$fields	= $data['fields'];
		if (@$c	= $fields['class']) $class .= " $c";
		if ($class) $class = " class=\"$class\"";
?>
<a href="<? if(isset($url)) echo htmlspecialchars($url) ?>" <? if(isset($split)) echo $split ?> title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"<? if(isset($class)) echo $class ?>><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
<? $split = ''; } ?>
<? return $search; } ?><? //	Template doc_read_menuTable loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menuTable.php3 ?>
<?
function doc_read_menuTable(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;

	$percent= floor(100/$db->rows());
	$ddb	= module('doc');
	$split	= ' id="first"';
	module('script:menu');
?>
<table class="menu popup" cellpadding="0" cellspacing="0" width="100%">
<tr>
<? while($data = $db->next()){
	$id			= $db->id();
    $url		= getURL($db->url());
	$class		= currentPage() == $id?' class="current"':'';
	$draggable	= docDraggableID($id, $data);
?>
<td <? if(isset($class)) echo $class ?><? if(isset($split)) echo $split ?> width="<? if(isset($percent)) echo htmlspecialchars($percent) ?>%">
<a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"<? if(isset($draggable)) echo $draggable ?> title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
<?
$split	= ' id="first"';
$ddb->open(doc2sql(array('parent' => $id, 'type'=>array('page', 'catalog'))));
if ($ddb->rows()){
	echo '<ul>';
	while($data = $ddb->next()){
		$id			= $ddb->id();
		$title		= htmlspecialchars($data['title']);
		$url		= getURL($ddb->url());
		$draggable	=docDraggableID($id, $data);
		echo "<li$split><a href=\"$url\"$draggable>$title</a></li>";
	}
	echo '</ul>';
}
$split = '';
?>
</td>
<? } ?>
</tr>
</table>
<? return $search; } ?><? //	Template doc_search loaded from  _modules/_module.doc/_search/template.doc_search.php3 ?>
<?
function doc_search($db, $val, $search)
{
	@list($id, $group) = explode(':', $val);
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	//	Проверим параметры поиска
	if (!is_array($search)) $search = array();
	if ($search) $search = array('prop' => $search);
	
	if (!$group)
		$group = 'productSearch';

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= $search;
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';
//	$s['price']		= '1-';
	@$s['url'] 		= array('search' => $s['prop']);
	doc_sql($sql, $s);

	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, $searchHash = "search_".hashData($sql)))
		return $s;

	//	Получить свойства и кол-во товаров со свойствами
	$n		= $data['fields']['any']['searchProps'];
	if ($n && is_array($n)) $n = implode(',' , $n);
	else{
		$props	= module("prop:name:productSearch");
		$n		= implode(',', array_keys($props));
	}
	//////////////////
	//	Созание поиска
	if (!$prop){
		endCompile($data, $searchHash);
		return $s;
	}
	
	///////////////////
	//	Табличка поиска
?>
<table width="100%" cellpadding="0" cellspacing="0" class="search property">
<tr><td colspan="2" class="title">
<big>Ваш выбор:</big>
<?
//	Выведем уже имеющиеся в поиске варианты
$s1		= NULL;
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp= array();
foreach($sProp as $name => $val){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) continue;
	
	//	Сделаем ссылку поиска но без текущего элемента
	$s1		= $search;
	unset($s1['prop'][$name]);
	$url	= getURL("page$id", makeQueryString($s1['prop'], 'search'));
	$val	= propFormat($val, $props[$name]);
	//	Покажем значение
?><span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($val)) echo $val ?></a></span> <? } ?>
<? if ($s1){ ?><a href="<? module("getURL:page$id"); ?>" class="clear">очистить</a><? } ?>
</td></tr>
<?
//	Выведем основные характеристики
foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
<tr>
    <th title="<? if(isset($note)) echo htmlspecialchars($note) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?>:</th>
    <td width="100%">
<?
foreach($property as $pName => $count)
{
	$s1					= $search;
	$s1['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL("page$id", makeQueryString($s1['prop'], 'search'));
?>
<span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a> (<? if(isset($count)) echo htmlspecialchars($count) ?>)</span>
<? }//	each prperty ?>
	</td>
</tr>
<? }// each prop ?>
</table>
<?
	endCompile($data, $searchHash);
	return $s;
} ?>

<? //	Template doc_search2 loaded from  _modules/_module.doc/_search/template.doc_search2.php3 ?>
<?
function doc_search2($db, $val, $search)
{
	@list($id, $group) = explode(':', $val);
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	//	Проверим параметры поиска
	if (!is_array($search)) $search = array();
	if ($search) $search = array('prop' => $search);
	
	if (!$group) $group = 'productSearch';

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= $search;
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';
	@$s['url'] 		= array('search' => $s['prop']);
	doc_sql($sql, $s);

	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, $searchHash = "search2_".hashData($sql)))
		return $s;

	//	Получить свойства и кол-во товаров со свойствами
	
	
	$n		= $data['fields']['any']['searchProps'];
	if ($n && is_array($n)) $n = implode(',' , $n);
	else{
		$props	= module("prop:name:productSearch");
		$n		= implode(',', array_keys($props));
	}
	$prop	= $n?module("prop:count:$n", $s):array();
	//////////////////
	//	Созание поиска
	if (!$prop){
		endCompile($data, $searchHash);
		return $s;
	}
	
	///////////////////
	//	Табличка поиска
?>
<div class="search search2 property">
<div class="title">
<big>Ваш выбор:</big>
<?
//	Выведем уже имеющиеся в поиске варианты
$s1		= NULL;
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp= array();
foreach($sProp as $name => $val){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) unset($sProp[$name]);
}
if ($sProp){ ?><a href="<? module("getURL:page$id"); ?>" class="clear">очистить</a><? }

foreach($sProp as $name => $val){
	//	Сделаем ссылку поиска но без текущего элемента
	$s1		= $search;
	unset($s1['prop'][$name]);
	$url	= getURL("page$id", makeQueryString($s1['prop'], 'search'));
	$val	= propFormat($val, $props[$name]);
	//	Покажем значение
?><div><a href="<? if(isset($url)) echo $url ?>"><? if(isset($val)) echo $val ?></a></div> <? } ?>
</div>
<?
//	Выведем основные характеристики
$totalCount = 0;
foreach($prop as $name => &$property) $totalCount += count($property) + 2;

foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
<div class="panel">
<h3 title="<? if(isset($note)) echo htmlspecialchars($note) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?>:</h3>
<? $ix = 0; foreach($property as $pName => $count)
{
	$s1					= $search;
	$s1['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL("page$id", makeQueryString($s1['prop'], 'search'));
?>
<? if ($totalCount > 20 && count($property) > 10 && $ix++ == 5) echo '<div class="expand">'; ?>
<div><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a> (<? if(isset($count)) echo htmlspecialchars($count) ?>)</div>
<? }//	each prperty ?>
<? if ($ix >= 5) echo '</div>' ?>
</div>
<? }// each prop ?>
</div>
<?
	endCompile($data, $searchHash);
	return $s;
} ?>

<? //	Template doc_searchPage loaded from  _modules/_module.doc/_search/template.doc_searchPage.php3 ?>
<?
function doc_searchPage($db, $val, $data)
{
	//	Попробуем взять параетры из строки
	@list($type, $template) = explode(':', $val);
	//	Если типа документа нет, пробуем взять из данных
	if (!$type) @$type = $data[1];
	//	Проверить на наличие такого типа данных
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes[$type])) $type = '';
	//	Залать то, что показывать документы именно этого типа
	if ($type) $documentType = $type;
	else $documentType = 'news';
	//	Пробуем получить шаблон из данных
	if (!$template) @$template	= $data[2];
	//	Сделаем ссылку
	$searchURL	= $type?"search_$type":'search';
	if ($template) $searchURL .= "_$template";
	else $template = 'catalog';

	//	Получить данные для поиска
	$search = getValue('search');
	//	Сохранить поиск по имени
	$name	= $search['name'];
	//	Удалить возможные посторонние параетры
	if (isset($search['prop'])){
		//	Сохранить поиск по свойствам
		$search = array('prop' => $search['prop']);
	}else{
		//	Обнулить поиск
		$search = array();
	}
	//	Если был поиск по имени, восстановить
	if ($name) $search['name'] = $name;
	//	Кешировать поиск без данных
	if (!$search && !beginCache($cache = "pageSearchCache")) return;
	
	$s			= $search;
	$s['type']	= $type?$type:'product';

	$ddb	= module('prop');
	$names	= array();
	//	В зависимости от поиска, исать все параметры или только часть
	$groups	= $search?"globalSearch,globalSearch2":"globalSearch";
	//	Получить свойства и кол-во товаров со свойствами
	$props	= module("prop:name:$groups");
	$n		= implode(',', array_keys($props));
	$prop	= $n?module("prop:count:$n", $s):array();

	//	Заполнить выбранные свойства
	$selected	= array();
	@$sProp		= $search['prop'];
	if (!is_array($sProp)) $sProp = array();
	foreach($sProp as $name => $val)
	{
		if (!isset($prop[$name])) continue;
		$s2 = $search;
		unset($s2['prop'][$name]);
		$selected[$val]	= array(getURL($searchURL, makeQueryString($s2, 'search')), $name);
	}
	//	Заполнить свойства для выбора
	$select = array();
	foreach($prop as $name => &$property){
		if (isset($search['prop'][$name])) continue;
		$select[$name] = $property;
	}

	m('page:title', 'Поиск по сайту');
?>
<form action="<? module("getURL:$searchURL"); ?>" method="post" class="form searchForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" type="text" class="input w100" value="<? if(isset($search["name"])) echo htmlspecialchars($search["name"]) ?>" /></td>
    <th><input type="submit" name="button" class="button" value="Искать" /></th>
</tr>
</table>
<? if ($selected || $select){ ?>
<table class="search property" width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td colspan="2" class="title">
<big>Ваш выбор: </big>
<? foreach($selected as $val => $url){ list($url, $name) = $url;?>
<span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($val)) echo htmlspecialchars($val) ?></a></span>
<? } ?>
<? if ($selected){ ?><a href="<? module("getURL:$searchURL"); ?>" class="clear">очистить</a><? } ?>
    </td>
</tr>
<? foreach($select as $name => &$property){
	$note = $props[$name]['note'];
?>
<tr>
	<th title="<? if(isset($note)) echo htmlspecialchars($note) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></th>
    <td width="100%">
<? 
$ix = 0;
foreach($property as $pName => $count)
{
	$s2					= $search;
	$s2['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL($searchURL, makeQueryString($s2, 'search'));
	if ($ix++ == 50) echo '<div class="expand">';
?>
    <span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a> (<? if(isset($count)) echo htmlspecialchars($count) ?>)</span>
<? } ?>
<?
	if ($ix >= 50) echo '</div>';
?>
    </td>
</tr>
<? } ?>
</table>
<? } ?>
</form>
<?
$sql = array();
doc_sql($sql, $search);

if ($sql){ $p = m("doc:read:$template", $s); ?>
    <h2>Результат поиска:</h2>
<? if (!$p){ ?>
    <h3>По вашему запросу ничего не найдено</h3>
<? }else echo $p; ?>
<? } ?>
<? if (!$search) endCache($cache); ?>
<? } ?>
<? //	Template gallery_default loaded from  _modules/_module.gallery/template.gallery_default.php3 ?>
<?
function gallery_default($val, &$data)
{
	$files	= getFiles($data['src']);
	if (!$files) return;

	$row = 0; $cols = 4;
	for($ix = 0; $ix < count($files); ++$row){
		for($iix = 0; $iix < $cols; ++$iix){
			$path			= '';
			@list(,$path)	= each($files); ++$ix;
			$table[$row][]	= $path;
		}
	}
	$class = ' id="first"';
	@$id	= $data['id'];
	if ($id) $id = '[$id]';
?>
<? module("page:style", 'gallery.css') ?>
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr <? if(isset($class)) echo $class ?>>
<? $class2 = ' id="first"'; foreach($row as $path){?>
    <td <? if(isset($class2)) echo $class2 ?>><a href="<? if(isset($path)) echo htmlspecialchars($path) ?>" rel="lightbox<? if(isset($id)) echo htmlspecialchars($id) ?>"><? displayThumbImage($path, array(150, 150))?></a></td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; } ?>
</table>
<? } ?><? //	Template gallery_small loaded from  _modules/_module.gallery/_gallery.small/template.gallery_small.php3 ?>
<?
function gallery_small($val, $data)
{
	m('script:scroll');
	m('page:style', 'gallerySmall.css');
	@$files = getFiles($data['src']);
	if (!$files) return;

	@$id	= $data['id'];
	if ($id) $id = "[$id]";
	
	@$title	= htmlspecialchars($data['title']);
	if ($title) $title = "title=\"$title\"";
?>
<? module("page:style", 'gallerySmall.css') ?>
<div class="scroll gallery small">
<table cellpadding="0" cellspacing="0"><tr>
<? foreach($files as $path){ ?>
<td><a href="<? if(isset($path)) echo htmlspecialchars($path) ?>" rel="lightbox<? if(isset($id)) echo htmlspecialchars($id) ?>"<? if(isset($title)) echo $title ?>><? displayThumbImage($path, array(50, 50))?></a></td>
<? } ?>
</tr></table>
</div>
<? } ?><? //	Template gallery_smallVertical loaded from  _modules/_module.gallery/_gallery.small/template.gallery_smallVertical.php3 ?>
<?
function gallery_smallVertical($val, $data)
{
	$files = getFiles($data['src']);
	if (!$files) return;

	@$id	= $data['id'];
	if ($id) $id = "[$id]";

	@$title	= htmlspecialchars($data['title']);
	if ($title) $title = "title=\"$title\"";

	module('script:scroll');
?>
<? module("page:style", 'gallerySmall.css') ?>
<div class="vertical gallery small">
<table cellpadding="0" cellspacing="0">
<? foreach($files as $path){ ?>
<tr><td><a href="<? if(isset($path)) echo htmlspecialchars($path) ?>" rel="lightbox<? if(isset($id)) echo htmlspecialchars($id) ?>"<? if(isset($title)) echo $title ?>><? displayThumbImage($path, array(50, 50))?></a></td></tr>
<? } ?>
</table>
</div>
<? } ?><? //	Template user_loginForm loaded from  _modules/_module.user/_templates/template.user_loginForm.php3 ?>
<?
function user_loginForm($db, $val, $data){
	$login = getValue('login');
?>
<? module("script:ajaxLink"); ?>
<? if (!defined('userID')){ ?>
<form method="post" action="<? module("getURL:user_login"); ?>" class="form login">
<div style="width:230px">
<table border="0" cellspacing="0" cellpadding="2" width="100%">
    <tr>
        <th nowrap="nowrap">Логин:</th>
        <td width="100%"><input name="login[login]" value="<? if(isset($login["login"])) echo htmlspecialchars($login["login"]) ?>" type="text" class="input w100" /></td>
    </tr>
    <tr>
        <th nowrap="nowrap">Пароль:</th>
        <td width="100%"><input name="login[passw]" type="password" value="<? if(isset($login["passw"])) echo htmlspecialchars($login["passw"]) ?>" class="input password w100" /></td>
    </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<? if ($val){?>
<tr>
    <td valign="top" nowrap="nowrap"><label for="loginRemember">Помнить меня</label></td>
    <td align="right" valign="top"><input type="checkbox" name="login[remember]" class="checkbox" id="loginRemember" value="1"<?= @$login['remember']?' checked="checked"':''?> /></td>
</tr>
<? } ?>
  <tr>
    <td valign="top" nowrap="nowrap">
<? if (access('register', '')){ ?>
<div><a href="<? module("getURL:user_register"); ?>" id="ajax">Регистрация</a><br /></div>
<? } ?>
<? if (!$val){ ?><div><? module("loginza:enter"); ?></div><? } ?>
<? if ($val){ ?><div><a href="<? module("getURL:user_lost"); ?>" id="ajax">Напомнить пароль?</a></div><? } ?>
  	</td>
    <td align="right" valign="top"><input type="submit" value="OK" class="button" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">
	</td>
    <td align="right">&nbsp;</td>
  </tr>
</table>
</div>
</form>
<? }else{ ?>
<div class="form">
<a href="<? $module_data = array(); $module_data[] = "logout"; moduleEx("getURL", $module_data); ?>">Выход</a>
</div>
<? } ?>
<? } ?><? //	Template feedback_display loaded from  _modules/_nodule.feedback/template.feedback_display.php3 ?>
<? function feedback_display($formName, &$data)
{
	module('script:maskInput');
	$bShowTitle		= $formName == '';
	@list($formName, $template) = explode(':', $formName);

	if (!$formName){
		$formName	= @$data[1];
		$data		= NULL;
	}
	if (!$formName) $formName = 'feedback';
	
	$form = module("feedback:get:$formName");
	if (!$form) return;
	if ($formName && is_array($data)){
		dataMerge($data, $form);
		$form = $data;
	}
	
	@$class	= $form[':']['class'];
	if (!$class) $class="feedback";
	$form[':']['class'] = $class;

	@$url	= $form[':']['url'];
	if (!$url) $url	= getURL("#");
	$form[':']['url'] = $url;

	@$buttonName	= $form[':']['button'];
	if (!$buttonName) $buttonName = 'Отправить';
	$form[':']['button'] = $buttonName;
	
	@$title	= $form[':']['title'];
	if ($title && $bShowTitle) module("page:title", $title);
	
	$menu = array();
	if (hasAccessRole('admin,developer,writer')){
		$menu['Изменить#ajax'] = getURL("feedback_edit_$formName");
	}

	$fn = getFn("feedback_display_$template");
	if ($fn){
		beginAdmin($menu);
		$fn($formName, $form);
		endAdmin($menu);
		return;
	}
	
	beginAdmin($menu);
	$formData = getValue($formName);
	if (feedbackSend($formName, $formData, $form)){
		module('display:message');
		endAdmin($menu);
		return;
	}
	
	@$title2 = $form[':']['formTitle'];
?>
<? module("page:style", 'feedback/feedback.css') ?>
<div class="<? if(isset($class)) echo htmlspecialchars($class) ?>">
<form action="<? if(isset($url)) echo $url ?>" method="post" enctype="multipart/form-data" id="<? if(isset($formName)) echo htmlspecialchars($formName) ?>">
<? if ($title2){ ?><h2><? if(isset($title2)) echo htmlspecialchars($title2) ?></h2><? } ?>
<? module("display:message"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else @$thisValue = $data['default'];
?>
<? switch($type){ ?>
<? case 'hidden': ?>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
</tr>
<tr>
  <th colspan="2"><? feedbackTextArea($fieldName, $thisValue, $values)?></th>
</tr>
<? break; ?>
<? case 'phone':	//	text field?>
<tr>
    <th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackPhone($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackRadio($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackSelect($fieldName, $thisValue, $values)?> </td>
</tr>
<? break; ?>
<? default:	//	text field?>
<tr>
    <th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackText($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="<? if(isset($buttonName)) echo htmlspecialchars($buttonName) ?>" class="button" /></p>
</form>
</div>
<?  endAdmin($menu); } ?>

<? function feedbackSelect(&$fieldName, &$thisValue, &$values){ ?>
<select name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" class="input w100">
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' selected="selected"':'';
?>
	<option value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?>><? if(isset($value)) echo htmlspecialchars($value) ?></option>
<? } ?>
</select>
<? } ?>

<? function feedbackCheckbox(&$fieldName, &$thisValue, &$values){ ?>
<?
if (!is_array($thisValue)) $thisValue = explode(',', $thisValue);
$thisValue = array_values($thisValue);

foreach($values as $name => $value){
	$class = $value && is_int(array_search($value, $thisValue))?' checked="checked"':'';
?>
    <div><label><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[<? if(isset($value)) echo htmlspecialchars($value) ?>]" type="checkbox" value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?> /> <? if(isset($value)) echo htmlspecialchars($value) ?></label></div>
<? } ?>
<? } ?>

<? function feedbackRadio(&$fieldName, &$thisValue, &$values){ ?>
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' checked="checked"':'';
?>
    <div><label><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="radio" value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?> /> <? if(isset($value)) echo htmlspecialchars($value) ?></label></div>
<? } ?>
<? } ?>

<? function feedbackText(&$fieldName, &$thisValue, &$values){ ?>
<input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="text" class="input w100" value="<? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?>" />
<? } ?>

<? function feedbackTextArea(&$fieldName, &$thisValue, &$values){ ?>
<textarea name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" rows="5" class="input w100"><? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?></textarea>
<? } ?>

<? function feedbackPhone(&$fieldName, &$thisValue, &$values){ 	module('script:maskInput') ?>
<input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="text" class="input w100 phone" value="<? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?>" />
<? } ?>
<? //	Template feedback_display_vertical loaded from  _modules/_nodule.feedback/template.feedback_display_vertical.php3 ?>
<?
function feedback_display_vertical(&$formName, &$form)
{
	$formData = getValue($formName);
	if (feedbackSend($formName, $formData))
		return module('display:message');

	$class		= $form[':']['class'];
	$url		= $form[':']['url'];
	$buttonName	= $form[':']['button'];
	@$titleForm	= $form[':']['titleForm'];
?>
<? module("page:style", 'feedback/feedback.css') ?>
<div class="<? if(isset($class)) echo htmlspecialchars($class) ?> vertical">
<form action="<? if(isset($url)) echo $url ?>" method="post" enctype="multipart/form-data" id="<? if(isset($formName)) echo htmlspecialchars($formName) ?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? if ($titleForm){ ?>
<tr><th><h2><? if(isset($titleForm)) echo htmlspecialchars($titleForm) ?></h2></th></tr>
<? } ?>
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$default	= $data['default'];
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
?>
<? switch($type){ ?>
<? case 'hidden': break; ?>
<? default:	//	text field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackText($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><th><? feedbackTextArea($fieldName, $thisValue, $values)?></th></tr>
<? break; ?>
<? case 'phone':	//	text field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackPhone($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackRadio($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackSelect($fieldName, $thisValue, $values)?> </td></tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="<? if(isset($buttonName)) echo htmlspecialchars($buttonName) ?>" class="button" /></p>
</form>
</div>
<? } ?><? //	Template module_feedback loaded from  _modules/_nodule.feedback/template.module_feedback.php3 ?>
<?
function module_feedback($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("feedback_$fn");
	return $fn?$fn($val, $data):NULL;
}
function feedback_get($formName, $data)
{
	$form = getCacheValue("form_$formName");
	if (!isset($form)){
		$form = readIniFile(images."/feedback/form_$formName.txt");
		if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$formName.txt");
		setCacheValue("form_$formName", $form);
	}
	return $form;
}
function getFormFeedbackType($data){
	$types = getFormFeedbackTypes();
	foreach($types as $name => $type){
		if (isset($data[$type])) return $type;
	}
}
function getFormFeedbackTypes()
{
	$types = array();
	$types['Текстовое поле']= 'text';
	$types['Тема']			= 'subject';
	$types['Ф.И.О.']		= 'name';
	$types['Телефон']		= 'phone';
	$types['Скрытое поле'] = 'hidden';
	$types['Адрес эл. почты']	= 'email';
	$types['Список выбора']		= 'select';
	$types['Чекбоксы']			= 'checkbox';
	$types['Радиоконпки']		= 'radio';
	$types['Поле ввода текста'] = 'textarea';
	return $types;
}
function checkValidFeedbackForm($formName, &$formData)
{
	$form = module("feedback:get:$formName");
	if (!$form) return 'Не данных для формы';
	
	foreach($form as $name => $data){ 
		if ($name[0] == ':') continue;

		$thisField	= $name;
		$fieldName	= $formName."[$thisField]";

		$name	= htmlspecialchars($name);
		$type	= getFormFeedbackType($data);
		
		@$values	= explode(',', $data[$type]);
		@$thisValue = $formData[$thisField];

		$bMustBe		= $data['mustBe'] != '';
		$mustBe			= explode('|', $data['mustBe']);
		$bValuePresent	= trim($thisValue) != '';
		
		foreach($mustBe as $orField){
			@$bValuePresent |= trim($formData[$orField]) != '';
		}
		if ($bMustBe && !$bValuePresent){
			if (count($mustBe) > 1){
				$name = implode('"</b> или <b>"', $mustBe);
			}
			return "Заполните обязательное поле \"<b>$name</b>\"";
		}

		switch($type){
		case 'select':
		case 'radio':
			if (!$thisValue) break;
			if (!is_int(array_search($thisValue, $values)))
				return "Неверное значение в поле \"<b>$name</b>\"";
			break;
		case 'checkbox':
			if (!$thisValue) break;
			if (!is_array($thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";
			$thisValue = array_values($thisValue);
			foreach($thisValue as $val){
				if (!is_int(array_search($val, $values)))
					return "Неверное значение в поле \"<b>$name</b>\"";
			}
			break;
		case 'email':
			if (!$thisValue) break;
			if (!module('mail:check', $thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";
			break;
		}
	 }
	 return true;
}
function makeFeedbackMail($formName, &$formData, $form = NULL)
{
	$error = checkValidFeedbackForm($formName, $formData);
	if (is_string($error)) return $error;

	if (!$form)	$form = module("feedback:get:$formName");
	$ini		= getCacheValue('ini');
	
	$mail		= '';
	$mailHtml	= '';
	@$mailTo	= $form[':']['mailTo'];

	@$title = $form[':']['mailTitle'];
	if (!$title) @$title = $form[':']['title'];
	if (!$title) @$title =  $form[':']['formTitle'];

	$mailFrom	= '';
	$nameFrom	= '';
	
	if (!$mailTo) @$mailTo = $ini[':mail']['mailFeedback'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];
	
	foreach($form as $name => $data)
	{ 
		if ($name[0] == ':') continue;
		
		$thisField	= $name;
		$type		= getFormFeedbackType($data);
		@$thisValue = $formData[$thisField];

		switch($type){
		default:
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		case 'checkbox':
			if (!$thisValue) continue;
			$thisValue	= implode(', ', $thisValue);
			$thisValue	= trim($thisValue);
			$mail 		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		case 'email':
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mailFrom	= $thisValue;
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> <a href=\"mailto:$thisValue\">$thisValue</a></p>";
		break;
		case 'hidden':
			$thisValue	= trim($data['hidden']);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		}
	}

	$mailTemplate = mail("mail:template", $formName);

	$mailData = array('plain'=>$mail, 'html'=>$mailHtml);
	$mailData['mailFrom']	= $mailFrom;
	$mailData['nameFrom']	= $nameFrom;
	$mailData['mailTo']		= $mailTo;
	$mailData['title']		= $title;
	$mailData['template']	= $mailTemplate;
	return $mailData;
}
function sendFeedbackForm($formName, &$formData, $form = NULL)
{
	$mailData = makeFeedbackMail($formName, $formData, $form);
	if (is_string($mailData)) return $mailData;
	
	if (module("mail:send:$mailData[mailFrom]:$mailData[mailTo]:$mailData[template]:$mailData[title]", $mailData))
		return true;

	return true;
}

function feedbackSend(&$formName, &$formData, $form = NULL)
{
	if ($formData && !defined("formSend_$formName"))
	{
		define("formSend_$formName", true);
		$error = sendFeedbackForm($formName, $formData, $form);
		if (!is_string($error)){
			module('message', "Ваше сообщение отправлено.");
			return true;
		}
		module('message:error', $error);
	}
}
?>