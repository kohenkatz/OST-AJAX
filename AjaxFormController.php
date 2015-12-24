<?php

require_once(__dir__.DIRECTORY_SEPARATOR.'config.php');
require_once(__dir__.DIRECTORY_SEPARATOR.'Encryption.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class AjaxFormController {

	private $config;

	public function __construct() {
		$this->config = new AjaxConfig();
	}

	public function get_open() {
		global $ost, $cfg;

		define('MY_DEBUG', isset($_GET['debug']) && ($_GET['debug'] === '1'));

		$topic_names = Topic::getPublicHelpTopics();
		if (isset($_GET['topics'])) {
			$allowed_topics = [];
			if (is_array($_GET['topics'])) {
				$allowed_topics = $_GET['topics'];
			} else {
				$allowed_topics = [$_GET['topics']];
			}

			$topic_names = array_filter($topic_names, function($name, $id) use ($allowed_topics) {
				return in_array($id, $allowed_topics) || in_array($name, $allowed_topics);
			}, ARRAY_FILTER_USE_BOTH);
		}

		$formPrefix = $this->config->get('ajax_submission_form_prefix');

		$csrf_token = $ost->getCSRF()->getToken();

		$topics = [];
		foreach ($topic_names as $id => $name) {
			if (($topic = Topic::lookup($id)) &&
				($form = $topic->getForm()) &&
				($fields = $form->getForm()->getFields())) {
					$topics[$id] = [
						'name' => $name,
						'instructions' => $form->get('instructions'),
						'fields' => [],
					];
					foreach ($fields as $field) {
						$topics[$id]['fields'][$field->get('name')] = [
							'name' => $formPrefix.$field->get('name'),
							'type' => $field->get('type'),
						];
						if ($field->get('type') === 'choices') {
							$topics[$id]['fields'][$field->get('name')]['choices'] = $field->getChoices();
						}
					}
			}
		}

		$captcha = null;
		$useCaptcha = $this->config->get('ajax_submission_captcha');
		if ($useCaptcha === 1) { // built-in
			$captcha = [
				'type' => 'osTicket',
				// FILL IN
			];
		} elseif ($useCaptcha === 2) { // recaptcha
			$public = $this->config->get('ajax_recaptcha_site');
			$private = $this->config->get('ajax_recaptcha_secret');
			if ($public && $private) {
				// Key creation based on https://github.com/google/recaptcha-java/blob/master/appengine/src/main/java/com/google/recaptcha/STokenUtils.java
				$encrypter = new Encryption(substr(hash('sha1', $private, true), 0, 16));
				$secure_token = json_encode([
					// PHP unique ID prefixed with the csrf token
					'session_id' => uniqid($csrf_token),
					'ts_ms' => bcmul(microtime(true), 1000, 0),
				]);
				$captcha = [
					'type' => 'reCaptcha',
					'public_key' => $public,
					'secure_token' => $encrypter->encrypt_aes_ecb_pkcs5($secure_token),
				];
			} else {
				throw new Exception('reCaptcha key not provided!');
			}
		}

		$return = [
			'method' => 'POST',
			'submit_url' => self::baseURL() . '/ajax.php/ajax-form/submit',
			'form_groups' => [
				'backend'       => ['hidden' => true, fields => ['csrf', 'action',],],
				'topic'         => ['legend' => 'Help Topic', fields => ['topicId',],],
				'user'          => ['legend' => 'Contact Information', fields => ['name', 'email', 'phone',],],
				'topic_details' => ['dynamic' => true, 'id' => $formPrefix.'topic_details'],
				'ticket'        => ['legend' => 'Ticket Details', fields => ['summary', 'details',],],
			],
			'form_fields' => [
				'csrf' => [
					'type' => 'hidden',
					'value' => $csrf_token,
					'name' => $formPrefix.$ost->getCSRF()->getTokenName(),
				],
				'action' => [
					'type' => 'hidden',
					'value' => 'open',
					'name' => $formPrefix.'a',
				],
				'topicId' => [
					'label' => 'Select a Topic',
					'required' => true,
					'type' => 'choices',
					'name' => $formPrefix.'topicId',
					'choices' => $topic_names,
				],
				'name' => [
					'label' => 'Full Name',
					'required' => true,
					'type' => 'text',
					'name' => $formPrefix.'name',
				],
				'email' => [
					'label' => 'Email Address',
					'required' => true,
					'type' => 'text',
					'name' => $formPrefix.'email',
				],
				'phone' => [
					'label' => 'Phone Number',
					'required' => true,
					'type' => 'text',
					'name' => $formPrefix.'phone',
				],
				'summary' => [
					'label' => 'Issue Summary',
					'required' => true,
					'type' => 'text',
					'name' => $formPrefix.'summary',
				],
				'details' => [
					'label' => 'Issue Details',
					'required' => true,
					'type' => 'textarea',
					'name' => $formPrefix.'details',
				],
			],
			'topics' => $topics,
			'captcha' => $captcha,
		];

		header('Access-Control-Allow-Origin: '. $this->config->get('ajax_cors_header'));

		$json_flags = 0;
		if (MY_DEBUG) {
			$json_flags += JSON_PRETTY_PRINT;
		}

		return json_encode($return, $json_flags);
	}

	public function post_submit() {

	}

	// Based on osTicket file: include/class.misc.php
	private function baseURL() {
		$str = 'http';
		if ($_SERVER['HTTPS'] == 'on') {
			$str .='s';
		}
		$str .= '://';
		if (!isset($_SERVER['REQUEST_URI'])) { //IIS???
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
			if (isset($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
			}
		}
		if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
			$str .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
		} else {
			$str .= $_SERVER['SERVER_NAME'];
		}
		return $str;
	}

}