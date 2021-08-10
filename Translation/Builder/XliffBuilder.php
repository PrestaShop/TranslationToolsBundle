<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Builder;

use DOMDocument;

class XliffBuilder
{
    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $originalFiles = [];

    /**
     * @var array
     */
    protected $transUnits = [];

    public function __construct()
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
    }

    /**
     * @return DOMDocument
     */
    public function build()
    {
        $xliff = $this->dom->appendChild($this->dom->createElement('xliff'));
        $xliff->setAttribute('version', $this->version);
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:' . $this->version);

        ksort($this->originalFiles);

        foreach ($this->originalFiles as $key => $file) {
            $body = $file->appendChild($this->dom->createElement('body'));

            foreach ($this->transUnits[$key] as $transUnit) {
                $body->appendChild($transUnit);
            }

            $xliff->appendChild($file);
        }

        return $this->dom;
    }

    /**
     * @param string $filename
     * @param string $sourceLanguage
     * @param string $targetLanguage
     *
     * @return \PrestaShop\TranslationToolsBundle\Translation\Builder\XliffBuilder
     */
    public function addFile($filename, $sourceLanguage, $targetLanguage)
    {
        if (!isset($this->originalFiles[$filename])) {
            $xliffFile = $this->dom->createElement('file');
            $xliffFile->setAttribute('original', $filename);
            $xliffFile->setAttribute('source-language', $sourceLanguage);
            $xliffFile->setAttribute('target-language', $targetLanguage);
            $xliffFile->setAttribute('datatype', 'plaintext');

            $this->originalFiles[$filename] = $xliffFile;
        }

        return $this;
    }

    /**
     * @param string $filename
     * @param string $source
     * @param string $target
     * @param string $note
     *
     * @return \PrestaShop\TranslationToolsBundle\Translation\Builder\XliffBuilder
     */
    public function addTransUnit($filename, $source, $target, $note)
    {
        $id = md5($source);
        $translation = $this->dom->createElement('trans-unit');
        $translation->setAttribute('id', $id);

        // Does the target contain characters requiring a CDATA section?
        $source_value = 1 === preg_match('/[&<>]/', $source) ? $this->dom->createCDATASection($source) : $this->dom->createTextNode($source);
        $target_value = 1 === preg_match('/[&<>]/', $target) ? $this->dom->createCDATASection($target) : $this->dom->createTextNode($target);
        $note_value = 1 === preg_match('/[&<>]/', $note) ? $this->dom->createCDATASection($note) : $this->dom->createTextNode($note);

        $s = $translation->appendChild($this->dom->createElement('source'));
        $s->appendChild($source_value);

        // Skip metadata
        $z = $translation->appendChild($this->dom->createElement('target'));
        $z->appendChild($target_value);

        $n = $translation->appendChild($this->dom->createElement('note'));
        $n->appendChild($note_value);

        $this->transUnits[$filename][$id] = $translation;

        return $this;
    }

    /**
     * @param string $version
     *
     * @return \PrestaShop\TranslationToolsBundle\Translation\Builder\XliffBuilder
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
