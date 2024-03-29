<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

use Contao\BackendTemplate;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
// use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ModuleVisitors
 *
 * @copyright  Glen Langer 2023
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

	private $monologLogger;

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create('')))
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### VISITORS LIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		// alte und neue Art gemeinsam zum Array bringen
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
	{						// visitors_template
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
			$this->Template = new FrontendTemplate($this->strTemplate);

			// System::getContainer()
			// 	 ->get('monolog.logger.contao')
			// 	 ->log(
			// 	 	LogLevel::ERROR,
			// 	 	'ModuleVisitors User Error: no published counter found.',
			// 	 	array('contao' => new ContaoContext('ModulVisitors compile ', ContaoContext::ERROR))
			// 	 );
			$this->monologLogger = System::getContainer()->get('bug_buster_visitors.logger');
			$this->monologLogger->logSystemLog('ModuleVisitors User Error: no published counter found.', 'ModulVisitors compile ', ContaoContext::ERROR);

			return;
		}

		$arrVisitors = array();

		while ($objVisitors->next())
		{
			// if (($objVisitors->visitors_template != $this->strTemplate) && ($objVisitors->visitors_template != '')) {
			if (($this->visitors_template != $this->strTemplate) && ($this->visitors_template != ''))
			{
				$this->strTemplate = $this->visitors_template;
				$this->Template = new FrontendTemplate($this->strTemplate);
			}
			if ($this->strTemplate != 'mod_visitors_fe_invisible')
			{
				// VisitorsStartDate
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
