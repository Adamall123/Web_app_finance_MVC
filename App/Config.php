<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'web_app_finance_mvc';

    /**
     * Database user
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    const SECRET_KEY = 'RaHAruH7YLdiQVmoHLuRAvosFCCGd2mp';

    const SENDGRID_API_KEY = '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';
}
