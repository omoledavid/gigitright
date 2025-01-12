<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CertificateResource;
use App\Models\Certification;
use App\Rules\FileTypeValidate;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $certifications = $user->certificate;
        return $this->ok('success',CertificateResource::collection($certifications));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'date_awarded' => 'required|date',
            'valid_until' => 'required|date',
            'certificate_file' => ['nullable', new FileTypeValidate(['pdf', 'docx'])],
        ]);
        if ($request->hasFile('certificate_file')) {
            try {
                $validatedData['certificate_file'] = fileUploader($request->certificate_file, getFilePath('certificates'));
            } catch (\Exception $exp) {
                return $this->error('Could not upload your image');
            }
        }
        $validatedData['user_id'] = $user->id;
        $validatedData['status'] = Status::ACTIVE;
        $certificate = Certification::query()->create($validatedData);
        return $this->ok('',new CertificateResource($certificate));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $certification = Certification::query()->findOrFail($id);
        return $this->ok('',new CertificateResource($certification));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $certificate = Certification::query()->findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'date_awarded' => 'required|date',
            'valid_until' => 'required|date',
            'certificate_file' => ['nullable', new FileTypeValidate(['pdf', 'docx'])],
        ]);
        if ($request->hasFile('certificate_file')) {
            try {
                $old = $certificate->certificate_file;
                $validatedData['certificate_file'] = fileUploader($request->certificate_file, getFilePath('certificates'), old:$old);
            } catch (\Exception $exp) {
                return $this->error('Could not upload your image');
            }
        }
        $certificate->update($validatedData);
        return $this->ok('Certificate updated successfully',new CertificateResource($certificate));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $certificate = Certification::query()->find($id);
        if(!$certificate){
            return $this->error("Certificate not found or deleted already", 404);
        }
        $certificate->delete();
        return $this->ok('Certificate deleted');
    }
}
