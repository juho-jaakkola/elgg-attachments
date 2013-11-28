<?php

$label = elgg_echo('attachments:search');
$search = elgg_view('input/autocomplete', array(
	'name' => 'attachment_guid',
	'match_on' => elgg_get_config('attachments_entity_subtypes'),
));
$desc = elgg_echo('attachments:warning:access_right');
$submit = elgg_view('input/submit', array('value' => elgg_echo('add')));
$guid = elgg_view('input/hidden', array(
	'name' => 'guid',
	'value' => $vars['guid']
));

echo <<<HTML
	<div>
		<label>$label</label>
		$search
		<span class="elgg-text-help">$desc</span>
	</div>
	<div>
		$submit
		$guid
	</div>
HTML;
