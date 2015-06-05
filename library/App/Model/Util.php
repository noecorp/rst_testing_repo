<?php

/**
 * Utility that manages the utility methods for defining
 * the generic methods in the application
 *
 * @package Core
 * @copyright transerv
 */
class Util extends App_Model {

    /**
     * Returns an array with all genders
     * 
     * 
     * @access public static
     * @return array
     */
    public static function getGender($product = false) {
        if($product==BANK_BOI_NSDC) {
        return array(
            'male' => 'Male',
            'female' => 'Female',
        );            
        } else {
        return array(
            'male' => 'Male',
            'female' => 'Female',
            'institution' => 'Institution',
        );
        }
    }

    public static function getActiveInactive() {
        return array(
            '' => 'Select',
            'active' => STATUS_ACTIVE,
            'inactive' => STATUS_INACTIVE,
        );
    }

    public static function getFundAccountType() {
        return array(
            'by agent' => 'By Agent',
                //'by principal distributor' => 'By principal distributor'
        );
    }

    public static function getDoctype() {
        return array(
            'passport' => 'Passport',
            'photo' => 'Photo',
            'shop photo' => 'Shop Photo',
            //'id proof' => 'ID proof',
            // 'address proof' => 'Address Proof',
            'pan' => 'PAN number',
            //'others' => 'Others',
            'uid' => 'UID',
            'voter id' => 'Voter ID',
            'ration card' => 'Ration Card',
            'electricity bill' => 'Electricity Bill',
            'telephone bill' => 'Telephone Bill',
            'state govt letter' => 'State Govt issued letter',
            'id card' => 'PSU/Govt Deptt/Defence ID card',
            'income tax letter' => 'Income Tax letter',
        );
    }

    // Random and unique verification code for Agent email verification
    public static function hashVerification($verification_code) {
        $config = App_DI_Container::get('ConfigObject');

        return sha1($config->security->csrfsalt . $verification_code . rand(0, 10000));
    }

    /**
     * Returns an array with all Castes
     * 
     * 
     * @access public static
     * @return array
     */
    public static function getCaste() {
        return array(
            'general' => 'General',
            'obc' => 'OBC',
            'sc' => 'SC/ST',
            'other' => 'Others',
        );
    }

    /**
     * Returns an array with all Marital Status
     * 
     * 
     * @access public static
     * @return array
     */
    public static function getMaritalStatus() {
        return array(
            'single' => 'Single',
            'married' => 'Married',
            'divorcee' => 'Divorcee',
            'widow' => 'Widow/Widower',
        );
    }

    /**
     * Returns an array with all Occupation
     * 
     * 
     * @access public static
     * @return array
     */
    public static function getOccupation() {
        return array(
            'salaried' => 'Salaried',
            'self employed' => 'Self Employed',
            'retired' => 'Retired',
            'housewife' => 'Housewife',
            'student' => 'Student',
            'others' => 'Others',
        );
    }

    /**
     * Returns an array with agent account types
     * 
     * 
     * @access public static
     * @return array
     */
    public static function getAgentAcctType() {
        return array(
            'saving' => 'Saving',
            'current' => 'Current',
        );
    }

    public static function getNationality() {

        return array('' => 'Select', 'indian' => 'Indian');
    }

    public static function getStates($country = 'IN') {
        $stateArr = array('IN' => array(
                "Andaman and Nicobar Islands" => "Andaman and Nicobar Islands",
                "Andhra Pradesh" => "Andhra Pradesh",
                "Arunachal Pradesh" => "Arunachal Pradesh",
                "Assam" => "Assam",
                "Bangladesh" => "Bangladesh",
                "Bihar" => "Bihar",
                "Chandigarh" => "Chandigarh",
                "Chhattisgarh" => "Chhattisgarh",
                "Dadra and Nagar Haveli" => "Dadra and Nagar Haveli",
                "Daman and Diu" => "Daman and Diu",
                "Delhi" => "Delhi",
                "Goa" => "Goa",
                "Gujarat" => "Gujarat",
                "Gujrat" => "Gujrat",
                "Haryana" => "Haryana",
                "Himachal Pradesh" => "Himachal Pradesh",
                "Jammu & Kashmir" => "Jammu & Kashmir",
                "Jammu and Kashmir" => "Jammu and Kashmir",
                "Jharkhand" => "Jharkhand",
                "Karnataka" => "Karnataka",
                "Kerala" => "Kerala",
                "Lakshadweep" => "Lakshadweep",
                "Madhya" => "Madhya",
                "Madhya Pradesh" => "Madhya Pradesh",
                "Maharashtra" => "Maharashtra",
                "Manipur" => "Manipur",
                "Meghalaya" => "Meghalaya",
                "Mizoram" => "Mizoram",
                "Nagaland" => "Nagaland",
                "Orissa" => "Orissa",
                "Pondicherry" => "Pondicherry",
                "Pradesh" => "Pradesh",
                "Punjab" => "Punjab",
                "Rajasthan" => "Rajasthan",
                "Sikkim" => "Sikkim",
                "Tamil Nadu" => "Tamil Nadu",
                "Tamilnadu" => "Tamilnadu",
                "Tripura" => "Tripura",
                "Uttar Pradesh" => "Uttar Pradesh",
                "Uttarakhand" => "Uttarakhand",
                "West Bengal" => "West Bengal"
            )
        );

        return $stateArr[$country];
    }

    public static function getCity($state = 'Maharashtra') {
        $cityArr = array("Maharashtra" => array(
                "Achalpur",
                "Ahmednagar",
                "Ahmedpur",
                "Ajra",
                "Akkalkot",
                "Akola",
                "Akot",
                "Alandi",
                "Alibag",
                "Amalner",
                "Ambad",
                "Ambejogai",
                "Ambivali Tarf Wankhal",
                "Amravati",
                "Anjangaon",
                "Arvi",
                "Ashta",
                "Aurangabad",
                "Ausa",
                "Baramati",
                "Bhandara",
                "Bhiwandi",
                "Bhusawal",
                "Chalisgaon",
                "Chandrapur",
                "Chinchani",
                "Chiplun",
                "Daund",
                "Devgarh",
                "Dhule",
                "Dombivli",
                "Durgapur",
                "Gadchiroli",
                "Ghatanji",
                "Gondiya",
                "Ichalkaranji",
                "Jalna",
                "Jalgaon",
                "Junnar",
                "Kalyan",
                "Kamthi",
                "Karad",
                "karjat",
                "Kolhapur",
                "Latur",
                "Loha",
                "Lonar",
                "Lonavla",
                "Mahabaleswar",
                "Mahad",
                "Mahuli",
                "Malegaon",
                "Malkapur",
                "Manchar",
                "Mangalvedhe",
                "Mangrulpir",
                "Manjlegaon",
                "Manmad",
                "Manwath",
                "Mehkar",
                "Mhaswad",
                "Mira-Bhayandar",
                "Miraj",
                "Morshi",
                "Mukhed",
                "Mul",
                "Mumbai",
                "Murtijapur",
                "Nagpur",
                "Nalasopara",
                "Nanded-Waghala",
                "Nandgaon",
                "Nandura",
                "Nandurbar",
                "Narkhed",
                "Nashik",
                "Navi Mumbai",
                "Nawapur",
                "Nilanga",
                "Osmanabad",
                "Ozar",
                "Pachora",
                "Paithan",
                "Palghar",
                "Pandharkaoda",
                "Pandharpur",
                "Panvel",
                "Parbhani",
                "Parli",
                "Parola",
                "Partur",
                "Pathardi",
                "Pathri",
                "Patur",
                "Pauni",
                "Pen",
                "Phaltan",
                "Pulgaon",
                "Pune",
                "Purna",
                "Pusad",
                "Raichuri",
                "Rajura",
                "Ramtek",
                "Ratnagiri",
                "Raver",
                "Risod",
                "Sailu",
                "Sangamner",
                "Sangli",
                "Sangole",
                "Sasvad",
                "Satana",
                "Satara",
                "Savner",
                "Sawantwadi",
                "Shahade",
                "Shegaon",
                "Shendurjana",
                "Shirdi",
                "Shirpur-Warwade",
                "Shirur",
                "Shrigonda",
                "Shrirampur",
                "Sillod",
                "Sinnar",
                "Solapur",
                "Soyagaon",
                "Talegaon Dabhade",
                "Talode",
                "Tasgaon",
                "Tirora",
                "Tuljapur",
                "Tumsar",
                "Uchgaon",
                "Udgir",
                "Umarga",
                "Umarkhed",
                "Umred",
                "Uran",
                "Uran Islampur",
                "Vadgaon Kasba",
                "Vaijapur",
                "Vasai",
                "Virar",
                "Vita",
                "Wadgaon Road",
                "Wai",
                "Wani",
                "Wardha",
                "Warora",
                "Warud",
                "Washim",
                "Yavatmal",
                "Yawal",
                "Yevla"
            )
        );

        return $cityArr[$state];
    }

    public static function getCountry() {
        return array(
            'IN' => 'India'
        );
    }

    public static function getCountryName($code) {
        if ($code == 'IN')
            return 'India';
    }

    public function getGenderChar($gender) {
        if ($gender == 'male')
            return 'M';
        else
            return 'F';
    }

    public static function getCountryCode($code) {
        if ($code == 'IN')
            return 356;
    }

    public static function getResidentType($returnValue = '') {
        $array = array(
            '' => 'Select',
            'owned' => 'Owned House',
            'rented' => 'Rented House',
            'parental' => 'Parental House'
        );
        if (strlen($returnValue) > 0) {

            return $array[$returnValue];
        } else {
            return $array;
        }
    }

    public static function getIdentificationType($additional = FALSE) {
        if($additional){
          return array(
            '' => 'Select Type',
            'passport' => 'Passport',
            'driving licence' => 'Driving Licence',
            'pan' => 'Pan Card',
            'aadhaar card' => 'Aadhaar No.',
            'voter id' => 'Voter ID',
            'id card' => 'PSU/Govt Deptt/Defence ID card',
            'marriage certificate' => 'Marriage certificate',
            
        );  
        }
    else{
     return array(
            '' => 'Select Type',
            'passport' => 'Passport',
            'uid' => 'UID',
            'pan' => 'Pan Card',
            'voter id' => 'Voter ID',
            'id card' => 'PSU/Govt Deptt/Defence ID card',
            'institution identity proof' => 'Institution Identity Proof'
            
        );
    }
    }

    public static function getAddressProofType() {
        return array(
            '' => 'Select Type',
            'passport' => 'Passport',
            //'uid' => 'UID',
            'voter id' => 'Voter ID',
            'ration card' => 'Ration Card',
            'electricity bill' => 'Electricity Bill',
            'water bill' => 'Water Bill',
            'telephone bill' => 'Telephone Bill',
            'state govt letter' => 'State Govt issued letter',
            'income tax letter' => 'Income Tax letter',
            'institution address proof' => 'Institution Address Proof'
        );
    }

