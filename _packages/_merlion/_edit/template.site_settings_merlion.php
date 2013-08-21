<?
function site_settings_merlion_update(&$ini){
	$cron = getValue('merlionCron');
	if (!is_array($cron)) $cron = array();
	$ini[':merlion']['cronSynch'] = implode(',',$cron);
}
function site_settings_merlion($ini){ ?>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap">Логин</td>
    <td><input type="text" name="settings[:merlion][login]" class="input" value="{$ini[:merlion][login]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Код</td>
    <td><input type="text" name="settings[:merlion][code]" class="input" value="{$ini[:merlion][code]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Пароль</td>
    <td><input type="text" name="settings[:merlion][passw]" class="input" value="{$ini[:merlion][passw]}" /></td>
  </tr>
</table>
<h3>Обновлять через CRON в эти часы:</h3>
<table border="0" cellspacing="2" cellpadding="0">
  <tr>
<? for($h = 0; $h < 24; ++$h){ ?>
    <th align="center">{$h}</th>
<? } ?>
  </tr>
  <tr>
<?
$synch	=  $ini[':merlion']['cronSynch'];
$cron	= $synch?explode(',', $synch):array();
for($h = 0; $h < 24; ++$h){
	$class	= is_int(array_search($h, $cron))?'checked="checked"':'';
?>
    <td><input type="checkbox" name="merlionCron[]" value="{$h}" {!$class} /></td>
<? } ?>
  </tr>
</table>
<? return 'Merlion'; } ?>