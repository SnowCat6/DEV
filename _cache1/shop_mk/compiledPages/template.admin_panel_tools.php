<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="25%" valign="top">
<h2 class="ui-state-default">Документы</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
$types = getCacheValue('docTypes');
foreach($types as $docType => $names){
	if (!access('add', "doc:$docType")) continue;
	$name = docType($docType, 1);
?>
  <tr>
    <td nowrap="nowrap"><a href="/<?= getURL("page_all_$docType")?>" id="ajax">Список <? if(isset($name)) echo htmlspecialchars($name) ?></a></td>
    <td><a href="/<?= getURL('page_add', "type=$docType")?>" id="ajax_edit">новый</a></td>
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
    <td nowrap="nowrap"><a href="/<?= getURL("page_all_$docType", "template=$template")?>" id="ajax"><? if(isset($name)) echo htmlspecialchars($name) ?></a></td>
    <td><a href="/<?= getURL('page_add', "type=$docType&template=$template")?>" id="ajax_edit">новый</a></td>
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
  <p><a href="/<?= getURL('page_all')?>" id="ajax">Разделы и каталоги</a></p>
<? if (hasAccessRole('admin,developer,writer')){ ?>
    <p><a href="/<? module("getURL:property_all"); ?>" id="ajax">Все ствойства документов</a></p>
<? } ?>
<? 	if (hasAccessRole('admin,developer,cashier')){ ?>
<p>
    <a href="/<? module("getURL:order_all"); ?>" id="ajax">Заказы</a> 
    <a href="/<? module("getURL:import"); ?>">Импорт</a> 
</p>
<? } ?>
<? event('admin.tools.edit', $data) ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Настроить</h2>
<? if (hasAccessRole('admin,developer,writer,manager')){ ?>
<p>
    <a href="/<? module("getURL:admin_mail"); ?>" id="ajax">Исходящая почта</a>
    (<a href="/<? module("getURL:admin_mailTemplates"); ?>" id="ajax">Шаблоны</a>)
</p>
<? } ?>
<? if (hasAccessRole('admin,developer,writer')){ ?>
<p>
	<a href="/<? module("getURL:feedback_all"); ?>" id="ajax"> Формы обратной связи</a>
</p>
<? } ?>
      
<? if (hasAccessRole('admin,developer')){ ?>
<p><a href="/<? module("getURL:admin_settings"); ?>" id="ajax">Настройки сервера</a>
<? if (hasAccessRole('developer')){ ?>
    (<a href="/<? module("getURL:admin_info"); ?>" target="_blank">Info</a>)
<? } ?>
</p>
<? } ?>
<? if (hasAccessRole('SEO')){ ?>
  <p><a href="/<? module("getURL:admin_SEO"); ?>" id="ajax">Настройки SEO</a></p>
<? } ?>
<? if (hasAccessRole('admin,developer,writer')){ ?>
  <p><a href="/<? module("getURL:snippets_all"); ?>" id="ajax">Сниппеты</a></p>
<? } ?>
 <? event('admin.tools.settings', $data) ?>
   </td>
    <td width="25%" align="right" valign="top">
<h2 class="ui-state-default">Обслуживание</h2>
<? if (access('clearCache', '')){ ?>
<p><a href="/<? $module_data = array(); $module_data[] = "clearCache"; moduleEx("getURL:#", $module_data); ?>" id="ajax_dialog">Удалить кеш</a></p>
<p><a href="/<? $module_data = array(); $module_data[] = "recompileDocuments"; moduleEx("getURL:#", $module_data); ?>" id="ajax_dialog">Обновить документы</a></p>
<p><a href="/<? $module_data = array(); $module_data[] = "clearThumb"; moduleEx("getURL:#", $module_data); ?>" id="ajax_dialog">Удалить миниизображения</a></p>
<? } ?>
<? if (hasAccessRole('developer')){ ?>
<p><a href="/<? $module_data = array(); $module_data[] = "clearCode"; moduleEx("getURL:#", $module_data); ?>" id="ajax_dialog">Пересобрать код</a></p>
<? } ?>
<? event('admin.tools.service', $data) ?>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>