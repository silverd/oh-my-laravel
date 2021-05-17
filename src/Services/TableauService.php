<?php

/**
 * Tableau Server RESTful API
 *
 * @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api.htm
 *
 * @author silverd <jian.jiang@wetax.com.cn>
 */

namespace Silverd\OhMyLaravel\Services;

class TableauService extends AbstractService
{
    const UNLICENSED_ROLE = 'Unlicensed';

    protected function request(string $method, string $url, array $params = [], array $headers = [])
    {
        $headers += [
            'Accept' => 'application/json',
        ];

        $prefix = $this->config['api_host'] . '/api/' . $this->config['api_version'];

        $response = \Http::withHeaders($headers)->$method($prefix . $url, $params);

        $response = json_decode($response, true);

        if (isset($response['error'])) {
            throws(implode(' | ', $response['error']));
        }

        return $response;
    }

    protected function requestWithToken(string $method, string $url, array $params = [])
    {
        $authToken = \Cache::remember('tableauAuthTokenV2', 3600, function () {
            return $this->signInByAccessToken()['credentials']['token'] ?? '';
        });

        $headers = [
            'X-Tableau-Auth' => $authToken,
        ];

        return $this->request($method, $url, $params, $headers);
    }

    protected function getParams(array $params, array $fields)
    {
        $updated = [];

        foreach ($fields as $field) {
            if (isset($params[$field])) {
                $updated[$field] = $params[$field];
            }
        }

        return $updated;
    }

    public function signInByPassword()
    {
        $params = [
            'credentials' => [
                'name' => $this->config['login_name'],
                'password' => $this->config['login_password'],
                'site' => [
                    'contentUrl' => '',
                ],
            ],
        ];

        return $this->request('POST', '/auth/signin', $params);
    }

    public function signInByAccessToken()
    {
        $params = [
            'credentials' => [
                'personalAccessTokenName' => $this->config['pat_name'],
                'personalAccessTokenSecret' => $this->config['pat_secret'],
                'site' => [
                    'contentUrl' => '',
                ],
            ],
        ];

        return $this->request('POST', '/auth/signin', $params);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_users_and_groups.htm#get_users_on_site
    public function getUsersOnSite(string $filter = '', string $fields = '_all_')
    {
        $params = [
            'filter' => $filter,
            'fields' => $fields,
        ];

        return $this->requestWithToken('GET', '/sites/' . $this->config['site_id'] . '/users', $params);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_users_and_groups.htm#query_user_on_site
    public function queryUserOnSite(string $userId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/users/' . $userId;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_users_and_groups.htm#update_user
    public function updateUser(string $userId, array $params)
    {
        $updated = $this->getParams($params, [
            'fullName',
            'email',
            'password',
            'siteRole',
            'authSetting',
        ]);

        if (! $updated) {
            return false;
        }

        $url = '/sites/' . $this->config['site_id'] . '/users/' . $userId;

        return $this->requestWithToken('PUT', $url, ['user' => $updated]);
    }

    public function changeRole(string $userId, string $siteRole = 'Unlicensed')
    {
        return $this->updateUser($userId, [
            'siteRole' => $siteRole,
        ]);
    }

    public function changeRoleByName(string $userName, string $siteRole = 'Unlicensed')
    {
        $result = $this->getUsersOnSite('name:eq:' . $userName);

        $userId = $result['users']['user'][0]['id'];

        return $this->updateUser($userId, [
            'siteRole' => $siteRole,
        ]);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_users_and_groups.htm#remove_user_from_site
    public function removeUserFromSite(string $mapAssetsToUserId)
    {
        $params = [
            'mapAssetsTo' => $mapAssetsToUserId,
        ];

        $url = '/sites/' . $this->config['site_id'] . '/users/' . $userId;

        return $this->requestWithToken('DELETE', $url, $params);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_workbooks_and_views.htm#query_workbooks_for_site
    public function queryWorkbooks()
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks';

        return $this->requestWithToken('GET', $url);
    }

    public function downloadWorkbook(string $workbookId, string $type = 'pdf')
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks/' . $workbookId . '/' . $type;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#query_schedules
    public function querySchedules()
    {
        $url = '/schedules';

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#get-schedule
    public function getSchedule(string $scheduleId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/schedules/' . $scheduleId;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#update_schedule
    public function updateSchedule(string $scheduleId)
    {
        $updated = $this->getParams($params, [
            'name',
            'priority',
            'frequency',
            'state',
            'executionOrder',
        ]);

        if (! $updated) {
            return false;
        }

        $url = '/sites/' . $this->config['site_id'] . '/schedules/' . $scheduleId;

        return $this->requestWithToken('PUT', $url, ['schedule' => $updated]);
    }

    public function enableSchedule(string $scheduleId, bool $on = true)
    {
        return $this->updateUser($userId, [
            'state' => $on ? 'Active' : 'Suspended',
        ]);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#get_extract_refresh_task
    public function getExtractRefreshTask(string $taskId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/tasks/extractRefreshes/' . $taskId;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#get_extract_refresh_tasks
    public function getRefreshTaskInSite(string $scheduleId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/tasks/extractRefreshes';

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#query_extract_refresh_tasks
    public function getRefreshTaskInSchedule(string $scheduleId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/schedules/' . $scheduleId . '/extracts';

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#run_extract_refresh_task
    public function runExtractRefreshTask(string $taskId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/tasks/extractRefreshes/' . $taskId . '/runNow';

        return $this->requestWithToken('POST', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#query_jobs
    public function queryJobs(string $filter = '')
    {
        $params = [
            'filter' => $filter,
        ];

        return $this->requestWithToken('GET', '/sites/' . $this->config['site_id'] . '/jobs', $params);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#query_job
    public function queryJob(string $jobId)
    {
        return $this->requestWithToken('GET', '/sites/' . $this->config['site_id'] . '/jobs/' . $jobId);
    }

    public function querySubscriptions()
    {
        return $this->requestWithToken('GET', '/sites/' . $this->config['site_id'] . '/subscriptions');
    }

    public function querySubscription(string $subscriptionId)
    {
        return $this->requestWithToken('GET', '/sites/' . $this->config['site_id'] . '/subscriptions/' . $subscriptionId);
    }

    public function deleteSubscription(string $subscriptionId)
    {
        return $this->requestWithToken('DELETE', '/sites/' . $this->config['site_id'] . '/subscriptions/' . $subscriptionId);
    }

    public function updateSubscription(string $subscriptionId)
    {
        $updated = $this->getParams($params, [
            'subject',
            'attachImage',
            'attachPdf',
            'pageOrientation',
            'pageSizeOption',
            'suspended',
        ]);

        if (! $updated) {
            return false;
        }

        $url = '/sites/' . $this->config['site_id'] . '/subscriptions/' . $subscriptionId;

        return $this->requestWithToken('PUT', $url, ['subscription' => $updated]);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_data_sources.htm#update_data_source_now
    public function updateDataSourceNow(string $datasourceId)
    {
        return $this->requestWithToken('POST', '/sites/' . $this->config['site_id'] . '/datasources/' . $datasourceId . '/refresh');
    }
}
