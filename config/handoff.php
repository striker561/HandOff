<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global upload limits
    |--------------------------------------------------------------------------
    |
    | Site-wide defaults for Livewire file upload components. Domain modals
    | may pass stricter limits via component props when needed.
    |
    */

    'uploads' => [
        'max_kilobytes' => (int) env('UPLOAD_MAX_KB', 102400),
        'max_files' => (int) env('UPLOAD_MAX_FILES', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deliverables
    |--------------------------------------------------------------------------
    */

    'deliverables' => [
        'max_files' => (int) env('DELIVERABLE_MAX_FILES', env('UPLOAD_MAX_FILES', 20)),
    ],

];
