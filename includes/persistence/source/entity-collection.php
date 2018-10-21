<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 15.03.2018
 * Time: 21:18
 */

class EntityCollection {

	//region Properties

	private $name;

	public function getName() {
		return $this -> name;
	}

	private $entities;

	public function getEntities() {
		return $this->entities;
	}

	public function setEntities( $entities ) {
		$this->entities = $entities;
	}

	//endregion

	//region Constructors

	public function __construct($name) {
		$this -> name = $name;
	}

	//endregion

	//region Methods: Public

	public function addEntity( $entity ) {
		if ( null === $this->entities ) {
			$this->entities = [];
		}
		array_push( $this->entities, $entity );
	}

	public function getJSON() {
		$result = "\"". $this -> name . "\":[";
		if ( null !== $this->entities ) {
			$count = 0;
			foreach ( $this->entities as $entity ) {
				$result = $result . $entity->getJSON();
				$result = $result . ",";
				$count++;
			}
			if ($count) {
				$result = substr($result, 0, -1);
			}
		}
		$result = $result . "]";
		return $result;
	}

	//endregion


}