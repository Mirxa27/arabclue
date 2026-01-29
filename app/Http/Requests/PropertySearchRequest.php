<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertySearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'guests' => 'nullable|integer|min:1|max:20',
            'property_type' => 'nullable|string|in:house,apartment,villa,studio,room',
            'room_type' => 'nullable|string|in:entire_place,private_room,shared_room',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'instant_booking' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:price_asc,price_desc,rating,newest',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:50',
        ];
    }
}
