<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\UploadAttachmentRequest;
use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\Submission;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * Загрузка файла
     */
    public function upload(UploadAttachmentRequest $request, Submission $submission)
    {
        try {
            $attachment = $this->attachmentService->upload(
                $submission,
                Auth::user(),
                $request->file('file')
            );

            return redirect()->back()->with('success', 'Файл успешно загружен');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка загрузки: ' . $e->getMessage());
        }
    }

    /**
     * Скачивание файла
     */
    public function download(Attachment $attachment)
    {
        try {
            $user = Auth::user();

            // Проверка доступа
            if ($user->isParticipant() && $attachment->submission->user_id !== $user->id) {
                abort(403, 'Доступ запрещен');
            }

            $url = $this->attachmentService->getSignedUrl($attachment);

            // Редирект на временную ссылку S3
            return redirect()->away($url);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Удаление файла
     */
    public function destroy(Attachment $attachment)
    {
        try {
            $submission = $attachment->submission;
            $user = Auth::user();

            // Проверяем права на удаление
            if ($submission->user_id !== $user->id && !$user->isAdmin()) {
                abort(403, 'Доступ запрещен');
            }

            // Проверяем, можно ли редактировать работу
            if (!$submission->canBeEdited()) {
                return redirect()->back()->with('error', 'Нельзя удалить файл в текущем статусе работы');
            }

            $this->attachmentService->delete($attachment);

            return redirect()->back()->with('success', 'Файл успешно удален');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка удаления: ' . $e->getMessage());
        }
    }

    /**
     * Превью файла (для изображений и PDF)
     */
    public function preview(Attachment $attachment)
    {
        try {
            $user = Auth::user();

            // Проверка доступа
            if ($user->isParticipant() && $attachment->submission->user_id !== $user->id) {
                abort(403, 'Доступ запрещен');
            }

            $url = $this->attachmentService->getSignedUrl($attachment);

            // Для изображений и PDF показываем в браузере
            if (strpos($attachment->mime, 'image/') === 0 || $attachment->mime === 'application/pdf') {
                return redirect()->away($url);
            }

            // Для остальных типов - скачивание
            return redirect()->route('attachments.download', $attachment);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
}
