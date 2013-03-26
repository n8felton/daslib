<?php

function generateGUID(){
	if (function_exists('com_create_guid') === TRUE){
		return trim(com_create_guid(), '{}');
	} else{
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
}

class DasLib{
	private $soapClient			= NULL;
	private $guid				= "";
	private $applicationName	= "";
	private $serviceTags		= "";
	private $assets				= "";
	private $wsdl 				= "http://xserv.dell.com/services/assetservice.asmx?WSDL";
	
	function __construct($guid,$applicationName,$serviceTags){
		if(!class_exists('SoapClient')) {
			throw new DasException('Looks like your PHP lacks SOAP support');
        }
		
		if(!preg_match('(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})', $guid)){
			throw new DasException('Invalid GUID: ' . $guid);
		}
		
		$this->soapClient = new SoapClient($this->wsdl, array('soap_version' => SOAP_1_2,'features' => SOAP_SINGLE_ELEMENT_ARRAYS,'trace' => TRUE));
		
		if(!$this->soapClient){
			throw new DasException('Failed to create a SOAP client.');
		}
		
		$this->guid 			= $guid;
		$this->applicationName 	= $applicationName;
		$this->serviceTags 		= $serviceTags;
	}
	
	public function printAllHTML(){
		print "GUID:\t\t\t" . $this->__getGUID() . "<br/>";
		print "Application Name:\t" . $this->__getApplicationName() . "<br/>";
		print "Service Tags:\t\t" . $this->__getServiceTags() . "<br/>";
		print "Number of Assets:\t" . $this->getNumAssets() . "<br/>";
		for($i=0;$i<$this->getNumAssets();$i++){
			print "Service Tags:\t\t" . $this->getServiceTag($i) . "<br/>";
			print "System ID:\t\t" . $this->getSystemID($i) . "<br/>";
			print "BUID:\t\t\t" . $this->getBuid($i) . "<br/>";
			print "Region:\t\t\t" . $this->getRegion($i) . "<br/>";
			print "System Type:\t\t" . $this->getSystemType($i) . "<br/>";
			print "System Model:\t\t" . $this->getSystemModel($i) . "<br/>";
			print "Ship Date:\t\t" . $this->getSystemShipDate($i) . "<br/>";
			print "Entitlements:\t\t" . $this->getNumEntitlements($i) . "<br/>";
			for($j=0;$j<$this->getNumEntitlements();$j++){
				print "Service Level Code:\t" . $this->getServiceLevelCode($i,$j) . "<br/>";
				print "Service Level:\t\t" . $this->getServiceLevelDescription($i,$j) . "<br/>";
				print "Provider:\t\t" . $this->getProvider($i,$j) . "<br/>";
				print "Entitlement Start Date:\t" . $this->getStartDate($i,$j) . "<br/>";
				print "Entitlement End Date:\t" . $this->getEndDate($i,$j) . "<br/>";
				print "Days Left:\t\t" . $this->getDaysLeft($i,$j) . "<br/>";
				print "Entitlement Type:\t" . $this->getEntitlementType($i,$j) . "<br/>";
			}
			print "Warrenty Expires:\t" . $this->getWarrantyExpireDate() . "<br/>";
		}
	}
	
	public function __getSoapClient(){
		return $this->soapClient;
	}
	
	public function __getGUID(){
		return $this->guid;
	}
	
	public function __getApplicationName(){
		return $this->applicationName;
	}
	
	public function __getServiceTags(){
		return $this->serviceTags;
	}
	
	public function __setGUID($guid){
		$this->guid = $guid;
	}
	
	public function __setApplicationName($applicationName){
		$this->applicationName = $applicationName;
	}
	
	public function __setServiceTags($serviceTags){
		$this->serviceTags = $serviceTags;
	}
	
	public function makeRequest($request){
		$params['guid'] 			= $this->guid;
		$params['applicationName'] 	= $this->applicationName;
		$params['serviceTags'] 		= $this->serviceTags;
		$this->assets = $this->soapClient->$request($params);
		// var_dump($this->assets);
	}

	public function getNumAssets(){
		$numAssets = count($this->assets->GetAssetInformationResult->Asset);
		return $numAssets;
	}
	
	public function getServiceTag($i=0){
		$serviceTag = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->ServiceTag;
		return $serviceTag;
	}
	
	public function getSystemID($i=0){
		$systemID = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemID;
		return $systemID;		
	}

	public function getBuid($i=0){
		$buid = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Buid;
		return $buid;
	}
	
	public function getRegion($i=0){
		$region = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Region;
		return $region;
	}
	
	public function getSystemType($i=0){
		$systemType = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemType;
		return $systemType;
	}
	
	public function getSystemModel($i=0){
		$systemModel = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemModel;
		return $systemModel;
	}
	
	public function getSystemShipDate($i=0){
		$systemShipDate = $this->assets->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemShipDate;
		return $systemShipDate;
	}
	
	public function getNumEntitlements($i=0){
		$numEntitlements = count($this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData);
		return $numEntitlements;
	}
	
	public function getServiceLevelCode($i=0,$j=0){
		$serviceLevelCode = $this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelCode;
		return $serviceLevelCode;	
	}
	
	public function getServiceLevelDescription($i=0,$j=0){
		$serviceLevelDescription = $this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelDescription;	
		return $serviceLevelDescription;
	}
	
	public function getProvider($i=0,$j=0){
		$provider = $this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->Provider;
		return $provider;	
	}
	
	public function getStartDate($i=0,$j=0){
		$startDate = $this->fixDate($this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->StartDate);
		return $startDate;
	}
	
	public function getEndDate($i=0,$j=0){
		$endDate = $this->fixDate($this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->EndDate);
		return $endDate;	
	}
	
	public function getDaysLeft($i=0,$j=0){
		$daysLeft = $this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->DaysLeft;
		return $daysLeft;	
	}
	
	public function getEntitlementType($i=0,$j=0){
		$entitlementType = $this->assets->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->EntitlementType;
		return $entitlementType;	
	}
	
	public function fixDate($date){
		$fixedDate = substr($date,0,10);
		return $fixedDate;
	}
	
	public function getWarrantyExpireDate(){
		foreach($this->assets->GetAssetInformationResult->Asset[0]->Entitlements->EntitlementData as $entitlement){
			switch($entitlement->EntitlementType){
				case "Credited":
					$warrantyExpireDate = $this->fixDate($entitlement->EndDate);
					break 2;
				case "Future":
					$warrantyExpireDate = $this->fixDate($entitlement->EndDate);
					break 2;
				case "Active":
					$warrantyExpireDate = $this->fixDate($entitlement->EndDate);
					break 2;
				case "Expired":
					$warrantyExpireDate = "Expired";
					break;
			}
		}
		return $warrantyExpireDate;
	}
	
}


class DasException extends Exception{
	function __construct($message, $request = null){
		$this->message = $message;
		$this->request = $request;
	}
}

?>
