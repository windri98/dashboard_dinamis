<?php

namespace App\Http\Controllers\Auth;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class LoginRegisterController extends Controller
{
    /**
     * AUTENTIKASI USER
     */

    /**
     * Menampilkan form login
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Menangani proses login
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Coba autentikasi pengguna
        if (Auth::attempt($credentials)) {
            // Jika autentikasi berhasil
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // Jika autentikasi gagal, kembalikan ke halaman login dengan pesan error
        return back()->with('error', 'Username atau password tidak valid.');
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * REGISTRASI USER
     */

    /**
     * Menampilkan form register
     *
     * @return \Illuminate\View\View
     */
    // public function showRegisterForm()
    // {
    //     return view('auth.register');
    // }

    /**
     * Menangani proses registrasi
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function register(Request $request)
    // {
    //     // Validasi input
    //     $request->validate([
    //         'id_card' => 'required|string|max:250|unique:users,id_card',
    //         'jabatan' => 'required|string|max:250',
    //         'nama' => 'required|string|max:250',
    //         'username' => 'required|string|max:250|unique:users,username',
    //         'password' => 'required|string|min:6|confirmed',
    //     ]);

    //     // Cari role default
    //     $defaultRole = Roles::where('role', 'user')->first();

    //     if (!$defaultRole) {
    //         return Redirect::back()->with('error', 'Default role "user" is not defined. Please contact the administrator.');
    //     }

    //     // Buat user baru
    //     $user = User::create([
    //         'id_card' => $request->id_card,
    //         'jabatan' => $request->jabatan,
    //         'nama' => $request->nama,
    //         'username' => $request->username,
    //         'password' => Hash::make($request->password),
    //         'role_id' => $defaultRole->id,
    //     ]);

    //     return redirect()->route('login')
    //         ->withSuccess('Akun Telah Terdaftar! Silakan login dengan akun baru Anda.');
    // }
}
