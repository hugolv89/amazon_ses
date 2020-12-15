<?php

namespace AmazonSES;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	// http://learn.elgg.org/en/stable/guides/plugins/bootstrap.html
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
				
		$plugin = \elgg_get_plugin_from_id('amazon_ses');
	
		\putenv('AWS_REGION='.$plugin->AWS_REGION);
		
		\putenv('AWS_ACCESS_KEY_ID='.$plugin->AWS_ACCESS_KEY_ID);
		\putenv('AWS_SECRET_ACCESS_KEY='.$plugin->AWS_SECRET_ACCESS_KEY);
		
		if($plugin->AWS_SESSION_TOKEN != ''){
			
			\putenv('AWS_SESSION_TOKEN='.$plugin->AWS_SESSION_TOKEN);
		}
		
	}
	
}