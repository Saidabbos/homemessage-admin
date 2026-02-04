@extends('admin.layouts.app')

@section('title', 'Massage Turi - ' . $serviceType->getTranslation('name', 'uz'))

@section('content')
<style>
    .detail-container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .detail-header {
        display: flex;
        gap: 30px;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }

    .detail-image {
        flex-shrink: 0;
    }

    .detail-image img {
        width: 250px;
        height: 250px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .detail-basic-info {
        flex: 1;
    }

    .detail-basic-info h2 {
        margin: 0 0 20px 0;
        color: #333;
    }

    .info-row {
        display: flex;
        gap: 30px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-item {
        flex: 1;
    }

    .info-label {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 18px;
        color: #333;
        font-weight: 500;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .translations-section {
        margin-bottom: 40px;
    }

    .translations-section h3 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .translation-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #667eea;
    }

    .translation-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .translation-header strong {
        font-size: 16px;
        color: #333;
    }

    .translation-name {
        font-size: 20px;
        color: #333;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .translation-description {
        color: #666;
        line-height: 1.6;
        font-size: 14px;
    }

    .action-buttons {
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

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        margin-left: auto;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .meta-info {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        gap: 30px;
        color: #999;
        font-size: 13px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .meta-label {
        text-transform: uppercase;
        font-weight: 600;
    }
</style>

<div class="detail-container">
    <!-- Header with Image and Basic Info -->
    <div class="detail-header">
        <div class="detail-image">
            <img src="{{ $serviceType->image_url }}" alt="{{ $serviceType->getTranslation('name', 'uz') }}">
        </div>

        <div class="detail-basic-info">
            <h2>{{ $serviceType->getTranslation('name', 'uz') }}</h2>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">💰 Narxi</div>
                    <div class="info-value">{{ number_format($serviceType->price, 0) }} so'm</div>
                </div>
                <div class="info-item">
                    <div class="info-label">⏱️ Davomiyligi</div>
                    <div class="info-value">{{ $serviceType->duration }} daqiqa</div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">🔗 Slug</div>
                    <div class="info-value" style="font-family: monospace; font-size: 14px;">{{ $serviceType->slug }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">📊 Status</div>
                    <span class="status-badge {{ $serviceType->status ? 'status-active' : 'status-inactive' }}">
                        {{ $serviceType->status ? '✓ Faol' : '✗ Nofaol' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Translations Section -->
    <div class="translations-section">
        <h3>🌍 Tarjimalar</h3>

        <!-- English -->
        <div class="translation-card">
            <div class="translation-header">
                <span>🇬🇧</span>
                <strong>English</strong>
            </div>
            <div class="translation-name">{{ $serviceType->getTranslation('name', 'en') }}</div>
            @if ($serviceType->getTranslation('description', 'en'))
                <div class="translation-description">
                    {{ $serviceType->getTranslation('description', 'en') }}
                </div>
            @endif
        </div>

        <!-- Uzbek -->
        <div class="translation-card">
            <div class="translation-header">
                <span>🇺🇿</span>
                <strong>O'zbek</strong>
            </div>
            <div class="translation-name">{{ $serviceType->getTranslation('name', 'uz') }}</div>
            @if ($serviceType->getTranslation('description', 'uz'))
                <div class="translation-description">
                    {{ $serviceType->getTranslation('description', 'uz') }}
                </div>
            @endif
        </div>

        <!-- Russian -->
        <div class="translation-card">
            <div class="translation-header">
                <span>🇷🇺</span>
                <strong>Русский</strong>
            </div>
            <div class="translation-name">{{ $serviceType->getTranslation('name', 'ru') }}</div>
            @if ($serviceType->getTranslation('description', 'ru'))
                <div class="translation-description">
                    {{ $serviceType->getTranslation('description', 'ru') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Metadata -->
    <div class="meta-info">
        <div class="meta-item">
            <div class="meta-label">Yaratilgan</div>
            <div>{{ $serviceType->created_at->format('d.m.Y H:i') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Oxirgi yangilash</div>
            <div>{{ $serviceType->updated_at->format('d.m.Y H:i') }}</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('admin.service-types.edit', $serviceType) }}" class="btn btn-primary">
            ✏️ Tahrir Qilish
        </a>
        <a href="{{ route('admin.service-types.index') }}" class="btn btn-secondary">
            ← Orqaga
        </a>
        <form action="{{ route('admin.service-types.destroy', $serviceType) }}" method="POST" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">
                🗑️ O'chirish
            </button>
        </form>
    </div>
</div>

@endsection
