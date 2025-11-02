<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/events",
     *     summary="Ambil semua event",
     *     description="Mengembalikan daftar seluruh event yang tersedia.",
     *     tags={"Event"},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar event",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Laravel Developer Conference 2025"),
     *                 @OA\Property(property="description", type="string", example="Event tahunan untuk para developer Laravel."),
     *                 @OA\Property(property="location", type="string", example="Jakarta Convention Center"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-11-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2025-11-03")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Event::all();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/{id}",
     *     summary="Lihat detail event",
     *     description="Mengambil detail lengkap dari event berdasarkan ID.",
     *     tags={"Event"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID event yang ingin dilihat",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail event ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Laravel Developer Conference 2025"),
     *             @OA\Property(property="description", type="string", example="Event tahunan untuk para developer Laravel."),
     *             @OA\Property(property="location", type="string", example="Jakarta Convention Center"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-11-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-11-03")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event tidak ditemukan")
     * )
     */
    public function show($id)
    {
        return Event::findOrFail($id);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/events/{id}/register",
     *     summary="Daftar ke event",
     *     description="User yang sedang login melakukan pendaftaran ke event berdasarkan ID.",
     *     tags={"Event"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID event yang ingin didaftarkan",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pendaftaran event berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registered"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=5),
     *                 @OA\Property(property="registration_time", type="string", format="datetime", example="2025-10-16T09:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Event tidak ditemukan")
     * )
     */
    public function register($id, Request $request)
    {
        $event = Event::findOrFail($id);

        $registration = EventRegistration::create([
            'user_id' => $request->user()->id,
            'event_id' => $event->id,
            'registration_time' => now()
        ]);

        return response()->json(['message' => 'Registered', 'data' => $registration]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/events/user-registered",
     *     summary="Daftar event yang diikuti user",
     *     description="Mengambil semua event yang sudah didaftarkan oleh user yang sedang login.",
     *     tags={"Event"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar event user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="event_id", type="integer", example=5),
     *                 @OA\Property(property="registration_time", type="string", format="datetime", example="2025-10-16T09:30:00Z"),
     *                 @OA\Property(property="event", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Laravel Developer Conference 2025"),
     *                     @OA\Property(property="location", type="string", example="Jakarta Convention Center")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function userEvents(Request $request)
    {
        $registrations = $request->user()->registrations()->with('event')->get();
        return response()->json($registrations);
    }
}
