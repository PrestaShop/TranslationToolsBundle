<?php

namespace PrestaShop\TranslationToolsBundle\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

class AppExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translation;

    /**
     * AppExtension constructor.
     *
     * @param TranslatorInterface $translation
     */
    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('renderhook', array($this, 'emptyFunction')),
            new \Twig_SimpleFilter('renderhooksarray', array($this, 'emptyFunction')),
            new \Twig_SimpleFilter('configuration', array($this, 'emptyFunction')),
            new \Twig_SimpleFilter('intCast', array($this, 'emptyFunction')),
            new \Twig_SimpleFilter('arrayCast', array($this, 'emptyFunction')),
        );
    }

    /**
     * We need to define and reset each twig function as the definition
     * of theses function is stored in PrestaShop codebase.
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('renderhook', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('renderhooksarray', array($this, 'transChoice')),
            new \Twig_SimpleFunction('trans', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('transChoice', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('transchoice', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('getLegacyLayout', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('template_from_string', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('hookcount', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('getAdminLink', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('youtube_link', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('getTranslationsForms', array($this, 'emptyFunction')),
            new \Twig_SimpleFunction('getTranslationsTree', array($this, 'emptyFunction')),
        );
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

    public function emptyFunction()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app';
    }
}
