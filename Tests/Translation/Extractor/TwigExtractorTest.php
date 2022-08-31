<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\TwigExtractor;
use PrestaShop\TranslationToolsBundle\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

class TwigExtractorTest extends TestCase
{
    /**
     * @throws Error
     */
    public function testExtractWithDomain(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue([
            'payment_return.html.twig',
            'log_alert.html.twig',
        ]);

        $expected = [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.',
                'Please send us a bank wire with:',
                'Please specify your order reference %s in the bankwire description.',
                'We\'ve also sent you this information by e-mail.',
                'Your order will be sent as soon as we receive payment.',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
                'We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].',
            ],
            'Emails.Body' => [
                'Hi {firstname} {lastname},',
                'You have received a new log alert',
                '[1]Warning:[/1] you have received a new log alert in your Back Office.',
                'You can check for it in the [1]Advanced Parameters > Logs[/1] section of your back office.',
            ],
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);

        $fixtureDirectory = parent::getResource('fixtures/twig');
        $expectedMetadata = [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 12,
                    'comment' => null,
                ],
                'Please send us a bank wire with:' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 13,
                    'comment' => 'here is a comment',
                ],
                'Please specify your order reference %s in the bankwire description.' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 18,
                    'comment' => null,
                ],
                'We\'ve also sent you this information by e-mail.' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 19,
                    'comment' => null,
                ],
                'Your order will be sent as soon as we receive payment.' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 21,
                    'comment' => null,
                ],
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 23,
                    'comment' => null,
                ],
                'We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' => [
                    'file' => $fixtureDirectory . '/payment_return.html.twig',
                    'line' => 27,
                    'comment' => null,
                ],
            ],
            'Emails.Body' => [
                'Hi {firstname} {lastname},' => [
                    'file' => $fixtureDirectory . '/log_alert.html.twig',
                    'line' => 7,
                    'comment' => null,
                ],
                'You have received a new log alert' => [
                    'file' => $fixtureDirectory . '/log_alert.html.twig',
                    'line' => 24,
                    'comment' => null,
                ],
                '[1]Warning:[/1] you have received a new log alert in your Back Office.' => [
                    'file' => $fixtureDirectory . '/log_alert.html.twig',
                    'line' => 29,
                    'comment' => null,
                ],
                'You can check for it in the [1]Advanced Parameters > Logs[/1] section of your back office.' => [
                    'file' => $fixtureDirectory . '/log_alert.html.twig',
                    'line' => 30,
                    'comment' => null,
                ],
            ],
        ];

        $this->verifyCatalogueMetadata($messageCatalogue, $expectedMetadata);
    }

    /**
     * @throws Error
     */
    private function buildMessageCatalogue(array $fixtureResources): MessageCatalogue
    {
        $messageCatalogue = new MessageCatalogue('en');
        $extractor = $this->buildExtractor();
        foreach ($fixtureResources as $fixtureResource) {
            $extractor->extract($this->getResource($fixtureResource), $messageCatalogue);
        }

        return $messageCatalogue;
    }

    private function buildExtractor(): TwigExtractor
    {
        $loader = new FilesystemLoader(parent::getResource('fixtures/twig'));
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../cache/twig',
        ]);
        $twig->addExtension(new TranslationExtension());

        return new TwigExtractor($twig);
    }

    /**
     * @param string $resourceName
     */
    protected function getResource($resourceName): string
    {
        return parent::getResource('fixtures/twig/' . $resourceName);
    }

    /**
     * @param array[] $expected
     */
    protected function verifyCatalogueMetadata(MessageCatalogue $messageCatalogue, array $expected)
    {
        foreach ($expected as $expectedDomain => $expectedDomainMetadata) {
            // all strings should be defined in the appropriate domain
            foreach ($expectedDomainMetadata as $message => $metadata) {
                $this->assertSame(
                    $messageCatalogue->getMetadata($message, $expectedDomain),
                    $metadata
                );
            }
        }
    }
}
