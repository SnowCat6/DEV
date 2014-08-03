<? function admin_panel_info(&$val)
{
	$pf	= array();
	$pf['Memcache']	= (class_exists('Memcache', false))?'OK':'Failed';
	$pf['fastcgi_finish_request']	= function_exists('fastcgi_finish_request')?'OK':'Failed';

	$cf	= array();
	$cf['PHAR']	= (class_exists('Phar', false))?'OK':'Failed';
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%"><h2 class="ui-state-default">Производительность</h2></td>
    <td width="33%"><h2 class="ui-state-default">Удобства</h2></td>
    <td width="33%"><h2 class="ui-state-default">Обновление</h2></td>
  </tr>
  <tr>
    <td valign="top"><? showServerInfo($pf)?></td>
    <td valign="top" style="padding:0 10px"><? showServerInfo($cf)?></td>
    <td valign="top">&nbsp;</td>
  </tr>
</table>

<? return 'Информация'; }?>
<? function showServerInfo(&$pf){ ?>
<? foreach($pf as $name=>$val){
	$style	= $val=='OK'?' style="float:right;color:green"':' style="float:right;color:red"';
?>
<div>{$name} <span {!$style}>{$val}</span></div>
<? } ?>
<? } ?>