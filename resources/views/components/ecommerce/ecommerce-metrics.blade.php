@php
    $totalUsers = \App\Models\User::count();
    $totalOrders = \App\Models\Order::count();
    $totalRevenue = \App\Models\Order::where('payment_status', 'paid')->sum('total');
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 md:gap-6">
    {{-- Customers --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-500/10">
            <svg class="text-blue-600 dark:text-blue-400" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M8.80443 5.60156C7.59109 5.60156 6.60749 6.58517 6.60749 7.79851C6.60749 9.01185 7.59109 9.99545 8.80443 9.99545C10.0178 9.99545 11.0014 9.01185 11.0014 7.79851C11.0014 6.58517 10.0178 5.60156 8.80443 5.60156ZM5.10749 7.79851C5.10749 5.75674 6.76267 4.10156 8.80443 4.10156C10.8462 4.10156 12.5014 5.75674 12.5014 7.79851C12.5014 9.84027 10.8462 11.4955 8.80443 11.4955C6.76267 11.4955 5.10749 9.84027 5.10749 7.79851ZM4.86252 15.3208C4.08769 16.0881 3.70377 17.0608 3.51705 17.8611C3.48384 18.0034 3.5211 18.1175 3.60712 18.2112C3.70161 18.3141 3.86659 18.3987 4.07591 18.3987H13.4249C13.6343 18.3987 13.7992 18.3141 13.8937 18.2112C13.9797 18.1175 14.017 18.0034 13.9838 17.8611C13.7971 17.0608 13.4132 16.0881 12.6383 15.3208C11.8821 14.572 10.6899 13.955 8.75042 13.955C6.81096 13.955 5.61877 14.572 4.86252 15.3208ZM3.8071 14.2549C4.87163 13.2009 6.45602 12.455 8.75042 12.455C11.0448 12.455 12.6292 13.2009 13.6937 14.2549C14.7397 15.2906 15.2207 16.5607 15.4446 17.5202C15.7658 18.8971 14.6071 19.8987 13.4249 19.8987H4.07591C2.89369 19.8987 1.73504 18.8971 2.05628 17.5202C2.28015 16.5607 2.76117 15.2906 3.8071 14.2549Z"
                    fill="currentColor" />
            </svg>
        </div>
        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Người dùng</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                {{ number_format($totalUsers) }}
            </h4>
        </div>
    </div>

    {{-- Orders --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-orange-50 rounded-xl dark:bg-orange-500/10">
            <svg class="text-orange-600 dark:text-orange-400" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M7.7832 19.5H7.7932M16.3203 19.5H16.3303" stroke="currentColor" stroke-width="3.5"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Tổng đơn hàng</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                {{ number_format($totalOrders) }}
            </h4>
        </div>
    </div>

    {{-- Pending Orders --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-warning-50 rounded-xl dark:bg-warning-500/10">
            <svg class="text-warning-600 dark:text-warning-400" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Chờ xác nhận</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                {{ number_format($pendingOrders) }}
            </h4>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-success-50 rounded-xl dark:bg-success-500/10">
            <svg class="text-success-600 dark:text-success-400" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="mt-5">
            <span class="text-sm text-gray-500 dark:text-gray-400">Doanh thu</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                {{ number_format($totalRevenue, 0, ',', '.') }} đ
            </h4>
        </div>
    </div>
</div>
