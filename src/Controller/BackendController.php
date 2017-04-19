<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\BackendVisitors;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao backend routes.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @Route("/contao", defaults={"_scope" = "backend", "_token_check" = true})
 */
class BackendController extends Controller
{
    /**
     * Renders the alerts content.
     *
     * @return Response
     *
     * @Route("/visitordetails", name="contao_backend_visitordetails")
     */
    public function visitordetailsAction()
    {
        $this->container->get('contao.framework')->initialize();

        $controller = new BackendVisitors();

        return $controller->run();
    }
}
