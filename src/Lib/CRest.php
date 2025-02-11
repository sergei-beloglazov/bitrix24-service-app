<?php

namespace Bitrix24Integration\Lib;

use mysqli_result;
use Bitrix24Integration\Db\Clients;
use Bitrix24Integration\Db\DbStorage;
use Bitrix24Integration\UserList\RequestSender;
use Bitrix24Integration\UserList\RequestDto;

//Load an application configuration
require_once($_SERVER['DOCUMENT_ROOT'] . '/../settings/.app_settings.php');

class CRest
{
	const VERSION = '1.36';
	const BATCH_COUNT    = 50; //count batch 1 query
	const TYPE_TRANSPORT = 'json'; // json or xml

	/** @var DbStorage DB Manager*/
	public static $dbStorage = null;
	/** @var string Client token (normalized, without wrong symbols)	 */
	public static $clientDbtoken = "";
	/** @var array Client data from DB */
	public static $clientData = [];
	/** @var string MEMBER_ID, unique client Bitrix24 identifier */
	public static $memberId = "";



	/**
	 * Createa new DB connection if not created
	 *
	 * @return array $result result data
	 * 
	 */
	public static function initDbConnection()
	{
		//Check if already connected
		if (self::$dbStorage !== null) {
			return ["success" => true];
		}
		//Connect to DB
		self::$dbStorage = new DbStorage();

		//Check DB
		if (!self::$dbStorage->isSuccess()) {
			return array_merge([
				"success" => false,
				'error' => "DB connection error: " . self::$dbStorage->getLastError()
			]);
		}
	}
	/**
	 * call where install application even url
	 * only for rest application, not webhook
	 */

	public static function installApp()
	{
		$result = [
			'rest_only' => true,
			'install' => false
		];
		$clientToken = $_REQUEST["service_app_token"];
		//Filter token
		self::$clientDbtoken = preg_replace("/[^0-9a-zA-Z-]/", "", $clientToken);

		//Check if client token is empty
		if (strlen(self::$clientDbtoken) == 0) {
			return array_merge($result, ['error' => "Empty client token"]);
		}
		//Connect to DB
		self::$dbStorage = new DbStorage();

		//Check DB
		if (!self::$dbStorage->isSuccess()) {
			return array_merge($result, ['error' => "DB connection error: " . self::$dbStorage->getLastError()]);
		}
		//Clients table
		$clientTable = new Clients(self::$dbStorage->getMysqli());

		//Check if there is no such client
		/** @var mysqli_result */
		$searchResult = $clientTable->findClientByToken(self::$clientDbtoken);
		//Check success
		if ($searchResult === false) {
			return array_merge($result, ['error' => "DB query error: " . $clientTable->getLastError()]);
		}

		if ($searchResult->num_rows == 0) {
			return array_merge($result, ['error' => "Wrong token. Client not found"]);
		}
		//Load client data
		self::$clientData = $searchResult->fetch_array(MYSQLI_ASSOC);

		//Check if access token is found
		if (empty($_REQUEST['access_token'])) {
			return array_merge($result, ['error' => "Wrong token. Client not found"]);
		}
		//Save data
		$saveResult = $clientTable->saveClient([
			'id' => intval(self::$clientData['id']),
			'member_id' => htmlspecialchars($_REQUEST['member_id']),
			'access_token' => htmlspecialchars($_REQUEST['access_token']),
			'expires_in' => htmlspecialchars($_REQUEST['expires_in']),
			'application_token' => htmlspecialchars($_REQUEST['application_token']),
			'refresh_token' => htmlspecialchars($_REQUEST['refresh_token']),
			'domain' => htmlspecialchars($_REQUEST['domain']),
			'client_endpoint' => 'https://' . htmlspecialchars($_REQUEST['domain']) . '/rest/',
		]);
		$result['rest_only'] = false;
		$result['install'] = $clientTable->isSuccess();

		//Check success
		if ($saveResult === false) {
			return array_merge($result, ['error' => "DB query error: " . $clientTable->getLastError()]);
		}
		return $result;
	}

