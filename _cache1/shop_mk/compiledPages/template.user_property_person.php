<?
function user_property_person_update(&$data)
{
	$userPerson = getValue('userPerson');
	if (!is_array($userPerson)) return;
	
	@dataMerge($userPerson, $data['fields']['person']);
	$data['fields']['person'] = $userPerson;
}
?>
<? function user_property_person($data){
	$db	= module('user', $data);
	$id	= $db->id();
	if (userID() != $id && !hasAccessRole('admin,developer,accountManager')) return;

	@$fields	= $data['fields'];
	@$person	= $fields['person'];
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap">Имя</td>
    <td width="100%"><input type="text" name="userPerson[name][last_name]" class="input w100" value="<? if(isset($person["name"]["last_name"])) echo htmlspecialchars($person["name"]["last_name"]) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Фамилия</td>
    <td><input type="text" name="userPerson[name][first_name]" class="input w100" value="<? if(isset($person["name"]["first_name"])) echo htmlspecialchars($person["name"]["first_name"]) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td nowrap="nowrap">E-Mail</td>
    <td><input type="text" name="userPerson[email]" value="<? if(isset($person["email"])) echo htmlspecialchars($person["email"]) ?>" class="input w100" /></td>
  </tr>
</table>
<? return '0-Информация'; } ?>