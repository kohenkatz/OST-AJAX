<?php

require_once(INCLUDE_DIR . 'class.plugin.php');
require_once(INCLUDE_DIR . 'class.signal.php');
require_once(INCLUDE_DIR . 'class.app.php');

require_once('config.php');

class AjaxPlugin extends Plugin {
	var $config_class = 'AjaxConfig';

	public function bootstrap() {
		$config = $this->getConfig();

		if ($config->get('ajax_submission_enable')) {
			Signal::connect('ajax.client', ['AjaxPlugin', 'registerDispatch']);
		}
	}

	public static function registerDispatch($dispatcher, $data) {
		$form_url = url('^/ajax-form/', patterns(
			'AjaxFormController.php:AjaxFormController',
			url_get('^open', 'get_open'),
			url_post('^submit', 'post_submit')
		));

		$dispatcher->append($form_url);
	}
}
