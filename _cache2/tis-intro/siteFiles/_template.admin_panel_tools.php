<?
function admin_panel_tools(&$data){
	if (!hasAccessRole('admin,developer,writer,manager')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="25%" valign="top">
<p>
    <a href="<?= getURL('page_add', 'type=page')?>" id="ajax_edit">Создать раздел</a> 
    <a href="<?= getURL('page_all_page')?>" id="ajax">Посмотреть</a>
</p>
<p>
    <a href="<?= getURL('page_add', 'type=article')?>" id="ajax_edit">Создать статью</a> 
    <a href="<?= getURL('page_all_article')?>" id="ajax">Посмотреть</a>
</p>
<p>
    <a href="{{getURL:property_all}}" id="ajax">Все ствойства документов</a> 
    <a href="{{getURL:snippets_all}}" id="ajax">Сниппеты</a> 
</p>
    </td>
    <td width="25%" valign="top">
<p><a href="{{getURL:person_add}}">Редактировать контакты</a></p>
<p><a href="{{getURL:work_add}}">Редактировать подразделения</a></p>
	</td>
    <td width="25%" valign="top">
<p>
<a href="{{getURL:admin_mail}}" id="ajax">Исходящая почта</a>
(<a href="{{getURL:admin_mailTemplates}}" id="ajax">Шаблоны</a>)
</p>

<p><a href="{{getURL:admin_settings}}" id="ajax">Настройки сервера</a>
<? if (hasAccessRole('admin,developer')){ ?>
(<a href="{{getURL:admin_info}}" target="_blank">Info</a>)
<? } ?>
</p>
<p><a href="{{getURL:admin_SEO}}" id="ajax">Настройки SEO</a></p>
    </td>
    <td width="25%" align="right" valign="top">
<? if (access('clearCache', '')){ ?>
<p><a href="{{getURL=clearCache}}" id="ajax_dialog43">Удалить кеш</a></p>
<p><a href="{{getURL=recompileDocuments}}" id="ajax_message">Обновить документы</a></p>
<? } ?>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>