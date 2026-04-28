<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'name'); 
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company = Company::create($validated);
        return response()->json(['message' => 'Company created!', 'company' => $company], 201);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo); 
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($validated);
        return response()->json(['message' => 'Company updated!', 'company' => $company]);
    }

    public function destroy(Company $company)
    {
        $company->delete(); 
        return response()->json(['message' => 'Company deleted!']);
    }

    public function trashed()
    {
        return response()->json(['data' => Company::onlyTrashed()->get()]);
    }

    public function restore($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();
        return response()->json(['message' => 'Company restored!']);
    }
}