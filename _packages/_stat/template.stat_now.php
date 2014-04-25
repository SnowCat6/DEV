<? function stat_now(&$db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;
	$punycode	= module('punycode');
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
	$userID	= $data['user_id'];
	if (!$userID){
		$userID 	= '';
		$userName	= '';
	}else{
		$dbUser		= module('user');
		$userData	= $dbUser->openID($userID);
		$userName	= mEx("user:name", $userData);
		$userURL	= getURL($dbUser->url());
		$userName	= "<a href=\"$userURL\">$userName</a>";
	}
	
	$ref		= $data['referer'];
	$ref		= $punycode->decodeIDN_URL($ref);
	$ref		= urldecode($ref);
	if ($ref) $ref = "referer: $ref";
	
	$url		= $data['url'];
	$hostName	= $punycode->decodeIDN_URL($data['url']);
	$hostName	= urldecode($hostName);
?>
  <tr>
    <td nowrap="nowrap">{!$date}</td>
    <td>{!$userName}</td>
    <td><a href="http://{!$url}" title="{$ref}">{$hostName}</a></td>
    <td nowrap="nowrap">{$render}</td>
  </tr>
<? } ?>
</table>
<? } ?>
