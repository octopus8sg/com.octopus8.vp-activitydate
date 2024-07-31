<?php

require_once 'vp_activitydate.civix.php';

use CRM_VpActivitydate_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function vp_activitydate_civicrm_config(&$config): void {
  _vp_activitydate_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function vp_activitydate_civicrm_install(): void {
  _vp_activitydate_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function vp_activitydate_civicrm_enable(): void {
  _vp_activitydate_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_pre().
 *
 * Sets the "Registration Date" custom field value to the current date when
 * creating a new activity of type "Volunteer Event Registration".
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre/
 */
function vp_activitydate_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName == 'Activity' && $op == 'create') {
    $activityTypeId = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Volunteer Event Registration');
    if (isset($params['activity_type_id']) && $params['activity_type_id'] == $activityTypeId) {
      try {
        $customFields = civicrm_api4('CustomField', 'get', [
          'select' => [
            'id',
          ],
          'where' => [
            ['custom_group_id:name', '=', 'Volunteer_Event_Registration_Details'],
            ['name', '=', 'Registration_Date'],
          ],
          'checkPermissions' => FALSE,
        ]);

        Civi::log()->debug('API response: ' . print_r($customFields, true));

        if (!empty($customFields)) {
          $customFieldId = $customFields[0]['id'];
          Civi::log()->debug('Testing custom field ID: ' . $customFieldId);
          $now = new DateTime();
          $timestamp = $now->format('YmdHis');
          $params['custom_' . $customFieldId] = $timestamp;

          // Update the nested custom array directly
          if (!isset($params['custom'])) {
            $params['custom'] = [];
          }
          $params['custom'][770] = [
            '-1' => [
              'id' => null,
              'value' => $timestamp,
              'type' => 'Date',
              'custom_field_id' => $customFieldId,
              'custom_group_id:name' => 'Volunteer_Event_Registration_Details',
              //'custom_group_id' => 12,
              'table_name' => 'civicrm_value_volunteer_eve_123',
              'column_name' => 'registration_date_770',
              'file_id' => null,
              'is_multiple' => 0,
              'serialize' => 0
            ]
          ];

          Civi::log()->debug('Modified params: ' . print_r($params, true));
        }
      } catch (\Exception $e) {
        // Handle error
        CRM_Core_Error::debug_log_message('Error retrieving custom field ID for Registration Date: ' . $e->getMessage());
      }
    }
  }
}



