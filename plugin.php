<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__));

return array(
	'id' => 'ymkatz:ajax',
	'version' => '0.1',
	'name' => 'AJAX Ticket Submission',
	'author' => 'YMKatz',
	'description' => 'Provides the ability to embed a ticket submission form in another website.',
	'url' => 'https://github.com/kohenkatz/OST-AJAX',
	'plugin' => 'ajax.php:AjaxPlugin'
);