<?php

namespace Database\Seeders;

use App\Models\HomePageSetting;
use Illuminate\Database\Seeder;

class HomePageSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'home_page_id' => 14,
                'key' => 'cooking',
                'value' => '{"title":"Become An Instructor","description":"Training programs can bring you a super exciting experience of learning through online! You never face any negative experience while enjoying your classes.\r\n\r\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent per conubi himenaeos Awesome site Lorem Ipsum has been the industry\'s standard dummy text ever since the unknown printer took a galley of type and scrambled.\r\n\r\nConsectetur adipiscing elit. Nunc vulputate ad litora torquent per conubi himenaeos Awesome site Lorem Ipsum has been the industry\'s standard dummy text ever since.","video_url":"https://www.youtube.com/watch?v=iTlsP6RfCQ8","image":"instructor_image.jpg"}',
            ],
            [
                'home_page_id' => 15,
                'key' => 'university',
                'value' => '{"image":"default-university.webp","faq_image":"default-university2.webp","slider_items":"[\"https://www.youtube.com/watch?v=iTlsP6RfCQ8\"]"}',
            ],
            [
                'home_page_id' => 17,
                'key' => 'development',
                'value' => '{"title":"Leading the Way in Software Development","description":"Far far away, behind the word mountains, far from the away countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean.\r\nTraining programs can bring you a super exciting experience of learning through online! You never face any negative experience while enjoying your classes. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent","video_url":null,"image":"default-dev-banner.webp"}',
            ],
            [
                'home_page_id' => 13,
                'key' => 'kindergarden',
                'value' => '{"title":"Creating A Community Of Life Long Learners","description":"Training programs can bring you a super exciting experience of learning through online! You never face any negative experience while enjoying your classes.\r\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent\r\nTraining programs can bring you a super exciting experience of learning through online! You never face any negative experience while enjoying your classes. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent","video_url":null,"image":"default-community-banner.webp"}',
            ],
            [
                'home_page_id' => 18,
                'key' => 'marketplace',
                'value' => '{"instructor":{"title":"Become an instructor","description":"Training programs can bring you a super exciting experience of learning through online! You never face any negative experience while enjoying your classes.\r\n\r\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate ad litora torquent per conubi himenaeos Awesome site Lorem Ipsum has been the industry\'s standard dummy text ever since the unknown printer took a galley of type and scrambled.\r\n\r\nConsectetur adipiscing elit. Nunc vulputate ad litora torquent per conubi himenaeos Awesome site Lorem Ipsum has been the industry\'s standard dummy text ever sinces.","video_url":"https://www.youtube.com/watch?v=i-rv4VQiBko","image":"default-video-area-banner.webp"},"slider":[{"banner_title":"LEARN FROM TODAY","banner_description":"Academy Starter is a community for creative people"},{"banner_title":"LEARN FROM TODAY","banner_description":"Academy Starter is a community for creative people"},{"banner_title":"LEARN FROM TODAY","banner_description":"Academy Starter is a community for creative people"},{"banner_title":"LEARN FROM TODAY","banner_description":"Academy Starter is a community for creative people"}]}',
            ],
            [
                'home_page_id' => 19,
                'key' => 'meditation',
                'value' => '{"big_image":"664b020ed2bbb.png","meditation":[{"banner_title":"Balance Body & Mind","image":"664b07fa650dd.yoga-benefit-1.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."},{"banner_title":"Balance Body & Minds","image":"664b08157c7ed.yoga-benefit-2.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."},{"banner_title":"Balance Body & Mind","image":"664b08157cab8.yoga-benefit-3.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."},{"banner_title":"Balance Body & Mind","image":"664b08157d2be.yoga-benefit-4.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."},{"banner_title":"Balance Body & Mind","image":"664b08263ba18.yoga-benefit-5.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."},{"banner_title":"Balance Body & Minddf","image":"664b08263bcca.yoga-benefit-6.svg","banner_description":"It is a long established fact that a reader will be distracted by the readable content."}]}',
            ],
        ];

        HomePageSetting::insert($settings);
    }
}
