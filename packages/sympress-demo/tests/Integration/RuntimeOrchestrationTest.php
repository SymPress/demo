<?php

declare(strict_types=1);

namespace SymPress\Demo\Tests\Integration;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;
use SymPress\Runtime\Application\ContainerFactory;
use SymPress\Runtime\Application\RunContext;
use SymPress\Runtime\Config\Config;
use SymPress\Runtime\Config\Validator;
use SymPress\Runtime\Console\Io;
use SymPress\Runtime\Filesystem\Paths;
use SymPress\Runtime\Services;
use SymPress\Runtime\Step\Registry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[BackupGlobals(true)]
final class RuntimeOrchestrationTest extends TestCase
{
    public function testExistingInstallationIsCheckedWithoutReseedingOrOverwritingItsHomepage(): void
    {
        self::assertSame(['wp db check'], $this->commands(true, true));
    }

    public function testMissingDatabaseConfigurationDoesNotTriggerInstallation(): void
    {
        self::assertSame(['wp --version'], $this->commands(false, false));
    }

    public function testFreshInstallationCreatesDatabaseAndSeedsOnlyAfterCoreInstall(): void
    {
        $commands = $this->commands(true, false);

        self::assertSame('wp db create', $commands[0]);
        self::assertStringStartsWith('wp core install ', $commands[1]);
        self::assertStringContainsString(
            '--admin_password=' . escapeshellarg("test password' with spaces"),
            $commands[1],
        );
        self::assertContains('wp plugin activate sympress-demo', $commands);
        self::assertContains('wp sympress-demo:create-notes --set=quotes --count=18 --reset', $commands);
        self::assertStringStartsWith('wp eval ', $commands[array_key_last($commands)]);
    }

    public function testMissingOrSharedPasswordGeneratesDifferentSecretsWithoutOutput(): void
    {
        $commands = [];
        ob_start();

        try {
            $commands[] = $this->commands(true, false, 'admin')[1];
            $commands[] = $this->commands(true, false, '')[1];
            self::assertSame('', ob_get_contents());
        } finally {
            ob_end_clean();
        }

        self::assertNotSame($commands[0], $commands[1]);
        foreach ($commands as $command) {
            self::assertMatchesRegularExpression("/--admin_password='[a-f0-9]{48}'/", $command);
            self::assertStringContainsString('--admin_email=' . escapeshellarg('admin@example.invalid'), $command);
            self::assertStringContainsString('--skip-email', $command);
        }
    }

    /** @return list<string> */
    private function commands(bool $valid, bool $installed, string $password = "test password' with spaces"): array
    {
        $root = dirname(__DIR__, 4);
        $paths = new Paths($root);
        $config = new Config(['compatibility' => false, 'compatibility-profile' => 'native'], new Validator($paths));
        $services = (new ContainerFactory())->create(
            $config,
            $paths,
            new Io(new ArrayInput([]), new BufferedOutput()),
            new RunContext($root, $paths->vendor(), $paths->bin()),
            new Registry(),
        )->get(Services::class);
        self::assertInstanceOf(Services::class, $services);
        $values = [
            'WPDB_ENV_VALID'    => $valid ? '1' : '0',
            'WPDB_EXISTS'       => $installed ? '1' : '0',
            'WP_INSTALLED'      => $installed ? '1' : '0',
            'WP_HOME'           => 'https://runtime-test.invalid',
            'WP_ADMIN_PASSWORD' => $password,
        ];
        $previous = [];
        foreach ($values as $name => $value) {
            $previous[$name] = getenv($name);
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Test the native process-environment boundary and restore it below.
            putenv($name . '=' . $value);
        }

        try {
            return require $root . '/dev-ops/orchestrate.php';
        } finally {
            foreach ($previous as $name => $value) {
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Restore the caller's environment even when the provider fails.
                putenv($value === false ? $name : $name . '=' . $value);
            }
        }
    }
}
