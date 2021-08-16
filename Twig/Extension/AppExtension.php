<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translation;

    /**
     * AppExtension constructor.
     */
    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    /**
     * We need to define and reset each twig function as the definition
     * of theses function is stored in PrestaShop codebase.
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('renderhooksarray', [$this, 'transChoice']),
        ];
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function transChoice($string)
    {
        return $this->translation->transChoice($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
