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
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated ModuleVisitorBrowser3Test::setUp()
        
        $this->moduleVisitorBrowser3 = new ModuleVisitorBrowser3(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
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
            array('unknown',    'Mosiller/42 (MyOs; MyBot/42')
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

    /**
     * Tests ModuleVisitorBrowser3->getPlatform()
     */
    public function testGetPlatform()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetPlatform()
        $this->markTestIncomplete("getPlatform test not implemented");
        
        $this->moduleVisitorBrowser3->getPlatform(/* parameters */);
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

    /**
     * Tests ModuleVisitorBrowser3->getVersion()
     */
    public function testGetVersion()
    {
        // TODO Auto-generated ModuleVisitorBrowser3Test->testGetVersion()
        $this->markTestIncomplete("getVersion test not implemented");
        
        $this->moduleVisitorBrowser3->getVersion(/* parameters */);
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

