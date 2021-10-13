<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__.'/DependencyInjection',
    __DIR__.'/Resources',
    __DIR__.'/Tests',
    __DIR__.'/Translation',
    __DIR__.'/Twig',
]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'error_suppression' => [
            'mute_deprecation_error' => false,
            'noise_remaining_usages' => false,
            'noise_remaining_usages_exclude' => [],
        ],
        'function_to_constant' => false,
        'no_alias_functions' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'protected_to_private' => false,
        'psr_autoloading' => false,
        'self_accessor' => false,
        'yoda_style' => [],
        'phpdoc_no_empty_return' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
;
