<?php

namespace Modified\Storefront\Ui\Runtime;

use LogicException;
use Modified\Storefront\Template\TemplateRuntime;
use Modified\Storefront\Module\ModuleRootResolver;
use Modified\Storefront\Ui\Module\Template\ModuleTemplateResolver;

/**
 * Provides the minimal request-local UI services required by module templates in PR 2.1.
 */
final class UiRuntime
{
    private static ?self $instance = null;

    private ModuleRootResolver $moduleRootResolver;
    private ModuleTemplateResolver $moduleTemplateResolver;

    public function __construct(
        ModuleRootResolver $moduleRootResolver,
        ModuleTemplateResolver $moduleTemplateResolver
    ) {
        $this->moduleRootResolver = $moduleRootResolver;
        $this->moduleTemplateResolver = $moduleTemplateResolver;
    }

    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = self::fromGlobals();
        }

        return self::$instance;
    }

    public static function install(self $runtime): void
    {
        self::$instance = $runtime;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public static function fromGlobals(): self
    {
        if (!defined('DIR_FS_CATALOG') || !is_scalar(DIR_FS_CATALOG) || (string) DIR_FS_CATALOG === '') {
            throw new LogicException('The DIR_FS_CATALOG constant must be defined before using the UI runtime.');
        }

        $templateRuntime = TemplateRuntime::get();
        $moduleRootResolver = new ModuleRootResolver((string) DIR_FS_CATALOG);

        return new self(
            $moduleRootResolver,
            new ModuleTemplateResolver(
                $templateRuntime->chain(),
                $templateRuntime->fileResolver(),
                $moduleRootResolver
            )
        );
    }

    public function moduleRootResolver(): ModuleRootResolver
    {
        return $this->moduleRootResolver;
    }

    public function moduleTemplateResolver(): ModuleTemplateResolver
    {
        return $this->moduleTemplateResolver;
    }
}
