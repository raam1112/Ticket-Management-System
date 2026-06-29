<nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <form action="{{ route('tickets.index') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search px-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control bg-light border-0 small" placeholder="Search tickets..." aria-label="Search" value="{{ request('search') }}">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ms-auto px-3">

        <!-- Nav Item - Dark Mode Toggle -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link text-secondary" href="#" id="themeToggleBtn" role="button" title="Toggle Theme">
                <i class="fas fa-moon fa-fw" id="themeIcon"></i>
            </a>
        </li>

        <!-- Nav Item - Alerts -->
        @php
            $unreadCount = auth()->user()->unreadNotifications->count();
        @endphp
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle text-secondary" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                @if($unreadCount > 0)
                    <span class="badge badge-danger badge-counter" style="position:absolute; top:15px; right:5px; font-size:10px;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in p-0" aria-labelledby="alertsDropdown" style="min-width: 300px;">
                <h6 class="dropdown-header bg-primary text-white p-3 font-weight-bold rounded-top">
                    Alerts Center
                </h6>
                @forelse(auth()->user()->unreadNotifications->take(4) as $notification)
                    <a class="dropdown-item d-flex align-items-center p-3 border-bottom" href="{{ route('notifications.index') }}">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary text-white rounded-circle p-2" style="width:40px; height:40px; text-align:center;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div style="padding-left:10px;">
                            <div class="small text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                            <span class="font-weight-bold">{{ $notification->data['message'] ?? 'New Notification' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="p-3 text-center text-muted small">No new notifications.</div>
                @endforelse
                <a class="dropdown-item text-center small text-gray-500 p-2" href="{{ route('notifications.index') }}">Show All Alerts</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block" style="border-left:1px solid #e3e6f0; margin:auto 1rem; height:calc(100% - 2rem);"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle text-secondary d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small me-2">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}" style="width:32px; height:32px;">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer bg-light border-0">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Theme Toggle Logic
    $(document).ready(function() {
        const themeIcon = $('#themeIcon');
        const themeToggleBtn = $('#themeToggleBtn');
        
        function updateIcon(state) {
            // state can be 'light', 'dark', or 'auto'
            themeIcon.removeClass('fa-moon fa-sun fa-desktop text-warning text-primary text-secondary');
            
            if (state === 'dark') {
                themeIcon.addClass('fa-moon text-warning');
                themeToggleBtn.attr('title', 'Dark Mode (Click to use Auto)');
            } else if (state === 'light') {
                themeIcon.addClass('fa-sun text-secondary');
                themeToggleBtn.attr('title', 'Light Mode (Click to use Dark)');
            } else {
                themeIcon.addClass('fa-desktop text-primary');
                themeToggleBtn.attr('title', 'Auto Mode (Click to use Light)');
            }
        }
        
        function applyThemeState(state) {
            let activeTheme = state;
            if (state === 'auto') {
                let hour = new Date().getHours();
                activeTheme = (hour >= 18 || hour < 7) ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', activeTheme);
            document.documentElement.setAttribute('data-theme-state', state);
            localStorage.setItem('etms_theme', state);
            updateIcon(state);
        }

        // Initialize icon on load
        const currentState = document.documentElement.getAttribute('data-theme-state') || 'auto';
        updateIcon(currentState);
        
        $('#themeToggleBtn').on('click', function(e) {
            e.preventDefault();
            let state = document.documentElement.getAttribute('data-theme-state') || 'auto';
            let newState = 'auto';
            
            if (state === 'auto') newState = 'light';
            else if (state === 'light') newState = 'dark';
            else if (state === 'dark') newState = 'auto';
            
            applyThemeState(newState);
        });

        // Check time periodically if in auto mode
        setInterval(function() {
            let state = document.documentElement.getAttribute('data-theme-state') || 'auto';
            if (state === 'auto') {
                let hour = new Date().getHours();
                let expectedTheme = (hour >= 18 || hour < 7) ? 'dark' : 'light';
                if (document.documentElement.getAttribute('data-bs-theme') !== expectedTheme) {
                    document.documentElement.setAttribute('data-bs-theme', expectedTheme);
                }
            }
        }, 60000); // Check every minute
    });

    let lastNotifCount = {{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }};

    function fetchLiveNotifications() {
        $.ajax({
            url: "{{ route('notifications.unread') }}",
            type: "GET",
            success: function(response) {
                let count = response.count;
                let badge = $('#alertsDropdown .badge-counter');
                
                if (count > lastNotifCount && response.notifications.length > 0) {
                    let newNotif = response.notifications[0];
                    if ($('#liveToast').length) {
                        $('#liveToast .toast-body').html(newNotif.message);
                        $('#liveToast .toast-time').text(newNotif.time);
                        let toastElement = document.getElementById('liveToast');
                        let toast = new bootstrap.Toast(toastElement);
                        toast.show();
                    }
                }
                lastNotifCount = count;
                
                if (count > 0) {
                    let displayCount = count > 9 ? '9+' : count;
                    if (badge.length === 0) {
                        $('#alertsDropdown').append('<span class="badge badge-danger badge-counter" style="position:absolute; top:15px; right:5px; font-size:10px;">' + displayCount + '</span>');
                    } else {
                        badge.text(displayCount);
                    }
                } else {
                    badge.remove();
                }

                let notifList = $('#alertsDropdown').next('.dropdown-menu');
                let header = notifList.find('.dropdown-header').prop('outerHTML');
                let footer = notifList.find('.dropdown-item.text-center').prop('outerHTML');
                
                let html = header;
                if (response.notifications.length > 0) {
                    response.notifications.forEach(function(n) {
                        html += `
                        <a class="dropdown-item d-flex align-items-center p-3 border-bottom" href="${n.url}">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary text-white rounded-circle p-2" style="width:40px; height:40px; text-align:center;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                            <div style="padding-left:10px;">
                                <div class="small text-gray-500">${n.time}</div>
                                <span class="font-weight-bold">${n.message}</span>
                            </div>
                        </a>`;
                    });
                } else {
                    html += '<div class="p-3 text-center text-muted small">No new notifications.</div>';
                }
                html += footer;
                
                notifList.html(html);
            }
        });
    }

    // Poll every 10 seconds
    setInterval(fetchLiveNotifications, 10000);
</script>
@endpush
