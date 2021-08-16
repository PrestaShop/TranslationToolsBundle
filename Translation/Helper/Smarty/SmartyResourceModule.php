<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Helper\Smarty;

/**
 * Stub to support "module" as a resource in smarty files
 */
class SmartyResourceModule extends \Smarty_Resource_Custom
{
    /**
     * Fetch a template.
     *
     * @param string $name template name
     * @param string $source template source
     * @param int $mtime template modification timestamp (epoch)
     */
    protected function fetch($name, &$source, &$mtime)
    {
        return;
    }
}
