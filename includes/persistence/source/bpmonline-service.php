<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 13.03.2018
 * Time: 22:36
 */

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Mekras\Atom\Document\FeedDocument;
use Mekras\OData\Client\OData;
use Mekras\OData\Client\Service;
use Mekras\OData\Client\URI\Uri;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ .'/entity-collection.php';
require_once __DIR__ .'/entity-column.php';


class BPMOnlineService
{
	private $service;
	private $client;
	private $serviceRootUrl;
	function __construct($rootUrl, $authorizationValue) {
		$serviceRootUrl = $rootUrl . '/0/ServiceModel/EntityDataService.svc';
		$this -> constructService($serviceRootUrl, $authorizationValue);
	}

	//region Constants

	const CollectionSuffix = 'Collection';

	//endregion

	#region Methods: Private

	private function constructService($serviceRootUrl, $authorizationValue) {
		$this -> serviceRootUrl = $serviceRootUrl;
		$authorizationHeader = 'Basic ' . $authorizationValue;
		$this -> service = new Service(
			$this -> serviceRootUrl,
			HttpClientDiscovery::find(),
			MessageFactoryDiscovery::find(),
			$authorizationHeader
		);
		$this -> client = $this -> service -> getHttpClient();
	}

	private function getCollection($collectionName, $filter, $orderBy = "", $direction = "") {
		$uri = new Uri();
		$uri->collection($collectionName);
		$options = $uri->options()
		    ->filter($filter)
		    ->top(999);
		if ($orderBy !== "") {
			$options->orderBy($orderBy, $direction);
		}
		$document = $this -> service->sendRequest(OData::GET, $uri);
		if (!$document instanceof FeedDocument) {
			die("Not a feed!\n");
		}
		$entries = $document->getFeed()->getEntries();
		return $entries;
	}

	#endregion

	#region Methods: Public

	public function getLandingsPagesTypes() {
		$landingsTypeOption = get_option('bpmonline_landingpagestypes');
		if ($landingsTypeOption !== null && $landingsTypeOption !== false) {
			$landingsTypeOptionDeserialized = maybe_unserialize($landingsTypeOption);
			return $landingsTypeOptionDeserialized;
		} else {
			$entries = $this -> getCollection('LandingTypeCollection', "");
			$landingTypes = [];
			foreach ($entries as $entry) {
				array_push($landingTypes, new LandingType($entry));
			}
			return $landingTypes;
		}
	}

	public function getLandings() {
		$landings = get_option('bpmonline_landings');
		if ($landings !== null && $landings !== false) {
			 $landingsDeserialized = maybe_unserialize($landings);
			 return $landingsDeserialized;
		} else {
			$entries = $this -> getCollection('GeneratedWebFormCollection', "");
			$landingTypes = [];
			foreach ($entries as $entry) {
				array_push($landingTypes, new LandingPage($entry));
			}
			return $landingTypes;
		}
	}

	public function getLandingObjectFields($objectName) {
		$fields = [];
		$metadata = $this->getMetadata();
		foreach ($metadata->getElementsByTagName("EntityType") as $entityType) {
			if ($entityType -> getAttribute("Name")=== $objectName) {
				foreach ($entityType -> getElementsByTagName("Property") as $property) {
					$columnName = $property -> getAttribute("Name");
					$columnType = $property -> getAttribute("Type");
					if ($columnType == "Edm.Decimal") {
						$columnType = "Edm.Int32";
					}
					$entityColumn = new EntityColumn($columnName, $columnType);
					array_push($fields, $entityColumn);
				}
			}
		}
		return $fields;
	}

	public function getMetadata() {
		$metadata = get_option("bpmonline_metadata");
		if ($metadata !== null && $metadata !== false) {
			$uncompressedMetadata = gzuncompress($metadata);
			$dom = new DOMDocument();
			$dom->loadHTML($uncompressedMetadata);
			return $dom;
		}
		return $this -> service -> sendRequest('GET', '$metadata');
	}

	public function getLookupValues($entitySchema, $fields) {
		$result = [];
		$navigationColumns = $entitySchema -> getNavigationColumns();
		foreach ($navigationColumns as $navigationColumn) {
			$columnName = $navigationColumn -> getName();
			if (in_array($navigationColumn -> getName(), $fields)) {
				$lookupCollection = new EntityCollection($columnName);
				$lookupValues = $this -> getCollection($navigationColumn -> getCollectionName(), "",'Name', 'asc');
				foreach($lookupValues as $lookupValue) {
					$lookupCollection -> addEntity(new BaseEntity($lookupValue));
				}
				array_push($result, $lookupCollection);
			}
		}

		return $result;
	}

	public function getLandingType($id) {
		$entries = $this -> getCollection('LandingTypeCollection', "Id eq guid'" .$id."'");
		$landingTypes = [];
		foreach ($entries as $entry) {
			array_push($landingTypes, new LandingType($entry));
		}
		return $landingTypes[0];
	}
	#endregion

}