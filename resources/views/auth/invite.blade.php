<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            {{-- <x-jet-authentication-card-logo /> --}}
            <span class="text-6xl nexus-blue nexus-font -mt-3 font-extrabold" style="font-family: 'Orbitron', sans-serif;">
                ne<span class="the-x text-gray-800">X</span>us
            </span>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Invite a new user to join neXus. They will receive an email with details on how to register and login') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-jet-validation-errors class="mb-4" />

        <form method="POST" action="/invite">
            @csrf

            <div class="block">
                <x-jet-label value="Email" />
                <x-jet-input class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-jet-button>
                    {{ __('Send Invite') }}
                </x-jet-button>
            </div>
        </form>
    </x-jet-authentication-card>
</x-guest-layout>
