<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Anthology',
	'description' => 'Plugins and helpers for listing and filtering TYPO3 record based content',
	'category' => 'plugin',
	'author' => 'Chris Tebbit',
	'author_email' => 'chris@liquidlight.co.uk',
	'author_company' => 'Liquid Light Ltd',
	'state' => 'stable',
	'version' => '1.2.0',
	'constraints' => [
		'depends' => [
			'typo3' => '13.4.0-13.4.99',
			'php' => '8.2.0-8.4.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
