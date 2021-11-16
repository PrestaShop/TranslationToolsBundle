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

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\TranslationCollection;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\CommentsNodeVisitor;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Translation\ArrayTranslationDefinition;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Translation\ExplicitTranslationCall;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Visitor\Translation\FormType\FormTypeDeclaration;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class PhpExtractor extends AbstractFileExtractor implements ExtractorInterface
{
    use TraitExtractor;

    /**
     * @var array
     */
    protected $visitors = [];

    /**
     * Prefix for new found message.
     *
     * @var string
     */
    private $prefix = '';

    /**
     * @var \PhpParser\Parser\Multiple
     */
    private $parser;

    public function __construct()
    {
        $lexer = new Lexer(
            [
                'usedAttributes' => [
                    'comments',
                    'startLine',
                    'endLine',
                    'startTokenPos',
                    'endTokenPos',
                ],
            ]
        );

        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalogue)
    {
        $files = $this->extractFiles($resource);

        foreach ($files as $file) {
            $this->parseFileTokens($file, $catalogue);
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
     * @param $file
     *
     * @throws \Exception
     */
    protected function parseFileTokens($file, MessageCatalogue $catalog)
    {
        $code = file_get_contents($file);

        $translationCollection = new TranslationCollection();
        $commentsNodeVisitor = new CommentsNodeVisitor($file->getFilename());

        $translationVisitors = [
            new ArrayTranslationDefinition($translationCollection),
            new ExplicitTranslationCall($translationCollection),
            new FormTypeDeclaration($translationCollection),
        ];

        $traverser = new NodeTraverser();
        $traverser->addVisitor($commentsNodeVisitor);
        foreach ($translationVisitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        try {
            $stmts = $this->parser->parse($code);
            $traverser->traverse($stmts);

            $comments = $commentsNodeVisitor->getComments();

            foreach ($translationCollection->getTranslations() as $translation) {
                $translation['domain'] = empty($translation['domain'])
                    ? $this->resolveDomain(null)
                    : $translation['domain'];

                $comment = $metadata['comment'] = $this->getEntryComment(
                    $comments,
                    $file->getFilename(),
                    ($translation['line'] - 1)
                );

                $catalog->set(
                    $translation['source'],
                    $this->prefix . trim($translation['source']),
                    $translation['domain']
                );

                $catalog->setMetadata(
                    $translation['source'],
                    [
                        'line' => $translation['line'],
                        'file' => $file->getRealPath(),
                        'comment' => $comment,
                    ],
                    $translation['domain']
                );
            }
        } catch (\PhpParser\Error $e) {
            throw new \Exception(sprintf('Could not parse tokens in "%s" file. Is it syntactically valid?', $file), $e->getCode(), $e);
        }
    }

    /**
     * @param string $file
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && 'php' === pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * @param string|array $directory
     *
     * @return array
     */
    protected function extractFromDirectory($directory)
    {
        return $this->getFinder()
            ->files()
            ->name('*.php')
            ->exclude($this->getExcludedDirectories())
            ->in($directory);
    }
}
