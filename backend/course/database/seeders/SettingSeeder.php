<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['type' => 'language', 'description' => 'english'],
            ['type' => 'system_name', 'description' => 'Valentine Hurst'],
            ['type' => 'system_title', 'description' => 'Learning Management System'],
            ['type' => 'system_email', 'description' => 'academy@example.com'],
            ['type' => 'address', 'description' => 'Sydney, Australia'],
            ['type' => 'phone', 'description' => '+143-52-9933631'],
            ['type' => 'purchase_code', 'description' => 'haiuhsai'],

            ['type' => 'paypal', 'description' => '[{"active":"1","mode":"sandbox","sandbox_client_id":"AfGaziKslex-scLAyYdDYXNFaz2aL5qGau-SbDgE_D2E80D3AFauLagP8e0kCq9au7W4IasmFbirUUYc","sandbox_secret_key":"EMa5pCTuOpmHkhHaCGibGhVUcKg0yt5-C3CzJw-OWJCzaXXzTlyD17SICob_BkfM_0Nlk7TWnN42cbGz","production_client_id":"1234","production_secret_key":"12345"}]'],
            ['type' => 'stripe_keys', 'description' => '[{"active":"1","testmode":"on","public_key":"pk_test_CAC3cB1mhgkJqXtypYBTGb4f","secret_key":"sk_test_iatnshcHhQVRXdygXw3L2Pp2","public_live_key":"pk_live_xxxxxxxxxxxxxxxxxxxxxxxx","secret_live_key":"sk_live_xxxxxxxxxxxxxxxxxxxxxxxx"}]'],

            ['type' => 'youtube_api_key', 'description' => 'youtube-and-google-drive-api-key'],
            ['type' => 'vimeo_api_key', 'description' => 'vimeo-api-key'],
            ['type' => 'slogan', 'description' => 'A course based video CMS'],
            ['type' => 'text_align', 'description' => null],
            ['type' => 'allow_instructor', 'description' => '1'],
            ['type' => 'instructor_revenue', 'description' => '70'],
            ['type' => 'system_currency', 'description' => 'INR'],
            ['type' => 'paypal_currency', 'description' => 'USD'],
            ['type' => 'stripe_currency', 'description' => 'USD'],
            ['type' => 'author', 'description' => 'Creativeitem'],
            ['type' => 'currency_position', 'description' => 'right-space'],
            ['type' => 'website_description', 'description' => 'Talemy is your ideal education the WordPress theme for sharing and selling your knowledge online. Teach what you love. Talemy gives you the tools.'],
            ['type' => 'website_keywords', 'description' => 'LMS,Learning Management System,Creativeitem,Academy LMS'],
            ['type' => 'footer_text', 'description' => 'LMS'],
            ['type' => 'footer_link', 'description' => 'https://lms.com/'],
            ['type' => 'protocol', 'description' => 'smtp'],
            ['type' => 'smtp_host', 'description' => 'smtp.gmail.com'],
            ['type' => 'smtp_port', 'description' => '465'],
            ['type' => 'smtp_user', 'description' => 'your-email-address'],
            ['type' => 'smtp_pass', 'description' => 'enter-your-smtp-password'],
            ['type' => 'version', 'description' => '1.6'],
            ['type' => 'student_email_verification', 'description' => '0'],
            ['type' => 'instructor_application_note', 'description' => 'Fill all the fields carefully and share if you want to share any document with us it will help us to evaluate you as an instructor. dfdfs'],

            ['type' => 'razorpay_keys', 'description' => '[{"active":"1","key":"rzp_test_J60bqBOi1z1aF5","secret_key":"uk935K7p4j96UCJgHK8kAU4q","theme_color":"#c7a600"}]'],
            ['type' => 'razorpay_currency', 'description' => 'USD'],

            ['type' => 'fb_app_id', 'description' => 'fb-app-id'],
            ['type' => 'fb_app_secret', 'description' => 'fb-app-secret'],
            ['type' => 'fb_social_login', 'description' => '0'],

            ['type' => 'drip_content_settings', 'description' => '{"lesson_completion_role":"duration","minimum_duration":"15:30:00","minimum_percentage":"60","locked_lesson_message":"<h3 xss=\\"removed\\" style=\\"text-align: center; \\"><span xss=\\"removed\\" style=\\"\\">Permission denied!<\/span><\/h3><p xss=\\"removed\\" style=\\"text-align: center; \\"><span xss=\\"removed\\">This course supports drip content, so you must complete the previous lessons.<\/span><\/p>","files":null}'],

            ['type' => 'course_accessibility', 'description' => 'publicly'],
            ['type' => 'smtp_crypto', 'description' => 'ssl'],
            ['type' => 'academy_cloud_access_token', 'description' => 'jdfghasdfasdfasdfasdfasdf'],
            ['type' => 'course_selling_tax', 'description' => '0'],

            ['type' => 'ccavenue_keys', 'description' => '[{"active":"1","ccavenue_merchant_id":"cmi_xxxxxx","ccavenue_working_key":"cwk_xxxxxxxxxxxx","ccavenue_access_code":"ccc_xxxxxxxxxxxxx"}]'],
            ['type' => 'ccavenue_currency', 'description' => 'INR'],

            ['type' => 'iyzico_keys', 'description' => '[{"active":"1","testmode":"on","iyzico_currency":"TRY","api_test_key":"atk_xxxxxxxx","secret_test_key":"stk_xxxxxxxx","api_live_key":"alk_xxxxxxxx","secret_live_key":"slk_xxxxxxxx"}]'],
            ['type' => 'iyzico_currency', 'description' => 'TRY'],

            ['type' => 'paystack_keys', 'description' => '[{"active":"1","testmode":"on","secret_test_key":"sk_test_c746060e693dd50c6f397dffc6c3b2f655217c94","public_test_key":"pk_test_0816abbed3c339b8473ff22f970c7da1c78cbe1b","secret_live_key":"sk_live_xxxxxxxxxxxxxxxxxxxxx","public_live_key":"pk_live_xxxxxxxxxxxxxxxxxxxxx"}]'],
            ['type' => 'paystack_currency', 'description' => 'NGN'],

            ['type' => 'paytm_keys', 'description' => '[{"PAYTM_MERCHANT_KEY":"PAYTM_MERCHANT_KEY","PAYTM_MERCHANT_MID":"PAYTM_MERCHANT_MID","PAYTM_MERCHANT_WEBSITE":"DEFAULT","INDUSTRY_TYPE_ID":"Retail","CHANNEL_ID":"WEB"}]'],

            ['type' => 'google_analytics_id', 'description' => null],
            ['type' => 'meta_pixel_id', 'description' => null],
            ['type' => 'smtp_from_email', 'description' => 'your-email-address'],
            ['type' => 'language_dirs', 'description' => '{"english":"ltr","hindi":"rtl","arabic":"rtl"}'],
            ['type' => 'certificate_template', 'description' => 'uploads/certificate-template/certificate-default.png'],

            // Big multi-line HTML kept intact with NOWDOC for readability
            ['type' => 'certificate_builder_content', 'description' => <<<'HTML'
<!-- Certificate template (cleaned) -->
<style>
  /* Load fonts once */
  @import url('https://fonts.googleapis.com/css2?family=Italianno&display=swap');
  @import url('https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap');
  @import url('https://fonts.googleapis.com/css2?family=Miss+Fajardose&display=swap');

  .certificate-layout-module { position: relative; width: 1069.2px; height: 755.055px; }
  .certificate-template { width: 100%; height: 100%; display: block; }

  /* Reusable text blocks */
  .draggable { position: absolute; padding: 5px !important; }
  .font-inherit { font-family: inherit; } /* replaces invalid "auto" */
  .font-pinyon { font-family: 'Pinyon Script', cursive; }
  .font-italianno { font-family: 'Italianno', cursive; }
  .font-miss-fajardose { font-family: 'Miss Fajardose', cursive; }
</style>

<div id="certificate-layout-module" class="certificate-layout-module resizeable-canvas draggable ui-draggable ui-draggable-handle ui-resizable">
  <img class="certificate-template" src="uploads/certificate-template/certificate-default.png" alt="Certificate template">

  <!-- QR Code -->
  <div class="draggable font-inherit" style="top:114px; left:93px; width:84.8906px; height:80px; font-size:16px;">
    {qr_code}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Instructor name -->
  <div class="draggable font-pinyon" style="top:546px; left:125px; width:210.031px; height:37px; font-size:18px;">
    {instructor_name}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Student name -->
  <div class="draggable font-pinyon" style="top:546px; left:724px; width:210.188px; height:39px; font-size:18px;">
    {student_name}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Course completion date -->
  <div class="draggable font-inherit" style="top:545px; left:442px; width:min-content; font-size:16px;">
    {course_completion_date}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Certificate download date -->
  <div class="draggable font-inherit" style="top:665px; left:457px; width:min-content; font-size:12px;">
    {certificate_download_date}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Main heading -->
  <div class="draggable font-inherit" style="top:136px; left:264px; width:534.336px; height:62px; font-size:30px;">
    COURSE COMPLETION CERTIFICATE
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Body text -->
  <div class="draggable font-pinyon" style="top:211px; left:205px; width:664.5px; height:98px; font-size:18px;">
    This certificate is awarded to {student_name} in recognition of their successful completion of Course on {course_completion_date}. Your hard work, dedication, and commitment to learning have enabled you to achieve this milestone, and we are proud to recognize your accomplishment.
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>

  <!-- Course title -->
  <div class="draggable font-inherit" style="top:316px; left:315px; width:428.25px; height:48px; font-size:18px;">
    {course_title}
    <i class="remove-item fi-rr-cross-circle cursor-pointer" onclick="$(this).parent().remove()"></i>
  </div>
</div>
HTML
            ],

            ['type' => '_token', 'description' => 'tEYJPyWB4tjFp0tz78j0gDLj07tLXnw5hVpU5mX7'],

            ['type' => 'zoom_account_email', 'description' => 'example@gmail.com'],
            ['type' => 'zoom_account_id', 'description' => 'RG4XYxxxxxxxxxxxxxxx'],
            ['type' => 'zoom_client_id', 'description' => 'mFgJ4xxxxxxxxxxxxxxx'],
            ['type' => 'zoom_client_secret', 'description' => 'OZ6m9xxxxxxxxxxxxxxxx'],
            ['type' => 'zoom_web_sdk', 'description' => 'active'],
            ['type' => 'zoom_sdk_client_id', 'description' => '7M6Wxxxxxxxxxxxx'],
            ['type' => 'zoom_sdk_client_secret', 'description' => 'z1Nzxxxxxxxxxxxxxx'],

            ['type' => 'open_ai_model', 'description' => 'gpt-3.5-turbo-0125'],
            ['type' => 'open_ai_max_token', 'description' => '100'],
            ['type' => 'open_ai_secret_key', 'description' => 'sk-JPYxxxxxxxxxxxxxxxxxxx'],

            ['type' => 'timezone', 'description' => 'Asia/Kolkata'],
            ['type' => 'device_limitation', 'description' => '10'],
        ];

        Setting::insert($settings);
    }
}
