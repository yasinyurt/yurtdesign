<?php
require_once '../../../../src/config.php';
require_once '../../../../src/includes/analytics.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['link_url']) && isset($input['page_url'])) {
    $analytics = new Analytics();
    $analytics->trackLinkClick($input['link_url'], $input['page_url']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>