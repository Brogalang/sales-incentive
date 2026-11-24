<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');

}

/**
 * Escape nilai untuk dipakai di dalam string literal OData: ' -> ''
 * Selalu pakai ini bila nilai dari user/session ditempel ke $filter.
 */
if (! function_exists('odata_quote')) {
    function odata_quote($value) {
        return str_replace("'", "''", (string) $value);
    }
}

/**
 * Terapkan opsi keamanan TLS ke handle curl.
 * - URL https://  -> verifikasi sertifikat & hostname WAJIB (opsional CA internal via config 'api_ca_file').
 * - URL http://   -> tidak ada TLS, jadi tidak ada yang diverifikasi (disarankan pindah ke HTTPS).
 */
if (! function_exists('apply_curl_security')) {
    function apply_curl_security($ch, $url) {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);

        if (stripos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $ca = function_exists('config_item') ? config_item('api_ca_file') : '';
            if (!empty($ca) && is_file($ca)) {
                curl_setopt($ch, CURLOPT_CAINFO, $ca);
            }
        }
    }
}

if(! function_exists('getToken')) {
    function getToken ($url = '', $user_id = '', $password = '') {
        $url .= 'token';

        // http_build_query meng-encode karakter khusus (&, +, %, =) di password/username
        $fields = http_build_query([
            'grant_type' => 'password',
            'username'   => $user_id,
            'password'   => $password
        ]);

        # Form our options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        apply_curl_security($ch, $url);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Ambil kode status HTTP

        if(curl_errno($ch)){
            $res = [
                "status" => "failed",
                "data" => curl_error($ch),
            ];
            return $res;
        }

        if($http_code == 200){
            $res = [
                "status" => "success",
                "data" => json_decode($result),
            ];
        } else {
            $res = [
                "status" => "failed",
                "data" => json_decode($result),
            ];
        }
        return $res;
    }
}


if(! function_exists('parseExec')) {
    function parseExec($token, $url, $fields = null) {
		$curl = curl_init($url);
		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
			"Authorization: Bearer $token"
		);

        if($fields != null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        }

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		apply_curl_security($curl, $url);

		$result = curl_exec($curl);

		// curl_close() is a no-op since PHP 8.0 and deprecated since PHP 8.5
		unset($curl);

        if($result == false) {
            return $result;
        }

        return json_decode($result);
    }
}
