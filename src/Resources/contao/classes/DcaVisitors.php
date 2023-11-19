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

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
# use Psr\Log\LogLevel;

/**
 * DCA Helper Class DcaVisitors
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 */
class DcaVisitors extends Backend
{
	private $monologLogger;

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->monologLogger = System::getContainer()->get('bug_buster_visitors.logger');
	}

	public function listVisitors($arrRow)
	{
		$key = $arrRow['published'] ? 'published' : 'unpublished';
		if (!\strlen($arrRow['visitors_startdate']))
		{
			$startdate = $GLOBALS['TL_LANG']['tl_visitors']['not_defined'];
		}
		else
		{
			$startdate = date (Config::get('dateFormat'), $arrRow['visitors_startdate']);
		}
		$output = '<div class="cte_type ' . $key . '"><span class="tl_label">' . $arrRow['visitors_name'] . '</span></div>';
		$output.= '<div>' . $GLOBALS['TL_LANG']['tl_visitors']['visitors_startdate'][0] . ': ' . $startdate . '</div>';

		// Debug $output.= '<div>'.print_r($arrRow,true).'</div>';
		return $output;
	}

	/**
	 * Return the "toggle visibility" button
	 * @param  array  $row
	 * @param  string $href
	 * @param  string $label
	 * @param  string $title
	 * @param  string $icon
	 * @param  string $attributes
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (\strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), Input::get('state') == 1);
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		$user = BackendUser::getInstance();
		if (!$user->isAdmin && !$user->hasAccess('tl_visitors::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.svg';
		}

		return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
	}

	/**
	 * Disable/enable a counter
	 * @param integer $intId
	 * @param boolean $blnVisible
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to publish
		$user = BackendUser::getInstance();
		if (!$user->isAdmin && !$user->hasAccess('tl_visitors::published', 'alexf'))
		{
			// System::getContainer()
			// 	->get('monolog.logger.contao')
			// 	->log(
			// 		LogLevel::ERROR,
			// 		'Not enough permissions to publish/unpublish Visitors ID "' . $intId . '"',
			// 		array('contao' => new ContaoContext('tl_visitors toggleVisibility', ContaoContext::ERROR))
			// 	);
			$this->monologLogger->logSystemLog('Not enough permissions to publish/unpublish Visitors ID "' . $intId . '"'
				,'tl_visitors toggleVisibility'
				, ContaoContext::ERROR);

			$this->redirect('contao/main.php?act=error');
		}

		// Update database
		Database::getInstance()->prepare("UPDATE
                                               tl_visitors
                                           SET
                                               published='" . ($blnVisible ? 1 : '') . "'
                                           WHERE
                                               id=?")
								->execute($intId);
	}
}
