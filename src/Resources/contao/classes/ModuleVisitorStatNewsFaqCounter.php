<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Modul Visitors Stat News / FAQ Counter
 *
 * @copyright  Glen Langer 2009..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

/**
 * Class ModuleVisitorStatNewsFaqCounter
 *
 * @copyright  Glen Langer 2014..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 */
class ModuleVisitorStatNewsFaqCounter extends \BackendModule
{

    /**
     * Current object instance
     * @var object
     */
    protected static $instance = null;

    protected $today;
    protected $yesterday;
    protected $newstableexists = false;
    protected $faqtableexists  = false;

    const PAGE_TYPE_NEWS = 1;    //1 = Nachrichten/News
    const PAGE_TYPE_FAQ  = 2;    //2 = FAQ

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->today     = date('Y-m-d');
        $this->yesterday = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));

        if (\Database::getInstance()->tableExists('tl_news') &&
            \Database::getInstance()->tableExists('tl_news_archive'))
        {
            $this->setNewstableexists(true);
        }
        if (\Database::getInstance()->tableExists('tl_faq') &&
            \Database::getInstance()->tableExists('tl_faq_category'))
        {
            $this->setFaqtableexists(true);
        }
    }

    protected function compile()
    {

    }

    /**
     * Return the current object instance (Singleton)
     * @return ModuleVisitorStatNewsFaqCounter
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

    /**
     * @return the $newstableexists
     */
    public function getNewstableexists()
    {
        return $this->newstableexists;
    }

    /**
     * @return the $faqtableexists
     */
    public function getFaqtableexists()
    {
        return $this->faqtableexists;
    }

    /**
     * @param boolean $newstableexists
     */
    public function setNewstableexists($newstableexists)
    {
        $this->newstableexists = $newstableexists;
    }

    /**
     * @param boolean $faqtableexists
     */
    public function setFaqtableexists($faqtableexists)
    {
        $this->faqtableexists = $faqtableexists;
    }

    //////////////////////////////////////////////////////////////

    public function generateNewsVisitHitTop($VisitorsID, $limit = 10, $parse = true)
    {
        $arrNewsStatCount = false;

        //News Tables exists?
        if (true === $this->getNewstableexists())
        {
            $objNewsStatCount = \Database::getInstance()
                            ->prepare("SELECT 
                                            visitors_page_id,
                                            visitors_page_lang,
                                            SUM(visitors_page_visit) AS visitors_page_visits,
                                            SUM(visitors_page_hit)   AS visitors_page_hits
                                        FROM
                                            tl_visitors_pages
                                        WHERE
                                            vid = ?
                                        AND visitors_page_type = ?
                                        GROUP BY 
                                            visitors_page_id, 
                                            visitors_page_lang
                                        ORDER BY 
                                            visitors_page_visits DESC,
                                            visitors_page_hits DESC,
                                            visitors_page_id,
                                            visitors_page_lang
                                    ")
                            ->limit($limit)
                            ->execute($VisitorsID, self::PAGE_TYPE_NEWS);

            while ($objNewsStatCount->next())
            {
        	    $alias   = false;
        	    $aliases = $this->getNewsAliases($objNewsStatCount->visitors_page_id);
        	    if (false !== $aliases['PageAlias'])
        	    {
        	       $alias = $aliases['PageAlias'] .'/'. $aliases['NewsAlias'];
        	    }

                if (false !== $alias) 
                {
                    $arrNewsStatCount[] = array
                    (
                        'title'         => $aliases['NewsArchivTitle'],
                        'alias'         => $alias,
                        'lang'          => $objNewsStatCount->visitors_page_lang,
                        'visits'        => $objNewsStatCount->visitors_page_visits,
                        'hits'          => $objNewsStatCount->visitors_page_hits
                    );
                }
            }
            if ($parse === true)
            {
                // @var $TemplatePartial Template
                $TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_newsvisithittop');
                $TemplatePartial->NewsVisitHitTop = $arrNewsStatCount;

                return $TemplatePartial->parse();
            }

            return $arrNewsStatCount;
        }

        return false;
    }

    public function generateNewsVisitHitDays($VisitorsID, $limit = 10, $parse = true, $days=7)
    {
        $arrNewsStatCount = false;
        $week = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-$days, (int) date("Y")));

        //News Tables exists?
        if (true === $this->getNewstableexists())
        {
            $objNewsStatCount = \Database::getInstance()
                            ->prepare("SELECT 
                                            visitors_page_id,
                                            visitors_page_lang,
                                            SUM(visitors_page_visit) AS visitors_page_visits,
                                            SUM(visitors_page_hit)   AS visitors_page_hits
                                        FROM
                                            tl_visitors_pages
                                        WHERE
                                            vid = ?
                                        AND 
                                            visitors_page_type = ?
                                        AND
                                            visitors_page_date >= ?
                                        GROUP BY 
                                            visitors_page_id, 
                                            visitors_page_lang
                                        ORDER BY 
                                            visitors_page_visits DESC,
                                            visitors_page_hits DESC,
                                            visitors_page_id,
                                            visitors_page_lang
                                    ")
                            ->limit($limit)
                            ->execute($VisitorsID, self::PAGE_TYPE_NEWS, $week);

            while ($objNewsStatCount->next())
            {
        	    $alias   = false;
        	    $aliases = $this->getNewsAliases($objNewsStatCount->visitors_page_id);
        	    if (false !== $aliases['PageAlias'])
        	    {
        	       $alias = $aliases['PageAlias'] .'/'. $aliases['NewsAlias'];
        	    }

                if (false !== $alias) 
                {
                    $arrNewsStatCount[] = array
                    (
                        'title'         => $aliases['NewsArchivTitle'],
                        'alias'         => $alias,
                        'lang'          => $objNewsStatCount->visitors_page_lang,
                        'visits'        => $objNewsStatCount->visitors_page_visits,
                        'hits'          => $objNewsStatCount->visitors_page_hits
                    );
                }
            }
            if ($parse === true)
            {
                // @var $TemplatePartial Template
                $TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_newsvisithitdays');
                $TemplatePartial->NewsVisitHitDays = $arrNewsStatCount;

                return $TemplatePartial->parse();
            }

            return $arrNewsStatCount;
        }

        return false;
    }

    public function generateFaqVisitHitTop($VisitorsID, $limit = 10, $parse = true)
    {
        $arrFaqStatCount = false;

        //FAQ Tables exists?
        if (true === $this->getFaqtableexists())
        {
            $objFaqStatCount = \Database::getInstance()
                            ->prepare("SELECT
                                            visitors_page_id,
                                            visitors_page_lang,
                                            SUM(visitors_page_visit) AS visitors_page_visits,
                                            SUM(visitors_page_hit)   AS visitors_page_hits
                                        FROM
                                            tl_visitors_pages
                                        WHERE
                                            vid = ?
                                        AND visitors_page_type = ?
                                        GROUP BY
                                            visitors_page_id,
                                            visitors_page_lang
                                        ORDER BY
                                            visitors_page_visits DESC,
                                            visitors_page_hits DESC,
                                            visitors_page_id,
                                            visitors_page_lang
                                    ")
                            ->limit($limit)
                            ->execute($VisitorsID, self::PAGE_TYPE_FAQ);

            while ($objFaqStatCount->next())
            {
                $alias   = false;
                $aliases = $this->getFaqAliases($objFaqStatCount->visitors_page_id);
                if (false !== $aliases['PageAlias'])
                {
                    $alias = $aliases['PageAlias'] .'/'. $aliases['FaqAlias'];
                }

                if (false !== $alias)
                {
                    $arrFaqStatCount[] = array
                    (
                        'title'         => $aliases['FaqArchivTitle'],
                        'alias'         => $alias,
                        'lang'          => $objFaqStatCount->visitors_page_lang,
                        'visits'        => $objFaqStatCount->visitors_page_visits,
                        'hits'          => $objFaqStatCount->visitors_page_hits
                    );
                }
            }
            if ($parse === true)
            {
                // @var $TemplatePartial Template
                $TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_faqvisithittop');
                $TemplatePartial->FaqVisitHitTop = $arrFaqStatCount;

                return $TemplatePartial->parse();
            }

            return $arrFaqStatCount;
        }

        return false;

    }

    public function generateFaqVisitHitDays($VisitorsID, $limit = 10, $parse = true, $days=7)
    {
        $arrFaqStatCount = false;
        $week = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-$days, (int) date("Y")));

        //FAQ Tables exists?
        if (true === $this->getFaqtableexists())
        {
            $objFaqStatCount = \Database::getInstance()
                            ->prepare("SELECT
                                            visitors_page_id,
                                            visitors_page_lang,
                                            SUM(visitors_page_visit) AS visitors_page_visits,
                                            SUM(visitors_page_hit)   AS visitors_page_hits
                                        FROM
                                            tl_visitors_pages
                                        WHERE
                                            vid = ?
                                        AND 
                                            visitors_page_type = ?
                                        AND
                                            visitors_page_date >= ?
                                        GROUP BY
                                            visitors_page_id,
                                            visitors_page_lang
                                        ORDER BY
                                            visitors_page_visits DESC,
                                            visitors_page_hits DESC,
                                            visitors_page_id,
                                            visitors_page_lang
                                    ")
                            ->limit($limit)
                            ->execute($VisitorsID, self::PAGE_TYPE_FAQ, $week);

            while ($objFaqStatCount->next())
            {
                $alias   = false;
                $aliases = $this->getFaqAliases($objFaqStatCount->visitors_page_id);
                if (false !== $aliases['PageAlias'])
                {
                    $alias = $aliases['PageAlias'] .'/'. $aliases['FaqAlias'];
                }

                if (false !== $alias)
                {
                    $arrFaqStatCount[] = array
                    (
                        'title'         => $aliases['FaqArchivTitle'],
                        'alias'         => $alias,
                        'lang'          => $objFaqStatCount->visitors_page_lang,
                        'visits'        => $objFaqStatCount->visitors_page_visits,
                        'hits'          => $objFaqStatCount->visitors_page_hits
                    );
                }
            }
            if ($parse === true)
            {
                // @var $TemplatePartial Template
                $TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_faqvisithitdays');
                $TemplatePartial->FaqVisitHitDays = $arrFaqStatCount;

                return $TemplatePartial->parse();
            }

            return $arrFaqStatCount;
        }

        return false;

    }

    public function getNewsAliases($visitors_page_id)
    {
        //News Tables exists?
        if (true === $this->getNewstableexists())
        {
            $objNewsAliases = \Database::getInstance()
                                ->prepare("SELECT 
                                                tl_page.alias AS 'PageAlias', 
                                                tl_news.alias AS 'NewsAlias',
                                                tl_news_archive.title as 'NewsArchivTitle'
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
                return array('PageAlias'       => $objNewsAliases->PageAlias, 
                             'NewsAlias'       => $objNewsAliases->NewsAlias,
                             'NewsArchivTitle' => $objNewsAliases->NewsArchivTitle);
            }
        }
        else 
        {
            return array('PageAlias'       => false, 
                         'NewsAlias'       => false,
                         'NewsArchivTitle' => false);
        }
    }

    public function getFaqAliases($visitors_page_id)
    {
        //FAQ Tables exists?
        if (true === $this->getFaqtableexists())
        {
            $objFaqAliases = \Database::getInstance()
                                ->prepare("SELECT
                                                tl_page.alias AS 'PageAlias',
                                                tl_faq.alias AS 'FaqAlias',
                                                tl_faq_category.title as 'FaqArchivTitle'
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
                return array('PageAlias'      => $objFaqAliases->PageAlias,
                             'FaqAlias'       => $objFaqAliases->FaqAlias,
                             'FaqArchivTitle' => $objFaqAliases->FaqArchivTitle);
            }
        }
        else
        {
            return array('PageAlias'      => false,
                         'FaqAlias'       => false,
                         'FaqArchivTitle' => false);
        }
    }

}
