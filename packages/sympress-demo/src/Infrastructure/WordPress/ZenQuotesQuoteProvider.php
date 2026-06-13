<?php

declare(strict_types=1);

namespace SymPress\Demo\Infrastructure\WordPress;

use SymPress\Demo\Application\Seed\DemoNoteFixture;
use SymPress\Demo\Application\Seed\QuoteFixtureFactory;
use SymPress\Demo\Application\Seed\RemoteQuoteProviderInterface;

final readonly class ZenQuotesQuoteProvider implements RemoteQuoteProviderInterface
{
    private const string API_URL = 'https://zenquotes.io/api/quotes';
    private const string ATTRIBUTION_URL = 'https://zenquotes.io/';

    public function __construct(
        private QuoteFixtureFactory $fixtures,
    ) {
    }

    public function quotes(int $limit): array
    {
        if (!function_exists('wp_remote_get')) {
            return [];
        }

        $response = wp_remote_get(self::API_URL, [
            'timeout' => 8,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'SymPress Demo/1.0; https://github.com/SymPress/demo',
            ],
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        if ($status < 200 || $status >= 300) {
            return [];
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        if (!is_array($decoded)) {
            return [];
        }

        return $this->mapQuotes($decoded, $limit);
    }

    /**
     * @param array<mixed> $decoded
     * @return list<DemoNoteFixture>
     */
    private function mapQuotes(array $decoded, int $limit): array
    {
        $fixtures = [];

        foreach ($decoded as $item) {
            if (count($fixtures) >= $limit || !is_array($item)) {
                break;
            }

            $quote = trim((string) ($item['q'] ?? ''));
            $author = trim((string) ($item['a'] ?? ''));

            if ($quote === '' || $author === '') {
                continue;
            }

            $fixtures[] = $this->fixtures->create(
                $quote,
                $author,
                sprintf('Attributed to %s through the ZenQuotes seed API.', $author),
                'ZenQuotes.io',
                self::ATTRIBUTION_URL,
            );
        }

        return $fixtures;
    }
}
