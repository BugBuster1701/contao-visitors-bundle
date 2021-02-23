<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 *
 * @copyright  Glen Langer 2019..2021 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\VisitorsBundle\Runonce;

use Contao\StringUtil;

/**
 * Class CompileCommands.
 *
 * Call over the hook sqlCompileCommands
 */
class CompileCommands
{
    /**
     * Hook Call sqlCompileCommands.
     *
     * @param array $definition Array of SQL statements
     *
     * @return array Array of SQL statements
     */
    public function runMigration(array $definition)
    {
        $this->runMigration1525();

        return $definition;
    }

    /**
     * Run the migration to version 1.5.2.5
     *
     */
    public function runMigration1525(): void
    {
        $migration = false;
        if (\Contao\Database::getInstance()->tableExists('tl_visitors_searchengines')) {
            if (\Contao\Database::getInstance()->fieldExists('visitors_keywords ', 'tl_visitors_searchengines')) {
                \Contao\Database::getInstance()
                ->prepare("DELETE FROM
                                tl_visitors_searchengines
                            WHERE
                                visitors_keywords LIKE ?")
                ->execute('%testing-xss%');
                $migration = true;
            }
        }

        if (true === $migration) {
            //Protokoll
            $strText = 'Visitors-Bundle has been migrated';
            \Contao\Database::getInstance()->prepare('INSERT INTO `tl_log` (tstamp, source, action, username, text, func, browser) VALUES(?, ?, ?, ?, ?, ?, ?)')
                            ->execute(time(), 'BE', 'CONFIGURATION', '', StringUtil::specialchars($strText), 'Visitors Bundle Migration', '127.0.0.1', 'NoBrowser')
            ;
        }
    }
}
