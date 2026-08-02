<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

class MasHerramientas extends Component
{
    use WithFileUploads;

    public $tab = 'pdf';

    public $pdfFiles = [];
    public $pdfAction = 'merge';
    public $pdfHtml = '';
    public $pdfOutput = '';
    public $pdfFilePaths = [];
    public $pdfFileNames = [];

    public $downloadUrl = '';
    public $downloadFormat = 'mp4';
    public $downloadQuality = 'best';
    public $downloadInfo = null;
    public $downloadOutput = '';
    public $downloadFile = '';

    public $convertTool = '';
    public $convertOutput = '';
    public $convertResultFile = '';
    public $convertResultFiles = [];
    public $convertFileName = '';
    public $convertFileMime = '';
    public $convertTempPath = '';
    public $convertPreviewUrl = '';

    public function mount()
    {
        $this->initConsultas();
    }

    public function selectConvertTool($tool)
    {
        $this->convertTool = $tool;
        $this->convertOutput = '';
        $this->convertResultFile = '';
        $this->convertResultFiles = [];
        $this->convertFileName = '';
        $this->convertFileMime = '';
        $this->convertTempPath = '';
        $this->convertPreviewUrl = '';
    }

    public function getConvertToolInfoProperty()
    {
        $tools = $this->getConvertTools();
        return $tools[$this->convertTool] ?? null;
    }

