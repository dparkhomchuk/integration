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


register_activation_hook( __FILE__, 'formidable_bpmonlineplugin_activate' );

function formidable_bpmonlineplugin_activate() {
}

register_uninstall_hook(__FILE__, 'formidable_bpmonlineplugin_uninstall');

function formidable_bpmonlineplugin_uninstall() {
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
				    $integration_key = substr($key, 0, strlen($key)-11);
					$bpmonlineintegration_params[ $integration_key] = $value;
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
				    $mappingKey = $value->id;
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
			update_option( $_POST['id'] . "_bpmonlineintegration", $bpmonlineintegration_params );
		}
	}

	return $options;
}

add_action('frm_entries_footer_scripts', 'set_custom_limit', 20, 2);

function set_custom_limit($fields, $form){
    $bpmonlinemapping = get_option($form->id . "_bpmonlineintegration");
	$bpmOnlineUrl           = get_option( 'bpmonline_url' );
	?>
    window.bpmonlineMapping = <?php echo json_encode($bpmonlinemapping); ?>;
    var config = {
    fields: {
        <?php foreach($bpmonlinemapping as $key=>$value) {
            if ($key != 'landingid') {
                echo("\"".$value."\":[id$=\"".$key."\"],");
            }
        } ?>
    },
    landingId:<?php echo("\"".$bpmonlinemapping['landingid']."\"");?>,
    serviceUrl:<?php echo("\"".$bpmOnlineUrl."\"");?> + "0/ServiceModel/GeneratedObjectWebFormService.svc/SaveWebFormObjectData",
    redirectUrl:""
    };
    landing.initLanding(config);
<?php
}

function bpmonline_init() {
	if(!is_admin()){
	} else {

		wp_enqueue_script($handle='frm-bpmonline-integrator', $src=plugin_dir_url(__FILE__). 'js/frm-bpmonline-integrator.js',
			$deps = array('jquery', 'wp-util'),
			$ver = false,
			$in_footer = true);
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

add_action( 'wp_ajax_bpmonlineRefreshCache', 'refresh_bpmonline_cache' );

function refresh_bpmonline_cache() {
    bpmonline_refreshcache();
	echo '<div id="message" class="updated fade"><p><strong>' . __('Cache refreshed.') . '</strong></p></div>';
    wp_die();
}

function bpmonline_refreshcache() {
	$url = get_option('bpmonline_url');
	$authorization = get_option('bpmonline_authorization');
	delete_option('bpmonline_metadata');
	$bpmOnlineService = new BPMOnlineService($url,  $authorization);
	$metadata = $bpmOnlineService -> getMetadata();
	$serializedMetadata = $metadata->saveXML();
	$compressedMetadData = gzcompress($serializedMetadata);
	update_option('bpmonline_metadata',$compressedMetadData);
	delete_option('bpmonline_landingpagestypes');
	$landingPagesTypes = $bpmOnlineService -> getLandingsPagesTypes();
	$landingPagesTypesSerialized = maybe_serialize($landingPagesTypes);
	update_option('bpmonline_landingpagestypes', $landingPagesTypesSerialized);
	delete_option('bpmonline_landings');
	$landings = $bpmOnlineService -> getLandings();
	$landingsSerialized = maybe_serialize($landings);
	update_option('bpmonline_landings', $landingsSerialized);
}


add_action('frm_after_create_entry', 'bpmonline_integration_datasend', 30, 2);
function bpmonline_integration_datasend($entry_id, $form_id){
	$settings = get_option($form_id.'_bpmonlineintegration');
	$formFieldsData = [];
	foreach ($_POST['item_meta'] as $key => $value) {
	    if ($key > 0) {
		    $bpmfield = $settings[$key];
		    $fieldObject = (object)['name'=>$bpmfield, 'value' => $value];
		    array_push($formFieldsData, $fieldObject);
        }
    }
    $landingid = $settings['landingid'];
	$data = (object) [
		'formData' => (object) [
		        'formFieldsData' => $formFieldsData,
                'formId' => $landingid,
        ],
	];

	$bpmUrl = get_option('bpmonline_url');

	$url = $bpmUrl ."/0/ServiceModel/GeneratedObjectWebFormService.svc/SaveWebFormObjectData";

    $args = array(
	    'method' => 'POST',
	    'timeout' => 45,
	    'redirection' => 5,
	    'httpversion' => '1.0',
	    'blocking' => true,
	    'headers' => array('Content-Type' => 'application/json; charset=UTF-8', 'Referer' => getallheaders()['Referer']),
	    'data_format' => 'body',
	    'body' => json_encode($data));

	$result = wp_remote_post($url, $args);

}

?>