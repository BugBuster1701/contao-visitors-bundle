<?php

/*
 * This file is part of a BugBuster Contao Bundle
 *
 * @copyright  Glen Langer 2019 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\BannerBundle\Tests;

use BugBuster\VisitorsBundle\BugBusterVisitorsBundle;
use PHPUnit\Framework\TestCase;

class BugBusterVisitorsBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new BugBusterVisitorsBundle();

        $this->assertInstanceOf('BugBuster\VisitorsBundle\BugBusterVisitorsBundle', $bundle);
    }
}
