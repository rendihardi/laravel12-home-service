<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingTransactionApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        foreach ($data['transaction_details'] as &$detail) {
            if (isset($detail['home_service']['thumbnail'])) {
                $detail['home_service']['thumbnail'] = asset('storage/'.$detail['home_service']['thumbnail']);
            }
        }

        return $data;
    }
}
