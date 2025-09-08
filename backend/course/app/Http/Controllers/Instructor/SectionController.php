<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SectionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check for duplicate section title within the same course
        $exists = Section::where('course_id', $request->course_id)
            ->where('title', $request->title)
            ->exists();

        if ($exists) {
            Session::flash('error', get_phrase('Section already exists.'));

            return redirect()->back();
        }

        // Create new section
        Section::create([
            'title' => $request->title,
            'user_id' => auth('web')->id(),
            'course_id' => $request->course_id,
        ]);

        Session::flash('success', get_phrase('Section added successfully'));

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'up_title' => 'required|string|max:255',
        ]);

        $section = Section::find($request->section_id);

        if (
            Section::where('course_id', $section->course_id)
                ->where('title', $request->up_title)
                ->where('id', '!=', $request->section_id)
                ->exists()
        ) {
            Session::flash('error', get_phrase('Section already exists.'));

            return redirect()->back();
        }

        $section->update([
            'title' => $request->up_title,
        ]);

        Session::flash('success', get_phrase('Update successfully'));

        return redirect()->back();
    }

    public function delete($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        Session::flash('success', get_phrase('Delete successfully'));

        return redirect()->back();
    }

    public function sort(Request $request)
    {
        $sections = json_decode($request->itemJSON);

        foreach ($sections as $index => $sectionId) {
            Section::where('id', $sectionId)->update([
                'sort' => $index + 1,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
