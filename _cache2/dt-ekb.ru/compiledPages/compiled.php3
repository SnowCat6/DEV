<? //	Template admin_edit loaded from  _modules/_module.admin/template.admin_edit.php3 ?>
<?
function admin_edit($val, &$data)
{
	setNoCache();
	$layout	= $data[':layout'];
	$bTop	= $data[':useTopMenu'];
	$dragID	= $data[':draggable'];
	if ($dragID) module('script:draggable');
	module('script:ajaxLink');
	define('noCache', true);
?><? module("page:style", 'admin.css') ?>
<div class="adminEditArea">
<? if ($bTop){ ?>
<div class="adminEditMenu">
<? if ($dragID){ ?><div class="ui-icon ui-icon-arrow-4-diag"<? if(isset($dragID)) echo $dragID ?>></div><? } ?><? foreach($data as $name => $url){
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="<? if(isset($url)) echo $url ?>"<? if(isset($iid)) echo $iid ?>><? if(isset($name)) echo htmlspecialchars($name) ?></a><? } ?>
</div>
<?= $layout ?><? }else{ ?><?= $layout ?>
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
?><? module("script:jq_ui"); ?><? module("script:ajaxLink"); ?>
<style>
body{
	padding-top:20px;
}
</style>
<? module("page:style", 'admin.css') ?><? module("page:style", 'baseStyle.css') ?>
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
?><? function script_ajaxLayout($val){ module('script:jq'); ?>
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
<? } ?><? function ajax_edit(&$data)
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

<? //	Template doc_editable loaded from  _modules/_module.doc/template.doc_editable.php3 ?>
﻿<?
function doc_editable($db, $val, &$data)
{
	if ($val == 'edit') return  doc_editableEdit($db, $data);

	list($id, $name) = explode(':', $val, 2);
	if (!$name) return;

	$id		= alias2doc($id);
	$data	= $db->openID($id);

	if (!$data) return;

	$menu	= array();
	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id"."_$name");
	}
	
	beginAdmin();
	if (beginCompile($data, "editable_$name"))
	{
		$doc	= $data['fields'];
		$doc	= $doc['any'];
		echo $doc["editable_$name"];
		endCompile($data);
	}
	endAdmin($menu);
}

function doc_editableEdit($db, &$data)
{
	$id		= $data[1];
	$name	= $data[2];
	if (!$name) return;
	if (!access('write', "doc:$id")) return;
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		mEx('prepare:2local', $doc);
		$d		= array();
		$d['fields']['any']["editable_$name"]	= $doc["editable_$name"];
		$iid	= moduleEx("doc:update:$id:edit", $d);
		if ($iid){
			m("doc:clear:$id");
			redirect(getURL($db->url($id)));
		}
	}
	
	$data	= $db->openID($id);
	$folder	= $db->folder();
	$url	= "page_edit_$id"."_$name";
	
	m('page:title', "Изменить $name");
	mEx('prepare:2public', $data);
	module("editor:$folder");
?>
<form method="post" action="<? module("url:$url"); ?>" class="admin ajaxForm ajaxReload">
<? module("display:message"); ?>
<div><textarea name="doc[editable_<? if(isset($name)) echo htmlspecialchars($name) ?>]" cols="" rows="35" class="input w100 editor"><? if(isset($data["fields"]["any"]["editable_$name"])) echo htmlspecialchars($data["fields"]["any"]["editable_$name"]) ?></textarea></div>
</form>
<? } ?><? //	Template module_prop loaded from  _modules/_module.doc/_module.property/template.module_prop.php3 ?>
<?
function module_prop($fn, &$data)
{
	//	База данных пользователей
	$db	= new dbRow('prop_name_tbl', 'prop_id');
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');
	$db->dbValues= new dbRow('prop_values_tbl','values_id');

	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("prop_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function propFormat($val, &$data, $bUseFormat = true){
	if ($format = $data['format'])
		return $bUseFormat?str_replace('%', "</span>$val<span>", "<span class=\"propFormat\"><span>$format</span></span>"):str_replace('%', $val, $format);
	return $bUseFormat?"<span class=\"propFormat\">$val</span>":$val;
}

function prop_get($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	$bNoCache	= false;
	
	$docID	= makeIDS($docID);
	$ids	= explode(',', $docID);
	if (count($ids) != 1) $bNoCache = true;
	
	if ($group)	$group	= explode(',', $group);
	else $group = array();
	
	if (!$bNoCache)
	{
		$ddb	= module('doc');
		$data	= $ddb->openID($docID);
		if (!$data) return array();

		@$res	= unserialize($data['property']);
		if (is_array($res))
		{
			if (!$group) return $res;
			foreach($res as $name => &$data){
				$g = explode(',', $data['group']);
				if (!array_intersect($group, $g)) unset($res[$name]);
			}
			return $res;
		}
	}
	
	$res	= array();
	$sql	= array();
	$sql[]	= "v.`doc_id` IN ($docID)";
	$sql[':from']['prop_name_tbl']	= 'p';
	$sql[':from']['prop_value_tbl']	= 'v';
	$table2	= $db->dbValues->table();
	$sql[':join']["$table2 AS vs"]	= 'vs.`values_id` = v.`values_id`';
	$sql[]		= "p.`prop_id` = v.`prop_id`";
	$db->group	= 'p.`prop_id`';
	$db->order	= 'p.`sort`';

	$unuinSQL	= array();
	$sql['type']= "p.`valueType` = 'valueDigit'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueDigit` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);
	
	$sql['type']= "p.`valueType` = 'valueText'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueText` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);

	$union		= '(' . implode(') UNION (', $unuinSQL) .') ORDER BY `sort`';
	$db->exec($union);

	while($data = $db->next())
	{
		if ($bNoCache){
			$g = explode(',', $data['group']);
			if (!array_intersect($group, $g)) continue;
		}
		$res[$data['name']] = $data;
	}
	
	if ($bNoCache) return $res;

	$ddb->setValue($docID, 'property', $res, false);
	if (!$group) return $res;
	
	foreach($res as $name => &$data){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) unset($res[$name]);
	}
	
	return $res;
}
function prop_set($db, $docID, $data)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$docIDS	= $docID;
		$docID	= explode(',', $docID);
	}

	if (!is_array($data)) return;
	
	$a	= array();
	setCacheValue('propNames', $a);

	$ids	= array();
	$ddb	= module('doc');
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= moduleEx("prop:add:$name", $valueType);
		if (!$iid || !$docID) continue;

		$props	= array();
		$propsID= array();
		//	Все свойства документов
		$sql	= array();
		$sql[]	= "`prop_id` = $iid AND `doc_id` IN ($docIDS)";
		$db->dbValue->open($sql);
		while($d = $db->dbValue->next()){
			//	Создать массиво имеющихся свойств
			//	doc_id:value => id
			$key	= "$d[doc_id]:$d[values_id]";
			$ixd	= $db->dbValue->id();
			$props[$key]	= $ixd;
			$propsID[$ixd]	= $ixd;
		}
		//	Проверить каждое значение свойства
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			$val = trim($val);
			if (!$val){
				$db->dbValue->delete("doc_id IN ($docID) AND prop_id = `$iid`");
				$ddb->setValue($docID, 'property', NULL);
				continue;
			}
			
			if ($valueType == 'valueDigit'){
				$v = $val = (int)$val;
			}else{
				$v = $val; makeSQLValue($v);
			}
			$db->dbValues->open("`$valueType` = $v");
			$d 			= $db->dbValues->next();
			$valuesID	= $db->dbValues->id();
			if (!$valuesID || $d[$valueType] != $val)
			{
				$d2 				= array();
				$d2['id']			= $valuesID;
				$d2['valueDigit']	= (int)$val;
				$d2['valueText']	= $val;
				$valuesID = $db->dbValues->update($d2, false);
			}

			foreach($docID as $doc_id)
			{
				//	Если такое значение уже есть, не добавлять
				$key = "$doc_id:$valuesID";
				if (@$ixd = $props[$key]){
					unset($propsID[$ixd]);
				}else{
					$d				= array();
					$d['prop_id']	= $iid;
					$d['doc_id'] 	= $doc_id;
					$d['values_id']	= $valuesID;
					$ixd = $db->dbValue->update($d, false);
					$props[$key]	= $ixd;
				}
				$ids[$doc_id] = $doc_id;
			}
		}
		if ($ids){
			$ddb->setValue($ids, 'property', NULL);
		}
		if ($propsID)	$db->dbValue->delete($propsID);
	}
}

