<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.03.2018
 * Time: 21:55
 */
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__."/../source/bpmonline-structure-builder.php";
require_once __DIR__."/../source/entity-column.php";
require_once __DIR__."/../source/base-entity.php";
require_once __DIR__."/../source/landing-type.php";
require_once __DIR__."/../source/landing-page.php";
require_once __DIR__."/../source/bpmonline-data-structure.php";
require_once __DIR__. "/ODataEntityMock.php";

class BPMOnlineServiceMock
{

	//region Methods: Public

	public function getLeadType() {
		 return new LandingType(ODataBaseEntityMock :: get_base_test_entity("Lead", "TypeId1"));
	}

	public function getLeadPage() {
		return new LandingPage(ODataBaseEntityMock :: get_landing_page_test_entity("Lead form", "Id1", "TypeId1"));
	}

	public function getEventParticipantType() {
		return new LandingType(ODataBaseEntityMock :: get_base_test_entity("Event participant", "TypeId2"));
	}

	public function getEventParticipantPage() {
		return new LandingPage(ODataBaseEntityMock :: get_landing_page_test_entity("Event participant form", "Id2", "TypeId2"));
	}

	public function getLandingsPagesTypes() {
		$landingTypes = [];
		array_push($landingTypes, $this -> getLeadType());
		array_push($landingTypes, $this -> getEventParticipantType());
		return $landingTypes;
	}

	public function getLandings() {
		$landingTypes = [];
		array_push($landingTypes, $this -> getLeadPage());
		array_push($landingTypes, $this -> getEventParticipantPage());
		return $landingTypes;
	}

	public function getLandingObjectFields($objectName) {
		$result = [];
		if ($objectName === "Event participant") {
			$entityColumn = new EntityColumn("Event column", "Type1");
			array_push($result, $entityColumn);
			return $result;
		} else {
			$entityColumn = new EntityColumn("Lead column", "Type2");
			array_push($result, $entityColumn);
			return $result;
		}
	}
	public function getLookupValues() {
		return [];
	}

	public function getMetadata() {
		return [];
	}

	//endregion
}

class BPMOnlineEntityStructureBuilderTest extends PHPUnit_Framework_TestCase
{

	//region Fields: Private

	private $service;

	//endregion

	//region Constructors

	function __construct() {
		parent::__construct();
		$this -> service = new BPMOnlineServiceMock();
	}

	//endregion

	//region Methods: Private

	private function assertType($expectedTypeName, $expectedTypeId, $expectedEntity, $actualType) {
		$this->assertEquals($expectedTypeName, $actualType -> get_name());
		$this->assertEquals($expectedTypeId, $actualType -> get_id());
		$actualLandings = $actualType -> getLandings();
		$this->assertEquals(1, count($actualLandings));
		$this->assertEquals($expectedEntity->getJSON(), $actualLandings[0]->getJSON());
	}

	private function assertLeadType($actualType) {
		$this -> assertType("Lead", "TypeId1", $this -> service -> getLeadPage()
			, $actualType);
		$expectedColumns = [];
		$expectedColumns["Type2"] = ["Lead column"];
		$actualColumns = $actualType -> getEntitySchemaFields();
		$this -> assertEquals($expectedColumns, $actualColumns);
	}

	private function assertEventParticipantType($actualType) {
		$this -> assertType("Event participant", "TypeId2"
			, $this -> service -> getEventParticipantPage(), $actualType);
		$expectedColumns = [];
		$expectedColumns["Edm.String"] = ["Contact", "Contact.Email", "Contact.Mobile", "Contact.JobTitle"];
		$actualColumns = $actualType -> getEntitySchemaFields();
		$this -> assertEquals($expectedColumns, $actualColumns);
	}

	//endregion

	//region Methods: Public

	/*public function test_buildStructure() {
		$structureBuilder = new BPMOnlineEntityStructureBuilder($this -> service);
		$actualDataStructure = $structureBuilder -> buildStructure();
		$landingTypes = $actualDataStructure->getLandingTypes();
		$this -> assertLeadType($landingTypes['TypeId1']);
		$this -> assertEventParticipantType($landingTypes['TypeId2']);
	}*/

	//endregion
}
