<? function doc_property_publish_update(&$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	$thisParent	= array();
	$parentToAdd= getValue('parentToAdd');
	if (is_array($parentToAdd))
	{
		foreach($parentToAdd as $parentID){
			if (!$parentID) continue;
			$thisParent[$parentID] = $parentID;
		}
	}
	$data[':property'][':parent'] = implode(', ', $thisParent);
}
?><? function doc_property_publish($data){?><?
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
    <td width="100%"><input name="doc[datePublish]" type="text" value="<? if(isset($data["datePublish"])) echo htmlspecialchars($data["datePublish"]) ?>" class="input w100" id="calendarPublish" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Шаблон</td>
    <td>
<?
$names		= array();
$templates	= getCacheValue('templates');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => &$val){
	if (!preg_match('#^(doc_read|doc_page)_([^_]+)_(.*)#', $name, $v)) continue;
	$names[$v[3]] = $v[3];
}

$templates	= getCacheValue('docTemplates');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => &$val){
	list($name, $template) = explode(':', $name);
	$names[$template] = $val;
}
?>
<select name="doc[template]" class="input w100">
	<option value="">-- стандартный --</option>
<?
@$template = $data['template'];
foreach($names as $name => $titleName){
	$class = $template == $name?' selected="selected" class="current"':'';
?>
	<option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($titleName)) echo htmlspecialchars($titleName) ?></option>
<? } ?>
</select>
    </td>
  </tr>
<? if (hasAccessRole('admin,developer')){ ?>
  <tr>
    <td nowrap="nowrap">Шаблон страницы</td>
    <td>
<select name="doc[fields][page]" class="input w100">
	<option value="">-- стандартная --</option>
<?
$namesPage	= array();
$pages		= getCacheValue('pages');
foreach($pages as $name => &$val){
	if (!preg_match('#^page\.(.*)#', $name, $v)) continue;
	$namesPage[$v[1]] = $v[1];
}
@$template = $data['fields']['page'];
foreach($namesPage as $name => &$val){
	$class = $template == $name?' selected="selected" class="current"':'';
?>
	<option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($val)) echo htmlspecialchars($val) ?></option>
<? } ?>
</select>
    </td>
  </tr>
<? } ?>
</table>
    </td>
    <td width="33%" valign="top" style="padding:0 20px"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td nowrap="nowrap"><label for="docVisible">Скрытый</label></td>
        <td align="right">
<input type="hidden" name="doc[visible]" value="1" />
<input type="checkbox" id="docVisible" name="doc[visible]" value="0"<?= $data['visible']?'':' checked="checked"'?> />
          </td>
      </tr>
<? if (hasAccessRole('admin,developer')){ ?>
      <tr>
        <td nowrap="nowrap"><label for="docDelete">Не удалять</label></td>
        <td align="right">
<input type="hidden" name="doc[fields][denyDelete]" value="0" />
<input type="checkbox" id="docDelete" name="doc[fields][denyDelete]" value="1"<?= $fields['denyDelete']?' checked="checked"':''?> />
          </td>
      </tr>
<? } ?><? if (hasAccessRole('admin,developer')){ ?>
      <tr>
        <td nowrap="nowrap"><label for="docAccessPage">Разрешить подкаталоги</label></td>
        <td align="right">
<input type="hidden" name="doc[fields][access][page]" value="0" />
<input type="checkbox" id="docAccessPage" name="doc[fields][access][page]" value="1"<?= $fields['access']['page']?' checked="checked"':''?> />
          </td>
      </tr>
      <tr>
        <td nowrap="nowrap"><label for="docAccessArticle">Разрешить документы</label></td>
        <td align="right">
<input type="hidden" name="doc[fields][access][article]" value="0" />
<input type="checkbox" id="docAccessArticle" name="doc[fields][access][article]" value="1"<?= $fields['access']['article']?' checked="checked"':''?> />
        </td>
      </tr>
      <tr>
        <td nowrap="nowrap"><label for="docAccessComment">Разрешить комментарии</label></td>
        <td align="right">
<input type="hidden" name="doc[fields][access][comment]" value="0" />
<input type="checkbox" id="docAccessComment" name="doc[fields][access][comment]" value="1"<?= $fields['access']['comment']?' checked="checked"':''?> />
        </td>
      </tr>
<? } ?>
      <tr>
        <td nowrap="nowrap">Сортировка</td>
        <td align="right"><input name="doc[sort]" type="text" class="input" value="<? if(isset($data["sort"])) echo htmlspecialchars($data["sort"]) ?>" size="4" /></td>
      </tr>
    </table>
    </td>
    <td width="33%" valign="top">
Родительские документы:
<div id="parentToAdd">
<?
$ddb	= module('doc');
$ddb->order = 'title';

$thisParents= array();
$prop		= $id?module("prop:get:$id"):array();
$ddb->openIN($prop[':parent']['property']);
while($d = $ddb->next()){
	$iid = $ddb->id();
	$thisParents[$iid] = $iid;
?>
<div><label><input name="parentToAdd[]" type="checkbox" value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>" checked="checked" /><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></label></div>
<? } ?>
</div>

<select name = "parentToAdd[]" class="input w100" id="parentToAdd">
<option value="">- добавить родителя -</option>
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
	if ($iid == $id) continue;
	if ($thisParents[$iid]) continue;
?><option value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>"><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option><? } ?>
</select>
    </td>
</tr>
</table>
<script>
$(function(){
	$("select#parentToAdd").change(function(){
		var val = $(this).val();
		if (!val) return;
		var text = $(this).find(":selected").text();
		$('<div><label><input type="checkbox" checked="checked" name="parentToAdd[]" value="' + val + '"  />' + text + '</label></div>')
			.appendTo("div#parentToAdd");
			$(this).attr("selectedIndex", 0);
	});
});
</script>
<? return '10-Публикация'; } ?>