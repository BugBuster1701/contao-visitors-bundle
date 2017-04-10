<?php 

/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Modul Visitors Checks - Frontend
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
 * Class ModuleVisitorChecks 
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL 
 */
class ModuleVisitorChecks extends \Frontend
{
	/**
	 * Current version of the class.
	 */
	const VERSION           = '3.4';
	
	/**
	 * Spider Bot Check
	 * 
	 * @return bool
	 */
	public function checkBot() 
	{
		if ( !in_array( 'botdetection', \ModuleLoader::getActive() ) )
		{
			//botdetection Modul fehlt, Abbruch
			$this->log('BotDetection extension required for extension: Visitors!', 'ModuleVisitorChecks checkBot', TL_ERROR);
			return false;
		}
		$ModuleBotDetection = new \BotDetection\ModuleBotDetection();
	    if ($ModuleBotDetection->checkBotAllTests()) 
	    {
	        ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': True' );
	    	return true;
	    }
	    ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': False' );
	    return false;
	} //checkBot
	
	/**
	 * HTTP_USER_AGENT Special Check
	 * 
	 * @return bool
	 */
	public function checkUserAgent($visitors_category_id)
	{
   	    if (\Environment::get('httpUserAgent')) 
   	    { 
	        $UserAgent = trim(\Environment::get('httpUserAgent')); 
	    } 
	    else 
	    { 
	        return false; // Ohne Absender keine Suche
	    }
	    $arrUserAgents = array();
	    $objUserAgents = \Database::getInstance()
	            ->prepare("SELECT 
                                `visitors_useragent` 
                            FROM 
                                `tl_module` 
                            WHERE 
                                `type` = ? AND `visitors_categories` = ?")
                ->execute('visitors',$visitors_category_id);
		if ($objUserAgents->numRows) 
		{
			while ($objUserAgents->next()) 
			{
				$arrUserAgents = array_merge($arrUserAgents,explode(",", $objUserAgents->visitors_useragent));
			}
		}
	    if (strlen(trim($arrUserAgents[0])) == 0) 
	    {
	    	return false; // keine Angaben im Modul
	    }
        // Suche
        $CheckUserAgent=str_replace($arrUserAgents, '#', $UserAgent);
        if ($UserAgent != $CheckUserAgent) 
        { 	// es wurde ersetzt also was gefunden
            ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': True' );
            return true;
        }
        ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': False' );
        return false; 
	} //checkUserAgent
	
	/**
	 * BE Login Check
	 * basiert auf Frontend.getLoginStatus
	 * 
	 * @return bool
	 */
	public function checkBE()
	{
		$strCookie = 'BE_USER_AUTH';
		$hash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? \Environment::get('ip') : '') . $strCookie);
		if (\Input::cookie($strCookie) == $hash)
		{
			// Try to find the session
			$objSession = \SessionModel::findByHashAndName($hash, $strCookie);
			// Validate the session ID and timeout
			if ($objSession !== null && $objSession->sessionID == session_id() && ($GLOBALS['TL_CONFIG']['disableIpCheck'] || $objSession->ip == \Environment::get('ip')) && ($objSession->tstamp + $GLOBALS['TL_CONFIG']['sessionTimeout']) > time())
			{
			    ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': True' );
				return true;
			}
		}
		ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': False' );
		return false;
	} //CheckBE
	
	/**
	 * Check if Domain valid
	 * 
	 * @param string    Host / domain.tld
	 * @return boolean
	 */
	public function isDomain($host)
	{
	    $dnsResult = false;
	    //$this->_vhost :  Host.TLD
	    //idn_to_ascii
	    $dnsResult = dns_get_record( \Idna::encode( $host ), DNS_ANY );
	    if ( $dnsResult )
	    {
	        ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': True' );
	        return true;
	    }
	    ModuleVisitorLog::writeLog( __METHOD__ , __LINE__ , ': False' );
	    return false;
	}
	
	/**
	 * Check if a string contains a valid IPv6 address.
	 * If the string was extracted with parse_url (host),
	 * the brackets must be removed.
	 *
	 * @param string
	 * @return boolean
	 */
	public function isIP6($ip6)
	{
	    return (filter_var( trim($ip6,'[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? true : false);
	}
	
	/**
	 * Check if a string contains a valid IPv4 address.
	 *
	 * @param string
	 * @return boolean
	 */
	public function isIP4($ip4)
	{
	    return (filter_var( $ip4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false);
	}
	
}

