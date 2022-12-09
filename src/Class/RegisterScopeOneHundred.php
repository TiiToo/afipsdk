<?php
/**
 * SDK for AFIP Register Scope Ten (ws_sr_padron_a10)
 * 
 * @link http://www.afip.gob.ar/ws/ws_sr_padron_a10/manual_ws_sr_padron_a10_v1.1.pdf WS Specification
 *
 * @author 	Afip SDK
 * @package Afip
 * @version 1.0
 **/

class RegisterScopeOneHundred extends AfipWebService {

	var $soap_version 	= SOAP_1_1;
	var $WSDL 			= 'ws_sr_padron_a100.wsdl';
	var $URL 			= 'https://awshomo.afip.gov.ar/sr-parametros/webservices/parameterServiceA100';
	var $WSDL_TEST 		= 'ws_sr_padron_a100.wsdl';
	var $URL_TEST 		= 'https://awshomo.afip.gov.ar/sr-parametros/webservices/parameterServiceA100';

	/**
	 * Asks to web service for servers status {@see WS 
	 * Specification item 3.1}
	 *
	 * @since 1.0
	 *
	 * @return object { appserver => Web Service status, 
	 * dbserver => Database status, authserver => Autentication 
	 * server status}
	**/
	public function GetServerStatus()
	{
		return $this->ExecuteRequest('dummy');
	}

	/**
	 * Asks to web service for taxpayer details {@see WS 
	 * Specification item 3.2}
	 *
	 * @since 1.0
	 *
	 * @throws Exception if exists an error in response 
	 *
	 * @return object|null if taxpayer does not exists, return null,  
	 * if it exists, returns persona property of response {@see 
	 * WS Specification item 3.2.2}
	**/
	public function getParameterCollectionByName($data)
	{
		$ta = $this->afip->GetServiceTA('ws_sr_padron_a100');
		
		$params = array(
			'token' 			=> $ta->token,
			'sign' 				=> $ta->sign,
			'cuitRepresentada'              => $this->afip->CUITOrganismo,
			'collectionName' 		=> $data
		);

		try {
			return $this->ExecuteRequest('getParameterCollectionByName', $params);
		} catch (Exception $e) {
			if (strpos($e->getMessage(), 'No existe') !== FALSE)
				return NULL;
			else
				throw $e;
		}
	}

	/**
	 * Sends request to AFIP servers
	 * 
	 * @since 0.7
	 *
	 * @param string 	$operation 	SOAP operation to do 
	 * @param array 	$params 	Parameters to send
	 *
	 * @return mixed Operation results 
	 **/
	public function ExecuteRequest($operation, $params = array())
	{
		$params = array_replace($this->GetWSInitialRequest($operation), $params); 

		$results = parent::ExecuteRequest($operation, $params);

		$this->_CheckErrors($operation, $results);

		return $results->{'parameterCollectionReturn'};
	}

	/**
	 * Make default request parameters for most of the operations
	 * 
	 * @since 0.7
	 *
	 * @param string $operation SOAP Operation to do 
	 *
	 * @return array Request parameters  
	 **/
	private function GetWSInitialRequest($operation)
	{
		if ($operation == 'FEDummy') {
			return array();
		}

		$ta = $this->afip->GetServiceTA('ws_sr_padron_a100');

		return array(
			'Auth' => array( 
				'Token' => $ta->token,
				'Sign' 	=> $ta->sign,
				'cuitSolicitante' 	=> $this->afip->CUIT
				)
		);
	}

	/**
	 * Check if occurs an error on Web Service request
	 * 
	 * @since 0.7
	 *
	 * @param string 	$operation 	SOAP operation to check 
	 * @param mixed 	$results 	AFIP response
	 *
	 * @throws Exception if exists an error in response 
	 * 
	 * @return void 
	 **/
	private function _CheckErrors($operation, $results)
	{
		$res = $results->{'parameterCollectionReturn'};

		

		if (isset($res->Errors)) {
			$err = is_array($res->Errors->Err) ? $res->Errors->Err[0] : $res->Errors->Err;
			throw new Exception('('.$err->Code.') '.$err->Msg, $err->Code);
		}
	}
}

