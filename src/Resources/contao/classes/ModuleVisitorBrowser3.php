<?php 

/**
 * File: Browser.php
 * Author: Chris Schuld (http://chrisschuld.com/)
 * Last Modified: August 20th, 2010
 * version 1.9
 * 
 * Contao Module Version
 * @author     Glen Langer (BugBuster); modified for Contao Module Visitors
 * @version 3.0.0
 *
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;

/**
 * Class ModuleVisitorBrowser3
 * @author Data
 *
 */
class ModuleVisitorBrowser3 
{
	private $_agent = '';
	private $_browser_name = '';
	private $_version = '';
	private $_platform = '';
	
	//#130 private $_os = '';
	private $_is_aol = false;
	private $_is_mobile = false;
	private $_is_robot = false;
	private $_aol_version = '';
	
	private $_platformVersion   = '';   //add BugBuster
	protected $_accept_language = null; //add BugBuster
	protected $_lang            = null; //add BugBuster

	const BROWSER_UNKNOWN = 'unknown';
	const VERSION_UNKNOWN = 'unknown';

	const BROWSER_OPERA = 'Opera';                            // http://www.opera.com/
	const BROWSER_OPERA_MINI = 'Opera Mini';                  // http://www.opera.com/mini/
	const BROWSER_WEBTV = 'WebTV';                            // http://www.webtv.net/pc/
	const BROWSER_MS_EDGE = 'Edge';                           // https://msdn.microsoft.com/en-us/library/hh869301%28v=vs.85%29.aspx
	const BROWSER_MS_EDGE_MOBILE = 'Edge Mobile';
	const BROWSER_IE = 'IE';    //modified for compatibility  // http://www.microsoft.com/ie/
	const BROWSER_IE_MOBILE = 'IE Mobile';
	const BROWSER_POCKET_IE = 'Pocket IE';//modified for compatibility     // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
	const BROWSER_KONQUEROR = 'Konqueror';                    // http://www.konqueror.org/
	const BROWSER_ICAB = 'iCab';                              // http://www.icab.de/
	const BROWSER_OMNIWEB = 'OmniWeb';                        // http://www.omnigroup.com/applications/omniweb/
	const BROWSER_FIREBIRD = 'Firebird';                      // http://www.ibphoenix.com/
	const BROWSER_FIREFOX = 'Firefox';                        // http://www.mozilla.com/en-US/firefox/firefox.html
	const BROWSER_ICEWEASEL = 'Iceweasel';                    // http://www.geticeweasel.org/
	const BROWSER_SHIRETOKO = 'Shiretoko';                    // http://wiki.mozilla.org/Projects/shiretoko
	const BROWSER_MOZILLA = 'Mozilla';                        // http://www.mozilla.com/en-US/
	const BROWSER_SONGBIRD  = 'Songbird';
	const BROWSER_SEAMONKEY = 'SeaMonkey';
	const BROWSER_AMAYA = 'Amaya';                            // http://www.w3.org/Amaya/
	const BROWSER_LYNX = 'Lynx';                              // http://en.wikipedia.org/wiki/Lynx
	const BROWSER_SAFARI = 'Safari';                          // http://apple.com
	const BROWSER_IPHONE = 'iPhone';                          // http://apple.com
	const BROWSER_IPOD = 'iPod';                              // http://apple.com
	const BROWSER_IPAD = 'iPad';                              // http://apple.com
	const BROWSER_CHROME = 'Chrome';                          // http://www.google.com/chrome
	const BROWSER_VIVALDI = 'Vivaldi';                        // http://vivaldi.com
	
	const BROWSER_ANDROID = 'Android';                        // http://www.android.com/
	const BROWSER_GALAXY_S        = 'Galaxy S';
	const BROWSER_GALAXY_S_PLUS   = 'Galaxy S Plus';
	const BROWSER_GALAXY_S_II     = 'Galaxy S II';
	const BROWSER_GALAXY_S_III       = 'Galaxy S III';
	const BROWSER_GALAXY_S_III_MINI  = 'Galaxy S III Mini';
	const BROWSER_GALAXY_S_III_NEO   = 'Galaxy S III Neo';
	const BROWSER_GALAXY_S4        = 'Galaxy S4';
	const BROWSER_GALAXY_S4_MINI   = 'Galaxy S4 Mini';
	const BROWSER_GALAXY_S4_ACTIVE = 'Galaxy S4 Active';
	const BROWSER_GALAXY_S4_ZOOM   = 'Galaxy S4 Zoom';
	const BROWSER_GALAXY_S5        = 'Galaxy S5';
	const BROWSER_GALAXY_S5_MINI   = 'Galaxy S5 Mini';
	const BROWSER_GALAXY_S5_ACTIVE = 'Galaxy S5 Active';
	const BROWSER_GALAXY_S5_ZOOM   = 'Galaxy S5 Zoom';
	const BROWSER_GALAXY_S5_PLUS   = 'Galaxy S5 Plus';
	const BROWSER_GALAXY_S6        = 'Galaxy S6';
	const BROWSER_GALAXY_S6_ACTIVE = 'Galaxy S6 Active';
	const BROWSER_GALAXY_S6_EDGE   = 'Galaxy S6 Edge';
	const BROWSER_GALAXY_S6_EDGE_P = 'Galaxy S6 Edge Plus';
	const BROWSER_GALAXY_S6_MINI   = 'Galaxy S6 Mini';
	const BROWSER_GALAXY_ACE      = 'Galaxy Ace';
	const BROWSER_GALAXY_ACE_2    = 'Galaxy Ace 2';
	const BROWSER_GALAXY_ACE_PLUS = 'Galaxy Ace Plus';
	const BROWSER_GALAXY_NOTE     = 'Galaxy Note';
	const BROWSER_GALAXY_TAB      = 'Galaxy Tab';
	const BROWSER_SAMSUNG_GALAXY_NEXUS = 'Galaxy Nexus';      // Google Phone Android 4, add BugBuster
	const BROWSER_SAMSUNG_NEXUS_S = 'Nexus S';                // Google Phone, add BugBuster
	const BROWSER_HTC_DESIRE_HD   = 'HTC Desire HD';
	const BROWSER_HTC_DESIRE_Z    = 'HTC Desire Z';
	const BROWSER_HTC_DESIRE_S    = 'HTC Desire S';
	const BROWSER_HTC_DESIRE      = 'HTC Desire';
	const BROWSER_HTC_MAGIC       = 'HTC Magic';
	const BROWSER_HTC_NEXUS_ONE   = 'HTC Nexus One'; 			  // Google Phone, add BugBuster
	const BROWSER_HTC_SENSATION       = 'HTC Sensation';
	const BROWSER_HTC_SENSATION_XE    = 'HTC Sensation XE';
	const BROWSER_HTC_SENSATION_XL    = 'HTC Sensation XL';
	const BROWSER_HTC_SENSATION_Z710  = 'HTC Sensation Z710';
	const BROWSER_HTC_WILDFIRES_A510E = 'HTC WildfireS A510e';
	const BROWSER_ACER_A501  = 'Acer A501 Tab';				  // (Android 3.x Tab), add BugBuster
	const BROWSER_ACER_A500  = 'Acer A500 Tab';				  // (Android 3.x Tab), add BugBuster
	const BROWSER_LENOVO_THINKPAD_TABLET = 'ThinkPad Tab'; 	  // (Android 3.x Tab), add BugBuster
	const BROWSER_MOTOROLA_XOOM_TABLET   = 'Motorola Xoom Tab';	// (Android 3/4 Tab), add BugBuster
	const BROWSER_ASUS_TRANSFORMER_PAD   = 'ASUS Transformer Pad'; // (Android 4 Tab), add BugBuster
	
