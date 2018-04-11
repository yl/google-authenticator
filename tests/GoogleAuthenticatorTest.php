<?php

use Endroid\QrCode\QrCode;
use Leonis\GoogleAuthenticator\GoogleAuthenticator;
use PHPUnit\Framework\TestCase;

class GoogleAuthenticatorTest extends TestCase
{
    protected $googleAuthenticator;

    protected function setUp()
    {
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    public function codeProvider()
    {
        // Secret, time, code
        return [
            ['SECRET', '0', '200470'],
            ['SECRET', '1385909245', '780018'],
            ['SECRET', '1378934578', '705013'],
        ];
    }

    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(GoogleAuthenticator::class, new GoogleAuthenticator());
    }

    public function testCreateSecretDefaultsToSixteenCharacters()
    {
        $this->assertEquals(strlen($this->googleAuthenticator->secret()), 16);
    }

    public function testCreateSecretLengthCanBeSpecified()
    {
        for ($secretLength = 16; $secretLength < 100; $secretLength++) {
            $secret = $this->googleAuthenticator->secret($secretLength);
            $this->assertEquals(strlen($secret), $secretLength);
        }
    }

    /**
     * @dataProvider codeProvider
     */
    public function testGetCodeReturnsCorrectValues($secret, $timeSlice, $code)
    {
        $this->assertEquals($code, $this->googleAuthenticator->code($secret, $timeSlice));
    }

    public function testQrCode()
    {
        $this->assertInstanceOf(QrCode::class, $this->googleAuthenticator->qrCode('Test', 'SECRET'));
    }

    public function testVerify()
    {
        $secret = 'SECRET';
        $code = $this->googleAuthenticator->code($secret);
        $result = $this->googleAuthenticator->verify($secret, $code);
        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = $this->googleAuthenticator->verify($secret, $code);
        $this->assertEquals(false, $result);
    }

    public function testVerifyCodeWithLeadingZero()
    {
        $secret = 'SECRET';
        $code = $this->googleAuthenticator->code($secret);
        $result = $this->googleAuthenticator->verify($secret, $code);
        $this->assertEquals(true, $result);

        $code = '0'.$code;
        $result = $this->googleAuthenticator->verify($secret, $code);
        $this->assertEquals(false, $result);
    }

    public function testSetCodeLength()
    {
        $this->assertInstanceOf(GoogleAuthenticator::class, $this->googleAuthenticator->setCodeLength(6));
    }
}
