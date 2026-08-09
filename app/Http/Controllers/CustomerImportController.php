<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCsvException;
use App\Models\CustomerImport;
use App\Services\Imports\ImportCustomers;
use App\Services\Imports\PreviewCustomerImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class CustomerImportController extends Controller
{
    public function index(): View
    {
        return view('welcome', [
            'imports' => CustomerImport::query()->latest()->limit(8)->get(),
            'summary' => session('import_summary'),
            'previewRows' => session('preview_rows', []),
            'originalFilename' => session('original_filename'),
            'totalRows' => session('total_rows', 0),
            'validRows' => session('valid_rows', 0),
            'invalidRows' => session('invalid_rows', 0),
        ]);
    }

    public function preview(
        Request $request,
        PreviewCustomerImport $previewer,
    ): RedirectResponse {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:'.config('imports.max_file_size_kb'),
            ],
        ], [
            'file.mimes' => 'Pasirink CSV faila.',
            'file.max' => 'Failas negali buti didesnis nei 5 MB.',
        ]);

        $this->clearPendingFile();
        session()->forget('import_summary');

        $file = $request->file('file');
        $path = $file->storeAs('imports/pending', Str::uuid().'.csv', 'local');

        if (! is_string($path)) {
            return back()->withErrors(['file' => 'Failo nepavyko issaugoti.']);
        }

        try {
            $preview = $previewer->handle(Storage::disk('local')->path($path));
        } catch (InvalidCsvException $exception) {
            Storage::disk('local')->delete($path);

            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        session([
            'import_pending_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'preview_rows' => array_map(fn ($row): array => $row->toArray(), $preview->rows),
            'total_rows' => $preview->totalRows(),
            'valid_rows' => $preview->validRows(),
            'invalid_rows' => $preview->invalidRows(),
        ]);

        return redirect()->route('imports.index');
    }

    public function import(
        PreviewCustomerImport $previewer,
        ImportCustomers $importer,
    ): RedirectResponse {
        $path = session('import_pending_path');
        $filename = session('original_filename');

        if (! is_string($path) || ! is_string($filename)) {
            return redirect()->route('imports.index')->withErrors([
                'import' => 'Pirma reikia perziureti CSV faila.',
            ]);
        }

        try {
            $preview = $previewer->handle(Storage::disk('local')->path($path));
            $result = $importer->handle($preview, $filename);
        } catch (InvalidCsvException $exception) {
            return redirect()->route('imports.index')->withErrors(['import' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('imports.index')->withErrors([
                'import' => 'Importo nepavyko baigti. Duomenys nepakeisti.',
            ]);
        }

        $this->clearPendingFile();

        return redirect()->route('imports.index')->with('import_summary', $result->toArray());
    }

    public function cancel(): RedirectResponse
    {
        $this->clearPendingFile();

        return redirect()->route('imports.index');
    }

    private function clearPendingFile(): void
    {
        $path = session('import_pending_path');

        if (is_string($path)) {
            Storage::disk('local')->delete($path);
        }

        session()->forget([
            'import_pending_path',
            'original_filename',
            'preview_rows',
            'total_rows',
            'valid_rows',
            'invalid_rows',
        ]);
    }
}
