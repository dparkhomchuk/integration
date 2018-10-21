<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 16.05.2018
 * Time: 21:53
 */

class ContactForm7ScriptBuilder extends BPMonlineScriptBuilder
{
	protected function getFormId() {
		$secondindex = strpos($this->post_data,'-', 7);
		$length = $secondindex - 7;
		return substr($this->post_data, 7,$length);
	}

	protected function getSubmitCSSSelector() {
		return ".wpcf7-form";
	}

}