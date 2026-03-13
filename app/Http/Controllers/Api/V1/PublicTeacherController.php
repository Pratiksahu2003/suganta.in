<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FilterOptionsHelper;
use App\Models\TeacherProfile;
use App\Services\PublicProfile\PublicTeacherFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTeacherController extends BaseApiController
{
    public function __construct(
        private PublicTeacherFormatter $formatter
    ) {}

    /**
     * Get filter options for teacher listing.
     */
    public function options(): JsonResponse
    {
        $optionKeys = [
            'gender', 'teaching_mode', 'availability_status', 'hourly_rate_range',
            'monthly_rate_range', 'teaching_experience_years', 'travel_radius_km', 'highest_qualification',
        ];
        $data = [
            'options' => FilterOptionsHelper::buildFromConfig($optionKeys),
            'subjects' => FilterOptionsHelper::getActiveSubjects(),
            'cities' => FilterOptionsHelper::getTeacherCities(),
        ];
        return $this->success('Teacher filter options retrieved successfully.', $data);
    }

    /**
     * Get public list of teachers (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = TeacherProfile::query()
            ->forPublicListing()
            ->with([
                'user:id,name',
                'user.profile:id,user_id,profile_image,gender_id,highest_qualification',
                'user.profile.teachingInfo',
                'subjects:id,name,slug',
                'institute:id,institute_name,city',
            ]);

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request->query('sort', 'rating'), $request->query('order', 'desc'));

        $perPage = min((int) $request->query('per_page', 15), 50);
        $teachers = $query->paginate($perPage);
        $items = $teachers->getCollection()->map(fn ($t) => $this->formatter->listItem($t));

        return $this->success('Teachers retrieved successfully.', [
            'teachers' => $items,
            'pagination' => FilterOptionsHelper::paginationMeta($teachers),
        ]);
    }

    /**
     * Get single teacher profile by ID.
     */
    public function show(int $id): JsonResponse
    {
        $teacher = TeacherProfile::query()
            ->forPublicListing()
            ->with([
                'user:id,name,email',
                'user.profile:id,user_id,profile_image,gender_id,highest_qualification,bio,phone_primary,whatsapp,city,state,pincode',
                'user.profile.teachingInfo',
                'subjects:id,name,slug,category',
                'institute:id,institute_name,city,address,website',
                'reviews' => fn ($q) => $q->where('status', 'published')->latest()->limit(5),
            ])
            ->where('id', $id)
            ->first();

        if (!$teacher) {
            return $this->notFound('Teacher not found.');
        }

        return $this->success('Teacher profile retrieved successfully.', $this->formatter->show($teacher));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->boolean('verified', true)) {
            $query->verified();
        }
        if ($location = trim((string) $request->query('location'))) {
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('teaching_city', 'like', "%{$location}%")
                    ->orWhereHas('user.profile', fn ($pq) => $pq->where('area', 'like', "%{$location}%"));
            });
        } elseif ($city = $request->query('city')) {
            $query->where(fn ($q) => $q->where('city', 'like', "%{$city}%")->orWhere('teaching_city', 'like', "%{$city}%"));
        }
        if ($pincode = $request->query('pincode')) {
            $query->whereHas('user.profile', fn ($q) => $q->where('pincode', $pincode));
        }
        if ($subjectId = $request->query('subject_id')) {
            $query->whereHas('subjects', fn ($q) => $q->where('subjects.id', $subjectId));
        }
        if ($hourlyRateRange = $request->query('hourly_rate_range')) {
            $query->whereHas('user.profile.teachingInfo', fn ($q) => $q->where('hourly_rate_id', $hourlyRateRange));
        }
        if ($monthlyRateRange = $request->query('monthly_rate_range')) {
            $query->whereHas('user.profile.teachingInfo', fn ($q) => $q->where('monthly_rate_id', $monthlyRateRange));
        }
        if ($experience = $request->query('experience')) {
            $query->where(fn ($q) => $q
                ->whereHas('user.profile.teachingInfo', fn ($tq) => $tq->where('teaching_experience_years', $experience))
                ->orWhere('experience_years', $experience));
        }
        if ($teachingMode = $request->query('teaching_mode')) {
            $query->whereHas('user.profile.teachingInfo', fn ($q) => $q->where('teaching_mode_id', $teachingMode));
        }
        if ($availability = $request->query('availability')) {
            $query->whereHas('user.profile.teachingInfo', fn ($q) => $q->where('availability_status_id', $availability));
        }
        if ($search = trim((string) $request->query('search'))) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
    }

    private function applySorting($query, string $sortBy, string $sortOrder): void
    {
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        match ($sortBy) {
            'price_low' => $query->orderBy('teacher_profiles.hourly_rate', 'asc'),
            'price_high' => $query->orderBy('teacher_profiles.hourly_rate', 'desc'),
            'name' => $query->join('users', 'teacher_profiles.user_id', '=', 'users.id')
                ->orderBy('users.name', $order)
                ->select('teacher_profiles.*'),
            'created_at' => $query->orderBy('teacher_profiles.created_at', $order),
            default => $query->orderBy('teacher_profiles.rating', $order)
                ->orderBy('teacher_profiles.total_reviews', 'desc'),
        };
    }
}
