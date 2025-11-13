<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once(PATH_HELPERS . 'sitegroups.class.php');

$pageLangFile = PATH_LANGUAGES . 'pages/site-list/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$siteGroupsHelper = new SiteGroups();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = Sanitize::html($_POST['action'] ?? '');
    $batchDate = Sanitize::html($_POST['batch_date'] ?? ($_POST['date'] ?? date('Y-m-d')));

    try {
        if ($action === 'create' || $action === 'update') {
            $groupIdValue = Sanitize::html($_POST['group_id'] ?? '');
            $domainList = $siteGroupsHelper->normalizeDomains($_POST['domains'] ?? '');
            $conflicts = $siteGroupsHelper->findConflictingDomains($domainList, $groupIdValue);

            if (!empty($conflicts)) {
                $messages = array();
                foreach ($conflicts as $domain => $info) {
                    $messages[] = sprintf(
                        $L->g('site-group-duplicate-domain'),
                        Sanitize::html($domain),
                        Sanitize::html($info['group_name'])
                    );
                }

                Alert::set(implode('<br>', $messages), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            $payload = array(
                'group_id'   => $groupIdValue,
                'group_name' => Sanitize::html($_POST['group_name'] ?? ''),
                'type'       => Sanitize::html($_POST['type'] ?? ''),
                'mode'       => Sanitize::html($_POST['mode'] ?? 'independent'),
                'domains'    => $domainList,
                'note'       => Sanitize::html($_POST['note'] ?? ''),
                'status'     => Sanitize::html($_POST['status'] ?? 'active'),
                'batch_date' => $batchDate
            );

            $siteGroupsHelper->saveGroup($payload);

            Alert::set($L->g($action === 'create' ? 'site-group-created' : 'site-group-updated'));
        } elseif ($action === 'delete') {
            $groupId = Sanitize::html($_POST['group_id'] ?? '');
            if ($groupId !== '') {
                if ($siteGroupsHelper->deleteGroup($groupId, $batchDate)) {
                    Alert::set($L->g('site-group-deleted'));
                } else {
                    Alert::set($L->g('site-group-delete-failed'), ALERT_STATUS_FAIL);
                }
            }
        }
    } catch (Exception $e) {
        Alert::set($e->getMessage(), ALERT_STATUS_FAIL);
    }

    Redirect::page('site-list');
}

$siteGroups = $siteGroupsHelper->listGroups();
$siteGroupSummary = $siteGroupsHelper->summarize($siteGroups);
$siteGroupDates = $siteGroupsHelper->getAvailableDates();

$layout['title'] .= ' - ' . $L->g('site-list-title');
