<? function site_settings_callbackAdv()
{
	$def	= getCacheValue(':callbackAdv');
	$ini	= getIniValue(':feedbackAdv');
	foreach($def as $name=>$v){
		if (!isset($ini[$name])) $ini[$name] = $v;
	}
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="50%" valign="top">

<table border="0" cellspacing="0" cellpadding="2">
  <tbody>
    <tr>
      <td>Время первого напоминания</td>
      <td nowrap="nowrap"><input name="settings[:feedbackAdv][timeout1]" type="text" class="input" placeholder="{$def[timeout1]}" value="{$ini[timeout1]}" size="5"> сек.</td>
    </tr>
    <tr>
      <td>Время второго напоминания</td>
      <td nowrap="nowrap"><input name="settings[:feedbackAdv][timeout2]" type="text" class="input" placeholder="{$def[timeout2]}" value="{$ini[timeout2]}" size="5"> сек.</td>
    </tr>
    <tr>
      <td>Время через которое показывать повторно</td>
      <td nowrap="nowrap"><input name="settings[:feedbackAdv][timeout3]" type="text" class="input" placeholder="{$def[timeout3]}" value="{$ini[timeout3]}" size="5"> 
      мин.</td>
    </tr>
  </tbody>
</table>

      </td>
      <td width="50%" valign="top"><table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tbody>
          <tr>
            <td nowrap="nowrap">Цвет фона</td>
            <td width="100%" nowrap="nowrap">
            	<input type="text" name="settings[:feedbackAdv][bkColor]" class="input w100" value="{$ini[bkColor]}" placeholder="{$def[bkColor]}">
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap">Цвет текста</td>
            <td nowrap="nowrap">
            	<input type="text" name="settings[:feedbackAdv][txColor]" class="input w100" value="{$ini[bkColor]}" placeholder="{$def[txColor]}">
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap">Изображение фона</td>
            <td nowrap="nowrap">
            </td>
          </tr>
        </tbody>
      </table>
      </td>
    </tr>
  </tbody>
</table>

<h2>Текст сообщения</h2>
{{read:callbackAdv}}


<? return 'Заказ звонка'; } ?>