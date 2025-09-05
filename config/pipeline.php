<?php

return [
    'cache_seconds' => 30,

    'stages' => [
        'applied' => [
            'label' => 'Applied',
            'description' => 'Application received.',
            'actions' => [
                'move_next',
                'reject',
                'view_profile',
                'add_tag_comment',
            ],
        ],
        'screening' => [
            'label' => 'Screening',
            'description' => 'Initial qualification review.',
            'actions' => [
                'move_next',
                'request_more_info',
                'reject',
                'keep_stage',
            ],
        ],
        'assessment' => [
            'label' => 'Assessment / Exam',
            'description' => 'Testing phase.',
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
            'description' => 'Interview rounds.',
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
                'hire',
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
        'move_next' => ['label'=>'Move to Next Stage','type'=>'dynamic_transition','icon'=>'fa-forward'],

        'reject'          => ['label'=>'Reject','type'=>'transition','to'=>'rejected','icon'=>'fa-times text-red-600'],
        'reject_withdraw' => ['label'=>'Withdraw / Reject','type'=>'transition','to'=>'rejected','icon'=>'fa-ban text-red-600'],
        'hire'            => ['label'=>'Mark as Hired','type'=>'transition','to'=>'hired','icon'=>'fa-user-check'],

        'view_profile'      => ['label'=>'View Profile','type'=>'view','icon'=>'fa-id-card'],
        'add_tag_comment'   => ['label'=>'Tag / Comment','type'=>'modal','modal'=>'tags','icon'=>'fa-tag'],
        'request_more_info' => ['label'=>'Request More Info','type'=>'action','event'=>'request_info','icon'=>'fa-inbox'],
        'keep_stage'        => ['label'=>'Keep in Stage','type'=>'noop','icon'=>'fa-pause'],

        'schedule_interview'        => ['label'=>'Schedule Interview','type'=>'modal','modal'=>'schedule_interview','icon'=>'fa-calendar'],
        'reschedule_interview'      => ['label'=>'Reschedule Interview','type'=>'modal','modal'=>'schedule_interview','icon'=>'fa-redo'],
        'record_interview_feedback' => ['label'=>'Record Interview Feedback','type'=>'modal','modal'=>'interview_feedback','icon'=>'fa-clipboard'],

        'send_exam_instructions' => ['label'=>'Send Exam Instructions','type'=>'modal','modal'=>'exam_instructions','icon'=>'fa-envelope-open-text'],
        'record_test_results'    => ['label'=>'Record Test Results','type'=>'modal','modal'=>'test_results','icon'=>'fa-file-alt'],
        'reschedule_test'        => ['label'=>'Reschedule Test','type'=>'modal','modal'=>'test_schedule','icon'=>'fa-redo'],

        'send_offer' => ['label'=>'Send Offer','type'=>'modal','modal'=>'send_offer','icon'=>'fa-paper-plane'],

        'send_rejection_email' => ['label'=>'Send Rejection Email','type'=>'action','event'=>'send_rejection','icon'=>'fa-envelope'],
        'archive_candidate'    => ['label'=>'Archive Candidate','type'=>'action','event'=>'archive','icon'=>'fa-archive'],
    ],
];