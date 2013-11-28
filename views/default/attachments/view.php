<?php
/**
 * Display an attachment as a list item
 */

$entity = elgg_extract('entity', $vars);
$parent = elgg_extract('parent', $vars);

$owner = $entity->getOwnerEntity();

$icon = elgg_view_entity_icon($owner, 'tiny');

$date = elgg_view_friendly_time($entity->time_created);

$subtitle = "$owner->name $date";

$subtype = elgg_echo("item:object:{$entity->getSubtype()}");

$title = elgg_view('output/url', array(
	'href' => $entity->getURL(),
	'text' => "<h4>{$subtype}: $entity->title</h4>",
));

$metadata = '';
if (!elgg_in_context('widgets') && $parent && $parent->canEdit()) {
	$metadata = elgg_view("output/confirmlink", array(
		'href' => "action/attachments/remove?guid={$parent->guid}&attachment_guid={$entity->getGUID()}",
		'text' => elgg_echo('attachments:unlink'),
		'confirm' => elgg_echo('question:areyousure'),
		'class' => 'float-alt elgg-subtext',
	));
}

$params = array(
	'entity' => $entity,
	'title' => false, //$title,
	'subtitle' => $title . $subtitle,
	'metadata' => $metadata,
	'tags' => false,
);
$params = $params + $vars;
$body = elgg_view('object/elements/summary', $params);

echo elgg_view_image_block($icon, $body, $vars);
