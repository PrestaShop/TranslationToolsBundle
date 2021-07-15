<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Twig\Lexer;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Translation\TwigExtractor as BaseTwigExtractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Source;

class TwigExtractor extends BaseTwigExtractor implements ExtractorInterface
{
    use TraitExtractor;

    /**
     * Prefix for found message.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * The twig environment.
     *
     * @var Environment
     */
    private $twig;

    /**
     * @var Lexer
     */
    private $twigLexer;

    /**
     * @var array
     */
    private $excludedDirectories = [];

    /**
     * The twig environment.
     *
     * @var Environment
     */
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);

        $this->twig = $twig;
        $this->twigLexer = new Lexer($this->twig);
    }

    public function getExcludedDirectories(): array
    {
        return $this->excludedDirectories;
    }

    public function excludedDirectories(array $excludedDirectories): self
    {
        $this->excludedDirectories = $excludedDirectories;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     */
    public function extract($resource, MessageCatalogue $catalogue): void
    {
        $files = $this->extractFiles($resource);
        foreach ($files as $file) {
            if (!$this->canBeExtracted($file->getRealpath())) {
                continue;
            }

            try {
                $this->extractTemplateFile($file, $catalogue);
            } catch (SyntaxError $e) {
                if ($file instanceof SplFileInfo) {
                    $e->setSourceContext(new Source(
                        $e->getSourceContext()->getCode(),
                        $e->getSourceContext()->getName(),
                        $file->getRelativePathname()
                    ));
                } elseif ($file instanceof \SplFileInfo) {
                    $e->setSourceContext(new Source(
                        $e->getSourceContext()->getCode(),
                        $e->getSourceContext()->getName(),
                        $file->getRealPath()
                    ));
                }

                throw $e;
            }
        }
    }

    /**
     * @param $file
     *
     * @throws SyntaxError
     */
    protected function extractTemplateFile($file, MessageCatalogue $catalogue): void
    {
        if (!$file instanceof \SplFileInfo) {
            $file = new \SplFileInfo($file);
        }

        $visitor = $this->twig->getExtension(TranslationExtension::class)->getTranslationNodeVisitor();
        $visitor->enable();

        $this->twig->setLexer(new Lexer($this->twig));

        $tokens = $this->twig->tokenize(new Source(file_get_contents($file->getPathname()), $file->getFilename()));
        $this->twig->parse($tokens);

        $comments = $this->twigLexer->getComments();

        foreach ($visitor->getMessages() as $message) {
            $domain = $this->resolveDomain($message[1] ?? null);

            $catalogue->set(
                $message[0],
                $this->prefix . trim($message[0]),
                $domain
            );

            $line = $tokens->getCurrent()->getLine();
            $metadata = [
                'file' => $file->getRealpath(),
                'line' => $line,
            ];

            $comment = $this->getEntryComment($comments, $file->getFilename(), ($line - 1));

            if (null != $comment) {
                $metadata['comment'] = $comment;
            }

            if (isset($line)) {
                $metadata['comment'] = $this->getEntryComment($comments, $file->getFilename(), ($line - 1));
            }

            $catalogue->setMetadata($message[0], $metadata, $domain);
        }

        $visitor->disable();
    }

    /**
     * @param $comments
     * @param $file
     * @param $line
     */
    public function getEntryComment($comments, $file, $line): ?array
    {
        foreach ($comments as $comment) {
            if ($comment['file'] == $file && $comment['line'] == $line) {
                return $comment['comment'];
            }
        }

        return null;
    }

    /**
     * @param string $directory
     */
    protected function extractFromDirectory($directory): Finder
    {
        return $this->getFinder()->files()
            ->name('*.twig')
            ->in($directory)
            ->exclude($this->getExcludedDirectories());
    }

    /**
     * @param string|iterable $resource Files, a file or a directory
     *
     * @return iterable
     */
    protected function extractFiles($resource)
    {
        if (\is_array($resource) || $resource instanceof \Traversable) {
            $files = [];
            foreach ($resource as $file) {
                if ($this->canBeExtracted($file)) {
                    $files[] = $this->toSplFileInfo($file);
                }
            }
        } elseif (is_file($resource)) {
            $files = $this->canBeExtracted($resource) ? [$this->toSplFileInfo($resource)] : [];
        } else {
            $files = $this->extractFromDirectory($resource);
        }

        return $files;
    }

    private function toSplFileInfo(string $file): \SplFileInfo
    {
        return new \SplFileInfo($file);
    }
}
