<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.5-flash'));
    }

    public function chat(array $messages, array $tools = [], array $toolConfig = []): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $payload = [
            'contents' => $messages,
            'systemInstruction' => [
                'parts' => [['text' => $this->systemPrompt()]],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];

        if (!empty($tools)) {
            $payload['tools'] = [['functionDeclarations' => $tools]];
        }
        if (!empty($toolConfig)) {
            $payload['toolConfig'] = $toolConfig;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-goog-api-key' => $this->apiKey])
                ->post($url, $payload);

            if ($response->failed()) {
                $body = $response->json();
                $message = $body['error']['message'] ?? $response->status();
                Log::error('Gemini API error: ' . $message, ['status' => $response->status()]);
                throw new \Exception('Error del servicio de IA: ' . $message);
            }

            return $response->json();
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'cURL')) {
                Log::error('Gemini connection error: ' . $e->getMessage());
                throw new \Exception('No se pudo conectar con el servicio de IA. Verifica tu conexión.');
            }
            throw $e;
        }
    }

    public function extractText(array $response): string
    {
        $candidates = $response['candidates'] ?? [];
        if (empty($candidates)) {
            return 'No obtuve respuesta. Intenta de nuevo.';
        }

        $parts = $candidates[0]['content']['parts'] ?? [];
        $text = '';
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }
        return trim($text) ?: 'No obtuve respuesta. Intenta de nuevo.';
    }

    public function extractFunctionCalls(array $response): array
    {
        $calls = [];
        $candidates = $response['candidates'] ?? [];
        if (empty($candidates)) return $calls;

        $parts = $candidates[0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $calls[] = $part['functionCall'];
            }
        }
        return $calls;
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
Eres "OLIMPO AI", el asistente inteligente del Sistema de Control OLIMPO.
OLIMPO es un área de control de personal, vehículos, visitas y ocurrencias.
NUNCA uses la palabra "club" para referirte a OLIMPO; es un ÁREA (grupo de trabajo).

Reglas:
- Responde SIEMPRE en español, con tono amable y directo.
- Sé conciso: usa listas o tablas cuando ayude, sin relleno innecesario.
- Si el usuario pregunta por datos del sistema (personal, asistencia, ocurrencias, vehículos, cumpleaños, recordatorios), usa las herramientas disponibles para consultarlos en vez de inventar.
- Si no tienes una herramienta para responder algo con datos reales, dilo con honestidad y sugiere dónde encontrar la información en el panel.
- Si algo no se entiende, pide aclaración brevemente.
PROMPT;
    }
}
