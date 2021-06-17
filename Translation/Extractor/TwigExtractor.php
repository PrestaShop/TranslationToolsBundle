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

use PrestaShop\TranslationToolsBundle\Twig\Extension\TranslationExtension;
use PrestaShop\TranslationToolsBundle\Twig\Lexer;
use Symfony\Bridge\Twig\Translation\TwigExtractor as BaseTwigExtractor;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Error\Error;
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
        $this->twig = $twig;
        $this->twigLexer = new Lexer($this->twig);

        $this->twig->registerUndefinedFunctionCallback(function () {});

        $this->twig->registerUndefinedFilterCallback(function () {});
    }

    public function getExcludedDirectories(): array
    {
        return $this->excludedDirectories;
    }

    /**
     * @return TwigExtractor
     */
    public function excludedDirectories(array $excludedDirectories): self
    {
        $this->excludedDirectories = $excludedDirectories;

        return $this;
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

            try {
                $this->extractTemplateFile($file, $catalogue);
            } catch (Error $e) {
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
     * {@inheritdoc}
     */
    protected function extractTemplateFile($file, MessageCatalogue $catalogue)
    {
        if (!$file instanceof \SplFileInfo) {
            $file = new \SplFileInfo($file);
        }

        $visitor = $this->twig->getExtension(TranslationExtension::class)->getTranslationNodeVisitor();
        $visitor->enable();

        $this->twig->setLexer($this->twigLexer);

        $tokens = $this->twig->tokenize(new Source(file_get_contents($file->getPathname()), $file->getFilename()));
        $this->twig->parse($tokens);

        $comments = $this->twigLexer->getComments();

        foreach ($visitor->getMessages() as $message) {
            $domain = $this->resolveDomain(isset($message[1]) ? $message[1] : null);

            $catalogue->set(
                $message[0],
                $this->prefix . trim($message[0]),
                $domain
            );

            $metadata = [
                'file' => $file->getRealpath(),
                'line' => $message['line'],
            ];

            $comment = $this->getEntryComment($comments, $file->getFilename(), ($message['line'] - 1));

            if (null != $comment) {
                $metadata['comment'] = $comment;
            }

            if (isset($message['line'])) {
                $metadata['comment'] = $this->getEntryComment($comments, $file->getFilename(), ($message['line'] - 1));
            }

            $catalogue->setMetadata($message[0], $metadata, $domain);
        }

        $visitor->disable();
    }

    /**
     * @param $comments
     * @param $file
     * @param $line
     *
     * @return array
     */
    public function getEntryComment($comments, $file, $line)
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
     *
     * @return Finder
     */
    protected function extractFromDirectory($directory)
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
