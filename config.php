<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class AjaxConfig extends PluginConfig {
	function getOptions() {
		return [
			'ajax_submission_enable' => new BooleanField([
				'id' => 'ajax_submission_enable',
				'label' => 'Enable AJAX Ticket Submission',
				'configuration' => [
					'desc' => ''
				]
			]),
			'ajax_submission_captcha' => new ChoiceField([
				'id' => 'ajax_submission_captcha',
				'label' => 'Use Captcha',
				'choices' => ['None', 'osTicket', 'reCaptcha'],
				'configuration' => []
			]),
			'ajax_recaptcha_secret' => new TextboxField([
				'id' => 'ajax_recaptcha_secret',
				'label' => 'reCaptcha Secret Key',
				'configuration' => [
					'desc' => 'If using reCaptcha, provide the Secret Key here',
					'size' => 40,
				]
			]),
			'ajax_recaptcha_site' => new TextboxField([
				'id' => 'ajax_recaptcha_site',
				'label' => 'reCaptcha Site Key',
				'configuration' => [
					'desc' => 'If using reCaptcha, provide the Site Key here',
					'size' => 40,
				]
			]),
			'ajax_cors_header' => new TextboxField([
				'id' => 'ajax_cors_header',
				'label' => 'Allowed domains',
				'default' => '*',
				'configuration' => [
					'desc' => 'Domains that can use this AJAX endpoint. Use <tt>*</tt> for &quot;all&quot;.',
					'size' => 40
				]
			]),
		];
	}

	function pre_save(&$config, &$errors) {
		global $msg;

		if (!$errors)
			$msg = 'Configuration updated successfully';

		return true;
	}
}