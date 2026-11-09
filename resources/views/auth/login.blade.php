@extends('layouts.app', ['title' => 'Login', 'showSidebar' => false])

@section('content')
<div class="login-wrapper">
    <div class="login-container">

        <!-- Logo/Brand (Di Luar Card, Atas) -->
        <div class="brand-top-header">
            <div class="brand-logo-box">
                @if (file_exists(public_path('images/telkom-property-logo.png')))
                    <img src="{{ asset('images/telkom-property-logo.png') }}" alt="Telkom Property">
                @elseif (file_exists(public_path('images/telkom-property-logo.png.png')))
                    <img src="{{ asset('images/telkom-property-logo.png.png') }}" alt="Telkom Property">
                @elseif (file_exists(public_path('images/telkom-property-logo.svg')))
                    <img src="{{ asset('images/telkom-property-logo.svg') }}" alt="Telkom Property">
                @else
                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12.5 12 4l9 8.5"></path>
                        <path d="M5 10.5V20h14v-9.5"></path>
                    </svg>
                @endif
            </div>
            <h1 class="brand-top-title">Monitoring Dokumen</h1>
            <p class="brand-top-sub">Regional III Telkom Property</p>
        </div>

        <!-- Split Card (Merah Tone) -->
        <div id="auth-card" class="split-card auth-card">
            
            <!-- Sisi Kiri: Form Login -->
            <div class="form-panel">
                <div class="form-top">
                    <h2 class="form-heading">Selamat Datang</h2>

                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

                        <!-- Email Input -->
                        <div class="form-group">
                            <label for="email" class="input-label">Email</label>
                            <div class="field-control @error('email') has-error @enderror">
                                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                    class="custom-input @error('email') border-danger @enderror"
                                    placeholder="nama@domain.com">
                            </div>
                            @error('email')
                                <span class="text-danger-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="form-group">
                            <label for="password" class="input-label">Password</label>
                            <div class="field-control @error('password') has-error @enderror">
                                <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input id="password" type="password" name="password" required autocomplete="current-password"
                                    class="custom-input @error('password') border-danger @enderror"
                                    placeholder="Masukkan password">
                                <button type="button" id="togglePassword" aria-label="Tampilkan/Sembunyikan password" class="password-toggle">
                                    <svg id="eyeOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="eyeClosed" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Ingat Saya -->
                        <div class="remember-row">
                            <label class="remember-label">
                                <input type="hidden" name="remember" value="0">
                                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="checkbox-input">
                                <span>Ingat saya</span>
                            </label>
                        </div>
                    </form>
                </div>

                <!-- Bottom Action (Button Masuk) -->
                <div class="form-bottom">
                    <button class="submit-btn" type="submit" onclick="document.querySelector('.login-form').requestSubmit();">
                        <span id="btnText">Masuk</span>
                        <span id="btnLoading" style="display: none;">
                            <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;">
                                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>

                    <!-- Mobile View Register Link -->
                    <div class="mobile-register-link">
                        Belum punya akun? <a href="{{ route('register') }}" class="register-anchor">Daftar di sini</a>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Panel Merah (Sejajar) -->
            <div class="info-panel">
                <!-- Background Geometris -->
                <div class="bg-shape shape-diamond-top"></div>
                <div class="bg-shape shape-diamond-bottom"></div>
                <div class="bg-shape shape-circle"></div>

                <div class="info-body">
                    <div class="info-top">
                        <h2 class="info-heading">Belum punya akun?</h2>
                        <p class="info-desc">
                            Silakan mendaftar untuk terhubung dan mengakses Sistem Monitoring Dokumen Regional III Telkom Property.
                        </p>
                    </div>

                    <!-- Bottom Action (Sejajar dengan Button Masuk) -->
                    <div class="info-bottom">
                        <a href="{{ route('register') }}" class="outline-pill-btn">
                            Daftar di sini
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Teks -->
        <div class="card-footer-text">
            <p>&copy; {{ date('Y') }} Telkom Property Regional III</p>
            <p>Sistem Monitoring Dokumen v1.0</p>
        </div>

    </div>
</div>

