<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <!-- Tüm kullanıcılara görünecek menü -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            
                            <!-- Ticket Menüsü - Tüm kullanıcılar -->
                            <li class="nav-item dropdown">
                                <a id="ticketsDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ __('Destek Talepleri') }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="ticketsDropdown">
                                    <a class="dropdown-item" href="{{ route('tickets.my') }}">{{ __('Taleplerim') }}</a>
                                    <a class="dropdown-item" href="{{ route('tickets.create') }}">{{ __('Yeni Talep Oluştur') }}</a>
                                    @if(Auth::user()->hasRole(['admin', 'staff', 'teknik destek']))
                                        <a class="dropdown-item" href="{{ route('tickets.index') }}">{{ __('Tüm Talepler') }}</a>
                                    @endif
                                </div>
                            </li>
                            
                            <!-- Yönetim Menüsü - Sadece Admin -->
                            @if(Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a id="adminDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ __('Yönetim') }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                    <a class="dropdown-item" href="{{ route('users.index') }}">{{ __('Kullanıcılar') }}</a>
                                    <a class="dropdown-item" href="{{ route('customers.index') }}">{{ __('Müşteriler') }}</a>
                                    <a class="dropdown-item" href="{{ route('departments.index') }}">{{ __('Departmanlar') }}</a>
                                    <a class="dropdown-item" href="{{ route('roles.index') }}">{{ __('Roller ve İzinler') }}</a>
                                    <a class="dropdown-item" href="{{ route('canned-responses.index') }}">{{ __('Hazır Yanıtlar') }}</a>
                                    <a class="dropdown-item" href="{{ route('admin.notifications.index') }}">{{ __('Bildirim Yönetimi') }}</a>
                                </div>
                            </li>
                            @endif
                            
                            <!-- Personel Menüsü - Sadece Staff ve Teknik Destek -->
                            @if(Auth::user()->hasRole(['staff', 'teknik destek']) && !Auth::user()->hasRole('admin'))
                            <li class="nav-item dropdown">
                                <a id="staffDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ __('Personel İşlemleri') }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="staffDropdown">
                                    <a class="dropdown-item" href="{{ route('tickets.assigned') }}">{{ __('Atanan Taleplerim') }}</a>
                                    <a class="dropdown-item" href="{{ route('canned-responses.index') }}">{{ __('Hazır Yanıtlarım') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <h6 class="dropdown-header">İstatistikler</h6>
                                    <a class="dropdown-item" href="{{ route('admin.reports.performance') }}">{{ __('Performans Raporu') }}</a>
                                </div>
                            </li>
                            @endif
                            
                            <!-- Bildirim Dropdown -->
                            <li class="nav-item dropdown">
                                <a id="notificationDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge bg-danger rounded-pill notification-badge" style="display: none;"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="width: 350px;">
                                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                        <h6 class="mb-0 fw-bold">Bildirimler</h6>
                                        @if(auth()->check())
                                        <a href="#" id="markAllAsRead" class="text-decoration-none small" data-url="{{ route('notifications.mark-all-read') }}">
                                            Tümünü okundu işaretle
                                        </a>
                                        @endif
                                    </div>
                                    <div class="notification-loader text-center p-3" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Yükleniyor...</span>
                                        </div>
                                    </div>
                                    <div class="notification-list" style="max-height: 300px; overflow-y: auto;"></div>
                                    @if(auth()->check())
                                    <div class="p-2 border-top">
                                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-primary d-block">
                                            Tüm Bildirimleri Görüntüle
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </li>

                            <!-- Kullanıcı Menüsü -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    @if(!Auth::user()->hasRole('customer'))
                                    <a class="dropdown-item" href="{{ route('profile') }}">{{ __('Profil') }}</a>
                                    <a class="dropdown-item" href="{{ route('profile.password') }}">{{ __('Şifre Değiştir') }}</a>
                                    <div class="dropdown-divider"></div>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Çıkış Yap') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>

        <footer class="bg-light py-3 border-top mt-auto">
            <div class="container">
                <div class="text-center">
                    <strong>Copyright &copy; {{ date('Y') }} <a href="https://pazmanya.tr" target="_blank">Pazmanya</a>.</strong>
                    Tüm hakları saklıdır.
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Özel JS -->
    @yield('scripts')
</body>
</html>
