<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO') &&	!access('use', 'adminPanel')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="25%" valign="top">
<h2 class="ui-state-default">Документы</h2>
<? toolsMenuEvent('admin.tools.add') ?>
    </td>
    <td width="25%" valign="top">
  <h2 class="ui-state-default">Изменить</h2>
<? toolsMenuEvent('admin.tools.edit') ?>
    </td>
    <td width="25%" valign="top">
<h2 class="ui-state-default">Настроить</h2>
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

foreach($menu as $name => &$data)
{
	if (is_array($data)) continue;

	$url= $data;
	$id	= NULL;
	list($name, $id) = explode('#', $name, 2);
	if ($id) $id = "id=\"$id\"";
	if ($url) echo "<div><a href=\"$url\"$id>$name</a></div>";
	else echo "<h2>$name</h2>";
}

foreach($menu as $name => &$data)
{
	if (!is_array($data)) continue;

	echo '<div>';
	foreach($data as $name => &$url){
		$id	= NULL;
		list($name, $id) = explode('#', $name, 2);
		if ($id) $id = "id=\"$id\"";
		if ($url) echo "<a href=\"$url\"$id>$name</a> ";
		else echo "<h2>$name</h2>";
	}
	echo '</div>';
}
echo $p;
}?>