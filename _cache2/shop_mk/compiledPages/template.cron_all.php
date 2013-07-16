<? function cron_all($val, $data){
	m('page:title', 'Задания');
	
	$crons = getCacheValue('cronWork');
	if (!is_array($crons)) $crons = array();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th nowrap>Разрешить</th>
    <th width="100%">Задание</th>
  </tr>
<? foreach($crons as $name => $command){ ?>
  <tr>
    <td>&nbsp;</td>
    <td><? if(isset($name)) echo htmlspecialchars($name) ?></td>
  </tr>
<? } ?>
</table>

<? } ?>
