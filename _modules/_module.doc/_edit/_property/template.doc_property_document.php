<?
//	Редактирование документа
function doc_property_document($data)
{
$db		= module('doc', $data);
$id		= $db->id();
$type	= $data['doc_type'];
$price	= docPrice($data);
?>
<? if ($type == 'product'){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%" nowrap="nowrap">Наименование товара</td>
<? if ($id){?>
<td align="right" nowrap="nowrap"><label for="saveAsCopy">Сохранить как копию</label>
    <input type="checkbox" name="saveAsCopy" id="saveAsCopy" value="doCopy" <?= getValue('saveAsCopy')=='doCopy'?' checked="checked"':''?> />
</td>
<? } ?>
<td align="right" nowrap="nowrap"><label for="copyExternal">Копировать файлы из интернета</label>
    <input type="checkbox" name="copyExternal" id="copyExternal" value="doCopy" <?= getValue('copyExternal')=='doCopy'?' checked="checked"':''?>/>
</td>
      </tr>
    </table></td>
    <td style="padding-left:10px">
Цена
<div></div>
    </td>
</tr>
<tr>
  <td><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></td>
  <td style="padding-left:10px"><input name="doc[price]" type="text" class="input" value="{$price}" size="15" /></td>
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
Текст документа
<div><textarea name="doc[originalDocument]" cols="" rows="35" class="input w100 editor">{$data[originalDocument]}</textarea></div>
<? return '1-Документ'; } ?>