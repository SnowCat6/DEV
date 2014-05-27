﻿<? function stat_report(&$db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;
?>
{{script:jq_ui}}
{{ajax:template=ajax_edit}}
{{page:title=Статистика посещения сайта}}
<div id="statTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="{{url:site_stat_now=ajax:ajaxResult}}">Сейчас</a></li>
    <li class="ui-corner-top"><a href="{{url:site_stat_today=ajax:ajaxResult}}">Сегодня</a></li>
    <li class="ui-corner-top"><a href="{{url:site_stat_month=ajax:ajaxResult}}">За месяц</a></li>
    <li class="ui-corner-top"><a href="{{url:site_stat_render=ajax:ajaxResult}}">Время выполнения</a></li>
</ul>
</div>
<script>
$(function(){
	$("#statTabs").tabs();
});
</script>
<? } ?>