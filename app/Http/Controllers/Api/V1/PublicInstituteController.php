<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FilterOptionsHelper;
use App\Models\Institute;
use App\Services\PublicProfile\PublicInstituteFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicInstituteController extends BaseApiController
{
    public function __construct(
        private PublicInstituteFormatter $formatter
    ) {}

    /**
     * Get filter options for institute listing.
     */
    public function options(): JsonResponse
    {
        $optionKeys = [
            'institute_type', 'institute_category', 'establishment_year_range',
            'total_students_range', 'total_teachers_range',
        ];
        $data = [
            'options' => FilterOptionsHelper::buildFromConfig($optionKeys),
            'subjects' => FilterOptionsHelper::getActiveSubjects(),
            'cities' => FilterOptionsHelper::getInstituteCities(),
        ];
        return $this->success('Institute filter options retrieved successfully.', $data);
    }

    /**
     * Get public list of institutes (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Institute::query()
            ->forPublicListing()
            ->with(['subjects:id,name,slug'])
            ->withCount('teachers');

        $this->applyFilters($query, $request);

        $perPage = min((int) $request->query('per_page', 15), 50);
        $institutes = $query->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->orderByDesc('teachers_count')
            ->paginate($perPage);
        $items = $institutes->getCollection()->map(fn ($i) => $this->formatter->listItem($i));

        return $this->success('Institutes retrieved successfully.', [
            'institutes' => $items,
            'pagination' => FilterOptionsHelper::paginationMeta($institutes),
        ]);
    }

    /**
     * Get single institute profile by ID.
     */
    public function show(int $id): JsonResponse
    {
        $institute = Institute::query()
            ->forPublicListing()
            ->with([
                'user:id,name,email',
                'user.profile.instituteInfo',
                'subjects:id,name,slug,category',
                'childBranches' => fn ($q) => $q->where('is_active_branch', true)
                    ->select('id', 'parent_institute_id', 'institute_name', 'branch_name', 'branch_address', 'branch_city', 'branch_state', 'branch_phone', 'branch_email'),
                'teachers' => fn ($q) => $q->where('verification_status', 'verified')->with('user:id,name')->limit(10),
            ])
            ->withCount('teachers')
            ->where('id', $id)
            ->first();

        if (!$institute) {
            return $this->notFound('Institute not found.');
        }

        return $this->success('Institute profile retrieved successfully.', $this->formatter->show($institute));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->boolean('verified', false)) {
            $query->where('verified', true);
        }
        if ($city = $request->query('city')) {
            $query->where(fn ($q) => $q->where('city', 'like', "%{$city}%")->orWhere('branch_city', 'like', "%{$city}%"));
        }
        if ($search = trim((string) $request->query('search'))) {
            $query->where(fn ($q) => $q->where('institute_name', 'like', "%{$search}%")->orWhere('branch_name', 'like', "%{$search}%"));
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
    }
}
