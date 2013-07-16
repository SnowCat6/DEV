<? function doc_addBulkPerson($db, $val, &$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	switch ($val)
	{
	case 'access':
		$d		= &$data[0];
		$doc	= &$data[1];
		$error	= &$data[2];

		if ($d['doc_type'] != 'article')return;
		if ($d['template'] != 'person')	return;
		
		@$person= $doc['fields'];
		@$person= $person['person'];
		
		dataMerge($person, $d['fields']['person']);
		$d['fields']['person'] = $person;
		
		$title	= array();
		if (@$v = $person['first_name'])	$title[] = $v;
		if (@$v = $person['second_name'])	$title[] = $v;
		if (@$v = $person['last_name'])		$title[] = $v;
		
		$title	= implode(' ', $title);
		$d['title'] = $title;
		
		if (!$title){
			$error = 'Введите Ф.И.О. сотрудника';
		}
		return;
	case 'edit';
		$d		= $data[2];
		if ($d['doc_type'] != 'article') return;
		
		$data[0]	= 'doc_property_document_person';
		$data[1]	= __FILE__;
		return;
	case 'tools':
		$url	= getURL('person_add');
		echo "<p><a href=\"$url\">Редактировать контакты</a></p>";
		return;
	}
	if (is_array($doc = getValue('doc')))
	{
		$docDelete			= getValue('docDelete');
		$titleImageDelete	= getValue('titleImageDelete');
		
		foreach($doc as $id => $data)
		{
			$data['template']	= 'person';
			if ($id){
				if (@$docDelete[$id]){
					module("doc:update:$id:delete");
					continue;
				}
				$iid = module("doc:update:$id:edit", $data);
			}else{
				$iid = alias2doc('person');
				$iid = module("doc:update:$iid:add:article", $data);
			}
			
			$folder	= $db->folder($iid);
			$folder	= "$folder/Title";
			
			@$file	= $_FILES['titleImage']['tmp_name'][$id];
			@$name	= $_FILES['titleImage']['name'][$id];

			if (is_file($file)){
				delTree($folder);
				makeDir($folder);
				move_uploaded_file($file, "$folder/$name");
			}else{
				if (@$titleImageDelete[$id]){
					delTree($folder);
				}
			}
		}
	}
$gProp		= module('prop:value:Должность,Статус');

@$workProp	= $gProp['Должность'];
if (!is_array($workProp))	$workProp = array();

@$statusProp= $gProp['Статус'];
if (!is_array($statusProp))	$statusProp = array();

?>
<? module("script:jq"); ?>
<? module("script:ajaxLink"); ?>
<? $module_data = array(); $module_data[] = "Добавление контакта"; moduleEx("page:title", $module_data); ?>
<? module("display:message"); ?>
<? module("page:style", 'style.css') ?>
<? if (access("add", "doc:article")){?>
<form action="<? module("getURL:person_add"); ?>" method="post" enctype="multipart/form-data" class="bulkAdd">
<table width="100%" border="0" cellspacing="0" cellpadding="10">
  <tr>
    <td width="25%" valign="top">
<div>Подразделение:</div>
<select name="doc[0][:property][:workParent]" class="input w100">
<option value="">---</option>
<?
@$thisVal	= '';
$ddb		= module('doc');
$ddb->open(doc2sql(array('template'=>'work', 'type'=>'page')));
while($d = $ddb->next()){
	$iid = $ddb->id();
?>
<option value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>"<?= $thisVal==$iid?' selected="selected"':''?>><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option>
<? } ?>
</select>
<div>Фотография:</div>
<div><input name="titleImage[0]" type="file" class="fileupload" /></div>
</td>
    <td width="25%" valign="top" nowrap="nowrap" class="border">
<div><input type="text" name="doc[0][fields][person][first_name]" class="input w100 clean" value2="Введите Фамилию"></div>
<div><input type="text" name="doc[0][fields][person][second_name]" class="input w100 clean" value2="Введите Имя"></div>
<div><input type="text" name="doc[0][fields][person][last_name]" class="input w100 clean" value2="Введите Отчество"></div>
<div> Показатели  работы
  <input type="hidden" name="doc[0][:property][!workdata]" value="" />
  <input type="checkbox" name="doc[0][:property][!workdata]" value="yes" <?= @$prop['!workdata']?' checked="checked"':''?> />
</div></td>
    <td width="25%" valign="top" nowrap="nowrap" class="border">
<div>
<select name="doc[0][:property][Статус]" class="input w100">
    <option value="">- выберите статус -</option>
    <? foreach($statusProp as $name){ ?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
</div>
<div>
<select name="doc[0][:property][Должность]" class="input w100">
    <option value="">- выберите должность -</option>
    <? foreach($workProp as $name){ ?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
</div>
<div><input type="text" name="doc[0][:property][Внутренний номер]" class="input w100 clean" value2="Введите внутренний номер"></div>
<div><input type="text" name="doc[0][:property][Мобильный номер]" class="input w100 clean" value2="Введите мобильный номер"></div>
    </td>
    <td width="25%" valign="top" class="border">
<div><input type="text" name="doc[0][:property][e-mail]" class="input w100 clean" value2="Введите эл. почту"></div>
<div><input type="text" name="doc[0][:property][ICQ]" class="input w100 clean" value2="Введите ICQ"></div>
<div><input type="text" name="doc[0][:property][Skype]" class="input w100 clean" value2="Введите skype"></div>
    </td>
  </tr>
  <tr class="submit">
    <td valign="top"><input type="submit" name="button" class="button w100" value="Добавить"></td>
    <td valign="top">&nbsp;</td>
    <td valign="top">&nbsp;</td>
    <td valign="top">&nbsp;</td>
    </tr>
</table>
</form>
<script>
$(function(){
	$(".bulkAdd .clean").focus(function(){
		$(this).val("").removeClass("clean").unbind();
	}).each(function(){
		$(this).val($(this).attr("value2"));
	}).parents("form").submit(function(){
		($(this).find(".clean")).val("");
	});
});
</script>
<? } ?>
<form action="<? module("getURL:person_add"); ?>" method="post" enctype="multipart/form-data" class="bulkAdd">
<table width="100%" border="0" cellspacing="0" cellpadding="10">
<tr>
  <th>Фотография</th>
  <th class="border">Подразделение</th>
  <th class="border">Подразделение</th>
  <th class="border">Ф.И.О и статус</th>
  <th class="border">Должность и телефоны</th>
</tr>
<?
$s = array();
$s['type']		= 'article';
$s['template']	= 'person';
$db->open(doc2sql($s));
while($data = $db->next()){
	$id		= $db->id();
	$prop	= module("prop:get:$id");
	
	@$person= $data['fields'];
	@$person= $person['person'];
	$title	= docTitleImage($id);
/*	
	$FIO	= explode(' ', $data['title']);
	$person['first_name']	= $FIO[0];
	$person['second_name']	= $FIO[1];
	$person['last_name']	= $FIO[2];
*/
?>
<? if (access("write", "doc:$id")){?>
<tr>
    <td width="20%" align="center" valign="top"><? displayThumbImage($title, 150)?></td>
    <td width="20%" valign="top" class="border">
<div>Подразделение:</div>
<select name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][:workParent]" class="input w100">
<option value="">---</option>
<?
@$thisVal	= $prop[':workParent']['property'];
$ddb		= module('doc');
$ddb->open(doc2sql(array('template'=>'work', 'type'=>'page')));
while($d = $ddb->next()){
	$iid = $ddb->id();
?>
<option value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>"<?= $thisVal==$iid?' selected="selected"':''?>><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option>
<? } ?>
</select>
<div> Показатели  работы
  <input type="hidden" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][!workdata]" value="" />
  <input type="checkbox" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][!workdata]" value="yes" <?= @$prop['!workdata']?' checked="checked"':''?> />
</div><br />

<div>Фотография:</div>
<div><input name="titleImage[<? if(isset($id)) echo htmlspecialchars($id) ?>]" type="file" class="fileupload" /></div>
Удалить фото <input name="titleImageDelete[<? if(isset($id)) echo htmlspecialchars($id) ?>]" type="checkbox" value="1" />
    </td>
    <td width="20%" valign="top" class="border">
Фамилмя:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][fields][person][first_name]" class="input w100" value="<? if(isset($person["first_name"])) echo htmlspecialchars($person["first_name"]) ?>"></div>
Имя:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][fields][person][second_name]" class="input w100" value="<? if(isset($person["second_name"])) echo htmlspecialchars($person["second_name"]) ?>"></div>
Отчество:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][fields][person][last_name]" class="input w100" value="<? if(isset($person["last_name"])) echo htmlspecialchars($person["last_name"]) ?>"></div>
Статус:
<div>
<select name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Статус]" class="input w100">
<option value="">- выберите статус -</option>
<?
@$thisVal	= $prop['Статус']['property'];
foreach($statusProp as $name){
	$class = $thisVal == $name?' selected="selected"':'';
?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
</div>
    </td>
    <td width="20%" valign="top" class="border">
Должность:
<div>
<select name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Должность]" class="input w100">
<option value="">- выберите должность -</option>
<?
@$thisVal	= $prop['Должность']['property'];
foreach($workProp as $name){
	$class = $thisVal == $name?' selected="selected"':'';
?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
</div>
Внут. номер:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Внутренний номер]" class="input w100" value="<? if(isset($prop["Внутренний номер"]["property"])) echo htmlspecialchars($prop["Внутренний номер"]["property"]) ?>"></div>
Моб. номер:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Мобильный номер]" class="input w100" value="<? if(isset($prop["Мобильный номер"]["property"])) echo htmlspecialchars($prop["Мобильный номер"]["property"]) ?>"></div>
    </td>
    <td width="20%" valign="top" class="border">
