<div class="tab-pane p-4 fade @if ($tab == 'live-class') show active @endif" id="pills-live-class" role="tabpanel"
    aria-labelledby="pills-live-class-tab" tabindex="0">
    <div class="row">
        <div class="col-md-12">
            <h6>{{ get_phrase('Class Schedules') }}:</h6>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <th>#</th>
                        <th>{{ get_phrase('Topic') }}</th>
                        <th>{{ get_phrase('Date & time') }}</th>
                        <th>{{ get_phrase('Action') }}</th>
                    </thead>
                    <tbody>

                        @foreach (App\Models\LiveClass::where('course_id', $course_details->id)->get() as $key => $live_class)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $live_class->class_topic }}
                                </td>
                                <td>{{ date('d M Y - h:i A', strtotime($live_class->class_date_and_time)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
