<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuizSubmission;
use App\Models\Section;
use App\Services\FileUploaderService;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class CurriculumController extends Controller
{
    public function __construct(private FileUploaderService $uploader) {}

    public function store(Request $request)
    {
        $request->validate(['title' => 'required']);

        $sort = Section::where('course_id', $request->course_id)
            ->max('sort') ?? 0;

        Section::create([
            'title' => $request->title,
            'user_id' => auth('web')->id(),
            'course_id' => $request->course_id,
            'sort' => $sort + 1,
        ]);

        Session::flash('success', get_phrase('Section added successfully'));

        return redirect()->back();
    }

    public function update(Request $request)
    {
        Section::where('id', $request->section_id)->update(['title' => $request->up_title]);
        Session::flash('success', get_phrase('Update successfully'));

        return redirect()->back();
    }

    public function delete($id)
    {
        Section::destroy($id);
        Session::flash('success', get_phrase('Deleted successfully'));

        return redirect()->back();
    }

    public function section_sort(Request $request)
    {
        $sections = json_decode($request->itemJSON);
        foreach ($sections as $key => $id) {
            Section::where('id', $id)->update(['sort' => $key + 1]);
        }
        Session::flash('success', get_phrase('Sections sorted successfully'));
    }

    public function lesson_store(Request $request)
    {
        $sort = Lesson::where('course_id', $request->course_id)->max('sort') ?? 0;

        $data = [
            'title' => $request->title,
            'user_id' => auth('web')->id(),
            'course_id' => $request->course_id,
            'section_id' => $request->section_id,
            'sort' => $sort + 1,
            'is_free' => $request->free_lesson,
            'lesson_type' => $request->lesson_type,
            'summary' => $request->summary,
        ];

        // Dynamic content based on lesson type
        match ($request->lesson_type) {
            'text' => $this->handleTextLesson($data, $request),
            'video-url', 'html5', 'vimeo-url', 'google_drive' => $this->handleUrlVideoLesson($data, $request),
            'iframe' => $data['lesson_src'] = $request->iframe_source,
            'document_type', 'image' => $this->handleFileAttachment($data, $request),
            'scorm' => $this->handleScorm($data, $request),
            'system-video' => $this->handleSystemVideo($data, $request),
            default => null,
        };

        Lesson::create($data);
        Session::flash('success', get_phrase('Lesson added successfully'));

        return redirect()->back();
    }

    protected function formatDuration(?string $input): string
    {
        if (empty($input)) {
            return '00:00:00';
        }
        $parts = explode(':', $input);

        return sprintf('%02d:%02d:%02d', $parts[0] ?? 0, $parts[1] ?? 0, $parts[2] ?? 0);
    }

    protected function handleTextLesson(array &$data, Request $request): void
    {
        $data['attachment'] = $request->text_description;
        $data['attachment_type'] = $request->lesson_provider;
    }

    protected function handleUrlVideoLesson(array &$data, Request $request): void
    {
        $data['video_type'] = $request->lesson_provider;
        $data['lesson_src'] = $request->lesson_src;
        $data['duration'] = $this->formatDuration($request->duration);
    }

    protected function handleFileAttachment(array &$data, Request $request): void
    {
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $this->uploader->upload(
                $request->file('attachment'),
                'uploads/lesson_file/attachment'
            );
            $data['attachment_type'] = $request->file('attachment')->getClientOriginalExtension();
        }
    }

    protected function handleScorm(array &$data, Request $request): void
    {
        if (! $request->hasFile('scorm_file')) {
            return;
        }

        $zip = $request->file('scorm_file');
        $fileName = time().'_'.random(4).'.'.$zip->getClientOriginalExtension();
        $uploadPath = public_path('uploads/lesson_file/scorm_content');

        $this->uploader->upload($zip, 'uploads/lesson_file/scorm_content/'.$fileName);

        $zipPath = $uploadPath.'/'.$fileName;
        $extractPath = $uploadPath.'/'.pathinfo($fileName, PATHINFO_FILENAME);

        $archive = new \ZipArchive;
        if ($archive->open($zipPath) === true) {
            $archive->extractTo($extractPath);
            $archive->close();
            File::delete($zipPath);
            $data['attachment'] = pathinfo($fileName, PATHINFO_FILENAME);
            $data['attachment_type'] = $request->scorm_provider;
        } else {
            abort(500, 'Failed to extract SCORM file');
        }
    }

    protected function handleSystemVideo(array &$data, Request $request): void
    {
        if (! $request->hasFile('system_video_file')) {
            return;
        }

        $file = $request->file('system_video_file');
        $fileName = time().'_'.random(4).'.'.$file->getClientOriginalExtension();
        $path = 'uploads/lesson_file/videos/'.$fileName;

        // Apply watermarking if enabled
        if (get_player_settings('watermark_type') === 'ffmpeg') {
            $status = WatermarkService::encode($file, $fileName, public_path('uploads/lesson_file/videos'));
            if (! $status) {
                abort(500, get_phrase('Watermark encoding failed.'));
            }
        }

        $this->uploader->upload($file, 'uploads/lesson_file/videos/'.$fileName);
        $data['lesson_src'] = $path;
        $data['video_type'] = $request->lesson_provider;
        $data['duration'] = $this->formatDuration($request->duration);
    }

    public function lesson_sort(Request $request)
    {
        $lessons = json_decode($request->itemJSON);
        foreach ($lessons as $key => $id) {
            Lesson::where('id', $id)->update(['sort' => $key + 1]);
        }
        Session::flash('success', get_phrase('Lessons sorted successfully'));
    }

    public function lesson_edit(Request $request)
    {
        $lesson = Lesson::findOrFail($request->id);
        $updated = [
            'title' => $request->title,
            'section_id' => $request->section_id,
            'summary' => $request->summary,
        ];

        match ($request->lesson_type) {
            'text' => $updated['attachment'] = $request->text_description,

            'video-url', 'html5', 'vimeo-url', 'google_drive' => function () use (&$updated, $request) {
                $updated['lesson_src'] = $request->lesson_src;
                $updated['duration'] = $this->formatDuration($request->duration);
            },

            'iframe' => $updated['lesson_src'] = $request->iframe_source,

            'document_type', 'image' => function () use (&$updated, $request, $lesson) {
                if ($request->hasFile('attachment')) {
                    remove_file('uploads/lesson_file/attachment/'.$lesson->attachment);
                    $updated['attachment'] = $this->uploader->upload(
                        $request->file('attachment'),
                        'uploads/lesson_file/attachment'
                    );
                    $updated['attachment_type'] = $request->file('attachment')->getClientOriginalExtension();
                }
            },

            'scorm' => function () use (&$updated, $request, $lesson) {
                $existingFolder = $lesson->attachment;

                if ($request->hasFile('scorm_file')) {
                    $this->deleteDir(public_path("uploads/lesson_file/scorm_content/{$existingFolder}"));

                    $fileName = time().'_'.random(4).'.'.$request->file('scorm_file')->getClientOriginalExtension();
                    $this->uploader->upload(
                        $request->file('scorm_file'),
                        'uploads/lesson_file/scorm_content/'.$fileName
                    );

                    $uploadPath = public_path('uploads/lesson_file/scorm_content');
                    $zipPath = $uploadPath.'/'.$fileName;
                    $extractPath = $uploadPath.'/'.pathinfo($fileName, PATHINFO_FILENAME);

                    $zip = new \ZipArchive;
                    if ($zip->open($zipPath) === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();
                        File::delete($zipPath);
                    } else {
                        abort(500, 'Failed to extract SCORM file.');
                    }

                    $updated['attachment'] = pathinfo($fileName, PATHINFO_FILENAME);
                    $updated['attachment_type'] = $request->scorm_provider;
                }
            },

            'system-video' => function () use (&$updated, $request, $lesson) {
                if ($request->hasFile('system_video_file')) {
                    remove_file($lesson->lesson_src);

                    $file = $request->file('system_video_file');
                    $fileName = time().'_'.random(4).'.'.$file->getClientOriginalExtension();
                    $path = 'uploads/lesson_file/videos/'.$fileName;

                    if (get_player_settings('watermark_type') === 'ffmpeg') {
                        if (! WatermarkService::encode($file, $fileName, public_path('uploads/lesson_file/videos'))) {
                            abort(500, get_phrase('Watermark encoding failed.'));
                        }
                    }

                    $this->uploader->upload($file, $path);
                    $updated['lesson_src'] = $path;
                    $updated['video_type'] = $request->lesson_provider;
                    $updated['duration'] = $this->formatDuration($request->duration);
                }
            },

            default => null
        };

        Lesson::where('id', $request->id)->update($updated);

        Session::flash('success', get_phrase('Lesson updated successfully'));

        return redirect()->back();
    }

    public function lesson_delete($id)
    {
        $lesson = Lesson::findOrFail($id);

        // Remove media
        remove_file($lesson->lesson_src);
        remove_file('uploads/lesson_file/attachment/'.$lesson->attachment);

        // If it's a quiz lesson, delete related questions and submissions
        if ($lesson->lesson_type === 'quiz') {
            Question::where('quiz_id', $id)->delete();
            QuizSubmission::where('quiz_id', $id)->delete();
        }

        $lesson->delete();

        Session::flash('success', get_phrase('Deleted successfully'));

        return redirect()->back();
    }

    protected function deleteDir($directoryPath): void
    {
        if (File::exists($directoryPath)) {
            File::deleteDirectory($directoryPath);
        }
    }
}
