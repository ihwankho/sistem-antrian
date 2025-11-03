{{-- Blok error global dihapus dari sini agar tampil di bawah input --}}

<div class="mb-3">
    <label for="video_urls_text" class="form-label">URL Video YouTube</label>
    <textarea class="form-control @error('video_urls_text') is-invalid @enderror" id="video_urls_text" name="video_urls_text" rows="5" placeholder="Satu URL lengkap per baris...">{{ old('video_urls_text', $setting ?? null ? implode("\n", $setting->video_urls) : '') }}</textarea>
    <div class="form-text">Masukkan satu URL YouTube lengkap per baris. Contoh: https://www.youtube.com/watch?v=xxxxx</div>
    
    {{-- Pesan error validasi untuk video_urls_text --}}
    @error('video_urls_text')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="running_text" class="form-label">Running Text</label>
    <textarea class="form-control @error('running_text') is-invalid @enderror" id="running_text" name="running_text" rows="3" placeholder="Teks yang akan berjalan di bagian bawah layar...">{{ old('running_text', $setting->running_text ?? '') }}</textarea>
    
    {{-- Pesan error validasi untuk running_text --}}
    @error('running_text')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
        <option value="inactive" @selected(old('status', $setting->status ?? 'inactive') == 'inactive')>
            Tidak Aktif
        </option>
        <option value="active" @selected(old('status', $setting->status ?? 'inactive') == 'active')>
            Aktif
        </option>
    </select>
    <div class="form-text">Jika diatur ke "Aktif", pengaturan lain yang sedang aktif akan otomatis menjadi "Tidak Aktif".</div>
    
    {{-- Pesan error validasi untuk status --}}
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>