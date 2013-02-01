<?
function admin_panel_tools(&$data){
	if (!hasAccessRole('admin,developer,writer,manager')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
<div>
<? if (access('add', 'doc:page')){ ?> <a href="<?= getURL('page_add', 'type=page')?>" id="ajax_edit">Создать раздел</a> <? } ?>
<a href="<?= getURL('page_all_page')?>" id="ajax">Посмотреть</a>
</div>
<div>
<? if (access('add', 'doc:article')){ ?><a href="<?= getURL('page_add', 'type=article')?>" id="ajax_edit">Создать статью</a> <? } ?>
<a href="<?= getURL('page_all_article')?>" id="ajax">Посмотреть</a>
</div>
    </td>
    <td valign="top" nowrap="nowrap">
<a href="<?= getURL('', 'clearCache')?>">Удалить кеш</a>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>