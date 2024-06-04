<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Resources\PatientCollection;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::where('user_id', auth()->user()->getAuthIdentifier())->get();

        return new PatientCollection($patients);
    }

    public function store(PatientStoreRequest $request)
    {
        $request->validated();

        $image = $request->file('image');
        $fileName = date('YmdHi') . $image->getClientOriginalName();
        $image->move(public_path('public/images'), $fileName);

        $response = Http::attach('image', file_get_contents(public_path('public/images/' . $fileName)), $fileName)->post('http://localhost:5000/upload');

        $patient = Patient::create([
            'name' => $request->name,
            'result' => $response->json("prediction"),
            'label' => '',
            'image' => $fileName,
            'user_id' => auth()->user()->getAuthIdentifier(),
        ]);

        return new PatientResource($patient);
    }

    public function show(Request $request, Patient $patient)
    {
        return new PatientResource($patient);
    }

    public function update(PatientUpdateRequest $request, Patient $patient)
    {
        $request->validated();


        $patient->update([
            'name' => $request->name,
            'label' => $request->label,
        ]);

        return new PatientResource($patient);
    }

    public function destroy(Request $request, Patient $patient)
    {
        $patient->delete();

        return response()->noContent();
    }
}
