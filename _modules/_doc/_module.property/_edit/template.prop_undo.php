<? function prop_undo($db, $docID, $data)
{
	$undo	= module("prop:get:$docID");
	addUndo("Redo property document $docID", "prop:$docID",
		array('action' => "prop:undo:$docID", 'data' => $undo)
	);

	lockUndo();
	module("prop:delete:$docID");
	module("prop:set:$docID", 	$data);
	unlockUndo();
	
	return true;
}?>