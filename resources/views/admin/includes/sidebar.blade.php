                <!-- Kullanıcı Yönetimi -->
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Kullanıcı Yönetimi</p>
                    </a>
                </li>
                
                <!-- Bildirim Yönetimi -->
                <li class="nav-item">
                    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>Bildirim Yönetimi</p>
                    </a>
                </li>
                
                <!-- Ayarlar -->
                <li class="nav-item"> 