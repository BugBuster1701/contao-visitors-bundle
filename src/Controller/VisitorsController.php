<?php

/**
 * @copyright  Glen Langer 2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Visitors
 * @license    LGPL-3.0+
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\VisitorsBundle\Controller;

use BugBuster\Visitors\BackendVisitors;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the Visitors back end routes.
 *
 * @copyright  Glen Langer 2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 *
 * @Route("/visitors", defaults={"_scope" = "backend", "_token_check" = true})
 */
class VisitorsController extends AbstractController
{
    /**
     * Renders the alerts content.
     *
     * @return Response
     *
     * @Route("/details", name="visitors_backend_details")
     */
    public function detailsAction()
    {
        $this->get('contao.framework')->initialize();

        $controller = new BackendVisitors();

        return $controller->run();
    }
}
