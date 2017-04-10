<?php 

/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Modul Visitors Referrer - Frontend
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @package    GLVisitors
 * @see	       https://github.com/BugBuster1701/visitors
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;
use BugBuster\Visitors\ModuleVisitorLog;

/**
 * Class ModuleVisitorReferrer
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL 
 */
class ModuleVisitorReferrer	extends \System
{
	/**
	 * Current version of the class.
	 */
	const VERSION          = '3.2';
	
    private $_http_referrer = '';
    
    private $_referrer_DNS  = '';
    
    private $_vhost        = '';
    
    private $_wrong_detail = '';
    
    const REFERRER_UNKNOWN  = '-';
    
    const REFERRER_OWN      = 'o';
    
    const REFERRER_WRONG    = 'w';
    
    /**
	* Reset all properties
	*/
	protected function reset() 
	{
	    ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , 'Referrer Raw: ' . $_SERVER['HTTP_REFERER'] );
	    //NEVER TRUST USER INPUT
	    if (function_exists('filter_var'))	// Adjustment for hoster without the filter extension
	    {
	    	$this->_http_referrer  = isset($_SERVER['HTTP_REFERER']) ? filter_var($_SERVER['HTTP_REFERER'],  FILTER_SANITIZE_URL) : self::REFERRER_UNKNOWN ;
	    } 
	    else 
	    {
	    	$this->_http_referrer  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : self::REFERRER_UNKNOWN ;
	    }
	    //Fixed #206
	    $this->_http_referrer = \Visitors\ForceUTF8\Encoding::toUTF8( urldecode( $this->_http_referrer ) );
	    
