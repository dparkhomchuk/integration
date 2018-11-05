<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/base-entity.php';
require_once __DIR__ . '/landing-type.php';
require_once __DIR__ . '/landing-page.php';
require_once __DIR__ . '/bpmonline-service.php';
require_once __DIR__ . '/bpmonline-structure-builder.php';


class Bpmonline_Data
{
    private $landingTypes;
    private $structure_script;

    public function set_landing_types($landingTypes) {
        $this -> landingTypes = $landingTypes;
    }

    public function get_landing_types() {
        return $this -> landingTypes;
    }

    public function getLandings($id) {
        return $this -> landingTypes[$id] -> getLandings();
    }

	public function  get_entitySchemas($id) {
		return $this -> landingTypes[$id] -> getEntitySchemaFields();
	}

	public function set_structure_script($structure_script) {
		$this -> structure_script = $structure_script;
	}

	public function get_structure_script() {
		return $this -> structure_script;
	}

}

?>