function prop_delete($db, $docID, $data)
{
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$ddb = module('doc');
	$ddb->setValue($docID, 'property', NULL);
}

function prop_add($db, $name, &$valueType)
{
	$name		= trim($name);
	@$aliases	= &$GLOBALS['_CONFIG']['propertyAliases'];
	if (!is_array($aliases)){
		$aliases = array();
		$db->open();
		while($data = $db->next()){
			$alias = explode("\r\n", $data['alias']);
			foreach($alias as $key) $aliases[strtolower($key)] = $data['name'];
		}
	}
	@$alias = trim($aliases[strtolower($name)]);
	if ($alias) $name = $alias;
	
	if (!$valueType) $valueType = 'valueText';
	$n		= $name; makeSQLValue($n);

	$db->open("name = $n");
	if ($data = $db->next()){
		$iid		= $db->id();
		$valueType	= $data['valueType'];
	}else{
		$d			= array();
		$d['name']	= $name;
		$d['valueType'] = $valueType;
		$d['group']	= $group;
		$iid		= $db->update($d, false);
	}
	
	return $iid;
}

function prop_filer(&$prop)
{
	foreach($prop as $name => &$val)
	{
		if ($name[0] != ':') continue;
		if (hasAccessRole('developer')) continue;
		unset($prop[$name]);
	}
}
function prop_value($db, $names, $data)
{
	$ret	= array();
	$names	= explode(',', $names);
	foreach($names as &$name){
		makeSQLValue($name);
	}
	
	$sql	= array();
	$tableValues	= $db->dbValues->table;
	$sql[':join']["$tableValues v"]	= "v.`values_id` = `values_id`";
	$sql[':from'][]	= ' p';

	$names = implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		$valueType	= $data['valueType'];
		$values		= explode("\r\n", $data['values']);
		foreach($values as $n){
			$n = trim($n);
			if ($n) $ret[$name][$n] = $n;
		}

		$db->dbValue->fields= "v.`$valueType` AS value";
		$db->dbValue->group	= "value";
		$db->dbValue->order	= "value";
		$sql[':where']	= "`prop_id` = $id";
		$db->dbValue->open($sql);
		while($d = $db->dbValue->next())
		{
			$n = $d['value'];
			if ($n) $ret[$name][$n] = $n;
		}
	}
	return $ret;
}
function prop_count($db, $names, &$search)
{
	$k	= hashData($search);
	$k	= "propCount:$names:$k";
	$ret	= memGet($k);
	if ($ret) return $ret;
	
	$ddb	= module('doc');
//////////////
	$key	= $ddb->key();
	$table	= $ddb->table();
	if ($search['type']	== 'product'){
		$search['price']	= '1-';
	}
	$sql	= doc2sql($search);
	$ids	= $ddb->selectKeys($key, $sql);
	if (!$ids) return array();
	$ddb->sql	=	'';
	
	$bLongQuery	= strlen($ids) > 20*1024;
///////////////
	$ret	= array();
	$union	= array();

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();

	$names	= explode(',', $names);
	foreach($names as &$name) makeSQLValue($name);
	$names	= implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		makeSQLValue($name);
		$sort	= $data['sort'];
		$sort2	= 0;

		$queryName	= $data['queryName'];
		$ev			= array(&$data['query'], array());
		if ($queryName) event("prop.query:$queryName", $ev);

		if ($query = &$ev[1])
		{
			$sql	= array();
			$fields	= "''";
			$fields2= $sort;
			foreach($query as $n => $q){
				makeSQLValue($n);
				$fields = "IF($q, $n, $fields)";
				$fields2= "IF($q, $sort2, $fields2)";
				++$sort2;
			}
			$ddb->fields= "$name AS name, $fields AS value, $sort AS sort, $fields2 AS sort2, count(*) AS cnt";
			$ddb->group	= 'value';
			
			if ($bLongQuery) $sql[] = "find_in_set(`$key`, @ids)";
			else $sql[]		= "`$key` IN ($ids)";
			
			$union[]	= $ddb->makeSQL($sql);
		}else{
			$sql	= array();
			$sql[':join']["$table2 AS pv$id"]	= "p$id.`values_id` = pv$id.`values_id`";
			$db->dbValue->group		= "pv$id.`values_id`";
			$sql[':where']	= "p$id.`prop_id`=$id";

			if ($bLongQuery) $sql[] = "find_in_set(`$key`, @ids)";
			else $sql[]		= "`$key` IN ($ids)";
			
			$sql[':from'][]	= "p$id";
			
			$db->dbValue->fields	= "$name AS name, pv$id.`$data[valueType]` AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
			$union[]	= $db->dbValue->makeSQL($sql);
		}
	}

	if ($bLongQuery) $ddb->exec("SET @ids = '$ids'");
	$union	= '(' . implode(') UNION (', $union) . ') ORDER BY `sort`, `sort2`, `name`, `value`';
	$ddb->exec($union);
	while($data = $ddb->next()){
		$count	= $data['cnt'];
		if ($count) $ret[$data['name']][$data['value']] = $count;
	}
	
	memSet($k, $ret);
	return $ret;
}
function prop_name($db, $group, $data)
{
	$db->order	= '`name`';
	$group		= explode(',', $group);
	$ret		= array();
	$db->open();
	while($data = $db->next()){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) continue;
		$ret[$data['name']] = $data;
	}
	return $ret;
}
function prop_clear($db, $id, $data)
{
	if ($id){
		$ids		= makeIDS($id);
		$ddb		= module('doc');
		$table		= $db->dbValue->table();
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable AS d INNER JOIN $table AS p ON d.`doc_id` = p.`doc_id` SET `property` = NULL  WHERE p.`prop_id` IN ($ids)";
		$ddb->exec($sql);
	}else{
		$ddb		= module('doc');
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable SET `property` = NULL";
//		$ddb->exec($sql);
	}

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql	= "DELETE vs FROM $table2 AS vs WHERE `values_id` NOT IN (SELECT `values_id` FROM $table)";
	$db->exec($sql);
	
	$dbDoc		= module('doc');
	$docTable	= $dbDoc->table();
	$sql		= "DELETE v FROM $table AS v WHERE `doc_id` NOT IN (SELECT doc_id FROM $docTable)";
	$db->exec($sql);

	memClear();
}
function prop_addQuery($db, $query, $queryName)
{
	$q	= getCacheValue('propertyQuery');
	if (!is_array($q)) $q = array();
	$q[$query] = $queryName;
	setCacheValue('propertyQuery', $q);
}
function prop_tools($db, $val, &$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;
	$data['Все ствойства документов#ajax']	= getURL('property_all');
}
?><? //	Template module_prop_sql loaded from  _modules/_module.doc/_module.property/template.module_prop_sql.php3 ?>
<?
function module_prop_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	//	Найти по родителю
	if (@$val = $search['parent']){
		$search['prop'][':parent'] = alias2doc($val);
	}

	//	Со всеми додкаталогами
	if (@$val = $search['parent*'])
	{
		@list($id, $type) = explode(':', $val);
		$id = alias2doc($id);
		if ($id){
			$db	= module('doc');
			
			if (!is_array($id)) $id = explode(',', makeIDS($id));
			$s	= array();
			$ids= $id;
			while(true){
				$s['prop'][':parent'] = $ids;
				if ($type) $s['type'] = $type;
				$ids = $db->selectKeys('doc_id', doc2sql($s));
				if (!$ids) break;
				$ids = array_diff(explode(',', $ids), $id);
				if (!$ids) break;
				$id = array_merge($id, $ids);
			};
			$search['prop'][':parent'] = implode(', ', $id);
		}else $sql[] = 'false';
	}
	if (isset($search['prop'][':parent']) && !is_array($search['prop'][':parent'])){
		$search['prop'][':parent'] = explode(',', makeIDS($search['prop'][':parent']));
	}

	//	Найти по свойствам
	@$val = $search['prop'];
	if ($val && is_array($val))
	{
		$bHasSQL	= false;
		//	База данных
		$db			= module('prop');
		//	Все условия свойств
		$thisSQL	= array();
		//	Кеш запросов
		$cacheProps	= getCacheValue('propNameCache');
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();

		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;
			
			$property	= &$cacheProps[$propertyName];
			if (!isset($property))
			{
				$name = $propertyName;
				makeSQLValue($name);
				$db->open("`name` = $name");
				if ($data = $db->next())
				{
					$queryName	= $data['queryName'];
					$ev			= array(&$data['query'], array());
					if ($queryName) event("prop.query:$queryName", $ev);
					$property	= array($db->id(), $data['name'], $data['valueType'], $ev[1]);
					setCacheValue('propNameCache', $cacheProps);
				}else{
					$property 	= array();
					$thisSQL	= array();
					break;
				}
			}
			
			list($id, $name, $valueType, $query) = $property;
			if ($query){
				foreach($values as &$value){
					$q		= $query[$value];
					$sql[]	= $q?$q:'false';
					$bHasSQL= true;
				}
			}else
			switch($valueType)
			{
				case 'valueDigit':
					foreach($values as &$value) $value = (int)$value;
					$values			= implode(',', $values);
					$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
				break;
				case 'valueText':
					foreach($values as &$value){
						if (!is_string($value)) $value = "$value";
						makeSQLValue($value);
					}
					$values			= implode(',', $values);
					$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
				break;
			}
		}

		if ($thisSQL || $bHasSQL){
			foreach($thisSQL as $id => &$s){
				$sql[] 								= $s;
				$sql[':join']["$table AS a$id"]		= "`doc_id` = a$id.`doc_id`";
				$sql[':join']["$table2 AS vs$id"]	= "vs$id.`values_id` = a$id.`values_id`";
			}
		}else $sql[] = 'false';
	}
}
?><? //	Template prop_read loaded from  _modules/_module.doc/_module.property/template.prop_read.php3 ?>
<?
function prop_read($db, $fn, $data)
{
	$props = module("prop:get:$data[id]:$data[group]");
	if (!$props) return;
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("prop_read_$fn");
	if ($fn) return $fn($props, $val);

	$split = '<ul>';
	foreach($props as $name => $data)
	{
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		echo $split; $split = '';
		$note	= htmlspecialchars($data['note']);
		$name	= htmlspecialchars($name);
		$prop	= htmlspecialchars($data['property']);
		
		if ($prop){
			$prop	= propFormat($prop, $data, true);
			echo "<li title=\"$note\">$name: <b>$prop</b></li>";
		}else{
			echo "<li title=\"$note\">$name</li>";
		}
	}
	if (!$split) echo '</ul>';
}

