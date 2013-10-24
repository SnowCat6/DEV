<? function doc_page_article_master($db, &$menu, &$data)
{
	if (!testValue('ajax')) setTemplate('master');
	
	$id		= $db->id();
	$date	= masterDate($data);
	
	$form	= array();
	$form[':']['mailTitle'] = "Запись на мастер-класс: $data[title]";
?>
<div class="slot article">
    <? $module_data = array(); $module_data["mask"] = "design/m1.png"; $module_data["popup"] = "false"; moduleEx("doc:titleImage:$id:mask", $module_data); ?>
    <div class="date bg"><? if(isset($date)) echo $date ?></div>
    <h1><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h1>
    <div class="holder">
<? beginAdmin() ?>
<div class="info bg">
<a href="<? if(isset($link)) echo $link ?>"><? if(isset($data["fields"]["any"]["place"])) echo htmlspecialchars($data["fields"]["any"]["place"]) ?></a>
</div>
        <div class="info2 bg2">
<p><? if(isset($data["fields"]["note"])) echo htmlspecialchars($data["fields"]["note"]) ?></p>
        </div>
<? endAdmin($menu) ?>
	</div>
</div>
<div class="page master">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" width="100%">
<? beginAdmin() ?><? document($data) ?><? endAdmin($menu) ?>
    </td>
    <td valign="top" width="400">
	<? module('feedback:display:master', $form) ?>
 <img src="/design/spacer.gif" width="400" height="1" />
    </td>
  </tr>
</table>
</div>
<? } ?>
