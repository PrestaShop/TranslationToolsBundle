<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor\Visitor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\CommentsNodeVisitor;
use PhpParser\Comment;

class TranslationNodeVisitorTest extends TestCase
{
    public function testGetComments()
    {
        $translationNodeVisitor = new CommentsNodeVisitor('LegacyController.php');
        $methodCall = $this->getMockBuilder('PhpParser\Node\Expr\MethodCall')->disableOriginalConstructor()->getMock();

        $methodCall
            ->method('getAttribute')
            ->willReturn([
                new Comment('//@yolo', '10'),
                new Comment('//@todo', '14'),
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
