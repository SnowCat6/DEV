<? function stat_pages(&$db, &$data){
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>Дата</th>
    <th>Пользователь</th>
    <th width="100%">Страница</th>
    <th>Время создания</th>
  </tr>
<?
$db->max	= 25;
$db->order	= '`date` DESC';
$db->open('', $db->max);
while($data = $db->next())
{
	$date	= makeDate($data['date']);
	$date	= date('<b>d.m</b> H:i:s', $date);
	$render	= round($data['renderTime'], 3);
	$url	= urldecode($data['url']);
	$ref	= urldecode($data['referer']);
	$userID	= $data['user_id'];
	if (!$userID) $userID = '';
?>
  <tr>
    <td nowrap="nowrap">{!$date}</td>
    <td>{!$userID}</td>
    <td><a href="http://{!$data[url]}" title="referer:{$ref}">{$url}</a></td>
    <td nowrap="nowrap">{$render}</td>
  </tr>
<? } ?>
</table>
<? } ?>
