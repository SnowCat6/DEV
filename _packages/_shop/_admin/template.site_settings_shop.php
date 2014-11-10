<?
function site_settings_shop_update(&$ini)
{
	$rate	= $ini[':priceRate'];
	$rate	= (float)$rate['rate'];
	if ($rate <= 0) $rate = 1;
	
	$old	= getIniValue(':priceRate');
	if ($old['rate'] == $rate) return;
	
	$ini[':priceRate']['rate']	= $rate;
	clearCache();
}
function site_settings_shop($ini){
	if (!hasAccessRole('admin')) return;
	$rate	= priceRate();
?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Множитель цен</td>
    <td><input type="text" name="settings[:priceRate][rate]" value="{$rate}" class="input"></td>
  </tr>
  <tr>
    <td colspan="2"><em>Курс валюты относительно базовой цены</em></td>
  </tr>
</table>

<? return 'Магазин'; } ?>