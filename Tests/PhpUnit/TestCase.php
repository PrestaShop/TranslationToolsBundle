<?php

namespace PrestaShop\TranslationToolsBundle\Tests\PhpUnit;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Translation\MessageCatalogue;

class TestCase extends PhpUnitTestCase
{
    /**
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getInaccessibleProperty($object, $property)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $result = $reflectionProperty->getValue($object);
        $reflectionProperty->setAccessible(false);

        return $result;
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @return TestCase
     */
    protected function setInaccessibleProperty($object, $property, $value)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        $reflectionProperty->setAccessible(false);

        return $this;
    }

    /**
     * @param object $object
     * @param string $method
     * @param mixed $args
     *
     * @return mixed
     */
    protected function invokeInaccessibleMethod($object, $method, $args = [])
    {
        $reflectionMethod = new ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($object, $args);
        $reflectionMethod->setAccessible(false);

        return $result;
    }

    protected function getResource(string $resourceName): string
    {
        return realpath(__DIR__ . '/../resources/' . $resourceName);
    }

    protected function verifyCatalogue(MessageCatalogue $messageCatalogue, array $expected): void
    {
        $domains = $messageCatalogue->getDomains();

        foreach ($expected as $expectedDomain => $expectedStrings) {
            // the domain should be defined
            $this->assertContains(
                $expectedDomain,
                $domains,
                sprintf('Domain "%s" is not defined in %s', $expectedDomain, print_r($domains, true))
            );

            // all strings should be defined in the appropriate domain
            foreach ($expectedStrings as $string) {
                $this->assertTrue(
                    $messageCatalogue->defines($string, $expectedDomain),
                    sprintf('"%s" not found in %s', $string, $expectedDomain)
                );
            }
        }

        // Now check that everything found was defined in expected
        foreach ($domains as $catalogDomain) {
            $this->assertContains(
                $catalogDomain,
                array_keys($expected),
                sprintf('Found domain "%s" is not defined in expected domains %s', $catalogDomain, print_r(array_keys($expected), true))
            );

            $domainMessages = $messageCatalogue->all($catalogDomain);
            $expectedDomainMessages = $expected[$catalogDomain];
            foreach ($domainMessages as $domainMessage) {
                $this->assertContains(
                    $domainMessage,
                    $expectedDomainMessages,
                    sprintf('Found message "%s" is not defined in expected domain %s', $domainMessage, $catalogDomain)
                );
            }
        }
    }
}
