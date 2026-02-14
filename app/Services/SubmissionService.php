<?php

namespace App\Services;

use App\Jobs\NotifyStatusChangedJob;
use App\Models\Submission;
use App\Models\SubmissionComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function create(array $data, User $user): Submission
    {
        return DB::transaction(function () use ($data, $user) {
            return Submission::create([
                'contest_id' => $data['contest_id'],
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => Submission::STATUS_DRAFT,
            ]);
        });
    }

    public function update(Submission $submission, array $data): Submission
    {
        if (!$submission->canBeEdited()) {
            throw new \Exception('Редактирование невозможно в текущем статусе');
        }

        return DB::transaction(function () use ($submission, $data) {
            $submission->update($data);
            return $submission;
        });
    }

    public function submit(Submission $submission): Submission
    {
        if (!$submission->hasScannedAttachments()) {
            throw new \Exception('Необходимо загрузить минимум один проверенный файл');
        }

        return DB::transaction(function () use ($submission) {
            $oldStatus = $submission->status;
            $submission->update(['status' => Submission::STATUS_SUBMITTED]);
            NotifyStatusChangedJob::dispatch($submission, $oldStatus);
            return $submission;
        });
    }

    public function changeStatus(Submission $submission, string $newStatus): Submission
    {
        $allowedTransitions = Submission::getAllowedStatusTransitions();

        if (!in_array($newStatus, $allowedTransitions[$submission->status] ?? [])) {
            throw new \Exception('Недопустимый переход статуса');
        }

        return DB::transaction(function () use ($submission, $newStatus) {
            $oldStatus = $submission->status;
            $submission->update(['status' => $newStatus]);
            NotifyStatusChangedJob::dispatch($submission, $oldStatus);
            return $submission;
        });
    }

    public function addComment(Submission $submission, User $user, string $body): SubmissionComment
    {
        return DB::transaction(function () use ($submission, $user, $body) {
            return $submission->comments()->create([
                'user_id' => $user->id,
                'body' => $body,
            ]);
        });
    }
}
