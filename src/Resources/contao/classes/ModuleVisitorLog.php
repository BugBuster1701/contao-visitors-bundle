<?php
/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Modul Visitors Log - Frontend
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @package    GLVisitors
 * @see	       https://github.com/BugBuster1701/visitors
 */

namespace BugBuster\Visitors;

/**
 * Class ModuleVisitorLog
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL
 */
class ModuleVisitorLog
{
    /**
     * Write in log file, if debug is enabled
     *
     * @param string    $method
     * @param integer   $line
     */
    public static function writeLog($method,$line,$value)
    {
        if ($method == '## START ##') 
        {
            if (!isset($GLOBALS['visitors']['debug']['first'])) 
            {
                if ((bool)$GLOBALS['visitors']['debug']['tag']          ||
                    (bool)$GLOBALS['visitors']['debug']['checks']       ||
                    (bool)$GLOBALS['visitors']['debug']['referrer']     ||
                    (bool)$GLOBALS['visitors']['debug']['searchengine'] ||
                    (bool)$GLOBALS['visitors']['debug']['screenresolutioncount']
                   )
                {
                    $arrUniqid = trimsplit('.', uniqid('c0n7a0',true) );
                    $GLOBALS['visitors']['debug']['first'] = $arrUniqid[1];
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$method,$line,$value),'visitors_debug.log');
                    return ;
                }
                return ;
            }
            else
            {
                return ;
            }
        }
                
        $arrNamespace = trimsplit('::', $method);
        $arrClass =  trimsplit('\\', $arrNamespace[0]);
        $vclass = $arrClass[2]; // class that will write the log
        
        if (is_array($value))
        {
            $value = print_r($value,true);
        }
        
        switch ($vclass)
        {
            case "ModuleVisitorsTag":
                if ($GLOBALS['visitors']['debug']['tag'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'visitors_debug.log');
                }
                break;
            case "ModuleVisitorChecks":
                if ($GLOBALS['visitors']['debug']['checks'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'visitors_debug.log');
                }
                break;
            case "ModuleVisitorReferrer":
                if ($GLOBALS['visitors']['debug']['referrer'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'visitors_debug.log');
                }
                break;
            case "ModuleVisitorSearchEngine":
                if ($GLOBALS['visitors']['debug']['searchengine'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'visitors_debug.log');
                }
                break;
            case "ModuleVisitorsScreenCount":
                if ($GLOBALS['visitors']['debug']['screenresolutioncount'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'visitors_debug.log');
                }
                break;
            default:
                log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['visitors']['debug']['first'],$method,$line,'('.$vclass.')'.$value),'visitors_debug.log');
                break;
        }
        return ;
    }
}
