<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profile Options Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the select box options for profile forms.
    | Each option uses integer values for database storage and string labels for display.
    |
    */

    'gender' => [
        1 => 'Male',
        2 => 'Female',
        3 => 'Other',
        4 => 'Prefer not to say'
    ],

    'institute_type' => [
        1 => 'School',
        2 => 'College',
        3 => 'University',
        4 => 'Coaching Center',
        5 => 'NGO'
    ],

    'institute_category' => [
        1 => 'Government',
        2 => 'Private',
        3 => 'Aided'
    ],

    'current_class' => [
        1 => 'Class 1',
        2 => 'Class 2',
        3 => 'Class 3',
        4 => 'Class 4',
        5 => 'Class 5',
        6 => 'Class 6',
        7 => 'Class 7',
        8 => 'Class 8',
        9 => 'Class 9',
        10 => 'Class 10',
        11 => 'Class 11',
        12 => 'Class 12',
        13 => 'Undergraduate',
        14 => 'Postgraduate'
    ],

    'board' => [
        1 => 'CBSE',
        2 => 'ICSE',
        3 => 'State Board',
        4 => 'IB',
        5 => 'IGCSE'
    ],

    'stream' => [
        1 => 'Science',
        2 => 'Commerce',
        3 => 'Arts',
        4 => 'Computer Science',
        5 => 'Engineering',
        6 => 'Medical'
    ],

    'teaching_mode' => [
        1 => 'Online Only',
        2 => 'Offline Only',
        3 => 'Both Online & Offline',
        4 => 'Google Meet',
        5 => 'Zoom Meetings'
    ],

    'availability_status' => [
        1 => 'Available',
        2 => 'Busy',
        3 => 'Unavailable',
        4 => 'On Leave',
        5 => 'In a Meeting'
    ],

    'timezone' => [
        1 => 'Asia/Kolkata', // India (IST)
        2 => 'UTC',
        3 => 'America/New_York', // Eastern Time
        4 => 'America/Los_Angeles', // Pacific Time
        5 => 'Europe/London', // UK
        6 => 'Australia/Sydney', // Australia
        7 => 'Asia/Dubai', // UAE
        8 => 'Asia/Singapore', // Singapore
        9 => 'Asia/Tokyo', // Japan
        10 => 'Europe/Paris' // France
    ],

    'country' => [
        1 => 'India',
    ],

    'highest_qualification' => [
        1 => 'High School',
        2 => 'Diploma',
        3 => 'Bachelor\'s Degree',
        4 => 'Master\'s Degree',
        5 => 'Ph.D.',
        6 => 'B.Ed',
        7 => 'M.Ed',
        8 => 'B.Tech',
        9 => 'M.Tech',
        10 => 'MBBS',
        11 => 'MD',
        12 => 'CA',
        13 => 'CS',
        14 => 'LLB',
        15 => 'LLM'
    ],

    'field_of_study' => [
        1 => 'Computer Science',
        2 => 'Mathematics',
        3 => 'Physics',
        4 => 'Chemistry',
        5 => 'Biology',
        6 => 'English',
        7 => 'Hindi',
        8 => 'History',
        9 => 'Geography',
        10 => 'Economics',
        11 => 'Commerce',
        12 => 'Accountancy',
        13 => 'Engineering',
        14 => 'Medical',
        15 => 'Law',
        16 => 'Arts',
        17 => 'Education',
        18 => 'Business Administration',
        19 => 'Information Technology',
        20 => 'Electronics'
    ],

    'teaching_experience_years' => [
        1 => '1 Year',
        2 => '2 Years',
        3 => '3 Years',
        4 => '4 Years',
        5 => '5 Years',
        6 => '6 Years',
        7 => '7 Years',
        8 => '8 Years',
        9 => '9 Years',
        10 => '10 Years',
        11 => '11-15 Years',
        12 => '16-20 Years',
        13 => '21-25 Years',
        14 => '26-30 Years',
        15 => '30+ Years'
    ],

    'travel_radius_km' => [
        0 => 'No Travel',
        1 => '1 km',
        2 => '2 km',
        3 => '3 km',
        4 => '4 km',
        5 => '5 km',
        6 => '6 km',
        7 => '7 km',
        8 => '8 km',
        9 => '9 km',
        10 => '10 km',
        15 => '15 km',
        20 => '20 km',
        25 => '25 km',
        30 => '30 km',
        40 => '40 km',
        50 => '50 km',
        75 => '75 km',
        100 => '100 km'
    ],

    'hourly_rate_range' => [
        0 => 'Not Specified',
        1 => '₹100-200',
        2 => '₹201-300',
        3 => '₹301-400',
        4 => '₹401-500',
        5 => '₹501-600',
        6 => '₹601-700',
        7 => '₹701-800',
        8 => '₹801-900',
        9 => '₹901-1000',
        10 => '₹1000+'
    ],

    'monthly_rate_range' => [
        0 => 'Not Specified',
        1 => '₹1000-2000',
        2 => '₹2001-3000',
        3 => '₹3001-4000',
        4 => '₹4001-5000',
        5 => '₹5001-6000',
        6 => '₹6001-7000',
        7 => '₹7001-8000',
        8 => '₹8001-9000',
        9 => '₹9001-10000',
        10 => '₹10000+'
    ],

    'budget_range' => [
        1 => '₹100-500',
        2 => '₹501-1000',
        3 => '₹1001-1500',
        4 => '₹1501-2000',
        5 => '₹2001-2500',
        6 => '₹2501-3000',
        7 => '₹3001-4000',
        8 => '₹4001-5000',
        9 => '₹5001-7500',
        10 => '₹7500+'
    ],

    'establishment_year_range' => [
        1 => 'Before 1950',
        2 => '1950-1960',
        3 => '1961-1970',
        4 => '1971-1980',
        5 => '1981-1990',
        6 => '1991-2000',
        7 => '2001-2010',
        8 => '2011-2020',
        9 => '2021-Present'
    ],

    'total_students_range' => [
        1 => '1-50',
        2 => '51-100',
        3 => '101-200',
        4 => '201-500',
        5 => '501-1000',
        6 => '1001-2000',
        7 => '2001-5000',
        8 => '5000+'
    ],

    'total_teachers_range' => [
        1 => '1-10',
        2 => '11-20',
        3 => '21-30',
        4 => '31-50',
        5 => '51-75',
        6 => '76-100',
        7 => '101-150',
        8 => '150+'
    ],

    'teaching_mode_enum' => [
        'online' => 'Online',
        'in-person' => 'In-person',
        'hybrid' => 'Hybrid',
        'google_meet' => 'Google Meet',
        'zoom_meetings' => 'Zoom Meetings'
    ],
   

];
