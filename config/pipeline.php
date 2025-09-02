<?php

return [

    'cache_seconds' => 30,

    'stages' => [
        'applied' => [
            'label' => 'Applied',
            'description' => 'Application received; not reviewed yet.',
            'actions' => [
                'move_next',
                'reject',
                'view_profile',
                'add_tag_comment',
            ],
        ],
        'screening' => [
            'label' => 'Screening',
            'description' => 'Qualification + auto match review.',
            'actions' => [
                'move_next',
                'request_more_info',
                'reject',
                'keep_stage',
            ],
        ],
        'assessment' => [
            'label' => 'Assessment / Exam',
            'description' => 'Candidate takes test/assessment.',
            'actions' => [
                'send_exam_instructions',
                'record_test_results',
                'reschedule_test',
                'move_next',
                'reject',
            ],
        ],
        'interview' => [
            'label' => 'Interview',
            'description' => 'HR / technical / managerial interviews.',
            'actions' => [
                'schedule_interview',
                'record_interview_feedback',
                'reschedule_interview',
                'move_next',
                'reject',
            ],
        ],
        'offer' => [
            'label' => 'Offer',
            'description' => 'Preparing or sending the job offer.',
            'actions' => [
                'move_next',
                'generate_offer_letter',
                'send_offer',
                'reject_withdraw',
                'keep_stage',
            ],
        ],
        'hired' => [
            'label' => 'Hired',
            'description' => 'Candidate accepted the offer.',
            'actions' => [
                'archive_candidate',
            ],
        ],
        'rejected' => [
            'label' => 'Rejected',
            'description' => 'Candidate not proceeding.',
            'actions' => [
                'send_rejection_email',
                'archive_candidate',
            ],
        ],
    ],

    'actions' => [
        // Transitions
        'move_next' => ['label' => 'Move to Next Stage', 'type' => 'dynamic_transition', 'icon' => 'fa-forward'],

        'move_to_screening'  => ['label' => 'Move to Screening',  'type' => 'transition', 'to' => 'screening',  'icon' => 'fa-forward'],
        'move_to_assessment' => ['label' => 'Move to Assessment', 'type' => 'transition', 'to' => 'assessment', 'icon' => 'fa-tasks'],
        'move_to_interview'  => ['label' => 'Move to Interview',  'type' => 'transition', 'to' => 'interview',  'icon' => 'fa-comments'],
        'move_to_onboarding' => ['label' => 'Move to Onboarding', 'type' => 'transition', 'to' => 'hired',      'icon' => 'fa-people-carry'],

        'reject'          => ['label' => 'Reject',            'type' => 'transition', 'to' => 'rejected', 'icon' => 'fa-times text-red-600'],
        'reject_withdraw' => ['label' => 'Withdraw / Reject', 'type' => 'transition', 'to' => 'rejected', 'icon' => 'fa-ban text-red-600'],
        'hire'            => ['label' => 'Mark as Hired',     'type' => 'transition', 'to' => 'hired',    'icon' => 'fa-user-check'],

        // View / modal / custom actions
        'view_profile'      => ['label' => 'View Profile',    'type' => 'view',  'icon' => 'fa-id-card'],
        'add_tag_comment'   => ['label' => 'Tag / Comment',   'type' => 'modal', 'modal' => 'tags',                'icon' => 'fa-tag'],
        'request_more_info' => ['label' => 'Request More Info','type' => 'action','event' => 'request_info',       'icon' => 'fa-inbox'],
        'keep_stage'        => ['label' => 'Keep in Stage',   'type' => 'noop',  'icon' => 'fa-pause'],

        // Interview related modals
        'schedule_interview'        => ['label' => 'Schedule Interview',        'type' => 'modal', 'modal' => 'schedule_interview', 'icon' => 'fa-calendar'],
        'reschedule_interview'      => ['label' => 'Reschedule Interview',      'type' => 'modal', 'modal' => 'schedule_interview', 'icon' => 'fa-redo'],
        'record_interview_feedback' => ['label' => 'Record Interview Feedback', 'type' => 'modal', 'modal' => 'interview_feedback', 'icon' => 'fa-clipboard'],

        // Assessment / exam modals 
        'send_exam_instructions' => ['label' => 'Send Exam Instructions', 'type' => 'modal', 'modal' => 'exam_instructions', 'icon' => 'fa-envelope-open-text'],
        'record_test_results'    => ['label' => 'Record Test Results',    'type' => 'modal', 'modal' => 'test_results',      'icon' => 'fa-file-alt'],
        'reschedule_test'        => ['label' => 'Reschedule Test',        'type' => 'modal', 'modal' => 'test_schedule',     'icon' => 'fa-redo'],

        // Offer stage
        'generate_offer_letter' => ['label' => 'Generate Offer Letter', 'type' => 'action', 'event' => 'generate_offer',  'icon' => 'fa-file-contract'],
        'send_offer'            => ['label' => 'Send Offer',            'type' => 'action', 'event' => 'send_offer',       'icon' => 'fa-paper-plane'],

        // Rejection / archive
        'send_rejection_email' => ['label' => 'Send Rejection Email', 'type' => 'action', 'event' => 'send_rejection', 'icon' => 'fa-envelope'],
        'archive_candidate'    => ['label' => 'Archive Candidate',    'type' => 'action', 'event' => 'archive',        'icon' => 'fa-archive'],
    ],
];