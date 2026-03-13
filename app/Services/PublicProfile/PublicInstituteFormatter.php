<?php

namespace App\Services\PublicProfile;

use App\Helpers\PublicProfileOptionsMapper;
use App\Models\Institute;
use Illuminate\Support\Str;

class PublicInstituteFormatter
{
    /**
     * Format institute for list view.
     */
    public function listItem(Institute $institute): array
    {
        return [
            'id' => $institute->id,
            'name' => $institute->display_name,
            'description' => Str::limit($institute->description ?? $institute->specialization ?? '', 150),
            'logo_url' => $institute->logo ? storage_file_url($institute->logo) : null,
            'city' => $institute->city ?? $institute->branch_city,
            'state' => $institute->state ?? $institute->branch_state,
            'rating' => (float) ($institute->rating ?? 0),
            'teachers_count' => $institute->teachers_count ?? $institute->teachers()->count(),
            'subjects' => $institute->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug])->take(5)->all(),
            'verified' => (bool) $institute->verified,
            'is_featured' => (bool) $institute->is_featured,
        ];
    }

    /**
     * Format institute for show/detail view.
     */
    public function show(Institute $institute): array
    {
        $instituteInfo = $institute->user?->profile?->instituteInfo;
        $options = PublicProfileOptionsMapper::mapInstituteOptions($institute, $instituteInfo);

        $galleryUrls = [];
        if (!empty($institute->gallery_images) && is_array($institute->gallery_images)) {
            foreach ($institute->gallery_images as $img) {
                if (is_string($img)) {
                    $galleryUrls[] = storage_file_url($img);
                }
            }
        }

        return [
            'id' => $institute->id,
            'user' => [
                'id' => $institute->user?->id,
                'name' => $institute->user?->name,
                'email' => $institute->user?->email,
            ],
            'profile' => [
                'name' => $institute->display_name,
                'description' => $institute->description,
                'specialization' => $institute->specialization,
                'affiliation' => $institute->affiliation,
                'registration_number' => $institute->registration_number,
                'website' => $institute->website,
                'contact_person' => $institute->contact_person,
                'contact_phone' => $institute->contact_phone,
                'contact_email' => $institute->contact_email,
                'address' => $institute->address ?? $institute->branch_address,
                'city' => $institute->city ?? $institute->branch_city,
                'state' => $institute->state ?? $institute->branch_state,
                'pincode' => $institute->pincode ?? $institute->branch_pincode,
                'established_year' => $institute->established_year,
                'institute_type' => $options['institute_type'],
                'institute_category' => $options['institute_category'],
                'establishment_year' => $options['establishment_year'],
                'total_students' => $institute->total_students,
                'total_students_range' => $options['total_students'],
                'total_teachers_range' => $options['total_teachers'],
                'logo_url' => $institute->logo ? storage_file_url($institute->logo) : null,
                'gallery_urls' => $galleryUrls,
                'facilities' => $institute->facilities ?? [],
            ],
            'rating' => (float) ($institute->rating ?? 0),
            'teachers_count' => $institute->teachers_count ?? $institute->teachers()->count(),
            'subjects' => $institute->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug, 'category' => $s->category])->all(),
            'branches' => $institute->childBranches->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->branch_name ?: $b->institute_name,
                'address' => $b->branch_address,
                'city' => $b->branch_city,
                'state' => $b->branch_state,
                'phone' => $b->branch_phone,
                'email' => $b->branch_email,
            ])->all(),
            'teachers_preview' => $institute->teachers->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->user?->name ?? 'Teacher',
            ])->all(),
            'verified' => (bool) $institute->verified,
            'is_featured' => (bool) $institute->is_featured,
        ];
    }
}
