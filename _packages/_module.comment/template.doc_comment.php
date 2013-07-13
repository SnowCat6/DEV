<? function doc_comment($db, $val, $id)
{
	$data = $db->openID($id);
	if (!$data) return;
	if (!access('add', "doc:$data[doc_type]:comment")) return;
	if ($val = trim(getValue('comment')))
	{
		$val= stripcslashes($val);
		$val= str_replace("\r\n", '<br />', $val);
		$val= str_replace('{'.'{', '', $val);
		$val= str_replace('}'.'}', '', $val);
		$val= str_replace('['.'[', '', $val);
		$val= str_replace(']'.']', '', $val);
		
		$d	= array();
		$d['title']				= "Комментарий: $data[title]";
		$d['originalDocument']	= $val;
		$iid = module("doc:update:$id:add:comment", $d);
	}
	$url	= $db->url($id);
?>
{{display:message}}
<link rel="stylesheet" type="text/css" href="comment.css">
{{doc:read:comment=parent:$id}}
<div class="comment form">
<h2>Ваш комментарий</h2>
<form action="{{getURL:$url}}" method="post">
<div class="document">
<div><textarea rows="5" class="input w100" name="comment"></textarea></div>
<p><input type="submit" class="button" value="Добавить"></p>
</div>
</form>
</div>
<? } ?>