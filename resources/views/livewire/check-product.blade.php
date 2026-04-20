<div class="w-full {{ $item->batch->product->type === 'digital' ? 'max-w-3xl' : 'max-w-md' }} mx-auto animate-fade-in" x-data="productCheck()">
    
    @if($item->batch->product->type === 'digital' && $item->batch->product->digital_file_path)
    <div class="mb-6 rounded-2xl shadow-xl overflow-hidden bg-black flex justify-center">
        @if($item->batch->product->digital_content_type === 'video')
            <video controls class="w-full max-h-[60vh] object-contain">
                <source src="{{ Storage::url($item->batch->product->digital_file_path) }}" type="video/mp4">
                Tarayıcınız video etiketini desteklemiyor.
            </video>
        @elseif($item->batch->product->digital_content_type === 'pdf')
            <iframe src="{{ Storage::url($item->batch->product->digital_file_path) }}" class="w-full h-[70vh] border-0"></iframe>
        @endif
    </div>
    @endif

    <div class="glass rounded-2xl shadow-xl overflow-hidden">
        <!-- Header / Status -->
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6 text-center text-white">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold mb-1">Orijinal Ürün</h1>
            <p class="text-emerald-100 text-sm">Bu ürün başarıyla doğrulandı.</p>
        </div>

        <!-- Product Details -->
        <div class="p-6">
            @if($item->batch->product->image)
            <div class="mb-6 flex justify-center">
                <img src="{{ Storage::url($item->batch->product->image) }}" alt="Ürün Görseli" class="w-32 h-32 object-cover rounded-xl shadow-md border border-gray-100">
            </div>
            @endif

            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Firma</p>
                    <p class="text-lg font-medium text-gray-800">{{ $item->batch->product->company->name }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Ürün</p>
                    <p class="text-lg font-medium text-gray-800">{{ $item->batch->product->name }}</p>
                </div>
                
                <div class="border-b border-gray-100 pb-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Parti No</p>
                    <p class="text-lg font-medium text-gray-800">{{ $item->batch->batch_number }}</p>
                </div>

                <div class="pt-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Seri No (UUID)</p>
                    <p class="font-mono text-sm text-gray-600 break-all bg-gray-50 p-3 rounded-lg border border-gray-100">
                        {{ $item->uuid }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-xs text-gray-400">Ürün Doğrulama Sistemi &copy; {{ date('Y') }}</p>
        </div>
    </div>

    <!-- Script to get location -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productCheck', () => ({
                init() {
                    this.getLocation();
                },
                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                @this.saveScanData(position.coords.latitude, position.coords.longitude);
                            },
                            (error) => {
                                console.warn('Konum alınamadı: ', error.message);
                                // Konum reddedilse bile okumayı kaydet
                                @this.saveScanData(null, null);
                            },
                            { timeout: 10000 }
                        );
                    } else {
                        // Geolocation API desteklenmiyor
                        @this.saveScanData(null, null);
                    }
                }
            }));
        });
    </script>
</div>
