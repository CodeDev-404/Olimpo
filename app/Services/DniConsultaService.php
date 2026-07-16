<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DniConsultaService
{
    protected array $herramientasEndpoint = [
        'telefonos' => 'telefonos',
        'sunarp' => 'sunarp',
        'reniec' => 'reniec',
        'ficha-reniec' => 'ficha-reniec',
        'busqueda-nombres' => 'busqueda-nombres',
        'dni-virtual' => 'dni-virtual',
        'arbol-genealogico' => 'arbol-genealogico',
        'reconocimiento-facial' => 'reconocimiento-facial',
        'justicia' => 'justicia',
        'sentinel' => 'sentinel',
        'vehiculo' => 'vehiculo',
        'siguele-plus' => 'siguele-plus',
        'actas' => 'actas',
        'doxing' => 'doxing',
        'persona-plus' => 'persona-plus',
        'sunat' => 'sunat',
    ];

    public function consultarHerramienta(string $herramienta, string $documento = '', string $searchTerm = ''): ?array
    {
        if ($herramienta === 'consultadni') {
            return $this->consultarConsultadni($documento);
        }

        if ($herramienta === 'kmente') {
            return $this->consultarKmente($documento);
        }

        if ($herramienta === 'sunat') {
            return $this->consultarRuc($documento);
        }

        $endpoint = $this->herramientasEndpoint[$herramienta] ?? null;
        if (!$endpoint) {
            return null;
        }

        return $this->consultarApiGenerica($endpoint, $documento, $searchTerm);
    }

    private function consultarApiGenerica(string $endpoint, string $documento = '', string $searchTerm = ''): ?array
    {
        $token = config('services.kmente.token');
        if (! $token) {
            return null;
        }

        $cacheKey = "kmente_{$endpoint}_" . ($documento ?: md5($searchTerm));
        $queryParams = ['token' => $token];

        if ($searchTerm && $endpoint === 'busqueda-nombres') {
            $queryParams['nombres'] = $searchTerm;
        } elseif ($documento) {
            $queryParams['dni'] = $documento;
        } else {
            return null;
        }

        return Cache::remember($cacheKey, 86400, function () use ($endpoint, $queryParams, $documento) {
            $response = Http::withoutVerifying()->timeout(10)->get(
                "https://kmente.com/api/v1/{$endpoint}",
                $queryParams
            );

            if ($response->successful() && $response->json('ok')) {
                $d = $response->json('data') ?? $response->json('resultado') ?? [];
                if (is_array($d)) {
                    $d['_proveedor'] = $endpoint;
                    if (!empty($d['preNombres'])) {
                        $d['nombre_completo'] = trim(($d['preNombres'] ?? '').' '.($d['apePaterno'] ?? '').' '.($d['apeMaterno'] ?? ''));
                    }
                    return $d;
                }
            }

            return null;
        });
    }

    private function consultarConsultadni(string $dni): ?array
    {
        if (strlen($dni) !== 8) return null;

        $apiKey = config('services.consultadni.api_key');
        if (! $apiKey) return null;

        $cacheKey = "consultadni_{$dni}";
        return Cache::remember($cacheKey, 86400, function () use ($dni, $apiKey) {
            $response = Http::withoutVerifying()->timeout(5)->get(
                'https://www.consultadni.com/api/v1/dni/completo',
                ['dni' => $dni, 'api_key' => $apiKey]
            );
            if ($response->successful() && $response->json('estado')) {
                return $response->json('resultado');
            }
            return null;
        });
    }

    private function consultarKmente(string $dni): ?array
    {
        if (strlen($dni) !== 8) return null;

        $token = config('services.kmente.token');
        if (! $token) return null;

        $cacheKey = "kmente_reniec_{$dni}";
        return Cache::remember($cacheKey, 86400, function () use ($dni, $token) {
            $response = Http::withoutVerifying()->timeout(5)->get(
                'https://kmente.com/api/v1/reniec',
                ['token' => $token, 'dni' => $dni]
            );

            if ($response->successful() && $response->json('ok')) {
                $d = $response->json('data');
                if (!$d) return null;

                return array_merge($d, [
                    'nombres' => $d['preNombres'] ?? '',
                    'apellido_paterno' => $d['apePaterno'] ?? '',
                    'apellido_materno' => $d['apeMaterno'] ?? '',
                    'fecha_nacimiento' => $d['feNacimiento'] ?? '',
                    'genero' => ($d['sexo'] ?? '') === 'FEMENINO' ? 'Femenino' : 'Masculino',
                    'nombre_completo' => trim(($d['preNombres'] ?? '').' '.($d['apePaterno'] ?? '').' '.($d['apeMaterno'] ?? '')),
                    '_proveedor' => 'kmente',
                ]);
            }

            return null;
        });
    }

    public function consultarRuc(string $ruc): ?array
    {
        if (strlen($ruc) !== 11) return null;

        $token = config('services.jsonpe.token');
        if (! $token) return null;

        $cacheKey = "ruc_{$ruc}";
        return Cache::remember($cacheKey, 86400, function () use ($ruc, $token) {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post('https://api.json.pe/api/ruc', ['ruc' => $ruc]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('data');
            }
            return null;
        });
    }

    public function forget(string $dni, string $proveedor = 'consultadni'): void
    {
        Cache::forget("{$proveedor}_{$dni}");
        Cache::forget("kmente_reniec_{$dni}");
    }
}
