<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Admin Panel</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    {{ Auth::user()->name }}
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
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
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="https://i.imgur.com/3yJc8IZ.png" alt="Pazmanya Logo" class="brand-image" style="max-height: 36px; margin-left: 0.8rem; margin-right: 0.5rem; background-color: #fff; padding: 3px; border-radius: 4px;">
            <span class="brand-text font-weight-light" style="opacity: 0;">Pazmanya</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <!-- Kullanıcı paneli kaldırıldı - menü daha sade olması için -->

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('tickets.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-ticket-alt"></i>
                            <p>
                                Ticket Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-list"></i>
                            <p>Tüm Ticketlar</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->routeIs('users.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                Müşteri Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Müşteriler</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.create') }}" class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-plus"></i>
                                    <p>Yeni Müşteri</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.import.form') }}" class="nav-link {{ request()->routeIs('users.import.form') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-import"></i>
                                    <p>Müşteri İçe Aktar</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('departments.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                Departman Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Departmanlar</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('customers.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>
                                Personel Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                    <li class="nav-item">
                                <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                                    <p>Personeller</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('customers.create') }}" class="nav-link {{ request()->routeIs('customers.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-plus"></i>
                                    <p>Yeni Personel</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('roles.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>
                                Rol Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tag"></i>
                                    <p>Roller</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('roles.create') }}" class="nav-link {{ request()->routeIs('roles.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plus-circle"></i>
                                    <p>Yeni Rol</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('admin.notifications.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>
                                Bildirim Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bell"></i>
                                    <p>Bildirimler</p>
                        </a>
                    </li>
                    <li class="nav-item">
                                <a href="{{ route('admin.notifications.create') }}" class="nav-link {{ request()->routeIs('admin.notifications.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plus-circle"></i>
                                    <p>Yeni Bildirim</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->routeIs('admin.shifts.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>
                                Mesai Yönetimi
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.shifts.index') }}" class="nav-link {{ request()->routeIs('admin.shifts.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <p>Mesai Takibi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.shifts.active') }}" class="nav-link {{ request()->routeIs('admin.shifts.active') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-clock"></i>
                                    <p>Aktif Personeller</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.shifts.settings') }}" class="nav-link {{ request()->routeIs('admin.shifts.settings') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Mesai Ayarları</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('admin.assignments.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>
                                Görev Atama
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.assignments.index') }}" class="nav-link {{ request()->routeIs('admin.assignments.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Görev Atamaları</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.assignments.settings') }}" class="nav-link {{ request()->routeIs('admin.assignments.settings') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <p>Atama Kuralları</p>
                                </a>
                            </li>
                    <li class="nav-item">
                                <a href="{{ route('admin.assignments.unassigned') }}" class="nav-link {{ request()->routeIs('admin.assignments.unassigned') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-inbox"></i>
                                    <p>Atanmamış Görevler</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item has-treeview {{ request()->routeIs('admin.reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Raporlar
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.shifts') }}" class="nav-link {{ request()->routeIs('admin.reports.shifts') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-pie"></i>
                                    <p>Mesai Raporu</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.workload') }}" class="nav-link {{ request()->routeIs('admin.reports.workload') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>İş Yükü Raporu</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.performance') }}" class="nav-link {{ request()->routeIs('admin.reports.performance') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Performans Raporu</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->routeIs('canned-responses.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('canned-responses.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-comment-dots"></i>
                            <p>
                                Hazır Yanıtlar
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('canned-responses.index') }}" class="nav-link {{ request()->routeIs('canned-responses.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-list"></i>
                                    <p>Tüm Yanıtlar</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('canned-responses.create') }}" class="nav-link {{ request()->routeIs('canned-responses.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plus-circle"></i>
                                    <p>Yeni Yanıt</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Başarılı!</h5>
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Hata!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="https://pazmanya.tr" target="_blank">Pazmanya</a>.</strong>
        Tüm hakları saklıdır.
        <div class="float-right d-none d-sm-inline-block">
            <b>Sürüm</b> 1.0.0
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

@yield('js')
@yield('scripts')
</body>
</html> 