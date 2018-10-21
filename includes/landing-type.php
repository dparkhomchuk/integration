<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 22:59
 */

class LandingType extends BaseEntity
{

	//region Properties: Public

	private $landings;
	public function getLandings() {
		if (null !== $this -> landings) {
			return $this -> landings;
		} else {
			$this -> landings = [];
			return $this -> landings;
		}
	}
	public function set_langings($landings) {
		$this -> landings = $landings;
	}

	private $entitySchemaFields;
	public function getEntitySchemaFields() {
		if (null !== $this -> entitySchemaFields) {
			return $this -> entitySchemaFields;
		} else {
			$this -> entitySchemaFields = [];
			return $this -> entitySchemaFields;
		}
	}
	public function setEntitySchemaFields($entitySchemaFields) {
		$this -> buildEntitySchemaFieldsStructure($entitySchemaFields);
	}

	//endregion

	//region Methods: Private

	private function buildEntitySchemaFieldsStructure($entitySchemaFields) {
		$this -> entitySchemaFields = [];
		foreach ($entitySchemaFields as $entitySchemaField) {
			$typeName = $entitySchemaField->getType();
			if (key_exists($typeName, $this -> entitySchemaFields)) {
				array_push($this -> entitySchemaFields[$typeName], $entitySchemaField->getName());
			} else {
				$this -> entitySchemaFields[$typeName] = [$entitySchemaField->getName()];
			}
		}
		foreach ($this -> entitySchemaFields as $type => $typedFields) {
			sort($typedFields);
			$this -> entitySchemaFields[$type] = $typedFields;
		}
	}

	//endregion

	//region Methods: Protected

	protected function getJSONFields() {
		$json_fields = parent::getJSONFields();
		$json_fields = $json_fields . ",";
		$json_fields = $json_fields . "\"LandingPages\":{";
		$landings_count = 0;
		foreach ($this -> landings as $landing) {
			$json_fields = $json_fields . "\"".strval($landing -> get_id()) . "\":";
			$json_fields = $json_fields . strval($landing -> getJSON()) . ",";
			$landings_count++;
		}
		if ($landings_count) {
			$json_fields = substr($json_fields, 0, -1);
		}
		$json_fields = $json_fields . "},";
		$json_fields = $json_fields . "\"EntitySchemaFields\":{";
		$entitySchemaTypesCount = 0;
		foreach ($this -> entitySchemaFields as $type => $entitySchemaFields) {
			$json_fields = $json_fields . "\"" . strval($type) . "\":[";
			$fieldsCount = 0;
			foreach ($entitySchemaFields as $entitySchemaField) {
				$json_fields = $json_fields . "\"" . strval($entitySchemaField) . "\",";
				$fieldsCount++;
			}
			if ($fieldsCount > 0) {
				$json_fields = substr($json_fields, 0, -1);
			}
			$json_fields = $json_fields . "],";
			$entitySchemaTypesCount++;
		}
		if ($entitySchemaTypesCount > 0) {
			$json_fields = substr($json_fields, 0, -1);
		}
		$json_fields = $json_fields. "}";
		return $json_fields;
	}

	//endregion

}