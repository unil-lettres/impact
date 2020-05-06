<?php

namespace App\Enums;

final class FileStatus
{
    const Processing = 'processing';
    const Transcoding = 'transcoding';
    const Failed = 'failed';
    const Ready = 'ready';
}
