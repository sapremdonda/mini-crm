<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('company');

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        if ($request->has('company_id') && $request->company_id != '') {
            $query->where('company_id', $request->company_id);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id', 
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);
        return response()->json(['message' => 'Contact created!', 'contact' => $contact], 201);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $contact->update($validated);
        return response()->json(['message' => 'Contact updated!', 'contact' => $contact]);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(['message' => 'Contact deleted!']);
    }
}