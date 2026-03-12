<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RequirementConnected;
use App\Models\StudyRequirement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudyRequirementController extends BaseApiController
{
    /**
     * Get paginated list of study requirements.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudyRequirement::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->status($request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('location_city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('learning_mode')) {
            $query->where('learning_mode', $request->string('learning_mode'));
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $requirements = $query->paginate($perPage);

        return $this->success('Study requirements retrieved successfully.', [
            'data' => $requirements->items(),
            'meta' => [
                'current_page' => $requirements->currentPage(),
                'last_page' => $requirements->lastPage(),
                'per_page' => $requirements->perPage(),
                'total' => $requirements->total(),
                'from' => $requirements->firstItem(),
                'to' => $requirements->lastItem(),
            ],
            'links' => [
                'first' => $requirements->url(1),
                'last' => $requirements->url($requirements->lastPage()),
                'prev' => $requirements->previousPageUrl(),
                'next' => $requirements->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Create a new study requirement (auth user stored in user_id).
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'contact_role' => ['required', 'string', Rule::in(['student', 'parent'])],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'student_grade' => ['nullable', 'string', 'max:100'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['string', 'max:255'],
            'learning_mode' => ['nullable', 'string', Rule::in(['online', 'offline', 'both'])],
            'preferred_days' => ['nullable', 'string', 'max:255'],
            'preferred_time' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'location_area' => ['nullable', 'string', 'max:255'],
            'location_pincode' => ['nullable', 'string', 'max:12'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'requirements' => ['nullable', 'string', 'max:5000'],
        ]);

        $requirement = StudyRequirement::create(array_merge($validated, [
            'user_id' => $user->id,
            'status' => 'new',
        ]));

        $requirement->load(['user:id,name,email']);

        return $this->created($requirement->toArray(), 'Study requirement created successfully.');
    }

    /**
     * Show a single study requirement.
     */
    public function show(StudyRequirement $studyRequirement): JsonResponse
    {
        $studyRequirement->load(['user:id,name,email', 'connectedUsers.user:id,name,email']);

        return $this->success('Study requirement retrieved successfully.', $studyRequirement->toArray());
    }

    /**
     * Connect the authenticated user to a study requirement (express interest).
     */
    public function connect(Request $request, StudyRequirement $studyRequirement): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($studyRequirement->user_id && (int) $studyRequirement->user_id === (int) $user->id) {
            return $this->error('You cannot connect to your own requirement.', 422);
        }

        if (! in_array($studyRequirement->status, ['new', 'in_review'], true)) {
            return $this->error('This requirement is no longer accepting connections.', 422);
        }

        if (RequirementConnected::where('requirement_id', $studyRequirement->id)->where('user_id', $user->id)->exists()) {
            return $this->error('You have already connected to this requirement.', 422);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $connected = RequirementConnected::create([
            'requirement_id' => $studyRequirement->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);

        $connected->load(['requirement', 'user:id,name,email']);

        return $this->created($connected->toArray(), 'Successfully connected to the requirement.');
    }
}
