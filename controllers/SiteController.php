<?php

namespace app\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{

    const TASKS_IN_DEV = 'inDev';
    const TASKS_REVIEW = 'review';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Проверяем содержимое конфигов
     * @param \yii\base\Action $action
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function beforeAction($action)
    {
        if (empty(Yii::$app->params['jira']['host'])) {
            throw new ServerErrorHttpException('Jira: host is empty in config');
        }

        if (empty(Yii::$app->params['jira']['apiSearchPath'])) {
            throw new ServerErrorHttpException('Jira: api search path is empty in config');
        }

        if (empty(Yii::$app->params['jira']['login'])) {
            throw new ServerErrorHttpException('Jira: user login is empty in config');
        }

        if (empty(Yii::$app->params['jira']['password'])) {
            throw new ServerErrorHttpException('Jira: user password is empty in config');
        }

        if (empty(Yii::$app->params['jira']['jql']['userIssuesInMonth'])) {
            throw new ServerErrorHttpException('Jira: JQL userIssuesInMonth is empty in config');
        }

        return parent::beforeAction($action);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionJira()
    {
        if (empty(Yii::$app->params['jira']['host'])) {
            throw new ServerErrorHttpException('Jira: host is empty in config');
        }

        if (empty(Yii::$app->params['jira']['apiSearchPath'])) {
            throw new ServerErrorHttpException('Jira: api search path is empty in config');
        }

        if (empty(Yii::$app->params['jira']['login'])) {
            throw new ServerErrorHttpException('Jira: user login is empty in config');
        }

        if (empty(Yii::$app->params['jira']['password'])) {
            throw new ServerErrorHttpException('Jira: user password is empty in config');
        }

        if (empty(Yii::$app->params['jira']['jql']['userIssuesInMonth'])) {
            throw new ServerErrorHttpException('Jira: JQL userIssuesInMonth is empty in config');
        }

        $userAuthString = base64_encode(Yii::$app->params['jira']['login'] . ':' . Yii::$app->params['jira']['password']);
        $requestParams = [
            'jql' => Yii::$app->params['jira']['jql']['userIssuesInMonth'],
            'maxResults' => 200,
        ];

        $client = new Client;
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl(Yii::$app->params['jira']['host'] . Yii::$app->params['jira']['apiSearchPath'])
            ->setHeaders([
                'Authorization' => 'Basic ' . $userAuthString,
                'content-type' => 'application/json'
            ])
            ->setContent(Json::encode($requestParams))
            ->send();
        if (!$response->isOk) {
            throw new ServerErrorHttpException('Error code: '.$response->statusCode . ' with message ' . $response->getContent());
        }

        $result = Json::decode($response->getContent());

        if (isset(Yii::$app->params['jira']['ignoreTasks'])) {
            foreach (Yii::$app->params['jira']['ignoreTasks'] as $task) {
                foreach ($result['issues'] as $id => $issue) {
                    if ($issue['key'] === $task) {
                        unset($result['issues'][$id]);
                    }
                }
            }
        }

        $requiredSP = Yii::$app->params['kanban']['estimate'] * ($this->workDaysInMonth() - Yii::$app->params['holidays_count_in_month']);

        $completedTotalSP = 0;
        foreach ($result['issues'] as $issue) {
            $completedTotalSP += $issue['fields']['customfield_10002'];
        }

        $inDevTotalSP = $this->getTotalSPByStatus(static::TASKS_IN_DEV);
        $reviewTotalSP = $this->getTotalSPByStatus(static::TASKS_REVIEW);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['issues']
        ]);

        return $this->render('jira', [
            'dataProvider' => $dataProvider,
            'completedTotalSP' => $completedTotalSP,
            'reviewTotalSP' => $reviewTotalSP,
            'inDevTotalSP' => $inDevTotalSP,
            'requiredSP' => $requiredSP
        ]);
    }

    private function getTotalSPByStatus($status)
    {
        $jql = '';
        switch ($status) {
            case static::TASKS_IN_DEV :
                $jql = Yii::$app->params['jira']['jql']['userIssuesInMonthInDev'];
                break;
            case static::TASKS_REVIEW  :
                $jql = Yii::$app->params['jira']['jql']['userIssuesInMonthReview'];
                break;
            default:
                return false;
        }

        $userAuthString = base64_encode(Yii::$app->params['jira']['login'] . ':' . Yii::$app->params['jira']['password']);

        $requestParams = [
            'jql' => $jql,
            'maxResults' => 200,
        ];

        $client = new Client;
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl(Yii::$app->params['jira']['host'] . Yii::$app->params['jira']['apiSearchPath'])
            ->setHeaders([
                'Authorization' => 'Basic ' . $userAuthString,
                'content-type' => 'application/json'
            ])
            ->setContent(Json::encode($requestParams))
            ->send();
        if (!$response->isOk) {
            throw new ServerErrorHttpException('Error code: '.$response->statusCode . ' with message ' . $response->getContent());
        }

        $result = Json::decode($response->getContent());

        $totalSP = 0;
        foreach ($result['issues'] as $issue) {
            $totalSP += $issue['fields']['customfield_10002'];
        }

        return $totalSP;

    }
    private function workDaysInMonth() {
        $count = 0;
        $month = (new \DateTime())->format('m');
        $year = (new \DateTime())->format('Y');
        $counter = mktime(0, 0, 0, $month, 1, $year);
        while (date("n", $counter) == $month) {
            if (in_array(date("w", $counter), [0, 6]) == false) {
                $count++;
            }
            $counter = strtotime("+1 day", $counter);
        }
        return $count;
    }
}
