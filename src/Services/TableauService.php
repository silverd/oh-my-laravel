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

    const STATE_SUSPENDED = 'Suspended';
    const STATE_ACTIVE    = 'Active';

    protected function request(
        string $method,
        string $url,
        array $params = [],
        array $headers = [],
        string $respType = 'JSON'
    )
    {
        $headers += [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $url = $this->config['api_host'] . '/api/' . $this->config['api_version'] . $url;

        $format = 'JSON';

        // 踩坑：Tableau 服务端要求 POST 即使空参数也必须传空对象，而不能是 NULL
        if ($method == 'POST' && ! $params) {
            $params = '{}';
            $format = 'RAW';
        }

        [$result, $respBody] = guzHttpRequest($url, $params, $method, $format, $headers, $respType);

        if (isset($result['error'])) {
            throws(implode(' | ', $result['error']));
        }

        if ($respType == 'RAW') {
            return $respBody;
        }

        return $result;
    }

    protected function requestWithToken(
        string $method,
        string $url,
        array $params = [],
        string $respType = 'JSON',
        int $failTimes = 0
    )
    {
        $tokenCacheKey = 'tableauAuthTokenV5';

        $authToken = \Cache::remember($tokenCacheKey, 1800, function () {
            return $this->signInByAccessToken()['credentials']['token'] ?? '';
        });

        $headers = [
            'X-Tableau-Auth' => $authToken,
        ];

        try {
            return $this->request($method, $url, $params, $headers, $respType);
        }

        catch (\Exception $e) {

            // 遇到 AuthToken 互顶情况重试3次
            if (strpos($e->getMessage(), '401002') !== false) {
                if ($failTimes < 3) {
                    \Cache::forget($tokenCacheKey);
                    return $this->requestWithToken($method, $url, $params, $respType, ++$failTimes);
                }
            }

            throw $e;
        }
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
    public function removeUserFromSite(string $mapAssetsToUserId, string $userId)
    {
        $params = [
            'mapAssetsTo' => $mapAssetsToUserId,
        ];

        $url = '/sites/' . $this->config['site_id'] . '/users/' . $userId;

        return $this->requestWithToken('DELETE', $url, $params);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_workbooks_and_views.htm#query_workbooks_for_site
    public function queryWorkbooks(int $pageNumber = 1, int $pageSize = 1000)
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks?pageSize=' . $pageSize . '&pageNumber=' . $pageNumber;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_workbooks_and_views.htm#get_view
    public function getView(string $viewId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/views/' . $viewId;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_workbooks_and_views.htm#get_view_by_path
    public function getViewByPath(string $name)
    {
        $url = '/sites/' . $this->config['site_id'] . '/views?filter=viewUrlName:eq:' . rawurlencode($name);

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_workbooks_and_views.htm#query_views_for_site
    public function queryViews()
    {
        $url = '/sites/' . $this->config['site_id'] . '/views';

        return $this->requestWithToken('GET', $url);
    }

    public function queryWorkbook(string $workbookId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks/' . $workbookId;

        return $this->requestWithToken('GET', $url);
    }

    public function queryWorkbookViews(string $workbookId)
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks/' . $workbookId . '/views';

        return $this->requestWithToken('GET', $url);
    }

    public function downloadWorkbook(string $workbookId, string $type = 'pdf')
    {
        $url = '/sites/' . $this->config['site_id'] . '/workbooks/' . $workbookId . '/' . $type;

        return $this->requestWithToken('GET', $url);
    }

    public function downloadView(string $viewId, string $type = 'image')
    {
        $url = '/sites/' . $this->config['site_id'] . '/views/' . $viewId . '/' . $type . '?maxAge=1';

        return $this->requestWithToken('GET', $url, [], 'RAW');
    }

    public function downloadWorkbookDefaultView(string $workbookId, string $type = 'image')
    {
        $workbook = $this->queryWorkbook($workbookId);

        return $this->downloadView($workbook['workbook']['defaultViewId'], $type);
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
        $url = '/schedules/' . $scheduleId;

        return $this->requestWithToken('GET', $url);
    }

    // @see https://help.tableau.com/current/api/rest_api/en-us/REST/rest_api_ref_jobs_tasks_and_schedules.htm#update_schedule
    public function updateSchedule(string $scheduleId, array $params)
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

        $url = '/schedules/' . $scheduleId;

        return $this->requestWithToken('PUT', $url, ['schedule' => $updated]);
    }

    public function enableSchedule(string $scheduleId, bool $on = true)
    {
        return $this->updateSchedule($scheduleId, [
            'state' => $on ? self::STATE_ACTIVE : self::STATE_SUSPENDED,
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

    public function updateSubscription(string $subscriptionId, array $params)
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

    public function runScheduleExtractTasks(string $scheduleId)
    {
        $jobIds = [];

        // 找出「数据抽取计划」中的所有抽取任务
        $extracts = $this->getRefreshTaskInSchedule($scheduleId);

        $extracts = $extracts['extracts']['extract'] ?? [];

        foreach ($extracts as $task) {

            // 异步执行抽取任务
            $job = $this->runExtractRefreshTask($task['id']);

            // 返回异步请求 ID
            $jobIds[] = $job['job']['id'];
        }

        return $jobIds;
    }

    // 获取嵌入 Iframe 网页的票据
    public function getIframeTicket()
    {
        $params = [
            'username' => $this->config['ticket_user'],
        ];

        $response = \Http::asForm()->post($this->config['api_host'] . '/trusted', $params);

        return $response->body();
    }

    public function getSchedulesByNames(array $scheduleNames)
    {
        $schedules = $this->querySchedules();

        $schedules = $schedules['schedules']['schedule'];

        return array_filter($schedules, function ($schedule) use ($scheduleNames) {
            return in_array($schedule['name'], $scheduleNames);
        });
    }

    public function getWorkbooksByNames(array $workbookNames)
    {
        $workbooks = $this->queryWorkbooks();

        $workbooks = $workbooks['workbooks']['workbook'];

        return array_filter($workbooks, function ($workbook) use ($workbookNames) {
            return in_array($workbook['name'], $workbookNames);
        });
    }
    
    public function queryProjects(int $pageNumber = 1, int $pageSize = 1000)
    {
        $url = '/sites/' . $this->config['site_id'] . '/projects?pageSize=' . $pageSize . '&pageNumber=' . $pageNumber;

        return $this->requestWithToken('GET', $url);
    }
}
