<? function prop_undo($db, $docID, $data)
{
	module("prop:delete:$docID");
	module("prop:set:$docID", 	$data);
	return true;
}?>