function prop_read_plain(&$props)
{
	$split = '';
	foreach($props as $name => $data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		$prop	= htmlspecialchars($data['property']);
		if (!$prop) continue;
		$prop	= propFormat($prop, $data, true);
		echo $split, $prop;
		$split = ', ';
	}
}

function prop_read_table(&$props, $cols)
{
	$cols = (int)$cols;
	if ($cols < 1) $cols = 1;
	
	$p = array();
	foreach($props as $name => &$data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;
		$p[] = $data;
	}
	$width	= floor(100/$cols);
	$rows	= floor(count($props) / $cols);
?>
<table border="0" cellspacing="0" cellpadding="0" class="read property">
<? for($row = 0; $row < $rows; ++$row){
	$class = $row%2?' class="alt"':'';
?>
<tr<?= $class?>>
<? for($col = 0; $col < $cols; ++$col){
	$ix		= ($row*$cols)+$col;
	$now	= $p[$ix];
	$class	= $col?'':' id="first"';
?><? if ($col){ ?>
    <td class="split">&nbsp;</td>
<? } ?>
    <th <? if(isset($class)) echo $class ?>><? if(isset($now["name"])) echo htmlspecialchars($now["name"]) ?></th>
    <td <? if(isset($class)) echo $class ?>><? if(isset($now["property"])) echo htmlspecialchars($now["property"]) ?></td>
<? } ?>
</tr>
<? } ?>
</table>
<? } ?><? //	Template doc_page_catalog loaded from  _sites/dt-ekb.ru/_modules/_catalog/template.doc_page_catalog.php3 ?>
<?
function doc_page_catalog($db, &$menu, &$data)
{
	$id		= $db->id();
	
	$ddb	= module('doc');
	$d		= $ddb->openID(currentPageRoot());
	if ($d['template']=='operator' && !testValue('ajax'))	setTemplate('catalog');

?><? beginAdmin() ?><? document($data) ?><? endAdmin($menu, true) ?><? module("doc:read:catalog", array('parent' => $id, 'type' => 'product')) ?><? } ?><? //	Template doc_page_default loaded from  _sites/dt-ekb.ru/_modules/_catalog/template.doc_page_default.php3 ?>
<? function doc_page_default(&$db, &$menu, &$data)
{
	$id		= $db->id();

	$ddb	= module('doc');
	$root	= currentPageRoot();
	$rootData	= $ddb->openID($root);
	if (!testValue('ajax') && @$rootData['template'] == 'operator') setTemplate('catalog');
?><? beginAdmin() ?><? document($data) ?><? endAdmin($menu, true) ?><? event('document.gallery',	$id)?><? event('document.feedback',	$id)?><? } ?><? //	Template doc_page_page_index loaded from  _modules/_module.doc/_pages/_pages/template.doc_page_page_index.php3 ?>
<? function doc_page_page_index($db, $val, $data){
	if (!testValue('ajax')) setTemplate('index'); 
} ?>
<? //	Template doc_page_product loaded from  _sites/dt-ekb.ru/_modules/_product/template.doc_page_product.php3 ?>
<?
function doc_page_product(&$db, &$menu, &$data)
{
	$id		= $db->id();
	$parents= getPageParents($id);
	if ($parents){
		return module('doc:page:url', array(0, $parents[count($parents)-1]));
	}

	@$fields= $data['fields'];
	@$any	= $fields['any'];
	$menu	= doc_menu($id, $data, false);
	$prop	= module("prop:get:$id");
	
	$p = array();
	if (@$any['date'])			$p['Вылет:']			= $any['date'];
	if (@$prop['Длительность'])	$p['Продолжительность:']= $prop['Длительность']['property'];
	if (@$prop['Отель']){
		$hotel	= $prop['Отель']['property'];
		@$url	= $fields['any'];
		@$url	= $url['hotelURL'];
		if ($url){
			$p['Проживание в отлеле:']= "<a href=\"$url\" target=\"_new\">$hotel</a>";
		}else{
			$p['Проживание в отлеле:']= $hotel;
		}
	}
	if (@$prop['Питание'])		$p['Питание:']			= $prop['Питание']['property'];

	m('script:lightbox');
	
	$price			= array();
	$price['тур:']	= priceNumber(docPrice($data)) . ' руб.';

	$price2	= $prop['Цена (топливо)'];
	if ($price2 && $price2['property'])	$price['топливо:']	= priceNumber($price2['property']) . ' руб.';

	$price2	= $prop['Цена (виза)'];
	if ($price2 && $price2['property'])	$price['виза:']		= priceNumber($price2['property']) . ' руб.';
	if (!testValue('ajax'))	setTemplate('catalog');
?><? beginAdmin() ?>
<div class="product page">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th width="250" valign="top">
<?  if (beginCompile($data, "productPageImage")){ ?><? displayThumbImage($title = docTitleImage($id), array(450, 350), ' class="thumb"', '', $title) ?><? $module_data = array(); $module_data["src"] = "$folder/Gallery"; moduleEx("gallery:small", $module_data); ?><?  endCompile($data, "productPageImage"); } ?>
    </th>
    <td width="100%" valign="top">
    <h3 class="title"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h3>
    <h4>
<? $split = ''; foreach($price as $name => $val){ ?><? if(isset($split)) echo htmlspecialchars($split) ?><? if(isset($name)) echo htmlspecialchars($name) ?> <span class="price"><? if(isset($val)) echo $val ?></span><? $split = ' + '; } ?>
    </h4>
<div class="property">
<? foreach($p as $name => &$val){ ?>
<div><? if(isset($name)) echo htmlspecialchars($name) ?> <b><? if(isset($val)) echo $val ?></b></div>
<? } ?>
</div>
    </td>
</tr>
</table>
<p><? document($data) ?></p>
</div>
<? endAdmin($menu, true) ?><? } ?><? //	Template doc_read_catalog loaded from  _sites/dt-ekb.ru/_modules/_product/template.doc_read_catalog.php3 ?>
<?
function doc_read_catalog_beginCache(&$db, $val, &$search){
	if (userID()) return;
	m('script:lightbox');
	m('gallery:small', array(0));
	return hashData($search);
}
function doc_read_catalog(&$db, $val, &$search)
{
	m('script:lightbox');
	m('gallery:small', array(0));
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="product">
<? while($data = $db->next())
{
	$id		= $db->id();
	$folder	= $db->folder();
	@$fields= $data['fields'];
	@$any	= $fields['any'];
	$menu	= doc_menu($id, $data, false);
	$price	= priceNumber(docPrice($data));
	$prop	= module("prop:get:$id");
	
	$p = array();
	if (@$any['date'])			$p['Вылет:']			= $any['date'];
	if (@$prop['Город вылета'])	$p['из ']= $prop['Город вылета']['property'];
	if (@$prop['Длительность'])	$p['Продолжительность:']= $prop['Длительность']['property'];
	$p[':1'] = '<div></div>';

	if (@$prop['Отель']){
		$hotel	= $prop['Отель']['property'];
		@$url	= $fields['any'];
		@$url	= $url['hotelURL'];
		if ($url){
			$p['Проживание в отеле:']= "<a href=\"$url\" target=\"_new\">$hotel</a>";
		}else{
			$p['Проживание в отеле:']= $hotel;
		}
	}else{
		$hotel = '';
	}
	if (@$prop['Питание'])		$p['Питание:']			= $prop['Питание']['property'];
	$p[':2'] = '<div></div>';

	$price			= array();
	$price['тур:']	= priceNumber(docPrice($data)) . ' <small>руб.</small>';

	$price2	= $prop['Цена (топливо)'];
	if ($price2)	$price['топливо:']	= priceNumber($price2['property']) . ' <small>руб.</small>';

	$price2	= $prop['Цена (виза)'];
	if ($price2)	$price['виза:']		= priceNumber($price2['property']) . ' <small>руб.</small>';

	$price2	= $prop['Скидка (%)'];
	if ($price2)	$sales = "<div class=\"sales\"><span>$price2[property]%</span></div>";
	else $sales = '';
	
	$p2		= array();
	if (@$prop['Страна'])		$p2['Страна:']	= $prop['Страна']['property'];
		if (@$prop['Курорт'])	$p2['Курорт:']	= $place = $prop['Курорт']['property'];
		else $place = '';

	$url		= getURL($db->url());
	$imageTitle	= "$data[title] $place $hotel";
?>
<tr>
    <th>
<?  if (beginCompile($data, "catalogThumb2")){ ?>
<a href="<?= htmlspecialchars($title = docTitleImage($id))?>" rel="lightbox[image<? if(isset($id)) echo htmlspecialchars($id) ?>]" title="<? if(isset($imageTitle)) echo htmlspecialchars($imageTitle) ?>"><? displayThumbImage($title, array(250, 200)) ?></a>
<? if(isset($sales)) echo $sales ?>
<div><? module('gallery:small', array('title'=>$imageTitle, 'id'=>"image$id", 'src'=>array("$folder/Title", "$folder/Gallery"))) ?></div>
<?  endCompile($data, "catalogThumb2"); } ?>
    </th>
    <td width="100%">
<? beginAdmin() ?>
    <div class="title dHowH3"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></div>
<div class="property"><big>
<? foreach($p2 as $name => &$val){
?><? if(isset($name)) echo htmlspecialchars($name) ?> <span class="bold"><? if(isset($val)) echo $val ?></span>
<? } ?>
</big></div>
    <div class="dHowH4"><? $split = ''; foreach($price as $name => $val){ ?><? if(isset($split)) echo htmlspecialchars($split) ?><? if(isset($name)) echo htmlspecialchars($name) ?> <span class="price"><? if(isset($val)) echo $val ?></span><? $split = ' + '; } ?></div>
<div class="property">
<? foreach($p as $name => &$val){
	if ($name[0] == ':'){
		echo $val; continue;
	}
?><? if(isset($name)) echo htmlspecialchars($name) ?> <span class="bold"><? if(isset($val)) echo $val ?></span>
<? } ?>
</div>
<? document($data) ?><? endAdmin($menu) ?>
    </td>
</tr>
<tr>
  <td colspan="2">&nbsp;</td>
  </tr>
<? } ?>
</table>
<div class="bottom_text"></div>
<? return $search; } ?>
<? //	Template doc_read_default loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_default.php3 ?>
<?
function doc_read_default(&$db, $val, &$search){
	if (!$db->rows())  return $search;
?><? while($data = $db->next()){
	$fn		= getFn("doc_read_$data[doc_type]_$data[template]");
	if ($fn){
		$fn($db, $val, $search);
		continue;
	}
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?><? beginAdmin() ?>
<div><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></div>
<? endAdmin($menu, true) ?><? } ?><? return $search; } ?>
<? //	Template doc_read_news loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news.php3 ?>
<?
function doc_read_news_beginCache(&$db, $val, &$search)
{
	if (userID()) return;
	return hashData($search);
}

