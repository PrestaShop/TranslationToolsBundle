<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty\TranslationTemplateCompiler;
use PrestaShop\TranslationToolsBundle\Translation\Helper\DomainHelper;
use SplFileInfo;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class SmartyExtractor extends AbstractFileExtractor implements ExtractorInterface
{
    use TraitExtractor;

    public const INCLUDE_EXTERNAL_MODULES = true;
    public const EXCLUDE_EXTERNAL_MODULES = false;

    /**
     * @var TranslationTemplateCompiler
     */
    private $smartyCompiler;
    private $prefix;

    /**
     * @var bool
     */
    private $includeExternalWordings;

    /**
     * @param bool $includeExternalWordings Set to SmartyCompiler::INCLUDE_EXTERNAL_MODULES to include wordings signed with 'mod' (external modules)
     */
    public function __construct(
        TranslationTemplateCompiler $smartyCompiler,
        $includeExternalWordings = self::EXCLUDE_EXTERNAL_MODULES
    ) {
        $this->smartyCompiler = $smartyCompiler;
        $this->includeExternalWordings = $includeExternalWordings;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        $files = $this->extractFiles($resource);
        foreach ($files as $file) {
            if (!$this->canBeExtracted($file->getRealpath())) {
                continue;
            }

            $this->extractFromFile($file, $catalogue);
        }
    }

    protected function extractFromFile(SplFileInfo $resource, MessageCatalogue $catalogue)
    {
        $compiler = $this->smartyCompiler->setTemplateFile($resource->getPathname());
        $translationTags = $compiler->getTranslationTags();

        foreach ($translationTags as $translation) {
            $extractedDomain = null;

            // skip "old styled" external translations
            if (isset($translation['tag']['mod'])) {
                if (!$this->includeExternalWordings) {
                    continue;
                }

                // domain
                $extractedDomain = DomainHelper::buildModuleDomainFromLegacySource(
                    $translation['tag']['mod'],
                    $resource->getBasename()
                );
            } elseif (isset($translation['tag']['d'])) {
                $extractedDomain = $translation['tag']['d'];
            }

            $domain = $this->resolveDomain($extractedDomain);
            $string = stripslashes($translation['tag']['s']);

            $catalogue->set($this->prefix . $string, $string, $domain);
            $catalogue->setMetadata(
                $this->prefix . $string,
                [
                    'line' => $translation['line'],
                    'file' => $translation['template'],
                ],
                $domain
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && 'tpl' === pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractFromDirectory($directory)
    {
        return $this->getFinder()
            ->name('*.tpl')
            ->in($directory)
            ->exclude($this->getExcludedDirectories());
    }
}
