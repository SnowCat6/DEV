<? function doc_property_publish_update($data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	$thisParent	= array();
	$parentToAdd= getValue('parentToAdd');
	if (is_array($parentToAdd))
	{
		foreach($parentToAdd as $parentID){
			if (!$parentID) continue;
			$thisParent[] = $parentID;
		}
	}

	$data[':property'][':parent'] = implode(', ', $thisParent);
}
?>
<? function doc_property_publish($data){?>
<?
	if (!hasAccessRole('admin,developer,writer')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	module('script:calendar');
	
	if (!$id){
		if ($type == 'article' || $type == 'product')
			$data['datePublish'] = date('d.m.Y');
	}else{
		$date = makeDate($data['datePublish']);
		if ($date) $data['datePublish'] = date('d.m.Y H:i', $date);
	}
	$folder		= $db->folder();
	$folder		= "$folder/Gallery";
	@$fields	= $data['fields'];
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <td width="33%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap">Дата публикации</td>
    <td width="100%"><input name="doc[datePublish]" type="text" value="{$data[datePublish]}" class="input w100" id="calendarPublish" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Шаблон</td>
    <td>
<?
$names		= array();

$templates	= getCacheValue('templates');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => $val){
	if (!preg_match('#^(doc_read|doc_page)_([^_]+)_(.*)#', $name, $v)) continue;
	$names[$v[3]] = $v[3];
}

$templates	= getCacheValue('docTemplates');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => $val){
	list($name, $template) = explode(':', $name);
	$names[$template] = $val;
}
?>
<select name="doc[template]" class="input w100">
	<option value="">-- стандартный --</option>
<?
@$template = $data['template'];
foreach($names as $name => $titleName){
	$class = $template == $name?' selected="selected" class="current"':''; ?>
	<option value="{$name}"{!$class}>{$titleName}</option>
<? } ?>
</select>
    </td>
  </tr>
  <tr>
    <td nowrap="nowrap">Показывать страницу</td>
    <td><input name="doc[fields][redirect]" type="text" value="{$fields[redirect]}" class="input w100" /></td>
  </tr>
</table>
    </td>
    <td width="33%" valign="top" style="padding:0 20px"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td nowrap="nowrap"><label for="docVisible">Скрытый</label></td>
        <td align="right"><input type="hidden" name="doc[visible]" value="1" />
          <input type="checkbox" id="docVisible" name="doc[visible]" value="0"<?= $data['visible']?'':' checked="checked"'?> /></td>
      </tr>
    </table>
    </td>
    <td width="33%" valign="top">
Родительские документы:
<table width="100%" cellpadding="0" cellspacing="0">
<?
$ddb	= module('doc');
$ddb->order = 'title';

$prop	= $id?module("prop:get:$id"):array();
$ddb->openIN($prop[':parent']['property']);
while($d = $ddb->next()){
	$iid = $ddb->id();
?>
<tr>
	<th><input name="parentToAdd[]" id="parent{$iid}" type="checkbox" value="{$iid}" checked="checked" /></th>
	<td width="100%"><label for="parent{$iid}">{$d[title]}</label></td>
</tr>
<? } ?>
</table>
<select name = "parentToAdd[]" class="input w100">
<option value="0">- добавить родителя -</option>
<?
$parentToAdd	= array();
$parentTypes	= getCacheValue('docTypes');
foreach($parentTypes as $parentType => $val){
	if (access('add', "doc:$parentType:$type"))
		$parentToAdd[] = $parentType;
};
$parentToAdd = implode(', ', $parentToAdd);

$s			= array();
$sql		= array();
$s['type'] 	= $parentToAdd;
doc_sql($sql, $s);

$ddb->open($sql);
while($d = $ddb->next()){
	$iid = $ddb->id();
	if ($iid == $id) coninue;
	if (is_int(array_search($iid, $prop))) continue;
?><option value="{$iid}">{$d[title]}</option><? } ?>
</select>
    </td>
</tr>
</table>

<? return '10-Публикация'; } ?>