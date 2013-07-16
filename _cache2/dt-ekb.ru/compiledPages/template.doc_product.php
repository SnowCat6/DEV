<? function doc_product($db, $val, &$data)
{
	switch ($val){
	case 'edit';
		$d		= $data[2];
		if ($d['doc_type'] != 'product') return;
		
		$data[0]	= 'doc_property_document_product';
		$data[1]	= __FILE__;
		return;
	}
}?>
<?
//	Редактирование документа
function doc_property_document_product_update(&$data)
{
	if (!hasAccessRole('admin,developer,writer,operator')) return;

	$price = str_replace(' ', '', $data[':property']['Цена (руб.)']);
	@$data['price'] = $price;
}
?>

<? function doc_property_document_product($data)
{
	if (!hasAccessRole('admin,developer,writer,operator')) return;
	
	$db		= module('doc', $data);
	$id		= $db->id();
	@$fields= $data['fields'];
	$prop	= module("prop:get:$id");
	
	$gProp	= module('prop:value:Город вылета,Страна');
	$propCity	= $gProp['Город вылета'];
	if (!is_array($propCity)) $propCity = array();
	$propCountry= $gProp['Страна'];
	if (!is_array($propCity)) $propCity = array();
?>
<table border="0" cellspacing="0" cellpadding="2" width="100%">
  <tr>
    <th valign="top" nowrap>Спецпредложение</th>
    <td><input name="doc[title]" type="text" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" class="input w100" /></td>
  </tr>
  <tr>
    <th valign="top">Место</th>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="33%">Страна</td>
        <td width="33%">Курорт</td>
        <td width="33%">Отель</td>
      </tr>
      <tr>
        <td>
<select name="doc[:property][Страна]" class="input w100">
    <option value="">- выберите страну -</option>
<?
@$thisValue	= $prop['Страна'];
@$thisValue	= $thisValue['property'];
foreach($propCountry as $name){
	$class = $thisValue==$name?' selected="selected"':'';
?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
        </td>
        <td><input name="doc[:property][Курорт]" type="text" value="<? if(isset($prop["Курорт"]["property"])) echo htmlspecialchars($prop["Курорт"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Отель]" type="text" value="<? if(isset($prop["Отель"]["property"])) echo htmlspecialchars($prop["Отель"]["property"]) ?>" class="input w100" /></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <th valign="top" nowrap>Ссылка на отель</th>
    <td><input name="doc[fields][any][hotelURL]" type="text" value="<? if(isset($fields["any"]["hotelURL"])) echo htmlspecialchars($fields["any"]["hotelURL"]) ?>" class="input w100" /></td>
  </tr>
  <tr>
    <th valign="top">Условия</th>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="25%">Город вылета</td>
        <td width="25%">Дата начала</td>
        <td width="25%">Длительность</td>
        <td width="25%">Питание</td>
      </tr>
      <tr>
        <td>
<select name="doc[:property][Город вылета]" class="input w100">
    <option value="">- выберите город -</option>
<?
@$thisValue	= $prop['Город вылета'];
@$thisValue	= $thisValue['property'];
foreach($propCity as $name){
	$class = $thisValue==$name?' selected="selected"':'';
?><option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>>из <? if(isset($name)) echo htmlspecialchars($name) ?></option><? } ?>
</select>
        </td>
        <td><input name="doc[fields][any][date]" type="text" value="<? if(isset($fields["any"]["date"])) echo htmlspecialchars($fields["any"]["date"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Длительность]" type="text" value="<? if(isset($prop["Длительность"]["property"])) echo htmlspecialchars($prop["Длительность"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Питание]" type="text" value="<? if(isset($prop["Питание"]["property"])) echo htmlspecialchars($prop["Питание"]["property"]) ?>" class="input w100" /></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <th valign="top" nowrap>Цена</th>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="16%">Валюта</td>
        <td width="16%">Цена</td>
        <td width="16%">Цена (руб.)</td>
        <td width="16%">Цена (виза)</td>
        <td width="16%">Цена (топливо)</td>
        <td width="20%">Скидка (%)</td>
      </tr>
      <tr>
        <td><input name="doc[:property][Валюта]" type="text" value="<? if(isset($prop["Валюта"]["property"])) echo htmlspecialchars($prop["Валюта"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Цена (вал.)]" type="text" value="<? if(isset($prop["Цена (вал.)"]["property"])) echo htmlspecialchars($prop["Цена (вал.)"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Цена (руб.)]" type="text" value="<? if(isset($prop["Цена (руб.)"]["property"])) echo htmlspecialchars($prop["Цена (руб.)"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Цена (виза)]" type="text" value="<? if(isset($prop["Цена (виза)"]["property"])) echo htmlspecialchars($prop["Цена (виза)"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Цена (топливо)]" type="text" value="<? if(isset($prop["Цена (топливо)"]["property"])) echo htmlspecialchars($prop["Цена (топливо)"]["property"]) ?>" class="input w100" /></td>
        <td><input name="doc[:property][Скидка (%)]" type="text" value="<? if(isset($prop["Скидка (%)"]["property"])) echo htmlspecialchars($prop["Скидка (%)"]["property"]) ?>" class="input w100" /></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <th nowrap>Описание тура</th>
    <td align="right"><? module("snippets:tools:doc[originalDocument]"); ?></td>
  </tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>

<? return '1-Путевка'; } ?>