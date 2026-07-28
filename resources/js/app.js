import { createIcons, icons } from 'lucide';

function initLucide() {
    createIcons({ icons });
}
document.addEventListener('DOMContentLoaded', initLucide);
document.addEventListener('livewire:navigated', initLucide);
let morphRaf = null;
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => {
        if (!morphRaf) {
            morphRaf = requestAnimationFrame(() => {
                morphRaf = null;
                initLucide();
            });
        }
    });
});

window.uploadFileBase64 = function(file, wire, el) {
    if (!file || !el) return;
    var root = el.closest('[x-data]');
    if (!root) return;
    var scope = typeof Alpine !== 'undefined' ? Alpine.$data(root) : null;
    if (!scope) return;
    scope.uploading = true;
    scope.progress = 0;
    scope.uploadError = '';
    var reader = new FileReader();
    reader.onprogress = function(e) {
        if (e.lengthComputable) {
            var pct = Math.round(e.loaded * 50 / e.total);
            scope.progress = pct;
        }
    };
    reader.onload = function() {
        scope.progress = 50;
        var b64 = reader.result.split(',', 2)[1];
        if (!b64) { scope.uploading = false; scope.uploadError = 'Error al leer el archivo'; return; }
        var fd = new FormData();
        fd.append('data', b64);
        fd.append('_token', (document.querySelector('meta[name="csrf-token"]') || {}).content || '');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/olimpo/upload-base64');
        xhr.setRequestHeader('X-File-Name', encodeURIComponent(file.name));
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var pct = 50 + Math.round(e.loaded * 50 / e.total);
                scope.progress = Math.min(pct, 99);
            }
        };
        xhr.onload = function() {
            scope.uploading = false;
            if (xhr.status === 200) {
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error) {
                        scope.uploadError = r.error;
                        return;
                    }
                    wire.set('convertTempPath', r.path);
                    wire.set('convertFileName', file.name);
                    wire.set('convertFileMime', file.type);
                    wire.set('convertPreviewUrl', r.preview_url || '');
                } catch(e) {
                    scope.uploadError = 'Error al procesar la respuesta';
                }
            } else {
                try {
                    var r = JSON.parse(xhr.responseText);
                    scope.uploadError = r.error || 'Error del servidor: ' + xhr.status;
                } catch(e) {
                    scope.uploadError = 'Error del servidor: ' + xhr.status;
                }
            }
        };
        xhr.onerror = function() {
            scope.uploading = false;
            scope.uploadError = 'Error de red';
        };
        xhr.send(fd);
    };
    reader.onerror = function() {
        scope.uploading = false;
        scope.uploadError = 'Error al leer el archivo';
    };
    reader.readAsDataURL(file);
};

window.uploadPdfFiles = function(files, wire, el) {
    if (!files || files.length === 0) return;
    var root = el.closest('[x-data]');
    if (!root) return;
    var scope = typeof Alpine !== 'undefined' ? Alpine.$data(root) : null;
    if (!scope) return;
    scope.uploading = true;
    scope.progress = 0;
    scope.uploadError = '';
    var total = files.length;
    var done = 0;

    var uploadNext = function(index) {
        if (index >= total) {
            scope.uploading = false;
            scope.progress = 100;
            return;
        }
        var file = files[index];
        var pctBase = Math.round(index * 90 / total);
        scope.progress = pctBase;
        var reader = new FileReader();
        reader.onprogress = function(e) {
            if (e.lengthComputable) {
                var pct = pctBase + Math.round(e.loaded * 40 / total / e.total);
                scope.progress = Math.min(pct, 99);
            }
        };
        reader.onload = function() {
            var b64 = reader.result.split(',', 2)[1];
            if (!b64) {
                scope.uploadError = 'Error al leer: ' + file.name;
                scope.uploading = false;
                return;
            }
            var fd = new FormData();
            fd.append('data', b64);
            fd.append('_token', (document.querySelector('meta[name="csrf-token"]') || {}).content || '');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/olimpo/upload-base64');
            xhr.setRequestHeader('X-File-Name', encodeURIComponent(file.name));
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var r = JSON.parse(xhr.responseText);
                        if (r.error) {
                            scope.uploadError = r.error;
                            scope.uploading = false;
                            return;
                        }
                        var paths = Array.isArray(wire.get('pdfFilePaths')) ? wire.get('pdfFilePaths') : [];
                        var names = Array.isArray(wire.get('pdfFileNames')) ? wire.get('pdfFileNames') : [];
                        paths.push(r.path);
                        names.push(r.name);
                        wire.set('pdfFilePaths', paths);
                        wire.set('pdfFileNames', names);
                    } catch(e) {
                        scope.uploadError = 'Error al procesar respuesta';
                        scope.uploading = false;
                        return;
                    }
                } else {
                    try {
                        var r = JSON.parse(xhr.responseText);
                        scope.uploadError = r.error || 'Error del servidor: ' + xhr.status;
                    } catch(e) {
                        scope.uploadError = 'Error del servidor: ' + xhr.status;
                    }
                    scope.uploading = false;
                    return;
                }
                done++;
                var pct = Math.round(done * 90 / total);
                scope.progress = Math.min(pct + 5, 95);
                uploadNext(index + 1);
            };
            xhr.onerror = function() {
                scope.uploadError = 'Error de red al subir: ' + file.name;
                scope.uploading = false;
            };
            xhr.send(fd);
        };
        reader.onerror = function() {
            scope.uploadError = 'Error al leer: ' + file.name;
            scope.uploading = false;
        };
        reader.readAsDataURL(file);
    };
    uploadNext(0);
};