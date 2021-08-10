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

use PrestaShop\TranslationToolsBundle\Translation\Builder\PhpBuilder;
use PrestaShop\TranslationToolsBundle\Translation\Helper\LegacyHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;

class PhpDumper extends FileDumper
{
    /**
     * @var PhpBuilder[]
     */
    private $builders = [];

    /**
     * {@inheritdoc}
     */
    public function dump(MessageCatalogue $messages, $options = [])
    {
        if (!array_key_exists('path', $options)) {
            throw new \InvalidArgumentException('The file dumper needs a path option.');
        }

        if (array_key_exists('default_locale', $options)) {
            $defaultLocale = $options['default_locale'];
        } else {
            $defaultLocale = $messages->getLocale();
        }

        // Add/update all Php builders (1/file)
        foreach ($messages->getDomains() as $domain) {
            $this->formatCatalogue($messages, $domain);
        }

        // Create files
        foreach ($this->builders as $filename => $builder) {
            $fullpath = $options['path'] . '/' . $filename;
            $directory = dirname($fullpath);

            if (!file_exists($directory) && !@mkdir($directory, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s".', $directory));
            }

            $fs = new Filesystem();
            $fs->dumpFile($fullpath, $builder->build());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        foreach ($messages->all($domain) as $source => $target) {
            $metadata = $messages->getMetadata($source, $domain);

            // Skip if output info can't be guessed
            if (!($outputInfo = LegacyHelper::getOutputInfo($metadata['file']))) {
                continue;
            }

            $outputFile = str_replace('[locale]', $messages->getLocale(), $outputInfo['file']);

            if (!isset($this->builders[$outputFile])) {
                $this->builders[$outputFile] = new PhpBuilder();
                $this->builders[$outputFile]->appendGlobalDeclaration($outputInfo['var']);
            }

            $this->builders[$outputFile]->appendStringLine(
                $outputInfo['var'],
                $outputInfo['generateKey']($target),
                $target
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return 'php';
    }
}
