<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Modul Visitors Stat - Backend
 * 
 * @copyright  Glen Langer 2009..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/visitors
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;

/**
 * Class ModuleVisitorStat
 *
 * @copyright  Glen Langer 2009..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @todo       Must be completely rewritten.
 */
class ModuleVisitorStat extends \BackendModule
{
    /**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_visitors_be_stat';
	
	/**
	 * Kat ID
	 * @var int
	 */
	protected $intKatID;

	/**
	 * Constructor
	 */
	public function __construct()
	{
	    $this->import('BackendUser', 'User');
	    parent::__construct();
	    
	    \System::loadLanguageFile('tl_visitors_stat_export');
	    
	    if (\Input::get('act',true)=='zero') 
	    {
	    	$this->setZero();
	    }
	    
	    if (\Input::get('act',true)=='zerobrowser') 
	    {
	    	$this->setZeroBrowser();
	    }
	    
	    if (\Input::post('act',true)=='export') //action Export
	    {
	        $this->generateExport();
	    }
	    
	    if (\Input::post('id')>0) //Auswahl im Statistikmenü
	    {
	    	$this->intKatID = preg_replace('@\D@', '', \Input::post('id')); //  only digits
	    } 
	    elseif (\Input::get('id')>0) //Auswahl in der Kategorieübersicht
	    {
	    	$this->intKatID = preg_replace('@\D@', '', \Input::get('id')); //  only digits
	    }
	    else 
	    {
	    	$this->intKatID = 0;
	    }
	}
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
	    $intCatIdAllowed = false;
	    /*
	    if ($this->intKatID == 0) //direkter Aufruf ohne ID 
	    { 
	        $objVisitorsKatID = \Database::getInstance()
	                ->prepare("SELECT
                                    MIN(pid) AS ANZ 
                                FROM 
                                    tl_visitors")
	                ->execute();
    	    $objVisitorsKatID->next();
    	    if ($objVisitorsKatID->ANZ === null) 
    	    {
    	    	$this->intKatID = 0;
    	    } 
    	    else 
    	    {
    	        $this->intKatID = $objVisitorsKatID->ANZ;
    	    }
	    }*/
	    
	    //alle Kategorien holen die der User sehen darf
	    $arrVisitorCategories = $this->getVisitorCategoriesByUsergroups();
	    // no categories : array('id' => '0', 'title' => '---------');
	    // empty array   : array('id' => '0', 'title' => '---------');
	    // array[0..n]   : array(0, array('id' => '1', ....), 1, ....)
	    
	    if ($this->intKatID == 0) //direkter Aufruf ohne ID
	    {
	        $this->intKatID = $this->getCatIdByCategories($arrVisitorCategories);
	    }
	    else
        {
            // ID des Aufrufes erlaubt?
            foreach ($arrVisitorCategories as $value)
            {
                if ($this->intKatID == $value['id'])
                {
                    $intCatIdAllowed = true;
                }
            }
            if ( false === $intCatIdAllowed )
            {
            	$this->intKatID = $this->getCatIdByCategories($arrVisitorCategories);
            }
	    }
	    
