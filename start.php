<?php

elgg_register_event_handler('init', 'system', 'attachments_init');

/**
 * Initialize the plugin
 */
function attachments_init() {
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'attachments_entity_menu_setup');
	elgg_register_plugin_hook_handler('route', 'livesearch', 'attachments_live_search');
	elgg_register_plugin_hook_handler('view', 'object/elements/summary', 'attachments_view');

	elgg_register_page_handler('attachments', 'attachments_page_handler');

	$actions_path = elgg_get_plugins_path() . 'attachments/actions/attachments/';
	elgg_register_action('attachments/add', "{$actions_path}add.php");
	elgg_register_action('attachments/remove', "{$actions_path}remove.php");

	elgg_set_config('attachments_entity_subtypes', array('blog', 'page_top', 'page', 'event_calendar'));

	elgg_extend_view('css/elgg', 'attachments/css');
}

/**
 * Add attachment link to entity menu
 * 
 * @param string         $hook   Hook name
 * @param string         $type   Hook type
 * @param ElggMenuItem[] $return Array of ElggMenuItem objects
 * @param array          $params Array of menu parameters
 * @return ElggMenuItem[] $return Array of ElggMenuItem objects
 */
function attachments_entity_menu_setup($hook, $type, $return, $params) {
	if (!elgg_is_logged_in()) {
		return $return;
	}

	$entity = $params['entity'];

	// Add the link only for users who can edit
	if (!$entity->canEdit()) {
		return $return;
	}

	if (in_array($entity->getSubtype(), elgg_get_config('attachments_entity_subtypes'))) {
		$return[] = ElggMenuItem::factory(array(
			'name' => 'attachment_add',
			'text' => elgg_view_icon('clip'),
			'title' => elgg_echo('attachments:add'),
			'href' => "attachments/add/{$entity->guid}",
			'priority' => 200,
		));
	}

	return $return;
}

/**
 * Handles requests to /attachments/*
 * 
 * @param array $page Url segments
 * @return boolean
 */
function attachments_page_handler($page) {
	if (!isset($page[1])) {
		register_error(elgg_echo('noaccess'));
		forward(REFERER);
	}

	$guid = $page[1];

	$entity = get_entity($guid);
	if (!$entity) {
		register_error(elgg_echo('notfound'));
		forward(REFERER);
	}

	elgg_push_breadcrumb(elgg_echo("item:object:{$entity->getSubtype()}"), $entity->getSubtype());
	elgg_push_breadcrumb(elgg_echo($entity->title), $entity->getURL());
	elgg_push_breadcrumb(elgg_echo('attachments:add'));

	$content .= elgg_view_form('attachments/add', array('class' => 'mvl'), array('guid' => $guid));

	$params = array(
		'title' => elgg_echo('attachments:title:add'),
		'content' => $content,
		'filter' => '',
	);

	$body = elgg_view_layout('content', $params);
	echo elgg_view_page($params['title'], $body);
}

/**
 * 
 */
function attachments_live_search($hook, $type, $return, $params) {
	global $CONFIG;

	// only return results to logged in users.
	if (!$user = elgg_get_logged_in_user_entity()) {
		exit;
	}

	if (!$q = get_input('term', get_input('q'))) {
		exit;
	}

	$q = sanitise_string($q);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $q);

	$match_on = get_input('match_on', 'all');

	if (!is_array($match_on)) {
		$match_on = array($match_on);
	}

	// Let the default livesearch handle the request if using regular content types
	$supported_types = elgg_get_config('attachments_entity_subtypes');
	foreach ($match_on as $match) {
		if (!in_array($match, $supported_types)) {
			return $return;
		}
	}

	if (get_input('match_owner', false)) {
		$owner_where = 'AND e.owner_guid = ' . $user->getGUID();
	} else {
		$owner_where = '';
	}

	$limit = sanitise_int(get_input('limit', 10));

	// grab a list of entities and send them in json.
	$results = array();

	$dbprefix = elgg_get_config('dbprefix');
	$entities = elgg_get_entities(array(
		'type' => 'object',
		'subtypes' => $match_on,
		'limit' => 10,
		'joins' => "JOIN {$dbprefix}objects_entity oe ON e.guid = oe.guid",
		'wheres' => "oe.title LIKE '%$q%'",
	));

	elgg_push_context('widgets');
	foreach ($entities as $entity) {
		/*
		$output = elgg_view_list_item($entity, array(
			'use_hover' => false,
			'class' => 'elgg-autocomplete-item',
		));
		*/
		$vars = array(
			'entity' => $entity,
			'use_hover' => false,
			'class' => 'elgg-autocomplete-item',
		);
		$output = elgg_view('attachments/view', $vars);

		$icon = elgg_view_entity_icon($entity, 'tiny', array(
			'use_hover' => false,
		));

		$result = array(
			'type' => $entity->getSubtype(),
			'name' => $entity->name,
			'desc' => $entity->description,
			'guid' => $entity->guid,
			'label' => $output,
			'value' => $entity->guid,
			'icon' => $icon,
			'url' => $entity->getURL(),
		);
		$results[$entity->name . rand(1, 100)] = $result;
	}
	elgg_pop_context();

	ksort($results);
	header("Content-Type: application/json");
	echo json_encode(array_values($results));
	exit;
}

/**
 * Add attachements list to object summary
 * 
 * @param string $hook   Hook name
 * @param string $type   Hook type
 * @param string $return The view string
 * @param array  $params Array of view parameters
 * @return string $return The view string
 */
function attachments_view($hook, $type, $return, $params) {
	$full_view = $params['vars']['full_view'];
	$entity = $params['vars']['entity'];

	if ($full_view) {
		$return .= elgg_view('attachments/list', array('entity' => $entity));
	} else {
		// Hack for event_calendar plugin
		if (elgg_instanceof($entity, 'object', 'event_calendar')) {
			$return .= elgg_view('attachments/list', array('entity' => $entity));
		}
	}

	return $return;
}
