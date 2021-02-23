<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 *
 * @copyright  Glen Langer 2021 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\VisitorsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * This migration fix the XSS nuclei scanner entries.
 */
class Version167Update extends AbstractMigration
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection the database connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the name.
     */
    public function getName(): string
    {
        return 'Visitors Bundle Version167Update XSS Fix';
    }

    /**
     * Must only run if:
     * - the Visitors tables are present and
     * - the column visitors_keywords.
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_visitors_searchengines'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_visitors_searchengines');
        if (isset($columns['visitors_keywords'])) {
            $result = $this->connection->query("
                    SELECT id FROM 
                        tl_visitors_searchengines
                    WHERE
                        visitors_keywords LIKE '%testing-xss%'
            ")->fetch();
            
            if (count($result) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): MigrationResult
    {
        $stmt = $this->connection->prepare("
            DELETE FROM
                tl_visitors_searchengines
            WHERE
                visitors_keywords LIKE '%testing-xss1%'
        ");

        $stmt->execute();

        return new MigrationResult(
            true,
            'XSS rows '.$stmt->rowCount().' x deleted. (Visitors Bundle)'
        );
    }
}
