<? function module_findTour($val, $data)
{
	$fn = getFn("findTour_$val");
	return $fn?$fn($val, $data):NULL;
}?>

