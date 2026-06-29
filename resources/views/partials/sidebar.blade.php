<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}" style="text-decoration:none;">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <div class="sidebar-brand-text mx-3">ETMS <sup>Pro</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0 border-white opacity-25">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider border-white opacity-25">

    <!-- Heading -->
    <div class="sidebar-heading px-3 pt-2 text-white-50 text-uppercase text-xs font-weight-bold">
        Tickets
    </div>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('tickets.create') ? 'active' : '' }}" href="{{ route('tickets.create') }}">
            <i class="fas fa-fw fa-plus-circle"></i>
            <span>Create Ticket</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
            <i class="fas fa-fw fa-list"></i>
            <span>All Tickets</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider border-white opacity-25">

    <!-- Heading -->
    <div class="sidebar-heading px-3 pt-2 text-white-50 text-uppercase text-xs font-weight-bold">
        Resources
    </div>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('kb.*') ? 'active' : '' }}" href="{{ route('kb.index') }}">
            <i class="fas fa-fw fa-book"></i>
            <span>Knowledge Base</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Reports</span>
        </a>
    </li>

    @if(auth()->user()->hasAnyRole(['admin', 'team_lead']))
        <!-- Divider -->
        <hr class="sidebar-divider border-white opacity-25">

        <!-- Heading -->
        <div class="sidebar-heading px-3 pt-2 text-white-50 text-uppercase text-xs font-weight-bold">
            Administration
        </div>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.sla.*') ? 'active' : '' }}" href="{{ route('admin.sla.index') }}">
                <i class="fas fa-fw fa-clock"></i>
                <span>SLA Policies</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Users & Roles</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.kb-articles.*') ? 'active' : '' }}" href="{{ route('admin.kb-articles.index') }}">
                <i class="fas fa-fw fa-book-open"></i>
                <span>Manage KB Articles</span>
            </a>
        </li>

        @if(auth()->user()->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Departments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <i class="fas fa-fw fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.priorities.*') ? 'active' : '' }}" href="{{ route('admin.priorities.index') }}">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Priorities</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>System Settings</span>
                </a>
            </li>
        @endif
    @endif

</ul>
