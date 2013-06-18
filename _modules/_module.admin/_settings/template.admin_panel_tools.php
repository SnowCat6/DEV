<?
function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="25%" valign="top">
<h2 class="ui-state-default">Создать</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
$types = getCacheValue('docTypes');
foreach($types as $docType => $names){
	if (!access('add', "doc:$docType")) continue;
	$name = docType($docType);
?>
  <tr>
    <td><a href="<?= getURL('page_add', "type=$docType")?>" id="ajax_edit">{$name}</a></td>
    <td><a href="<?= getURL("page_all_$docType")?>" id="ajax"> все</a></td>
  </tr>
<? } ?>
</table>
    </td>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <?
$types = getCacheValue('docTemplates');
foreach($types as $docType => $name){
	list($docType, $template) = explode(':', $docType);
	if (!access('add', "doc:$docType")) continue;
?>
  <tr>
    <td><a href="<?= getURL('page_add', "type=$docType&template=$template")?>" id="ajax_edit">{$name}</a></td>
    <td><a href="<?= getURL("page_all_$docType", "template=$template")?>" id="ajax">все</a></td>
  </tr>
<? } ?>
</table>
    </td>
  </tr>
</table>
<? event('admin.tools.add', $data) ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Изменить</h2>
  <p>
    <a href="<?= getURL('page_all')?>" id="ajax">Разделы и каталоги</a>
  </p>
  <p>
    <a href="{{getURL:property_all}}" id="ajax">Все ствойства документов</a> 
  </p>
  <p>
    <a href="{{getURL:order_all}}" id="ajax">Заказы</a> 
    <a href="{{getURL:import}}">Импорт</a> 
  </p>
<? event('admin.tools.edit', $data) ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Настроить</h2>
  <p>
    <a href="{{getURL:admin_mail}}" id="ajax">Исходящая почта</a>
    (<a href="{{getURL:admin_mailTemplates}}" id="ajax">Шаблоны</a>)
  </p>
  <p>
 <a href="{{getURL:feedback_all}}" id="ajax"> Формы обратной связи</a>
  </p>
      
  <p><a href="{{getURL:admin_settings}}" id="ajax">Настройки сервера</a>
  <? if (hasAccessRole('developer')){ ?>
    (<a href="{{getURL:admin_info}}" target="_blank">Info</a>)
  <? } ?>
  </p>
  <? if (hasAccessRole('SEO')){ ?>
  <p><a href="{{getURL:admin_SEO}}" id="ajax">Настройки SEO</a></p>
  <? } ?>
  <p><a href="{{getURL:snippets_all}}" id="ajax">Сниппеты</a></p>
 <? event('admin.tools.settings', $data) ?>
   </td>
    <td width="25%" align="right" valign="top">
<h2 class="ui-state-default">Обслуживание</h2>
<? if (access('clearCache', '')){ ?>
<p><a href="{{getURL=clearCache}}" id="ajax_dialog">Удалить кеш</a></p>
<p><a href="{{getURL=recompileDocuments}}" id="ajax_dialog">Обновить документы</a></p>
<p><a href="{{getURL=clearThumb}}" id="ajax_dialog">Удалить миниизображения</a></p>
<? } ?>
<? event('admin.tools.service', $data) ?>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>