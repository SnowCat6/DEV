<? function admin_toolbar(){?>
{{script:jq_ui}}
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="adminToolbar adminForm">
	<div class="adminPanel"><a href="#">Панель управления сайтом</a></div>
	<div class="adminWindow">

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Инстументы</a></li>
    <li><a href="#tabs-2">Настройки</a></li>
    <li><a href="#tabs-4">Права доступа</a></li>
    <li><a href="#tabs-3">Лог</a></li>
  </ul>
  <!-- Инструменты -->
  <div id="tabs-1">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
<div>
<? if (access('add', 'doc:page')){ ?> <a href="<?= getURL('page_add', 'type=page')?>" id="ajax">Создать раздел</a> <? } ?>
<a href="<?= getURL('page_all_page')?>" id="popup">Посмотреть</a>
</div>
<div>
<? if (access('add', 'doc:article')){ ?><a href="<?= getURL('page_add', 'type=article')?>" id="ajax">Создать статью</a> <? } ?>
<a href="<?= getURL('page_all_article')?>" id="popup">Посмотреть</a>
</div>
    </td>
    <td valign="top" nowrap="nowrap">
<a href="<?= getURL('', 'clearCache')?>">Удалить кеш</a>
    </td>
  </tr>
</table>

  </div>
  <!-- Настройки -->
  <div id="tabs-2">
{{admin:settings}}
  </div>
  <!-- Лог -->
  <div id="tabs-3">
{{debug:executeTime}}
<pre>{{page:display:log}}</pre>
  </div>
  <!-- Права доступа -->
  <div id="tabs-4">
  </div>
</div>
	<div class="clear"></div>
    </div>
</div>
<script>
$(function() {
	$( ".adminForm #tabs" ).tabs();
	$( ".adminForm input[type=submit]").button();
});
</script>
<? } ?>

