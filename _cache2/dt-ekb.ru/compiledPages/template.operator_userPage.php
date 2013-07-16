<? function operator_userPage($val, $userID)
{
	$db		= module('user');
	$data	= $db->openID($userID);
	$operatorID	= $data['operator_id'];
	if (!$operatorID) return;
	
	m("script:ajaxLink");
	
	$db = module('doc');
	$db->open(doc2sql(array('parent' => $operatorID, 'type' => 'catalog')));
	$db->next();
	$iid = $db->id(); 
?><br><br>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top"><h2>Ваши путевки:</h2></td>
    <td width="300" align="right" valign="top">
<? if ($iid && access('add', "doc:$iid:product")) { ?>
    <a href="<? $module_data = array(); $module_data["type"] = "product"; moduleEx("getURL:page_add_$iid", $module_data); ?>" id="ajax" class="button">Добавть путевку</a>
<? } ?>
    </td>
</tr>
</table>
<? if (!module('doc:read:catalog', array('parent*' => $operatorID, 'type' => 'product'))){ ?>
<? messageBox('У вас нет путевок') ?>
<? } ?>
<? } ?>
