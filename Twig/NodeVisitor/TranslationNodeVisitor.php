<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Twig\NodeVisitor;

use Symfony\Bridge\Twig\NodeVisitor\TranslationNodeVisitor as BaseTranslationNodeVisitor;
use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

class TranslationNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var BaseTranslationNodeVisitor
     */
    private $baseTranslationNodeVisitor;

    private $messages = [];

    public function __construct()
    {
        $this->baseTranslationNodeVisitor = new BaseTranslationNodeVisitor();
    }

    public function enable()
    {
        $this->messages = [];
        $this->baseTranslationNodeVisitor->enable();
    }

    public function disable()
    {
        $this->messages = [];
        $this->baseTranslationNodeVisitor->disable();
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function doEnterNode(Node $node, Environment $env)
    {
        return $this->baseTranslationNodeVisitor->enterNode($node, $env);
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Node $node, Environment $env): ?Node
    {
        $node = $this->baseTranslationNodeVisitor->leaveNode($node, $env);

        $messages = $this->baseTranslationNodeVisitor->getMessages();

        if (count($messages) > count($this->messages)) {
            $this->messages[] = array_merge(end($messages), ['line' => $node->getTemplateLine()]);
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->baseTranslationNodeVisitor->getPriority();
    }
}
