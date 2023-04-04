<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\Behat\NodeProcessor;

use Netlogix\XmlProcessor\Behat\NodeProcessor\ArrayNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class ArrayNodeProcessorTest extends TestCase
{

    function test__invoke(): void
    {
        $nodeProcessor = new ArrayNodeProcessor();
        self::assertIsArray($nodeProcessor());
    }

    function testGetSubscribedEvents(): void
    {
        $nodeProcessor = new ArrayNodeProcessor();
        $context = $this->getMockBuilder(XmlProcessorContext::class)->disableOriginalConstructor()->getMock();
        self::assertIsIterable($nodeProcessor->getSubscribedEvents('test', $context));
        $events = iterator_to_array($nodeProcessor->getSubscribedEvents('test', $context));
        self::assertEquals([
            'NodeType_' . \XMLReader::ELEMENT => [$nodeProcessor, 'openElement'],
            'NodeType_' . \XMLReader::TEXT => [$nodeProcessor, 'textElement']
        ], $events);
    }


    function testOpenElement(): void
    {
        $data = [
            [
                'nodePath' => ['foo'],
                'attributes' => ['id' => '1'],
            ],
            [
                'nodePath' => ['foo', 'bar'],
                'attributes' => ['name' => 'me'],
                'text' => 'test'
            ],
            [
                'nodePath' => ['foo', 'bar'],
                'attributes' => ['name' => 'you'],
                'text' => 'test2'
            ],
            [
                'nodePath' => ['foo'],
                'attributes' => ['id' => '2'],
            ],
        ];

        $nodeProcessor = new ArrayNodeProcessor();
        $xmlProcessorContext = $this->getMockBuilder(XmlProcessorContext::class)->disableOriginalConstructor()->getMock();

        foreach ($data as $item) {
            $context = new OpenContext($xmlProcessorContext, $item['nodePath']);
            $context->setAttributes($item['attributes']);
            $nodeProcessor->openElement($context);
            if (isset($item['text'])) {
                $textContext = new TextContext($xmlProcessorContext, $item['nodePath']);
                $textContext->setText($item['text']);
                $nodeProcessor->textElement($textContext);
            }
        }

        self::assertEquals([
            [
                'node' => 'foo',
                'level' => 1,
                'attributes' => ['id' => '1'],
                'children' => [
                    [
                        'node' => 'bar',
                        'level' => 2,
                        'attributes' => ['name' => 'me'],
                        'children' => [],
                        'text' => 'test'
                    ],
                    [
                        'node' => 'bar',
                        'level' => 2,
                        'attributes' => ['name' => 'you'],
                        'children' => [],
                        'text' => 'test2'
                    ],
                ],

            ],
            [
                'node' => 'foo',
                'level' => 1,
                'attributes' => ['id' => '2'],
                'children' => [],
            ],
        ], $nodeProcessor());
    }

}
