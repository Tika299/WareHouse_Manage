<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, bạn vui lòng xác nhận địa chỉ email bằng cách nhấp vào liên kết chúng tôi vừa gửi cho bạn? Nếu bạn không nhận được email, chúng tôi sẽ gửi lại cái khác.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('Một liên kết xác thực mới đã được gửi đến địa chỉ email bạn đã cung cấp.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div>
                <x-primary-button>
                    {{ __('Gửi lại email xác nhận') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 rounded-md">
                {{ __('Đăng xuất') }}
            </button>
        </form>
    </div>
</x-guest-layout>