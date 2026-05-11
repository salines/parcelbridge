<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            color: #222;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        h1 {
            font-size: 22px;
            margin: 0 0 12px;
        }
        h2 {
            font-size: 15px;
            margin: 18px 0 8px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f1f3f5;
            font-weight: bold;
            width: 32%;
        }
        .meta {
            color: #666;
            margin-bottom: 16px;
        }
        .text-block {
            border: 1px solid #d9d9d9;
            padding: 8px;
        }
    </style>
</head>
<body>
    <?= $this->fetch('content') ?>
</body>
</html>
