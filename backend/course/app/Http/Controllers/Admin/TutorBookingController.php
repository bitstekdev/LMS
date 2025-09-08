<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorCategory;
use App\Models\TutorSubject;
use Illuminate\Http\Request;

class TutorBookingController extends Controller
{
    // -------------------- SUBJECTS -------------------- //

    public function subjects()
    {
        $page_data['subjects'] = TutorSubject::orderBy('id', 'asc')->paginate(10);

        return view('admin.tutor_booking.subjects', $page_data);
    }

    public function tutor_subject_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $slug = slugify($request->name);

        if (TutorSubject::where('slug', $slug)->exists()) {
            return redirect()->route('admin.tutor_subjects')
                ->with('error', get_phrase('Subject already exists with this name. Please choose a different name.'));
        }

        TutorSubject::create([
            'name' => $request->name,
            'slug' => $slug,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tutor_subjects')->with('success', get_phrase('Subject added successfully.'));
    }

    public function tutor_subject_update(Request $request, $id)
    {
        $subject = TutorSubject::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $slug = slugify($request->name);

        if (TutorSubject::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            return redirect()->route('admin.tutor_subjects')
                ->with('error', get_phrase('Duplicate subject name. Please choose another.'));
        }

        $subject->update([
            'name' => $request->name,
            'slug' => $slug,
            'status' => 1,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tutor_subjects')->with('success', get_phrase('Subject updated successfully.'));
    }

    public function tutor_subject_status($id, $status)
    {
        $subject = TutorSubject::findOrFail($id);
        $subject->update(['status' => $status === 'active' ? 1 : 0]);

        return redirect()->route('admin.tutor_subjects')->with('success', get_phrase('Subject status updated successfully.'));
    }

    public function tutor_subject_delete($id)
    {
        TutorSubject::findOrFail($id)->delete();

        return redirect()->route('admin.tutor_subjects')->with('success', get_phrase('Subject deleted successfully.'));
    }

    // -------------------- CATEGORIES -------------------- //

    public function tutor_categories()
    {
        $page_data['categories'] = TutorCategory::orderBy('id', 'asc')->paginate(10);

        return view('admin.tutor_booking.categories', $page_data);
    }

    public function tutor_category_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $slug = slugify($request->name);

        if (TutorCategory::where('slug', $slug)->exists()) {
            return redirect()->route('admin.tutor_categories')
                ->with('error', get_phrase('Category name already exists. Please choose another.'));
        }

        TutorCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tutor_categories')->with('success', get_phrase('Category added successfully.'));
    }

    public function tutor_category_update(Request $request, $id)
    {
        $category = TutorCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $slug = slugify($request->name);

        if (TutorCategory::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            return redirect()->route('admin.tutor_categories')
                ->with('error', get_phrase('Duplicate category name. Please choose another.'));
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tutor_categories')->with('success', get_phrase('Category updated successfully.'));
    }

    public function tutor_category_status($id, $status)
    {
        $category = TutorCategory::findOrFail($id);
        $category->update(['status' => $status === 'active' ? 1 : 0]);

        return redirect()->route('admin.tutor_categories')->with('success', get_phrase('Category status updated successfully.'));
    }

    public function tutor_category_delete($id)
    {
        TutorCategory::findOrFail($id)->delete();

        return redirect()->route('admin.tutor_categories')->with('success', get_phrase('Category deleted successfully.'));
    }
}
