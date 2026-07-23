<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['Super Admin', 'Wakasek Sarpras']);
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:100|unique:locations,name',
            'type' => 'required|string|in:Gudang,Lab,Studio,Kelas,Ruang',
        ];
    }
}