<?php

namespace PrestaShop\TranslationToolsBundle\Tests\PhpUnit;

use ReflectionProperty;
use ReflectionMethod;
use PHPUnit_Framework_TestCase as PhpUnitTestCase;
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
     * @param mixed  $value
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
     * @param mixed  $args
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

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getResource($resourceName)
    {
        return realpath(__DIR__.'/../resources/'.$resourceName);
    }

    /**
     * @param $messageCatalogue
     * @param array[] $expected
     */
    protected function verifyCatalogue(MessageCatalogue $messageCatalogue, $expected)
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
    }
}
