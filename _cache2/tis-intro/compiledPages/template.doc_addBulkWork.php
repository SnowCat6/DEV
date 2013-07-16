<? function doc_addBulkWork($db, $val, &$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;
	
	switch ($val)
	{
	case 'access':
		$d		= &$data[0];
		$doc	= &$data[1];
		if ($d['doc_type'] != 'article') return;
		if ($d['template'] != 'work') return;
		
		@$work= $doc['fields'];
		@$work= $work['work'];
		
		dataMerge($work, $d['fields']['work']);
		$d['fields']['work'] = $work;
		return;
	case 'edit';
		$d		= $data[2];
		if ($d['doc_type'] != 'page') return;
		
		$data[0]	= 'doc_property_document_work';
		$data[1]	= __FILE__;
		return;
	case 'tools':
		$url	= getURL('work_add');
		echo "<p><a href=\"$url\">Редактировать подразделения</a></p>";
		return;
	}

	if (is_array($doc = getValue('doc')))
	{
		$docDelete			= getValue('docDelete');
		
		foreach($doc as $id => $data)
		{
			$data['template']	= 'work';
			if ($id){
				if (@$docDelete[$id]){
					module("doc:update:$id:delete");
					continue;
				}
				$iid = module("doc:update:$id:edit", $data);
			}else{
				$iid = alias2doc('work');
				$iid = module("doc:update:$iid:add:page", $data);
			}
		}
	}
	module('script:jq_ui');
	$db->sortByKey('sort', getValue('documentOrder'));
?>
<? module("script:jq"); ?>
<? $module_data = array(); $module_data[] = "Подразделения"; moduleEx("page:title", $module_data); ?>
<? module("display:message"); ?>
<? module("page:style", 'style.css') ?>
<form action="<? module("getURL:work_add"); ?>" method="post" class="bulkAdd">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th nowrap>&nbsp;</th>
    <th nowrap>Название</th>
    <th nowrap>Тел. / факс.</th>
    <th nowrap>Моб. телефон</th>
    <th width="1%" nowrap>&nbsp;</th>
  </tr>
  <tr>
    <td colspan="3"><h2>Добавть новое подразделение</h2></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td><input name="doc[0][:property][workData]" type="checkbox" value="yes"  /></td>
    <td><input type="text" name="doc[0][title]" class="input w100" value=""/></td>
    <td><input type="text" name="doc[0][:property][Телефон]" class="input w100" value=""/></td>
    <td><input type="text" name="doc[0][:property][Мобильный номер]" class="input w100" value=""/></td>
    <td><input type="submit" class="button" value="Добавить"></td>
  </tr>
<tr>
    <td colspan="3"><h2>Изменить</h2></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tbody id="sortable">
<?
$s = array();
$s['type']		= 'page';
$s['template']	= 'work';
$db->order		= 'sort';
$db->open(doc2sql($s));
while($data = $db->next()){
	$id		= $db->id();
	$prop	= module("prop:get:$id");
	
	@$work	= $data['fields'];
	@$work	= $work['work'];
?>
  <tr>
    <td>
      <input type="hidden" name="documentOrder[]" value= "<? if(isset($id)) echo htmlspecialchars($id) ?>" />
      <input name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][workData]" type="hidden" value="">
      <input name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][workData]" type="checkbox" value="yes" <?= @$prop['workData']?' checked="checked"':''?>>
    </td>
    <td>
    <input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][title]" class="input w100" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>">
    </td>
    <td><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Телефон]" class="input w100" value="<? if(isset($prop["Телефон"]["property"])) echo htmlspecialchars($prop["Телефон"]["property"]) ?>"></td>
    <td><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Мобильный номер]" class="input w100" value="<? if(isset($prop["Мобильный номер"]["property"])) echo htmlspecialchars($prop["Мобильный номер"]["property"]) ?>"></td>
    <td><input type="submit" class="button" value="Сохранить"></td>
  </tr>
<? } ?>
</tbody>
</table>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable();
	$( "#sortable" ).disableSelection();
});
</script>
<? }?>
<? function doc_property_document_work(&$data)
{
	$db		= module('doc', $data);
	$id		= $db->id();
	$prop	= module("prop:get:$id");
?>
Подразделение
<div><input type="text" name="doc[title]" class="input w100" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" /></div>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
    <td valign="top" nowrap>Телефон</td>
    <td width="100%" valign="top"><input type="text" name="doc[:property][Телефон]" class="input w100" value="<? if(isset($prop["Телефон"]["property"])) echo htmlspecialchars($prop["Телефон"]["property"]) ?>" /></td>
</tr>
<tr>
  <td valign="top" nowrap>Моб. телефон</td>
  <td valign="top"><input type="text" name="doc[:property][Мобильный номер]" class="input w100" value="<? if(isset($prop["Мобильный номер"]["property"])) echo htmlspecialchars($prop["Мобильный номер"]["property"]) ?>" /></td>
</tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>

<? return '1-Редактирование';} ?>

