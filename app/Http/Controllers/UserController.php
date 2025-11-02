<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user/profile",
     *     summary="Ambil profil user yang sedang login",
     *     description="Mengembalikan data profil user yang sedang login berdasarkan token autentikasi.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data profil user",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Damar"),
     *             @OA\Property(property="email", type="string", example="damar@example.com"),
     *             @OA\Property(property="phone", type="string", example="+628123456789"),
     *             @OA\Property(property="created_at", type="string", format="datetime", example="2025-10-16T09:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/update-profile",
     *     summary="Update profil user",
     *     description="Memperbarui data profil user seperti nama dan nomor telepon.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=100, example="Damar Setiawan"),
     *             @OA\Property(property="phone", type="string", maxLength=15, example="+628123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Damar Setiawan"),
     *             @OA\Property(property="email", type="string", example="damar@example.com"),
     *             @OA\Property(property="phone", type="string", example="+628123456789")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'string|max:100',
            'phone' => 'string|max:15',
        ]);
        $user->update($data);
        return response()->json($user);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/change-password",
     *     summary="Ubah password user",
     *     description="Mengganti password user dengan validasi password lama.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "new_password"},
     *             @OA\Property(property="old_password", type="string", example="oldpass123"),
     *             @OA\Property(property="new_password", type="string", minLength=6, example="newsecurepassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Password lama salah",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect old password")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        if (!Hash::check($request->old_password, $request->user()->password)) {
            return response()->json(['message' => 'Incorrect old password'], 400);
        }

        $request->user()->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json(['message' => 'Password updated']);
    }
}
