<?php
/**
 * Created by PhpStorm.
 * User: Dmytro
 * Date: 21.10.2018
 * Time: 20:30
 */
add_action( 'wp_ajax_loadBpmonlineSettings', 'get_my_new_settings' );
class BpmonlineFormidableformsMapping {

	public static function get_settings_placeholder($values) {
		?>
        <div
             id="bpmonline-settings-panel" aria-labelledby="ui-id-5" role="tabpanel" aria-hidden="true"
             style="display: block;">
            <div class="config-error"></div>
            <h3>Bpm'online</h3>
            <fieldset id="bpmonline-fieldset">
                <span>Retrieving data structure from Bpm'online. Please wait it can take around 30 seconds.</span>
                <p class="description" id="bpmonline-spinner">
                    <label><span class="spinner is-active" style="float:left"></span></label>
                </p>
            </fieldset>
        </div>

        <script>
            setTimeout(()=>{
                wp.ajax.post('loadBpmonlineSettings', {id: <?php echo($values['id']);?>}).done(function(response){
                    jQuery("#bpmonline-spinner").remove();
                    jQuery('#bpmonline-fieldset').html(response);
                }).fail( function(response) {
                    jQuery("#bpmonline-spinner").remove();
                    jQuery('#bpmonline-fieldset').html(response);
                });
            }, 1000);
        </script>
		<?php
	}
}
function get_my_new_settings ($values) {
    $id = $_POST['id'];
		$form_fields = FrmField::getAll('fi.form_id='. (int) $id ." and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
		$my_form_opts = maybe_unserialize( get_option('frm_mysettings_' . $id) );
        $bpmOnlineUrl=get_option('bpmonline_url');
		$bpmOnlineAuthorization = get_option('bpmonline_authorization');
		$selectedMapping = get_option($id. "_bpmonlineintegration");
 		$selectedMappingType = $selectedMapping == null ? null : $selectedMapping['landingtypeid'];
 		$selectedLanding = $selectedMapping == null ? null : $selectedMapping["landingid"];
		$bpmOnlineService = new BPMOnlineService($bpmOnlineUrl, $bpmOnlineAuthorization);
		$bpmonline_entity_structure_builder = new BPMOnlineEntityStructureBuilder($bpmOnlineService);
		$bpmonline_data_structure = $bpmonline_entity_structure_builder -> buildStructure();
		$structure_script = $bpmonline_data_structure -> getJSON();
		$bpmonlineData = new Bpmonline_Data();
		$bpmonlineData -> set_landing_types($bpmonline_data_structure -> getLandingTypes());
		$bpmonlineData -> set_structure_script($structure_script);
		$landingTypes = $bpmonlineData -> get_landing_types();
		$selectedlLandings = $selectedMappingType == null ? null : $bpmonlineData -> getLandings($selectedMappingType);
		$selectedFields = $selectedMappingType == null ? [] : $bpmonlineData->get_entitySchemas($selectedMappingType);
		?>
		<table class="form-table">
			<tr>
				<td width="150px">
					<label style="height: 22px; padding-top:2px; padding-bottom:2px;display:inline-block">Select landing type.</label>
				</td>
				<td>
                    <select name="bpmonline_landing_type_id" onchange="handle_landing_type_change();">
						<?php
						foreach ($landingTypes as $landingType) {
							?><option value="<?php echo($landingType->get_id())?>" <?php if($landingType -> get_id() == $selectedMappingType) echo("selected=\"selected\""); ?>><?php echo($landingType->get_name())?></option>
							<?php
						}
						?>
                    </select>
				</td>
            </tr>
            <tr>
                <td width="150px">
                    <label style="height: 22px; padding-top:2px; padding-bottom:2px;display:inline-block">Select landing.</label>
                </td>
                <td>
					<?php if($selectedlLandings == null) {
						?> <select name="bpmonline_landing_id"></select> <?php
					} else {
						?><select name="bpmonline_landing_id"><?php
						foreach ($selectedlLandings as $landing) {
							?><option value="<?php echo($landing->get_id());?>" <?php if ($selectedLanding == $landing -> get_id()) { echo("selected=\"selected\""); } ?>><?php echo($landing->get_name());?></option><?php
						} ?>
                        </select>
					<?php }?>
                </td>
            </tr>
            <?php
            foreach ( $form_fields as $form_field ) {
                if($form_field->type != 'file') {
                ?> <tr>
                    <td width="150px">
                        <label style="height: 22px; padding-top:2px; padding-bottom:2px;display:inline-block"><?php echo($form_field->name)?></label>
                    </td>
                    <td>
                        <?php if ($selectedMapping == null) {
                            ?>
                            <select name="<?php echo($form_field->id)?>_bpmmapping" data-type=<?php echo(wpc7_editor_get_bpm_type_from_string($form_field->type))?>>
                            </select>
                        <?php } else { ?>
	                        <select name="<?php echo($form_field->id)?>_bpmmapping" data-type=<?php echo(wpc7_editor_get_bpm_type_from_string($form_field->type))?>>
                                <?php
                                    $fieldType = wpc7_editor_get_bpm_type_from_string($form_field->type);
                                    $bpmfields = $selectedFields[$fieldType];
                                    if(array_key_exists($fieldType, $selectedFields))
                                    {
                                        foreach ($bpmfields as $bpmfield) {?>
                                        <option value="<?php echo($bpmfield);?>" <?php if($selectedMapping[$form_field->id]==$bpmfield) echo("selected=\"selected\""); ?>><?php echo($bpmfield);?> </option>
                                    <?php }}?>
                            </select>
                        <?php }?>
                    </td>
                 </tr>

                <?php }}
            ?>
		</table>
		<?php echo(wpc7_editor_panel_bpmonline_script($structure_script));
	}
function wpc7_editor_panel_bpmonline_script($structure) {
		?>
        <script><?php echo($structure) ?>

            jQuery('[name = bpmonline_landing_type_id]').change(handle_landing_type_change);
            function handle_landing_type_change(event) {
                jQuery('[name = bpmonline_landing_id]').empty();
                jQuery('[name *= "_bpmmapping"]').empty();
                var selectedValue = jQuery("[name='bpmonline_landing_type_id']").val();
                var selectedType = Terrasoft[selectedValue];
                var selectedTypeLandings = selectedType.LandingPages;
                var selectedTypeLandingsValues = [];
                for(var id in selectedTypeLandings) {
                    var selectedLanding = selectedTypeLandings[id];
                    selectedTypeLandingsValues.push({Id: selectedLanding.Id, Name: selectedLanding.Name});
                }
                fillSelect('[name = bpmonline_landing_id]', selectedTypeLandingsValues);
                var selectedLangingFields = selectedType.EntitySchemaFields;
                fillTypedSelect('[name *= "_bpmmapping"]', selectedLangingFields);
            }
            function fillSelect(selector, values) {
                jQuery(selector).each(function(index, element) {
                    jQuery.each(values, function() {
                        jQuery(element).append(jQuery("<option />").val(this.Id ? this.Id : this).text(this.Name ? this.Name : this));
                    });
                });
            }
            function fillTypedSelect(selector, values) {
                jQuery(selector).each(function(index, element) {
                    var jElement = jQuery(element);
                    var elementType = jElement.data('type');
                    var typedValues = values[elementType];
                    jQuery.each(typedValues, function() {
                        jQuery(element).append(jQuery("<option />").val(this.Id ? this.Id : this).text(this.Name ? this.Name : this));
                    });
                });
            }

        </script>
		<?php wp_die();
}