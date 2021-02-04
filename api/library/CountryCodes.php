<?php
/*
* Country Array to HTML Select List
* Developed By: Jose Philip Raja - www.josephilipraja.com
* About Author: Creative Director of CreaveLabs IT Solutions - www.creavelabs.com
*
* Usage:
*   countrySelector(); // Basic
*   countrySelector("country short name"); // Set default Country with its country short name
*   countrySelector("country short name", "my-country", "my-country", "form-control"); // With full Options
*
*/
class CountryCodes
{
	
	public static function countryArray(){
		return array(
			'AD'=>array('name'=>'ANDORRA','code'=>'376', "regExp"=>"", "length"=> 0),
			'AE'=>array('name'=>'UNITED ARAB EMIRATES','code'=>'971', "length"=> 0),
			'AF'=>array('name'=>'AFGHANISTAN','code'=>'93', "length"=> 0),
			'AG'=>array('name'=>'ANTIGUA AND BARBUDA','code'=>'1268', "length"=> 0),
			'AI'=>array('name'=>'ANGUILLA','code'=>'1264', "length"=> 0),
			'AL'=>array('name'=>'ALBANIA','code'=>'355', "length"=> 0),
			'AM'=>array('name'=>'ARMENIA','code'=>'374', "length"=> 0),
			'AN'=>array('name'=>'NETHERLANDS ANTILLES','code'=>'599', "length"=> 0),
			'AO'=>array('name'=>'ANGOLA','code'=>'244', "length"=> 0),
			'AQ'=>array('name'=>'ANTARCTICA','code'=>'672', "length"=> 0),
			'AR'=>array('name'=>'ARGENTINA','code'=>'54', "length"=> 0),
			'AS'=>array('name'=>'AMERICAN SAMOA','code'=>'1684', "length"=> 0),
			'AT'=>array('name'=>'AUSTRIA','code'=>'43', "length"=> 0),
			'AU'=>array('name'=>'AUSTRALIA','code'=>'61', "length"=> 0),
			'AW'=>array('name'=>'ARUBA','code'=>'297', "length"=> 0),
			'AZ'=>array('name'=>'AZERBAIJAN','code'=>'994', "length"=> 0),
			'BA'=>array('name'=>'BOSNIA AND HERZEGOVINA','code'=>'387', "length"=> 0),
			'BB'=>array('name'=>'BARBADOS','code'=>'1246', "length"=> 0),
			'BD'=>array('name'=>'BANGLADESH','code'=>'880', "length"=> 0),
			'BE'=>array('name'=>'BELGIUM','code'=>'32', "length"=> 0),
			'BF'=>array('name'=>'BURKINA FASO','code'=>'226', "length"=> 0),
			'BG'=>array('name'=>'BULGARIA','code'=>'359', "length"=> 0),
			'BH'=>array('name'=>'BAHRAIN','code'=>'973', "length"=> 0),
			'BI'=>array('name'=>'BURUNDI','code'=>'257', "length"=> 0),
			'BJ'=>array('name'=>'BENIN','code'=>'229', "length"=> 0),
			'BL'=>array('name'=>'SAINT BARTHELEMY','code'=>'590', "length"=> 0),
			'BM'=>array('name'=>'BERMUDA','code'=>'1441', "length"=> 0),
			'BN'=>array('name'=>'BRUNEI DARUSSALAM','code'=>'673', "length"=> 0),
			'BO'=>array('name'=>'BOLIVIA','code'=>'591', "length"=> 0),
			'BR'=>array('name'=>'BRAZIL','code'=>'55', "length"=> 0),
			'BS'=>array('name'=>'BAHAMAS','code'=>'1242', "length"=> 0),
			'BT'=>array('name'=>'BHUTAN','code'=>'975', "length"=> 0),
			'BW'=>array('name'=>'BOTSWANA','code'=>'267', "length"=> 0),
			'BY'=>array('name'=>'BELARUS','code'=>'375', "length"=> 0),
			'BZ'=>array('name'=>'BELIZE','code'=>'501', "length"=> 0),
			'CA'=>array('name'=>'CANADA','code'=>'1', "length"=> 0),
			'CC'=>array('name'=>'COCOS (KEELING) ISLANDS','code'=>'61', "length"=> 0),
			'CD'=>array('name'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE','code'=>'243', "length"=> 0),
			'CF'=>array('name'=>'CENTRAL AFRICAN REPUBLIC','code'=>'236', "length"=> 0),
			'CG'=>array('name'=>'CONGO','code'=>'242', "length"=> 0),
			'CH'=>array('name'=>'SWITZERLAND','code'=>'41', "length"=> 0),
			'CI'=>array('name'=>'COTE D IVOIRE','code'=>'225', "length"=> 0),
			'CK'=>array('name'=>'COOK ISLANDS','code'=>'682', "length"=> 0),
			'CL'=>array('name'=>'CHILE','code'=>'56', "length"=> 0),
			'CM'=>array('name'=>'CAMEROON','code'=>'237', "length"=> 0),
			'CN'=>array('name'=>'CHINA','code'=>'86', "length"=> 0),
			'CO'=>array('name'=>'COLOMBIA','code'=>'57', "length"=> 0),
			'CR'=>array('name'=>'COSTA RICA','code'=>'506', "length"=> 0),
			'CU'=>array('name'=>'CUBA','code'=>'53', "length"=> 0),
			'CV'=>array('name'=>'CAPE VERDE','code'=>'238', "length"=> 0),
			'CX'=>array('name'=>'CHRISTMAS ISLAND','code'=>'61', "length"=> 0),
			'CY'=>array('name'=>'CYPRUS','code'=>'357', "length"=> 0),
			'CZ'=>array('name'=>'CZECH REPUBLIC','code'=>'420', "length"=> 0),
			'DE'=>array('name'=>'GERMANY','code'=>'49', "length"=> 0),
			'DJ'=>array('name'=>'DJIBOUTI','code'=>'253', "length"=> 0),
			'DK'=>array('name'=>'DENMARK','code'=>'45', "length"=> 0),
			'DM'=>array('name'=>'DOMINICA','code'=>'1767', "length"=> 0),
			'DO'=>array('name'=>'DOMINICAN REPUBLIC','code'=>'1809', "length"=> 0),
			'DZ'=>array('name'=>'ALGERIA','code'=>'213', "length"=> 0),
			'EC'=>array('name'=>'ECUADOR','code'=>'593', "length"=> 0),
			'EE'=>array('name'=>'ESTONIA','code'=>'372', "length"=> 0),
			'EG'=>array('name'=>'EGYPT','code'=>'20', "length"=> 0),
			'ER'=>array('name'=>'ERITREA','code'=>'291', "length"=> 0),
			'ES'=>array('name'=>'SPAIN','code'=>'34', "length"=> 0),
			'ET'=>array('name'=>'ETHIOPIA','code'=>'251', "length"=> 0),
			'FI'=>array('name'=>'FINLAND','code'=>'358', "length"=> 0),
			'FJ'=>array('name'=>'FIJI','code'=>'679', "length"=> 0),
			'FK'=>array('name'=>'FALKLAND ISLANDS (MALVINAS)','code'=>'500', "length"=> 0),
			'FM'=>array('name'=>'MICRONESIA, FEDERATED STATES OF','code'=>'691', "length"=> 0),
			'FO'=>array('name'=>'FAROE ISLANDS','code'=>'298', "length"=> 0),
			'FR'=>array('name'=>'FRANCE','code'=>'33', "length"=> 0),
			'GA'=>array('name'=>'GABON','code'=>'241', "length"=> 0),
			'GB'=>array('name'=>'UNITED KINGDOM','code'=>'44', "length"=> 0),
			'GD'=>array('name'=>'GRENADA','code'=>'1473', "length"=> 0),
			'GE'=>array('name'=>'GEORGIA','code'=>'995', "length"=> 0),
			'GH'=>array('name'=>'GHANA','code'=>'233', "length"=> 10),
			'GI'=>array('name'=>'GIBRALTAR','code'=>'350', "length"=> 0),
			'GL'=>array('name'=>'GREENLAND','code'=>'299', "length"=> 0),
			'GM'=>array('name'=>'GAMBIA','code'=>'220', "length"=> 0),
			'GN'=>array('name'=>'GUINEA','code'=>'224', "length"=> 0),
			'GQ'=>array('name'=>'EQUATORIAL GUINEA','code'=>'240', "length"=> 0),
			'GR'=>array('name'=>'GREECE','code'=>'30', "length"=> 0),
			'GT'=>array('name'=>'GUATEMALA','code'=>'502', "length"=> 0),
			'GU'=>array('name'=>'GUAM','code'=>'1671', "length"=> 0),
			'GW'=>array('name'=>'GUINEA-BISSAU','code'=>'245', "length"=> 0),
			'GY'=>array('name'=>'GUYANA','code'=>'592', "length"=> 0),
			'HK'=>array('name'=>'HONG KONG','code'=>'852', "length"=> 0),
			'HN'=>array('name'=>'HONDURAS','code'=>'504', "length"=> 0),
			'HR'=>array('name'=>'CROATIA','code'=>'385', "length"=> 0),
			'HT'=>array('name'=>'HAITI','code'=>'509', "length"=> 0),
			'HU'=>array('name'=>'HUNGARY','code'=>'36', "length"=> 0),
			'ID'=>array('name'=>'INDONESIA','code'=>'62', "length"=> 0),
			'IE'=>array('name'=>'IRELAND','code'=>'353', "length"=> 0),
			'IL'=>array('name'=>'ISRAEL','code'=>'972', "length"=> 0),
			'IM'=>array('name'=>'ISLE OF MAN','code'=>'44', "length"=> 0),
			'IN'=>array('name'=>'INDIA','code'=>'91', "length"=> 0),
			'IQ'=>array('name'=>'IRAQ','code'=>'964', "length"=> 0),
			'IR'=>array('name'=>'IRAN, ISLAMIC REPUBLIC OF','code'=>'98', "length"=> 0),
			'IS'=>array('name'=>'ICELAND','code'=>'354', "length"=> 0),
			'IT'=>array('name'=>'ITALY','code'=>'39', "length"=> 0),
			'JM'=>array('name'=>'JAMAICA','code'=>'1876', "length"=> 0),
			'JO'=>array('name'=>'JORDAN','code'=>'962', "length"=> 0),
			'JP'=>array('name'=>'JAPAN','code'=>'81', "length"=> 0),
			'KE'=>array('name'=>'KENYA','code'=>'254', "length"=> 0),
			'KG'=>array('name'=>'KYRGYZSTAN','code'=>'996', "length"=> 0),
			'KH'=>array('name'=>'CAMBODIA','code'=>'855', "length"=> 0),
			'KI'=>array('name'=>'KIRIBATI','code'=>'686', "length"=> 0),
			'KM'=>array('name'=>'COMOROS','code'=>'269', "length"=> 0),
			'KN'=>array('name'=>'SAINT KITTS AND NEVIS','code'=>'1869', "length"=> 0),
			'KP'=>array('name'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF','code'=>'850', "length"=> 0),
			'KR'=>array('name'=>'KOREA REPUBLIC OF','code'=>'82', "length"=> 0),
			'KW'=>array('name'=>'KUWAIT','code'=>'965', "length"=> 0),
			'KY'=>array('name'=>'CAYMAN ISLANDS','code'=>'1345', "length"=> 0),
			'KZ'=>array('name'=>'KAZAKSTAN','code'=>'7', "length"=> 0),
			'LA'=>array('name'=>'LAO PEOPLES DEMOCRATIC REPUBLIC','code'=>'856', "length"=> 0),
			'LB'=>array('name'=>'LEBANON','code'=>'961', "length"=> 0),
			'LC'=>array('name'=>'SAINT LUCIA','code'=>'1758', "length"=> 0),
			'LI'=>array('name'=>'LIECHTENSTEIN','code'=>'423', "length"=> 0),
			'LK'=>array('name'=>'SRI LANKA','code'=>'94', "length"=> 0),
			'LR'=>array('name'=>'LIBERIA','code'=>'231', "length"=> 0),
			'LS'=>array('name'=>'LESOTHO','code'=>'266', "length"=> 0),
			'LT'=>array('name'=>'LITHUANIA','code'=>'370', "length"=> 0),
			'LU'=>array('name'=>'LUXEMBOURG','code'=>'352', "length"=> 0),
			'LV'=>array('name'=>'LATVIA','code'=>'371', "length"=> 0),
			'LY'=>array('name'=>'LIBYAN ARAB JAMAHIRIYA','code'=>'218', "length"=> 0),
			'MA'=>array('name'=>'MOROCCO','code'=>'212', "length"=> 0),
			'MC'=>array('name'=>'MONACO','code'=>'377', "length"=> 0),
			'MD'=>array('name'=>'MOLDOVA, REPUBLIC OF','code'=>'373', "length"=> 0),
			'ME'=>array('name'=>'MONTENEGRO','code'=>'382', "length"=> 0),
			'MF'=>array('name'=>'SAINT MARTIN','code'=>'1599', "length"=> 0),
			'MG'=>array('name'=>'MADAGASCAR','code'=>'261', "length"=> 0),
			'MH'=>array('name'=>'MARSHALL ISLANDS','code'=>'692', "length"=> 0),
			'MK'=>array('name'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','code'=>'389', "length"=> 0),
			'ML'=>array('name'=>'MALI','code'=>'223', "length"=> 0),
			'MM'=>array('name'=>'MYANMAR','code'=>'95', "length"=> 0),
			'MN'=>array('name'=>'MONGOLIA','code'=>'976', "length"=> 0),
			'MO'=>array('name'=>'MACAU','code'=>'853', "length"=> 0),
			'MP'=>array('name'=>'NORTHERN MARIANA ISLANDS','code'=>'1670', "length"=> 0),
			'MR'=>array('name'=>'MAURITANIA','code'=>'222', "length"=> 0),
			'MS'=>array('name'=>'MONTSERRAT','code'=>'1664', "length"=> 0),
			'MT'=>array('name'=>'MALTA','code'=>'356', "length"=> 0),
			'MU'=>array('name'=>'MAURITIUS','code'=>'230', "length"=> 0),
			'MV'=>array('name'=>'MALDIVES','code'=>'960', "length"=> 0),
			'MW'=>array('name'=>'MALAWI','code'=>'265', "length"=> 0),
			'MX'=>array('name'=>'MEXICO','code'=>'52', "length"=> 0),
			'MY'=>array('name'=>'MALAYSIA','code'=>'60', "length"=> 0),
			'MZ'=>array('name'=>'MOZAMBIQUE','code'=>'258', "length"=> 0),
			'NA'=>array('name'=>'NAMIBIA','code'=>'264', "length"=> 0),
			'NC'=>array('name'=>'NEW CALEDONIA','code'=>'687', "length"=> 0),
			'NE'=>array('name'=>'NIGER','code'=>'227', "length"=> 0),
			'NG'=>array('name'=>'NIGERIA','code'=>'234', "length"=> 11),
			'NI'=>array('name'=>'NICARAGUA','code'=>'505', "length"=> 0),
			'NL'=>array('name'=>'NETHERLANDS','code'=>'31', "length"=> 0),
			'NO'=>array('name'=>'NORWAY','code'=>'47', "length"=> 0),
			'NP'=>array('name'=>'NEPAL','code'=>'977', "length"=> 0),
			'NR'=>array('name'=>'NAURU','code'=>'674', "length"=> 0),
			'NU'=>array('name'=>'NIUE','code'=>'683', "length"=> 0),
			'NZ'=>array('name'=>'NEW ZEALAND','code'=>'64', "length"=> 0),
			'OM'=>array('name'=>'OMAN','code'=>'968', "length"=> 0),
			'PA'=>array('name'=>'PANAMA','code'=>'507', "length"=> 0),
			'PE'=>array('name'=>'PERU','code'=>'51', "length"=> 0),
			'PF'=>array('name'=>'FRENCH POLYNESIA','code'=>'689', "length"=> 0),
			'PG'=>array('name'=>'PAPUA NEW GUINEA','code'=>'675', "length"=> 0),
			'PH'=>array('name'=>'PHILIPPINES','code'=>'63', "length"=> 0),
			'PK'=>array('name'=>'PAKISTAN','code'=>'92', "length"=> 0),
			'PL'=>array('name'=>'POLAND','code'=>'48', "length"=> 0),
			'PM'=>array('name'=>'SAINT PIERRE AND MIQUELON','code'=>'508', "length"=> 0),
			'PN'=>array('name'=>'PITCAIRN','code'=>'870', "length"=> 0),
			'PR'=>array('name'=>'PUERTO RICO','code'=>'1', "length"=> 0),
			'PT'=>array('name'=>'PORTUGAL','code'=>'351', "length"=> 0),
			'PW'=>array('name'=>'PALAU','code'=>'680', "length"=> 0),
			'PY'=>array('name'=>'PARAGUAY','code'=>'595', "length"=> 0),
			'QA'=>array('name'=>'QATAR','code'=>'974', "length"=> 0),
			'RO'=>array('name'=>'ROMANIA','code'=>'40', "length"=> 0),
			'RS'=>array('name'=>'SERBIA','code'=>'381', "length"=> 0),
			'RU'=>array('name'=>'RUSSIAN FEDERATION','code'=>'7', "length"=> 0),
			'RW'=>array('name'=>'RWANDA','code'=>'250', "length"=> 0),
			'SA'=>array('name'=>'SAUDI ARABIA','code'=>'966', "length"=> 0),
			'SB'=>array('name'=>'SOLOMON ISLANDS','code'=>'677', "length"=> 0),
			'SC'=>array('name'=>'SEYCHELLES','code'=>'248', "length"=> 0),
			'SD'=>array('name'=>'SUDAN','code'=>'249', "length"=> 0),
			'SE'=>array('name'=>'SWEDEN','code'=>'46', "length"=> 0),
			'SG'=>array('name'=>'SINGAPORE','code'=>'65', "length"=> 0),
			'SH'=>array('name'=>'SAINT HELENA','code'=>'290', "length"=> 0),
			'SI'=>array('name'=>'SLOVENIA','code'=>'386', "length"=> 0),
			'SK'=>array('name'=>'SLOVAKIA','code'=>'421', "length"=> 0),
			'SL'=>array('name'=>'SIERRA LEONE','code'=>'232', "length"=> 0),
			'SM'=>array('name'=>'SAN MARINO','code'=>'378', "length"=> 0),
			'SN'=>array('name'=>'SENEGAL','code'=>'221', "length"=> 0),
			'SO'=>array('name'=>'SOMALIA','code'=>'252', "length"=> 0),
			'SR'=>array('name'=>'SURINAME','code'=>'597', "length"=> 0),
			'ST'=>array('name'=>'SAO TOME AND PRINCIPE','code'=>'239', "length"=> 0),
			'SV'=>array('name'=>'EL SALVADOR','code'=>'503', "length"=> 0),
			'SY'=>array('name'=>'SYRIAN ARAB REPUBLIC','code'=>'963', "length"=> 0),
			'SZ'=>array('name'=>'SWAZILAND','code'=>'268', "length"=> 0),
			'TC'=>array('name'=>'TURKS AND CAICOS ISLANDS','code'=>'1649', "length"=> 0),
			'TD'=>array('name'=>'CHAD','code'=>'235', "length"=> 0),
			'TG'=>array('name'=>'TOGO','code'=>'228', "length"=> 0),
			'TH'=>array('name'=>'THAILAND','code'=>'66', "length"=> 0),
			'TJ'=>array('name'=>'TAJIKISTAN','code'=>'992', "length"=> 0),
			'TK'=>array('name'=>'TOKELAU','code'=>'690', "length"=> 0),
			'TL'=>array('name'=>'TIMOR-LESTE','code'=>'670', "length"=> 0),
			'TM'=>array('name'=>'TURKMENISTAN','code'=>'993', "length"=> 0),
			'TN'=>array('name'=>'TUNISIA','code'=>'216', "length"=> 0),
			'TO'=>array('name'=>'TONGA','code'=>'676', "length"=> 0),
			'TR'=>array('name'=>'TURKEY','code'=>'90', "length"=> 0),
			'TT'=>array('name'=>'TRINIDAD AND TOBAGO','code'=>'1868', "length"=> 0),
			'TV'=>array('name'=>'TUVALU','code'=>'688', "length"=> 0),
			'TW'=>array('name'=>'TAIWAN, PROVINCE OF CHINA','code'=>'886', "length"=> 0),
			'TZ'=>array('name'=>'TANZANIA, UNITED REPUBLIC OF','code'=>'255', "length"=> 0),
			'UA'=>array('name'=>'UKRAINE','code'=>'380', "length"=> 0),
			'UG'=>array('name'=>'UGANDA','code'=>'256', "length"=> 0),
			'US'=>array('name'=>'UNITED STATES','code'=>'1', "length"=> 0),
			'UY'=>array('name'=>'URUGUAY','code'=>'598', "length"=> 0),
			'UZ'=>array('name'=>'UZBEKISTAN','code'=>'998', "length"=> 0),
			'VA'=>array('name'=>'HOLY SEE (VATICAN CITY STATE)','code'=>'39', "length"=> 0),
			'VC'=>array('name'=>'SAINT VINCENT AND THE GRENADINES','code'=>'1784', "length"=> 0),
			'VE'=>array('name'=>'VENEZUELA','code'=>'58', "length"=> 0),
			'VG'=>array('name'=>'VIRGIN ISLANDS, BRITISH','code'=>'1284', "length"=> 0),
			'VI'=>array('name'=>'VIRGIN ISLANDS, U.S.','code'=>'1340', "length"=> 0),
			'VN'=>array('name'=>'VIET NAM','code'=>'84', "length"=> 0),
			'VU'=>array('name'=>'VANUATU','code'=>'678', "length"=> 0),
			'WF'=>array('name'=>'WALLIS AND FUTUNA','code'=>'681', "length"=> 0),
			'WS'=>array('name'=>'SAMOA','code'=>'685', "length"=> 0),
			'XK'=>array('name'=>'KOSOVO','code'=>'381', "length"=> 0),
			'YE'=>array('name'=>'YEMEN','code'=>'967', "length"=> 0),
			'YT'=>array('name'=>'MAYOTTE','code'=>'262', "length"=> 0),
			'ZA'=>array('name'=>'SOUTH AFRICA','code'=>'27', "length"=> 0),
			'ZM'=>array('name'=>'ZAMBIA','code'=>'260', "length"=> 0),
			'ZW'=>array('name'=>'ZIMBABWE','code'=>'263', "length"=> 0),
		);
	}

	public static function support_country(){
		return array(
			'GH'=>array('name'=>'GHANA','code'=>'233', "length"=> 10),
			'NG'=>array('name'=>'NIGERIA','code'=>'234', "length"=> 11),
		);
	}

	public static function getCountryShortName($countryName = ""){
	    $output = null;
		foreach(self::countryArray() as $code => $country){
			if (strtolower($countryName)===strtolower($country["name"])) {
				$output = strtoupper($code);
			}
		}
		return $output;
	}

	public static function getCountry($shortname = ""){
	    $output = null;
		foreach(self::countryArray() as $code => $country){
			if ($code===$shortname) {
				$output = ucwords(strtolower($country["name"]));
			}
		}
		return $output;
	}

	public static function getCountryCode($shortname = ""){
	    $output = null;
		foreach(self::countryArray() as $code => $country){
			if ($code===$shortname) {
				$output = ucwords(strtolower($country["code"]));
			}
		}
		return $output;
	}


	public static function validateNumber($number,$shortname){
	    $output = false;
		foreach(self::countryArray() as $code => $country){
			if ($code===$shortname) {
				$length = $country["length"];
				if(strlen($number) === $length){
					$output = true;
				}
			}
		}
		return $output;
	}

	public static function formatNumber($number,$shortname){
	    $output = null;
	    $num = $number;
		foreach(self::countryArray() as $code => $country){
			if ($code===$shortname) {
				$output = $country["code"];
			}
		}

		if (preg_match('~^0\d+$~', $number)) {
			$num = preg_replace('/^0/',$output,$number);
		}else{
			if (strpos($number, $output)===0) {
				$num = $number;
			} else {
				$num = $output.$number;
			}
		}
		return $num;
	}
}