<?php 

function get_IP() {
	return $_SERVER['REMOTE_ADDR'];
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0;
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
	
function get_server_var($id) {
        return isset($_SERVER[$id]) ? $_SERVER[$id] : '';
    }
 
function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }
	
function formatFrenchPhoneNumber($phoneNumber, $international = false)
	{
	//Supprimer tous les caractères qui ne sont pas des chiffres
	$phoneNumber = preg_replace('/[^0-9]+/', '', $phoneNumber);
	//Garder les 9 derniers chiffres
	$phoneNumber = substr($phoneNumber, -9);
	//On ajoute +33 si la variable $international vaut true et 0 dans tous les autres cas
	$motif = $international ? '+33 (\1).\2.\3.\4.\5' : '0\1.\2.\3.\4.\5';
	$phoneNumber = preg_replace('/(\d{1})(\d{2})(\d{2})(\d{2})(\d{2})/', $motif, $phoneNumber);

	return $phoneNumber;
	} 

function genererPwd($taille = 10)
	{
		$tab =array('a','z','e','r','t','y','u','i','o','p','q','s','d','f','g','h','j','k','l','m','w','x','c','v','b','n','1','2','3','4','5','6','7','8','9','*','+','$','!','&');
		shuffle($tab);
		return substr(implode('',$tab),0,$taille);
	}

function noAccentFeed($text, $EncIn = 'CP1252')
	{
		return iconv($EncIn, 'ASCII//TRANSLIT//IGNORE', $text);
	}

function removeAccent($string)
	{
		$string = utf8_decode($string);
		$string = strtr($string,    'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
									'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
		$string = utf8_encode($string);                 
		return $string;
	}

function UTCdatestringToTime($utcdatestring)
{
    $tz = date_default_timezone_get();
    date_default_timezone_set('UTC');

    $result = strtotime($utcdatestring);

    date_default_timezone_set($tz);
    return $result;
}
	
function cryptText($texte,$key)
	{

		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, "", MCRYPT_MODE_ECB, "");
		$iv =  str_pad("",32,"123");
		mcrypt_generic_init($td, $key, $iv);
		
		$temp = mcrypt_generic($td, $texte);
		// mcrypt_generic_end ($td);
		
		return $temp;
	}

function decryptText($texte,$key)
	{
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, "", MCRYPT_MODE_ECB, "");
		$iv =  str_pad("",32,"123");
		mcrypt_generic_init($td, $key, $iv);
		$temp = mdecrypt_generic($td, $texte);
		// mcrypt_generic_end ($td);
		return trim($temp);
	}

function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

function urlsafe_b64decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

function decrypt_request($requestcrypt,$key)
{
	$requesttemp = urlsafe_b64decode($requestcrypt);
	$requestdecrypt = decryptText($requesttemp,$key);
	// $requestdecrypt = $requestuid
	return $requestdecrypt;
}

function crypt_request($requestuid,$key)
{
	// $request = $requestuid
	return urlsafe_b64encode(cryptText($requestuid,$key));
}
	
function get_xml($url)
    {	
		libxml_use_internal_errors(true);
		$xml=@simplexml_load_file($url,"SimpleXMLElement",LIBXML_NOCDATA);

		if($xml ===  FALSE){
			$xml= false;
		}
		elseif ($xml->web_service_result->errorsExist=="true")
			{
				$xml= false;
			}
		
		return $xml;
	
    }

function get_xml_from_string($str_xml)
    {	
		libxml_use_internal_errors(true);
		$xml=@simplexml_load_string($str_xml,"SimpleXMLElement",LIBXML_NOCDATA);
		// print_r($xml->errorsExist);
		
		if($xml ===  FALSE){
			$xml = false;
		}
		elseif ($xml->errorsExist=="true")
			{
				$xml = false;
				// die("là");
			}
		
		return $xml;
    }
	
function get_reservation_alma_xml_from_string($str_xml)
    {	
		$erreur = "";
		$message = "";
		$debug = "";
		$requestid = "";
		$ajustdebut = 0;
		$ajustfin = 0;
		$debut = "";
		$fin = "";
		
		libxml_use_internal_errors(true);
		$xml_create_result=@simplexml_load_string($str_xml,"SimpleXMLElement",LIBXML_NOCDATA);
		
		$error = (string) $xml_create_result->errorsExist;
		
		if(strcmp($error, "true") == 0)
		{
			$erreur .= "<div style='margin-left=5px;'>";
			$erreur .= (string) $xml_create_result->errorList->error->errorMessage."<br/>";
			// $erreur .= "- Code erreur : ".(string) $xml_create_result->errorList->error->errorCode."<br/>";
			// $erreur .= "- Code de suivi : ".(string) $xml_create_result->errorList->error->trackingId;	
			$erreur .= "</div>";	
		}
		else
		{
			$requestid = (string) $xml_create_result->request_id;
			
			//Grab the Start Stop Times so we can see if they have been adjusted
			$booking_start = (string) $xml_create_result->booking_start_date;
			$adjusted_start = (string) $xml_create_result->adjusted_booking_start_date;
			$booking_end = (string) $xml_create_result->booking_end_date;
			$adjusted_end = (string) $xml_create_result->adjusted_booking_end_date;

			// infos supplémentaires
			$message .=  "Réservation N° " . $requestid . " réussie.";
			$message .=  "<BR>Titre :  " . (string) $xml_create_result->title;
			$message .=  "<BR>Descritpion :  " . (string) $xml_create_result->description;
			$message .=  "<BR>Statut :" . (string) $xml_create_result->request_status;
			
			if(strcmp($booking_start, $adjusted_start) !== 0)
				{
					// on ne devrait jamais passer ici
					$ajustdebut = 1;
				}
			
			$debut = date('Y-m-d H:i',strtotime((string) $xml_create_result->adjusted_booking_start_date. ' UTC'));
			
			if(strcmp($booking_end, $adjusted_end) !== 0)
				{
					// on ne devrait jamais passer ici
					$ajustfin = 1;
				}
			
			$fin = date('Y-m-d H:i',strtotime((string) $xml_create_result->adjusted_booking_end_date. ' UTC'));
		}
		
		$array['requestid'] = $requestid;
		
		$array['ajustdebut'] = $ajustdebut;
		$array['debut'] = $debut;
		
		$array['ajustfin'] = $ajustfin;
		$array['fin'] = $fin;
		
		$array['message'] = $message;
		
		$array['erreur'] = $erreur;
		
		$array['debug'] = $debug;
		
		return $array;

    }
	
function get_name($xml)
    {	
		$ligne = "";
		
		if (trim($xml->first_name) != "" && trim($xml->last_name) != "")
			$ligne = $xml->first_name." ".$xml->last_name;
		elseif (trim($xml->full_name) != "" && strlen(trim($xml->full_name)) > 1)
			$ligne = $xml->full_name;
		else
			$ligne = "";
		
		return $ligne;
	
    }

function get_mail_preferred($xml)
    {	
		$ligne = "";
		
		foreach ($xml->contact_info->emails->email as $emailinfo):
			
			if ($emailinfo['preferred']=="true")
				$ligne = $emailinfo->email_address;
			
		endforeach;
		
		return $ligne;
    }
	
function get_first_mail($xml,$mask=true)
    {	
		$ligne = "";
		
		if ($xml->contact_info->emails->email->email_address != "")
		{	
			$ligne = $xml->contact_info->emails->email->email_address;
			
			return $ligne;
			
		}	
		
    }


?>