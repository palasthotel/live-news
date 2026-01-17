<?php

namespace Palasthotel\WordPress\LiveNews;

class ProLitteris {

    public Plugin $plugin;

	public function __construct( Plugin $plugin) {
		$this->plugin = $plugin;
	}

	public function isMessageTheContent(){
		return function_exists('pro_litteris_is_message_the_content') && pro_litteris_is_message_the_content();
	}

}
