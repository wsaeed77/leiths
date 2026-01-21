<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Security
 *
 * @author Grzegorz
 */
class Daq_Helper_Security
{
    private $iv = null; #Same as in JAVA
    private $key = null; #Same as in JAVA

    function __construct($iv = null, $key = null)
    {
        $this->iv = $iv;
        $this->key = $key;
    }

    public function get_algo() {
        $methods = openssl_get_cipher_methods();
        $default_cipher = apply_filters( "daq_default_cipher", "aes-128-cbc" );
        if( in_array( $default_cipher, $methods ) ) {
            return $default_cipher;
        } else {
            return $methods[0];
        }
    }

    public function encrypt($str) {
        $mode = apply_filters( "daq_encryption_module", "openssl" );
        if( $mode == "openssl" ) {
            return $this->encrypt_openssl($str);
        } else if( $mode == "mcrypt" ) {
            return $this->encrypt_mcrypt($str);
        } else {
            return null;
        }
    }

    public function decrypt($code) {
        $mode = apply_filters( "daq_encryption_module", "openssl" );
        if( $mode == "openssl" ) {
            return $this->decrypt_openssl($code);
        } else if( $mode == "mcrypt" ) {
            return $this->decrypt_mcrypt($code);
        } else {
            return null;
        }
    }

    public function encrypt_openssl($str) {
        return bin2hex( openssl_encrypt(
            $str,
            $this->get_algo(),
            $this->key,
            0,
            $this->iv,
        ) );
    }

    public function decrypt_openssl($code) {
        $code = $this->hex2bin($code);
        $iv = $this->iv;

        $decrypted = openssl_decrypt(
            $code,
            $this->get_algo(),
            $this->key,
            0,
            $iv
        );

        return utf8_encode(trim($decrypted));
    }

    public function encrypt_mcrypt($str) 
    {   
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $this->key, $iv);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return bin2hex($encrypted);
    }

    public function decrypt_mcrypt($code) 
    {
        $code = $this->hex2bin($code);
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $this->key, $iv);
        $decrypted = mdecrypt_generic($td, $code);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

    protected function hex2bin($hexdata) 
    {
        $bindata = '';

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
                $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

}

?>
