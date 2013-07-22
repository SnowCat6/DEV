<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO') &&	!access('use', 'adminPanel')) return;
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
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$docType")?>" id="ajax">Список {$name}</a></td>
    <td><a href="<?= getURL('page_add', "type=$docType")?>" id="ajax_edit">новый</a></td>
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
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$docType", "template=$template")?>" id="ajax">{$name}</a></td>
    <td><a href="<?= getURL('page_add', "type=$docType&template=$template")?>" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>
</table>
    </td>
  </tr>
</table>
<? toolsMenuEvent('admin.tools.add') ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Изменить</h2>
  <p><a href="<?= getURL('page_all')?>" id="ajax">Разделы и каталоги</a></p>
<? if (hasAccessRole('admin,developer,writer')){ ?>
    <p><a href="{{getURL:property_all}}" id="ajax">Все ствойства документов</a></p>
<? } ?>
<? toolsMenuEvent('admin.tools.edit') ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Настроить</h2>
<? if (hasAccessRole('admin,developer,writer,manager')){ ?>
<p>
    <a href="{{getURL:admin_mail}}" id="ajax">Исходящая почта</a>
    (<a href="{{getURL:admin_mailTemplates}}" id="ajax">Шаблоны</a>)
</p>
<? } ?>
<? if (hasAccessRole('admin,developer,writer')){ ?>
<p>
	<a href="{{getURL:feedback_all}}" id="ajax"> Формы обратной связи</a>
</p>
<? } ?>
      
<? if (hasAccessRole('admin,developer')){ ?>
<p><a href="{{getURL:admin_settings}}" id="ajax">Настройки сервера</a>
<? if (hasAccessRole('developer')){ ?>
    (<a href="{{getURL:admin_info}}" target="_blank">Info</a>)
<? } ?>
</p>
<? } ?>
<? if (hasAccessRole('SEO')){ ?>
  <p><a href="{{getURL:admin_SEO}}" id="ajax">Настройки SEO</a></p>
<? } ?>
<? if (hasAccessRole('admin,developer,writer')){ ?>
  <p><a href="{{getURL:snippets_all}}" id="ajax">Сниппеты</a></p>
<? } ?>
<? toolsMenuEvent('admin.tools.settings') ?>
   </td>
    <td width="25%" align="right" valign="top">
<h2 class="ui-state-default">Обслуживание</h2>
<? if (access('clearCache', '')){ ?>
<p><a href="{{getURL:#=clearCache}}" id="ajax_dialog">Удалить кеш</a></p>
<p><a href="{{getURL:#=recompileDocuments}}" id="ajax_dialog">Обновить документы</a></p>
<p><a href="{{getURL:#=clearThumb}}" id="ajax_dialog">Удалить миниизображения</a></p>
<? } ?>
<? if (hasAccessRole('developer')){ ?>
<p><a href="{{getURL:#=clearCode}}" id="ajax_dialog">Пересобрать код</a></p>
<? } ?>
<? toolsMenuEvent('admin.tools.service') ?>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>
<? function toolsMenuEvent($eventName)
{
ob_start();
$menu	= array();
event($eventName, $menu);
$p		= ob_get_clean();

foreach($menu as $name => $url)
{
	$id	= NULL;
	list($name, $id) = explode('#', $name, 2);
	if ($id) $id = "id=\"$id\"";
	echo "<div><a href=\"$url\"$id>$name</a></div>";
}
echo $p;
}?>