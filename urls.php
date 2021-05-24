<?php
require_once 'engine/url.php';

$urls = [
    url('/', 'HomeView', 'index'),
    //auth
    url('/login', 'AuthView', 'login'),
    url('/login', 'AuthView', 'login', 'POST'),
    url('/logout', 'AuthView', 'logout'),
    //orders
    url('/orders/list', 'OrderView', 'order_list'),
    url('/orders/add', 'OrderView', 'post', 'POST'),
    url('/orders/update', 'OrderView', 'put', 'POST'),
    url('/orders/single', 'OrderView', 'single'),
    url('/orders/comments/add', 'CommentView', 'post', 'POST'),
    url('/orders/comments/delete', 'CommentView', 'delete', 'POST'),
    //admin
    url('/dashboard', 'DashboardView', 'index'),
    url('/dashboard/get-list', 'DashboardView', 'get_list'),
    url('/dashboard/archival', 'ArchivalView', 'index'),
    url('/dashboard/orders/single', 'DashboardView', 'single'),
    url('/dashboard/orders/proceed', 'DashboardView', 'proceed', 'POST'),
    url('/dashboard/orders/order-execution', 'DashboardView', 'order_execution', 'POST'),
    url('/dashboard/orders/order-transaction', 'DashboardView', 'order_transaction', 'POST'),
    url('/dashboard/orders/management-request', 'DashboardView', 'management_request', 'POST'),
    url('/dashboard/orders/management-accept', 'DashboardView', 'management_accept', 'POST'),
    url('/dashboard/orders/stage-dates', 'DashboardView', 'stage_dates', 'POST'),
    url('/dashboard/orders/paytime-settings', 'DashboardView', 'paytime_settings', 'POST'),
    url('/dashboard/statistics', 'StatisticView', 'index'),
    url('/dashboard/statistics/generate', 'StatisticView', 'generate'),
    url('/dashboard/statistics/generate-xlsx', 'StatisticView', 'generate_xlsx'),
    //files
    url('/files', 'FileView', 'index'),
    //user
    url('/account', 'UserView', 'index'),
    url('/account', 'UserView', 'index', 'POST'),
    //pdf
    url('/orders/pdf-generate', 'PDFView', 'index'),
    //messages
    url('/orders/messages/delete', 'MessageView', 'delete', 'GET')
];

?>