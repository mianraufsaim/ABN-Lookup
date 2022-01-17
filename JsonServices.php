/*
    Validate Australian ACN Number
    */
    function osf_valid_acn($acn) {
        $weights = array(8, 7, 6, 5, 4, 3, 2, 1, 0);
        /* Strip anything other than numbers */
        $acn = preg_replace('/[^0-9]/', '', $acn);
        /* Check string lenth is 9 characters */
        if (strlen($acn) != 9) return false;
        /* Add the products */
        $sum = 0; foreach (str_split($acn) as $key => $digit) {
            $sum += $digit * $weights[$key];
        }
        /* Get remainder */
        $remainder = $sum % 10;
        /* Remainder compliment */
        $complement = (string)(10 - $remainder);
        /* If complement is 10, set to 0 */
        if ($complement === "10") $complement = "0";
        return ($acn[8] === $complement) ? true : false;
    }

    /*
    Validate Australian ABN Number
    */
    function osf_valid_abn($abn) {
        $weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
        /* Strip anything other than numbers */
        $abn = preg_replace('/[^0-9]/', '', $abn);
        /* 11 characters */
        if (strlen($abn) != 11) return false;
        /* Subtract one from first digit */
        $abn[0] = ((int) $abn[0] - 1);
        /* Add the products */
        $sum = 0; foreach (str_split($abn) as $key => $digit) {
            $sum += ($digit * $weights[$key]);
        }
        if (($sum % 89) != 0) {
            return false;
        }
        return true;
    }
    /*
    Convert JSONP to Simple JSON
    */
    function osf_json_decode($jsonp, $assoc = true) {
        if ($jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp,'();'), $assoc);
    }

    /*
    Australian ABN, ACN, and Name Lookup With PHP
    */
    function osf_abn_lookup($abn, $guid , $results = '20', ) {
        /* ABN or ACN */
        if (preg_match('#^[0-9\s?]+$#', $abn)) {
            /* Strip white space & get length */
            $abn = preg_replace('/\s+/', '', $abn);
            $abn_length = strlen($abn);
            switch ($abn_length) {
            case '9':
                if (osf_valid_acn($abn) === false) return false;
                print_r($abn);
                $data = @file_get_contents('https://abr.business.gov.au/json/AcnDetails.aspx?acn=' . $abn . '&callback=callback&guid=' . $guid);
                $data = osf_json_decode($data);
                return $data;
                break;
            case '11':
                if (osf_valid_abn($abn) === false) return false;
                $data = @file_get_contents('https://abr.business.gov.au/json/AbnDetails.aspx?abn=' . $abn . '&callback=callback&guid=' . $guid);
                $data = osf_json_decode($data);
                print_r($data);
                return $data;
            default:
                return false;
            }
        /* Else it's a text search */
        } elseif (preg_match('#(a-Z0-9\!\?\#\$\&\'\%\(\)\*\?\-/\:\;\=@,.\s\{\|\})*#', $abn)) {
            $data = @file_get_contents('https://abr.business.gov.au/json/MatchingNames.aspx?name=' . str_replace(' ', '+', $abn) . '&maxResults=' . $results . '&guid=' . $guid);
            $data = osf_json_decode($data);
            print_r($data);
            return $data;
        } else {
            return false;
        }
    }
    print_r(osf_abn_lookup("The Trustee for SM8A Trust", "daa3b186-571b-4177-857d-313de4c731d0") , true);
   
?>
