<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerminalTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bus_id' => [
                'required',
                'exists:buses,id'
            ],

            'driver_id' => [
                'required',
                'exists:drivers,id'
            ],

            'route_origin' => [
                'required',
                'string',
                'max:255'
            ],

            'route_destination' => [
                'required',
                'string',
                'max:255',
                'different:route_origin'
            ],

            'departure_time' => [
                'required',
                'date',
                'after:now'
            ],

            'ticket_price' => [
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
            'bus_id.required' => 'Please select a bus.',
            'bus_id.exists' => 'The selected bus does not exist.',

            'driver_id.required' => 'Please select a driver.',
            'driver_id.exists' => 'The selected driver does not exist.',

            'route_origin.required' => 'Route origin is required.',
            'route_destination.required' => 'Route destination is required.',
            'route_destination.different' => 'Origin and destination must be different.',

            'departure_time.after' => 'Departure time must be in the future.',

            'ticket_price.min' => 'Ticket price must be greater than zero.',
        ];
    }
}