    public static function getAgentRegistrationTab($current = 'basic', $agentId = 0) {
        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'basic' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/add") . '" >Basic Details</a></li>
                        <li ' . ($current == 'educational' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/agents/addeducation") . '">Educational Details</a></li>
                        <li ' . ($current == 'identification' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/addidentification") . '">Identification Details</a></li>
                        <li ' . ($current == 'address' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/addaddress") . '">Address Details</a></li>
                        <li ' . ($current == 'bank' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/addbank") . '">Bank Details</a></li>
                        </ul>
            </div>';
        //  <li '. ($current == 'docs' ? 'class ="selected"': '') .'><a href="/agents/addocs'.$addURL.'">Upload Documents</a></li>                    

        return $strReturn;
    }

    public static function getAgentRegistrationAgentTab($current = 'basic', $agentId = 0) {
        $addURL = '';
        if ($agentId > 0) {
            //$addURL = '?id='.$agentId;
        }
        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'basic' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/add") . '" >Basic Details</a></li>
                        <li ' . ($current == 'educational' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/signup/addeducation") . '">Educational Details</a></li>
                        <li ' . ($current == 'identification' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/addidentification") . '">Identification Details</a></li>
                        <li ' . ($current == 'address' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/addaddress") . '">Address Details</a></li>
                        <li ' . ($current == 'bank' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/addbank") . '">Bank Details</a></li>
                        </ul>
                </div>
            </div>';
        //  <li '. ($current == 'docs' ? 'class ="selected"': '') .'><a href="/agents/addocs'.$addURL.'">Upload Documents</a></li>                    

        return $strReturn;
    }