function doc_read_news(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?><? while($data = $db->next()){
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
<? beginAdmin() ?><? if(isset($date)) echo $date ?><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
<? endAdmin($menu, true) ?>
</p>
<? } ?><? return $search; } ?>
<? //	Template doc_read_news2 loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news2.php3 ?>
<?
function doc_read_news2_beginCache(&$db, $val, &$search)
{
	if (userID()) return;
	return hashData($search);
}
function doc_read_news2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?><? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= docNote($data);
?><? beginAdmin() ?>
<h3><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h3>
<p><? if(isset($note)) echo $note ?></p>
<? endAdmin($menu, true) ?><? } ?><? return $search; } ?>
<? //	Template doc_read_news3 loaded from  _modules/_module.doc/_pages/_reads/template.doc_read_news3.php3 ?>
<?
function doc_read_news3_beginCache(&$db, $val, &$search){
	if (userID()) return;
	return hashData($search);
}
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
<? beginAdmin() ?><?  if (beginCompile($data, "news3")){ ?>
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
<? //	Template doc_menuTools loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_menuTools.php3 ?>
﻿<?
function menuBeginCache($name, $search)
{
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}
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
	$fields= $data['fields'];
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
	<li<?= $class?>>
    	<a href="<? module("getURL:$url")?>"<?= $draggable?> title="<?= htmlspecialchars($data['title'])?>">
        <span><?= htmlspecialchars($data['title'])?></span>
		<?= $note?>
        </a>
        <?= $p?>
	</li>
<? } ?>
</ul>
<?
	return $search;
}

