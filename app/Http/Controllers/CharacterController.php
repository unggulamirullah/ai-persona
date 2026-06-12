<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CharacterController extends Controller
{
    /**
     * Show character details with Lazy Fetching logic.
     */
    public function show($api_id)
    {
        // 1. Cek database lokal characters berdasarkan api_id
        $character = Character::where('api_id', $api_id)->first();

        // 2. Jika belum ada di database lokal
        if (!$character) {
            // Lakukan HTTP Request ke Detail API (Jikan)
            $response = Http::get("https://api.jikan.moe/v4/characters/{$api_id}");
            
            if ($response->successful()) {
                $data = $response->json('data');
                $lore = $data['about'] ?? null;

                // Terjemahkan lore ke Bahasa Indonesia menggunakan Groq API jika lore tersedia
                $apiKey = env('GROQ_API_KEY');
                if ($lore && $apiKey) {
                    try {
                        $llmResponse = Http::timeout(20)->withHeaders([
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Content-Type' => 'application/json',
                        ])->post('https://api.groq.com/openai/v1/chat/completions', [
                            'model' => 'llama-3.1-8b-instant',
                            'messages' => [
                                ['role' => 'system', 'content' => "Kamu adalah ahli analisis karakter fiksi. Tugasmu menganalisis data karakter berikut dan merangkumnya menjadi Buku Panduan Persona dalam Bahasa Indonesia.\n\nFormat output WAJIB seperti ini tanpa teks lain:\n\n--- LATAR BELAKANG ---\n[Sejarah singkat karakter]\n\n--- PANDUAN BERMAIN PERAN (PERSONA) ---\nSifat: [Jelaskan sifat asli, kebiasaan psikologis]\nGaya Bicara: [Nada bicara, kata ganti, tempo]\nKata Khas: [Sebutkan kata atau kalimat ikonik yang PALING SERING dia ucapkan di animenya. JIKA TIDAK ADA, tulis '-']\nContoh Dialog:\n1. [Saat menyapa]\n2. [Saat marah/bertarung]"],
                                ['role' => 'user', 'content' => $lore],
                            ],
                            'temperature' => 0.3,
                        ]);

                        if ($llmResponse->successful()) {
                            $translated = $llmResponse->json()['choices'][0]['message']['content'];
                            if (!empty($translated)) {
                                $lore = $translated;
                            }
                        }
                    } catch (\Exception $e) {
                        // Jika terjemahan gagal (timeout, dll), biarkan lore tetap dalam bahasa aslinya.
                    }
                }
                
                // Simpan name, image_url, dan lore ke database MySQL
                $character = Character::create([
                    'api_id' => $data['mal_id'],
                    'name' => $data['name'],
                    'image_url' => $data['images']['jpg']['image_url'] ?? null,
                    'lore' => $lore,
                ]);
            } else {
                // Jika API Jikan tidak menemukan karakternya
                abort(404, 'Character not found on Jikan API');
            }
        }

        return view('character.show', compact('character'));
    }

    /**
     * Handle AJAX chat request to LLM (Groq API).
     */
    public function chat(Request $request, $api_id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'scenario' => 'nullable|string|max:1000'
        ]);

        $character = Character::where('api_id', $api_id)->firstOrFail();

        $scenarioText = "";
        if ($request->filled('scenario')) {
            $scenarioText = " [SKENARIO SAAT INI: {$request->scenario}]. Sesuaikan gaya, emosi, dan balasanmu dengan situasi di skenario ini.";
        }

        // Racik System Prompt dengan penegakan Persona yang super ketat
        $systemPrompt = "System: Kamu WAJIB meniru karakter {$character->name} dengan tingkat akurasi 100%. DILARANG KERAS keluar dari karakter (Out of Character).\n\nBerikut adalah Buku Panduan Personamu:\n{$character->lore}\n{$scenarioText}\n\nJawab pertanyaan user HANYA menggunakan gaya bicara, sifat, dan kata khas (catchphrase) yang disebutkan di atas. Kamu WAJIB membalas dalam format JSON murni tanpa tambahan teks lain, berisi tiga kunci: 'emotion', 'reply', dan 'background_prompt'.\n- 'emotion' HANYA boleh bernilai: 'happy', 'sad', 'angry', 'neutral', 'shocked', atau 'smug'.\n- 'reply' adalah pesan teks balasanmu.\n- 'background_prompt' adalah prompt pendek (maks 5 kata dalam bahasa Inggris) yang menggambarkan pemandangan lokasi obrolan saat ini berdasarkan skenario atau mood. Contoh: 'dark spooky haunted house' atau 'peaceful anime meadow'.";

        $apiKey = env('GROQ_API_KEY');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API Key LLM (GROQ_API_KEY) belum dikonfigurasi di .env'], 500);
        }

        // Hit LLM API (Groq API - OpenAI Compatible)
        try {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $request->message],
            ];

            $payload = [
                'model' => 'llama-3.1-8b-instant',
                'messages' => $messages,
                'temperature' => 0.7,
                'tools' => \App\Services\AgentService::getToolsSchema(),
                'tool_choice' => 'auto'
            ];

            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageData = $data['choices'][0]['message'];
                $content = '{}';

                if (!empty($messageData['tool_calls'])) {
                    // Agentic Workflow: Tool Call Detected!
                    \Illuminate\Support\Facades\Log::info("Tool Call Triggered in Character Chat!");
                    $messages[] = $messageData;

                    foreach ($messageData['tool_calls'] as $toolCall) {
                        $toolResult = \App\Services\AgentService::executeTool($toolCall);
                        $messages[] = [
                            'role' => 'tool',
                            'tool_call_id' => $toolCall['id'],
                            'name' => $toolCall['function']['name'],
                            'content' => $toolResult
                        ];
                    }

                    // Second API Call to get final JSON
                    $payload['messages'] = $messages;
                    unset($payload['tools']);
                    unset($payload['tool_choice']);
                    $payload['response_format'] = ['type' => 'json_object'];

                    $response2 = Http::timeout(45)->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', $payload);

                    if ($response2->successful()) {
                        $content = $response2->json('choices.0.message.content') ?? '{}';
                    }
                } else {
                    $content = $messageData['content'] ?? '{}';
                }

                $parsed = json_decode($content, true);
                
                $reply = $parsed['reply'] ?? 'Saya tidak bisa merespons saat ini.';
                $emotion = $parsed['emotion'] ?? 'neutral';
                $backgroundPrompt = $parsed['background_prompt'] ?? 'anime background landscape';
                
                return response()->json([
                    'reply' => $reply,
                    'emotion' => strtolower($emotion),
                    'background_prompt' => $backgroundPrompt
                ]);
            }

            return response()->json(['error' => 'Gagal menghubungi API LLM. Detail: ' . $response->body()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}
