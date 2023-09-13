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

use Contao\EasyCodingStandard\Fixer\MultiLineIfIndentationFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([__DIR__.'/../vendor/contao/easy-coding-standard/config/contao.php']);

    $ecsConfig->skip([
        BlankLineAfterOpeningTagFixer::class,
        DeclareStrictTypesFixer::class,
        LinebreakAfterOpeningTagFixer::class,
        MultiLineIfIndentationFixer::class,
        NoAlternativeSyntaxFixer::class,
        ReferenceUsedNamesOnlySniff::class,
        SemicolonAfterInstructionFixer::class,
        StatementIndentationFixer::class,
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        VisibilityRequiredFixer::class,
        VoidReturnFixer::class,
    ]);

    $ecsConfig->parallel();
    $ecsConfig->lineEnding("\n");
    $ecsConfig->fileExtensions(['html5']);
    $ecsConfig->cacheDirectory(sys_get_temp_dir().'/ecs_template_cache');
};
