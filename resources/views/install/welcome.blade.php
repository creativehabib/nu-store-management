@extends('install.layout')

@section('title', 'Welcome')

@section('content')
    <div>
        <h4 class="text-xl font-bold text-gray-800">Welcome</h4>
        <p class="mt-2 max-w-2xl leading-relaxed text-gray-500">
            Before getting started, we need some information on the database. You will need to know the following items before proceeding.
        </p>
        <div class="mt-10 max-w-3xl">
            <label class="mb-2 block text-lg font-medium text-gray-700" for="language">Language</label>
            <select
                class="w-full appearance-none rounded-md border border-gray-300 bg-no-repeat bg-right px-4 py-1.5 pr-10 text-gray-700 outline-none transition focus:border-blue-500 focus:ring-blue-500"
                id="language"
                name="language"
                style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-position: right 1rem center; background-size: 1.5em;"
            >
                <option>English - en</option>
                <option>Bengali - bn</option>
            </select>
        </div>
    </div>
@endsection

@section('footer')
    <div class="flex justify-end">
        <a class="rounded-md bg-[#2b64b9] px-8 py-2.5 font-semibold text-white shadow-sm transition hover:bg-blue-800" href="{{ route('install.requirements') }}" data-install-loading>
            Let's go
        </a>
    </div>
@endsection
