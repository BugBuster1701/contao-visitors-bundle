<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace BugBuster\VisitorsBundle\Classes;

use Contao\CoreBundle\Monolog\ContaoContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class VisitorLogger
{
    public function __construct(
        private readonly ContainerInterface $container,
        private LoggerInterface $visitorsLogger,
        private $contaologger)
    {

    }


    /**
     * Add a log entry to contao system log.
     *
     * @param string  $message  The log message
     * @param string  $method   The function name. Typically __METHOD__
     * @param string  $category The category name. Use constants in ContaoContext
     */
    public function logSystemLog(string $message, string $method, string $category): void
    {
        $level = (ContaoContext::ERROR === $category ? LogLevel::ERROR : LogLevel::INFO);

        //$this->container->get('monolog.logger.contao')->log($level, $message, [
        $this->contaologger->log($level, $message, [
            'contao' => new ContaoContext($method, $category),
        ]);
    }

    /**
     * Add a log entry to var/log/prod|test log.
     *
     * @param string       $message  The log message
     * @param string|bool  $class    The function name. Typically __METHOD__
     * @param integer      $line     The line number. Typically __LINE__
     * @param string       $level    The log level
     */
    public function logMonologLog(string $message, string $class, int $line, string $level = 'debug'): void
    {
		if (false !== $class) {
			$this->visitorsLogger->$level($message,["class" => $class.'::'.$line]);
		} else {
			$this->visitorsLogger->$level($message,[]);
		}
    }
}
