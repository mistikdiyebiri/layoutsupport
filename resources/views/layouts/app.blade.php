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

    <!-- Debug info for development -->
    @if(config('app.debug'))
    <style>
        .debug-info {
            position: fixed;
            bottom: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 10px;
            font-size: 12px;
            z-index: 9999;
            max-width: 300px;
            max-height: 200px;
            overflow: auto;
        }
    </style>
    @endif
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="https://i.imgur.com/3yJc8IZ.png" alt="Pazmanya" class="brand-logo" style="height: 40px;">
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
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                                    <a class="dropdown-item" href="{{ route('profile') }}">{{ __('Profil') }}</a>
                                    <a class="dropdown-item" href="{{ route('tickets.assigned', ['staff_id' => auth()->id()]) }}">{{ __('Atanan Taleplerim') }}</a>
                                    <a class="dropdown-item" href="{{ route('tickets.index') }}">{{ __('Tüm Talepler') }}</a>
                                    
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
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

    <!-- Debug info for development -->
    @if(config('app.debug'))
    <div class="debug-info">
        <strong>Auth:</strong> {{ Auth::check() ? 'Logged in as ' . Auth::user()->name : 'Not logged in' }}<br>
        <strong>Route:</strong> {{ Route::currentRouteName() }}<br>
        <strong>URL:</strong> {{ request()->url() }}<br>
        @if(Auth::check())
        <strong>Roles:</strong> {{ implode(', ', Auth::user()->getRoleNames()->toArray()) }}<br>
        @endif
    </div>
    @endif
</body>
</html>
