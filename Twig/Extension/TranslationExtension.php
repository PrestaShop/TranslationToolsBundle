<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Twig\Extension;

use PrestaShop\TranslationToolsBundle\Twig\NodeVisitor\TranslationNodeVisitor;
use Symfony\Bridge\Twig\Extension\TranslationExtension as BaseTranslationExtension;
use Symfony\Bridge\Twig\NodeVisitor\TranslationDefaultDomainNodeVisitor;
use Twig\Extension\AbstractExtension;

class TranslationExtension extends AbstractExtension
{
    /**
     * @var BaseTranslationExtension
     */
    protected $baseTranslationExtension;

    /**
     * @var TranslationNodeVisitor
     */
    protected $nodeVisitors;

    public function __construct()
    {
        $this->baseTranslationExtension = new BaseTranslationExtension();
        $this->nodeVisitors = new TranslationNodeVisitor();
    }

    public function getTokenParsers()
    {
        return $this->baseTranslationExtension->getTokenParsers();
    }

    public function getNodeVisitors()
    {
        return [$this->nodeVisitors, new TranslationDefaultDomainNodeVisitor()];
    }

    public function getTranslationNodeVisitor()
    {
        return $this->nodeVisitors;
    }

    public function getFilters()
    {
        return $this->baseTranslationExtension->getFilters();
    }

    public function getTests()
    {
        return $this->baseTranslationExtension->getFilters();
    }

    public function getFunctions()
    {
        return $this->baseTranslationExtension->getFunctions();
    }

    public function getOperators()
    {
        return $this->baseTranslationExtension->getOperators();
    }
}