	    $this->_referrer_DNS = self::REFERRER_UNKNOWN;
	    if ($this->_http_referrer == '' || 
	        $this->_http_referrer == '-') 
	    {
	    	//ungueltiger Referrer
	    	$this->_referrer_DNS = self::REFERRER_WRONG;
	    	$this->_wrong_detail = 'Invalid referrer';
	    }
	}
	
	public function checkReferrer($referrer='') 
	{
		$this->reset();
		if( $referrer != "" ) 
		{
			//NEVER TRUST USER INPUT
			if (function_exists('filter_var'))	// Adjustment for hoster without the filter extension
	    	{
				$this->_http_referrer = filter_var($referrer,  FILTER_SANITIZE_URL);
	    	} 
	    	else 
	    	{
	    		$this->_http_referrer = $referrer;
	    	}
		}
		if ($this->_http_referrer !== self::REFERRER_UNKNOWN && 
		    $this->_referrer_DNS  !== self::REFERRER_WRONG) 
		{ 
			$this->detect();
		}
		ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , $this->__toString() );
	}
	
	protected function detect()
	{
	    $this->_referrer_DNS = parse_url( $this->_http_referrer, PHP_URL_HOST );
	    if ($this->_referrer_DNS === NULL) 
	    {
	    	//try this...
	    	try 
	    	{
	    	    $this->_referrer_DNS = parse_url( 'http://'.$this->_http_referrer, PHP_URL_HOST );
	    	} 
	    	catch (\Exception $e) 
	    	{
	    	    $this->_referrer_DNS = NULL;
	    	}
	    	if ($this->_referrer_DNS === NULL || 
	    	    $this->_referrer_DNS === false) 
	    	{
	    		//wtf...
	    		$this->_referrer_DNS = self::REFERRER_WRONG;
	    		$this->_wrong_detail = 'Problem on parse_url for referrer: '.$this->_http_referrer;
	    		return ;
	    	}
	    }
	    $this->_vhost = parse_url( 'http://'.$this->vhost(), PHP_URL_HOST );
	    //ReferrerDNS = HostDomain ?
	    if ( $this->_referrer_DNS == $this->_vhost ) 
	    {
	    	$this->_referrer_DNS = self::REFERRER_OWN;
	    	return ;
	    }

	    //Special fake and local checks
	    $this->import('\Visitors\ModuleVisitorChecks','ModuleVisitorChecks');

	    if ( $this->ModuleVisitorChecks->isIP4($this->_referrer_DNS) === true 
	      || $this->ModuleVisitorChecks->isIP6($this->_referrer_DNS) === true) 
	    {
	        // loopback ?
	        if ( substr($this->_referrer_DNS, 0,3)  == '127'
	            || trim($this->_referrer_DNS, '[]') == '::1' ) 
	        {
	            //Debug log_message('detect: loopback True','debug.log');
	            $this->_wrong_detail = 'Referrer DNS was loopback IP: '.$this->_referrer_DNS;
	            $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer was loopback IP
	            return ;
	        }
	        //remove IPv6 [] (comes from parse_url) 
	        $this->_referrer_DNS = trim($this->_referrer_DNS, '[]');
	        return ;
	    }
	    else
	    {
	        //no IP 
    	    //Kill external local domain (Github #63)
    	    if ( strpos($this->_referrer_DNS, '.') === false )
    	    {
    	        //Debug log_message('detect: Domain (not dot in Host) True','debug.log');
    	        $this->_wrong_detail = 'Referrer DNS was local (not domain): '.$this->_referrer_DNS;
    	        $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer was local (not domain)
    	        return ;
    	    }
	    }
	    
	    //Special for Fake Google.com (GitHub #32, #53)
	    if ( rtrim($this->_http_referrer,"/") == 'http://'  . $this->_referrer_DNS ||
	         rtrim($this->_http_referrer,"/") == 'https://' . $this->_referrer_DNS  )
	    {
	        if ( substr($this->_referrer_DNS, 0, 10) == 'www.google') 
	        {
    	        $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer is a fake. (shortened)
    	        $this->_wrong_detail = 'Referrer is a fake. (shortened): '.$this->_http_referrer;
    	        return ;
	        }
	    }
	    //Special for DuckDuckGo (GitHub #33)
	    if ( $this->_http_referrer == 'http://duckduckgo.com/post.html') 
	    {
	        $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer was duckduckgo.
	        $this->_wrong_detail = 'Referrer is a fake. (duckduckgo): '.$this->_http_referrer;
	        return ;
	    }
	    //Special for http:// (GitHub #37)
	    if ( $this->_http_referrer == 'http://' || $this->_http_referrer == 'http:/' )
	    {
	        $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer was shortened.
	        $this->_wrong_detail = 'Referrer was shortened: '.$this->_http_referrer;
	        return ;
	    }
	    
	    //Kill fake domain (local.lan, planet.ufp, ....)
	    if ( $this->ModuleVisitorChecks->isDomain($this->_referrer_DNS) === false )
	    {
	        //Debug log_message('detect: Domain (not valid Domain) True','debug.log');
	        $this->_wrong_detail = 'Referrer DNS was not a valid Domain: '.$this->_referrer_DNS;
	        $this->_referrer_DNS = self::REFERRER_WRONG; // Referrer was not a valid Domain
	        return ;
	    }
	}
	
	/**
	 * Return the current URL without path or query string or protocol
	 * @return string
	 */
	protected function vhost()
	{
		$host = rtrim($_SERVER['HTTP_HOST']);
		if (empty($host))
		{
			$host = $_SERVER['SERVER_NAME'];
		}
		$host  = preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', $host);
		
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) 
		{
			$xhost = preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', rtrim($_SERVER['HTTP_X_FORWARDED_HOST'],'/'));
		} 
		else 
		{
			$xhost = '';
		}
		
		return (!empty($xhost) ? $xhost : $host) ;
	}
	
	/**
	 * Return the request URI 
	 * @return string
	 */
	protected function requestURI()
	{
		if (!empty($_SERVER['REQUEST_URI']))
		{
			return htmlspecialchars( $_SERVER['REQUEST_URI']); 
		}
		else
		{
			return '';
		}
	}
	
   
	public function getReferrerDNS()  { return $this->_referrer_DNS;  }
	
	public function getReferrerFull() { return $this->_http_referrer; }
	
	public function getHost()  { return $this->_vhost; }
	
	public function __toString() 
	{
	    $out = '';
	    if ($this->_wrong_detail != '') 
	    {
	        $out = "Error Detail: {$this->_wrong_detail}\n" ;
	    }
	    return "\n".
	           "Referrer DNS : {$this->getReferrerDNS()}\n{$out}" .
			   "Referrer full: {$this->getReferrerFull()}\n".
			   "Server Host : {$this->getHost()}\n";
	}
	
}

