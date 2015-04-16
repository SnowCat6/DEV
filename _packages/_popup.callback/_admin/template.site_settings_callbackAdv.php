<? function site_settings_callbackAdv()
{
	$def	= array(
		'timeout1' => 15,
		'timeout2' => 50,
		'timeout3' => 60
	);
	$ini	= getIniValue(':feedbackAdv');
	foreach($def as $name=>$v){
		if ((int)$ini[$name] <= 0) $ini[$name] = $v;
	}
?>

<table border="0" cellspacing="0" cellpadding="2">
  <tbody>
    <tr>
      <td>Время первого напоминания</td>
      <td><input type="text" name="settings[:feedbackAdv][timeout1]" class="input" value="{$ini[timeout1]}" placeholder="{$def[timeout1]}"> сек.</td>
    </tr>
    <tr>
      <td>Время второго напоминания</td>
      <td><input type="text" name="settings[:feedbackAdv][timeout2]" class="input" value="{$ini[timeout2]}" placeholder="{$def[timeout2]}"> сек.</td>
    </tr>
    <tr>
      <td>Время через которое показывать повторно</td>
      <td><input type="text" name="settings[:feedbackAdv][timeout3]" class="input" value="{$ini[timeout3]}" placeholder="{$def[timeout3]}"> сек.</td>
    </tr>
  </tbody>
</table>

<h2>Текст сообщения</h2>
{{read:callbackAdv}}


<? return 'Заказ звонка'; } ?>