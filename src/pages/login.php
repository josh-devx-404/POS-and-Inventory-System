<?php
// src/pages/login.php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Café POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Gradient Background */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #ff6b95 75%, #ff9a56 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        /* Gradient Shapes */
        .gradient-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 20s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #f093fb 0%, #ff6b95 100%);
            bottom: -80px;
            right: -80px;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b95 100%);
            top: 50%;
            left: 10%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }
            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }
        
        /* Input Focus Effect */
        .input-field:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        /* Button Gradient */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-gradient:active {
            transform: translateY(0);
        }
        
        /* Checkbox Custom Style */
        .custom-checkbox {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .custom-checkbox:checked {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        
        .custom-checkbox:checked::after {
            content: '\2713';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Logo Animation */
        .logo-container {
            animation: fadeInDown 1s ease-out;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Form Animation */
        .form-container {
            animation: fadeInUp 1s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Welcome Text Animation */
        .welcome-text {
            animation: fadeInLeft 1.2s ease-out;
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive Typography */
        @media (max-width: 768px) {
            .gradient-shape {
                display: none;
            }
        }
    </style>
</head>
<body class="gradient-bg">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-6xl flex flex-col lg:flex-row rounded-3xl overflow-hidden shadow-2xl">
            
            <!-- Left Panel - Design/Welcome Section -->
            <div class="hidden lg:flex lg:w-1/2 relative p-12 items-center justify-center overflow-hidden">
                <!-- Gradient Shapes -->
                <div class="gradient-shape shape-1"></div>
                <div class="gradient-shape shape-2"></div>
                <div class="gradient-shape shape-3"></div>
                
                <!-- Welcome Content -->
                <div class="relative z-10 text-white welcome-text">
                    <h1 class="text-5xl font-bold mb-6 leading-tight">
                        Welcome to<br/>Café POS System
                    </h1>
                    <p class="text-lg opacity-90 leading-relaxed max-w-md">
                        Streamline your café operations with our modern point of sale and inventory management system. Experience seamless workflow, real-time tracking, and intuitive design crafted for coffee excellence.
                    </p>
                    
                    <!-- Decorative Elements -->
                    <div class="mt-12 flex space-x-4">
                        <div class="w-16 h-1 bg-white opacity-50 rounded-full"></div>
                        <div class="w-8 h-1 bg-white opacity-30 rounded-full"></div>
                        <div class="w-4 h-1 bg-white opacity-20 rounded-full"></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Login Form -->
            <div class="w-full lg:w-1/2 glass-card p-8 sm:p-12 flex items-center justify-center">
                <div class="w-full max-w-md form-container">
                    
                    <!-- Logo -->
                    <div class="flex justify-center mb-8 logo-container">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center shadow-lg">
                            <i class="fas fa-coffee text-white text-3xl"></i>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-3xl font-bold text-center mb-2 text-gray-800">USER LOGIN</h2>
                    <p class="text-center text-gray-500 mb-8">Enter your credentials to access</p>
                    
                    <!-- Login Form -->
                    <form id="loginForm" class="space-y-6">
                        
                        <!-- Username Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="username"
                                    name="username"
                                    required
                                    autocomplete="username"
                                    class="input-field w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl transition-all duration-300 focus:border-purple-500"
                                    placeholder="Enter your username"
                                >
                            </div>
                        </div>
                        
                        <!-- Password Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    class="input-field w-full pl-12 pr-12 py-3 border-2 border-gray-200 rounded-xl transition-all duration-300 focus:border-purple-500"
                                    placeholder="Enter your password"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" class="custom-checkbox">
                                <span class="text-sm text-gray-600">Remember Me</span>
                            </label>
                            <a href="#" class="text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors">
                                Forgot password?
                            </a>
                        </div>
                        
                        <!-- Login Button -->
                        <button 
                            type="submit" 
                            id="loginBtn"
                            class="btn-gradient w-full py-3 text-white font-semibold rounded-xl shadow-lg"
                        >
                            <span id="btnText">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </span>
                            <span id="btnSpinner" class="hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading...
                            </span>
                        </button>
                        
                        <!-- Divider -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">Secure Login</span>
                            </div>
                        </div>
                        
                        <!-- Footer Note -->
                        <p class="text-center text-xs text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Protected by SSL Encryption
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <script>
        // Password Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Toast Notification
        function showToast(message, type = 'error') {
            const container = document.getElementById('toastContainer');
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-orange-500',
                info: 'bg-blue-500'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-3 min-w-[300px] animate-slideIn`;
            toast.innerHTML = `
                <i class="fas ${icons[type]} text-xl"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto hover:bg-white/20 rounded-lg p-1 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Form Submit Handler
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            // Validation
            if (!username || !password) {
                showToast('Please fill in all fields', 'warning');
                return;
            }
            
            // Loading state
            loginBtn.disabled = true;
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            
            try {
                const response = await fetch('../config/auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    showToast(result.message, 'error');
                    loginBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                }
            } catch (error) {
                showToast('Connection error. Please try again.', 'error');
                console.error('Login error:', error);
                
                loginBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnSpinner.classList.add('hidden');
            }
        });
        
        // Auto-focus username
        window.addEventListener('load', () => {
            document.getElementById('username').focus();
        });
        
        // Add slideIn/slideOut animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .animate-slideIn {
                animation: slideIn 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>