		// Alle Zähler je Kat holen, die Aktiven zuerst
		$objVisitorsX = \Database::getInstance()
            		        ->prepare("SELECT 
                                            id 
                                        FROM 
                                            tl_visitors 
                                        WHERE 
                                            pid=? 
                                        ORDER BY 
                                            published DESC,id")
                            ->execute($this->intKatID);
		$intRowsX = $objVisitorsX->numRows;
		$intAnzCounter=0;
		if ($intRowsX>0) 
		{
			//Vorbereiten Chart
			$ModuleVisitorCharts = new \Visitors\ModuleVisitorCharts();
			$ModuleVisitorCharts->setName($GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['visit'].' (<span style="color:red">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['chart_red'].'</span>)');
			$ModuleVisitorCharts->setName2($GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['hit'].' (<span style="color:green">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['chart_green'].'</span>)');
			$ModuleVisitorCharts->setHeight(270); // setMaxvalueHeight + 20 + 20 +10
			$ModuleVisitorCharts->setWidth(330);
			$ModuleVisitorCharts->setMaxvalueHeight(220); // Balkenhöhe setzen
			
			while ($objVisitorsX->next()) 
			{
		        // 14 Tages Stat [0..13] und Vorgabewerte [100,104,110] (ehemals 7)
		        $arrVisitorsStatDays[$intAnzCounter]    = $this->getSevenDays($this->intKatID,$objVisitorsX->id);
				$objVisitorsID = $arrVisitorsStatDays[$intAnzCounter][104]['VisitorsID'];
		        
				//Monat Stat
				$arrVisitorsStatMonth[$intAnzCounter]   = $this->getMonth($objVisitorsID);
				
				//Other Monat Stat
				$arrVisitorsStatOtherMonth[$intAnzCounter]   = $this->getOtherMonth($objVisitorsID);
				
				//Other Year Stat
				$arrVisitorsStatOtherYears[$intAnzCounter]   = $this->getOtherYears($objVisitorsID);
								
				//Total Visits Hits
				$arrVisitorsStatTotal[$intAnzCounter]   = $this->getTotal($objVisitorsID);
				
				// Durchschnittswerte
			    $arrVisitorsStatAverage[$intAnzCounter] = $this->getAverage($objVisitorsID);
			    
				// Week Stat
				$arrVisitorsStatWeek[$intAnzCounter]    = $this->getWeeks($objVisitorsID);
				
				// Online
				$arrVisitorsStatOnline[$intAnzCounter]  = $this->getVisitorsOnline($objVisitorsID);
				
				//BestDay
				$arrVisitorsStatBestDay[$intAnzCounter] = $this->getBestDay($objVisitorsID);
				
				//BadDay
				$arrVisitorsStatBadDay[$intAnzCounter]  = $this->getBadDay($objVisitorsID);
				
				//Chart
				//Debug log_message(print_r(array_reverse($arrVisitorsStatDays[$intAnzCounter]),true), 'debug.log');
				$day  = 0;
				$days = count($arrVisitorsStatDays[$intAnzCounter]);
				foreach (array_reverse($arrVisitorsStatDays[$intAnzCounter]) as $key => $valuexy)
				{
					if (isset($valuexy['visitors_date_ymd'])) 
					{
					    $day++;
					    if ($days - $day < 17) 
					    {
    						//Debug log_message(print_r(substr($valuexy['visitors_date'],0,2),true), 'debug.log');
    						//Debug log_message(print_r($valuexy['visitors_visit'],true), 'debug.log');
    						// chart resetten, wie? fehlt noch
    						$ModuleVisitorCharts->addX(substr($valuexy['visitors_date_ymd'],8,2).'<br>'.substr($valuexy['visitors_date_ymd'],5,2));
    
    						$ModuleVisitorCharts->addY(str_replace(array('.',',',' ','\''),array('','','',''),$valuexy['visitors_visit'])); // Formatierte Zahl wieder in reine Zahl
    
    						$ModuleVisitorCharts->addY2(str_replace(array('.',',',' ','\''),array('','','',''),$valuexy['visitors_hit'])); // Formatierte Zahl wieder in reine Zahl
					    }
					}
				}
				$arrVisitorsChart[$intAnzCounter] = $ModuleVisitorCharts->display(false);
				
				//Page Hits
				$arrVisitorsPageVisitHits[$intAnzCounter]          = \Visitors\ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitTop($objVisitorsID,20);
				$arrVisitorsPageVisitHitsDays[$intAnzCounter]      = \Visitors\ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitDays($objVisitorsID,7,5);
				$arrVisitorsPageVisitHitsToday[$intAnzCounter]     = \Visitors\ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitToday($objVisitorsID,5);
				$arrVisitorsPageVisitHitsYesterday[$intAnzCounter] = \Visitors\ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitYesterday($objVisitorsID,5);
				
				//Browser
				$arrVSB = $this->getBrowserTop($objVisitorsID);
				$arrVisitorsStatBrowser[$intAnzCounter] = $arrVSB['TOP'];
				$arrVisitorsStatBrowser2[$intAnzCounter] = $arrVSB['TOP2'];
				$arrVisitorsStatBrowserDefinition[$intAnzCounter] = $arrVSB['DEF'];
				
				//Referrer
				$arrVisitorsStatReferrer[$intAnzCounter] = $this->getReferrerTop($objVisitorsID);
				
				//Screen Resolutions
				$arrVisitorsScreenTopResolution[$intAnzCounter]     = \Visitors\ModuleVisitorStatScreenCounter::getInstance()->generateScreenTopResolution($objVisitorsID,20);
				$arrVisitorsScreenTopResolutionDays[$intAnzCounter] = \Visitors\ModuleVisitorStatScreenCounter::getInstance()->generateScreenTopResolutionDays($objVisitorsID,20,30);
				
				$intAnzCounter++;
			} //while X next
		} // if intRowsX >0

		if (!isset($GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['footer'])) 
		{
		    $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['footer'] = '';
		}
		// Version, Base, Footer
		$this->Template->visitors_version = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['modname'] . ' ' . VISITORS_VERSION .'.'. VISITORS_BUILD;
		$this->Template->visitors_base    = \Environment::get('base');
		$this->Template->visitors_footer  = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['footer'];
		$this->Template->theme            = $this->getTheme();
		$this->Template->theme0           = 'default'; // for down0.gif

		
		$this->Template->visitorsanzcounter   	   = $intAnzCounter;
		$this->Template->visitorsstatDays     	   = $arrVisitorsStatDays;
		$this->Template->visitorsstatWeeks    	   = $arrVisitorsStatWeek;
		$this->Template->visitorsstatMonths   	   = $arrVisitorsStatMonth;
		$this->Template->visitorsstatOtherMonths   = $arrVisitorsStatOtherMonth;
		$this->Template->visitorsstatOtherYears    = $arrVisitorsStatOtherYears;
		$this->Template->visitorsstatTotals   	   = $arrVisitorsStatTotal;
		$this->Template->visitorsstatAverages 	   = $arrVisitorsStatAverage;
		$this->Template->visitorsstatOnline        = $arrVisitorsStatOnline;
		$this->Template->visitorsstatBestDay       = $arrVisitorsStatBestDay;
		$this->Template->visitorsstatBadDay        = $arrVisitorsStatBadDay;
		$this->Template->visitorsstatChart    	   = $arrVisitorsChart;
		$this->Template->visitorsstatPageVisitHits          = $arrVisitorsPageVisitHits;
		$this->Template->visitorsstatPageVisitHitsDays      = $arrVisitorsPageVisitHitsDays;
		$this->Template->visitorsstatPageVisitHitsToday     = $arrVisitorsPageVisitHitsToday;
		$this->Template->visitorsstatPageVisitHitsYesterday = $arrVisitorsPageVisitHitsYesterday;
		$this->Template->visitorsstatBrowser  	   = $arrVisitorsStatBrowser;
		$this->Template->visitorsstatBrowser2  	   = $arrVisitorsStatBrowser2;
		$this->Template->visitorsstatBrowserDefinition = $arrVisitorsStatBrowserDefinition;
		$this->Template->visitorsstatReferrer      = $arrVisitorsStatReferrer;
		$this->Template->visitorsstatScreenTop     = $arrVisitorsScreenTopResolution;
		$this->Template->visitorsstatScreenTopDays = $arrVisitorsScreenTopResolutionDays;
		
		//Debug log_message(print_r($this->Template->visitorsstatBrowser,true), 'debug.log');
		//Debug log_message(print_r($this->Template->visitorsstatAverages,true), 'debug.log');

		// Kat sammeln
		$this->Template->visitorskats          = $arrVisitorCategories;
		$this->Template->visitorskatid         = $this->intKatID;
		$this->Template->visitorsstatkat       = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['kat'];
		$this->Template->visitors_export_title = $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_button_title'];
		$this->Template->visitors_exportfield  = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['kat'].' '.$GLOBALS['TL_LANG']['tl_visitors_stat_export']['export'];
        $this->Template->visitors_base_be      = \Environment::get('base') . 'contao';
		
        //ExportDays
        $this->Template->visitors_export_days   = (isset($_SESSION['VISITORS_EXPORT_DAYS'])) ? $_SESSION['VISITORS_EXPORT_DAYS'] : 365;
        
		//SearchEngines
		$arrSE = $this->getSearchEngine($this->intKatID);
		if ($arrSE !== false) 
		{
			$this->Template->visitorssearchengines        = $arrSE['SearchEngines'];
			$this->Template->visitorssearchenginekeywords = $arrSE['SearchEngineKeywords'];
		} 
		else 
		{
			$this->Template->visitorssearchengine = false;
		}
	}
	
