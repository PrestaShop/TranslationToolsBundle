<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Dumper;

use Locale;
use PrestaShop\TranslationToolsBundle\Configuration;
use PrestaShop\TranslationToolsBundle\Translation\Builder\XliffBuilder;
use PrestaShop\TranslationToolsBundle\Translation\Helper\DomainHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Dumper\XliffFileDumper as BaseXliffFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

class XliffFileDumper extends BaseXliffFileDumper
{
    protected $relativePathTemplate = '%locale%/%domain%.%extension%';

    /**
     * Gets the relative file path using the template.
     *
     * @param string $domain The domain
     * @param string $locale The locale
     *
     * @return string The relative file path
     */
    private function getRelativePath($domain, $locale)
    {
        return strtr($this->relativePathTemplate, [
            '%locale%' => $locale,
            '%domain%' => $domain,
            '%extension%' => $this->getExtension(),
        ]);
    }

    /**
     * Options array:
     *   'path' => Path where the XLIFF files are dumped,
     *   'root_dir' => Root folder for PrestaShop,
     *   'default_locale' => Default locale (en by default),
     *   'split_files' => Boolean, indicates of th XLIFF files must split the trans units into separate files nodes (default: true),
     *
     * @return void
     */
    public function dump(MessageCatalogue $messages, array $options = [])
    {
        if (!array_key_exists('path', $options)) {
            throw new \InvalidArgumentException('The file dumper needs a path option.');
        }

        $fs = new Filesystem();
        // save a file for each domain
        foreach ($messages->getDomains() as $domain) {
            $domainPath = DomainHelper::getExportPath($domain);
            $fullpath = sprintf('%s/%s', $options['path'], $this->getRelativePath($domainPath, $messages->getLocale()));
            $directory = dirname($fullpath);
            if (!file_exists($directory) && !@mkdir($directory, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s".', $directory));
            }

            $fs->dumpFile($fullpath, $this->formatCatalogue($messages, $domain, $options));
        }
    }

    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = []): string
    {
        if (array_key_exists('default_locale', $options)) {
            $defaultLocale = $options['default_locale'];
        } else {
            $defaultLocale = \Locale::getDefault();
        }

        if (array_key_exists('split_files', $options)) {
            $splitFiles = $options['split_files'];
        } else {
            $splitFiles = true;
        }

        $rootDir = !empty($options['root_dir']) ? realpath($options['root_dir']) : null;

        if ($splitFiles) {
            return $this->formatSplitFiles($messages, $domain, $defaultLocale, $rootDir);
        }

        return $this->formatSingleFile($messages, $domain, $defaultLocale, $rootDir);
    }

    /**
     * The historic format of PrestaShop XLIFF catalog splits the trans units messages into separate
     * files node representing the file where they were extracted from.
     */
    private function formatSplitFiles(MessageCatalogue $messages, string $domain, string $defaultLocale, ?string $rootDir): string
    {
        $xliffBuilder = new XliffBuilder();
        $xliffBuilder->setVersion('1.2');

        foreach ($messages->all($domain) as $source => $target) {
            if (!empty($source)) {
                $metadata = $this->getMetadata($messages, $source, $domain, $rootDir);
                $xliffBuilder->addFile($metadata['file'], $defaultLocale, $messages->getLocale());
                $xliffBuilder->addTransUnit($metadata['file'], $source, $target, $this->getNote($metadata));
            }
        }

        return html_entity_decode($xliffBuilder->build()->saveXML());
    }

    /**
     * The new PrestaShop format (starting from V9) uses a single file node not linked to any particular existing code file
     * (we use a common placeholder) that stores all the trans units, the units are also sorted alphabetically. The purpose
     * is to have some extracts that are less prone to changes because of code evolution.
     *
     * We also remove the note part that contained the line in the original file, since the file is not indicated anymore the
     * line is not relevant anymore. Besides just giving a file and a line did not give much context for the translators anyway.
     */
    private function formatSingleFile(MessageCatalogue $messages, string $domain, string $defaultLocale, ?string $rootDir): string
    {
        $xliffBuilder = new XliffBuilder();
        $xliffBuilder->setVersion('1.2');

        $transUnits = [];
        foreach ($messages->all($domain) as $source => $target) {
            if (!empty($source)) {
                $transUnits[$source] = $target;
            }
        }
        // Sort alphabetically based on the source key
        ksort($transUnits);

        $singleFileName = $domain . '.xlf';
        $xliffBuilder->addFile($singleFileName, $defaultLocale, $messages->getLocale());
        foreach ($transUnits as $source => $target) {
            $metadata = $this->getMetadata($messages, $source, $domain, $rootDir);
            if (!empty($metadata['file']) && !empty($metadata['line'])) {
                $note = sprintf('File: %s [Line: %s]', $metadata['file'], $metadata['line']);
            } else {
                $note = '';
            }

            $xliffBuilder->addTransUnit($singleFileName, $source, $target, $note);
        }

        return html_entity_decode($xliffBuilder->build()->saveXML());
    }

    private function getMetadata(MessageCatalogue $messages, string $source, string $domain, ?string $rootDir): array
    {
        $metadata = $messages->getMetadata($source, $domain);

        /*
         * Handle original file information from xliff file.
         * This is needed if at least part of the catalogue was read from xliff files
         */
        if (is_array($metadata['file']) && !empty($metadata['file']['original'])) {
            $metadata['file'] = $metadata['file']['original'];
        }

        $metadata['file'] = Configuration::getRelativePath(
            $metadata['file'],
            $rootDir
        );

        return $metadata;
    }

    /**
     * @param array $transMetadata
     *
     * @return string
     */
    private function getNote($transMetadata)
    {
        $notes = [];

        if (!empty($transMetadata['file'])) {
            if (isset($transMetadata['line'])) {
                $notes['line'] = 'Line: ' . $transMetadata['line'];
            }

            if (isset($transMetadata['comment'])) {
                $notes['comment'] = 'Comment: ' . $transMetadata['comment'];
            }
        }

        if (empty($notes) && isset($transMetadata['notes'][0]['content'])) {
            // use notes loaded from xliff file
            return $transMetadata['notes'][0]['content'];
        }

        return implode(PHP_EOL, $notes);
    }

    public function getExtension(): string
    {
        return 'xlf';
    }
}
