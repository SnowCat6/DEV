<?
//	+function holderInput_default_update
function holderInput_default_update($holder, $name, $val){
	return $val;
}
//	+function holderInput_default
function holderInput_default($holder, $name, $cfg){ ?>
   	<input type="text" class="input w100" name="widgetConfig[{$name}]" value="{$cfg[value]}" placeholder="{$cfg[default]}" />
<? }?>

<?
//	+function holderInput_textarea
function holderInput_textarea($holder, $name, $cfg){ ?>
   	<textarea rows="8" class="input w100" name="widgetConfig[{$name}]" placeholder="{$cfg[default]}">{$cfg[value]}</textarea>
<? }?>

<?
//	+function holderInput_html
function holderInput_html($holder, $name, $cfg){ ?>
	{{editor}}
   	<textarea rows="15" class="input w100 editor" name="widgetConfig[{$name}]" placeholder="{$cfg[default]}">{$cfg[value]}</textarea>
<? }?>

<?
//	+function holderInput_select_update
function holderInput_select_update($holder, $name, $val){
	return $val;
}
//	+function holderInput_select
function holderInput_select($holder, $name, $cfg){ ?>
<select name="widgetConfig[{$name}]" class="input w100">
	<option value="">-- {$cfg[default]} --</option>
<? foreach(explode(',', $cfg['select']) as $value){ ?>
	<option value="{$value}"{selected:$value==$cfg[value]}>{$value}</option>
<? } ?>
</select>
<? }?>


<?
//	+function holderInput_checkbox
function holderInput_checkbox($holder, $name, $cfg){ ?>
   	<input type="hidden" name="widgetConfig[{$name}]" value="{$cfg[default]}"  />
   	<input type="checkbox" name="widgetConfig[{$name}]" value="{$cfg[checked]}" {checked:$cfg[value]==$cfg[checked]}  />
<? } ?>

