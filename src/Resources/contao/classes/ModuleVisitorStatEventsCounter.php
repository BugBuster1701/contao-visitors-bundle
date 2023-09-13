<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
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

use Contao\BackendModule;
use Contao\BackendTemplate;
use Contao\Database;

/**
 * Class ModuleVisitorStatEventsCounter
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 */
class ModuleVisitorStatEventsCounter extends BackendModule
{
	/**
	 * Current object instance
	 * @var object
	 */
	protected static $instance;

	protected $today;

	protected $yesterday;

	protected $eventstableexists = false;

	const PAGE_TYPE_EVENTS    = 4;      // 4   = Events

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->today     = date('Y-m-d');
		$this->yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-1, (int) date("Y")));

		if (
			Database::getInstance()->tableExists('tl_calendar_events')
			&& Database::getInstance()->tableExists('tl_calendar')
		) {
			$this->setEventstableexists(true);
		}
	}

	protected function compile()
	{
	}

	/**
	 * Return the current object instance (Singleton)
	 * @return ModuleVisitorStatEventsCounter
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	// ////////////////////////////////////////////////////////////

	/**
	 * @return the
	 */
	public function getEventstableexists()
	{
		return $this->eventstableexists;
	}

	/**
	 * @param boolean $eventstableexists
	 */
	public function setEventstableexists($eventstableexists)
	{
		$this->eventstableexists = $eventstableexists;
	}

	// ////////////////////////////////////////////////////////////

	public function generateEventsVisitHitTop($VisitorsID, $limit = 10, $parse = true)
	{
		$arrEventsStatCount = array();

		// News Tables exists?
		if (true === $this->getEventstableexists())
		{
			$objEventsStatCount = Database::getInstance()
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
							->execute($VisitorsID, self::PAGE_TYPE_EVENTS);

			while ($objEventsStatCount->next())
			{
				$alias   = false;
				$aliases = $this->getEventsAliases($objEventsStatCount->visitors_page_id);
				if (false !== $aliases['PageAlias'])
				{
					$alias = $aliases['PageAlias'] . '/' . $aliases['EventsAlias'];
				}

				if (false !== $alias)
				{
					$arrEventsStatCount[] = array
					(
						'title'         => $aliases['CalendarAlias'],
						'alias'         => $alias,
						'lang'          => $objEventsStatCount->visitors_page_lang,
						'visits'        => $objEventsStatCount->visitors_page_visits,
						'hits'          => $objEventsStatCount->visitors_page_hits
					);
				}
			}
			if ($parse === true)
			{
				// @var Template $TemplatePartial
				$TemplatePartial = new BackendTemplate('mod_visitors_be_stat_partial_eventsvisithittop');
				$TemplatePartial->EventsVisitHitTop = $arrEventsStatCount;

				return $TemplatePartial->parse();
			}

			return $arrEventsStatCount;
		}

		return false;
	}

	public function generateEventsVisitHitDays($VisitorsID, $limit = 10, $parse = true, $days=7)
	{
		$arrEventsStatCount = array();
		$week               = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-$days, (int) date("Y")));

		// News Tables exists?
		if (true === $this->getEventstableexists())
		{
			$objEventsStatCount = Database::getInstance()
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
							->execute($VisitorsID, self::PAGE_TYPE_EVENTS, $week);

			while ($objEventsStatCount->next())
			{
				$alias   = false;
				$aliases = $this->getEventsAliases($objEventsStatCount->visitors_page_id);
				if (false !== $aliases['PageAlias'])
				{
					$alias = $aliases['PageAlias'] . '/' . $aliases['EventsAlias'];
				}

				if (false !== $alias)
				{
					$arrEventsStatCount[] = array
					(
						'title'         => $aliases['CalendarAlias'],
						'alias'         => $alias,
						'lang'          => $objEventsStatCount->visitors_page_lang,
						'visits'        => $objEventsStatCount->visitors_page_visits,
						'hits'          => $objEventsStatCount->visitors_page_hits
					);
				}
			}
			if ($parse === true)
			{
				// @var Template $TemplatePartial
				$TemplatePartial = new BackendTemplate('mod_visitors_be_stat_partial_eventsvisithitdays');
				$TemplatePartial->EventsVisitHitDays = $arrEventsStatCount;

				return $TemplatePartial->parse();
			}

			return $arrEventsStatCount;
		}

		return false;
	}

	public function getEventsAliases($visitors_page_id)
	{
		// Events Tables exists?
		if (true === $this->getEventstableexists())
		{
			// direkte Reader Seite?
			$objEventsAliases = Database::getInstance()
								->prepare(
									"SELECT
                                        tl_page.alias AS 'PageAlias',
                                        ''  AS 'EventsAlias',
                                        '-' AS 'CalendarAlias'
                                    FROM
                                        tl_page
                                    INNER JOIN
                                        tl_calendar ON tl_calendar.jumpTo = tl_page.id
                                    WHERE tl_calendar.jumpTo = ?
                                    LIMIT 1
                                    "
								)
								->execute($visitors_page_id);

			while ($objEventsAliases->next())
			{
				return array('PageAlias'     => $objEventsAliases->PageAlias,
					'EventsAlias'   => $objEventsAliases->EventsAlias,
					'CalendarAlias' => $objEventsAliases->CalendarAlias);
			}

			$objEventsAliases = Database::getInstance()
								->prepare("SELECT
                                                tl_page.alias AS 'PageAlias',
                                                tl_calendar_events.alias AS 'EventsAlias',
                                                tl_calendar.title as 'CalendarAlias'
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
				return array('PageAlias'       => $objEventsAliases->PageAlias,
					'EventsAlias'       => $objEventsAliases->EventsAlias,
					'CalendarAlias' => $objEventsAliases->CalendarAlias);
			}
		}
		else
		{
			return array('PageAlias'     => false,
				'EventsAlias'   => false,
				'CalendarAlias' => false);
		}
	}
}
