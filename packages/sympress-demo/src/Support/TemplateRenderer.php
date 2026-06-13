<?php

declare(strict_types=1);

namespace SymPress\Demo\Support;

final readonly class TemplateRenderer
{
    public function __construct(
        private string $viewPath,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $path = $this->resolvePath($template);

        if ($path === null) {
            throw new \RuntimeException('Requested view was not found.');
        }

        ob_start();

        try {
            $this->includeView($path, $data);

            return (string) ob_get_clean();
        } catch (\Throwable $throwable) {
            ob_end_clean();

            throw $throwable;
        }
    }

    private function resolvePath(string $template): ?string
    {
        $rootPath = realpath($this->viewPath);

        if ($rootPath === false) {
            return null;
        }

        $templatePath = realpath($rootPath . '/' . ltrim($template, '/'));

        if ($templatePath === false || !str_starts_with($templatePath, $rootPath . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return is_file($templatePath) ? $templatePath : null;
    }

    /** @param array<string, mixed> $viewData */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter -- The included PHP view consumes $viewData.
    private function includeView(string $path, array $viewData): void
    {
        require $path;
    }
}
