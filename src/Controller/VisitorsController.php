<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace BugBuster\VisitorsBundle\Controller;

use BugBuster\Visitors\BackendVisitors;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the Visitors back end routes.
 */
#[Route('/visitors', defaults: ['_scope' => 'backend', '_token_check' => true])]
class VisitorsController extends AbstractController
{
    /**
     * Renders the alerts content.
     *
     * @return Response
     *
     */
    #[Route('/details', name: 'visitors_backend_details')]
    public function detailsAction()
    {
        $this->container->get('contao.framework')->initialize();

        $controller = new BackendVisitors();

        return $controller->run();
    }
}
