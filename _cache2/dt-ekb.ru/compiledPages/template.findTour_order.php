<? function findTour_order($val, $data)
{
	$booklink	= getValue('booklink');
	if (!$booklink) return;

	setTemplate('findTour');
	$data	= array();
	$data[':']['url']	= getURL('#', "booklink=".urlencode($booklink));
	$data['Карточка товара']['hidden']	= $booklink;
?>
<? module("page:style", '../../style.css') ?>
<style>
.feedback p{ margin:0;}
</style>
<? module('feedback:display:order', $data)?>
<? } ?>
