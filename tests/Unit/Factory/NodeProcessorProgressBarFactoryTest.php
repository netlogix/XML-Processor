<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit;

use Netlogix\XmlProcessor\Factory\NodeProcessorProgressBarFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NodeProcessorProgressBarFactoryTest extends TestCase
{
    function test__construct(): void
    {
        $nodeProcessorProgressBarFactory = new NodeProcessorProgressBarFactoryTest();
        self::assertInstanceOf(NodeProcessorProgressBarFactoryTest::class, $nodeProcessorProgressBarFactory);
    }

    function testCreateProgressBar(): void
    {
        $nodeProcessorProgressBarFactory = new NodeProcessorProgressBarFactory();
        $progressBar = $nodeProcessorProgressBarFactory->createProgressBar();
        self::assertNull($progressBar);

        $progressBar = $nodeProcessorProgressBarFactory->createProgressBar($this->getMockBuilder(OutputInterface::class)->getMock());
        self::assertNull($progressBar);

        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $output->method('getErrorOutput')->willReturn($output);
        $output->method('isDecorated')->willReturn(true);

        $progressBar = $nodeProcessorProgressBarFactory->createProgressBar($output);
        self::assertInstanceOf(ProgressBar::class, $progressBar);
    }
}