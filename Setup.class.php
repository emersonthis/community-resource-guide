<?php

namespace resourceguide;

class Setup {

    public static $contributorRoleName = 'resource_contributor';

    public function __construct() {
        # initialize vars
        $this->contributorCaps = [
            'edit_resources',
            'read',
            'edit_published_resources'    
        ];

        $this->moderatorCaps = array_merge(
            $this->contributorCaps,
            [
                'edit_other_resources',
                'publish_resources',
                'read_private_resources',
                'delete_resource',
            ]
            );
    }

    public function activate() {
        # Create new role
        add_role(
            self::$contributorRoleName,
            __('Resource Contributor'),
        );

        # Add respective permissions
        $contributorRole = get_role( self::$contributorRoleName );
        foreach ($this->contributorCaps as $capability) {
            $contributorRole->add_cap( $capability );
        }

        $editorRole = get_role('editor');
        foreach($this->moderatorCaps as $capability) {
            $editorRole->add_cap( $capability );
        }

        $adminRole = get_role('administrator');
        foreach($this->moderatorCaps as $capability) {
            $adminRole->add_cap( $capability );
        }

    }

    public function deactivate() {
        # This intentionally leaves behind the resource contributor role
        # but removes all of its capabilities. This is so that users with
        # this role do not have to manually reassigned after deactivating
        # the plugin.
        $contributorRole = get_role( self::$contributorRoleName );
        if ($contributorRole) {
            foreach ($this->contributorCaps as $capability) {
                $contributorRole->remove_cap( $capability );
            }
        }

        $editorRole = get_role('editor');
        foreach($this->moderatorCaps as $capability) {
            $editorRole->remove_cap( $capability );
        }

        $adminRole = get_role('administrator');
        foreach($this->moderatorCaps as $capability) {
            $adminRole->remove_cap( $capability );
        }
    }
}