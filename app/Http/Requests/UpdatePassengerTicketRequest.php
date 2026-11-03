<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassengerTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passenger_name' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'passenger_email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255'
            ],

            'seat_number' => [
                'sometimes',
                'required',
                'integer',
                'min:1'
            ],

            'status' => [
                'sometimes',
                'required',
                'in:booked,cancelled'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'passenger_name.required' => 'Passenger name is required.',
            'passenger_email.email' => 'Enter a valid email.',
            'seat_number.min' => 'Seat number must be at least 1.',
            'status.in' => 'Invalid ticket status.',
        ];
    }
}