    public static function getIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
            $ip_address = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = @$_SERVER['REMOTE_ADDR'];
        }
        return $ip_address;
    }

    public static function is_decimal($val) {
        if (is_float($val) && floor($val) != $val)
            return TRUE;
        else
            return FALSE;
    }

    public static function numberFormat($num, $commaAllow = FLAG_YES) {
        if ($commaAllow == FLAG_YES) {
            return number_format($num, 2);
        } else {
            return number_format($num, 2, '.', '');
        }
    }

    public static function getAgentEditTab($current = 'basic', $agentId = 0) {
        $addURL = '';
        if ($agentId > 0) {
            $addURL = '?id=' . $agentId;
        }
        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'basic' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/edit$addURL") . '" >Basic Details</a></li>
                        <li ' . ($current == 'educational' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/agents/editeducation") . '">Educational Details</a></li>
                        <li ' . ($current == 'identification' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/editidentification") . '">Identification Details</a></li>
                        <li ' . ($current == 'address' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/editaddress") . '">Address Details</a></li>
                        <li ' . ($current == 'bank' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/agents/editbank") . '">Bank Details</a></li>
                    </ul>
            </div>';
        return $strReturn;
    }

    public static function getCardholderSignupTab($current = 'step1') {

        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'step1' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step1") . '">Step1 Details</a></li>
                        <li ' . ($current == 'step2' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step2") . '">Step2 Details</a></li>
                        <li ' . ($current == 'step3' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step3") . '">Step3 Details</a></li> 
                        <li ' . ($current == 'index' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_loadbalance/index") . '">Load Balance</a></li>
                    </ul>
                </div>
            </div>';
        return $strReturn;
    }

    public static function getCardholderFundLoadTab($current = 'mobile') {

        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'mobile' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholderfund/mobile") . '">Mobile Detail</a></li>
                        <li ' . ($current == 'load' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholderfund/load") . '">Fund Load Details</a></li>
                    </ul>
                </div>
            </div>';
        return $strReturn;
    }

    public static function getCardholderEditTab($current = 'step1', $chId = '') {

        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'step1' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step1?ch='.$chId.'") . '">Step1 Details</a></li>
                        <li ' . ($current == 'step2' ? 'class="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step2?ch='.$chId.'") . '">Step2 Details</a></li>
                        <li ' . ($current == 'step3' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/mvc_axis_cardholder/step3?ch='.$chId.'") . '">Step3 Details</a></li> 
                    </ul>
                </div>
            </div>';
        return $strReturn;
    }

    public static function getEducationType() {
        return array(
            '' => 'Select',
            'intermediate' => 'Under Graduate',
            'graduate' => 'Graduate',
            'post graduate' => 'Post Graduate',
            'professional' => 'Professional'
        );
    }

    public static function getYesNo() {

        return array('' => 'Select', FLAG_YES => ucfirst(FLAG_YES), FLAG_NO => ucfirst(FLAG_NO));
    }

    public static function getRelationship() {
        return array(
            '' => 'Select',
            'father' => 'Father',
            'mother' => 'Mother',
            'brother' => 'Brother',
            'sister' => 'Sister',
            'friend' => 'Friend',
            'other' => 'Other',
        );
    }

    public static function getVehicleType() {

        return array('' => 'Select', 'two wheeler' => 'Two Wheeler', 'car' => 'Car', 'others' => 'Others');
    }

    public static function generateRandomPassword() {
        return base64_encode(rand(0, 1000000));
    }


    public static function getTitle($program='') {
        if($program == BANK_BOI_NDSC) {
            return array('' => 'Select', 'mr' => 'Mr', 'mrs' => 'Mrs', 'ms' => 'Ms');
        } else {
            return array('' => 'Select', 'mr' => 'Mr.', 'mrs' => 'Mrs.', 'miss' => 'Miss', 'ms' => 'Ms.', 'sir' => 'Sir', 'dr' => 'Dr.', 'chief' => 'Chief');            
        }
    }

    public static function getAgentSearchCriteria() {
        return array('' => 'Select', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email Id', 'mobile1' => 'Mobile', 'agent_code' => 'Agent Code', 'estab_city' => 'City');
    }

    public static function getCardholderSearchCriteria() {
        return array('' => 'Select', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email Id', 'mobile_number' => 'Mobile', 'city' => 'City');
    }

    public static function getFamilyMembers() {
        return array('' => 'Select', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8');
    }

    public static function getCorporateSearchCriteria() {
        return array('' => 'Select', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'email' => 'Email Id', 'mobile' => 'Mobile', 'corporate_code' => 'Corporate Code', 'estab_city' => 'City');
    }
    
    /**
     * objectToArray 
     * Convert stdClass object into array
     * @param type $d
     * @return type
     */
    public static function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        return $d;
    }

    /**
     * encodeToNumericCode
     * Method used to encode string into numeric code, Using crc32 hash
     * @param string $str
     * @param int $length
     * @param bool $flg
     * @return integer
     */
    public static function encodeToNumericCode($str, $length = 7, $flg = false) {
        if (!$flg) {
            $str2 = sprintf("%0" . $length . "s", sprintf('%u', crc32($str)));
            if (strlen($str2) > $length) {
                $str2 = substr($str2, 0, $length);
            }
        } else {
            $str = sprintf("%0" . $length . "s", sprintf('%u', crc32($str)));
            $str2 = substr($str, 1, $length - 4) . rand(1111, 9999);
        }
        return $str2;
    }

    /**
     * getLenghtId
     * Used to generate string to defined $lenght string
     * @param integer $id
     * @param integer $lenght
     * @return string
     */
    public static function getLenghtId($id, $length = 4) {
        if (strlen($id) > $length) {
            return $id;
        }
        return sprintf("%0" . $length . "s", $id);
    }

    /**
     * getAgentCode
     * Used to concatenate agent code
     * @param type $numericCode
     * @param type $agentId
     * @return type
     */
    public static function getAgentCode($numericCode, $agentId) {
        return $numericCode . $agentId;
    }

    /**
     * objectToArray 
     * Convert stdClass object into array
     * @param type $d
     * @return type
     */
    public static function wsdlObjectToArray($d) {
        $arr = array();
        foreach ($d as $key => $value) {
            $arr[$value->key] = $value->value;
        }

        return $arr;
    }

    /**
     * getCardholderTC()     
     * @return terms conditions text
     */
   /* public static function getCardholderTC() {
        $tc = 'Terms and Conditions for e-Wallet Card
Please read these Terms of Service (TOS) carefully before using the e-Wallet Card.
The words, "Bank", "We" and "Us" refers to Axis Bank Ltd., a banking company incorporated under the Companies Act, 1956 and having its registered office at Thrishul, 3rd Floor, Opposite Samartheshwar Temple, Law Garden, Ellis Bridge, Ahmedabad 380 006, Gujarat India.
The word "Card(s)" shall mean Visa/Visa Electron Debit Credit and prepaid Cards as the case may be as issued by the Bank.
The words "we", "us" and "our" refer to Visa and the Bank as the case may be, and "you", "your" or "yours" refer to the account holders using the e-Wallet Card.
The terms / words not defined herein shall have the same meanings as defined in the Terms & conditions applicable to the Account(s).
These TOS supplements and are in addition to, and not in derogation of, the applicable Terms and Conditions relating to your usage of any other Axis Bank services that I may be currently availing or may in the future avail, including without limitation of the Net Banking service.
PRODUCT DEFINITION:
e-Wallet Card is a unique online secure payment solution relating to "Cards" provided by the Bank. This can be created by any Axis Bank customer currently using Axis bank service through Internet banking using netsecure or mobile banking through secured mpin.
The key features of e-Wallet Cards are as follows:
•	It&nbsp;s a onetime use card with a limited validity period from time of creation 
•	The e-Wallet Card can be used at any merchant website that accept Visa Cards. 
•	The exposure is limited only to the extent of the card limit of the e-Wallet Card and subject to a limited defined validity. 
e-Wallet Cards offer a unique security feature, wherein the card details can be used only one time over the internet. You will have the option of generating a virtual (virtual) card number/s, drawing funds from your Axis bank account. You can then use this e-Wallet Card number at any online merchant site and complete your transaction with security and ease.
ACCEPTANCE OF TERMS
•	A.e-Wallet Cards are subject to the TOS and the Terms and Conditions applicable to the Account(s). The TOS are subject to review from time to time. Use of the service for generating e-Wallet Cards constitutes your acceptance of the terms and you agree to abide by it.

You can review the most current version of the TOS at any time at www.axisbank.com. In addition, when using e-Wallet Cards, you shall be subject to any guidelines or rules applicable to e-Wallet Cards that may be posted from time to time at the same web site.

You agree that usage of the service, creation of any e-Wallet Card, and/or use of e-Wallet Card, will represent your acceptance of this TOS, and that continued use of e-Wallet Card after revisions to this TOS shall constitute your agreement to such revised terms and any applicable posted guidelines or rules.
•	B. Unless explicitly stated otherwise, any new features that augment enhance or otherwise change e-Wallet Card shall be subject to this TOS.
•	C. Visa and/or the Bank reserves the right at any time and from time to time to modify or discontinue, temporarily or permanently, e-Wallet Card (or any part thereof) with reasonable notice.
•	D. The Bank shall not be responsible for interception/ misuse of e-Wallet Card. Therefore creation and usage of the card will be tantamount to creation and usage of the e-Wallet Cards by you. The Bank is not liable if the e-Wallet Card details falls into wrong hands due to any reason whatsoever and or if the terms and conditions relating to use of e-Wallet Cards is not complied with.
AUTHENTICATION
Certain websites/the Bank at a later date may provide for any additional authentication in addition to what has been requested for. The Card member agrees to validate such requirements at a future date.
LIABILITY
The Bank shall not be liable if a transaction on the net does not materialize or is delayed or is incomplete.
DEALINGS WITH MERCHANTS
Your correspondence or business dealings with, or participation in promotions of, online retail or other merchants on or through e-Wallet Card, including payment and delivery of related goods or services, and any other terms, conditions, warranties or representations associated with such dealings, are solely between you and such merchant. You agree that, except as otherwise provided by Applicable Law or in our Card member Agreement or Terms and Conditions applicable to the Account with you, we will not be responsible or liable for any loss or damage of any sort incurred as the result of any such dealings. You understand that use of e-Wallet Card does not, in any way, indicate that we recommend or endorse any merchant, regardless of whether the merchant participates in e-Wallet Card. For example, e-Wallet Card does not verify the identity of the merchant or the quality of the merchant&nbsp;s goods or services.
NOTICE
You may also be notified of changes to this TOS or other matters by notices displayed on or links to notices displayed on http://www.axisbank.com
RESPONSIBILITY
Except as otherwise provided by Applicable Law or Terms and Conditions applicable to the Account, you understand that you are financially responsible for all uses of the e-Wallet Cards by you and those authorized by you during the creation of e-Wallet Cards.
MISCELLANEOUS
Misuses of e-Wallet Cards: You acknowledge that if any third person obtains access to your card details, such third person would be able to carry out transactions.
Internet Frauds: The Internet per se is susceptible to a number of frauds, misuses, hacking and other actions, which could affect making/use of e-Wallet Cards. Whilst the Bank shall aim to provide security to prevent the same, there cannot be any guarantee from such Internet frauds, hacking and other actions, which could affect the making and use of the e-Wallet Cards. You shall separately evolve/evaluate all risks arising out of the same.
Technology Risks: It may also be possible that the site of the Bank may require maintenance and during such time it may not be possible to process the request of the Customers. This could result in delays in the processing of Instruction or failure in the processing of instructions and other such failures and inability. You understand and acknowledge that the Bank disclaims all and any liability, arising out of any failure or inability by the Bank to honor any customer instruction.
Limits: You are aware that the Bank may from time to time impose maximum and minimum limits on the e-Wallet Cards. You realize, accept and agree that the same is to reduce the risks on you. For instance, the Bank may impose transaction restrictions within particular periods or amount restrictions within a particular period or even limits on each transaction. You shall be bound by such limits imposed and shall strictly comply with them.
Indemnity: You shall indemnify the Bank for and against all losses and damages that may be caused to the Bank as a consequence of breach of any of the Terms and Conditions governing the use of e-Wallet Cards.
Withdrawal of Facility: Axis Bank reserves the absolute right to withdraw / change / modify / suspend / cancel and / or alter any of the terms and conditions of this service at any time without giving any notice.
Charges: You hereby agree to bear the charges as may be stipulated by the Bank from time to time for availing of these services. You hereby authorize the Bank to recover all charges related to e-Wallet Cards as determined by the Bank from time to time by debiting your accounts. The charges (if any) for card creation and usage will be communicated in website www.axisbank.com as well as during creation of card.
Credit of unutilized amount:
•	In case of unutilized e-Wallet Card the amount will be credited back to the customer&nbsp;s source account without any interest if it&nbsp;s completely unutilized within 48 hours after the validity period of the e-Wallet Card. 
•	In case the unutilized e-Wallet Card is cancelled the amount will be credited back to the customers source account without any interest if it is completely unutilized within 48 hours of the validity period of the e-Wallet Card. 
•	In case the customer has used part amount for a transaction, the balance will get credited back to the customer&nbsp;s account without any interest approximately within 30 days from the date of transaction if the same is not claimed by Visa/Merchant establishment. 
JURISDICTION
The Courts in Mumbai alone shall have exclusive jurisdiction as regards any claims or disputes or matters arising out of the use of e-Wallet Cards and all such disputes will be governed by the laws of India.

';
        return $tc;
    }
   */
    
    /**
     * getCardholderTC()     
     * @return terms conditions text
     */
    public static function getCardholderTC() {
        $tc = 'Declaration
      I have read, understood and hereby agree to abide by the terms & conditions, rules and regulations and other statutory requirements applicable in respect to the Axis Bank Shmart Pay and other allied products. I understand that access to any changes / updates in the terms and conditions applicable to this relationship shall be available only on the website of Transerv at <www.shmart.in>.  I hereby declare that the particulars and information given in this application Form (and all documents referred and provided therewith) are true, correct, complete and up-to-date in all respects and to the best of my knowledge and belief and that I have not withheld any information. I understand that certain particulars given by me are required by the operational guidelines governing banking companies. I agree and undertake to provide any further information/documentation as and when required by Axis Bank,  Transerv  or its authorized agents. The documents submitted along with this application Form are genuine and I am not providing this Form in contravention of any Act, Rules, Regulations or any statute or legislation, or any notifications, directions issued by any governmental or statutory authority from time to time. I hereby undertake to promptly inform Transerv, <Transerv Address>, of any changes in the information provided herein above and agree and accept that Transerv and Axis Bank are not liable or responsible for any losses, costs, damages arising out of any actions undertaken or activities performed by them on the basis of information provided by me and also due to my non intimation/delay in initiating such changes. I hereby agree to use the Axis bank Shmart Pay Instrument within Reserve Bank of India specified limit and for all transactions with prescribed merchants for the products/services as mentioned by the merchant on this Application form & website and further agree not to use it for any unlawful purposes/activities. I will neither abet nor be a party to any illegal/criminal/money laundering activities undertaken, using this Axis bank Shmart Pay Instrument. I agree and understand that Transerv and Axis Bank reserve the right to reject the application without providing any reason or reference to me. I agree that the features and functionalities of Axis bank Shmart Pay Instrument can be downgraded in the event that KYC documents provided by me are not as per the requirements of Axis Bank / Transerv. I agree and understand that Axis Bank / Transerv reserves the right to retain the application forms and the documents provided therewith including photographs and shall not return the same to me. I shall not hold Transerv and Axis Bank responsible for storing and furnishing of the processed information/data/products thereof to other Banks/Financial Institutions/Credit Providers/any statutory and regulatory authority.
      
      I have no objection to Transerv Private Limited, its group companies, agents/representatives to provide me information on various products, offers and services provided by Transerv Private Limited /its group companies, agents/representatives or other entities through any mode (including without limitation through telephone calls/SMSs/emails) and authorise Transerv Private Limited, its group companies, agents/representatives for the above purpose.';
        return $tc;
    }

    /**
     * getEmailTC()     
     * @return terms conditions text
     */
    public static function getEmailTC() {
        $emailtc = 'Integulatory authority.ernet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.';
        return $emailtc;
    }

    /**
     * getEmailDisclaimer()     
     * @return Disclaimer text
     */
    public static function getEmailDisclaimer() {
        $emailDisclaimer = 'This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.';
        return $emailDisclaimer;
    }

    /**
     * getEmailImportant()     
     * @return Improtant text
     */
    public static function getEmailImportant() {
       $getEmailImportant = "Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.";
	return $getEmailImportant;
    }

    /**
     * getaltEmailTC()     
     * @return terms conditions text
     */
    public static function getaltEmailTC() {
        $altemailtc = 'Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.';
        return $altemailtc;
    }

    /**
     * getaltEmailDisclaimer()     
     * @return Disclaimer text
     */
    public static function getaltEmailDisclaimer() {
        $altemailDisclaimer = 'This communication is confidential and privileged and is directed to and for the use of the addressee only. The recipient if not the addressee should not use this message if erroneously received, and access and use of this e-mail in any manner by anyone other than the addressee is unauthorized. The recipient acknowledges that Transerv may be unable to exercise control or ensure or guarantee the integrity of the text of the email message and the text is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.';
        return $altemailDisclaimer;
    }

    /**
     * getaltEmailImportant()     
     * @return Important text
     */
    public static function getaltEmailImportant() {
        $getaltEmailImportant = 'Please do not reply to this message or email address.';
        return $getaltEmailImportant;
    }

    /**
     * getagentEmailImportant()     
     * @return Important text
     */
    public static function getagentEmailImportant() {
        $getagentEmailImportant = 'Please do not reply to this e-mail. For any queries or suggestions, email us on care@shmart.in.';
        return $getagentEmailImportant;
    }

    public function currentDateValidation($date) {
        if ($date > date('Y-m-d')) {
            return true;
        }
        return false;
    }

    public static function returnDateFormatted($date, $formatIn = "d-m-Y", $formatOut = "Y-m-d", $separator = "-", $implodeSeparator = "-", $type = '') {

        if ($date != '') {
            $arrDate = explode(' ', $date);
            $strDate = explode($separator, $arrDate[0]);
            $strDate = array_reverse($strDate);
            $arrDate[0] = implode($implodeSeparator, $strDate);
            $date = implode(' ', $arrDate);
        }
        if($type == 'from'){
            $date .= ' 00:00:00';
        }elseif($type == 'to'){
            $date .= ' 23:59:59';
        }
        
        return $date;
    }
    
    public static function returnDateFormattedFromString($date, $formatIn = "Ydm", $formatOut = "Y-m-d", $separator = "-") {
        if ($date != '') {
            if($formatIn = "Ydm") {
                $date = substr($date, 0, 4).substr($date, 6, 2).substr($date, 4, 2);
                $date = date("Y-m-d",strtotime($date));
            } else if($formatIn = "Ymd"){
                $date = substr($date, 0, 4).substr($date, 4, 2).substr($date, 6, 2);
                $date = date("Y-m-d",strtotime($date));
            }
        }
        return $date;
    }

    /* convertInYesNo()
     * that functon convert 0 and 1 to No and Yes
     * will accept 0 or 1
     */

    public static function convertInYesNo($digit) {
        $val = array('0' => ucfirst(FLAG_NO), '1' => ucfirst(FLAG_YES));
        return isset($val[$digit]) ? $val[$digit] : '';
    }
    
    /* convertIntoYesNo()
     * that functon convert N and Y to No and Yes
     * will accept N ans u
     */

    public static function convertIntoYesNo($alpha) {
        $val = array('n' => ucfirst(FLAG_NO), 'y' => ucfirst(FLAG_YES));
        $alpha = strtolower($alpha);
        return isset($val[$alpha]) ? $val[$alpha] : '';
    }


    /* convertYesNo()
     * that functon convert 0 and 1 to No and Yes
     * will accept 0 or 1
     */

    public static function convertYesNoInNum($val) {
        $val = array('0' => ucfirst(FLAG_NO), '1' => ucfirst(FLAG_YES));
        return isset($val[$digit]) ? $val[$digit] : '';
    }

    /**
     * serialize
     * 
     * @param type $str
     * @return string
     */
    public static function serialize($str) {
        return serialize($str);
    }

    /**
     * API serialize
     * 
     * @param type $str
     * @return string
     */
    public static function apiSerialize($str) {
        return $str;
    }

    /* getDecrypt()
     * that functon decrypt the val
     * will accept one decrypting value
     */

    public static function getDecrypt($val) {
        if (trim($val) == '')
            return '';

        return base64_decode($val);
    }

    /* getEncrypt()
     * that functon encrypt the val
     * will accept one encrypting value
     */

    public static function getEncrypt($val) {
        if (trim($val) == '')
            return '';

        return base64_encode($val);
    }

    public static function getMonthDays($month, $year) {
        if ($month < 1 || $year < 1)
            return '';

        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function getFundResponseStatus() {
        return array('' => 'Select', 'approve' => 'Approve', 'pending' => 'Pending', 'decline' => 'Decline');
    }

    public static function limitText($data, $limit) {
        if (trim($data) == '' || $limit < 1)
            return '';

        $data = substr($data, 0, $limit);
        $ret = $data . '...';

        return $ret;
    }

    public static function getDuration() {
        return array('' => 'Select', 'yesterday' => 'Yesterday', 'today' => 'Today', 'week' => 'Current Week', 'month' => 'Current Month');
    }
    
    public static function getDurationDates($duration) {
        switch ($duration) {
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 days'));
                return array('from' => $yesterday . ' 00:00:00', 'to' => $yesterday . ' 23:59:59');
                break;
            case 'today':
                return array('from' => date('Y-m-d') . ' 00:00:00', 'to' => date('Y-m-d') . ' 23:59:59');
                break;
            case 'week':
                $lastMonday = date('Y-m-d', strtotime('last monday')) . ' 00:00:00';
                $cuDate = date('Y-m-d') . ' 23:59:59';
                return array('from' => $lastMonday, 'to' => $cuDate);
                break;
            case 'month':
                $cuMonth = date('m');
//               $cuMonth = date('m') - 1; // mmmmm temporary
                $cuYear = date('Y');
                $cuDate = date('Y-m-d') . ' 23:59:59';
                return array('from' => $cuYear . '-' . $cuMonth . '-01' . ' 00:00:00', 'to' => $cuDate);
                break;
            case 'default':
                return array('from' => date('Y-m-d') . ' 00:00:00', 'to' => date('Y-m-d') . ' 23:59:59');
                break;
        }
    }

    /* getDurationAllDates function will return the all dates with start and end time of day in array
     * it will accept the duration parameter
     */

    public static function getDurationAllDates($duration, $time = TRUE) {
        switch ($duration) {
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 days'));
                if($time){
                    return array(0 => array('from' => $yesterday . ' 00:00:00', 'to' => $yesterday . ' 23:59:59'));
                    
                }else {
                    return array(0 => array('from' => $yesterday, 'to' => $yesterday));
                }
                break;
            case 'today':
                if($time){
                    return array(0 => array('from' => date('Y-m-d') . ' 00:00:00', 'to' => date('Y-m-d') . ' 23:59:59'));
                } else {
                    return array(0 => array('from' => date('Y-m-d'), 'to' => date('Y-m-d')));
                }
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('last monday')); //last monday
                $endDate = date('Y-m-d'); // current date as end date
                $retDates = array();
                //echo $startDate.'@@@',$endDate;
                while ($startDate <= $endDate) {
                    if($time){
                        $dtStart = $startDate . ' 00:00:00';
                        $dtEnd = $startDate . ' 23:59:59';
                    } else {
                        $dtStart = $startDate;
                        $dtEnd = $startDate;
                    }
                    $retDates[] = array('from' => $dtStart, 'to' => $dtEnd);
                    //$startDate = strtotime('+1 day', $startDate);
                    //echo $startDate.'-----';
                    $startDate = date('Y-m-d', strtotime("$startDate, +1 day"));
                }

                return $retDates;
                break;
            case 'month':
                $cuMonth = date('m');
                $cuYear = date('Y');
                $startDate = $cuYear . '-' . $cuMonth . '-01'; //first day of month
                $endDate = date('Y-m-d'); // current date as end date

                while ($startDate <= $endDate) {
                    if($time){
                        $dtStart = $startDate . ' 00:00:00';
                        $dtEnd = $startDate . ' 23:59:59';
                    } else {
                        $dtStart = $startDate;
                        $dtEnd = $startDate;
                    }
                    $retDates[] = array('from' => $dtStart, 'to' => $dtEnd);
                    $startDate = date('Y-m-d', strtotime("$startDate, +1 day"));
                }

                return $retDates;
                break;

            case 'default':
                if($time){
                    return array(0 => array('from' => date('Y-m-d') . ' 00:00:00', 'to' => date('Y-m-d') . ' 23:59:59'));
                } else {
                    return array(0 => array('from' => date('Y-m-d'), 'to' => date('Y-m-d')));
                }
                break;
        }
    }

    public static function getDaysArr($from, $to, $format = 'Y-m-d') {
        $dates = array();
        $current = strtotime($from);
        $last = strtotime($to);

        while ($current <= $last) {

            $dates[] = date($format, $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    public static function filterMVCResponse($data) {

        if (isset($data) && !empty($data)) {
            foreach ($data as $key => $val) {
                if ($val == '') {
                    $data[$key] = '-';
                } else {
                    switch ($key) {
                        case 'AccountBlockStatus':
                            switch ($val) {
                                case 0:
                                    $data[$key] = STATUS_BLOCKED;
                                    break;
                                case 1:
                                    $data[$key] = STATUS_ACTIVE;
                                    break;
                            }
                            break;

                        case 'AccountClose':
                            switch ($val) {
                                case 1:
                                    $data[$key] = STATUS_ACTIVE;
                                    break;
                                case 2:
                                    $data[$key] = STATUS_PENDING;
                                    break;
                                case 3:
                                    $data[$key] = STATUS_CLOSED;
                                    break;
                            }
                            break;

                        case 'ActivationStatus':
                            switch ($val) {
                                case 0:
                                    $data[$key] = STATUS_INACTIVE;
                                    break;
                                case 1:
                                    $data[$key] = STATUS_ACTIVATED;
                                    break;
                            }
                            break;

                        case 'CustomerType':
                            switch ($val) {
                                case 0:
                                    $data[$key] = 'MVCC';
                                    break;
                                case 1:
                                    $data[$key] = 'MVCI';
                                    break;
                            }
                            break;

                        case 'StatusCode':
                            switch ($val) {
                                case 0:
                                    $data[$key] = 'No Record';
                                    break;
                                case 1:
                                    $data[$key] = 'Used';
                                    break;
                                case 2:
                                    $data[$key] = 'Cancelled';
                                    break;
                                case 3:
                                    $data[$key] = 'Reversed';
                                    break;
                            }
                            break;
                    }
                }
            }
        }
//echo "<pre>";print_r($data);
        return $data;
    }

    public static function getPanCardOptions() {
        return array(
            '' => 'Select Type',
            ucfirst(STATUS_APPLIED) => 'Applied For',
            STATUS_ALREADY => 'Already Have'
        );
    }

    public static function getCurrDateTime($showcomma = FLAG_YES) {
        if ($showcomma == FLAG_YES) {
            return date("d/m/y, \a\\t h:i:s A", time());
        } else {
            return date("d/m/y \a\\t h:i:s A", time());
        }
    }

    public static function getFormattedDate($format = 'd/m/Y') {
        return date($format);
    }

    public static function ssl_encrypt($data) {
        $config = App_DI_Container::get('ConfigObject');
        $pass = $config->system->url->salt;
        $salt = substr(md5(mt_rand(), true), 8);

        $key = md5($pass . $salt, true);
        $iv = md5($key . $pass . $salt, true);

        $ct = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);

        return base64_encode('Salted__' . $salt . $ct);
    }

    public static function ssl_decrypt($data) {

        $data = base64_decode($data);
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);

        $key = md5($pass . $salt, true);
        $iv = md5($key . $pass . $salt, true);

        $pt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ct, MCRYPT_MODE_CBC, $iv);

        return $pt;
    }

    public static function encrypt($plaintext) {
        $config = App_DI_Container::get('ConfigObject');
        $pass = $config->system->url->salt;
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $pass, $iv);
        $crypttext = mcrypt_generic($td, $plaintext);
        mcrypt_generic_deinit($td);
        return base64_encode($iv . $crypttext);
    }

    public static function decrypt($crypttext) {
        $config = App_DI_Container::get('ConfigObject');
        $pass = $config->system->url->salt;
        $crypttext = base64_decode($crypttext);
        $plaintext = '';
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $ivsize = mcrypt_enc_get_iv_size($td);
        $iv = substr($crypttext, 0, $ivsize);
        $crypttext = substr($crypttext, $ivsize);
        if ($iv) {
            mcrypt_generic_init($td, $pass, $iv);
            $plaintext = mdecrypt_generic($td, $crypttext);
        }
        return trim($plaintext);
    }

    /**
     * encryptURL
     * Function used to Encrypt URI 
     * @param type $url
     * @return type
     */
    public static function encryptURL($url) {
        $config = App_DI_Container::get('ConfigObject');
        if ($config->system->url->encryption == TRUE) {
            if (substr($url, 1) != '/') {
                if (substr($url, 0, 4) == 'http') {
                    $domain = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"];
                    //print 'Humm.. included http need to remove domain name with http';exit;                   
                    //if(CURRENT_MODULE == 'operation') {
                    $url = str_replace($domain, '', $url);
//                   } elseif (CURRENT_MODULE == 'agent') {
//                       $url = str_replace($config->agent->url, '', $url);
//                   }
                }
            }
            return '/?a=' . urlencode(Util::encrypt($url));
        } else {
            return $url;
        }
    }

    /**
     * decryptURL
     * Function Used to Decrypt URL based on given URI 
     * @param type $uriString
     * @return string decrypted URI
     */
    public static function decryptURL($uriString) {
        $config = App_DI_Container::get('ConfigObject');
        if ($config->system->url->encryption == TRUE) {


            //return $uriString;
            //return Util::decrypt(urldecode($uriString));
            // echo Util::decrypt(($uriString));exit;

            return Util::decrypt(($uriString));
        } else {
            return $uriString;
        }
    }

    /**
     * Format URL
     * Function is used to format/Encrypt URL based on gived URI
     * @param type $url
     * @return string Encrypted/Formated URL
     */
    public static function formatURL($url) {
        if ($url != '' || $url != '/') {
            $url = Util::encryptURL($url);
        }
        return $url;
    }

    /**
     * Filter Encrypted URL
     * Function used to filter encrypted URL, It will set controller and action as per 
     * the URI
     * @param type $get
     * @param type $router
     * @return array
     */
    public static function filterEncryptURL($get, $router) {
        $request = new Zend_Controller_Request_Http();

        $config = App_DI_Container::get('ConfigObject');
        if ($config->system->url->encryption == TRUE) {
            if (isset($get['a'])) {
                $a = ($get['a']);
                $r = Util::decryptURL($a);
                $d = explode("/", $r);
                $controller = $d[1];
                $action = isset($d[2]) ? $d[2] : '';
                if (count($d) > 2) {
                    if (count($d) > 3) {
                        for ($i = 3; $i <= count($d);) {
                            if (isset($d[$i + 1])) {
                                $request->setParam($d[$i], $d[$i + 1]);
                            }
                            $i = $i + 2;
                        }
                    }
                    $getParam = explode('?', $d[2]);
                    $action = $getParam[0];
                    if (isset($getParam[1])) {
                        $param = explode('&', $getParam[1]);
                        foreach ($param as $value) {
                            $val = explode('=', $value);
                            if (isset($val[1])) {
                                $request->setParam($val[0], $val[1]);
                            }
                        }
                    }
                }
                $routerArr = array(
                    'controller' => $controller,
                    'action' => $action
                );
            } elseif (isset($request) && $request->getRequestUri() == '/') {
                $routerArr = array(
                    'controller' => 'index',
                    'action' => 'index'
                );
//                $router->addRoute('new',
//                    new Zend_Controller_Router_Route('*', array(
//                        'module'     => CURRENT_MODULE,                     
//                        'controller' => 'index',
//                        'action'     => 'index'
//                    ))
//                );             
            } else {
                $flgSet = FALSE;
                $allowedActions = Zend_Registry::get('ALLOWED_CONTROLLER_ACTION');
                //print '<pre>';print_r($allowedActions);exit;
                $ret = Util::getActionInfoFromURI($request->getRequestUri());
                //print_r($ret);exit;
                if ($ret != FALSE && isset($ret['controller']) && isset($ret['action'])) {
                    //if(in_array($ret['controller'],$allowedActions[CURRENT_MODULE])) {
                    if (isset($allowedActions[CURRENT_MODULE][$ret['controller']]) && in_array($ret['action'], $allowedActions[CURRENT_MODULE][$ret['controller']])) {
                        //print 'In';exit;
                        $flgSet = TRUE;
                        $routerArr = array(
                            'controller' => $ret['controller'],
                            'action' => $ret['action']
                        );
//                               $router->addRoute('new',
//                                   new Zend_Controller_Router_Route('*', array(
//                                       'controller' => $ret['controller'],
//                                       'action'     => $ret['action'],
//                                       'module'     => CURRENT_MODULE
//                                   ))
//                               );             
                    }
                    //}
                }
                // exit;
                if ($flgSet == FALSE) {
                    $routerArr = array(
                        'controller' => 'error',
                        'action' => 'error'
                    );

//                   $router->addRoute('new',
//                       new Zend_Controller_Router_Route('*', array(
//                           'controller' => 'error',
//                           'action'     => 'error',
//                           'module'     => CURRENT_MODULE
//                       ))
//                   );      
                }
            }
            $router->addRoute('new', new Zend_Controller_Router_Route('*', array(
                'controller' => $routerArr['controller'],
                'action' => $routerArr['action'],
                'module' => CURRENT_MODULE
                    ))
            );
            $routerArr['module'] = CURRENT_MODULE;
            Zend_Registry::set('ROUTER_VALUES', $routerArr);
        }

        return array(
            'request' => $request,
            'router' => $router,
            'module' => CURRENT_MODULE
        );
    }

    /**
     * Function used to get controller and action info from URI
     * @param type $uri
     * @return array()
     */
    public static function getActionInfoFromURI($uri) {

        if (substr($uri, 0, 1) == '/') {
            $d = explode("/", $uri);
            if (isset($d[2]) && count($d) > 2) {
                return array(
                    'controller' => $d[1],
                    'action' => Util::filterAction($d[2])
                );
            }
        } else {
            $d = explode("/", $uri);
            if (isset($d[1]) && count($d) > 2) {
                return array(
                    'controller' => $d[0],
                    'action' => Util::filterAction($d[1])
                );
            }
        }
        return false;
    }

    public static function filterAction($action) {
        if (strpos($action, '?') === false) {
            return $action;
        }
        $d = explode("?", $action);
        return isset($d[0]) ? $d[0] : '';
    }

    public static function addControllerURIForPaginator($uri) {
        $config = App_DI_Container::get('ConfigObject');
        if ($config->system->url->encryption != TRUE) {
            throw new Exception(__CLASS__ . ' : ' . __FUNCTION__ . ' only works when ROUTER_VALUES SET Only when URL Encryption is TRUE');
        }
        $routerInfo = Zend_Registry::get('ROUTER_VALUES'); //ROUTER_VALUES SET Only when URL Encryption is TRUE
        if (isset($routerInfo) && !empty($routerInfo)) {
            return '/' . $routerInfo['controller'] . '/' . $routerInfo['action'] . $uri;
        } else {
            return $uri;
        }
    }

    public static function formatURLforPaginator($uri) {
        $config = App_DI_Container::get('ConfigObject');
        if ($config->system->url->encryption == TRUE) {
            return Util::encryptURL(Util::addControllerURIForPaginator($uri));
        } else {
            return $uri;
        }
    }

    public static function dateDiff($timeFirst, $timeSecond) {
        $timeFirst = strtotime($timeFirst);
        $timeSecond = strtotime($timeSecond);
        $differenceInSeconds = $timeSecond - $timeFirst;
        return $differenceInSeconds;
    }

    public static function validateSingleSession() {
        /* single session */
        if (App_DI_Container::get('ConfigObject')->session->ssn_implementation) {
            $user = Zend_Auth::getInstance()->getIdentity();
            if (isset($user->id)) {
                $sessionModel = new Session();
                //$validSession = ;
                if (!$sessionModel->validateLoginSession()) {
                    //$this->_redirect(Util::formatURL('/profile/login?sess=err'));
                    Util::redirect(Util::formatURL('/profile/login?sess=err'));
                }
            }
        }
    }

    /**
     * redirect - Only use from util/model
     * @param type $url
     */
    public static function redirect($url) {
        header("Location: $url"); /* Redirect browser */
        exit();
    }

    /**
     * Convert object to array
     * 
     */
    public static function toArray($obj) {
        if (is_object($obj)) {
            return $obj->toArray();
        } else {
            return $obj;
        }
    }

    /*
     * Corresponding values in t_settings table for 'program_type'
     */

    public static function getProgramType() {
        $blankProgramType = array('' => 'Select Program Type');
        $selProgramType = array_merge($blankProgramType, Zend_Registry::get("PROGRAM_TYPE_TXT"));
        return $selProgramType;
    }

    /*
     * Get Account type
     */

    public static function getBankAccountType() {
        return array(
            '' => 'Select Type',
            'savings' => 'Savings',
            'current' => 'Current'
        );
    }

    /* getRemitterSearchCriteria() will return the search options of remitter
     */

    public static function getRemitterSearchCriteria() {
        return array('' => 'Select', 'name' => 'Name', 'bank_name' => 'Bank Name', 'mobile' => 'Mobile', 'email' => 'Email');
    }
    
    /* getRatRemitterSearchOptions() will return the search options of remitter
     */

    public static function getRatRemitterSearchOptions() {
        return array('' => 'Select', 'name' => 'Name','mobile' => 'Mobile', 'email' => 'Email');
    }

    /* getRatRemitterSearchCriteria() will return the search options for ratnakar remitter
     */

    public static function getRatRemitterSearchCriteria() {
        return array('' => 'Select', 'name' => 'Name', 'bank_name' => 'Bank Name', 'mobile' => 'Mobile', 'email' => 'Email', 'utr' => 'NEFT Reference Number', 'txn_code' => 'Shmart Reference Number');
    }
    
    /* getRatRemitterReportSearchCriteria() will return the first name of search options for ratnakar remitter
     */

    public static function getRatRemitterReportSearchCriteria() {
        return array('' => 'Select', 'name' => 'First Name');
    }

    /*
     * Get the fee components like partial fee and service tax
     */

    public static function getFeeComponents($fee = 0) {// Srvice tax percent on Fee
        if ($fee == 0) {
            return array(
                'partialFee' => 0,
                'serviceTax' => 0,
            );
        } else {
            $pcntServiceTax = App_DI_Container::get('ConfigObject')->remittance->service_tax->percent;
            $partialFee = ($fee / (100 + $pcntServiceTax)) * 100; // fee * 112.36%
            $serviceTax = $fee - $partialFee; // partial fee * 12.36%
            return array(
                'partialFee' => $partialFee,
                'serviceTax' => $serviceTax,
            );
        }
    }

    /*
     * Calculate Fee OR Commission
     * Parameters required
     * $params['txn_flat'] = txn_flat
     * $params['txn_pcnt'] = txn_pcnt
     * $params['txn_min'] = txn_min
     * $params['txn_max'] = txn_max
     * $params['amount'] = amount
     * 
     */

    public static function calculateFee($params = array()) {
        $feeComm = 0;
        if (isset($params['txn_flat']) && $params['txn_flat'] > 0) {
            $feeComm = $params['txn_flat'];
        } else { // pcnt rate 
            $pcnt = ($params['txn_pcnt'] * $params['amount']) / 100;
            $min = $params['txn_min'];
            $max = $params['txn_max'];
            if ($pcnt < $min) {
                $feeComm = $min;
            } else if ($pcnt > $max) {
                $feeComm = $max;
            } else {
                $feeComm = $pcnt;
            }
        }
	error_log('Util.calculateFee');
	error_log($pcnt);
        error_log($min);
        error_log($max);

        if($params['return_type'] == TYPE_COMMISSION){
            return $feeComm;
        }
        else { //if($params['return_type'] == TYPE_FEE)
            return $feeComm;
        }
    }

	public static function calculateRoundedFee($params = array()) {
        	$feeComm = 0;
        	$min=0;
        	$max=0;
        	if (isset($params['txn_flat']) && $params['txn_flat'] > 0) {
        	    $feeComm = $params['txn_flat'];
        	} else { // pcnt rate 
            $pcnt = ($params['txn_pcnt'] * $params['amount']) / 100;
            $min = $params['txn_min'];
            $max = $params['txn_max'];
            if ($pcnt < $min) {
                $feeComm = $min;
            } else if ($pcnt > $max) {
                $feeComm = $max;
            } else {
                $feeComm = $pcnt;
            }
        }

        $whole = floor($feeComm);       
        $fraction = $feeComm - $whole;  
        if($fraction > 0.25 && $fraction <= 0.75){
            $fraction = 0.50;
        }elseif($fraction > 0.75){
            $fraction = 0.00;
            $whole = $whole + 1;
        }else{
            $fraction = 0.00;
        }

        $feeComm = $whole + $fraction;

        if($min > 0 || $max > 0){
            if ($feeComm < $min) {
                $feeComm = $min;
            } else if ($feeComm > $max) {
                $feeComm = $max;
            }
        }

        return $feeComm;
    }


    public static function getServerNameForCronAlert() {
        $config = App_DI_Container::get('ConfigObject');
        return $config->operation->url;
    }

    /*
     * Get Account type for BOI NEFT
     */

    public static function getAccountTypeNeft($accType) {
        switch ($accType) {
            case 'savings':
                $numericAccType = 10;
                break;
            case 'current':
                $numericAccType = 11;
                break;
        }
        return $numericAccType;
    }

    /**
     * return the name of  NEFT batch files as per the format
     */
    public static function getNeftBatchFileName() {
        $date = date("dmyHis", time());
        $date = $date . mt_rand(1111, 9999);
        return $date;
    }

    public static function getListingTitle($title, $dur, $singleDayOnly = false) {

        if (!$singleDayOnly) {
            $durationArr = self::getDurationDates($dur);
            $fromDate = explode(' ', Util::returnDateFormatted($durationArr['from'], "d-m-Y", "Y-m-d", "-"));
            $toDate = explode(' ', Util::returnDateFormatted($durationArr['to'], "d-m-Y", "Y-m-d", "-"));
            switch ($dur) {
                case 'yesterday':
                    $title .= ' For ' . $toDate[0];
                    break;
                case 'today':
                    $title .= ' For ' . $toDate[0];
                    break;
                case 'week':
                case 'month':
                case 'default':
                    $title .= ' For ' . $fromDate[0] . ' to ' . $toDate[0];
                    break;
            }
        } else {
            $dt = explode(' ', self::returnDateFormatted($dur, "Y-m-d", "d-m-Y", "-"));
            $title .= ' For ' . $dt[0];
        }

        return $title;
    }

    /**
     * Return restored ip address
     * 
     * @param type $ip
     * @return string ip in AAA.BBB.CCC.DDD
     */
    public static function restoreIpAddressFromat($ip) {
        return (string) ((int) substr($ip, 0, 3) . '.' . (int) substr($ip, 3, 3) . '.' . (int) substr($ip, 6, 3) . '.' . (int) substr($ip, 9, 3));
    }

    /**
     * Return NEFT status
     * 
     * @param type $status
     * @return string status string Neft Successful or Neft Failed or Refunded
     */
    public static function getNeftStatus($status) {
        switch ($status) {
            case STATUS_SUCCESS:
                $neftStatus = 'Neft Successful';
                break;
            case STATUS_REFUND:
                $neftStatus = 'Refunded';
                break;
            case STATUS_FAILURE:
                $neftStatus = 'Neft Failed';
                break;
            case 'default':
                $neftStatus = $status;
                break;
        }
        return $neftStatus;
    }

    public static function getRecordsPerPage($type='low') {
        
        if($type =='high') {
            return array(
                '100' => 'Default (100)',
                '500' => '500',
                '1000' => '1000',
                '2000' => '2000',
                '3000' => '3000'
            );
        } else {
            $config = App_DI_Container::get('ConfigObject');

            $defaultperpage = App_DI_Container::get('ConfigObject')->paginator->items_per_page;
            return array(
                $defaultperpage => 'Default (' . $defaultperpage . ')',
                '50' => '50',
                '100' => '100',
                '200' => '200',
                '500' => '500'
            );
        }
    }

    public static function getUserType() {
        return array('' => 'Select User Type',
            DbTable::TABLE_AGENTS => USER_TYPE_AGENT,
            DbTable::TABLE_OPERATION_USERS => USER_TYPE_OPERATION
        );
    }

    // Return Array length
    public static function getArrayLength($array = array()) {
        return count($array);
    }

    /* digits only, no dots */

    public static function isDigits($element) {
        $element = trim($element);
        return !preg_match("/[^0-9]/", $element);
    }

    /* digits only */

    public static function checkDigitsLength($val, $minLength = 0, $maxLength = 0, $fixedLength = 0) {
        $val = trim($val);

        if ($val != '') {
            if ($minLength > 0 && $minLength > 0) {
                if (strlen($val) < $minLength || strlen($val) > $maxLength)
                    return false;
            } else if ($fixedLength > 0) {
                if (strlen($val) != $fixedLength)
                    return false;
            }

            return true;
        } else {
            return false;
        }
    }

    public static function getCorpcardholderSearchCriteria() {
        return array('' => 'Select', 
            'first_name' => 'First Name', 
            'last_name' => 'Last Name', 
            'mobile' => 'Mobile',
            'medi_assist_id' => 'Medi Assist Id',
            'card_number' => 'Card Number'
            );
    }
    
    
    public static function getCorpkotakcardholderSearchCriteria() {
        return array('' => 'Select', 
            'first_name' => 'First Name', 
            'last_name' => 'Last Name', 
            'mobile' => 'Mobile',
            'member_id' => 'Member Id',
            'card_number' => 'Card Number'
            );
    }
    
    
    public static function getAmulcardholderSearchCriteria() {
        return array('' => 'Select', 
            'first_name' => 'First Name', 
            'last_name' => 'Last Name', 
            'member_id' => 'Member Id',
            'card_number' => 'Card Number'
            );
    }
    
     public static function getBoicardholderSearchCriteria() {
        return array('' => 'Select', 
            'first_name' => 'First Name', 
            'last_name' => 'Last Name', 
            'ref_num' => 'Application Reference Number',
            'member_id' => 'Member Id',
            'card_number' => 'Card Number',
            'account_no' => 'Account Number'
            );
    }
    
    public static function getMASearchCriteria() {
        return array('' => 'Select', 'file' => 'File Name');
    }

    /*
     * 
     */

    public function getGenderTxt($chr) {
       $gender = '';
        if (strtolower($chr) == 'm'){
           $gender = 'male';
        }
        else if(strtolower($chr) == 'f'){
           $gender = 'female';
        }
        return $gender;
    }

    /**
     * removeSpecialChars
     * Function to remove special character
     * @param type $string
     * @return type
     */
    public static function removeSpecialChars($string, $removeTab=FALSE) {
        if($removeTab){
            $str = str_replace(array("\r", "\n"), "", $string);
            return trim(preg_replace('/\t+/', '', $str));
        }
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    /**
     * truncateString
     * Function to truncate string
     * @param <String> $text
     * @param <Number> $maxLength
     * @return <String>
     */
    public static function truncateString($text, $maxLength = 200) {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength);
        }
        return $text;
    }

    /*
     * bankUnicodesArray returns the array with the Unicodes of the Banks
     */

    public static function bankUnicodesArray() {

        $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
        $bankAxisUnicode = $bankAxis->bank->unicode;
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bankKotak->bank->unicode;

        $unicodeArr = array($bankAxisUnicode, $bankBoiUnicode, $bankRatnakarUnicode, $bankKotakUnicode);
        return $unicodeArr;
    }

     /*
     * bankProductUnicodesArray returns the array with the Unicodes of the Product
     */

    public static function bankProductRemitUnicodesArray() {

        $bankProductBoi = App_DI_Definition_BankProduct::getInstance(BANK_BOI_REMIT);
        $bankProductBoiUnicode = $bankProductBoi->product->unicode;
        $bankProductKotak = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_REMIT);
        $bankProductKotakUnicode = $bankProductKotak->product->unicode;
        $bankProductRatnakar = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_REMIT);
        $bankProductRatnakarUnicode = $bankProductRatnakar->product->unicode;
        
        $unicodeProductArr = array($bankProductBoiUnicode, $bankProductKotakUnicode, $bankProductRatnakarUnicode);
        return $unicodeProductArr;
    }

    /**
     * generateRandomNumber
     * Generate Random Number based on lenthgth
     * if $initialZero = TRUE then it will return the minimum value can be start with 1 to the length - 1 of the given length
     * otherwise it will start with lenght 1 i.e. if length is 5 minimum will be 11111
     * @param <Number> $len
     * @param <Number> $initialZero
     * @return <Number>
     */
    public static function generateRandomNumber($len = 5, $initialZero = FALSE) {
        if (!$initialZero) {
            $min = str_repeat(0, $len - 1) . 1;
        } else {
            $min = str_repeat(1, $len);
        }
        $max = str_repeat(9, $len);
        return mt_rand($min, $max);
    }

    public static function pr($arr, $die = true) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
        if ($die)
            die();
    }

    public static function dump($arr, $die = true) {
        echo "<pre>";
        var_dump($arr);
        echo "</pre>";
        if ($die)
            die();
    }

    /* getDurationRangeAllDates function will return the all dates with start and end time of day in array
     * it will accept the duration parameter
     */

    public static function getDurationRangeAllDates($duration) {

        $startDate = $duration['from'];
        $endDate = $duration['to'];

        while ($startDate <= $endDate) {
            $dtStart = $startDate . ' 00:00:00';
            $dtEnd = $startDate . ' 23:59:59';
            $retDates[] = array('from' => $dtStart, 'to' => $dtEnd);
            $startDate = date('Y-m-d', strtotime("$startDate, +1 day"));
        }

        return $retDates;
    }
    
    public static function arrayToObject($data) {
        if(is_array($data)) {
            $object = new stdClass();
            foreach ($data as $key => $value)
            {
                $object->$key = $value;
            }            
            return $object;
        } else {
            return $data;
        }
            
    }

    //Get Load channels for purse master
     public static function getLoadChannel() {
        return array(
            '' => 'Select Load Channel',
            BY_OPS => BY_OPS,
            MEDIASSIST => MEDI_ASSIST,
            BY_API => BY_API,
            TYPE_NONE => TYPE_NONE,
        );
    }
    
     //Get Txn Restriction Type for purse master
     public static function getTxnRestrictionType() {
        return array(
            '' => 'Select',
            'tid' => TYPE_TID,
            'mcc' => TYPE_MCC,
        );
    }
    
     public static function cutoffValidity($params) {
        $cutOffInSeconds = ($params['load_validity_min'] * 60) + ($params['load_validity_hr'] * 60 * 60)  + ($params['load_validity_day'] * 60 * 60 * 24);
        return $cutOffInSeconds;
    }
    
    public static function filterAmount($amount) {
            return $amount * 100;
    }
    
    public static function validateAmount($amt,$type = AMOUNT_INT) {
        if($type == AMOUNT_INT) {
            $filter = new Zend_Filter_Digits();
            
            $rAmt = $filter->filter($amt);
            if($amt === $rAmt) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public static function maskCard($str = '', $end = 4, $start = 6) {
        if($str != '')
        {
            $str2 = substr($str, 0, $start);
            $remaining = substr($str, $start);
            $mask = strlen($remaining) - $end; 
            for($i = 0; $i < $mask; $i++)
            {
                $str2 .= '*';
            }
            $str2 .= substr($str, (-1*$end));
            return $str2;
        }
        return '';
    }
    
    public static function convertToPaisa($amt = 0) {
        if(is_object($amt)) {
            //Typecasting in case of Object
            $amt = (string) $amt;
        }        
        if(is_numeric($amt))
        {
            return $amt / 100;
        }
        return 0;
    }
    
    public static function getRs($amt = 0) {
        $str = explode(".", $amt);
        return $str[0];
    }
    
    public static function getAgentTypeValue($type) {
        if(empty($type)) {
            return false;
        }
        switch ($type)
        {
            case SUPER_AGENT:
                $flg = SUPER_AGENT_DB_VALUE;
                break;
            
            case DISTRIBUTOR_AGENT:
                $flg = DISTRIBUTOR_AGENT_DB_VALUE;
                break;
            
            case SUB_AGENT:
                $flg = SUB_AGENT_DB_VALUE;
                break;
            
            default :
                $flg = FALSE;
                break;
        }
        return $flg;
    }
    
    
    public static function getKotakCustStatus($statusArr) {
        $status = '';
        if ($statusArr['status'] == STATUS_PENDING) {
        {
            if ($statusArr['status_ops'] == STATUS_PENDING) {
                $status = 'Pending at Operations ';
            } else if ($statusArr['status_ops'] == STATUS_APPROVED) {
                if ($statusArr['status_bank'] == STATUS_APPROVED) {
                    if ($statusArr['status_ecs'] == STATUS_FAILURE) {
                        $status = 'ECS Registration failure';
                    } else {
                        $status = 'Pending at ECS ';
                    }
                } elseif ($statusArr['status_bank'] == STATUS_REJECTED) {
                    $status = 'Rejected at Bank ';
                } else {
                    $status = 'Pending at Bank ';
                }
            } else if ($statusArr['status_ops'] == STATUS_REJECTED) {
                $status = 'Rejected by Operations';
            }
        }
        
        }
        else {
            $status = $statusArr['status'];
        }


        return $status;
    }
    
        public static function getApplicationStatus() {
        return array(
            '' => 'Select Type',
            'all' => 'all',
            STATUS_PENDING => STATUS_PENDING,
            STATUS_APPROVED => STATUS_APPROVED,
            STATUS_REJECTED => STATUS_REJECTED,
            
        );
    }
    
    public static function getCRNStatusDropDown() {
        return array('' => 'Select', 'free' => 'Free', 'used' => 'Used', 'duplicate' => 'Duplicate', 'blocked' => 'Blocked');
    }
    
    public static function getNsdcStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_PENDING => 'Pending',
            STATUS_APPROVED => 'Approved',
            STATUS_CARD_ISSUED => 'Card Issued',
            STATUS_REJECTED => 'Rejected',
        );
    }
    
    public static function productUnicodesArray($program_type = PROGRAM_TYPE_REMIT) {
        $unicodeArr = array();
        if($program_type == PROGRAM_TYPE_REMIT) {
            $prodRemitBoi = App_DI_Definition_BankProduct::getInstance(BANK_BOI_REMIT);
            $prodRemitBoiUnicode = $prodRemitBoi->product->unicode;
            $prodRemitKotak = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_REMIT);
            $prodRemitKotakUnicode = $prodRemitKotak->product->unicode;
            $prodRemitRatnakar = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_REMIT);
            $prodRemitRatnakarUnicode = $prodRemitRatnakar->product->unicode;
            $unicodeArr = array($prodRemitBoiUnicode, $prodRemitKotakUnicode, $prodRemitRatnakarUnicode);
        }
        return $unicodeArr;
    }
    
    public function formatString($str, $type,$lenght,$placeholder,$fixer='pre')
    {
        $string ='';
        if($type == 'int') {
            $len = strlen(round($str));
            //echo '='.$len.'=';
            if($len<$lenght) {
                for($len;$len<$lenght;$len++) {
                    $string .= $placeholder;
                }
            }
            
        }
        
        if($type == 'string') {
            $len = strlen(($str));
            //echo '='.$len.'=';
            if($len<$lenght) {
                for($len;$len<$lenght;$len++) {
                    $string .= $placeholder;
                }
            }
            
        }
        
        if($fixer == 'pre') {
            return $string.$str;
        } else {
            return $str.$string;
        }
        
    }
    
      public function generate6DigitCode()
    {
        return rand(111111,999999);
    }
    

    public static function compareAmount($amount1,$amount2) {

        $epsilon = 0.00001;
        if(abs($amount1-$amount2) < $epsilon) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


     //Get Load channels for Global Purse master
     public static function getWalletLoadChannel() {
        return array(
            '' => 'Select Load Channel',
            BY_OPS => BY_OPS,
            BY_AGENT => BY_AGENT,
            BY_API => BY_API,
        );
    }
     //Get Txn Restriction Type for Global Purse master
     public static function getWalletTxnRestrictionType() {
        return array(
            '' => 'Select',
            'tid' => TYPE_TID,
            'mcc' => TYPE_MCC,
            'none' => TYPE_NONE,
        );
    }
     public static function getKotakStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_PENDING => 'Pending',
            STATUS_APPROVED => 'Approved',
            STATUS_CARD_ISSUED => 'Card Issued',
            STATUS_REJECTED => 'Rejected',
        );
    }
    
    
    public static function isValidMVCType($m) {
        if(in_array(strtolower($m), array('mvci','mvcc'))) {
            return TRUE;
        }
        return FALSE;
    }
     public static function getWalletStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_LOADED => 'Loaded',
            STATUS_FAILED => 'Not Loaded',
           
        );
    }
    
    public static function debug($data ='',$terminate = TRUE) 
    {
        echo 'Debuging<br />';
        if(!empty($data)) {
            echo '<pre>';print_r($data);echo '</pre>';
        }
        $callers=debug_backtrace();
        echo 'Function: '.$callers[1]['function'] . '<br />';
        echo 'Line Number: '.$callers[0]['line'] . '<br />';
        echo 'Class : '.$callers[1]['class'] . '<br />';
        echo 'Class Path: '.$callers[0]['file'] . '<br />';
        if($terminate) {
            exit;
        }
    }
    
    public static function getCorporateRegistrationAgentTab($current = 'basic', $agentId = 0) {
        $addURL = '';
        if ($agentId > 0) {
            //$addURL = '?id='.$agentId;
        }
        $strReturn = '<div class="path">
                <ul  class="subnav">
                        <li ' . ($current == 'basic' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/add") . '" >Basic Details</a></li>
                        <li ' . ($current == 'address' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/addaddress") . '">Address Details</a></li>
                        <li ' . ($current == 'identification' ? 'class ="selected"' : '') . '><a href="' . Util::formatURL("/signup/addidentification") . '">Identification Details</a></li>
                        </ul>
                </div>
            </div>';
        //  <li '. ($current == 'docs' ? 'class ="selected"': '') .'><a href="/agents/addocs'.$addURL.'">Upload Documents</a></li>                    

        return $strReturn;
    }
    
    public static function getCorporateTypeValue($type) {
        if(empty($type)) {
            return false;
        }
        switch ($type)
        {
            case HEAD_CORPORATE:
                $flg = SUPER_CORPORATE_DB_VALUE;
                break;
            
            case REGIONAL_CORPORATE:
                $flg = DISTRIBUTOR_CORPORATE_DB_VALUE;
                break;
            
            case LOCAL_CORPORATE:
                $flg = SUB_CORPORATE_DB_VALUE;
                break;
            
            default :
                $flg = FALSE;
                break;
        }
        return $flg;
    }

    public static function getMonths() 
    {
        return $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
    }
    
    public static function getYears() 
    {
        $starting_year  =date('Y');
        $ending_year = date('Y', strtotime('-15 year'));
        $years = array();
        for($starting_year; $starting_year >= $ending_year; $starting_year--) {
            $years[$starting_year] =  $starting_year;  
           
        }
        return $years;
    }
    
    public static function getBucket() 
    {
        $globalBuckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
        unset($globalBuckets['9']);
        $globalBuckets[''] = 'All';
        ksort($globalBuckets);
        return $globalBuckets;
        //return  array(''=>'All',1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6');
    }
    
    public function getReportNames(){
         
        $reportArr = array(
            AGENT_BALANCE_SHEET_REPORT          =>  'Agent Balance Sheet Report',
            WALLET_BALANCE_SHEET_REPORT         =>  'Wallet Balance Report',
            AGENT_VIRTUAL_BALANCE_SHEET_REPORT  =>  'Agent Virtual Balance Sheet Report'
        );
        return $reportArr;
    }
    
    public function getPaymentStatus(){
        $payamArr = array(''=>'All','generated'=>'Generated','processed'=>'Processed','hold'=>'Hold','manual'=>'Manual','pending'=>'Pending');
        return $payamArr;
    }
    public static function getCardLoadStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_PENDING => 'Pending',
            STATUS_LOADED => 'Loaded',
            STATUS_FAILED => 'failed',
            STATUS_CUTOFF => 'cutoff',
        );
    }
    public static function getCardHolderStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_ACTIVE => 'Active',
            STATUS_BLOCKED => 'Blocked',
            STATUS_ECS_FAILED => 'ECS Failed',
            STATUS_ECS_PENDING => 'ECS Pending',
            STATUS_INACTIVE => 'Inactive',
        );
    }
    
     public static function getKYCCorpcardholderSearchCriteria() {
        return array('' => 'Select', 
            'card_pack_id' => 'Card Pack ID', 
            'card_number' => 'Card Number', 
            'mobile' => 'Mobile',
            'aadhaar card' => 'Aadhaar No.',
            'passport' => 'Passport No.',
            'employee_id' => 'Employee ID'
            );
    }
    
     public static function getCardStatusList() {
        return array(
            '' => 'Select Status',
            STATUS_ACTIVATION_PENDING => 'Activation Pending',
            STATUS_PRE_ACTIVATED => 'Pre Activated',
           
        );
    }
    
    public static function getAgentImportStatus() {
        return array(
            '' => 'Select Type',
            STATUS_PENDING => 'PENDING',
            STATUS_COMPLETE => 'COMPLETE',
            STATUS_DUPLICATE => 'DUPLICATE',
            STATUS_FAILED => 'FAILED',
            STATUS_TEMP => 'TEMP',
            
        );
    }
    
    public static function getCorporateBankLogo($bankUnicode) {
        if(empty($bankUnicode)) {
            return '';
        }
        $bankAxis = App_DI_Definition_Bank::getInstance(BANK_AXIS);
        $bankAxisUnicode = $bankAxis->bank->unicode;
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bankKotak->bank->unicode;
        switch ($bankUnicode)
        {
            case $bankAxisUnicode:
                $flg = '';
                break;
            
            case $bankBoiUnicode:
                $flg = '';
                break;
            
            case $bankRatnakarUnicode:
                $flg = 'rbl-bank-logo.jpg';
                break;
            
            case $bankKotakUnicode:
                $flg = 'logo.gif';
                break;
            
            default :
                $flg = FALSE;
                break;
        }
        return $flg;
    }
    
     public static function getRatIdentificationType() {
       
          return array(
            '' => 'Select Type',
            'Passport' => 'Passport',
            'Driving license' => 'Driving Licence',
            'PAN card' => 'PAN card',
            'Aadhar card' => 'Aadhaar No.',
            'Government approved ID card' => 'Government approved ID card',
            
        );  
     
    }

    public static function getRatAddressProofType() {
        return array(
            '' => 'Select Type',
            'Passport' => 'Passport',
            'Bank account statement' => 'Bank account statement',
            'Electricity bill' => 'Electricity bill',
            'Ration card' => 'Ration card',
            'Government approved Address Proof' => 'Government approved Address Proof',
        );
    }
    
 
    /**
     * Returns file extension
     * 
     *@access public static
     * @return string
     */
    public static function getFileExtension($filename)
    {
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!empty($file_ext)) {
            return $file_ext;
        }
        return FALSE;
    }
    
    /**
     * getCardType()
     * Returns card type for kotakGPR
     * @access public static
     * @return array
     */
    public static function getCardType()
    {
       return array('n' => 'Normal');
    }
    /**
     * getKptakTxnIdentifier()
     * Returns card type for kotakGPR
     * @access public static
     * @return array
     */
    public static function getKptakTxnIdentifier()
    {
       return array('' => 'Select','cn' => 'Card Number','mi' => 'Member ID','ei' => 'Employee Id');
    }
    /*
     * getRemittaceResponseStatus : getting the status of remittance response
     */
    public static function getRemittaceResponseStatus() {
        return array(
            '' => 'Select Type',
            'all' => 'all',
            STATUS_PROCESSED => 'Processed',
            STATUS_IN_PROCESS => 'In Process',
            STATUS_SUCCESS => 'Success',
            STATUS_FAILURE => 'Failure',
            STATUS_REFUND => 'Refund',
            STATUS_INCOMPLETE => 'In Complete',
        );
    }
    
    /*
    * getStamentBankList :
    * Display bank list in Agentfunding- uploadkotak bank statement
    */
    public static function getStamentBankList() {
        $bank = new Banks();
        $bankRatnakar = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $bankRatnakar->bank->unicode;
        $ratnakarBank = $bank->getBankbyUnicode($bankRatnakarUnicode);
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bankKotak->bank->unicode;
        $kotakBank = $bank->getBankbyUnicode($bankKotakUnicode);
        
        $bankIcici = App_DI_Definition_Bank::getInstance(BANK_ICICI);
        $bankIciciUnicode = $bankIcici->bank->unicode;
        $iciciBank = $bank->getBankbyUnicode($bankIciciUnicode);
        
        return array(
            '' => 'Select Bank',
            $ratnakarBank->id => $ratnakarBank->name,
            $kotakBank->id => $kotakBank->name,
            $iciciBank->id => $iciciBank->name,
        );
    }
    
    
    public static function disabledSMSByProduct($constProduct) {
        $disabledProductArr = array(PRODUCT_CONST_RAT_GPR,PRODUCT_CONST_RAT_COP);
        if(in_array($constProduct,$disabledProductArr)){
            return TRUE;
        }else{
            return FALSE;
        }
    }


	public static function dateDiffOTP($timeFirst, $timeSecond) {
        $firstArr = explode(" ",$timeFirst);
        $dateFirst = $firstArr[0];

        $secondArr = explode(" ",$timeSecond);
        $dateSecond = $secondArr[0];

        $datetimeFirst = strtotime($dateFirst);
        $datetimeSecond = strtotime($dateSecond);
        $differenceInSeconds = $datetimeFirst - $datetimeSecond ;
        return $differenceInSeconds;
    }


      public function fill_chunck($array, $parts) 
	  {
        $result = array();
        $temp =1;
        $cnt =1;
        for($i=0;$i < count($array); $i++ ){
            if($temp > CORP_DISBURESEMENT_TITUM_MAX_RECORDS){
                $temp =1;
                $cnt++;
            }
            $result[$cnt][]=$array[$i];
            $temp++;
        }
        return $result;
    }

	public static function aplhaValidation($str){
        $pattern  = '/[^a-z\s]/i';
        if(preg_match($pattern, $str)) {
            return FALSE;
        }
        return TRUE;
    }

    public static function aplhanumericValidation($str){
        $pattern = '#^[a-z0-9\s]+$#i';
        if(!preg_match($pattern, $str)) {
            return FALSE;
        }
        return TRUE;
    }

  public static function convertToRupee($amt = 0) {
        if(is_object($amt)) {
            //Typecasting in case of Object
            $amt = (string) $amt;
        }
        if(is_numeric($amt))
        {
            return $amt / 100;
        }
        return 0;
    }

    public static function getProgramTypeArray()
    {
        return array(PROGRAM_TYPE_CORP, PROGRAM_TYPE_DIGIWALLET, PROGRAM_TYPE_MVC);
    }

    public function getDateTimeValidation($date) {
        if ($date != '0000-00-00 00:00:00') {
            return true;
        }
        return false;
    }

