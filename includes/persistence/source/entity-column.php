<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.03.2018
 * Time: 22:04
 */


class EntityColumn
{

	//region Properties: Public

	private $name;

	public function setName($name) {
		$this -> name = $name;
	}

	public function getName() {
		return $this -> name;
	}

	private $type;

	public function setType($type) {
		$this -> type = $type;
	}

	public function getType() {
		return $this -> type;
	}

	//endregion

	//region Constructors

	function __construct($name, $type) {
		$this -> name = $name;
		$this -> type = $type;
	}

	//endregion

}