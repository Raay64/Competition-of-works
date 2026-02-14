<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $submissions = Submission::all();
        $users = User::all();

        $fileNames = [
            'project.pdf',
            'presentation.pptx',
            'source_code.zip',
            'images.zip',
            'report.docx',
            'diagram.png',
            'screenshot.jpg',
            'documentation.pdf',
            'research_data.zip',
            'poster.png',
        ];

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
        ];

        $statuses = ['pending', 'scanned', 'rejected'];

        foreach ($submissions as $submission) {
            // Каждая работа имеет от 0 до 3 файлов
            $numFiles = rand(0, 3);

            for ($i = 0; $i < $numFiles; $i++) {
                $ext = array_rand(['pdf', 'zip', 'png', 'jpg']);
                $fileName = $fileNames[array_rand($fileNames)];
                $status = $statuses[array_rand($statuses)];
                $size = rand(100000, 5000000); // от 100KB до 5MB

                $attachment = Attachment::create([
                    'submission_id' => $submission->id,
                    'user_id' => $submission->user_id,
                    'original_name' => $fileName,
                    'mime' => $mimeTypes[$ext] ?? 'application/octet-stream',
                    'size' => $size,
                    'storage_key' => 'attachments/' . $submission->id . '/' . uniqid() . '_' . $fileName,
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? 'Файл не соответствует требованиям' : null,
                    'created_at' => $submission->created_at,
                ]);

                // Для отклоненных файлов добавляем случайную причину
                if ($status === 'rejected') {
                    $reasons = [
                        'Файл слишком большой',
                        'Недопустимый формат',
                        'Имя файла содержит недопустимые символы',
                        'Файл поврежден',
                        'Обнаружены потенциально опасные элементы',
                    ];
                    $attachment->rejection_reason = $reasons[array_rand($reasons)];
                    $attachment->save();
                }
            }
        }

        $totalAttachments = Attachment::count();
        $this->command->info("Создано {$totalAttachments} файлов");
    }
}
