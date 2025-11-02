<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register user baru",
     *     description="Mendaftarkan akun baru ke sistem.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "nim", "phone", "password"},
     *             @OA\Property(property="name", type="string", maxLength=100, example="Damar Setiawan"),
     *             @OA\Property(property="email", type="string", format="email", example="damar@example.com"),
     *             @OA\Property(property="nim", type="string", example="1201234567"),
     *             @OA\Property(property="phone", type="string", example="+628123456789"),
     *             @OA\Property(property="password", type="string", minLength=6, example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Damar Setiawan"),
     *                 @OA\Property(property="email", type="string", example="damar@example.com"),
     *                 @OA\Property(property="nim", type="string", example="1201234567"),
     *                 @OA\Property(property="phone", type="string", example="+628123456789")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'nim' => 'required|size:10|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|min:6'
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json(['user' => $user], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login user",
     *     description="Login menggunakan email dan password untuk mendapatkan token autentikasi.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="damar@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil, token dikembalikan",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|XyZabcTokenExample123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Email atau password salah",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = $request->user()->createToken('api_token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout user",
     *     description="Menghapus semua token autentikasi user yang sedang login.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
