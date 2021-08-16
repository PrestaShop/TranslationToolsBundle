<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation;

class MultilanguageCatalog
{
    /** @var [] (key/locale) */
    private $messages = [];

    /**
     * @param string|int $key
     * @param string|int $locale
     *
     * @return bool
     */
    public function has($key, $locale = null)
    {
        return !empty($this->messages[$key]) && (is_null($locale) || !empty($this->messages[$key][$locale]));
    }

    /**
     * @param string|int $key
     * @param string|int $locale
     *
     * @return mixed
     */
    public function get($key, $locale = null)
    {
        if (is_null($locale)) {
            return $this->messages[$key];
        }

        return $this->messages[$key][$locale];
    }

    /**
     * @param string|int $key
     * @param string|int $locale
     * @param mixed $translation
     */
    public function set($key, $locale, $translation)
    {
        if (!isset($this->messages[$key])) {
            $this->messages[$key] = [];
        }

        $this->messages[$key][$locale] = $translation;

        return $this;
    }
}
