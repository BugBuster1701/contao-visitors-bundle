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

namespace BugBuster\Visitors;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Frontend;
use Contao\Input;
use Contao\System;
# use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front end visitors wizard.
 */
class FrontendVisitors extends Frontend
{
	private $_SCREEN = false; // Screen Resolution

	private static $_BackendUser  = false;

	private $monologLogger;

	/**
	 * Initialize the object (do not remove)
	 */
	public function __construct()
	{
		parent::__construct();

		System::loadLanguageFile('tl_visitors');

		$this->monologLogger = System::getContainer()->get('bug_buster_visitors.logger');
	}

	/**
	 * Run the controller
	 *
	 * @return Response
	 */
	public function run()
	{
		# $logger = System::getContainer()->get('monolog.logger.contao');

		if (false === self::$_BackendUser)
		{
			$objTokenChecker = System::getContainer()->get('contao.security.token_checker');
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

		// Parameter holen
		if ((int) Input::get('vcid')  > 0)
		{
			$visitors_category_id = (int) Input::get('vcid');
			$this->visitorScreenSetDebugSettings($visitors_category_id);
			$this->visitorScreenSetResolutions();
			if ($this->_SCREEN !== false)
			{
				/* __________  __  ___   _____________   ________
				  / ____/ __ \/ / / / | / /_  __/  _/ | / / ____/
				 / /   / / / / / / /  |/ / / /  / //  |/ / / __
				/ /___/ /_/ / /_/ / /|  / / / _/ // /|  / /_/ /
				\____/\____/\____/_/ |_/ /_/ /___/_/ |_/\____/ only
				*/
				$objVisitors = Database::getInstance()
							->prepare("SELECT
                                        tl_visitors.id AS id, visitors_block_time
                                    FROM
                                        tl_visitors
                                    LEFT JOIN
                                        tl_visitors_category ON (tl_visitors_category.id = tl_visitors.pid)
                                    WHERE
                                        pid = ? AND published = ?
                                    ORDER BY id , visitors_name")
								->limit(1)
								->execute($visitors_category_id, 1);
				if ($objVisitors->numRows < 1)
				{
					// $logger->log(
					// 	LogLevel::ERROR,
					// 	$GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid'],
					// 	array('contao' => new ContaoContext('FrontendVisitors ' . VISITORS_VERSION . '.' . VISITORS_BUILD, ContaoContext::ERROR))
					// );
					$this->monologLogger->logSystemLog($GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid']
						,'FrontendVisitors ' . VISITORS_VERSION . '.' . VISITORS_BUILD
						, ContaoContext::ERROR);
				}
				else
				{
					while ($objVisitors->next())
					{
						$this->visitorScreenCountUpdate($objVisitors->id, $objVisitors->visitors_block_time, $visitors_category_id, self::$_BackendUser);
					}
				}
			} // SCREEN !== false
		}
		else
		{
			// $logger->log(
			// 	LogLevel::ERROR,
			// 	$GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid'],
			// 	array('contao' => new ContaoContext('FrontendVisitors ' . VISITORS_VERSION . '.' . VISITORS_BUILD, ContaoContext::ERROR))
			// );
			$this->monologLogger->logSystemLog($GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid']
				,'FrontendVisitors ' . VISITORS_VERSION . '.' . VISITORS_BUILD
				, ContaoContext::ERROR);
		}

		// raus hier
		$objResponse = new Response();

		return $objResponse;
	}

	/**
	 * Set $_SCREEN variable
	 */
	protected function visitorScreenSetResolutions()
	{
		$this->_SCREEN = array("scrw"  => (int) Input::get('scrw'),
			"scrh"  => (int) Input::get('scrh'),
			"scriw" => (int) Input::get('scriw'),
			"scrih" => (int) Input::get('scrih')
		);
		if (
			(int) Input::get('scrw')  == 0
			|| (int) Input::get('scrh')  == 0
			|| (int) Input::get('scriw') == 0
			|| (int) Input::get('scrih') == 0
		) {
			ModuleVisitorLog::writeLog(
				__METHOD__,
				__LINE__,
				'ERR: ' . print_r(array("scrw"  => Input::get('scrw'),
					"scrh"  => Input::get('scrh'),
					"scriw" => Input::get('scriw'),
					"scrih" => Input::get('scrih')
				), true)
			);
			$this->_SCREEN = false;
		}
	}

	/**
	 * Insert/Update Counter
	 */
	protected function visitorScreenCountUpdate($vid, $BlockTime, $visitors_category_id, $BackendUser = false)
	{
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Screen: ' . implode(' ', (array) $this->_SCREEN));

		$ModuleVisitorChecks = new ModuleVisitorChecks($BackendUser);
		if ($ModuleVisitorChecks->checkBot() === true)
		{
			// Debug log_message("visitorCountUpdate BOT=true","debug.log");
			return; // Bot / IP gefunden, wird nicht gezaehlt
		}
		if ($ModuleVisitorChecks->checkUserAgent($visitors_category_id) === true)
		{
			// Debug log_message("visitorCountUpdate UserAgent=true","debug.log");
			return; // User Agent Filterung
		}
		if ($ModuleVisitorChecks->checkBE() === true)
		{
			return; // Backend eingeloggt, nicht zaehlen (Feature: #197)
		}
		// Debug log_message("visitorCountUpdate count: ".$this->Environment->httpUserAgent,"useragents-noblock.log");
		$ClientIP = bin2hex(sha1($visitors_category_id . $ModuleVisitorChecks->visitorGetUserIP(), true)); // sha1 20 Zeichen, bin2hex 40 zeichen
		$BlockTime = (empty($BlockTime)) ? 1800 : $BlockTime; // Sekunden
		$CURDATE = date('Y-m-d');

		// Visitor Screen Blocker
		Database::getInstance()
					->prepare("DELETE FROM
                                    tl_visitors_blocker
                                WHERE
                                    CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                                AND
                                    vid = ?
                                AND
                                    visitors_type = ?")
					->execute($BlockTime, $vid, 's');

		// Blocker IP lesen, sofern vorhanden
		$objVisitBlockerIP = Database::getInstance()
					->prepare("SELECT
                                    id, visitors_ip
                                FROM
                                    tl_visitors_blocker
                                WHERE
                                    visitors_ip = ? AND vid = ? AND visitors_type = ?")
					->execute($ClientIP, $vid, 's');
		// Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':\n'.$objVisitBlockerIP->query );
		// Daten lesen, nur Screen Angaben, die Inner Angaben werden jedesmal überschrieben
		$objScreenCounter = Database::getInstance()
										   ->prepare("SELECT
                                                            id, v_screen_counter
                                                        FROM
                                                            tl_visitors_screen_counter
                                                        WHERE
                                                            v_date = ?
                                                        AND vid = ?
                                                        AND v_s_w = ?
                                                        AND v_s_h = ?")
											->execute($CURDATE, $vid, $this->_SCREEN['scrw'], $this->_SCREEN['scrh']);

		if ($objScreenCounter->numRows < 1)
		{
			if ($objVisitBlockerIP->numRows < 1)
			{
				// Insert IP + Update Visits
				Database::getInstance()
								->prepare("INSERT INTO
                                                 tl_visitors_blocker
                                            SET
                                                vid=?,
                                                visitors_tstamp=CURRENT_TIMESTAMP,
                                                visitors_ip=?,
                                                visitors_type=?")
								->execute($vid, $ClientIP, 's');
				// Insert
				$arrSet = array
				(
					'vid'              => $vid,
					'v_date'           => $CURDATE,
					'v_s_w'            => $this->_SCREEN['scrw'],
					'v_s_h'            => $this->_SCREEN['scrh'],
					'v_s_iw'           => $this->_SCREEN['scriw'],
					'v_s_ih'           => $this->_SCREEN['scrih'],
					'v_screen_counter' => 1
				);
				Database::getInstance()
							   ->prepare("INSERT IGNORE INTO tl_visitors_screen_counter %s")
							   ->set($arrSet)
							   ->execute();
				ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': insert into tl_visitors_screen_counter');

				return;
			}

			// Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.'Update tstamp' );
			// Update tstamp
			Database::getInstance()
						   ->prepare("UPDATE
                                             tl_visitors_blocker
                                        SET
                                            visitors_tstamp=CURRENT_TIMESTAMP
                                        WHERE
                                            visitors_ip=? AND vid=? AND visitors_type=?")
							->execute($ClientIP, $vid, 's');
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': update tl_visitors_blocker');

			return;
		}

		// Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.$objScreenCounter->numRows );
		if ($objVisitBlockerIP->numRows < 1)
		{
			// Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.$objVisitBlockerIP->numRows );
			// Insert IP
			Database::getInstance()
						   ->prepare("INSERT INTO
                                                 tl_visitors_blocker
                                             SET
                                                vid=?,
                                                visitors_tstamp=CURRENT_TIMESTAMP,
                                                visitors_ip=?,
                                                visitors_type=?")
							->execute($vid, $ClientIP, 's');

			$objScreenCounter->next();
			// Update der Screen Counter, Inner Daten dabei aktualisieren
			Database::getInstance()
							->prepare("UPDATE
                            	                tl_visitors_screen_counter
                        	                SET
                            	                v_s_iw = ?,
                            	                v_s_ih = ?,
                                                v_screen_counter = ?
                        	               WHERE
                            	               v_date = ?
                                            AND vid = ?
                                            AND v_s_w = ?
                                            AND v_s_h = ?")
							->execute(
								$this->_SCREEN['scriw'],
								$this->_SCREEN['scrih'],
								$objScreenCounter->v_screen_counter +1,
								$CURDATE,
								$vid,
								$this->_SCREEN['scrw'],
								$this->_SCREEN['scrh']
							);
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': update tl_visitors_screen_counter');
		}
		else
		{
			// Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.'Update tstamp' );
			// Update tstamp
			Database::getInstance()
						   ->prepare("UPDATE
                                                tl_visitors_blocker
                                             SET
                                                visitors_tstamp=CURRENT_TIMESTAMP
                                            WHERE
                                                visitors_ip=? AND vid=? AND visitors_type=?")
							->execute($ClientIP, $vid, 's');
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': update tl_visitors_blocker');
		}
	} // visitorScreenCountUpdate

	protected function visitorScreenSetDebugSettings($visitors_category_id)
	{
		$GLOBALS['visitors']['debug']['screenresolutioncount'] = false;

		$objVisitors = Database::getInstance()
							   ->prepare("SELECT
                                                visitors_expert_debug_screenresolutioncount
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
			$GLOBALS['visitors']['debug']['screenresolutioncount'] = (bool) $objVisitors->visitors_expert_debug_screenresolutioncount;
			ModuleVisitorLog::writeLog('## START ##', '## SCREEN DEBUG ##', '#S' . (int) $GLOBALS['visitors']['debug']['screenresolutioncount']);
		}
	}
}
