<?php
/** @var \Core\Router $router */

// ── Public routes ────────────────────────────────────────────────────
$router->get('/',       'AuthController@showLogin');
$router->get('/login',  'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ── Authenticated routes ─────────────────────────────────────────────
$router->group(['AuthMiddleware'], function ($r) {

    $r->get('/dashboard', 'DashboardController@index');

    // Profile
    $r->get('/profile',         'AuthController@profile');
    $r->post('/profile/update', 'AuthController@updateProfile');

    // Leads
    $r->get('/leads',                'LeadController@index');
    $r->get('/leads/create',         'LeadController@create');
    $r->post('/leads/store',         'LeadController@store');
    $r->get('/leads/upload',         'LeadUploadController@index');
    $r->post('/leads/import',        'LeadUploadController@import');
    $r->get('/leads/{id}',           'LeadController@show');
    $r->get('/leads/{id}/edit',      'LeadController@edit');
    $r->post('/leads/{id}/update',   'LeadController@update');
    $r->post('/leads/{id}/delete',   'LeadController@delete');
    $r->post('/leads/{id}/followup', 'LeadController@addFollowup');

    // Projects
    $r->get('/projects',                   'ProjectController@index');
    $r->get('/projects/create',            'ProjectController@create');
    $r->post('/projects/store',            'ProjectController@store');
    $r->get('/projects/{id}',              'ProjectController@show');
    $r->get('/projects/{id}/edit',         'ProjectController@edit');
    $r->post('/projects/{id}/update',      'ProjectController@update');
    $r->post('/projects/{id}/delete',      'ProjectController@delete');
    $r->get('/projects/{id}/units',        'ProjectController@units');
    $r->post('/projects/{id}/units/store', 'ProjectController@storeUnit');
    $r->post('/units/{id}/update',         'ProjectController@updateUnit');
    $r->post('/units/{id}/delete',         'ProjectController@deleteUnit');

    // Site Visits
    $r->get('/site-visits',              'SiteVisitController@index');
    $r->get('/site-visits/create',       'SiteVisitController@create');
    $r->post('/site-visits/store',       'SiteVisitController@store');
    $r->get('/site-visits/{id}/edit',    'SiteVisitController@edit');
    $r->post('/site-visits/{id}/update', 'SiteVisitController@update');
    $r->post('/site-visits/{id}/delete', 'SiteVisitController@delete');

    // Notifications
    $r->get('/notifications',           'NotificationController@index');
    $r->post('/notifications/read-all', 'NotificationController@markAllRead');
    $r->get('/notifications/unread',    'NotificationController@unreadCount');

    // Attendance
    $r->get('/attendance',                  'AttendanceController@index');
    $r->get('/attendance/checkin',          'AttendanceController@checkin');
    $r->post('/attendance/mark-face',       'AttendanceController@markByFace');
    $r->get('/attendance/descriptors',      'AttendanceController@descriptors');
    $r->get('/attendance/enroll',           'AttendanceController@enroll');
    $r->get('/attendance/enroll/{id}',      'AttendanceController@enroll');
    $r->post('/attendance/save-descriptor', 'AttendanceController@saveDescriptor');
    $r->post('/attendance/manual',          'AttendanceController@manualMark');
    $r->get('/attendance/report',           'AttendanceController@report');

    // My salary slips (any authenticated user)
    $r->get('/my-slips', 'PayrollController@mySlips');

    // Targets (all staff can view)
    $r->get('/targets', 'TargetController@index');

    // ── Manager + Admin + HR ─────────────────────────────────────
    $r->group(['ManagerMiddleware'], function ($r) {

        $r->get('/employees',              'EmployeeController@index');
        $r->get('/employees/create',       'EmployeeController@create');
        $r->post('/employees/store',       'EmployeeController@store');
        $r->get('/employees/{id}',         'EmployeeController@show');
        $r->get('/employees/{id}/edit',    'EmployeeController@edit');
        $r->post('/employees/{id}/update', 'EmployeeController@update');
        $r->post('/employees/{id}/delete', 'EmployeeController@delete');

        $r->get('/reports',             'ReportController@index');
        $r->get('/reports/leads',       'ReportController@leads');
        $r->get('/reports/sales',       'ReportController@sales');
        $r->get('/reports/performance', 'ReportController@performance');

        $r->get('/targets/create',  'TargetController@create');
        $r->post('/targets/store',  'TargetController@store');

        $r->get('/expenses',               'ExpenseController@index');
        $r->post('/expenses/store',        'ExpenseController@store');
        $r->post('/expenses/{id}/delete',  'ExpenseController@delete');
    });

    // ── Admin + Super Admin only ─────────────────────────────────
    $r->group(['AdminMiddleware'], function ($r) {
        $r->get('/payroll',                  'PayrollController@index');
        $r->get('/payroll/create',           'PayrollController@create');
        $r->get('/payroll/calc-attendance',  'PayrollController@calcAttendance');
        $r->post('/payroll/store',           'PayrollController@store');
        $r->get('/payroll/{id}/slip',        'PayrollController@slip');
        $r->post('/payroll/{id}/delete',     'PayrollController@delete');
    });
});
