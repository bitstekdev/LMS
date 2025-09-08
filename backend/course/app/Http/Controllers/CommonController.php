<?php

namespace App\Http\Controllers;

use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Throwable;

class CommonController extends Controller
{
    /**
     * Get video details from YouTube or Vimeo.
     */
    public function get_video_details(Request $request, string $url = '')
    {
        $url = $url ?: $request->input('url');

        if (empty($url)) {
            return response()->json(['error' => 'No video URL provided'], 400);
        }

        $host = parse_url($url, PHP_URL_HOST);
        $host = str_replace('www.', '', strtolower($host));
        $host = explode('.', $host)[0];

        $vimeo_api_key = get_settings('vimeo_api_key');
        $youtube_api_key = get_settings('youtube_api_key');

        if ($host === 'vimeo') {
            return $this->getVimeoDetails($url, $vimeo_api_key);
        }

        if (in_array($host, ['youtube', 'youtu'])) {
            return $this->getYouTubeDetails($url, $youtube_api_key);
        }

        return response()->json(['error' => 'Unsupported video provider'], 400);
    }

    /**
     * Load and return a rendered view with request data.
     */
    public function rendered_view(Request $request, string $path = '')
    {
        if (! View::exists($path)) {
            abort(404, "View [$path] not found.");
        }

        return view($path, $request->all())->render();
    }

    /**
     * Get Vimeo video details.
     */
    protected function getVimeoDetails(string $url, string $apiKey)
    {
        $video_id = ltrim(parse_url($url, PHP_URL_PATH), '/');

        try {
            $response = Http::withToken($apiKey)->get("https://api.vimeo.com/videos/{$video_id}");

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch Vimeo video'], 500);
            }

            $video = $response->object();

            return [
                'provider' => 'Vimeo',
                'video_id' => $video_id,
                'title' => $video->name ?? '',
                'thumbnail' => $video->pictures->sizes[0]->link ?? '',
                'video' => $video->link ?? '',
                'embed_video' => "https://player.vimeo.com/video/{$video_id}",
                'duration' => gmdate('H:i:s', $video->duration ?? 0),
            ];
        } catch (Throwable $e) {
            return response()->json(['error' => 'Vimeo request failed'], 500);
        }
    }

    /**
     * Get YouTube video details.
     */
    protected function getYouTubeDetails(string $url, string $apiKey)
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $video_id = $match[1] ?? null;

        if (! $video_id) {
            return response()->json(['error' => 'Invalid YouTube URL'], 400);
        }

        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                'part' => 'snippet,contentDetails',
                'id' => $video_id,
                'key' => $apiKey,
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch YouTube video'], 500);
            }

            $video = $response->object()->items[0] ?? null;

            if (! $video) {
                return response()->json(['error' => 'Video not found'], 404);
            }

            $duration = new DateInterval($video->contentDetails->duration);

            return [
                'provider' => 'YouTube',
                'video_id' => $video_id,
                'title' => $video->snippet->title ?? '',
                'thumbnail' => "https://i.ytimg.com/vi/{$video_id}/default.jpg",
                'video' => "https://www.youtube.com/watch?v={$video_id}",
                'embed_video' => "https://www.youtube.com/embed/{$video_id}",
                'duration' => $duration->format('%H:%I:%S'),
            ];
        } catch (Throwable $e) {
            return response()->json(['error' => 'YouTube request failed'], 500);
        }
    }
}
