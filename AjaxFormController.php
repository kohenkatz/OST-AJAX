<?php

require_once(__dir__.DIRECTORY_SEPARATOR.'config.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class AjaxFormController {

	private $config;

	public function __construct() {
		$this->config = new AjaxConfig();
	}

	public function get_open() {
		global $ost;

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

		$topics = [];

		$captcha = [];

		$return = [
			'method' => 'POST',
			'submit_url' => self::baseURL() . '/ajax.php/ajax-form/submit',
			'form_groups' => [
				'backend'       => ['hidden' => true, fields => ['csrf', 'action',],],
				'topic'         => ['legend' => 'Help Topic', fields => ['topicId',],],
				'user'          => ['legend' => 'Contact Information', fields => ['name', 'email', 'phone',],],
				'topic_details' => ['dynamic' => true, 'id' => 'ost_ajaxform_topic_details'],
				'ticket'        => ['legend' => 'Ticket Details', fields => ['summary', 'details',],],
			],
			'form_fields' => [
				'csrf' => [
					'type' => 'hidden',
					'value' => $ost->getCSRF()->getToken(),
					'name' => $ost->getCSRF()->getTokenName(),
				],
				'action' => [
					'type' => 'hidden',
					'value' => 'open',
					'name' => 'a',
				],
				'topicId' => [
					'required' => true,
					'type' => 'select',
					'name' => 'topicId',
					'options' => $topic_names,
				],
				'name' => [
					'required' => true,
					'type' => 'text',
					'name' => 'name',
				],
				'email' => [
					'required' => true,
					'type' => 'text',
					'name' => 'email',
				],
				'phone' => [
					'required' => true,
					'type' => 'text',
					'name' => 'phone',
				],
				'summary' => [
					'required' => true,
					'type' => 'text',
					'name' => 'summary',
				],
				'details' => [
					'required' => true,
					'type' => 'textarea',
					'name' => 'details',
				],
			],
			'topics' => $topics,
			'captcha' => $captcha,
		];

		header('Access-Control-Allow-Origin: '. $this->config->get('ajax_cors_header'));

		return json_encode($return);
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