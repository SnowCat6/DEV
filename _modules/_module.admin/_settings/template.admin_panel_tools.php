<?
function admin_panel_tools(&$data){
	if (!hasAccessRole('admin,developer,writer,manager')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
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
</p>
    </td>
    <td width="33%" valign="top">
<p>
    <a href="<?= getURL('page_add', 'type=catalog')?>" id="ajax_edit">Создать каталог</a> 
    <a href="<?= getURL('page_all_catalog')?>" id="ajax">Посмотреть</a>
</p>
<p>
    <a href="<?= getURL('page_add', 'type=product')?>" id="ajax_edit">Создать товар</a> 
    <a href="<?= getURL('page_all_product')?>" id="ajax">Посмотреть</a>
</p>
    </td>
    <td width="33%" align="right" valign="top">
<p><a href="{{getURL=clearCache}}" id="ajax_message">Удалить кеш</a></p>
<p><a href="{{getURL=recompileDocuments}}" id="ajax_message">Обновить документы</a></p>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>