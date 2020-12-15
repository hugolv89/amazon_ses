<?php

return [
	'bootstrap' => \AmazonSES\Bootstrap::class,
	'hooks' => [
		'transport' => [
				'system:email' => [
						'\AmazonSES\Ses::transport' => [],
				],
		],
	],

];