<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ get_phrase('Course Playing Page') }} | {{ config('app.name') }}</title>

    <!-- Meta Tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset(get_frontend_settings('favicon')) }}" />

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/course_player/vendors/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/plyr/plyr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/course_player/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/course_player/css/custom.css') }}">

    <!-- FlatIcons -->
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-bold-rounded/css/uicons-bold-rounded.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-bold-straight/css/uicons-bold-straight.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/icons/uicons-regular-rounded/css/uicons-regular-rounded.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/icons/uicons-solid-rounded/css/uicons-solid-rounded.css') }}">

    <!-- Summernote -->
    <link rel="stylesheet" href="{{ asset('assets/global/summernote/summernote.min.css') }}">

    <!-- Tagify -->
    <link rel="stylesheet" href="{{ asset('assets/global/tagify-master/dist/tagify.css') }}">
</head>

<body>

    <!-- Header -->
    <header class="playing-header-section">
        @include('course_player.header')
    </header>

    <!-- Main Content -->
    <section class="video-playlist-section">
        <div class="my-container">
            <div class="row">
                <div class="col-lg-8" id="player_content">
                    @php
                        $locked_ids = get_locked_lesson_ids($course_details->id, auth()->user()->id);
                        $is_locked = in_array($lesson_details->id, $locked_ids);
                        $drip_settings = json_decode($course_details->drip_content_settings, true) ?? [];
                        $locked_message = remove_js(
                            htmlspecialchars_decode($drip_settings['locked_lesson_message'] ?? ''),
                        );
                    @endphp

                    @if ($is_locked && $course_details->enable_drip_content)
                        <div class="py-5 my-5">
                            {!! $locked_message !!}
                        </div>
                    @else
                        @include('course_player.player_page')
                    @endif

                    <!-- Tabs -->
                    <div class="course-video-navtab">
                        @include('course_player.tab_bar')
                    </div>
                </div>

                <div class="col-lg-4" id="player_side_bar">
                    @include('course_player.side_bar')
                </div>
            </div>
        </div>
    </section>

    <!-- JS Dependencies -->
    <script src="{{ asset('assets/frontend/default/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/default/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/summernote/summernote.min.js') }}"></script>
    <script src="{{ asset('assets/global/course_player/vendors/fontawesome/fontawesome.all.min.js') }}"></script>
    <script src="{{ asset('assets/global/plyr/plyr.js') }}"></script>
    <script src="{{ asset('assets/global/tagify-master/dist/tagify.min.js') }}"></script>
    <script src="{{ asset('assets/global/jquery-form/jquery.form.min.js') }}"></script>

    <!-- Notifications -->
    @include('frontend.default.toaster')

    <!-- Custom Scripts -->
    <script src="{{ asset('assets/global/course_player/js/script.js') }}"></script>

    @include('admin.common_scripts')
    @include('course_player.init')
    @stack('js')
</body>

</html>
