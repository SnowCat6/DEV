﻿<? function script_calendar($val){ module('script:jq_ui'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" language="javascript">
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('[id*="calendar"], .calendar').each(function(){
			attachDatetimepicker($(this));
		});
	});
});
function attachDatetimepicker(o){
	o.datetimepicker({
		dateFormat: 	'dd.mm.yy',
		monthNames: 	['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort:['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сент','Окт','Ноя','Дек'],
		dayNamesMin: 	['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
		firstDay: 		1,
		timeOnlyTitle: 'Выберите время',
		timeText: 'Время',
		hourText: 'Часы',
		minuteText: 'Минуты',
		secondText: 'Секунды',
		currentText: 'Теперь',
		closeText: 'Закрыть'
		});
}
</script>
<? } ?>
