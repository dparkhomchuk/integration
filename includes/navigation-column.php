<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 14.04.2018
 * Time: 14:17
 */

class NavigationColumn
{
	//region Constructors

	public function __construct($name, $collectionName) {
		$this -> name = $name;
		$this -> collectionName = $collectionName;
	}

	//endregion

	//region Properties: Public

	private $name;
	public function getName() {
		return $this -> name;
	}

	private $collectionName;
	public function getCollectionName() {
		return $this -> collectionName;
	}

	//endregion

}