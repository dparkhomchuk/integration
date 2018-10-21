<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.03.2018
 * Time: 22:25
 */

class BPMOnlineDataStructure
{

	//region Properties

	private $landingTypes;

	public function setLandingTypes($landingTypes) {
		$this -> landingTypes = $landingTypes;
	}

	public function getLandingTypes() {
		return $this -> landingTypes;
	}

	//endregion


	//region Methods

	public function getJSON() {
		$script = "Terrasoft ={};";
		foreach ($this -> landingTypes as $key => $value) {
			$script = $script . "Terrasoft['" . $key . "']=";
			$script = $script . $value ->getJSON();
			$script = $script . ";";
		}
		return $script;
	}

	//endregion

}