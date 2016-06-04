<?
//	+function docConfig_main_update
function docConfig_main_update($data)
{
	if (!hasAccessRole('developer')) return;
}
?>
<?
//	+function docConfig_main
function docConfig_main($data)
{
	$type	= $data['id'];
	list($typeName, $templateName)	= explode(':', $type);
?>
<? if ($type){ ?>
    <input type="hidden" name="type" value="{$type}" />
<? } ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
  <tbody>
<? if (!$type){ ?>
    <tr>
      <td nowrap="nowrap" valign="top">Идентификатор класса</td>
      <td width="100%">
      <div>
			Тип документа
            <select name="typeType" class="input w100">
<?
foreach(docConfig::getTypes() as $docType){
	$name	= docType($docType, 1);
?>
				<option value="{$docType}">{$name}</option>
<? } ?>
            </select>
        </div>
        <div>
            Шаблон документа
            <input type="text" class="input w100" name="typeTemplate" value=""
        </div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td></td>
    </tr>
<? } ?>
    <tr>
      <td nowrap="nowrap">Название ед. число</td>
      <td width="100%"><input type="text" class="input w100" name="docConfig[NameOne]" value="{$data[NameOne]}" /></td>
    </tr>
    <tr>
      <td nowrap="nowrap">Название мн. число</td>
      <td><input type="text" class="input w100" name="docConfig[NameOther]" value="{$data[NameOther]}" /></td>
      </tr>
    <tr>
      <td nowrap="nowrap">Шаблон контента</td>
      <td><select name="docConfig[contentFn]" class="input w100">
        <option value="">-- стандартный --</option>
        <? foreach(docConfig::getContentFns() as $contentFn){ ?>
        <option value="{$contentFn}" {selected:$contentFn==$data[contentFn]}>{$contentFn}</option>
        <? } ?>
      </select></td>
      </tr>
    <tr>
      <td nowrap="nowrap">Шаблон страницы</td>
      <td><select name="docConfig[pageTemplate]" class="input w100">
        <option value="">-- стандартный --</option>
        <? foreach(docConfig::getPageTemplates() as $pageTemplate){ ?>
        <option value="{$pageTemplate}" {selected:$pageTemplate==$data[pageTemplate]}>{$pageTemplate}</option>
        <? } ?>
      </select></td>
      </tr>
    <tr>
      <td nowrap="nowrap">Комментарий</td>
      <td><input type="text" name="docConfig[note]" value="{$data[note]}" class="input w100" /></td>
      </tr>
  </tbody>
</table>
<? return '0-Основные настройки'; } ?>
