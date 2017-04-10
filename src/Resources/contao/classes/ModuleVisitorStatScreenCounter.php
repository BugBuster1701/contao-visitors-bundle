<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Modul Visitors Stat Screen Counter
 *
 * @copyright  Glen Langer 2009..2014 <http://www.contao.glen-langer.de>
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
 * Class ModuleVisitorStatScreenCounter
 *
 * @copyright  Glen Langer 2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 */
class ModuleVisitorStatScreenCounter extends \BackendModule
{
    
    /**
     * Current object instance
     * @var object
     */
    protected static $instance = null;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    protected function compile()
    {
    
    }
    
    /**
     * Return the current object instance (Singleton)
     * @return ModuleVisitorStatScreenCounter
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new ModuleVisitorStatScreenCounter();
        }
    
        return self::$instance;
    }

    //////////////////////////////////////////////////////////////
    
    public function generateScreenTopResolution($VisitorsID,$limit=20)
    {
        $arrScreenStatCount = false;
        
        $this->TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_screentopresolution');
        
        $objScreenStatCount = \Database::getInstance()
                        ->prepare("SELECT 
                                        `v_s_w`,
                                        `v_s_h`,
                                        `v_s_iw`,
                                        `v_s_ih`,
                                        SUM(`v_screen_counter`) AS v_screen_sum
                                    FROM
                                        `tl_visitors_screen_counter`
                                    WHERE
                                        `vid` = ?
                                    GROUP BY `v_s_w` , `v_s_h` 
                                    ORDER BY v_screen_sum DESC
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID);

        while ($objScreenStatCount->next())
        {
            $arrScreenStatCount[] = array
            (
                'v_s_width'     => $objScreenStatCount->v_s_w,
                'v_s_height'    => $objScreenStatCount->v_s_h,
                'v_s_iwidth'    => $objScreenStatCount->v_s_iw,
                'v_s_iheight'   => $objScreenStatCount->v_s_ih,
                'v_screen_sum'  => $objScreenStatCount->v_screen_sum
            );
        }
        $this->TemplatePartial->ScreenTopResolution = $arrScreenStatCount;        
        return $this->TemplatePartial->parse();
    }
    
    public function generateScreenTopResolutionDays($VisitorsID,$limit=20,$days=30)
    {
        $arrScreenStatCount = false;
        $lastdays = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-$days, date("Y")));
    
        $this->TemplatePartial = new \BackendTemplate('mod_visitors_be_stat_partial_screentopresolutiondays');
    
        $objScreenStatCount = \Database::getInstance()
                        ->prepare("SELECT
                                        `v_s_w`,
                                        `v_s_h`,
                                        `v_s_iw`,
                                        `v_s_ih`,
                                        SUM(`v_screen_counter`) AS v_screen_sum
                                    FROM
                                        `tl_visitors_screen_counter`
                                    WHERE
                                        `vid` = ?
                                    AND
                                        `v_date` >= ?
                                    GROUP BY `v_s_w` , `v_s_h` 
                                    ORDER BY v_screen_sum DESC
                                ")
                        ->limit($limit)
                        ->execute($VisitorsID,$lastdays);

        while ($objScreenStatCount->next())
        {
            $arrScreenStatCount[] = array
            (
                'v_s_width'     => $objScreenStatCount->v_s_w,
                'v_s_height'    => $objScreenStatCount->v_s_h,
                'v_s_iwidth'    => $objScreenStatCount->v_s_iw,
                'v_s_iheight'   => $objScreenStatCount->v_s_ih,
                'v_screen_sum'  => $objScreenStatCount->v_screen_sum
            );
        }
        $this->TemplatePartial->ScreenTopResolutionDays = $arrScreenStatCount;
        return $this->TemplatePartial->parse();
    }
    
}
