<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - เข้าสู่ระบบ</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/api/storage/init.js?v={{ time() }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Kanit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th { font-family: 'Kanit', sans-serif !important; }
        .pink-gradient { background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%); }

        /* Hide Edge/IE native password reveal eye icon */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }

        /* Inline error message styles */
        .field-error {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            padding: 8px 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 400;
            animation: slideDown 0.3s ease-out;
        }
        .field-error.show {
            display: flex;
        }
        .field-error svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
        }
        .input-error {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.15) !important;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }
        .shake {
            animation: shake 0.4s ease-in-out;
        }
    </style>
</head>
<body class="bg-pink-50 flex items-center justify-center min-h-screen py-10">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 border border-pink-100 overflow-hidden">

        <div class="p-8 text-center pb-2">
            <img src="{{ asset('img/logo-yru.png') }}" alt="YRU Logo" class="mx-auto h-28 object-contain mb-4">
            <h1 class="text-[17px] font-bold text-gray-800 leading-tight">ระบบติดตามเส้นทางการเดินรถไฟฟ้า</h1>
            <h2 class="text-[17px] font-bold text-gray-800 leading-tight mt-1">มหาวิทยาลัยราชภัฏยะลา</h2>
            <div class="w-12 h-1 bg-pink-400 mx-auto mt-4 rounded-full"></div>
        </div>

        {{-- Card Body --}}
        <div class="p-6">
            <p class="text-sm text-gray-500 font-light mb-5"></p>

            <form id="mockLoginForm" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">อีเมล หรือ รหัสนักศึกษา <span class="text-red-500">*</span></label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                        placeholder="อีเมล / รหัสนักศึกษา">
                    <div id="email-login-error" class="field-error">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="email-login-error-text"></span>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่าน <span class="text-red-500">*</span></label>
                    <div class="flex flex-row items-center w-full border border-pink-200 rounded-lg focus-within:ring-2 focus-within:ring-pink-400 bg-white transition-all overflow-hidden relative z-10">
                        <input type="password" id="password" name="password" required
                            autocomplete="current-password"
                            class="flex-1 w-full pl-4 py-3 outline-none border-none bg-transparent font-sans placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword" class="flex-shrink-0 px-3 py-2 flex items-center justify-center text-gray-500 hover:text-pink-600 transition-colors focus:outline-none bg-transparent">
                            <svg id="eyeIcon" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <div id="password-login-error" class="field-error">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="password-login-error-text"></span>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 text-pink-500 border-pink-300 rounded focus:ring-pink-400 accent-pink-500">
                        <span class="text-sm text-gray-600 ml-1">จดจำฉันไว้</span>
                    </label>
                    <a href="#" id="forgotPasswordLink" class="text-sm text-pink-500 hover:text-pink-700 hover:underline transition-colors">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <div class="pt-4 space-y-4">
                    <button type="submit"
                        class="w-full bg-[#ec4899] hover:bg-pink-600 text-white font-medium rounded-lg shadow-sm transition-all flex items-center justify-center h-[50px]">
                        เข้าสู่ระบบ
                    </button>
                    <button type="button" onclick="openRegisterModal()"
                        class="w-full border border-[#ec4899] text-[#ec4899] font-medium rounded-lg hover:bg-pink-50 transition-colors flex items-center justify-center h-[50px] gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        สมัครสมาชิก
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Forgot Password Modal Overlay -->
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center min-h-screen py-10 px-4 hidden z-50 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-pink-100 overflow-hidden transform scale-95 transition-transform duration-300">
            <!-- Header -->
            <div class="p-5 text-white flex justify-between items-center" style="background-color: #D81B60;">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    กู้คืนรหัสผ่าน
                </h3>
                <button onclick="closeForgotModal()" class="text-white/80 hover:text-white transition-colors outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <!-- Step 1: Input Email -->
                <div id="step-email" class="space-y-5">
                    <p class="text-sm text-gray-600 font-light font-kanit">กรุณากรอก Username ของท่านเพื่อขอรับรหัส OTP ทางอีเมล</p>
                    <div>
                        <label for="forgot_email" class="block text-sm font-medium text-gray-700 mb-2 font-kanit">Username</label>
                        <input type="text" id="forgot_email" required 
                            class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light font-kanit"
                            placeholder="กรอก Username เช่น muhammad หรือ somchai">
                    </div>
                    <div id="email-error" class="text-red-500 text-sm hidden font-light font-kanit"></div>
                    <div class="flex gap-3 pt-2">
                        <button onclick="closeForgotModal()" class="w-1/3 border border-pink-200 text-pink-500 font-bold py-3 rounded-lg hover:bg-pink-50 transition-colors flex items-center justify-center font-kanit">
                            ยกเลิก
                        </button>
                        <button onclick="submitEmailRequest()" id="btn-submit-email" class="w-2/3 border border-transparent text-white font-bold py-3 rounded-lg shadow-md hover:brightness-110 transition-all flex items-center justify-center gap-2 font-kanit" style="background-color: #D81B60;">
                            <span>ส่งข้อมูล</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: OTP Verification Form -->
                <div id="step-otp-verify" class="space-y-4 hidden">
                    <p class="text-sm text-gray-600 font-light font-kanit">เราได้ส่งรหัส OTP 6 หลักไปยังอีเมลของท่านแล้ว กรุณากรอกรหัสเพื่อยืนยันตัวตน</p>
                    
                    <div>
                        <label for="forgot_otp" class="block text-sm font-medium text-gray-700 mb-2 font-kanit">รหัส OTP 6 หลัก</label>
                        <input type="text" id="forgot_otp" maxlength="6" required 
                            class="w-full px-4 py-3 text-center text-2xl tracking-[0.25em] font-mono border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300"
                            placeholder="000000">
                    </div>
                    
                    <div class="flex justify-between items-center text-sm font-kanit">
                        <span class="text-gray-500 font-light">รหัสผ่านหมดอายุใน: <span id="otp-timer" class="font-bold text-pink-600">05:00</span> นาที</span>
                        <button id="btn-resend-otp" onclick="resendOtp()" disabled class="text-pink-600 hover:text-pink-800 disabled:text-gray-400 font-medium transition-colors">
                            ส่งรหัสซ้ำ (60s)
                        </button>
                    </div>

                    <div id="otp-error" class="text-red-500 text-sm hidden font-light font-kanit"></div>

                    <div class="flex gap-3 pt-2">
                        <button onclick="showStep('step-email')" class="w-1/3 border border-pink-200 text-pink-500 font-bold py-3 rounded-lg hover:bg-pink-50 transition-colors flex items-center justify-center font-kanit">
                            ย้อนกลับ
                        </button>
                        <button onclick="submitOtpVerify()" id="btn-submit-otp" class="w-2/3 border border-transparent text-white font-bold py-3 rounded-lg shadow-md hover:brightness-110 transition-all flex items-center justify-center gap-2 font-kanit" style="background-color: #D81B60;">
                            <span>ยืนยัน OTP</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: New Password Form -->
                <div id="step-new-password" class="space-y-5 hidden">
                    <p class="text-sm text-gray-600 font-light font-kanit">กรุณากำหนดรหัสผ่านใหม่สำหรับความปลอดภัยในบัญชีของท่าน</p>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2 font-kanit">รหัสผ่านใหม่</label>
                        <div class="flex flex-row items-center w-full border border-pink-200 rounded-lg focus-within:ring-2 focus-within:ring-pink-400 bg-white transition-all overflow-hidden relative z-10">
                            <input type="password" id="new_password" required 
                                class="flex-1 w-full pl-4 py-3 outline-none border-none bg-transparent font-kanit placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                                placeholder="••••••••">
                            <button type="button" id="toggleNewPassword" class="flex-shrink-0 px-3 py-2 flex items-center justify-center text-gray-400 hover:text-pink-500 transition-colors focus:outline-none bg-transparent">
                                <svg id="newEyeIcon" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2 font-kanit">ยืนยันรหัสผ่านใหม่</label>
                        <div class="flex flex-row items-center w-full border border-pink-200 rounded-lg focus-within:ring-2 focus-within:ring-pink-400 bg-white transition-all overflow-hidden relative z-10">
                            <input type="password" id="confirm_password" required 
                                class="flex-1 w-full pl-4 py-3 outline-none border-none bg-transparent font-kanit placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                                placeholder="••••••••">
                            <button type="button" id="toggleConfirmPassword" class="flex-shrink-0 px-3 py-2 flex items-center justify-center text-gray-400 hover:text-pink-500 transition-colors focus:outline-none bg-transparent">
                                <svg id="confirmEyeIcon" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="password-error" class="text-red-500 text-sm hidden font-light font-kanit"></div>
                    <button onclick="submitNewPassword()" id="btn-submit-password" class="w-full text-white font-bold py-2.5 rounded-lg shadow-md hover:brightness-110 transition-all flex items-center justify-center gap-2 font-kanit" style="background-color: #D81B60;">
                        <span>บันทึกรหัสผ่านใหม่</span>
                    </button>
                </div>

                <!-- Step 4: Success confirmation screen -->
                <div id="step-success" class="text-center py-6 space-y-4 hidden">
                    <div class="w-16 h-16 bg-green-50 border border-green-200 rounded-full flex items-center justify-center mx-auto text-green-500">
                        <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-lg font-bold text-gray-800 font-kanit">ส่งลิงก์สำเร็จ!</h4>
                        <p class="text-sm text-gray-500 font-light font-kanit">เราได้ส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปยังอีเมลของคุณแล้ว กรุณาตรวจสอบอีเมลของคุณ (และในโฟลเดอร์จดหมายขยะ/สแปม)</p>
                    </div>
                    <button onclick="closeForgotModal()" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 rounded-lg shadow-md transition-colors font-kanit">
                        ตกลง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal Overlay -->
    <div id="registerModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center min-h-screen py-10 px-4 hidden z-50 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-pink-100 overflow-hidden transform scale-95 transition-transform duration-300">
            <!-- Header -->
            <div class="pink-gradient p-5 text-white flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    สมัครสมาชิก
                </h3>
                <button onclick="closeRegisterModal()" class="text-white/80 hover:text-white transition-colors outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <!-- Step 1: Registration Form -->
                <div id="register-form-step" class="space-y-5">
                    <p class="text-sm text-gray-600 font-light">กรุณากรอกข้อมูลเพื่อสมัครสมาชิกเข้าใช้งานระบบ</p>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="w-full sm:w-1/4">
                            <label for="reg_title" class="block text-sm font-medium text-gray-700 mb-2">คำนำหน้า <span class="text-red-500">*</span></label>
                            <input type="text" id="reg_title" required 
                                class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light h-[50px]"
                                placeholder="เช่น นาย">
                        </div>
                        <div class="w-full sm:w-1/3">
                            <label for="reg_firstname" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ <span class="text-red-500">*</span></label>
                            <input type="text" id="reg_firstname" required 
                                class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light h-[50px]"
                                placeholder="เช่น สมชาย">
                        </div>
                        <div class="w-full sm:w-5/12">
                            <label for="reg_lastname" class="block text-sm font-medium text-gray-700 mb-2">นามสกุล <span class="text-red-500">*</span></label>
                            <input type="text" id="reg_lastname" required 
                                class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light h-[50px]"
                                placeholder="เช่น ใจดี">
                        </div>
                    </div>
                    <div>
                        <label for="reg_email" class="block text-sm font-medium text-gray-700 mb-2">อีเมล <span class="text-red-500">*</span></label>
                        <input type="email" id="reg_email" required 
                            class="w-full px-4 py-3 border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                            placeholder="example@gmail.com">
                    </div>
                    <div>
                        <label for="reg_password" class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่าน <span class="text-red-500">*</span></label>
                        <div class="flex flex-row items-center w-full border border-pink-200 rounded-lg focus-within:ring-2 focus-within:ring-pink-400 bg-white transition-all overflow-hidden relative z-10">
                            <input type="password" id="reg_password" required 
                                class="flex-1 w-full pl-4 py-3 outline-none border-none bg-transparent font-sans placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                                placeholder="อย่างน้อย 8 ตัว (a-z, A-Z, อักขระพิเศษ)">
                            <button type="button" id="toggleRegPassword" class="flex-shrink-0 px-3 py-2 flex items-center justify-center text-gray-400 hover:text-pink-500 transition-colors focus:outline-none bg-transparent">
                                <svg id="regEyeIcon" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2 text-xs space-y-1 pl-1" id="password-requirements">
                            <p id="req-length" class="text-gray-400 flex items-center gap-1.5 transition-colors"><i class="fas fa-times-circle"></i> อย่างน้อย 8 ตัวอักษรขึ้นไป</p>
                            <p id="req-case" class="text-gray-400 flex items-center gap-1.5 transition-colors"><i class="fas fa-times-circle"></i> ตัวพิมพ์เล็ก (a-z) และตัวพิมพ์ใหญ่ (A-Z)</p>
                            <p id="req-num" class="text-gray-400 flex items-center gap-1.5 transition-colors"><i class="fas fa-times-circle"></i> ตัวเลขผสมอยู่อย่างน้อย 1 ตัว (0-9)</p>
                            <p id="req-special" class="text-gray-400 flex items-center gap-1.5 transition-colors"><i class="fas fa-times-circle"></i> อักขระพิเศษอย่างน้อย 1 ตัว (เช่น @, #, $, !, %, *, ?, &)</p>
                        </div>
                    </div>
                    <div>
                        <label for="reg_password_confirm" class="block text-sm font-medium text-gray-700 mb-2">ยืนยันรหัสผ่าน <span class="text-red-500">*</span></label>
                        <div class="flex flex-row items-center w-full border border-pink-200 rounded-lg focus-within:ring-2 focus-within:ring-pink-400 bg-white transition-all overflow-hidden relative z-10">
                            <input type="password" id="reg_password_confirm" required 
                                class="flex-1 w-full pl-4 py-3 outline-none border-none bg-transparent font-sans placeholder:text-gray-300 placeholder:text-base placeholder:font-light"
                                placeholder="กรอกรหัสผ่านอีกครั้ง">
                            <button type="button" id="toggleRegPasswordConfirm" class="flex-shrink-0 px-3 py-2 flex items-center justify-center text-gray-400 hover:text-pink-500 transition-colors focus:outline-none bg-transparent">
                                <svg id="regConfirmEyeIcon" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="register-error" class="text-red-500 text-sm hidden font-light"></div>
                    <div class="flex gap-3 pt-2">
                        <button onclick="closeRegisterModal()" class="w-1/3 border border-pink-200 text-pink-500 font-bold rounded-lg hover:bg-pink-50 transition-colors flex items-center justify-center h-[50px]">
                            ยกเลิก
                        </button>
                        <button onclick="submitRegister()" id="btn-submit-register" class="w-2/3 border border-transparent pink-gradient text-white font-bold rounded-lg shadow-md hover:brightness-110 transition-all flex items-center justify-center gap-2 h-[50px]">
                            <span>สมัครสมาชิก</span>
                        </button>
                    </div>
                </div>

                <!-- Step 1.5: OTP Verification -->
                <div id="register-otp-step" class="space-y-4 hidden">
                    <p class="text-sm text-gray-600 font-light font-kanit">เราได้ส่งรหัส OTP 6 หลักไปยังอีเมล <span id="register-display-email" class="font-medium text-pink-600"></span> แล้ว กรุณากรอกรหัสเพื่อยืนยันตัวตน</p>
                    
                    <div>
                        <label for="register_otp" class="block text-sm font-medium text-gray-700 mb-2 font-kanit">รหัส OTP 6 หลัก</label>
                        <input type="text" id="register_otp" maxlength="6" required 
                            class="w-full px-4 py-3 text-center text-2xl tracking-[0.25em] font-mono border border-pink-200 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none transition-all placeholder:text-gray-300"
                            placeholder="000000">
                    </div>
                    
                    <div class="flex justify-between items-center text-sm font-kanit">
                        <span class="text-gray-500 font-light">รหัสผ่านหมดอายุใน: <span id="register-otp-timer" class="font-bold text-pink-600">05:00</span> นาที</span>
                        <button id="btn-resend-register-otp" onclick="resendRegisterOtp()" disabled class="text-pink-600 hover:text-pink-800 disabled:text-gray-400 font-medium transition-colors">
                            ส่งรหัสซ้ำ (60s)
                        </button>
                    </div>

                    <div id="register-otp-error" class="text-red-500 text-sm hidden font-light font-kanit"></div>

                    <div class="flex gap-3 pt-2">
                        <button onclick="closeRegisterModal()" class="w-1/3 border border-pink-200 text-pink-500 font-bold py-3 rounded-lg hover:bg-pink-50 transition-colors flex items-center justify-center font-kanit">
                            ยกเลิก
                        </button>
                        <button onclick="submitRegisterOtpVerify()" id="btn-submit-register-otp" class="w-2/3 border border-transparent text-white font-bold py-3 rounded-lg shadow-md hover:brightness-110 transition-all flex items-center justify-center gap-2 font-kanit pink-gradient">
                            <span>ยืนยัน OTP</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Success -->
                <div id="register-success-step" class="text-center py-6 space-y-4 hidden">
                    <div class="w-16 h-16 bg-green-50 border border-green-200 rounded-full flex items-center justify-center mx-auto text-green-500">
                        <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-lg font-bold text-gray-800">สมัครสมาชิกสำเร็จ</h4>
                        <p class="text-sm text-gray-500 font-light"></p>
                    </div>
                    <button onclick="closeRegisterModal()" class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 rounded-lg shadow-md transition-colors">
                        เข้าสู่ระบบทันที
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // โหลดรหัสผู้ใช้งานและรหัสผ่านจาก localStorage ที่บันทึกไว้ (ถ้ามี)
        document.addEventListener("DOMContentLoaded", function() {
            const savedUser = localStorage.getItem("remembered_username");
            const savedPass = localStorage.getItem("remembered_password");
            const rememberCheckbox = document.getElementById("remember");

            if (savedUser && savedPass) {
                document.getElementById("username").value = savedUser;
                document.getElementById("password").value = savedPass;
                if (rememberCheckbox) {
                    rememberCheckbox.checked = true;
                }
            }
        });

        // === ฟังก์ชันแสดง/ซ่อน inline error messages ===
        function clearLoginErrors() {
            const emailErr = document.getElementById('email-login-error');
            const passErr = document.getElementById('password-login-error');
            const usernameInput = document.getElementById('username');
            const passwordWrapper = document.getElementById('password-wrapper');
            emailErr.classList.remove('show');
            passErr.classList.remove('show');
            usernameInput.classList.remove('input-error', 'shake');
            if (passwordWrapper) passwordWrapper.classList.remove('input-error', 'shake');
            passwordInput.classList.remove('input-error', 'shake');
        }

        function showLoginError(errorType, message) {
            clearLoginErrors();
            const emailErr = document.getElementById('email-login-error');
            const emailErrText = document.getElementById('email-login-error-text');
            const passErr = document.getElementById('password-login-error');
            const passErrText = document.getElementById('password-login-error-text');
            const usernameInput = document.getElementById('username');
            const passwordInputEl = document.getElementById('password');

            if (errorType === 'email_not_found') {
                emailErrText.textContent = message;
                emailErr.classList.add('show');
                usernameInput.classList.add('input-error', 'shake');
                usernameInput.focus();
            } else if (errorType === 'wrong_password') {
                passErrText.textContent = message;
                passErr.classList.add('show');
                const passwordWrapper = document.getElementById('password-wrapper');
                if (passwordWrapper) passwordWrapper.classList.add('input-error', 'shake');
                passwordInputEl.classList.add('input-error', 'shake'); // Keep for safety
                passwordInputEl.focus();
                passwordInputEl.select();
            } else {
                // general error - show under email field
                emailErrText.textContent = message;
                emailErr.classList.add('show');
                usernameInput.classList.add('input-error', 'shake');
            }
        }

        // ล้าง error เมื่อผู้ใช้เริ่มพิมพ์ใหม่
        document.getElementById('username').addEventListener('input', clearLoginErrors);
        document.getElementById('password').addEventListener('input', clearLoginErrors);

        // ระบบ Login จริงผ่าน Database
        document.getElementById('mockLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> กำลังเข้าสู่ระบบ...`;

            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value.trim();
            const remember = document.getElementById('remember') ? document.getElementById('remember').checked : false;

            fetch('/api/login-submit', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ username: u, password: p, remember: remember })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; }).catch(e => {
                        if (e instanceof SyntaxError || e.message?.includes('JSON')) {
                            throw { message: 'เซสชันหมดอายุหรือเกิดข้อผิดพลาดในระบบ กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง' };
                        }
                        throw e;
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    if (data.user) {
                        try {
                            localStorage.setItem("yru_user_login", JSON.stringify(data.user));
                            sessionStorage.setItem("yru_user_login", JSON.stringify(data.user));
                        } catch(e) {}
                    }
                    // จัดการจดจำรหัสผ่านใน localStorage ตามการเลือก "จดจำฉันไว้"
                    if (remember) {
                        localStorage.setItem("remembered_username", u);
                        localStorage.setItem("remembered_password", p);
                    } else {
                        localStorage.removeItem("remembered_username");
                        localStorage.removeItem("remembered_password");
                    }
                    window.location.href = data.redirect_url;
                } else {
                    showLoginError(data.error_type || 'general', data.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(err => {
                let msg = 'รหัสผู้ใช้งาน หรือรหัสผ่านไม่ถูกต้อง';
                if (err.message) {
                    if (err.message === 'CSRF token mismatch.') {
                        msg = 'เซสชันหมดอายุ (CSRF Mismatch) กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง';
                    } else if (err.message.includes('token') && err.message.includes('JSON')) {
                        msg = 'เซสชันหมดอายุ กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง';
                    } else {
                        msg = err.message;
                    }
                }
                showLoginError(err.error_type || 'general', msg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });

        // ระบบลืมรหัสผ่าน
        let resetToken = '';
        let resetEmail = '';
        let otpCountdownInterval = null;
        let resendCooldownInterval = null;

        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const forgotModal = document.getElementById('forgotPasswordModal');

        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            openForgotModal();
        });

        function openForgotModal() {
            forgotModal.classList.remove('hidden');
            setTimeout(() => {
                forgotModal.classList.remove('opacity-0');
                forgotModal.querySelector('.transform').classList.remove('scale-95');
            }, 50);

            showStep('step-email');
            document.getElementById('forgot_email').value = document.getElementById('username').value.trim();
            document.getElementById('email-error').classList.add('hidden');
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('password-error').classList.add('hidden');
        }

        function closeForgotModal() {
            forgotModal.classList.add('opacity-0');
            forgotModal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                forgotModal.classList.add('hidden');
            }, 300);
            clearInterval(otpCountdownInterval);
            clearInterval(resendCooldownInterval);
        }

        function showStep(stepId) {
            const steps = ['step-email', 'step-otp-verify', 'step-new-password', 'step-success'];
            steps.forEach(id => {
                const element = document.getElementById(id);
                if (id === stepId) {
                    element.classList.remove('hidden');
                } else {
                    element.classList.add('hidden');
                }
            });
        }

        function startOtpTimer() {
            clearInterval(otpCountdownInterval);
            let timeRemaining = 300; // 5 minutes
            const timerDisplay = document.getElementById('otp-timer');
            
            function updateTimer() {
                let minutes = Math.floor(timeRemaining / 60);
                let seconds = timeRemaining % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                if (timeRemaining <= 0) {
                    clearInterval(otpCountdownInterval);
                    document.getElementById('otp-error').textContent = 'รหัส OTP หมดอายุแล้ว กรุณากดขอรหัสใหม่อีกครั้ง';
                    document.getElementById('otp-error').classList.remove('hidden');
                }
                timeRemaining--;
            }
            
            updateTimer();
            otpCountdownInterval = setInterval(updateTimer, 1000);
        }

        function startResendCooldown() {
            clearInterval(resendCooldownInterval);
            let cooldownRemaining = 60;
            const resendButton = document.getElementById('btn-resend-otp');
            resendButton.disabled = true;
            
            function updateCooldown() {
                if (cooldownRemaining <= 0) {
                    clearInterval(resendCooldownInterval);
                    resendButton.disabled = false;
                    resendButton.textContent = 'ส่งรหัสซ้ำ';
                } else {
                    resendButton.disabled = true;
                    resendButton.textContent = `ส่งรหัสซ้ำ (${cooldownRemaining}s)`;
                }
                cooldownRemaining--;
            }
            
            updateCooldown();
            resendCooldownInterval = setInterval(updateCooldown, 1000);
        }

        function submitEmailRequest() {
            const emailInput = document.getElementById('forgot_email');
            const username = emailInput.value.trim();
            const errorDiv = document.getElementById('email-error');
            const btn = document.getElementById('btn-submit-email');

            if (!username) {
                errorDiv.textContent = 'กรุณากรอก Username';
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>กำลังส่ง...</span>`;

            fetch('/api/forgot-password-request', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ username: username })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; }).catch(e => {
                        if (e instanceof SyntaxError || e.message?.includes('JSON')) {
                            throw { message: 'เซสชันหมดอายุหรือเกิดข้อผิดพลาดในระบบ กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง' };
                        }
                        throw e;
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // แสดง OTP step พร้อมบอก email ที่ส่งไป
                    resetEmail = username;
                    document.getElementById('otp-error').classList.add('hidden');
                    document.getElementById('forgot_otp').value = '';
                    
                    const emailLower = (data.email || username).toLowerCase();
                    let openUrl = '';
                    if (emailLower.endsWith('@gmail.com') || emailLower.endsWith('@yru.ac.th')) {
                        openUrl = 'https://mail.google.com';
                    } else if (emailLower.endsWith('@outlook.com') || emailLower.endsWith('@hotmail.com') || emailLower.endsWith('@live.com')) {
                        openUrl = 'https://outlook.live.com';
                    }

                    alert("ระบบได้ส่งรหัส OTP ไปยังอีเมลของท่านเรียบร้อยแล้ว กรุณาตรวจสอบกล่องจดหมาย");
                    if (openUrl) {
                        window.open(openUrl, '_blank');
                    }

                    showStep('step-otp-verify');
                    startOtpTimer();
                    startResendCooldown();
                } else {
                    errorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
                    errorDiv.classList.remove('hidden');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            })
            .catch(err => {
                let msg = 'ไม่พบบัญชีผู้ใช้นี้ในระบบ';
                if (err.message) {
                    if (err.message === 'CSRF token mismatch.') {
                        msg = 'เซสชันหมดอายุ (CSRF Mismatch) กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง';
                    } else {
                        msg = err.message;
                    }
                }
                errorDiv.textContent = msg;
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        }



        function resendOtp() {
            const btn = document.getElementById('btn-resend-otp');
            const otpErrorDiv = document.getElementById('otp-error');
            btn.disabled = true;
            otpErrorDiv.classList.add('hidden');

            fetch('/api/forgot-password-request', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ username: resetEmail })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('forgot_otp').value = '';
                    
                    const emailLower = (data.email || resetEmail).toLowerCase();
                    let openUrl = '';
                    if (emailLower.endsWith('@gmail.com') || emailLower.endsWith('@yru.ac.th')) {
                        openUrl = 'https://mail.google.com';
                    } else if (emailLower.endsWith('@outlook.com') || emailLower.endsWith('@hotmail.com') || emailLower.endsWith('@live.com')) {
                        openUrl = 'https://outlook.live.com';
                    }

                    alert("ระบบได้ส่งรหัส OTP ใหม่ไปยังอีเมลของท่านเรียบร้อยแล้ว กรุณาตรวจสอบกล่องจดหมาย");
                    if (openUrl) {
                        window.open(openUrl, '_blank');
                    }

                    startOtpTimer();
                    startResendCooldown();
                } else {
                    otpErrorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
                    otpErrorDiv.classList.remove('hidden');
                    btn.disabled = false;
                }
            })
            .catch(err => {
                otpErrorDiv.textContent = err.message || 'เกิดข้อผิดพลาดในการส่ง OTP';
                otpErrorDiv.classList.remove('hidden');
                btn.disabled = false;
            });
        }

        function submitOtpVerify() {
            const enteredOtp = document.getElementById('forgot_otp').value.trim();
            const errorDiv = document.getElementById('otp-error');
            const btn = document.getElementById('btn-submit-otp');
            
            if (enteredOtp.length !== 6) {
                errorDiv.textContent = 'กรุณากรอกรหัส OTP ให้ครบ 6 หลัก';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            errorDiv.classList.add('hidden');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>กำลังตรวจสอบ...</span>`;

            fetch('/api/forgot-password-verify', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    username: resetEmail,
                    otp: enteredOtp
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    resetToken = data.token;
                    clearInterval(otpCountdownInterval);
                    clearInterval(resendCooldownInterval);
                    showStep('step-new-password');
                } else {
                    errorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
                    errorDiv.classList.remove('hidden');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            })
            .catch(err => {
                errorDiv.textContent = err.message || 'รหัส OTP ไม่ถูกต้องหรือหมดอายุการใช้งาน';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        }

        function submitNewPassword() {
            const pass = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('password-error');
            const btn = document.getElementById('btn-submit-password');

            if (pass.length < 8) {
                errorDiv.textContent = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
                errorDiv.classList.remove('hidden');
                return;
            }

            if (pass !== confirm) {
                errorDiv.textContent = 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน';
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>กำลังบันทึก...</span>`;

            fetch('/api/reset-password-submit', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    username: resetEmail,
                    password: pass,
                    token: resetToken
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; }).catch(e => {
                        if (e instanceof SyntaxError || e.message?.includes('JSON')) {
                            throw { message: 'เซสชันหมดอายุหรือเกิดข้อผิดพลาดในระบบ กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง' };
                        }
                        throw e;
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('username').value = resetEmail;
                    document.getElementById('password').value = '';
                    showStep('step-success');
                } else {
                    errorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
                    errorDiv.classList.remove('hidden');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            })
            .catch(err => {
                let msg = 'เกิดข้อผิดพลาดในการบันทึกรหัสผ่านใหม่';
                if (err.message) {
                    if (err.message === 'CSRF token mismatch.') {
                        msg = 'เซสชันหมดอายุ (CSRF Mismatch) กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง';
                    } else {
                        msg = err.message;
                    }
                }
                errorDiv.textContent = msg;
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        }

        // Simple toggle for password visibility (type attribute)
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Change eye icon accordingly (optional)
            if (type === 'password') {
                // Closed eye (password hidden)
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
            } else {
                // Open eye (password visible)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
            passwordInput.focus();
        });

        // จัดการคลิกเปิด/ปิดตาเพื่อดูรหัสผ่านในหน้าสมัครสมาชิก
        const toggleRegPassword = document.getElementById('toggleRegPassword');
        const regPasswordInput = document.getElementById('reg_password');
        const regEyeIcon = document.getElementById('regEyeIcon');

        toggleRegPassword.addEventListener('click', function () {
            const type = regPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            regPasswordInput.setAttribute('type', type);
            regPasswordInput.focus();
            
            if (type === 'password') {
                // ตาปิด (ซ่อนรหัสผ่าน)
                regEyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                // ตาเปิด (ดูรหัสผ่านได้)
                regEyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });

        // ตรวจสอบเงื่อนไขรหัสผ่านแบบ Real-time
        regPasswordInput.addEventListener('input', function() {
            const val = this.value;
            const reqLength = document.getElementById('req-length');
            const reqCase = document.getElementById('req-case');
            const reqNum = document.getElementById('req-num');
            const reqSpecial = document.getElementById('req-special');

            if (!reqLength || !reqCase || !reqNum || !reqSpecial) return;

            const checkLength = val.length >= 8;
            const checkCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
            const checkNum = /[0-9]/.test(val);
            const checkSpecial = /[\W_]/.test(val);

            const updateReq = (el, valid) => {
                const text = el.innerText.trim();
                if (valid) {
                    el.className = 'text-green-500 flex items-center gap-1.5 transition-colors font-medium';
                    el.innerHTML = '<i class="fas fa-check-circle"></i> ' + text;
                } else {
                    if (val.length > 0) {
                        el.className = 'text-red-500 flex items-center gap-1.5 transition-colors font-medium';
                        el.innerHTML = '<i class="fas fa-times-circle"></i> ' + text;
                    } else {
                        el.className = 'text-gray-400 flex items-center gap-1.5 transition-colors';
                        el.innerHTML = '<i class="fas fa-times-circle"></i> ' + text;
                    }
                }
            };

            updateReq(reqLength, checkLength);
            updateReq(reqCase, checkCase);
            updateReq(reqNum, checkNum);
            updateReq(reqSpecial, checkSpecial);
        });

        const toggleRegPasswordConfirm = document.getElementById('toggleRegPasswordConfirm');
        const regPasswordConfirmInput = document.getElementById('reg_password_confirm');
        const regConfirmEyeIcon = document.getElementById('regConfirmEyeIcon');

        toggleRegPasswordConfirm.addEventListener('click', function () {
            const type = regPasswordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            regPasswordConfirmInput.setAttribute('type', type);
            regPasswordConfirmInput.focus();
            
            if (type === 'password') {
                // ตาปิด (ซ่อนรหัสผ่าน)
                regConfirmEyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                // ตาเปิด (ดูรหัสผ่านได้)
                regConfirmEyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });

        // จัดการคลิกเปิด/ปิดตาเพื่อดูรหัสผ่านในหน้าลืมรหัสผ่าน (ตั้งรหัสผ่านใหม่)
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const newPasswordInput = document.getElementById('new_password');
        const newEyeIcon = document.getElementById('newEyeIcon');

        if (toggleNewPassword) {
            toggleNewPassword.addEventListener('click', function () {
                const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                newPasswordInput.setAttribute('type', type);
                newPasswordInput.focus();
                
                if (type === 'password') {
                    newEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    `;
                } else {
                    newEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            });
        }

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const confirmEyeIcon = document.getElementById('confirmEyeIcon');

        if (toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function () {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                confirmPasswordInput.focus();
                
                if (type === 'password') {
                    confirmEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    `;
                } else {
                    confirmEyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            });
        }

        // ===== ระบบสมัครสมาชิก =====
        const registerModal = document.getElementById('registerModal');

        function openRegisterModal() {
            // Reset form
            document.getElementById('reg_title').value = '';
            document.getElementById('reg_firstname').value = '';
            document.getElementById('reg_lastname').value = '';
            document.getElementById('reg_email').value = '';
            document.getElementById('reg_password').value = '';
            document.getElementById('reg_password_confirm').value = '';
            document.getElementById('register-error').classList.add('hidden');
            document.getElementById('register-otp-error').classList.add('hidden');
            if (typeof registerOtpCountdownInterval !== 'undefined') clearInterval(registerOtpCountdownInterval);
            if (typeof registerResendCooldownInterval !== 'undefined') clearInterval(registerResendCooldownInterval);
            document.getElementById('register-form-step').classList.remove('hidden');
            document.getElementById('register-otp-step').classList.add('hidden');
            document.getElementById('register-success-step').classList.add('hidden');

            registerModal.classList.remove('hidden');
            setTimeout(() => {
                registerModal.classList.remove('opacity-0');
                registerModal.querySelector('.transform').classList.remove('scale-95');
            }, 50);
        }

        function closeRegisterModal() {
            registerModal.classList.add('opacity-0');
            registerModal.querySelector('.transform').classList.add('scale-95');
            if (typeof registerOtpCountdownInterval !== 'undefined') clearInterval(registerOtpCountdownInterval);
            if (typeof registerResendCooldownInterval !== 'undefined') clearInterval(registerResendCooldownInterval);
            setTimeout(() => {
                registerModal.classList.add('hidden');
            }, 300);
        }

        function submitRegister() {
            const title = document.getElementById('reg_title').value;
            const firstname = document.getElementById('reg_firstname').value.trim();
            const lastname = document.getElementById('reg_lastname').value.trim();
            const email = document.getElementById('reg_email').value.trim();
            const password = document.getElementById('reg_password').value;
            const passwordConfirm = document.getElementById('reg_password_confirm').value;
            const errorDiv = document.getElementById('register-error');
            const btn = document.getElementById('btn-submit-register');

            // Validation
            if (!title) {
                errorDiv.textContent = 'กรุณาเลือกคำนำหน้าชื่อ';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (!firstname) {
                errorDiv.textContent = 'กรุณากรอกชื่อ';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (!lastname) {
                errorDiv.textContent = 'กรุณากรอกนามสกุล';
                errorDiv.classList.remove('hidden');
                return;
            }
            const name = title + firstname + " " + lastname;
            if (!email) {
                errorDiv.textContent = 'กรุณากรอกอีเมล';
                errorDiv.classList.remove('hidden');
                return;
            }
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/;
            if (!passwordRegex.test(password)) {
                errorDiv.textContent = 'รหัสผ่านไม่ตรงตามเงื่อนไขที่กำหนด';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (password !== passwordConfirm) {
                errorDiv.textContent = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>กำลังสมัคร...</span>`;

            fetch('/api/register-submit', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: passwordConfirm
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; }).catch(e => {
                        if (e instanceof SyntaxError || e.message?.includes('JSON')) {
                            throw { message: 'เซสชันหมดอายุหรือเกิดข้อผิดพลาดในระบบ กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง' };
                        }
                        throw e;
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Auto-fill login form
                    document.getElementById('username').value = email;
                    document.getElementById('password').value = '';
                    
                    // เพิ่มรายชื่อลงใน localStorage สำหรับแอดมินทันที
                    try {
                        const defaultExternalUsers = [
                            { id: "EXT001", name: "นายอับดุลเลาะ มะ", contact: "abdul@gmail.com", reg_date: "01/07/2569", status: "ปกติ", reg_timestamp: new Date("2026-07-01").getTime() },
                            { id: "EXT002", name: "นางสาวนูรียะห์ ยะลา", contact: "nuriyah@outlook.com", reg_date: "05/07/2569", status: "ปกติ", reg_timestamp: new Date("2026-07-05").getTime() },
                            { id: "EXT003", name: "นายซูไบดี ดาโอะ", contact: "subaidi@hotmail.com", reg_date: "08/07/2569", status: "ถูกระงับ", reg_timestamp: new Date("2026-07-08").getTime() },
                            { id: "EXT004", name: "นางสาวฟาติมะห์ แสละ", contact: "fatimah@gmail.com", reg_date: "12/07/2569", status: "ปกติ", reg_timestamp: new Date("2026-07-12").getTime() }
                        ];
                        
                        let extUsersList = [];
                        const raw = localStorage.getItem('yru_external_users_v4');
                        if (raw) {
                            extUsersList = JSON.parse(raw);
                        } else {
                            extUsersList = JSON.parse(JSON.stringify(defaultExternalUsers));
                        }
                        
                        let nextNum = 1;
                        extUsersList.forEach(u => {
                            const num = parseInt(u.id.replace('EXT', ''));
                            if (num >= nextNum) nextNum = num + 1;
                        });
                        const nextExtId = "EXT" + String(nextNum).padStart(3, '0');
                        
                        const today = new Date();
                        const year = today.getFullYear() + 543;
                        const month = String(today.getMonth() + 1).padStart(2, '0');
                        const date = String(today.getDate()).padStart(2, '0');
                        const thaiDateStr = `${date}/${month}/${year}`;
                        
                        const newUser = {
                            id: nextExtId,
                            name: name,
                            contact: email,
                            reg_date: thaiDateStr,
                            status: "ปกติ",
                            reg_timestamp: Date.now()
                        };
                        
                        extUsersList.push(newUser);
                        localStorage.setItem('yru_external_users_v4', JSON.stringify(extUsersList));
                    } catch (e) {
                        console.error("Error saving new external user:", e);
                    }

                    // Skip OTP step and go directly to success step for all users
                    document.getElementById('register-form-step').classList.add('hidden');
                    document.getElementById('register-success-step').classList.remove('hidden');
                } else {
                    errorDiv.textContent = data.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
                    errorDiv.classList.remove('hidden');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            })
            .catch(err => {
                let msg = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
                if (err.message) {
                    if (err.message === 'CSRF token mismatch.') {
                        msg = 'เซสชันหมดอายุ (CSRF Mismatch) กรุณารีเฟรชหน้าเว็บ (F5) แล้วลองใหม่อีกครั้ง';
                    } else {
                        msg = err.message;
                    }
                }
                errorDiv.textContent = msg;
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        }

        // ===== OTP Variables and Functions for Registration =====
        let registerEmail = '';
        let registerOtpCountdownInterval = null;
        let registerResendCooldownInterval = null;

        function startRegisterOtpTimer() {
            clearInterval(registerOtpCountdownInterval);
            let timeRemaining = 300; // 5 minutes
            const timerDisplay = document.getElementById('register-otp-timer');
            
            function updateTimer() {
                let minutes = Math.floor(timeRemaining / 60);
                let seconds = timeRemaining % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                if (timeRemaining <= 0) {
                    clearInterval(registerOtpCountdownInterval);
                    document.getElementById('register-otp-error').textContent = 'รหัส OTP หมดอายุแล้ว กรุณากดขอรหัสใหม่อีกครั้ง';
                    document.getElementById('register-otp-error').classList.remove('hidden');
                }
                timeRemaining--;
            }
            
            updateTimer();
            registerOtpCountdownInterval = setInterval(updateTimer, 1000);
        }

        function startRegisterResendCooldown() {
            clearInterval(registerResendCooldownInterval);
            let cooldownRemaining = 60;
            const resendButton = document.getElementById('btn-resend-register-otp');
            resendButton.disabled = true;
            
            function updateCooldown() {
                if (cooldownRemaining <= 0) {
                    clearInterval(registerResendCooldownInterval);
                    resendButton.disabled = false;
                    resendButton.textContent = 'ส่งรหัสซ้ำ';
                } else {
                    resendButton.disabled = true;
                    resendButton.textContent = `ส่งรหัสซ้ำ (${cooldownRemaining}s)`;
                }
                cooldownRemaining--;
            }
            
            updateCooldown();
            registerResendCooldownInterval = setInterval(updateCooldown, 1000);
        }

        function resendRegisterOtp() {
            const btn = document.getElementById('btn-resend-register-otp');
            const otpErrorDiv = document.getElementById('register-otp-error');
            btn.disabled = true;
            otpErrorDiv.classList.add('hidden');

            fetch('/api/register-resend-otp', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: registerEmail })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('register_otp').value = '';
                    startRegisterOtpTimer();
                    startRegisterResendCooldown();
                } else {
                    otpErrorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
                    otpErrorDiv.classList.remove('hidden');
                    btn.disabled = false;
                }
            })
            .catch(err => {
                otpErrorDiv.textContent = err.message || 'เกิดข้อผิดพลาดในการส่ง OTP';
                otpErrorDiv.classList.remove('hidden');
                btn.disabled = false;
            });
        }

        function submitRegisterOtpVerify() {
            const enteredOtp = document.getElementById('register_otp').value.trim();
            const errorDiv = document.getElementById('register-otp-error');
            const btn = document.getElementById('btn-submit-register-otp');
            
            if (enteredOtp.length !== 6) {
                errorDiv.textContent = 'กรุณากรอกรหัส OTP 6 หลัก';
                errorDiv.classList.remove('hidden');
                return;
            }

            errorDiv.classList.add('hidden');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>กำลังยืนยัน...</span>`;

            fetch('/api/register-verify-otp', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: registerEmail, otp: enteredOtp })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Show success step
                    document.getElementById('register-otp-step').classList.add('hidden');
                    document.getElementById('register-success-step').classList.remove('hidden');
                } else {
                    errorDiv.textContent = data.message || 'รหัส OTP ไม่ถูกต้อง';
                    errorDiv.classList.remove('hidden');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            })
            .catch(err => {
                errorDiv.textContent = err.message || 'เกิดข้อผิดพลาดในการยืนยัน OTP';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        }
    </script>
</body>
</html>