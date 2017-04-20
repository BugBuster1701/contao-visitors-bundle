<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace BugBuster\VisitorsBundle\Controller;

use BugBuster\Visitors\FrontendVisitors;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao backend routes.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @Route("/visitors", defaults={"_scope" = "frontend", "_token_check" = false})
 */
class VisitorsFeController extends Controller
{
    /**
     * Renders the alerts content.
     *
     * @return Response
     *
     * @Route("/screencount", name="visitors_frontend_screencount")
     */
    public function screencountAction()
    {
        $this->container->get('contao.framework')->initialize();
    
        $controller = new FrontendVisitors();
    
        return $controller->run();
    }
}
