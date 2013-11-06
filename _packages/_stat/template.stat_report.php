<? function stat_report(&$db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;
?>
{{page:title=Статистика посещения сайта}}
{{stat:online}}
{{stat:visitors}}
{{stat:pages}}
<? } ?>