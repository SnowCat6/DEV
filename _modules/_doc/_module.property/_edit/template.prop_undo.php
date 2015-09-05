<? function prop_undo($db, $docID, $data)
{
	$undo	= module("prop:get:$docID");
	undo::add("Свойства $docID изменены", "prop:$docID",
		array('action' => "prop:undo:$docID", 'data' => $undo)
	);

	undo:lock();
	module("prop:delete:$docID");
	module("prop:set:$docID", 	$data);
	undo:unlock();
	
	return true;
}?>