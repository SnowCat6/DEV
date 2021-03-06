<?
//	+function site_settings_callbackAdv
function site_settings_callbackAdv()
{
	$def	= getCacheValue(':callbackAdv');
	$ini	= getIniValue(':feedbackAdv');
	foreach($def as $name=>$v){
		if (!isset($ini[$name])) $ini[$name] = $v;
	}

	module("admin:tab:callbackAdvTab", $ini);	
?>

{{script:jq}}
<script src="../script/jqCallbackAdmin.js"></script>
<? return 'Заказ звонка'; } ?>


<?
//	+function callbackAdvTab_design
function callbackAdvTab_design($ini)
{
?>
<link rel="stylesheet" type="text/css" href="../css/callbackAdv.css">


<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="25%" valign="top">
      
<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tbody>
          <tr>
            <td nowrap="nowrap">Цвет фона</td>
            <td width="100%" nowrap="nowrap">
            	<input type="text" name="settings[:feedbackAdv][bkColor]" class="input w100" rel="advStyle:background" value="{$ini[bkColor]}" placeholder="{$def[bkColor]}">
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap">Цвет текста</td>
            <td nowrap="nowrap">
            	<input type="text" name="settings[:feedbackAdv][txColor]" class="input w100" rel="advStyle:color" value="{$ini[txColor]}" placeholder="{$def[txColor]}">
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
      <td valign="top">
      
<div class="callbackAdvAdmin">
<!--- ------------------- -->
{{callbackAdvContent}}
<!--- ------------------- -->
</div>
      
      </td>
    </tr>
  </tbody>
</table>


<? return 'Оформление'; } ?>

<?
//	+function callbackAdvTab_settings
function callbackAdvTab_settings($ini)
{
	$days	= explode(',', 'Понедельник,Вторник,Среда,Четверг,Пятница,Суббота,Воскресение');
?>

{{script:splitInput}}
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="50%" valign="top">
      
<b>Настройка уведомления</b>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td nowrap="nowrap">E-mail для SMS</td>
      <td width="100%">
          <input name="settings[:feedbackAdv][mailSMS]" type="text" class="input w100 splitInput" placeholder="{$def[mailSMS]}" value="{$ini[mailSMS]}" /> 
      </td>
    </tr>
    <tr>
      <td nowrap="nowrap">Время показа (сейчас {{date:%H:%i=now}} )</td>
      <td><input name="settings[:feedbackAdv][timeFrom]" type="text" class="input" value="{$ini[timeFrom]}" size="5">
до
<input name="settings[:feedbackAdv][timeTo]" type="text" class="input" value="{$ini[timeTo]}" size="5"></td>
    </tr>
    <tr>
      <td nowrap="nowrap">Дни показа:

от </td>
      <td><select class="input" name="settings[:feedbackAdv][dayFrom]">
	<option value="">-----------------</option>
<? foreach($days as $ix => $n){ ++$ix; ?>
	<option value="{$ix}" {selected:$ix==$ini[dayFrom]}>{$n}</option>
<? } ?>
</select>

до
<select class="input" name="settings[:feedbackAdv][dayTo]">
	<option value="">-----------------</option>
<? foreach($days as $ix => $n){ ++$ix; ?>
	<option value="{$ix}" {selected:$ix==$ini[dayTo]}>{$n}</option>
<? } ?>
</select>
      </td>
    </tr>
  </tbody>
</table>
      
      </td>
      <td width="50%" valign="top">
      
<b>Настройка контента</b>
<table border="0" cellspacing="0" cellpadding="2">
  <tbody>
    <tr>
      <td>Время первого напоминания</td>
      <td nowrap="nowrap">
          <input name="settings[:feedbackAdv][timeout1]" type="text" class="input" placeholder="{$def[timeout1]}" value="{$ini[timeout1]}" size="5">
          сек.
      </td>
    </tr>
    <tr>
      <td>Время второго напоминания</td>
      <td nowrap="nowrap">
        <input name="settings[:feedbackAdv][timeout2]" type="text" class="input" placeholder="{$def[timeout2]}" value="{$ini[timeout2]}" size="5">
        сек.
      </td>
    </tr>
    <tr>
      <td>Время через которое показывать повторно</td>
      <td nowrap="nowrap">
        <input name="settings[:feedbackAdv][timeout3]" type="text" class="input" placeholder="{$def[timeout3]}" value="{$ini[timeout3]}" size="5"> 
        мин.
      </td>
    </tr>
  </tbody>
</table>
      
      </td>
    </tr>
  </tbody>
</table>

<? return 'Настройки уведомления'; } ?>
