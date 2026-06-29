<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') - {{ config('app.name', 'ETMS') }}</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="auth-bg">

    <div class="container fade-in">
        <div class="row justify-content-center mt-5 pt-5">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card auth-card">
                    <div class="row g-0">
                        <div class="col-lg-6 d-none d-lg-block auth-illustration">
                            <div class="text-center px-4" style="z-index: 1;">
                                <i class="fas fa-layer-group fa-4x mb-4 text-white opacity-75"></i>
                                <h2 class="font-weight-bold mb-3" style="font-size: 2rem; letter-spacing: 1px;">ETMS Enterprise</h2>
                                <p class="lead opacity-75 mb-0" style="font-size: 1.1rem; line-height: 1.6;">Streamline your workflow, resolve tickets faster, and deliver exceptional service.</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card-body p-5">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
