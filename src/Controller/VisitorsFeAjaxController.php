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

namespace BugBuster\VisitorsBundle\Controller;

use Doctrine\Dbal\Connection;
use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Component\Routing\Annotation\Route;
use BugBuster\VisitorsBundle\Classes\VisitorLogger;
use BugBuster\VisitorsBundle\Classes\VisitorCalculator;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\PageModel;
use Contao\System;

/**
 * Handles the Visitors front end routes.
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 *
 * @Route("/visitors", defaults={"_scope" = "frontend", "_token_check" = false})
 */
class VisitorsFeAjaxController
{
    private $db;
    private $monologLogger;
    private $visitorCalculator;
    private $objPage;

    public function __construct(Connection $db, VisitorLogger $logger, VisitorCalculator $VisitorCalculator)
    {
        $this->db = $db;
        $this->monologLogger = $logger;
        $this->visitorCalculator = $VisitorCalculator;

        /** @var PageModel $this->$objPage */
        $this->objPage = $this->getPageModel();
        if (null != $this->objPage)
        {
            $this->objPage->current()->loadDetails(); // for language via cache call
        }
    }

    protected function getPageModel(): PageModel|null
    {
        $request = null;
        $container = System::getContainer();
        if (null !== $container)
        {
            $request = $container->get('request_stack')->getCurrentRequest();
        }


        if (null !== $request && ($pageModel = $request->attributes->get('pageModel')) instanceof PageModel) {
            return $pageModel;
        }

        return null;
    }

    /**
     * Renders the Counter Values as JSON
     *
     * @return JsonResponse 
     *
     * @Route("/coval/{vc}", name="visitors_frontend_countervalues")
     */
    public function  __invoke(int $vc): JsonResponse 
    {
        $rowBasics = $this->getBasics($vc);

        $rowValues = $this->getValues($rowBasics, $vc);

        $arrJson = [
            'statusBasics'   => !$rowBasics ? ['return' => 'no published counter found'] : ['return' => 'ok'],
            'visitorBasics'  => !$rowBasics ? null : $rowBasics,
            'statusValues'   => !$rowValues ? ['return' => 'no values'] : ['return' => 'ok'],
            'visitorsValues' => !$rowValues ? null : $rowValues,
            'vc' => $vc,
            'dateFormat' => $this->objPage->dateFormat
        ];

        return new JsonResponse($arrJson); 
    }

    protected function getBasics(int $vc): array|bool
    {
        $stmt = $this->db->prepare(
            "SELECT tl_visitors.id AS id,
                    visitors_name,
                    visitors_startdate,
                    visitors_visit_start,
                    visitors_hit_start,
                    visitors_average,
                    visitors_thousands_separator
                FROM tl_visitors 
                LEFT JOIN tl_visitors_category ON (tl_visitors_category.id = tl_visitors.pid)
                WHERE pid = :pid AND published = :published
                ORDER BY id
                LIMIT :limit"
        );
        $stmt->bindValue('pid', $vc, \PDO::PARAM_INT);
        $stmt->bindValue('published', 1, \PDO::PARAM_INT);
        $stmt->bindValue('limit', 1, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        $row = $resultSet->fetchAssociative();
        if (false === $row)
        {
            $this->monologLogger->logSystemLog('VisitorsFeAjaxController User Error: no published counter found.'
                                    ,'VisitorsFeAjaxController getBasics '
                                    , ContaoContext::ERROR);

            return false;
        }

        return $row;
    }

    protected function getValues(array|bool $rowBasics, int $vc): array|bool
    {
        if (false === $rowBasics)
        {
            return false;
        }

        $visitorsValues = $this->visitorCalculator->getVisitorValues($rowBasics, $vc, $this->objPage);

        // Filter for Ajax Request, nothing all is necessary
        unset($visitorsValues[0]['VisitorsName']);
        unset($visitorsValues[0]['VisitorsStartDate']);
        unset($visitorsValues[0]['VisitorsStartDateValue']);

        return $visitorsValues;
        

    }
}
