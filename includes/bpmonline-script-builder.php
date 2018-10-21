<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 10.03.2018
 * Time: 22:03
 */

abstract class BPMonlineScriptBuilder
{

	//region Constants

	const bpmMappingSuffix = '_bpmmapping';

	const bpmLandingIdKey = 'landingid';

	const bpmOptionSuffix = '_bpmonlineintegration';

	//endregion

	//region Fields: Protected

	protected $post_data;

	protected $serviceUrl;

	//endregion

	//region Constructors

	function __construct($serviceUrl) {
		if (null !== $_POST && array_key_exists('data', $_POST)) {
			$this -> post_data = $_POST['data'];
		}
		$this -> serviceUrl = $serviceUrl;
	}

	//endregion

	//region Methods: Private

	private function getMappingConfigValues($option) {
		$mapping_config = [];
		$mapping_config['landingid'] = $option[BPMonlineScriptBuilder::bpmLandingIdKey];
		foreach ($option as $key => $value) {
			if ($key != BPMonlineScriptBuilder::bpmLandingIdKey) {
				$mapping_index = strrpos($key, BPMonlineScriptBuilder::bpmMappingSuffix);
				$form_field = substr($key, 0, $mapping_index);
				$mapping_config[$value] = $form_field;
			}
		}
		return $mapping_config;
	}

	private function getMappingConfig($mapping_config_values) {
		$config = "var config = { fields: {";
		foreach ($mapping_config_values as $key => $value) {
			if ($key != BPMonlineScriptBuilder::bpmLandingIdKey) {
				$config = $config. "\"". $key. "\": '[name = \"".$value."\"]',";
			}
		}
		$config = substr($config, 0, -1);
		$config = $config."},";
		$config = $config."landingId: \"".$mapping_config_values['landingid']."\",";
		$config = $config."serviceUrl: \"".$this->serviceUrl."\",";
		$config = $config."redirectUrl: \"".""."\"";
		$config = $config."};";
		return $config;
	}

	//endregion

	//region Methods: Protected

	protected abstract function getFormId();

	protected abstract function getSubmitCSSSelector();

	//endregion

	//region Methods: Public

	public function getOptionName() {
		$id = $this -> getFormId();
		return $id . BPMonlineScriptBuilder::bpmOptionSuffix;
	}

	public function getMappingScript($option) {
		$script = "<script>";
		$mapping_config_values = $this -> getMappingConfigValues($option);
		$mapping_config = $this -> getMappingConfig($mapping_config_values);
		$script = $script. $mapping_config;
		$script = $script. "Terrasoft={};Terrasoft.Mapping=config;";
		$script = $script. "function createObject(){landing.createObjectFromLanding(config);};";
		$script = $script. "function initLanding(){landing.initLanding(config)};";
		$script = $script . "jQuery(':checkbox').click(function(event) {
			if (event && event.target && event.target.name && jQuery(event.target).parents &&
			    jQuery(event.target).parents('.wpcf7-form').length) {
			    var targetName = event.target.name;
				var clickValue = event.target.checked;
				if (!Terrasoft.checkBoxesValue) {
					Terrasoft.checkBoxesValue = {};
				}
				Terrasoft.checkBoxesValue[targetName]=clickValue;
			}
		});";
		$script = $script . "jQuery('.wpcf7-form').submit(function(event) {
			var landingData = {};
			jQuery.each(jQuery(event.target).serializeArray(), function(_, kv) {
				var fieldName = '[name = \"'.concat(kv.name).concat('\"]');
			    landingData[fieldName] = kv.value;
			});
			Terrasoft.landingData = landingData;
			if (Terrasoft.checkBoxesValue) {
				jQuery.each(Terrasoft.checkBoxesValue, function(key, value) {
					var fieldName = '[name = \"'.concat(key).concat('\"]');
					Terrasoft.landingData[fieldName]=value;
				});
				Terrasoft.checkBoxesValue = {};
			}
		});";
		$script = $script. "jQuery(document).ajaxSuccess(function(event, xhr, settings, data) {
			var queryUrl = '';
			if (settings && settings.url) {
				queryUrl = settings.url;
			} else {
				return;
			}
			var wpc7Root = '';
			if (wpcf7 && wpcf7.apiSettings && wpcf7.apiSettings.root) {
				wpc7Root = wpcf7.apiSettings.root;
			} else {
				return;
			}
			if (queryUrl.startsWith(wpc7Root)) {
				if(data && 'mail_sent' == data.status) {
					landing.setFieldsData = function setFieldsData(b){
						for(var c in this.config.fields){
							var d=this.config.fields[c];
							if (Terrasoft.landingData[d]) {
								b.formFieldsData.push({
									name : c,
									value: Terrasoft.landingData[d]
								});
							}
						}
					};
					createObject();
				}
			}
		});";
		$script = $script. "jQuery(document).ready(initLanding);</script>";
		return $script;
	}

	public function getLookupValuesScript($entityCollectionArray) {
		$script = "<script>Terrasoft.Lookups={";
		$count = 0;
		foreach ($entityCollectionArray as $entityCollection) {
			$script = $script . $entityCollection -> getJSON();
			$script = $script . ",";
			$count++;
		}
		if ($count) {
			$script = substr($script, 0, -1);
		}
		$script = $script . "};</script>";
		return $script;
	}

	/**
	 * TODO: вынести в скрипт и зареквайерить.
	 */
	public function getFEStorage() {
		$storage = "<script>";
		$storage = $storage . "function fill_select(selector, values){jQuery(selector).each(function(index, element){jQuery(element).empty();jQuery.each";
		$storage = $storage . "(values,function(){jQuery(element).append(jQuery(\"<option />\").val(this.Id ? this.Id";
		$storage = $storage . ": this).text(this.Name ? this.Name : this));";
		$storage = $storage .	"});";
		$storage = $storage .   "jQuery(element).val(values[0].Id);});";
		$storage = $storage .  "}";
		$storage = $storage . "var fields = Terrasoft.Mapping.fields;
								for(var field in fields)
								{
									if (Terrasoft.Lookups[field]) {
										var selector = fields[field];
										var lookupValues = Terrasoft.Lookups[field];
										fill_select(selector, lookupValues);
									}
								}";
		$storage = $storage . "</script>";
		return $storage;
	}

	//endregion

}