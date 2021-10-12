<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Translation\FormType;

use PhpParser\Node;

/**
 * This class looks for a default translation domain declaration.
 *
 * It looks like this:
 *
 * ```php
 * public function configureOptions(OptionsResolver $resolver)
 * {
 *     $resolver->setDefaults([
 *         'translation_domain' => 'Admin.Shopparameters.Feature',
 *     ]);
 * }
 * ```
 */
class DefaultTranslationDomainExtractor
{
    public const CONFIGURE_OPTIONS = 'configureOptions';

    /**
     * Types of nodes that we are interested in inspecting
     */
    public const INTERESTING_NODE_TYPES = [
        Node\Stmt\ClassMethod::class,
        Node\Expr\MethodCall::class,
    ];

    /**
     * Name of the method that sets default settings
     */
    public const SET_DEFAULTS_DECLARATION_METHOD_NAME = 'setDefaults';

    /**
     * Index of the OptionsResolver parameter in the configureOptions method declaration
     */
    public const OPTIONS_RESOLVER_PARAM_INDEX = 0;

    /**
     * The extracted default translation domain
     *
     * @var string
     */
    private $defaultTranslationDomain = '';

    /**
     * Indicates if the default translation domain has been found
     *
     * @var bool
     */
    private $defaultTranslationDomainFound = false;

    /**
     * Indicates if we are currently analyzing nodes inside the configureOptions method declaration
     *
     * @var bool
     */
    private $insideConfigureOptions = false;

    /**
     * Name of the OptionsResolver parameter in the configureOptions method declaration
     *
     * @var string
     */
    private $optionsResolverName = '';

    /**
     * @return bool
     */
    public function lookForDefaultTranslationDomain(Node $node)
    {
        return
            $this->isAnInterestingNode($node)
            && !$this->defaultDomainHasBeenFound()
            && $this->process($node)
        ;
    }

    /**
     * @return bool
     */
    public function defaultDomainHasBeenFound()
    {
        return $this->defaultTranslationDomainFound;
    }

    /**
     * @return string
     */
    public function getDefaultTranslationDomain()
    {
        return $this->defaultTranslationDomain;
    }

    /**
     * @return bool
     */
    private function process(Node $node)
    {
        if ($this->isThisNodeInsideConfigureOptionsMethod($node)
            && $this->isDefaultsDeclaration($node)
        ) {
            $this->extractDefaultTranslationDomain($node);
        }

        return $this->defaultDomainHasBeenFound();
    }

    /**
     * Check if this node should be inspected
     *
     * @return bool
     */
    private function isAnInterestingNode(Node $node)
    {
        foreach (self::INTERESTING_NODE_TYPES as $nodeType) {
            if ($node instanceof $nodeType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isThisNodeInsideConfigureOptionsMethod(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            if ($this->nodeIsConfigurationOptionsMethod($node)) {
                $this->optionsResolverName = $this->getOptionsResolverName($node);
                // we are inside the configuration options method all right
                // but we won't acknowledge it if the options resolver parameter name cannot be found
                $this->insideConfigureOptions = !empty($this->optionsResolverName);
            } else {
                $this->insideConfigureOptions = false;
            }

            return false;
        }

        return $this->insideConfigureOptions;
    }

    /**
     * @return bool
     */
    private function nodeIsConfigurationOptionsMethod(Node\Stmt\ClassMethod $node)
    {
        /* @var $node->name Identifier */
        return $node->name->name === self::CONFIGURE_OPTIONS;
    }

    /**
     * Returns the name of the OptionsResolver parameter in the configureOptions method declaration.
     *
     * @return string
     */
    private function getOptionsResolverName(Node\Stmt\ClassMethod $classMethod)
    {
        if (isset($classMethod->params[self::OPTIONS_RESOLVER_PARAM_INDEX])) {
            $resolverParam = $classMethod->params[self::OPTIONS_RESOLVER_PARAM_INDEX];

            return $resolverParam->var->name;
        }

        return '';
    }

    /**
     * @return bool
     */
    private function isDefaultsDeclaration(Node $node)
    {
        return $node instanceof Node\Expr\MethodCall
            && $node->var instanceof Node\Expr\Variable
            && $node->var->name === $this->optionsResolverName
            // $node->name is an instance of Identifier
            && $node->name->name === self::SET_DEFAULTS_DECLARATION_METHOD_NAME
            && count($node->args) > 0
        ;
    }

    /**
     * @return bool
     */
    private function extractDefaultTranslationDomain(Node\Expr\MethodCall $node)
    {
        $defaults = $node->args[0];
        if ($defaults instanceof Node\Arg
            && $defaults->value instanceof Node\Expr\Array_
            && !empty($defaults->value->items)
        ) {
            foreach ($defaults->value->items as $item) {
                if ($item instanceof Node\Expr\ArrayItem
                   && $item->key instanceof Node\Scalar\String_
                   && $item->key->value === 'translation_domain'
                   && $item->value instanceof Node\Scalar\String_
               ) {
                    $this->setDefaultTranslationDomain($item->value->value);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $defaultTranslationDomain
     */
    private function setDefaultTranslationDomain($defaultTranslationDomain)
    {
        $this->defaultTranslationDomain = $defaultTranslationDomain;
        $this->defaultTranslationDomainFound = true;
    }
}
