<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Netlogix\XmlProcessor\Behat\NodeProcessor\InvokeNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\XmlProcessor;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private ?XmlProcessor $xmlProcessor = NULL;

    /**
     * @Given initialize XMLProcessor with :nodeProcessorClass
     */
    public function iInitializeXmlProcessor(string $nodeProcessorClass): void
    {
        if (!class_exists($nodeProcessorClass)) {
            throw new \Exception(sprintf('Class %s does not exist', $nodeProcessorClass));
        }
        if (!is_subclass_of($nodeProcessorClass, NodeProcessorInterface::class)) {
            throw new \Exception(sprintf('Class %s does not extend %s', $nodeProcessorClass, NodeProcessorInterface::class));
        }
        $this->xmlProcessor = new XmlProcessor([
            new $nodeProcessorClass()
        ]);
    }

    /**
     * @When process xml with current XMLProcessor instance:
     */
    public function iRunXmlProcessor(PyStringNode $content): void
    {
        $fileName = tempnam(sys_get_temp_dir(), 'xmlprocessor');
        file_put_contents($fileName, $content->getRaw());
        $this->xmlProcessor->processFile($fileName);
    }

    /**
     * @Then NodeProcessor :nodeProcessorClass should return:
     */
    function nodeProcessorShouldReturn(string $nodeProcessorClass, PyStringNode $content): void
    {
        if (!class_exists($nodeProcessorClass)) {
            throw new \Exception(sprintf('Class %s does not exist', $nodeProcessorClass));
        }
        if (!is_subclass_of($nodeProcessorClass, NodeProcessorInterface::class)) {
            throw new \Exception(sprintf('Class %s does not extend %s', $nodeProcessorClass, NodeProcessorInterface::class));
        }
        /** @var InvokeNodeProcessorInterface $nodeProcessor */
        $nodeProcessor = $this->xmlProcessor->getProcessorContext()->getProcessor($nodeProcessorClass);
        $expected = json_decode($content->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(sprintf('Could not decode expected result: %s', json_last_error_msg()));
        }
        $actual = call_user_func($nodeProcessor);
        \PHPUnit\Framework\assertEquals($expected, $actual,
            "Expected: " . json_encode($expected, JSON_PRETTY_PRINT) . ", got:" . json_encode($actual, JSON_PRETTY_PRINT)
        );
    }
}