	/**
	 * Installation with logging
	 */
	public static function runInstallApp()
	{
		//Try to install
		$result = self::installApp();
		//Log
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'installApp');
		return $result;
	}


	/**
	 * Loads settings from DB by member_id parameter from request
	 */
	public static function loadSettings()
	{
		$result = [
			'load' => false
		];
		$memberId = $_REQUEST['MEMBER_ID'] ?? $_REQUEST['member_id'];
		self::$memberId = $memberId;

		//Check if member_id is empty
		if (strlen(self::$memberId) == 0) {
			return array_merge($result, ['error' => "Received an empty MEMBER_ID"]);
		}
		//Connect to DB
		self::$dbStorage = new DbStorage();

		//Check DB
		if (!self::$dbStorage->isSuccess()) {
			return array_merge($result, ['error' => "DB connection error: " . self::$dbStorage->getLastError()]);
		}

		//Clients table
		$clientTable = new Clients(self::$dbStorage->getMysqli());

		//Check if there is no such client
		/** @var mysqli_result */
		$searchResult = $clientTable->findClientByMemberId(self::$memberId);
		//Check success
		if (($searchResult === false) || (!$clientTable->isSuccess())) {
			return array_merge($result, ['error' => "DB query error: " . $clientTable->getLastError()]);
		}
		//Check selected data
		if ($searchResult->num_rows == 0) {
			return array_merge($result, ['error' => "Wrong MEMBER_ID. Client is not found"]);
		}
		//Load client data
		self::$clientData = $searchResult->fetch_array(MYSQLI_ASSOC);

		$result['load'] = $clientTable->isSuccess();
		$result['loadData'] = self::$clientData;
		return $result;
	}

	/**
	 * Loads settings from DB by member_id parameter from request and logs it
	 */
	public static function runLoadSettings()
	{
		//Try to load settings
		$result = self::loadSettings();
		//Log
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'loadAppSettings');
		return $result;
	}

	/**
	 * Save settings to DB, received in request data
	 *
	 * @return array
	 * 
	 */
	public static function saveSettings()
	{
		$result = ['save' => false];
		//Check if client data is already loaded
		if (empty(self::$clientData)) {
			return array_merge($result, ['error' => "Error: client data is empty"]);
		}
		//Check if manager id is present
		if (intval($_REQUEST["manager_id"][1]) == 0) {
			return array_merge($result, ['error' => "Error: a manager didn't selected"]);
		}
		//Show selected manager
		echo '<b>Selected manager:</b> [' . intval($_REQUEST["manager_id"][1]) . '] '
			. htmlspecialchars($_REQUEST["manager_name"][1]) . '<br>';

		//TODO: handle service_app_token change

		$result['save'] = true;
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'appSettings');
		return $result;
	}


	/**
	 * Saves settings and make log
	 */
	public static function runSaveSettings()
	{
		//Try to save settings
		$result = self::saveSettings();
		//Log
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'appSettings');
		return $result;
	}

	/**
	 * @var $arParams array
	 * $arParams = [
	 *      'method'    => 'some rest method',
	 *      'params'    => []//array params of method
	 * ];
	 * @return mixed array|string|boolean curl-return or error
	 *
	 */
	protected static function callCurl($arParams)
	{
		if (!function_exists('curl_init')) {
			return [
				'error'             => 'error_php_lib_curl',
				'error_information' => 'need install curl lib'
			];
		}
		//$arSettings = static::getAppSettings();
		if (!empty(self::$clientData)) {
			if (isset($arParams['this_auth']) && $arParams['this_auth'] == 'Y') {
				$url = 'https://oauth.bitrix.info/oauth/token/';
			} else {
				$url = self::$clientData["client_endpoint"] . $arParams['method'] . '.' . static::TYPE_TRANSPORT;
				if (empty(self::$clientData['is_web_hook']) || self::$clientData['is_web_hook'] != 'Y') {
					$arParams['params']['auth'] = self::$clientData['access_token'];
				}
			}

			$sPostFields = http_build_query($arParams['params']);

			try {
				$obCurl = curl_init();
				curl_setopt($obCurl, CURLOPT_URL, $url);
				curl_setopt($obCurl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($obCurl, CURLOPT_POSTREDIR, 10);
				curl_setopt($obCurl, CURLOPT_USERAGENT, 'Bitrix24 CRest PHP ' . static::VERSION);
				if ($sPostFields) {
					curl_setopt($obCurl, CURLOPT_POST, true);
					curl_setopt($obCurl, CURLOPT_POSTFIELDS, $sPostFields);
				}
				curl_setopt(
					$obCurl,
					CURLOPT_FOLLOWLOCATION,
					(isset($arParams['followlocation']))
						? $arParams['followlocation'] : 1
				);
				if (defined("C_REST_IGNORE_SSL") && C_REST_IGNORE_SSL === true) {
					curl_setopt($obCurl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($obCurl, CURLOPT_SSL_VERIFYHOST, false);
				}
				$out = curl_exec($obCurl);
				$info = curl_getinfo($obCurl);
				if (curl_errno($obCurl)) {
					$info['curl_error'] = curl_error($obCurl);
				}
				if (static::TYPE_TRANSPORT == 'xml' && (!isset($arParams['this_auth']) || $arParams['this_auth'] != 'Y')) //auth only json support
				{
					$result = $out;
				} else {
					$result = static::expandData($out);
				}
				curl_close($obCurl);

				if (!empty($result['error'])) {
					if (($result['error'] == 'expired_token' || $result['error'] == 'invalid_token') && empty($arParams['this_auth'])) {
						$result = static::GetNewAuth($arParams);
					} else {
						$arErrorInform = [
							'expired_token'          => 'expired token, cant get new auth? Check access oauth server.',
							'invalid_token'          => 'invalid token, need reinstall application',
							'invalid_grant'          => 'invalid grant, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
							'invalid_client'         => 'invalid client, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
							'QUERY_LIMIT_EXCEEDED'   => 'Too many requests, maximum 2 query by second',
							'ERROR_METHOD_NOT_FOUND' => 'Method not found! You can see the permissions of the application: CRest::call(\'scope\')',
							'NO_AUTH_FOUND'          => 'Some setup error b24, check in table "b_module_to_module" event "OnRestCheckAuth"',
							'INTERNAL_SERVER_ERROR'  => 'Server down, try later'
						];
						if (!empty($arErrorInform[$result['error']])) {
							$result['error_information'] = $arErrorInform[$result['error']];
						}
					}
				}
				if (!empty($info['curl_error'])) {
					$result['error'] = 'curl_error';
					$result['error_information'] = $info['curl_error'];
				}

				static::setLog(
					[
						'url'    => $url,
						'info'   => $info,
						'params' => $arParams,
						'result' => $result
					],
					'callCurl'
				);

				return $result;
			} catch (\Exception $e) {
				static::setLog(
					[
						'message' => $e->getMessage(),
						'code' => $e->getCode(),
						'trace' => $e->getTrace(),
						'params' => $arParams
					],
					'exceptionCurl'
				);

				return [
					'error' => 'exception',
					'error_exception_code' => $e->getCode(),
					'error_information' => $e->getMessage(),
				];
			}
		} else {
			static::setLog(
				[
					'params' => $arParams
				],
				'emptySetting'
			);
		}

		return [
			'error'             => 'no_install_app',
			'error_information' => 'error install app, pls install local application '
		];
	}

	/**
	 * Generate a request for callCurl()
	 *
	 * @var $method string
	 * @var $params array method params
	 * @return mixed array|string|boolean curl-return or error
	 */

	public static function call($method, $params = [])
	{
		$arPost = [
			'method' => $method,
			'params' => $params
		];
		if (defined('C_REST_CURRENT_ENCODING')) {
			$arPost['params'] = static::changeEncoding($arPost['params']);
		}

		$result = static::callCurl($arPost);
		return $result;
	}

	/**
	 * @example $arData:
	 * $arData = [
	 *      'find_contact' => [
	 *          'method' => 'crm.duplicate.findbycomm',
	 *          'params' => [ "entity_type" => "CONTACT",  "type" => "EMAIL", "values" => array("info@bitrix24.com") ]
	 *      ],
	 *      'get_contact' => [
	 *          'method' => 'crm.contact.get',
	 *          'params' => [ "id" => '$result[find_contact][CONTACT][0]' ]
	 *      ],
	 *      'get_company' => [
	 *          'method' => 'crm.company.get',
	 *          'params' => [ "id" => '$result[get_contact][COMPANY_ID]', "select" => ["*"],]
	 *      ]
	 * ];
	 *
	 * @var $arData array
	 * @var $halt   integer 0 or 1 stop batch on error
	 * @return array
	 *
	 */

	public static function callBatch($arData, $halt = 0)
	{
		$arResult = [];
		if (is_array($arData)) {
			if (defined('C_REST_CURRENT_ENCODING')) {
				$arData = static::changeEncoding($arData);
			}
			$arDataRest = [];
			$i = 0;
			foreach ($arData as $key => $data) {
				if (!empty($data['method'])) {
					$i++;
					if (static::BATCH_COUNT >= $i) {
						$arDataRest['cmd'][$key] = $data['method'];
						if (!empty($data['params'])) {
							$arDataRest['cmd'][$key] .= '?' . http_build_query($data['params']);
						}
					}
				}
			}
			if (!empty($arDataRest)) {
				$arDataRest['halt'] = $halt;
				$arPost = [
					'method' => 'batch',
					'params' => $arDataRest
				];
				$arResult = static::callCurl($arPost);
			}
		}
		return $arResult;
	}


	/**
	 * Getting a new authorization and sending a request for the 2nd time
	 *
	 * @var $arParams array request when authorization error returned
	 * @return array query result from $arParams
	 *
	 */

	private static function GetNewAuth($arParams)
	{
		$result = [];
		//$arSettings = static::getAppSettings();

		if (!empty(self::$clientData)) {
			$arParamsAuth = [
				'this_auth' => 'Y',
				'params'    =>
				[
					'client_id'     => C_REST_CLIENT_ID,
					'grant_type'    => 'refresh_token',
					'client_secret' => C_REST_CLIENT_SECRET,
					'refresh_token' => self::$clientData["refresh_token"],
				]
			];
			$newData = static::callCurl($arParamsAuth);
			//Update tokens
			self::$clientData['access_token'] = $newData['access_token'];
			self::$clientData['refresh_token'] = $newData['refresh_token'];
			if (static::saveNewClientTokens($newData)) {
				$arParams['this_auth'] = 'N';
				$result = static::callCurl($arParams);
			}
		}
		return $result;
	}

	/**
	 * Can overridden this method to change the data storage location.
	 * @var $arSettings array settings application
	 * @return boolean is successes save data for setSettingData()
	 */

	protected static function saveNewClientTokens($arSettings)
	{
		//Update DB connection
		$connectionResult = self::initDbConnection();
		//Check DB
		if ($connectionResult["success"] !== true) {
			return $connectionResult;
		}
		//Clients table
		$clientTable = new Clients(self::$dbStorage->getMysqli());
		//Update tokens
		$saveResult = $clientTable->saveClientTokens(self::$clientData);
		//Success
		return ["success" => $saveResult, "error" => $clientTable->getLastError()];
	}

	/**
	 * @var $data mixed
	 * @var $encoding boolean true - encoding to utf8, false - decoding
	 *
	 * @return string json_encode with encoding
	 */
	protected static function changeEncoding($data, $encoding = true)
	{
		if (is_array($data)) {
			$result = [];
			foreach ($data as $k => $item) {
				$k = static::changeEncoding($k, $encoding);
				$result[$k] = static::changeEncoding($item, $encoding);
			}
		} else {
			if ($encoding) {
				$result = iconv(C_REST_CURRENT_ENCODING, "UTF-8//TRANSLIT", $data);
			} else {
				$result = iconv("UTF-8", C_REST_CURRENT_ENCODING, $data);
			}
		}

		return $result;
	}

	/**
	 * @var $data mixed
	 * @var $debag boolean
	 *
	 * @return string json_encode with encoding
	 */
	protected static function wrapData($data, $debag = false)
	{
		if (defined('C_REST_CURRENT_ENCODING')) {
			$data = static::changeEncoding($data, true);
		}
		$return = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

		if ($debag) {
			$e = json_last_error();
			if ($e != JSON_ERROR_NONE) {
				if ($e == JSON_ERROR_UTF8) {
					return 'Failed encoding! Recommended \'UTF - 8\' or set define C_REST_CURRENT_ENCODING = current site encoding for function iconv()';
				}
			}
		}

		return $return;
	}

	/**
	 * @var $data mixed
	 * @var $debag boolean
	 *
	 * @return string json_decode with encoding
	 */
	protected static function expandData($data)
	{
		$return = json_decode($data, true);
		if (defined('C_REST_CURRENT_ENCODING')) {
			$return = static::changeEncoding($return, false);
		}
		return $return;
	}



	/**
	 * Can overridden this method to change the log data storage location.
	 *
	 * @var $arData array of logs data
	 * @var $type   string to more identification log data
	 * @return boolean is successes save log data
	 */

	public static function setLog($arData, $type = '')
	{
		$return = false;
		if (!defined("C_REST_BLOCK_LOG") || C_REST_BLOCK_LOG !== true) {
			if (defined("C_REST_LOGS_DIR")) {
				$path = C_REST_LOGS_DIR;
			} else {
				$path = $_SERVER['DOCUMENT_ROOT'] . '/../logs/';
			}
			$path .= date("Y-m-d/H") . '/';

			if (!file_exists($path)) {
				@mkdir($path, 0775, true);
			}

			$path .= time() . '_' . $type . '_' . rand(1, 9999999) . 'log';
			if (!defined("C_REST_LOG_TYPE_DUMP") || C_REST_LOG_TYPE_DUMP !== true) {
				$jsonLog = static::wrapData($arData);
				if ($jsonLog === false) {
					$return = file_put_contents($path . '_backup.txt', var_export($arData, true));
				} else {
					$return = file_put_contents($path . '.json', $jsonLog);
				}
			} else {
				$return = file_put_contents($path . '.txt', var_export($arData, true));
			}
		}
		return $return;
	}

	/**
	 * check minimal settings server to work CRest
	 * @var $print boolean
	 * @return array of errors
	 */
	public static function checkServer($print = true)
	{
		$return = [];

		//check curl lib install
		if (!function_exists('curl_init')) {
			$return['curl_error'] = 'Need install curl lib.';
		}

		//creat setting file
		file_put_contents(__DIR__ . '/settings_check.json', static::wrapData(['test' => 'data']));
		if (!file_exists(__DIR__ . '/settings_check.json')) {
			$return['setting_creat_error'] = 'Check permission! Recommended: folders: 775, files: 664';
		}
		unlink(__DIR__ . '/settings_check.json');
		//creat logs folder and files
		$path = __DIR__ . '/logs/' . date("Y-m-d/H") . '/';
		if (!mkdir($path, 0775, true) && !file_exists($path)) {
			$return['logs_folder_creat_error'] = 'Check permission! Recommended: folders: 775, files: 664';
		} else {
			file_put_contents($path . 'test.txt', var_export(['test' => 'data'], true));
			if (!file_exists($path . 'test.txt')) {
				$return['logs_file_creat_error'] = 'check permission! recommended: folders: 775, files: 664';
			}
			unlink($path . 'test.txt');
		}

		if ($print === true) {
			if (empty($return)) {
				$return['success'] = 'Success!';
			}
			echo '<pre>';
			print_r($return);
			echo '</pre>';
		}

		return $return;
	}

	/**
	 * Set the value of dbStorage
	 *
	 * @return  self
	 */
	public function setDbStorage($dbStorage)
	{
		$this->dbStorage = $dbStorage;
		return $this;
	}

	/*********/
	/* ADMIN */
	/*********/

	/**
	 * Loads client list
	 */
	public static function getClientsList()
	{
		$result = [
			'load' => false
		];

		//Connect to DB
		self::$dbStorage = new DbStorage();

		//Check DB
		if (!self::$dbStorage->isSuccess()) {
			return array_merge($result, ['error' => "DB connection error: " . self::$dbStorage->getLastError()]);
		}

		//Clients table
		$clientTable = new Clients(self::$dbStorage->getMysqli());

		/** @var mysqli_result */
		$searchResult = $clientTable->getClientsList();
		//Check success
		if (($searchResult === false) || (!$clientTable->isSuccess())) {
			return array_merge($result, ['error' => "DB query error: " . $clientTable->getLastError()]);
		}

		if ($searchResult->num_rows == 0) {
			return array_merge($result, ['error' => "Clients not found"]);
		}
		//Clients list
		$clients = [];
		//Load client data
		while ($client = $searchResult->fetch_array(MYSQLI_ASSOC)) {
			$clients[] = $client;
		}

		$result['load'] = $clientTable->isSuccess();
		$result['loadData'] = $clients;
		return $result;
	}

	/**
	 * Loads settings from DB by member_id parameter from request and logs it
	 */
	public static function runGetClientsList()
	{
		//Try to load settings
		$result = self::getClientsList();
		//Log
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'adminClientsList');
		return $result;
	}


	/**
	 * Get Bitrix24 user list
	 */
	public static function getBitrix24Users()
	{
		$result = [
			'send' => false
		];

		//Check Fields
		if (!isset($_POST["CLIENT_ID"]) || (!(intval($_POST["CLIENT_ID"]) > 0))) {
			return array_merge($result, ['error' => "Please select a client"]);
		}
		//Client ID
		$clientId = intval($_POST["CLIENT_ID"]);


		/* Load client */
		//Connect to DB
		self::$dbStorage = new DbStorage();

		//Check DB
		if (!self::$dbStorage->isSuccess()) {
			return array_merge($result, ['error' => "DB connection error: " . self::$dbStorage->getLastError()]);
		}
		//Clients table
		$clientTable = new Clients(self::$dbStorage->getMysqli());
		//Check if there is no such client
		/** @var mysqli_result */
		$searchResult = $clientTable->findClientById($clientId);
		//Check success
		if (($searchResult === false) || (!$clientTable->isSuccess())) {
			return array_merge($result, ['error' => "DB query error: " . $clientTable->getLastError()]);
		}
		//Check selected data
		if ($searchResult->num_rows == 0) {
			return array_merge($result, ['error' => "Wrong MEMBER_ID. Client is not found"]);
		}
		//Load client data
		self::$clientData = $searchResult->fetch_array(MYSQLI_ASSOC);

		//Get results from Bitrix24
		$sendResult = (new RequestSender(new RequestDto()))
			//Send req to Bitrix24 CRM
			->sendUserListRequest();

		//Success result
		$result = [
			'send' => $sendResult->success,
			'error' => $sendResult->errorMessage
		];

		//Success result
		//$result['send'] = true;
		return $result;
	}

	/**
	 * Get Bitrix24 user list
	 */
	public static function runGetBitrix24Users()
	{
		//Try to load settings
		$result = self::getBitrix24Users();
		//Log
		static::setLog(['request' => $_REQUEST, 'result' => $result], 'adminSendCallback');
		return $result;
	}
}
