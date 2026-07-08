<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ==========================
    // HALAMAN LOGIN
    // ==========================

    public function showLogin()
    {
        return view('auth.login');
    }

    // ==========================
    // HALAMAN REGISTER
    // ==========================

    public function showRegister()
    {
        return view('auth.register');
    }

    // ==========================
    // REGISTER MANUAL
    // ==========================

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|max:20',
            'identity_number' => 'required|max:30',
            'address' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {

            $user = User::create([
                'role_id' => 2,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Guest::create([
                'user_id' => $user->id,
                'identity_number' => $request->identity_number,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            DB::commit();

            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil, silakan login.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'register' => 'Registrasi gagal.'
            ]);
        }
    }

    // ==========================
    // LOGIN MANUAL
    // ==========================

    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if (!Auth::attempt($request->only('email','password'))) {

            return back()->withErrors([
                'login'=>'Email atau Password salah.'
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role->name == 'Admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }

    // ==========================
    // GOOGLE LOGIN
    // ==========================

    public function redirectGoogleLogin()
    {
        session(['google_action'=>'login']);

        return Socialite::driver('google')->redirect();
    }

    // ==========================
    // GOOGLE REGISTER
    // ==========================

    public function redirectGoogleRegister()
    {
        session(['google_action'=>'register']);

        return Socialite::driver('google')->redirect();
    }

    // ==========================
    // CALLBACK GOOGLE
    // ==========================

    public function handleGoogleCallback(Request $request)
    {
        $googleUser = Socialite::driver('google')->user();

        $action = session('google_action');

        $user = User::where('email',$googleUser->getEmail())->first();

        // =====================
        // LOGIN GOOGLE
        // =====================

        if($action == 'login'){

            if(!$user){

                return redirect()->route('login')
                    ->withErrors([
                        'google'=>'Akun belum terdaftar. Silakan register terlebih dahulu.'
                    ]);
            }

            Auth::login($user);

            $request->session()->regenerate();

            if($user->role->name == 'Admin'){
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('customer.dashboard');
        }

        // =====================
        // REGISTER GOOGLE
        // =====================

        if(!$user){

            DB::beginTransaction();

            try{

                $user = User::create([
                    'role_id'=>2,
                    'name'=>$googleUser->getName(),
                    'email'=>$googleUser->getEmail(),
                    'password'=>Hash::make(Str::random(20)),
                ]);

                Guest::create([
                    'user_id'=>$user->id,
                    'identity_number'=>'-',
                    'phone'=>'-',
                    'address'=>'-',
                ]);

                DB::commit();

            }catch(\Exception $e){

                DB::rollBack();

                return redirect()->route('register')
                    ->withErrors([
                        'google'=>'Registrasi Google gagal.'
                    ]);
            }

        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    // ==========================
    // LOGOUT
    // ==========================

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
