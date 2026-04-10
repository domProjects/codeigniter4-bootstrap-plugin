<?php

declare(strict_types=1);

/**
 * This file is part of domprojects/codeigniter4-bootstrap-plugin.
 *
 * (c) domProjects
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace domProjects\CodeIgniterBootstrapPlugin\Tests\Unit;

use Composer\Script\ScriptEvents;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\FakeComposer;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\FakeConfig;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\FakeEvent;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\FakeIO;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\FakePackage;
use domProjects\CodeIgniterBootstrapPlugin\Tests\Support\Composer\TestBootstrapPublishPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BootstrapPublishPluginTest extends TestCase
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private string $workspaceRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspaceRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'test-runtime';

        $this->deletePath($this->workspaceRoot);
        if (! is_dir($this->workspaceRoot)) {
            mkdir($this->workspaceRoot, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        $this->deletePath($this->workspaceRoot);

        parent::tearDown();
    }

    public function testSubscribedEventsAreRegistered(): void
    {
        $this->assertSame(
            [
                ScriptEvents::POST_INSTALL_CMD => 'publishBootstrapAssets',
                ScriptEvents::POST_UPDATE_CMD  => 'publishBootstrapAssets',
            ],
            TestBootstrapPublishPlugin::getSubscribedEvents(),
        );
    }

    public function testPublishReturnsSilentlyWhenSparkFileDoesNotExist(): void
    {
        $plugin = $this->createPlugin();

        $plugin->publishBootstrapAssets(new FakeEvent());

        $this->assertNull($plugin->getLastCommand());
        $this->assertSame([], $plugin->getIo()->writes);
        $this->assertSame([], $plugin->getIo()->errors);
    }

    public function testPublishCanBeDisabledThroughExtraConfiguration(): void
    {
        $plugin = $this->createPlugin([
            'domprojects-codeigniter4-bootstrap-plugin' => [
                'auto-publish' => false,
            ],
        ], true);

        $plugin->publishBootstrapAssets(new FakeEvent());

        $this->assertNull($plugin->getLastCommand());
        $this->assertSame(
            ['<info>domProjects Bootstrap Plugin:</info> automatic asset publishing disabled.'],
            $plugin->getIo()->writes,
        );
    }

    public function testPublishBuildsForceCommandByDefault(): void
    {
        $plugin = $this->createPlugin([], true);
        $plugin->setNextExecutionResult(0, "output line\n");

        $plugin->publishBootstrapAssets(new FakeEvent());

        $command = $plugin->getLastCommand();

        $this->assertNotNull($command);
        $this->assertStringContainsString('assets:publish-bootstrap --force', $command);
        $this->assertSame(
            [
                '<info>domProjects Bootstrap Plugin:</info> publishing Bootstrap assets...',
                'output line',
            ],
            $plugin->getIo()->writes,
        );
        $this->assertSame([], $plugin->getIo()->errors);
    }

    public function testPublishCanRunWithoutForceOption(): void
    {
        $plugin = $this->createPlugin([
            'domprojects-codeigniter4-bootstrap-plugin' => [
                'force' => false,
            ],
        ], true);

        $plugin->publishBootstrapAssets(new FakeEvent());

        $command = $plugin->getLastCommand();

        $this->assertNotNull($command);
        $this->assertStringContainsString('assets:publish-bootstrap', $command);
        $this->assertStringNotContainsString('--force', $command);
    }

    public function testPublishReportsFailures(): void
    {
        $plugin = $this->createPlugin([], true);
        $plugin->setNextExecutionResult(1, 'failure output');

        $plugin->publishBootstrapAssets(new FakeEvent());

        $this->assertSame(
            ['<warning>domProjects Bootstrap Plugin:</warning> automatic asset publishing failed.'],
            $plugin->getIo()->errors,
        );
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function createPlugin(array $extra = [], bool $withSpark = false): TestBootstrapPublishPlugin
    {
        $projectRoot = $this->workspaceRoot . DIRECTORY_SEPARATOR . 'project-' . bin2hex(random_bytes(4));
        $vendorDir   = $projectRoot . DIRECTORY_SEPARATOR . 'vendor';

        mkdir($vendorDir, 0775, true);

        if ($withSpark) {
            file_put_contents($projectRoot . DIRECTORY_SEPARATOR . 'spark', '#!/usr/bin/env php');
        }

        $composer = new FakeComposer(
            new FakeConfig(['vendor-dir' => $vendorDir]),
            new FakePackage($extra),
        );
        $io = new FakeIO();

        $plugin = new TestBootstrapPublishPlugin();
        $plugin->activate($composer, $io);
        $plugin->setIo($io);

        return $plugin;
    }

    private function deletePath(string $path): void
    {
        if (is_file($path) || is_link($path)) {
            @unlink($path);

            return;
        }

        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $this->deletePath($path . DIRECTORY_SEPARATOR . $item);
        }

        @rmdir($path);
    }
}