function showDocMenuDeepEx($db2, &$tree)
{
	if (!$tree) return;
	
	$bFirst		= true;
	$bCurrent	= false;
	echo '<ul>';
	foreach($tree as $id => &$childs)
	{
		$data	= $db2->openID($id);
		$url	= getURL($db2->url($id));
		$fields= $data['fields'];
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
}?><? //	Template doc_read_menu loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menu.php3 ?>
<?
function doc_read_menu(&$db, $val, &$search){
	return showDocMenuDeep($db, $search,  0);
}
function doc_read_menu_beginCache(&$db, $val, &$search)	{
	m('doc:menuTools');
	return menuBeginCache(1, $search);
}
?><? //	Template doc_read_menu2 loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menu2.php3 ?>
<?
function doc_read_menu2(&$db, $val, &$search){
	return showDocMenuDeep($db, $search, 1);
}
function doc_read_menu2_beginCache(&$db, $val, &$search){
	m('doc:menuTools');
	return menuBeginCache(2, $search);
}
?><? //	Template doc_read_menu3 loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menu3.php3 ?>
<?
function doc_read_menu3(&$db, $val, &$search){
	return showDocMenuDeep($db, $search, 2);
}
function doc_read_menu3_beginCache(&$db, $val, &$search){
	m('doc:menuTools');
	return menuBeginCache(3, $search);
}
?><? //	Template doc_read_menuEx loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menuEx.php3 ?>
<?
function doc_read_menuEx_beginCache($db, $val, $search)
{
	if (userID()) return;
	m('script:menuEx');
	return hashData($search);
}
function doc_read_menuEx($db, $val, $search)
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
<?  } ?><? function script_menuEx($val){ ?>
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
<? } ?><? function showMenuEx(&$db, &$tree, $title, $bDrop)
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
function doc_read_menuLink_beginCache(&$db, $val, &$search){
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}

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
<? $split = ''; } ?><? return $search; } ?><? //	Template doc_read_menuTable loaded from  _modules/_module.doc/_pages/_reads/_menu/template.doc_read_menuTable.php3 ?>
<?
function doc_read_menuTable_beginCache(&$db, $val, &$search)
{
	module('script:menu');
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}
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
	
	if (!$group) $group = 'productSearch';

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= $search;
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';

//	$s['price']		= '1-';
	@$s['url'] 		= $s['prop']?array('search' => $s['prop']):'';
	
	$s[':order']	= $search['prop'][':order'];
	$s['prop'][':order'] = '';
	unset($s['prop'][':order']);

	$s[':pages']	= $search['prop'][':pages'];
	$s['prop'][':pages'] = '';
	unset($s['prop'][':pages']);
	
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
?><span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($val)) echo $val ?></a></span> <? } ?><? if ($s1){ ?><a href="<? module("getURL:page$id"); ?>" class="clear">очистить</a><? } ?>
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
<span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a><sup><? if(isset($count)) echo htmlspecialchars($count) ?></sup></span>
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
	@$s['url'] 		= $s['prop']?array('search' => $s['prop']):'';
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
foreach($prop as $name => &$property){
	$totalCount += count($property) + 2;
}

foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
<div class="panel">
<h3 title="<? if(isset($note)) echo htmlspecialchars($note) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?>:</h3>
<div>
<?
$chars	= 0;
foreach($property as $pName => $count){
	$chars	+= strlen($pName) + 5;
}
$nColumns	= floor($chars?$chars/30:1);
$nColumns	= max(1, $nColumns);
$rowLimit	= 0;
$rowLimit = 20;

$ix			= 1;
$close		= '';
foreach($property as $pName => $count)
{
	if ($ix++ == $rowLimit){
		echo '<div class="expand">';
		$close	= '</div>';
	}
	
	$s1					= $search;
	$s1['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL("page$id", makeQueryString($s1['prop'], 'search'));
?>
<span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a><sup><? if(isset($count)) echo htmlspecialchars($count) ?></sup></span>
<? }//	each prperty ?><? if(isset($close)) echo $close ?>
</div>
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
	if (!$type) $type	= $data[1];
	if (!$type) $type	= 'product';

	//	Проверить на наличие такого типа данных
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes["$type:"])) $type = '';

	//	Пробуем получить шаблон из данных
	if (!$template) $template	= $data[2];

	//	Сделаем ссылку
	if ($type){
		$searchURL	= "search_$type";
		if ($template) $searchURL .= "$template";
	}else{
		$searchURL	= "search";
	}
	if (!$template){
		switch($type){
		case 'product':	$template = 'catalog'; break;
		case 'article':	$template = 'news'; break;
		default: 		$template = 'catalog'; break;
		}
	}

	//	Получить данные для поиска
	$search = getValue('search');
	//	Сохранить поиск по имени
	$name	= $search['name'];
	//	Удалить возможные посторонние параетры
