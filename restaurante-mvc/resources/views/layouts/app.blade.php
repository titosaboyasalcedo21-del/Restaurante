<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RestaurantChain') — Sistema de Gestión</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - DM Sans -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <!-- Custom Dark Theme CSS -->
    <link href="{{ asset('css/dark-theme.css') }}" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        /* Quick toggle styles */
        .sidebar-collapsed #sidebar { width: 64px; }
        .sidebar-collapsed #sidebar .sidebar-brand-text,
        .sidebar-collapsed #sidebar .nav-text,
        .sidebar-collapsed #sidebar .user-info,
        .sidebar-collapsed #sidebar .logout-btn span { display: none; }
        .sidebar-collapsed #sidebar .nav-link { justify-content: center; padding: 0.75rem; }
        .sidebar-collapsed #main-content { margin-left: 64px; }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">🍔</div>
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-title">RESTAURANT</div>
                <div class="sidebar-brand-subtitle">CHAIN MGR</div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-nav">
            <!-- Dashboard - Admin & Manager only (employees redirect to inventory) -->
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="nav-section-title">Principal</div>
            <div class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            @endif

            <!-- Gestión -->
            <div class="nav-section-title">Gestión</div>
            <div class="nav-item">
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span class="nav-text">Productos</span>
                </a>
            </div>

            <!-- Categorías - Visible to all but with restricted styling for employees -->
            <div class="nav-item">
                <a href="{{ auth()->user()->isEmployee() ? '#' : route('categories.index') }}"
                   class="nav-link {{ auth()->user()->isEmployee() ? 'blocked' : '' }} {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                   @if(auth()->user()->isEmployee()) onclick="return false;" @endif>
                    <i class="bi bi-tags"></i>
                    <span class="nav-text">Categorías</span>
                </a>
            </div>

            <!-- Inventario -->
            <div class="nav-section-title">Operaciones</div>
            <div class="nav-item">
                <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="nav-text">Inventario</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('inventory.movements') }}" class="nav-link {{ request()->routeIs('inventory.movements') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span class="nav-text">Movimientos</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('inventory.low-stock') }}" class="nav-link {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span class="nav-text">Stock Bajo</span>
                </a>
            </div>

            <!-- Sucursales - Visible to all but with restricted styling for employees -->
            <div class="nav-section-title">Sucursales</div>
            <div class="nav-item">
                <a href="{{ auth()->user()->isEmployee() ? '#' : route('branches.index') }}"
                   class="nav-link {{ auth()->user()->isEmployee() ? 'blocked' : '' }} {{ request()->routeIs('branches.*') ? 'active' : '' }}"
                   @if(auth()->user()->isEmployee()) onclick="return false;" @endif>
                    <i class="bi bi-shop"></i>
                    <span class="nav-text">Sucursales</span>
                </a>
            </div>

            <!-- Usuarios - Admin Only -->
            @if(auth()->user()->isAdmin())
            <div class="nav-section-title">Administración</div>
            <div class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-wide-connected"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </div>
            @endif
        </div>

        <!-- User Section -->
        @auth
        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2">
                <div class="user-avatar {{ auth()->user()->role }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role_label }}</div>
                </div>
            </div>
            <a href="{{ auth()->user()->isEmployee() ? '#' : route('profile.edit') }}" class="logout-btn {{ auth()->user()->isEmployee() ? 'blocked' : '' }}" @if(auth()->user()->isEmployee()) onclick="return false;" @endif>
                <i class="bi bi-gear"></i>
                <span>Configuración</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </div>
        @endauth
    </nav>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <ol class="breadcrumb-custom">
                    <li><span class="breadcrumb-root">Sistema</span></li>
                    <li><span class="breadcrumb-current">@yield('module', request()->route()->getName())</span></li>
                </ol>
            </div>
            <div class="topbar-right">
                <span class="topbar-date">{{ now()->format('d/m/Y') }}</span>
                @auth
                <span class="role-badge {{ auth()->user()->role }}">
                    <i class="bi bi-person-fill"></i>
                    {{ auth()->user()->role_label }}
                </span>
                @endauth
            </div>
        </div>

        <!-- Toast Notifications -->
        @if(session('success'))
        <div class="alert-toast alert-success" id="toast-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert-toast alert-error" id="toast-error">
            <i class="bi bi-x-circle-fill"></i>
            {{ session('error') }}
        </div>
        @endif

        <!-- Page Content -->
        <div class="main-container">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/charts.js') }}"></script>
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
        }

        // Auto-dismiss toasts after 4 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var toasts = document.querySelectorAll('.alert-toast');
                toasts.forEach(function(toast) {
                    toast.style.animation = 'slideIn 0.3s ease reverse';
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                });
            }, 4000);
        });
    </script>
    @stack('scripts')
</body>
</html>
