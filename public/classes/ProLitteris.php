<?php

namespace Palasthotel\WordPress\LiveNews;

/**
 * @property Plugin $plugin
 */
class ProLitteris {

	public function __construct( Plugin $plugin) {
		$this->plugin = $plugin;
	}

	public function isMessageTheContent(){
		return function_exists('pro_litteris_is_message_the_content') && pro_litteris_is_message_the_content();
	}

}