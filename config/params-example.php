<?php
/**
 * Created by PhpStorm.
 * User: denniselite
 * Date: 18.10.16
 * Time: 15:24
 */

return [
    'jira' => [
        'host' => 'http://jira.fbs-d.com',
        'apiSearchPath' => '/rest/api/2/search',
        'issueBrowse' => '/browse',
        'login' => '',
        'password' => '',
        'jql' => [
            'userIssuesInMonth' => '(assignee = currentUser() OR Responsible = currentUser()) AND  status changed from Test to \'Ready for Release\' during (startOfMonth(), now())  ORDER BY cf[10002] ASC, priority DESC, \'Feedback priority\' DESC',
            'userIssuesInMonthInDev' => '(assignee = currentUser() OR Responsible = currentUser()) AND  status changed from \'Open\' to \'In development\' during (startOfMonth(), now()) AND status = \'In development\' ORDER BY cf[10002] ASC, priority DESC, \'Feedback priority\' DESC',
            'userIssuesInMonthReview' => '(assignee = currentUser() OR Responsible = currentUser()) AND  status changed from \'In development\' to \'Review\' during (startOfMonth(), now()) AND status = \'Review\' ORDER BY cf[10002] ASC, priority DESC, \'Feedback priority\' DESC',
        ],
        'kanban' => [
            'estimate' => 0.6
        ],
        'ignoreTasks' => []
    ]
];