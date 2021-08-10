<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Translation;

use PhpParser\NodeVisitorAbstract;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\TranslationCollection;

abstract class AbstractTranslationNodeVisitor extends NodeVisitorAbstract implements TranslationVisitorInterface
{
    /**
     * @var TranslationCollection
     */
    protected $translations;

    public function __construct(TranslationCollection $collection)
    {
        $this->translations = $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslationCollection()
    {
        return $this->translations;
    }
}
