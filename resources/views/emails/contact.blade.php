@php
    $data = $data ?? [];
@endphp

<x-mail::message>
# İletişim Formu

Yeni bir iletişim formu mesajı aldınız.

**Ad Soyad:** {{ $data['name'] ?? '-' }}  
**E-posta:** {{ $data['email'] ?? '-' }}  
**Telefon:** {{ $data['phone'] ?? '-' }}  

**Mesaj:**  
{{ $data['message'] ?? '-' }}

<small>Bu mesaj, {{ config('app.name') }} iletişim formu üzerinden gönderilmiştir.</small>
</x-mail::message>
