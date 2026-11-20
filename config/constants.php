<?php

    declare(strict_types=1);

    if (!defined('APP_NAME')){
        define('APP_NAME', 'Wishlist');
    }

    if (!defined('ASSETS_PATH')){
        define('ASSETS_PATH', '/assets');
    }



    if (!defined('ROUTE_HOME')){
        define('ROUTE_HOME', '/index.php');
    }

    if (!defined('ROUTE_REGISTER')){
        define('ROUTE_REGISTER', '/pages/register.php');
    }

    if (!defined('ROUTE_LOGIN')){
        define('ROUTE_LOGIN', '/pages/login.php');
    }

    if (!defined('ROUTE_PROFILE')){
        define('ROUTE_PROFILE', '/pages/profile.php');
    }

    if (!defined('ROUTE_SETTINGS')){
        define('ROUTE_SETTINGS', '/pages/settings.php');
    }

    if (!defined('ROUTE_FULFILLED')){
        define('ROUTE_FULFILLED', '/pages/fulfilled.php');
    }

    if (!defined('ROUTE_RESERVATIONS')){
        define('ROUTE_RESERVATIONS', '/pages/reservations.php');
    }

    if (!defined('ROUTE_SEARCH')){
        define('ROUTE_SEARCH', '/pages/search.php');
    }

    if (!defined('ROUTE_USER')){
        define('ROUTE_USER', '/pages/user.php');
    }



    if (!defined('ROUTE_REGISTER_HANDLER')){
        define('ROUTE_REGISTER_HANDLER', '/actions/auth/register_handler.php');
    }

    if (!defined('ROUTE_LOGOUT_HANDLER')){
        define('ROUTE_LOGOUT_HANDLER', '/actions/auth/logout_handler.php');
    }

    if (!defined('ROUTE_LOGIN_HANDLER')){
        define('ROUTE_LOGIN_HANDLER', '/actions/auth/login_handler.php');
    }

    if (!defined('ROUTE_SAVE_WISH_HANDLER')){
        define('ROUTE_SAVE_WISH_HANDLER', '/actions/catalog/save_wish_handler.php');
    }

    if (!defined('ROUTE_CREATE_WISH_HANDLER')){
        define('ROUTE_CREATE_WISH_HANDLER', '/actions/wishes/create_wish_handler.php');
    }

    if (!defined('ROUTE_UPDATE_WISH_HANDLER')){
        define('ROUTE_UPDATE_WISH_HANDLER', '/actions/wishes/update_wish_handler.php');
    }

    if (!defined('ROUTE_DELETE_WISH_HANDLER')){
        define('ROUTE_DELETE_WISH_HANDLER', '/actions/wishes/delete_wish_handler.php');
    }

    if (!defined('ROUTE_ADD_FULFILLED_WISH_HANDLER')){
        define('ROUTE_ADD_FULFILLED_WISH_HANDLER', '/actions/wishes/add_fulfilled_wish_handler.php');
    }

    if (!defined('ROUTE_DELETE_FULFILLED_WISH_HANDLER')){
        define('ROUTE_DELETE_FULFILLED_WISH_HANDLER', '/actions/wishes/delete_fulfilled_wish_handler.php');
    }

    if (!defined('ROUTE_UPDATE_FULFILLED_WISH_HANDLER')){
        define('ROUTE_UPDATE_FULFILLED_WISH_HANDLER', '/actions/wishes/update_fulfilled_wish_handler.php');
    }

    if (!defined('ROUTE_DELETE_FULFILLED_WISH_PERMANENT_HANDLER')){
        define('ROUTE_DELETE_FULFILLED_WISH_PERMANENT_HANDLER', '/actions/wishes/delete_fulfilled_wish_permanent_handler.php');
    }

    if (!defined('ROUTE_RESERVE_WISH_HANDLER')){
        define('ROUTE_RESERVE_WISH_HANDLER', '/actions/wishes/reserve_wish_handler.php');
    }

    if (!defined('ROUTE_UNRESERVE_WISH_HANDLER')){
        define('ROUTE_UNRESERVE_WISH_HANDLER', '/actions/wishes/unreserve_wish_handler.php');
    }

    if (!defined('ROUTE_UPDATE_PROFILE_HANDLER')){
        define('ROUTE_UPDATE_PROFILE_HANDLER', '/actions/profile/update_profile_handler.php');
    }

    if (!defined('ROUTE_UPLOAD_AVATAR_HANDLER')){
        define('ROUTE_UPLOAD_AVATAR_HANDLER', '/actions/profile/upload_avatar_handler.php');
    }

    if (!defined('ROUTE_CHANGE_EMAIL_HANDLER')){
        define('ROUTE_CHANGE_EMAIL_HANDLER', '/actions/profile/change_email_handler.php');
    }

    if (!defined('ROUTE_CHANGE_PASSWORD_HANDLER')){
        define('ROUTE_CHANGE_PASSWORD_HANDLER', '/actions/profile/change_password_handler.php');
    }

    if (!defined('ROUTE_DELETE_PROFILE_HANDLER')){
        define('ROUTE_DELETE_PROFILE_HANDLER', '/actions/profile/delete_profile_handler.php');
    }

    if (!defined('ROUTE_SEARCH_HANDLER')){
        define('ROUTE_SEARCH_HANDLER', '/actions/search/search_handler.php');
    }



    if (!defined('DEFAULT_REDIRECT_HOME')){
        define('DEFAULT_REDIRECT_HOME', ROUTE_HOME);
    }

    if (!defined('DEFAULT_REDIRECT_LOGIN')){
        define('DEFAULT_REDIRECT_LOGIN', ROUTE_LOGIN);
    }

    if (!defined('DEFAULT_REDIRECT_REGISTER')){
        define('DEFAULT_REDIRECT_REGISTER', ROUTE_REGISTER);
    }

    if (!defined('DEFAULT_REDIRECT_PROFILE')){
        define('DEFAULT_REDIRECT_PROFILE', ROUTE_PROFILE);
    }



    if (!defined('PASSWORD_MIN_LENGTH')){
        define('PASSWORD_MIN_LENGTH', 8);
    }

    if (!defined('PASSWORD_MAX_LENGTH')){
        define('PASSWORD_MAX_LENGTH', 72);
    }

    if (!defined('NAME_MIN_LENGTH')){
        define('NAME_MIN_LENGTH', 2);
    }

    if (!defined('NAME_MAX_LENGTH')){
        define('NAME_MAX_LENGTH', 50);
    }

    if (!defined('USERNAME_MIN_LENGTH')){
        define('USERNAME_MIN_LENGTH', 3);
    }

    if (!defined('USERNAME_MAX_LENGTH')){
        define('USERNAME_MAX_LENGTH', 30);
    }

    if (!defined('EMAIL_MAX_LENGTH')){
        define('EMAIL_MAX_LENGTH', 254);
    }

    

    if (!defined('FLASH_MESSAGES_KEY')){
        define('FLASH_MESSAGES_KEY', 'flash_messages');
    }

    if (!defined('OLD_INPUT_KEY')){
        define('OLD_INPUT_KEY', 'old_input');
    }

    if (!defined('VALIDATION_ERRORS_KEY')){
        define('VALIDATION_ERRORS_KEY', 'validation_errors');
    }

    if (!defined('AUTH_SESSION_KEY')){
        define('AUTH_SESSION_KEY', 'auth_user');
    }

    if (!defined('AUTH_REDIRECT_KEY')){
        define('AUTH_REDIRECT_KEY', 'auth_redirect_to');
    }

    if (!defined('WISH_IMAGE_MAX_SIZE_BYTES')){
        define('WISH_IMAGE_MAX_SIZE_BYTES', 8 * 1024 * 1024);
    }

    if (!defined('WISH_UPLOADS_WEB_PATH')){
        define('WISH_UPLOADS_WEB_PATH', '/uploads/wishes');
    }

    if (!defined('WISH_UPLOADS_DIR')){
        define('WISH_UPLOADS_DIR', __DIR__ . '/../uploads/wishes');
    }

    if (!defined('WISH_ALLOWED_MIME_TYPES')){
        define('WISH_ALLOWED_MIME_TYPES', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ]);
    }

    if (!defined('AVATAR_IMAGE_MAX_SIZE_BYTES')){
        define('AVATAR_IMAGE_MAX_SIZE_BYTES', 4 * 1024 * 1024);
    }

    if (!defined('AVATAR_UPLOADS_WEB_PATH')){
        define('AVATAR_UPLOADS_WEB_PATH', '/uploads/avatar');
    }

    if (!defined('AVATAR_UPLOADS_DIR')){
        define('AVATAR_UPLOADS_DIR', __DIR__ . '/../uploads/avatar');
    }

    if (!defined('AVATAR_ALLOWED_MIME_TYPES')){
        define('AVATAR_ALLOWED_MIME_TYPES', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ]);
    }