@extends('layouts.app')

@section('content')

    <section class="roles-section" id="edit-user">
        <div class="roles-header">
            <h1>Ubah Password</h1>
            <a href="/dashboard" class="back-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                Kembali
            </a>
        </div>
        
        @if (session('success'))
            <div class="alert success-alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert error-alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="container-edit">
            <form action="{{ route('update.password') }}" method="POST" class="form-container" id="passwordForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="passlama" class="form-label">Password Lama:</label>
                    <div class="password-input-container">
                        <input type="password" name="passlama" id="passlama" class="form-control @error('passlama') is-invalid @enderror" placeholder="Masukkan Password Lama" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('passlama')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    @error('passlama')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="passbaru" class="form-label">Password Baru:</label>
                    <div class="password-input-container">
                        <input type="password" name="passbaru" id="passbaru" class="form-control @error('passbaru') is-invalid @enderror" placeholder="Masukkan Password Baru (min. 8 karakter)" minlength="8" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('passbaru')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    <small class="form-text text-muted">Password minimal 8 karakter dan tidak boleh sama dengan password lama</small>
                    @error('passbaru')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="passbaru_confirmation" class="form-label">Konfirmasi Password Baru:</label>
                    <div class="password-input-container">
                        <input type="password" name="passbaru_confirmation" id="passbaru_confirmation" class="form-control @error('passbaru_confirmation') is-invalid @enderror" placeholder="Ketik ulang Password Baru" minlength="8" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility('passbaru_confirmation')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                    <div id="password-match-indicator" class="mt-1"></div>
                    @error('passbaru_confirmation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="button-group">
                    <button class="primary-button" type="submit" id="submit-button">Update Password</button>
                </div>
            </form>
        </div>
    </section>

<script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        }
        
        // Check if passwords match
        document.addEventListener("DOMContentLoaded", function() {
            const passbaruField = document.getElementById('passbaru');
            const confirmField = document.getElementById('passbaru_confirmation');
            const indicator = document.getElementById('password-match-indicator');
            
            function checkPasswordMatch() {
                if (confirmField.value === '') {
                    indicator.innerHTML = '';
                    return;
                }
                
                if (passbaruField.value === confirmField.value) {
                    indicator.innerHTML = '<span class="password-match">Password cocok ✓</span>';
                } else {
                    indicator.innerHTML = '<span class="password-mismatch">Password tidak cocok ✗</span>';
                }
            }
            
            passbaruField.addEventListener('keyup', checkPasswordMatch);
            confirmField.addEventListener('keyup', checkPasswordMatch);
            
            // Form submission validation
            document.getElementById('passwordForm').addEventListener('submit', function(event) {
                if (passbaruField.value !== confirmField.value) {
                    event.preventDefault();
                    indicator.innerHTML = '<span class="password-mismatch">Konfirmasi Password Baru harus sama dengan Password Baru</span>';
                }
            });
        });

        // alert
    document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 - Tampilkan Notifikasi Jika Data Berhasil atau Gagal
    let successMessage = @json(session('success'));
    let errorMessage = @json(session('error'));

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: successMessage,
            confirmButtonColor: '#1f6e8c',
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
            confirmButtonColor: '#ff0000',
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
    {{-- alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection