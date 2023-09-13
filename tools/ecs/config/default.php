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

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
// use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/../vendor/contao/easy-coding-standard/config/contao.php']);

    $ecsConfig->skip([
        'src/Resources/contao/*',
        // MethodChainingIndentationFixer::class => [
        //     '*/DependencyInjection/Configuration.php',
        // ],
    ]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => "This file is part of a BugBuster Contao Bundle.\n\n@copyright  Glen Langer ".date('Y')." <http://contao.ninja>\n@author     Glen Langer (BugBuster)\n@package    Contao Visitors Bundle\n@link       https://github.com/BugBuster1701/contao-visitors-bundle\n\n@license    LGPL-3.0-or-later\nFor the full copyright and license information,\nplease view the LICENSE file that was distributed with this source code.",
    ]);

    $ecsConfig->parallel();
    $ecsConfig->lineEnding("\n");
    $ecsConfig->cacheDirectory(sys_get_temp_dir().'/ecs_default_cache');
};
