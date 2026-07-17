<?php

namespace PrestaShop\TranslationToolsBundle\Tests\resources\fixtures;

use Symfony\Contracts\Translation\TranslatorInterface;

class Test84NewWithoutParenthesesController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function indexAction(): string
    {
        // PHP 8.4 "new without parentheses": only php-parser v5 can tokenize this.
        // The extractor must not crash while scanning this file, and must still
        // pick up the translation call below.
        $formatted = new \DateTime()->format('Y-m-d');

        return $this->trans('Eight point four', [], 'Admin.Product.Help');
    }
}