/*
	$order	= $search[':order'];
	if (isset($search['prop'])){
		//	Сохранить поиск по свойствам
		$search = array('prop' => $search['prop']);
	}else{
		//	Обнулить поиск
		$search = array();
	}
	if ($order) $search[':order'] = $order;
*/
	//	Если был поиск по имени, восстановить
	if ($name) $search['name'] = $name;
	//	Кешировать поиск без данных
	if (!$search && !beginCache($cache = "pageSearchCache")) return;
	
	$s			= $search;
	$s['type']	= $type;

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
	$sProp		= $search['prop'];
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
<? } ?><? if ($selected){ ?><a href="<? module("getURL:$searchURL"); ?>" class="clear">очистить</a><? } ?>
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
    <span><a href="<? if(isset($url)) echo $url ?>"><? if(isset($nameFormat)) echo $nameFormat ?></a><sup><? if(isset($count)) echo htmlspecialchars($count) ?></sup></span>
<? } ?><?
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
<? }else{ ?><? module("display:sort"); ?><? if(isset($p)) echo $p ?><? } ?><? } ?><? if (!$search) endCache($cache); ?><? } ?>
<? //	Template gallery_default loaded from  _modules/_module.gallery/template.gallery_default.php3 ?>
<?
function gallery_default($val, &$data)
{
	$f	= getFiles($data['src']);
	if (!$f) return;

	//	Получить параметры
	$mask	= $data['mask'];
	if (!$mask){
		$size	= $data['size'];
		if (!$size) $size = array(150, 150);
	}
	
	$id	= $data['id'];
	if ($id) $id = '[$id]';

	//	Отсортировать по соотношению сторон	
	$f2	= array();
	foreach($f as $name => $path){
		list($w, $h) = getimagesize($path);
		$f2[100*$h/$w][]	= $path;
	}
	ksort($f2);
	
	//	Создать массив изображений
	$files	= array();
	foreach($f2 as &$val){
		foreach($val as $path) $files[] = $path;
	}

	//	Создать табличку
	$row = 0; $cols = 4;
	for($ix = 0; $ix < count($files); ++$row){
		for($iix = 0; $iix < $cols; ++$iix){
			$path			= '';
			@list(,$path)	= each($files); ++$ix;
			$table[$row][]	= $path;
		}
	}
	$class = ' id="first"';
?><? module("page:style", 'gallery.css') ?>
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr <? if(isset($class)) echo $class ?>>
<? $class2 = ' id="first"'; foreach($row as $path){?>
    <td <? if(isset($class2)) echo $class2 ?>><a href="<? if(isset($path)) echo htmlspecialchars($path) ?>" rel="lightbox<? if(isset($id)) echo htmlspecialchars($id) ?>"><? $mask?displayThumbImageMask($path, $mask):displayThumbImage($path, $size)?></a></td>
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
?><? module("page:style", 'gallerySmall.css') ?>
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
?><? module("page:style", 'gallerySmall.css') ?>
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
?><? module("script:ajaxLink"); ?><? if (!defined('userID')){ ?>
<form method="post" action="<? module("getURL:user_login"); ?>" class="form login">
<div style="width:230px">
<table border="0" cellspacing="0" cellpadding="2" width="100%" class="loginInput">
    <tr>
        <th nowrap="nowrap">Логин:</th>
        <td width="100%"><input name="login[login]" value="<? if(isset($login["login"])) echo htmlspecialchars($login["login"]) ?>" type="text" class="input w100" /></td>
    </tr>
    <tr>
        <th nowrap="nowrap">Пароль:</th>
        <td width="100%"><input name="login[passw]" type="password" value="<? if(isset($login["passw"])) echo htmlspecialchars($login["passw"]) ?>" class="input password w100" /></td>
    </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="loginOptions">
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
<? } ?><? if (!$val){ ?><div><? module("loginza:enter"); ?></div><? } ?><? if ($val){ ?><div><a href="<? module("getURL:user_lost"); ?>" id="ajax">Напомнить пароль?</a></div><? } ?>
  	</td>
    <td align="right" valign="top"><input type="submit" value="OK" class="button" /></td>
  </tr>
</table>
</div>
</form>
<? }else{ ?>
<div class="form">
<a href="<? $module_data = array(); $module_data[] = "logout"; moduleEx("getURL", $module_data); ?>">Выход</a>
</div>
<? } ?><? } ?><? //	Template feedback_display loaded from  _modules/_nodule.feedback/template.feedback_display.php3 ?>
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
?><? module("page:style", 'feedback/feedback.css') ?>
<div class="<? if(isset($class)) echo htmlspecialchars($class) ?>">
<form action="<? if(isset($url)) echo $url ?>" method="post" enctype="multipart/form-data" id="<? if(isset($formName)) echo htmlspecialchars($formName) ?>">
<? if ($title2){ ?><h2><? if(isset($title2)) echo htmlspecialchars($title2) ?></h2><? } ?><? module("display:message"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? foreach($form as $name => $data){ ?><?
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
?><? switch($type){ ?><? case 'hidden': ?><? break; ?><? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
</tr>
<tr>
  <th colspan="2"><? feedbackTextArea($fieldName, $thisValue, $values)?></th>
</tr>
<? break; ?><? case 'phone':	//	text field?>
<tr>
    <th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackPhone($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?><? case 'radio':	//	radio field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackRadio($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?><? case 'checkbox':	//	checkbox field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?><? case 'select':	//	select field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackSelect($fieldName, $thisValue, $values)?> </td>
</tr>
<? break; ?><? case 'passport':	//	select field?>
<tr>
    <th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackPassport($fieldName, $thisValue, $values)?> </td>
</tr>
<? break; ?><? default:	//	text field?>
<tr>
    <th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th>
    <td><? feedbackText($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?><? }//	switch ?><? }//	foreach ?>
</table>
<p><input type="submit" value="<? if(isset($buttonName)) echo htmlspecialchars($buttonName) ?>" class="button" /></p>
</form>
</div>
<?  endAdmin($menu); } ?><? function feedbackSelect(&$fieldName, &$thisValue, &$values){ ?>
<select name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" class="input w100">
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' selected="selected"':'';
?>
	<option value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?>><? if(isset($value)) echo htmlspecialchars($value) ?></option>
<? } ?>
</select>
<? } ?><? function feedbackCheckbox(&$fieldName, &$thisValue, &$values){ ?><?
if (!is_array($thisValue)) $thisValue = explode(',', $thisValue);
$thisValue = array_values($thisValue);

foreach($values as $name => $value){
	$class = $value && is_int(array_search($value, $thisValue))?' checked="checked"':'';
?>
    <div><label><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[<? if(isset($value)) echo htmlspecialchars($value) ?>]" type="checkbox" value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?> /> <? if(isset($value)) echo htmlspecialchars($value) ?></label></div>
<? } ?><? } ?><? function feedbackRadio(&$fieldName, &$thisValue, &$values){ ?><? foreach($values as $name => $value){
	$class = $thisValue == $value?' checked="checked"':'';
?>
    <div><label><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="radio" value="<? if(isset($value)) echo htmlspecialchars($value) ?>"<? if(isset($class)) echo $class ?> /> <? if(isset($value)) echo htmlspecialchars($value) ?></label></div>
<? } ?><? } ?><? function feedbackText(&$fieldName, &$thisValue, &$values){ ?>
<input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="text" class="input w100" value="<? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?>" />
<? } ?><? function feedbackTextArea(&$fieldName, &$thisValue, &$values){ ?>
<textarea name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" rows="5" class="input w100"><? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?></textarea>
<? } ?><? function feedbackPhone(&$fieldName, &$thisValue, &$values, $nStyle = ''){ 	module('script:maskInput') ?>
<input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>" type="text" class="input w100 phone" value="<? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?>" />
<? } ?><? function feedbackPassport(&$fieldName, &$thisValue, &$values, $style = ''){
	switch($style){
?><? case 'vertical': ?>
<style>
.feedback .passport td{
	width:auto;
}
</style>
<table width="100%" cellpadding="2" cellspacing="0" class="passport">
<tr>
    <td nowrap="nowrap"><label for="f1">Серия:</label></td><td width="100%"><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f1]" id="f1" type="text" class="input w100" value="<? if(isset($thisValue["f1"])) echo htmlspecialchars($thisValue["f1"]) ?>" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f2">Номер:</label></td><td><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f2]" id="f2" type="text" class="input w100" value="<? if(isset($thisValue["f2"])) echo htmlspecialchars($thisValue["f2"]) ?>" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f3">Кем выдан:</label></td><td><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f3]" id="f3" type="text" class="input w100" value="<? if(isset($thisValue["f3"])) echo htmlspecialchars($thisValue["f3"]) ?>" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f4">Дата выдачи:</label></td><td><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f4]" id="f4" type="text" class="input w100" value="<? if(isset($thisValue["f4"])) echo htmlspecialchars($thisValue["f4"]) ?>" /></td>
