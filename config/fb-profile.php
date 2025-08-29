<?php

return [
    'max_avatar_size' => env('PROFILE_AVATAR_SIZE', 200),
    'avatar_disk' => env('AVATAR_DISK', 'public'),
    'avatar_visibility' => env('AVATAR_VISIBILITY', 'public'),
    'avatar_folder' => env('AVATAR_FOLDER', 'avatars'),
    'mobile_required' => env('PROFILE_MOBILE_REQUIRED', false),
    'email_required' => env('PROFILE_EMAIL_REQUIRED', false),
    'username_required' => env('PROFILE_USERNAME_REQUIRED', false),
    'nid_required' => env('PROFILE_NID_REQUIRED', false),
    'use_passport_number_on_nid' => env('PROFILE_PASS_NUMBER', false),
    'gender_required' => env('PROFILE_GENDER_REQUIRED', false),
    'birth_date_required' => env('PROFILE_BIRTH_DATE_REQUIRED', false),
];
