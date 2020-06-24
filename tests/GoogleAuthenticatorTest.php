<?php

use Leonis\GoogleAuthenticator\GoogleAuthenticator;
use PHPUnit\Framework\TestCase;

class GoogleAuthenticatorTest extends TestCase
{
    public function codeProvider()
    {
        // Secret, time, code
        return [
            ['SECRET', '0', '200470'],
            ['SECRET', '1385909245', '780018'],
            ['SECRET', '1378934578', '705013'],
        ];
    }

    public function testCreateSecretDefaultsToSixteenCharacters()
    {
        $this->assertEquals(strlen(GoogleAuthenticator::secret()), 16);
    }

    public function testCreateSecretLengthCanBeSpecified()
    {
        for ($secretLength = 16; $secretLength < 100; $secretLength++) {
            $secret = GoogleAuthenticator::secret($secretLength);
            $this->assertEquals(strlen($secret), $secretLength);
        }
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetCodeReturnsCorrectValues($secret, $timeSlice, $code)
    {
        $this->assertEquals($code, GoogleAuthenticator::code($secret, $timeSlice));
    }

    public function testVerify()
    {
        $secret = 'SECRET';
        $code = GoogleAuthenticator::code($secret);
        $result = GoogleAuthenticator::verify($secret, $code);
        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = GoogleAuthenticator::verify($secret, $code);
        $this->assertEquals(false, $result);
    }

    public function testVerifyCodeWithLeadingZero()
    {
        $secret = 'SECRET';
        $code = GoogleAuthenticator::code($secret);
        $result = GoogleAuthenticator::verify($secret, $code);
        $this->assertEquals(true, $result);

        $code = '0'.$code;
        $result = GoogleAuthenticator::verify($secret, $code);
        $this->assertEquals(false, $result);
    }
}
