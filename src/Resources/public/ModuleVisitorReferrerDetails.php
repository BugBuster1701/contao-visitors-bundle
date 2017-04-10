<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Modul Visitors Stat - Backend Referrer Details
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @package    GLVisitors
 * @see	       https://github.com/BugBuster1701/visitors 
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');

$dir = __DIR__;
 
while ($dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php'))
{
    $dir = dirname($dir);
}
 
if (!is_file($dir . '/system/initialize.php'))
{
    throw new \ErrorException('Could not find initialize.php!',2,1,basename(__FILE__),__LINE__);
}
require($dir . '/system/initialize.php');


/**
 * Class ModuleVisitorReferrerDetails
 *
 * @copyright  Glen Langer 2007..2014
 * @author     Glen Langer
 * @package    GLVisitors
 */
class ModuleVisitorReferrerDetails extends \Backend // Backend bringt DB mit
{
   
    /**
	 * Set the current file
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct(); 
		$this->User->authenticate(); 
	    $this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
		$this->loadLanguageFile('tl_visitors_referrer'); 
	}
	
    public function run()
	{
   	    if ( is_null( \Input::get('tl_referrer',true) ) || 
   	         is_null( \Input::get('tl_vid',true) ) )
   	    {
   	        echo "<html><body>".$GLOBALS['TL_LANG']['tl_visitors_referrer']['no_referrer']."</body></html>";
            return ;
	    }
	    
	    echo 'echo <!DOCTYPE html>
<html lang="de">	    
<head>
<meta charset="utf-8">
<base href="'.\Environment::get('base').'"></base>
<meta name="generator" content="Contao Open Source CMS">
<title>Contao Open Source CMS '.VERSION.'</title>
<link rel="stylesheet" type="text/css" href="system/themes/'.$this->getTheme().'/basic.css" media="screen" />
<link rel="stylesheet" type="text/css" href="system/themes/'.$this->getTheme().'/main.css" media="screen" />
';
echo '<!--[if lte IE 7]><link type="text/css" rel="stylesheet" href="system/themes/'.$this->getTheme().'/iefixes.css" media="screen" /><![endif]-->
';
echo '
<link rel="stylesheet" type="text/css" href="system/modules/visitors/assets/mod_visitors_be_'.VISITORS_BE_CSS.'.css" media="all" />
</head>
<body id="top">
<div id="main">
	<br>
	<h1 class="main_headline">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['details_for'].': '.\Idna::decode(str_rot13($this->Input->get('tl_referrer',true))).'</h1>
	<br><br>
	<div class="tl_formbody_edit">
		<table cellpadding="0" cellspacing="0" summary="Table lists records" class="mod_visitors_be_table">
		<tbody>
			<tr>
				<td style="padding-left: 2px;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer'].'</td>
				<td style="width: 120px; padding-left: 2px;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['visitor_referrer_last_seen'].'</td>
				<td style="width: 80px; padding-left: 2px; text-align: center;" class="tl_folder_tlist">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['number'].'</td>
			</tr>';
		/*$objDetails = \Database::getInstance()->prepare("SELECT `visitors_referrer_full`, count(id) as ANZ"
						                     . " FROM `tl_visitors_referrer`"
						                     . " WHERE `visitors_referrer_dns` = ?"
						                     . " AND `vid` = ?"
						                     . " GROUP BY 1 ORDER BY 2 DESC")*/
		$objDetails = \Database::getInstance()
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
                ->execute(str_rot13(\Input::get('tl_referrer',true)),\Input::get('tl_vid',true));
		$intRows = $objDetails->numRows;
		if ($intRows > 0) 
		{
	        while ($objDetails->next())
	        {
				echo '
			<tr>
				<td class="tl_file_list" style="padding-left: 2px; text-align: left;">'.rawurldecode(htmlspecialchars(\Idna::decode($objDetails->visitors_referrer_full))).'</td>
				<td class="tl_file_list" style="padding-left: 2px; text-align: left;">'.date($GLOBALS['TL_CONFIG']['datimFormat'],$objDetails->maxtstamp).'</td>
				<td class="tl_file_list" style="text-align: center;">'.$objDetails->ANZ.'</td>
			</tr>';
	        }
        } 
        else 
        {
        	echo '
        	<tr>
				<td colspan="3">'.$GLOBALS['TL_LANG']['tl_visitors_referrer']['no_data'].'</td>
			</tr>';
        }
	    echo '
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>
		</table>
	</div>
</div>
<div class="clear"></div>
</body>
</html>';

	} // run
}

/**
 * Instantiate
 */
$objModuleVisitorReferrerDetails = new ModuleVisitorReferrerDetails();
$objModuleVisitorReferrerDetails->run();
