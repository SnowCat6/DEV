<?
//	+function module_callbackAdvForm
function module_callbackAdvForm($val, $data)
{
	if (userID() && !hasAccessRole('user')) return;

	setTemplate('');
	
	$phone			= trim(getValue('callbackAdvPhone'));
	if (!$phone) return;
	
	$mail['html']	= "Телефон: " . htmlspecialchars($phone);
	$mail['plain']	= "Телефон: $phone";
	$mail['SMS']	= "Телефон: $phone";

	$template	  	= module("mail:template", 'feedback');
	m("mail:send:::$template:Заказ обратного звонка", $mail);
} ?>

<? function module_callbackAdv($val, $data)
{
	if (userID() && !hasAccessRole('user')) return;
?>

{{script:jq}}
<link rel="stylesheet" type="text/css" href="css/callbackAdv.css">
<script type="text/javascript" src="script/jq.callbackAdv.js"></script>

<div class="callbackAdvHolder" style="display:none">
<iframe name="callbackAdvFrame" style="display:none"></iframe>
	<form action="{{url:callbackAdv}}" method="post" target="callbackAdvFrame">
<? module_callbackAdvContent($val, $data) ?>
    </form>
</div>

<? } ?>

<?
// +function module_callbackAdvContent
function module_callbackAdvContent($val, $data)
{
	$def	= getCacheValue(':callbackAdv');
	$ini	= getIniValue(':feedbackAdv');
	foreach($def as $name => $v){
		if (strlen($ini[$name]) == 0) $ini[$name] = $v;
	}
	
	$style	= array();
	if ($ini['bkColor']) $style['background']	= $ini['bkColor'];
	if ($ini['txColor']) $style['color']		= $ini['txColor'];
	
	$style	= makeStyle($style);
?>
<script>
var callbackAdvTimeout = <?= (int)$ini['timeout1'] ?>;
var callbackAdvTimeout2= <?= (int)$ini['timeout2'] ?>;
var callbackAdvTimeout3= <?= (int)$ini['timeout3']*60 ?>;
</script>

        <div class="callbackAdv" {!$style}>
            <span class="callbackAdvClose">ЗАКРЫТЬ</span>
<center>
<? ob_start() ?>
            <h1>НЕ НАШЛИ, ЧТО ИСКАЛ?</h1>
            
            <p>Получите выгоду, позвоните нам!</p>
        
            <p>Оставте номер телефона, мы позвоним через минуту!</p>
<?
$cfg	= array('default' => ob_get_clean());
module('read:callbackAdv', $cfg);
?>
</center>
            
            <div class="callbackAdvPhone">
                <div class="input"><input type="text" placeholder="ВАШ ТЕЛЕФОН" name="callbackAdvPhone"></div>
            </div>
            
            <center>
            	нажмите ENTER для отправки телефона
            </center>
        </div>
<? } ?>
