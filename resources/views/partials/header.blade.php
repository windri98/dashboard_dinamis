<header class="header">
    <div class="header-left">
        <button class="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Cari...">
        </div>
    </div>

    <div class="header-right">
        <div class="notification">
            <a href="#">
            </a>
            <i class="fas fa-bell"></i>
            <div class="notification-badge">3</div>
        </div>

        <div class="profile">
            <img src="/assets/image/png-clipart-google-contacts-mobile-app-contact-manager-app-store-android-application-package-email-miscellaneous-blue-thumbnail.png" alt="Avatar" class="avatar">
            <div class="user-info">
            <p class="username">{{ Auth::user()->username }}</p>
            <p class="role">{{ Auth::user()->roles->role }}</p>
            </div>
            <i class="fas fa-caret-down dropdown-icon"></i> <!-- Dropdown indicator -->

            <div class="dropdown-content-navbar">
            <a href="{{ route('ubah.password') }}" class="dropdown-item">Profil</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">Keluar</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
            </div>
        </div>
    </div>
</header>
