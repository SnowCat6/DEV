<? function doc_property_document($data){ ?>
<?
$db		= module('doc', $data);
$type	= $data['doc_type'];
$price	= docPrice($data);
?>
<? if ($type == 'product'){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%">
Заголовок
<div><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></div>
    </td>
    <td style="padding-left:10px">
Цена
<div><input name="doc[price]" type="text" class="input" value="{$price}" size="15" /></div>
    </td>
</tr>
</table>
<? }else{ ?>
Заголовок
<div><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></div>
<? } ?>
Текст документа
<div><textarea name="doc[originalDocument]" cols="" rows="35" class="input w100 editor">{$data[originalDocument]}</textarea></div>
<? return '1-Документ'; } ?>