<?php

namespace Modified\Storefront\Template\Smarty\Legacy;

use Throwable;

final class LegacyApiDeprecationReporter
{
    private const RATE_LIMIT_SECONDS = 86400;
    private const RATE_LIMIT_DIRECTORY = 'cache/smarty-legacy-api-notices';

    /** @var array<string, true> */
    private static array $reportedInThisRequest = [];

    public function report(string $legacyApi, string $replacement): void
    {
        try {
            $logger = $this->logger();
            if ($logger === null) {
                return;
            }

            $caller = $this->caller();
            if ($caller === null) {
                return;
            }

            [$callerFile, $callerLine] = $caller;
            if ($this->isIntentionalInternalUse($legacyApi, $callerFile)) {
                return;
            }

            $identity = hash('sha256', $callerFile . "\0" . $legacyApi);
            if (isset(self::$reportedInThisRequest[$identity])) {
                return;
            }
            self::$reportedInThisRequest[$identity] = true;

            $message = $this->message($legacyApi, $replacement, $callerFile, $callerLine);
            if (defined('SMARTY_LEGACY_API_NOTICE_RATE_LIMIT') && SMARTY_LEGACY_API_NOTICE_RATE_LIMIT === false) {
                $logger->notice($message);
                return;
            }

            $this->reportWithinRateLimit($logger, $identity, $message);
        } catch (Throwable) {
            // A migration hint must never affect the legacy operation or the shop response.
        }
    }

    private function logger(): ?object
    {
        $logger = $GLOBALS['LoggingManager'] ?? null;

        return is_object($logger) && is_callable([$logger, 'notice']) ? $logger : null;
    }

    /** @return null|array{string, int} */
    private function caller(): ?array
    {
        if (!defined('DIR_FS_CATALOG') || !is_string(DIR_FS_CATALOG) || DIR_FS_CATALOG === '') {
            return null;
        }

        $catalogRoot = rtrim(str_replace('\\', '/', realpath(DIR_FS_CATALOG) ?: DIR_FS_CATALOG), '/') . '/';
        $compatibilityClass = dirname(__DIR__, 4) . '/Smarty.php';
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8) as $frame) {
            $file = $frame['file'] ?? null;
            if (!is_string($file) || $file === __FILE__ || $file === $compatibilityClass) {
                continue;
            }

            $normalizedFile = str_replace('\\', '/', realpath($file) ?: $file);
            if (str_starts_with($normalizedFile, $catalogRoot)) {
                $normalizedFile = substr($normalizedFile, strlen($catalogRoot));
            }

            return [$normalizedFile, (int)($frame['line'] ?? 0)];
        }

        return null;
    }

    private function reportWithinRateLimit(object $logger, string $identity, string $message): void
    {
        $directory = rtrim(DIR_FS_CATALOG, '/\\') . '/' . self::RATE_LIMIT_DIRECTORY;
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            return;
        }

        $marker = @fopen($directory . '/' . $identity, 'c+');
        if ($marker === false) {
            return;
        }

        try {
            if (!flock($marker, LOCK_EX)) {
                return;
            }

            $lastReport = (int)stream_get_contents($marker);
            if ($lastReport > 0 && time() - $lastReport < self::RATE_LIMIT_SECONDS) {
                return;
            }

            $logger->notice($message);
            rewind($marker);
            if (!ftruncate($marker, 0) || fwrite($marker, (string)time()) === false) {
                return;
            }
            fflush($marker);
        } finally {
            flock($marker, LOCK_UN);
            fclose($marker);
        }
    }

    private function isIntentionalInternalUse(string $legacyApi, string $callerFile): bool
    {
        return $legacyApi === 'addPluginsDir()'
            && str_ends_with($callerFile, '/LegacyTemplateEngineExtensionApi.php');
    }

    private function message(string $legacyApi, string $replacement, string $file, int $line): string
    {
        return sprintf(
            'Deprecated Smarty API "Smarty::%s" used in "%s:%d". Migrate to "Smarty::%s"; the compatibility API is temporary. '
            . 'This notice is logged for the same file and API at most once every 24 hours. '
            . "To disable this rate limit for migration diagnostics, set define('SMARTY_LEGACY_API_NOTICE_RATE_LIMIT', false); in includes/configure.php.",
            $legacyApi,
            $file,
            $line,
            $replacement
        );
    }
}
