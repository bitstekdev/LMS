<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\FileUploaderService;
use App\Services\WatermarkService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class LessonController extends Controller
{
    public function store(Request $request)
    {
        $sort = Lesson::where('course_id', $request->course_id)
            ->max('sort') ?? 0;

        if (Lesson::where('course_id', $request->course_id)
            ->where('title', $request->title)
            ->exists()) {
            Session::flash('error', get_phrase('Lesson already exists.'));

            return back();
        }

        $lesson = new Lesson([
            'title' => $request->title,
            'user_id' => auth('web')->id(),
            'course_id' => $request->course_id,
            'section_id' => $request->section_id,
            'is_free' => $request->free_lesson,
            'lesson_type' => $request->lesson_type,
            'summary' => $request->summary,
            'sort' => $sort + 1,
        ]);

        $this->handleLessonType($lesson, $request);
        $lesson->save();

        Session::flash('success', get_phrase('Lesson added successfully.'));

        return back();
    }

    public function update(Request $request)
    {
        $lesson = Lesson::findOrFail($request->id);
        $data = [
            'title' => $request->title,
            'section_id' => $request->section_id,
            'summary' => $request->summary,
        ];

        $this->handleLessonType($lesson, $request, true);

        $lesson->update($lesson->toArray());

        Session::flash('success', get_phrase('Lesson updated successfully.'));

        return back();
    }

    protected function handleLessonType(Lesson &$lesson, Request $request, $isUpdate = false)
    {
        $type = $request->lesson_type;
        $lesson->lesson_type = $type;

        switch ($type) {
            case 'text':
                $lesson->attachment = $request->text_description;
                break;

            case 'video-url':
            case 'html5':
            case 'google_drive':
            case 'vimeo-url':
                $lesson->video_type = $request->lesson_provider;
                $lesson->lesson_src = $request->lesson_src;
                $lesson->duration = $this->formatDuration($request->duration);
                break;

            case 'iframe':
                $lesson->lesson_src = $request->iframe_source;
                break;

            case 'document_type':
            case 'image':
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $path = 'uploads/lesson_file/attachment/';
                    $filename = $this->uploadFile($file, $path);
                    if ($isUpdate) {
                        remove_file($path.$lesson->attachment);
                    }

                    $lesson->attachment = $filename;
                    $lesson->attachment_type = $type === 'image'
                        ? $file->getClientOriginalExtension()
                        : $request->attachment_type;
                }
                break;

            case 'system-video':
                if ($request->hasFile('system_video_file')) {
                    $file = $request->file('system_video_file');
                    $path = 'uploads/lesson_file/videos/';
                    $filename = $this->processSystemVideo($file, $path);
                    if ($isUpdate) {
                        remove_file($lesson->lesson_src);
                    }

                    $lesson->lesson_src = $path.$filename;
                    $lesson->video_type = $request->lesson_provider;
                    $lesson->duration = $this->formatDuration($request->duration);
                }
                break;

            case 'scorm':
                if ($request->hasFile('scorm_file')) {
                    $filename = $this->processScorm($request->file('scorm_file'), $isUpdate ? $lesson->attachment : null);
                    $lesson->attachment = $filename;
                    $lesson->attachment_type = $request->scorm_provider;
                }
                break;
        }
    }

    protected function formatDuration($input)
    {
        if (! $input) {
            return '00:00:00';
        }
        $parts = explode(':', $input);

        return sprintf('%02d:%02d:%02d', $parts[0] ?? 0, $parts[1] ?? 0, $parts[2] ?? 0);
    }

    protected function uploadFile($file, $path)
    {
        $filename = time().random(4).'.'.$file->getClientOriginalExtension();
        $uploadPath = public_path($path);
        if (! File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }
        app(FileUploaderService::class)->upload($file, $path.$filename);

        return $filename;
    }

    protected function processScorm($file, $existingDir = null)
    {
        $filename = time().random(4).'.'.$file->getClientOriginalExtension();
        $basePath = public_path('uploads/lesson_file/scorm_content');
        if (! File::exists($basePath)) {
            File::makeDirectory($basePath, 0777, true, true);
        }

        if ($existingDir) {
            $this->deleteDir("$basePath/$existingDir");
        }

        $file->move($basePath, $filename);

        $zipPath = "$basePath/$filename";
        $extractPath = "$basePath/".pathinfo($filename, PATHINFO_FILENAME);

        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            File::delete($zipPath);

            return pathinfo($filename, PATHINFO_FILENAME);
        }

        throw new Exception('Failed to extract SCORM zip.');
    }

    protected function processSystemVideo($file, $path)
    {
        $filename = time().random(4).'.'.$file->getClientOriginalExtension();
        $fullPath = public_path($path);

        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0777, true, true);
        }

        if (get_player_settings('watermark_type') === 'ffmpeg') {
            $watermark = get_player_settings('watermark_logo');
            if (! file_exists(public_path($watermark))) {
                throw new Exception('Watermark file not found.');
            }

            if (! WatermarkService::encode($file, $filename, $fullPath)) {
                throw new Exception('Watermark processing failed.');
            }
        }

        app(FileUploaderService::class)->upload($file, $path.$filename);

        return $filename;
    }

    public function sort(Request $request)
    {
        $lessons = json_decode($request->itemJSON);
        foreach ($lessons as $i => $lessonId) {
            Lesson::where('id', $lessonId)->update(['sort' => $i + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteDir($path)
    {
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

    public function delete($id)
    {
        $lesson = Lesson::findOrFail($id);

        remove_file($lesson->lesson_src);
        remove_file('uploads/lesson_file/attachment/'.$lesson->attachment);

        $lesson->delete();

        Session::flash('success', get_phrase('Delete successfully'));

        return back();
    }
}