	/**
	 * 14 Tagesstat und Vorgabewerte
	 * 
	 */
	protected function getSevenDays($KatID, $VisitorsXid)
	{
		$visitors_today_visit     = 0;
		$visitors_today_hit       = 0;
		$visitors_yesterday_visit = 0;
		$visitors_yesterday_hit   = 0;
		$visitors_visit_start     = 0;
		$visitors_hit_start       = 0;
		$visitors_day_of_week_prefix = '';
		//Anzahl Tage zu erst auslesen die angezeigt werden sollen
		$objVisitors = \Database::getInstance()->prepare("SELECT tv.visitors_statistic_days FROM tl_visitors tv WHERE tv.pid = ? AND tv.id = ?")
                                               ->limit(1)
                                               ->execute($KatID, $VisitorsXid);
		while ($objVisitors->next())
		{
		    $visitors_statistic_days = $objVisitors->visitors_statistic_days;
		}
		if ($visitors_statistic_days < 14) 
		{
			$visitors_statistic_days = 14;
		}
		if ($visitors_statistic_days > 99)
		{
		    $visitors_statistic_days = 99;
		}
	    // 7 Tages Statistik und Vorgabewerte
	    $objVisitors = \Database::getInstance()
	            ->prepare("SELECT 
                                tv.id,
                                tv.visitors_name,
                                tv.visitors_startdate,
                                tv.visitors_visit_start,
                                tv.visitors_hit_start,
                                tv.published,
                                tvc.visitors_date,
                                tvc.visitors_visit,
                                tvc.visitors_hit
                            FROM
                                tl_visitors tv,
                                tl_visitors_counter tvc
                            WHERE
                                tv.id = tvc.vid AND tv.pid = ? AND tv.id = ?
                            ORDER BY tv.visitors_name , tvc.visitors_date DESC")
                ->limit($visitors_statistic_days)
                ->execute($KatID, $VisitorsXid);
		$intRowsVisitors = $objVisitors->numRows;
		if ($intRowsVisitors>0) 
		{ // Zählungen vorhanden
		    while ($objVisitors->next())
    		{
    		    if ($objVisitors->published == 1) 
    		    {
    		        
    		        $objVisitors->published = '<span class="visitors_stat_yes">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_yes'].'</span>';
    		    } 
    		    else 
    		    {
    		    	$objVisitors->published = '<span class="visitors_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_no'].'</span>';
    		    }
    		    if (!strlen($objVisitors->visitors_startdate)) 
    		    {
    		    	$visitors_startdate = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['startdate_not_defined'];
    		    } 
    		    else 
    		    {
    		        $visitors_startdate = $this->parseDateVisitors($GLOBALS['TL_LANGUAGE'], $objVisitors->visitors_startdate);
    		    }
    		    // day of the week prüfen
    		    if (strpos($GLOBALS['TL_CONFIG']['dateFormat'],'D')===false  // day of the week short 
    		     && strpos($GLOBALS['TL_CONFIG']['dateFormat'],'l')===false) // day of the week long
    		    {
    		        $visitors_day_of_week_prefix = 'D, ';
    		    }
    		    $arrVisitorsStat[] = array
    			(
    			    'visitors_id'           => $objVisitors->id,
    				'visitors_name'         => specialchars(ampersand($objVisitors->visitors_name)),
    				'visitors_active'       => $objVisitors->published,
    				'visitors_date'         => $this->parseDateVisitors($GLOBALS['TL_LANGUAGE'], strtotime($objVisitors->visitors_date), $visitors_day_of_week_prefix),
    				'visitors_date_ymd'     => $objVisitors->visitors_date,
    				'visitors_startdate'    => $visitors_startdate,
    				'visitors_visit'        => $this->getFormattedNumber($objVisitors->visitors_visit,0),
    				'visitors_hit'          => $this->getFormattedNumber($objVisitors->visitors_hit,0)
	            );
	            if ($objVisitors->visitors_date == date("Y-m-d")) 
	            {
	                $visitors_today_visit = $objVisitors->visitors_visit;
	                $visitors_today_hit   = $objVisitors->visitors_hit;
	            }
	            if ($objVisitors->visitors_date == date("Y-m-d", time()-(60*60*24))) 
	            {
	                $visitors_yesterday_visit = $objVisitors->visitors_visit;
	                $visitors_yesterday_hit   = $objVisitors->visitors_hit;
	            }
    		} // while
    		$arrVisitorsStat[104]['VisitorsID'] = $objVisitors->id;
			$visitors_visit_start = $objVisitors->visitors_visit_start;
			$visitors_hit_start   = $objVisitors->visitors_hit_start;
		} 
		else 
		{
			$objVisitors = \Database::getInstance()
			        ->prepare("SELECT 
                                    tv.id, 
                                    tv.visitors_name, 
                                    tv.visitors_startdate, 
                                    tv.published
                                FROM
                                    tl_visitors tv
                                WHERE
                                    tv.pid = ? AND tv.id = ?
                                ORDER BY tv.visitors_name")
                    ->limit(1)
                    ->execute($KatID, $VisitorsXid);
			$objVisitors->next();
			if ($objVisitors->published == 1) 
			{
		        $objVisitors->published = '<span class="visitors_stat_yes">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_yes'].'</span>';
		    } 
		    else 
		    {
		    	$objVisitors->published = '<span class="visitors_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_no'].'</span>';
		    }
		    if (!strlen($objVisitors->visitors_startdate)) 
		    {
		    	$visitors_startdate = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['startdate_not_defined'];
		    } 
		    else 
		    {
		        $visitors_startdate = $this->parseDateVisitors($GLOBALS['TL_LANGUAGE'],$objVisitors->visitors_startdate);
		    }
		    $arrVisitorsStat[] = array
			(
			    'visitors_id'           => $objVisitors->id,
				'visitors_name'         => specialchars(ampersand($objVisitors->visitors_name)),
				'visitors_active'       => $objVisitors->published,
				'visitors_startdate'    => $visitors_startdate
            );
		    $arrVisitorsStat[104]['VisitorsID'] = 0;
		}
		$arrVisitorsStat[100]['visitors_today_visit']     = $this->getFormattedNumber($visitors_today_visit,0);
	    $arrVisitorsStat[100]['visitors_today_hit']       = $this->getFormattedNumber($visitors_today_hit,0);
	    $arrVisitorsStat[100]['visitors_yesterday_visit'] = $this->getFormattedNumber($visitors_yesterday_visit,0);
	    $arrVisitorsStat[100]['visitors_yesterday_hit']   = $this->getFormattedNumber($visitors_yesterday_hit,0);
	    $arrVisitorsStat[110]['visitors_visit_start']     = $this->getFormattedNumber($visitors_visit_start,0);
		$arrVisitorsStat[110]['visitors_hit_start']       = $this->getFormattedNumber($visitors_hit_start,0);
		return $arrVisitorsStat;
	}
	
	/**
	 * Monatswerte (Aktueller und Letzer)
	 * 
	 */
	protected function getMonth($VisitorsID)
	{
	    $LastMonthVisits    = 0;
	    $LastMonthHits      = 0;
	    $CurrentMonthVisits = 0;
	    $CurrentMonthHits   = 0;
	    $YearCurrentMonth   = date('Y-m-d');
	    $YearLastMonth      = date('Y-m-d' ,mktime(0, 0, 0, date("m")-1, 1, date("Y")));
	    $CurrentMonth   	= (int)date('m');
	    $LastMonth			= (int)date('m',mktime(0, 0, 0, date("m")-1, 1, date("Y")));
	    $ORDER = ($CurrentMonth > $LastMonth) ? 'DESC' : 'ASC'; // damit immer eine absteigene Monatsreihenfolge kommt
		if ($VisitorsID) 
		{
		    $sqlMonth = 'SELECT 
                                EXTRACT( MONTH FROM visitors_date ) AS M, 
                                SUM( visitors_visit ) AS SUMV , 
                                SUM( visitors_hit ) AS SUMH 
                            FROM 
                                tl_visitors_counter 
                            WHERE 
                                vid=? AND visitors_date BETWEEN ? AND ?
                            GROUP BY M
                            ORDER BY M %s';
		    $sqlMonth = sprintf($sqlMonth, $ORDER);
		    
			//Total je Monat (aktueller und letzter)
			$objVisitorsToMo = \Database::getInstance()
            			        ->prepare($sqlMonth)
                                ->limit(2)
                                ->execute($VisitorsID, $YearLastMonth, $YearCurrentMonth);
			$intRows = $objVisitorsToMo->numRows;
			if ($intRows>0) 
			{ 
			    $objVisitorsToMo->next();
			    if ( (int)$objVisitorsToMo->M == (int)date('m') ) 
			    {
			    	$CurrentMonthVisits = $objVisitorsToMo->SUMV;
			    	$CurrentMonthHits   = $objVisitorsToMo->SUMH;
			    }
			    if ( (int)$objVisitorsToMo->M == (int)date('m',mktime(0, 0, 0, date("m")-1, 1, date("Y"))) ) 
			    {
		            $LastMonthVisits = $objVisitorsToMo->SUMV;
		            $LastMonthHits   = $objVisitorsToMo->SUMH;
	            }
			    if ($intRows==2) 
			    {
	                $objVisitorsToMo->next();
	                if ( (int)$objVisitorsToMo->M == (int)date('m',mktime(0, 0, 0, date("m")-1, 1, date("Y"))) ) 
	                {
		        	    $LastMonthVisits = $objVisitorsToMo->SUMV;
		                $LastMonthHits   = $objVisitorsToMo->SUMH;
	                }
			    }
			}
		}
		return array('LastMonthVisits'    => $this->getFormattedNumber($LastMonthVisits,0),
		             'LastMonthHits'      => $this->getFormattedNumber($LastMonthHits,0),
		             'CurrentMonthVisits' => $this->getFormattedNumber($CurrentMonthVisits,0),
		             'CurrentMonthHits'   => $this->getFormattedNumber($CurrentMonthHits,0)
		             );

	}
	
	/**
	 * Monatswerte (Vorletzter und älter, max 10)
	 * 
	 */
	protected function getOtherMonth($VisitorsID)
	{
	    $StartMonth = date('Y-m-d',mktime(0, 0, 0, date("m")-11, 1, date("Y"))); // aktueller Monat -11
	    $EndMonth   = date('Y-m-d',mktime(0, 0, 0, date("m")-1 , 0, date("Y"))); // letzter Tag des vorletzten Monats
		if ($VisitorsID) 
		{
			//Total je Monat (aktueller und letzter)
            $objVisitorsToMo = \Database::getInstance()
			        ->prepare('SELECT 
                                    EXTRACT( YEAR FROM visitors_date ) AS Y, 
                                    EXTRACT( MONTH FROM visitors_date ) AS M, 
                                    SUM( visitors_visit ) AS SUMV, 
                                    SUM( visitors_hit ) AS SUMH 
                                 FROM 
                                    tl_visitors_counter 
                                 WHERE 
                                    vid=? AND visitors_date BETWEEN ? AND ?
                                 GROUP BY Y, M
                                 ORDER BY Y DESC, M DESC')
                    ->execute($VisitorsID,$StartMonth,$EndMonth);
			$intRows = $objVisitorsToMo->numRows;
			$arrOtherMonth = array();
			if ($intRows>0) 
			{ 
				while ($objVisitorsToMo->next()) 
				{
					$arrOtherMonth[] = array($objVisitorsToMo->Y,
					                         $GLOBALS['TL_LANG']['MONTHS'][($objVisitorsToMo->M - 1)],
					                         $this->getFormattedNumber($objVisitorsToMo->SUMV,0),
					                         $this->getFormattedNumber($objVisitorsToMo->SUMH,0)
					                        );
				}
			}
		}
		return $arrOtherMonth;
	}
	
	/**
	 * Jahreswerte (Aktuelles und älter (10) = max 11)
	 *
	 */
	protected function getOtherYears($VisitorsID)
	{
	    $StartYear = date('Y-m-d',mktime(0, 0, 0, 1, 1, date("Y")-11)); // aktuelles Jahr -11
	    $EndYear   = date('Y-m-d'); // Aktuelles Datum
	    if ($VisitorsID)
	    {
	        //Total je Monat (aktueller und letzter)
	        $objVisitorsToYear = \Database::getInstance()
                                    ->prepare('SELECT
                                                EXTRACT( YEAR FROM visitors_date ) AS Y,
                                                SUM( visitors_visit ) AS SUMV,
                                                SUM( visitors_hit ) AS SUMH
                                             FROM
                                                tl_visitors_counter
                                             WHERE
                                                vid=? AND visitors_date BETWEEN ? AND ?
                                             GROUP BY Y
                                             ORDER BY Y DESC')
	                                 ->execute($VisitorsID,$StartYear,$EndYear);
	        $intRows = $objVisitorsToYear->numRows;
	        $arrOtherYear = array();
	        if ($intRows>0)
	        {
	            while ($objVisitorsToYear->next())
	            {
	                $arrOtherYear[] = array($objVisitorsToYear->Y,
	                    $this->getFormattedNumber($objVisitorsToYear->SUMV,0),
	                    $this->getFormattedNumber($objVisitorsToYear->SUMH,0)
	                );
	            }
	        }
	    }
	    return $arrOtherYear;
	}
	
	/**
	 * Average Stat
	 * 
	 */
	protected function getAverage($VisitorsID)
	{
    	$VisitorsAverageVisits   = 0;
    	$VisitorsAverageHits     = 0;
    	$VisitorsAverageVisits30 = 0;
    	$VisitorsAverageHits30   = 0;
    	$VisitorsAverageVisits60 = 0;
    	$VisitorsAverageHits60   = 0;
    	$tmpTotalDays            = 0;
    	
		if ($VisitorsID) 
		{
			$today     = date('Y-m-d');
			$yesterday = date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
    	    //Durchschnittswerte bis heute 00:00 Uhr, also bis einschließlich gestern
    	    $objVisitorsAverageCount = \Database::getInstance()
    	            ->prepare("SELECT 
                                    SUM(visitors_visit) AS SUMV, 
                                    SUM(visitors_hit) AS SUMH, 
                                    MIN( visitors_date ) AS MINDAY
                                FROM 
                                    tl_visitors_counter
                                WHERE 
                                    vid=? AND visitors_date<?")
                    ->execute($VisitorsID,$today);
            if ($objVisitorsAverageCount->numRows > 0) 
            {
                $objVisitorsAverageCount->next();
                $tmpTotalDays = floor(1+(strtotime($yesterday) - strtotime($objVisitorsAverageCount->MINDAY))/60/60/24 );

                $VisitorsAverageVisitCount = ($objVisitorsAverageCount->SUMV === null) ? 0 : $objVisitorsAverageCount->SUMV;
                $VisitorsAverageHitCount   = ($objVisitorsAverageCount->SUMH === null) ? 0 : $objVisitorsAverageCount->SUMH;
                if ($tmpTotalDays >0) 
                {
	                $VisitorsAverageVisits = $this->getFormattedNumber($VisitorsAverageVisitCount / $tmpTotalDays , 2);
	                $VisitorsAverageHits   = $this->getFormattedNumber($VisitorsAverageHitCount   / $tmpTotalDays , 2);
                }
            }
            if ($tmpTotalDays > 30) 
            {
	            //Durchschnittswerte der letzten 30 Tage
	            $day30 = date('Y-m-d',mktime(0, 0, 0, date("m")-1 , date("d")-1 ,date("Y")));            
			
	            $objVisitorsAverageCount = \Database::getInstance()
	                    ->prepare("SELECT 
                                        SUM(visitors_visit) AS SUMV, 
                                        SUM(visitors_hit) AS SUMH
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=? AND visitors_date BETWEEN ? AND ?")
                        ->execute($VisitorsID,$day30,$yesterday);
	            if ($objVisitorsAverageCount->numRows > 0) 
	            {
	                $objVisitorsAverageCount->next();
	                $VisitorsAverageVisitCount = ($objVisitorsAverageCount->SUMV === null) ? 0 : $objVisitorsAverageCount->SUMV;
	                $VisitorsAverageHitCount   = ($objVisitorsAverageCount->SUMH === null) ? 0 : $objVisitorsAverageCount->SUMH;
	                $VisitorsAverageVisits30 = $this->getFormattedNumber($VisitorsAverageVisitCount / 30 , 2);
	                $VisitorsAverageHits30   = $this->getFormattedNumber($VisitorsAverageHitCount   / 30 , 2);
	            }
            }
            if ($tmpTotalDays > 60) 
            {
	            //Durchschnittswerte der letzten 60 Tage
	            $day60 = date('Y-m-d',mktime(0, 0, 0, date("m")-2 , date("d")-1 ,date("Y")));
	
	            $objVisitorsAverageCount = \Database::getInstance()
	                    ->prepare("SELECT 
                                        SUM(visitors_visit) AS SUMV, 
                                        SUM(visitors_hit) AS SUMH
                                    FROM 
                                        tl_visitors_counter
                                    WHERE 
                                        vid=? AND visitors_date BETWEEN ? AND ?")
                        ->execute($VisitorsID,$day60,$yesterday);
	            if ($objVisitorsAverageCount->numRows > 0) 
	            {
	                $objVisitorsAverageCount->next();
	                $VisitorsAverageVisitCount = ($objVisitorsAverageCount->SUMV === null) ? 0 : $objVisitorsAverageCount->SUMV;
	                $VisitorsAverageHitCount   = ($objVisitorsAverageCount->SUMH === null) ? 0 : $objVisitorsAverageCount->SUMH;
	                $VisitorsAverageVisits60 = $this->getFormattedNumber($VisitorsAverageVisitCount / 60 , 2);
	                $VisitorsAverageHits60   = $this->getFormattedNumber($VisitorsAverageHitCount   / 60 , 2);
	            }
            }
	    }
	    return array('VisitorsAverageVisits'   => $VisitorsAverageVisits,
	    			 'VisitorsAverageHits'     => $VisitorsAverageHits,
	    			 'VisitorsAverageDays'     => "&nbsp;", //$tmpTotalDays,
	    			 'VisitorsAverageVisits30' => $VisitorsAverageVisits30,
	    			 'VisitorsAverageHits30'   => $VisitorsAverageHits30,
	    			 'VisitorsAverageDays30'   => ($VisitorsAverageHits30 === 0) ? '<span class="mod_visitors_be_average_nodata">(30)&nbsp;</span>' : '(30)&nbsp;',
	    			 'VisitorsAverageVisits60' => $VisitorsAverageVisits60,
	    			 'VisitorsAverageHits60'   => $VisitorsAverageHits60,
	    			 'VisitorsAverageDays60'   => ($VisitorsAverageHits60 === 0) ? '<span class="mod_visitors_be_average_nodata">(60)&nbsp;</span>' : '(60)&nbsp;',
	                );
	}
	
	/**
	 * Wochenwerte
	 */
	protected function getWeeks($VisitorsID)
	{
	    /*
	     * YEARWEEK('2013-12-31', 3) ergibt 201401 (iso Jahr der iso Woche)!
	     * muss so sein, da sonst between nicht funktioniert 
	     * (Current muss größer sein als Last)
	     * daher muss PHP auch das ISO Jahr zurückgeben!
	     */
	    
	    $LastWeekVisits    = 0;
	    $LastWeekHits      = 0;
	    $CurrentWeekVisits = 0;
	    $CurrentWeekHits   = 0;
	    $CurrentWeek       = date('W'); 
	    $LastWeek          = date('W', mktime(0, 0, 0, date("m"), date("d")-7, date("Y")) );
        $YearCurrentWeek   = date('o');
        $YearLastWeek      = date('o', mktime(0, 0, 0, date("m"), date("d")-7, date("Y")) );
	    
	    if ($VisitorsID) 
	    {
    		//Total je Woche (aktuelle und letzte)
    		$objVisitorsToWe = \Database::getInstance()
    		        ->prepare('SELECT 
                                    YEARWEEK(visitors_date, 3) AS YW,
                                    SUM(visitors_visit) AS SUMV,
                                    SUM(visitors_hit) AS SUMH
                                FROM
                                    tl_visitors_counter
                                WHERE
                                    vid = ? AND YEARWEEK(visitors_date, 3) BETWEEN ? AND ?
                                GROUP BY YW
                                ORDER BY YW DESC')
                    ->limit(2)
                    ->execute($VisitorsID,$YearLastWeek.$LastWeek,$YearCurrentWeek.$CurrentWeek);
    		$intRows = $objVisitorsToWe->numRows;
    		if ($intRows>0) 
    		{ 
    		    $objVisitorsToWe->next();
    		    if ($objVisitorsToWe->YW == $YearCurrentWeek.$CurrentWeek) 
    		    {
		    		$CurrentWeekVisits = $objVisitorsToWe->SUMV;
				    $CurrentWeekHits   = $objVisitorsToWe->SUMH;
    		    }
    		    if ($objVisitorsToWe->YW == $YearLastWeek.$LastWeek) 
    		    {
    		    	$LastWeekVisits = $objVisitorsToWe->SUMV;
			        $LastWeekHits   = $objVisitorsToWe->SUMH;
    		    }
    		    if ($intRows==2) 
    		    {
                    $objVisitorsToWe->next();
                    if ($objVisitorsToWe->YW == $YearLastWeek.$LastWeek) 
                    {
		                $LastWeekVisits = $objVisitorsToWe->SUMV;
			            $LastWeekHits   = $objVisitorsToWe->SUMH;
                    }
    		    }
    		}
	    }
	    return array('LastWeekVisits'   => $this->getFormattedNumber($LastWeekVisits,0),
	                 'LastWeekHits'     => $this->getFormattedNumber($LastWeekHits,0),
	                 'CurrentWeekVisits'=> $this->getFormattedNumber($CurrentWeekVisits,0),
	                 'CurrentWeekHits'  => $this->getFormattedNumber($CurrentWeekHits,0)
	                );
	}
	
	/**
	 * Total Hits und Visits
	 */
	protected function getTotal($VisitorsID)
	{
		$VisitorsTotalVisitCount = 0;
    	$VisitorsTotalHitCount   = 0;
	    if ($VisitorsID) 
	    {
    		//Total seit Zählung
    		$objVisitorsTotalCount = \Database::getInstance()
    		        ->prepare("SELECT 
                                    SUM(visitors_visit) AS SUMV, 
                                    SUM(visitors_hit) AS SUMH
                                FROM 
                                    tl_visitors_counter
                                WHERE 
                                    vid=?")
                    ->execute($VisitorsID);
    	    if ($objVisitorsTotalCount->numRows > 0) 
    	    {
    		    $objVisitorsTotalCount->next();
                $VisitorsTotalVisitCount = ($objVisitorsTotalCount->SUMV === null) ? 0 : $objVisitorsTotalCount->SUMV;
                $VisitorsTotalHitCount   = ($objVisitorsTotalCount->SUMH === null) ? 0 : $objVisitorsTotalCount->SUMH;
    	    }
	    }
	    return array('VisitorsTotalVisitCount' => $this->getFormattedNumber($VisitorsTotalVisitCount,0),
	                 'VisitorsTotalHitCount'   => $this->getFormattedNumber($VisitorsTotalHitCount,0)
	                );
	}
	
	/**
	 * Statistic, set on zero
	 */
	protected function setZero()
	{
	    $intCID = preg_replace('@\D@', '', \Input::get('zid')); //  only digits 
	    if ($intCID>0) 
	    {
	        // mal sehen ob ein Startdatum gesetzt war
    	    $objVisitorsDate = \Database::getInstance()
    	            ->prepare("SELECT 
                                    visitors_startdate 
                                FROM 
                                    tl_visitors 
                                WHERE 
                                    id=?")
                    ->execute($intCID);
    	    $objVisitorsDate->next();
    	    if (0 < (int)$objVisitorsDate->visitors_startdate) 
    	    {
    	        // ok es war eins gesetzt, dann setzen wir es wieder
                \Database::getInstance()
                        ->prepare("UPDATE tl_visitors 
                                    SET 
                                        tstamp=?, 
                                        visitors_startdate=? 
                                    WHERE 
                                        id=?")
                        ->execute( time(), strtotime(date('Y-m-d')), $intCID );
    	    }
    	    // und nun die eigendlichen counter
    	    \Database::getInstance()
    	            ->prepare("DELETE FROM tl_visitors_counter WHERE vid=?")
                    ->execute($intCID);
            \Database::getInstance()
                    ->prepare("DELETE FROM tl_visitors_blocker WHERE vid=?")
                    ->execute($intCID);
            \Database::getInstance()
                    ->prepare("DELETE FROM tl_visitors_browser WHERE vid=?")
                    ->execute($intCID);
            \Database::getInstance()
                    ->prepare("DELETE FROM tl_visitors_pages WHERE vid=?")
                    ->execute($intCID);
            \Database::getInstance()
                    ->prepare("DELETE FROM tl_visitors_screen_counter WHERE vid=?")
                    ->execute($intCID);
	    }
	    return ;
	}
	
	/**
	 * Statistic, set on zero
	 */
	protected function setZeroBrowser()
	{
	    $intCID = preg_replace('@\D@', '', \Input::get('zid')); //  only digits 
	    if ($intCID>0) 
	    {
	        // Browser 
            \Database::getInstance()
                    ->prepare("DELETE FROM tl_visitors_browser WHERE vid=?")
                    ->execute($intCID);
	    }
	    return ;
	}
	
	/**
	 * TOP Browser
	 */
	protected function getBrowserTop($VisitorsID)
	{
		$topNo = 20;
		$VisitorsBrowserVersion = array();
		$VisitorsBrowserLang    = array();
		$VisitorsBrowserOS      = array();
	    if ($VisitorsID) 
	    {
    		//Version
    		$objVisitorsBrowserVersion = \Database::getInstance()
    		        ->prepare("SELECT 
                                    `visitors_browser`, 
                                    SUM(`visitors_counter`) AS SUMBV
                                FROM 
                                    tl_visitors_browser
                                WHERE 
                                    vid=? 
                                    AND visitors_browser !=? 
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?
                                GROUP BY `visitors_browser`
                                ORDER BY SUMBV DESC, visitors_browser ASC")
                    ->limit($topNo)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserVersion->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserVersion->next()) 
    		    {
    		    	$VisitorsBrowserVersion[] = array($objVisitorsBrowserVersion->visitors_browser, 
                                                      $objVisitorsBrowserVersion->SUMBV);
    		    }
    	    }
    	    //Version without number
    	    $objVisitorsBrowserVersion2 = \Database::getInstance()
    	            ->prepare("SELECT 
                                    visitors_browser, 
                                    SUM(`visitors_counter`) AS SUMBV 
                                FROM
                                    (SELECT 
                                        SUBSTRING_INDEX(`visitors_browser`,' ',1) AS visitors_browser, 
                                        `visitors_counter`
                                    FROM tl_visitors_browser
                                    WHERE vid=?
                                    AND visitors_browser !=?
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?
                                    ) AS A
                                GROUP BY `visitors_browser`
                                ORDER BY SUMBV DESC, visitors_browser ASC")
                    ->limit(10)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserVersion2->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserVersion2->next()) 
    		    {
    		    	$VisitorsBrowserVersion2[] = array($objVisitorsBrowserVersion2->visitors_browser, 
                                                       $objVisitorsBrowserVersion2->SUMBV);
    		    }
    	    }
    	    // Unknown Version
    	    $objVisitorsBrowserVersion = \Database::getInstance()
    	            ->prepare("SELECT 
                                    SUM(`visitors_counter`) AS SUMBV
                                FROM 
                                    `tl_visitors_browser`
                                WHERE 
                                    `vid`=?
                                    AND `visitors_browser` =?
                                    AND `visitors_os` =?")
                                    //AND (visitors_browser =? OR SUBSTRING_INDEX(`visitors_browser`,' ',1) =?)
                                //GROUP BY `visitors_browser`
                                //ORDER BY SUMBV DESC, visitors_browser ASC"
                    ->limit(1)
                    ->execute($VisitorsID,'Unknown','Unknown'); // ,'Mozilla'
    	    if ($objVisitorsBrowserVersion->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserVersion->next()) 
    		    {
    		    	$VisitorsBrowserVersionUNK = $objVisitorsBrowserVersion->SUMBV;
    		    }
    	    }
    	    //Count all versions
    	    $objVisitorsBrowserVersion = \Database::getInstance()
    	            ->prepare("SELECT 
                                    COUNT(DISTINCT `visitors_browser`) AS SUMBV
                                FROM 
                                    tl_visitors_browser
                                WHERE 
                                    vid=? 
                                    AND visitors_browser !=?
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?")
                    ->limit(1)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserVersion->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserVersion->next()) 
    		    {
    		    	$VisitorsBrowserVersionKNO = $objVisitorsBrowserVersion->SUMBV;
    		    }
    	    }
    	    //Language
    		$objVisitorsBrowserLang = \Database::getInstance()
    		        ->prepare("SELECT 
                                    `visitors_lang`, 
                                    SUM(`visitors_counter`) AS SUMBL
                                FROM 
                                    tl_visitors_browser
                                WHERE 
                                    vid=? AND `visitors_lang` !=?
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?
                                GROUP BY `visitors_lang`
                                ORDER BY SUMBL DESC, `visitors_lang` ASC")
                    ->limit($topNo)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserLang->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserLang->next()) 
    		    {
                	$VisitorsBrowserLang[] = array($objVisitorsBrowserLang->visitors_lang, 
                                                   $objVisitorsBrowserLang->SUMBL);
    		    }
    	    }
    	    //OS
    		$objVisitorsBrowserOS = \Database::getInstance()
    		        ->prepare("SELECT 
                                    `visitors_os`, 
                                    SUM(`visitors_counter`) AS SUMBOS
                                FROM 
                                    tl_visitors_browser
                                WHERE 
                                    vid=? AND visitors_os !=?
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?
                                GROUP BY `visitors_os`
                                ORDER BY SUMBOS DESC, visitors_os ASC")
                    ->limit($topNo)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserOS->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserOS->next()) 
    		    {
                	$VisitorsBrowserOS[] = array($objVisitorsBrowserOS->visitors_os, 
                                                 $objVisitorsBrowserOS->SUMBOS);
    		    }
    	    }
    	    //Count all OS
    	    $objVisitorsBrowserOS = \Database::getInstance()
    	            ->prepare("SELECT 
                                    COUNT(DISTINCT `visitors_os`) AS SUMBOS
                                FROM 
                                    tl_visitors_browser
                                WHERE 
                                    vid=? AND visitors_os !=?
                                    AND SUBSTRING_INDEX(`visitors_browser`,' ',1) !=?")
                    ->limit(1)
                    ->execute($VisitorsID,'Unknown','Mozilla');
    	    if ($objVisitorsBrowserOS->numRows > 0) 
    	    {
    		    while ($objVisitorsBrowserOS->next()) 
    		    {
                	$VisitorsBrowserOSALL = $objVisitorsBrowserOS->SUMBOS;
    		    }
    	    }
	    }
	    for ($BT=0; $BT<$topNo; $BT++)
	    {
	    	$VisitorsBrowserVersion[$BT] = (isset($VisitorsBrowserVersion[$BT][0])) ? $VisitorsBrowserVersion[$BT] : '0';
	    	$VisitorsBrowserLang[$BT]    = (isset($VisitorsBrowserLang[$BT][0]))    ? $VisitorsBrowserLang[$BT]    : '0';
	    	$VisitorsBrowserOS[$BT]      = (isset($VisitorsBrowserOS[$BT][0]))      ? $VisitorsBrowserOS[$BT]      : '0';
			//Platz 1-20 [0..19]
	    	$arrBrowserTop[$BT] = array($VisitorsBrowserVersion[$BT],
                                        $VisitorsBrowserLang[$BT],$VisitorsBrowserOS[$BT]);
	    }
		$VisitorsBrowserVersionUNK = (isset($VisitorsBrowserVersionUNK)) ? $VisitorsBrowserVersionUNK : 0;
		$VisitorsBrowserVersionKNO = (isset($VisitorsBrowserVersionKNO)) ? $VisitorsBrowserVersionKNO : 0;
		$VisitorsBrowserOSALL      = (isset($VisitorsBrowserOSALL))      ? $VisitorsBrowserOSALL      : 0;
		//Version without number
		for ($BT=0; $BT<10; $BT++)
		{
			$VisitorsBrowserVersion2[$BT] = (isset($VisitorsBrowserVersion2[$BT][0])) ? $VisitorsBrowserVersion2[$BT] : array(0,0);
		}
		//Debug log_message(print_r($VisitorsBrowserVersion2,true), 'debug.log');
	    return array('TOP' =>$arrBrowserTop
	    		    ,'TOP2'=>$VisitorsBrowserVersion2
	    		    ,'DEF' =>array('UNK'  => $VisitorsBrowserVersionUNK,
                                   'KNO'  => $VisitorsBrowserVersionKNO,
                                   'OSALL'=> $VisitorsBrowserOSALL));
	}
	
	/**
	 * TOP 20 SearchEngine
	 * 
	 * @param	integer	$VisitorsID	Category-ID des Zählers
	 * @return	array
	 */
	protected function getSearchEngine($VisitorsKatID)
	{
		$VisitorsSearchEngines        = array(); // only searchengines
		$VisitorsSearchEngineKeywords = array(); //searchengine - keywords, order by keywords
		$day60 = mktime(0, 0, 0, date("m")-2 , date("d") ,date("Y"));
		
		$objVisitors = \Database::getInstance()
		        ->prepare("SELECT 
                                tl_visitors.id AS id
                            FROM 
                                tl_visitors 
                            LEFT JOIN 
                                tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                            WHERE 
                                tl_visitors.pid=? AND published=?
                            ORDER BY id")
                ->limit(1)
                ->executeUncached($VisitorsKatID,1);
		if ($objVisitors->numRows > 0) 
		{
			$objVisitors->next();
			$VisitorsID = $objVisitors->id;
			
			$objVisitorsSearchEngines = \Database::getInstance()
			        ->prepare("SELECT 
                                    `visitors_searchengine`,
                                    count(*) as anz 
                                FROM 
                                    `tl_visitors_searchengines` 
                                WHERE 
                                    `vid`=? AND `tstamp` > ? 
                                GROUP BY 1 
                                ORDER BY anz DESC")
                    ->limit(20)
                    ->execute($VisitorsID,$day60);
			if ($objVisitorsSearchEngines->numRows > 0) 
			{
			    while ($objVisitorsSearchEngines->next()) 
			    {
			        if ('Generic' == $objVisitorsSearchEngines->visitors_searchengine)
			        {
			            $objVisitorsSearchEngines->visitors_searchengine = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['searchengine_unknown'];
			        }
	            	$VisitorsSearchEngines[] = array($objVisitorsSearchEngines->visitors_searchengine, 
                                                     $objVisitorsSearchEngines->anz);
			    }
		    }
		    
		    $objVisitorsSearchEngineKeywords = \Database::getInstance()
		            ->prepare("SELECT 
                                    `visitors_searchengine`, 
                                    lower(`visitors_keywords`) AS keyword, 
                                    count(*) AS anz 
                                FROM 
                                    `tl_visitors_searchengines` 
                                WHERE 
                                    `vid`=? AND `tstamp` > ? 
                                GROUP BY 1,2 
                                ORDER BY anz DESC")
                    ->limit(20)
                    ->execute($VisitorsID,$day60);
			if ($objVisitorsSearchEngineKeywords->numRows > 0) 
			{
			    while ($objVisitorsSearchEngineKeywords->next()) 
			    {
			        // Issue #67
			        if ('notdefined' == $objVisitorsSearchEngineKeywords->keyword) 
			        {
			            $objVisitorsSearchEngineKeywords->keyword = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['not_defined'];
			        }
			        if ('Generic' == $objVisitorsSearchEngineKeywords->visitors_searchengine)
			        {
			            $objVisitorsSearchEngineKeywords->visitors_searchengine = $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['searchengine_unknown'];
			        }
	            	$VisitorsSearchEngineKeywords[] = array($objVisitorsSearchEngineKeywords->visitors_searchengine, 
                                                            $objVisitorsSearchEngineKeywords->keyword, 
                                                            $objVisitorsSearchEngineKeywords->anz);
			    }
		    }
	    }
	    return array('SearchEngines' => $VisitorsSearchEngines, 
                     'SearchEngineKeywords' =>$VisitorsSearchEngineKeywords);
	}
	
	/**
	 * TOP 30 Referrer
	 *
	 * @param	integer	$VisitorsID	ID des Zählers
	 * @return	array
	 */
	protected function getReferrerTop($VisitorsID)
	{
		$topNo = 30;
		$VisitorsReferrerTop = array();
		if ($VisitorsID) 
		{
			//Version
    		$objVisitorsReferrerTop = \Database::getInstance()
    		        ->prepare("SELECT 
                                    `visitors_referrer_dns`, 
                                    count(`id`) AS SUMRT
                                FROM 
                                    tl_visitors_referrer
                                WHERE 
                                    vid=?
                                GROUP BY `visitors_referrer_dns`
                                ORDER BY SUMRT DESC, visitors_referrer_dns ASC")
                    ->limit($topNo)
                    ->execute($VisitorsID);
    	    if ($objVisitorsReferrerTop->numRows > 0) 
    	    {
    		    while ($objVisitorsReferrerTop->next()) 
    		    {
    		    	if ($objVisitorsReferrerTop->visitors_referrer_dns !== '-') 
    		    	{
    		    		$VisitorsReferrerTop[] = array($objVisitorsReferrerTop->visitors_referrer_dns, $objVisitorsReferrerTop->SUMRT, $VisitorsID);
    		    	} 
    		    	else 
    		    	{
    		    		$VisitorsReferrerTop[] = array('- ('.$GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['referrer_direct'].')', $objVisitorsReferrerTop->SUMRT, 0);
    		    	}
    		    	
    		    }
    	    }
		}
		return $VisitorsReferrerTop;
	}
	
	/**
	 * Timestamp nach Datum in deutscher oder internationaler Schreibweise
	 *
	 * @param	string		$language
	 * @param	insteger	$intTstamp
	 * @return	string
	 */
	protected function parseDateVisitors($language='en', $intTstamp=null, $prefix='')
	{
	    switch ($language)
	    {
	        case 'de':
	            $strModified = $prefix . 'd.m.Y';
	            break;
	        case 'en':
	            $strModified = $prefix . 'Y-m-d';
	            break;
	        default :
	            $strModified = $prefix . $GLOBALS['TL_CONFIG']['dateFormat'];
	            break;
	    }
        if (is_null($intTstamp))
		{
			$strDate = date($strModified);
		}
		elseif (!is_numeric($intTstamp))
		{
			return '';
		}
		else
		{
			$strDate = $this->parseDate($strModified, $intTstamp);
		}
		return $strDate;
	}
	
	/**
	 * Get VisitorsOnline
	 *
	 * @param	integer	$VisitorsID	ID des Zählers
	 * @return 	integer
	 */
	protected function getVisitorsOnline($VisitorsID)
	{
		$objVisitorsOnlineCount = \Database::getInstance()
		        ->prepare("SELECT 
                                COUNT(id) AS VOC 
                            FROM 
                                tl_visitors_blocker
                            WHERE 
                                vid=? AND visitors_type=?")
                ->executeUncached($VisitorsID,'v');
		$objVisitorsOnlineCount->next();
		return ($objVisitorsOnlineCount->VOC === null) ? 0 : $objVisitorsOnlineCount->VOC;
	}
	
	/**
	 * Get Best Day Data (most Visitors on this day)
	 *
	 * @param integer $VisitorsID	ID des Zählers
	 * @return array	Date,Visits,Hits
	 */
	protected function getBestDay($VisitorsID)
	{
		$objVisitorsBestday = \Database::getInstance()
		        ->prepare("SELECT 
                                visitors_date, 
                                visitors_visit, 
                                visitors_hit
                            FROM 
                                tl_visitors_counter
                            WHERE 
                                vid=?
                            ORDER BY visitors_visit DESC,visitors_hit DESC")
                ->limit(1)
                ->execute($VisitorsID);
		if ($objVisitorsBestday->numRows > 0) 
		{
        	$objVisitorsBestday->next();
        	$visitors_date   = $this->parseDateVisitors($GLOBALS['TL_LANGUAGE'],strtotime($objVisitorsBestday->visitors_date));
        	$visitors_visits = $this->getFormattedNumber($objVisitorsBestday->visitors_visit,0);
        	$visitors_hits   = $this->getFormattedNumber($objVisitorsBestday->visitors_hit,0);
		} 
		else 
		{
			$visitors_date   = '';
			$visitors_visits = 0;
			$visitors_hits   = 0;
		}
		return array('VisitorsBestDayDate'   => $visitors_date,
					 'VisitorsBestDayVisits' => $visitors_visits,
					 'VisitorsBestDayHits'   => $visitors_hits
					);
	}
	
	/**
	 * Get Bad Day Data (fewest Visitors on this day)
	 *
	 * @param	integer	$VisitorsID	ID des Zählers
	 * @return 	array	Date,Visits,Hits
	 */
	protected function getBadDay($VisitorsID)
	{
		$objVisitorsBadday = \Database::getInstance()
		        ->prepare("SELECT 
                                visitors_date, 
                                visitors_visit, 
                                visitors_hit
                            FROM 
                                tl_visitors_counter
                            WHERE 
                                vid=? AND visitors_date < ?
                            ORDER BY visitors_visit ASC, visitors_hit ASC")
                ->limit(1)
                ->execute($VisitorsID, date('Y-m-d'));
		if ($objVisitorsBadday->numRows > 0) 
		{
        	$objVisitorsBadday->next();
        	$visitors_date   = $this->parseDateVisitors($GLOBALS['TL_LANGUAGE'],strtotime($objVisitorsBadday->visitors_date));
        	$visitors_visits = $this->getFormattedNumber($objVisitorsBadday->visitors_visit,0);
        	$visitors_hits   = $this->getFormattedNumber($objVisitorsBadday->visitors_hit,0);
		} 
		else 
		{
			$visitors_date   = '';
			$visitors_visits = 0;
			$visitors_hits   = 0;
		}
		return array('VisitorsBadDayDate'   => $visitors_date,
					 'VisitorsBadDayVisits' => $visitors_visits,
					 'VisitorsBadDayHits'   => $visitors_hits
					);
	}
	
	/**
	 * Get first category id by arrVisitorCategories
	 *
	 * @param   array   $arrVisitorCategories
	 * @return  number  CatID
	 */
	protected function getCatIdByCategories($arrVisitorCategories)
	{
	    $arrFirstCat = array_shift($arrVisitorCategories);
	    return $arrFirstCat['id'];
	}
	
	/**
	 * Get visitor categories by usergroups
	 *
	 * @param array $Usergroups
	 * @return array
	 */
	protected function getVisitorCategoriesByUsergroups()
	{
	    $arrVisitorCats = array();
	    $objVisitorCat = \Database::getInstance()
                            ->prepare("SELECT
                                            `id`
                                          , `title`
                                          , `visitors_stat_protected`
                                          , `visitors_stat_groups`
                                       FROM
                                            tl_visitors_category
                                        WHERE 1
                                        ORDER BY
                                            title
                                        ")
                            ->execute();
	    while ($objVisitorCat->next())
	    {
	        if ( true === $this->isUserInVisitorStatGroups($objVisitorCat->visitors_stat_groups,
                                                    (bool) $objVisitorCat->visitors_stat_protected) )
	        {
	            $arrVisitorCats[] = array
	            (
	                'id'    => $objVisitorCat->id,
	                'title' => $objVisitorCat->title
	            );
	        }
	    }
	
	    if (0 == count($arrVisitorCats))
	    {
	        $arrVisitorCats[] = array('id' => '0', 'title' => '---------');
	    }
	    return $arrVisitorCats;
	}
	
	/**
	 * Check if User member of group in visitor statistik groups
	 *
	 * @param   string  DB Field "visitors_stat_groups", serialized array
	 * @return  bool    true / false
	 */
	protected function isUserInVisitorStatGroups($visitors_stat_groups, $visitors_stat_protected)
	{
	    if ( true === $this->User->isAdmin )
	    {
	        //Debug log_message('Ich bin Admin', 'visitors_debug.log');
	        return true; // Admin darf immer
	    }
	    //wenn  Schutz nicht aktiviert ist, darf jeder
	    if (false === $visitors_stat_protected) 
	    {
	        //Debug log_message('Schutz nicht aktiviert', 'visitors_debug.log');
	    	return true; 
	    }
	    
	    //Schutz aktiviert, Einschränkungen vorhanden?
	    if (0 == strlen($visitors_stat_groups))
	    {
	        //Debug log_message('visitor_stat_groups ist leer', 'visitors_debug.log');
	        return false; // nicht gefiltert, also darf keiner außer Admin
	    }
	     
	    //mit isMemberOf ermitteln, ob user Member einer der Cat Groups ist
	    foreach (deserialize($visitors_stat_groups) as $id => $groupid)
	    {
	        if ( true === $this->User->isMemberOf($groupid) )
	        {
	            //Debug log_message('Ich bin in der richtigen Gruppe', 'visitors_debug.log');
	            return true; // User is Member of visitor_stat_group
	        }
	    }
	    //Debug log_message('Ich bin in der falschen Gruppe', 'visitors_debug.log');
	    return false;
	}
	
	protected function generateExport()
	{
	    $export = new \BugBuster\Visitors\Stat\Export\VisitorsStatExport;
	    return $export->run();
	}
	
}
