<?php

/**
 * 2007-2016 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\TranslationToolsBundle\Command;

use ZipArchive;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use PrestaShop\TranslationToolsBundle\Configuration;
use AppBundle\PrestaShop\LocaleMapper;

class GzipCommand extends BaseCommand
{
    protected $mapper;

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        return class_exists('AppBundle\PrestaShop\LocaleMapper');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:gzip')
            ->setDescription('Create legacy lang pack for PrestaShop 1.6')
            ->addOption(
                'package',
                null,
                InputOption::VALUE_OPTIONAL,
                'Language code or "all" to download a bundle with translations to all languages.', 'all'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->mapper = new LocaleMapper(
            $this->getContainer()->get('kernel')->locateResource('@AppBundle/Resources').'/mapping.json'
        );
        $this->dumpDir = $this->getContainer()->getParameter('translation.dir_dump');

        $packageFileList = $this->getPackageFileList($this->dumpDir.'/legacyDownload/');
        $this->createLegacyPacks(
            $packageFileList, $this->dumpDir.'/legacyPacks/'
        );
    }

    /**
     * @param string $filesDirectory
     *
     * @return array
     */
    protected function getPackageFileList($filesDirectory)
    {
        $packages = [];
        $finder = new Finder();
        $files = $finder->files()->in($filesDirectory);

        foreach ($files as $file) {
            preg_match('/([a-z]{2}-[A-Z]{2})/', $file->getRealpath(), $matches);

            if (!isset($packages[$matches[1]])) {
                $packages[$matches[1]] = [];
            }

            $packages[$matches[1]][] = $file;
        }

        return $packages;
    }

    /**
     * @param array  $packageFileList
     * @param string $destination
     */
    protected function createLegacyPacks(array $packageFileList, $destination)
    {
        $tempDir = Configuration::getCacheDir().'legacy/';
        $finder = new Finder();

        // an is a technical lang, no pack should be generated for it
        unset($packageFileList['an-ES']);
        // Pirate english should be removed from crowdin
        unset($packageFileList['en-PT']);
        // Can't find how to map them
        unset($packageFileList['es-HN']);
        unset($packageFileList['es-VE']);
        unset($packageFileList['is-IS']);
        unset($packageFileList['km-KH']);
        unset($packageFileList['kn-IN']);
        unset($packageFileList['ku-TR']);
        unset($packageFileList['me-ME']);
        unset($packageFileList['mg-MG']);
        unset($packageFileList['mn-MN']);
        unset($packageFileList['ne-NP']);
        unset($packageFileList['or-IN']);
        unset($packageFileList['sw-TZ']);
        unset($packageFileList['tt-RU']);

        $this->mapPackageFilesToLegacyIso($packageFileList, $tempDir);
        $this->copyEmails($this->dumpDir.'/legacyEmails/', $tempDir);
        $this->removeEmptyTranslations($tempDir);

        $renamedPackageFileList = $finder->depth('== 0')->directories()->in($tempDir);

        foreach ($renamedPackageFileList as $folder) {
            $iso = $folder->getFilename();
            $this->createLegacyPackArchive($iso, $folder->getRealPath().'/', $destination);
        }
    }

    protected function mapPackageFilesToLegacyIso($packageFileList, $tempDir)
    {
        $fs = new Filesystem();
        $fs->remove($tempDir);
        $fs->mkdir($tempDir);

        foreach ($packageFileList as $locale => $files) {
            $iso = $this->mapper->getLegacyIso($locale);
            $fs->mkdir($tempDir.$iso);

            foreach ($files as $file) {
                if (preg_match('/install-dev/', $file->getRelativePath())) {
                    continue;
                }

                $filename = preg_replace('/'.$locale.'/', $iso, $file->getFilename());
                $destDir = $tempDir.$iso.'/'.preg_replace('/'.$locale.'/', $iso, $file->getRelativePath()).'/';
                $fs->mkdir($destDir);
                $fs->copy(
                    $file->getRealpath(),
                    $destDir.$filename
                );
            }
        }
    }

    protected function copyEmails($emailDir, $tempDir)
    {
        $fs = new Filesystem();

        $folders = (new Finder())->depth('== 0')->directories()->in($emailDir);
        $isoList = (new Finder())->depth('== 0')->directories()->in($tempDir);

        foreach ($folders as $folder) {
            foreach ($isoList as $isoFolder) {
                $iso = $isoFolder->getFilename();
                $parent_iso = $this->mapper->getParentLegacyIso($iso);

                if (preg_match('/mails/', $folder->getBasename())) {
                    $fs->mkdir($tempDir . $iso . '/mails/' . $iso);
                    $source = $folder->getRealPath() . '/' . $iso;

                    if (!is_dir($source)) {
                        if ($parent_iso) {
                            $source = $folder->getRealPath() . '/' . $parent_iso;
                        }
                    }

                    $fs->mirror($source, $tempDir . $iso . '/mails/' . $iso);
                } else {
                    $destDir = $tempDir . $iso . '/modules/' . $folder->getBasename() . '/mails/' . $iso;
                    $fs->mkdir($destDir);
                    $source = $folder->getRealPath() . '/mails/' . $iso;

                    if (!is_dir($source)) {
                        if ($parent_iso) {
                            $source = $folder->getRealPath() . '/mails/' . $parent_iso;
                        }
                    }

                    $fs->mirror($source, $destDir);
                }
            }
        }
    }

    private function removeEmptyTranslations($tempDir)
    {
        $mapping = [
            '_LANGMAIL' => 'lang.php',
            '_MODULE' => '/[a-z]{2}\.php/',
            '_LANGADM' => 'admin.php',
            '_ERRORS' => 'errors.php',
            '_FIELDS' => 'fields.php',
            '_LANGPDF' => 'pdf.php',
            '_TABS' => 'tabs.php',
        ];

        foreach ($mapping as $varName => $pattern) {
            $finder = Finder::create();
            $adminFiles = $finder->files()->in($tempDir)->name($pattern);
            foreach ($adminFiles as $file) {
                $$varName = [];
                require $file->getPathname();
                foreach ($$varName as $key => $value) {
                    if ('' === $value) {
                        unset($$varName[$key]);
                    }
                }
                $this->writeTranslationFile($file->getPathname(), $varName, $$varName);
                unset($$varName);
            }
        }
    }

    private function writeTranslationFile($filename, $varName, $data)
    {
        $arrayStr = "<?php\n\nglobal \$$varName;\n\$$varName = array();\n\n";

        foreach ($data as $key => $value) {
            $arrayStr .= "\$$varName"."['$key'] = '".str_replace("'", "\'", $value)."';\n";
        }

        $arrayStr .= "\n\nreturn \$$varName;\n";

        file_put_contents($filename, $arrayStr);
    }

    private function createLegacyPackArchive($iso, $folder, $destination)
    {
        exec("tar -czf $destination$iso.gzip --directory=\"$folder\" .");
    }
}
