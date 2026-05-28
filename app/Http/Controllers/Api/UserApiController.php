<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    public function index() { return response()->json(User::withCount('domains', 'mailboxes')->paginate(15)); }
    public function store(Request $request) {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:8']);
        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'role' => $request->role ?? 'user', 'status' => 'active', 'email_verified_at' => now()]);
        return response()->json($user, 201);
    }
    public function show(User $user) { return response()->json($user->load('domains', 'mailboxes')); }
    public function update(Request $request, User $user) {
        $data = $request->only(['name', 'email', 'role', 'status', 'max_domains', 'max_mailboxes', 'max_quota']);
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $user->update($data);
        return response()->json($user);
    }
    public function destroy(User $user) { $user->delete(); return response()->json(null, 204); }
}