    private function getConvertTools(): array
    {
        return [
            'word_to_pdf' => ['name' => 'WORD a PDF', 'desc' => 'Convierte documentos Word a PDF', 'accept' => '.docx,.doc', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            'pdf_to_word' => ['name' => 'PDF a WORD', 'desc' => 'Convierte PDF a Word editable', 'accept' => '.pdf', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            'jpg_to_pdf' => ['name' => 'Imagen a PDF', 'desc' => 'Convierte JPG/PNG a PDF', 'accept' => '.jpg,.jpeg,.png,.gif,.webp', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'pdf_to_jpg' => ['name' => 'PDF a JPG', 'desc' => 'Convierte páginas PDF a imágenes', 'accept' => '.pdf', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'txt_to_pdf' => ['name' => 'TXT a PDF', 'desc' => 'Convierte texto plano a PDF', 'accept' => '.txt', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            'pdf_to_txt' => ['name' => 'PDF a TXT', 'desc' => 'Extrae texto de PDF', 'accept' => '.pdf', 'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
        ];
    }

    public function render()
    {
        $resultado = $this->resultadoKey ? Cache::get($this->resultadoKey) : null;

        return view('livewire.olimpo.mas-herramientas', ['resultado' => $resultado])
            ->layout('layouts.olimpo', ['title' => 'Más Herramientas']);
    }

    public function updatedPdfAction()
    {
        $this->pdfFilePaths = [];
        $this->pdfFileNames = [];
        $this->pdfOutput = '';
    }

    public function processPdf()
    {
        if ($this->pdfAction === 'merge') {
            if (!$this->pdfFilePaths || count($this->pdfFilePaths) === 0) {
                $this->pdfOutput = 'Selecciona al menos un PDF.';
                return;
            }
            try {
                $pdf = new Mpdf(['tempDir' => storage_path('app/temp/mpdf')]);
                if (!is_dir(storage_path('app/temp/mpdf'))) mkdir(storage_path('app/temp/mpdf'), 0755, true);

                foreach ($this->pdfFilePaths as $path) {
                    $fullPath = Storage::disk('local')->path($path);
                    if (!file_exists($fullPath)) continue;
                    $pages = $pdf->setSourceFile($fullPath);
                    for ($i = 1; $i <= $pages; $i++) {
                        $tpl = $pdf->importPage($i);
                        if ($i > 1 || $pdf->page > 0) $pdf->AddPage();
                        $pdf->useTemplate($tpl);
                    }
                }

                if (!is_dir(storage_path('app/temp/pdf'))) mkdir(storage_path('app/temp/pdf'), 0755, true);
                $outName = 'merged_' . uniqid() . '.pdf';
                $outPath = storage_path('app/temp/pdf/' . $outName);
                $pdf->Output($outPath, \Mpdf\Output\Destination::FILE);
                $this->pdfOutput = 'PDF combinado exitosamente: ' . $outName;
            } catch (\Exception $e) {
                $this->pdfOutput = 'Error: ' . $e->getMessage();
            }

        } elseif ($this->pdfAction === 'html') {
            if (!$this->pdfHtml) {
                $this->pdfOutput = 'Escribe el contenido HTML.';
                return;
            }
            try {
                $pdf = new Mpdf(['tempDir' => storage_path('app/temp/mpdf')]);
                $pdf->WriteHTML($this->pdfHtml);
                if (!is_dir(storage_path('app/temp/pdf'))) mkdir(storage_path('app/temp/pdf'), 0755, true);
                $outName = 'document_' . uniqid() . '.pdf';
                $outPath = storage_path('app/temp/pdf/' . $outName);
                $pdf->Output($outPath, \Mpdf\Output\Destination::FILE);
                $this->pdfOutput = 'PDF generado exitosamente: ' . $outName;
            } catch (\Exception $e) {
                $this->pdfOutput = 'Error: ' . $e->getMessage();
            }
        }
    }

    public function updatedDownloadUrl()
    {
        $this->downloadFile = '';
        $this->downloadInfo = null;
        $this->downloadOutput = '';
    }

    public function fetchVideoInfo()
    {
        if (!$this->downloadUrl) {
            $this->downloadOutput = 'Ingresa una URL de video.';
            return;
        }
        $this->downloadInfo = null;
        $this->downloadOutput = '';
        $this->downloadFile = '';

        $url = escapeshellarg($this->downloadUrl);
        $py = 'C:\Python314\python.exe';
        $wrapper = base_path('yt_dlp_wrapper.py');
        $cmd = sprintf('"%s" "%s" --no-download --dump-json %s 2>&1', $py, $wrapper, $url);
        $output = shell_exec($cmd);

        if (!$output) {
            $this->downloadOutput = 'No se pudo obtener información del video. Verifica la URL.';
            return;
        }

        $lines = explode("\n", trim($output));
        $json = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && $trimmed[0] === '{' && !$json) {
                $json = $trimmed;
            }
        }
        if (!$json) {
            $last = $lines ? end($lines) : $output;
            $this->downloadOutput = 'Error: ' . ($last ?: 'sin respuesta del comando');
            return;
        }

        $data = json_decode($json, true);
        if (!$data) {
            $this->downloadOutput = 'Error al procesar la información del video.';
            return;
        }

        $duration = '';
        if (isset($data['duration'])) {
            $mins = floor($data['duration'] / 60);
            $secs = $data['duration'] % 60;
            $duration = $mins . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
        }

        $formats = [];
        if (isset($data['formats'])) {
            foreach ($data['formats'] as $f) {
                if (isset($f['format_note'], $f['ext'])) {
                    $formats[] = [
                        'note' => $f['format_note'],
                        'ext' => $f['ext'],
                        'size' => $f['filesize'] ?? null,
                    ];
                }
            }
        }

        $this->downloadInfo = [
            'title' => $data['title'] ?? 'Sin título',
            'duration' => $duration,
            'uploader' => $data['uploader'] ?? $data['channel'] ?? '',
            'thumbnail' => $data['thumbnail'] ?? null,
            'formats' => $formats,
        ];
        $this->downloadOutput = 'Información obtenida correctamente.';
    }

    public function downloadVideo()
    {
        if (!$this->downloadUrl) {
            $this->downloadOutput = 'Ingresa una URL de video.';
            return;
        }

        $url = escapeshellarg($this->downloadUrl);
        $outDir = storage_path('app/temp/downloads');
        if (!is_dir($outDir)) mkdir($outDir, 0755, true);

        $formatMap = [
            'mp4' => 'best[ext=mp4]/best',
            'webm' => 'best[ext=webm]/best',
            'mp3' => 'bestaudio/best',
            'm4a' => 'bestaudio[ext=m4a]/bestaudio',
        ];
        $format = $formatMap[$this->downloadFormat] ?? 'best';

        $qualityMap = [
            'best' => '',
            '1080p' => '[height<=1080]',
            '720p' => '[height<=720]',
            '480p' => '[height<=480]',
            '360p' => '[height<=360]',
            'worst' => '[height<=144]',
        ];
        $qual = $qualityMap[$this->downloadQuality] ?? '';

        if ($qual && $this->downloadFormat !== 'mp3' && $this->downloadFormat !== 'm4a') {
            $format = preg_replace('/^(bestvideo|best)/', '$1' . $qual, $format);
        }

        $py = 'C:\Python314\python.exe';
        $wrapper = base_path('yt_dlp_wrapper.py');
        $prefix = uniqid('dl_');
        $outTemplate = $outDir . '\\' . $prefix . '_%(id)s.%(ext)s';
        $cmd = sprintf('"%s" "%s" %s -f "%s" -o "%s" --no-playlist --no-cache-dir --no-mtime 2>&1', $py, $wrapper, $url, $format, $outTemplate);

        $this->downloadOutput = 'Descargando... esto puede tomar varios minutos.';
        $result = shell_exec($cmd);

        $files = glob($outDir . '\\' . $prefix . '_*');
        if ($files) {
            $dlFile = $files[0];
            $ext = strtolower(pathinfo($dlFile, PATHINFO_EXTENSION));
            $title = $this->downloadInfo['title'] ?? $baseName;
            $safeTitle = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $title);
            $safeTitle = preg_replace('/\s+/', '_', trim($safeTitle));
            $safeTitle = preg_replace('/[_\-.]+/', '_', $safeTitle);
            $safeTitle = trim($safeTitle, '_-');
            $safeTitle = $safeTitle ?: 'video';
            $newName = $safeTitle . '_olimpo.' . $ext;
            rename($dlFile, $outDir . '\\' . $newName);
            $size = number_format(filesize($outDir . '\\' . $newName) / 1024 / 1024, 1);
            $this->downloadFile = $newName;
            $this->downloadOutput = 'Descarga completada: ' . $newName . ' (' . $size . ' MB)';
            $this->downloadInfo = null;
        } else {
            $lines = explode("\n", trim($result));
            $last = end($lines);
            $this->downloadOutput = 'Error en la descarga. ' . ($last ?: 'sin respuesta del comando');
        }
    }

    private function convertImage($fullPath, $ext, $outPath, $outExt)
    {
        $fn = null;
        switch ($ext) {
            case 'jpg': case 'jpeg': $fn = @imagecreatefromjpeg($fullPath); break;
            case 'png': $fn = @imagecreatefrompng($fullPath); break;
            case 'gif': $fn = @imagecreatefromgif($fullPath); break;
            case 'webp': $fn = @imagecreatefromwebp($fullPath); break;
        }
        if (!$fn) throw new \Exception('No se pudo leer la imagen.');
        $ok = false;
        switch ($outExt) {
            case 'jpg': case 'jpeg': $ok = imagejpeg($fn, $outPath, 90); break;
            case 'png': $ok = imagepng($fn, $outPath, 6); break;
            case 'gif': $ok = imagegif($fn, $outPath); break;
            case 'webp': $ok = imagewebp($fn, $outPath, 85); break;
        }
        imagedestroy($fn);
        if (!$ok) throw new \Exception('No se pudo escribir la imagen convertida.');
    }

    public function processConvert()
    {
        if (!$this->convertTempPath) {
            $this->convertOutput = 'Selecciona un archivo.';
            return;
        }
        if (!$this->convertTool) {
            $this->convertOutput = 'Selecciona una herramienta de conversión.';
            return;
        }
        $this->convertResultFile = '';
        $this->convertResultFiles = [];
        $tempBase = storage_path('app/temp');
        $outDir = $tempBase . '/convert';
        $mpdfTemp = $tempBase . '/mpdf';
        $phpWordTemp = $tempBase . '/phpword';
        foreach ([$outDir, $mpdfTemp, $phpWordTemp] as $dir) {
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
        }
        Settings::setTempDir($phpWordTemp);
        try {
            $fullPath = Storage::path($this->convertTempPath);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $baseName = pathinfo($this->convertFileName ?: 'file', PATHINFO_FILENAME);

            switch ($this->convertTool) {
                case 'word_to_pdf':
                    if (!in_array($ext, ['docx', 'doc'])) {
                        $this->convertOutput = 'Selecciona un archivo Word (.docx/.doc).';
                        break;
                    }
                    Settings::setPdfRendererPath($tempBase);
                    Settings::setPdfRendererName('MPDF');
                    $phpWord = IOFactory::load($fullPath);
                    $outName = $baseName . '_olimpo.pdf';
                    $writer = IOFactory::createWriter($phpWord, 'PDF');
                    $writer->save($outDir . '/' . $outName);
                    $this->convertResultFile = $outName;
                    $this->convertOutput = 'Convertido a PDF correctamente.';
                    break;

                case 'pdf_to_word':
                    if ($ext !== 'pdf') {
                        $this->convertOutput = 'Selecciona un archivo PDF.';
                        break;
                    }
                    $text = $this->extractPdfText($fullPath);
                    $phpWord = new PhpWord();
                    $section = $phpWord->addSection();
                    $section->addText($text);
                    $outName = $baseName . '_olimpo.docx';
                    $writer = IOFactory::createWriter($phpWord, 'Word2007');
                    $writer->save($outDir . '/' . $outName);
                    $this->convertResultFile = $outName;
                    $this->convertOutput = 'Convertido a Word correctamente.';
                    break;

                case 'jpg_to_pdf':
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $this->convertOutput = 'Selecciona un archivo de imagen.';
                        break;
                    }
                    if (!file_exists($fullPath)) {
                        $this->convertOutput = 'Error: archivo no encontrado';
                        break;
                    }
                    $imageData = file_get_contents($fullPath);
                    if ($imageData === false) {
                        $this->convertOutput = 'Error: no se pudo leer la imagen';
                        break;
                    }
                    $b64 = base64_encode($imageData);
                    $mimeMap = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'webp' => 'webp'];
                    $mime = 'image/' . ($mimeMap[$ext] ?? 'jpeg');
                    $pdf = new Mpdf(['tempDir' => $mpdfTemp]);
                    $pdf->AddPage();
                    $pdf->Image('data:' . $mime . ';base64,' . $b64, 10, 10, 190, 0, $ext);
                    $outName = $baseName . '_olimpo.pdf';
                    $pdf->Output($outDir . '/' . $outName, \Mpdf\Output\Destination::FILE);
                    $this->convertResultFile = $outName;
                    $this->convertOutput = 'Convertido a PDF correctamente.';
                    break;

                case 'pdf_to_jpg':
                    if ($ext !== 'pdf') {
                        $this->convertOutput = 'Selecciona un archivo PDF.';
                        break;
                    }
                    $gs = $this->findGhostscript();
                    if (!$gs) {
                        $this->convertOutput = 'Ghostscript no está instalado.';
                        break;
                    }
                    $outPrefix = $outDir . '/' . $baseName . '_olimpo_page_%d.jpg';
                    $cmd = sprintf('"%s" -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -r150 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -sOutputFile="%s" "%s" 2>&1', $gs, $outPrefix, $fullPath);
                    shell_exec($cmd);
                    $files = glob($outDir . '/' . $baseName . '_olimpo_page_*.jpg');
                    if (count($files) === 0) {
                        $this->convertOutput = 'No se pudieron generar las imágenes.';
                        break;
                    }
                    if (count($files) === 1) {
                        $this->convertResultFile = basename($files[0]);
                        $this->convertOutput = 'Imagen generada correctamente.';
                    } else {
                        $this->convertResultFiles = array_map('basename', $files);
                        $this->convertOutput = count($files) . ' imágenes generadas correctamente.';
                    }
                    break;

                case 'txt_to_pdf':
                    if ($ext !== 'txt') {
                        $this->convertOutput = 'Selecciona un archivo de texto (.txt).';
                        break;
                    }
                    $content = '<pre>' . e(file_get_contents($fullPath)) . '</pre>';
                    $pdf = new Mpdf(['tempDir' => $mpdfTemp]);
                    $pdf->WriteHTML($content);
                    $outName = $baseName . '_olimpo.pdf';
                    $pdf->Output($outDir . '/' . $outName, \Mpdf\Output\Destination::FILE);
                    $this->convertResultFile = $outName;
                    $this->convertOutput = 'Convertido a PDF correctamente.';
                    break;

                case 'pdf_to_txt':
                    if ($ext !== 'pdf') {
                        $this->convertOutput = 'Selecciona un archivo PDF.';
                        break;
                    }
                    $text = $this->extractPdfText($fullPath);
                    $outName = $baseName . '_olimpo.txt';
                    file_put_contents($outDir . '/' . $outName, $text);
                    $this->convertResultFile = $outName;
                    $this->convertOutput = 'Texto extraído correctamente.';
                    break;

                default:
                    $this->convertOutput = 'Herramienta no soportada.';
            }
        } catch (\Exception $e) {
            $this->convertOutput = 'Error: ' . $e->getMessage();
        }
    }

    private function extractPdfText($fullPath): string
    {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception('Librería PDF Parser no disponible.');
        }
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();
        return trim($text) ?: 'No se pudo extraer texto del PDF.';
    }

