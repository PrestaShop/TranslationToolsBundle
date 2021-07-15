<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\TwigExtractor;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigExtractorTest extends TestCase
{
    /**
     * @var TwigExtractor
     */
    protected $twigExtractor;
    private $resourcesDir;

    public function setUp(): void
    {
        $this->resourcesDir = $this->getResource('directory/twig/');
        $templatesLoader = new FilesystemLoader($this->resourcesDir);
        $twig = new Environment($templatesLoader);
        $twig->addExtension(new TranslationExtension());
        $this->twigExtractor = new TwigExtractor($twig);
    }

    public function testExtractFromDirectory()
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->twigExtractor->extract($this->resourcesDir, $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(2, array_keys($catalogue));

        $this->verifyCatalogue($messageCatalogue, [
            'Test.Translation.FirstSubDir' => [
                'Translate me please' => 'Translate me please',
            ],
            'Test.Translation.SecondSubDir' => [
                'string to translate' => 'string to translate',
            ],
        ]);

        $messageCatalogue = new MessageCatalogue('en');
        $this->twigExtractor
            ->excludedDirectories(['subdirectory'])
            ->extract($this->resourcesDir, $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(1, array_keys($catalogue));

        $this->verifyCatalogue($messageCatalogue, [
            'Test.Translation.SecondSubDir' => [
                'string to translate' => 'string to translate',
            ],
        ]);

        $messageCatalogue = new MessageCatalogue('en');
        $this->twigExtractor
            ->excludedDirectories(['subdirectory', 'subdirectory2'])
            ->extract($this->resourcesDir, $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertEmpty($catalogue);
    }
}
