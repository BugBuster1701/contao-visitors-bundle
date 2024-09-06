<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

require_once 'src/Resources/contao/classes/ModuleVisitorBrowser3.php';

use BugBuster\Visitors\ModuleVisitorBrowser3;
use PHPUnit\Framework\TestCase;

/**
 * ModuleVisitorBrowser3 test case.
 */
class ModuleVisitorBrowser3Test extends TestCase
{
    /**
     * @var ModuleVisitorBrowser3
     */
    private $moduleVisitorBrowser3;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // TODO Auto-generated ModuleVisitorBrowser3Test::setUp()

        $this->moduleVisitorBrowser3 = new ModuleVisitorBrowser3(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test::tearDown()
        $this->moduleVisitorBrowser3 = null;

        parent::tearDown();
    }

    public static function providerAgents(): iterable
    {
        return [// result,host
            ['Aloha Browser', 'Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-G975U1 Build/NMF26F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.96 Mobile Safari/537.36 AlohaBrowser/3.1.1'],
            ['Aloha Browser', 'Mozilla/5.0 (iPad; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Version/15.0 Safari/605.1.15 AlohaBrowser/3.2.6'],
            ['Safari', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'],
            ['Safari', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'],
            ['Chrome', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'],
            ['Chrome', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13'],
            ['ChromePlus', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.28.3 (KHTML, like Gecko) Version/3.2.3 ChromePlus/4.0.222.3 Chrome/4.0.222.3 Safari/525.28.3'],
            ['Firefox', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1'],
            ['IE', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'],
            ['IE', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)'],
            ['IE', 'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko'],
            ['Vivaldi', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.89 Vivaldi/1.0.94.2 Safari/537.36'],
            ['Dooble', 'Dooble/0.07 (de_DE) WebKit'],
            ['QtWeb Browser', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'],
            ['unknown', 'Mosiller/42 (MyOs; MyBot/42'],
            ['Edge', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'],
            ['Edge (Chromium)', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.48 Safari/537.36 Edg/74.1.96.24'],
            ['Edge', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'],
            ['Edge', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/116 Version/13.0.3 Safari/605.1.15'],
            ['Edge', 'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'],
            ['Firefox', 'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'],
            ['Yandex Browser', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 YaBrowser/24.7.1.1144 Yowser/2.5 Safari/537.36'],
            ['Galaxy S6 Edge', 'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36'],
            ['Galaxy S7', 'Mozilla/5.0 (Linux; Android 7.0; SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36'],
            ['Galaxy S7 Edge', 'Mozilla/5.0 (Linux; Android 6.0.1; SM-G935S Build/MMB29K; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36'],
            ['Galaxy S8', 'Mozilla/5.0 (Linux; Android 7.0; SM-G892A Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/60.0.3112.107 Mobile Safari/537.36'],
            ['Galaxy S9', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.137 Mobile Safari/537.36'],
            ['Galaxy S9 Plus', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G965F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.109 Mobile Safari/537.36'],
            ['Galaxy S10', 'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G973F Build/PPR1.180610.011) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/8.0 Chrome/63.0.3239.111 Mobile Safari/537.36'],
            ['Galaxy S10 Plus', 'Mozilla/5.0 (Linux; Android 9; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.83 Mobile Safari/537.36'],
            ['Galaxy S10e', 'Mozilla/5.0 (Linux; Android 9; SM-G970F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Mobile Safari/537.36'],
            ['Galaxy S20', 'Mozilla/5.0 (Linux; Android 10; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.162 Mobile Safari/537.36'],
            ['Galaxy A5', 'Mozilla/5.0 (Linux; U; Android 4.4.4; tr-tr; SM-A500F Build/KTU84P) AppleWebKit/537.16 (KHTML, like Gecko) Version/4.0 Mobile Safari/537.16'],
            ['Galaxy A20', 'Mozilla/5.0 (Linux; Android 9; SM-A205F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.99 Mobile Safari/537.36'],
            ['Galaxy A40', 'Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-A405FN) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/11.2 Chrome/75.0.3770.143 Mobile Safari/537.36'],
            ['Galaxy A50', 'Mozilla/5.0 (Linux; Android 9; SM-A505F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.105 Mobile Safari/537.36'],
            ['Galaxy A80', 'Mozilla/5.0 (Linux; Android 9; SM-A805F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.112 Mobile Safari/537.36'],
            ['Galaxy Tab', 'Mozilla/5.0 (Linux; Android 7.0; SM-T827R4 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.116 Safari/537.36'], // S3
            ['Galaxy Tab', 'Mozilla/5.0 (Linux; Android 5.0.2; SAMSUNG SM-T550 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.3 Chrome/38.0.2125.102 Safari/537.36'], // A
        ];
    }

    /**
     * Tests ModuleVisitorBrowser3->isBrowser().
     */
    public function testIsBrowser(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsBrowser()
        $this->markTestIncomplete('isBrowser test not implemented');

        $this->moduleVisitorBrowser3->isBrowser(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getBrowser().
     *
     * @dataProvider providerAgents
     */
    public function testGetBrowser($result, $host): void
    {
        $this->moduleVisitorBrowser3->initBrowser($host);

        // Result must be equal
        $this->assertSame($result, $this->moduleVisitorBrowser3->getBrowser());
    }

    /**
     * Tests ModuleVisitorBrowser3->setBrowser().
     */
    public function testSetBrowser(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetBrowser()
        $this->markTestIncomplete('setBrowser test not implemented');

        $this->moduleVisitorBrowser3->setBrowser(/* parameters */);
    }

    public static function providerAgentPlatforms(): iterable
    {
        return [// result,host
            ['Apple', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'],
            ['Windows', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'],
            ['Windows', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)'],
            ['unknown', 'Dooble/0.07 (de_DE) WebKit'],
            ['Linux', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'],
            ['unknown', 'Mosiller/42 (MyOs; MyBot/42'],
            ['Windows', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'],
            ['Apple', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'],
            ['Android', 'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'],
            ['Apple', 'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'],
            ['Android', 'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36'],
            ['Apple', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/116 Version/13.0.3 Safari/605.1.15'],
        ];
    }

    /**
     * Tests ModuleVisitorBrowser3->getPlatform().
     *
     * @dataProvider providerAgentPlatforms
     */
    public function testGetPlatform($result, $host): void
    {
        $this->moduleVisitorBrowser3->initBrowser($host);

        // Result must be equal
        $this->assertSame($result, $this->moduleVisitorBrowser3->getPlatform());
    }

    /**
     * Tests ModuleVisitorBrowser3->setPlatform().
     */
    public function testSetPlatform(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetPlatform()
        $this->markTestIncomplete('setPlatform test not implemented');

        $this->moduleVisitorBrowser3->setPlatform(/* parameters */);
    }

    public static function providerAgentVersions(): iterable
    {
        return [// result,host
            ['8.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'],
            ['41.0', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'],
            ['4.0', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.28.3 (KHTML, like Gecko) Version/3.2.3 ChromePlus/4.0.222.3 Chrome/4.0.222.3 Safari/525.28.3'],
            ['40.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1'],
            ['10.0', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'],
            ['1.0', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.89 Vivaldi/1.0.94.2 Safari/537.36'],
            ['0.07', 'Dooble/0.07 (de_DE) WebKit'],
            ['5.9', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'],
            // alter Edge, gemapped
            ['42.17134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'],
            // neuere Edges, nur Major Version wegen Abwärtskompatibilität
            ['74.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.48 Safari/537.36 Edg/74.1.96.24'],
            ['41.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41'],
            ['41.0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'],
            ['116.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/116 Version/13.0.3 Safari/605.1.15'],
            ['116.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/116.12 Version/13.0.3 Safari/605.1.15'],
            ['41.0', 'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41'],
            ['41.0', 'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'],

            ['7.5b3349', 'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'],
            ['unknown', 'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36'],
            ['3.1', 'Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-G975U1 Build/NMF26F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.96 Mobile Safari/537.36 AlohaBrowser/3.1.1'],
        ];
    }

    /**
     * Tests ModuleVisitorBrowser3->getVersion().
     *
     * @dataProvider providerAgentVersions
     */
    public function testGetVersion($result, $host): void
    {
        $this->moduleVisitorBrowser3->initBrowser($host);

        $this->assertSame($result, $this->moduleVisitorBrowser3->getVersion());
    }

    /**
     * Tests ModuleVisitorBrowser3->setVersion().
     */
    public function testSetVersion(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetVersion()
        $this->markTestIncomplete('setVersion test not implemented');

        $this->moduleVisitorBrowser3->setVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getAolVersion().
     */
    public function testGetAolVersion(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetAolVersion()
        $this->markTestIncomplete('getAolVersion test not implemented');

        $this->moduleVisitorBrowser3->getAolVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setAolVersion().
     */
    public function testSetAolVersion(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetAolVersion()
        $this->markTestIncomplete('setAolVersion test not implemented');

        $this->moduleVisitorBrowser3->setAolVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isAol().
     */
    public function testIsAol(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsAol()
        $this->markTestIncomplete('isAol test not implemented');

        $this->moduleVisitorBrowser3->isAol(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isMobile().
     */
    public function testIsMobile(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsMobile()
        $this->markTestIncomplete('isMobile test not implemented');

        $this->moduleVisitorBrowser3->isMobile(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isRobot().
     */
    public function testIsRobot(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsRobot()
        $this->markTestIncomplete('isRobot test not implemented');

        $this->moduleVisitorBrowser3->isRobot(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setAol().
     */
    public function testSetAol(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetAol()
        $this->markTestIncomplete('setAol test not implemented');

        $this->moduleVisitorBrowser3->setAol(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getUserAgent().
     */
    public function testGetUserAgent(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetUserAgent()
        $this->markTestIncomplete('getUserAgent test not implemented');

        $this->moduleVisitorBrowser3->getUserAgent(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setUserAgent().
     */
    public function testSetUserAgent(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetUserAgent()
        $this->markTestIncomplete('setUserAgent test not implemented');

        $this->moduleVisitorBrowser3->setUserAgent(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isChromeFrame().
     */
    public function testIsChromeFrame(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsChromeFrame()
        $this->markTestIncomplete('isChromeFrame test not implemented');

        $this->moduleVisitorBrowser3->isChromeFrame(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->__toString().
     */
    public function testToString(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->test__toString()
        $this->markTestIncomplete('__toString test not implemented');

        $this->moduleVisitorBrowser3->__toString(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getPlatformVersion().
     */
    public function testGetPlatformVersion(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetPlatformVersion()
        $this->markTestIncomplete('getPlatformVersion test not implemented');

        $this->moduleVisitorBrowser3->getPlatformVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getLang().
     */
    public function testGetLang(): void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetLang()
        $this->markTestIncomplete('getLang test not implemented');

        $this->moduleVisitorBrowser3->getLang(/* parameters */);
    }
}
