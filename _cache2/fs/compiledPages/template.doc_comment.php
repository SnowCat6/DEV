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
<? module("display:message"); ?>
<? module("page:style", 'comment.css') ?>
<? $module_data = array(); $module_data["parent"] = "$id"; moduleEx("doc:read:comment", $module_data); ?>
<div class="comment form">
<h2>Ваш комментарий</h2>
<form action="<? module("getURL:$url"); ?>" method="post">
<div class="document">
<div><textarea rows="5" class="input w100" name="comment"></textarea></div>
<p><input type="submit" class="button" value="Добавить"></p>
</div>
</form>
</div>
<? } ?>