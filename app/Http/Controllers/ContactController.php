<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Mail::send(new ContactMail($validated));

        return redirect()->route('contact')->with('success', 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.');
    }
}
