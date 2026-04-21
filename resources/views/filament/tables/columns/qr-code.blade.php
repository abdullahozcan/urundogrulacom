<div class="p-2">
    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate('https://urundogrula.com/check/' . $getRecord()->uuid) !!}
</div>
