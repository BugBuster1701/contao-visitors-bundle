<?php

/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 * 
 * Modul Visitors Tag - Frontend for InsertTags
 *
 * @copyright  Glen Langer 2012..2020 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

use BugBuster\Visitors\ModuleVisitorBrowser3;
use BugBuster\Visitors\ModuleVisitorChecks;
use BugBuster\Visitors\ModuleVisitorLog;
use BugBuster\Visitors\ModuleVisitorReferrer;
use BugBuster\Visitors\ModuleVisitorSearchEngine;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\StringUtil;
use Psr\Log\LogLevel;

/**
 * Class ModuleVisitorsTag 
 *
 * @copyright  Glen Langer 2012..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL 
 */
class ModuleVisitorsTag extends \Frontend  
{
	private $_BOT = false;	// Bot

	private $_SE  = false;	// Search Engine

	private $_PF  = false;	// Prefetch found

	private $_VB  = false;	// Visit Blocker

	private $_VisitCounted = false;

	private $_HitCounted   = false;

	private static $_BackendUser  = false;

	const PAGE_TYPE_NORMAL     = 0;    //0   = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
	const PAGE_TYPE_NEWS       = 1;    //1   = Nachrichten/News
	const PAGE_TYPE_FAQ        = 2;    //2   = FAQ
	const PAGE_TYPE_ISOTOPE    = 3;    //3   = Isotope
	const PAGE_TYPE_FORBIDDEN  = 403;  //403 = Forbidden Page

