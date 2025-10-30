<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD Polinema - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="https://polinema.ac.id/wp-content/uploads/2023/01/Logo-Polinema-Horizontal-Putih-1024x341.png" 
                             alt="Polinema" class="h-12">
                        <h1 class="text-xl font-bold">SIAKAD Polinema</h1>
                    </div>
                    <div class="text-right">
                        <p class="text-sm">Selamat Datang,</p>
                        <p class="font-semibold">{{ $nama }}</p>
                        <p class="text-xs">NIM/NIP: {{ $identifier }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- User Profile Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Profil Pengguna</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Nama Lengkap</p>
                            <p class="font-medium">{{ $nama }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ $role === 'mahasiswa' ? 'NIM' : 'NIP' }}</p>
                            <p class="font-medium">{{ $identifier }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $email }}</p>
                        </div>
                        @if($role === 'mahasiswa')
                        <div>
                            <p class="text-sm text-gray-600">Program Studi</p>
                            <p class="font-medium">{{ $prodi }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- LMS Access Card - INI YANG PENTING -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-center mb-2">LMS Cerdas</h2>
                    <p class="text-center text-green-100 mb-6">
                        Akses Learning Management System untuk melihat materi kuliah, tugas, dan pengumuman.
                    </p>
                    
                    <!-- TOMBOL CONNECT TO LMS -->
                    <form action="{{ $ssoUrl }}" method="GET" id="lmsForm">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="timestamp" value="{{ $timestamp }}">
                        <input type="hidden" name="signature" value="{{ $signature }}">
                        
                        <button type="submit" 
                                class="w-full bg-white text-green-600 font-bold py-3 px-6 rounded-lg hover:bg-green-50 transition duration-200 shadow-lg flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <span>Connect to LMS</span>
                        </button>
                    </form>
                    
                    <p class="text-center text-sm text-green-100 mt-4">
                        Klik untuk login otomatis tanpa input ulang password
                    </p>
                </div>

                <!-- Other Services -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Layanan Lainnya</h2>
                    <div class="space-y-3">
                        <a href="#" class="block p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            <p class="font-medium text-blue-700">📚 SPADA</p>
                            <p class="text-xs text-gray-600">E-learning Polinema</p>
                        </a>
                        <a href="#" class="block p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                            <p class="font-medium text-purple-700">📝 KRS Online</p>
                            <p class="text-xs text-gray-600">Kartu Rencana Studi</p>
                        </a>
                        <a href="#" class="block p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition">
                            <p class="font-medium text-orange-700">📊 Nilai</p>
                            <p class="text-xs text-gray-600">Lihat Nilai & Transkrip</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Simulasi SIAKAD Portal:</strong> Ini adalah halaman demo untuk menunjukkan alur SSO dari SIAKAD ke LMS. 
                            Di sistem production, halaman ini adalah bagian dari Portal SIAKAD Polinema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit after 3 seconds (optional)
        // setTimeout(() => {
        //     document.getElementById('lmsForm').submit();
        // }, 3000);
    </script>
</body>
</html>
