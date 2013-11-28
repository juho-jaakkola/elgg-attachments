<?php

$guid = get_input('guid');
$attachment_guid = get_input('attachment_guid');

$entity = get_entity($guid);
$attachment_entity = get_entity($attachment_guid);

if (!$entity || !$attachment_entity) {
	register_error(elgg_echo('actionunauthorized'));
	forward(REFERER);
}

if (add_entity_relationship($attachment_guid, 'attachment', $guid)) {
	system_message(elgg_echo('attachments:add:success'));
} else {
	register_error(elgg_echo('attachments:add:error'));
}

forward($entity->getURL());