<?php

declare(strict_types=1);

/**
 * Plugin Name: SymPress Demo Error Reporting
 * Description: Keeps local development output readable while preserving debug mode.
 */

namespace SymPress\Demo\BaseMuPlugins\Debug;

set_error_handler(static function (int $errno): bool {
    return ErrorReportingManager::isIgnoredDeprecation($errno);
});

final class ErrorReportingManager
{
    private const array BYPASS_REQUEST_TYPES = [
        'XMLRPC_REQUEST',
        'REST_REQUEST',
        'MS_FILES_REQUEST',
        'WP_INSTALLING',
        'DOING_AJAX',
    ];

    private const string JSON_CONTENT_TYPE_PATTERN = '/(^|\s|,)application\/([\w!#$&\-\^.]+\+)?json(\+oembed)?($|\s|;|,)/i';

    public static function initialize(): void
    {
        self::setupEarlyErrorReporting();
        self::registerDebugModeHook();
    }

    public static function handleDebugMode(): false
    {
        if (self::shouldBypassDisplay()) {
            ini_set('display_errors', '0');

            return false;
        }

        if (self::isDebugEnabled()) {
            self::configureDebugMode();

            return false;
        }

        error_reporting(self::getProductionErrorLevel());

        return false;
    }

    public static function isIgnoredDeprecation(int $errno): bool
    {
        return ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) && !self::shouldDisplayDeprecated();
    }

    private static function setupEarlyErrorReporting(): void
    {
        if (!self::isDebugEnabled()) {
            return;
        }

        error_reporting(self::getErrorReportingLevel());
    }

    private static function registerDebugModeHook(): void
    {
        if (!isset($GLOBALS['wp_filter']['enable_wp_debug_mode_checks'][10])) {
            $GLOBALS['wp_filter']['enable_wp_debug_mode_checks'][10] = [];
        }

        $GLOBALS['wp_filter']['enable_wp_debug_mode_checks'][10][] = [
            'accepted_args' => 0,
            'function'      => [self::class, 'handleDebugMode'],
        ];
    }

    private static function shouldBypassDisplay(): bool
    {
        return self::isSpecialRequestType() || self::isJsonRequest();
    }

    private static function isSpecialRequestType(): bool
    {
        foreach (self::BYPASS_REQUEST_TYPES as $constant) {
            if (defined($constant) && constant($constant)) {
                return true;
            }
        }

        return false;
    }

    private static function isJsonRequest(): bool
    {
        return self::hasJsonContentType('HTTP_ACCEPT')
            || self::hasJsonContentType('CONTENT_TYPE');
    }

    private static function hasJsonContentType(string $header): bool
    {
        $value = filter_input(INPUT_SERVER, $header, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($value === null || $value === false) {
            return false;
        }

        return preg_match(self::JSON_CONTENT_TYPE_PATTERN, $value) === 1;
    }

    private static function configureDebugMode(): void
    {
        error_reporting(self::getErrorReportingLevel());
        self::configureDisplayErrors();
        self::configureErrorLog();
    }

    private static function configureDisplayErrors(): void
    {
        if (!defined('WP_DEBUG_DISPLAY')) {
            return;
        }

        ini_set('display_errors', WP_DEBUG_DISPLAY ? '1' : '0');
    }

    private static function configureErrorLog(): void
    {
        $logPath = self::getErrorLogPath();

        if ($logPath === null) {
            return;
        }

        ini_set('log_errors', '1');
        ini_set('error_log', $logPath);
    }

    private static function getErrorLogPath(): ?string
    {
        if (!defined('WP_DEBUG_LOG')) {
            return null;
        }

        $debugLog = constant('WP_DEBUG_LOG');

        if (is_string($debugLog)) {
            return $debugLog;
        }

        if (in_array(strtolower((string) $debugLog), ['true', '1'], true)) {
            return WP_CONTENT_DIR . '/debug.log';
        }

        return null;
    }

    private static function isDebugEnabled(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    private static function getErrorReportingLevel(): int
    {
        if (self::shouldDisplayDeprecated()) {
            return E_ALL;
        }

        return E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED;
    }

    private static function getProductionErrorLevel(): int
    {
        return E_CORE_ERROR
            | E_CORE_WARNING
            | E_COMPILE_ERROR
            | E_ERROR
            | E_WARNING
            | E_PARSE
            | E_USER_ERROR
            | E_USER_WARNING
            | E_RECOVERABLE_ERROR;
    }

    private static function shouldDisplayDeprecated(): bool
    {
        return filter_var(
            getenv('SYMPRESS_DEMO_DISPLAY_DEPRECATED'),
            FILTER_VALIDATE_BOOLEAN,
        );
    }
}

ErrorReportingManager::initialize();
