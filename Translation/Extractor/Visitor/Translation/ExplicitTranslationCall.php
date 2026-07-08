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

use PhpParser\Node;

/**
 * Looks up for a translation call using l(), trans() or t()
 */
class ExplicitTranslationCall extends AbstractTranslationNodeVisitor
{
    public const SUPPORTED_METHODS = ['l', 'trans', 't'];

    public function leaveNode(Node $node)
    {
        $this->translations->add($this->extractFrom($node));
    }

    public function extractFrom(Node $node)
    {
        if (!$this->appliesFor($node)) {
            return [];
        }

        /** @var Node\Expr\MethodCall|Node\Expr\FuncCall $node */
        $nodeName = $this->getNodeName($node);
        if (!in_array($nodeName, self::SUPPORTED_METHODS)) {
            return [];
        }

        // Sometimes the arg is a PhpParser\Node\VariadicPlaceholder, we ignore such cases
        if (!$node->args[0] instanceof Node\Arg) {
            return [];
        }

        $key = $this->getValue($node->args[0]);
        if (empty($key)) {
            return [];
        }

        $translation = [
            'source' => $key,
            'line' => $node->args[0]->getLine(),
        ];

        if ($nodeName == 'trans') {
            // First line is Symfony Style, second is Prestashop FrameworkBundle Style
            if (count($node->args) > 2 && $node->args[2]->value instanceof Node\Scalar\String_) {
                $translation['domain'] = $node->args[2]->value->value;
            } elseif (count($node->args) > 1 && $node->args[1]->value instanceof Node\Scalar\String_) {
                $translation['domain'] = $node->args[1]->value->value;
            }
        } elseif ($nodeName == 't') {
            $translation['domain'] = 'Emails.Body';
        }

        return [$translation];
    }

    /**
     * @return bool
     */
    private function appliesFor(Node $node)
    {
        if (empty($node->args)) {
            return false;
        }

        return
            ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\FuncCall)
            && ($node->name instanceof Node\Identifier || $node->name instanceof Node\Name)
        ;
    }

    /**
     * @return string|null
     */
    private function getValue(Node\Arg $arg)
    {
        if ($arg->value instanceof Node\Scalar\String_) {
            return $arg->value->value;
        } elseif (gettype($arg) === 'string') {
            return $arg->value;
        }
    }

    /**
     * @param Node|Node\Expr\MethodCall|Node\Expr\FuncCall $node
     */
    private function getNodeName(Node $node)
    {
        if ($node->name instanceof Node\Name) {
            // $node->name is an instance of Identifier
            return $node->name->parts[0];
        }

        return $node->name->name;
    }
}
