<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 01.03.2018
 * Time: 23:09
 */

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__."/../source/landing-page.php";
require_once __DIR__. "/ODataEntityMock.php";

class LandingPageTest extends PHPUnit_Framework_TestCase {

	//region Methods: Public

	public function testTo_JSON() {
		$entity = ODataBaseEntityMock :: get_landing_page_test_entity("Name value",
			"Id1", "00000000-0000");
		$bpmonline_landing_page = new LandingPage($entity);
		$actual_json = $bpmonline_landing_page->getJSON();
		$expected_json="{\"Id\":\"Id1\",\"Name\":\"Name value\",\"TypeId\":\"00000000-0000\"}";
		$this->assertEquals($expected_json, $actual_json);
	}

	//endregion

}
