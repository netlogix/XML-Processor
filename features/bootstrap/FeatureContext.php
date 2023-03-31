<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\Behat\NodeProcessor\TestNodeProcessor;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{

    private XmlProcessor $xmlProcessor;

    private TestNodeProcessor $testNodeProcessor;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->testNodeProcessor = new TestNodeProcessor();
        $this->xmlProcessor = new XmlProcessor(
            [$this->testNodeProcessor]
        );
    }

    /**
     * @When I run XMLProcessor with
     */
    public function iRunXmlProcessor(PyStringNode $content)
    {
        $fileName = tempnam(sys_get_temp_dir(), 'xmlprocessor');
        file_put_contents($fileName, $content->getRaw());
        $this->xmlProcessor->processFile($fileName);
    }


}
