<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 14.04.2018
 * Time: 13:38
 */
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
require_once __DIR__."/../source/metadata-parser.php";

class MetadataParserTest extends PHPUnit_Framework_TestCase
{

	//region Methods: Private

	private function getTestMetadata() {
		$xml = <<< XML
			<edmx:Edmx Version="1.0" xmlns:edmx="http://schemas.microsoft.com/ado/2007/06/edmx">
				<edmx:DataServices m:DataServiceVersion="3.0" m:MaxDataServiceVersion="3.0" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata">
		            <EntityType Name="AcademyURL">
		                <Key>
		                    <PropertyRef Name="Id" />
		                </Key>
		                <Property Name="Id" Type="Edm.Guid" Nullable="false" />
		                <Property Name="Name" Type="Edm.String" />
		                <NavigationProperty Name="CreatedBy" Relationship="Terrasoft.Configuration.AcademyURL_CreatedBy" ToRole="CreatedBy" FromRole="AcademyURL" />
		                <NavigationProperty Name="ModifiedBy" Relationship="Terrasoft.Configuration.AcademyURL_ModifiedBy" ToRole="ModifiedBy" FromRole="AcademyURL" />
		            </EntityType>
		            <EntityContainer Name="BPMonline" m:IsDefaultEntityContainer="true">
		           		<EntitySet Name="AcademyURLCollection" EntityType="Terrasoft.Configuration.AcademyURL" />
						<AssociationSet Name="AcademyURLCreatedByCollectionContact" Association="Terrasoft.Configuration.AcademyURL_CreatedBy">
			                    <End Role="AcademyURL" EntitySet="AcademyURLCollection" />
			                    <End Role="CreatedBy" EntitySet="ContactCollection" />
			            </AssociationSet>
			            <AssociationSet Name="AcademyURLModifiedByCollectionContact" Association="Terrasoft.Configuration.AcademyURL_ModifiedBy">
		                    <End Role="AcademyURL" EntitySet="AcademyURLCollection" />
		                    <End Role="ModifiedBy" EntitySet="ContactCollection" />
                		</AssociationSet>
					</EntityContainer>
				</edmx:DataServices>
			</edmx:Edmx>
XML;
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		return $dom;

	}

	private function getExpectedFields() {
		$columns = [];
		array_push($columns, new EntityColumn("Id", "Edm.Guid"));
		array_push($columns, new EntityColumn("Name", "Edm.String"));
		return $columns;
	}

	private function getExpectedSchema() {
		$expectedColumns = [];
		array_push($expectedColumns, new EntityColumn("Name", "Edm.String"));
		$expectedNavigationColumns = [];
		array_push($expectedNavigationColumns, new NavigationColumn("CreatedBy", "ContactCollection"));
		array_push($expectedNavigationColumns, new NavigationColumn("ModifiedBy", "ContactCollection"));
		return new EntitySchema($expectedColumns, $expectedNavigationColumns);
	}

	//endregion

	//region Methods: Public

	public function testGetObjectFields_ReturnsExpectedArray() {
		$metadataParser = new MetadataParser();
		$metadata = $this -> getTestMetadata();
		$expectedFields = $this -> getExpectedFields();
		$actualFields = $metadataParser -> getObjectFields($metadata, "AcademyURL");
		$this->assertEquals($expectedFields, $actualFields);
	}

	public function test_GetExpectedSchema_ReturnsExpectedSchema() {
		$metadataParser = new MetadataParser();
		$metadata = $this -> getTestMetadata();
		$expectedSchema = $this -> getExpectedSchema();
		$actualSchema = $metadataParser -> getEntitySchema($metadata, "AcademyURL");
		$this -> assertEquals($expectedSchema -> getColumns(), $actualSchema -> getColumns());
		$this -> assertEquals($expectedSchema -> getNavigationColumns(), $actualSchema -> getNavigationColumns());
	}

	//endregion

}
