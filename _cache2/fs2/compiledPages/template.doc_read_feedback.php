<?
function doc_read_feedback_beginCache(&$db, &$data, &$search)
{
	if (userID()) return;
	return hashData($search);
}
function doc_read_feedback(&$db, &$data, &$search)
{
	if (!$db->rows()) return $search;
?><? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	$note	= makeNote(getDocument($data));
?><? beginAdmin() ?>
<b><a href="<? if(isset($url)) echo htmlspecialchars($url) ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></b>
<div><? if(isset($note)) echo $note ?></div>
<? endAdmin($menu, true) ?><? } ?><? return $search; } ?>
