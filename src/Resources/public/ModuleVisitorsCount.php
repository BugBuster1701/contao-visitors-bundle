<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Modul Visitors Count - Frontend for Counting
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

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');

$dir = __DIR__;

while ($dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php'))
{
    $dir = dirname($dir);
}

if (!is_file($dir . '/system/initialize.php'))
{
    throw new \ErrorException('Could not find initialize.php!',2,1,basename(__FILE__),__LINE__);
}
require($dir . '/system/initialize.php');


/**
 * Class ModuleVisitorsCount 
 *
 * @copyright  Glen Langer 2012..2014
 * @author     Glen Langer 
 * @package    GLVisitors
 * @license    LGPL 
 */
class ModuleVisitorsCount extends \Frontend  
{
	private $_BOT = false; // Bot
	
	private $_SE  = false; // Search Engine
	
	private $_PF  = false; // Prefetch found
	
	private $_VB  = false;	// Visit Blocker
	
	/**
	 * Initialize object 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function run()
	{
		//Parameter holen
		if ((int)\Input::get('vkatid')>0) 
		{
			$visitors_category_id = (int)\Input::get('vkatid');
			/* __________  __  ___   _____________   ________
			  / ____/ __ \/ / / / | / /_  __/  _/ | / / ____/
			 / /   / / / / / / /  |/ / / /  / //  |/ / / __  
			/ /___/ /_/ / /_/ / /|  / / / _/ // /|  / /_/ /  
			\____/\____/\____/_/ |_/ /_/ /___/_/ |_/\____/ only
			*/
			$objVisitors = \Database::getInstance()
	                ->prepare("SELECT 
                                    tl_visitors.id AS id, visitors_block_time
                                FROM
                                    tl_visitors
                                LEFT JOIN
                                    tl_visitors_category ON (tl_visitors_category.id = tl_visitors.pid)
                                WHERE
                                    pid = ? AND published = ?
                                ORDER BY id , visitors_name")
                      ->limit(1)
				      ->executeUncached($visitors_category_id,1);
			if ($objVisitors->numRows < 1) 
			{
			    $this->log($GLOBALS['TL_LANG']['tl_visitors']['wrong_katid'], 'ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR);
			} 
			else 
			{
				while ($objVisitors->next()) 
				{
				    $this->visitorCountUpdate($objVisitors->id, $objVisitors->visitors_block_time, $visitors_category_id);
				    $this->visitorCheckSearchEngine($objVisitors->id);
				    if ($this->_BOT === false && $this->_SE === false) 
				    {
				    	$this->visitorCheckReferrer($objVisitors->id);
				    }
				}
			}
		} 
		else 
		{
			$this->log($GLOBALS['TL_LANG']['tl_visitors']['wrong_count_katid'], 'ModulVisitorsCount '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR);
		}
		//Debug log_message('run BOT SE : '.(int)$this->_BOT . '-' . (int)$this->_SE,'debug.log');
		//Pixel und raus hier
		header('Cache-Control: no-cache');
		header('Content-type: image/gif');
		header('Content-length: 43');

		echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
	} //function
	
	/**
	 * Insert/Update Counter
	 */
	protected function visitorCountUpdate($vid, $BlockTime, $visitors_category_id)
	{
		$ModuleVisitorChecks = new \Visitors\ModuleVisitorChecks();
		if ($ModuleVisitorChecks->checkBot() === true) 
		{
			$this->_BOT = true;
			//Debug log_message("visitorCountUpdate BOT=true","debug.log");
	    	return; //Bot / IP gefunden, wird nicht gezaehlt
	    }
	    if ($ModuleVisitorChecks->checkUserAgent($visitors_category_id) === true) 
	    {
	    	$this->_PF = true; // Bad but functionally
	    	//Debug log_message("visitorCountUpdate UserAgent=true","debug.log");
	    	return ; //User Agent Filterung
	    }
	    //Debug log_message("visitorCountUpdate count: ".$this->Environment->httpUserAgent,"useragents-noblock.log");
	    $ClientIP = bin2hex(sha1($visitors_category_id . $this->visitorGetUserIP(),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BlockTime = ($BlockTime == '') ? 1800 : $BlockTime; //Sekunden
	    $CURDATE = date('Y-m-d');

	    //Visitor Blocker
	    \Database::getInstance()
	            ->prepare("DELETE FROM
                                tl_visitors_blocker
                            WHERE
                                CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                            AND 
                                vid = ?
                            AND 
                                visitors_type = ?")
                ->executeUncached($BlockTime, $vid, 'v');
	    
	    //Hit Blocker for IE8 Bullshit
	    \Database::getInstance()
	            ->prepare("DELETE FROM
                                tl_visitors_blocker
                            WHERE
                                CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                            AND 
                                vid = ?
                            AND 
                                visitors_type = ?")
                ->executeUncached(3, $vid, 'h');
	    
	    if ($ModuleVisitorChecks->checkBE() === true) 
	    {
	    	$this->_PF = true; // Bad but functionally
			return; // Backend eingeloggt, nicht zaehlen (Feature: #197)
		}
		
	    //Hits und Visits lesen
	    $objHitCounter = \Database::getInstance()
	            ->prepare("SELECT 
                                id, visitors_hit, visitors_visit
                            FROM
                                tl_visitors_counter
                            WHERE
                                visitors_date = ? AND vid = ?")
                ->executeUncached($CURDATE, $vid);

	    //Test ob Hits gesetzt werden muessen (IE8 Bullshit)
	    $objHitIP = \Database::getInstance()
	            ->prepare("SELECT 
                                id, visitors_ip
                            FROM
                                tl_visitors_blocker
                            WHERE
                                visitors_ip = ? AND vid = ? AND visitors_type = ?")
                ->executeUncached($ClientIP, $vid, 'h');

	    //Hits setzen
	    if ($objHitCounter->numRows < 1) 
	    {
	    	if ($objHitIP->numRows < 1) 
	    	{
	    	    //first block
	    	    \Database::getInstance()
	    	            ->prepare("INSERT INTO 
                                        tl_visitors_blocker
                                    SET 
                                        vid=?,
                                        visitors_tstamp=CURRENT_TIMESTAMP,
                                        visitors_ip=?,
                                        visitors_type=?")
                        ->executeUncached($vid, $ClientIP, 'h');
	    	    
		        // Insert
		        $arrSet = array
	            (
	                'vid'               => $vid,
	                'visitors_date'     => $CURDATE,
	                'visitors_visit'    => 1,
	                'visitors_hit'      => 1
	            );
			    \Database::getInstance()
			            ->prepare("INSERT IGNORE INTO tl_visitors_counter %s")
			            ->set($arrSet)
			            ->executeUncached();
	    	} 
	    	else 
	    	{
	    		$this->_PF = true;
	    	}
		    $visitors_hits=1;
		    $visitors_visit=1;
	    } 
	    else 
	    {
	        $objHitCounter->next();
	        $visitors_hits = $objHitCounter->visitors_hit +1;
	        $visitors_visit= $objHitCounter->visitors_visit +1; // wird nur gesetzt wenn auch neuer Besucher
			if ($objHitIP->numRows < 1) 
			{
		        // Update
		    	\Database::getInstance()
		    	        ->prepare("UPDATE tl_visitors_counter SET visitors_hit=? WHERE id=?")
		    	        ->executeUncached($visitors_hits, $objHitCounter->id);
		    	\Database::getInstance()
		    	        ->prepare("INSERT INTO
                                        tl_visitors_blocker
                                    SET
                                        vid=?,
                                        visitors_tstamp=CURRENT_TIMESTAMP,
                                        visitors_ip=?,
                                        visitors_type=?")
                        ->executeUncached($vid, $ClientIP, 'h');
			} 
			else 
			{
	    		$this->_PF = true;
	    	}
	    }
	    
	    //Visits / IP setzen
	    $objVisitIP = \Database::getInstance()
	            ->prepare("SELECT 
                                id, visitors_ip
                            FROM
                                tl_visitors_blocker
                            WHERE
                                visitors_ip = ? AND vid = ? AND visitors_type = ?")
                ->executeUncached($ClientIP, $vid, 'v');
	    if ($objVisitIP->numRows < 1) 
	    {
	        // Insert IP + Update Visits
	        \Database::getInstance()
        	        ->prepare("INSERT INTO
                                    tl_visitors_blocker
                                SET
                                    vid=?,
                                    visitors_tstamp=CURRENT_TIMESTAMP,
                                    visitors_ip=?,
                                    visitors_type=?")
                    ->executeUncached($vid, $ClientIP, 'v');
	        \Database::getInstance()
	                ->prepare("UPDATE
                                    tl_visitors_counter
                                SET
                                    visitors_visit = ?
                                WHERE
                                    visitors_date = ? AND vid = ?")
                    ->executeUncached($visitors_visit, $CURDATE, $vid);
	    } 
	    else 
	    {
	    	// Update tstamp
	    	\Database::getInstance()
	    	        ->prepare("UPDATE
                                    tl_visitors_blocker
                                SET
                                    visitors_tstamp=CURRENT_TIMESTAMP
                                WHERE
                                    visitors_ip=? AND vid=? AND visitors_type=?")
                    ->executeUncached($ClientIP, $vid, 'v');
	    	$this->_VB = true;
	    }
	    if ($objVisitIP->numRows < 1) 
	    { //Browser Check wenn nicht geblockt
		    //Only counting if User Agent is set.
		    if ( strlen(\Environment::get('httpUserAgent'))>0 ) 
		    {
			    /* Variante 3 */
			    $ModuleVisitorBrowser3 = new \Visitors\ModuleVisitorBrowser3();
				$ModuleVisitorBrowser3->initBrowser(\Environment::get('httpUserAgent'),implode(",", \Environment::get('httpAcceptLanguage')));
				if ($ModuleVisitorBrowser3->getLang() === null) 
				{
					log_message("ModuleVisitorBrowser3 Systemerror","error.log");
			    	$this->log("ModuleVisitorBrowser3 Systemerror",'ModulVisitors', TL_ERROR);
			    } 
			    else 
			    {
			    	$arrBrowser['Browser']  = $ModuleVisitorBrowser3->getBrowser();
					$arrBrowser['Version']  = $ModuleVisitorBrowser3->getVersion();
					$arrBrowser['Platform'] = $ModuleVisitorBrowser3->getPlatformVersion();
					$arrBrowser['lang']     = $ModuleVisitorBrowser3->getLang();
				    //Anpassen an Version 1 zur Weiterverarbeitung
				    if ($arrBrowser['Browser'] == 'unknown') 
				    {
				    	$arrBrowser['Browser'] = 'Unknown';
				    }
				    if ($arrBrowser['Version'] == 'unknown') 
				    {
				    	$arrBrowser['brversion'] = $arrBrowser['Browser'];
				    } 
				    else 
				    {
				    	$arrBrowser['brversion'] = $arrBrowser['Browser'] . ' ' . $arrBrowser['Version'];
				    }
				    if ($arrBrowser['Platform'] == 'unknown') 
				    {
				    	$arrBrowser['Platform'] = 'Unknown';
				    }
				    //Debug if ( $arrBrowser['Platform'] == 'Unknown' || $arrBrowser['Platform'] == 'Mozilla' || $arrBrowser['Version'] == '0' ) {
				    //Debug log_message("Unbekannter User Agent: ".$this->Environment->httpUserAgent."", 'unknown.log');
				    //Debug }
				    $objBrowserCounter = \Database::getInstance()
				            ->prepare("SELECT
                                            id,visitors_counter
                                        FROM
                                            tl_visitors_browser
                                        WHERE
                                            vid=? 
				                        AND 
				                            visitors_browser=? 
				                        AND 
				                            visitors_os=? 
                                        AND 
				                            visitors_lang=?")
                            ->executeUncached($vid, $arrBrowser['brversion'], $arrBrowser['Platform'], $arrBrowser['lang']);
				    //setzen
				    if ($objBrowserCounter->numRows < 1) 
				    {
				        // Insert
				        $arrSet = array
			            (
			                'vid'               => $vid,
			                'visitors_browser'  => $arrBrowser['brversion'], // version
			                'visitors_os'		=> $arrBrowser['Platform'],  // os
			                'visitors_lang'		=> $arrBrowser['lang'],
			                'visitors_counter'  => 1
			            );
					    \Database::getInstance()
					            ->prepare("INSERT INTO tl_visitors_browser %s")
					            ->set($arrSet)
					            ->executeUncached();
				    } 
				    else 
				    {
				    	//Update
				        $objBrowserCounter->next();
				        $visitors_counter = $objBrowserCounter->visitors_counter +1;
				    	// Update
				    	\Database::getInstance()
				    	        ->prepare("UPDATE tl_visitors_browser SET visitors_counter=? WHERE id=?")
                                ->executeUncached($visitors_counter, $objBrowserCounter->id);
				    }
			    } // else von NULL
			} // if strlen
	    } //VisitIP numRows
	} //visitorCountUpdate
	
	/**
	 * Check for Searchengines
	 *
	 * @param integer $vid	Visitors ID
	 */
	protected function visitorCheckSearchEngine($vid)
	{
		$ModuleVisitorSearchEngine = new \Visitors\ModuleVisitorSearchEngine();
		$ModuleVisitorSearchEngine->checkEngines();
		$SearchEngine = $ModuleVisitorSearchEngine->getEngine();
		$Keywords     = $ModuleVisitorSearchEngine->getKeywords();
		if ($SearchEngine !== 'unknown') 
		{
			$this->_SE = true;
			if ($Keywords !== 'unknown') {
				// Insert
		        $arrSet = array
		        (
		            'vid'                   => $vid,
		            'tstamp'                => time(),
		            'visitors_searchengine' => $SearchEngine,
		            'visitors_keywords'		=> $Keywords
		        );
			    \Database::getInstance()
			            ->prepare("INSERT INTO tl_visitors_searchengines %s")
			            ->set($arrSet)
			            ->executeUncached();
			    // Delete old entries
			    $CleanTime = mktime(0, 0, 0, date("m")-3, date("d"), date("Y")); // EintrÃ¤ge >= 90 Tage werden gelÃ¶scht
			    \Database::getInstance()
			            ->prepare("DELETE FROM tl_visitors_searchengines WHERE tstamp<? AND vid=?")
		                ->execute($CleanTime,$vid);
			} //keywords
		} //searchengine
	} //visitorCheckSearchEngine
	
	/**
	 * Check for Referrer
	 *
	 * @param integer $vid	Visitors ID
	 */
	protected function visitorCheckReferrer($vid)
	{
		if ($this->_VB === false) 
		{
			if ($this->_PF === false) 
			{
				$ModuleVisitorReferrer = new \Visitors\ModuleVisitorReferrer();
				$ModuleVisitorReferrer->checkReferrer();
				$ReferrerDNS = $ModuleVisitorReferrer->getReferrerDNS();
				$ReferrerFull= $ModuleVisitorReferrer->getReferrerFull();
				if ($ReferrerDNS != 'o' && $ReferrerDNS != 'w') 
				{ 	// not the own, not wrong
					// Insert
			        $arrSet = array
			        (
			            'vid'                   => $vid,
			            'tstamp'                => time(),
			            'visitors_referrer_dns' => $ReferrerDNS,
			            'visitors_referrer_full'=> $ReferrerFull
			        );
			        //Referrer setzen
			        \Database::getInstance()
			                ->prepare("INSERT INTO tl_visitors_referrer %s")
			                ->set($arrSet)
			                ->executeUncached();
				    // Delete old entries
				    $CleanTime = mktime(0, 0, 0, date("m")-4, date("d"), date("Y")); // Einträge >= 120 Tage werden gelöscht
				    \Database::getInstance()
				            ->prepare("DELETE FROM tl_visitors_referrer WHERE tstamp<? AND vid=?")
			                ->execute($CleanTime,$vid);
				}
		    } //if PF
	    } //if VB
	} // visitorCheckReferrer
	
	/**
	 * Get User IP
	 *
	 * @return string
	 */
	protected function visitorGetUserIP()
	{
	    $UserIP = \Environment::get('ip');
	    if (strpos($UserIP, ',') !== false) //first IP
	    {
	        $UserIP = trim( substr($UserIP, 0, strpos($UserIP, ',') ) );
	    }
	    if ( true === $this->visitorIsPrivateIP($UserIP) &&
	        false === empty($_SERVER['HTTP_X_FORWARDED_FOR'])
	    )
	    {
	        //second try
	        $HTTPXFF = $_SERVER['HTTP_X_FORWARDED_FOR'];
	        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
	
	        $UserIP = \Environment::get('ip');
	        if (strpos($UserIP, ',') !== false) //first IP
	        {
	            $UserIP = trim( substr($UserIP, 0, strpos($UserIP, ',') ) );
	        }
	        $_SERVER['HTTP_X_FORWARDED_FOR'] = $HTTPXFF;
	    }
	    return $UserIP;
	}
	
	/**
	 * Check if an IP address is from private or reserved ranges.
	 *
	 * @param string $UserIP
	 * @return boolean         true = private/reserved
	 */
	protected function visitorIsPrivateIP($UserIP = false)
	{
	    return !filter_var($UserIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}
	
} // class

/**
 * Instantiate controller
 */
$objVisitorsCount = new ModuleVisitorsCount();
$objVisitorsCount->run();
