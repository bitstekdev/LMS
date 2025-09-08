<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BootcampModule;
use App\Models\BootcampResource;
use App\Services\FileUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BootcampResourceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|numeric|exists:bootcamp_modules,id',
            'upload_type' => 'required|in:resource,record',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $module = BootcampModule::join('bootcamps', 'bootcamp_modules.bootcamp_id', '=', 'bootcamps.id')
            ->select('bootcamp_modules.*', 'bootcamps.title as bootcamp_title')
            ->where('bootcamp_modules.id', $request->module_id)
            ->first();

        if (! $module) {
            Session::flash('error', get_phrase('Module not found.'));

            return redirect()->back();
        }

        $allowedExtensions = ['mp4', 'mov', 'avi', 'wmv', 'webm'];
        $basePath = 'uploads/bootcamp/resource/'.clean_path_segment(auth('web')->user()->name).'/'.clean_path_segment($module->bootcamp_title).'/'.clean_path_segment($module->title);

        DB::beginTransaction();
        try {
            foreach ($request->file('files') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());

                if ($request->upload_type === 'record' && ! in_array($ext, $allowedExtensions)) {
                    throw new \Exception(get_phrase('File must be a valid video type.'));
                }

                $originalName = $file->getClientOriginalName();
                $safeName = replace_url_symbol($originalName);
                $fullPath = "{$basePath}/{$safeName}";

                // Avoid duplicates
                if (BootcampResource::where('title', $safeName)->where('module_id', $module->id)->exists()) {
                    throw new \Exception(get_phrase("File '{$safeName}' already exists."));
                }

                BootcampResource::create([
                    'module_id' => $module->id,
                    'title' => $safeName,
                    'file' => $fullPath,
                    'upload_type' => $request->upload_type,
                ]);

                app(FileUploaderService::class)->upload($file, $fullPath);
            }

            DB::commit();
            Session::flash('success', get_phrase(ucfirst($request->upload_type).' file(s) uploaded successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', $e->getMessage());
        }

        return redirect()->back();
    }

    public function delete($id)
    {
        $resource = BootcampResource::find($id);

        if (! $resource) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $filePath = public_path($resource->file);
        if (file_exists($filePath)) {
            remove_file($filePath);
        }

        $resource->delete();

        Session::flash('success', get_phrase(ucfirst($resource->upload_type).' has been deleted.'));

        return redirect()->back();
    }

    public function download($id)
    {
        $resource = BootcampResource::find($id);

        if (! $resource) {
            Session::flash('error', get_phrase('Data not found.'));

            return redirect()->back();
        }

        $filePath = public_path($resource->file);
        if (! file_exists($filePath)) {
            Session::flash('error', get_phrase('File does not exist.'));

            return redirect()->back();
        }

        return response()->download($filePath);
    }
}
