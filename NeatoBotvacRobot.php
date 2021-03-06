<?php

/*
* Neato Botvac Robot
* Makes requests against the robot
*
* PHP port based on https://github.com/kangguru/botvac
*
* Author: Tom Rosenback tom.rosenback@gmail.com  2016
*/

require_once("NeatoBotvacApi.php");

class NeatoBotvacRobot {
	protected $baseUrl = "https://nucleo.neatocloud.com/vendors/neato/robots/";
	protected $metaUrl;
	protected $serial;
	protected $secret;

	public function __construct($serial, $secret, $model = "default" ) {
		$this->serial = $serial;
		$this->secret = $secret;

		if ($model == "VR200") {

			$this->baseUrl = "https://nucleo.ksecosys.com:4443/vendors/vorwerk/robots/";
			$this->metaUrl = "https://beehive.ksecosys.com/robots/";
		}
	}

	public function getState() {
		return $this->doAction("getRobotState");
	}

	public function startCleaning($category = 2, $eco = 1, $size = 200, $modifier = 1) {
		$params = array("category" => $category, "mode" => $eco, "spotWidth" => $size, "spotHeight" => $size, "modifier" => $modifier);
		return $this->doAction("startCleaning", $params);
	}

	public function pauseCleaning() {
		return $this->doAction("pauseCleaning");
	}
	
	public function resumeCleaning() {
		return $this->doAction("resumeCleaning");
	}

	public function stopCleaning() {
		return $this->doAction("stopCleaning");
	}

	public function sendToBase() {
		return $this->doAction("sendToBase");
	}

	public function enableSchedule() {
		return $this->doAction("enableSchedule");
	}

	public function disableSchedule() {
		return $this->doAction("disableSchedule");
	}

	public function getSchedule() {
		return $this->doAction("getSchedule");
	}
	
	public function setSchedule($events = false) {
		$params = array("type" => 1, "events" => $events);
		return $this->doAction("setSchedule", $params);
	}
	
	public function setName($name = false, $token = false) {
		$params = array("name" => $name);
		return $this->doMeta($params, $token);
	}
	

	protected function doAction($command, $params = false) {
		$result = array("message" => "no serial or secret");

		if($this->serial !== false && $this->secret !== false) {
			$payload = array("reqId" => "77", "cmd" => $command);

			if($params !== false) {
				$payload["params"] = $params;
			}

			$payload = json_encode($payload);
			$date = gmdate("D, d M Y H:i:s")." GMT";
			$data = implode("\n", array(strtolower($this->serial), $date, $payload));
			$hmac = hash_hmac("sha256", $data, $this->secret);

			$headers = array(
	    	"Date: ".$date,
	    	"Authorization: NEATOAPP ".$hmac
			);
			
			$result = NeatoBotvacApi::request($this->baseUrl.$this->serial."/messages", $payload, "POST", $headers);		
		}

		return $result;
	}
	
	
	protected function doMeta($params = false, $token = false) {
		$result = array("message" => "no name, token or serial");

		if($this->serial !== false && $params !== false && $token !== false) {

			$headers = array(
	    	"Authorization: Token token=".$token
			);

			$result = NeatoBotvacApi::request($this->metaUrl.$this->serial, $params, "PUT", $headers);
		}

		return $result;
	}
}
