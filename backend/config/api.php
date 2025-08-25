<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the API endpoints and settings.
    |
    */

    'version' => 'v1',
    
    'base_url' => env('APP_URL') . '/api',
    
    'rate_limit' => [
        'default' => 60, // requests per minute
        'auth' => 5,     // login attempts per minute
    ],
    
    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],
    
    'roles' => [
        'admin' => 'admin',
        'coach' => 'coach', 
        'member' => 'member',
    ],
    
    'subscription_statuses' => [
        'active' => 'active',
        'expired' => 'expired',
        'cancelled' => 'cancelled',
        'pending' => 'pending',
    ],
    
    'genders' => [
        'male' => 'male',
        'female' => 'female',
    ],
    
    'workout_difficulties' => [
        'beginner' => 'beginner',
        'intermediate' => 'intermediate',
        'advanced' => 'advanced',
    ],
    
    'nutrition_goals' => [
        'weight_loss' => 'weight_loss',
        'muscle_gain' => 'muscle_gain',
        'maintenance' => 'maintenance',
        'performance' => 'performance',
    ],
]; 