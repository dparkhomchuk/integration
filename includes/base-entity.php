<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 21:47
 */

//region Class: BaseEntity

class BaseEntity
{

	//region Properties: Public

	private $id;
	public function get_id() {
		return $this->id;
	}

	private $name;
	public function get_name() {
		return $this->name;
	}

	//endregion

	//region Constructors: Public

	function __construct($odataEntity) {
		foreach ($odataEntity->getProperties() as $property) {
			if ($property->getName() == 'Id') {
				$this->id = $property->getValue();
			}
			if ($property->getName() == 'Name') {
				$this->name = $property->getValue();
			}
		}
	}

	//endregion

	//region Methods: Protected

	protected function getJSONFields() {
		$json_fields = "\"Id\":\"" . strval($this -> get_id()) . "\",";
		$json_fields = $json_fields . "\"Name\":\"" . strval($this -> get_name()) . "\"";
		return $json_fields;
	}

	//endregion

	//region Methods: Public

	public function getJSON() {
		$json =  "{";
		$json = $json . $this->getJSONFields();
		$json = $json . "}";
		return $json;
	}

	//endregion

}

//endregion