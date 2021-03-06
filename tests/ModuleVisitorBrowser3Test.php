<?php
require_once 'src/Resources/contao/classes/ModuleVisitorBrowser3.php';

use BugBuster\Visitors\ModuleVisitorBrowser3;
use PHPUnit\Framework\TestCase;

/**
 * ModuleVisitorBrowser3 test case.
 */
class ModuleVisitorBrowser3Test extends TestCase
{

    /**
     *
     * @var ModuleVisitorBrowser3
     */
    private $moduleVisitorBrowser3;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() : void
    {
        parent::setUp();
        
        // TODO Auto-generated ModuleVisitorBrowser3Test::setUp()
        
        $this->moduleVisitorBrowser3 = new ModuleVisitorBrowser3(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() : void
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test::tearDown()
        $this->moduleVisitorBrowser3 = null;
        
        parent::tearDown();
    }

    public function providerAgents()
    {
        return array(//result,host
            array('Safari',     'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'),
            array('Safari',     'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'),
            array('Chrome',     'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'),
            array('Chrome',     'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13'),
            array('ChromePlus', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.28.3 (KHTML, like Gecko) Version/3.2.3 ChromePlus/4.0.222.3 Chrome/4.0.222.3 Safari/525.28.3'),
            array('Firefox',    'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1'),
            array('IE',         'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'),
            array('IE',         'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)'),
            array('IE',         'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko'),
            array('Vivaldi',    'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.89 Vivaldi/1.0.94.2 Safari/537.36'),
            array('Dooble',     'Dooble/0.07 (de_DE) WebKit'),
            array('QtWeb Browser','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'),
            array('unknown',    'Mosiller/42 (MyOs; MyBot/42'),
            array('Edge',           'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'),
            array('Edge (Chromium)','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.48 Safari/537.36 Edg/74.1.96.24'),
            array('Edge',           'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'),
            array('Edge',           'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'),
            array('Firefox',        'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'),
            array('Galaxy S6 Edge', 'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36'),
            array('Galaxy S7',      'Mozilla/5.0 (Linux; Android 7.0; SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36'),
            array('Galaxy S7 Edge', 'Mozilla/5.0 (Linux; Android 6.0.1; SM-G935S Build/MMB29K; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36'),
            array('Galaxy S8',      'Mozilla/5.0 (Linux; Android 7.0; SM-G892A Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/60.0.3112.107 Mobile Safari/537.36'),
            array('Galaxy S9',      'Mozilla/5.0 (Linux; Android 8.0.0; SM-G960F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.137 Mobile Safari/537.36'),
            array('Galaxy S9 Plus', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G965F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.109 Mobile Safari/537.36'),
            array('Galaxy S10',     'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G973F Build/PPR1.180610.011) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/8.0 Chrome/63.0.3239.111 Mobile Safari/537.36'),
            array('Galaxy S10 Plus','Mozilla/5.0 (Linux; Android 9; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.83 Mobile Safari/537.36'),
            array('Galaxy S10e',    'Mozilla/5.0 (Linux; Android 9; SM-G970F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Mobile Safari/537.36'),
            array('Galaxy S20',     'Mozilla/5.0 (Linux; Android 10; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.162 Mobile Safari/537.36'),
            array('Galaxy A5',      'Mozilla/5.0 (Linux; U; Android 4.4.4; tr-tr; SM-A500F Build/KTU84P) AppleWebKit/537.16 (KHTML, like Gecko) Version/4.0 Mobile Safari/537.16'),
            array('Galaxy A20',     'Mozilla/5.0 (Linux; Android 9; SM-A205F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.99 Mobile Safari/537.36'),
            array('Galaxy A40',     'Mozilla/5.0 (Linux; Android 10; SAMSUNG SM-A405FN) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/11.2 Chrome/75.0.3770.143 Mobile Safari/537.36'),
            array('Galaxy A50',     'Mozilla/5.0 (Linux; Android 9; SM-A505F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.105 Mobile Safari/537.36'),
            array('Galaxy A80',     'Mozilla/5.0 (Linux; Android 9; SM-A805F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.112 Mobile Safari/537.36'),
            array('Galaxy Tab',     'Mozilla/5.0 (Linux; Android 7.0; SM-T827R4 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.116 Safari/537.36'), //S3
            array('Galaxy Tab',     'Mozilla/5.0 (Linux; Android 5.0.2; SAMSUNG SM-T550 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.3 Chrome/38.0.2125.102 Safari/537.36') //A
        );
    }
    
    /**
     * Tests ModuleVisitorBrowser3->isBrowser()
     */
    public function testIsBrowser()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsBrowser()
        $this->markTestIncomplete("isBrowser test not implemented");
        
        $this->moduleVisitorBrowser3->isBrowser(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getBrowser()
     * 
     * 
     * @dataProvider providerAgents
     */
    public function testGetBrowser($result, $host)
    {
       
        $this->moduleVisitorBrowser3->initBrowser($host);
        
        //Result must be equal
        $this->assertEquals($result,$this->moduleVisitorBrowser3->getBrowser());
        
    }

    /**
     * Tests ModuleVisitorBrowser3->setBrowser()
     */
    public function testSetBrowser()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetBrowser()
        $this->markTestIncomplete("setBrowser test not implemented");
        
        $this->moduleVisitorBrowser3->setBrowser(/* parameters */);
    }

    public function providerAgentPlatforms()
    {
        return array(//result,host
            array('Apple',    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'),
            array('Windows',  'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'),
            array('Windows',  'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 7.0; InfoPath.3; .NET CLR 3.1.40767; Trident/6.0; en-IN)'),
            array('unknown',  'Dooble/0.07 (de_DE) WebKit'),
            array('Linux',    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'),
            array('unknown',  'Mosiller/42 (MyOs; MyBot/42'),
            array('Windows',  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'),
            array('Apple',    'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'),
            array('Android',  'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'),
            array('Apple',    'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'),
            array('Android',  'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36')
        );
    }

    /**
     * Tests ModuleVisitorBrowser3->getPlatform()
     * 
     * @dataProvider providerAgentPlatforms
     */
    public function testGetPlatform($result, $host)
    {
        $this->moduleVisitorBrowser3->initBrowser($host);
        
        //Result must be equal
        $this->assertEquals($result,$this->moduleVisitorBrowser3->getPlatform());
    }

    /**
     * Tests ModuleVisitorBrowser3->setPlatform()
     */
    public function testSetPlatform()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetPlatform()
        $this->markTestIncomplete("setPlatform test not implemented");
        
        $this->moduleVisitorBrowser3->setPlatform(/* parameters */);
    }


    public function providerAgentVersions()
    {
        return array(//result,host
            array('8.0',  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/600.7.12 (KHTML, like Gecko) Version/8.0.7 Safari/600.7.12'),
            array('41.0', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'),
            array('4.0',  'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.28.3 (KHTML, like Gecko) Version/3.2.3 ChromePlus/4.0.222.3 Chrome/4.0.222.3 Safari/525.28.3'),
            array('40.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1'),
            array('10.0', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'),
            array('1.0',  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.89 Vivaldi/1.0.94.2 Safari/537.36'),
            array('0.0',  'Dooble/0.07 (de_DE) WebKit'),
            array('5.9',  'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.3 Chrome/56.0.2924.122 Safari/537.36'),
            array('42.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134'),
            array('74.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.48 Safari/537.36 Edg/74.1.96.24'),
            array('41.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit/603.2.4(KHTML, like Gecko) Mobile/14F89 Safari/603.2.4 EdgiOS/41.1.35.1'),
            array('41.1', 'Mozilla/5.0 (Linux; Android 8.0; Pixel XL Build/OPP3.170518.006) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.0 Mobile Safari/537.36 EdgA/41.1.35.1'),
            array('7.5',  'Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) FxiOS/7.5b3349 Mobile/14A456 Safari/602.1.50'),
            array('unknown', 'Mozilla/5.0 (Linux; Android 5.1.1; SM-G925F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36')
        );
    }


    /**
     * Tests ModuleVisitorBrowser3->getVersion()
     * 
     * @dataProvider providerAgentVersions
     */
    public function testGetVersion($result, $host)
    {
        $this->moduleVisitorBrowser3->initBrowser($host);
        
        $this->assertEquals($result,$this->moduleVisitorBrowser3->getVersion());
    }

    /**
     * Tests ModuleVisitorBrowser3->setVersion()
     */
    public function testSetVersion()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetVersion()
        $this->markTestIncomplete("setVersion test not implemented");
        
        $this->moduleVisitorBrowser3->setVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getAolVersion()
     */
    public function testGetAolVersion()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetAolVersion()
        $this->markTestIncomplete("getAolVersion test not implemented");
        
        $this->moduleVisitorBrowser3->getAolVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setAolVersion()
     */
    public function testSetAolVersion()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetAolVersion()
        $this->markTestIncomplete("setAolVersion test not implemented");
        
        $this->moduleVisitorBrowser3->setAolVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isAol()
     */
    public function testIsAol()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsAol()
        $this->markTestIncomplete("isAol test not implemented");
        
        $this->moduleVisitorBrowser3->isAol(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isMobile()
     */
    public function testIsMobile()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsMobile()
        $this->markTestIncomplete("isMobile test not implemented");
        
        $this->moduleVisitorBrowser3->isMobile(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isRobot()
     */
    public function testIsRobot()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsRobot()
        $this->markTestIncomplete("isRobot test not implemented");
        
        $this->moduleVisitorBrowser3->isRobot(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setAol()
     */
    public function testSetAol()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetAol()
        $this->markTestIncomplete("setAol test not implemented");
        
        $this->moduleVisitorBrowser3->setAol(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getUserAgent()
     */
    public function testGetUserAgent()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetUserAgent()
        $this->markTestIncomplete("getUserAgent test not implemented");
        
        $this->moduleVisitorBrowser3->getUserAgent(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->setUserAgent()
     */
    public function testSetUserAgent()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testSetUserAgent()
        $this->markTestIncomplete("setUserAgent test not implemented");
        
        $this->moduleVisitorBrowser3->setUserAgent(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->isChromeFrame()
     */
    public function testIsChromeFrame()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testIsChromeFrame()
        $this->markTestIncomplete("isChromeFrame test not implemented");
        
        $this->moduleVisitorBrowser3->isChromeFrame(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->__toString()
     */
    public function test__toString()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->test__toString()
        $this->markTestIncomplete("__toString test not implemented");
        
        $this->moduleVisitorBrowser3->__toString(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getPlatformVersion()
     */
    public function testGetPlatformVersion()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetPlatformVersion()
        $this->markTestIncomplete("getPlatformVersion test not implemented");
        
        $this->moduleVisitorBrowser3->getPlatformVersion(/* parameters */);
    }

    /**
     * Tests ModuleVisitorBrowser3->getLang()
     */
    public function testGetLang()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetLang()
        $this->markTestIncomplete("getLang test not implemented");
        
        $this->moduleVisitorBrowser3->getLang(/* parameters */);
    }
}