	const BROWSER_GOOGLEBOT = 'GoogleBot';                    // http://en.wikipedia.org/wiki/Googlebot
	const BROWSER_SLURP = 'Yahoo! Slurp';                     // http://en.wikipedia.org/wiki/Yahoo!_Slurp
	const BROWSER_W3CVALIDATOR = 'W3C Validator';             // http://validator.w3.org/
	const BROWSER_BLACKBERRY = 'BlackBerry';                  // http://www.blackberry.com/
	const BROWSER_ICECAT = 'IceCat';                          // http://en.wikipedia.org/wiki/GNU_IceCat
	const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser';        // http://en.wikipedia.org/wiki/Web_Browser_for_S60
	const BROWSER_NOKIA = 'Nokia Browser';                    // * all other WAP-based browsers on the Nokia Platform
	const BROWSER_MSN = 'MSN Browser';                        // http://explorer.msn.com/
	const BROWSER_MSNBOT = 'MSN Bot';                         // http://search.msn.com/msnbot.htm
	                                                          // http://en.wikipedia.org/wiki/Msnbot  (used for Bing as well)
	const BROWSER_CHROME_PLUS   = 'ChromePlus';    //add BugBuster // http://www.chromeplus.org/ (based on Chromium)
	const BROWSER_HTTP_REQUEST2 = 'HTTP_Request2'; //add BugBuster // http://pear.php.net/package/http_request2
	const BROWSER_COOL_NOVO     = 'CoolNovo';      //add BugBuster // http://http://www.coolnovo.com/ (previous ChromePlus)
	const BROWSER_COOL_MAXTHON  = 'Maxthon';       //add BugBuster // http://de.maxthon.com/
	
	const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';  // http://browser.netscape.com/ (DEPRECATED)
	const BROWSER_GALEON = 'Galeon';                          // http://galeon.sourceforge.net/ (DEPRECATED)
	const BROWSER_NETPOSITIVE = 'NetPositive';                // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
	const BROWSER_PHOENIX = 'Phoenix';                        // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)
	const BROWSER_TONLINE = 'T-Online';
	const BROWSER_KINDLE_FIRE = 'Kindle Fire';//add BugBuster // http://amazonsilk.wordpress.com/useful-bits/silk-user-agent/

	const PLATFORM_UNKNOWN = 'unknown';
	const PLATFORM_WINDOWS = 'Windows';
	const PLATFORM_WINDOWS_CE = 'WinCE'; //modified for compatibility
	const PLATFORM_WINDOWS_PHONE = 'WinPhone';           // http://www.developer.nokia.com/Community/Wiki/User-Agent_headers_for_Nokia_devices
	const PLATFORM_APPLE = 'Apple';
	const PLATFORM_LINUX = 'Linux';
	const PLATFORM_OS2 = 'OS/2';
	const PLATFORM_BEOS = 'BeOS';
	const PLATFORM_IPHONE = 'iPhone';
	const PLATFORM_IPOD = 'iPod';
	const PLATFORM_IPAD = 'iPad';
	const PLATFORM_BLACKBERRY = 'BlackBerry';
	const PLATFORM_NOKIA = 'Nokia';
	const PLATFORM_FREEBSD = 'FreeBSD';
	const PLATFORM_OPENBSD = 'OpenBSD';
	const PLATFORM_NETBSD = 'NetBSD';
	const PLATFORM_SUNOS = 'SunOS';
	const PLATFORM_OPENSOLARIS = 'OpenSolaris';
	const PLATFORM_ANDROID = 'Android';
	const PLATFORM_PLAYSTATION = 'PlayStation';
	
	const PLATFORM_PHP = 'PHP';	//add BugBuster
	
	const OPERATING_SYSTEM_UNKNOWN = 'unknown';
	
	/**
	 * Special Platform Add-On, BugBuster (Glen Langer)
	 */
	const PLATFORM_WINDOWS_95    = 'Win95';
	const PLATFORM_WINDOWS_98    = 'Win98';
	const PLATFORM_WINDOWS_ME    = 'WinME';
	const PLATFORM_WINDOWS_NT    = 'WinNT';
	const PLATFORM_WINDOWS_2000  = 'Win2000';
	const PLATFORM_WINDOWS_2003  = 'Win2003';
	const PLATFORM_WINDOWS_XP    = 'WinXP';
	const PLATFORM_WINDOWS_VISTA = 'WinVista';
	const PLATFORM_WINDOWS_7     = 'Win7';
	const PLATFORM_WINDOWS_8     = 'Win8';
	const PLATFORM_WINDOWS_81    = 'Win8.1';
	const PLATFORM_WINDOWS_10    = 'Win10';
	const PLATFORM_WINDOWS_RT    = 'WinRT';
	const PLATFORM_MACOSX        = 'MacOSX';
	const PLATFORM_IOSX          = 'iOS';
	const PLATFORM_WARP4         = 'OS/2 Warp 4';

	public function initBrowser($useragent="",$accept_language="") { //modified for compatibility
		$this->reset();
		$this->_accept_language = $accept_language;
		$this->setLang();
		if( $useragent != "" ) {
			$this->setUserAgent($useragent);
		}
		else {
			$this->determine();
		}
	}

	/**
	* Reset all properties
	*/
	public function reset() {
		$this->_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
		$this->_browser_name = self::BROWSER_UNKNOWN;
		$this->_version = self::VERSION_UNKNOWN;
		$this->_platform = self::PLATFORM_UNKNOWN;
		//#130 $this->_os = self::OPERATING_SYSTEM_UNKNOWN;
		$this->_is_aol = false;
		$this->_is_mobile = false;
		$this->_is_robot = false;
		$this->_aol_version = self::VERSION_UNKNOWN;
		$this->_platformVersion = self::PLATFORM_UNKNOWN;	//add BugBuster
	}

	/**
	* Check to see if the specific browser is valid
	* @param string $browserName
	* @return True if the browser is the specified browser
	*/
	public function isBrowser($browserName) { return( 0 == strcasecmp($this->_browser_name, trim($browserName))); }

