<?php

namespace Haxibiao\Breeze\Console\Matomo;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MatomoGoalModuleCommand extends Command
{
    protected $signature   = 'matomo:goals {--endPoint=} {--authToken=} {--siteId=}';
    protected $description = 'matomo goals 管理器:提供了 Goal 创建、查询、删除、更新函数支持';
    protected $endPoint;
    protected $authToken;
    // doc:https://developer.matomo.org/api-reference/reporting-api#Goals

    const DEFAULT_FILL_GOALS = [
        ['name' => '下载极速版js点击事件'],
        ['name' => '访问app下载页', 'matchAttribute' => 'url', 'pattern' => 'download', 'patternType' => 'contains'],
        ['name' => '下载apk的事件'],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->endPoint  = $this->option('endPoint') ?? config('matomo.app_url');
        $this->authToken = $this->option('authToken') ?? config('matomo.token_auth');
        $siteId          = $this->option('siteId') ?? config('matomo.matomo_id');

        if (empty($this->endPoint) || empty($this->authToken) || empty($siteId)) {
            return $this->error('请提供以下参数:[endPoint,authToken,siteId]');
        }

        $this->fillDefaultGoals($siteId);
    }

    public function fillDefaultGoals($siteId)
    {
        $goals = collect($this->getGoals($siteId));
        foreach (self::DEFAULT_FILL_GOALS as $defaultGoal) {
            $goalExisted = (bool) $goals->firstWhere('name', '=', $defaultGoal['name']);
            if (!$goalExisted) {
                $this->addGoal($siteId, $defaultGoal['name'], $defaultGoal);
            }
        }

        // print cli
        $newGoals = $this->getGoals($siteId);
        if (count($newGoals) == 0) {
            return $this->error('no goals');
        }
        $this->table(array_keys($newGoals[0]), $newGoals);
    }

    /**
     * Goals.getGoals (idSite)
     */
    public function getGoals($siteId)
    {
        $data = ['method' => 'Goals.getGoals', 'idSite' => $siteId];
        return $this->request($data)->json();
    }

    /**
     *  Goals.addGoal (idSite, name, matchAttribute, pattern, patternType, caseSensitive = '', revenue = '', allowMultipleConversionsPerVisit = '', description = '', useEventValueAsRevenue = '')
     */
    public function addGoal($siteId, $name, $params = [])
    {
        $data = array_merge([
            'method'         => 'Goals.addGoal',
            'patternType'    => 'contains',
            'idSite'         => $siteId,
            'name'           => $name,
            'matchAttribute' => 'manually',
            'pattern'        => 0,
        ], $params);
        return $this->request($data)->json();
    }

    /**
     * Goals.updateGoal (idSite, idGoal, name, matchAttribute, pattern, patternType, caseSensitive = '', revenue = '', allowMultipleConversionsPerVisit = '', description = '', useEventValueAsRevenue = '')
     */
    public function updateGoal($siteId, $goalId, $params = [])
    {
        $data = array_merge([
            'method'         => 'Goals.updateGoal',
            'patternType'    => 'contains',
            'idSite'         => $siteId,
            'idGoal'         => $goalId,
            'matchAttribute' => 'manually',
            'pattern'        => 0,
        ], $params);
        return $this->request($data)->json();
    }

    /**
     * Goals.deleteGoal (idSite, idGoal)
     */
    public function deleteGoal($siteId, $goalId)
    {
        $data = ['method' => 'Goals.deleteGoal', 'idSite' => $siteId, 'idGoal' => $goalId];
        return $this->request($data)->json();
    }

    public function request($data, $action = 'get')
    {
        return Http::$action($this->endPoint, array_merge($data, [
            'module'     => 'API',
            'format'     => 'json',
            'token_auth' => $this->authToken,
        ]));
    }
}
