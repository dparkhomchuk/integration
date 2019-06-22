<?php
/*
*/
require_once __DIR__ .  '/includes/repository/file-repository.php';

register_activation_hook( __FILE__, 'flex_bpmonline_files_activate' );

function flex_bpmonline_files_activate() {
}

register_uninstall_hook(__FILE__, 'flex_bpmonline_files_uninstall');

function flex_bpmonline_files_uninstall() {
}

add_action('flex-file-saved', 'flex_file_saved', 10, 2);

function flex_file_saved($integrationId, $path) {
	$filesRepository = new FileRepository();
	$filesRepository -> saveFile($integrationId, $path);
}

add_action('flex-send-file', 'flex_send_file', 10, 1);

class FlexBpmCredentials
{
	private $userName;
	private $userPassword;

	public function __construct($userName, $userPassword) {
		$this -> userName = $userName;
		$this -> userPassword = $userPassword;
	}

	public function __toString() {
		$result = "{\"UserName\":\"";
		$result = $result . $this -> userName;
		$result = $result . "\",\"UserPassword\":\"";
		$result = $result . $this -> userPassword;
		$result = $result . "\"}";
		return $result;
	}
}


class SendFileRequest
{
	private $filePath;
	private $entitySchemaName;
	private $fileId;
	private $parentColumnName;
	private $parentColumnValue;
	//private $mimeType: application/json

	public function __construct($filePath, $entitySchemaName, $integrationId) {
		$this -> filePath = $filePath;
		$this -> parentColumnName = $entitySchemaName;
		$this -> parentColumnValue = $integrationId;
		$this -> fileId = getGUID();
		$this -> entitySchemaName = $entitySchemaName == 'Lead' ? 'FileLead' : $entitySchemaName . 'File';
	}


	public function getMultiPart() {
		return array(
			'multipart' => [
				[
					'name' => 'files',
					'contents' => fopen($this ->filePath, 'r')
				],
				[
					'name' => 'totalFileLength',
					'contents' => filesize($this -> filePath)
				],
				[
					'name' => 'fileId',
					'contents' => $this -> fileId
				],
				[
					'name' => 'entitySchemaName',
					'contents' => $this -> entitySchemaName
				],
				[
					'name' => 'parentColumnName',
					'contents' => $this -> parentColumnName
				],
				[
					'name' => 'parentColumnValue',
					'contents' => $this -> parentColumnValue
				]/*,
		        [
		        	'name' => 'mimeType',
			        'contents' => $this -> mimeType
		        ]*/,
				[
					'name' => 'columnName',
					'contents' => 'Data'
				]
			]
		);
	}

}

function flex_send_file($params) {
	$integrationId = $params['integrationId'];
	$filesRepository = new FileRepository();
	$filePath = $filesRepository -> getFilePath($integrationId);
	if ($filePath == null) {
		return;
	}
	$decodedCredentials = base64_decode($params['authorization']);
	$credentialsArray = explode(':', $decodedCredentials);
	$login = $credentialsArray[0];
	$password = $credentialsArray[1];
	$httpClient = new \GuzzleHttp\Client(['cookies' => true]);
	$postData = new FlexBpmCredentials($login, $password);
	$options = array('body' => $postData, 'headers'=>array('Content-Type' => 'application/json'));
	$url = $params['url'];
	$result = $httpClient->request('POST',$url . "/ServiceModel/AuthService.svc/Login",  $options);
	$cookiesArray =$httpClient->getConfig('cookies')->toArray();
	foreach ($cookiesArray as $cookie) {
		if ($cookie['Name'] == 'BPMCSRF') {
			$bpmcsrf = $cookie['Value'];
		}
	}
	$fileRequest = new SendFileRequest($filePath, $params['entityName'], $integrationId );
	$options = $fileRequest -> getMultiPart();
	$options['headers'] = array('BPMCSRF' => $bpmcsrf);
	$result = $httpClient->request('POST',$url . "/0/rest/FileApiService/Upload",  $options);
	$filesRepository -> removeByIntegrationId($integrationId);
}


function getGUID(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		        .substr($charid, 0, 8).$hyphen
		        .substr($charid, 8, 4).$hyphen
		        .substr($charid,12, 4).$hyphen
		        .substr($charid,16, 4).$hyphen
		        .substr($charid,20,12)
		        .chr(125);// "}"
		return $uuid;
	}
}