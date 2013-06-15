<?
function module_feedback($fn, $data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("feedback_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function getFormFeedbackType($data){
	$types = getFormFeedbackTypes();
	foreach($types as $name => $type){
		if (isset($data[$type])) return $type;
	}
}
function getFormFeedbackTypes(){
	$types = array();
	$types['Текстовое поле']= 'text';
	$types['Список выбора']	= 'select';
	$types['Чекбоксы']		= 'checkbox';
	$types['Радиоконпки']	= 'radio';
	$types['Поле ввода текста'] = 'textarea';
	$types['Адрес эл. почты']	= 'email';
	$types['Телефон']		= 'phone';
	return $types;
}
?>