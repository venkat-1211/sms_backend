<?php

namespace App\Constants;

class AppConstants
{
    // Status Constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    // Pagination
    const DEFAULT_PER_PAGE = 15;
    const MAX_PER_PAGE = 100;

    // Cache TTL (seconds)
    const CACHE_TTL_SHORT = 300;     // 5 minutes
    const CACHE_TTL_MEDIUM = 3600;   // 1 hour
    const CACHE_TTL_LONG = 86400;    // 24 hours

    // Cache Keys
    const CACHE_KEY_DASHBOARD = 'dashboard:data';
    const CACHE_KEY_DASHBOARD_STATS = 'dashboard:stats';
    const CACHE_KEY_STUDENTS = 'students:*';
    const CACHE_KEY_COURSES = 'courses:*';
    const CACHE_KEY_ADMISSIONS = 'admissions:*';

    // User Roles
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';

    // File Upload
    const MAX_FILE_SIZE = 2048; // KB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    // Date Formats
    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DISPLAY_DATE_FORMAT = 'M d, Y';
    const DISPLAY_DATETIME_FORMAT = 'M d, Y H:i';

    // Payment
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';
}