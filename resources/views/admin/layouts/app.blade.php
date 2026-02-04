<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - HomeMessage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
        }

        /* Header */
        .admin-header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-menu {
            position: relative;
        }

        .user-menu-toggle {
            background: none;
            border: none;
            color: #333;
            cursor: pointer;
            font-size: 18px;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
            display: none;
        }

        .user-dropdown.active {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .user-dropdown a:last-child {
            border-bottom: none;
        }

        .user-dropdown a:hover {
            background-color: #f5f7fa;
        }

        .logout-btn {
            color: #c33 !important;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        /* Alerts */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .content {
                padding: 20px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 200px;
                padding: 15px;
            }

            .main-content {
                margin-left: 200px;
            }

            .sidebar-menu a {
                padding: 10px 12px;
                font-size: 14px;
            }

            .sidebar-logo {
                font-size: 20px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">🏠 HomeMessage</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        👥 Foydalanuvchilar
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.service-types.index') }}" class="{{ request()->routeIs('admin.service-types*') ? 'active' : '' }}">
                        💆 Massage Turlari
                    </a>
                </li>
                <li>
                    <a href="#" class="{{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                        📅 Bronlar
                    </a>
                </li>
                <li>
                    <a href="#" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        📊 Hisobotlar
                    </a>
                </li>
                <li>
                    <a href="#" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        ⚙️ Sozlamalar
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <h2>@yield('title', 'Dashboard')</h2>
                <div class="admin-header-right">
                    <div class="user-info">
                        <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <div>
                            <div style="font-weight: 600;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 12px; color: #999;">
                                {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                            </div>
                        </div>
                    </div>
                    <div class="user-menu">
                        <button class="user-menu-toggle">⋮</button>
                        <div class="user-dropdown">
                            <a href="#">👤 Profil</a>
                            <a href="#">⚙️ Sozlamalar</a>
                            <form action="{{ route('admin.logout') }}" method="POST" style="display: none;" id="logout-form">
                                @csrf
                            </form>
                            <a href="#" onclick="document.getElementById('logout-form').submit();" class="logout-btn">
                                🚪 Chiqish
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        ❌ {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Toggle user dropdown
        document.querySelector('.user-menu-toggle').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                document.querySelector('.user-dropdown').classList.remove('active');
            }
        });
    </script>
</body>
</html>
