<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Modul Visitors Screen Count - Frontend for Screen Counting
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
use BugBuster\Visitors\ModuleVisitorLog;

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');

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
 * Class ModuleVisitorsScreenCount 
 *
 * @copyright  Glen Langer 2012..2014
 * @author     Glen Langer 
 * @package    GLVisitors
 * @license    LGPL 
 */
class ModuleVisitorsScreenCount extends \Frontend  
{
	private $_SCREEN = false; // Screen Resolution
	
	/**
	 * Initialize object 
	 */
	public function __construct()
	{
		parent::__construct();
		\System::loadLanguageFile('tl_visitors');
	}

	public function run()
	{
		//Parameter holen
		if ((int)\Input::get('vcid')  > 0)
		{
			$visitors_category_id = (int)\Input::get('vcid');
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
    			$objVisitors = \Database::getInstance()
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
    				      ->executeUncached($visitors_category_id,1);
    			if ($objVisitors->numRows < 1) 
    			{
    			    $this->log($GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid'], 'ModuleVisitorsScreenCount '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR);
    			} 
    			else 
    			{
    				while ($objVisitors->next()) 
    				{
    				    $this->visitorScreenCountUpdate($objVisitors->id, $objVisitors->visitors_block_time, $visitors_category_id);
    				    
    				}
    			}
            } //SCREEN !== false
		} 
		else 
		{
			$this->log($GLOBALS['TL_LANG']['tl_visitors']['wrong_screen_catid'], 'ModuleVisitorsScreenCount '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR);
		}

		//Pixel und raus hier
		header('Cache-Control: no-cache');
		header('Content-type: image/gif');
		header('Content-length: 43');

		echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
	} //function
	
	/**
	 * Set $_SCREEN variable
	 */
	protected function visitorScreenSetResolutions()
	{
	    $this->_SCREEN = array( "scrw"  => (int)\Input::get('scrw'),
                    	        "scrh"  => (int)\Input::get('scrh'),
                    	        "scriw" => (int)\Input::get('scriw'),
                    	        "scrih" => (int)\Input::get('scrih')
                    	    );
	    if ((int)\Input::get('scrw')  == 0 ||
	        (int)\Input::get('scrh')  == 0 ||
	        (int)\Input::get('scriw') == 0 ||
	        (int)\Input::get('scrih') == 0
	       )
	    {
	        ModuleVisitorLog::writeLog(__METHOD__ ,
                                     __LINE__ , 
                                     'ERR: '.print_r(array( "scrw"  => \Input::get('scrw'),
                                                	        "scrh"  => \Input::get('scrh'),
                                                	        "scriw" => \Input::get('scriw'),
                                                	        "scrih" => \Input::get('scrih')
                                                	      ), true) 
                                     );
	        $this->_SCREEN = false;
	    }
	}
	
	/**
	 * Insert/Update Counter
	 */
	protected function visitorScreenCountUpdate($vid, $BlockTime, $visitors_category_id)
	{
	    ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ': '.print_r($this->_SCREEN, true) );
	    
