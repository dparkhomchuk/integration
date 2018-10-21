<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 15.03.2018
 * Time: 21:22
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__. "/ODataEntityMock.php";
require_once __DIR__."/../source/entity-collection.php";
require_once __DIR__."/../source/base-entity.php";

class EntityCollectionTest extends PHPUnit_Framework_TestCase {

	public function testAddEntity() {
		$entityCollection = new EntityCollection("Collection");
		$entity = ODataBaseEntityMock :: get_base_test_entity("Name value", "Id1");
		$entityCollection -> addEntity($entity);
		$expectedCollection = [$entity];
		$this->assertEquals($expectedCollection, $entityCollection -> getEntities());
	}

	public function testGetJSON_WhenListIsNotEmpty_ReturnsJSON() {
		$entityCollection = new EntityCollection("Collection");
		$entity = ODataBaseEntityMock :: get_base_test_entity("Name value", "Id1");
		$baseEntity = new BaseEntity($entity);
		$entityCollection -> addEntity($baseEntity);
		$expectedJson = "\"Collection\":[{\"Id\":\"Id1\",\"Name\":\"Name value\"}]";
		$this->assertEquals($expectedJson, $entityCollection -> getJSON());
	}

	public function testGetJSON_WhenListIsEmpty_ReturnsEmptyJSON() {
		$entityCollection = new EntityCollection("Collection");
		$expectedJson = "\"Collection\":[]";
		$this->assertEquals($expectedJson, $entityCollection -> getJSON());
	}

}
