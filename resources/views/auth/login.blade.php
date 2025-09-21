
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('assets/auth/css/stylie.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
    <body>
        <div class="container">
            <div class="form-container">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-dasharray="28" stroke-dashoffset="28" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                            <path d="M4 21v-1c0 -3.31 2.69 -6 6 -6h4c3.31 0 6 2.69 6 6v1">
                                <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.4s" values="28;0"/>
                            </path>
                            <path d="M12 11c-2.21 0 -4 -1.79 -4 -4c0 -2.21 1.79 -4 4 -4c2.21 0 4 1.79 4 4c0 2.21 -1.79 4 -4 4Z">
                                <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.4s" dur="0.4s" values="28;0"/>
                            </path>
                        </g>
                    </svg>
                </h2>
                <form class="form" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <input type="text" id="username" name="username" placeholder="Username" value="{{ old('username') }}">
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" placeholder="Password">
                    </div>
                    <button type="submit" class="submit-btn">Login</button>
                </form>
                @if(session('error'))
                    <div class="alert alert-danger" style="margin-top:10px;">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let successMessage = @json(session('success'));
                let errorMessage = @json(session('error'));

                if (successMessage) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: successMessage,
                        confirmButtonColor: '#a05353',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-success-popup',
                            title: 'custom-title',
                            content: 'custom-text',
                            confirmButton: 'custom-confirm-button'
                        }
                    });
                }

                if (errorMessage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage,
                        confirmButtonColor: '#667eea',
                        confirmButtonText: 'Coba Lagi',
                        customClass: {
                            popup: 'custom-error-popup',
                            title: 'custom-title',
                            content: 'custom-text',
                            confirmButton: 'custom-confirm-button'
                        }
                    });
                }
            });
        </script>
    </body>
</html>