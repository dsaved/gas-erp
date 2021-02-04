<?php
class Currency
{
	public static function currency_symbols(){
		return array(
            array('Leke', 'ALL', 'Lek'),
            array('Dollars', 'USD', '$'),
            array('Afghanis', 'AFN', '؋'),
            array('Pesos', 'ARS', '$'),
            array('Guilders', 'AWG', 'ƒ'),
            array('Dollars', 'AUD', '$'),
            array('New Manats', 'AZN', 'ман'),
            array('Dollars', 'BSD', '$'),
            array('Dollars', 'BBD', '$'),
            array('Rubles', 'BYR', 'p.'),
            array('Euro', 'EUR', '€'),
            array('Dollars', 'BZD', 'BZ$'),
            array('Dollars', 'BMD', '$'),
            array('Bolivianos', 'BOB', '$b'),
            array('Convertible Marka', 'BAM', 'KM'),
            array('Pula', 'BWP', 'P'),
            array('Leva', 'BGN', 'лв'),
            array('Reais', 'BRL', 'R$'),
            array('Pounds', 'GBP', '£'),
            array('Dollars', 'BND', '$'),
            array('Riels', 'KHR', '៛'),
            array('Dollars', 'CAD', '$'),
            array('Dollars', 'KYD', '$'),
            array('Pesos', 'CLP', '$'),
            array('Yuan Renminbi', 'CNY', '¥'),
            array('Pesos', 'COP', '$'),
            array('Colón', 'CRC', '₡'),
            array('Kuna', 'HRK', 'kn'),
            array('Pesos', 'CUP', '₱'),
            array('Koruny', 'CZK', 'Kč'),
            array('Kroner', 'DKK', 'kr'),
            array('Pesos', 'DOP ', 'RD$'),
            array('Dollars', 'XCD', '$'),
            array('Pounds', 'EGP', '£'),
            array('Colones', 'SVC', '$'),
            array('Pounds', 'FKP', '£'),
            array('Dollars', 'FJD', '$'),
            array('Cedis', 'GHS', '¢'),
            array('Pounds', 'GIP', '£'),
            array('Quetzales', 'GTQ', 'Q'),
            array('Pounds', 'GGP', '£'),
            array('Dollars', 'GYD', '$'),
            array('Lempiras', 'HNL', 'L'),
            array('Dollars', 'HKD', '$'),
            array('Forint', 'HUF', 'Ft'),
            array('Kronur', 'ISK', 'kr'),
            array('Rupees', 'INR', 'Rp'),
            array('Rupiahs', 'IDR', 'Rp'),
            array('Rials', 'IRR', '﷼'),
            array('Pounds', 'IMP', '£'),
            array('New Shekels', 'ILS', '₪'),
            array('Dollars', 'JMD', 'J$'),
            array('Yen', 'JPY', '¥'),
            array('Pounds', 'JEP', '£'),
            array('Tenge', 'KZT', 'лв'),
            array('Won', 'KPW', '₩'),
            array('Won', 'KRW', '₩'),
            array('Soms', 'KGS', 'лв'),
            array('Kips', 'LAK', '₭'),
            array('Lati', 'LVL', 'Ls'),
            array('Pounds', 'LBP', '£'),
            array('Dollars', 'LRD', '$'),
            array('Switzerland Francs', 'CHF', 'CHF'),
            array('Litai', 'LTL', 'Lt'),
            array('Denars', 'MKD', 'ден'),
            array('Ringgits', 'MYR', 'RM'),
            array('Rupees', 'MUR', '₨'),
            array('Pesos', 'MXN', '$'),
            array('Tugriks', 'MNT', '₮'),
            array('Meticais', 'MZN', 'MT'),
            array('Dollars', 'NAD', '$'),
            array('Rupees', 'NPR', '₨'),
            array('Guilders', 'ANG', 'ƒ'),
            array('Dollars', 'NZD', '$'),
            array('Cordobas', 'NIO', 'C$'),
            array('Nairas', 'NGN', '₦'),
            array('Krone', 'NOK', 'kr'),
            array('Rials', 'OMR', '﷼'),
            array('Rupees', 'PKR', '₨'),
            array('Balboa', 'PAB', 'B/.'),
            array('Guarani', 'PYG', 'Gs'),
            array('Nuevos Soles', 'PEN', 'S/.'),
            array('Pesos', 'PHP', 'Php'),
            array('Zlotych', 'PLN', 'zł'),
            array('Rials', 'QAR', '﷼'),
            array('New Lei', 'RON', 'lei'),
            array('Rubles', 'RUB', 'руб'),
            array('Pounds', 'SHP', '£'),
            array('Riyals', 'SAR', '﷼'),
            array('Dinars', 'RSD', 'Дин.'),
            array('Rupees', 'SCR', '₨'),
            array('Dollars', 'SGD', '$'),
            array('Dollars', 'SBD', '$'),
            array('Shillings', 'SOS', 'S'),
            array('Rand', 'ZAR', 'R'),
            array('Rupees', 'LKR', '₨'),
            array('Kronor', 'SEK', 'kr'),
            array('Dollars', 'SRD', '$'),
            array('Pounds', 'SYP', '£'),
            array('New Dollars', 'TWD', 'NT$'),
            array('Baht', 'THB', '฿'),
            array('Dollars', 'TTD', 'TT$'),
            array('Lira', 'TRY', 'TL'),
            array('Liras', 'TRL', '£'),
            array('Dollars', 'TVD', '$'),
            array('Hryvnia', 'UAH', '₴'),
            array('Pesos', 'UYU', '$U'),
            array('Sums', 'UZS', 'лв'),
            array('Bolivares Fuertes', 'VEF', 'Bs'),
            array('Dong', 'VND', '₫'),
            array('Rials', 'YER', '﷼'),
            array('Zimbabwe Dollars', 'ZWD', 'Z$')
        );
    }

