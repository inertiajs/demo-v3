<?php

namespace App\Http\Controllers\Feature;

use App\Http\Requests\Feature\DottedKeysRequest;
use App\Http\Requests\Feature\FileUploadRequest;
use App\Http\Requests\Feature\PrecognitionRequest;
use App\Http\Requests\Feature\SimpleFormRequest;
use App\Http\Requests\Feature\ValidationErrorBagRequest;
use App\Http\Requests\Feature\ValidationRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FormController
{
    public function useForm(): Response
    {
        return Inertia::render('Features/Forms/UseForm');
    }

    public function submitUseForm(SimpleFormRequest $request): RedirectResponse
    {
        return Inertia::flash('message', 'Form submitted successfully! Name: '.$request->validated('name'))->back();
    }

    public function formComponent(): Response
    {
        return Inertia::render('Features/Forms/FormComponent');
    }

    public function submitFormComponent(SimpleFormRequest $request): RedirectResponse
    {
        return Inertia::flash('message', 'Form submitted successfully! Name: '.$request->validated('name'))->back();
    }

    public function fileUploads(): Response
    {
        return Inertia::render('Features/Forms/FileUploads');
    }

    public function submitFileUploads(FileUploadRequest $request): RedirectResponse
    {
        $fileCount = count($request->file('files', []));
        $photoCount = $request->hasFile('photo') ? 1 : 0;
        $count = $fileCount + $photoCount;

        return Inertia::flash('message', "Uploaded {$count} file(s) successfully!")->back();
    }

    public function validation(): Response
    {
        return Inertia::render('Features/Forms/Validation');
    }

    public function submitValidation(ValidationRequest $request): RedirectResponse
    {
        return Inertia::flash('message', 'Primary form submitted successfully!')->back();
    }

    public function submitValidationSecondary(ValidationErrorBagRequest $request): RedirectResponse
    {
        return Inertia::flash('message', 'Secondary form submitted successfully!')->back();
    }

    public function precognition(): Response
    {
        return Inertia::render('Features/Forms/Precognition');
    }

    public function storeAccount(PrecognitionRequest $request): RedirectResponse
    {
        return Inertia::flash('message', 'Account created for '.$request->validated('username').'!')->back();
    }

    public function optimisticUpdates(Request $request): Response
    {
        return Inertia::render('Features/Forms/OptimisticUpdates', [
            'contacts' => ContactResource::collection(
                Contact::query()->latest()->orderByDesc('id')->limit(10)->get()
            ),
        ]);
    }

    public function useFormContext(): Response
    {
        return Inertia::render('Features/Forms/UseFormContext');
    }

    public function dottedKeys(): Response
    {
        return Inertia::render('Features/Forms/DottedKeys');
    }

    public function submitDottedKeys(DottedKeysRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return Inertia::flash('message', 'Form submitted successfully!')
            ->flash('parsedData', $data)
            ->back();
    }

    public function wayfinder(): Response
    {
        return Inertia::render('Features/Forms/Wayfinder', [
            'sampleContact' => new ContactResource(Contact::firstOrFail()),
        ]);
    }

    public function toggleFavorite(Request $request, Contact $contact): RedirectResponse
    {
        Sleep::for(1)->second();

        if ($request->boolean('simulate_error')) {
            throw ValidationException::withMessages([
                'contact' => 'Simulated validation error for optimistic update rollback demo.',
            ]);
        }

        $contact->update(['is_favorite' => ! $contact->is_favorite]);

        return Inertia::flash('message', $contact->is_favorite ? 'Added to favorites.' : 'Removed from favorites.')->back();
    }
}
