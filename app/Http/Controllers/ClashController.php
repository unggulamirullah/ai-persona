<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClashController extends Controller
{
    /**
     * Tampilkan halaman Setup Clash.
     */
    public function setup()
    {
        return view('clash.setup');
    }

    /**
     * Fetch character from Jikan API and save to DB (Lazy Fetching for Clash)
     */
    private function getOrFetchCharacter($api_id)
    {
        $character = Character::where('api_id', $api_id)->first();

        if (!$character) {
            $response = Http::get("https://api.jikan.moe/v4/characters/{$api_id}");
            if ($response->successful()) {
                $data = $response->json('data');
                $lore = $data['about'] ?? null;

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
                        // ignore
                    }
                }

                $character = Character::create([
                    'api_id' => $data['mal_id'],
                    'name' => $data['name'],
                    'image_url' => $data['images']['jpg']['image_url'] ?? null,
                    'lore' => $lore,
                ]);
            } else {
                abort(404, "Character ID {$api_id} not found.");
            }
        }
        return $character;
    }

    /**
     * Tampilkan Arena Clash.
     */
    public function show($id1, $id2)
    {
        if ($id1 == $id2) {
            return redirect('/clash')->with('error', 'Pilih dua karakter yang berbeda.');
        }

        $char1 = $this->getOrFetchCharacter($id1);
        $char2 = $this->getOrFetchCharacter($id2);

        return view('clash.show', compact('char1', 'char2'));
    }

    /**
     * Handle AJAX Group Chat Request.
     */
    public function chat(Request $request, $id1, $id2)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'scenario' => 'nullable|string|max:1000'
        ]);

        $char1 = Character::where('api_id', $id1)->firstOrFail();
        $char2 = Character::where('api_id', $id2)->firstOrFail();

        $apiKey = env('GROQ_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key LLM (GROQ_API_KEY) belum dikonfigurasi di .env'], 500);
        }

        $scenarioText = "";
        if ($request->filled('scenario')) {
            $scenarioText = "\nSKENARIO LINGKUNGAN SAAT INI: {$request->scenario}\nPastikan kedua karakter bereaksi dan menyesuaikan perilaku mereka sesuai dengan situasi di atas.";
        }

        $systemPrompt = "System: Kamu adalah mesin simulasi peran grup. Kamu WAJIB memainkan DUA karakter sekaligus dengan tingkat akurasi 100%. DILARANG KERAS keluar dari karakter (Out of Character).
Karakter 1: {$char1->name}
Buku Panduan Persona 1: {$char1->lore}

Karakter 2: {$char2->name}
Buku Panduan Persona 2: {$char2->lore}
{$scenarioText}
ATURAN KETAT:
1. User mengirim pesan ke ruangan grup.
2. Kamu merespons sebagai {$char1->name} dan {$char2->name} HANYA dalam format JSON.
3. Mereka berdua harus saling menyadari, bisa setuju atau bertengkar.
4. Format output JSON wajib seperti ini persis:
{
  \"char1\": {\"emotion\": \"happy|sad|angry|neutral|shocked|smug\", \"reply\": \"Pesan {$char1->name}\"},
  \"char2\": {\"emotion\": \"happy|sad|angry|neutral|shocked|smug\", \"reply\": \"Pesan {$char2->name}\"},
  \"background_prompt\": \"prompt pendek maks 5 kata dalam bahasa Inggris yang menggambarkan lingkungan visual saat ini\"
}";

        try {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $request->message],
            ];

            $payload = [
                'model' => 'llama-3.1-8b-instant',
                'messages' => $messages,
                'temperature' => 0.8,
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
                    \Illuminate\Support\Facades\Log::info("Tool Call Triggered in Clash Arena!");
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
                    // Bersihkan teks ekstra (seperti markdown ```json)
                    $start = strpos($content, '{');
                    $end = strrpos($content, '}');
                    if ($start !== false && $end !== false) {
                        $content = substr($content, $start, $end - $start + 1);
                    }
                }

                \Illuminate\Support\Facades\Log::info("LLM Clash Response: " . $content);
                $jsonResponse = json_decode($content, true);
                
                $keys = array_keys($jsonResponse ?? []);
                $key1 = $keys[0] ?? 'char1';
                $key2 = $keys[1] ?? 'char2';

                return response()->json([
                    'reply1' => $jsonResponse[$key1]['reply'] ?? '',
                    'emotion1' => strtolower($jsonResponse[$key1]['emotion'] ?? 'neutral'),
                    'reply2' => $jsonResponse[$key2]['reply'] ?? '',
                    'emotion2' => strtolower($jsonResponse[$key2]['emotion'] ?? 'neutral'),
                    'background_prompt' => $jsonResponse['background_prompt'] ?? 'anime arena landscape',
                ]);
            }

            return response()->json(['error' => 'Gagal menghubungi API LLM. Detail: ' . $response->body()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}
