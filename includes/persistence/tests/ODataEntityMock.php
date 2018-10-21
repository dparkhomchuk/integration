<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 22:05
 */

class ODataEntityPropertyMock
{

	//region Properties: Public

	private $name;
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}

	private $value;
	public function setValue($value) {
		$this->value = $value;
	}
	public function getValue() {
		return $this->value;
	}

	//endregion

}

class ODataEntityMock
{
	//region Properties: Public
	private $properties;
	public function setProperties($properties) {
		$this -> properties = $properties;
	}
	public function getProperties() {
		if (null === $this->properties) {
			$this->properties=[];
		}
		return $this->properties;
	}
	//endregion
}

class ODataBaseEntityMock
{
	//region Static Methods: Public

	public static function get_base_test_entity($nameValue, $idValue) {
		$name_property = new ODataEntityPropertyMock();
		$name_property->setName('Name');
		$name_property->setValue($nameValue);
		$id_property = new ODataEntityPropertyMock();
		$id_property->setName('Id');
		$id_property->setValue($idValue);
		$entity = new ODataEntityMock();
		$entity_properties = [];
		array_push($entity_properties, $name_property);
		array_push($entity_properties, $id_property);
		$entity ->setProperties($entity_properties);
		return $entity;
	}

	public static function get_landing_page_test_entity($name_value, $id_value, $type_id_value) {
		$entity = ODataBaseEntityMock :: get_base_test_entity($name_value, $id_value);
		$type_property = new ODataEntityPropertyMock();
		$type_property->setName('TypeId');
		$type_property->setValue($type_id_value);
		$entity_properties = $entity -> getProperties();
		array_push($entity_properties, $type_property);
		$entity -> setProperties($entity_properties);
		return $entity;
	}


	//endregion
}