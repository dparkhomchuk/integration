<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 23:28
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__."/../source/base-entity.php";
require_once __DIR__."/../source/landing-type.php";
require_once __DIR__."/../source/landing-page.php";
require_once __DIR__."/../source/entity-column.php";
require_once __DIR__. "/ODataEntityMock.php";

class LandingTypeTest extends PHPUnit_Framework_TestCase {

	//region Methods: Private

	private function getTestLangingTypeEntity() {
		$entity = ODataBaseEntityMock :: get_base_test_entity("Landing type", "Id1");
		$bpmonline_landing_type = new LandingType($entity);
		$landing_page_entity = ODataBaseEntityMock :: get_landing_page_test_entity("LandingPage", "Id2", "0000");
		$bpmonline_landing_pages = [];
		array_push($bpmonline_landing_pages, new LandingPage($landing_page_entity));
		$bpmonline_landing_type->set_langings($bpmonline_landing_pages);
		$entityColumns = [];
		array_push($entityColumns, $this ->getEntityColumn("Column1", "Type1"));
		$bpmonline_landing_type->setEntitySchemaFields($entityColumns);
		return $bpmonline_landing_type;
	}

	private function getEntityColumn($name, $type) {
		$entityColumn = new EntityColumn($name, $type);
		return $entityColumn;
	}

	private function getTestEntityColumns() {
		$result = [];
		array_push($result, $this ->getEntityColumn("Column3", "Type1"));
		array_push($result, $this ->getEntityColumn("Column1", "Type1"));
		array_push($result, $this ->getEntityColumn("Column2", "Type2"));
		return $result;
	}

	private function getExpectedEntityColumnsStructure() {
		$result = [];
		$result["Type1"] = ["Column1", "Column3"];
		$result["Type2"] = ["Column2"];
		return $result;
	}


	//endregion

	public function testTo_JSON() {
		$bpmonline_landing_type = $this -> getTestLangingTypeEntity();
		$actual_json = $bpmonline_landing_type->getJSON();
		$expected_json = "{\"Id\":\"Id1\",\"Name\":\"Landing type\",";
		$expected_json = $expected_json . "\"LandingPages\":{\"Id2\":{\"Id\":\"Id2\",\"Name\":\"LandingPage\",\"TypeId\":\"0000\"}},";
		$expected_json = $expected_json . "\"EntitySchemaFields\":{\"Type1\":[\"Column1\"]}}";
		$this->assertEquals($expected_json, $actual_json);
	}

	public function test_SetEntitySchemaFields() {
		$bpmonlineLandingType = $this -> getTestLangingTypeEntity();
		$testEntityColumns = $this->getTestEntityColumns();
		$bpmonlineLandingType -> setEntitySchemaFields($testEntityColumns);
		$expectedEntitySchemaFields = $this -> getExpectedEntityColumnsStructure();
		$actualEntitySchemaFields = $bpmonlineLandingType -> getEntitySchemaFields();
		$this -> assertEquals($expectedEntitySchemaFields, $actualEntitySchemaFields);
	}

}