e-mail:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][e-mail]" class="input w100" value="<? if(isset($prop["e-mail"]["property"])) echo htmlspecialchars($prop["e-mail"]["property"]) ?>"></div>
ICQ:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][ICQ]" class="input w100" value="<? if(isset($prop["ICQ"]["property"])) echo htmlspecialchars($prop["ICQ"]["property"]) ?>"></div>
Skype:
<div><input type="text" name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Skype]" class="input w100" value="<? if(isset($prop["Skype"]["property"])) echo htmlspecialchars($prop["Skype"]["property"]) ?>"></div>
    </td>
</tr>
<tr class="submit">
  <td><input type="submit" name="button" class="button w100" value="Сохранить"></td>
  <td><span class="border">
<? if (access("delete", "doc:$id")){?>
    <input name="docDelete[<? if(isset($id)) echo htmlspecialchars($id) ?>]" type="checkbox" value="1" />  удалить</span>
<? } ?>
    </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
<? } ?>
<? }// access ?>
</table>

</form>
<? } ?>
<? function doc_property_document_person(&$data)
{
	$db		= module('doc', $data);
	$id		= $db->id();
	$fields	= $data['fields'];
	@$person= $fields['person'];
	$folder	= $db->folder();
	$prop	= $id?module("prop:get:$id"):array();

	$gProp		= module('prop:value:Должность');
	@$workProp	= $gProp['Должность'];
	if (!is_array($workProp)) $workProp = array();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
  <td width="20%" valign="top" nowrap><div>Подразделение:</div>
<select name="doc[:property][:workParent]" class="input w100">
    <option value="">---</option>
<?
@$thisVal	= $prop[':workParent']['property'];
$ddb		= module('doc');
$ddb->open(doc2sql(array('template'=>'work', 'type'=>'page')));
while($d = $ddb->next()){
	$iid = $ddb->id();
?>
    <option value="<? if(isset($iid)) echo htmlspecialchars($iid) ?>"<?= $thisVal==$iid?' selected="selected"':''?>><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option>
<? } ?>
</select>
<div>Картинка:</div></td>
    <td width="20%" valign="top" nowrap>Фамилмя:
      <div>
        <input type="text" name="doc[fields][person][first_name]" class="input w100" value="<? if(isset($person["first_name"])) echo htmlspecialchars($person["first_name"]) ?>" />
      </div>
Имя:
<div>
  <input type="text" name="doc[fields][person][second_name]" class="input w100" value="<? if(isset($person["second_name"])) echo htmlspecialchars($person["second_name"]) ?>" />
</div>
Отчество:
<div>
  <input type="text" name="doc[fields][person][last_name]" class="input w100" value="<? if(isset($person["last_name"])) echo htmlspecialchars($person["last_name"]) ?>" />
</div>
<div> Показатели  работы
  <input type="hidden" name="doc[:property][!workdata]" value="" />
  <input type="checkbox" name="doc[:property][!workdata]" value="yes" <?= @$prop['!workdata']?' checked="checked"':''?> />
</div><div></div></td>
    <td width="20%" valign="top">Статус:
      <div>
        <input type="text" name="doc[:property][Статус]" class="input w100" value="<? if(isset($prop["Статус"]["property"])) echo htmlspecialchars($prop["Статус"]["property"]) ?>" />
      </div>
      Должность:
      <div>
<select name="doc[<? if(isset($id)) echo htmlspecialchars($id) ?>][:property][Должность]" class="input w100">
    <option value="">---</option>
<?
@$thisVal	= $prop['Должность']['property'];
foreach($workProp as $name){
	$class = $thisVal == $name?' selected="selected"':'';
?>
    <option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?></option>
<? } ?>
</select>
      </div>
Внут. номер:
<div>
  <input type="text" name="doc[:property][Внутренний номер]" class="input w100" value="<? if(isset($prop["Внутренний номер"]["property"])) echo htmlspecialchars($prop["Внутренний номер"]["property"]) ?>" />
</div>
Моб. номер:
<div>
  <input type="text" name="doc[:property][Мобильный номер]" class="input w100" value="<? if(isset($prop["Мобильный номер"]["property"])) echo htmlspecialchars($prop["Мобильный номер"]["property"]) ?>" />
</div></td>
    <td width="20%" valign="top">e-mail:
      <div>
        <input type="text" name="doc[:property][e-mail]" class="input w100" value="<? if(isset($prop["e-mail"]["property"])) echo htmlspecialchars($prop["e-mail"]["property"]) ?>" />
      </div>
ICQ:
<div>
  <input type="text" name="doc[:property][ICQ]" class="input w100" value="<? if(isset($prop["ICQ"]["property"])) echo htmlspecialchars($prop["ICQ"]["property"]) ?>" />
</div>
Skype:
<div>
  <input type="text" name="doc[:property][Skype]" class="input w100" value="<? if(isset($prop["Skype"]["property"])) echo htmlspecialchars($prop["Skype"]["property"]) ?>" />
</div></td>
</tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>

<? return '1-Редактирование';} ?>

