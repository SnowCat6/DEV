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
	
	$templatePlatform	= array();
	$templatePlatform['Стандартный'] 	= 'pageTemplate';
	$templatePlatform['Телефон'] 		= 'phone_pageTemplate';
	$templatePlatform['Планшет'] 		= 'tablet_pageTemplate';

	$contentFnPlatform	= array();
	$contentFnPlatform['Стандартный'] 	= 'pageTemplate';
	$contentFnPlatform['Телефон'] 		= 'phone_pageTemplate';
	$contentFnPlatform['Планшет'] 		= 'tablet_pageTemplate';
?>
<? if ($type){ ?>
    <input type="hidden" name="type" value="{$type}" />
<? } ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
  <tbody>
<? if (!$type){ ?>
    <tr>
      <td nowrap="nowrap" valign="top">Идентификатор класса</td>
      <td width="100%" colspan="3">
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
            <input type="text" class="input w100" name="typeTemplate" value="" />
        </div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan="3"></td>
    </tr>
<? } ?>
    <tr>
      <td nowrap="nowrap">Название ед. число</td>
      <td width="100%" colspan="3"><input type="text" class="input w100" name="docConfig[NameOne]" value="{$data[NameOne]}" /></td>
    </tr>
    <tr>
      <td nowrap="nowrap">Название мн. число</td>
      <td colspan="3"><input type="text" class="input w100" name="docConfig[NameOther]" value="{$data[NameOther]}" /></td>
      </tr>
    <tr>
      <th nowrap="nowrap">&nbsp;</th>
<? foreach($contentFnPlatform as $name=>$var){ ?>
      <th width="33%">{$name}</th>
<? } ?>
    </tr>
    <tr>
      <td nowrap="nowrap">Шаблон контента</td>
<? foreach($contentFnPlatform as $name=>$varName){ ?>
      <td><select name="docConfig[{$varName}]" class="input w100">
        <option value="">-- стандартный --</option>
        <? foreach(docConfig::getContentFns() as $contentFn){ ?>
        <option value="{$contentFn}" {selected:$contentFn==$data[$varName]}>{$contentFn}</option>
        <? } ?>
      </select></td>
<? } ?>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      </tr>
    <tr>
      <td nowrap="nowrap">Шаблон страницы</td>
<? foreach($contentFnPlatform as $name=>$varName){ ?>
      <td><select name="docConfig[{$varName}]" class="input w100">
        <option value="">-- стандартный --</option>
        <? foreach(docConfig::getPageTemplates() as $pageTemplate){ ?>
        <option value="{$pageTemplate}" {selected:$pageTemplate==$data[$varName]}>{$pageTemplate}</option>
        <? } ?>
      </select></td>
<? } ?>
      </tr>
    <tr>
      <td nowrap="nowrap" valign="top">Комментарий</td>
      <td colspan="3"><textarea name="docConfig[note]" rows="4" class="input w100">{$data[note]}</textarea></td>
      </tr>
  </tbody>
</table>

<p><a href="{{url:admin_docconfig}}" id="ajax">Типы документов</a></p>

<? return '0-Основные настройки'; } ?>
