<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 14.04.2018
 * Time: 14:24
 */

class NavigationProperty
{
	//region Constructors

	public function __construct($name, $relationshipName, $toRole) {
		$this -> name = $name;
		$this -> relationshipName = $relationshipName;
		$this -> toRole = $toRole;
	}

	//endregion

	//region Properties: Public

	private $name;
	public function getName() {
		return $this -> name;
	}

	private $relationshipName;
	public function getRelationshipName() {
		return $this -> relationshipName;
	}

	private $toRole;
	public function getToRole() {
		return $this -> toRole;
	}

	//endregion

}