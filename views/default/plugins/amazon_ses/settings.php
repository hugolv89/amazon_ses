<?php

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);


echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('amazon_ses:settings:region'),
	'#help' => elgg_echo('amazon_ses:settings:region:description', [elgg_get_site_entity()->getEmailAddress()]),
	'name' => 'params[AWS_REGION]',
	'value' => $plugin->AWS_REGION,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('amazon_ses:settings:access_key_id'),
	'#help' => elgg_echo('amazon_ses:settings:access_key_id:description', [elgg_get_site_entity()->getEmailAddress()]),
	'name' => 'params[AWS_ACCESS_KEY_ID]',
	'value' => $plugin->AWS_ACCESS_KEY_ID,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('amazon_ses:settings:secret_access_key'),
	'#help' => elgg_echo('amazon_ses:settings:secret_access_key:description', [elgg_get_site_entity()->getEmailAddress()]),
	'name' => 'params[AWS_SECRET_ACCESS_KEY]',
	'value' => $plugin->AWS_SECRET_ACCESS_KEY,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('amazon_ses:settings:session_token'),
	'#help' => elgg_echo('amazon_ses:settings:session_token:description', [elgg_get_site_entity()->getEmailAddress()]),
	'name' => 'params[AWS_SESSION_TOKEN]',
	'value' => $plugin->AWS_SESSION_TOKEN,
]);