<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role permissions (ability keys)
    |--------------------------------------------------------------------------
    |
    | admin: * (all abilities)
    | teacher: academics — attendance, exams/grades, read-only directory
    | staff: office — fees, payments, notices; no grade edits
    | parent: portal routes only (not listed here)
    |
    */

    'roles' => [
        'admin' => ['*'],

        'teacher' => [
            'dashboard.view',
            'students.view',
            'classes.view',
            'subjects.view',
            'teachers.view',
            'attendance.view',
            'attendance.manage',
            'exams.view',
            'exams.manage',
            'grades.manage',
            'timetable.view',
            'announcements.feed',
            'reports.view',
            'exports.download',
            'library.view',
            'library.manage',
            'academics.view',
            'academics.manage',
            'files.upload',
            'communication.view',
            'communication.messages',
        ],

        'staff' => [
            'dashboard.view',
            'students.view',
            'classes.view',
            'parents.view',
            'parents.manage',
            'fees.view',
            'fees.manage',
            'fee_types.manage',
            'payments.record',
            'announcements.view',
            'announcements.manage',
            'announcements.feed',
            'reports.view',
            'exports.download',
            'library.manage',
            'transport.manage',
            'hr.view',
            'hr.manage',
            'academics.view',
            'promotion.manage',
            'finance.expenses',
            'finance.payments.view',
            'audit.view',
            'files.upload',
            'communication.view',
            'communication.notify',
            'communication.messages',
            'communication.moderate',
            'communication.calendar',
        ],
    ],

];
