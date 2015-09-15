<?php 

if (!class_exists('sidekick')) {
	class sidekick{

		// var $url          = 'https://apiv2.sidekick.pro/';
		var $url             = 'https://api.staging.sidekick.pro/';
		var $email           = null;
		var $password        = null;
		var $subscription_id = null;

		function login(){

			if (!$this->email || !$this->password) {
				echo "Missing SIDEKICK credentials!";
				exit(1);
			}

			$result = $this->send('login', null, array('email' => $this->email, 'password' => $this->password));
			// var_dump($result);
			if (!$result->success) {
				echo "Error logging in - $result->message\n";
				exit(1);
				return false;
			} else {
				pm_Settings::set('sidekick_token', $result->payload->token->value);			
				return true;
			}

		}

		function generate_key(){

			$response = pm_ApiRpc::getService()->call("<server><get><gen_info/></get></server>");

			if ($response) {
				$serverName = $response->server->get->result->gen_info->server_name;
				$serverGuid = $response->server->get->result->gen_info->server_guid;
				$data = array('domainName' => "$serverName - $serverGuid");
			} else {
				$data = array('domainName' => 'Unknown');
			}

			if ($this->subscription_id) {
				$data['subscriptionId'] = $this->subscription_id;
			}

			$result = $this->send('domains',null,$data);

			if (isset($result->payload->domainKey)) {
				pm_Settings::set('sidekick_activation_id', $result->payload->domainKey);			
				pm_Settings::set('sidekick_activation_key_id', $result->payload->id);			
				return true;
			} else {
				exit(1);
			}

		}

		function ping(){
			$sidekick_activation_id = pm_Settings::get('sidekick_activation_id');
			$result = $this->send('ping',null,array('domainKey' => $sidekick_activation_id));
		}

		function get_subscription_id(){

			$result = $this->send('users/subscriptions','GET');

			if ($result) {
				$subscription_id = $result->payload[0]->id;
				echo "subscription_id = $subscription_id";
				return $subscription_id;
			} else {
				echo "No subscription Id";
				exit(1);
			}

		}

		function delete_key(){

			$key_id = pm_Settings::get('sidekick_activation_key_id');

			if (!$key_id) {
				echo "No domain key id to delete the domain";
				exit(1);
			}

			$result = $this->send('domains','DELETE',array('domainId' => $key_id),true);

			if ($result->success) {
				pm_Settings::clean();
				return true;
			} else {
				echo "$result->message - $key_id";
				// Clean key anyway...
				pm_Settings::clean();
				return false;
			}
		}

		function send($endpoint, $type = 'POST',$data = array(),$queryParams = false){
			return $this->send_request_curl($endpoint, $type, $data,$queryParams);
		}

		function send_request_curl($endpoint, $type = 'POST',$data, $queryParams){

			if ($queryParams) {
				$endpoint .= '?' . http_build_query($data);
			}

			$ch = curl_init($this->url . $endpoint);


			$headers = array('Content-Type:application/json');
			if ($endpoint !== 'login') {
				$token = pm_Settings::get('sidekick_token');

				if (!$token) {
					echo "No token";
					exit(1);
				}

				$headers[] = "Authorization: $token";
				$data['Authorization'] = $token; 
			}

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			return json_decode($result);
		}

	}
}
