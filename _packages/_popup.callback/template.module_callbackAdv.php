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

{{script:jq}}
<link rel="stylesheet" type="text/css" href="css/callbackAdv.css">
<script type="text/javascript" src="script/jq.callbackAdv.js"></script>

<script>
var callbackAdvTimeout = <?= (int)$ini['timeout1'] ?>;
var callbackAdvTimeout2= <?= (int)$ini['timeout2'] ?>;
var callbackAdvTimeout3= <?= (int)$ini['timeout3'] ?>;
</script>

<div class="callbackAdvHolder" style="display:none">
<iframe name="callbackAdvFrame" style="display:none"></iframe>
	<form action="{{url:callbackAdv}}" method="post" target="callbackAdvFrame">
        <div class="callbackAdv">
            <a class="callbackAdvClose" href="{{url:#}}">ЗВКРЫТЬ</a>
<center>
<? ob_start() ?>
            <h1>НЕ НАШЕЛ, ЧТО ИСКАЛ?</h1>
            
            <div class="callbackAdvNote">
                Получи выгоду, позвони нам!
            </div>
            
            <div class="callbackAdvNote">
                Оставте номер телефона, мы позвоним и договоримся!
            </div>
<?
$cfg	= array('default' => ob_get_clean());
module('read:callbackAdv', $cfg);
?>
</center>
            
            <div class="callbackAdvPhone">
                <div class="input"><input type="text" placeholder="НАЖМИТЕ ENTER" name="callbackAdvPhone"></div>
            </div>
            
            <center>
            	нахмите ENTER для отправки телефона
            </center>
        </div>
    </form>
</div>

<? } ?>
