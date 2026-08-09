<main class="min-h-screen bg-gray-100 text-gray-900">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <header class="mb-6">
            <h1 class="text-2xl font-semibold">Klientu importas</h1>
            <p class="mt-1 text-sm text-gray-600">CSV failo ikelimas ir patikrinimas</p>
        </header>

        @if ($summary)
            <section class="mb-5 border border-gray-300 bg-white p-4" aria-labelledby="summary-title">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="summary-title" class="font-semibold">Importas baigtas</h2>
                    <form method="POST" action="{{ route('imports.cancel') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="border border-gray-400 px-3 py-1.5 text-sm hover:bg-gray-100">Kitas failas</button>
                    </form>
                </div>
                <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                    <div class="border border-gray-200 p-3"><dt class="text-gray-500">Viso</dt><dd class="mt-1 text-xl font-semibold">{{ $summary['total_rows'] }}</dd></div>
                    <div class="border border-gray-200 p-3"><dt class="text-gray-500">Importuota</dt><dd class="mt-1 text-xl font-semibold">{{ $summary['imported_rows'] }}</dd></div>
                    <div class="border border-gray-200 p-3"><dt class="text-gray-500">Nepavyko</dt><dd class="mt-1 text-xl font-semibold">{{ $summary['failed_rows'] }}</dd></div>
                </dl>
            </section>
        @endif

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="space-y-5">
                <section class="border border-gray-300 bg-white" aria-labelledby="upload-title">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                        <h2 id="upload-title" class="font-semibold">CSV failas</h2>
                        <a href="/sample-customers.csv" download class="text-sm text-blue-700 hover:underline">Pavyzdys</a>
                    </div>

                    <form method="POST" action="{{ route('imports.preview') }}" enctype="multipart/form-data" class="p-4">
                        @csrf
                        <label for="file" class="block text-sm font-medium text-gray-700">Pasirink faila</label>
                        <input id="file" name="file" type="file" accept=".csv,text/csv" required class="mt-2 block w-full border border-gray-400 bg-white px-3 py-2 text-sm file:mr-3 file:border-0 file:bg-gray-200 file:px-3 file:py-1.5 file:text-sm">
                        <p class="mt-2 text-xs text-gray-500">CSV/TXT, iki 5 MB ir 1000 eiluciu</p>
                        @error('file') <p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <button type="submit" class="border border-gray-900 bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Perziureti</button>
                        </div>
                    </form>
                </section>

                @if ($previewRows !== [])
                    <section class="border border-gray-300 bg-white" aria-labelledby="preview-title">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                            <h2 id="preview-title" class="font-semibold">Perziura: {{ $originalFilename }}</h2>
                            <p class="text-sm text-gray-600">Validu: {{ $validRows }} / Klaidu: {{ $invalidRows }}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-[58rem] w-full text-left text-sm">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                                    <tr><th class="px-3 py-2">Eil.</th><th class="px-3 py-2">Vardas</th><th class="px-3 py-2">El. pastas</th><th class="px-3 py-2">Telefonas</th><th class="px-3 py-2">Gimimo data</th><th class="px-3 py-2">Salis</th><th class="px-3 py-2">Busena</th></tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($previewRows as $row)
                                        <tr class="align-top {{ $row['is_valid'] ? '' : 'bg-red-50' }}">
                                            <td class="px-3 py-3 text-xs text-gray-500">{{ $row['row_number'] }}</td>
                                            <td class="px-3 py-3 font-medium">{{ $row['first_name'] }} {{ $row['last_name'] }}</td>
                                            <td class="px-3 py-3">{{ $row['email'] }}</td>
                                            <td class="px-3 py-3 text-gray-600">{{ $row['phone_number'] ?: '-' }}</td>
                                            <td class="px-3 py-3 text-gray-600">{{ $row['birth_date'] ?: '-' }}</td>
                                            <td class="px-3 py-3 text-gray-600">{{ $row['country'] }}</td>
                                            <td class="px-3 py-3">
                                                @if ($row['is_valid'])
                                                    <span class="text-sm text-green-700">Tinka</span>
                                                @else
                                                    <ul class="space-y-1 text-xs text-red-700">@foreach ($row['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3">
                            <form method="POST" action="{{ route('imports.cancel') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Atsaukti</button>
                            </form>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">Klaidingos eilutes bus praleistos.</span>
                                <form method="POST" action="{{ route('imports.store') }}">
                                    @csrf
                                    <button type="submit" @disabled($validRows === 0) class="border border-gray-900 bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-40">Importuoti {{ $validRows }}</button>
                                </form>
                            </div>
                        </div>
                        @error('import') <p class="border-t border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</p> @enderror
                    </section>
                @endif
            </div>

            <aside class="border border-gray-300 bg-white p-4" aria-labelledby="history-title">
                <div class="flex items-center justify-between"><h2 id="history-title" class="font-semibold">Importai</h2><span class="text-xs text-gray-500">8 paskutiniai</span></div>
                <div class="mt-4 space-y-3">
                    @forelse ($imports as $history)
                        <article class="border-b border-gray-200 pb-3 last:border-0">
                            <p class="truncate text-sm" title="{{ $history->original_filename }}">{{ $history->original_filename }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $history->created_at->diffForHumans() }}</p>
                            <p class="mt-1 text-xs text-gray-600">{{ $history->imported_rows }} importuota, {{ $history->failed_rows }} klaidu</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Kol kas nieko nera.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</main>
