<?php

function generateGUID(){
	if (function_exists('com_create_guid') === true){
		return trim(com_create_guid(), '{}');
	} else{
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
}

class DasLib{
	private $soapClient;
	private $assetInformation;
	private $wsdl = "http://xserv.dell.com/services/assetservice.asmx?WSDL";
	
	function __construct($guid,$applicationName,$serviceTags){
		if(!class_exists('SoapClient')) {
			throw new DasException('Looks like your PHP lacks SOAP support');
        }
		
		if(!preg_match('(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})', $guid)){
			throw new DasException('Invalid GUID: ' . $guid);
		}
		
		$this->soapClient = new SoapClient($this->wsdl, array('soap_version' => SOAP_1_2,'features' => SOAP_SINGLE_ELEMENT_ARRAYS));
		
		if(!$this->soapClient){
			throw new DasException('Failed to create a SOAP client.');
		}
		
		$this->assetInformation = $this->soapClient->GetAssetInformation(array('guid' => $guid,'applicationName' => $applicationName,'serviceTags' => $serviceTags));
		
	}

	public function getNumAssets(){
		$numResults = count($this->assetInformation->GetAssetInformationResult->Asset);
		return $numResults;
	}
	
	public function getServiceTag($i=0){
		$serviceTag = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->ServiceTag;
		return $serviceTag;
	}
	
	public function getSystemID($i=0){
		$systemID = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemID;
		return $systemID;		
	}

	public function getBuid($i=0){
		$buid = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Buid;
		return $buid;
	}
	
	public function getRegion($i=0){
		$region = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Region;
		return $region;
	}
	
	public function getSystemType($i=0){
		$systemType = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemType;
		return $systemType;
	}
	
	public function getSystemModel($i=0){
		$systemModel = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemModel;
		return $systemModel;
	}
	
	public function getSystemShipDate($i=0){
		$systemShipDate = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemShipDate;
		return $systemShipDate;
	}
	
	public function getNumEntitlements($i=0){
		$numEntitlements = count($this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData);
		return $numEntitlements;
	}
	
	public function getServiceLevelCode($i=0,$j=0){
		$serviceLevelCode = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelCode;
		return $serviceLevelCode;	
	}
	
	public function getServiceLevelDescription($i=0,$j=0){
		$serviceLevelDescription = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelDescription;	
		return $serviceLevelDescription;
	}
	
	public function getProvider($i=0,$j=0){
		$provider = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->Provider;
		return $provider;	
	}
	
	public function getStartDate($i=0,$j=0){
		$startDate = $this->fixDate($this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->StartDate);
		return $startDate;
	}
	
	public function getEndDate($i=0,$j=0){
		$endDate = $this->fixDate($this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->EndDate);
		return $endDate;	
	}
	
	public function getDaysLeft($i=0,$j=0){
		$daysLeft = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->DaysLeft;
		return $daysLeft;	
	}
	
	public function getEntitlementType($i=0,$j=0){
		$entitlementType = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->EntitlementType;
		return $entitlementType;	
	}
	
	public function fixDate($date){
		$fixedDate = substr($date,0,10);
		return $fixedDate;
	}
	
	public function getWarrantyExpireDate(){
		foreach($this->assetInformation->GetAssetInformationResult->Asset[0]->Entitlements->EntitlementData as $entitlement){
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