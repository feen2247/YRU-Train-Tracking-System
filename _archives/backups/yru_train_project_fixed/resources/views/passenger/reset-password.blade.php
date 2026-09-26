<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ - YRU EV Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 m-4">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-pink-100 text-pink-600 mb-4">
                <i class="fas fa-lock text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">ตั้งรหัสผ่านใหม่</h2>
            <p class="text-sm text-gray-500 mt-2">กรุณากำหนดรหัสผ่านใหม่สำหรับบัญชีของคุณ</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-600 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-key text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-colors" placeholder="อย่างน้อย 8 ตัวอักษร">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-check-circle text-gray-400"></i>
                        </div>
                        <input type="password" name="password_confirmation" required class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition-colors" placeholder="กรอกรหัสผ่านอีกครั้ง">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full mt-6 bg-pink-600 hover:bg-pink-700 text-white font-medium py-2.5 rounded-lg transition-colors shadow-lg shadow-pink-600/30">
                บันทึกรหัสผ่านใหม่
            </button>
        </form>
    </div>

</body>
</html>
