<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2022 Leo Feyer
 *
 * Modul Visitors File - Frontend
 *
 * @copyright  Glen Langer 2012..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle 
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Contao\Database;

/**
 * Class ModuleVisitors 
 *
 * @copyright  Glen Langer 2009..2022
 * @author     Glen Langer 
 * @license    LGPL 
 */
class ModuleVisitors extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_visitors_fe_all';

	protected $useragent_filter = '';

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \Contao\BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### VISITORS LIST ###';
			$objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		//alte und neue Art gemeinsam zum Array bringen
		if (strpos($this->visitors_categories, ':') !== false) 
		{
			$this->visitors_categories = StringUtil::deserialize($this->visitors_categories, true);
		} 
		else 
		{
			$this->visitors_categories = array($this->visitors_categories);
		}
		// Return if there are no categories
		if (!\is_array($this->visitors_categories) || !is_numeric($this->visitors_categories[0]))
		{
			return '';
		}
		$this->useragent_filter = $this->visitors_useragent;

		return parent::generate();
	}

	/**
	 * Generate module
	 */
	protected function compile()
	{						//visitors_template
		$objVisitors = Database::getInstance()
		        ->prepare("SELECT 
                                tl_visitors.id AS id, 
                                visitors_name, 
                                visitors_startdate, 
                                visitors_average
                            FROM 
                                tl_visitors 
                            LEFT JOIN 
                                tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                            WHERE 
                                pid=? AND published=?
                            ORDER BY id, visitors_name")
                ->limit(1)
                ->execute($this->visitors_categories[0], 1);
		if ($objVisitors->numRows < 1)
		{
			$this->strTemplate = 'mod_visitors_error';
			$this->Template = new \Contao\FrontendTemplate($this->strTemplate); 

			System::getContainer()
			     ->get('monolog.logger.contao')
			     ->log(
			         LogLevel::ERROR,
			         'ModuleVisitors User Error: no published counter found.',
			         array('contao' => new ContaoContext('ModulVisitors compile ', TL_ERROR))
			     );

			return;
		}

		$arrVisitors = array();

		while ($objVisitors->next())
		{
		    //if (($objVisitors->visitors_template != $this->strTemplate) && ($objVisitors->visitors_template != '')) {
		    if (($this->visitors_template != $this->strTemplate) && ($this->visitors_template != '')) 
		    {
                $this->strTemplate = $this->visitors_template;
                $this->Template = new \Contao\FrontendTemplate($this->strTemplate); 
		    }
		    if ($this->strTemplate != 'mod_visitors_fe_invisible') 
		    {
		    	//VisitorsStartDate
	            if (!\strlen($objVisitors->visitors_startdate)) 
	            {
			    	$VisitorsStartDate = false;
			    } 
			    else 
			    {
			        $VisitorsStartDate = true;
			    } 
			    if ($objVisitors->visitors_average) 
			    {
			    	$VisitorsAverageVisits = true;
			    } 
			    else 
			    {
	                $VisitorsAverageVisits = false;
	            } 
	            if (!isset($GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'])) 
	            {
	                $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend']='';
	            }
			    $arrVisitors[] = array
				(
	                'VisitorsName'        => trim($objVisitors->visitors_name),
	                'VisitorsKatID'       => $this->visitors_categories[0],
	                'VisitorsStartDate'   => $VisitorsStartDate, 
	                'AverageVisits'       => $VisitorsAverageVisits, 
	                'VisitorsNameLegend'        => $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'],
	                'VisitorsOnlineCountLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsOnlineCountLegend'],
	                'VisitorsStartDateLegend'   => $GLOBALS['TL_LANG']['visitors']['VisitorsStartDateLegend'],
	                'TotalVisitCountLegend'     => $GLOBALS['TL_LANG']['visitors']['TotalVisitCountLegend'],
	                'TotalHitCountLegend'       => $GLOBALS['TL_LANG']['visitors']['TotalHitCountLegend'],
	                'TodayVisitCountLegend'     => $GLOBALS['TL_LANG']['visitors']['TodayVisitCountLegend'],
	                'TodayHitCountLegend'       => $GLOBALS['TL_LANG']['visitors']['TodayHitCountLegend'],
	                'AverageVisitsLegend'       => $GLOBALS['TL_LANG']['visitors']['AverageVisitsLegend'],
				    'YesterdayHitCountLegend'   => $GLOBALS['TL_LANG']['visitors']['YesterdayHitCountLegend'],
				    'YesterdayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayVisitCountLegend'],
				    'PageHitCountLegend'        => $GLOBALS['TL_LANG']['visitors']['PageHitCountLegend']
				);
			} 
			else 
			{
				// invisible, but counting!
				$arrVisitors[] = array('VisitorsKatID' => $this->visitors_categories[0]);
			}
		}
		$this->Template->visitors = $arrVisitors;
	} // compile
} // class

