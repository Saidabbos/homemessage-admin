@extends('admin.layouts.app')

@section('title', 'Yangi Massage Turi')

@section('content')
<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .form-section h3 {
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea,
    select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    input:focus,
    textarea:focus,
    select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .image-preview {
        margin-top: 10px;
        max-width: 200px;
    }

    .image-preview img {
        width: 100%;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .error-message {
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
    }

    .help-text {
        color: #999;
        font-size: 12px;
        margin-top: 5px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-submit {
        background: #667eea;
        color: white;
    }

    .btn-submit:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }

    .tab-warning {
        background: #fff3cd;
        padding: 10px 12px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #ffc107;
    }
</style>

<div class="form-container">
    <h2>➕ Yangi Massage Turi</h2>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Xatoliklar:</strong>
            <ul style="margin: 5px 0 0 20px; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.service-types.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Basic Information -->
        <div class="form-section">
            <h3>📋 Asosiy Ma'lumot</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="slug">Slug (URL uchun)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g., relaxation-massage">
                    @error('slug')<span class="error-message">{{ $message }}</span>@enderror
                    <span class="help-text">Faqat harf, raqam va tire(-) foydalaning</span>
                </div>

                <div class="form-group">
                    <label for="duration">Davomiyligi (minutlar)</label>
                    <input type="number" id="duration" name="duration" value="{{ old('duration', 60) }}" min="15" max="480">
                    @error('duration')<span class="error-message">{{ $message }}</span>@enderror
                    <span class="help-text">Default: 60 minut. Min: 15, Max: 480</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Narxi (so'm)</label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" placeholder="0" step="0.01">
                    @error('price')<span class="error-message">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="image">Rasm</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    @error('image')<span class="error-message">{{ $message }}</span>@enderror
                    <span class="help-text">Maksimal o'lcham: 2MB. Formatlar: JPEG, PNG, GIF</span>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="status" name="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                    <label for="status" style="margin: 0;">Faol qilish</label>
                </div>
            </div>
        </div>

        <!-- Translations -->
        <div class="form-section">
            <h3>🌍 Tarjimalar</h3>

            <!-- English -->
            <div class="tab-warning">
                🇬🇧 English
            </div>

            <div class="form-group">
                <label for="en_name">Nomi (English) *</label>
                <input type="text" id="en_name" name="en[name]" value="{{ old('en.name') }}" placeholder="e.g., Relaxation Massage">
                @error('en.name')<span class="error-message">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="en_description">Tavsifi (English)</label>
                <textarea id="en_description" name="en[description]" placeholder="Massage turining batafsil tavsifi...">{{ old('en.description') }}</textarea>
                @error('en.description')<span class="error-message">{{ $message }}</span>@enderror
            </div>

            <!-- Uzbek -->
            <div class="tab-warning" style="margin-top: 20px;">
                🇺🇿 O'zbek
            </div>

            <div class="form-group">
                <label for="uz_name">Nomi (O'zbek) *</label>
                <input type="text" id="uz_name" name="uz[name]" value="{{ old('uz.name') }}" placeholder="Masalan, Relaxation Massaji">
                @error('uz.name')<span class="error-message">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="uz_description">Tavsifi (O'zbek)</label>
                <textarea id="uz_description" name="uz[description]" placeholder="Massage turining batafsil tavsifi...">{{ old('uz.description') }}</textarea>
                @error('uz.description')<span class="error-message">{{ $message }}</span>@enderror
            </div>

            <!-- Russian -->
            <div class="tab-warning" style="margin-top: 20px;">
                🇷🇺 Русский
            </div>

            <div class="form-group">
                <label for="ru_name">Nomi (Russian) *</label>
                <input type="text" id="ru_name" name="ru[name]" value="{{ old('ru.name') }}" placeholder="Например, Релаксирующий массаж">
                @error('ru.name')<span class="error-message">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="ru_description">Tavsifi (Russian)</label>
                <textarea id="ru_description" name="ru[description]" placeholder="Подробное описание типа массажа...">{{ old('ru.description') }}</textarea>
                @error('ru.description')<span class="error-message">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="button-group">
            <button type="submit" class="btn btn-submit">💾 Saqlash</button>
            <a href="{{ route('admin.service-types.index') }}" class="btn btn-cancel">❌ Bekor Qilish</a>
        </div>
    </form>
</div>

<script>
    // Auto-generate slug from name
    document.getElementById('uz_name').addEventListener('change', function() {
        if (!document.getElementById('slug').value) {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            document.getElementById('slug').value = slug;
        }
    });

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                let preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'image-preview';
                    document.querySelector('label[for="image"]').parentElement.appendChild(preview);
                }
                preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
