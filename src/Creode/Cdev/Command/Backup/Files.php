<?php

namespace Creode\Cdev\Command\Backup;

class Files
{
    const DB_DIR = 'db';
    const MEDIA_DIR = 'media';
    
    const DB_FILE = DB_DIR . '/backup.sql';
    const MEDIA_FILE = MEDIA_DIR . '/backup.tar';
}
