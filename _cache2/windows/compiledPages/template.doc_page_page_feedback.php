<? function doc_page_page_feedback(&$db, &$menu, &$data){ ?>
<? beginAdmin() ?>
<? document($data) ?>
<? endAdmin($menu, true) ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="feedbackPage">
<?
$s = array();
$s['parent']= $db->id();
$s['type']	= 'article';

$ddb = module('doc');
$ddb->open(doc2sql($s));
while($data = $ddb->next()){
	$iid	= $ddb->id();
	$title	= docTitleImage($iid);
	$menu	= doc_menu($iid, $data);
?>
<tr>
    <td width="250" valign="top">
    <? displayThumbImageMask($title, 'design/feedbackMask.png', '', '', $title)?>
    </td>
    <td valign="top">
<? beginAdmin() ?>
    <h3><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h3>
    <blockquote><? document($data) ?></blockquote>
<? endAdmin($menu, true) ?>
    </td>
</tr>
<? } ?>
</table>
<? } ?>