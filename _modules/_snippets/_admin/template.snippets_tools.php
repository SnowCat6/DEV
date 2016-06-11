<? function snippets_tools($val, $data)
{
	m('script:jq');
?>
<link rel="stylesheet" type="text/css" href="css/adminSnippets.css">
<script src="script/adminSnippets.js"></script>

<div class="snippetEdit">
<div class="snippetEditButton">Сниппеты</div>
<div class="snippetEditHolder shadow">

<? foreach(snippetsWrite::get() as $name => $code){ ?>
    <a href="#">{$name}</a>
<? } ?>

</div>
</div>
<? } ?>
