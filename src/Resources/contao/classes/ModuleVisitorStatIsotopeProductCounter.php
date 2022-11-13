<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2018 Leo Feyer
 *
 * Modul Visitors Stat Isotope Product Counter
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

/**
 * Class ModuleVisitorStatIsotopeProductCounter
 *
 * @copyright  Glen Langer 2014..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 */
class ModuleVisitorStatIsotopeProductCounter extends \Contao\BackendModule
{

    /**
     * Current object instance
     * @var object
     */
    protected static $instance;

    protected $today;
    protected $yesterday;
    protected $isotopeExists = false;

    const PAGE_TYPE_ISOTOPE    = 3;    //3   = Isotope

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->today     = date('Y-m-d');
        $this->yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-1, (int) date("Y")));

        if (\Contao\Database::getInstance()->tableExists('tl_iso_product'))
        {
            $this->setIsotopeTableExists(true);
        }
    }

    protected function compile()
    {

    }

    /**
     * Return the current object instance (Singleton)
     * @return ModuleVisitorStatIsotopeProductCounter
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
     * @return the $isotopeExists
     */
    public function getIsotopeTableExists()
    {
        return $this->isotopeExists;
    }

    /**
     * @param boolean $isotopeExists
     */
    public function setIsotopeTableExists($IsotopeTableExists)
    {
        $this->isotopeExists = $IsotopeTableExists;
    }

    //////////////////////////////////////////////////////////////

    public function generateIsotopeVisitHitTop($VisitorsID, $limit = 20, $parse = true)
    {
        $arrIsotopeStatCount = false;

        //Isotope Table exists?
        if (true === $this->getIsotopeTableExists())
        {
            $objIsotopeStatCount = \Contao\Database::getInstance()
                            ->prepare("SELECT 
                                            visitors_page_id,
                                            visitors_page_pid,
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
                                            visitors_page_pid,
                                            visitors_page_lang
                                        ORDER BY 
                                            visitors_page_visits DESC,
                                            visitors_page_hits DESC,
                                            visitors_page_id,
                                            visitors_page_pid,
                                            visitors_page_lang
                                    ")
                            ->limit($limit)
                            ->execute($VisitorsID, self::PAGE_TYPE_ISOTOPE);

            while ($objIsotopeStatCount->next())
            {
        	    $alias   = false;
        	    $aliases = $this->getIsotopeAliases($objIsotopeStatCount->visitors_page_id, $objIsotopeStatCount->visitors_page_pid);
        	    if (false !== $aliases['PageAlias'])
        	    {
        	       $alias = $aliases['PageAlias'] .'/'. $aliases['ProductAlias'];
        	       $title = $aliases['ProductTeaser'] .': '. $aliases['ProductName'];
        	    }

                if (false !== $alias) 
                {
                    $arrIsotopeStatCount[] = array
                    (
                        'title'         => $title,
                        'alias'         => $alias,
                        'lang'          => $objIsotopeStatCount->visitors_page_lang,
                        'visits'        => $objIsotopeStatCount->visitors_page_visits,
                        'hits'          => $objIsotopeStatCount->visitors_page_hits
                    );
                }
            }

            if ($parse === true)
            {
                // @var $TemplatePartial Template
                $TemplatePartial = new \Contao\BackendTemplate('mod_visitors_be_stat_partial_isotopevisithittop');
                $TemplatePartial->IsotopeVisitHitTop = $arrIsotopeStatCount;

                return $TemplatePartial->parse();
            }

            return $arrIsotopeStatCount;
        }

        return false;
    }

    /**
     * @param  unknown $visitors_page_id  Product ID
     * @param  unknown $visitors_page_pid Contao Page ID
     * @return array
     */
    public function getIsotopeAliases($visitors_page_id, $visitors_page_pid)
    {
        //Isotope Table exists?
        if (true === $this->getIsotopeTableExists())
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
                                                tl_iso_product.alias  AS 'ProductAlias',
                                                tl_iso_product.teaser AS 'ProductTeaser',
                                                tl_iso_product.name   AS 'ProductName'
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
                             'ProductAlias'  => $objIsotopeProduct->ProductAlias,
                             'ProductTeaser' => $objIsotopeProduct->ProductTeaser,
                             'ProductName'   => $objIsotopeProduct->ProductName);
            }
        }

        return array('PageAlias'       => false, 
                     'ProductAlias'    => false,
                     'ProductTeaser'   => false,
                     'ProductName'     => false
        );
    }

}
