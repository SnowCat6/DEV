<?
function doc_property_dev_update(&$data)
{
	if (!hasAccessRole('developer')) return;
	if (!$data['type']) return;
	
	list($doc_type, $template)	= explode(':', $data['type'], 2);
	$data['doc_type']	= $doc_type;
	$data['template']	= $template;
	unset($data['type']);
}

function doc_property_dev($data)
{
	if (!hasAccessRole('developer')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$fields	= $data['fields'];
	if (!$fields) $fields = array();
?>

<h2 class="ui-state-default">Настройки документа</h2>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap"><label for="docAccessPage">Разрешить подкаталоги</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][page]" value="0" />
<input type="checkbox" id="docAccessPage" name="doc[fields][access][page]" value="1"<?= $fields['access']['page']?' checked="checked"':''?> />
    </td>
    <td nowrap="nowrap">Тип документа</td>
    <td>
  <select name="doc[type]" class="input w100">
<?
$thisType	= "$data[doc_type]:$data[template]";
$thisData	= docConfig::getTemplate($thisType);
if (!$thisData) docConfig::setTemplate($thisType, array());
foreach(docConfig::getTemplates() as $type => $typeData){?>
      <option value="{$type}"{selected:$type==$thisType} title="{$type} {$typeData[note]}">{$typeData[NameOne]}</option>
<? } ?>
  </select>
    </td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessArticle">Разрешить документы</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][article]" value="0" />
<input type="checkbox" id="docAccessArticle" name="doc[fields][access][article]" value="1"<?= $fields['access']['article']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">&nbsp;</td>
    <td><a href="{{url:admin_doctype=type:$thisType}}" id="ajax"><strong>{$thisType} </strong></a> {$thisData[note]}</td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessComment">Разрешить комментарии</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][comment]" value="0" />
<input type="checkbox" id="docAccessComment" name="doc[fields][access][comment]" value="1"<?= $fields['access']['comment']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">Вид контента</td>
    <td><select name="doc[fields][pageFn]" class="input w100">
      <option value="">-- стандартная --</option>
<?
$template = $data['fields']['pageFn'];
foreach(docConfig::getContentFns() as $name){?>
      <option value="{$name}" {selected:$template==$name}>{$name}</option>
<? } ?>
    </select></td>
  </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td align="right">&nbsp;</td>
    <td nowrap="nowrap">Вид страницы</td>
    <td><select name="doc[fields][page]" class="input w100">
      <option value="">-- стандартная --</option>
<?
$template = $data['fields']['page'];
foreach(docConfig::getPageTemplates() as $name => $val){ ?>
      <option value="{$name}" {selected:$template==$name}>{$val}</option>
<? } ?>
    </select></td>
  </tr>
</table>

<? return '99-Разработчик'; } ?>