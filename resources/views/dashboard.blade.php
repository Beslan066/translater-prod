 <x-app-layout>
    <x-slot name="header">

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-green-900">
                        Добро пожаловать, вы успешно зарегистрировались! Как только администратор подтвердит вас вы
                        увидите кнопку внизу, нажмите на нее и вы сможете начать работу.
                    </p>
                </div>

                <div class="flex justify-center">
                    @if(auth()->user()->role === 3)
                        <button type="button"
                                class="focus:outline-none text-white
                            bg-green-700 hover:bg-green-800 focus:ring-4
                            focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2
                            dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
                            <a href="{{route('home')}}">
                                Начать переводы
                            </a>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
