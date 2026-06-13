<?php

declare(strict_types=1);

namespace SymPress\Demo\Support;

final readonly class TemplateRenderer
{
    public function __construct(
        private string $viewPath,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $path = sprintf('%s/%s', rtrim($this->viewPath, '/'), ltrim($template, '/'));

        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('View "%s" was not found.', $template));
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

    /**
     * @param array<string, mixed> $data
     */
    private function includeView(string $path, array $data): void
    {
        (static function () use ($path, $data): void {
            extract($data, EXTR_SKIP);
            require $path;
        })();
    }
}
