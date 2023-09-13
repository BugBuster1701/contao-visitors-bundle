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

use BugBuster\BotDetection\ModuleBotDetection;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Environment;
use Contao\Frontend;
use Contao\Idna;
use Contao\System;
use Psr\Log\LogLevel;

/**
 * Class ModuleVisitorChecks
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 * @license    LGPL
 */
class ModuleVisitorChecks extends Frontend
{
	/**
	 * Current version of the class.
	 */
	const VERSION           = '4.1';

	private $_BackendUser   = false;

	/**
	 * Initialize the object (do not remove)
	 */
	public function __construct($BackendUser = false)
	{
		parent::__construct();
		$this->_BackendUser = $BackendUser;
	}

	/**
	 * Spider Bot Check
	 *
	 * @return bool
	 */
	public function checkBot()
	{
		$bundles = array_keys(System::getContainer()->getParameter('kernel.bundles')); // old \ModuleLoader::getActive()

		if (!\in_array('BugBusterBotdetectionBundle', $bundles))
		{
			// BugBusterBotdetectionBundle Modul fehlt, Abbruch
			System::getContainer()
				 ->get('monolog.logger.contao')
				 ->log(
				 	LogLevel::ERROR,
				 	'contao-botdetection-bundle extension required for extension: Visitors!',
				 	array('contao' => new ContaoContext('ModuleVisitorChecks checkBot ', ContaoContext::ERROR))
				 );
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, print_r($bundles, true));

			return false;
		}
		$ModuleBotDetection = new ModuleBotDetection();
		if ($ModuleBotDetection->checkBotAllTests())
		{
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': True');

			return true;
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': False');

		return false;
	} // checkBot

	/**
	 * HTTP_USER_AGENT Special Check
	 *
	 * @return bool
	 */
	public function checkUserAgent($visitors_category_id)
	{
		if (Environment::get('httpUserAgent'))
		{
			$UserAgent = trim(Environment::get('httpUserAgent'));
		}
		else
		{
			return false; // Ohne Absender keine Suche
		}
		$arrUserAgents = array();
		$objUserAgents = Database::getInstance()
				->prepare("SELECT
                                `visitors_useragent`
                            FROM
                                `tl_module`
                            WHERE
                                `type` = ? AND `visitors_categories` = ?")
				->execute('visitors', $visitors_category_id);
		if ($objUserAgents->numRows)
		{
			while ($objUserAgents->next())
			{
				$arrUserAgents = array_merge($arrUserAgents, explode(",", $objUserAgents->visitors_useragent));
			}
		}
		if (\strlen(trim($arrUserAgents[0])) == 0)
		{
			return false; // keine Angaben im Modul
		}
		// Suche
		$CheckUserAgent=str_replace($arrUserAgents, '#', $UserAgent);
		if ($UserAgent != $CheckUserAgent)
		{ 	// es wurde ersetzt also was gefunden
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': True');

			return true;
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': False');

		return false;
	} // checkUserAgent

	/**
	 * BE Login Check
	 * basiert auf Frontend.getLoginStatus
	 *
	 * @return bool
	 */
	public function checkBE()
	{
		if ($this->_BackendUser)
		{
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': True');

			return true;
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': False');

		return false;
	} // CheckBE

	/**
	 * Check if Domain valid
	 *
	 * @param  string  $host Host / domain.tld
	 * @return boolean
	 */
	public function isDomain($host)
	{
		$dnsResult = false;
		// $this->_vhost :  Host.TLD
		// idn_to_ascii
		$dnsResult = @dns_get_record(Idna::encode($host), DNS_A + DNS_AAAA);
		if ((bool) $dnsResult)
		{
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': True');

			return true;
		}
		ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': False');

		return false;
	}

	/**
	 * Check if a string contains a valid IPv6 address.
	 * If the string was extracted with parse_url (host),
	 * the brackets must be removed.
	 *
	 * @param  string  $ip6
	 * @return boolean
	 */
	public function isIP6($ip6)
	{
		return filter_var(trim($ip6, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? true : false;
	}

	/**
	 * Check if a string contains a valid IPv4 address.
	 *
	 * @param  string  $ip4
	 * @return boolean
	 */
	public function isIP4($ip4)
	{
		return filter_var($ip4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false;
	}

	/**
	 * Get User IP
	 *
	 * @return string
	 */
	public function visitorGetUserIP()
	{
		$UserIP = Environment::get('ip');
		if (strpos($UserIP, ',') !== false) // first IP
		{
			$UserIP = trim(substr($UserIP, 0, strpos($UserIP, ',')));
		}
		if (
			true === $this->visitorIsPrivateIP($UserIP)
			&& false === empty($_SERVER['HTTP_X_FORWARDED_FOR'])
		) {
			// second try
			$HTTPXFF = $_SERVER['HTTP_X_FORWARDED_FOR'];
			$_SERVER['HTTP_X_FORWARDED_FOR'] = '';

			$UserIP = Environment::get('ip');
			if (strpos($UserIP, ',') !== false) // first IP
			{
				$UserIP = trim(substr($UserIP, 0, strpos($UserIP, ',')));
			}
			$_SERVER['HTTP_X_FORWARDED_FOR'] = $HTTPXFF;
		}

		return $UserIP;
	}

	/**
	 * Check if an IP address is from private or reserved ranges.
	 *
	 * @param  string  $UserIP
	 * @return boolean true = private/reserved
	 */
	public function visitorIsPrivateIP($UserIP = false)
	{
		return !filter_var($UserIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}
}
