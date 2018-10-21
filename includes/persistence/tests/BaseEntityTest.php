<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 21:53
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__. "/ODataEntityMock.php";
require_once __DIR__."/../source/base-entity.php";

class BaseEntityTest extends PHPUnit_Framework_TestCase {

	//region Methods: Public

	public function testTo_JSON() {
		$entity = ODataBaseEntityMock :: get_base_test_entity("Name value", "Id1");
		$bpmonline_base_entity = new BaseEntity($entity);
		$actual_json = $bpmonline_base_entity->getJSON();
		$expected_json="{\"Id\":\"Id1\",\"Name\":\"Name value\"}";
		$this->assertEquals($expected_json, $actual_json);
	}

	//endregion

}
