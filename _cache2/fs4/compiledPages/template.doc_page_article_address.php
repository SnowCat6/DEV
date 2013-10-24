<? function doc_page_article_address($db, &$menu, &$data)
{
	$id		= $db->id();
	$note	= nl2br($data['fields']['any']['note']);
	
	$places	= explode("\r\n", $data['fields']['any']['places']);
	$places	= implode('<br />', $places);
	
	$url	= $data['fields']['any']['url'];
	if ($url) $url = "<a href=\"$url\" title=\"$title\" target=\"_blank\">$url</a>";
?><? beginAdmin() ?>
<address><? if(isset($places)) echo $places ?></address>
<blockquote><? if(isset($note)) echo $note ?></blockquote>
<p><? if(isset($url)) echo $url ?></p>
<? document($data) ?><? endAdmin($menu) ?><? $module_data = array(); $module_data["id"] = "$id"; moduleEx("doc:read:yandexMap", $module_data); ?><? } ?>