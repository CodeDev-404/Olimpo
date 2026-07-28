<div x-data="{ tab: 'pdf' }" x-cloak>
    <div class="flex items-center justify-between mb-5">
        <div class="flex gap-1 bg-[#f4f6f9] dark:bg-white/[0.06] rounded-lg p-0.5" role="tablist">
            <button @click="tab = 'pdf'" :class="tab === 'pdf' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">
                <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Editor de PDF
            </button>
            <button @click="tab = 'convert'" :class="tab === 'convert' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">
                <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Convertidor
            </button>
            <button @click="tab = 'download'" :class="tab === 'download' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">
                <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar Video Link
            </button>
            <button @click="tab = 'dni'" :class="tab === 'dni' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">
                <i data-lucide="search-check" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
                DNI / RUC
            </button>
        </div>
    </div>

    {{-- PDF EDITOR TAB --}}
    <div x-show="tab === 'pdf'" class="card">
        <div class="card-body space-y-4">
            <div class="flex gap-2">
                <button wire:click="$set('pdfAction', 'merge')"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $pdfAction === 'merge' ? 'bg-ink-800 text-white shadow-sm' : 'bg-[#f4f6f9] dark:bg-white/[0.06] text-ink-600 dark:text-ink-400 hover:bg-ink-200' }}">
                    Combinar PDFs
                </button>
                <button wire:click="$set('pdfAction', 'html')"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $pdfAction === 'html' ? 'bg-ink-800 text-white shadow-sm' : 'bg-[#f4f6f9] dark:bg-white/[0.06] text-ink-600 dark:text-ink-400 hover:bg-ink-200' }}">
                    HTML a PDF
                </button>
            </div>

            @if($pdfAction === 'merge')
                <div x-data="{ uploading: false, progress: 0, uploadError: '' }">
                    <label class="block text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider mb-2">Archivos PDF</label>
                     <div class="relative border-2 border-dashed border-ink-200 dark:border-ink-600 rounded-lg p-8 text-center hover:border-ink-400 transition-colors"
                          @dragover.prevent="$el.classList.add('border-ink-400')"
                          @dragleave.prevent="$el.classList.remove('border-ink-400')"
                          @drop.prevent="$el.classList.remove('border-ink-400'); window.uploadPdfFiles($event.dataTransfer.files, $wire, $el)">
                         <svg class="w-10 h-10 mx-auto mb-2 text-ink-300 dark:text-ink-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                         </svg>
                         <p class="text-sm text-ink-500 dark:text-ink-400 font-medium pointer-events-none font-display">Arrastra PDFs aquí o haz clic para seleccionar</p>
                         <p class="text-xs text-ink-400 mt-1 pointer-events-none">Selecciona múltiples archivos para combinar</p>
                         <input type="file" accept=".pdf" multiple
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                @change="window.uploadPdfFiles($event.target.files, $wire, $el); $event.target.value = ''">
                      </div>
                    <div x-show="uploading" class="mt-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-2 bg-ink-200 dark:bg-ink-600 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all duration-200"
                                     :style="'width: ' + progress + '%'"></div>
                            </div>
                            <span class="text-xs text-ink-500 font-medium w-10 text-right shrink-0" x-text="Math.round(progress) + '%'"></span>
                        </div>
                    </div>
                    <div x-show="uploadError" class="mt-2 flex items-center gap-2 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 px-3 py-2 rounded-lg">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="uploadError"></span>
                    </div>
                    @if(count($pdfFilePaths) > 0)
                        <div class="mt-3 space-y-1">
                            @foreach($pdfFileNames as $i => $name)
                                <div class="flex items-center gap-2 text-xs text-ink-600 dark:text-ink-400">
                                    <span class="w-5 h-5 rounded-full bg-ink-100 dark:bg-ink-700 flex items-center justify-center text-[10px] font-semibold">{{ $i + 1 }}</span>
                                    <span>{{ $name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div>
                    <label class="block text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider mb-2">Contenido HTML</label>
                    <textarea wire:model="pdfHtml" rows="10" class="input-field w-full font-mono text-sm" placeholder="&lt;h1&gt;Título&lt;/h1&gt;&lt;p&gt;Contenido...&lt;/p&gt;"></textarea>
                </div>
            @endif

            <div>
                <button wire:click="processPdf" wire:loading.attr="disabled" class="btn btn-primary">
                    <svg wire:loading wire:target="processPdf" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ $pdfAction === 'merge' ? 'Combinar PDFs' : 'Generar PDF' }}
                </button>
            </div>

            @if($pdfOutput)
                <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg {{ str_contains($pdfOutput, 'Error') ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ str_contains($pdfOutput, 'Error') ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                    </svg>
                    <span>{{ $pdfOutput }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- CONVERTIDOR TAB --}}
    <div x-show="tab === 'convert'" class="card">
        <div class="card-body space-y-4"
             x-data="{ uploading: false, progress: 0, uploadError: '' }">
            @php $tools = [
                'word_to_pdf' => ['name' => 'WORD a PDF', 'desc' => 'Word a PDF', 'accept' => '.docx,.doc', 'color' => 'bg-blue-600'],
                'pdf_to_word' => ['name' => 'PDF a WORD', 'desc' => 'PDF a Word', 'accept' => '.pdf', 'color' => 'bg-emerald-600'],
                'jpg_to_pdf' => ['name' => 'Imagen a PDF', 'desc' => 'JPG/PNG a PDF', 'accept' => '.jpg,.jpeg,.png,.gif,.webp', 'color' => 'bg-amber-600'],
                'pdf_to_jpg' => ['name' => 'PDF a Imagen', 'desc' => 'PDF a JPG', 'accept' => '.pdf', 'color' => 'bg-[#5D87FF]'],
                'txt_to_pdf' => ['name' => 'TXT a PDF', 'desc' => 'Texto a PDF', 'accept' => '.txt', 'color' => 'bg-rose-600'],
                'pdf_to_txt' => ['name' => 'PDF a TXT', 'desc' => 'PDF a texto', 'accept' => '.pdf', 'color' => 'bg-teal-600'],
            ]; @endphp

            @if(!$convertTool)
                <div>
                    <p class="text-sm text-ink-500 dark:text-ink-400 mb-4 font-medium text-center">Selecciona una herramienta:</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($tools as $val => $t)
                            <button wire:click="selectConvertTool('{{ $val }}')"
                                    class="flex flex-col items-center gap-2 p-4 rounded-xl {{ $t['color'] }} text-white shadow-sm hover:shadow-md hover:brightness-110 transition-all">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if(in_array($val, ['word_to_pdf', 'pdf_to_word', 'txt_to_pdf', 'pdf_to_txt']))
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    @elseif(in_array($val, ['jpg_to_pdf', 'pdf_to_jpg']))
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    @endif
                                </svg>
                                <span class="text-sm font-semibold leading-tight text-center">{{ $t['name'] }}</span>
                                <span class="text-[10px] opacity-80">{{ $t['desc'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                @php $toolInfo = $this->convertToolInfo; @endphp
                <div class="flex items-center gap-2">
                    <button wire:click="selectConvertTool('')" class="text-xs text-ink-500 hover:text-ink-700 dark:hover:text-ink-300 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Volver
                    </button>
                    <span class="text-xs text-ink-300">|</span>
                    <span class="text-sm font-semibold text-ink-800 dark:text-ink-200 font-display">{{ $toolInfo['name'] ?? '' }}</span>
                </div>

                <div>
                    <div class="relative border-2 border-dashed rounded-lg p-8 text-center transition-colors"
                         :class="$wire.convertTempPath ? 'border-ink-400 dark:border-ink-400 bg-ink-50/50 dark:bg-ink-800/30' : 'border-ink-200 dark:border-ink-600 hover:border-ink-400 dark:hover:border-ink-400'"
                         @dragover.prevent="$el.classList.add('border-ink-400')"
                         @dragleave.prevent="$el.classList.remove('border-ink-400')"
                         @drop.prevent="$el.classList.remove('border-ink-400'); window.uploadFileBase64($event.dataTransfer.files[0], $wire, $el)">
                         @if($convertTempPath)
                             @php $isImage = str_starts_with($convertFileMime, 'image/'); @endphp
                             @if($isImage && $convertPreviewUrl)
                                 <img src="{{ $convertPreviewUrl }}" class="max-h-40 mx-auto rounded shadow-sm">
                             @else
                                 <svg class="w-14 h-14 mx-auto text-ink-300 dark:text-ink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                 </svg>
                             @endif
                             <p class="text-sm font-semibold text-ink-700 dark:text-ink-300 mt-2">{{ $convertFileName }}</p>
                             <button wire:click="selectConvertTool('{{ $convertTool }}')"
                                     class="mt-2 text-xs text-ink-500 hover:text-ink-700 underline">Cambiar archivo</button>
                         @else
                            <svg class="w-10 h-10 mx-auto mb-2 text-ink-300 dark:text-ink-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <p class="text-sm text-ink-500 dark:text-ink-400 font-medium pointer-events-none">Arrastra un archivo aquí o haz clic para seleccionar</p>
                            <p class="text-xs text-ink-400 mt-1 pointer-events-none">{{ $toolInfo['desc'] ?? '' }}</p>
                        @endif
                        <input type="file" accept="{{ $toolInfo['accept'] ?? '*' }}"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                               @change="window.uploadFileBase64($event.target.files[0], $wire, $el); $event.target.value = ''">
                    </div>
                    <div x-show="uploading" class="mt-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-2 bg-ink-200 dark:bg-ink-600 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all duration-200"
                                     :style="'width: ' + progress + '%'"></div>
                            </div>
                            <span class="text-xs text-ink-500 font-medium w-10 text-right shrink-0" x-text="Math.round(progress) + '%'"></span>
                        </div>
                    </div>
                    <div x-show="uploadError" class="mt-2 flex items-center gap-2 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 px-3 py-2 rounded-lg">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="uploadError"></span>
                    </div>
                </div>

                <div>
                     <button wire:click="processConvert" wire:loading.attr="disabled"
                             {{ !$convertTempPath ? 'disabled' : '' }}
                             class="btn btn-primary w-full justify-center {{ !$convertTempPath ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <svg wire:loading wire:target="processConvert" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ $toolInfo ? 'Convertir a ' . explode(' a ', $toolInfo['name'])[1] : 'Convertir' }}
                    </button>
                </div>

                @if($convertOutput)
                    <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg {{ str_contains($convertOutput, 'Error') ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ str_contains($convertOutput, 'Error') ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                        </svg>
                        <span>{{ $convertOutput }}</span>
                    </div>
                    @if($convertResultFile)
                        <div>
                            <a href="{{ route('olimpo.download-convert', $convertResultFile) }}" class="btn btn-primary w-full justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Guardar archivo convertido
                            </a>
                        </div>
                    @endif
                    @if(count($convertResultFiles) > 0)
                        <div class="space-y-2">
                            <p class="text-xs text-ink-500 font-medium">Archivos generados:</p>
                            @foreach($convertResultFiles as $f)
                                <a href="{{ route('olimpo.download-convert', $f) }}"
                                   class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    {{ $f }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>

    {{-- DNI / RUC TAB --}}
    <div x-show="tab === 'dni'">
        @include('livewire.olimpo.consultas-dni')
    </div>

    {{-- DOWNLOAD VIDEO TAB --}}
    <div x-show="tab === 'download'" class="card">
        <div class="card-body space-y-4">
            <div>
                <label class="block text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider mb-2">URL del Video</label>
                <input type="url" wire:model="downloadUrl" class="input-field w-full" placeholder="https://www.youtube.com/watch?v=...">
                <p class="text-xs text-ink-400 mt-1">Soporta YouTube, Facebook, Instagram, TikTok, Twitter/X, Vimeo y más</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider mb-2">Formato</label>
                    <select wire:model="downloadFormat" class="input-field w-full">
                        <option value="mp4">MP4 (Video)</option>
                        <option value="webm">WebM (Video)</option>
                        <option value="mp3">MP3 (Audio)</option>
                        <option value="m4a">M4A (Audio)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider mb-2">Calidad</label>
                    <select wire:model="downloadQuality" class="input-field w-full">
                        <option value="best">Mejor calidad</option>
                        <option value="1080p">1080p</option>
                        <option value="720p">720p</option>
                        <option value="480p">480p</option>
                        <option value="360p">360p</option>
                        <option value="worst">Peor calidad</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="fetchVideoInfo" wire:loading.attr="disabled" class="btn btn-secondary">
                    <svg wire:loading wire:target="fetchVideoInfo" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Obtener Información
                </button>
                <button wire:click="downloadVideo" wire:loading.attr="disabled" class="btn btn-primary">
                    <svg wire:loading wire:target="downloadVideo" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Descargar Video
                </button>
            </div>

            @if($downloadInfo)
                <div class="bg-ink-50 dark:bg-ink-800 rounded-lg p-4 space-y-2">
                    <div class="flex items-start gap-3">
                        @if(!empty($downloadInfo['thumbnail']))
                            <img src="{{ $downloadInfo['thumbnail'] }}" class="w-32 rounded object-cover" alt="Thumbnail">
                        @endif
                        <div class="flex-1 min-w-0 text-sm">
                            <p class="font-semibold text-ink-900 dark:text-ink-100 font-display">{{ $downloadInfo['title'] ?? 'Sin título' }}</p>
                            <p class="text-ink-500 dark:text-ink-400 text-xs mt-0.5">{{ $downloadInfo['duration'] ?? '' }}</p>
                            <p class="text-ink-500 dark:text-ink-400 text-xs">{{ $downloadInfo['uploader'] ?? '' }}</p>
                            @if(!empty($downloadInfo['formats']))
                                <div class="mt-2 text-xs text-ink-500 dark:text-ink-400">
                                    <span class="font-medium">{{ count($downloadInfo['formats']) }}</span> formatos disponibles
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($downloadOutput)
                <div class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg {{ str_contains($downloadOutput, 'Error') ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ str_contains($downloadOutput, 'Error') ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                    </svg>
                    <span>{{ $downloadOutput }}</span>
                </div>
                @if($downloadFile)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('olimpo.download-video', $downloadFile) }}" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Guardar archivo
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
