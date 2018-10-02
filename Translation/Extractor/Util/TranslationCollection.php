<?php

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Util;

class TranslationCollection
{

    private $translations = [];

    /**
     * @param array $translations
     */
    public function add($translations)
    {
        if (!empty($translations)) {
            $this->translations = array_merge($this->translations, $translations);
        }
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

}
