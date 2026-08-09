<?php

return [
    'max_rows' => (int) env('IMPORT_MAX_ROWS', 1000),
    'max_file_size_kb' => (int) env('IMPORT_MAX_FILE_SIZE_KB', 5120),
];
