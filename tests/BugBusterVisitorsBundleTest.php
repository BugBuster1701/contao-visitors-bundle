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

namespace BugBuster\VisitorsBundle\Tests;

use BugBuster\VisitorsBundle\BugBusterVisitorsBundle;
use PHPUnit\Framework\TestCase;

class BugBusterVisitorsBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new BugBusterVisitorsBundle();

        $this->assertInstanceOf('BugBuster\VisitorsBundle\BugBusterVisitorsBundle', $bundle);
    }
}
