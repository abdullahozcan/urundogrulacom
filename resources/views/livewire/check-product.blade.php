<div class="w-full max-w-3xl mx-auto py-12 px-6 animate-fade-in" x-data="productCheck()">
    
    <!-- Trust Scan Animation Style -->
    <style>
        @keyframes scanSweep {
            0% { transform: translateY(-100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(100%); opacity: 0; }
        }
        .trust-scan::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(111, 251, 190, 0.1), transparent);
            animation: scanSweep 3s infinite;
            pointer-events: none;
            z-index: 50;
        }
    </style>

    <!-- Digital Product Content -->
    @if($item->batch->product->type === 'digital' && $item->batch->product->digital_file_path)
    <div class="mb-12 rounded-[1rem] bg-surface-container-lowest shadow-[0_40px_80px_rgba(11,28,48,0.05)] overflow-hidden flex justify-center relative">
        @if($item->batch->product->digital_content_type === 'video')
            <video controls class="w-full max-h-[60vh] object-contain rounded-[1rem]">
                <source src="{{ Storage::url($item->batch->product->digital_file_path) }}" type="video/mp4">
                Tarayıcınız video etiketini desteklemiyor.
            </video>
        @elseif($item->batch->product->digital_content_type === 'pdf')
            <iframe src="{{ Storage::url($item->batch->product->digital_file_path) }}" class="w-full h-[70vh] border-0 rounded-[1rem]"></iframe>
        @endif
    </div>
    @endif

    <!-- Verification Card -->
    <div class="relative bg-surface-container-lowest rounded-[1rem] shadow-[0_40px_80px_rgba(11,28,48,0.05)] overflow-hidden trust-scan p-8 md:p-12">
        <!-- Status Indicator -->
        <div class="flex flex-col items-center mb-10 text-center">
            <div class="w-20 h-20 bg-tertiary-container rounded-full flex items-center justify-center mb-6 shadow-[0_0_40px_rgba(78,222,163,0.3)]">
                <span class="material-symbols-outlined text-tertiary-fixed-dim text-4xl" style="font-variation-settings: 'FILL' 1;">verified</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-primary-container tracking-tight mb-2">Orijinal Ürün</h1>
            <p class="text-on-surface-variant font-medium">Bu ürün başarıyla doğrulandı.</p>
        </div>

        <div class="flex flex-col md:flex-row gap-10">
            <!-- Product Image -->
            @if($item->batch->product->image)
            <div class="w-full md:w-1/3 flex-shrink-0">
                <div class="bg-surface-container-low rounded-[1rem] p-4 flex items-center justify-center aspect-square">
                    <img src="{{ Storage::url($item->batch->product->image) }}" alt="Ürün Görseli" class="w-full h-full object-cover rounded-xl">
                </div>
            </div>
            @endif

            <!-- Product Data -->
            <div class="w-full flex-grow flex flex-col justify-center space-y-6">
                <!-- Data Row -->
                <div class="bg-surface p-5 rounded-xl">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-[0.05em] mb-1">Firma</p>
                    <p class="text-lg font-bold text-on-background">{{ $item->batch->product->company->name }}</p>
                </div>
                
                <!-- Data Row -->
                <div class="bg-surface p-5 rounded-xl">
                    <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-[0.05em] mb-1">Ürün</p>
                    <p class="text-2xl font-black text-primary-container tracking-tight">{{ $item->batch->product->name }}</p>
                </div>
                
                <!-- Data Row -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="bg-surface p-5 rounded-xl flex-1">
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-[0.05em] mb-1">Parti No</p>
                        <p class="text-lg font-bold text-on-background">{{ $item->batch->batch_number }}</p>
                    </div>
                    
                    <div class="bg-surface p-5 rounded-xl flex-1">
                        <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-[0.05em] mb-1">Seri No (UUID)</p>
                        <p class="font-mono text-sm text-on-surface-variant break-all">
                            {{ $item->uuid }}
                        </p>
                    </div>
                </div>
            </div>
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
                                @this.saveScanData(null, null);
                            },
                            { timeout: 10000 }
                        );
                    } else {
                        @this.saveScanData(null, null);
                    }
                }
            }));
        });
    </script>
</div>
