<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerminalTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bus_id' => [
                'sometimes',
                'required',
                'exists:buses,id'
            ],

            'driver_id' => [
                'sometimes',
                'required',
                'exists:drivers,id'
            ],

            'route_origin' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'route_destination' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'different:route_origin'
            ],

            'departure_time' => [
                'sometimes',
                'required',
                'date',
                'after:now'
            ],

            'ticket_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01'
            ],

            'trip_status' => [
                'sometimes',
                'in:scheduled,departed,completed,cancelled'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bus_id.exists' => 'Selected bus is invalid.',
            'driver_id.exists' => 'Selected driver is invalid.',
            'departure_time.after' => 'Departure must be in the future.',
            'ticket_price.min' => 'Ticket price must be greater than zero.',
        ];
    }
}