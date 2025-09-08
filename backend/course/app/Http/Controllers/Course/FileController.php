<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileController extends Controller
{
    public function get_file(Request $request)
    {
        $user = auth('web')->user();

        if (! isset($request->course_id, $request->lesson_id) || ! $user) {
            abort(403, 'Unauthorized access.');
        }

        $lesson = Lesson::findOrFail($request->lesson_id);
        $lessonType = $lesson->lesson_type;
        $fileUrl = null;

        if (! enroll_status($request->course_id, $user->id) && $user->role !== 'admin' && ! is_course_instructor($request->course_id, $user->id)) {
            abort(403, 'Access denied.');
        }

        if (in_array($lessonType, ['image', 'document_type'])) {
            $fileUrl = 'uploads/lesson_file/attachment/'.$lesson->attachment;
        } elseif ($lessonType === 'system-video') {
            $fileUrl = $lesson->lesson_src;
        }

        // Fix protocol mismatch if any
        if (str_starts_with(url(''), 'https:') && str_starts_with($fileUrl, 'http:')) {
            $fileUrl = str_replace('http:', 'https:', $fileUrl);
        } elseif (str_starts_with(url(''), 'http:') && str_starts_with($fileUrl, 'https:')) {
            $fileUrl = str_replace('https:', 'http:', $fileUrl);
        }

        $fileUrl = str_replace(url(''), '', $fileUrl);
        $fullPath = public_path($fileUrl);
        $basename = basename($fileUrl);

        $contentType = 'application/octet-stream';
        $fileSize = 0;

        if (str_starts_with($fileUrl, 'http')) {
            $headers = get_headers($fileUrl, 1);
            $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? 'application/octet-stream';
            $fileSize = $headers['Content-Length'] ?? $headers['content-length'] ?? 0;
        } elseif (File::exists($fullPath)) {
            $contentType = mime_content_type($fullPath);
            $fileSize = filesize($fullPath);
        } else {
            abort(404, 'File not found.');
        }

        // Serve file based on lesson type
        if (in_array($lessonType, ['image', 'document_type'])) {
            return response()->file($fullPath, [
                'Content-Type' => $contentType,
                'Content-Length' => $fileSize,
            ]);
        } elseif ($lessonType === 'system-video') {
            return $this->streamVideo($fullPath, $contentType, $fileSize, $basename);
        }

        abort(404, 'Invalid lesson type.');
    }

    private function streamVideo($path, $contentType, $fileSize, $filename)
    {
        $chunkSize = min($fileSize, 3 * 1024 * 1024); // Max 3MB
        $start = 0;
        $end = $fileSize - 1;

        if (isset($_SERVER['HTTP_RANGE'])) {
            header('HTTP/1.1 206 Partial Content');
            preg_match('/bytes=(\d+)-?(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
            $start = intval($matches[1]);
            $end = isset($matches[2]) ? intval($matches[2]) : min($start + $chunkSize - 1, $fileSize - 1);
            header("Content-Range: bytes $start-$end/$fileSize");
        }

        header('Accept-Ranges: bytes');
        header("Content-Type: $contentType");
        header("Content-Disposition: inline; filename=\"$filename\"");
        header('Content-Length: '.($end - $start + 1));
        header('Cache-Control: public, max-age=0');
        header('Pragma: public');

        $handle = fopen($path, 'rb');
        fseek($handle, $start);

        while (! feof($handle) && ftell($handle) <= $end) {
            echo fread($handle, 1024 * 1024); // 1MB chunks
            ob_flush();
            flush();
        }

        fclose($handle);
        exit;
    }

    public function get_video_file(Request $request)
    {
        $user = auth('web')->user();

        if (! isset($request->course_id, $request->lesson_id) || ! $user) {
            abort(403, 'Unauthorized');
        }

        $lesson = Lesson::findOrFail($request->lesson_id);

        if (
            $lesson->lesson_type === 'system-video' &&
            (enroll_status($request->course_id, $user->id) || $user->role === 'admin' || is_course_instructor($request->course_id, $user->id))
        ) {
            $filePath = public_path($lesson->lesson_src);
            if (! File::exists($filePath)) {
                abort(404, 'Video not found');
            }

            $stream = fopen($filePath, 'rb');

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type' => mime_content_type($filePath),
                'Content-Length' => filesize($filePath),
                'Content-Disposition' => 'inline; filename="'.basename($filePath).'"',
            ]);
        }

        abort(403, 'Access denied');
    }

    public function pdf_canvas($course_id = '', $lesson_id = '')
    {
        $user = auth('web')->user();
        if (enroll_status($course_id, $user->id) || $user->role === 'admin' || is_course_instructor($course_id, $user->id)) {
            return view('course_player.pdf_canvas', compact('course_id', 'lesson_id'));
        }

        return abort(403, get_phrase('Access denied'));
    }
}