<style id="auth-page-style">
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin { animation: spin 1s linear infinite; }

    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 20px;
        box-sizing: border-box;
    }

    body.auth-page {
        background:
            linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)),
            url('{{ asset('images/bg login regis.png') }}') center / cover no-repeat fixed;
    }

    .auth-page .content,
    .auth-page .container {
        width: 100%;
        max-width: none;
    }

    .login-container {
        width: 100%;
        max-width: 1140px;
        margin: 0 auto;
    }

    /* Top Brand Header (Di Luar Card) */
    .brand-top-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .brand-logo-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 76px;
        height: 76px;
        border-radius: 20px;
        background: linear-gradient(135deg, #ffffff, #fff9d9);
        border: 1px solid #f1df80;
        box-shadow: 0 12px 32px rgba(225, 29, 42, 0.14);
        margin-bottom: 14px;
    }

    .brand-logo-box img {
        width: 66px;
        height: 66px;
        object-fit: contain;
    }

    .brand-top-title {
        margin: 0 0 4px;
        font-size: 32px;
        font-weight: 800;
        color: var(--text, #1e293b);
        letter-spacing: -0.02em;
    }

    .brand-top-sub {
        margin: 0;
        font-size: 19px;
        font-weight: 600;
        color: var(--primary, #E11D2A);
    }

    /* Split Card Container */
    .split-card {
        display: grid;
        grid-template-columns: minmax(0, var(--form-w)) minmax(0, var(--info-w));
        grid-template-rows: minmax(0, 1fr);
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 
            0 20px 50px rgba(225, 29, 42, 0.08), 
            0 8px 24px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        width: 1140px;
        max-width: 100%;
        height: 620px;
        border: 1px solid rgba(214, 225, 236, 0.7);
        position: relative;
        min-width: 0;
        min-height: 0;
    }

    /* Left Panel: Form Login */
    .form-panel {
        flex: 1;
        width: 100%;
        height: 100%;
        padding: 44px 48px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #ffffff;
        position: relative;
        z-index: 2;
        box-sizing: border-box;
        min-width: 0;
        min-height: 0;
        will-change: transform;
    }

    .form-top {
        display: flex;
        flex-direction: column;
    }

    .form-heading {
        margin: 0 0 28px;
        font-size: 32px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.02em;
    }

    .form-group {
        margin-bottom: 22px;
    }

    .input-label {
        display: block;
        margin-bottom: 8px;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .field-control {
        position: relative;
    }

    .field-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 22px;
        height: 22px;
        color: #8E8E93;
        pointer-events: none;
        transition: color .2s ease;
    }

    .custom-input {
        width: 100%;
        padding: 16px 20px 16px 54px;
        border: 1px solid rgba(214, 225, 236, 0.9);
        border-radius: 16px;
        font-size: 18px;
        background: #f8fafc;
        color: #1e293b;
        transition: all .2s ease;
        box-sizing: border-box;
    }

    .custom-input:focus {
        outline: none;
        border-color: #E11D2A !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(225, 29, 42, 0.12) !important;
    }

    .field-control:focus-within .field-icon {
        color: #E11D2A;
    }

    .password-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #8E8E93;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color .2s;
    }

    .password-toggle:hover {
        color: #E11D2A;
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 6px 0 20px;
    }

    .remember-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        color: #334155;
        cursor: pointer;
        user-select: none;
    }

    .checkbox-input {
        width: 18px;
        height: 18px;
        accent-color: #E11D2A;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-bottom {
        margin-top: 16px;
    }

    .submit-btn {
        width: 100%;
        position: relative;
        top: -10px;
        padding: 16px 32px;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #E11D2A 0%, #B91C1C 100%);
        border: none;
        border-radius: 36px;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(225, 29, 42, 0.28);
        transition: all .25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .submit-btn:hover {
        background: linear-gradient(135deg, #f32331 0%, #99111b 100%);
        box-shadow: 0 14px 28px rgba(225, 29, 42, 0.38);
        transform: translateY(-1px);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .mobile-register-link {
        display: none;
        margin-top: 20px;
        text-align: center;
        font-size: 16px;
        color: #64748b;
    }

    .register-anchor {
        color: #E11D2A;
        font-weight: 700;
        text-decoration: none;
    }

    .register-anchor:hover {
        text-decoration: underline;
    }

    /* Right Panel: Tone Warna Merah */
    .info-panel {
        flex: 1;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #E11D2A 0%, #C81E2B 50%, #A5131E 100%);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 44px 48px;
        color: #ffffff;
        overflow: hidden;
        box-sizing: border-box;
        min-width: 0;
        min-height: 0;
        will-change: transform;
    }

    /* Background Geometris */
    .bg-shape {
        position: absolute;
        pointer-events: none;
    }

    .shape-diamond-top {
        width: 140px;
        height: 140px;
        background: rgba(255, 255, 255, 0.08);
        transform: rotate(45deg);
        border-radius: 24px;
        top: -30px;
        right: -30px;
    }

    .shape-diamond-bottom {
        width: 160px;
        height: 160px;
        background: rgba(255, 255, 255, 0.08);
        transform: rotate(45deg);
        border-radius: 28px;
        bottom: -40px;
        left: -40px;
    }

    .shape-circle {
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        bottom: 5%;
        right: -10%;
    }

    .info-body {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 100%;
        max-width: 360px;
    }

    .info-top {
        margin-top: 0;
        margin-bottom: 28px;
    }

    .info-heading {
        margin: 0 0 16px;
        font-size: 32px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }

    .info-desc {
        margin: 0;
        font-size: 18px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.92);
        font-weight: 500;
    }

    .info-bottom {
        margin-top: 0;
    }

    .outline-pill-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 15px 36px;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 36px;
        text-decoration: none;
        transition: all .25s ease;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        box-sizing: border-box;
    }

    .outline-pill-btn:hover {
        background: #ffffff;
        color: #E11D2A;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
        transform: translateY(-1px);
    }

    .outline-pill-btn:active {
        transform: translateY(0);
    }

    .text-danger-msg {
        display: block;
        margin-top: 6px;
        font-size: 14px;
        color: #ef4444;
    }

    .border-danger {
        border-color: #ef4444 !important;
    }

    .auth-page input:-webkit-autofill,
    .auth-page input:-webkit-autofill:hover,
    .auth-page input:-webkit-autofill:focus {
        -webkit-text-fill-color: #1e293b !important;
        -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;
    }

    .card-footer-text {
        text-align: center;
        margin-top: 18px;
        font-size: 15.5px;
        color: var(--muted, #8E8E93);
        opacity: 0.8;
    }

    .card-footer-text p {
        margin: 2px 0;
    }

    /* Responsive untuk Tablet & Mobile */

    @media (max-width: 767px) {
        .split-card {
            grid-template-columns: 1fr;
            border-radius: 20px;
            min-height: auto;
            height: auto;
        }

        .form-panel, .info-panel {
            width: 100%;
            flex: none;
            grid-column: auto;
            padding: 32px 24px;
        }

        .info-panel {
            order: 2;
        }

        .info-heading {
            font-size: 26px;
        }

        .info-desc {
            font-size: 16px;
        }

        .form-heading {
            font-size: 26px;
        }

        .input-label, .remember-label {
            font-size: 16px;
        }

        .custom-input {
            font-size: 16px;
            padding: 14px 18px 14px 48px;
        }

        .submit-btn, .outline-pill-btn {
            font-size: 16px;
            padding: 14px 24px;
        }

        .mobile-register-link {
            display: block;
        }
    }
</style>

<script>
    function initAuthPage() {
        const bindPasswordToggle = (buttonId, inputId, openId, closedId) => {
            const button = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            const openIcon = document.getElementById(openId);
            const closedIcon = document.getElementById(closedId);

            if (!button || !input || button.dataset.bound) return;
            button.dataset.bound = 'true';
            button.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                if (openIcon) openIcon.style.display = isPassword ? 'none' : 'block';
                if (closedIcon) closedIcon.style.display = isPassword ? 'block' : 'none';
            });
        };

        bindPasswordToggle('togglePassword', 'password', 'eyeOpen', 'eyeClosed');
        bindPasswordToggle('togglePasswordConfirm', 'password_confirmation', 'eyeOpenConfirm', 'eyeClosedConfirm');

        const form = document.querySelector('form');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');
        const submitBtn = document.querySelector('.submit-btn');

        if (form && !form.dataset.bound) {
            form.dataset.bound = 'true';
            form.addEventListener('submit', function() {
                if (submitBtn) submitBtn.disabled = true;
                if (btnText) btnText.style.display = 'none';
                if (btnLoading) btnLoading.style.display = 'inline-flex';
            });
        }

        const unitSelect = document.querySelector('.unit-select');
        const unitTrigger = unitSelect?.querySelector('.unit-select-trigger');
        const unitInput = document.getElementById('unit_id');
        const unitLabel = document.getElementById('unitLabel');
        const unitOptions = unitSelect?.querySelectorAll('[role="option"]') ?? [];

        if (unitSelect && unitTrigger && unitInput && unitLabel && !unitSelect.dataset.bound) {
            unitSelect.dataset.bound = 'true';
            unitOptions.forEach(function(option) {
                if (option.dataset.value === unitInput.value) {
                    unitLabel.textContent = option.textContent.trim();
                    option.classList.add('is-selected');
                }

                option.addEventListener('click', function() {
                    unitInput.value = option.dataset.value;
                    unitLabel.textContent = option.textContent.trim();
                    unitOptions.forEach(item => item.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    unitSelect.classList.remove('is-open');
                    unitTrigger.setAttribute('aria-expanded', 'false');
                });
            });

            unitTrigger.addEventListener('click', function() {
                const isOpen = unitSelect.classList.toggle('is-open');
                unitTrigger.setAttribute('aria-expanded', String(isOpen));
            });

            document.addEventListener('click', function(event) {
                if (!unitSelect.contains(event.target)) {
                    unitSelect.classList.remove('is-open');
                    unitTrigger.setAttribute('aria-expanded', 'false');
                }
            });
        }

    }

    document.addEventListener('DOMContentLoaded', initAuthPage);
</script>
@endsection