</tr>
</table>
<? break; ?><? default: ?>
<table width="100%" cellpadding="2" cellspacing="0" class="passport">
<tr>
    <td><label for="f1">Серия:</label></td><td width="25%"><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f1]" id="f1" type="text" class="input w100" value="<? if(isset($thisValue["f1"])) echo htmlspecialchars($thisValue["f1"]) ?>" /></td>
    <td><label for="f2">Номер:</label></td><td width="25%"><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f2]" id="f2" type="text" class="input w100" value="<? if(isset($thisValue["f2"])) echo htmlspecialchars($thisValue["f2"]) ?>" /></td>
    <td><label for="f3">Кем выдан:</label></td><td width="25%"><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f3]" id="f3" type="text" class="input w100" value="<? if(isset($thisValue["f3"])) echo htmlspecialchars($thisValue["f3"]) ?>" /></td>
    <td><label for="f4">Дата выдачи:</label></td><td width="25%"><input name="<? if(isset($fieldName)) echo htmlspecialchars($fieldName) ?>[f4]" id="f4" type="text" class="input w100" value="<? if(isset($thisValue["f4"])) echo htmlspecialchars($thisValue["f4"]) ?>" /></td>
</tr>
</table>
<? }//	swith ?><? } ?>
<? //	Template feedback_display_vertical loaded from  _modules/_nodule.feedback/template.feedback_display_vertical.php3 ?>
<?
function feedback_display_vertical(&$formName, &$form)
{
	$formData = getValue($formName);
	if (feedbackSend($formName, $formData, $form))
		return module('display:message');

	$class		= $form[':']['class'];
	$url		= $form[':']['url'];
	$buttonName	= $form[':']['button'];
	@$titleForm	= $form[':']['titleForm'];
	
	$style		= 'vertical';
?><? module("page:style", 'feedback/feedback.css') ?>
<div class="<? if(isset($class)) echo htmlspecialchars($class) ?> vertical">
<form action="<? if(isset($url)) echo $url ?>" method="post" enctype="multipart/form-data" id="<? if(isset($formName)) echo htmlspecialchars($formName) ?>">
<? module("display:message"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? if ($titleForm){ ?>
<tr><th><h2><? if(isset($titleForm)) echo htmlspecialchars($titleForm) ?></h2></th></tr>
<? } ?><? foreach($form as $name => $data){ ?><?
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
?><? switch($type){ ?><? case 'hidden': break; ?><? default:	//	text field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackText($fieldName, $thisValue, $values, $style)?></td></tr>
<? break; ?><? case 'textarea':	//	textarea field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><th><? feedbackTextArea($fieldName, $thisValue, $values, $style)?></th></tr>
<? break; ?><? case 'phone':	//	text field?>
<tr><th><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackPhone($fieldName, $thisValue, $values, $style)?></td></tr>
<? break; ?><? case 'radio':	//	radio field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackRadio($fieldName, $thisValue, $values, $style)?></td></tr>
<? break; ?><? case 'checkbox':	//	checkbox field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackCheckbox($fieldName, $thisValue, $values, $style)?></td></tr>
<? break; ?><? case 'select':	//	select field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackSelect($fieldName, $thisValue, $values, $style)?> </td></tr>
<? break; ?><? case 'passport':	//	checkbox field?>
<tr><th valign="top"><? if(isset($name)) echo $name ?><? if(isset($note)) echo $note ?></th></tr>
<tr><td><? feedbackPassport($fieldName, $thisValue, $values, $style)?></td></tr>
<? break; ?><? }//	switch ?><? }//	foreach ?>
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
	$types['Паспорт'] 			= 'passport';
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
		
		$values		= explode(',', $data[$type]);
		$thisValue	= $formData[$thisField];

		$bMustBe		= $data['mustBe'] != '';
		$mustBe			= explode('|', $data['mustBe']);
		$bValuePresent	= trim($thisValue) != '';
		
		foreach($mustBe as $orField){
			$bValuePresent |= trim($formData[$orField]) != '';
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
		case 'passport':
			if (!$bMustBe) break;
			if (!is_array($thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";

			foreach($thisValue as &$f) $f = trim($f);
			
			if (!$thisValue['f1'] ||
				!$thisValue['f2'] ||
				!$thisValue['f3'] ||
				!$thisValue['f4'])
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
		case 'passport':
			if (!is_array($thisValue)) continue;
			$mail		.= "$name: \r\n";
			$mail		.= "Серия $thisValue[f1]\r\n";
			$mail		.= "Номер $thisValue[f2]\r\n";
			$mail		.= "Кем выдан $thisValue[f3]\r\n";
			$mail		.= "Дата выдачи $thisValue[f4]\r\n";
			$mail		.= "\r\n";
			foreach($thisValue as &$f) $f = htmlspecialchars($f);
			$mailHtml	.= "<p><b>$name:</b><br />";
			$mailHtml	.= "Серия $thisValue[f1]<br />";
			$mailHtml	.= "Номер $thisValue[f2]<br />";
			$mailHtml	.= "Кем выдан $thisValue[f3]<br />";
			$mailHtml	.= "Дата выдачи $thisValue[f4]";
			$mailHtml	.= "</p>";
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
function module_feedback_access($access, &$data){
	return hasAccessRole('admin,developer,writer');
}
function feedback_tools($val, &$data){
	if (!access('write', 'feedback:')) return;
	$data['Формы обратной связи#ajax']	= getURL('feedback_all');
}
?><? //	Template module_operator loaded from  _sites/dt-ekb.ru/_modules/_managers/template.module_operator.php3 ?>
<? function module_operator($fn, &$data)
{
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("operator_$fn");
	return $fn?$fn($val, $data):NULL;
}
?><? //	Template operator_access loaded from  _sites/dt-ekb.ru/_modules/_managers/template.operator_access.php3 ?>
<? function operator_access($access, &$ev)
{
	$user	= userData();
	if (!$user) retrun;
	
	$operatorID	= $user['operator_id'];
	if (!$operatorID) return;
	if (!hasAccessRole('operator')) return;
	
	$id		= $ev[1];
	if (!$id) return true;

	$db		= module('doc');
	$data	= $db->openID($id);
	$parents	= getPageParents($id);
	$hasAccess	= is_int(array_search($operatorID, $parents));
		
	switch($access){
	case 'delete':
	case 'write':
		return $hasAccess && $data['doc_type'] == 'product';
	case 'add':
		return $hasAccess && $data['doc_type'] == 'catalog' && $ev[2] == 'product';
	}
}?><? //	Template operator_contacts loaded from  _sites/dt-ekb.ru/_modules/_managers/template.operator_contacts.php3 ?>
<? function operator_contacts($val, $data){ ?><? module("page:style", 'managers.css') ?>
<h3>Офисы операторов:</h3>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="operator contacts">
<?
$db		= module('doc');

$s	= array();
$s['type']			= 'catalog';
$s['prop']['!place']= 'operator';
$db->open(doc2sql($s));
while($d = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	
	$class	= $db->ndx == 1?' id="first"':'';
	@$fields= $d['fields'];
	@$info	= $fields['any'];
	@$info	= nl2br($info['info']);
?>
<tr <? if(isset($class)) echo $class ?>>
    <td class="info">
<? beginAdmin() ?>
<div class="logo l<? if(isset($id)) echo htmlspecialchars($id) ?>"><a href="<? if(isset($url)) echo $url ?>"></a></div><br />
<div><? if(isset($info)) echo $info ?></div>
<? endAdmin($menu) ?>
    </td>
    <td width="100%"><? module("operator:managers:$id"); ?></td>
</tr>
<? } ?>
</table>
<? } ?><? //	Template operator_manager loaded from  _sites/dt-ekb.ru/_modules/_managers/template.operator_manager.php3 ?>
<? function operator_manager($val, $data)
{
	m('page:style', 'managers.css');
	$operatorID = (int)$val;
	if (!beginCache("operator$operatorID")) return;
	
	$db = module('user');
	$db->open("`operator_id` = $operatorID");
	if (!$db->rows()){
		endCache();
		return;
	}
	
	srand($operatorID+userIP());
	$seek	= rand(0, $db->rows()-1);
	$db->seek($seek);
	$data	= $db->next();
	if (!$data){
		endCache();
		return;
	}

	$person = userPerson($data);
	$phone	= $person['phone'];
	if ($phone) $phone = "<div>$phone</div>";

	$folder		= $db->folder();
	$files		= getFiles("$folder/Title", '');
	@list($titleImage, $titleImagePath)	= each($files);
?><? module("page:style", 'managers.css') ?>
<div class="manager">
<? displayThumbImage($titleImagePath, 180)?>
<div class="dHowH4">Ваш консультант:</div>
<div class="dHowH3"><? module('user:name:full', $data)?></div>
<? if(isset($phone)) echo $phone ?>
</div>
<? endCache(); } ?>
<? //	Template operator_managers loaded from  _sites/dt-ekb.ru/_modules/_managers/template.operator_managers.php3 ?>
<? function operator_managers($val, $data)
{
	$cols		= 3;
	
	$rootData	= NULL;
	$operatorID = (int)$val;
	if (!$operatorID)
	{
		$db			= module('doc');
		$operatorID = currentPageRoot();
		$rootData	= $db->openID($operatorID);

		@$fields= $rootData['fields'];
		@$info	= $fields['any'];
		@$info	= nl2br(trim($info['info']));
	}
	if ($rootData) $percent = round(100/($cols+1));
	else $percent = round(100/$cols);
?><? module("page:style", 'managers.css') ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="operator managers">
<?	
	$db			= module('user');
	$db->open("`operator_id` = $operatorID");
	$data	= $db->next();
	while($data){
?>
<tr>
<? if ($rootData){ ?>
<td width="<? if(isset($percent)) echo htmlspecialchars($percent) ?>%"><? if(isset($info)) echo $info ?></td>
<? } ?><? for($ix=0; $ix < $cols; ++ $ix){ ?>
<td width="<? if(isset($percent)) echo htmlspecialchars($percent) ?>%">
	<? if ($data){
	$id		= $db->id();
	$person = userPerson($data);

	$mail	= htmlspecialchars($person['email']);
	if ($mail) $mail = "<p>e-mail:<br /><a href=\"mailto:$mail\">$mail</a></p>";

	$phone	= $person['phone'];
	if ($phone) $phone = "<p>Телефон:<br />$phone</p>";

	$folder		= $db->folder();
	$files		= getFiles("$folder/Title", '');
	@list($titleImage, $titleImagePath)	= each($files);
?><? displayThumbImage($titleImagePath, 150, 'class="thumb"')?>
<h3><? module('user:name:full', $data)?></h3>
<p><? if(isset($person["work"])) echo htmlspecialchars($person["work"]) ?></p>
<div><? if(isset($mail)) echo $mail ?></div>
<div><? if(isset($phone)) echo $phone ?></div>
    <? $data = $db->next(); }else echo '&nbsp;';  ?></td>
<? } ?>
</tr>
<? } ?>
</table>
<? } ?><? //	Template module_geo_map loaded from  _sites/dt-ekb.ru/_modules/_map/template.module_geo_map.php3 ?>
<? function module_geo_map($val, $data){
	m('script:jq');
	define('_hasNavMap_', true);
	
	$tabs = array();
	$tabs['Схема проезда']	= 'tabMap';
	$tabs['Яндекс-карта']	= 'tabYaMap';
	$tabs['Фотография']		= 'tabGallery';
	$tabs['Видеотур']		= 'tabVideo';

	if (!access('write', 'text:')){
		foreach($tabs as $name => $file){
			if (m("read:$file")) continue;
			unset($tabs[$name]);
		}
	}
	if (!$tabs) return;
?><? module("page:style", 'tabs.css') ?>
<div class="tabs">
    <ul class="tabHolder">
<? $class = 'class="current"'; foreach($tabs as $name => $file){ ?>
        <li <? if(isset($class)) echo $class ?>><a href="#<? if(isset($file)) echo htmlspecialchars($file) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></a></li>
<? $class = NULL; } ?>
        <br clear="all">
    </ul>
<? $class = 'current '; foreach($tabs as $name => $file){ ?>
    <div id="<? if(isset($file)) echo htmlspecialchars($file) ?>" class="<? if(isset($class)) echo htmlspecialchars($class) ?>tabContent"><? module("read:$file"); ?></div>
<? $class = NULL; } ?>
</div>
<script>
$(function(){
	$(".tabs .tabHolder a").click(function(){
		$(".tabs li").removeClass("current");
		$(".tabs .tabContent").hide();
		$(this).parent().addClass("current");
		$(".tabs .tabContent" + $(this).attr("href")).show();
		return false;
	});
});
</script>
<? } ?>