<?php

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use Symfony\Component\Translation\Extractor\ExtractorInterface as BaseExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

interface ExtractorInterface extends BaseExtractorInterface
{
    /**
     * Extracts translation messages from files, a file or a directory to the catalogue.
     *
     * @param string|array $resource Files, a file or a directory
     * @param MessageCatalogue $catalogue The catalogue
     */
    public function extract($resource, MessageCatalogue $catalogue, array $excludedResources = []);
}
