<?php

namespace Leonis\GoogleAuthenticator;

use Exception;

class GoogleAuthenticator
{
    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $length
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function secret($length = 16)
    {
        $base32Chars = self::base32Chars();
        // Valid secret lengths are 80 to 640 bits
        if ($length < 16 || $length > 128) {
            throw new Exception('Bad secret length');
        }
        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($length, $cryptStrong);
            if (!$cryptStrong) {
                $rnd = false;
            }
        }
        if ($rnd !== false) {
            for ($i = 0; $i < $length; $i++) {
                $secret .= $base32Chars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('No source of secure random');
        }

        return $secret;
    }

    /**
     * Calculate the code, with given secret and point in time.
     *
     * @param string   $secret
     * @param int      $codeLength
     * @param int|null $timeSlice
     *
     * @return string
     */
    public static function code($secret, $codeLength = 6, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretKey = self::base32Decode($secret);
        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashPart = substr($hm, $offset, 4);
        // Unpack binary value
        $value = unpack('N', $hashPart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, $codeLength);

        return str_pad($value % $modulo, $codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now.
     *
     * @param string   $secret
     * @param string   $code
     * @param int      $codeLength
     * @param int      $discrepancy      This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $currentTimeSlice time slice if we want use other that time()
     *
     * @return bool
     */
    public static function verify($secret, $code, $codeLength = 6, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }
        if (strlen($code) != $codeLength) {
            return false;
        }
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::code($secret, $currentTimeSlice + $i);
            if (self::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper class to decode base32.
     *
     * @param $secret
     *
     * @return bool|string
     */
    protected static function base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }
        $base32chars = self::base32Chars();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32.
     *
     * @return array
     */
    protected static function base32Chars()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }

    /**
     * A timing safe equals comparison
     * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
     *
     * @param string $safeString The internal (safe) value to be checked
     * @param string $userString The user submitted (unsafe) value
     *
     * @return bool True if the two strings are identical
     */
    private static function timingSafeEquals($safeString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen !== $safeLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }
}
