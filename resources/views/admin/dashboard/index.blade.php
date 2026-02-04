@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="dashboard-header">
    <h1>Dashboard</h1>
    <p>HomeMessage Admin Panel'ga xush kelibsiz, {{ auth()->user()->name }}!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon users">👥</div>
        <div class="stat-content">
            <h3>Jami Foydalanuvchilar</h3>
            <p class="stat-number">{{ $stats['total_users'] }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon admins">🔐</div>
        <div class="stat-content">
            <h3>Adminlar</h3>
            <p class="stat-number">{{ $stats['total_admins'] }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon editors">✏️</div>
        <div class="stat-content">
            <h3>Redaktorlar</h3>
            <p class="stat-number">{{ $stats['total_editors'] }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon writers">📝</div>
        <div class="stat-content">
            <h3>Yozuvchilar</h3>
            <p class="stat-number">{{ $stats['total_writers'] }}</p>
        </div>
    </div>
</div>

<div class="dashboard-content">
    <div class="panel">
        <h2>Tezkor Amallar</h2>
        <div class="quick-actions">
            <a href="#" class="action-btn users">
                <span>👥</span>
                <span>Foydalanuvchilarni Boshqarish</span>
            </a>
            <a href="#" class="action-btn services">
                <span>🔧</span>
                <span>Xizmatlarni Boshqarish</span>
            </a>
            <a href="#" class="action-btn bookings">
                <span>📅</span>
                <span>Bronlarni Boshqarish</span>
            </a>
            <a href="#" class="action-btn reports">
                <span>📊</span>
                <span>Hisobotlar</span>
            </a>
        </div>
    </div>

    <div class="panel">
        <h2>Oxirgi Faoliyat</h2>
        <p style="color: #999; padding: 20px;">Faoliyat yo'q</p>
    </div>
</div>

<style>
    .dashboard-header {
        margin-bottom: 30px;
    }

    .dashboard-header h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 5px;
    }

    .dashboard-header p {
        color: #666;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        font-size: 40px;
        margin-right: 20px;
    }

    .stat-content h3 {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #667eea;
    }

    .panel {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .panel h2 {
        color: #333;
        margin-bottom: 20px;
        font-size: 20px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border: 2px solid #eee;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
    }

    .action-btn:hover {
        border-color: #667eea;
        background-color: #f5f7ff;
        transform: translateY(-3px);
    }

    .action-btn span:first-child {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .action-btn span:last-child {
        font-size: 14px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .dashboard-header h1 {
            font-size: 24px;
        }
    }
</style>
@endsection
