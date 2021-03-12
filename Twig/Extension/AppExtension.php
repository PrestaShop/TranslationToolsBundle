<?php

namespace PrestaShop\TranslationToolsBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;
use Twig_Extension_InitRuntimeInterface;

class AppExtension extends \Twig_Extension implements Twig_Extension_InitRuntimeInterface
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

    public function initRuntime(Twig_Environment $environment)
    {
        $environment->registerUndefinedFunctionCallback(function () {
            return;
        });

        $environment->registerUndefinedFilterCallback(function () {
            return;
        });
    }

    /**
     * We need to define and reset each twig function as the definition
     * of theses function is stored in PrestaShop codebase.
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('renderhooksarray', [$this, 'transChoice']),
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
