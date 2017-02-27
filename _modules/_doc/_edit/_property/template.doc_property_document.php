<?
//	Редактирование документа
function doc_property_document($data)
{
$db		= module('doc', $data);
$id		= $db->id();
$type	= $data['doc_type'];

$folder			= $db->folder();
$uploadFolders	= array("$folder/Title", "$folder/Image");

$prices			= getCacheValue(':price');
?>
<? if ($type == 'product'){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%" nowrap="nowrap">Наименование товара</td>
<? if ($id){?>
<td align="right" nowrap="nowrap"><label for="saveAsCopy" title="Создать копию документа и записать">Сохранить как копию</label>
    <input type="checkbox" name="saveAsCopy" id="saveAsCopy" value="doCopy" <?= getValue('saveAsCopy')=='doCopy'?' checked="checked"':''?> />
</td>
<? } ?>
<td align="right" nowrap="nowrap"><label for="copyExternal" title="Копирует изображения с сылками на внешние ресурсы в документ">Копировать файлы из интернета</label>
    <input type="checkbox" name="copyExternal" id="copyExternal" value="doCopy" <?= getValue('copyExternal')=='doCopy'?' checked="checked"':''?>/>
</td>
      </tr>
    </table>
	</td>
<? foreach($prices as $name => $field){
	$name	= $field[1];
?>
    <td style="padding-left:10px">{$name}</td>
<? } ?>
</tr>
<tr>
  <td><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></td>
<? foreach($prices as $name => $field){
	$field	= $field[0];
?>
  <td style="padding-left:10px"><input name="doc[{$field}]" type="text" class="input" value="{$data[$field]}" size="15" /></td>
<? } ?>
</tr>
</table>
<? }else{ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" nowrap="nowrap">Заголовок документа</td>
<? if ($id){?>
<td align="right" nowrap="nowrap"><label for="saveAsCopy">Сохранить как копию</label>
    <input type="checkbox" name="saveAsCopy" id="saveAsCopy" value="doCopy" />
</td>
<? } ?>
<td align="right" nowrap="nowrap"><label for="copyExternal">Копировать файлы из интернета</label>
    <input type="checkbox" name="copyExternal" id="copyExternal" value="doCopy" />
</td>
  </tr>
</table>
<div><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></div>
<? } ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%">Текст документа</td>
    <td align="right">{{editor:tools:doc[document]=folder:$uploadFolders}}</td>
  </tr>
</table>
<div><textarea name="doc[document]" {{editor:data:$folder}} cols="" rows="35" class="input w100 editor">{$data[document]}</textarea></div>
<? return '1-Документ'; } ?>