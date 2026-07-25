<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\ApprovalFlow;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $request->validated('role'),
            'position' => $request->validated('position'),
            'department' => $request->validated('department'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,agent,admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update([
            'role' => $request->input('role'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function categories()
    {
        $categories = Category::withCount('tickets')
            ->with(['subcategories' => fn ($q) => $q->withCount('tickets')->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => $this->serializeCategory($category))
            ->values();

        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'min:3', 'max:255', Rule::unique('categories', 'name')],
            'description'        => ['nullable', 'string'],
            'requires_approval'  => ['boolean'],
            'is_active'          => ['boolean'],
        ]);

        $category = Category::create([
            'name'              => $validated['name'],
            'description'       => $validated['description'] ?? null,
            'requires_approval' => $request->boolean('requires_approval'),
            'is_active'         => $request->boolean('is_active', true),
        ]);

        return response()->json(['category' => $this->serializeCategory($category)], 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'min:3', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description'        => ['nullable', 'string'],
            'requires_approval'  => ['boolean'],
        ]);

        $category->update([
            'name'              => $validated['name'],
            'description'       => $validated['description'] ?? null,
            'requires_approval' => $request->boolean('requires_approval'),
        ]);

        return response()->json(['category' => $this->serializeCategory($category)]);
    }

    /**
     * Activar/desactivar sin borrar: una categoría desactivada no aparece
     * como opción nueva (formulario de ticket, catálogo del navbar) pero
     * los tickets históricos que ya la usan la siguen mostrando normal.
     */
    public function toggleCategory(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json(['category' => $this->serializeCategory($category)]);
    }

    public function destroyCategory(Category $category)
    {
        $ticketCount = $category->tickets()->count();

        if ($ticketCount > 0) {
            return response()->json([
                'message' => "No se puede eliminar: {$ticketCount} ticket(s) usan esta categoría. Desactívala en su lugar para que deje de aparecer como opción nueva.",
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }

    public function storeSubcategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'min:3', 'max:255', Rule::unique('subcategories', 'name')->where('category_id', $category->id)],
            'is_active' => ['boolean'],
        ]);

        $subcategory = $category->subcategories()->create([
            'name'      => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['subcategory' => $this->serializeSubcategory($subcategory)], 201);
    }

    public function updateSubcategory(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'min:3', 'max:255',
                Rule::unique('subcategories', 'name')->where('category_id', $subcategory->category_id)->ignore($subcategory->id),
            ],
        ]);

        $subcategory->update(['name' => $validated['name']]);

        return response()->json(['subcategory' => $this->serializeSubcategory($subcategory)]);
    }

    public function toggleSubcategory(Subcategory $subcategory)
    {
        $subcategory->update(['is_active' => ! $subcategory->is_active]);

        return response()->json(['subcategory' => $this->serializeSubcategory($subcategory)]);
    }

    public function destroySubcategory(Subcategory $subcategory)
    {
        $ticketCount = $subcategory->tickets()->count();

        if ($ticketCount > 0) {
            return response()->json([
                'message' => "No se puede eliminar: {$ticketCount} ticket(s) usan esta subcategoría. Desactívala en su lugar para que deje de aparecer como opción nueva.",
            ], 422);
        }

        $subcategory->delete();

        return response()->json(['message' => 'Subcategoría eliminada correctamente.']);
    }

    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'requires_approval' => (bool) $category->requires_approval,
            'is_active' => (bool) $category->is_active,
            'tickets_count' => $category->tickets_count ?? $category->tickets()->count(),
            'subcategories' => $category->subcategories
                ->map(fn (Subcategory $subcategory) => $this->serializeSubcategory($subcategory))
                ->values(),
        ];
    }

    private function serializeSubcategory(Subcategory $subcategory): array
    {
        return [
            'id' => $subcategory->id,
            'category_id' => $subcategory->category_id,
            'name' => $subcategory->name,
            'is_active' => (bool) $subcategory->is_active,
            'tickets_count' => $subcategory->tickets_count ?? $subcategory->tickets()->count(),
        ];
    }

    public function approvalFlows()
    {
        $approvalFlows = ApprovalFlow::with(['category', 'levels'])->latest()->get();

        return view('admin.approval-flows.index', compact('approvalFlows'));
    }

    public function createApprovalFlow()
    {
        $categories = Category::whereDoesntHave('approvalFlow')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.approval-flows.create', compact('categories', 'users'));
    }

    public function storeApprovalFlow(Request $request)
    {
        $data = $this->validateApprovalFlow($request);

        DB::transaction(function () use ($data) {
            $approvalFlow = ApprovalFlow::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
            ]);

            foreach ($data['levels'] as $level) {
                $approvalFlow->levels()->create($level);
            }
        });

        return redirect()->route('admin.approval-flows.index')
            ->with('success', 'Flujo de aprobación creado correctamente.'.$this->missingApproversWarning($data['levels']));
    }

    public function editApprovalFlow(ApprovalFlow $approvalFlow)
    {
        $approvalFlow->load(['category', 'levels' => fn ($query) => $query->orderBy('order')->with('approver')]);

        $categories = Category::where('id', $approvalFlow->category_id)
            ->orWhereDoesntHave('approvalFlow')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        return view('admin.approval-flows.edit', compact('approvalFlow', 'categories', 'users'));
    }

    public function updateApprovalFlow(Request $request, ApprovalFlow $approvalFlow)
    {
        $data = $this->validateApprovalFlow($request, $approvalFlow);

        DB::transaction(function () use ($approvalFlow, $data) {
            $approvalFlow->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
            ]);

            // Reemplaza todos los niveles (decisión: no hay historial real que proteger todavía).
            $approvalFlow->levels()->delete();

            foreach ($data['levels'] as $level) {
                $approvalFlow->levels()->create($level);
            }
        });

        return redirect()->route('admin.approval-flows.edit', $approvalFlow)
            ->with('success', 'Flujo de aprobación actualizado correctamente.'.$this->missingApproversWarning($data['levels']));
    }

    public function destroyApprovalFlow(ApprovalFlow $approvalFlow)
    {
        $approvalFlow->delete();

        return redirect()->route('admin.approval-flows.index')->with('success', 'Flujo de aprobación eliminado correctamente.');
    }

    /**
     * Valida nombre/categoría del flujo y la lista de niveles. El "order" de
     * cada nivel no se toma del request: se recalcula por la posición en el
     * arreglo enviado, así un orden duplicado es estructuralmente imposible.
     */
    protected function validateApprovalFlow(Request $request, ?ApprovalFlow $ignoring = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id'),
                Rule::unique('approval_flows', 'category_id')->ignore($ignoring?->id),
            ],
            'levels' => ['required', 'array', 'min:1'],
            'levels.*.approver_id' => ['nullable', Rule::exists('users', 'id')],
        ], [
            'levels.required' => 'Debes agregar al menos un nivel de aprobación.',
            'levels.min' => 'Debes agregar al menos un nivel de aprobación.',
            'category_id.unique' => 'Esa categoría ya tiene un flujo de aprobación asignado.',
        ]);

        $validated['levels'] = collect($validated['levels'])
            ->values()
            ->map(fn ($level, $index) => [
                'order' => $index + 1,
                'approver_id' => $level['approver_id'] ?: null,
                'name' => 'Nivel '.($index + 1),
            ])
            ->all();

        return $validated;
    }

    protected function missingApproversWarning(array $levels): string
    {
        $missing = collect($levels)->whereNull('approver_id')->count();

        if ($missing === 0) {
            return '';
        }

        return ' '.$missing.' nivel(es) quedaron sin aprobador asignado — complétalo cuando tengas usuarios cargados.';
    }
}
