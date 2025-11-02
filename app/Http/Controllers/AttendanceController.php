<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/attendance/check-in",
     *     summary="Melakukan check-in ke event",
     *     description="User melakukan check-in dengan QR code untuk event tertentu.",
     *     tags={"Attendance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "event_id", "qr_code_value"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID user yang melakukan check-in"),
     *             @OA\Property(property="event_id", type="integer", example=5, description="ID event yang diikuti"),
     *             @OA\Property(property="qr_code_value", type="string", example="EVT2025-USER01", description="Nilai QR code unik event")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-in berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checked in"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=5),
     *                 @OA\Property(property="check_in_time", type="string", format="datetime", example="2025-10-16T09:00:00Z"),
     *                 @OA\Property(property="qr_code_value", type="string", example="EVT2025-USER01")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required',
            'event_id' => 'required|exists:events,id',
            'qr_code_value' => 'required|string'
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $data['user_id'], 'event_id' => $data['event_id']],
            ['check_in_time' => now(), 'qr_code_value' => $data['qr_code_value']]
        );

        return response()->json(['message' => 'Checked in', 'data' => $attendance]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attendance/check-out",
     *     summary="Melakukan check-out dari event",
     *     description="User melakukan check-out dari event yang sudah diikuti.",
     *     tags={"Attendance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "event_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="event_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Check-out berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checked out")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Data absensi tidak ditemukan")
     * )
     */
    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required',
            'event_id' => 'required|exists:events,id',
        ]);

        $attendance = Attendance::where('user_id', $data['user_id'])
            ->where('event_id', $data['event_id'])
            ->firstOrFail();

        $attendance->check_out_time = now();
        $attendance->save();

        return response()->json(['message' => 'Checked out']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attendance/history",
     *     summary="Melihat riwayat absensi user",
     *     description="Mengambil semua data absensi (check-in & check-out) milik user yang sedang login.",
     *     tags={"Attendance"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar riwayat absensi user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="event_id", type="integer", example=5),
     *                 @OA\Property(property="check_in_time", type="string", format="datetime", example="2025-10-16T09:00:00Z"),
     *                 @OA\Property(property="check_out_time", type="string", format="datetime", example="2025-10-16T17:00:00Z"),
     *                 @OA\Property(property="event", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Laravel Dev Conference 2025")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function history(Request $request)
    {
        $history = $request->user()->attendances()->with('event')->get();
        return response()->json($history);
    }
}