	/**
	* The name of the browser.  All return types are from the class contants
	* @return string Name of the browser
	*/
	public function getBrowser() { return $this->_browser_name; }
	/**
	* Set the name of the browser
	* @param $browser The name of the Browser
	*/
	public function setBrowser($browser) { return $this->_browser_name = $browser; }
	/**
	* The name of the platform.  All return types are from the class contants
	* @return string Name of the browser
	*/
	public function getPlatform() { return $this->_platform; }
	/**
	* Set the name of the platform
	* @param $platform The name of the Platform
	*/
	public function setPlatform($platform) { return $this->_platform = $platform; }
	/**
	* The version of the browser.
	* @return string Version of the browser (will only contain alpha-numeric characters and a period)
	*/
	public function getVersion() { return $this->_version; }
	/**
	* Set the version of the browser
	* @param $version The version of the Browser
	*/
	public function setVersion($version) { $this->_version = preg_replace('/[^0-9,.,a-z,A-Z-]/','',$version); }
	/**
	* The version of AOL.
	* @return string Version of AOL (will only contain alpha-numeric characters and a period)
	*/
	public function getAolVersion() { return $this->_aol_version; }
	/**
	* Set the version of AOL
	* @param $version The version of AOL
	*/
	public function setAolVersion($version) { $this->_aol_version = preg_replace('/[^0-9,.,a-z,A-Z]/','',$version); }
	/**
	* Is the browser from AOL?
	* @return boolean True if the browser is from AOL otherwise false
	*/
	public function isAol() { return $this->_is_aol; }
	/**
	* Is the browser from a mobile device?
	* @return boolean True if the browser is from a mobile device otherwise false
	*/
	public function isMobile() { return $this->_is_mobile; }
	/**
	* Is the browser from a robot (ex Slurp,GoogleBot)?
	* @return boolean True if the browser is from a robot otherwise false
	*/
	public function isRobot() { return $this->_is_robot; }
	/**
	* Set the browser to be from AOL
	* @param $isAol
	*/
	public function setAol($isAol) { $this->_is_aol = $isAol; }
	/**
	 * Set the Browser to be mobile
	 * @param boolean $value is the browser a mobile brower or not
	 */
	protected function setMobile($value=true) { $this->_is_mobile = $value; }
	/**
	 * Set the Browser to be a robot
	 * @param boolean $value is the browser a robot or not
	 */
	protected function setRobot($value=true) { $this->_is_robot = $value; }
	/**
	* Get the user agent value in use to determine the browser
	* @return string The user agent from the HTTP header
	*/
	public function getUserAgent() { return $this->_agent; }
	/**
	* Set the user agent value (the construction will use the HTTP header value - this will overwrite it)
	* @param $agent_string The value for the User Agent
	*/
	public function setUserAgent($agent_string) {
		$this->reset();
		$this->_agent = $agent_string;
		$this->determine();
	}
	/**
	 * Used to determine if the browser is actually "chromeframe"
	 * @since 1.7
	 * @return boolean True if the browser is using chromeframe
	 */
	public function isChromeFrame() {
		return( strpos($this->_agent,"chromeframe") !== false );
	}
	/**
	* Returns a formatted string with a summary of the details of the browser.
	* @return string formatted string with a summary of the browser
	*/
	public function __toString() {
		return "<strong>Browser Name:</strong>{$this->getBrowser()}<br />\n" .
		       "<strong>Browser Version:</strong>{$this->getVersion()}<br />\n" .
		       "<strong>Browser Sprache:</strong>{$this->getLang()}<br />\n" .
		       "<strong>Browser User Agent String:</strong>{$this->getUserAgent()}<br />\n" .
		       "<strong>Platform:</strong>{$this->getPlatform()}<br />\n" .
		       "<strong>PlatformVersion:</strong>{$this->getPlatformVersion()}<br />";
	}
	
	/**
	 * Protected routine to calculate and determine what the browser is in use (including platform)
	 */
	protected function determine() {
		$this->checkPlatform();
		$this->checkBrowsers();
		$this->checkForAol();
		$this->reduceVersion(); //modified for compatibility
		$this->checkPlatformVersion();	//add BugBuster
	}
	
	/**
	 * Modify version for compatibility
	 */
	protected function reduceVersion() {
	    if ($this->_version === self::VERSION_UNKNOWN) {
	    	return ;
	    }
	    if (stripos($this->_version,'.') !== false ) {
	    	$this->_version = substr($this->_version,0,stripos($this->_version,'.')+2);
	    }
	}
	
	/**
	 * Protected routine to determine the browser type
	 * http://www.useragentstring.com/index.php
	 * 
	 * @return boolean True if the browser was detected otherwise false
	 */
	 protected function checkBrowsers() {
		return (
			// well-known, well-used
			// Special Notes:
			// (1) Opera must be checked before FireFox due to the odd
			//     user agents used in some older versions of Opera
			// (2) WebTV is strapped onto Internet Explorer so we must
			//     check for WebTV before IE
			// (3) (deprecated) Galeon is based on Firefox and needs to be
			//     tested before Firefox is tested
			// (4) OmniWeb is based on Safari so OmniWeb check must occur
			//     before Safari
			// (5) Netscape 9+ is based on Firefox so Netscape checks
			//     before FireFox are necessary
			$this->checkBrowserWebTv() ||
		    $this->checkBrowserMaxthon()    ||  //add BugBuster, must be before IE, (Dual Engine: Webkit and Trident)
			$this->checkBrowserInternetExplorer() ||
			$this->checkBrowserGaleon() ||
			$this->checkBrowserNetscapeNavigator9Plus() ||
			$this->checkBrowserFirefox()    ||
			$this->checkBrowserSongbird()   ||	//add BugBuster
			$this->checkBrowserSeaMonkey()  ||	//add BugBuster
			$this->checkBrowserChromePlus() ||	//add BugBuster
		    $this->checkBrowserCoolNovo()   ||	//add BugBuster
		    $this->checkBrowserVivaldi()    ||  //add BugBuster
			
			$this->checkBrowserOmniWeb() ||

			// common mobile
			$this->checkBrowserAndroidSamsungGalaxy() ||  //add BugBuster
			$this->checkBrowserAndroidHTCDesire() ||      //add BugBuster
			$this->checkBrowserAndroidHTCMagic() ||       //add BugBuster
			$this->checkBrowserAndroidHTCSensation() ||   //add BugBuster
			$this->checkBrowserAndroidHTCNexusOne() ||    //add BugBuster
			$this->checkBrowserAndroidHTCWildfire() ||    //add BugBuster
			$this->checkBrowserAndroidAcerA500() ||       //add BugBuster
			$this->checkBrowserAndroidAcerA501() ||       //add BugBuster
			$this->checkBrowserAndroidSamsungNexusS()  || //add BugBuster
			$this->checkBrowserAndroidThinkPadTablet() || //add BugBuster
			$this->checkBrowserAndroidXoomTablet()     || //add BugBuster
			$this->checkBrowserAndroidAsusTransfomerPad() || //add BugBuster
			$this->checkBrowserAndroidKindleFire() ||      //add BugBuster

			//at last Android only!
			$this->checkBrowserAndroid() ||

			$this->checkBrowseriPad() ||
			$this->checkBrowseriPod() ||
			$this->checkBrowseriPhone() ||
			$this->checkBrowserBlackBerry() ||
			$this->checkBrowserNokia() ||

			// common bots
			//$this->checkBrowserGoogleBot() ||
			//$this->checkBrowserMSNBot() ||
			//$this->checkBrowserSlurp() ||
		    // chrome post Android Pads
		    $this->checkBrowserChrome() ||
			// WebKit base check (post mobile and others)
			$this->checkBrowserSafari() ||
			// Opera Mini must check post mobile
			$this->checkBrowserOpera() ||
				
			// everyone else
			$this->checkBrowserTOnline() ||
			$this->checkBrowserNetPositive() ||
			$this->checkBrowserFirebird() ||
			$this->checkBrowserKonqueror() ||
			$this->checkBrowserIcab() ||
			$this->checkBrowserPhoenix() ||
			$this->checkBrowserAmaya() ||
			$this->checkBrowserLynx() ||
			$this->checkBrowserShiretoko() ||
			$this->checkBrowserIceCat() ||
			//$this->checkBrowserW3CValidator() ||
			$this->checkBrowserIceweasel() || //why not?; BugBuster
			$this->checkBrowserHTTPRequest2() || // add BugBuster
			$this->checkBrowserMozilla() /* Mozilla is such an open standard that you must check it last */
		);
    }

