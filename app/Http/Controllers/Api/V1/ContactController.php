<?php

namespace App\Http\Controllers\Api\V1;

use App\Mail\ContactMailable;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends BaseApiController
{
    /**
     * Store a new contact form submission (public - no auth required).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contact = Contact::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('company.contact.email', config('mail.from.address')))->send(new ContactMailable($contact));
        } catch (\Throwable $e) {
            Log::warning('Contact form: notification email failed', ['contact_id' => $contact->id, 'error' => $e->getMessage()]);
        }

        return $this->created($contact, 'Contact form submitted successfully.');
    }
}