	/**
	 * replaceInsertTags
	 * 
	 * From TL 2.8 you can use prefix "cache_". Thus the InserTag will be not cached. (when "cache" is enabled)
	 * 
	 * visitors::katid::name			- VisitorsName
	 * visitors::katid::online			- VisitorsOnlineCount
	 * visitors::katid::start			- VisitorsStartDate
	 * visitors::katid::totalvisit		- TotalVisitCount
	 * visitors::katid::totalhit		- TotalHitCount
	 * visitors::katid::todayvisit		- TodayVisitCount
	 * visitors::katid::todayhit		- TodayHitCount
	 * visitors::katid::yesterdayvisit	- YesterdayVisitCount
	 * visitors::katid::yesterdayhit	- YesterdayHitCount
	 * visitors::katid::averagevisits	- AverageVisits
	 * visitors::katid::pagehits        - PageHits
	 * 
	 * cache_visitors::katid::count		- Counting (only)
	 * 
	 * Not used in the templates:
	 * visitors::katid::bestday::date   - Day (Date) with the most visitors
	 * visitors::katid::bestday::visits - Visits of the day with the most visitors
	 * visitors::katid::bestday::hits   - Hits of the day with the most visitors! (not hits!)
	 * 
	 * @param  string $strTag
	 * @return bool   / string
	 */
	public function replaceInsertTagsVisitors($strTag)
	{
		$arrTag = StringUtil::trimsplit('::', $strTag);
		if ($arrTag[0] != 'visitors')
		{
			if ($arrTag[0] != 'cache_visitors') 
			{
				return false; // nicht für uns
			}
		}
		\System::loadLanguageFile('tl_visitors');

		if (isset($arrTag[1]))
		{
		    $visitors_category_id = (int) $arrTag[1];
		    //Get Debug Settings
		    $this->visitorSetDebugSettings($visitors_category_id);
		}

		if (false === self::$_BackendUser )
		{
    		$objTokenChecker = \System::getContainer()->get('contao.security.token_checker');
    		if ($objTokenChecker->hasBackendUser())
    		{
    		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': BackendUser: Yes');
    		    self::$_BackendUser = true;
    		} 
    		else 
    		{
    		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': BackendUser: No');
    		}
		}

		if (!isset($arrTag[2])) 
		{
			\System::getContainer()
			     ->get('monolog.logger.contao')
			     ->log(LogLevel::ERROR,
			           $GLOBALS['TL_LANG']['tl_visitors']['no_key'],
			           array('contao' => new ContaoContext('ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));

			return false;  // da fehlt was
		}

		if ($arrTag[2] == 'count') 
		{
			/* __________  __  ___   _____________   ________
			  / ____/ __ \/ / / / | / /_  __/  _/ | / / ____/
			 / /   / / / / / / /  |/ / / /  / //  |/ / / __  
			/ /___/ /_/ / /_/ / /|  / / / _/ // /|  / /_/ /  
			\____/\____/\____/_/ |_/ /_/ /___/_/ |_/\____/ only
			*/

		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
			$objVisitors = \Database::getInstance()
			        ->prepare("SELECT 
                                    tl_visitors.id AS id, 
                                    visitors_block_time
                                FROM 
                                    tl_visitors 
                                LEFT JOIN 
                                    tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                                WHERE 
                                    pid=? AND published=?
                                ORDER BY id, visitors_name")
                    ->limit(1)
                    ->execute($visitors_category_id, 1);
			if ($objVisitors->numRows < 1)
			{
			    \System::getContainer()
			         ->get('monolog.logger.contao')
			         ->log(LogLevel::ERROR,
			               $GLOBALS['TL_LANG']['tl_visitors']['wrong_katid'],
			               array('contao' => new ContaoContext('ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));

				return false;
			}
			while ($objVisitors->next())
			{
			    $this->visitorCountUpdate($objVisitors->id, $objVisitors->visitors_block_time, $visitors_category_id, self::$_BackendUser);
			    $this->visitorCheckSearchEngine($objVisitors->id);
			    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'BOT: '.(int) $this->_BOT);
			    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'SE : '.(int) $this->_SE);
			    if ($this->_BOT === false && $this->_SE === false) 
			    {
			    	$this->visitorCheckReferrer($objVisitors->id);
			    }
			}
		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Counted Server: True');

			return '<!-- counted -->'; 
		}

		/* ____  __  ____________  __  ________
		  / __ \/ / / /_  __/ __ \/ / / /_  __/
		 / / / / / / / / / / /_/ / / / / / /   
		/ /_/ / /_/ / / / / ____/ /_/ / / /    
		\____/\____/ /_/ /_/    \____/ /_/ 
		*/
		$objVisitors = \Database::getInstance()
		        ->prepare("SELECT 
                                tl_visitors.id AS id, 
                                visitors_name, 
                                visitors_startdate, 
                                visitors_visit_start, 
                                visitors_hit_start, 
                                visitors_average, 
                                visitors_thousands_separator
                            FROM 
                                tl_visitors 
                            LEFT JOIN 
                                tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                            WHERE 
                                pid=? AND published=?
                            ORDER BY id, visitors_name")
                ->limit(1)
                ->execute($visitors_category_id, 1);
		if ($objVisitors->numRows < 1)
		{
		    \System::getContainer()
		          ->get('monolog.logger.contao')
		          ->log(LogLevel::ERROR,
		                $GLOBALS['TL_LANG']['tl_visitors']['wrong_katid'],
		                array('contao' => new ContaoContext('ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));

			return false;
		}
		$objVisitors->next();
		$boolSeparator = ($objVisitors->visitors_thousands_separator == 1) ? true : false;
		switch ($arrTag[2]) 
		{
		    case "name":
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);

				return trim($objVisitors->visitors_name);
				break;
		    case "online":
			    //VisitorsOnlineCount
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
			    $objVisitorsOnlineCount = \Database::getInstance()
			            ->prepare("SELECT 
                                        COUNT(id) AS VOC 
                                    FROM 
                                        tl_visitors_blocker
                                    WHERE 
                                        vid=? AND visitors_type=?")
                        ->execute($objVisitors->id, 'v');
	            $objVisitorsOnlineCount->next();
	            $VisitorsOnlineCount = ($objVisitorsOnlineCount->VOC === null) ? 0 : $objVisitorsOnlineCount->VOC;

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsOnlineCount, 0) : $VisitorsOnlineCount;
				break;
		    case "start":
		    	//VisitorsStartDate
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
		        if (!\strlen($objVisitors->visitors_startdate)) 
		        {
			    	$VisitorsStartDate = '';
			    } 
			    else 
			    {
					/** @var PageModel $objPage */
			        global $objPage;
			        $VisitorsStartDate = \Date::parse($objPage->dateFormat, $objVisitors->visitors_startdate);
			    }

				return $VisitorsStartDate;
				break;
		    case "totalvisit":
		    	//TotalVisitCount
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
	            $objVisitorsTotalCount = \Database::getInstance()
	                    ->prepare("SELECT 
                                        SUM(visitors_visit) AS SUMV
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=?")
                        ->execute($objVisitors->id);
				$VisitorsTotalVisitCount = $objVisitors->visitors_visit_start; //startwert
				if ($objVisitorsTotalCount->numRows > 0) 
				{
	    		    $objVisitorsTotalCount->next();
	                $VisitorsTotalVisitCount += ($objVisitorsTotalCount->SUMV === null) ? 0 : $objVisitorsTotalCount->SUMV;
			    }

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsTotalVisitCount, 0) : $VisitorsTotalVisitCount;
				break;
		    case "totalhit":
	    		//TotalHitCount
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
	            $objVisitorsTotalCount = \Database::getInstance()
	                    ->prepare("SELECT 
                                        SUM(visitors_hit) AS SUMH
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=?")
                        ->execute($objVisitors->id);
				$VisitorsTotalHitCount   = $objVisitors->visitors_hit_start;   //startwert
				if ($objVisitorsTotalCount->numRows > 0) 
				{
	    		    $objVisitorsTotalCount->next();
	                $VisitorsTotalHitCount += ($objVisitorsTotalCount->SUMH === null) ? 0 : $objVisitorsTotalCount->SUMH;
			    }

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsTotalHitCount, 0) : $VisitorsTotalHitCount;
				break;
		    case "todayvisit":
				//TodaysVisitCount
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
			    $objVisitorsTodaysCount = \Database::getInstance()
			            ->prepare("SELECT 
                                        visitors_visit
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=? AND visitors_date=?")
                        ->execute($objVisitors->id, date('Y-m-d'));
			    if ($objVisitorsTodaysCount->numRows < 1) 
			    {
			    	$VisitorsTodaysVisitCount = 0;
			    } 
			    else 
			    {
	    		    $objVisitorsTodaysCount->next();
	    		    $VisitorsTodaysVisitCount = ($objVisitorsTodaysCount->visitors_visit === null) ? 0 : $objVisitorsTodaysCount->visitors_visit;
			    }

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsTodaysVisitCount, 0) : $VisitorsTodaysVisitCount;
				break;
		    case "todayhit":
				//TodaysHitCount
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
			    $objVisitorsTodaysCount = \Database::getInstance()
			            ->prepare("SELECT 
                                        visitors_hit
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=? AND visitors_date=?")
                        ->execute($objVisitors->id, date('Y-m-d'));
			    if ($objVisitorsTodaysCount->numRows < 1) 
			    {
			    	$VisitorsTodaysHitCount   = 0;
			    } 
			    else 
			    {
	    		    $objVisitorsTodaysCount->next();
	    		    $VisitorsTodaysHitCount = ($objVisitorsTodaysCount->visitors_hit === null) ? 0 : $objVisitorsTodaysCount->visitors_hit;
			    }

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsTodaysHitCount, 0) : $VisitorsTodaysHitCount;
				break;
			case "yesterdayvisit":
				    //YesterdayVisitCount
				    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
				    $objVisitorsYesterdayCount = \Database::getInstance()
                        ->prepare("SELECT
                                        visitors_visit
                                    FROM
                                        tl_visitors_counter
                                    WHERE
                                        vid=? AND visitors_date=?")
                        ->execute($objVisitors->id, date('Y-m-d', strtotime('-1 days')));
                    if ($objVisitorsYesterdayCount->numRows < 1)
                    {
                        $VisitorsYesterdayVisitCount = 0;
                    }
                    else
                    {
                        $objVisitorsYesterdayCount->next();
                        $VisitorsYesterdayVisitCount = ($objVisitorsYesterdayCount->visitors_visit === null) ? 0 : $objVisitorsYesterdayCount->visitors_visit;
                    }

                    return ($boolSeparator) ? $this->getFormattedNumber($VisitorsYesterdayVisitCount, 0) : $VisitorsYesterdayVisitCount;
                    break;
            case "yesterdayhit":
                    //YesterdayHitCount
                    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
                    $objVisitorsYesterdayCount = \Database::getInstance()
                        ->prepare("SELECT
                                        visitors_hit
                                    FROM
                                        tl_visitors_counter
                                    WHERE
                                        vid=? AND visitors_date=?")
                        ->execute($objVisitors->id, date('Y-m-d', strtotime('-1 days')));
                    if ($objVisitorsYesterdayCount->numRows < 1)
                    {
                        $VisitorsYesterdayHitCount   = 0;
                    }
                    else
                    {
                        $objVisitorsYesterdayCount->next();
                        $VisitorsYesterdayHitCount = ($objVisitorsYesterdayCount->visitors_hit === null) ? 0 : $objVisitorsYesterdayCount->visitors_hit;
                    }

                    return ($boolSeparator) ? $this->getFormattedNumber($VisitorsYesterdayHitCount, 0) : $VisitorsYesterdayHitCount;
                    break;
		    case "averagevisits":
				// Average Visits
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
			    if ($objVisitors->visitors_average) 
			    {
			    	$today     = date('Y-m-d');
					$yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-1, (int) date("Y")));
	                $objVisitorsAverageCount = \Database::getInstance()
	                        ->prepare("SELECT 
                                            SUM(visitors_visit)  AS SUMV, 
                                            MIN( visitors_date ) AS MINDAY
                                        FROM 
                                            tl_visitors_counter
                                        WHERE 
                                            vid=? AND visitors_date<?")
                            ->execute($objVisitors->id, $today);
	    		    if ($objVisitorsAverageCount->numRows > 0) 
	    		    {
	                    $objVisitorsAverageCount->next();
	                    $tmpTotalDays = floor((strtotime($yesterday) - strtotime($objVisitorsAverageCount->MINDAY))/60/60/24);
	                    $VisitorsAverageVisitCount = ($objVisitorsAverageCount->SUMV === null) ? 0 : (int) $objVisitorsAverageCount->SUMV;
	                    if ($tmpTotalDays > 0) 
	                    {
	                    	$VisitorsAverageVisits = round($VisitorsAverageVisitCount / $tmpTotalDays, 0);
	                    } 
	                    else 
	                    {
	                    	$VisitorsAverageVisits = 0;
	                    }
	                }
			    } 
			    else 
			    {
	                $VisitorsAverageVisits = 0;
	            }

				return ($boolSeparator) ? $this->getFormattedNumber($VisitorsAverageVisits, 0) : $VisitorsAverageVisits;
				break;
		    case "pagehits":
		        // Page Hits
				ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
				
		        /** @var PageModel $objPage */
		        global $objPage;
		        //if page from cache, we have no page-id
		        if ($objPage->id == 0)
		        {
		            $objPage = $this->visitorGetPageObj();

		        } //$objPage->id == 0
				ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID '. $objPage->id);
				//#80, bei Readerseite den Beitrags-Alias beachten
				//0 = reale Seite / 404 / Reader ohne Parameter - Auflistung der News/FAQs
				//1 = Nachrichten/News
				//2 = FAQ
				//3 = Isotope
				//403 = Forbidden
				$visitors_page_type = $this->visitorGetPageType($objPage);
				//bei News/FAQ id des Beitrags ermitteln und $objPage->id ersetzen
				$objPageId    = $this->visitorGetPageIdByType($objPage->id, $visitors_page_type, $objPage->alias);

		        $objPageStatCount = \Database::getInstance()
                        ->prepare("SELECT
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND 
										visitors_page_id = ?
									AND
										visitors_page_type = ?
                                  ")
                        ->execute($objVisitors->id, $objPageId, $visitors_page_type);
                if ($objPageStatCount->numRows > 0)
                {
                    $objPageStatCount->next();
                    $VisitorsPageHits = $objPageStatCount->visitors_page_hits;
                }
                else 
                {
                    $VisitorsPageHits = 0;
                }

		        return ($boolSeparator) ? \System::getFormattedNumber($VisitorsPageHits, 0) : $VisitorsPageHits;
		        break;
		    case "bestday":
		    	//Day with the most visitors
		        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
		    	if (!isset($arrTag[3])) 
		    	{
					\System::getContainer()
					       ->get('monolog.logger.contao')
					       ->log(LogLevel::ERROR,
					             $GLOBALS['TL_LANG']['tl_visitors']['no_param4'],
					             array('contao' => new ContaoContext('ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));
					ModuleVisitorLog::writeLog(__METHOD__, __LINE__, $GLOBALS['TL_LANG']['tl_visitors']['no_param4']);

					return false;  // da fehlt was
				}
				$objVisitorsBestday = \Database::getInstance()
				        ->prepare("SELECT 
                                        visitors_date, 
                                        visitors_visit, 
                                        visitors_hit
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=?
                                    ORDER BY visitors_visit DESC, visitors_hit DESC")
                        ->limit(1)
                        ->execute($objVisitors->id);
				if ($objVisitorsBestday->numRows > 0) 
				{
		        	$objVisitorsBestday->next();
				}
				switch ($arrTag[3]) 
				{
					case "date":
					    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2].'::'.$arrTag[3]);
						if (!isset($arrTag[4])) 
						{
							return date($GLOBALS['TL_CONFIG']['dateFormat'], strtotime($objVisitorsBestday->visitors_date));
						} 
						else 
						{
							return date($arrTag[4], strtotime($objVisitorsBestday->visitors_date));
						}
						break;
					case "visits":
					    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2].'::'.$arrTag[3]);

						return ($boolSeparator) ? $this->getFormattedNumber($objVisitorsBestday->visitors_visit, 0) : $objVisitorsBestday->visitors_visit;
						break;
					case "hits":
					    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2].'::'.$arrTag[3]);

						return ($boolSeparator) ? $this->getFormattedNumber($objVisitorsBestday->visitors_hit, 0) : $objVisitorsBestday->visitors_hit;
						break;
					default:
						return false;
						break;
				}
		    	break;
			default:
			    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':' .$GLOBALS['TL_LANG']['tl_visitors']['wrong_key']);
				\System::getContainer()
				        ->get('monolog.logger.contao')
				        ->log(LogLevel::ERROR,
				              $GLOBALS['TL_LANG']['tl_visitors']['wrong_key'],
				              array('contao' => new ContaoContext('ModulVisitors ReplaceInsertTags '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));

				return false;
				break;
		}
	} //function

	/**
	 * Insert/Update Counter
	 */
	protected function visitorCountUpdate($vid, $BlockTime, $visitors_category_id, $BackendUser = false)
	{
		$ModuleVisitorChecks = new ModuleVisitorChecks($BackendUser);
		if (!isset($GLOBALS['TL_CONFIG']['mod_visitors_bot_check']) || $GLOBALS['TL_CONFIG']['mod_visitors_bot_check'] !== false) 
		{
			if ($ModuleVisitorChecks->checkBot() === true) 
			{
				$this->_BOT = true;

		    	return; //Bot / IP gefunden, wird nicht gezaehlt
		    }
		}
	    if ($ModuleVisitorChecks->checkUserAgent($visitors_category_id) === true) 
	    {
	    	$this->_PF = true; // Bad but functionally
	    	return; //User Agent Filterung
	    }
	    //Debug log_message("visitorCountUpdate count: ".$this->Environment->httpUserAgent,"useragents-noblock.log");
	    $ClientIP = bin2hex(sha1($visitors_category_id . $ModuleVisitorChecks->visitorGetUserIP(), true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BlockTime = ($BlockTime == '') ? 1800 : $BlockTime; //Sekunden
	    $CURDATE = date('Y-m-d');
	    //Visitor Blocker
	    \Database::getInstance()
	            ->prepare("DELETE FROM 
                                tl_visitors_blocker
                            WHERE 
                                CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                                AND vid = ? 
                                AND visitors_type = ?")
                ->execute($BlockTime, $vid, 'v');

	    //Hit Blocker for IE8 Bullshit and Browser Counting
	    \Database::getInstance()
	            ->prepare("DELETE FROM 
                                tl_visitors_blocker
                            WHERE 
                                CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                                AND vid = ? 
                                AND visitors_type = ?")
                ->execute(3, $vid, 'h'); // 3 Sekunden Blockierung zw. Zählung per Tag und Zählung per Browser
	    if ($ModuleVisitorChecks->checkBE() === true) 
	    {
	    	$this->_PF = true; // Bad but functionally
			return; // Backend eingeloggt, nicht zaehlen (Feature: #197)
		}

		//Test ob Hits gesetzt werden muessen (IE8 Bullshit and Browser Counting)
		$objHitIP = \Database::getInstance()
		        ->prepare("SELECT 
                                id, 
                                visitors_ip
                            FROM 
                                tl_visitors_blocker
                            WHERE 
                                visitors_ip = ?
                                AND vid = ? 
                                AND visitors_type = ?")
                ->execute($ClientIP, $vid, 'h');

	    //Hits und Visits lesen
	    $objHitCounter = \Database::getInstance()
	            ->prepare("SELECT 
                                id, 
                                visitors_hit, 
                                visitors_visit
                            FROM 
                                tl_visitors_counter
                            WHERE 
                                visitors_date = ? AND vid = ?")
                ->execute($CURDATE, $vid);
        //Hits setzen
	    if ($objHitCounter->numRows < 1) 
	    {
	    	if ($objHitIP->numRows < 1) 
	    	{
	    	    //at first: block
	    	    \Database::getInstance()
	    	            ->prepare("INSERT INTO 
                                        tl_visitors_blocker
                                    SET 
                                        vid = ?, 
                                        visitors_tstamp=CURRENT_TIMESTAMP, 
                                        visitors_ip = ?, 
                                        visitors_type = ?")
                        ->execute($vid, $ClientIP, 'h');
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
                        ->execute();
			    //for page counter
			    $this->_HitCounted = true;
	    	} 
	    	else 
	    	{
	    		$this->_PF = true; // Prefetch found
	    	}
		    $visitors_hits=1;
		    $visitors_visit=1;
	    } 
	    else 
	    {
	        $objHitCounter->next();
	        $visitors_hits = $objHitCounter->visitors_hit +1;
	        $visitors_visit= $objHitCounter->visitors_visit +1; 
			if ($objHitIP->numRows < 1) 
			{
		        // Update
		    	\Database::getInstance()
		    	        ->prepare("INSERT INTO 
                                        tl_visitors_blocker
                                    SET 
                                        vid = ?, 
                                        visitors_tstamp=CURRENT_TIMESTAMP, 
                                        visitors_ip = ?, 
                                        visitors_type = ?")
                        ->execute($vid, $ClientIP, 'h');
		    	\Database::getInstance()
		    	        ->prepare("UPDATE 
                                        tl_visitors_counter 
                                    SET 
                                        visitors_hit=? 
                                    WHERE 
                                        id=?")
                        ->execute($visitors_hits, $objHitCounter->id);
		    	//for page counter
		    	$this->_HitCounted = true;
			} 
			else 
			{
	    		$this->_PF = true; // Prefetch found
	    	}
	    }

	    //Visits / IP setzen
	    $objVisitIP = \Database::getInstance()
	            ->prepare("SELECT 
                                id, 
                                visitors_ip
                            FROM 
                                tl_visitors_blocker
                            WHERE 
                                visitors_ip = ? AND vid = ? AND visitors_type = ?")
                ->execute($ClientIP, $vid, 'v');
	    if ($objVisitIP->numRows < 1) 
	    {
	        // not blocked: Insert IP + Update Visits
	        \Database::getInstance()
	                ->prepare("INSERT INTO 
                                    tl_visitors_blocker
                                SET 
                                    vid = ?, 
                                    visitors_tstamp = CURRENT_TIMESTAMP, 
                                    visitors_ip = ?, 
                                    visitors_type = ?")
                    ->execute($vid, $ClientIP, 'v');

	        \Database::getInstance()
	                ->prepare("UPDATE 
                                    tl_visitors_counter 
                                SET 
                                    visitors_visit = ?
                                WHERE 
                                    visitors_date = ? AND vid = ?")
                    ->execute($visitors_visit, $CURDATE, $vid);
	        //for page counter
	        $this->_VisitCounted = true;
	    } 
	    else 
	    {
	    	// blocked: Update tstamp
	    	\Database::getInstance()
	    	        ->prepare("UPDATE 
                                    tl_visitors_blocker
                                SET 
                                    visitors_tstamp = CURRENT_TIMESTAMP
                                WHERE 
                                    visitors_ip = ?
                                    AND vid = ? 
                                    AND visitors_type = ?")
                    ->execute($ClientIP, $vid, 'v');
	    	$this->_VB = true;
	    }

	    //Page Counter 
	    if ($this->_HitCounted === true || $this->_VisitCounted === true) 
	    {
			/** @var PageModel $objPage */
    	    global $objPage;
    	    //if page from cache, we have no page-id
    	    if ($objPage->id == 0) 
    	    {
    	        $objPage = $this->visitorGetPageObj();

            } //$objPage->id == 0
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID / Lang in Object: '. $objPage->id .' / '.$objPage->language);

	 	    //#102, bei Readerseite den Beitrags-Alias zählen (Parameter vorhanden)
	 	    //0 = reale Seite / 404 / Reader ohne Parameter - Auflistung der News/FAQs
            //1 = Nachrichten/News
            //2 = FAQ
            //3 = Isotope
            //403 = Forbidden
	 	    $visitors_page_type = $this->visitorGetPageType($objPage);
	 	    //bei News/FAQ id des Beitrags ermitteln und $objPage->id ersetzen
	 	    //Fixed #211, Duplicate entry in tl_search
	 	    $objPageIdOrg = $objPage->id;
	 	    $objPageId    = $this->visitorGetPageIdByType($objPage->id, $visitors_page_type, $objPage->alias);

	 	    if (self::PAGE_TYPE_ISOTOPE != $visitors_page_type) {
	 	        $objPageIdOrg = 0; //backward compatibility
	 	    }
    	    $objPageHitVisit = \Database::getInstance()
                	               ->prepare("SELECT
                                                id,
                                                visitors_page_visit,
                                                visitors_page_hit
                                            FROM
                                                tl_visitors_pages
                                            WHERE
                                                visitors_page_date = ?
                                            AND
                                                vid = ?
                                            AND
                                                visitors_page_id = ?
                                            AND
                                                visitors_page_pid = ?
                                            AND
                                                visitors_page_lang = ?
                                            AND
                                                visitors_page_type = ?
                                            ")
                                    ->execute($CURDATE, $vid, $objPageId, $objPageIdOrg, $objPage->language, $visitors_page_type);
    	    // eventuell $GLOBALS['TL_LANGUAGE']
    	    // oder      $objPage->rootLanguage; // Sprache der Root-Seite
    	    if ($objPageHitVisit->numRows < 1)
    	    {
    	        if ($objPageId > 0) 
    	        {
        	        //Page Counter Insert
        	        $arrSet = array
        	        (
        	            'vid'                 => $vid,
        	            'visitors_page_date'  => $CURDATE,
        	            'visitors_page_id'    => $objPageId,
        	            'visitors_page_pid'   => $objPageIdOrg,
        	            'visitors_page_type'  => $visitors_page_type,
        	            'visitors_page_visit' => 1,
        	            'visitors_page_hit'   => 1,
        	            'visitors_page_lang'  => $objPage->language
        	        );
        	        \Database::getInstance()
                    	        ->prepare("INSERT IGNORE INTO tl_visitors_pages %s")
                    	        ->set($arrSet)
                    	        ->execute();
    	        }
    	    }
    	    else
    	    {
    	        $objPageHitVisit->next();
    	        $visitors_page_hits   = $objPageHitVisit->visitors_page_hit;
    	        $visitors_page_visits = $objPageHitVisit->visitors_page_visit;

    	        if ($this->_HitCounted === true)
    	        {
        	        //Update Hit
    	            $visitors_page_hits += 1;
    	        }
    	        if ($this->_VisitCounted === true)
    	        {
    	            //Update Visit
    	            $visitors_page_visits += 1;    	            
    	        }
    	        \Database::getInstance()
                	        ->prepare("UPDATE
                                            tl_visitors_pages
                                        SET
                                            visitors_page_hit = ?,
                                            visitors_page_visit = ?
                                        WHERE
                                            id = ?
                                        ")
                            ->execute($visitors_page_hits, 
                                      $visitors_page_visits,
                                      $objPageHitVisit->id);
    	    }
	    }
	    //Page Counter End

	    if ($objVisitIP->numRows < 1) 
	    { //Browser Check wenn nicht geblockt
		    //Only counting if User Agent is set.
		    if (\strlen(\Environment::get('httpUserAgent'))>0) 
		    {
			    // Variante 3
				$ModuleVisitorBrowser3 = new ModuleVisitorBrowser3();
				$ModuleVisitorBrowser3->initBrowser(\Environment::get('httpUserAgent'), implode(",", \Environment::get('httpAcceptLanguage')));
				if ($ModuleVisitorBrowser3->getLang() === null) 
				{
    		    	\System::getContainer()
    	                   ->get('monolog.logger.contao')
			    	        ->log(LogLevel::ERROR,
			    	              'ModuleVisitorBrowser3 Systemerror',
			    	              array('contao' => new ContaoContext('ModulVisitors', TL_ERROR)));
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
				    //Debug if ( $arrBrowser['Platform'] == 'Unknown' || $arrBrowser['Platform'] == 'Mozilla' || $arrBrowser['Version'] == 'unknown' ) {
				    //Debug 	log_message("Unbekannter User Agent: ".$this->Environment->httpUserAgent."", 'unknown.log');
				    //Debug }
				    $objBrowserCounter = \Database::getInstance()
				            ->prepare("SELECT 
                                            id,
                                            visitors_counter
                                        FROM 
                                            tl_visitors_browser
                                        WHERE 
                                            vid = ? 
                                            AND visitors_browser = ?
                                            AND visitors_os = ?
                                            AND visitors_lang = ?")
                            ->execute($vid, $arrBrowser['brversion'], $arrBrowser['Platform'], $arrBrowser['lang']);
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
                                ->execute();
				    } 
				    else 
				    {
				    	//Update
				        $objBrowserCounter->next();
				        $visitors_counter = $objBrowserCounter->visitors_counter +1;
				    	// Update
				    	\Database::getInstance()
                                ->prepare("UPDATE tl_visitors_browser SET visitors_counter=? WHERE id=?")
                                ->execute($visitors_counter, $objBrowserCounter->id);
				    }
			    } // else von NULL
			} // if strlen
	    } //VisitIP numRows
	} //visitorCountUpdate

	protected function visitorGetPageObj()
	{
	    $objPage = null;
	    $pageId  = null;

	    $pageId = $this->visitorGetPageIdFromUrl(); // Alias, not ID :-(

	    // Load a website root page object if there is no page ID
	    if ($pageId === null)
	    {
	        $pageId = $this->visitorGetRootPageFromUrl();
	    }
	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID over URL: '. $pageId);
	    // Get the current page object(s), NULL on type 404
	    $objPage = \PageModel::findPublishedByIdOrAlias($pageId);

	    // Check the URL and language of each page if there are multiple results
	    if ($objPage !== null && $objPage->count() > 1)
	    {
	        $objNewPage = null;
	        $arrPages   = array();

	        // Order by domain and language
	        while ($objPage->next())
	        {
	            $objCurrentPage = $objPage->current()->loadDetails();

	            $domain = $objCurrentPage->domain ?: '*';
	            $arrPages[$domain][$objCurrentPage->rootLanguage] = $objCurrentPage;

	            // Also store the fallback language
	            if ($objCurrentPage->rootIsFallback)
	            {
	                $arrPages[$domain]['*'] = $objCurrentPage;
	            }
	        }

	        $strHost = \Environment::get('host');

	        // Look for a root page whose domain name matches the host name
	        if (isset($arrPages[$strHost]))
	        {
	            $arrLangs = $arrPages[$strHost];
	        }
	        else
	        {
	            $arrLangs = $arrPages['*']; // empty domain
	        }

	        // Use the first result (see #4872)
	        if (!$GLOBALS['TL_CONFIG']['addLanguageToUrl'])
	        {
	            $objNewPage = current($arrLangs);
	        }
	        // Try to find a page matching the language parameter
	        elseif (($lang = \Input::get('language')) != '' && isset($arrLangs[$lang]))
	        {
	            $objNewPage = $arrLangs[$lang];
	        }

	        // Store the page object
	        if (\is_object($objNewPage))
	        {
	            $objPage = $objNewPage;
	        }
	    }
	    elseif ($objPage !== null && $objPage->count() == 1)
	    {
	        $objPage = $objPage->current()->loadDetails();
	    }
	    elseif ($objPage === null)
	    {
	        //404 page aus dem Cache
	        $pageId = $this->visitorGetRootPageFromUrl(false);
	        $objPage = \PageModel::find404ByPid($pageId);
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page Root ID / Page ID 404: '. $pageId .' / '.$objPage->id);
	    }

	    return $objPage;
	}

	protected function visitorCheckSearchEngine($vid)
	{
		$ModuleVisitorSearchEngine = new ModuleVisitorSearchEngine();
		$ModuleVisitorSearchEngine->checkEngines();
		$SearchEngine = $ModuleVisitorSearchEngine->getEngine();
		$Keywords     = $ModuleVisitorSearchEngine->getKeywords();
		if ($SearchEngine !== 'unknown') 
		{
			$this->_SE = true;
			if ($Keywords !== 'unknown') 
			{
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
                        ->execute();
			    // Delete old entries
			    $CleanTime = mktime(0, 0, 0, (int) date("m")-3, (int) date("d"), (int) date("Y")); // Einträge >= 90 Tage werden gelöscht
			    \Database::getInstance()
			            ->prepare("DELETE FROM tl_visitors_searchengines WHERE tstamp < ? AND vid = ?")
                        ->execute($CleanTime, $vid);
			} //keywords
		} //searchengine
		//Debug log_message('visitorCheckSearchEngine $SearchEngine: ' . $SearchEngine,'debug.log');
	} //visitorCheckSearchEngine

	/**
	 * Check for Referrer
	 *
	 * @param integer $vid Visitors ID
	 */
	protected function visitorCheckReferrer($vid)
	{
		if ($this->_HitCounted === true) 
		{
			if ($this->_PF === false) 
			{
				$ModuleVisitorReferrer = new ModuleVisitorReferrer();
				$ModuleVisitorReferrer->checkReferrer();
				$ReferrerDNS = $ModuleVisitorReferrer->getReferrerDNS();
				$ReferrerFull= $ModuleVisitorReferrer->getReferrerFull();
				//Debug log_message('visitorCheckReferrer $ReferrerDNS:'.print_r($ReferrerDNS,true), 'debug.log');
				//Debug log_message('visitorCheckReferrer Host:'.print_r($this->ModuleVisitorReferrer->getHost(),true), 'debug.log');
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
			    	//Debug log_message('visitorCheckReferrer Referrer setzen', 'debug.log');
			        \Database::getInstance()
			                ->prepare("INSERT INTO tl_visitors_referrer %s")
                            ->set($arrSet)
                            ->execute();
				    // Delete old entries
				    $CleanTime = mktime(0, 0, 0, (int) date("m")-4, (int) date("d"), (int) date("Y")); // Einträge >= 120 Tage werden gelöscht
				    \Database::getInstance()
                            ->prepare("DELETE FROM tl_visitors_referrer WHERE tstamp < ? AND vid = ?")
                            ->execute($CleanTime, $vid);
		    	}
		    } //if PF
	    } //if VB
	} // visitorCheckReferrer

	protected function visitorSetDebugSettings($visitors_category_id)
	{
	    $GLOBALS['visitors']['debug']['tag']          = false; 
	    $GLOBALS['visitors']['debug']['checks']       = false;
	    $GLOBALS['visitors']['debug']['referrer']     = false;
	    $GLOBALS['visitors']['debug']['searchengine'] = false;

	    $objVisitors = \Database::getInstance()
                ->prepare("SELECT
                                visitors_expert_debug_tag,
                                visitors_expert_debug_checks,
                                visitors_expert_debug_referrer,
                                visitors_expert_debug_searchengine
                            FROM
                                tl_visitors
                            LEFT JOIN
                                tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                            WHERE
                                pid=? AND published=?
                            ORDER BY tl_visitors.id, visitors_name")
                ->limit(1)
                ->execute($visitors_category_id, 1);
	    while ($objVisitors->next())
	    {
	        $GLOBALS['visitors']['debug']['tag']          = (bool) $objVisitors->visitors_expert_debug_tag;
	        $GLOBALS['visitors']['debug']['checks']       = (bool) $objVisitors->visitors_expert_debug_checks;
	        $GLOBALS['visitors']['debug']['referrer']     = (bool) $objVisitors->visitors_expert_debug_referrer;
	        $GLOBALS['visitors']['debug']['searchengine'] = (bool) $objVisitors->visitors_expert_debug_searchengine;
	        ModuleVisitorLog::writeLog('## START ##', '## DEBUG ##', 'T'.(int) $GLOBALS['visitors']['debug']['tag'] .'#C'. (int) $GLOBALS['visitors']['debug']['checks'] .'#R'.(int) $GLOBALS['visitors']['debug']['referrer'] .'#S'.(int) $GLOBALS['visitors']['debug']['searchengine']);
	    }
	}

	/**
	 * Fork from Page::getPageIdFromUrl
	 *
	 * @return void
	 */
	protected function visitorGetPageIdFromUrl()
	{
		$strRequest = \Environment::get('relativeRequest');

		if ($strRequest == '')
		{
			return null;
		}
		
		// Get the request without the query string
		list($strRequest) = explode('?', $strRequest, 2);

		// URL decode here (see #6232)
		$strRequest = rawurldecode($strRequest);

		// The request string must not contain "auto_item" (see #4012)
		if (strpos($strRequest, '/auto_item/') !== false)
		{
			return false;
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Request.1: '. $strRequest);

		// Extract the language
		if (\Config::get('addLanguageToUrl'))
		{
			$arrMatches = array();

			// Use the matches instead of substr() (thanks to Mario Müller)
			if (preg_match('@^([a-z]{2}(-[A-Z]{2})?)/(.*)$@', $strRequest, $arrMatches))
			{
				\Input::setGet('language', $arrMatches[1]);

				// Trigger the root page if only the language was given
				if ($arrMatches[3] == '')
				{
					return null;
				}

				$strRequest = $arrMatches[3];
			}
			else
			{
				return false; // Language not provided
			}
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Request.2: '. $strRequest);

		// Remove the URL suffix if not just a language root (e.g. en/) is requested
		if ($strRequest != '' && (!\Config::get('addLanguageToUrl') || !preg_match('@^[a-z]{2}(-[A-Z]{2})?/$@', $strRequest)))
		{
			$intSuffixLength = \strlen(\Config::get('urlSuffix'));

			// Return false if the URL suffix does not match (see #2864)
			if ($intSuffixLength > 0)
			{
				if (substr($strRequest, -$intSuffixLength) != \Config::get('urlSuffix'))
				{
					return false;
				}

				$strRequest = substr($strRequest, 0, -$intSuffixLength);
			}
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Request.3: '. $strRequest);

		$arrFragments = null;

		// Use folder-style URLs
		if (strpos($strRequest, '/') !== false)
		{
			$strAlias = $strRequest;
			$arrOptions = array($strAlias);

			// Compile all possible aliases by applying dirname() to the request (e.g. news/archive/item, news/archive, news)
			while ($strAlias != '/' && strpos($strAlias, '/') !== false)
			{
				$strAlias = \dirname($strAlias);
				$arrOptions[] = $strAlias;
			}

			/** @var PageModel $objPageModel */
			$objPageModel = \System::getContainer()->get('contao.framework')->getAdapter(\PageModel::class);

			// Check if there are pages with a matching alias
			$objPages = $objPageModel->findByAliases($arrOptions);

			if ($objPages !== null)
			{
				$arrPages = array();

				// Order by domain and language
				while ($objPages->next())
				{
					/** @var PageModel $objModel */
					$objModel = $objPages->current();
					$objPage  = $objModel->loadDetails();

					$domain = $objPage->domain ?: '*';
					$arrPages[$domain][$objPage->rootLanguage][] = $objPage;

					// Also store the fallback language
					if ($objPage->rootIsFallback)
					{
						$arrPages[$domain]['*'][] = $objPage;
					}
				}

				$arrAliases = array();
				$strHost = \Environment::get('host');

				// Look for a root page whose domain name matches the host name
				$arrLangs = $arrPages[$strHost] ?? $arrPages['*'] ?? array();

				// Use the first result (see #4872)
				if (!\Config::get('addLanguageToUrl'))
				{
					$arrAliases = current($arrLangs);
				}
				// Try to find a page matching the language parameter
				elseif (($lang = \Input::get('language')) && isset($arrLangs[$lang]))
				{
					$arrAliases = $arrLangs[$lang];
				}

				// Return if there are no matches
				if (empty($arrAliases))
				{
					return false;
				}

				$objPage = $arrAliases[0];

				// The request consists of the alias only
				if ($strRequest == $objPage->alias)
				{
					$arrFragments = array($strRequest);
				}
				// Remove the alias from the request string, explode it and then re-insert the alias at the beginning
				else
				{
					$arrFragments = explode('/', substr($strRequest, \strlen($objPage->alias) + 1));
					array_unshift($arrFragments, $objPage->alias);
				}
			}
		}

		// If folderUrl is deactivated or did not find a matching page
		if ($arrFragments === null)
		{
			if ($strRequest == '/')
			{
				return false;
			}

			$arrFragments = explode('/', $strRequest);
		}

		// Add the second fragment as auto_item if the number of fragments is even
		if (\count($arrFragments) % 2 == 0)
		{
			if (!\Config::get('useAutoItem'))
			{
				return false; // see #264
			}

			$this->visitorArrayInsert($arrFragments, 1, array('auto_item'));
		}

		// Return if the alias is empty (see #4702 and #4972)
		if ($arrFragments[0] == '' && \count($arrFragments) > 1)
		{
			return false;
		}

		// Add the fragments to the $_GET array
		for ($i=1, $c=\count($arrFragments); $i<$c; $i+=2)
		{
			// Return false if the key is empty (see #4702 and #263)
			if ($arrFragments[$i] == '')
			{
				return false;
			}

			// Return false if there is a duplicate parameter (duplicate content) (see #4277)
			if (isset($_GET[$arrFragments[$i]]))
			{
				return false;
			}

			// Return false if the request contains an auto_item keyword (duplicate content) (see #4012)
			if (\Config::get('useAutoItem') && \in_array($arrFragments[$i], $GLOBALS['TL_AUTO_ITEM']))
			{
				return false;
			}

			\Input::setGet($arrFragments[$i], $arrFragments[$i+1], true);
		}
		$url = $arrFragments[0] ?: null;
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Fragment: '. $url);

		return $url;
	}

	protected function visitorGetRootPageFromUrl($next=true)
	{
	    // simple Frontend:getRootPageFromUrl
	    $host = \Environment::get('host');

	    // The language is set in the URL
	    if ($GLOBALS['TL_CONFIG']['addLanguageToUrl'] && !empty($_GET['language']))
	    {
	        $objRootPage = \PageModel::findFirstPublishedRootByHostAndLanguage($host, \Input::get('language'));
        }
	    else // No language given
	    {
	        $accept_language = \Environment::get('httpAcceptLanguage');

	        // Find the matching root pages (thanks to Andreas Schempp)
	        $objRootPage = \PageModel::findFirstPublishedRootByHostAndLanguage($host, $accept_language);
	    }
	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Root Page ID over URL: '. $objRootPage->id);
	    if ($next === false) 
	    {
	    	return $objRootPage->id;
	    }
        //simple PageRoot:generate
	    $objNextPage = \PageModel::findFirstPublishedByPid($objRootPage->id);
	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Next Page ID over URL: '. $objNextPage->id);

	    return $objNextPage->id;
	}

	/**
	 * Get Page-Type
	 * 
	 * @param  integer $objPage
	 * @return integer 0 = reale Seite, 1 = News, 2 = FAQ, 403 = Forbidden
	 */
	protected function visitorGetPageType($objPage)
	{
	    $PageId = $objPage->id;
	    //Return:
	    //0 = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
	    //1 = Nachrichten/News
	    //2 = FAQ
	    //403 = Forbidden

	    $page_type = self::PAGE_TYPE_NORMAL;

	    if ($objPage->protected == 1) 
	    {
	    	//protected Seite. user 
	        $this->import('FrontendUser', 'User');
	        if (!$this->User->authenticate())
	        {
	            $page_type = self::PAGE_TYPE_FORBIDDEN;
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

	            return $page_type;
	        }
	    }

        //Set the item from the auto_item parameter
        //from class ModuleNewsReader#L48
        if (!isset($_GET['items']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
        	\Input::setGet('items', \Input::get('auto_item'));
        }
        if (!\Input::get('items'))
        {
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

            return $page_type;
        }

	    //News Table exists?
	    if (\Input::get('items') && \Database::getInstance()->tableExists('tl_news')) 
	    {
    	    //News Reader?
    	    $objReaderPage = \Database::getInstance()
                                ->prepare("SELECT id FROM tl_news_archive WHERE jumpTo=?")
                                ->limit(1)
                                ->execute($PageId);
    	    if ($objReaderPage->numRows > 0)
    	    {
    	        //News Reader
    	        $page_type = self::PAGE_TYPE_NEWS;
    	    }
	    }

	    //FAQ Table exists?
	    if (\Input::get('items') && \Database::getInstance()->tableExists('tl_faq_category'))
	    {
	        //FAQ Reader?
	        $objReaderPage = \Database::getInstance()
                                ->prepare("SELECT id FROM tl_faq_category WHERE jumpTo=?")
                                ->limit(1)
                                ->execute($PageId);
	        if ($objReaderPage->numRows > 0)
	        {
	            //FAQ Reader
	            $page_type = self::PAGE_TYPE_FAQ;
	        }
	    }

	    //Isotope Table tl_iso_product exists?
	    if (\Input::get('items') && \Database::getInstance()->tableExists('tl_iso_product'))
	    {
			$strAlias = \Input::get('items');
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Get items: '. print_r($strAlias, true));			

	        $objReaderPage = \Database::getInstance()
                                ->prepare("SELECT id FROM tl_iso_product WHERE alias=?")
                                ->limit(1)
                                ->execute($strAlias);
			if ($objReaderPage->numRows > 0)
			{
	            //Isotope Reader
	            $page_type = self::PAGE_TYPE_ISOTOPE;
	        }
	    }

	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

	    return $page_type;
	}

	/**
	 * Get Page-ID by Page-Type
	 * 
	 * @param  integer $PageId
	 * @param  integer $PageType
	 * @param  string  $PageAlias
	 * @return integer
	 */
	protected function visitorGetPageIdByType($PageId, $PageType, $PageAlias)
	{
	    if ($PageType == self::PAGE_TYPE_NORMAL) 
	    {
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal: '. $PageId);

	    	return $PageId;
	    }

	    if ($PageType == self::PAGE_TYPE_FORBIDDEN)
	    {
	        //Page ID von der 403 Seite ermitteln - nicht mehr
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal over 403: '. $PageId);

	        return $PageId; 
	    }

        //Reader mit Parameter oder ohne?
        $uri = $_SERVER['REQUEST_URI']; // /news/james-wilson-returns.html
        $alias = '';
        //steht suffix (html) am Ende?
        $urlSuffix = \System::getContainer()->getParameter('contao.url_suffix'); // default: .html
        if (substr($uri, -\strlen($urlSuffix)) == $urlSuffix)
        {
            //Alias nehmen
            $alias = substr($uri, strrpos($uri, '/')+1, -\strlen($urlSuffix));
            if (false === $alias) 
            {
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdReaderSelf: '. $PageId);

            	return $PageId; // kein Parameter, Readerseite selbst
            }
        }
        else 
        {
            $alias = substr($uri, strrpos($uri, '/')+1);
        }
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Alias: '. $alias);

        if ($PageType == self::PAGE_TYPE_NEWS)
        {
            //alias = james-wilson-returns
            $objNews = \Database::getInstance()
                            ->prepare("SELECT id FROM tl_news WHERE alias=?")
                            ->limit(1)
                            ->execute($alias);
            if ($objNews->numRows > 0)
            {
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNews: '. $objNews->id);

                return $objNews->id;
            } 

	    }
	    if ($PageType == self::PAGE_TYPE_FAQ)
	    {
	        //alias = are-there-exams-how-do-they-work
	        $objFaq = \Database::getInstance()
                            ->prepare("SELECT id FROM tl_faq WHERE alias=?")
                            ->limit(1)
                            ->execute($alias);
	        if ($objFaq->numRows > 0)
	        {
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdFaq: '. $objFaq->id);

	            return $objFaq->id;
	        }
	    }
	    if ($PageType == self::PAGE_TYPE_ISOTOPE)
	    {
	        //alias = a-perfect-circle-thirteenth-step
	        $objIsotope = \Database::getInstance()
                	        ->prepare("SELECT id FROM tl_iso_product WHERE alias=?")
                	        ->limit(1)
                	        ->execute($alias);
	        if ($objIsotope->numRows > 0)
	        {
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdIsotope: '. $objIsotope->id);

	            return $objIsotope->id;
	        }
	    }

	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Unknown PageType: '. $PageType);
	}

	/**
	 * Check if contao/core-bundle >= 4.5.0
	 * @deprecated
	 * 
	 * @return boolean
	 */
	protected function isContao45()
	{
	    $packages = \System::getContainer()->getParameter('kernel.packages');
	    $coreVersion = $packages['contao/core-bundle']; //a.b.c
	    if (version_compare($coreVersion, '4.5.0', '>='))
	    {
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': True');

	        return true;
	    }
	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': False');

	    return false;
	}

	/**
	 * From ArrayUtil::arrayInsert (Contao 4.10)
	 *
	 * @param [type] $arrCurrent
	 * @param [type] $intIndex
	 * @param [type] $arrNew
	 * @return void
	 */
	protected function visitorArrayInsert(&$arrCurrent, $intIndex, $arrNew): void
	{
		if (!\is_array($arrCurrent))
		{
			$arrCurrent = $arrNew;

			return;
		}

		if (\is_array($arrNew))
		{
			$arrBuffer = array_splice($arrCurrent, 0, $intIndex);
			$arrCurrent = array_merge_recursive($arrBuffer, $arrNew, $arrCurrent);

			return;
		}

		array_splice($arrCurrent, $intIndex, 0, $arrNew);
	}

} // class

