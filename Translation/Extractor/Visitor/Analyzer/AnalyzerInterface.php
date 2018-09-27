<?php

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Analyzer;

use PhpParser\Node;

interface AnalyzerInterface
{
    /**
     * @return array
     */
    public function getTranslations(Node $node);
}
