<?php
/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Modul Visitors Log - Frontend
 *
 * @copyright  Glen Langer 2012..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\Visitors;
use Contao\StringUtil;

/**
 * Class ModuleVisitorLog
 *
 * @copyright  Glen Langer 2012..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL
 */
class ModuleVisitorLog
{
    /**
     * Write in log file, if debug is enabled
     *
     * @param string  $method
     * @param integer $line
     */
    public static function writeLog($method, $line, $value)
    {
        $GLOBALS['visitors']['debug']['tag'] = (bool) ($GLOBALS['visitors']['debug']['tag'] ?? false);
        $GLOBALS['visitors']['debug']['checks'] = (bool) ($GLOBALS['visitors']['debug']['checks'] ?? false);
        $GLOBALS['visitors']['debug']['referrer'] = (bool) ($GLOBALS['visitors']['debug']['referrer'] ?? false);
        $GLOBALS['visitors']['debug']['searchengine'] = (bool) ($GLOBALS['visitors']['debug']['searchengine'] ?? false);
        $GLOBALS['visitors']['debug']['screenresolutioncount'] = (bool) ($GLOBALS['visitors']['debug']['screenresolutioncount'] ?? false);
        
        if ($method == '## START ##') 
        {
            if (!isset($GLOBALS['visitors']['debug']['first'])) 
            {

                if ($GLOBALS['visitors']['debug']['tag']          ||
                    $GLOBALS['visitors']['debug']['checks']       ||
                    $GLOBALS['visitors']['debug']['referrer']     ||
                    $GLOBALS['visitors']['debug']['searchengine'] ||
                    $GLOBALS['visitors']['debug']['screenresolutioncount']
                   )
                {
                    $arrUniqid = StringUtil::trimsplit('.', uniqid('c0n7a0', true));
                    $GLOBALS['visitors']['debug']['first'] = $arrUniqid[1];
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $method, $line, $value), 'visitors_debug');

                    return;
                }

                return;
            }
            else
            {
                return;
            }
        }

        $arrNamespace = StringUtil::trimsplit('::', $method);
        $arrClass =  StringUtil::trimsplit('\\', $arrNamespace[0]);
        $vclass = $arrClass[\count($arrClass)-1]; // class that will write the log

        if (\is_array($value))
        {
            $value = print_r($value, true);
        }

        switch ($vclass)
        {
            case "ModuleVisitorsTag":
                if ($GLOBALS['visitors']['debug']['tag'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorChecks":
                if ($GLOBALS['visitors']['debug']['checks'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorReferrer":
                if ($GLOBALS['visitors']['debug']['referrer'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "ModuleVisitorSearchEngine":
                if ($GLOBALS['visitors']['debug']['searchengine'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "FrontendVisitors":
                if ($GLOBALS['visitors']['debug']['screenresolutioncount'])
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            case "VisitorsFrontendController":
                if ($GLOBALS['visitors']['debug']['tag']) //@todo temporär, eigene Regel notwendig  
                {
                    self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $vclass.'::'.$arrNamespace[1], $line, $value), 'visitors_debug');
                }
                break;
            default:
                self::logMessage(sprintf('[%s] [%s] [%s] %s', $GLOBALS['visitors']['debug']['first'], $method, $line, '('.$vclass.')'.$value), 'visitors_debug');
                break;
        }

        return;
    }

    /**
     * Wrapper for old log_message
     * 
     * @param string $strMessage
     * @param string $strLogg
     */
    public static function logMessage($strMessage, $strLog=null)
    {
        $env = $_SERVER['APP_ENV'] ?? 'prod';

        if ($strLog === null)
        {
            $strLog = $env . '-' . date('Y-m-d') . '.log';
        }
        else 
        {
            $strLog = $env . '-' . date('Y-m-d') . '-' . $strLog . '.log';
        }

        $strLogsDir = null;

        if (($container = \System::getContainer()) !== null)
        {
            $strLogsDir = $container->getParameter('kernel.logs_dir');
        }

        if (!$strLogsDir)
        {
            $strLogsDir = TL_ROOT . '/var/logs';
        }

        error_log(sprintf("[%s] %s\n", date('d-M-Y H:i:s'), $strMessage), 3, $strLogsDir . '/' . $strLog);
    }

    /**
     * Triggers a silenced warning notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     *
     * @author Nicolas Grekas <p@tchwork.com> (original was trigger_deprecation)
     */
    public static function triggerWarning(string $package, string $version, string $message, ...$args)
    {
       @trigger_error(($package || $version ? "Since $package $version: " : '').($args ? vsprintf($message, $args) : $message), E_USER_WARNING);
    }

    /**
     * Triggers a silenced deprecation notice.
     *
     * @param string $package The name of the Composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message The message of the deprecation
     * @param mixed  ...$args Values to insert in the message using printf() formatting
     */
    function triggerDeprecation(string $package, string $version, string $message, ...$args)
    {
        trigger_deprecation($package, $version, $message, ...$args);
    }
}
