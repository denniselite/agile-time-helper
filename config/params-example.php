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
            'userIssuesInMonth' => '(assignee = currentUser() OR Responsible = currentUser()) AND  status changed from Test to \'Ready for Release\' during (startOfMonth(), now())  ORDER BY cf[10002] ASC, priority DESC, \'Feedback priority\' DESC'
        ]
    ]
];