<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GroqService;
use App\Models\Ocurrencia;
use App\Models\Personal;
use App\Models\Asistencia;
use App\Models\Cumpleano;
use App\Models\ControlVehiculo;
use App\Models\ChatMensaje;
use Illuminate\Support\Facades\DB;

class ChatIa extends Component
{
    public bool $open = false;
    public string $message = '';
    public array $messages = [];
    public bool $loading = false;
    protected ?string $pendienteUrl = null;

    public function mount(): void
    {
        $this->messages = ChatMensaje::where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'razonamiento' => $m->razonamiento,
                'fecha_mensaje' => substr((string) $m->created_at, 0, 10),
            ])
            ->values()
            ->toArray();
    }

    protected function tools(): array
    {
        return [
            [
                'name' => 'consultar_resumen_del_dia',
                'description' => 'Resumen del día: personal activo, ocurrencias hoy/mes, asistencia, vehículos, cumpleaños de hoy.',
                'parameters' => ['type' => 'object', 'properties' => (object) []],
            ],
            [
                'name' => 'consultar_persona',
                'description' => 'BÚSQUEDA INTEGRAL en todo el sistema: personal (ficha), ocurrencias, asistencia, movimientos de vehículos, combustibles y cumpleaños que coincidan con el nombre. ÚSALA para preguntas sobre una persona o entidad (ej. "qué hizo clemente", "historial de un chofer", "todo lo relacionado con X"). Devuelve secciones por área solo con datos encontrados.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre' => ['type' => 'string', 'description' => 'Nombre o parte del nombre a buscar (obligatorio)'],
                        'desde' => ['type' => 'string', 'description' => 'Fecha inicial dd/mm/yyyy (opcional)'],
                        'hasta' => ['type' => 'string', 'description' => 'Fecha final dd/mm/yyyy (opcional)'],
                    ],
                    'required' => ['nombre'],
                ],
            ],
            [
                'name' => 'consultar_ocurrencias',
                'description' => 'Ocurrencias. Filtros: fecha (dd/mm/yyyy), rango desde/hasta (dd/mm/yyyy) o nombre. Sin fecha: las del mes actual.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'fecha' => ['type' => 'string', 'description' => 'dd/mm/yyyy (opcional)'],
                        'desde' => ['type' => 'string', 'description' => 'Fecha inicial dd/mm/yyyy (opcional)'],
                        'hasta' => ['type' => 'string', 'description' => 'Fecha final dd/mm/yyyy (opcional)'],
                        'nombre' => ['type' => 'string', 'description' => 'Nombre (opcional)'],
                    ],
                ],
            ],
            [
                'name' => 'consultar_personal',
                'description' => 'Personal activo del área. Filtros: nombre o cargo (ej. "chofer", "vigilante", "cocina"). Los choferes/conductores son personal con cargo CHOFER: consulta aquí, NO en consultar_vehiculos. Cada fila trae el nombre completo en el campo "nombre" (no repetir apellidos).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre' => ['type' => 'string', 'description' => 'Nombre (opcional)'],
                        'cargo' => ['type' => 'string', 'description' => 'Cargo (opcional)'],
                    ],
                ],
            ],
            [
                'name' => 'consultar_asistencia',
                'description' => 'Asistencia de una fecha (presentes/tardanzas/ausentes).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'fecha' => ['type' => 'string', 'description' => 'dd/mm/yyyy (opcional)'],
                        'nombre' => ['type' => 'string', 'description' => 'Nombre (opcional)'],
                    ],
                ],
            ],
            [
                'name' => 'consultar_cumpleanos',
                'description' => 'Cumpleaños. "mm"=mes (ej. 08=agosto), "dd/mm"=día. Sin fecha: mes actual.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'fecha' => ['type' => 'string', 'description' => 'mm o dd/mm (opcional)'],
                    ],
                ],
            ],
            [
                'name' => 'consultar_vehiculos',
                'description' => 'Movimientos de salida/ingreso de vehículos (fecha, placa, hora de salida/ingreso, km). No usar para preguntar por choferes: los choferes son el personal con cargo CHOFER (consultar_personal).',
                'parameters' => ['type' => 'object', 'properties' => (object) []],
            ],
            [
                'name' => 'consultar_todo',
                'description' => 'SQL SELECT (solo lectura) sobre: personal, cargos, asistencia, ocurrencias, tipos_ocurrencia, cumpleanos, control_vehiculos, camionetas, combustibles, configuracion, users, consulta_historial. Para estadísticas o preguntas no cubiertas.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sql' => ['type' => 'string', 'description' => 'SELECT. Ej: SELECT cargo, COUNT(*) FROM personal GROUP BY cargo;'],
                    ],
                    'required' => ['sql'],
                ],
            ],
            [
                'name' => 'consultar_herramienta',
                'description' => 'Consulta de datos externos (personas, vehículos, empresas) igual que el panel Más Herramientas. Herramientas por tipo de documento: DNI (8 dígitos): usa kmente (proveedor principal y con mayor cobertura), también: telefonos, sunarp, reniec, ficha-reniec, dni-virtual, arbol-genealogico, reconocimiento-facial, justicia, sentinel, siguele-plus, actas, doxing, persona-plus, consultadni. RUC (11 dígitos): sunat. Placa (3-10 caracteres): vehiculo. Nombres (mínimo 3): busqueda-nombres. El resultado queda guardado en el historial del panel.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'herramienta' => ['type' => 'string', 'description' => 'Id de la herramienta: kmente, telefonos, sunarp, reniec, ficha-reniec, dni-virtual, arbol-genealogico, reconocimiento-facial, justicia, sentinel, siguele-plus, actas, doxing, persona-plus, consultadni, sunat, vehiculo, busqueda-nombres'],
                        'documento' => ['type' => 'string', 'description' => 'DNI, RUC o placa (opcional según la herramienta)'],
                        'nombres' => ['type' => 'string', 'description' => 'Nombres a buscar (solo para busqueda-nombres)'],
                    ],
                    'required' => ['herramienta'],
                ],
            ],
            [
                'name' => 'generar_pdf',
                'description' => 'GENERAR un documento PDF a partir de HTML o texto (ej. listados, reportes, constancias). ÚSALA cuando el usuario pida crear/generar un PDF o documento. El archivo queda guardado en el servidor y se informa su nombre.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'contenido' => ['type' => 'string', 'description' => 'Contenido del documento: HTML completo o texto plano (obligatorio)'],
                        'titulo' => ['type' => 'string', 'description' => 'Título o encabezado del documento (opcional)'],
                    ],
                    'required' => ['contenido'],
                ],
            ],
            [
                'name' => 'info_video',
                'description' => 'Información de un video (YouTube u otras plataformas) desde su URL: título, duración, canal y formatos disponibles, sin descargarlo.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'url' => ['type' => 'string', 'description' => 'URL completa del video (obligatorio)'],
                    ],
                    'required' => ['url'],
                ],
            ],
            [
                'name' => 'descargar_video',
                'description' => 'DESCARGAR un video o audio desde una URL (YouTube, etc.) con yt-dlp. El archivo se guarda en el servidor (storage/app/temp/downloads) y puede tardar varios minutos. Para formato de audio usa mp3 o m4a.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'url' => ['type' => 'string', 'description' => 'URL completa del video (obligatorio)'],
                        'formato' => ['type' => 'string', 'description' => 'mp4, webm, mp3 o m4a (opcional, por defecto mp4)'],
                        'calidad' => ['type' => 'string', 'description' => 'best, 1080p, 720p, 480p, 360p o worst (opcional, por defecto best)'],
                    ],
                    'required' => ['url'],
                ],
            ],
            [
                'name' => 'registrar_cumpleano',
                'description' => 'AGREGAR un nuevo cumpleaños. ÚSALA cuando el usuario pida registrar/agregar un cumpleaños indicando nombre y fecha. La fecha es SIEMPRE en formato dd/mm (ej. 01/08).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre' => ['type' => 'string', 'description' => 'Nombre de la persona (obligatorio)'],
                        'fecha' => ['type' => 'string', 'description' => 'Día y mes dd/mm (obligatorio, ej. 01/08)'],
                        'parentesco' => ['type' => 'string', 'description' => 'Parentesco o categoría: Personal, Familiar, Invitado, etc. (opcional)'],
                        'dni' => ['type' => 'string', 'description' => 'DNI de 8 dígitos (opcional)'],
                    ],
                    'required' => ['nombre', 'fecha'],
                ],
            ],
            [
                'name' => 'registrar_palm',
                'description' => 'AGREGAR un nuevo registro PALM (consulta por DNI) con el nombre y el documento. ÚSALA cuando el usuario pida registrar/agregar un registro palm indicando nombre y DNI.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre' => ['type' => 'string', 'description' => 'Nombre de la persona (obligatorio)'],
                        'dni' => ['type' => 'string', 'description' => 'DNI de 8 dígitos (obligatorio)'],
                    ],
                    'required' => ['nombre', 'dni'],
                ],
            ],
        ];
    }

    public function toggle()
    {
        $this->open = !$this->open;
        if ($this->open && empty($this->messages)) {
            $this->mensajeBienvenida();
        }
    }

    public function send()
    {
        $this->validate(['message' => 'required|string|min:1|max:2000']);

        $userMsg = trim($this->message);
        $this->message = '';

        if ($this->esComando($userMsg)) {
            $this->procesarComando($userMsg);
            return;
        }

        if ($this->esPreguntaComandos($userMsg)) {
            $this->responderComandos();
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $userMsg, 'fecha_mensaje' => now()->format('Y-m-d')];
        $this->guardarMensaje('user', $userMsg);
        $this->loading = true;
        $this->pendienteUrl = null;

        try {
            $service = app(GroqService::class);
            $result = $this->runConversation($service, $this->messages);

            $text = $service->extractText($result);
            $text = $this->normalizarSalidaLinea($text);
            if ($this->pendienteUrl && !str_contains($text, $this->pendienteUrl)) {
                $text .= "\n\nDescarga tu archivo aquí: " . $this->pendienteUrl;
            }
            $mensajeFinal = [
                'role' => 'assistant',
                'content' => $text,
                'fecha_mensaje' => now()->format('Y-m-d'),
            ];
            if ($reasoning = $service->extractReasoning($result)) {
                $mensajeFinal['razonamiento'] = $reasoning;
            }
            $this->messages[] = $mensajeFinal;
            $this->guardarMensaje('assistant', $text, $reasoning ?? null);
        } catch (\Exception $e) {
            $mensaje = '⚠️ ' . $e->getMessage();
            $this->messages[] = ['role' => 'assistant', 'content' => $mensaje];
            $this->guardarMensaje('assistant', $mensaje);
        } finally {
            $this->loading = false;
        }
    }

    protected function mensajeBienvenida(): void
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "¡Hola! Soy OLIMPO AI 🤖\nPuedo ayudarte con datos del sistema: personal, asistencia, ocurrencias, vehículos, cumpleaños y más. Escribe !ayuda para ver los comandos disponibles.",
        ];
    }

    protected function guardarMensaje(string $role, string $content, ?string $razonamiento = null): void
    {
        ChatMensaje::create([
            'user_id' => auth()->id(),
            'role' => $role,
            'content' => $content,
            'razonamiento' => $razonamiento,
        ]);
    }

    protected function esComando(string $mensaje): bool
    {
        return str_starts_with(strtolower($mensaje), '!ayuda')
            || str_starts_with(strtolower($mensaje), '!borrar');
    }

    protected function esPreguntaComandos(string $mensaje): bool
    {
        return str_contains(strtolower($mensaje), 'comando')
            || str_contains(strtolower($mensaje), '!ayuda');
    }

    protected function responderComandos(): void
    {
        $texto = "**Comandos disponibles:**\n\n"
            . "• `!ayuda` — muestra esta lista\n"
            . "• `!borrar todo` — elimina TODO tu historial de chat\n"
            . "• `!borrar hoy` — elimina el chat de hoy\n"
            . "• `!borrar ayer` — elimina el chat de ayer\n"
            . "• `!borrar dd/mm/aaaa` — elimina el chat de una fecha específica\n\n"
            . "El historial se guarda automáticamente por usuario, incluso al cerrar sesión.";
        $this->messages[] = ['role' => 'assistant', 'content' => $texto];
        $this->guardarMensaje('assistant', $texto);
    }

    protected function procesarComando(string $mensaje): void
    {
        $comando = strtolower(trim($mensaje));
        $texto = '';

        if (str_starts_with($comando, '!ayuda')) {
            $texto = "**Comandos disponibles:**\n\n"
                . "• `!ayuda` — muestra esta lista\n"
                . "• `!borrar todo` — elimina TODO tu historial de chat\n"
                . "• `!borrar hoy` — elimina el chat de hoy\n"
                . "• `!borrar ayer` — elimina el chat de ayer\n"
                . "• `!borrar dd/mm/aaaa` — elimina el chat de una fecha específica\n\n"
                . "El historial se guarda automáticamente por usuario, incluso al cerrar sesión.";
            $this->messages[] = ['role' => 'assistant', 'content' => $texto];
            $this->guardarMensaje('assistant', $texto);
            return;
        }

        if (str_starts_with($comando, '!borrar')) {
            $argumento = trim(substr($comando, 7));
            $borrados = $this->borrarChat($argumento);
            $texto = "🗑️ Se eliminaron **$borrados mensaje(s)** de tu chat.";
            $this->messages[] = ['role' => 'assistant', 'content' => $texto];
            $this->guardarMensaje('assistant', $texto);
            return;
        }
    }

    protected function borrarChat(string $argumento): int
    {
        $query = ChatMensaje::where('user_id', auth()->id());

        if ($argumento === '' || $argumento === 'todo') {
            $borrados = (clone $query)->count();
            $query->delete();
            $this->messages = [];
            $this->mensajeBienvenida();
            return $borrados;
        }

        if ($argumento === 'hoy') {
            $fecha = now()->format('Y-m-d');
        } elseif ($argumento === 'ayer') {
            $fecha = now()->subDay()->format('Y-m-d');
        } else {
            $dt = \DateTime::createFromFormat('d/m/Y', $argumento);
            if (!$dt) {
                $dt = \DateTime::createFromFormat('d-m-Y', $argumento);
            }
            if (!$dt) {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => "⚠️ Fecha no válida. Usa el formato `!borrar dd/mm/aaaa` (ej: `!borrar 15/07/2026`).",
                ];
                return 0;
            }
            $fecha = $dt->format('Y-m-d');
        }

        $borrados = (clone $query)->whereRaw("date(created_at) = ?", [$fecha])->count();
        $query->whereRaw("date(created_at) = ?", [$fecha])->delete();

        $this->messages = array_values(array_filter($this->messages, function ($m) use ($fecha) {
            return ($m['fecha_mensaje'] ?? null) !== $fecha;
        }));
        if (empty($this->messages)) {
            $this->mensajeBienvenida();
        }
        return $borrados;
    }

    protected function runConversation(GroqService $service, array $messages, int $depth = 0): array
    {
        $response = $service->chat($messages, $this->tools());

        $calls = $service->extractToolCalls($response);
        if (empty($calls) || $depth >= 3) {
            return $response;
        }

        $messages[] = [
            'role' => 'assistant',
            'content' => null,
            'reasoning' => $response['choices'][0]['message']['reasoning'] ?? null,
            'tool_calls' => array_map(fn ($call) => [
                'id' => $call['id'],
                'type' => 'function',
                'function' => [
                    'name' => $call['function']['name'],
                    'arguments' => $call['function']['arguments'] ?? '{}',
                ],
            ], $calls),
        ];

        foreach ($calls as $call) {
            $name = $call['function']['name'];
            $args = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];
            $result = $this->executeTool($name, $args);
            if (!empty($result['url'])) {
                $this->pendienteUrl = $result['url'];
            }
            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $call['id'],
                'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ];
        }

        return $this->runConversation($service, $messages, $depth + 1);
    }

    protected function executeTool(string $name, array $args = []): array
    {
        return match ($name) {
            'consultar_resumen_del_dia' => $this->resumenDelDia(),
            'consultar_persona' => $this->buscarPersonaCompleto($args),
            'consultar_ocurrencias' => $this->buscarOcurrencias($args),
            'consultar_personal' => $this->buscarPersonal($args),
            'consultar_asistencia' => $this->buscarAsistencia($args),
            'consultar_cumpleanos' => $this->buscarCumpleanos($args),
            'consultar_vehiculos' => $this->buscarVehiculos(),
            'consultar_todo' => $this->consultarTodo($args['sql'] ?? ''),
            'consultar_herramienta' => $this->consultarHerramientaPanel($args),
            'generar_pdf' => $this->generarPdf($args),
            'info_video' => $this->infoVideo($args),
            'descargar_video' => $this->descargarVideo($args),
            'registrar_cumpleano' => $this->registrarCumpleano($args),
            'registrar_palm' => $this->registrarPalm($args),
            default => ['error' => "Herramienta desconocida: $name"],
        };
    }

    protected function consultarTodo(string $sql): array
    {
        $sql = trim($sql);
        if (!preg_match('/^\s*SELECT\b/i', $sql)) {
            return ['error' => 'Solo se permiten consultas SELECT de solo lectura.'];
        }
        if (preg_match('/\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|ATTACH|DETACH|PRAGMA|REPLACE|VACUUM|REINDEX)\b/i', $sql)) {
            return ['error' => 'Solo se permiten consultas SELECT de solo lectura.'];
        }
        if (preg_match('/;\s*\S/', substr($sql, 0, -1))) {
            return ['error' => 'Solo una consulta a la vez.'];
        }

        $tablasPermitidas = [
            'personal', 'asistencia', 'ocurrencias', 'cumpleanos',
            'control_vehiculos', 'camionetas', 'combustibles',
            'cargos', 'tipos_ocurrencia', 'configuracion', 'users', 'consulta_historial',
        ];
        $tablaDetectada = null;
        foreach ($tablasPermitidas as $tabla) {
            if (preg_match('/\b' . preg_quote($tabla, '/') . '\b/i', $sql)) {
                $tablaDetectada = $tabla;
                break;
            }
        }
        if (!$tablaDetectada) {
            return ['error' => 'La consulta no referencia ninguna tabla permitida del sistema.'];
        }

        if (!preg_match('/\bLIMIT\b/i', $sql)) {
            $sql .= ' LIMIT 50';
        }

        try {
            $filas = DB::select($sql);
            $resultado = array_map(fn ($f) => (array) $f, $filas);
            if (count($resultado) > 50) {
                $resultado = array_slice($resultado, 0, 50);
            }
            return ['total' => count($resultado), 'filas' => $resultado];
        } catch (\Throwable $e) {
            return ['error' => 'Consulta SQL inválida: ' . $e->getMessage()];
        }
    }

    protected function resumenDelDia(): array
    {
        $hoy = now()->format('d/m/Y');
        $mes = now()->format('m');
        $anio = now()->format('Y');

        return [
            'fecha' => $hoy,
            'personal_activo' => Personal::activos()->count(),
            'personal_total' => Personal::count(),
            'ocurrencias_hoy' => Ocurrencia::where('fecha', $hoy)->count(),
            'ocurrencias_mes' => Ocurrencia::where('mes', $mes)->where('anio', $anio)->count(),
            'asistencia_hoy' => Asistencia::where('fecha', $hoy)->count(),
            'presentes_hoy' => Asistencia::where('fecha', $hoy)->where('turno', '!=', 'FALTA')->where('turno', '!=', 'DESCANSO')->count(),
            'tardanzas_hoy' => Asistencia::where('fecha', $hoy)->where('tardanza_min', '>', 0)->count(),
            'cumpleanos_hoy' => Cumpleano::whereRaw("substr(fecha, 1, 5) = ?", [now()->format('d/m')])->count(),
            'vehiculos' => ControlVehiculo::count(),
        ];
    }

    protected function buscarOcurrencias(array $args): array
    {
        $query = Ocurrencia::query();

        $desde = $args['desde'] ?? null;
        $hasta = $args['hasta'] ?? null;

        if (!empty($args['fecha'])) {
            $query->where('fecha', $args['fecha'])
                ->orderByRaw('hora_ingreso IS NULL, hora_ingreso ASC, id ASC');
        } elseif ($desde || $hasta) {
            if ($desde) $query->whereRaw('fecha >= ?', [$desde]);
            if ($hasta) $query->whereRaw('fecha <= ?', [$hasta]);
            $query->orderByRaw('fecha DESC, id DESC');
        } else {
            $query->where('fecha', now()->format('d/m/Y'))
                ->orderByRaw('hora_ingreso IS NULL, hora_ingreso ASC, id ASC');
        }

        if (!empty($args['nombre'])) {
            $query->where('persona_nombre', 'like', '%' . $args['nombre'] . '%');
        }

        $resultado = $query->limit(15)->get(['fecha', 'hora_ingreso', 'hora_salida', 'persona_nombre', 'tipo', 'vehiculo', 'destino', 'turno'])
            ->map(fn ($o) => $o->toArray())
            ->values()
            ->toArray();

        if (empty($resultado)) {
            $filtro = trim(($args['nombre'] ?? '') . ' del ' . ($args['fecha'] ?? now()->format('d/m/Y')));
            return ['mensaje' => "No se encontraron ocurrencias para $filtro."];
        }

        return $resultado;
    }

    protected function buscarPersonal(array $args): array
    {
        $query = Personal::query()->activos()->orderBy('nombre');

        if (!empty($args['nombre'])) {
            $query->where('nombre', 'like', '%' . $args['nombre'] . '%');
        }

        if (!empty($args['cargo'])) {
            $query->where(function ($q) use ($args) {
                $q->where('cargo', 'like', '%' . $args['cargo'] . '%')
                  ->orWhere('departamento', 'like', '%' . $args['cargo'] . '%');
            });
        }

        $resultado = $query->limit(20)->get(['nombre', 'cargo', 'departamento', 'estado'])
            ->map(fn ($p) => [
                'nombre' => trim(preg_replace('/\s+/', ' ', $p->nombre)),
                'cargo' => $p->cargo,
                'departamento' => $p->departamento,
                'estado' => $p->estado,
            ])
            ->toArray();

        if (empty($resultado)) {
            $filtro = $args['cargo'] ?? $args['nombre'] ?? '';
            return ['mensaje' => "No se encontró personal" . ($filtro ? " con $filtro" : '') . '.'];
        }

        return $resultado;
    }

    protected function buscarAsistencia(array $args): array
    {
        $fecha = $args['fecha'] ?? now()->format('d/m/Y');
        $registros = Asistencia::where('fecha', $fecha)
            ->orderByRaw('turno = "FALTA", turno = "DESCANSO", persona_nombre ASC')
            ->get();

        if (!empty($args['nombre'])) {
            $registros = $registros->filter(fn ($a) => str_contains(strtoupper($a->persona_nombre), strtoupper($args['nombre'])));
        }

        $respuesta = [
            'fecha' => $fecha,
            'total_registros' => $registros->count(),
            'presentes' => $registros->where('turno', '!=', 'FALTA')->where('turno', '!=', 'DESCANSO')->count(),
            'tardanzas' => $registros->where('tardanza_min', '>', 0)->count(),
            'faltas' => $registros->where('turno', 'FALTA')->count(),
            'descansos' => $registros->where('turno', 'DESCANSO')->count(),
            'detalle' => $registros->sortBy('persona_nombre')->take(15)->map(fn ($a) => [
                'persona' => $a->persona_nombre,
                'hora_entrada' => $a->hora_entrada,
                'turno' => $a->turno,
                'tardanza_min' => $a->tardanza_min,
            ])->values()->toArray(),
        ];

        if ($registros->isEmpty()) {
            $respuesta['mensaje'] = "No hay registros de asistencia para $fecha.";
        }

        return $respuesta;
    }

    protected function buscarCumpleanos(array $args): array
    {
        $query = Cumpleano::query();

        if (!empty($args['fecha'])) {
            $fechaArg = trim($args['fecha']);
            if (str_contains($fechaArg, '/')) {
                [$d, $m] = array_pad(explode('/', $fechaArg), 2, '');
                if ($m) $query->whereRaw("substr(fecha, 4) = ?", [$m]);
                if ($d) $query->whereRaw("substr(fecha, 1, 2) = ?", [$d]);
            } else {
                $query->whereRaw("substr(fecha, 4) = ?", [$fechaArg]);
            }
        } else {
            $query->whereRaw("substr(fecha, 4) = ?", [now()->format('m')]);
        }

        $resultado = $query->orderByRaw("substr(fecha, 1, 2)")->limit(30)
            ->get(['fecha', 'nombre', 'parentesco'])
            ->map(function ($c) {
                [$d, $m] = array_pad(explode('/', $c->fecha), 2, '');
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                $dNum = (int) $d;
                $mNum = (int) $m;
                $diaSemana = isset($dias[$fechaDia = ((int) date('w', mktime(0, 0, 0, $mNum, $dNum, 2026)))]) ? $dias[$fechaDia] : '';
                return [
                    'fecha' => $c->fecha,
                    'dia_semana' => $diaSemana,
                    'fecha_legible' => $d . ' de ' . ($meses[$mNum - 1] ?? ''),
                    'nombre' => $c->nombre,
                    'parentesco' => $c->parentesco,
                ];
            })
            ->toArray();

        if (empty($resultado)) {
            return ['mensaje' => 'No se encontraron cumpleaños para esa fecha o mes.'];
        }

        return $resultado;
    }

    protected function buscarVehiculos(): array
    {
        $resultado = ControlVehiculo::orderByRaw('hora_ingreso IS NOT NULL, fecha DESC, id DESC')->limit(20)
            ->get()
            ->map(fn ($v) => [
                'fecha' => $v->fecha ?? null,
                'placa' => $v->placa ?? null,
                'marca' => $v->marca ?? null,
                'modelo' => $v->modelo ?? null,
                'chofer' => $v->chofer ?? null,
                'hora_salida' => $v->hora_salida ?? null,
                'hora_ingreso' => $v->hora_ingreso ?? null,
            ])
            ->toArray();

        if (empty($resultado)) {
            return ['mensaje' => 'No hay movimientos de vehículos registrados.'];
        }

        return $resultado;
    }

    protected function buscarPersonaCompleto(array $args): array
    {
        $nombre = trim($args['nombre'] ?? '');
        if ($nombre === '') {
            return ['mensaje' => 'Indica un nombre para buscar en el sistema.'];
        }

        $desde = trim($args['desde'] ?? '');
        $hasta = trim($args['hasta'] ?? '');
        $resultado = [];

        $personal = Personal::activos()
            ->where(function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%$nombre%")
                  ->orWhere('apellido_paterno', 'like', "%$nombre%")
                  ->orWhere('apellido_materno', 'like', "%$nombre%");
            })
            ->limit(5)
            ->get(['nombre', 'cargo', 'departamento', 'estado'])
            ->map(fn ($p) => [
                'nombre' => trim(preg_replace('/\s+/', ' ', $p->nombre)),
                'cargo' => $p->cargo,
                'departamento' => $p->departamento,
                'estado' => $p->estado,
            ])
            ->values()
            ->toArray();
        if (!empty($personal)) {
            $resultado['personal'] = $personal;
        }

        $ocurrencias = Ocurrencia::where(function ($q) use ($nombre) {
                $q->where('persona_nombre', 'like', "%$nombre%")
                  ->orWhere('detalles', 'like', "%$nombre%")
                  ->orWhere('nota_texto', 'like', "%$nombre%");
            })
            ->when($desde, fn ($q) => $q->whereRaw('fecha >= ?', [$desde]))
            ->when($hasta, fn ($q) => $q->whereRaw('fecha <= ?', [$hasta]))
            ->orderByRaw('fecha DESC, id DESC')
            ->limit(20)
            ->get(['fecha', 'hora_ingreso', 'hora_salida', 'persona_nombre', 'tipo', 'detalles', 'vehiculo', 'destino', 'turno'])
            ->map(fn ($o) => $o->toArray())
            ->values()
            ->toArray();
        if (!empty($ocurrencias)) {
            $resultado['ocurrencias'] = $ocurrencias;
        }

        $asistencia = Asistencia::where('persona_nombre', 'like', "%$nombre%")
            ->when($desde, fn ($q) => $q->whereRaw('fecha >= ?', [$desde]))
            ->when($hasta, fn ($q) => $q->whereRaw('fecha <= ?', [$hasta]))
            ->orderByRaw('fecha DESC')
            ->limit(20)
            ->get(['fecha', 'persona_nombre', 'hora_entrada', 'hora_salida', 'turno', 'tardanza_min'])
            ->map(fn ($a) => $a->toArray())
            ->values()
            ->toArray();
        if (!empty($asistencia)) {
            $resultado['asistencia'] = $asistencia;
        }

        $vehiculos = ControlVehiculo::where('chofer', 'like', "%$nombre%")
            ->when($desde, fn ($q) => $q->whereRaw('fecha >= ?', [str_replace('/', '-', $desde)]))
            ->when($hasta, fn ($q) => $q->whereRaw('fecha <= ?', [str_replace('/', '-', $hasta)]))
            ->orderByRaw('fecha DESC, id DESC')
            ->limit(20)
            ->get(['fecha', 'placa', 'marca', 'modelo', 'chofer', 'hora_salida', 'hora_ingreso', 'km_salida', 'km_ingreso'])
            ->map(fn ($v) => $v->toArray())
            ->values()
            ->toArray();
        if (!empty($vehiculos)) {
            $resultado['vehiculos'] = $vehiculos;
        }

        $combustibles = DB::table('combustibles')->where('conductor', 'like', "%$nombre%")
            ->when($desde, fn ($q) => $q->whereRaw('fecha >= ?', [str_replace('/', '-', $desde)]))
            ->when($hasta, fn ($q) => $q->whereRaw('fecha <= ?', [str_replace('/', '-', $hasta)]))
            ->orderByRaw('fecha DESC, id DESC')
            ->limit(20)
            ->get(['fecha', 'placa', 'marca', 'modelo', 'conductor', 'combustible', 'galones', 'precio_galon', 'total'])
            ->map(fn ($c) => (array) $c)
            ->values()
            ->toArray();
        if (!empty($combustibles)) {
            $resultado['combustibles'] = $combustibles;
        }

        $cumpleanos = Cumpleano::where('nombre', 'like', "%$nombre%")
            ->limit(5)
            ->get(['fecha', 'nombre', 'parentesco'])
            ->map(fn ($c) => $c->toArray())
            ->values()
            ->toArray();
        if (!empty($cumpleanos)) {
            $resultado['cumpleanos'] = $cumpleanos;
        }

        if (empty($resultado)) {
            $rango = '';
            if ($desde || $hasta) {
                $rango = " entre " . ($desde ?: 'inicio') . " y " . ($hasta ?: 'hoy');
            }
            return ['mensaje' => "No se encontró información de \"$nombre\" en el sistema" . $rango . '.'];
        }

        return $resultado;
    }

    protected function normalizarSalidaLinea(string $texto): string
    {
        $etiquetas = [
            'Nombre completo', 'Apellido paterno', 'Apellido materno', 'Fecha de nacimiento',
            'Grado de instrucción', 'Estado civil', 'Razón social', 'N° chasis / VIN',
            'Formatos disponibles', 'Proveedor', 'Título', 'Duración', 'Canal',
            'DNI', 'RUC', 'Nombres', 'Edad', 'Sexo', 'Distrito', 'Provincia', 'Departamento',
            'Dirección', 'Padre', 'Madre', 'Estado', 'Condición', 'Teléfono', 'Marca',
            'Modelo', 'Año', 'Color', 'Clase', 'Uso', 'Asientos', 'Motor', 'Propietario', 'Placa',
        ];
        usort($etiquetas, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $patron = '/\s+(?=(?:' . implode('|', array_map(fn ($l) => preg_quote($l, '/'), $etiquetas)) . '):\s?)/u';
        $texto = preg_replace('/\s+(?=•)/u', "\n", $texto);
        $texto = preg_replace('/\s+(?=\d+\.\s)/u', "\n", $texto);
        $texto = preg_replace($patron, "\n", $texto);

        return preg_replace('/\n[\s]*[\*\+]\s+/', "\n", $texto);
    }

    protected function consultarHerramientaPanel(array $args): array
    {
        $herramientas = [
            'consultadni' => ['label' => 'Simple', 'input' => 'dni'],
            'kmente' => ['label' => 'Búsqueda por DNI', 'input' => 'dni'],
            'telefonos' => ['label' => 'Teléfonos', 'input' => 'dni'],
            'sunarp' => ['label' => 'Sunarp', 'input' => 'dni'],
            'reniec' => ['label' => 'Reniec', 'input' => 'dni'],
            'ficha-reniec' => ['label' => 'Ficha Reniec', 'input' => 'dni'],
            'busqueda-nombres' => ['label' => 'Búsqueda por nombres', 'input' => 'name'],
            'dni-virtual' => ['label' => 'DNI Virtual', 'input' => 'dni'],
            'arbol-genealogico' => ['label' => 'Árbol genealógico', 'input' => 'dni'],
            'reconocimiento-facial' => ['label' => 'Reconocimiento facial', 'input' => 'dni'],
            'justicia' => ['label' => 'Justicia', 'input' => 'dni'],
            'sentinel' => ['label' => 'Sentinel', 'input' => 'dni'],
            'vehiculo' => ['label' => 'Vehículo', 'input' => 'plate'],
            'siguele-plus' => ['label' => 'Síguelo Plus', 'input' => 'dni'],
            'actas' => ['label' => 'Actas', 'input' => 'dni'],
            'doxing' => ['label' => 'Doxing', 'input' => 'dni'],
            'persona-plus' => ['label' => 'Persona Plus', 'input' => 'dni'],
            'sunat' => ['label' => 'Sunat', 'input' => 'ruc'],
        ];

        $herramienta = trim($args['herramienta'] ?? '');
        $info = $herramientas[$herramienta] ?? null;
        if (!$info) {
            return ['mensaje' => "Herramienta desconocida: \"$herramienta\". Disponibles: " . implode(', ', array_keys($herramientas)) . '.'];
        }

        $documento = trim($args['documento'] ?? '');
        $nombres = trim($args['nombres'] ?? '');
        $inputType = $info['input'];

        if ($inputType === 'dni' && !preg_match('/^\d{8}$/', $documento)) {
            return ['mensaje' => "DNI inválido: \"$documento\". Debe tener 8 dígitos."];
        }
        if ($inputType === 'ruc' && !preg_match('/^\d{11}$/', $documento)) {
            return ['mensaje' => "RUC inválido: \"$documento\". Debe tener 11 dígitos."];
        }
        if ($inputType === 'plate' && !preg_match('/^[A-Za-z0-9\-]{3,10}$/', $documento)) {
            return ['mensaje' => "Placa inválida: \"$documento\". Formato: 3 a 10 caracteres alfanuméricos (ej. ABC-123)."];
        }
        if ($inputType === 'name' && mb_strlen($nombres) < 3) {
            return ['mensaje' => 'Indica los nombres a buscar (mínimo 3 caracteres).'];
        }

        try {
            $service = app(\App\Services\DniConsultaService::class);
            $data = $service->consultarHerramienta($herramienta, $documento, $nombres);
            if (!$data && $herramienta === 'consultadni') {
                $data = $service->consultarHerramienta('kmente', $documento, '');
            }
        } catch (\Exception $e) {
            return ['mensaje' => 'Error de conexión: ' . $e->getMessage()];
        }

        if (!$data) {
            $buscado = $inputType === 'name' ? $nombres : $documento;
            return ['mensaje' => "No se encontraron resultados con $herramienta" . ($buscado ? " para \"$buscado\"" : '') . '.'];
        }

        $data = array_filter($data, fn ($v, $k) => !in_array(strtolower($k), ['foto', 'foto_base64', 'imagen', 'imagen_base64', 'huella', 'firma'])
            && (is_string($v) ? strlen($v) <= 300 : true), ARRAY_FILTER_USE_BOTH);

        $nombreMostrar = $data['nombre_completo'] ?? $data['razon_social'] ?? $data['nombre'] ?? $documento ?: $nombres;

        \App\Models\ConsultaHistorial::create([
            'user_id' => auth()->id(),
            'tipo' => strtoupper($info['label']),
            'documento' => $documento ?: $nombres,
            'resultado_json' => $data,
            'nombre_mostrar' => $nombreMostrar,
        ]);

        return $this->curarResultadoConsulta($data);
    }

    protected function curarResultadoConsulta(array $data): array
    {
        $campos = [
            ['Nombre completo', ['nombre_completo', 'nombreCompleto']],
            ['DNI', ['dni', 'nuDni']],
            ['RUC', ['ruc', 'numero_documento']],
            ['Razón social', ['razon_social', 'razonSocial']],
            ['Placa', ['placa', 'numero_placa']],
            ['Nombres', ['nombres', 'preNombres']],
            ['Apellido paterno', ['apellido_paterno', 'apePaterno']],
            ['Apellido materno', ['apellido_materno', 'apeMaterno']],
            ['Fecha de nacimiento', ['fecha_nacimiento', 'feNacimiento']],
            ['Edad', ['nuEdad', 'edad']],
            ['Sexo', ['sexo', 'genero']],
            ['Estado civil', ['estadoCivil', 'estado_civil']],
            ['Grado de instrucción', ['gradoInstruccion']],
            ['Distrito', ['distrito', 'distDireccion']],
            ['Provincia', ['provincia', 'provDireccion']],
            ['Departamento', ['departamento', 'depaDireccion']],
            ['Dirección', ['direccion', 'Direccion', 'desDireccion']],
            ['Padre', ['nomPadre', 'nombre_padre']],
            ['Madre', ['nomMadre', 'nombre_madre']],
            ['Estado', ['estado', 'Estado', 'estado_vehicular']],
            ['Condición', ['condicion', 'Condicion']],
            ['Teléfono', ['telefono', 'Telefono']],
            ['Marca', ['marca', 'Marca']],
            ['Modelo', ['modelo', 'Modelo']],
            ['Año', ['anio', 'anho', 'anno', 'anio_fabricacion', 'anio_modelo']],
            ['Color', ['color', 'Color']],
            ['Clase', ['clase', 'Clase']],
            ['Uso', ['uso', 'Uso']],
            ['Asientos', ['asientos', 'numero_asientos', 'num_asientos']],
            ['Motor', ['motor', 'Motor', 'nro_motor']],
            ['N° chasis / VIN', ['vin', 'chasis', 'nro_vin', 'nro_chasis']],
            ['Propietario', ['propietario', 'titular', 'nombre_titular']],
        ];

        $limpio = [];
        foreach ($campos as [$etiqueta, $claves]) {
            $valor = null;
            foreach ($claves as $clave) {
                if (array_key_exists($clave, $data) && $data[$clave] !== null && $data[$clave] !== '') {
                    $valor = $data[$clave];
                    break;
                }
            }
            if ($valor !== null && is_scalar($valor)) {
                $limpio[$etiqueta] = $valor;
            }
        }

        if (!isset($limpio['Nombre completo']) && isset($limpio['Nombres'])) {
            $completo = trim(($limpio['Nombres'] ?? '') . ' ' . ($limpio['Apellido paterno'] ?? '') . ' ' . ($limpio['Apellido materno'] ?? ''));
            if ($completo !== '') {
                $limpio = ['Nombre completo' => preg_replace('/\s+/', ' ', $completo)] + $limpio;
            }
        }

        if (empty($limpio)) {
            return $data;
        }

        if (isset($data['_proveedor'])) {
            $limpio['Proveedor'] = $data['_proveedor'];
        }

        return $limpio;
    }

    protected function generarPdf(array $args): array
    {
        $contenido = trim($args['contenido'] ?? '');
        if ($contenido === '') {
            return ['mensaje' => 'Indica el contenido del documento a generar.'];
        }

        $titulo = trim($args['titulo'] ?? '');
        if ($titulo !== '') {
            $contenido = '<h1>' . e($titulo) . '</h1>' . $contenido;
        }

        try {
            if (!is_dir(storage_path('app/temp/mpdf'))) mkdir(storage_path('app/temp/mpdf'), 0755, true);
            $pdf = new \Mpdf\Mpdf(['tempDir' => storage_path('app/temp/mpdf')]);
            $pdf->WriteHTML($contenido);
            if (!is_dir(storage_path('app/temp/pdf'))) mkdir(storage_path('app/temp/pdf'), 0755, true);
            $outName = 'document_' . uniqid() . '.pdf';
            $outPath = storage_path('app/temp/pdf/' . $outName);
            $pdf->Output($outPath, \Mpdf\Output\Destination::FILE);
            return ['mensaje' => 'PDF generado exitosamente: ' . $outName, 'archivo' => $outName, 'ruta' => $outPath, 'url' => route('olimpo.download-pdf', $outName)];
        } catch (\Exception $e) {
            return ['mensaje' => 'Error al generar el PDF: ' . $e->getMessage()];
        }
    }

    protected function infoVideo(array $args): array
    {
        $url = trim($args['url'] ?? '');
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['mensaje' => 'Indica una URL de video válida.'];
        }

        $py = 'C:\Python314\python.exe';
        $wrapper = base_path('yt_dlp_wrapper.py');
        $cmd = sprintf('"%s" "%s" --no-download --dump-json %s 2>&1', $py, $wrapper, escapeshellarg($url));
        $output = shell_exec($cmd);

        if (!$output) {
            return ['mensaje' => 'No se pudo obtener información del video. Verifica la URL.'];
        }

        $json = '';
        foreach (explode("\n", trim($output)) as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && $trimmed[0] === '{' && !$json) {
                $json = $trimmed;
            }
        }
        if (!$json) {
            return ['mensaje' => 'Error: no se pudo obtener información del video.'];
        }

        $data = json_decode($json, true);
        if (!$data) {
            return ['mensaje' => 'Error al procesar la información del video.'];
        }

        $duracion = '';
        if (isset($data['duration'])) {
            $mins = floor($data['duration'] / 60);
            $secs = $data['duration'] % 60;
            $duracion = $mins . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
        }

        $formatos = [];
        if (isset($data['formats'])) {
            foreach ($data['formats'] as $f) {
                if (isset($f['format_note'], $f['ext'])) {
                    $formatos[] = [
                        'nota' => $f['format_note'],
                        'ext' => $f['ext'],
                        'tamano' => $f['filesize'] ?? null,
                    ];
                }
            }
        }

        return [
            'titulo' => $data['title'] ?? 'Sin título',
            'duracion' => $duracion,
            'canal' => $data['uploader'] ?? $data['channel'] ?? '',
            'formatos' => array_slice($formatos, 0, 10),
        ];
    }

    protected function descargarVideo(array $args): array
    {
        $url = trim($args['url'] ?? '');
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['mensaje' => 'Indica una URL de video válida.'];
        }

        $formato = trim($args['formato'] ?? 'mp4');
        if (!in_array($formato, ['mp4', 'webm', 'mp3', 'm4a'])) {
            $formato = 'mp4';
        }
        $calidad = trim($args['calidad'] ?? 'best');
        if (!in_array($calidad, ['best', '1080p', '720p', '480p', '360p', 'worst'])) {
            $calidad = 'best';
        }

        $outDir = storage_path('app/temp/downloads');
        if (!is_dir($outDir)) mkdir($outDir, 0755, true);

        $formatMap = [
            'mp4' => 'best[ext=mp4]/best',
            'webm' => 'best[ext=webm]/best',
            'mp3' => 'bestaudio/best',
            'm4a' => 'bestaudio[ext=m4a]/bestaudio',
        ];
        $format = $formatMap[$formato];

        $qualityMap = [
            'best' => '',
            '1080p' => '[height<=1080]',
            '720p' => '[height<=720]',
            '480p' => '[height<=480]',
            '360p' => '[height<=360]',
            'worst' => '[height<=144]',
        ];
        $qual = $qualityMap[$calidad];
        if ($qual && $formato !== 'mp3' && $formato !== 'm4a') {
            $format = preg_replace('/^(bestvideo|best)/', '$1' . $qual, $format);
        }

        $py = 'C:\Python314\python.exe';
        $wrapper = base_path('yt_dlp_wrapper.py');
        $prefix = uniqid('dl_');
        $outTemplate = $outDir . '\\' . $prefix . '_%(id)s.%(ext)s';
        $cmd = sprintf('"%s" "%s" %s -f "%s" -o "%s" --no-playlist --no-cache-dir --no-mtime 2>&1', $py, $wrapper, escapeshellarg($url), $format, $outTemplate);
        $result = shell_exec($cmd);

        $files = glob($outDir . '\\' . $prefix . '_*');
        if ($files) {
            $dlFile = $files[0];
            $newName = basename($dlFile);
            $size = number_format(filesize($dlFile) / 1024 / 1024, 1);
            return [
                'mensaje' => 'Descarga completada: ' . $newName . ' (' . $size . ' MB)',
                'archivo' => $newName,
                'ruta' => $outDir . '\\' . $newName,
                'url' => route('olimpo.download-video', $newName),
            ];
        }

        $lines = explode("\n", trim((string) $result));
        return ['mensaje' => 'Error en la descarga. ' . (end($lines) ?: 'sin respuesta del comando')];
    }

    protected function registrarCumpleano(array $args): array
    {
        $nombre = trim($args['nombre'] ?? '');
        $fecha = trim($args['fecha'] ?? '');
        $parentesco = isset($args['parentesco']) && trim($args['parentesco']) !== '' ? trim($args['parentesco']) : null;
        $dni = isset($args['dni']) && trim($args['dni']) !== '' ? trim($args['dni']) : null;

        if ($nombre === '' || $fecha === '') {
            return ['mensaje' => 'Faltan datos: nombre y fecha (dd/mm) son obligatorios para registrar un cumpleaños.'];
        }

        if (!preg_match('/^\d{2}\/\d{2}$/', $fecha)) {
            return ['mensaje' => "Fecha inválida: \"$fecha\". Usa el formato dd/mm (ej. 01/08)."];
        }
        [$d, $m] = explode('/', $fecha);
        if ((int) $d < 1 || (int) $d > 31 || (int) $m < 1 || (int) $m > 12) {
            return ['mensaje' => "Fecha inválida: \"$fecha\". El día va de 01 a 31 y el mes de 01 a 12."];
        }
        if ($dni !== null && !preg_match('/^\d{8}$/', $dni)) {
            return ['mensaje' => "DNI inválido: \"$dni\". Debe tener 8 dígitos."];
        }

        $yaExiste = Cumpleano::where('fecha', $fecha)
            ->whereRaw('UPPER(nombre) = ?', [strtoupper(trim($nombre))])
            ->exists();
        if ($yaExiste) {
            return ['mensaje' => "Ya existe un cumpleaños de $nombre el $fecha. No se duplicó."];
        }

        Cumpleano::create([
            'fecha' => $fecha,
            'nombre' => ucwords(strtolower($nombre)),
            'detalles' => '',
            'parentesco' => $parentesco,
            'dni' => $dni,
            'recordatorio_activo' => true,
            'recordatorio_hora' => '07:30:00',
        ]);
        cache()->forget('cumpleanos_list');

        return ['mensaje' => "Cumpleaños de $nombre registrado el $fecha."];
    }

    protected function registrarPalm(array $args): array
    {
        $nombre = trim($args['nombre'] ?? '');
        $dni = trim($args['dni'] ?? '');

        if ($nombre === '' || $dni === '') {
            return ['mensaje' => 'Faltan datos: nombre y DNI son obligatorios para registrar un registro PALM.'];
        }
        if (!preg_match('/^\d{8}$/', $dni)) {
            return ['mensaje' => "DNI inválido: \"$dni\". Debe tener 8 dígitos."];
        }

        \App\Models\ConsultaHistorial::create([
            'user_id' => auth()->id(),
            'tipo' => 'BÚSQUEDA POR DNI',
            'documento' => $dni,
            'resultado_json' => ['nombre' => ucwords(strtolower($nombre)), 'dni' => $dni, 'origen' => 'chat-ia'],
            'nombre_mostrar' => ucwords(strtolower($nombre)),
        ]);

        return ['mensaje' => "Registro PALM de $nombre (DNI $dni) agregado."];
    }

    public function render()
    {
        return view('livewire.chat-ia');
    }
}
