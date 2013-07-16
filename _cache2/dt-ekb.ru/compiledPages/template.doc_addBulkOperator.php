<? function doc_addBulkOperator($db, $val, &$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	switch ($val){
	case 'access':
		$d		= &$data[0];
		$doc	= &$data[1];
		$error	= &$data[2];

		if ($d['doc_type'] != 'catalog')	return;
		if ($d['template'] != 'operator')	return;

		return;
	case 'edit';
		$d		= $data[2];
		if ($d['doc_type'] != 'catalog') return;
		
		$data[0]	= 'doc_property_document_operator';
		$data[1]	= __FILE__;
		return;
	case 'editUser';
		$data[0]	= 'user_property_operator';
		$data[1]	= __FILE__;
		return;
	case 'accessUser':
		$d		= &$data[0];
		$user	= &$data[1];
		$error	= &$data[2];
		if (isset($user['operator_id'])){
			$d['operator_id']	= (int)$user['operator_id'];
		}

		return;
	}
}
?>
<? function doc_property_document_operator(&$data)
{
	$db = module('doc', $data);
	$id	= $db->id();
	$prop	= $id?module("prop:get:$id"):array();
	@$fields= $data['fields'];
?>
<table border="0" cellspacing="0" cellpadding="1" width="100%">
  <tr>
    <td nowrap="nowrap">Название</td>
    <td width="100%"><input type="text" name="doc[title]" class="input w100" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" /></td>
  </tr>
</table>


Краткая информация, телефон, факс, e-mail
<div><textarea name="doc[fields][any][info]" cols="" rows="5" class="input w100"><? if(isset($fields["any"]["info"])) echo htmlspecialchars($fields["any"]["info"]) ?></textarea></div>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%">Страница оператора</td>
    <td>Сниппеты</td>
    <td><select name="snippets" id="snippets" class="input" onchange="editorInsertHTML('doc[originalDocument]', this.value); this.selectedIndex = 0; ">
<option value="">-- вставить сниппет ---</option>
<?
$snippets = module('snippets:get');
foreach($snippets as $name => $code){ $code = '['."[$name]".']'; ?>
<option value="<? if(isset($code)) echo htmlspecialchars($code) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></option>
<? } ?>
    </select></td>
  </tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>

<? return '1-Оператор'; } ?>

<? function user_property_operator_update($data){
	$operatorID = getValue('userOperator');
	$data['operator_id'] = $operatorID;
}?>
<? function user_property_operator($data){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <tr>
    <td nowrap="nowrap">Оператор</td>
    <td width="100%">
<select name="userOperator" class="input w100">
<option value="-">-- Оператор не назначен --</option>
<?
$db	= module('doc');
$s	= array('type'=>'catalog', 'template'=>'operator');
$db->open(doc2sql($s));

@$thisValue = $data['operator_id'];
while($d = $db->next()){
	$iid		= $db->id();
	$class	= $thisValue == $iid?' selected="selected" class="current"':'';
?>
<option value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>"<? if(isset($class)) echo $class ?>><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option>
<? } ?>
</select>
    </td>
  </tr>
</table>

<? return '1-Оператор'; } ?>