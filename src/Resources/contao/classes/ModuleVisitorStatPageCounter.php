<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Modul Visitors Stat Page Counter
 *
 * @copyright  Glen Langer 2009..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

use Contao\CoreBundle\Exception\NoRootPageFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ModuleVisitorStatPageCounter
 *
 * @copyright  Glen Langer 2014..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 */
class ModuleVisitorStatPageCounter extends \Contao\BackendModule
{

    /**
     * Current object instance
     * @var object
     */
    protected static $instance;

    protected $today;
    protected $yesterday;

    const PAGE_TYPE_NORMAL      = 0;    //0 = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
    const PAGE_TYPE_NEWS        = 1;    //1 = Nachrichten/News
    const PAGE_TYPE_FAQ         = 2;    //2 = FAQ
    const PAGE_TYPE_ISOTOPE     = 3;    //3   = Isotope
    const PAGE_TYPE_EVENTS      = 4;    //4   = Events
    const PAGE_TYPE_FORBIDDEN   = 403;  //403 = Forbidden Seite

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->today     = date('Y-m-d');
        $this->yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-1, (int) date("Y")));
    }

    protected function compile()
    {

    }

    /**
     * Return the current object instance (Singleton)
     * @return ModuleVisitorStatPageCounter
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //////////////////////////////////////////////////////////////

    public function generatePageVisitHitTop($VisitorsID, $limit = 20, $parse = true)
    {
        $arrPageStatCount = false;
        $objPageStatCount = \Contao\Database::getInstance()
                        ->prepare("SELECT 
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type,
                                        SUM(visitors_page_visit) AS visitors_page_visits,
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND 
                                        visitors_page_type IN (?,?)
                                    GROUP BY 
                                        visitors_page_id, 
                                        visitors_page_lang,
                                        visitors_page_type
                                    ORDER BY 
                                        visitors_page_visits DESC,
                                        visitors_page_hits DESC,
                                        visitors_page_id,
                                        visitors_page_lang
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID, self::PAGE_TYPE_NORMAL, self::PAGE_TYPE_FORBIDDEN);

        while ($objPageStatCount->next())
        {
            switch ($objPageStatCount->visitors_page_type) 
            {
            	case self::PAGE_TYPE_NORMAL:
                    $objPage = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
                    if (!\is_null($objPage))
                    {
                        $alias = $objPage->alias;
                    } 
                    else 
                    {
                        //Seite in der Statistik existiert nicht mehr in der Seitenstruktur
                        $alias = '-/-';    
                    }
                	break;
    	        case self::PAGE_TYPE_FORBIDDEN:
    	            $alias   = false;
    	            $objPage  = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
    	            $alias403 = $this->getForbiddenAlias(
    	                $objPageStatCount->visitors_page_id,
    	                $objPageStatCount->visitors_page_lang
    	            );
    	            $alias = $alias403 .' ['.$objPage->alias.']';
    	            break;
            	default:
            		$alias = '-/-';
            	break;
            }

            if (false !== $alias) 
            {
                $arrPageStatCount[] = array
                (
                    'alias'         => $alias,
                    'lang'          => $objPageStatCount->visitors_page_lang,
                    'visits'        => $objPageStatCount->visitors_page_visits,
                    'hits'          => $objPageStatCount->visitors_page_hits
                );
            }
        }

        if ($parse === true) 
        {
            $this->TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_pagevisithittop');        
            $this->TemplatePartial->PageVisitHitTop = $arrPageStatCount;

            return $this->TemplatePartial->parse();
        }
        else 
        {
            return $arrPageStatCount;
        }
    }

    public function generatePageVisitHitToday($VisitorsID, $limit=5)
    {
        $arrPageStatCount = false;

        $this->TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_pagevisithittoday');

        $objPageStatCount = \Contao\Database::getInstance()
                        ->prepare("SELECT
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type,
                                        SUM(visitors_page_visit) AS visitors_page_visits,
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND 
                                        visitors_page_type IN (?,?)
                                    AND
                                        visitors_page_date = ?
                                    GROUP BY
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type
                                    ORDER BY
                                        visitors_page_visits DESC,
                                        visitors_page_hits DESC,
                                        visitors_page_id,
                                        visitors_page_lang
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID, self::PAGE_TYPE_NORMAL, self::PAGE_TYPE_FORBIDDEN, $this->today);

        while ($objPageStatCount->next())
        {
            switch ($objPageStatCount->visitors_page_type) 
            {
            	case self::PAGE_TYPE_NORMAL:
                    $objPage = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
                    if (!\is_null($objPage))
                    {
                        $alias = $objPage->alias;
                    } 
                    else 
                    {
                        //Seite in der Statistik existiert nicht mehr in der Seitenstruktur
                        $alias = '-/-';    
                    }
                	break;
    	        case self::PAGE_TYPE_FORBIDDEN:
    	            $alias   = false;
    	            $objPage  = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
    	            $alias403 = $this->getForbiddenAlias(
    	                $objPageStatCount->visitors_page_id,
    	                $objPageStatCount->visitors_page_lang
    	            );
    	            $alias = $alias403 .' ['.$objPage->alias.']';
            	default:
            		$alias = '-/-';
            	break;
            }

            if (false !== $alias) 
            {
                $arrPageStatCount[] = array
                (
                    'alias'         => $alias,
                    'lang'          => $objPageStatCount->visitors_page_lang,
                    'visits'        => $objPageStatCount->visitors_page_visits,
                    'hits'          => $objPageStatCount->visitors_page_hits
                );
            }
        }

        $this->TemplatePartial->PageVisitHitToday = $arrPageStatCount;

        return $this->TemplatePartial->parse();
    }

    public function generatePageVisitHitYesterday($VisitorsID, $limit=5)
    {
        $arrPageStatCount = false;

        $this->TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_pagevisithityesterday');

        $objPageStatCount = \Contao\Database::getInstance()
                        ->prepare("SELECT
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type,
                                        SUM(visitors_page_visit) AS visitors_page_visits,
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND 
                                        visitors_page_type IN (?,?)
                                    AND
                                        visitors_page_date = ?
                                    GROUP BY
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type
                                    ORDER BY
                                        visitors_page_visits DESC,
                                        visitors_page_hits DESC,
                                        visitors_page_id,
                                        visitors_page_lang
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID, self::PAGE_TYPE_NORMAL, self::PAGE_TYPE_FORBIDDEN, $this->yesterday);

        while ($objPageStatCount->next())
        {
            switch ($objPageStatCount->visitors_page_type) 
            {
            	case self::PAGE_TYPE_NORMAL:
                    $objPage = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
                    if (!\is_null($objPage))
                    {
                        $alias = $objPage->alias;
                    } 
                    else 
                    {
                        //Seite in der Statistik existiert nicht mehr in der Seitenstruktur
                        $alias = '-/-';    
                    }
                	break;
    	        case self::PAGE_TYPE_FORBIDDEN:
    	            $alias   = false;
    	            $objPage  = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
    	            $alias403 = $this->getForbiddenAlias(
    	                $objPageStatCount->visitors_page_id,
    	                $objPageStatCount->visitors_page_lang
    	            );
    	            $alias = $alias403 .' ['.$objPage->alias.']';
            	default:
            		$alias = '-/-';
            	break;
            }

            if (false !== $alias) 
            {
                $arrPageStatCount[] = array
                (
                    'alias'         => $alias,
                    'lang'          => $objPageStatCount->visitors_page_lang,
                    'visits'        => $objPageStatCount->visitors_page_visits,
                    'hits'          => $objPageStatCount->visitors_page_hits
                );
            }
        }

        $this->TemplatePartial->PageVisitHitYesterday = $arrPageStatCount;

        return $this->TemplatePartial->parse();
    }

    public function generatePageVisitHitDays($VisitorsID, $limit=20, $days=7)
    {
        $arrPageStatCount = false;
        $week = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-$days, (int) date("Y")));

        $this->TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_pagevisithitdays');

        $objPageStatCount = \Contao\Database::getInstance()
                        ->prepare("SELECT
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type,
                                        SUM(visitors_page_visit) AS visitors_page_visits,
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND 
                                        visitors_page_type IN (?,?)
                                    AND
                                        visitors_page_date >= ?
                                    GROUP BY
                                        visitors_page_id,
                                        visitors_page_lang,
                                        visitors_page_type
                                    ORDER BY
                                        visitors_page_visits DESC,
                                        visitors_page_hits DESC,
                                        visitors_page_id,
                                        visitors_page_lang
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID, self::PAGE_TYPE_NORMAL, self::PAGE_TYPE_FORBIDDEN, $week);

        while ($objPageStatCount->next())
        {
            switch ($objPageStatCount->visitors_page_type) 
            {
            	case self::PAGE_TYPE_NORMAL:
                    $objPage = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
                    if (!\is_null($objPage))
                    {
                        $alias = $objPage->alias;
                    } 
                    else 
                    {
                        //Seite in der Statistik existiert nicht mehr in der Seitenstruktur
                        $alias = '-/-';    
                    }
                	break;
    	        case self::PAGE_TYPE_FORBIDDEN:
    	            $alias   = false;
    	            $objPage  = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
    	            $alias403 = $this->getForbiddenAlias(
    	                $objPageStatCount->visitors_page_id,
    	                $objPageStatCount->visitors_page_lang
    	            );
    	            $alias = $alias403 .' ['.$objPage->alias.']';
            	default:
            		$alias = '-/-';
            	break;
            }

            if (false !== $alias)
            {
                $arrPageStatCount[] = array
                (
                    'alias'         => $alias,
                    'lang'          => $objPageStatCount->visitors_page_lang,
                    'visits'        => $objPageStatCount->visitors_page_visits,
                    'hits'          => $objPageStatCount->visitors_page_hits
                );
            }
        }

        $this->TemplatePartial->PageVisitHitDays = $arrPageStatCount;

        return $this->TemplatePartial->parse();
    }

    public function getNewsAliases($visitors_page_id)
    {
        //News Tables exists?
        if (\Contao\Database::getInstance()->tableExists('tl_news') &&
            \Contao\Database::getInstance()->tableExists('tl_news_archive'))
        {
            $objNewsAliases = \Contao\Database::getInstance()
                                ->prepare("SELECT 
                                                tl_page.alias AS 'PageAlias', 
                                                tl_news.alias AS 'NewsAlias'
                                            FROM
                                                tl_page
                                            INNER JOIN
                                                tl_news_archive ON tl_news_archive.jumpTo = tl_page.id
                                            INNER JOIN
                                                tl_news ON tl_news.pid = tl_news_archive.id
                                            WHERE
                                                tl_news.id = ?
                                            ")
                                ->limit(1)
                                ->execute($visitors_page_id);
            while ($objNewsAliases->next())
            {
                return array('PageAlias' => $objNewsAliases->PageAlias, 
                             'NewsAlias' => $objNewsAliases->NewsAlias);
            }
        }
        else 
        {
            return array('PageAlias' => false, 
                         'NewsAlias' => false);
        }
    }

    public function getFaqAliases($visitors_page_id)
    {
        //FAQ Tables exists?
        if (\Contao\Database::getInstance()->tableExists('tl_faq') &&
            \Contao\Database::getInstance()->tableExists('tl_faq_category'))
        {
            $objFaqAliases = \Contao\Database::getInstance()
                                ->prepare("SELECT
                                                tl_page.alias AS 'PageAlias',
                                                tl_faq.alias AS 'FaqAlias'
                                            FROM
                                                tl_page
                                            INNER JOIN
                                                tl_faq_category ON tl_faq_category.jumpTo = tl_page.id
                                            INNER JOIN
                                                tl_faq ON tl_faq.pid = tl_faq_category.id
                                            WHERE
                                                tl_faq.id = ?
                                            ")
                                ->limit(1)
                                ->execute($visitors_page_id);
            while ($objFaqAliases->next())
            {
                return array('PageAlias' => $objFaqAliases->PageAlias,
                             'FaqAlias'  => $objFaqAliases->FaqAlias);
            }
        }
        else
        {
            return array('PageAlias' => false,
                         'FaqAlias'  => false);
        }
    }

    public function getEventsAliases($visitors_page_id)
    {
        //Events Tables exists?
        if (\Contao\Database::getInstance()->tableExists('tl_calendar') &&
            \Contao\Database::getInstance()->tableExists('tl_calendar_events'))
        {
            $objEventsAliases = \Contao\Database::getInstance()
                                ->prepare("SELECT
                                                tl_page.alias AS 'PageAlias',
                                                tl_calendar_events.alias AS 'EventsAlias'
                                            FROM
                                                tl_page
                                            INNER JOIN
                                                tl_calendar ON tl_calendar.jumpTo = tl_page.id
                                            INNER JOIN
                                                tl_calendar_events ON tl_calendar_events.pid = tl_calendar.id
                                            WHERE
                                                tl_calendar_events.id = ?
                                            ")
                                ->limit(1)
                                ->execute($visitors_page_id);
            while ($objEventsAliases->next())
            {
                return array('PageAlias'    => $objEventsAliases->PageAlias,
                             'EventsAlias'  => $objEventsAliases->EventsAlias);
            }
        }
        else
        {
            return array('PageAlias'    => false,
                         'EventsAlias'  => false);
        }
    }

    public function getIsotopeAliases($visitors_page_id, $visitors_page_pid)
    {
        //Isotope Table exists?
        if (\Contao\Database::getInstance()->tableExists('tl_iso_product'))
        {
            $PageAlias = false;
            $objIsotopePageAlias = \Contao\Database::getInstance()
                                    ->prepare("SELECT
                                                tl_page.alias AS 'PageAlias'
                                            FROM
                                                tl_page
                                            WHERE
                                                tl_page.id = ?
                                            ")
                                    ->limit(1)
                                    ->execute($visitors_page_pid);

            while ($objIsotopePageAlias->next())
            {
                $PageAlias = $objIsotopePageAlias->PageAlias;
            }

            $objIsotopeProduct= \Contao\Database::getInstance()
                                ->prepare("SELECT
                                                tl_iso_product.alias  AS 'ProductAlias'
                                            FROM
                                                tl_iso_product
                                            WHERE
                                                tl_iso_product.id = ?
                                            ")
                                ->limit(1)
                                ->execute($visitors_page_id);

            while ($objIsotopeProduct->next())
            {
                return array('PageAlias'     => $PageAlias,
                             'ProductAlias'  => $objIsotopeProduct->ProductAlias);
            }
        }

        return array('PageAlias'       => false,
                     'ProductAlias'    => false
                    );

    }

    public function getForbiddenAlias($visitors_page_id, $visitors_page_lang)
    {
        //Page ID von der 403 Seite ermitteln
        // $host = \Contao\Environment::get('host');
        // Find the matching root pages (thanks to Andreas Schempp)
        // $objRootPage = \Contao\PageModel::findFirstPublishedRootByHostAndLanguage($host, $visitors_page_lang);
        $objRequest = \Contao\System::getContainer()->get('request_stack')->getCurrentRequest();
		if ($objRequest instanceof Request)
		{
			$objPage = $objRequest->attributes->get('pageModel');

			if ($objPage instanceof \Contao\PageModel)
			{
				$objPage->loadDetails();

				$objRootPage = \Contao\PageModel::findByPk($objPage->rootId);
			}
		}
		else
		{
			throw new NoRootPageFoundException('No root page found');
		}
        $objPage = \Contao\PageModel::find403ByPid($objRootPage->id);

        return $objPage->alias;
    }

    /**
     * generatePageVisitHitTopDays speziell für den Export
     * Filterung nach Anzahl Tagen
     * 
     * @param integer $VisitorsID
     * @param number  $days
     * @param string  $parse
     * @return array|string NULL
     */
    public function generatePageVisitHitTopDays($VisitorsID, $days = 365, $parse = false)
    {
        $STARTDATE = date("Y-m-d", mktime(0, 0, 0, (int) date("m"), (int) date("d")-$days, (int) date("Y"))); 
        $arrPageStatCount = false;
        $objPageStatCount = \Contao\Database::getInstance()
                            ->prepare("SELECT
                                        visitors_page_id,
                                        visitors_page_pid,
                                        visitors_page_lang,
                                        visitors_page_type,
                                        SUM(visitors_page_visit) AS visitors_page_visits,
                                        SUM(visitors_page_hit)   AS visitors_page_hits
                                    FROM
                                        tl_visitors_pages
                                    WHERE
                                        vid = ?
                                    AND
                                        visitors_page_date >= ?
                                    GROUP BY
                                        visitors_page_id,
                                        visitors_page_pid,
                                        visitors_page_lang,
                                        visitors_page_type
                                    ORDER BY
                                        visitors_page_visits DESC,
                                        visitors_page_hits DESC,
                                        visitors_page_id,
                                        visitors_page_pid,
                                        visitors_page_lang
                                    ")
                            ->execute($VisitorsID, $STARTDATE);

        while ($objPageStatCount->next())
        {
            switch ($objPageStatCount->visitors_page_type)
            {
            	case self::PAGE_TYPE_NORMAL:
            	    $objPage = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
            	    if (!\is_null($objPage))
                    {
                        $alias = $objPage->alias;
                    } 
                    else 
                    {
                        //Seite in der Statistik existiert nicht mehr in der Seitenstruktur
                        $alias = '-/-';    
                    }
            	    break;
            	case self::PAGE_TYPE_NEWS:
            	    $alias   = false;
            	    $aliases = $this->getNewsAliases($objPageStatCount->visitors_page_id);
            	    if (false !== $aliases['PageAlias'])
            	    {
            	        $alias = $aliases['PageAlias'] .'/'. $aliases['NewsAlias'];
            	    }
            	    break;
            	case self::PAGE_TYPE_FAQ:
            	    $alias   = false;
            	    $aliases = $this->getFaqAliases($objPageStatCount->visitors_page_id);
            	    if (false !== $aliases['PageAlias'])
            	    {
            	        $alias = $aliases['PageAlias'] .'/'. $aliases['FaqAlias'];
            	    }
            	    break;
                case self::PAGE_TYPE_EVENTS:
                    $alias   = false;
                    $aliases = $this->getEventsAliases($objPageStatCount->visitors_page_id);
                    if (false !== $aliases['PageAlias'])
                    {
                        $alias = $aliases['PageAlias'] .'/'. $aliases['EventsAlias'];
                    }
                    break;
        	    case self::PAGE_TYPE_ISOTOPE:
        	        $alias   = false;
        	        $aliases = $this->getIsotopeAliases($objPageStatCount->visitors_page_id, $objPageStatCount->visitors_page_pid);
        	        if (false !== $aliases['PageAlias'])
        	        {
        	            $alias = $aliases['PageAlias'] .'/'. $aliases['ProductAlias'];
        	        }
        	        break;
            	case self::PAGE_TYPE_FORBIDDEN:
            	    $alias   = false;
            	    $objPage  = \Contao\PageModel::findWithDetails($objPageStatCount->visitors_page_id);
            	    $alias403 = $this->getForbiddenAlias(
            	        $objPageStatCount->visitors_page_id,
            	        $objPageStatCount->visitors_page_lang
            	    );
            	    $alias = $alias403 .' ['.$objPage->alias.']';
            	    break;
            	default:
            	    $alias = '-/-';
                	break;
            }

            if (false !== $alias)
            {
                $arrPageStatCount[] = array
                (
                    'alias'         => $alias,
                    'lang'          => $objPageStatCount->visitors_page_lang,
                    'visits'        => $objPageStatCount->visitors_page_visits,
                    'hits'          => $objPageStatCount->visitors_page_hits
                );
            }
        }

        if ($parse === true)
        {
            $this->TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_pagevisithittop');
            $this->TemplatePartial->PageVisitHitTop = $arrPageStatCount;

            return $this->TemplatePartial->parse();
        }
        else
        {
            return $arrPageStatCount;
        }
    }

}
