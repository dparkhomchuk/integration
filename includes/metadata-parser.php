<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 14.04.2018
 * Time: 13:37
 */
require_once __DIR__ .'/entity-column.php';
require_once __DIR__ .'/entity-schema.php';
require_once __DIR__ .'/navigation-column.php';
require_once __DIR__ .'/navigation-property.php';

class MetadataParser
{

	//region Methods: Public

	public function getObjectFields($metadata, $objectName) {
		$fields = [];
		foreach ($metadata->getElementsByTagName("EntityType") as $entityType) {
			if ($entityType -> getAttribute("Name")=== $objectName) {
				foreach ($entityType -> getElementsByTagName("Property") as $property) {
					$columnName = $property -> getAttribute("Name");
					$columnType = $property -> getAttribute("Type");
					$entityColumn = new EntityColumn($columnName, $columnType);
					array_push($fields, $entityColumn);
				}
			}
		}
		return $fields;
	}

	public function getEntitySchema($metadata, $objectName) {
		$columns = [];
		$navigationColumns = [];
		$associations = [];
		foreach ($metadata->getElementsByTagName("EntityType") as $entityType) {
			if ($entityType -> getAttribute("Name")=== $objectName) {
				foreach ($entityType -> getElementsByTagName("Property") as $property) {
					$columnName = $property -> getAttribute("Name");
					$columnType = $property -> getAttribute("Type");
					if ($columnType == "Edm.Decimal") {
						$columnType = "Edm.Int32";
					}
					if ($columnType != "Edm.Guid") {
						$entityColumn = new EntityColumn($columnName, $columnType);
						array_push($columns, $entityColumn);
					}
				}
				foreach ($entityType -> getElementsByTagName("NavigationProperty") as $navigationProperty) {
					$navigationColumnName = $navigationProperty -> getAttribute("Name");
					$relationshipName = $navigationProperty -> getAttribute("Relationship");
					$toRole = $navigationProperty -> getAttribute("ToRole");
					$navigationProperty = new NavigationProperty($navigationColumnName, $relationshipName, $toRole);
					if (array_key_exists($relationshipName, $associations)) {
						array_push($associations[$relationshipName], $navigationProperty);
					} else {
						$associations[$relationshipName] = [$navigationProperty];
					}
				}
			}
		}
		foreach ($metadata->getElementsByTagName("AssociationSet") as $associationSet) {
			$association = $associationSet -> getAttribute("Association");
			if (null !== $associations[$association]) {
				$ends = $associationSet -> getElementsByTagName("End");
				$end1 = $ends[0];
				$end2 = $ends[1];
				$navigationProperties = $associations[$association];
				foreach($navigationProperties as $navigationProperty) {
					$collectionName = "";
					if ($navigationProperty -> getToRole() == $end1 -> getAttribute("Role")) {
						$collectionName = $end1 -> getAttribute("EntitySet");
					} else {
						$collectionName = $end2 -> getAttribute("EntitySet");
					}
					array_push($navigationColumns, new NavigationColumn($navigationProperty -> getName(), $collectionName));
				}
			}
		}
		return new EntitySchema($columns, $navigationColumns);
	}


	//endregion

}