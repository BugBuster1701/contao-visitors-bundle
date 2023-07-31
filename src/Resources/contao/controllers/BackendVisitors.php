<?php

/**
 * @copyright  Glen Langer 2017..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL-3.0+
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\Visitors;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Back end visitors wizard.
 *
 * @author     Glen Langer (BugBuster)
 */
class BackendVisitors extends \Contao\Backend
{

	/**
	 * Initialize the controller
	 *
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Authenticate the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('Contao\BackendUser', 'User');
		parent::__construct();

		//$this->User->authenticate(); //deprecated
		if (false === \Contao\System::getContainer()->get('contao.security.token_checker')->hasBackendUser()) 
		{
			throw new AccessDeniedException('Access denied');
		}

		\Contao\System::loadLanguageFile('default');
		\Contao\System::loadLanguageFile('modules');
		\Contao\System::loadLanguageFile('tl_visitors_referrer');
	}

	/**
	 * Run the controller and parse the template
	 *
	 * @return Response
	 */
	public function run()
	{
		/** @var BackendTemplate|object $objTemplate */
		$objTemplate                = new \Contao\BackendTemplate('mod_visitors_be_stat_details_referrer');
		$objTemplate->theme         = \Contao\Backend::getTheme();
		$objTemplate->base          = \Contao\Environment::get('base');
		$objTemplate->language      = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title         = \Contao\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['systemMessages']);
		$objTemplate->charset       = \Contao\Config::get('characterSet');
		$objTemplate->visitorsbecss = VISITORS_BE_CSS;

		if (\is_null(\Contao\Input::get('tl_vid', true)))
		{
		    $objTemplate->messages = $GLOBALS['TL_LANG']['tl_visitors_referrer']['no_referrer'];

		    return $objTemplate->getResponse();
		}
		// <h1 class="main_headline">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['details_for'].': '.\Idna::decode(str_rot13($this->Input->get('tl_referrer',true))).'</h1>
		$objTemplate->messages = '
    	<div class="tl_listing_container list_view">
            <table cellpadding="0" cellspacing="0" summary="Table lists records" class="mod_visitors_be_table_version">
                <tbody>
                    <tr>
                        <th style="padding-left: 2px;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer'].'</th>
                        <th style="width: 145px; padding-left: 2px;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer_last_seen'].'</th>
                        <th style="width: 80px; padding-left: 2px; text-align: center;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['number'].'</th>
                    </tr>';
		/*$objDetails = \Database::getInstance()->prepare("SELECT `visitors_referrer_full`, count(id) as ANZ"
						                     . " FROM `tl_visitors_referrer`"
						                     . " WHERE `visitors_referrer_dns` = ?"
						                     . " AND `vid` = ?"
						                     . " GROUP BY 1 ORDER BY 2 DESC")*/
		$objDetails = \Contao\Database::getInstance()
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
                ->execute(str_rot13(\Contao\Input::get('tl_referrer', true)), \Contao\Input::get('tl_vid', true));
		$intRows = $objDetails->numRows;
		if ($intRows > 0)
		{
	        while ($objDetails->next())
	        {
				$objTemplate->messages .= '
                    <tr>
                        <td class="tl_file_list" style="padding-left: 2px; text-align: left;">'.rawurldecode(htmlspecialchars(\Contao\Idna::decode($objDetails->visitors_referrer_full))).'</td>
                        <td class="tl_file_list" style="padding-left: 2px; text-align: left;">'.date(\Contao\Config::get('datimFormat'), $objDetails->maxtstamp).'</td>
                        <td class="tl_file_list" style="text-align: center;">'.$objDetails->ANZ.'</td>
                    </tr>';
	        }
        }
        else
        {
        	$objTemplate->messages .= '
                    <tr>
                        <td colspan="3">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['no_data'].'</td>
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
