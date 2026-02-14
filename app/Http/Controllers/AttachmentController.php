<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadAttachmentRequest;
use App\Models\Attachment;
use App\Models\Submission;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function upload(UploadAttachmentRequest $request, Submission $submission): JsonResponse
    {
        try {
            $attachment = $this->attachmentService->upload(
                $submission,
                Auth::user(),
                $request->file('file')
            );

            return response()->json($attachment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function download(Attachment $attachment): JsonResponse
    {
        try {
            $url = $this->attachmentService->getSignedUrl($attachment);

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
