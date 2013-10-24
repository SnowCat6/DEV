<?
function user_property_person_update(&$data)
{
	$userPerson = getValue('userPerson');
	if (!is_array($userPerson)) return;
	
	@dataMerge($userPerson, $data['fields']['person']);
	$data['fields']['person'] = $userPerson;
	$db		= module('user', $data);
	$folder	= $db->folder();
	modFileAction($folder, true);
}
?><? function user_property_person($data){
	$db	= module('user', $data);
	$id	= $db->id();
	if (userID() != $id && !hasAccessRole('admin,developer,accountManager')) return;

	@$fields	= $data['fields'];
	@$person	= $fields['person'];
	
	$folder		= $db->folder();
	$files		= getFiles("$folder/Title", '');
	@list($titleImage, $titleImagePath)	= each($files);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td nowrap="nowrap">Имя</td>
        <td width="100%"><input type="text" name="userPerson[name][last_name]" class="input w100" value="<? if(isset($person["name"]["last_name"])) echo htmlspecialchars($person["name"]["last_name"]) ?>" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Фамилия</td>
        <td><input type="text" name="userPerson[name][first_name]" class="input w100" value="<? if(isset($person["name"]["first_name"])) echo htmlspecialchars($person["name"]["first_name"]) ?>" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Должность</td>
        <td><input type="text" name="userPerson[work]" value="<? if(isset($person["work"])) echo htmlspecialchars($person["work"]) ?>" class="input w100" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td nowrap="nowrap">Телефон</td>
        <td><input type="text" name="userPerson[phone]" value="<? if(isset($person["phone"])) echo htmlspecialchars($person["phone"]) ?>" class="input w100" /></td>
      </tr>
      <tr>
        <td nowrap="nowrap">E-Mail</td>
        <td><input type="text" name="userPerson[email]" value="<? if(isset($person["email"])) echo htmlspecialchars($person["email"]) ?>" class="input w100" /></td>
      </tr>
    </table></td>
    <td valign="top" style="padding-left:20px">
Фотография:
<div><input name="modFileUpload[Title][]" type="file" class="fileupload w100" /></div>
<? if (displayThumbImage($titleImagePath, 150, '', '', $titleImagePath)){?>
<div><label><input type="checkbox" name="modFile[delete][Title]" value="<? if(isset($titleImage)) echo htmlspecialchars($titleImage) ?>" />Удалить</label></div>
<? }else{ ?>
<img src="/design/spacer.gif" width="150" height="1" />
<? } ?>
    </td>
  </tr>
</table>
<? return '0-Информация'; } ?>