    /**
     * Determine if the user is using a BlackBerry (last updated 1.7)
     * @return boolean True if the browser is the BlackBerry browser otherwise false
     */
    protected function checkBrowserBlackBerry() {
	    if( stripos($this->_agent,'blackberry') !== false ) {
		    $aresult = explode("/",stristr($this->_agent,"BlackBerry"));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->_browser_name = self::BROWSER_BLACKBERRY;
		    $this->setMobile(true);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the user is using an AOL User Agent (last updated 1.7)
     * @return boolean True if the browser is from AOL otherwise false
     */
    protected function checkForAol() {
		$this->setAol(false);
		$this->setAolVersion(self::VERSION_UNKNOWN);

		if( stripos($this->_agent,'aol') !== false ) {
		    $aversion = explode(' ',stristr($this->_agent, 'AOL'));
		    $this->setAol(true);
		    $this->setAolVersion(preg_replace('/[^0-9\.a-z]/i', '', $aversion[1]));
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is the GoogleBot or not (last updated 1.7)
     * @return boolean True if the browser is the GoogletBot otherwise false
     */
    protected function checkBrowserGoogleBot() {
	    if( stripos($this->_agent,'googlebot') !== false ) {
			$aresult = explode('/',stristr($this->_agent,'googlebot'));
			$aversion = explode(' ',$aresult[1]);
			$this->setVersion(str_replace(';','',$aversion[0]));
			$this->_browser_name = self::BROWSER_GOOGLEBOT;
			$this->setRobot(true);
			return true;
	    }
	    return false;
    }

	/**
     * Determine if the browser is the MSNBot or not (last updated 1.9)
     * @return boolean True if the browser is the MSNBot otherwise false
     */
	protected function checkBrowserMSNBot() {
		if( stripos($this->_agent,"msnbot") !== false ) {
			$aresult = explode("/",stristr($this->_agent,"msnbot"));
			$aversion = explode(" ",$aresult[1]);
			$this->setVersion(str_replace(";","",$aversion[0]));
			$this->_browser_name = self::BROWSER_MSNBOT;
			$this->setRobot(true);
			return true;
		}
		return false;
	}	    
    
    /**
     * Determine if the browser is the W3C Validator or not (last updated 1.7)
     * @return boolean True if the browser is the W3C Validator otherwise false
     */
    protected function checkBrowserW3CValidator() {
	    if( stripos($this->_agent,'W3C-checklink') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'W3C-checklink'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->_browser_name = self::BROWSER_W3CVALIDATOR;
		    return true;
	    }
	    else if( stripos($this->_agent,'W3C_Validator') !== false ) {
			// Some of the Validator versions do not delineate w/ a slash - add it back in
			$ua = str_replace("W3C_Validator ", "W3C_Validator/", $this->_agent);
		    $aresult = explode('/',stristr($ua,'W3C_Validator'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->_browser_name = self::BROWSER_W3CVALIDATOR;
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is the Yahoo! Slurp Robot or not (last updated 1.7)
     * @return boolean True if the browser is the Yahoo! Slurp Robot otherwise false
     */
    protected function checkBrowserSlurp() {
	    if( stripos($this->_agent,'slurp') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Slurp'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->_browser_name = self::BROWSER_SLURP;
			$this->setRobot(true);
			$this->setMobile(false);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Internet Explorer or not (last updated 1.7)
     * @return boolean True if the browser is Internet Explorer otherwise false
     */
    protected function checkBrowserInternetExplorer() 
    {
        $match = '';
        
	    // Test for v1 - v1.5 IE
	    if( stripos($this->_agent,'microsoft internet explorer') !== false ) 
	    {
		    $this->setBrowser(self::BROWSER_IE);
		    $this->setVersion('1.0');
		    $aresult = stristr($this->_agent, '/');
		    if( preg_match('/308|425|426|474|0b1/i', $aresult) ) {
			    $this->setVersion('1.5');
		    }
			return true;
	    }
	    // Test for versions > 1.5
	    else if( stripos($this->_agent,'msie')     !== false 
	          && stripos($this->_agent,'opera')    === false 
	          && stripos($this->_agent,'iemobile') === false ) 
	    {
	    	/*// See if the browser is the odd MSN Explorer
	    	if( stripos($this->_agent,'msnb') !== false ) {
		    	$aresult = explode(' ',stristr(str_replace(';','; ',$this->_agent),'MSN'));
			    $this->setBrowser( self::BROWSER_MSN );
			    $this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
			    return true;
	    	} */
	    	$aresult = explode(' ',stristr(str_replace(';','; ',$this->_agent),'msie'));
	    	$this->setBrowser( self::BROWSER_IE );
	    	$this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
	    	return true;
	    }
	    // Test for versions for Edge
	    else if ( stripos($this->_agent,'Edge')          !== false 
	    	   && stripos($this->_agent,'windows phone') === false ) 
	    {
	        $aresult = explode('/',stristr($this->_agent,'Edge'));
	        $aversion = explode('.',$aresult[1]);
	        $this->setVersion($aversion[0]);
	        $this->setBrowser(self::BROWSER_MS_EDGE);
	        return true;
	    }
	    // Test for versions for Edge mobile
	    else if ( stripos($this->_agent,'Edge')          !== false
	           && stripos($this->_agent,'windows phone') !== false )
	    {
	        $aresult = explode('/',stristr($this->_agent,'Edge'));
	        $aversion = explode('.',$aresult[1]);
	        $this->setVersion($aversion[0]);
	        $this->setBrowser(self::BROWSER_MS_EDGE_MOBILE);
	        $this->setPlatform( self::PLATFORM_WINDOWS_PHONE );
	        $this->setMobile(true);
	        return true;
	    }
	    
	    // Test for versions > 10
	    else if (  preg_match('/Trident\/[0-9\.]+/', $this->_agent) 
	            && preg_match('/rv:([0-9\.]+)/', $this->_agent, $match) )
	    {
	        $this->setBrowser( self::BROWSER_IE );
	        $this->setVersion($match[1]);
	        return true;
	    }
	    else if (stripos($this->_agent,'iemobile')      !== false
	          && stripos($this->_agent,'windows phone') !== false ) 
	    { // Windows Phones mit IEMobile
	        $this->setPlatform( self::PLATFORM_WINDOWS_PHONE );
	        $this->setBrowser( self::BROWSER_IE_MOBILE );
	        $this->setMobile(true);
	        $aresult = explode(' ',stristr(str_replace('/',' ',$this->_agent),'iemobile'));
	        $this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
	        return true;
	    }
	    else if (stripos($this->_agent,'iemobile')      !== false
	          && stripos($this->_agent,'windows phone') === false )
	    { // Irgendwas mit IEMobile
    	    $this->setBrowser( self::BROWSER_IE_MOBILE );
    	    $this->setMobile(true);
    	    $aresult = explode(' ',stristr(str_replace('/',' ',$this->_agent),'iemobile'));
    	    $this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
    	    return true;
	    }
	    // Test for Pocket IE
	    else if( stripos($this->_agent,'mspie') !== false || stripos($this->_agent,'pocket') !== false ) 
	    {
		    $aresult = explode(' ',stristr($this->_agent,'mspie'));
		    $this->setPlatform( self::PLATFORM_WINDOWS_CE );
		    $this->setBrowser( self::BROWSER_POCKET_IE );
		    $this->setMobile(true);

		    if( stripos($this->_agent,'mspie') !== false ) {
			    $this->setVersion($aresult[1]);
		    }
		    else {
			    $aversion = explode('/',$this->_agent);
			    $this->setVersion($aversion[1]);
		    }
		    return true;
	    }
		return false;
    }

    /**
     * Determine if the browser is Opera or not (last updated 1.7)
     * @return boolean True if the browser is Opera otherwise false
     */
    protected function checkBrowserOpera() {
	    if( stripos($this->_agent,'opera mini') !== false ) {
		    $resultant = stristr($this->_agent, 'opera mini');
		    if( preg_match('/\//',$resultant) ) {
			    $aresult = explode('/',$resultant);
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
			}
		    else {
			    $aversion = explode(' ',stristr($resultant,'opera mini'));
			    $this->setVersion($aversion[1]);
		    }
		    $this->_browser_name = self::BROWSER_OPERA_MINI;
			$this->setMobile(true);
			return true;
	    }
	    else if( stripos($this->_agent,'opera') !== false ) {
		    $resultant = stristr($this->_agent, 'opera');
		    if( preg_match('/Version\/(10.*)$/',$resultant,$matches) ) {
			    $this->setVersion($matches[1]);
		    }
		    if( preg_match('/Version\/(11.*)$/',$resultant,$matches) ) {
			    $this->setVersion($matches[1]);
		    }
		    else if( preg_match('/\//',$resultant) ) {
			    $aresult = explode('/',str_replace("("," ",$resultant));
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $aversion = explode(' ',stristr($resultant,'opera'));
			    $this->setVersion(isset($aversion[1])?$aversion[1]:"");
		    }
		    $this->_browser_name = self::BROWSER_OPERA;
		    return true;
	    }
		return false;
    }

    /**
     * Determine if the browser is Chrome or not (last updated 1.7)
     * @return boolean True if the browser is Chrome otherwise false
     */
    protected function checkBrowserChrome() {
	    if( stripos($this->_agent,'Chrome') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Chrome'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_CHROME);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Vivaldi or not 
     * @return boolean True if the browser is Vivaldi otherwise false
     */
    protected function checkBrowserVivaldi() {
        if( stripos($this->_agent,'Vivaldi') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Vivaldi'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_VIVALDI);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is WebTv or not (last updated 1.7)
     * @return boolean True if the browser is WebTv otherwise false
     */
    protected function checkBrowserWebTv() {
	    if( stripos($this->_agent,'webtv') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'webtv'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_WEBTV);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is NetPositive or not (last updated 1.7)
     * @return boolean True if the browser is NetPositive otherwise false
     */
    protected function checkBrowserNetPositive() {
	    if( stripos($this->_agent,'NetPositive') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'NetPositive'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion(str_replace(array('(',')',';'),'',$aversion[0]));
		    $this->setBrowser(self::BROWSER_NETPOSITIVE);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Galeon or not (last updated 1.7)
     * @return boolean True if the browser is Galeon otherwise false
     */
    protected function checkBrowserGaleon() {
	    if( stripos($this->_agent,'galeon') !== false ) {
		    $aresult = explode(' ',stristr($this->_agent,'galeon'));
		    $aversion = explode('/',$aresult[0]);
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_GALEON);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Konqueror or not (last updated 1.7)
     * @return boolean True if the browser is Konqueror otherwise false
     */
    protected function checkBrowserKonqueror() {
	    if( stripos($this->_agent,'Konqueror') !== false ) {
		    $aresult = explode(' ',stristr($this->_agent,'Konqueror'));
		    $aversion = explode('/',$aresult[0]);
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_KONQUEROR);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is iCab or not (last updated 1.7)
     * @return boolean True if the browser is iCab otherwise false
     */
    protected function checkBrowserIcab() {
	    if( stripos($this->_agent,'icab') !== false ) {
		    $aversion = explode(' ',stristr(str_replace('/',' ',$this->_agent),'icab'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_ICAB);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is OmniWeb or not (last updated 1.7)
     * @return boolean True if the browser is OmniWeb otherwise false
     */
    protected function checkBrowserOmniWeb() {
	    if( stripos($this->_agent,'omniweb') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'omniweb'));
		    $aversion = explode(' ',isset($aresult[1])?$aresult[1]:"");
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_OMNIWEB);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Phoenix or not (last updated 1.7)
     * @return boolean True if the browser is Phoenix otherwise false
     */
    protected function checkBrowserPhoenix() {
	    if( stripos($this->_agent,'Phoenix') !== false ) {
		    $aversion = explode('/',stristr($this->_agent,'Phoenix'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_PHOENIX);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Firebird or not (last updated 1.7)
     * @return boolean True if the browser is Firebird otherwise false
     */
    protected function checkBrowserFirebird() {
	    if( stripos($this->_agent,'Firebird') !== false ) {
		    $aversion = explode('/',stristr($this->_agent,'Firebird'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_FIREBIRD);
			return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Netscape Navigator 9+ or not (last updated 1.7)
	 * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008)
     * @return boolean True if the browser is Netscape Navigator 9+ otherwise false
     */
    protected function checkBrowserNetscapeNavigator9Plus() {
	    if( stripos($this->_agent,'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i',$this->_agent,$matches) ) {
		    $this->setVersion($matches[1]);
		    $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
		    return true;
	    }
	    else if( stripos($this->_agent,'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i',$this->_agent,$matches) ) {
		    $this->setVersion($matches[1]);
		    $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Shiretoko or not (https://wiki.mozilla.org/Projects/shiretoko) (last updated 1.7)
     * @return boolean True if the browser is Shiretoko otherwise false
     */
    protected function checkBrowserShiretoko() {
	    if( stripos($this->_agent,'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i',$this->_agent,$matches) ) {
		    $this->setVersion($matches[1]);
		    $this->setBrowser(self::BROWSER_SHIRETOKO);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Ice Cat or not (http://en.wikipedia.org/wiki/GNU_IceCat) (last updated 1.7)
     * @return boolean True if the browser is Ice Cat otherwise false
     */
    protected function checkBrowserIceCat() {
	    if( stripos($this->_agent,'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i',$this->_agent,$matches) ) {
		    $this->setVersion($matches[1]);
		    $this->setBrowser(self::BROWSER_ICECAT);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Nokia or not (last updated 1.7)
     * @return boolean True if the browser is Nokia otherwise false
     */
    protected function checkBrowserNokia() {
	    if( preg_match("/Nokia([^\/]+)\/([^ SP]+)/i",$this->_agent,$matches) ) {
		    $this->setVersion($matches[2]);
			if( stripos($this->_agent,'Series60') !== false || strpos($this->_agent,'S60') !== false ) {
				$this->setBrowser(self::BROWSER_NOKIA_S60);
			}
			else {
				$this->setBrowser( self::BROWSER_NOKIA );
			}
		    $this->setMobile(true);
		    return true;
	    }
		return false;
    }

    /**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserFirefox() {
	    if( stripos($this->_agent,'safari') === false ) {
			if( preg_match("/Firefox[\/ \(]([^ ;\)]+)/i",$this->_agent,$matches) ) {
				$this->setVersion($matches[1]);
				$this->setBrowser(self::BROWSER_FIREFOX);
				return true;
			}
			else if( preg_match("/Firefox$/i",$this->_agent,$matches) ) {
				$this->setVersion("");
				$this->setBrowser(self::BROWSER_FIREFOX);
				return true;
			}
		}
	    return false;
    }

	/**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserIceweasel() {
		if( stripos($this->_agent,'Iceweasel') !== false ) {
			$aresult = explode('/',stristr($this->_agent,'Iceweasel'));
			$aversion = explode(' ',$aresult[1]);
			$this->setVersion($aversion[0]);
			$this->setBrowser(self::BROWSER_ICEWEASEL);
			return true;
		}
		return false;
	}
	
	/**
     * Determine if the browser is Songbird or not, add by BugBuster
     * @return boolean True if the browser is Songbird otherwise false
     */
    protected function checkBrowserSongbird() {
	    if( stripos($this->_agent,'Songbird') !== false ) {
		    $aversion = explode('/',stristr($this->_agent,'Songbird'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_SONGBIRD);
			return true;
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is Songbird or not, add by BugBuster
     * @return boolean True if the browser is Songbird otherwise false
     */
    protected function checkBrowserSeaMonkey() {
	    if( stripos($this->_agent,'SeaMonkey') !== false ) {
		    $aversion = explode('/',stristr($this->_agent,'SeaMonkey'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_SEAMONKEY);
			return true;
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is Mozilla or not (last updated 1.7)
     * @return boolean True if the browser is Mozilla otherwise false
     */
    protected function checkBrowserMozilla() {
	    if( stripos($this->_agent,'mozilla') !== false  && preg_match('/rv:[0-9].[0-9][a-b]?/i',$this->_agent) && stripos($this->_agent,'netscape') === false) {
		    $aversion = explode(' ',stristr($this->_agent,'rv:'));
		    preg_match('/rv:[0-9].[0-9][a-b]?/i',$this->_agent,$aversion);
		    $this->setVersion(str_replace('rv:','',$aversion[0]));
		    $this->setBrowser(self::BROWSER_MOZILLA);
		    return true;
	    }
	    else if( stripos($this->_agent,'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i',$this->_agent) && stripos($this->_agent,'netscape') === false ) {
		    $aversion = explode('',stristr($this->_agent,'rv:'));
		    $this->setVersion(str_replace('rv:','',$aversion[0]));
		    $this->setBrowser(self::BROWSER_MOZILLA);
		    return true;
	    }
	    else if( stripos($this->_agent,'mozilla') !== false  && preg_match('/mozilla\/([^ ]*)/i',$this->_agent,$matches) && stripos($this->_agent,'netscape') === false ) {
		    $this->setVersion($matches[1]);
		    $this->setBrowser(self::BROWSER_MOZILLA);
		    return true;
	    }
		return false;
    }
    
    /**
     * Determine if the browser is T-Online Browser or not, add by BugBuster
     * @return boolean True if the browser is T-Online Browser otherwise false
     */
    protected function checkBrowserTOnline() {
	    if( stripos($this->_agent,'T-Online Browser') !== false ) {
		    $this->setBrowser(self::BROWSER_TONLINE);
			return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Lynx or not (last updated 1.7)
     * @return boolean True if the browser is Lynx otherwise false
     */
    protected function checkBrowserLynx() {
	    if( stripos($this->_agent,'lynx') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Lynx'));
		    $aversion = explode(' ',(isset($aresult[1])?$aresult[1]:""));
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_LYNX);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Amaya or not (last updated 1.7)
     * @return boolean True if the browser is Amaya otherwise false
     */
    protected function checkBrowserAmaya() {
	    if( stripos($this->_agent,'amaya') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Amaya'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_AMAYA);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Safari or not (last updated 1.7)
     * @return boolean True if the browser is Safari otherwise false
     */
    protected function checkBrowserSafari() 
    {
	    if( stripos($this->_agent,'Safari') !== false 
	     && stripos($this->_agent,'iPhone') === false 
	     && stripos($this->_agent,'iPod') === false ) 
	{
		    $aresult = explode('/',stristr($this->_agent,'Version'));
		    if( isset($aresult[1]) ) {
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $this->setVersion(self::VERSION_UNKNOWN);
		    }
		    $this->setBrowser(self::BROWSER_SAFARI);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is iPhone or not (last updated 1.7)
     * @return boolean True if the browser is iPhone otherwise false
     */
    protected function checkBrowseriPhone() {
	    if( stripos($this->_agent,'iPhone') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Version'));
		    if( isset($aresult[1]) ) {
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $this->setVersion(self::VERSION_UNKNOWN);
		    }
		    $this->setMobile(true);
		    $this->setBrowser(self::BROWSER_IPHONE);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is iPod or not (last updated 1.7)
     * @return boolean True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPad() {
	    if( stripos($this->_agent,'iPad') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Version'));
		    if( isset($aresult[1]) ) {
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $this->setVersion(self::VERSION_UNKNOWN);
		    }
		    $this->setMobile(true);
		    $this->setBrowser(self::BROWSER_IPAD);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is iPod or not (last updated 1.7)
     * @return boolean True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPod() {
	    if( stripos($this->_agent,'iPod') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'Version'));
		    if( isset($aresult[1]) ) {
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $this->setVersion(self::VERSION_UNKNOWN);
		    }
		    $this->setMobile(true);
		    $this->setBrowser(self::BROWSER_IPOD);
		    return true;
	    }
	    return false;
    }

    /**
     * Determine if the browser is Android or not (last updated 1.7)
     * @return boolean True if the browser is Android otherwise false
     */
    protected function checkBrowserAndroid() {
	    if( stripos($this->_agent,'Android') !== false ) {
		    $aresult = explode(' ',stristr($this->_agent,'Android'));
		    if( isset($aresult[1]) ) {
			    $aversion = explode(' ',$aresult[1]);
			    $this->setVersion($aversion[0]);
		    }
		    else {
			    $this->setVersion(self::VERSION_UNKNOWN);
		    }
		    $this->setMobile(true);
		    $this->setBrowser(self::BROWSER_ANDROID);
		    return true;
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is Android and Samsung Galaxy or not, add by BugBuster
     * @return boolean True if the browser is Samsung Galaxy otherwise false
     */
    protected function checkBrowserAndroidSamsungGalaxy() 
    {
	    if( stripos($this->_agent,'Android') !== false ) 
	    {
	        $this->setVersion(self::VERSION_UNKNOWN);
	        $this->setMobile(true);
	        
	    	if( stripos($this->_agent,'GT-I9000') !== false ) 
	    	{
    		    $this->setBrowser(self::BROWSER_GALAXY_S);
			    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9001') !== false ) 
	    	{
			    $this->setBrowser(self::BROWSER_GALAXY_S_PLUS);
			    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9100') !== false ) 
	    	{
			    $this->setBrowser(self::BROWSER_GALAXY_S_II);
			    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9300') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S_III);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I8190') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S_III_MINI);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9301') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S_III_NEO);
	    	    return true;
	    	}
	    	//S4
	    	if( stripos($this->_agent,'GT-I9500') !== false ||
	    	    stripos($this->_agent,'GT-I9505') !== false ||
	    	    stripos($this->_agent,'GT-I9506') !== false ||
	    	    stripos($this->_agent,'GT-I9515') !== false  )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S4);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9195') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S4_MINI);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9295') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S4_ACTIVE);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-C101') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S4_ZOOM);
	    	    return true;
	    	}
	    	//S5
	    	if( stripos($this->_agent,'SM-G900') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S5);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G800') !== false ) 
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S5_MINI);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G870') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S5_ACTIVE);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-C115') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S5_ZOOM);
	    	    return true;
	    	}
	    	//S6 
	    	if( stripos($this->_agent,'SM-G920') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S6);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G890') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S6_ACTIVE);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G9198') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S6_MINI);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G925') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S6_EDGE);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'SM-G928') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_S6_EDGE_P);
	    	    return true;
	    	}
	    	
	    	
	    	if( stripos($this->_agent,'GT-S5830') !== false ) 
	    	{
			    $this->setBrowser(self::BROWSER_GALAXY_ACE);
			    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I8160') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_ACE_2);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-S7500') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_ACE_PLUS);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-I9250') !== false ||
	    	    stripos($this->_agent,'Galaxy Nexus Build') !== false ) 
	    	{
	    	    $this->setBrowser(self::BROWSER_SAMSUNG_GALAXY_NEXUS);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-N7000') !== false )
	    	{
	    	    $this->setBrowser(self::BROWSER_GALAXY_NOTE);
	    	    return true;
	    	}
	    	if( stripos($this->_agent,'GT-P1000') !== false ||
	    		stripos($this->_agent,'GT-P1010') !== false ||
	    		stripos($this->_agent,'GT-P7100') !== false ||
	    		stripos($this->_agent,'GT-P7300') !== false ||
	    		stripos($this->_agent,'GT-P7510') !== false ||
	    		stripos($this->_agent,'GT-P6200') !== false ||
	    		stripos($this->_agent,'GT-P6210') !== false )
	    	{
			    $this->setBrowser(self::BROWSER_GALAXY_TAB);
			    return true;
	    	}
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is Android and HTC Desire or not, add by BugBuster
     * @return boolean True if the browser is HTC Desire otherwise false
     */
    protected function checkBrowserAndroidHTCDesire()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'HTC_DesireHD') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_DESIRE_HD);
                return true;
            }
            if( stripos($this->_agent,'HTC Desire Z') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_DESIRE_Z);
                return true;
            }
            if( stripos($this->_agent,'HTC_DesireS') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_DESIRE_S);
                return true;
            }
            if( stripos($this->_agent,'HTC_Desire')   !== false ||
                stripos($this->_agent,'HTC Desire')   !== false ||
            	stripos($this->_agent,'Desire_A8181') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_DESIRE);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is Android and HTC Magic or not, add by BugBuster
     * @return boolean True if the browser is HTC Magic otherwise false
     */
    protected function checkBrowserAndroidHTCMagic()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'HTC Magic') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_MAGIC);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is Android and HTC Nexus One (4Google) or not, add by BugBuster
     * @return boolean True if the browser is HTC Nexus One otherwise false
     */
    protected function checkBrowserAndroidHTCNexusOne()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'Nexus One') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_NEXUS_ONE);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is Android and Samsung Nexus S (4Google) or not, add by BugBuster
     * @return boolean True if the browser is Samsung Nexus S otherwise false
     */
    protected function checkBrowserAndroidSamsungNexusS()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'Nexus S Build') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_SAMSUNG_NEXUS_S);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is Android and HTC WildfireS A510e or not, add by BugBuster
     * @return boolean True if the browser is HTC WildfireS A510e otherwise false
     */
    protected function checkBrowserAndroidHTCWildfire()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'HTC_WildfireS_A510e') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_WILDFIRES_A510E);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is Android and HTC Sensation or not, add by BugBuster
     * @return boolean True if the browser is HTC Sensation otherwise false
     */
    protected function checkBrowserAndroidHTCSensation()
    {
        if( stripos($this->_agent,'Android') !== false ||
        	stripos($this->_agent,'Macintosh') !== false )
        {
        	if( stripos($this->_agent,'HTC_SensationXE')  !== false ||
        		stripos($this->_agent,'HTC Sensation XE') !== false )
        	{
        	    $this->setVersion(self::VERSION_UNKNOWN);
        	    $this->setMobile(true);
        	    $this->setBrowser(self::BROWSER_HTC_SENSATION_XE);
        	    return true;
        	}
        	if( stripos($this->_agent,'HTC_SensationXL')  !== false ||
        	    stripos($this->_agent,'HTC Sensation XL') !== false ||
        	    stripos($this->_agent,'HTC_Runnymede') !== false ) //usa name
        	{
        	    $this->setVersion(self::VERSION_UNKNOWN);
        	    $this->setMobile(true);
        	    $this->setBrowser(self::BROWSER_HTC_SENSATION_XL);
        	    return true;
        	}
        	if( stripos($this->_agent,'HTC_Sensation_Z710') !== false )
        	{
        	    $this->setVersion(self::VERSION_UNKNOWN);
        	    $this->setMobile(true);
        	    $this->setBrowser(self::BROWSER_HTC_SENSATION_Z710);
        	    return true;
        	}
        	if( stripos($this->_agent,'HTC Sensation') !== false ||
        		stripos($this->_agent,'HTC_Sensation') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_HTC_SENSATION);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is ChromePlus or not, add by BugBuster
     * @return boolean True if the browser is ChromePlus otherwise false
     */
    protected function checkBrowserChromePlus() {
	    if( stripos($this->_agent,'ChromePlus') !== false ) {
		    $aresult = explode('/',stristr($this->_agent,'ChromePlus'));
		    $aversion = explode(' ',$aresult[1]);
		    $this->setVersion($aversion[0]);
		    $this->setBrowser(self::BROWSER_CHROME_PLUS);
		    return true;
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is CoolNovo (previous ChromePlus) or not, add by BugBuster
     * @return boolean True if the browser is CoolNovo otherwise false
     */
    protected function checkBrowserCoolNovo() {
        if( stripos($this->_agent,'CoolNovo') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'CoolNovo'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_COOL_NOVO);
            return true;
        }
        return false;
    }
    
    /**
     * Determine if the browser is Maxthon or not, add by BugBuster
     * @return boolean True if the browser is Maxthon otherwise false
     */
    protected function checkBrowserMaxthon() {
        if( stripos($this->_agent,'Maxthon') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Maxthon'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_COOL_MAXTHON);
            return true;
        }
        return false;
    }
    
    /**
     * Determine if the browser is Pear HTTP_Request2 or not, add by BugBuster
     * @return boolean True if the browser is Pear HTTP_Request2 otherwise false
     */
    protected function checkBrowserHTTPRequest2() {
	    if( stripos($this->_agent,'HTTP_Request2') !== false ) {
		    $aversion = explode('/',stristr($this->_agent,'HTTP_Request2'));
		    $this->setVersion($aversion[1]);
		    $this->setBrowser(self::BROWSER_HTTP_REQUEST2);
		    return true;
	    }
	    return false;
    }
    
    /**
     * Determine if the browser is an Acer Iconia A500 Tablet or not, add by BugBuster
     * @return boolean True if the browser is an Acer Iconia A500 Tablet otherwise false
     */
    protected function checkBrowserAndroidAcerA500()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'A500 Build') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_ACER_A500);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is an Acer A501 Tablet or not, add by BugBuster
     * @return boolean True if the browser is an Acer A501 Tablet otherwise false
     */
    protected function checkBrowserAndroidAcerA501()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'A501 Build') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_ACER_A501);
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the browser is a Lenovo ThinkPad Tablet or not, add by BugBuster
     * @return boolean True if the browser is a Lenovo ThinkPad Tablet otherwise false
     */
    protected function checkBrowserAndroidThinkPadTablet()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'ThinkPad Tablet') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_LENOVO_THINKPAD_TABLET);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is a Motorola Xoom Tablet or not, add by BugBuster
     * @return boolean True if the browser is a Motorola Xoom Tablet otherwise false
     */
    protected function checkBrowserAndroidXoomTablet()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'Xoom Build') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_MOTOROLA_XOOM_TABLET);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is a Asus Transfomer Pad or not, add by BugBuster
     * @return boolean True if the browser is a Asus Transfomer Pad otherwise false
     */
    protected function checkBrowserAndroidAsusTransfomerPad()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'ASUS Transformer Pad') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setMobile(true);
                $this->setBrowser(self::BROWSER_ASUS_TRANSFORMER_PAD);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine if the browser is a Kindle Fire with Silk Browser (Safari) or not, add by BugBuster
     * @return boolean True if the browser is a Kindle Fire otherwise false
     */
    protected function checkBrowserAndroidKindleFire()
    {
        if( stripos($this->_agent,'Android') !== false )
        {
            if( stripos($this->_agent,'Silk') !== false )
            {
                $this->setVersion(self::VERSION_UNKNOWN);
                $this->setBrowser(self::BROWSER_KINDLE_FIRE);
                $this->setMobile(true);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine the user's platform (last updated 1.7)
     */
    protected function checkPlatform() 
    {
        if( stripos($this->_agent, 'iPad') !== false ) 
        {
		    $this->_platform = self::PLATFORM_APPLE; // iOS folgt spaeter
	    }
	    elseif( stripos($this->_agent, 'iPod') !== false ) 
	    {
		    $this->_platform = self::PLATFORM_APPLE; // iOS folgt spaeter
	    }
	    elseif( stripos($this->_agent, 'iPhone') !== false ) 
	    {
		    $this->_platform = self::PLATFORM_APPLE; // iOS folgt spaeter
	    }
	    elseif( stripos($this->_agent, 'android') !== false ) {
		    $this->_platform = self::PLATFORM_ANDROID;
	    }
	    elseif( stripos($this->_agent, 'mac') !== false ) {
	        $this->_platform = self::PLATFORM_APPLE;
	    }
	    elseif( stripos($this->_agent, 'linux') !== false ) {
		    $this->_platform = self::PLATFORM_LINUX;
	    }
	    elseif( stripos($this->_agent, 'windows phone') !== false ) {
	        $this->_platform = self::PLATFORM_WINDOWS_PHONE;
	    }
	    elseif( stripos($this->_agent, 'Nokia') !== false ) {
		    $this->_platform = self::PLATFORM_NOKIA;
	    }
	    elseif( stripos($this->_agent, 'BlackBerry') !== false ) {
		    $this->_platform = self::PLATFORM_BLACKBERRY;
	    }
	    elseif( stripos($this->_agent, 'windows ce') !== false ) {
	        $this->_platform = self::PLATFORM_WINDOWS_CE;
	    }
	    elseif( stripos($this->_agent, 'windows') !== false ) {
	        $this->_platform = self::PLATFORM_WINDOWS;
	    }
	    elseif( stripos($this->_agent,'FreeBSD') !== false ) {
		    $this->_platform = self::PLATFORM_FREEBSD;
	    }
	    elseif( stripos($this->_agent,'OpenBSD') !== false ) {
		    $this->_platform = self::PLATFORM_OPENBSD;
	    }
	    elseif( stripos($this->_agent,'NetBSD') !== false ) {
		    $this->_platform = self::PLATFORM_NETBSD;
	    }
	    elseif( stripos($this->_agent, 'OpenSolaris') !== false ) {
		    $this->_platform = self::PLATFORM_OPENSOLARIS;
	    }
	    elseif( stripos($this->_agent, 'SunOS') !== false ) {
		    $this->_platform = self::PLATFORM_SUNOS;
	    }
	    elseif( stripos($this->_agent, 'OS/2') !== false ) {
		    $this->_platform = self::PLATFORM_OS2;
	    }
	    elseif( stripos($this->_agent, 'BeOS') !== false ) {
		    $this->_platform = self::PLATFORM_BEOS;
	    }
	    elseif( stripos($this->_agent, 'win') !== false ) {
		    $this->_platform = self::PLATFORM_WINDOWS;
	    }
	    // add BugBuster
	    elseif( stripos($this->_agent, 'PHP') !== false ) {
		    $this->_platform = self::PLATFORM_PHP;
	    }
	    // add BugBuster
	    elseif( stripos($this->_agent, 'PLAYSTATION') !== false ) {
	        $this->_platform = self::PLATFORM_PLAYSTATION;
	    }

    }
    
    /**
	* The name of the platform.  All return types are from the class contants
	* Fallback platformVersion with platform if platformVersion unknown
	* @return string Platformversion of the browser
	*/
	public function getPlatformVersion() { 
		if ($this->_platformVersion === self::PLATFORM_UNKNOWN ) 
		{
			$this->_platformVersion = $this->_platform;
		}
		return $this->_platformVersion; 
	}
    /**
     * Improved checkPlatform with Windows Plattform Details
     * and Mac OS X
     * BugBuster (Glen Langer)
     */
    protected function checkPlatformVersion() 
    {
        // based on browscap.ini
        if ($this->_platform == self::PLATFORM_WINDOWS) 
        {
	        /*if( stripos($this->_agent, 'windows NT 7.1') !== false ) {
			    $this->_platform = self::PLATFORM_WINDOWS_7;
		    }
	        else*/
            if( stripos($this->_agent, 'windows NT 10.0') !== false ) 
            {
                $this->_platformVersion = self::PLATFORM_WINDOWS_10;
            }
            elseif( stripos($this->_agent, 'windows NT 6.3') !== false ) 
            {
                $this->_platformVersion = self::PLATFORM_WINDOWS_81;
                if( stripos($this->_agent, 'arm') !== false )
                {
                    $this->_platformVersion = self::PLATFORM_WINDOWS_RT;
                }
            }
            elseif( stripos($this->_agent, 'windows NT 6.2') !== false ) 
            {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_8;
			    if( stripos($this->_agent, 'arm') !== false ) 
			    {
			        $this->_platformVersion = self::PLATFORM_WINDOWS_RT;
			    }
		    }
		    elseif( stripos($this->_agent, 'windows NT 6.1') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_7;
		    }
		    elseif( stripos($this->_agent, 'windows NT 6.0') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_VISTA;
		    }
		    elseif( stripos($this->_agent, 'windows NT 5.2') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_2003;
		    }
		    elseif( stripos($this->_agent, 'windows NT 5.1') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_XP;
		    }
		    elseif( stripos($this->_agent, 'windows XP') !== false ) {
		        $this->_platformVersion = self::PLATFORM_WINDOWS_XP;
		    }
		    elseif( stripos($this->_agent, 'windows NT 5.0') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_2000;
		    }
		    elseif( stripos($this->_agent, 'windows NT 4.0') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_NT;
		    }
		    elseif( stripos($this->_agent, 'windows Me') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_ME;
		    }
		    elseif( stripos($this->_agent, 'windows 98') !== false ) {
			    $this->_platformVersion = self::PLATFORM_WINDOWS_98;
		    }
		    elseif( stripos($this->_agent, 'windows 95') !== false ) {
		        $this->_platformVersion = self::PLATFORM_WINDOWS_95;
		    }
        }

        if ($this->_platform == self::PLATFORM_WINDOWS_PHONE)
        {
            if ( stripos($this->_agent, 'Windows Phone OS') !== false )
            {
                $aresult = explode(' ',stristr($this->_agent,'Windows Phone OS'));
                $this->_platformVersion = self::PLATFORM_WINDOWS_PHONE .' '. str_replace(array('(',')',';'),'',$aresult[3]);
            }
            elseif ( stripos($this->_agent, 'Windows Phone') !== false )
            {
                $aresult = explode(' ',stristr($this->_agent,'Windows Phone'));
                $this->_platformVersion = self::PLATFORM_WINDOWS_PHONE .' '. str_replace(array('(',')',';'),'',$aresult[2]);
            }
        }
        
        if ($this->_platform == self::PLATFORM_APPLE)
        {
            if ( stripos($this->_agent, 'Mac OS X') !== false ) 
            {
                $this->_platformVersion = self::PLATFORM_MACOSX;
            }
            if ( stripos($this->_agent, 'iPad') !== false 
              || stripos($this->_agent, 'iPod') !== false
              || stripos($this->_agent, 'iPhone') !== false) 
            {
                $this->_platformVersion = self::PLATFORM_IOSX;
            }
        }
	    elseif( stripos($this->_agent, 'Warp 4') !== false ) {
		    $this->_platformVersion = self::PLATFORM_WARP4;
	    }
    }
    
    /**
     * Ermittle akzeptierten Sprachen
     * 
     * @return bool	true
     * @access protected
     * 
     */
    protected function setLang() 
    {
        $array = explode(",", $this->_accept_language);
        $ca = count($array);
        for($i = 0; $i < $ca; $i++) 
        {
            //Konqueror
            $array[$i] = str_replace(" ", null, $array[$i]);
            $array[$i] = substr($array[$i], 0, 2);
            $array[$i] = strtolower($array[$i]);
        }
        $array = array_unique($array);
        $this->_lang = strtoupper($array[0]);
        if(empty($this->_lang) || strlen($this->_lang) < 2) 
        {
        	$this->_lang = 'Unknown';
        }
        return true;
    }
    
    public function getLang() { return $this->_lang; }
    
}

