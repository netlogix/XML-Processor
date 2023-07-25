<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Hooks;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

final class BypassFinalHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
        BypassFinals::setWhitelist([
            '*/vendor/symfony/console/Helper/*',
        ]);
    }
}
