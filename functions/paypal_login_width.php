<?php
define('KEY', 'ATXrxTX5F0GTiX8qPgmM6n9X92Svnms-JRgvKIAXPvtm9KcMcEtbYINg3iGi0_Clg7hP9m18pWG_cIqH');
define('SECRET', 'ECgFfOD475DVTD34ykawnFf6UEFJkURvgN2CM5wMYdAhBAutJ6c3n4aTDTp2o1KKkE5jiwwYQ7Y1_Aor');

//define('CALLBACK_URL', 'http://www.welcart.com/wc-settlement/paypal_guide/?wcact=liwpp');
define('AUTHORIZATION_ENDPOINT', 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize');
define('ACCESS_TOKEN_ENDPOINT', 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/tokenservice');
define('PROFILE_ENDPOINT', 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/userinfo');

/***************************************************************************
 * Function: Run CURL
 * Description: Executes a CURL request
 * Parameters: url (string) - URL to make request to
 *             method (string) - HTTP transfer method
 *             headers - HTTP transfer headers
 *             postvals - post values
 **************************************************************************/
function run_curl($url, $method = 'GET', $postvals = null){
    $ch = curl_init($url);
    
    //GET request: send headers and return data transfer
    if ($method == 'GET'){
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1
        );
        curl_setopt_array($ch, $options);
    //POST / PUT request: send post object and return data transfer
    } else {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_POSTFIELDS => $postvals,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1
        );
        curl_setopt_array($ch, $options);
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>