<?php
/*
Plugin Name: Gravity Forms Entry Auto-Remover
Plugin URI: https://github.com/danbeckett
Description: Automatically remove Gravity Forms entries 5 days after submission.
Version: 1.0.0
Requires at least: 5.2.1
Author: danbeckett
Author URI: https://github.com/danbeckett
------------------------------------------------------------------------
Copyright (C) 2019  Dan Beckett

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
------------------------------------------------------------------------
*/

define('BASE_FILE', __FILE__);

class EntryRemover {

    public function __construct()
    {
        register_activation_hook(BASE_FILE, array($this, 'activate_gravityforms_auto_entry_remover_cron'));
        register_deactivation_hook(BASE_FILE, array($this, 'deactivate_gravityforms_auto_entry_remover_cron'));
        add_action('daily_entries_removal', array($this, 'remove_entries'));
    }

    public function activate_gravityforms_auto_entry_remover_cron() {
        if (! wp_next_scheduled ( 'daily_entries_removal' )) {
            wp_schedule_event(strtotime('today midnight'), 'daily', 'daily_entries_removal');
        }
    }

    public function deactivate_gravityforms_auto_entry_remover_cron() {
        wp_clear_scheduled_hook('daily_entries_removal');
    }

    public function remove_entries()
    {
        $forms = GFAPI::get_forms();
        foreach ($forms as $form) {
           $this->remove_form_entries($form['id']);
        }
    }

    public function remove_form_entries($form_id)
    {
        $search_criteria = [
            'start_date' => date( 'Y-m-d', 0),
            'end_date' => date( 'Y-m-d', strtotime('-5 days'))
        ];
        $entries = GFAPI::get_entries($form_id, $search_criteria);
        foreach ($entries as $entry) {
            GFAPI::delete_entry($entry['id']);
        }
    }
}

$entryRemover = new EntryRemover();
