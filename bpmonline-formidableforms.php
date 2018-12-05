<?php
   /*
   Plugin Name: BpmOnline Formidableforms Connector
   description: Enables easy connection of WordPress landing pages to BpmOnline.
   Version: 1.0
   Author: Flexentric
   Author URI: http://flexentric.eu/
   */

require_once __DIR__ . '/includes/bpmonline-script-builder.php';
require_once __DIR__ . '/includes/persistence/source/bpmonline-service.php';
require_once __DIR__ . '/includes/persistence/source/bpmonline-structure-builder.php';
require_once __DIR__ . '/includes/persistence/source/base-entity.php';
require_once __DIR__ . '/includes/persistence/source/landing-page.php';
require_once __DIR__ . '/includes/persistence/source/landing-type.php';
require_once __DIR__ . '/includes/load-bpmonline-settings.php';
require_once __DIR__ . '/includes/persistence/source/bpmonline-integration.php';
require_once __DIR__ . '/includes/settings/bpmonline-formidableforms-mapping.php';

register_activation_hook( __FILE__, 'bpmonlineplugin_activate' );

function bpmonlineplugin_activate() {
}

register_uninstall_hook(__FILE__, 'bpmonlineplugin_uninstall');

function bpmonlineplugin_uninstall() {
    delete_option('bpmonline_url');
	delete_option('bpmonline_login');
	delete_option('bpmonline_authorization');
	delete_option('bpmonline_last_value');
	delete_option('bpmonline_last_check');
	delete_option('bpmonline_licence');
}

add_action( 'init', 'bpmonline_init' );
add_action( 'admin_menu','bpmonline_admin_init', 20 );
add_filter('frm_add_form_settings_section', 'frm_add_new_settings_tab', 10, 2);
function frm_add_new_settings_tab( $sections, $values ) {
	$sections[] = array(
		'name'		=> 'Bpm\'online fields mapping',
		'anchor'	=> 'new_tab_name',
		'function'	=> 'get_my_new_settings',
		'class'	=> 'BpmonlineFormidableformsMapping'
	);
	return $sections;
}

add_filter('frm_form_options_before_update', 'frm_update_my_form_option', 20, 2);

