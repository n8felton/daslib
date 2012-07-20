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
		
		$this->soapClient = new SoapClient($this->wsdl, array('soap_version' => SOAP_1_2));
		
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
		if($this->getNumAssets() > 1){
			$serviceTag = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->ServiceTag;
		} else{
			$serviceTag = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->ServiceTag;
		}
		return $serviceTag;
	}
	
	public function getSystemID($i=0){
		if($this->getNumAssets() > 1){
			$systemID = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemID;
		}else{
			$systemID = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->SystemID;
		}
		return $systemID;		
	}

	public function getBuid($i=0){
		if($this->getNumAssets() > 1){
			$buid = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Buid;
		}else{
			$buid = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->Buid;
		}
		return $buid;
	}
	
	public function getRegion($i=0){
		if($this->getNumAssets() > 1){
			$region = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->Region;
		}else{
			$region = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->Region;
		}	
		return $region;
	}
	
	public function getSystemType($i=0){
		if($this->getNumAssets() > 1){
			$systemType = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemType;
		}else{
			$systemType = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->SystemType;
		}
		return $systemType;
	}
	
	public function getSystemModel($i=0){
		if($this->getNumAssets() > 1){
			$systemModel = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemModel;
		}else{
			$systemModel = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->SystemModel;
		}	
		return $systemModel;
	}
	
	public function getSystemShipDate($i=0){
		if($this->getNumAssets() > 1){
			$systemShipDate = $this->assetInformation->GetAssetInformationResult->Asset[$i]->AssetHeaderData->SystemShipDate;
		}else{
			$systemShipDate = $this->assetInformation->GetAssetInformationResult->Asset->AssetHeaderData->SystemShipDate;
		}			
		return $systemShipDate;
	}
	
	public function getNumEntitlements($i=0){
		if($this->getNumAssets() > 1){
			$numEntitlements = count($this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData);
		}else{
			$numEntitlements = count($this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData);
		}		
		return $numEntitlements;
	}
	
	public function getServiceLevelCode($i=0;$j=0){
		if($this->getNumAssets() > 1){
			if($this->getNumEntitlements($i) > 1){
				$serviceLevelCode = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelCode;
			}else{
				$serviceLevelCode = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData->ServiceLevelCode;
			}
		}else{
			if($this->getNumEntitlements($i) > 1){
				$serviceLevelCode = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$j]->ServiceLevelCode;
			}else{
				$serviceLevelCode = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData->ServiceLevelCode;
			}
		}			
		return $serviceLevelCode;	
	}
	
	public function getServiceLevelDescription($i){
		if($this->getNumAssets() > 1){
			if($this->getNumEntitlements($i) > 1){
				$serviceLevelDescription = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData[$j]->ServiceLevelDescription;
			}else{
				$serviceLevelDescription = $this->assetInformation->GetAssetInformationResult->Asset[$i]->Entitlements->EntitlementData->ServiceLevelDescription;
			}
		}else{
			if($this->getNumEntitlements($i) > 1){
				$serviceLevelDescription = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$j]->ServiceLevelDescription;
			}else{
				$serviceLevelDescription = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData->ServiceLevelDescription;
			}
		}				
		return $serviceLevelDescription;
	}
	
	public function getProvider($i){
		if($this->getNumAssets() > 1){
			if($this->getNumEntitlements($i) > 1){
				$provider = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$j]->Provider;
			}else{
				$provider = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData->Provider;
			}
		}else{
			if($this->getNumEntitlements($i) > 1){
				$provider = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$j]->Provider;
			}else{
				$provider = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData->Provider;
			}
		}	
		return $provider;	
	}
	
	public function getStartDate($i){
		$startDate = $this->fixDate($this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$i]->StartDate);
		return $startDate;
	}
	
	public function getEndDate($i){
		$endDate = $this->fixDate($this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$i]->EndDate);
		return $endDate;	
	}
	
	public function getDaysLeft($i){
		$daysLeft = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$i]->DaysLeft;
		return $daysLeft;	
	}
	
	public function getEntitlementType($i){
		$entitlementType = $this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData[$i]->EntitlementType;
		return $entitlementType;	
	}
	
	public function fixDate($date){
		$fixedDate = substr($date,0,10);
		return $fixedDate;
	}
	
	public function getWarrantyExpireDate(){
		foreach($this->assetInformation->GetAssetInformationResult->Asset->Entitlements->EntitlementData as $entitlement){
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