    private function findGhostscript(): ?string
    {
        $candidates = [
            'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.07.0\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.06.0\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }
        $fromWhere = trim((string) shell_exec('where gswin64c 2>nul'));
        if ($fromWhere && file_exists($fromWhere)) return $fromWhere;
        return null;
    }

    // ─── DNI / RUC Consultas ───

    public string $documento = '';
    public string $tipo = 'dni';
    public string $herramienta = 'consultadni';
    public string $modo = 'simple';
    public ?string $resultadoKey = null;
    public array $historial = [];
    public bool $showModal = false;
    public string $modalTitle = '';
    public string $searchTerm = '';
    public array $herramientas = [];
    public array $premiumHerramientas = [];

    private int $historialLimit = 10;

    public function initConsultas()
    {
        $this->herramientas = $this->getHerramientas();
        $this->premiumHerramientas = collect($this->herramientas)
            ->where('id', '!=', 'consultadni')
            ->sortBy(function ($item) {
                if ($item['id'] === 'kmente') return '0';
                if ($item['id'] === 'busqueda-nombres') return '1';
                return $item['label'];
            })
            ->values()
            ->toArray();
        $this->limpiarResultado();
        $this->showModal = false;
        $this->cargarHistorial();
    }

    private function guardarResultado(array $data): void
    {
        $key = 'consulta_resultado_' . auth()->id();
        Cache::put($key, $data, now()->addHours(2));
        $this->resultadoKey = $key;
    }

    private function limpiarResultado(): void
    {
        if ($this->resultadoKey) {
            Cache::forget($this->resultadoKey);
        }
        $this->resultadoKey = null;
    }

    public function cambiarModo(string $modo)
    {
        $this->modo = $modo;
        if ($modo === 'simple') {
            $this->seleccionarHerramienta('consultadni');
        } else {
            $first = $this->premiumHerramientas[0] ?? null;
            if ($first) {
                $this->seleccionarHerramienta($first['id']);
            }
        }
    }

    public function cargarHistorial()
    {
        $this->historial = \App\Models\ConsultaHistorial::where('user_id', auth()->id())
            ->latest()
            ->take($this->historialLimit)
            ->get(['id', 'tipo', 'documento', 'nombre_mostrar', 'created_at'])
            ->toArray();
    }

    public function getHerramientas(): array
    {
        return [
            ['id' => 'consultadni', 'label' => 'Simple', 'input' => 'dni', 'color' => 'bg-slate-500', 'group' => 'Rápido'],
            ['id' => 'kmente', 'label' => 'Búsqueda por DNI', 'input' => 'dni', 'color' => 'bg-amber-500', 'group' => 'Completo'],
            ['id' => 'telefonos', 'label' => 'Teléfonos', 'input' => 'dni', 'color' => 'bg-green-500', 'group' => 'Completo'],
            ['id' => 'sunarp', 'label' => 'Sunarp', 'input' => 'dni', 'color' => 'bg-indigo-500', 'group' => 'Completo'],
            ['id' => 'reniec', 'label' => 'Reniec', 'input' => 'dni', 'color' => 'bg-blue-500', 'group' => 'Completo'],
            ['id' => 'ficha-reniec', 'label' => 'Ficha Reniec', 'input' => 'dni', 'color' => 'bg-blue-600', 'group' => 'Completo'],
            ['id' => 'busqueda-nombres', 'label' => 'Búsqueda por nombres', 'input' => 'name', 'color' => 'bg-cyan-500', 'group' => 'Completo'],
            ['id' => 'dni-virtual', 'label' => 'DNI Virtual', 'input' => 'dni', 'color' => 'bg-teal-500', 'group' => 'Completo'],
            ['id' => 'arbol-genealogico', 'label' => 'Árbol genealógico', 'input' => 'dni', 'color' => 'bg-emerald-500', 'group' => 'Completo'],
            ['id' => 'reconocimiento-facial', 'label' => 'Reconocimiento facial', 'input' => 'dni', 'color' => 'bg-[#5D87FF]', 'group' => 'Completo'],
            ['id' => 'justicia', 'label' => 'Justicia', 'input' => 'dni', 'color' => 'bg-red-500', 'group' => 'Completo'],
            ['id' => 'sentinel', 'label' => 'Sentinel', 'input' => 'dni', 'color' => 'bg-orange-500', 'group' => 'Completo'],
            ['id' => 'vehiculo', 'label' => 'Vehículo', 'input' => 'plate', 'color' => 'bg-rose-500', 'group' => 'Completo'],
            ['id' => 'siguele-plus', 'label' => 'Síguelo Plus', 'input' => 'dni', 'color' => 'bg-fuchsia-500', 'group' => 'Completo'],
            ['id' => 'actas', 'label' => 'Actas', 'input' => 'dni', 'color' => 'bg-pink-500', 'group' => 'Completo'],
            ['id' => 'doxing', 'label' => 'Doxing', 'input' => 'dni', 'color' => 'bg-purple-500', 'group' => 'Completo'],
            ['id' => 'persona-plus', 'label' => 'Persona Plus', 'input' => 'dni', 'color' => 'bg-sky-500', 'group' => 'Completo'],
            ['id' => 'sunat', 'label' => 'Sunat', 'input' => 'ruc', 'color' => 'bg-lime-500', 'group' => 'Completo'],
        ];
    }

    public function seleccionarHerramienta(string $id)
    {
        $this->herramienta = $id;
        $herramienta = collect($this->herramientas)->firstWhere('id', $id);

        if ($id !== 'consultadni') {
            if ($herramienta['input'] === 'ruc') {
                $this->tipo = 'ruc';
            } elseif ($herramienta['input'] === 'dni') {
                $this->tipo = 'dni';
            }
        }

        $this->limpiarResultado();
        $this->showModal = false;
        $this->documento = '';
        $this->searchTerm = '';
    }

    public function consultar()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $isSimple = $this->modo === 'simple';
        $inputType = $isSimple ? $this->tipo : (collect($this->herramientas)->firstWhere('id', $this->herramienta)['input'] ?? 'dni');
        $inputLabel = match ($inputType) {
            'dni' => 'DNI (8 dígitos)',
            'ruc' => 'RUC (11 dígitos)',
            'plate' => 'Placa (ej: ABC-123)',
            'name' => 'Nombres',
            default => 'Documento',
        };

        $rules = match ($inputType) {
            'dni' => ['documento' => 'digits:8'],
            'ruc' => ['documento' => 'digits:11'],
            'plate' => ['documento' => 'regex:/^[A-Za-z0-9\-]{3,10}$/'],
            'name' => ['searchTerm' => 'required|min:3'],
            default => ['documento' => 'required'],
        };

        $this->validate($rules, [
            'documento.digits' => "El campo debe ser $inputLabel",
            'documento.regex' => 'Formato de placa inválido',
            'searchTerm.required' => 'Ingrese términos de búsqueda',
            'searchTerm.min' => 'Mínimo 3 caracteres',
        ]);

        try {
            $service = app(\App\Services\DniConsultaService::class);
            if ($isSimple && $this->tipo === 'ruc') {
                $data = $service->consultarRuc($this->documento);
            } else {
                $data = $service->consultarHerramienta($this->herramienta, $this->documento, $this->searchTerm);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error de conexión: '.$e->getMessage(), type: 'error');
            return;
        }

        if ($data) {
            $this->guardarResultado($data);
            $this->showModal = true;

            $herramientaLabel = collect($this->herramientas)->firstWhere('id', $this->herramienta)['label'] ?? $this->herramienta;
            $this->modalTitle = 'RESULTADO: '.strtoupper($herramientaLabel);

            $nombreMostrar = $data['nombre_completo'] ?? $data['razon_social'] ?? $data['nombre'] ?? $this->documento ?: $this->searchTerm;

            \App\Models\ConsultaHistorial::create([
                'user_id' => auth()->id(),
                'tipo' => strtoupper($herramientaLabel),
                'documento' => $this->documento ?: $this->searchTerm,
                'resultado_json' => $data,
                'nombre_mostrar' => $nombreMostrar,
            ]);

            $this->cargarHistorial();
            $this->dispatch('notify', message: 'Consulta exitosa: '.$nombreMostrar, type: 'success');
        } else {
            $this->guardarResultado(['error' => true]);
            $this->showModal = true;
            $herramientaLabel = collect($this->herramientas)->firstWhere('id', $this->herramienta)['label'] ?? $this->herramienta;
            $this->modalTitle = 'RESULTADO: '.strtoupper($herramientaLabel);
            $this->dispatch('notify', message: 'No se encontraron resultados', type: 'warning');
        }
    }

    public function verResultado($index)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $entry = $this->historial[$index] ?? null;
        if ($entry) {
            $full = \App\Models\ConsultaHistorial::find($entry['id']);
            if ($full?->resultado_json) {
                $this->guardarResultado($full->resultado_json);
            }
            $this->documento = $entry['documento'];
            $this->searchTerm = $entry['documento'];
            $this->modalTitle = 'RESULTADO: '.strtoupper($entry['tipo']);
            $this->showModal = true;
        }
    }

    public function limpiarHistorial()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        \App\Models\ConsultaHistorial::where('user_id', auth()->id())->delete();
        $this->historial = [];
        $this->dispatch('notify', message: 'Historial eliminado.', type: 'success');
    }
}
