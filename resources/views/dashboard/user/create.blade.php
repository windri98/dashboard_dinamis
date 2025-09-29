@extends('layouts.app')

@section('content')

<section class="roles-section" id="create-user">
    <div class="roles-header">
        <h1>Tambah User</h1>
        <a href="{{ route('settings.users.index') }}" class="back-button">
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

    @if ($errors->any())
        <div class="alert error-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert error-alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="container-create">
        <form action="{{ route('create.user') }}" method="POST" class="form-container">
            @csrf   
            <div class="form-group">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama" value="{{ old('nama') }}" required maxlength="250">
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan Username" value="{{ old('username') }}" required maxlength="250">
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Role:</label>
                <select name="role_id" id="role" class="form-control" required>
                    <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->role) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Konfirm Password:</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Masukkan Konfirmasi Password" required minlength="6">
            </div>
            
            <div class="button-group">
                <button class="primary-button" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</section>
@endsection