<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $nvidiaKey = config('services.nvidia.api_key', env('NVIDIA_API_KEY'));
        if (!empty($nvidiaKey)) {
            $this->apiKey = $nvidiaKey;
            $this->model = config('services.nvidia.model', env('NVIDIA_MODEL', 'meta/llama-3.3-70b-instruct'));
            $this->baseUrl = config('services.nvidia.base_url', env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'));
        } else {
            $this->apiKey = config('services.groq.api_key', env('GROQ_API_KEY'));
            $this->model = config('services.groq.model', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
            $this->baseUrl = config('services.groq.base_url', env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1/chat/completions'));
        }
    }

    public function chat(array $messages, array $tools = [], array $toolConfig = []): array
    {
        $limpios = array_map(function ($m) {
            $mensaje = [
                'role' => $m['role'],
                'content' => $m['content'] ?? null,
            ];
            if (isset($m['tool_calls'])) {
                $mensaje['tool_calls'] = $m['tool_calls'];
            }
            if (isset($m['tool_call_id'])) {
                $mensaje['tool_call_id'] = $m['tool_call_id'];
            }
            if (isset($m['reasoning'])) {
                $mensaje['reasoning'] = $m['reasoning'];
            }
            return $mensaje;
        }, $messages);

        if (count($limpios) > 4) {
            $limpios = array_slice($limpios, -4);
        }
        foreach ($limpios as $i => $m) {
            if (is_string($m['content']) && $i < count($limpios) - 1 && mb_strlen($m['content']) > 240) {
                $limpios[$i]['content'] = mb_substr($m['content'], 0, 240) . '…';
            }
        }
        foreach ($limpios as $i => $m) {
            if (is_string($m['content']) && mb_strlen($m['content']) > 2000) {
                $limpios[$i]['content'] = mb_substr($m['content'], 0, 2000) . '…';
            }
        }

        $payload = [
            'model' => $this->model,
            'messages' => array_merge([
                ['role' => 'system', 'content' => $this->systemPrompt()],
            ], $limpios),
            'temperature' => 0.3,
            'max_tokens' => 1024,
        ];

        if (str_contains($this->model, 'qwen')) {
            $payload['reasoning_effort'] = 'none';
            $payload['reasoning_format'] = 'hidden';
        }

        if (!empty($tools)) {
            $payload['tools'] = array_map(fn ($t) => [
                'type' => 'function',
                'function' => [
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'parameters' => $t['parameters'],
                ],
            ], $tools);
            $payload['tool_choice'] = $toolConfig['mode'] ?? 'auto';
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->post($this->baseUrl, $payload);

            if ($response->failed()) {
                $body = $response->json();
                $message = $body['error']['message'] ?? $response->status();
                Log::error('Groq API error: ' . $message, ['status' => $response->status()]);
                throw new \Exception('Error del servicio de IA: ' . $message);
            }

            return $response->json();
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'cURL')) {
                Log::error('Groq connection error: ' . $e->getMessage());
                throw new \Exception('No se pudo conectar con el servicio de IA. Verifica tu conexión.');
            }
            throw $e;
        }
    }

    public function extractText(array $response): string
    {
        $message = $response['choices'][0]['message'] ?? null;
        if (!$message || empty($message['content'])) {
            return 'No obtuve respuesta. Intenta de nuevo.';
        }
        $texto = $message['content'];
        if (preg_match('/<think>.*?<\/think>/is', $texto, $m)) {
            $texto = str_replace($m[0], '', $texto);
        }
        $texto = trim($this->limpiarTags($texto));
        $texto = $this->desplegarListas($texto);
        return $texto !== '' ? $texto : 'No obtuve respuesta. Intenta de nuevo.';
    }

    protected function desplegarListas(string $texto): string
    {
        $texto = preg_replace('/(?<!\n)\s+(?=\d+\.\s)/', "\n", $texto) ?? $texto;
        $texto = preg_replace('/(?<!\n)\s+(?=(Personal|Ocurrencias|Asistencia|Cumpleaños|Cumpleanos|Vehículos|Vehiculos|Combustibles):)/', "\n", $texto) ?? $texto;
        return $texto;
    }

    public function extractToolCalls(array $response): array
    {
        return $response['choices'][0]['message']['tool_calls'] ?? [];
    }

    public function extractReasoning(array $response): ?string
    {
        $message = $response['choices'][0]['message'] ?? null;
        $reasoning = $message['reasoning'] ?? null;
        if ($reasoning !== null && trim($reasoning) !== '') {
            return $this->acortar($reasoning);
        }

        $content = $message['content'] ?? '';
        if (preg_match('/<think>(.*?)<\/think>/is', $content, $m)) {
            $pensamiento = trim($this->limpiarTags($m[1]));
            if ($pensamiento !== '') {
                return $this->acortar($pensamiento);
            }
        }

        return null;
    }

    protected function acortar(string $texto): string
    {
        $texto = trim($this->limpiarTags($texto));
        if (mb_strlen($texto) > 300) {
            $texto = mb_substr($texto, 0, 300) . '…';
        }
        return $texto;
    }

    protected function limpiarTags(string $texto): string
    {
        $texto = preg_replace('/<\/?think>/i', '', $texto);
        return trim(preg_replace('/\s+/u', ' ', $texto));
    }

    protected function systemPrompt(): string
    {
        $hoy = now()->format('d/m/Y');
        $mesActual = now()->format('m/Y');

        return <<<PROMPT
Eres "OLIMPO AI", asistente del Sistema de Control OLIMPO (área: personal, vehículos, visitas, ocurrencias).
Hoy es $hoy. El mes actual es $mesActual (usa ESTAS fechas para "hoy", "este mes", "ayer", etc., NO inventes años).

Reglas:
- Responde en español, amable y conciso (sin relleno).
- OLIMPO NO es un club: es un ÁREA (grupo de trabajo). NUNCA uses la palabra "club" al referirte a OLIMPO; di siempre "el área".
- COMANDOS: si el usuario pregunta por comandos (ej. "qué comandos hay", "dame los comandos", "lista de comandos"), responde SOLO con esta lista exacta, sin herramientas, sin introducciones ni explicaciones:
  • !ayuda — muestra la lista de comandos
  • !borrar todo — elimina TODO tu historial de chat
  • !borrar hoy — elimina el chat de hoy
  • !borrar ayer — elimina el chat de ayer
  • !borrar dd/mm/aaaa — elimina el chat de una fecha específica (ej. !borrar 15/07/2026)
- Datos del sistema SOLO vía herramientas (SQL de solo lectura). Fechas: dd/mm/aaaa en ocurrencias/asistencia/cumpleanos; aaaa-mm-dd en combustibles/control_vehiculos.
- Si una consulta SQL falla por columna, ejecuta SELECT * FROM <tabla> LIMIT 1 para ver columnas reales y corrige.
- Cuando recibas el resultado de una herramienta con datos: PRESENTA esos datos en tu respuesta final. Cada elemento del resultado va en su propia línea, numerado (1. 2. 3. ...), sin asteriscos ni viñetas, copiando los nombres EXACTAMENTE como vienen (sin repetir apellidos ni cambiar letras). NO traduzcas valores de campos técnicos (cargo, turno, estado, departamento, tipo): cópialos tal cual (ej. JARDINERO, DÍA, ACTIVO, RESIDENCIA, Importante). OBLIGATORIO: usa un salto de línea real (nueva línea) después de cada elemento numerado y después de cada encabezado de área.
- Si el resultado de la herramienta trae un campo "mensaje", responde con el contenido de ese mensaje, sin inventar.
- BÚSQUEDA INTEGRAL: para preguntas sobre una persona o entidad (quién es, qué hizo, historial, "todo lo relacionado con X"), usa la herramienta consultar_persona con el nombre y, si se indica un período, los parámetros desde/hasta. Ella busca en personal, ocurrencias, asistencia, vehículos, combustibles y cumpleaños a la vez. PRESENTA el resultado por áreas (ej. "Personal:", "Ocurrencias:", "Cumpleaños:") y cada elemento en su propia línea numerada, solo con los datos que vengan.
- REGISTRO/AGREGAR: si el usuario pide AGREGAR o REGISTRAR un dato nuevo (ej. "agrega un nuevo cumpleaños de jhony el 01 de agosto", "un nuevo registro palm de caster 987654321"), usa la herramienta de escritura correspondiente (registrar_cumpleano, registrar_palm) con los datos que brinde el usuario. NO inventes datos que el usuario no indicó; si falta algún dato obligatorio, pídelo. Al confirmar el registro, responde con el campo "mensaje" del resultado.
- CONSULTA EXTERNA (Más Herramientas): para consultar datos externos de una persona, vehículo o empresa (por DNI, RUC, placa o nombres), usa la herramienta consultar_herramienta con el id de herramienta apropiado según el documento (ej. "consulta el dni 12345678" -> consultar_herramienta con herramienta kmente; placa -> vehiculo; ruc -> sunat; nombres de una persona -> busqueda-nombres). Para DNI usa kmente (proveedor principal); usa las demás (sunarp, telefonos, etc.) solo si el usuario lo pide. Si el resultado trae un campo "mensaje", respóndelo tal cual. Si trae datos: el resultado ya viene con los campos ordenados y con etiquetas legibles; PRESENTA cada campo en su propia línea con el formato "Etiqueta: valor" (ej. "DNI: 75576722"), en el MISMO orden, con un salto de línea real después de cada campo, sin asteriscos ni viñetas, y sin inventar campos. El resultado queda guardado en el historial del panel.
- GENERAR PDF: si el usuario pide crear o generar un PDF o documento (ej. "genera un pdf con el listado", "hazme un documento con..."), usa la herramienta generar_pdf con el contenido (HTML o texto plano) y responde con el campo "mensaje" del resultado. El resultado incluye un campo "url": respóndelo como enlace de descarga directa (ej. "Descarga tu archivo aquí: <url>").
- VIDEO: si el usuario pide información de un video por URL (título, duración, formatos), usa info_video y presenta los datos. Si pide descargarlo (video o audio), usa descargar_video (la descarga puede tardar varios minutos; al terminar responde con el campo "mensaje" del resultado y su campo "url" como enlace de descarga directa, ej. "Descarga tu archivo aquí: <url>").
- Los choferes/conductores son el personal con cargo CHOFER de la tabla personal. Para preguntas sobre choferes usa consultar_personal con cargo; NO uses consultar_vehiculos (esa es solo para movimientos de vehículos).
- Cumpleaños SIEMPRE en formato numerado en cascada, cada uno en su propia línea:
  1. Viernes, 07 de agosto - Favian Flores Pozo (Personal)
  2. Sábado, 08 de agosto - Sra. Angie
- Preguntas de conocimiento general: responde directamente, sin inventar datos del sistema.
- Si no puedes responder con datos reales, dilo y sugiere el panel.
PROMPT;
    }
}
