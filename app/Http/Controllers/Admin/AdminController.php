<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,agent,approver,admin',
        ]);

        $user->update($request->only('role'));

        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function categories()
    {
        $categories = Category::latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'requires_approval'  => 'boolean',
        ]);

        Category::create([
            'name'              => $request->name,
            'description'       => $request->description,
            'requires_approval' => $request->boolean('requires_approval'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría creada correctamente.');
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'requires_approval'  => 'boolean',
        ]);

        $category->update([
            'name'              => $request->name,
            'description'       => $request->description,
            'requires_approval' => $request->boolean('requires_approval'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoría eliminada correctamente.');
    }

    public function approvalFlows()
    {
        $approvalFlows = ApprovalFlow::with(['category', 'levels'])->latest()->get();

        return view('admin.approval-flows.index', compact('approvalFlows'));
    }

    public function createApprovalFlow()
    {
        $categories = Category::whereDoesntHave('approvalFlow')->get();

        return view('admin.approval-flows.create', compact('categories'));
    }

    public function storeApprovalFlow(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id|unique:approval_flows,category_id',
            'name'        => 'required|string|max:255',
        ]);

        $approvalFlow = ApprovalFlow::create($request->only('category_id', 'name'));

        return redirect()->route('admin.approval-flows.edit', $approvalFlow)->with('success', 'Flujo de aprobación creado correctamente.');
    }

    public function editApprovalFlow(ApprovalFlow $approvalFlow)
    {
        $approvalFlow->load(['category', 'levels']);

        return view('admin.approval-flows.edit', compact('approvalFlow'));
    }

    public function updateApprovalFlow(Request $request, ApprovalFlow $approvalFlow)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $approvalFlow->update($request->only('name'));

        return redirect()->route('admin.approval-flows.edit', $approvalFlow)->with('success', 'Flujo de aprobación actualizado correctamente.');
    }

    public function destroyApprovalFlow(ApprovalFlow $approvalFlow)
    {
        $approvalFlow->delete();

        return redirect()->route('admin.approval-flows.index')->with('success', 'Flujo de aprobación eliminado correctamente.');
    }

    public function storeApprovalLevel(Request $request, ApprovalFlow $approvalFlow)
    {
        $request->validate([
            'order' => 'required|integer|min:1',
            'role'  => 'required|in:user,agent,approver,admin',
            'name'  => 'required|string|max:255',
        ]);

        $approvalFlow->levels()->create($request->only('order', 'role', 'name'));

        return redirect()->route('admin.approval-flows.edit', $approvalFlow)->with('success', 'Nivel de aprobación agregado correctamente.');
    }

    public function destroyApprovalLevel(ApprovalLevel $approvalLevel)
    {
        $approvalFlow = $approvalLevel->approval_flow_id;
        $approvalLevel->delete();

        return redirect()->route('admin.approval-flows.edit', $approvalFlow)->with('success', 'Nivel de aprobación eliminado correctamente.');
    }
}
