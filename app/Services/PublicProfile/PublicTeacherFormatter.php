<?php

namespace App\Services\PublicProfile;

use App\Helpers\PublicProfileOptionsMapper;
use App\Models\TeacherProfile;
use Illuminate\Support\Str;

class PublicTeacherFormatter
{
    /**
     * Format teacher for list view.
     */
    public function listItem(TeacherProfile $teacher): array
    {
        $user = $teacher->user;
        $options = PublicProfileOptionsMapper::mapTeacherOptions(
            $teacher,
            $user?->profile?->teachingInfo,
            $user?->profile
        );

        return [
            'id' => $teacher->id,
            'name' => $user?->name ?? 'Teacher',
            'bio' => Str::limit($teacher->bio ?? '', 120),
            'avatar_url' => $this->avatarUrl($teacher),
            'qualification' => $teacher->qualification ?? $teacher->qualifications,
            'experience_years' => $options['experience_years'],
            'rating' => (float) ($teacher->rating ?? 0),
            'total_reviews' => (int) ($teacher->total_reviews ?? 0),
            'hourly_rate' => $teacher->hourly_rate ? (float) $teacher->hourly_rate : null,
            'city' => $teacher->city ?? $teacher->teaching_city,
            'state' => $teacher->state,
            'teaching_mode' => $options['teaching_mode'],
            'availability_status' => $options['availability_status'],
            'subjects' => $teacher->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug])->all(),
            'institute' => $teacher->institute
                ? ['id' => $teacher->institute->id, 'name' => $teacher->institute->institute_name, 'city' => $teacher->institute->city]
                : null,
            'verified' => (bool) ($teacher->verified ?? ($teacher->verification_status === 'verified')),
            'is_featured' => (bool) $teacher->is_featured,
        ];
    }

    /**
     * Format teacher for show/detail view.
     */
    public function show(TeacherProfile $teacher): array
    {
        $user = $teacher->user;
        $profile = $user?->profile;
        $teachingInfo = $profile?->teachingInfo;
        $options = PublicProfileOptionsMapper::mapTeacherOptions($teacher, $teachingInfo, $profile);

        $reviews = $teacher->reviews->map(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'created_at' => $r->created_at?->toIso8601String(),
        ])->all();

        return [
            'id' => $teacher->id,
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? 'Teacher',
                'email' => $user?->email,
            ],
            'profile' => [
                'bio' => $teacher->bio ?? $profile?->bio,
                'profile_image_url' => $this->avatarUrl($teacher),
                'phone_primary' => $profile?->phone_primary,
                'whatsapp' => $profile?->whatsapp,
                'city' => $teacher->city ?? $teacher->teaching_city ?? $profile?->city,
                'state' => $teacher->state ?? $profile?->state,
                'pincode' => $profile?->pincode,
                'gender' => $options['gender'],
                'highest_qualification' => $options['highest_qualification'],
            ],
            'teaching' => [
                'qualification' => $teacher->qualification ?? $teacher->qualifications,
                'experience_years' => $options['experience_years'],
                'specialization' => $teacher->specialization,
                'languages' => $teacher->languages ?? [],
                'hourly_rate' => $teacher->hourly_rate ? (float) $teacher->hourly_rate : null,
                'hourly_rate_range' => $options['hourly_rate_range'],
                'monthly_rate' => $teacher->monthly_rate ? (float) $teacher->monthly_rate : null,
                'monthly_rate_range' => $options['monthly_rate_range'],
                'teaching_mode' => $options['teaching_mode'],
                'availability_status' => $options['availability_status'],
                'travel_radius_km' => $options['travel_radius_km'],
                'online_classes' => (bool) $teacher->online_classes,
                'home_tuition' => (bool) $teacher->home_tuition,
                'institute_classes' => (bool) $teacher->institute_classes,
            ],
            'rating' => (float) ($teacher->rating ?? 0),
            'total_reviews' => (int) ($teacher->total_reviews ?? 0),
            'total_students' => (int) ($teacher->total_students ?? 0),
            'subjects' => $teacher->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'slug' => $s->slug, 'category' => $s->category])->all(),
            'institute' => $teacher->institute ? [
                'id' => $teacher->institute->id,
                'name' => $teacher->institute->institute_name,
                'city' => $teacher->institute->city,
                'address' => $teacher->institute->address,
                'website' => $teacher->institute->website,
            ] : null,
            'verified' => (bool) ($teacher->verified ?? ($teacher->verification_status === 'verified')),
            'is_featured' => (bool) $teacher->is_featured,
            'reviews_sample' => $reviews,
        ];
    }

    private function avatarUrl(TeacherProfile $teacher): ?string
    {
        $path = $teacher->avatar
            ?? $teacher->user?->profile?->profile_image
            ?? null;
        if ($path) {
            return storage_file_url($path);
        }
        return $teacher->user?->avatar_url ?? null;
    }
}
