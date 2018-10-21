<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 23:07
 */

require_once __DIR__ . '/base-entity.php';

//region Class: LandingPage

class LandingPage extends BaseEntity
{

	//region Properties: Public

	private $type_id;
	public function get_type_id() {
		return $this->type_id;
	}

	//endregion

	//region Constructors: Public

	function __construct( $odataEntity ) {
		parent::__construct( $odataEntity );
		foreach ($odataEntity->getProperties() as $property) {
			if ($property->getName() == 'TypeId') {
				$this->type_id = $property->getValue();
			}
		}
	}

	//endregion

	//region Methods: Protected

	protected function getJSONFields() {
		$json_fields = parent::getJSONFields();
		$json_fields = $json_fields . ",";
		$json_fields = $json_fields . "\"TypeId\":\"" . strval($this -> get_type_id()) ."\"";
		return $json_fields;
	}

	//endregion

}

//endregion