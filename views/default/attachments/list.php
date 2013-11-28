<?php

$parent = $vars['entity'];

$entities = elgg_get_entities_from_relationship(array(
	'type' => 'object',
	'subtype' => elgg_get_config('attachments_entity_subtypes'),
	'relationship' => 'attachment',
	'relationship_guid' => $parent->guid,
	'inverse_relationship' => true,
));

$list = '';
foreach ($entities as $entity) {
	$list .= elgg_view('attachments/view', array(
		'entity' => $entity,
		'parent' => $parent,
	));
}

if ($list) {
	$count = count($entities);

	$link = elgg_view('output/url', array(
		'href' => "#elgg-attachments-{$guid}",
		'text' => elgg_view_icon('clip') . elgg_echo('attachments:count', array($count)),
		'class' => 'elgg-attachments-link',
		'rel' => 'toggle',
	));

	echo <<<HTML
		$link
		<div id="elgg-attachments-{$guid}" class="elgg-border-plain hidden pal mvm">$list</div>
HTML;
}
