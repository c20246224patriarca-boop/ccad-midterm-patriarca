<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassengerTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terminal_trip_id' => [
                'required',
                'exists:terminal_trips,id'
            ],

            'passenger_name' => [
                'required',
                'string',
                'max:255'
            ],

            'passenger_email' => [
                'nullable',
                'email',
                'max:255'
            ],

            'seat_number' => [
                'required',
                'integer',
                'min:1'
            ],

            'status' => [
                'sometimes',
                'in:booked,cancelled'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'terminal_trip_id.required' => 'Please select a trip.',
            'passenger_name.required' => 'Passenger name is required.',
            'passenger_email.email' => 'Enter a valid email address.',
            'seat_number.required' => 'Seat number is required.',
            'seat_number.min' => 'Seat number must be at least 1.',
        ];
    }
}