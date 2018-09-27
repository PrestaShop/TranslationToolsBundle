<?php

/**
 * 2007-2016 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Analyzer\AnalyzerInterface;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Analyzer\ArrayTranslationDefinition;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Analyzer\ExplicitTranslationCall;

class TranslationNodeVisitor extends NodeVisitorAbstract
{
    protected $file;

    /**
     * @var array
     */
    protected $translations = [];

    /**
     * @var array
     */
    protected $comments = [];

    /**
     * @var AnalyzerInterface[]
     */
    private $analyzers = [];

    /**
     * TranslationNodeVisitor constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;

        $this->analyzers = [
            new ExplicitTranslationCall(),
            new ArrayTranslationDefinition()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        $this->tryExtractComments($node);

        foreach ($this->analyzers as $analyzer) {
            $translations = $analyzer->getTranslations($node);
            if (!empty($translations)) {
                $this->translations = array_merge($this->translations, $translations);
                // first analyzer to find translations wins
                return;
            }
        }
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Node $node
     */
    private function tryExtractComments(Node $node)
    {
        $comments = $node->getAttribute('comments');

        if (is_array($comments)) {
            foreach ($comments as $comment) {
                $this->comments[] = [
                    'line'    => $comment->getLine(),
                    'file'    => $this->file,
                    'comment' => trim($comment->getText(), " \t\n\r\0\x0B/*"),
                ];
            }
        }
    }
}
