<?php
namespace WPPOOL\SCF;

// Exit if accessed directly.
defined('ABSPATH') || exit; // phpcs:ignore Universal.PHP.RequireExitDieParentheses.Missing

if ( ! \class_exists(__NAMESPACE__ . '\Admin') ) {
    class Admin {

        /**
         * To be recognized by Older version of Ultimate only.
         * No role in current version.
         */
    }
}
