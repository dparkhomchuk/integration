<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.03.2018
 * Time: 22:33
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__."/../source/base-entity.php";
require_once __DIR__."/../source/bpmonline-data-structure.php";
require_once __DIR__. "/ODataEntityMock.php";

class BPMOnlineDataStructureTest extends PHPUnit_Framework_TestCase
{

	//region Methods: Public

	public function testTo_JSON() {
		$entity = ODataBaseEntityMock :: get_base_test_entity("Name value", "Id1");
		$bpmonline_base_entity = new BaseEntity($entity);
		$bpmonlineDataStructure = new BPMOnlineDataStructure();
		$landingTypes = [];
		$landingTypes['Name'] = $bpmonline_base_entity;
		$bpmonlineDataStructure -> setLandingTypes($landingTypes);
		$actualJson = $bpmonlineDataStructure->getJSON();
		$expectedJson = "Terrasoft ={};Terrasoft['Name']={\"Id\":\"Id1\",\"Name\":\"Name value\"};";
		$this->assertEquals($expectedJson, $actualJson);
	}

	//endregion

}
