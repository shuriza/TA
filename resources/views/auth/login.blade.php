<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- SSO Status Message -->
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- SIAKAD SSO Notice -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800">Login dengan Akun SIAKAD</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Gunakan NIM/NIP dan password SIAKAD Polinema Anda untuk login.
                    <br>
                    <a href="{{ config('services.siakad.url') }}" target="_blank" class="underline hover:text-blue-900">
                        Portal SIAKAD Polinema →
                    </a>
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('siakad.login') }}">
        @csrf

        <!-- Username (NIM/NIP/Email) -->
        <div>
            <x-input-label for="username" value="NIM / NIP / Email" />
            <x-text-input 
                id="username" 
                class="block mt-1 w-full" 
                type="text" 
                name="username" 
                :value="old('username')" 
                required 
                autofocus 
                autocomplete="username"
                placeholder="Masukkan NIM, NIP, atau Email"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">Contoh: 2341760001 (untuk mahasiswa) atau 198001012000 (untuk dosen)</p>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password SIAKAD" />

            <x-text-input 
                id="password" 
                class="block mt-1 w-full"
                type="password"
                name="password"
                required 
                autocomplete="current-password"
                placeholder="Password SIAKAD Anda"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Login dengan SIAKAD
            </x-primary-button>
        </div>
    </form>

    <!-- Fallback to standard login -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <p class="text-center text-sm text-gray-600">
            Tidak bisa login dengan SIAKAD? 
            <a href="{{ route('login.standard') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                Login dengan Email
            </a>
        </p>
    </div>
</x-guest-layout>
