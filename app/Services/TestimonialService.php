<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialService
{
    public function create(array $data, Request $request): Testimonial
    {
        $data['status'] = $request->has('status');

        return Testimonial::create($data);
    }

    public function update(Testimonial $testimonial, array $data, Request $request): Testimonial
    {
        $data['status'] = $request->has('status');

        $testimonial->update($data);

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }

    public function getEditData(Testimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'rating' => $testimonial->rating,
            'sort_order' => $testimonial->sort_order,
            'status' => $testimonial->status,
            'en' => [
                'client_name' => $testimonial->getTranslation('client_name', 'en'),
                'client_role' => $testimonial->getTranslation('client_role', 'en'),
                'comment' => $testimonial->getTranslation('comment', 'en'),
            ],
            'uz' => [
                'client_name' => $testimonial->getTranslation('client_name', 'uz'),
                'client_role' => $testimonial->getTranslation('client_role', 'uz'),
                'comment' => $testimonial->getTranslation('comment', 'uz'),
            ],
            'ru' => [
                'client_name' => $testimonial->getTranslation('client_name', 'ru'),
                'client_role' => $testimonial->getTranslation('client_role', 'ru'),
                'comment' => $testimonial->getTranslation('comment', 'ru'),
            ],
        ];
    }
}
