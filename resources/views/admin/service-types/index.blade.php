@extends('admin.layouts.app')

@section('title', 'Massage Turlari')

@section('content')
<style>
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
    }

    td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
    }

    tr:hover {
        background: #f8f9fa;
    }

    .btn {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 13px;
        margin-right: 5px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }

    .btn-info {
        background: #17a2b8;
        color: white;
    }

    .btn-warning {
        background: #ffc107;
        color: #333;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .image-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
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

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .pagination a, .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-decoration: none;
        color: #667eea;
    }

    .pagination .active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #999;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
</style>

<div class="header-section">
    <h2>💆 Massage Turlari</h2>
    <a href="{{ route('admin.service-types.create') }}" class="btn btn-primary">
        ➕ Yangi Turl Qo'shish
    </a>
</div>

@if (session('success'))
    <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
        ✓ {{ session('success') }}
    </div>
@endif

@if ($serviceTypes->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rasm</th>
                    <th>Nomi (UZ)</th>
                    <th>Davomiyligi</th>
                    <th>Narxi</th>
                    <th>Status</th>
                    <th>Amallar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serviceTypes as $type)
                    <tr>
                        <td>
                            <img src="{{ $type->image_url }}" alt="{{ $type->name }}" class="image-thumb">
                        </td>
                        <td>
                            <strong>{{ $type->getTranslation('name', 'uz') }}</strong>
                            <br>
                            <small style="color: #999;">{{ $type->slug }}</small>
                        </td>
                        <td>
                            ⏱️ {{ $type->duration }} min
                        </td>
                        <td>
                            💰 {{ number_format($type->price, 0) }} so'm
                        </td>
                        <td>
                            @if ($type->status)
                                <span class="status-badge status-active">Faol</span>
                            @else
                                <span class="status-badge status-inactive">Nofaol</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.service-types.show', $type) }}" class="btn btn-info">Ko'rish</a>
                                <a href="{{ route('admin.service-types.edit', $type) }}" class="btn btn-warning">Tahrir</a>
                                <form action="{{ route('admin.service-types.destroy', $type) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">O'chirish</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $serviceTypes->links() }}
    </div>
@else
    <div class="table-container">
        <div class="empty-state">
            <div class="empty-state-icon">🏥</div>
            <h3>Massage turlari yo'q</h3>
            <p>Hozircha hech qanday massage turi yaratilmadi</p>
            <a href="{{ route('admin.service-types.create') }}" class="btn btn-primary" style="margin-top: 15px;">
                ➕ Birinchi Turl Qo'shish
            </a>
        </div>
    </div>
@endif
@endsection
