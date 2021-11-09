<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor\Visitor;

use PhpParser\Comment;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\CommentsNodeVisitor;

class TranslationNodeVisitorTest extends TestCase
{
    public function testGetComments(): void
    {
        $translationNodeVisitor = new CommentsNodeVisitor('LegacyController.php');
        $methodCall = $this->getMockBuilder('PhpParser\Node\Expr\MethodCall')->disableOriginalConstructor()->getMock();

        $methodCall
            ->method('getAttribute')
            ->willReturn([
                new Comment('//@yolo', 10),
                new Comment('//@todo', 14),
            ]);

        $translationNodeVisitor->leaveNode($methodCall);
        $this->assertEquals([
            [
                'line' => 10,
                'comment' => '@yolo',
                'file' => 'LegacyController.php',
            ],
            [
                'line' => 14,
                'comment' => '@todo',
                'file' => 'LegacyController.php',
            ],
        ], $translationNodeVisitor->getComments());
    }
}
