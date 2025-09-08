<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendSetting;
use App\Models\HomePageSetting;
use App\Models\Language;
use App\Models\LanguagePhrase;
use App\Models\NotificationSetting;
use App\Models\PaymentGateway;
use App\Models\PlayerSetting;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserReview;
use App\Services\FileUploaderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function system_settings()
    {
        return view('admin.setting.system_setting');
    }

    public function system_settings_update(Request $request)
    {
        $data = $request->except(['_token']);
        foreach ($data as $key => $item) {
            Setting::where('type', $key)->update(['description' => $item]);
        }
        Session::flash('success', get_phrase('System settings update successfully'));

        return redirect()->back();
    }

    public function website_settings()
    {
        return view('admin.setting.website_setting');
    }

    public function website_settings_update(Request $request)
    {
        $data = $request->except(['_token', 'type']);
        if ($request->type == 'frontend_settings') {
            foreach ($data as $key => $item) {
                FrontendSetting::where('key', $key)->update(['value' => $item]);
            }
            Session::flash('success', get_phrase('Frontend settings update successfully'));
        }
        if ($request->type == 'motivational_speech') {
            $motivations = [];
            $images = [];
            $titles = array_filter($request->input('titles', []));
            foreach ($titles as $key => $title) {
                $motivations[$key]['title'] = $title;
                $motivations[$key]['designation'] = $request->input("designation.$key");
                $motivations[$key]['description'] = $request->input("descriptions.$key");
                if ($request->hasFile("images.$key")) {
                    $motivations[$key]['image'] = app(FileUploaderService::class)->upload($request->file("images.$key"), 'uploads/motivational_speech', 500);
                } else {
                    $motivations[$key]['image'] = $request->input("previous_images.$key");
                }
                $images[$key] = $motivations[$key]['image'];
            }
            $files = glob('uploads/motivational_speech/*');
            foreach ($files as $file) {
                $file_name = basename($file);
                if (! in_array($file_name, $images)) {
                    remove_file($file);
                }
            }
            FrontendSetting::where('key', 'motivational_speech')->update(['value' => json_encode($motivations)]);
            Session::flash('success', get_phrase('Motivational speech update successfully'));
        }
        if ($request->type == 'websitefaqs') {
            $faqs = [];
            $questions = array_filter($request->input('questions', []));
            foreach ($questions as $key => $question) {
                $faqs[$key]['question'] = $question;
                $faqs[$key]['answer'] = $request->input("answers.$key");
            }
            FrontendSetting::where('key', 'website_faqs')->update(['value' => json_encode($faqs)]);
            Session::flash('success', get_phrase('Website Faqs update successfully'));
        }
        if ($request->type == 'contact_info') {
            $contact_information = json_encode($data);
            FrontendSetting::updateOrCreate(['key' => 'contact_info'], ['value' => $contact_information]);
            Session::flash('success', get_phrase('Contact information update successfully'));
        }
        if ($request->type == 'recaptcha_settings') {
            FrontendSetting::where('key', 'recaptcha_status')->update(['value' => $request->input('recaptcha_status')]);
            FrontendSetting::where('key', 'recaptcha_sitekey')->update(['value' => $request->input('recaptcha_sitekey')]);
            FrontendSetting::where('key', 'recaptcha_secretkey')->update(['value' => $request->input('recaptcha_secretkey')]);
            Session::flash('success', get_phrase('Recaptcha setting update successfully'));
        }
        if ($request->type == 'banner_image') {
            if ($request->hasFile('banner_image')) {
                $ext = $request->file('banner_image')->extension();
                $path = 'uploads/banner_image/'.nice_file_name('banner_image', $ext);
                app(FileUploaderService::class)->upload($request->file('banner_image'), $path);
                if (get_frontend_settings('home_page')) {
                    $active_banner = [get_frontend_settings('home_page') => $path];
                    FrontendSetting::where('key', 'banner_image')->update(['value' => json_encode($active_banner)]);
                } else {
                    FrontendSetting::where('key', 'banner_image')->update(['value' => $path]);
                }
                Session::flash('success', get_phrase('Banner image update successfully'));
            }
        }
        if ($request->type == 'light_logo' && $request->hasFile('light_logo')) {
            $path = 'uploads/light_logo/'.nice_file_name('light_logo', $request->file('light_logo')->extension());
            app(FileUploaderService::class)->upload($request->file('light_logo'), $path, 400, null, 200, 200);
            FrontendSetting::where('key', 'light_logo')->update(['value' => $path]);
            Session::flash('success', get_phrase('Light logo update successfully'));
        }
        if ($request->type == 'dark_logo' && $request->hasFile('dark_logo')) {
            $path = 'uploads/dark_logo/'.nice_file_name('dark_logo', $request->file('dark_logo')->extension());
            app(FileUploaderService::class)->upload($request->file('dark_logo'), $path, 400, null, 200, 200);
            FrontendSetting::where('key', 'dark_logo')->update(['value' => $path]);
            Session::flash('success', get_phrase('Dark logo update successfully'));
        }
        if ($request->type == 'small_logo' && $request->hasFile('small_logo')) {
            $path = 'uploads/small_logo/'.nice_file_name('small_logo', $request->file('small_logo')->extension());
            app(FileUploaderService::class)->upload($request->file('small_logo'), $path, 400, null, 200, 200);
            FrontendSetting::where('key', 'small_logo')->update(['value' => $path]);
            Session::flash('success', get_phrase('Small logo update successfully'));
        }
        if ($request->type == 'favicon' && $request->hasFile('favicon')) {
            $path = 'uploads/favicon/'.nice_file_name('favicon', $request->file('favicon')->extension());
            app(FileUploaderService::class)->upload($request->file('favicon'), $path, 400, null, 200, 200);
            FrontendSetting::where('key', 'favicon')->update(['value' => $path]);
            Session::flash('success', get_phrase('Favicon logo update successfully'));
        }

        return redirect()->back();
    }

    public function drip_content_settings()
    {
        return view('admin.setting.drip_content_setting');
    }

    public function drip_content_settings_update(Request $request)
    {
        $alldata = $request->except(['_token']);
        Setting::where('type', 'drip_content_settings')->update(['description' => json_encode($alldata)]);
        Session::flash('success', get_phrase('Drip content settings update successfully'));

        return redirect()->back();
    }

    public function payment_settings()
    {
        return view('admin.setting.payment_setting');
    }

    public function payment_settings_update(Request $request)
    {
        $data = $request->except(['_token', 'top_part', 'identifier']);
        if ($request->top_part == 'top_part') {
            foreach ($data as $key => $item) {
                Setting::where('type', $key)->update(['description' => $item]);
            }
        } else {
            if ($request->identifier == 'paypal') {
                $keys = json_encode([
                    'sandbox_client_id' => $data['sandbox_client_id'] ?? '',
                    'sandbox_secret_key' => $data['sandbox_secret_key'] ?? '',
                    'production_client_id' => $data['production_client_id'] ?? '',
                    'production_secret_key' => $data['production_secret_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'stripe') {
                $keys = json_encode([
                    'public_key' => $data['public_key'] ?? '',
                    'secret_key' => $data['secret_key'] ?? '',
                    'public_live_key' => $data['public_live_key'] ?? '',
                    'secret_live_key' => $data['secret_live_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'razorpay') {
                $keys = json_encode([
                    'public_key' => $data['public_key'] ?? '',
                    'secret_key' => $data['secret_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'flutterwave') {
                $keys = json_encode([
                    'public_key' => $data['public_key'] ?? '',
                    'secret_key' => $data['secret_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'paytm') {
                $keys = json_encode([
                    'paytm_merchant_key' => $data['paytm_merchant_key'] ?? '',
                    'paytm_merchant_mid' => $data['paytm_merchant_mid'] ?? '',
                    'paytm_merchant_website' => $data['paytm_merchant_website'] ?? '',
                    'industry_type_id' => $data['industry_type_id'] ?? '',
                    'channel_id' => $data['channel_id'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'offline') {
                $keys = json_encode([
                    'bank_information' => $data['bank_information'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'paystack') {
                $keys = json_encode([
                    'secret_test_key' => $data['secret_test_key'] ?? '',
                    'public_test_key' => $data['public_test_key'] ?? '',
                    'secret_live_key' => $data['secret_live_key'] ?? '',
                    'public_live_key' => $data['public_live_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'ccavenue') {
                $keys = json_encode([
                    'ccavenue_merchant_id' => $data['ccavenue_merchant_id'] ?? '',
                    'ccavenue_working_key' => $data['ccavenue_working_key'] ?? '',
                    'ccavenue_access_code' => $data['ccavenue_access_code'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'pagseguro') {
                $keys = json_encode([
                    'api_key' => $data['api_key'] ?? '',
                    'secret_key' => $data['secret_key'] ?? '',
                    'other_parameter' => $data['other_parameter'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'iyzico') {
                $keys = json_encode([
                    'api_test_key' => $data['api_test_key'] ?? '',
                    'secret_test_key' => $data['secret_test_key'] ?? '',
                    'api_live_key' => $data['api_live_key'] ?? '',
                    'secret_live_key' => $data['secret_live_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'xendit') {
                $keys = json_encode([
                    'api_key' => $data['api_key'] ?? '',
                    'secret_key' => $data['secret_key'] ?? '',
                    'other_parameter' => $data['other_parameter'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'payu') {
                $keys = json_encode([
                    'pos_id' => $data['pos_id'] ?? '',
                    'second_key' => $data['second_key'] ?? '',
                    'client_id' => $data['client_id'] ?? '',
                    'client_secret' => $data['client_secret'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'skrill') {
                $keys = json_encode([
                    'skrill_merchant_email' => $data['skrill_merchant_email'] ?? '',
                    'secret_passphrase' => $data['secret_passphrase'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'doku') {
                $keys = json_encode([
                    'client_id' => $data['client_id'] ?? '',
                    'shared_key' => $data['shared_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'maxicash') {
                $keys = json_encode([
                    'merchant_id' => $data['merchant_id'] ?? '',
                    'merchant_password' => $data['merchant_password'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'cashfree') {
                $keys = json_encode([
                    'client_id' => $data['client_id'] ?? '',
                    'client_secret' => $data['client_secret'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'aamarpay') {
                $keys = json_encode([
                    'store_id' => $data['store_id'] ?? '',
                    'signature_key' => $data['signature_key'] ?? '',
                    'store_live_id' => $data['store_live_id'] ?? '',
                    'signature_live_key' => $data['signature_live_key'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'tazapay') {
                $keys = json_encode([
                    'public_key' => $data['public_key'] ?? '',
                    'api_key' => $data['api_key'] ?? '',
                    'api_secret' => $data['api_secret'] ?? '',
                ]);
                $data = ['keys' => $keys];
            } elseif ($request->identifier == 'sslcommerz') {
                $keys = json_encode([
                    'store_key' => $data['store_key'] ?? '',
                    'store_password' => $data['store_password'] ?? '',
                    'store_live_key' => $data['store_live_key'] ?? '',
                    'store_live_password' => $data['store_live_password'] ?? '',
                    'sslcz_testmode' => $data['sslcz_testmode'] ?? '',
                    'is_localhost' => $data['is_localhost'] ?? '',
                    'sslcz_live_testmode' => $data['sslcz_live_testmode'] ?? '',
                    'is_live_localhost' => $data['is_live_localhost'] ?? '',
                ]);
                $data = ['keys' => $keys];
            }
            PaymentGateway::where('identifier', $request->identifier)->update($data);
        }
        Session::flash('success', get_phrase('Payment settings update successfully'));

        return redirect(route('admin.payment.settings', ['tab' => $request->identifier]));
    }

    public function language_import(Request $request)
    {
        $request->validate([
            'language_file' => 'required|mimetypes:application/json,text/plain|max:2048',
            'language_id' => 'required|exists:languages,id',
        ]);
        $languageFile = $request->file('language_file');
        if (! $languageFile->isValid()) {
            return redirect()->back()->with('error', get_phrase('Uploaded file is not valid.'));
        }
        $content = file_get_contents($languageFile->getPathname());
        $languageData = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($languageData)) {
            return redirect()->back()->with('error', get_phrase('Invalid JSON format.'));
        }
        $language = Language::find($request->input('language_id'));
        if (! $language) {
            return redirect()->back()->with('error', get_phrase('Language not found.'));
        }
        foreach ($languageData as $phrase => $translated) {
            if (! is_string($phrase) || ! is_string($translated)) {
                continue;
            }
            $lp = LanguagePhrase::firstOrCreate([
                'language_id' => $language->id,
                'phrase' => trim($phrase),
            ]);
            $lp->translated = trim($translated);
            $lp->save();
        }

        return redirect()->back()->with('success', get_phrase('Language imported and updated successfully.'));
    }

    public function language_export($id)
    {
        $language = Language::find($id);
        if (! $language) {
            return redirect()->back()->with('error', get_phrase('Language not found'));
        }
        $phrases = LanguagePhrase::where('language_id', $language->id)->get();
        $data = [];
        foreach ($phrases as $phrase) {
            $data[$phrase->phrase] = $phrase->translated;
        }
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($jsonData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="'.$language->name.'.json"');
    }

    public function manage_language()
    {
        return view('admin.setting.language_setting');
    }

    public function language_direction_update(Request $request)
    {
        $request->validate([
            'language_id' => 'required|exists:languages,id',
            'direction' => 'required|in:ltr,rtl',
        ]);
        Language::where('id', $request->language_id)->update(['direction' => $request->direction]);

        return response()->json(['status' => 'success', 'message' => get_phrase('Language direction updated successfully')]);
    }

    public function edit_phrase($lan_id)
    {
        $page_data['phrases'] = LanguagePhrase::where('language_id', $lan_id)->get();
        $page_data['language'] = Language::where('id', $lan_id)->first();

        return view('admin.setting.edit_phrase', $page_data);
    }

    public function update_phrase(Request $request, $phrase_id)
    {
        $request->validate([
            'translated_phrase' => 'required|string',
        ]);
        LanguagePhrase::where('id', $phrase_id)->update([
            'translated' => trim($request->translated_phrase),
            'updated_at' => now(),
        ]);
    }

    public function phrase_import($lan_id)
    {
        $english = Language::whereRaw('LOWER(name) = ?', ['english'])->first();
        if (! $english) {
            return redirect()->back()->with('error', get_phrase('English base language not found.'));
        }
        $phrases = LanguagePhrase::where('language_id', $english->id)->get();
        foreach ($phrases as $phrase) {
            LanguagePhrase::firstOrCreate(
                ['language_id' => $lan_id, 'phrase' => $phrase->phrase],
                ['translated' => $phrase->translated, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        Session::flash('success', get_phrase('Phrases imported successfully.'));

        return redirect(route('admin.language.phrase.edit', ['lan_id' => $lan_id]));
    }

    public function language_store(Request $request)
    {
        $request->validate(['language' => 'required|string|max:100']);
        $languageName = trim($request->language);
        $exists = Language::whereRaw('LOWER(name) = ?', [strtolower($languageName)])->exists();
        if ($exists) {
            Session::flash('error', get_phrase('Language already exists'));

            return redirect()->back();
        }
        $newLanguage = Language::create(['name' => $languageName, 'direction' => 'ltr']);
        $english = Language::whereRaw('LOWER(name) = ?', ['english'])->first();
        if ($english) {
            $phrases = LanguagePhrase::where('language_id', $english->id)->get();
            foreach ($phrases as $phrase) {
                LanguagePhrase::create([
                    'language_id' => $newLanguage->id,
                    'phrase' => $phrase->phrase,
                    'translated' => $phrase->translated,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        Session::flash('success', get_phrase('Language added successfully'));

        return redirect()->back();
    }

    public function language_delete($id)
    {
        Language::where('id', $id)->delete();
        LanguagePhrase::where('language_id', $id)->delete();
        Session::flash('success', get_phrase('Language deleted successfully'));

        return redirect()->back();
    }

    public function notification_settings()
    {
        return view('admin.setting.notification_setting');
    }

    public function notification_settings_store(Request $request, $param1 = '', $id = '')
    {
        $data = $request->all();
        if ($param1 == 'smtp_settings') {
            $payload = $request->except(['_token']);
            foreach ($payload as $key => $item) {
                Setting::where('type', $key)->update(['description' => $item]);
            }
            Session::flash('success', get_phrase('SMTP setting update successfully'));
        }
        if ($param1 == 'edit_email_template') {
            $payload = $request->except(['_token', 'files']);
            $payload['subject'] = json_encode($request->subject);
            $payload['template'] = json_encode($request->template);
            NotificationSetting::where('id', $id)->update($payload);
            Session::flash('success', get_phrase('Email template update successfully'));
        }
        if ($param1 == 'notification_enable_disable') {
            $nid = $request->id;
            $user_type = $request->user_types;
            $notification_type = $request->notification_type;
            $input_val = $request->input_val;
            $row = NotificationSetting::where('id', $nid)->first();
            if ($row) {
                if ($notification_type == 'system') {
                    $arr = json_decode($row->system_notification, true);
                    $arr[$user_type] = $input_val;
                    $data['system_notification'] = json_encode($arr);
                }
                if ($notification_type == 'email') {
                    $arr = json_decode($row->email_notification, true);
                    $arr[$user_type] = $input_val;
                    $data['email_notification'] = json_encode($arr);
                }
                if ($row->is_editable == 1) {
                    unset($data['notification_type'], $data['input_val'], $data['user_types']);
                    NotificationSetting::where('id', $nid)->update($data);
                }
            }
            $msg = $input_val == 1 ? 'Successfully enabled' : 'Successfully disabled';
        }
        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'msg' => $msg ?? get_phrase('Updated')]);
        } else {
            return redirect()->back();
        }
    }

    public function curl_request($code = '')
    {
        $purchase_code = $code;
        $personal_token = 'FkA9UyDiQT0YiKwYLK3ghyFNRVV9SeUn';
        $bearer = 'bearer '.$personal_token;
        $header = [
            'Content-length: 0',
            'Content-type: application/json; charset=utf-8',
            'Authorization: '.$bearer,
        ];
        $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:'.$purchase_code.'.json';
        $ch_verify = curl_init($verify_url.'?code='.$purchase_code);
        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);
        $response = json_decode($cinit_verify_data, true);
        if (is_array($response) && isset($response['verify-purchase']) && count($response['verify-purchase']) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function api_configurations()
    {
        return view('admin.api_configuration.index');
    }

    public function api_configuration_update(Request $request, $type = '')
    {
        if (Setting::where('type', $type)->exists()) {
            Setting::where('type', $type)->update(['description' => $request->$type]);
            Session::flash('success', get_phrase('API updated successfully'));
        } else {
            Setting::insert(['type' => $type, 'description' => $request->$type]);
            Session::flash('success', get_phrase('API added successfully'));
        }

        return redirect()->back();
    }

    public function certificate()
    {
        return view('admin.certificate.index');
    }

    public function certificate_update_template(Request $request)
    {
        $request->validate(['certificate_template' => 'required|image']);
        $row = Setting::where('type', 'certificate_template');
        if ($row->count() > 0) {
            remove_file(get_settings('certificate_template'));
            $path = app(FileUploaderService::class)->upload($request->certificate_template, 'uploads/certificate-template', 1000);
            Setting::where('type', 'certificate_template')->update(['description' => $path]);
        } else {
            $path = app(FileUploaderService::class)->upload($request->certificate_template, 'uploads/certificate-template', 1000);
            Setting::insert(['type' => 'certificate_template', 'description' => $path]);
        }
        $certificate_builder_content = get_settings('certificate_builder_content');
        if ($certificate_builder_content) {
            $modifiedHtml = preg_replace('/(<img[^>]+src=")([^"]+)(")/', '$1'.get_image($path).'$3', $certificate_builder_content);
            Setting::where('type', 'certificate_builder_content')->update(['description' => $modifiedHtml]);
        }

        return redirect(route('admin.certificate.settings'))->with('success', get_phrase('Certificate template has been updated'));
    }

    public function certificate_builder()
    {
        return view('admin.certificate.builder');
    }

    public function certificate_builder_update(Request $request)
    {
        $request->validate(['certificate_builder_content' => 'required']);
        $row = Setting::where('type', 'certificate_builder_content');
        if ($row->count() > 0) {
            Setting::where('type', 'certificate_builder_content')->update(['description' => $request->certificate_builder_content]);
        } else {
            Setting::insert(['type' => 'certificate_builder_content', 'description' => $request->certificate_builder_content]);
        }
        Session::flash('success', get_phrase('Certificate builder template has been updated'));

        return route('admin.certificate.settings');
    }

    public function user_review_add()
    {
        $page_data['userList'] = User::where('role', 'student')->get();

        return view('admin.setting.user_review_create', $page_data);
    }

    public function user_review_stor(Request $request)
    {
        $data = $request->all();
        $reviewAdd = new UserReview;
        $reviewAdd['user_id'] = $data['user_id'];
        $reviewAdd['rating'] = $data['rating'];
        $reviewAdd['review'] = $data['review'];
        $reviewAdd->save();
        Session::flash('success', get_phrase('Review added successfull'));

        return redirect()->back();
    }

    public function review_edit($id)
    {
        $page_data['review_data'] = UserReview::find($id);
        $page_data['userList'] = User::where('role', 'student')->get();

        return view('admin.setting.user_review_edit', $page_data);
    }

    public function review_update(Request $request, $id)
    {
        $data = $request->except(['_token']);
        UserReview::where('id', $id)->update($data);
        Session::flash('success', get_phrase('Review Update successfully'));

        return redirect()->route('admin.website.settings');
    }

    public function review_delete($id)
    {
        UserReview::where('id', $id)->delete();
        Session::flash('success', get_phrase('Review delete successfully'));

        return redirect()->back();
    }

    public function update_home(Request $request, $id)
    {
        $home_page = $request->type;
        if ($home_page == 'cooking') {
            $speech = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'video_url' => $request->input('video_url'),
                'image' => $request->input('previous_image'),
            ];
            if ($request->hasFile('image')) {
                $image_name = $request->file('image')->getClientOriginalName();
                app(FileUploaderService::class)->upload($request->file('image'), 'uploads/home_page_image/cooking/'.$image_name);
                $speech['image'] = $image_name;
                $previous_image = $request->input('previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/cooking/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            }
        } elseif ($home_page == 'university') {
            $homePageSetting = HomePageSetting::where('home_page_id', $id)->first();
            $storImage = $homePageSetting ? json_decode($homePageSetting->value, true) : [];
            if ($request->hasFile('image')) {
                $image_name = uniqid().'.'.$request->file('image')->getClientOriginalExtension();
                app(FileUploaderService::class)->upload($request->file('image'), 'uploads/home_page_image/university/'.$image_name);
                $speech['image'] = $image_name;
                $previous_image = $request->input('previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/university/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            } elseif (! array_key_exists('image', $storImage ?? [])) {
                $speech['image'] = 0;
            } else {
                $speech['image'] = $storImage['image'];
            }
            if ($request->hasFile('faq_image')) {
                $image_name = uniqid().'.'.$request->file('faq_image')->getClientOriginalExtension();
                app(FileUploaderService::class)->upload($request->file('faq_image'), 'uploads/home_page_image/university/'.$image_name);
                $speech['faq_image'] = $image_name;
                $previous_images = $request->input('previous_faq_image');
                if (! empty($previous_images)) {
                    $previous_image_path = public_path('uploads/home_page_image/university/').$previous_images;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            } elseif (! array_key_exists('faq_image', $storImage ?? [])) {
                $speech['faq_image'] = 0;
            } else {
                $speech['faq_image'] = $storImage['faq_image'];
            }
            $slider_items = [];
            $previous_slider_items = $request->input('previous_slider_items', []);
            if ($previous_slider_items && is_array($previous_slider_items)) {
                foreach ($previous_slider_items as $key => $previous_slider_item) {
                    if ($previous_slider_item == 'no') {
                        if ($request->hasFile("slider_items.$key")) {
                            $file_path = app(FileUploaderService::class)->upload($request->file("slider_items.$key"), 'uploads/home_page_image/university', 1500);
                            if ($file_path) {
                                $slider_items[] = $file_path;
                            }
                        } else {
                            if ($request->input("slider_items.$key")) {
                                $slider_items[] = $request->input("slider_items.$key");
                            }
                        }
                    } else {
                        if ($request->hasFile("slider_items.$key")) {
                            remove_file($previous_slider_item);
                            $file_path = app(FileUploaderService::class)->upload($request->file("slider_items.$key"), 'uploads/home_page_image/university', 1500);
                            if ($file_path) {
                                $slider_items[] = $file_path;
                            }
                        } else {
                            $slider_items[] = $previous_slider_item;
                        }
                    }
                }
            }
            $speech['slider_items'] = json_encode($slider_items);
        } elseif ($home_page == 'development') {
            $speech = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'video_url' => $request->input('video_url'),
                'image' => $request->input('previous_image'),
            ];
            if ($request->hasFile('image')) {
                $image_name = uniqid().'.'.$request->file('image')->getClientOriginalName();
                app(FileUploaderService::class)->upload($request->file('image'), 'uploads/home_page_image/development/'.$image_name);
                $speech['image'] = $image_name;
                $previous_image = $request->input('previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/development/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            }
        } elseif ($home_page == 'kindergarden') {
            $speech = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'image' => $request->input('previous_image'),
            ];
            if ($request->hasFile('image')) {
                $image_name = uniqid().'.'.$request->file('image')->getClientOriginalName();
                app(FileUploaderService::class)->upload($request->file('image'), 'uploads/home_page_image/kindergarden/'.$image_name);
                $speech['image'] = $image_name;
                $previous_image = $request->input('previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/kindergarden/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            }
        } elseif ($home_page == 'marketplace') {
            $instructor = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'video_url' => $request->input('video_url'),
                'image' => $request->input('previous_image'),
            ];
            if ($request->hasFile('image')) {
                $image_name = uniqid().'.'.$request->file('image')->getClientOriginalName();
                app(FileUploaderService::class)->upload($request->file('image'), 'uploads/home_page_image/marketplace/'.$image_name);
                $instructor['image'] = $image_name;
                $previous_image = $request->input('previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/marketplace/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            }
            $marketplace_banner = [];
            foreach ($request->input('slider', []) as $slider) {
                $banner_title_field = 'banner_title'.$slider;
                $banner_description_field = 'banner_description'.$slider;
                $datas['banner_title'] = $request->input($banner_title_field);
                $datas['banner_description'] = $request->input($banner_description_field);
                $marketplace_banner[] = $datas;
            }
            $speech['instructor'] = $instructor;
            $speech['slider'] = $marketplace_banner;
        } elseif ($home_page == 'meditation') {
            if ($request->hasFile('big_image')) {
                $image_name = uniqid().'.'.$request->file('big_image')->getClientOriginalExtension();
                app(FileUploaderService::class)->upload($request->file('big_image'), 'uploads/home_page_image/meditation/'.$image_name);
                $speech['big_image'] = $image_name;
                $previous_image = $request->input('big_previous_image');
                if (! empty($previous_image)) {
                    $previous_image_path = public_path('uploads/home_page_image/meditation/').$previous_image;
                    if (file_exists($previous_image_path)) {
                        remove_file($previous_image_path);
                    }
                }
            } else {
                $homePageSetting = HomePageSetting::where('home_page_id', $id)->first();
                $storImage = $homePageSetting ? json_decode($homePageSetting->value, true) : [];
                if (! array_key_exists('big_image', $storImage ?? [])) {
                    $speech['big_image'] = 0;
                } else {
                    $speech['big_image'] = $storImage['big_image'] ?? $request->input('big_previous_image');
                }
            }
            $meditation_array = [];
            foreach ($request->input('meditation', []) as $meditation) {
                $title_key = 'banner_title'.$meditation;
                $img_key = 'image'.$meditation;
                $old_img_key = 'old_image'.$meditation;
                $image_name = $request->input($old_img_key);
                if ($request->hasFile($img_key)) {
                    $file = $request->file($img_key);
                    $image_name = uniqid().'.'.$file->getClientOriginalName();
                    app(FileUploaderService::class)->upload($file, 'uploads/home_page_image/meditation/'.$image_name);
                    $old_image = $request->input('old_image');
                    $previous_path = public_path('uploads/home_page_image/meditation/').$old_image;
                    if (file_exists($previous_path)) {
                        remove_file($previous_path);
                    }
                }
                $desc_key = 'banner_description'.$meditation;
                $stor['banner_title'] = $request->input($title_key);
                $stor['image'] = $image_name;
                $stor['banner_description'] = $request->input($desc_key);
                $meditation_array[] = $stor;
            }
            $speech['meditation'] = $meditation_array;
        }
        $payload['home_page_id'] = $id;
        $payload['key'] = $home_page;
        $payload['value'] = json_encode($speech ?? []);
        $homePageSetting = HomePageSetting::where('key', $home_page);
        if ($homePageSetting->first()) {
            $homePageSetting->update($payload);
        } else {
            $payload['created_at'] = Carbon::now();
            $payload['updated_at'] = Carbon::now();
            $homePageSetting->insert($payload);
        }
        Session::flash('success', get_phrase('Homepage updated successfully'));

        return redirect()->back();
    }

    public function player_settings()
    {
        return view('admin.setting.player_settings');
    }

    public function player_settings_update(Request $request)
    {
        if ($request->type == 'watermark') {
            $watermark = [
                'watermark_width' => $request->watermark_width,
                'watermark_height' => $request->watermark_height,
                'watermark_top' => $request->watermark_top,
                'watermark_left' => $request->watermark_left,
                'watermark_opacity' => $request->watermark_opacity,
                'watermark_type' => $request->watermark_type,
                'watermark_logo' => $request->watermark_logo,
                'animation_speed' => $request->animation_speed,
            ];
            $validator = Validator::make($watermark, [
                'watermark_width' => 'required|numeric',
                'watermark_height' => 'required|numeric',
                'watermark_top' => 'required|numeric',
                'watermark_left' => 'required|numeric',
                'watermark_opacity' => 'required|integer|min:0|max:100',
                'watermark_type' => 'required|in:js,ffmpeg',
                'animation_speed' => 'required|numeric',
            ]);
            $validator->sometimes('watermark_logo', 'file|mimes:png,jpg,gif', function ($input) {
                return isset($input->watermark_logo);
            });
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            unset($watermark['watermark_logo']);
            if ($request->hasFile('watermark_logo')) {
                $logoPath = 'uploads/watermark/'.nice_file_name('watermark', $request->file('watermark_logo')->extension());
                app(FileUploaderService::class)->upload($request->file('watermark_logo'), $logoPath);
                $watermark['watermark_logo'] = $logoPath;
            }
            foreach ($watermark as $key => $val) {
                if (! PlayerSetting::where('title', $key)->exists()) {
                    PlayerSetting::insert(['title' => $key, 'description' => $val]);
                } else {
                    PlayerSetting::where('title', $key)->update(['description' => $val]);
                }
            }
        }
        Session::flash('success', get_phrase('Your changes has been saved.'));

        return redirect()->route('admin.player.settings');
    }
}
