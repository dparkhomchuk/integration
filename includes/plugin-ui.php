<?php


    class BpmCredentials
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

    require_once __DIR__ . '/persistence/source/bpmonline-service.php';

	if( isset($_POST['url'])) {
	    $url = $_POST['url'];
	    $login = $_POST['login'];
	    $password = $_POST['password'];
	    $licence = $_POST['licence'];
        $authorization = base64_encode($login.":".$password);
        $httpClient = new \GuzzleHttp\Client(['cookies' => true]);
        $postData = new BpmCredentials($login, $password);
        $options = array('body' => $postData, 'headers'=>array('Content-Type' => 'application/json'));
		try {
			$result = $httpClient->request('POST',$url . "/ServiceModel/AuthService.svc/Login",  $options);
			$resultBody = (string)$result->getBody();
			if (strpos($resultBody,"\"Message\":\"Invalid username or password specified.") == false) {
				/*$licenceurl = 'http://www.licenseengine.com/licenses/a/?action=check_license&item_name=bpmonline-landings.zip&product_id=BpmonlineWordpressIntegration&license='.$licence."&domain=".$_SERVER['SERVER_NAME'];
				$licenceResult = $httpClient->request('GET',$licenceurl);
				$licenceResultBody = (string)$licenceResult->getBody();
				$obj = json_decode($licenceResultBody);
				if(empty($obj) OR $obj->license!='valid') {
					echo '<div id="message" class="error"><p><strong>' . __('Invalid license.') . '</strong></p></div>';
                } else {*/
					update_option('bpmonline_url', $url);
					update_option('bpmonline_login', $login);
					update_option('bpmonline_authorization', $authorization);
                    update_option('bpmonline_licence', $licence);
					echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.') . '</strong></p></div>';
                //}
            } else {
				echo '<div id="message" class="error"><p><strong>' . __('Incorrect bpm\'online credentails.') . '</strong></p></div>';
            }
		} catch (\Exception $ex) {
			echo '<div id="message" class="error"><p><strong>' . __('Incorrect bpm\'online credentails.') . '</strong></p></div>';
		}
	}
?>
		<div id="<?php echo $P?>" class="wrap metabox-holder">
            <div id="poststuff">
		
                <h2>Bpm'online integration settings</h2>
                <div class="description">
                    <p><?php _e('Please fill in the link to your Bpm\'online website and credentials of a user with system administrator access rights. Your credentials will stored in secure environment', $P); ?>.</p>
                </div>

                <form method="post">
                    <input type="hidden" name="Forms3rdPartyIntegration" value=""/>
                    <fieldset class="postbox">
                        <div class="inside">
                            <div class="field">
                                <label for="dbg-email">Bpmonline url</label>
                                <input id="url" type="text" class="text" name="url" value="<?php if (null !== get_option('bpmonline_url')) echo(get_option('bpmonline_url')); ?>" required/>
                            </div>
                            <div class="field">
                                <label for="dbg-email">Bpmonline login</label>
                                <input id="login" type="text" class="text" name="login" value="<?php if (null !== get_option('bpmonline_login')) echo(get_option('bpmonline_login')); ?>" placeholder="your bpm'online login" required/>
                            </div>
                            <div class="field">
                                <label for="dbg-email">Bpmonline password</label>
                                <input id="password" type="password" class="text" name="password" value="<?php if (null !== get_option('bpmonline_authorization')) echo('AAAAAAAAAAAAA'); ?>" required  />
                            </div>
                            <div class="field">
                                <label for="dbg-email">License key</label>
                                <input id="licence" type="text" class="text" name="licence" value="<?php if (null !== get_option('bpmonline_licence')) echo(get_option('bpmonline_licence')); ?>" placeholder="your license key" required/>
                            </div>
                        </div>
                    </fieldset>

                    <div class="buttons">
                        <input type="submit" id="submit" name="submit" class="button button-primary" value="Save" />
                    </div>

                </form>

		    </div>
        </div>
        <script>
            var myInput = document.getElementById("password");
            myInput.addEventListener('focus', function(e) {e.target.value = '';}, true);
        </script>
