    {{-- <aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <nav class="nav flex-column p-3">              
                <a href="{{ route('order') }}" class="nav-link text-secondary active fw-bold" style="color: var(--bs-primary) !important;">➕ Enregistrer un dépôt</a>   
                <a href="{{ route('clients.index') }}" class="nav-link text-secondary">✅ Gestion des clients</a>
                <a href="{{ route('manager.order') }}" class="nav-link text-secondary">✅ Gestion des dépôts</a>
                <a href="{{ route('articles.index') }}" class="nav-link text-secondary">✅ Gestion des articles</a>
                <a href="{{ route('services.index') }}" class="nav-link text-secondary">✅ Gestion des services</a>
                
                @if (Auth::User()->role === 'admin')
                    <a href="{{ route('manager.gestionnaire') }}" class="nav-link text-secondary">🧑 Gestionnaire</a>
                @endif
                <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-secondary" data-bs-toggle="dropdown" href="#">💰 Charges</a>
                <ul class="dropdown-menu">
                    @if (Auth::User()->role === 'admin')
                    <li><a class="dropdown-item" href="{{ route('manager.payroll.index') }}">👥 Salaire</a></li>
                    @endif  
                    <li><a class="dropdown-item" href="{{ route('spenses.index') }}">📦 Autres Dépenses</a></li>
                </ul>
                </div>
                @if (Auth::User()->role === 'admin')
                    <a href="{{ route('statistics') }}" class="nav-link text-secondary">📊 Statistiques</a>
                @endif
                <a href="#" class="nav-link text-secondary">⚙️ Paramètres</a>
                <a href="{{ route('dashboard') }}" class="nav-link text-secondary">🏠 Tableau de bord</a>
            </nav>

            <div class="mt-auto p-3 border-top">
                <form method="POST" action="{{route('logout')}}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">🚪 Déconnexion</button>
                </form>
            </div>
        </div>
    </aside> --}}
<aside class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-primary fw-bold">🧺 Pressing Manager</h5>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <nav class="nav flex-column p-3">               
            @if (Auth::user()->role === 'manager')
                {{-- MENU MANAGER SYSTÈME --}}
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'text-primary fw-bold' : 'text-secondary' }}">🏠 Tableau de bord</a>
                {{-- <a href="{{ route('manager.subscriptions') }}" class="nav-link {{ request()->routeIs('manager.subscriptions') ? 'text-primary fw-bold' : 'text-secondary' }}">💳 Gestion Abonnements</a> --}}
                {{-- <a href="{{ route('manager.passwords.reset') }}" class="nav-link text-secondary">🔑 Reset Mots de passe Admin</a> --}}
            @else
                {{-- MENU ADMIN / STAFF PRESSING --}}
                <a href="{{ route('order') }}" class="nav-link text-secondary active fw-bold" style="color: var(--bs-primary) !important;">➕ Enregistrer un dépôt</a>   
                <a href="{{ route('clients.index') }}" class="nav-link text-secondary">✅ Gestion des clients</a>
                <a href="{{ route('manager.order') }}" class="nav-link text-secondary">✅ Gestion des dépôts</a>
                <a href="{{ route('articles.index') }}" class="nav-link text-secondary">✅ Gestion des articles</a>
                <a href="{{ route('services.index') }}" class="nav-link text-secondary">✅ Gestion des services</a>
                
                @if (Auth::User()->role === 'admin')
                    <a href="{{ route('manager.gestionnaire') }}" class="nav-link text-secondary">🧑 Gestionnaire</a>
                @endif
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-secondary" data-bs-toggle="dropdown" href="#">💰 Charges</a>
                    <ul class="dropdown-menu">
                        @if (Auth::User()->role === 'admin')
                        <li><a class="dropdown-item" href="{{ route('manager.payroll.index') }}">👥 Salaire</a></li>
                        @endif  
                        <li><a class="dropdown-item" href="{{ route('spenses.index') }}">📦 Autres Dépenses</a></li>
                    </ul>
                </div>
                @if (Auth::User()->role === 'admin')
                    <a href="{{ route('statistics') }}" class="nav-link text-secondary">📊 Statistiques</a>
                @endif
                <a href="#" class="nav-link text-secondary">⚙️ Paramètres</a>
                <a href="{{ route('dashboard') }}" class="nav-link text-secondary">🏠 Tableau de bord</a>
            @endif
        </nav>

        <div class="mt-auto p-3 border-top">
            <form method="POST" action="{{route('logout')}}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">🚪 Déconnexion</button>
            </form>
        </div>
    </div>
</aside>