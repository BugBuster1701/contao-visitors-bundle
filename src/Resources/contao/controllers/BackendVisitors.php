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

namespace BugBuster\Visitors;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Environment;
use Contao\Idna;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;

/**
 * Back end visitors wizard.
 */
class BackendVisitors extends Backend
{
	/**
	 * Initialize the controller
	 */
	public function __construct()
	{
		if (false === System::getContainer()->get('contao.security.token_checker')->hasBackendUser())
		{
			throw new AccessDeniedException('Access denied');
		}

		System::loadLanguageFile('default');
		System::loadLanguageFile('modules');
		System::loadLanguageFile('tl_visitors_referrer');
	}

	/**
	 * Run the controller and parse the template
	 *
	 * @return Response
	 */
	public function run()
	{
		/** @var BackendTemplate|object $objTemplate */
		$objTemplate                = new BackendTemplate('mod_visitors_be_stat_details_referrer');
		$objTemplate->theme         = Backend::getTheme();
		$objTemplate->base          = Environment::get('base');
		$objTemplate->language      = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title         = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['systemMessages']);
		$objTemplate->charset       = System::getContainer()->getParameter('kernel.charset');
		$objTemplate->visitorsbecss = VISITORS_BE_CSS;
		$objTemplate->contaoversion = ContaoCoreBundle::getVersion();

		if (null === Input::get('tl_vid', true))
		{
			$objTemplate->messages = $GLOBALS['TL_LANG']['tl_visitors_referrer']['no_referrer'];

			return $objTemplate->getResponse();
		}
		// <h1 class="main_headline">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['details_for'].': '.\Contao\Idna::decode(str_rot13($this->Input->get('tl_referrer',true))).'</h1>
		$objTemplate->messages = '
    	<div class="tl_listing_container list_view">
            <table cellpadding="0" cellspacing="0" summary="Table lists records" class="mod_visitors_be_table_version with-zebra">
                <tbody>
                    <tr>
                        <th style="padding-left: 2px;" class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer'] . '</th>
                        <th style="width: 145px; padding-left: 2px;" class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer_last_seen'] . '</th>
                        <th style="width: 80px; padding-left: 2px; text-align: center;" class="tl_folder_tlist">' . $GLOBALS['TL_LANG']['tl_visitors_referrer']['number'] . '</th>
                    </tr>';
		/*$objDetails = \Database::getInstance()->prepare("SELECT `visitors_referrer_full`, count(id) as ANZ"
											 . " FROM `tl_visitors_referrer`"
											 . " WHERE `visitors_referrer_dns` = ?"
											 . " AND `vid` = ?"
											 . " GROUP BY 1 ORDER BY 2 DESC")*/
		$objDetails = Database::getInstance()
				->prepare("SELECT
                                visitors_referrer_full,
                                count(id)   as ANZ,
                                max(tstamp) as maxtstamp
                            FROM
                                tl_visitors_referrer
                            WHERE
                                visitors_referrer_dns = ? AND vid = ?
                            GROUP BY 1
                            ORDER BY 2 DESC")
				->execute(str_rot13(Input::get('tl_referrer', true)), Input::get('tl_vid', true));
		$intRows = $objDetails->numRows;
		if ($intRows > 0)
		{
			while ($objDetails->next())
			{
				$objTemplate->messages .= '
                    <tr>
                        <td class="tl_file_list" style="padding-left: 2px; text-align: left;">' . rawurldecode(htmlspecialchars(Idna::decode($objDetails->visitors_referrer_full))) . '</td>
                        <td class="tl_file_list" style="padding-left: 2px; text-align: left;">' . date(Config::get('datimFormat'), $objDetails->maxtstamp) . '</td>
                        <td class="tl_file_list" style="text-align: center;">' . $objDetails->ANZ . '</td>
                    </tr>';
			}
		}
		else
		{
			$objTemplate->messages .= '
                    <tr>
                        <td colspan="3">' . $GLOBALS['TL_LANG']['tl_visitors_referrer']['no_data'] . '</td>
                    </tr>';
		}
		$objTemplate->messages .= '
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>';

		return $objTemplate->getResponse();
	}
}