function frm_update_my_form_option( $options, $values ){
	if ( isset( $values['bpmonline_landing_id'] ) ) {
		$bpmOnlineUrl           = get_option( 'bpmonline_url' );
		$bpmOnlineAuthorization = get_option( 'bpmonline_authorization' );
		if ( null !== $bpmOnlineUrl && null !== $bpmOnlineAuthorization ) {
			$service                                      = new BPMOnlineService( $bpmOnlineUrl, $bpmOnlineAuthorization );
			$bpmonlineintegration_params                  = [];
			$bpmonlineintegration_params['landingid']     = $values['bpmonline_landing_id'];
			$landingTypeId                                = $values['bpmonline_landing_type_id'];
			$bpmonlineintegration_params['landingtypeid'] = $landingTypeId;
			$fields                                       = [];
			foreach ( $values as $key => $value ) {
				if ( preg_match( '@_bpmmapping@', $key ) ) {
					$bpmonlineintegration_params[ $key ] = $value;
					array_push( $fields, $value );
				}
			}
			$form_fields = FrmField::getAll('fi.form_id='. (int) $values['id'] ." and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');

			$metadata       = $service->getMetadata();
			$metadataParser = new MetadataParser();
			$landingType    = $service->getLandingType( $landingTypeId );
			$shemaName      = $landingType->get_name();
			if ( $shemaName == "Event participant" ) {
				$shemaName = "Contact";
			}
			$entitySchema                           = $metadataParser->getEntitySchema( $metadata, $shemaName );
			$lookupValues                           = $service->getLookupValues( $entitySchema, $fields );
			foreach ($form_fields as $value) {
				if ($value -> type == "select") {
				    $mappingKey = $value->name . "_bpmmapping";
				    $columnName = $bpmonlineintegration_params[$mappingKey];
				    foreach ($lookupValues as $lookupValue) {
				        if ($lookupValue -> getName() == $columnName) {
					        $id = $value -> id;
					        $values = [];
					        $entities = $lookupValue->getEntities();
					        $names = [];
					        foreach($entities as $entity) {
					            array_push($names, $entity -> get_name());
                            }
					        $values['options'] = $names;
					        FrmField::update($id, $values);
                        }
                    }
                }
			}
			$bpmonlineintegration_params['lookups'] = $lookupValues;
			update_option( $values['id'] . "_bpmonlineintegration", $bpmonlineintegration_params );
		}
	}

	return $options;
}

function get_my_new_settings( $values ) {
	$form_fields = FrmField::getAll('fi.form_id='. (int) $values['id'] ." and fi.type not in ('break', 'divider', 'html', 'captcha', 'form')", 'field_order');
	$my_form_opts = maybe_unserialize( get_option('frm_mysettings_' . $values['id']) );

	?>
    <table class="form-table">
        <tr>
            <td width="200px">
                <label>Select landing type.</label>
            </td>
            <td>
                <select name="frm_mysettings[firstopt]">
                    <option value=""><?php _e( '— Select —' ); ?></option>
					<?php foreach ( $form_fields as $form_field ) {
						$selected = ( isset( $my_form_opts['firstopt'] ) && $my_form_opts['firstopt'] == $form_field->id ) ? ' selected="selected"' : '';
						?>
                        <option value="<?php echo $form_field->id ?>" <?php echo $selected ?>><?php echo FrmAppHelper::truncate( $form_field->name, 40 ) ?></option>
					<?php } ?>
                </select>
            </td>
        </tr>
    </table>
	<?php
}



function bpmonline_init() {
	if(!is_admin()){
		add_action('wp_footer', 'addBpmonlineMappingScript');
		wp_register_style( 'bpmonline-client-styles', plugin_dir_url( __FILE__ ) . 'css/styles.css', array() , '1.0');
		wp_enqueue_style('bpmonline-client-styles');
	} else {
		wp_enqueue_script( $handle = 'bpmonline-integration-tab' ,
            $src = plugin_dir_url( __FILE__ ) . 'js/integration-tab.js',
            $deps = array('jquery', 'wp-util'),
            $ver = false,
            $in_footer = true);
		wp_localize_script('bpmonline-integration-tab', 'bpmonline', array( 'siteurl' => get_option('siteurl') ));
    }
}

function bpmonline_admin_init() {
	if ( function_exists('add_submenu_page') ){
		$page = add_submenu_page('formidable', 'Forms: Bpmonline Integration', 'Bpmonline integration setup',
			'manage_options',
               basename(__FILE__,'.php').'-config',
                'my_submenu_config');
	}
}

function my_submenu_config(){
	include_once(dirname(__FILE__).'/includes/plugin-ui.php');
}


add_action('frm_after_create_entry', 'yourfunctionname', 30, 2);
function yourfunctionname($entry_id, $form_id){
	if($form_id == 5){ //replace 5 with the id of the form
		$args = array();
		if(isset($_POST['item_meta'][30])) //replace 30 and 31 with the appropriate field IDs from your form
			$args['data1'] = $_POST['item_meta'][30]; //change 'data1' to the named parameter to send
		if(isset($_POST['item_meta'][31]))
			$args['data2'] = $_POST['item_meta'][31]; //change 'data2' to whatever you need
		$result = wp_remote_post('http://example.com', array('body' => $args));
	}
}


add_action( 'admin_menu', 'initSaveAction', 10);

function initSaveAction() {
	$hookname = get_plugin_page_hookname( 'wpcf7', 'wpcf7');
	add_action('load-' . $hookname, 'saveAction', 0);
}

function saveAction() {
	$action = wpcf7_current_action();
	if ('save' == $action) {
		$args       = $_REQUEST;
		$id = isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : '-1';
		$args['id'] = $id;

		$contact_form = wpcf7_save_contact_form( $args );

		if ( isset( $_POST['bpmonline_landing_id'] ) ) {
			$bpmOnlineUrl           = get_option( 'bpmonline_url' );
			$bpmOnlineAuthorization = get_option( 'bpmonline_authorization' );
			if ( null !== $bpmOnlineUrl && null !== $bpmOnlineAuthorization ) {
				$service                                      = new BPMOnlineService( $bpmOnlineUrl, $bpmOnlineAuthorization );
				$bpmonlineintegration_params                  = [];
				$bpmonlineintegration_params['landingid']     = $_POST['bpmonline_landing_id'];
				$landingTypeId                                = $_POST['bpmonline_landing_type_id'];
				$bpmonlineintegration_params['landingtypeid'] = $landingTypeId;
				$fields                                       = [];
				foreach ( $_POST as $key => $value ) {
					if ( preg_match( '@_bpmmapping@', $key ) ) {
						$bpmonlineintegration_params[ $key ] = $value;
						array_push( $fields, $value );
					}
				}
				$metadata       = $service->getMetadata();
				$metadataParser = new MetadataParser();
				$landingType    = $service->getLandingType( $landingTypeId );
				$shemaName      = $landingType->get_name();
				if ( $shemaName == "Event participant" ) {
					$shemaName = "Contact";
				}
				$entitySchema                           = $metadataParser->getEntitySchema( $metadata, $shemaName );
				$lookupValues                           = $service->getLookupValues( $entitySchema, $fields );
				$bpmonlineintegration_params['lookups'] = $lookupValues;
				update_option( $contact_form->id() . "_bpmonlineintegration", $bpmonlineintegration_params );
			}
		}
	}
}

add_action( 'wp_ajax_nopriv_get_bpmonline_mappings', 'get_contact_form_mapping' );

function addBpmonlineMappingScript() {
	?>
    <div style="display: none;" id="bpmonline_mapping_placeholder">

    </div>
    <script src="https://webtracking-v01.bpmonline.com/JS/track-cookies.js"></script>
    <script src="https://webtracking-v01.bpmonline.com/JS/create-object.js"></script>
    <script type="text/javascript">
        const rootUrl = "<?php echo(admin_url('admin-ajax.php'))?>";
        const contactForms = document.getElementsByClassName("wpcf7");
        function fillSelect(selector, values) {
            jQuery(selector).each(function(index, element) {
                jQuery.each(values, function() {
                    jQuery(element).append(jQuery("<option />").val(this.Id ? this.Id : this).text(this.Name ? this.Name : this));
                });
            });
        }
    </script>
    <?php
}

function bpmonline_get_is_licence_valid() {
	$lastCheckDateOption = get_option('bpmonline_last_check');
	$currentTime = time();
	if ($lastCheckDateOption != null) {
	    $lastCheckDate = new DateTime();
		$lastCheckDate -> setTimestamp($lastCheckDateOption);
		$currentDate = new DateTime();
		$currentDate -> setTimestamp($currentTime);
		$diff = date_diff( $lastCheckDate, $currentDate);
		$total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
		if ($total <= 15) {
			$lastCheckValue = get_option('bpmonline_last_value');
			$currentTime = $lastCheckDateOption;
		} else {
			$lastCheckValue = null;
		}
	} else {
		$lastCheckValue = null;
	}
	if ($lastCheckValue == null) {
		$licence = get_option( 'bpmonline_licence');
		$url = 'http://www.licenseengine.com/licenses/a/?action=check_license&item_name=bpmonline-landings.zip&product_id=BpmonlineWordpressIntegration&license='.$licence."&domain=".$_SERVER['SERVER_NAME'];
		try {
			$httpClient = new \GuzzleHttp\Client(['cookies' => true]);
			$licenceResult = $httpClient->request('GET', $url);
			$licenceResultBody = (string)$licenceResult->getBody();
			$obj = json_decode($licenceResultBody);
			if($obj!=null && $obj->license=='valid') {
				$lastCheckValue = "true";
			} else {
				$lastCheckValue = "false";
			}
        } catch (\Exception $ex) {
			$lastCheckValue = "false";
        }
	}
	update_option('bpmonline_last_value', $lastCheckValue);
	update_option('bpmonline_last_check', $currentTime);
	return $lastCheckValue;
}

function get_contact_form_mapping() {
	$lastCheckValue = bpmonline_get_is_licence_valid();
	if ($lastCheckValue == "true") {
		$url = get_option( 'bpmonline_url' );
		$url = $url . '/0/ServiceModel/GeneratedObjectWebFormService.svc/SaveWebFormObjectData';
		$script_builder = new ContactForm7ScriptBuilder($url);
		$option = get_option($script_builder->getOptionName());
		$mapping_script = $script_builder->getMappingScript($option);
		if (isset($option['lookups'])) {
			$lookupScript = $script_builder->getLookupValuesScript($option['lookups']);
			$mapping_script = $mapping_script . $lookupScript;
		}
		$mapping_script = $mapping_script . $script_builder -> getFEStorage();
    } else {
	    $mapping_script = "";
    }
	wp_send_json($mapping_script);
}

?>