public static function sendStaticOTP($remitterId = 0 ,$bankUnicode) {
       $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
       $bankKotakUnicode = $bankKotak->bank->unicode;
       $maxTxn = 0;
       $otpLife = 0;
       $otp = '';
       switch ($bankUnicode)
        {
            case $bankKotakUnicode:
                $Obj =  new Remit_Kotak_Remittancerequest();
                $maxTxn = $bankKotak->remit->otp->max_txn ;
                $otpLife = $bankKotak->remit->otp->life ;
                break;
        }
        $lastOTPDetails = $Obj->getRemitterOTPDetails($remitterId);
        if($otpLife> 0){
            // Check last OTP sent Datetime
            $currdateTime = strtotime(date('Y-m-d'));
            
            if($lastOTPDetails['date_otp'] != ''){
            $lastOTPDate = explode(" ",$lastOTPDetails['date_otp']);
            $lastOTPSentdateTime = strtotime($lastOTPDate[0]);
            $diff = $currdateTime - $lastOTPSentdateTime;
            
            $otpLifeTime = $otpLife * 86400;
          
            if ($diff > $otpLifeTime){
                 return $otp;
              }
              else{
                $remitterRemittance = $Obj->getRemitterRemittanceCount($remitterId, $lastOTPDetails['otp']);
           
                if ($remitterRemittance >= $maxTxn){
                    return $otp;
                }
                else {
                    $otp = $lastOTPDetails['otp'];
                    return $otp;
                }
            }
            }
            else{
                 return $otp;
            }
        }
        if($maxTxn > 0) {
            // Check number of remittance
           
           $remitterRemittance = $Obj->getRemitterRemittanceCount($remitterId, $lastOTPDetails['otp']);
           
            if ($remitterRemittance >= $maxTxn){
                return $otp;
             }
        }
       
       return $otp;
    }


