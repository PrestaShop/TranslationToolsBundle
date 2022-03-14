<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty;

use Smarty_Internal_SmartyTemplateCompiler;
use Smarty_Internal_Template;
use Smarty_Internal_Templateparser;
use SmartyCompilerException;
use SmartyException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class TranslationTemplateCompiler extends Smarty_Internal_SmartyTemplateCompiler
{
    /**
     * @var bool
     */
    public $nocache = false;

    /**
     * @var bool
     */
    public $tag_nocache = false;

    /**
     * @var bool
     */
    private $abort_and_recompile = false;

    /**
     * @var string
     */
    private $templateFile;

    /**
     * @return array
     */
    public function getTranslationTags()
    {
        $this->init();
        $tagFound = [];
        $comment = [];

        // get tokens from lexer and parse them
        while ($this->lex->yylex() && !$this->abort_and_recompile) {
            try {
                if ('extends' == $this->lex->value) {
                    $this->lex->value = 'dummy';
                }

                $this->parser->doParse($this->lex->token, $this->lex->value);
                if ($this->lex->token === Smarty_Internal_Templateparser::TP_TEXT) {
                    $comment = [
                        'line' => $this->lex->line,
                        'value' => $this->lex->value,
                    ];
                }
            } catch (SmartyCompilerException $e) {
                if (($tag = $this->explodeLTag($this->parser->yystack))) {
                    $tagFound[] = $this->getTag($tag, $e, $comment);
                }

                $this->parser->yy_accept();
            } catch (SmartyException $e) {
            }
        }

        return $tagFound;
    }

    /**
     * @param string $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        if (!file_exists($templateFile)) {
            throw new FileNotFoundException(null, 0, null, $templateFile);
        }

        $this->templateFile = $templateFile;

        return $this;
    }

    private function init()
    {
        /* here is where the compiling takes place. Smarty
          tags in the templates are replaces with PHP code,
          then written to compiled files. */
        // init the lexer/parser to compile the template
        $this->parent_compiler = $this;
        $this->template = new Smarty_Internal_Template($this->templateFile, $this->smarty);
        $this->lex = new $this->lexer_class(file_get_contents($this->templateFile), $this);
        $this->parser = new $this->parser_class($this->lex, $this);

        if (((int) ini_get('mbstring.func_overload')) & 2) {
            mb_internal_encoding('ASCII');
        }
    }

    /**
     * @param string $string
     * @param int|null $token
     *
     * @return string
     */
    private function naturalize($string, $token = null)
    {
        switch ($token) {
            case Smarty_Internal_Templateparser::TP_TEXT:
                return trim($string, " \t\n\r\0\x0B{*}");
            default:
                return substr($string, 1, -1);
        }
    }

    /**
     * @return array|null
     */
    private function explodeLTag(array $tagStack)
    {
        $tag = null;

        foreach ($tagStack as $entry) {
            if ($entry->minor === 'l') {
                $tag = [];
            }

            if (is_array($tag) && is_array($entry->minor)) {
                foreach ($entry->minor as $minor) {
                    foreach ($minor as $attr => $val) {
                        // Skip on variables
                        if (0 === strpos($val, '$') && 's' === $attr) {
                            return;
                        }

                        $tag[$attr] = $this->naturalize($val);
                    }
                }
            }
        }

        return $tag;
    }

    /**
     * @return array
     */
    private function getTag(array $value, SmartyCompilerException $exception, array $previousComment)
    {
        $tag = [
            'tag' => $value,
            'line' => $exception->getLine(),
            'template' => $exception->template,
        ];

        if (!empty($previousComment) && $previousComment['line'] == $tag['line'] - 1) {
            $tag['comment'] = $this->naturalize($previousComment['value'], Smarty_Internal_Templateparser::TP_TEXT);
        }

        return $tag;
    }
}
