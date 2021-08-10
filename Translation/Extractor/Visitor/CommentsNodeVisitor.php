<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Extracts comment information
 */
class CommentsNodeVisitor extends NodeVisitorAbstract
{
    protected $file;

    /**
     * @var array
     */
    protected $comments = [];

    /**
     * TranslationNodeVisitor constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        $this->tryExtractComments($node);
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    private function tryExtractComments(Node $node)
    {
        $comments = $node->getAttribute('comments');

        if (is_array($comments)) {
            foreach ($comments as $comment) {
                $this->comments[] = [
                    'line' => $comment->getLine(),
                    'file' => $this->file,
                    'comment' => trim($comment->getText(), " \t\n\r\0\x0B/*"),
                ];
            }
        }
    }
}