public static function isValidPurseId($purse_id, $purseArr) {
        if(in_array($purse_id, $purseArr)) {
            return TRUE;
        }
        return FALSE;
    }

    public static function convertIntoPaisa($amt = 0) {
        if(is_object($amt)) {
            //Typecasting in case of Object
            $amt = (string) $amt;
        }
        if(is_numeric($amt))
        {
            return $amt * 100;
        }
        return 0;
    }
    public static function dateValidCheck($date,$separation){
        
        if($separation = 'dd/mm/yyyy') {
            list($day,$month,$year) = sscanf($date, '%d/%d/%d');
        } elseif($separation = 'dd-mm-yyyy') {
            list($day,$month,$year) = sscanf($date, '%d-%d-%d');
        } elseif($separation = 'yyyy-mm-dd') {
            list($year,$month,$day) = sscanf($date, '%d-%d-%d');
        }
            
        if (checkdate($month, $day, $year)) {
           return true;  
        }
        return false;
    }
    
    public static function getRandTime() {
        $date = date("His", time());
        $date = $date . mt_rand(1111, 9999);
        return $date;
    }
    
    public static function getStatusArray($status) {
        switch ($status) {
            case 'active':                
                $status = 'Active';
                break;
            case 'in_process':
                $status = 'In Process';
                break;
            case 'refund':                
                $status = 'Refund';
                break;
            case 'inactive':                
                $status = 'Inactive';
                break;
            case 'failure':                
                $status = 'Failure';
                break;
            case 'hold':                
                $status = 'Hold';
                break;
        }
        
        return $status;
    }
    
    public static function getdefaultExpiryDate() {
        
        $nextYear = date("YmdHis", strtotime('+12 month'));
        
        return $nextYear;
    }

    public function getBeneCodeFromId($id) {
        if (strlen($id) < 8) {
            $id = $id + '10000000';
        }
        return $id;
    }
    
    public static function getArrayBykey($params,$filterKey){
       
        $keyArray = array();
        foreach($params as $key=>$purseCode){
            $keyArray[$key] = $params[$key][$filterKey];   
        }
        return $keyArray;
    }


    public static function crypt_fn($key, $string, $action = 'encrypt') {
        $res = '';
        if ($action !== 'encrypt') {
            $string = base64_decode($string);
        }
        for ($i = 0; $i < strlen($string); $i++) {
            $c = ord(substr($string, $i));
            if ($action == 'encrypt') {
                $c += ord(substr($key, (($i + 1) % strlen($key))));
                $res .= chr($c & 0xFF);
            } else {
                $c -= ord(substr($key, (($i + 1) % strlen($key))));
                $res .= chr(abs($c) & 0xFF);
            }
        }
        if ($action == 'encrypt') {
            $res = base64_encode($res);
        }
        return $res;
    }
    
    public static function apiResponseArray() {
        return array('AckNo', 'ResponseCode', 'ResponseMessage');
    }
    
    public static function apiRequestArray() {
        return array('TransactionRefNo', 'PartnerRefNo', 'ProductCode', 'Mobile', 'RemitterFlag', 'RemitterCode', 'AckNo', 'TxnIdentifierType', 'MemberIDCardNo');
    }
    
    public static function insertCardCrn($c) {
        if(($c != 0) && ($c != '')){
	    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $c = new Zend_Db_Expr("AES_ENCRYPT('".$c."','".$encryptionKey."')");
	}
	return $c;   
    }
    
    public static function walletType(){
	return array(
	    FLAG_NO => 'General Wallet',
	    FLAG_YES => 'Virtual Wallet',
	);
    }
    
    public static function generate_random_password($length = 8)
    {
        $alphabets = range('A','Z');
        $numbers = range('0','9');
        $additional_characters = array();
        $final_array = array_merge($alphabets,$numbers,$additional_characters);

        $password = '';

        while($length--)
        {
          $key = array_rand($final_array);
          $password .= $final_array[$key];
        }

        return $password;
    }
}
