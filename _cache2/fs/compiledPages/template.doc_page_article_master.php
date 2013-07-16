<? function doc_page_article_master($db, $val, $data)
{
	if (!testValue('ajax')) setTemplate('master');
	
	$id		= $db->id();
	$menu	= doc_menu($id, $data, false);
	
	$form	= array();
	$form[':']['mailTitle'] = "Запись на мастер-класс: $data[title]";
?>
<div class="slot article">
<?  if (beginCompile($data, "slot1")){ ?>
<? displayThumbImageMask(docTitleImage($id), 'design/m1.png')?>
<?  endCompile($data, "slot1"); } ?>
    <h1><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h1>
    <div class="holder">
<? beginAdmin() ?>
<div class="info bg">
<a href="<? if(isset($link)) echo $link ?>"><? if(isset($data["fields"]["any"]["place"])) echo htmlspecialchars($data["fields"]["any"]["place"]) ?></a>
</div>
        <div class="info2 bg2">
<div><?= masterDate($data)?></div>
<p><? if(isset($data["fields"]["note"])) echo htmlspecialchars($data["fields"]["note"]) ?></p>
        </div>
<? endAdmin($menu) ?>
	</div>
</div>
<div class="page master">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" width="100%">
<? beginAdmin() ?>
<? document($data) ?>
<? endAdmin($menu) ?>
    </td>
    <td valign="top" width="400">
	<? module('feedback:display:master', $form) ?>
 <img src="design/spacer.gif" width="400" height="1" />
    </td>
  </tr>
</table>
</div>
<? } ?>
