<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            {{-- <x-jet-authentication-card-logo /> --}}

            <span class="text-6xl nexus-blue nexus-font -mt-3 font-extrabold" style="font-family: 'Orbitron', sans-serif;">
                ne<span class="the-x text-gray-800">X</span>us
            </span>
        </x-slot>

        <x-jet-validation-errors class="mb-4" />

        @if (is_object($user))
            <form method="POST" action="/create-profile/{{ $user->id }}">
                @csrf

                <div>
                    <x-jet-label value="Name" />
                    <x-jet-input class="block mt-1 w-full" type="text" name="name" :value="$user->name" required autofocus autocomplete="name" />
                </div>

                <div class="mt-4">
                    <x-jet-label value="Email" />
                    <x-jet-input class="block mt-1 w-full" type="email" name="email" :value="$user->email" required />
                </div>

                <div class="mt-4">
                    <x-jet-label value="Password" />
                    <x-jet-input class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                </div>

                <div class="mt-4">
                    <x-jet-label value="Confirm Password" />
                    <x-jet-input class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    {{-- <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a> --}}

                    <x-jet-button class="ml-4">
                        {{ __('Register') }}
                    </x-jet-button>
                </div>
            </form>
        @endif
    </x-jet-authentication-card>
</x-guest-layout>
