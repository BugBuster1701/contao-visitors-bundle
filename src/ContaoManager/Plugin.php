<?php

/**
 * @copyright  Glen Langer 2008..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Visitors
 * @license    LGPL-3.0+
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 */

namespace BugBuster\VisitorsBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @author Glen Langer (BugBuster)
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('BugBuster\VisitorsBundle\BugBusterVisitorsBundle')
                ->setLoadAfter(['BugBuster\BotdetectionBundle\BugBusterBotdetectionBundle'])
                ->setReplace(['visitors']),
        ];
    }
}
