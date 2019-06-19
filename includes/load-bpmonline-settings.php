<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 16.05.2018
 * Time: 21:37
 */




function loadBpmonlineSettings() {
	$lastCheckValue = bpmonline_get_is_licence_valid();
    if ($lastCheckValue == "true") {
	    $id = $_POST['id'];
	    $post = wpcf7_contact_form( $id );
	    wpcf7_editor_panel_bpmonline_settings($post);
	    wp_die();
    } else {
	    wp_die();
    }
}

function wpcf7_editor_panel_bpmonline_settings ( $post) {
	$bpmonlineData = wcf7_bpmonline_integration();
	$landingTypes = $bpmonlineData -> get_landing_types();
	$landings = [];
	$settings = get_option($post->id().'_bpmonlineintegration');
	$entitySchemas = [];
	if (null !== $settings && null !== $settings['landingtypeid']) {
		$entitySchemas = $bpmonlineData -> get_entitySchemas($settings['landingtypeid']);
		$landings = $bpmonlineData -> getLandings($settings['landingtypeid']);
	} else {
		$entitySchemas = $bpmonlineData -> get_entitySchemas('62b81c7e-bc2b-4ec5-8441-d486b384f57d');
		$landings = $bpmonlineData -> getLandings('62b81c7e-bc2b-4ec5-8441-d486b384f57d');
	}
	$mailTags = $post -> collect_mail_tags();
	$formTags = $post -> scan_form_tags();
	$bpmonlineTags = get_bpmonline_tags($formTags);
	$script = $bpmonlineData -> get_structure_script();
	?>
		<?php echo(wpc7_editor_panel_bpmonline_landing_type($landingTypes, $settings))?>
		<?php echo(wpc7_editor_panel_bpmonline_landing($landings, $settings))?>
		<?php
		foreach ( $bpmonlineTags as $bpmonlineTag ) {
			?> <p class="description"><label style="padding-right:20px;">
					<?php echo($bpmonlineTag->getTagName()); ?></label><?php wpcf7_editor_panel_select($bpmonlineTag, $entitySchemas, $settings);?> </p> <?php
		}
		?>
	<?php echo(wpc7_editor_panel_bpmonline_script($script));
}

function get_bpmonline_tags($tags) {
	$bpmonlineTags = [];
	foreach($tags as $tag) {
		$name = $tag['name'];
		$type = $tag['type'];
		$bpmType = wpc7_editor_get_bpm_type($type);
		if ($bpmType !== "") {
			$typedTag = new TypedMailTag($name, $bpmType);
			array_push($bpmonlineTags, $typedTag);
		}
	}
	return $bpmonlineTags;
}

function wpc7_editor_get_bpm_type($tagType) {
	if (strpos($tagType, '*') !== false) {
		$tagType = substr($tagType, 0, strlen($tagType) -1);
	}
	return wpc7_editor_get_bpm_type_from_string($tagType);
}

function wpc7_editor_get_bpm_type_from_string($tagTypeString) {
	switch  ($tagTypeString) {
		case "text":
			return "Edm.String";
		case "email":
			return "Edm.String";
		case "url":
			return "Edm.String";
		case "tel":
			return "Edm.String";

		case "phone":
			return "Edm.String";
		case "number":
			return "Edm.Int32";
		case "date":
			return "Edm.DateTime";
		case "textarea":
			return "Edm.String";
		case "select":
			return "Edm.Guid";
		case "dropdown":
			return "Edm.Guid";
		case "checkboxes":
			return "";
		case "radio":
			return "";
		case "acceptance":
			return "Edm.Boolean";
		case "quiz":
			return "";
		case "reCAPTCHA":
			return "";
		case "file":
			return "";
		case "submit":
			return "";
		case "hidden":
			return "";
		default:
			return "";
	}
}

function wpc7_editor_panel_bpmonline_landing_type($landingTypes, $settings) {
	$selected_landing_type = "";
	if (null !== $settings) {
		$selected_landing_type = $settings['landingtypeid'];
	}
	?>
    <p class="description">
        <label style="padding-right:20px;">Select landing type.</label>
        <select name="bpmonline_landing_type_id">
			<?php
			foreach ($landingTypes as $landingType) {
				?><option value="<?php echo($landingType->get_id())?>" <?php echo(wcf7_editor_panel_selected($landingType->get_id(), $selected_landing_type))?>><?php echo($landingType->get_name())?></option>
				<?php
			}
			?>
        </select>
		<?php
		}

		function wcf7_editor_panel_selected($id, $selected) {
			if ($id == $selected) {
				?>selected ="selected"<?php
			};
		}

		function wpc7_editor_panel_bpmonline_landing($landings, $settings) {
		$selected_landing = "";
		if (null !== $settings) {
			$selected_landing = $settings['landingid'];
		}
		?>
    <p class="description">
        <label style="padding-right:20px;">Select landing.</label>
        <select name="bpmonline_landing_id">
			<?php
			foreach ($landings as $landing) {
				?><option value="<?php echo($landing->get_id())?>" <?php echo(wcf7_editor_panel_selected($landing->get_id(), $selected_landing))?>><?php echo($landing->get_name())?></option>
				<?php
			}
			?>
        </select>
    </p>
	<?php
}

function wpcf7_editor_panel_select($tag, $params, $settings) {
	$selected_mapping = "";
	$tagType = $tag -> getTagType();
	$tagName = $tag -> getTagName();
	$typedParams = $params[$tagType];
	if (null !== $settings) {
		$selected_mapping = $settings[$tagName."_bpmmapping"];
	}
	?><select name="<?php echo($tagName)?>_bpmmapping" data-type=<?php echo($tagType)?>><?php
	foreach ($typedParams as $typedParam) {
		?><option value = "<?php echo($typedParam);?>" <?php echo(wcf7_editor_panel_selected($typedParam, $selected_mapping))?>><?php echo($typedParam);?></option> <?php
	}
	?></select><?php
}

class TypedMailTag
{
	private $tagName;
	private $tagType;
	function __construct($tagName, $tagType) {
		$this -> tagName = $tagName;
		$this -> tagType = $tagType;
	}
	public function getTagName() {
		return $this -> tagName;
	}
	public function getTagType() {
		return $this->tagType;
	}
}

function wcf7_bpmonline_integration() {
	$bpmOnlineUrl = get_option('bpmonline_url');
	$bpmOnlineAuthorization = get_option('bpmonline_authorization');
	if (null !== $bpmOnlineUrl && null !== $bpmOnlineAuthorization) {
		$bpmOnlineService = new BPMOnlineService($bpmOnlineUrl, $bpmOnlineAuthorization);
		$bpmonline_entity_structure_builder = new BPMOnlineEntityStructureBuilder($bpmOnlineService);
		$bpmonline_data_structure = $bpmonline_entity_structure_builder -> buildStructure();
		$structure_script = $bpmonline_data_structure -> getJSON();
		$bpmonlineData = new Bpmonline_Data();
		$bpmonlineData -> set_landing_types($bpmonline_data_structure -> getLandingTypes());
		$bpmonlineData -> set_structure_script($structure_script);
		return $bpmonlineData;
	}
}