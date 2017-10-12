<?php
namespace Specter;

use Specter\SpecterSessionException;

class SpecterSession implements \SessionHandlerInterface
{
    public static $compressionLevel = 3;
    public static $cookieName = 'SDATA';
    public static $cookiePath = '/';
    public static $cookieDomain = null;
    public static $cookieSecure = true;
    public static $cookieHTTPOnly = true;
    private $encryptionKey;
    private $encryptionKeySalt;

    public static function initialize($encryptionKey, $encryptionKeySalt) {
        $handler = new SessionHandler($encryptionKey, $encryptionKeySalt);
        return session_set_save_handler($handler, true);
    }

    public function __construct($encryptionKey, $encryptionKeySalt) {
        if (empty($encryptionKey)) {
            throw new SpecterSessionException('You must specify an encryption key');
        }
        if (empty($encryptionKeySalt)) {
            throw new SpecterSessionException('You must specify an encryption key salt');
        }
        if (!extension_loaded('mcrypt')) {
            throw new SpecterSessionException('The PHP mcrypt extension is not installed. This extension is required to be able to use the API.');
        }
        $this->encryptionKey     = $encryptionKey;
        $this->encryptionKeySalt = $encryptionKeySalt;
    }

    public function encrypt($data) {
        $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        $salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        $key = hash('SHA256', $this->encryptionKey . $salt . $this->encryptionKeySalt, true);
        $padding = 16 - (strlen($data) % 16);
        $data   .= str_repeat(chr($padding), $padding);
        return $salt . $iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
    }

    public function decrypt($data) {
        $salt    = substr($data, 0, 16);
        $iv      = substr($data, 16, 16);
        $data    = substr($data, 32);
        $key = hash('SHA256', $this->encryptionKey . $salt . $this->encryptionKeySalt, true);
        $data    = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }

    public function compress($data) {
        $data = gzdeflate($data, self::$compressionLevel);
        if ($data === false) {
            throw new SpecterSessionException('Unable to compress the session data');
        }
        return $data;
    }

    public function decompress($data) {
        return gzinflate($data);
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $data = isset($_COOKIE[self::$cookieName]) ? $_COOKIE[self::$cookieName] : null;
        if (($delimiterPos = strpos($data, '-')) === false) {
            return '';
        }
        $signature = substr($data, 0, $delimiterPos);
        $data      = substr($data, $delimiterPos + 1);
        $expectedSignature = base64_encode(hash_hmac('sha256', $data, $this->encryptionKey . $id . $this->encryptionKeySalt, true));
        if (strcmp($signature, $expectedSignature) !== 0) {
            return '';
        }
        $data = base64_decode($data);
        if ($data === false) {
            return '';
        }
        $data = $this->decrypt($data);
        $data = $this->decompress($data);
        return ($data !== false) ? $data : '';
    }

    public function write($id, $data)
    {
        if (headers_sent($file, $line)) {
            throw new SpecterSessionException('Can not send session data cookie - headers already sent (output started at ' . $file . ':' . $line . ')');
        }
        $data = $this->compress($data);
        $data = $this->encrypt($data);
        $data = base64_encode($data);
        $signature = hash_hmac('sha256', $data, $this->encryptionKey . $id . $this->encryptionKeySalt, true);
        $data = base64_encode($signature) . '-' . $data;
        if (strlen($data) > 3072) {
            throw new SpecterSessionException("The session data is too large (over 3KB)", 1);
        }
        $cookieLifetime = ini_get('session.cookie_lifetime');

        return setcookie(self::$cookieName, $data, $cookieLifetime, self::$cookiePath, self::$cookieDomain, self::$cookieSecure, self::$cookieHTTPOnly);
    }

    public function destroy($id)
    {
        $expirationTime = time() - 10800;
        return setcookie(self::$cookieName, '', $expirationTime, self::$cookiePath, self::$cookieDomain, self::$cookieSecure, self::$cookieHTTPOnly);
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}