		$ModuleVisitorChecks = new \Visitors\ModuleVisitorChecks();
		if ($ModuleVisitorChecks->checkBot() === true) 
		{
			//Debug log_message("visitorCountUpdate BOT=true","debug.log");
	    	return; //Bot / IP gefunden, wird nicht gezaehlt
	    }
	    if ($ModuleVisitorChecks->checkUserAgent($visitors_category_id) === true) 
	    {
	    	//Debug log_message("visitorCountUpdate UserAgent=true","debug.log");
	    	return ; //User Agent Filterung
	    }
	    if ($ModuleVisitorChecks->checkBE() === true)
	    {
	        return; // Backend eingeloggt, nicht zaehlen (Feature: #197)
	    }
	    //Debug log_message("visitorCountUpdate count: ".$this->Environment->httpUserAgent,"useragents-noblock.log");
	    $ClientIP = bin2hex(sha1($visitors_category_id . $this->visitorGetUserIP(),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BlockTime = ($BlockTime == '') ? 1800 : $BlockTime; //Sekunden
	    $CURDATE = date('Y-m-d');

	    //Visitor Screen Blocker
	    \Database::getInstance()
	            ->prepare("DELETE FROM
                                tl_visitors_blocker
                            WHERE
                                CURRENT_TIMESTAMP - INTERVAL ? SECOND > visitors_tstamp
                            AND 
                                vid = ?
                            AND 
                                visitors_type = ?")
                ->executeUncached($BlockTime, $vid, 's');
	    
	    //Blocker IP lesen, sofern vorhanden
	    $objVisitBlockerIP = \Database::getInstance()
	            ->prepare("SELECT 
                                id, visitors_ip
                            FROM
                                tl_visitors_blocker
                            WHERE
                                visitors_ip = ? AND vid = ? AND visitors_type = ?")
                ->executeUncached($ClientIP, $vid, 's');
	    //Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':\n'.$objVisitBlockerIP->query );
	    //Daten lesen, nur Screen Angaben, die Inner Angaben werden jedesmal überschrieben 
	    $objScreenCounter = \Database::getInstance() 
                    	    ->prepare("SELECT
                                            id, v_screen_counter
                                        FROM
                                            tl_visitors_screen_counter
                                        WHERE
                                            v_date = ? 
                                        AND vid = ? 
                                        AND v_s_w = ? 
                                        AND v_s_h = ?")
                            ->executeUncached($CURDATE, $vid, $this->_SCREEN['scrw'], $this->_SCREEN['scrh']);
	    
	    if ($objScreenCounter->numRows < 1)
	    {
    	    if ($objVisitBlockerIP->numRows < 1) 
    	    {
    	        // Insert IP + Update Visits
    	        \Database::getInstance()
            	        ->prepare("INSERT INTO
                                        tl_visitors_blocker
                                    SET
                                        vid=?,
                                        visitors_tstamp=CURRENT_TIMESTAMP,
                                        visitors_ip=?,
                                        visitors_type=?")
                        ->executeUncached($vid, $ClientIP, 's');
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
    	        \Database::getInstance()
                	        ->prepare("INSERT IGNORE INTO tl_visitors_screen_counter %s")
                	        ->set($arrSet)
                	        ->executeUncached();
    	        ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ': insert into tl_visitors_screen_counter' );
    	        return ;
    	    } 
    	    else 
    	    {
    	        //Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.'Update tstamp' );
    	    	// Update tstamp
    	    	\Database::getInstance()
    	    	        ->prepare("UPDATE
                                        tl_visitors_blocker
                                    SET
                                        visitors_tstamp=CURRENT_TIMESTAMP
                                    WHERE
                                        visitors_ip=? AND vid=? AND visitors_type=?")
                        ->executeUncached($ClientIP, $vid, 's');
    	    	ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ': update tl_visitors_blocker' );
    	    	return ;
    	    }
	    }
	    else 
	    {
	        //Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.$objScreenCounter->numRows );
	        if ($objVisitBlockerIP->numRows < 1)
	        {
	            //Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.$objVisitBlockerIP->numRows );
	            // Insert IP
	            \Database::getInstance()
                            ->prepare("INSERT INTO
                                             tl_visitors_blocker
                                        SET
                                            vid=?,
                                            visitors_tstamp=CURRENT_TIMESTAMP,
                                            visitors_ip=?,
                                            visitors_type=?")
                            ->executeUncached($vid, $ClientIP, 's');
	         
    	        $objScreenCounter->next();
    	        //Update der Screen Counter, Inner Daten dabei aktualisieren
                \Database::getInstance()
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
                	        ->executeUncached($this->_SCREEN['scriw'],
                                              $this->_SCREEN['scrih'],
                                              $objScreenCounter->v_screen_counter +1,
                                              $CURDATE, 
                                              $vid,
                                              $this->_SCREEN['scrw'],
                                              $this->_SCREEN['scrh']
                                              );
                ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ': update tl_visitors_screen_counter' );
	        }
	        else 
	        {
	            //Debug ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ':'.'Update tstamp' );
	            // Update tstamp
	            \Database::getInstance()
                            ->prepare("UPDATE
                                            tl_visitors_blocker
                                        SET
                                            visitors_tstamp=CURRENT_TIMESTAMP
                                        WHERE
                                            visitors_ip=? AND vid=? AND visitors_type=?")
                            ->executeUncached($ClientIP, $vid, 's');
	            ModuleVisitorLog::writeLog(__METHOD__ , __LINE__ , ': update tl_visitors_blocker' );
	        }
	    }
	    return ;
	} //visitorScreenCountUpdate
	
	protected function visitorScreenSetDebugSettings($visitors_category_id)
	{
	    $GLOBALS['visitors']['debug']['screenresolutioncount'] = false;
	     
	    $objVisitors = \Database::getInstance()
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
	           ->executeUncached($visitors_category_id,1);
	    while ($objVisitors->next())
	    {
	        $GLOBALS['visitors']['debug']['screenresolutioncount'] = (boolean)$objVisitors->visitors_expert_debug_screenresolutioncount;
	        ModuleVisitorLog::writeLog('## START ##', '## SCREEN DEBUG ##', '#S'.(int)$GLOBALS['visitors']['debug']['screenresolutioncount']);
	    }
	}
	
	/**
	 * Get User IP
	 *
	 * @return string
	 */
	protected function visitorGetUserIP()
	{
	    $UserIP = \Environment::get('ip');
	    if (strpos($UserIP, ',') !== false) //first IP
	    {
	        $UserIP = trim( substr($UserIP, 0, strpos($UserIP, ',') ) );
	    }
	    if ( true === $this->visitorIsPrivateIP($UserIP) &&
	        false === empty($_SERVER['HTTP_X_FORWARDED_FOR'])
	    )
	    {
	        //second try
	        $HTTPXFF = $_SERVER['HTTP_X_FORWARDED_FOR'];
	        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
	
	        $UserIP = \Environment::get('ip');
	        if (strpos($UserIP, ',') !== false) //first IP
	        {
	            $UserIP = trim( substr($UserIP, 0, strpos($UserIP, ',') ) );
	        }
	        $_SERVER['HTTP_X_FORWARDED_FOR'] = $HTTPXFF;
	    }
	    return $UserIP;
	}
	
	/**
	 * Check if an IP address is from private or reserved ranges.
	 *
	 * @param string $UserIP
	 * @return boolean         true = private/reserved
	 */
	protected function visitorIsPrivateIP($UserIP = false)
	{
	    return !filter_var($UserIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}
	
} // class

/**
 * Instantiate controller
 */
$objVisitorsScreenCount = new ModuleVisitorsScreenCount();
$objVisitorsScreenCount->run();
