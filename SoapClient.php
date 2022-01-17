
/**
 * @author Mian Rauf - 18 January 2012
 * extends php soap client to utilize the Australian Government ABN Lookup web service 
 * requires php 5 or greater and lib-xml enabled/compiled with apache
 * @link	http://www.php.net/manual/en/book.soap.php
 * @link	http://abr.business.gov.au/Webservices.aspx
 * 
 * @param string $guid - get a guid id by registering @ http://abr.business.gov.au/Webservices.aspx
 * 
 */


class abnlookup extends SoapClient{

    private $guid = ""; 
	
	public function __construct($guid)
    {
		$this->guid = $guid;
		$params = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => true,
			'trace' => 1,
			'cache_wsdl' => WSDL_CACHE_NONE
		); 

		parent::__construct('https://abr.business.gov.au/abrxmlsearch/ABRXMLSearch.asmx?WSDL', $params);
    }
	
	public function searchByAbn($abn, $historical = 'N'){
		$params = new stdClass();
		$params->searchString				= $abn;
		$params->includeHistoricalDetails	= $historical;
		$params->authenticationGuid			= $this->guid;
		return $this->ABRSearchByABN($params);
	}

    public function searchByName($company_name){
        $erfnt = new stdClass();
        $erfnt->tradingName = "Y";      // for yes put 'Y', for no put 'N'
        $erfnt->legalName = "Y";        
        
        $erfsc = new stdClass();   //  corresponds to ExternalRequestFilterStateCode from WSDL
        $erfsc->QLD = 'Y';        // for yes put 'Y', for no put 'N'
        $erfsc->NT = 'Y';
        $erfsc->SA = 'Y';
        $erfsc->WA = 'Y';
        $erfsc->VIC = 'Y';
        $erfsc->ACT = 'Y';
        $erfsc->TAS = 'Y';
        $erfsc->NSW = 'Y';
        
        $erf = new stdClass();
        $erf->nameType = $erfnt;    
        $erf->stateCode = $erfsc;   
        
        $ens = new stdClass();
        $ens->authenticationGUID =  $this->guid ;
        $ens->name = $company_name;     
        $ens->filters = $erf;
        
        $params = new stdClass();
        $params->externalNameSearch         = $ens; 
        $params->authenticationGuid         = $this->guid; 
        return $this->ABRSearchByName($params);
        
        }
}

$abn_search_string = ""; // put here ABN name or By Name
$guid = ""; // Put here guid 
try{
	$abnlookup = new abnlookup($guid);
	try{
		$result = $abnlookup->searchByName($abn_search_string); 
		
		// display all results
		echo "<pre>";
		print_r($result);
		echo "</pre>";
		
		// also display by variables using object notation.
		echo "<pre>";
		$result->ABRPayloadSearchResults->response;
		echo "</pre>";
		
	} catch	(Exception $e){
		throw $e;
	}
	
} catch(Exception $e){
	echo $e->getMessage();
}
