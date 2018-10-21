<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.03.2018
 * Time: 21:58
 */
require_once __DIR__ .'/bpmonline-data-structure.php';
require_once __DIR__ .'/entity-column.php';
require_once __DIR__ .'/metadata-parser.php';


class BPMOnlineEntityStructureBuilder {

	//region Fields: Private

	private $bpmOnlineService;

	private $metadataParser;

	//endregion

	function __construct($bpmOnlineService) {
		$this -> bpmOnlineService = $bpmOnlineService;
		$this -> metadataParser = new MetadataParser();
	}

	//region Properties: Public

	private $landingTypes;
	public function get_landingTypes() {
		return $this -> landingTypes;
	}

	private $landings;
	public function getLandings() {
		return $this -> landings;
	}

	//endregion

	//region Methods: Private

	private function loadLandingTypes() {
		$this -> landingTypes = [];
		foreach ($this->bpmOnlineService->getLandingsPagesTypes() as $langingPageType) {
			$this -> landingTypes[$langingPageType->get_id()]=$langingPageType;
		}
	}

	private function loadLandings() {
		$this -> landings = [];
		foreach ($this->bpmOnlineService->getLandings() as $landing) {
			array_push($this -> landings, $landing);
		}
	}

	private function getFieldsFromSchema($schemaName) {
		$metadata = $this -> bpmOnlineService -> getMetadata();
		$schema = $this -> metadataParser -> getEntitySchema($metadata, $schemaName);
		$fields = $schema -> getColumns();
		$guidType = "Edm.Guid";
		foreach ($schema -> getNavigationColumns() as $navigationColumn) {
			$columnName = $navigationColumn -> getName();
			if (strpos($columnName, "Collection") == false) {
				array_push($fields, new EntityColumn($navigationColumn -> getName(),$guidType));
			}
		}
		return $fields;
	}

	private function processCaseFields($landingFields) {
		$emailColumn = new EntityColumn("Email", "Edm.String");
		if (!in_array($emailColumn, $landingFields)) {
			array_push($landingFields, $emailColumn);
		}
		$nameColumn = new EntityColumn("Name", "Edm.String");
		if (!in_array($nameColumn, $landingFields)) {
			array_push($landingFields, $nameColumn);
		}
		$phoneColumn = new EntityColumn("Phone", "Edm.String");
		if (!in_array($phoneColumn, $landingFields)) {
			array_push($landingFields, $phoneColumn);
		}
		return $landingFields;
	}

	private function loadTypes() {
		foreach ($this->landingTypes as $landing_type) {
			$landing_type_name = $landing_type->get_name();
			$landing_fields = [];
			if ($landing_type_name == "Event participant") {
				$landing_fields = $this->get_event_participation_fields();
			} else if ($landing_type_name == "Case") {
				$landing_fields = $this->getFieldsFromSchema($landing_type_name);
				$landing_fields = $this->processCaseFields($landing_fields);
			} else {
				$landing_fields = $this->getFieldsFromSchema($landing_type_name);
			}
			$landing_type -> setEntitySchemaFields($landing_fields);
		}
	}

	private function get_event_participation_fields() {
		$stringType = "Edm.String";
		$result = [];
		array_push($result, new EntityColumn("Contact", $stringType));
		array_push($result, new EntityColumn("Contact.MobilePhone", $stringType));
		array_push($result, new EntityColumn("Contact.JobTitle", $stringType));
		array_push($result, new EntityColumn("Contact.Email", $stringType));
		return $result;
	}

	private function buildLandingTypes() {
		foreach ($this -> landings as $landing) {
			$landing_type = $this -> landingTypes[$landing->get_type_id()];
			$landings = $landing_type -> getLandings();
			array_push($landings, $landing);
			$landing_type -> set_langings($landings);
		}
	}

	public function loadStructure() {
		$this -> loadLandingTypes();
		$this -> loadLandings();
		$this -> buildLandingTypes();
		$this -> loadTypes();
	}

	//endregion

	//region Methods: Public

	public function buildStructure() {
		$this -> loadStructure();
		$structure = new BPMOnlineDataStructure();
		$structure -> setLandingTypes($this ->landingTypes);
		return $structure;
	}

	//endregion

}