	public static function render($defaultCurrency = "", $id = "", $name = "", $classes = ""){
	    $output = "<select id='".$id."' name='".$name."' class='".$classes."'>";
		foreach(self::currency_symbols() as $currency){
			$currencyName = ucwords(strtolower($currency[0])); // Making it look good
			$output .= "<option value='".$currency[2]."' ".(($currency[2]===strtoupper($defaultCurrency))?"selected":"").">".$currencyName." - ".$currency[1]." (".$currency[2].")</option>";
		}
		$output .= "</select>";
		render($output);
    }

	public static function select($id = "", $name = "", $classes = ""){
	    $output = "<select id='".$id."' name='".$name."' class='".$classes."'>";
		foreach(self::currency_symbols() as $currency){
            $currencyName = ucwords(strtolower($currency[0])); // Making it look good
            $value = '{"code":"'.$currency[1].'","symbol":"'.$currency[2].'"}';
            $defaultCurrency = '{"code":"'.SYS_CURRENCY_CODE.'","symbol":"'.SYS_CURRENCY.'"}';
			$output .= "<option value='".$value."' ".((strtolower($value)===strtolower($defaultCurrency))?"selected":"").">".$currencyName." - ".$currency[1]." (".$currency[2].")</option>";
		}
		$output .= "</select>";
		render($output);
    }
    
	public static function getCurrency($code = ""){
	    $output = null;
		foreach(self::currency_symbols() as $currency){
			if ($currency[2]===$code) {
				$output = ucwords(strtolower($currency[0]));
			}
		}
		return $output;
    }
    
	public static function getCurrencyCode($code = ""){
	    $output = null;
		foreach(self::currency_symbols() as $code => $country){
			if ($currency[2]===$code) {
				$output = ucwords(strtolower($country[1]));
			}
		}
		return $output;
	}

    // $currency_input = 2;
    // $currency_from = "USD";
    // $currency_to = "INR";
    // $currency = currencyConverter($currency_from,$currency_to,$currency_input);
    // echo $currency_input.' '.$currency_from.' = '.$currency.' '.$currency_to;
   
    public static function convert($currency_from,$currency_to,$currency_input){
        $currencyfronto = $currency_from."_".$currency_to;
        $yql_base_url = "http://free.currencyconverterapi.com/api/v5/convert?q=".$currencyfronto."&compact=y";

        $yqlexec = http_get($yql_base_url);
        $yql_json =  json_decode($yqlexec,true);
        return (float)$currency_input * $yql_json[$currencyfronto]['val'];
    }

    // $currency_from = "USD";
    // $currency_to = "INR";
    // $rate = currencyConverter($currency_from,$currency_to);
    // echo $rate;
    public static function exchangerate($currency_from,$currency_to){
        $currencyfronto = $currency_from."_".$currency_to;
        $yql_base_url = "http://free.currencyconverterapi.com/api/v5/convert?q=".$currencyfronto."&compact=y";

        $yqlexec = http_get($yql_base_url);
        $yql_json =  json_decode($yqlexec,true);
    
        return $yql_json[$currencyfronto]['val'];
    }
}