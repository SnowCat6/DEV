<?
function prop_edit($db, $val, $data)
{
	$id = (int)$data[1];
	noCache();
	
	$propGroups	= array();
	$propGroups['globalSearch']		= 'Глобальный поиск';
	$propGroups['globalSearch2']	= 'Глобальный поиск уточняющий';
	$propGroups['productSearch']	= 'Поиск товаров';
	$propGroups['productSearch2']	= 'Отображение товаров в каталоге';

	if (!hasAccessRole('admin,developer,writer'))
		return module('message:error', 'Недостаточно прав');
	
	if ($val == 'add'){
		$data			= array();
		$data['visible']= 1;
		$data['sort']	= 0;
	}else{
		$data = $db->openID($id);
		if (!$data) return module('message:error', 'Нет свойства');
	}
	
	$prop = getValue('property');
	if (is_array($prop))
	{
		$prop['sort']	= (int)$prop['sort'];
		$prop['group']	= implode(',', array_filter($prop['group'], 'strlen'));
		$iid			= addProperty($id, $prop);		
		if (is_int($iid)){
			module('message', 'Данные сохранены');
			return module('prop:all');
		}
		module('message:error', $iid);
		dataMerge($prop, $data);
		$data	= $prop;
	}

	module('script:ajaxForm');
	module('script:ajaxLink');
	if (testValue('ajax')) setTemplate('ajax_edit');
	
	$name	= htmlspecialchars($data['name']);
?>
{{page:title=Изменение свойства $name}}
{{display:message}}
<form action="{{getURL:#}}" method="post" class="admin ajaxForm ajaxReload">
<div id="propertyEditTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#propertyEdit1">Основные настройки</a></li>
    <li class="ui-corner-top"><a href="#propertyEdit2">Стандартные значения</a></li>
    <li class="ui-corner-top"><a href="#propertyEdit3">Обработчик</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Выполнить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="propertyEdit1">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td nowrap="nowrap">Название</td>
        <td width="100%"><input type="text"name="property[name]" class="input w100" value="{$data[name]}" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Формат*</td>
        <td><input type="text" name="property[format]" class="input w100" value="{$data[format]}" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Тип</td>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><select name="property[valueType]" class="input w100">
      <?
foreach(explode(',', 'valueText,valueDigit') as $name){
	$class = $name==$data['valueType']?' selected="selected"':'';
?>
      <option value="{$name}"{!$class}>{$name}</option>
<? } ?>
    </select></td>
    <td nowrap="nowrap">Сортировка</td>
    <td><input type="text" name="property[sort]" class="input" value="{$data[sort]}" /></td>
  </tr>
</table>
</td>
      </tr>
    </table></td>
    <td valign="top" nowrap="nowrap" style="padding-left:10px">
<?
$thisValue	= explode(',', $data['group']);
foreach($propGroups as $name => $value){
	$class	= is_int(array_search($name, $thisValue))?' checked="checked"':''
?>
<div>
<input name="property[group][{$name}]" type="hidden" value="" />
<label><input type="checkbox" name="property[group][{$name}]" value="{$name}"{!$class} /> {$value}</label>
</div>
<? } ?>
<div>
<input name="property[visible]" type="hidden" value="1" />
<label><input type="checkbox" name="property[visible]" value="0"<?= $data['visible']?'':' checked="checked"'?> />Не показывать в свойствах</label>
</div>
    </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>Описание</th>
    <th>Псевдонимы</th>
  </tr>
  <tr>
    <td width="50%" valign="top">
<textarea name="property[note]" rows="6" class="input w100">{$data[note]}</textarea>
    </td>
    <td width="50%" valign="top">
<textarea name="property[alias]" rows="6" class="input w100">{$data[alias]}</textarea>
<p>В каждой строчке по одному названию, при обнаружении свойств с таким названием они будут объеденены</p>
    </td>
  </tr>
</table>

<p><a href="{{getURL:property_all}}" class="seekLink">Посмотреть все свойства</a></p>
<p>Для обозначения места подстановки значения используйте знак <strong>%</strong> в поле <strong>формат</strong></p>

</div>

<div id="propertyEdit2">
<textarea name="property[values]" rows="15" class="input w100">{$data[values]}</textarea>
<p>В каждой строчке по одному названию, используется со свойствами фиксированного выбора</p>
</div>

<div id="propertyEdit3">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" width="50%">
<select class="input w100" name="property[queryName]">
<option value="">-- обработчик --</option>
<?
$thisValue	= $data['queryName'];
$q			= getCacheValue('propertyQuery');
if (!is_array($q)) $q = $array();
foreach($q as $query => $name){ $class = $thisValue == $query?' selected="selected"':'';
?><option value="{$query}"{!$class}>{$name}</option>
<? } ?>
</select>
<div><textarea name="property[query]" rows="15" class="input w100">{$data[query]}</textarea></div>
    </td>
    <td valign="top" width="50%">
<ul>
<? foreach($q as $query => $name){ ?>
<li>
<div><b>{$name}</b></div>
<? event("prop.queryHelp:$query")?></li>
<? } ?>
</ul>
      </td>
  </tr>
</table>

</div>

</div>
{{script:jq_ui}}
<script>
$(function() { $("#propertyEditTabs").tabs(); });
</script>
</form>
<? } ?>
<? function addproperty($id, $prop)
{
	$db	= module('prop');
	if (!$prop['name']) return 'Введите название свойства';
	
	if (!$id){
		$id		= $db->update($prop, false);
		if (!$id) return 'Ошибка записи в базу данных.';
	}
	
	$table = $db->dbValue->table();
	//	Объеденить свойства
	$aliases= array();
	@$a		= explode("\r\n", $prop['alias']);
	foreach($a as $key => &$val){
		$val = trim($val);
		if ($val) $aliases[$val] = $val;
	}

	$a = array();
	foreach($aliases as $name){
		$name	= dbEncString($db, $name);
		$a[]	= $name;
	};
	
	if ($data['name'] != $prop['name']){
		$name	= $prop['name'];
		$name	= dbEncString($db, $name);
		$a[]	= $name;
	}
		
	if ($a){
		$bHasUpdate = false;
		$a = implode(', ', $a);
		$db->open("`name` IN ($a)");
		while($d = $db->next())
		{
			$iid = $db->id();
			if ($iid == $id) continue;
			
			@$a = explode("\r\n", $data['alias']);
			foreach($a as $val){
				$val = trim($val);
				if ($val) $aliases[$val] = $val;
			}
			
			$bHasUpdate = true;
			$aliases[$d['name']] = $d['name'];
			
			$ddb->dbValue->exec("UPDATE $table SET prop_id = $id WHERE prop_id = $iid");
			$ddb->delete($iid);
		}
		$prop['alias'] = implode("\r\n", $aliases);
		if ($bHasUpdate) module('doc:recompile');
	}

	$db->setValues($id, $prop, false);
	m("prop:clear:$id");
	m('doc:recompile');
	
	return $id;
} ?>
