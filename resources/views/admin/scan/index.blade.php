<x-admin.layout title="Scan Barcode">
    @push('vite')
        @vite('resources/js/scanner.js')
    @endpush

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Scan Barcode</h1>
    </div>

    @if (session('scan_success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 p-6 text-emerald-900 ring-1 ring-emerald-200">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-lg font-semibold">
                    {{ session('scan_success') }}
                </div>
            </div>
        </div>
    @endif

    @if (session('scan_error'))
        <div class="mb-6 rounded-2xl bg-rose-50 p-6 text-rose-900 ring-1 ring-rose-200">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-lg font-semibold">
                    {{ session('scan_error') }}
                </div>
            </div>
        </div>
    @endif

    <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <form action="{{ route('admin.scan.store') }}" method="POST" id="scan-form" class="max-w-xl mx-auto text-center">
            @csrf
            
            <label for="voucher_id" class="block text-sm font-medium text-slate-700 mb-4">
                Ready to scan. Please scan the voucher barcode now.
            </label>
            
            <div class="flex items-center gap-2 mb-4">
                <input 
                    type="text" 
                    name="voucher_id" 
                    id="voucher_id" 
                    autofocus 
                    autocomplete="off"
                    class="block w-full rounded-2xl border-0 py-4 px-6 text-center text-2xl text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-[#4E2E36] sm:text-2xl sm:leading-6 transition-shadow" 
                    placeholder="Scan or enter barcode..." 
                    required
                >
                <button type="button" id="start-camera" class="flex shrink-0 items-center justify-center rounded-2xl bg-slate-900 p-4 text-white shadow-sm hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-[#4E2E36] focus:ring-offset-2" title="Use Camera Scanner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>
            
            @error('voucher_id')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <div id="reader-container" class="hidden mb-6 overflow-hidden rounded-2xl ring-1 ring-slate-200">
                <div id="reader" class="w-full"></div>
                <button type="button" id="stop-camera" class="w-full hidden bg-rose-500 py-3 text-sm font-semibold text-white hover:bg-rose-600 transition-colors">
                    Cancel Camera Scan
                </button>
            </div>

            <div class="mt-8 text-sm text-slate-500">
                <p>The scanner will automatically submit upon reading a barcode.</p>
                <p class="mt-1">Alternatively, enter the code manually and press Enter.</p>
            </div>
        </form>
    </div>
    
    <script>
        // Ensure the input field always has focus
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('voucher_id');
            const startCameraBtn = document.getElementById('start-camera');
            const stopCameraBtn = document.getElementById('stop-camera');
            
            // Only focus if the camera is not active
            input.focus();
            
            // Re-focus if user clicks away (helpful for dedicated scanning stations)
            // But only if we are not clicking the camera buttons
            document.addEventListener('click', function(e) {
                if (
                    e.target !== input && 
                    !startCameraBtn.contains(e.target) && 
                    !stopCameraBtn.contains(e.target) &&
                    !document.getElementById('reader-container').contains(e.target)
                ) {
                    input.focus();
                }
            });
        });
    </script>
</x-admin.layout>
