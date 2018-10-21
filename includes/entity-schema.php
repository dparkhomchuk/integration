<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 14.04.2018
 * Time: 14:12
 */

class EntitySchema
{

	//region Constructors

	public function __construct($columns, $navigationColumns) {
		$this -> columns = $columns;
		$this -> navigationColumns = $navigationColumns;
	}

	//endregion

	//region Properties: Public

	private $columns;
	public function getColumns() {
		return $this -> columns;
	}

	private $navigationColumns;
	public function getNavigationColumns() {
		return $this -> navigationColumns;
	}

	//endregion

}