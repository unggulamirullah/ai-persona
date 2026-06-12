<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentService
{
    /**
     * Define the tools available to the LLM.
     */
    public static function getToolsSchema()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_wikipedia',
                    'description' => 'Cari fakta, berita, atau informasi dunia nyata di Wikipedia. Gunakan ini JIKA user menanyakan sesuatu yang tidak kamu ketahui atau butuh fakta akurat.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Kata kunci pencarian (contoh: "Presiden Indonesia saat ini", "Cuaca Jakarta", "Sejarah Anime")'
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_current_time',
                    'description' => 'Dapatkan jam dan tanggal server saat ini secara real-time.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'timezone' => [
                                'type' => 'string',
                                'description' => 'Zona waktu opsional (default: Asia/Jakarta)'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Execute the requested tool and return the string result.
     */
    public static function executeTool($toolCall)
    {
        $name = $toolCall['function']['name'];
        $arguments = json_decode($toolCall['function']['arguments'], true);

        try {
            if ($name === 'search_wikipedia') {
                return self::searchWikipedia($arguments['query']);
            } elseif ($name === 'get_current_time') {
                return self::getCurrentTime($arguments['timezone'] ?? 'Asia/Jakarta');
            }
        } catch (\Exception $e) {
            Log::error("Tool Execution Error ({$name}): " . $e->getMessage());
            return "Terjadi kesalahan saat menjalankan tool {$name}.";
        }

        return "Tool {$name} tidak ditemukan.";
    }

    /**
     * Tool 1: Search Wikipedia
     */
    private static function searchWikipedia($query)
    {
        $response = Http::get('https://id.wikipedia.org/w/api.php', [
            'action' => 'query',
            'list' => 'search',
            'srsearch' => $query,
            'utf8' => '',
            'format' => 'json'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['query']['search']) && count($data['query']['search']) > 0) {
                // Return top 2 results
                $results = [];
                foreach (array_slice($data['query']['search'], 0, 2) as $item) {
                    $snippet = strip_tags($item['snippet']);
                    $results[] = "Judul: {$item['title']}\nSnippet: {$snippet}";
                }
                return "Hasil Pencarian Wikipedia untuk '{$query}':\n" . implode("\n\n", $results);
            }
            return "Tidak ada hasil ditemukan di Wikipedia untuk '{$query}'.";
        }

        return "Gagal mengakses Wikipedia.";
    }

    /**
     * Tool 2: Get Current Time
     */
    private static function getCurrentTime($timezone)
    {
        $date = now($timezone);
        return "Waktu saat ini (Zona {$timezone}): " . $date->translatedFormat('l, d F Y H:i:s');
    }
}
